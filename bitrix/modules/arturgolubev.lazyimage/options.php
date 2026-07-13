<?
use \Bitrix\Main\Loader, 
	\Bitrix\Main\Localization\Loc;

use \Arturgolubev\Lazyimage\Settings,
	\Arturgolubev\Lazyimage\Unitools as UTools;

$module_id = 'arturgolubev.lazyimage';
$module_name = str_replace('.', '_', $module_id);
if(!Loader::includeModule($module_id)){
	include 'autoload.php';
}

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/options.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/arturgolubev.lazyimage/options.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/arturgolubev.lazyimage/menu.php");

global $USER, $APPLICATION;
if (!$USER->IsAdmin()) return;

$r = Settings::checkModuleDemoEx($module_id);
if(is_array($r) && $r['status'] == 'exit'){
	$APPLICATION->RestartBuffer();
	echo \Bitrix\Main\Web\Json::encode($r);
	die();
}


/* context menu */
$aMenu = [[
	"TEXT"=>Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_DOCUMENTATIONS_OPTION"),
	"TITLE"=>Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_DOCUMENTATIONS_OPTION"),
	"MENU" => [
		[
			"TEXT"=>Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_DOCUMENTATIONS_INSTALLATION"),
			"TITLE"=>Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_DOCUMENTATIONS_INSTALLATION"),
			"LINK"=>'javascript: window.open("https://arturgolubev.ru/knowledge/course19/", "_blank");void(0);',
		],
		[
			"TEXT"=>Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_DOCUMENTATIONS_BUY"),
			"TITLE"=>Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_DOCUMENTATIONS_BUY"),
			"LINK"=>'javascript: window.open("https://arturgolubev.ru/knowledge/course1/chapter064/", "_blank");void(0);',
		],
		[
			"TEXT"=>Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_DOCUMENTATIONS_SUPPORT"),
			"TITLE"=>Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_DOCUMENTATIONS_SUPPORT"),
			"LINK"=>'javascript: window.open("https://arturgolubev.ru/knowledge/course1/", "_blank");void(0);',
		],
	],
]];

$context = new CAdminContextMenu($aMenu);
$context->Show();
/* end context menu */

$siteList = Settings::getSites();

$arOptions = [];
$arTabs = [];

