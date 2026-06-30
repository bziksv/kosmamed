<?

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;

CJSCore::Init(array("jquery"));
Asset::getInstance()->addJs('/bitrix/js/up.boxberrydelivery/settings.js?v=' . time());
Loc::loadMessages(__FILE__);
class CModuleOptions
{
    public $arCurOptionValues = array();
    private $errors = [];
    private $module_id = '';
    private $arTabs = array();
    private $arGroups = array();
    private $arOptions = array();
    private $need_access_tab = false;

    public function __construct($module_id, $arTabs, $arGroups, $arOptions, $need_access_tab = false)
    {
        $this->module_id = $module_id;
        $this->arTabs = $arTabs;
        $this->arGroups = $arGroups;
        $this->arOptions = $arOptions;
        $this->need_access_tab = $need_access_tab;

        if($need_access_tab)
            $this->arTabs[] = array(
                'DIV' => 'edit_access_tab',
                'TAB' => Loc::getMessage('CmoduleOption_access_Tab'),
                'ICON' => '',
                'TITLE' => Loc::getMessage('CmoduleOption_access_title')
            );

        if($_REQUEST['update'] == 'Y' && check_bitrix_sessid()){
            $this->SaveOptions();
            if($this->need_access_tab)
            {
                $this->SaveGroupRight();
            }
        }


        $this->GetCurOptionValues();
    }

    private function SaveOptions()
    {
        foreach($this->arOptions as $opt => $arOptParams)
        {
            if($arOptParams['TYPE'] != 'CUSTOM')
            {
                $val = $_REQUEST[$opt];

                if ($opt === 'RECEPTION_POINT_NAME' && !ReceptionPointsTable::getPointCodeByName($val)) {
                    $this->errors[] = Loc::getMessage('BB_RECEPTION_POINT_NOT_SELECTED_ERROR');
                    $val = '';
                }

                if ($opt === 'RECEPTION_POINT_CODE' && !ReceptionPointsTable::getPointNameByCode($val)) {
                    $this->errors[] = Loc::getMessage('BB_RECEPTION_POINT_NOT_SELECTED_ERROR');
                    $val = '';
                }

                if ($opt === 'API_TOKEN' && empty($val)) {
                    $this->errors[] = Loc::getMessage('BB_EMPTY_API_TOKEN');
                    $val = '';
                }

                if($arOptParams['TYPE'] == 'CHECKBOX' && $val != 'Y')
                    $val = 'N';
                elseif(is_array($val))
                    $val = serialize($val);

                COption::SetOptionString($this->module_id, $opt, $val);
            }
        }

        if (CBoxberry::initApi()) {
            CBoxberry::handleUserExport();
        }

        if (CBoxberry::isSourceYandex()) {
            if (Option::get($this->module_id, 'RECEPTION_POINT_NAME') && Option::get($this->module_id, 'RECEPTION_POINT_CODE')) {
                CBoxberry::removeReceptionPointSelectNeededMessage();
            }
        } elseif (CBoxberry::isReceptionPointMessageAdded()) {
            CBoxberry::removeReceptionPointSelectNeededMessage();
        }
    }

    private function SaveGroupRight()
    {
        CMain::DelGroupRight($this->module_id);
        $GROUP = $_REQUEST['GROUPS'];
        $RIGHT = $_REQUEST['RIGHTS'];

        foreach($GROUP as $k => $v) {
            if($k == 0) {
                COption::SetOptionString($this->module_id, 'GROUP_DEFAULT_RIGHT', $RIGHT[0], 'Right for groups by default');
            }
            else {
                CMain::SetGroupRight($this->module_id, $GROUP[$k], $RIGHT[$k]);
            }
        }


    }

