<?php
namespace Ipolh\SDEK\Bitrix;

/**
 * Class Tools
 * @package Ipolh\SDEK\Bitrix
 */
class Tools
{
	private static $MODULE_ID  = IPOLH_SDEK;
	private static $MODULE_LBL = IPOLH_SDEK_LBL;

    // COMMON
    static function getMessage($code,$forseUTF=false)
    {
        $mess = GetMessage('IPOLSDEK_'.$code);
        if($forseUTF){
            $mess = \sdekHelper::zajsonit($mess);
        }
        return $mess;
    }

    /**
     * Returns JS files path
     * @return string
     */
    public static function getJSPath()
    {
        return '/bitrix/js/'.self::$MODULE_ID.'/';
    }

    /**
     * Returns Tools files path
     * @return string
     */
    public static function getToolsPath()
    {
        return '/bitrix/tools/'.self::$MODULE_ID.'/';
    }

	static public function placeErrorLabel($content,$header=false)
	{?>
		<tr><td colspan='2'>
			<div class="adm-info-message-wrap adm-info-message-red">
				<div class="adm-info-message">
					<?php if($header){ ?><div class="adm-info-message-title"><?=$header?></div><?php } ?>
					<?=$content?>
					<div class="adm-info-message-icon"></div>
				</div>
			</div>
		</td></tr>
	<?php }

	static public function placeWarningLabel($content,$header=false,$heghtLimit=false,$click=false)
	{?>
		<tr><td colspan='2'>
			<div class="adm-info-message-wrap">
				<div class="adm-info-message" style='color: #000000'>
					<?php if($header){ ?><div class="adm-info-message-title"><?=$header?></div><?php } ?>
					<?php if($click){ ?><input type="button" <?=($click['id'] ? 'id="'.self::$MODULE_LBL.$click['id'].'"' : '')?> onclick='<?=$click['action']?>' value="<?=$click['name']?>"/><?php } ?>
						<div <?php if($heghtLimit){ ?>style="max-height: <?=$heghtLimit?>px; overflow: auto;"<?php } ?>>
						<?=$content?>
					</div>
				</div>
			</div>
		</td></tr>
	<?php }

    static public function isB24()
    {
        return (\COption::GetOptionString('sale','~IS_SALE_CRM_SITE_MASTER_FINISH','N') === 'Y');
    }

    static public function getB24URLs()
    {
        return array (
            'ORDER' => '/shop/orders/details/',
            'SHIPMENT' => '/shop/orders/shipment/details/',
        );
    }

    static public function isConverted()
    {
        return (\COption::GetOptionString("main","~sale_converted_15",'N') == 'Y');
    }


    // OPTIONS
    static function placeFAQ($code){?>
        <a class="ipol_header" onclick="$(this).next().toggle(); return false;"><?=self::getMessage('FAQ_'.$code.'_TITLE')?></a>
        <div class="ipol_inst"><?=self::getMessage('FAQ_'.$code.'_DESCR')?></div>
    <?php }

    static function placeHint($code){?>
        <div id="pop-<?=$code?>" class="<?=self::$MODULE_LBL?>b-popup" style="display: none; ">
            <div class="<?=self::$MODULE_LBL?>pop-text"><?=self::getMessage("HELPER_".$code)?></div>
            <div class="<?=self::$MODULE_LBL?>close" onclick="$(this).closest('.<?=self::$MODULE_LBL?>b-popup').hide();"></div>
        </div>
    <?php }

    /**
     * @param $code
     * makes da heading, FAQ und send command to establish included options
     */
    static function placeOptionBlock($code,$isHidden=false)
    {
        global $arAllOptions;
        ?>
        <tr class="heading"><td colspan="2" valign="top" align="center" <?=($isHidden) ? "class='".self::$MODULE_LBL."headerLink' onclick='".self::$MODULE_LBL."setups.getPage(\"main\").showHidden($(this))'" : ''?>><?=self::getMessage("HDR_".$code)?></td></tr>
        <?php if(self::getMessage('FAQ_' . $code . '_TITLE')) { ?>
            <tr><td colspan="2"><?php self::placeFAQ($code) ?></td></tr>
        <?php }
        /*if(Logger::getLogInfo($code)){
            self::placeWarningLabel(Logger::toOptions($code),self::getMessage("WARNING_".$code),150,array('name'=>Tools::getMessage('LBL_CLEAR'),'action'=>'IPONY_setups.getPage("main").clearLog("'.$code.'")','id'=>'clear'.$code));
        }*/
        if(array_key_exists($code,$arAllOptions)) {
            ShowParamsHTMLByArray($arAllOptions[$code], $isHidden);

            $collection = \Ipolh\SDEK\option::collection();
            foreach ($arAllOptions[$code] as $arOption){
                if(
                    array_key_exists($arOption[0],$collection) &&
                    $collection[$arOption[0]]['hasHint'] == 'Y'
                ){
                    self::placeHint($arOption[0]);
                }
            }
        }
    }

