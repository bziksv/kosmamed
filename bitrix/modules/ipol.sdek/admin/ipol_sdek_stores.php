<?php
use Ipolh\SDEK\Admin\StoresForm;
use Ipolh\SDEK\Admin\StoresGrid;
use Ipolh\SDEK\Bitrix\Tools;

use Bitrix\Main\Localization\Loc;

define("ADMIN_MODULE_NAME", "ipol.sdek");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin.php");
global $APPLICATION, $USER;

Loc::loadMessages(__FILE__);

if (!CModule::IncludeModule(ADMIN_MODULE_NAME))
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

//if ($GLOBALS['APPLICATION']->GetGroupRight(IPOLH_SDEK) > 'D')

$APPLICATION->SetTitle(Tools::getMessage('ADMIN_STORES_TITLE'));
$APPLICATION->SetAdditionalCSS('/bitrix/css/main/grid/webform-button.css');

if (!CheckVersion(SM_VERSION, '18.0.0')) {
    $gridVersionLock = new CAdminMessage([
        'MESSAGE' => Tools::getMessage("ADMIN_GRID_MIN_VERSION"),
        'TYPE' => 'ERROR',
        'DETAILS' => Tools::getMessage("ADMIN_GRID_MIN_VERSION_TEXT"),
        'HTML' => true
    ]);
    echo $gridVersionLock->Show();
} else {
    // Interface buttons, filter and grid
    $grid = new StoresGrid();

    $buttons = $grid->getButtons();
    if (!empty($buttons)) {
        $APPLICATION->IncludeComponent('bitrix:ui.button.panel', '.default', [
            'ALIGN'   => 'left',
            'BUTTONS' => $buttons,
        ]);
    }

    $columns = $grid->getFilterColumns();
    if (!empty($columns)) {
        $APPLICATION->IncludeComponent('bitrix:main.ui.filter', '.default', [
            'GRID_ID'             => $grid->getId(),
            'FILTER_ID'           => $grid->getFilterId(),
            'FILTER'              => $columns,
            'ENABLE_LIVE_SEARCH'  => false,
            'ENABLE_LABEL'        => true,
            'DISABLE_SEARCH'      => false, // Quick search in FIND field
            // Undocumented ?
            'VALUE_REQUIRED_MODE' => false,
            'VALUE_REQUIRED'      => false,
        ]);
    }

    $APPLICATION->IncludeComponent('bitrix:main.ui.grid', '.default', [
        'GRID_ID'                   => $grid->getId(),
        'COLUMNS'                   => $grid->getColumns(),
        'ROWS'                      => $grid->getRows(),
        'NAV_OBJECT'                => $grid->getPagination(),
        'AJAX_ID'                   => \CAjax::getComponentID('bitrix:main.ui.grid', '.default', ''),
        'AJAX_MODE'                 => 'Y',
        'AJAX_OPTION_HISTORY'       => false,
        'AJAX_OPTION_JUMP'          => 'N',
        'PAGE_SIZES'                => [
            ['VALUE' => '10',   'NAME' => '10'],
            ['VALUE' => '20',   'NAME' => '20'],
            ['VALUE' => '50',   'NAME' => '50'],
            ['VALUE' => '100',  'NAME' => '100'],
            ['VALUE' => '200',  'NAME' => '200'],
            ['VALUE' => '500',  'NAME' => '500'],
        ],
        'SHOW_ROW_CHECKBOXES'       => false,
        'SHOW_CHECK_ALL_CHECKBOXES' => false,
        'SHOW_ROW_ACTIONS_MENU'     => true,
        'SHOW_GRID_SETTINGS_MENU'   => true,
        'SHOW_NAVIGATION_PANEL'     => true,
        'SHOW_PAGINATION'           => true,
        'SHOW_SELECTED_COUNTER'     => true,
        'SHOW_TOTAL_COUNTER'        => true,
        'SHOW_PAGESIZE'             => true,
        'SHOW_ACTION_PANEL'         => false,
        'ALLOW_SORT'                => true,
        'ALLOW_COLUMNS_SORT'        => true,
        'ALLOW_COLUMNS_RESIZE'      => true,
        'ALLOW_HORIZONTAL_SCROLL'   => true,
        'ALLOW_PIN_HEADER'          => true,
        'TOTAL_ROWS_COUNT'          => $grid->getPagination()->getRecordCount(),

        // Undocumented params
        'EDITABLE'                  => true,

        // Group actions
        'ACTION_PANEL'              => [
            'GROUPS' => [
                'TYPE' => [
                    'ITEMS' => $grid->getControls(),
                ]
            ]
        ],
    ]);

    Tools::initJQuery();
    // CSS hack for grid coloring
    ?>
    <style>.main-grid-cell {background: none !important;}</style>
    <script type="text/javascript" src="<?=Tools::getJSPath()?>adminInterface.js"></script>
    <script type="text/javascript">
        var <?=IPOLH_SDEK_LBL?>controller = new ipol_sdek_adminInterface({
            'ajaxPath': '<?=Tools::getJSPath()?>ajax.php',
            'label':    '<?=IPOLH_SDEK?>',
            'logging':   true
        });

        <?=IPOLH_SDEK_LBL?>controller.expander({
            label: '<?=IPOLH_SDEK_LBL?>',
        });

        <?=IPOLH_SDEK_LBL?>controller.addPage('grid', {
            init: function(){
                this.actions(this);
                this.grids(this);
            },
            actions: (function(self){
                self.actions = {
                    newStore: function(){
                        self.self.ajax({
                            data: {
                                isdek_action: 'newStoreRequest',
                                isdek_token:  '<?=\sdekHelper::getModuleToken();?>',
                            },
                            dataType: 'json',
                            success:  self.actions.onEditStore
                        });
                    },
                    editStore: function(storeId){
                        self.self.ajax({
                            data: {
                                isdek_action: 'loadStoreRequest',
                                isdek_token:  '<?=\sdekHelper::getModuleToken();?>',
                                storeId:      storeId,
                            },
                            dataType: 'json',
                            success:  self.actions.onEditStore
                        });
                    },
                    onEditStore: function(data){
                        if (data.success){
                            <?=IPOLH_SDEK_LBL?>controller.getPage('form').onEdit(data.data);
                        } else {
                            var str = '<?=Tools::getMessage('MESS_STORE_NOT_LOADED')?>';
                            if (data.errors.length) {
                                str += "\n" + data.errors;
                            }
                            alert(str);
                        }
                    },
                    deleteStore: function(storeId){
                        if (confirm('<?=Tools::getMessage('MESS_STORE_DELETE')?>')) {
                            self.self.ajax({
                                data: {
                                    isdek_action: 'deleteStoreRequest',
                                    isdek_token:  '<?=\sdekHelper::getModuleToken();?>',
                                    storeId:      storeId,
                                },
                                dataType: 'json',
                                success:  self.actions.onDeleteStore
                            });
                        }
                    },
                    onDeleteStore: function(data){
                        if (data.success){
                            alert('<?=Tools::getMessage('MESS_STORE_DELETED')?>');
                            self.grids.reload();
                        } else {
                            var str = '<?=Tools::getMessage('MESS_STORE_NOT_DELETED')?>';
                            if (data.errors.length) {
                                str += "\n" + data.errors;
                            }
                            alert(str);
                        }
                    },
                }
            }),
            grids: (function(self){
                self.grids = {
                    reload: function(){
                        self.grids.reloading('<?=$grid->getId()?>');
                    },
                    reloading: function(gridId){
                        var reloadParams = {apply_filter: 'Y',
                            /* clear_nav: 'Y' */
                        };
                        var gridObject = BX.Main.gridManager.getById(gridId);

                        if (gridObject.hasOwnProperty('instance')) {
                            gridObject.instance.reloadTable('POST', reloadParams);
                        }
                    }
                }
            })
        });
        $(document).ready(<?=IPOLH_SDEK_LBL?>controller.init);
    </script>
    <?php

    // Form window
    StoresForm::makeFormWindow();
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");