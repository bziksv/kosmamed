<?php
use Ipolh\SDEK\Admin\CourierCallsForm;
use Ipolh\SDEK\Admin\CourierCallsGrid;
use Ipolh\SDEK\Bitrix\Tools;

use Bitrix\Main\Localization\Loc;

define("ADMIN_MODULE_NAME", "ipol.sdek");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin.php");
global $APPLICATION, $USER;

Loc::loadMessages(__FILE__);

if (!CModule::IncludeModule(ADMIN_MODULE_NAME))
    $APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

//if ($GLOBALS['APPLICATION']->GetGroupRight(IPOLH_SDEK) > 'D')

$APPLICATION->SetTitle(Tools::getMessage('ADMIN_COURIER_CALLS_TITLE'));
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
    $grid = new CourierCallsGrid();

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
                    /* Get statuses */
                    getStatusesBtn: false,
                    getStatuses: function(btnLink){
                        if (!self.actions.getStatusesBtn) {
                            self.actions.getStatusesBtn = $(btnLink);
                        }
                        self.actions.getStatusesBtn.attr('disabled', 'disabled');
                        self.actions.getStatusesBtn.css('opacity', 0.7);
                        self.self.ajax({
                            data: {
                                isdek_action: 'getCourierCallStatesRequest',
                                isdek_token:  '<?=\sdekHelper::getModuleToken();?>',
                            },
                            dataType: 'json',
                            success:  self.actions.onGetStatuses
                        });
                    },
                    onGetStatuses: function(data){
                        self.actions.getStatusesBtn.removeAttr('disabled');
                        self.actions.getStatusesBtn.css('opacity', '');
                        self.grids.reload();
                    },
                    newCall: function(callType){
                        self.self.ajax({
                            data: {
                                isdek_action: 'newCourierCallRequest',
                                isdek_token:  '<?=\sdekHelper::getModuleToken();?>',
                                callType:     callType,
                            },
                            dataType: 'json',
                            success:  self.actions.onEditCall
                        });
                    },
                    editCall: function(callId){
                        self.self.ajax({
                            data: {
                                isdek_action: 'loadCourierCallRequest',
                                isdek_token:  '<?=\sdekHelper::getModuleToken();?>',
                                callId:       callId,
                            },
                            dataType: 'json',
                            success:  self.actions.onEditCall
                        });
                    },
                    onEditCall: function(data){
                        if (data.success){
                            <?=IPOLH_SDEK_LBL?>controller.getPage('form').onEdit(data.data);
                        } else {
                            var str = '<?=Tools::getMessage('MESS_COURIER_CALL_NOT_LOADED')?>';
                            if (data.errors.length) {
                                str += "\n" + data.errors;
                            }
                            alert(str);
                        }
                    },
                    getStatus: function(callId){
                        self.self.ajax({
                            data: {
                                isdek_action: 'getCourierCallStateRequest',
                                isdek_token:  '<?=\sdekHelper::getModuleToken();?>',
                                callId:       callId,
                            },
                            dataType: 'json',
                            success:  self.actions.onGetStatus
                        });
                    },
                    onGetStatus: function(data){
                        self.grids.reload();
                    },
                    eraseCall: function(callId){
                        if (confirm('<?=Tools::getMessage('MESS_COURIER_CALL_ERASE')?>')) {
                            self.self.ajax({
                                data: {
                                    isdek_action: 'eraseCourierCallRequest',
                                    isdek_token:  '<?=\sdekHelper::getModuleToken();?>',
                                    callId:       callId,
                                },
                                dataType: 'json',
                                success:  self.actions.onEraseCall
                            });
                        }
                    },
                    onEraseCall: function(data){
                        if (data.success){
                            alert('<?=Tools::getMessage('MESS_COURIER_CALL_ERASED')?>');
                            self.grids.reload();
                        } else {
                            var str = '<?=Tools::getMessage('MESS_COURIER_CALL_NOT_ERASED')?>';
                            if (data.errors.length) {
                                str += "\n" + data.errors;
                            }
                            alert(str);
                        }
                    },
                    deleteCall: function(callId){
                        if (confirm('<?=Tools::getMessage('MESS_COURIER_CALL_DELETE')?>')) {
                            self.self.ajax({
                                data: {
                                    isdek_action: 'deleteCourierCallRequest',
                                    isdek_token:  '<?=\sdekHelper::getModuleToken();?>',
                                    callId:       callId,
                                },
                                dataType: 'json',
                                success:  self.actions.onDeleteCall
                            });
                        }
                    },
                    onDeleteCall: function(data){
                        if (data.success){
                            alert('<?=Tools::getMessage('MESS_COURIER_CALL_DELETED')?>');
                            self.grids.reload();
                        } else {
                            var str = '<?=Tools::getMessage('MESS_COURIER_CALL_NOT_DELETED')?>';
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
    CourierCallsForm::makeFormWindow();
}

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");