    /**
     * @param $name
     * @param $val
     * Draws tr-td. That's all. Bwahahahaha.
     */
    static function placeOptionRow($name, $val){
        if($name){?>
            <tr>
                <td width='50%' class='adm-detail-content-cell-l'><?=$name?></td>
                <td width='50%' class='adm-detail-content-cell-r'><?=$val?></td>
            </tr>
        <?php } else { ?>
            <tr><td colspan = '2' style='text-align: center'><?=$val?></td></tr>
        <?php } ?>
    <?php }

    static function defaultOptionPath()
    {
        return "/bitrix/modules/".self::$MODULE_ID."/optionsInclude/";
    }

    /**
     * Makes select for module options and forms
     * @param string $id
     * @param array $vals ['code' => 'value']
     * @param mixed $def default value
     * @param string $atrs attributes
     * @return string
     */
    public static function makeSelect($id, $vals, $def = false, $atrs = '')
    {
        $select = "<select ".(($id) ? "name='".((strpos($atrs, 'multiple') === false) ? $id : $id.'[]')."' id='{$id}' " : '')." {$atrs}>";
        if (is_array($vals)) {
            foreach ($vals as $val => $sign) {
                $select .= "<option value='{$val}' " . (((is_array($def) && in_array($val, $def)) || $def == $val) ? 'selected' : '') . ">{$sign}</option>";
            }
        }
        $select .= "</select>";

        return $select;
    }

    /**
     * Makes radio button for module options and forms
     * @param string $id
     * @param array $vals ['code' => 'value']
     * @param mixed $def default value
     * @param string $atrs attributes
     * @return string
     */
    public static function makeRadio($id, $vals, $def = false, $atrs = '')
    {
        $radio = "";
        if (is_array($vals)) {
            foreach ($vals as $val => $sign) {
                $checked = ($val == $def) ? 'checked' : '';
                $radio .= "<input type='radio' {$atrs} {$checked} name='{$id}' id='".$id.'_'.$val."' value='{$val}'>&nbsp;<label for='".$id.'_'.$val."'>{$sign}</label><br>";
            }
        }

        return $radio;
    }

    /**
     * Makes module form header row
     * @param string $code
     * @param string|false $link
     * @param string $headerClass
     */
    public static function placeFormHeaderRow($code, $link = false, $headerClass = '')
    {
        ?>
        <tr class="heading <?=(($headerClass) ? self::$MODULE_LBL.$headerClass : '')?>">
            <td colspan="2">
                <?=($link)?'<a href="javascript:void(0)" onclick="'.$link.'">':''?><?=self::getMessage('HDR_'.$code)?><?=($link)?'</a>':''?>
                <?php if(self::getMessage('HELPER_'.$code)){?> <a href='#' class='<?=self::$MODULE_LBL?>PropHint' onclick='return <?=self::$MODULE_LBL?>export.popup("pop-<?=$code?>", this, "#<?=self::$MODULE_LBL?>wndOrder");'></a><?php self::placeHint($code);}?>
            </td>
        </tr>
        <?php
    }