foreach($siteList as $arSite){
	$optionKey = "setting_".$arSite["ID"];
	$arOptions[$optionKey] = [];
	$arTabs[] = ["DIV" => "site_setting_".$arSite["ID"], "TAB" => $arSite["NAME"].' ['.$arSite["ID"].']', "TITLE" => Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_SITE_SETTING").' "'.$arSite["NAME"].'" ['.$arSite["ID"].']', "OPTIONS"=>$optionKey];
	

	$selectedJsType = UTools::getSetting("javascript_lib_".$arSite["ID"], 'jquery');

	/* base */
	$arOptions[$optionKey][] = ["enabled_".$arSite["ID"], Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_ENABLED_SITE")." <b>".$arSite["NAME"]." [".$arSite["ID"]."]</b>", "N", ["checkbox"]];

	$arOptions[$optionKey][] = ["javascript_lib_".$arSite["ID"], Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_JAVASCRIPT_LIB"), "N", ["selectbox", [
		"js" => Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_JAVASCRIPT_LIB_JS"),
		"jquery" => Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_JAVASCRIPT_LIB_JQUERY"),
	]], false, Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_SAVE_AFTER_CHAGE")];
	
	/* img */
	$arOptions[$optionKey][] = Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_SUBTITLE_IMAGE");
	$arOptions[$optionKey][] = ["enable_image_".$arSite["ID"], Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_ENABLE_IMAGE"), "N", ["checkbox"]];
	$arOptions[$optionKey][] = ["browser_ll_image_".$arSite["ID"], Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_BROWSER_LL_IMAGE_SELECT"), "N", ["selectbox", [
		"Y" => Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_TECHNOLOGY_BROWSER"),
		"" => Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_TECHNOLOGY_JQUERY"),
	]]];

	if(COption::GetOptionString($module_id, "browser_ll_image_".$arSite["ID"]) != 'Y')
		$arOptions[$optionKey][] = ["noscript_image_".$arSite["ID"], Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_NOSCRIPT_IMAGE"), "N", ["checkbox"]];

	$arOptions[$optionKey][] = ["exceptions_image_".$arSite["ID"], Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_EXCEPTIONS_TAG"), "", ["textarea", 5, 40]];
	
	/* bg */
	$arOptions[$optionKey][] = Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_SUBTITLE_BG");
	$arOptions[$optionKey][] = ["enable_bg_".$arSite["ID"], Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_ENABLE_BG"), "N", ["checkbox"]];
	$arOptions[$optionKey][] = ["browser_ll_bg_".$arSite["ID"], Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_BROWSER_LL_IMAGE_SELECT"), "N", ["selectbox", [
		"" => Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_TECHNOLOGY_JQUERY"),
	]]];
	$arOptions[$optionKey][] = ["exceptions_bg_".$arSite["ID"], Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_EXCEPTIONS_TAG"), "", ["textarea", 5, 40]];
	
	/* iframe */
	$arOptions[$optionKey][] = Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_SUBTITLE_IFRAME");
	$arOptions[$optionKey][] = ["enable_iframe_".$arSite["ID"], Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_ENABLE_IFRAME"), "N", ["checkbox"]];
	$arOptions[$optionKey][] = ["browser_ll_iframe_".$arSite["ID"], Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_BROWSER_LL_IMAGE_SELECT"), "N", ["selectbox", [
		"Y" => Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_TECHNOLOGY_BROWSER"),
		"" => Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_TECHNOLOGY_JQUERY"),
	]]];
	$arOptions[$optionKey][] = ["exceptions_iframe_".$arSite["ID"], Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_EXCEPTIONS_TAG"), "", ["textarea", 5, 40]];

	/* additionsl */
	if($selectedJsType == 'jquery'){
		$arOptions[$optionKey][] = Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_ADDITIONAL_FEATURES");
		$arOptions[$optionKey][] = ["effect_type_".$arSite["ID"], Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_EFFECT_TYPE"), "Y", ["checkbox"]];
		$arOptions[$optionKey][] = ["effect_speed_".$arSite["ID"], Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_EFFECT_SPEED"), "500", ["text"]];
		$arOptions[$optionKey][] = ["preloading_".$arSite["ID"], Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_PRELOADING"), "300", ["selectbox", [
			"1" => Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_PRELOADING_1"),
			"100" => Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_PRELOADING_100"),
			"200" => Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_PRELOADING_200"),
			"300" => Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_PRELOADING_300"),
			"400" => Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_PRELOADING_400"),
			"500" => Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_PRELOADING_500"),
		]]];
	}
	
	$arOptions[$optionKey][] = Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_SYSTEM_SETTINGS");
	
	if($selectedJsType == 'jquery'){
		$arOptions[$optionKey][] = ["jquery_".$arSite["ID"], Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_JQUERY"), "N", ["checkbox"]];
	}
	
	$arOptions[$optionKey][] = ["page_exceptions_".$arSite["ID"], Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_PAGE_EXCEPTION"), "", ["textarea", 5, 40]];
	$arOptions[$optionKey][] = ["debug_".$arSite["ID"], Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_DEBUG"), "N", ["checkbox"]];
}

$tabControl = new CAdminTabControl("tabControl", $arTabs);

// ****** SaveBlock
if($REQUEST_METHOD=="POST" && strlen($Update.$Apply)>0 && check_bitrix_sessid())
{
	foreach ($arOptions as $aOptGroup) {
		foreach ($aOptGroup as $option) {
			__AdmSettingsSaveOption($module_id, $option);
		}
	}

	if(UTools::onComposite()){
		// CAdminNotify::Add(['MESSAGE' => Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_CLEAR_CACHE"),  'TAG' => $module_name."_clear_cache", 'MODULE_ID' => $module_id, 'ENABLE_CLOSE' => 'Y']);
		
		$staticHtmlCache = \Bitrix\Main\Data\StaticHtmlCache::getInstance();
		$staticHtmlCache->deleteAll();
	}
	
    if (strlen($Update) > 0 && strlen($_REQUEST["back_url_settings"]) > 0)
        LocalRedirect($_REQUEST["back_url_settings"]);
    else
        LocalRedirect($APPLICATION->GetCurPage() . "?mid=" . urlencode($mid) . "&lang=" . urlencode(LANGUAGE_ID) . "&back_url_settings=" . urlencode($_REQUEST["back_url_settings"]) . "&" . $tabControl->ActiveTabParam());
}
?>

<form class="lazy-setting-form" method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($mid)?>&amp;lang=<?=LANGUAGE_ID?>">
	<?$tabControl->Begin();?>
	
	<?foreach($arTabs as $key=>$tab):
		$tabControl->BeginNextTab();
			Settings::showSettingsList($module_id, $arOptions, $tab);
	endforeach;?>
	
	<?$tabControl->Buttons();?>
		<input type="submit" name="Update" value="<?=Loc::getMessage("MAIN_SAVE")?>" title="<?=Loc::getMessage("MAIN_OPT_SAVE_TITLE")?>">
				
		<?if(strlen($_REQUEST["back_url_settings"])>0):?>
			<input type="hidden" name="back_url_settings" value="<?=htmlspecialchars($_REQUEST["back_url_settings"])?>">
		<?endif?>
		
		<?=bitrix_sessid_post();?>
	<?$tabControl->End();?>
</form>

<?Settings::showInitUI();?>

<div class="help_note_wrap">
	<?= BeginNote();?>
		<p><?=Loc::getMessage("ARTURGOLUBEV_LAZYIMAGE_HELP_TAB_VALUE")?></p>
	<?= EndNote();?>
</div>