    private function GetCurOptionValues()
    {
        foreach($this->arOptions as $opt => $arOptParams)
        {
            if($arOptParams['TYPE'] != 'CUSTOM')
            {
                $this->arCurOptionValues[$opt] = Option::get($this->module_id, $opt, $arOptParams['DEFAULT']);
                if(in_array($arOptParams['TYPE'], array('MSELECT')))
                    $this->arCurOptionValues[$opt] = unserialize($this->arCurOptionValues[$opt]);
            }
        }
    }

    public function ShowHTML()
    {
        global $APPLICATION;

        $arP = array();

        foreach($this->arGroups as $group_id => $group_params)
            $arP[$group_params['TAB']][$group_id] = array();

        if(is_array($this->arOptions))
        {
            foreach($this->arOptions as $option => $arOptParams)
            {
                $val = $this->arCurOptionValues[$option];

                if($arOptParams['SORT'] < 0 || !isset($arOptParams['SORT']))
                    $arOptParams['SORT'] = 0;

                $label = (isset($arOptParams['TITLE']) && $arOptParams['TITLE'] != '') ? $arOptParams['TITLE'] : '';
                $opt = htmlspecialchars($option);

                switch($arOptParams['TYPE'])
                {
                    case 'CHECKBOX':
                        $input = '<input type="checkbox" name="'.$opt.'" id="'.$opt.'" value="Y"'.($val == 'Y' ? ' checked' : '').' '.($arOptParams['REFRESH'] == 'Y' ? 'onclick="document.forms[\''.$this->module_id.'\'].submit();"' : '').' />';
                        break;
                    case 'CHECKBOXINACTIVE':
                        $input = '<input type="checkbox" disabled="disabled" name="'.$opt.'" id="'.$opt.'" value="N"'.($val == 'Y' ? ' checked' : '').' '.($arOptParams['REFRESH'] == 'Y' ? 'onclick="document.forms[\''.$this->module_id.'\'].submit();"' : '').' />';
                        break;
                    case 'TEXT':
                        if(!isset($arOptParams['COLS']))
                            $arOptParams['COLS'] = 25;
                        if(!isset($arOptParams['ROWS']))
                            $arOptParams['ROWS'] = 5;
                        $input = '<textarea rows="'.$type[1].'" cols="'.$arOptParams['COLS'].'" rows="'.$arOptParams['ROWS'].'" name="'.$opt.'">'.htmlspecialchars($val).'</textarea>';
                        if($arOptParams['REFRESH'] == 'Y')
                            $input .= '<input type="submit" name="refresh" value="OK" />';
                        break;
                    case 'SELECT':
                        $input = SelectBoxFromArray($opt, $arOptParams['VALUES'], $val, '', '', ($arOptParams['REFRESH'] == 'Y' ? true : false), ($arOptParams['REFRESH'] == 'Y' ? $this->module_id : ''));
                        if($arOptParams['REFRESH'] == 'Y')
                            $input .= '<input type="submit" name="refresh" value="OK" />';
                        break;
                    case 'MSELECT':
                        $input = SelectBoxMFromArray($opt.'[]', $arOptParams['VALUES'], $val, '', false, 5, 'style="width:370px;"');
                        if($arOptParams['REFRESH'] == 'Y')
                            $input .= '<input type="submit" name="refresh" value="OK" />';
                        break;
                    case 'COLORPICKER':
                        if(!isset($arOptParams['FIELD_SIZE']))
                            $arOptParams['FIELD_SIZE'] = 25;
                        ob_start();
                        echo     '<input id="__CP_PARAM_'.$opt.'" name="'.$opt.'" size="'.$arOptParams['FIELD_SIZE'].'" value="'.htmlspecialchars($val).'" type="text" style="float: left;" '.($arOptParams['FIELD_READONLY'] == 'Y' ? 'readonly' : '').' />
                                <script>
                                    function onSelect_'.$opt.'(color, objColorPicker)
                                    {
                                        var oInput = BX("__CP_PARAM_'.$opt.'");
                                        oInput.value = color;
                                    }
                                </script>';
                        $APPLICATION->IncludeComponent('bitrix:main.colorpicker', '', Array(
                                'SHOW_BUTTON' => 'Y',
                                'ID' => $opt,
                                'NAME' => ' ',
                                'ONSELECT' => 'onSelect_'.$opt
                            ), false
                        );
                        $input = ob_get_clean();
                        if($arOptParams['REFRESH'] == 'Y')
                            $input .= '<input type="submit" name="refresh" value="OK" />';
                        break;
                    case 'FILE':
                        if(!isset($arOptParams['FIELD_SIZE']))
                            $arOptParams['FIELD_SIZE'] = 25;
                        if(!isset($arOptParams['BUTTON_TEXT']))
                            $arOptParams['BUTTON_TEXT'] = '...';
                        CAdminFileDialog::ShowScript(Array(
                            'event' => 'BX_FD_'.$opt,
                            'arResultDest' => Array('FUNCTION_NAME' => 'BX_FD_ONRESULT_'.$opt),
                            'arPath' => Array(),
                            'select' => 'F',
                            'operation' => 'O',
                            'showUploadTab' => true,
                            'showAddToMenuTab' => false,
                            'fileFilter' => '',
                            'allowAllFiles' => true,
                            'SaveConfig' => true
                        ));
                        $input =     '<input id="__FD_PARAM_'.$opt.'" name="'.$opt.'" size="'.$arOptParams['FIELD_SIZE'].'" value="'.htmlspecialchars($val).'" type="text" style="float: left;" '.($arOptParams['FIELD_READONLY'] == 'Y' ? 'readonly' : '').' />
                                    <input value="'.$arOptParams['BUTTON_TEXT'].'" type="button" onclick="window.BX_FD_'.$opt.'();" />
                                    <script>
                                        setTimeout(function(){
                                            if (BX("bx_fd_input_'.strtolower($opt).'"))
                                                BX("bx_fd_input_'.strtolower($opt).'").onclick = window.BX_FD_'.$opt.';
                                        }, 200);
                                        window.BX_FD_ONRESULT_'.$opt.' = function(filename, filepath)
                                        {
                                            var oInput = BX("__FD_PARAM_'.$opt.'");
                                            if (typeof filename == "object")
                                                oInput.value = filename.src;
                                            else
                                                oInput.value = (filepath + "/" + filename).replace(/\/\//ig, \'/\');
                                        }
                                    </script>';
                        if($arOptParams['REFRESH'] == 'Y')
                            $input .= '<input type="submit" name="refresh" value="OK" />';
                        break;
                    case 'CUSTOM':
                        $input = $arOptParams['VALUE'];
                        break;
                    default:
                        if(!isset($arOptParams['SIZE']))
                            $arOptParams['SIZE'] = 25;
                        if(!isset($arOptParams['MAXLENGTH']))
                            $arOptParams['MAXLENGTH'] = 255;
                        $input = '<input type="'.($arOptParams['TYPE'] == 'INT' ? 'number' : 'text').'" size="'.$arOptParams['SIZE'].'" maxlength="'.$arOptParams['MAXLENGTH'].'" value="'.htmlspecialchars($val).'" name="'.htmlspecialchars($option).'" />';
                        if($arOptParams['REFRESH'] == 'Y')
                            $input .= '<input type="submit" name="refresh" value="OK" />';
                        break;
                }


                $notes = '';
				if(isset($arOptParams['NOTES']) && $arOptParams['NOTES'] != '') {
                    $notes =  '
                    <tr><td align="center" colspan="2">           
                        <div align="center" class="adm-info-message-wrap">
                            <div class="adm-info-message">
                                '.$arOptParams['NOTES'].'                
                            </div>
                        </div>
                    </td></tr>';
                }
                $html = $label != '' ? '<tr><td valign="center" width="40%">'.$label.'</td><td valign="top" nowrap>'.$input.'</td></tr>'.$notes : '<tr><td valign="top" colspan="2" align="center">'.$input.'</td></tr>'.$notes;
                $arP[$this->arGroups[$arOptParams['GROUP']]['TAB']][$arOptParams['GROUP']]['OPTIONS'][] = array('HTML' => $html, "INFO" => $arOptParams);
                $arP[$this->arGroups[$arOptParams['GROUP']]['TAB']][$arOptParams['GROUP']]['OPTIONS_SORT'][] = $arOptParams['SORT'];
            }

            if (!Option::get($this->module_id, 'RECEPTION_POINT_NAME') || !Option::get($this->module_id, 'RECEPTION_POINT_CODE')) {
                $this->errors[] = Loc::getMessage('BB_RECEPTION_POINT_NOT_SELECTED_ERROR');
            }

            $this->errors = array_unique($this->errors);

            if (!empty($this->errors)) {
                foreach ($this->errors as $error) {
                    CAdminMessage::ShowMessage(['MESSAGE' => Loc::getMessage('BB_ERRORS_CMODULE_HEADER'), 'TYPE' => 'ERROR', 'DETAILS' => $error, 'HTML' => true]);
                }
            }

            echo '<div class="adm-info-message-wrap" >
                <div class="adm-info-message">
                    <a href="https://help.boxberry.ru/pages/viewpage.action?pageId=1704556" target="_blank" style="text-decoration:none">'. Loc::getMessage("BB_DOC_INFO_MESSAGE").'</a> <b>|</b>
                    <a href="https://jira.boxberry.ru/servicedesk/customer/portal/38" target="_blank" style="text-decoration:none">'. Loc::getMessage("BB_SUPPORT_INFO_MESSAGE").'</a>
                </div>
            </div>';

            $tabControl = new CAdminTabControl('tabControl', $this->arTabs);
            $tabControl->Begin();
            echo '<form name="'.$this->module_id.'" method="POST" action="'.$APPLICATION->GetCurPage().'?mid='.$this->module_id.'&lang='.LANGUAGE_ID.'" enctype="multipart/form-data">'.bitrix_sessid_post();

            foreach($arP as $tab => $groups)
            {
                $tabControl->BeginNextTab();

                foreach($groups as $group_id => $group)
                {
                    if(sizeof((array)$group['OPTIONS_SORT']) > 0)
                    {
                        echo '<tr class="heading"><td colspan="2">'.$this->arGroups[$group_id]['TITLE'].'</td></tr>';

                        array_multisort($group['OPTIONS_SORT'], $group['OPTIONS']);
                        foreach($group['OPTIONS'] as $opt)
                        {
							echo $opt["HTML"];

						}

                    }
                }
            }

            if($this->need_access_tab)
            {
                $tabControl->BeginNextTab();
                $module_id = $this->module_id;
                require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
            }

            $tabControl->Buttons();

            echo '<script type="text/javascript">
                $(document).ready(function() {
                    if ($("#BB_STATUS_PARSELCREATE").val().length > 0){
                        $(\'#BB_PARSELCREATE, [for="BB_PARSELCREATE"]\').attr({"disabled":"disabled", "checked":false});
                    }else{
                        $(\'#BB_PARSELCREATE, [for="BB_PARSELCREATE"]\').removeAttr("disabled");
                    }
                });
                
                $("#BB_STATUS_PARSELCREATE").on("change", function() {
                    if ($(this).val().length > 0) {
                        $(\'#BB_PARSELCREATE, [for="BB_PARSELCREATE"]\').attr({"disabled":"disabled", "checked":false});
                    } else {
                        $(\'#BB_PARSELCREATE, [for="BB_PARSELCREATE"]\').removeAttr("disabled");
                    }
                });
                </script>';

            echo    '<input type="hidden" name="update" value="Y" />
                    <input type="submit" name="save" value="'. Loc::getMessage('CmoduleOption_submit_save').'" />
                    <input type="reset" name="reset" value="'. Loc::getMessage('CmoduleOption_submit_cancel').'" />
                    </form>';

            $tabControl->End();
        }
    }
}
?>