    /**
     * Makes module form row
     * @param $code
     * @param $type
     * @param $def
     * @param $vals
     * @param $attrs
     * @param $trClass
     */
    public static function placeFormRow($code, $type, $def = false, $vals = false, $attrs = false, $trClass = false)
    {
        if ($type !== 'select' && $type !== 'radio') {
            $attrs = "id='".self::$MODULE_LBL.$code."' name='".self::$MODULE_LBL.$code."' ".$attrs;
        }

        $class = '';
        if ($trClass) {
            $class = 'class="';
            if (is_array($trClass)) {
                foreach ($trClass as $className) {
                    $class .= self::$MODULE_LBL.$className.' ';
                }
            } else {
                $class .= self::$MODULE_LBL.$trClass;
            }
            $class .= '"';
        }
        ?>
        <tr <?=$class?>>
            <td>
                <label for="<?=self::$MODULE_LBL?><?=$code?>"><?=self::getMessage('LBL_'.$code)?></label>
                <?php if($hint = Tools::getMessage('HELPER_'.$code)) {?>
                    <a href='#' class='<?=self::$MODULE_LBL?>PropHint' onclick='return <?=self::$MODULE_LBL?>export.popup("pop-<?=$code?>", this, "#<?=self::$MODULE_LBL?>wndOrder");'></a>
                    <?php self::placeHint($code);
                }?>
            </td>
            <td>
                <?php
                switch($type) {
                    case 'text'     : ?><input type="text" <?=$attrs?> value="<?=htmlspecialchars($def)?>"/><?php break;
                    case 'radio'    : echo self::makeRadio(self::$MODULE_LBL.$code, $vals, $def, $attrs); break;
                    case 'select'   : echo self::makeSelect(self::$MODULE_LBL.$code, $vals, $def, $attrs); break;
                    case 'sign'     : echo $def; break;
                    case 'checkbox' : ?><input type="checkbox" <?=$attrs?> value="Y" <?=($def) ? 'checked' : ''?>/><?php break;
                    case 'textbox'  : ?><textarea <?=$attrs?>><?=$def?></textarea><?php break;
                    case 'hidden'   : ?><input type="hidden" <?=$attrs?>  value="<?=$def?>"/><span id="<?=self::$MODULE_LBL?>hidLabel_<?=$code?>"><?=$def?></span><?php break;
                }?>
            </td>
        </tr>
        <?php
    }

    static function sdekLinkForShipment($shipment, $shipId = false, $anyDelServ = false) //use $anyDelServ = true to add SDEK tracking link to every order that has SDEK tracknumber
    {

        if (isset($shipment['ORDER_ID']) && isset($shipment['DELIVERY_ID']))
        {
            if($anyDelServ || \sdekHelper::defineDelivery($shipment['DELIVERY_ID']))
            {
                if($shipId)
                {
                    $req = \sdekdriver::GetByOI($shipId, 'shipment');
                    if(!isset($req['SDEK_ID'])) return false;
                    else return \Ipolh\SDEK\SDEK\Tools::getTrackLink($req['SDEK_ID']);
                }
                else
                {
                    $req = \sdekdriver::GetByOI($shipment['ORDER_ID']);
                    if(!isset($req['SDEK_ID'])) return false;
                    else return \Ipolh\SDEK\SDEK\Tools::getTrackLink($req['SDEK_ID']);
                }
            }
        }

        return false;
    }

    /**
     * Checks if this is AJAX module request or not
     * @return bool
     */
    public static function isModuleAjaxRequest()
    {
        return
            /** Old key compatibility */
            (array_key_exists('isdek_action', $_REQUEST) && $_REQUEST['isdek_action']) ||
            /** New key */
            (array_key_exists(self::$MODULE_LBL.'action', $_REQUEST) && $_REQUEST[self::$MODULE_LBL.'action']);
    }

    /**
     * Adds common CSS styles for options, hints etc
     * @return void
     */
    public static function getCommonCss()
    {
        ?>
        <style>
            .<?=self::$MODULE_LBL?>errInput {
                background-color: #ffb3b3 !important;
            }
            .<?=self::$MODULE_LBL?>PropHint, .<?=self::$MODULE_LBL?>PropHint:hover {
                background: url("/bitrix/images/<?=self::$MODULE_ID?>/hint.gif") no-repeat transparent !important;
                text-decoration: none !important;
                display: inline-block;
                height: 12px;
                position: relative;
                width: 12px;
            }
            .<?=self::$MODULE_LBL?>b-popup {
                background-color: #FEFEFE;
                border: 1px solid #9A9B9B;
                box-shadow: 0 0 10px #B9B9B9;
                display: none;
                font-size: 12px;
                padding: 19px 13px 15px;
                position: absolute;
                top: 38px;
                width: 300px;
                z-index: 50;
            }
            .<?=self::$MODULE_LBL?>b-popup .<?=self::$MODULE_LBL?>pop-text {
                margin-bottom: 10px;
                color: #000;
            }
            .<?=self::$MODULE_LBL?>pop-text i {
                color: #AC12B1;
            }
            .<?=self::$MODULE_LBL?>b-popup .<?=self::$MODULE_LBL?>close {
                background: url("/bitrix/images/<?=self::$MODULE_ID?>/popup_close.gif") no-repeat transparent;
                cursor: pointer;
                height: 10px;
                position: absolute;
                right: 4px;
                top: 4px;
                width: 10px;
            }
            .<?=self::$MODULE_LBL?>warning {
                color: red !important;
            }
            .<?=self::$MODULE_LBL?>hidden {
                display: none !important;
            }
        </style>
    <?php
    }

    /**
     * Makes jQuery loaded again
     * @return void
     */
    public static function initJQuery()
    {
        $data = \CJSCore::getExtInfo('jquery');

        if (file_exists($_SERVER['DOCUMENT_ROOT'].$data['js'])) {
            \CJSCore::Init(['jquery']);
        } else if (!\CJSCore::IsExtRegistered('ipol_jquery_1')) {
            \CJSCore::RegisterExt('ipol_jquery_1', ['js' => self::getJSPath().'jquery-1.12.4.min.js']);
            \CJSCore::Init(['ipol_jquery_1']);
        }
    }

    /**
     * Encode data from site encoding to UTF8 and makes JSON from it
     * @param $wat
     * @return string
     */
    public static function jsonEncode($wat)
    {
        return json_encode(self::encodeToUTF8($wat));
    }

    /**
     * @param $handle
     * @return mixed
     * ѕреобразует данные из кодировки сайта в utf-8
     */
    public static function encodeToUTF8($handle){
        if(LANG_CHARSET !== 'UTF-8') {
            if (is_array($handle)) {
                foreach ($handle as $key => $val) {
                    unset($handle[$key]);
                    $key          = self::encodeToUTF8($key);
                    $handle[$key] = self::encodeToUTF8($val);
                }
            } elseif (is_object($handle)){
                $arCorresponds = array(); // why = because
                foreach($handle as $key => $val){
                    $arCorresponds[$key] = array('utf_key' => self::encodeToUTF8($key), 'utf_val' => self::encodeToUTF8($val));
                }
                foreach($arCorresponds as $key => $new)
                {
                    unset($handle->$key);
                    $utf_key = $new['utf_key'];
                    $handle->$utf_key = $new['utf_val'];
                }
            }else {
                $handle = $GLOBALS['APPLICATION']->ConvertCharset($handle, LANG_CHARSET, 'UTF-8');
            }
        }
        return $handle;
    }

    /**
     * @param $handle
     * @return mixed
     * ѕреобразует данные из utf-8 в кодировку сайта
     */
    public static function encodeFromUTF8($handle){
        if(LANG_CHARSET !== 'UTF-8'){
            if(is_array($handle)) {
                foreach ($handle as $key => $val) {
                    unset($handle[$key]);
                    $key          = self::encodeFromUTF8($key);
                    $handle[$key] = self::encodeFromUTF8($val);
                }
            } elseif (is_object($handle)){
                $arCorresponds = array();
                foreach($handle as $key => $val){
                    $arCorresponds[$key] = array('site_encode_key' => self::encodeFromUTF8($key), 'site_encode_val' => self::encodeFromUTF8($val));
                }
                foreach($arCorresponds as $key => $new)
                {
                    unset($handle->$key);
                    $site_encode_key = $new['site_encode_key'];
                    $handle->$site_encode_key = $new['site_encode_val'];
                }
            } else {
                $handle = $GLOBALS['APPLICATION']->ConvertCharset($handle, 'UTF-8', LANG_CHARSET);
            }
        }
        return $handle;
    }

    public static function getLogContentMindingSize($path,$maxSize = 1000000000){
        $contents = '';

        if(file_exists($path)) {
            if (filesize($path) > $maxSize) {
                $cont = file_get_contents($path);
                $cont = substr($cont, strlen($cont) / 2);
                file_put_contents($path,$cont);
            }
            $contents = file_get_contents($path);
        }

        return $contents;
    }
}
?>