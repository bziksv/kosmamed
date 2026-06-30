<?
use \Bitrix\Main\Loader,
	\Bitrix\Main\Localization\Loc;

use \Arturgolubev\Chatgpt\Settings,
	\Arturgolubev\Chatgpt\Unitools as UTools,
	\Arturgolubev\Chatgpt\Admin\SettingsHelper as SHelper,
	\Arturgolubev\Chatgpt\Tools;

$module_id = 'arturgolubev.chatgpt';
$module_name = str_replace('.', '_', $module_id);

if(!Loader::includeModule($module_id)){
	include 'autoload.php';
}

IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/options.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/options.php");
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$module_id."/forms.php");

global $USER, $APPLICATION;
if (!$USER->IsAdmin()) return;

$r = Settings::checkModuleDemoEx($module_id);
if(is_array($r) && $r['status'] == 'exit'){
	$APPLICATION->RestartBuffer();
	echo \Bitrix\Main\Web\Json::encode($r);
	die();
}

$arRightsList = SHelper::getUserGroups();

$arImageModels = [
	'gpt-image-1' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_OPTION_ALG_IMAGE_MODEL_I1'),
	'dall-e-3' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_OPTION_ALG_IMAGE_MODEL_D3'),
	'dall-e-2' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_OPTION_ALG_IMAGE_MODEL_D2'),
];
$arRoles = [
	'user' => Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_ALG_ROLE_USER"),
	'system' => Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_ALG_ROLE_SYSTEM"),
	'assistant' => Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_ALG_ROLE_ASSISTANT"),
];

$arTemperatures = []; $i = 0.0;
while($i < 2.1){
	$arTemperatures[strval($i)] = strval($i); $i += 0.1;
}

$arOptions = [];
// chagpt
$arOptions['chatgpt'] = [];
$arOptions['chatgpt'][] = Loc::getMessage("ARTURGOLUBEV_CHATGPT_CONNECTION_SETTINGS");
$arOptions['chatgpt'][] = ["api_key", Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_API_KEY"), "", ["textarea"]];
$arOptions['chatgpt'][] = ["chatgpt_custom_base", Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_CHATGPT_CUSTOM_LINK"), "https://api.openai.com/v1", ["text"], 'N', Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_CHATGPT_CUSTOM_LINK_MORE")];
$arOptions['chatgpt'][] = Loc::getMessage("ARTURGOLUBEV_CHATGPT_PROXY_SETTINGS");
$arOptions["chatgpt"][] = ["note" => Loc::getMessage("ARTURGOLUBEV_CHATGPT_PROXY_SETTINGS_NOTE")];
$arOptions['chatgpt'][] = ["proxy_ip", Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_PROXY_IP"), "", ["text"], false, Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_PROXY_IP_NOTE")];
$arOptions['chatgpt'][] = ["proxy_port", Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_PROXY_PORT"), "", ["text"], false, Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_PROXY_PORT_NOTE")];
$arOptions['chatgpt'][] = ["proxy_login", Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_PROXY_LOGIN"), "", ["text"], false, Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_PROXY_LOGIN_NOTE")];
$arOptions['chatgpt'][] = ["proxy_password", Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_PROXY_PASSWORD"), "", ["text"], false, Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_PROXY_PASSWORD_NOTE")];
// $arOptions['chatgpt'][] = ["proxy", Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_PROXY"), "", ["textarea"], false, Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_PROXY_NOTE")];

$arOptions['chatgpt'][] = Loc::getMessage("ARTURGOLUBEV_CHATGPT_ALGORITM_SETTINGS");
$arOptions['chatgpt'][] = ["alg_model", Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_ALG_MODEL"), "gpt-3.5-turbo", ["selectbox", [
	'gpt-5-mini' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_MODEL_GPT_5_MINI'),
	'gpt-5-nano' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_MODEL_GPT_5_NANO'),
	'gpt-5' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_MODEL_GPT_5_0'),

	'gpt-4.1' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_MODEL_GPT_4_1'),

	'gpt-4o-mini' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_MODEL_GPT_4_O_MINI'),
	'gpt-4o' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_MODEL_GPT_4_O'),

	'o4-mini' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_MODEL_O4_MINI'),
	'o3-mini' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_MODEL_O3_MINI'),
	
	'gpt-3.5-turbo' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_MODEL_GPT_35'),
	'other' => Loc::getMessage('ARTURGOLUBEV_CHATGPT_MODEL_OTHER'),
]]];
$arOptions['chatgpt'][] = ["alg_model_other", Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_ALG_MODEL_OTHER"), "", ["text"]];
$arOptions['chatgpt'][] = ["alg_role", Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_ALG_ROLE"), "user", ["selectbox", $arRoles]];
$arOptions['chatgpt'][] = ["alg_temperature", Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_ALG_TEMPERATURE"), "0.8", ["selectbox", $arTemperatures]];
$arOptions['chatgpt'][] = ["alg_max_tokens", Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_ALG_MAX_TOKENS"), "8192", ["text"]];
$arOptions['chatgpt'][] = ["alg_image_model", Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_ALG_IMAGE_MODEL"), "gpt-3.5-turbo", ["selectbox", $arImageModels]];

// $arOptions['chatgpt'][] = Loc::getMessage("ARTURGOLUBEV_CHATGPT_SYSTEM_SETTINGS");


// sber
$arOptions['sber'] = [];
$arOptions['sber'][] = Loc::getMessage("ARTURGOLUBEV_CHATGPT_CONNECTION_SETTINGS");
$arOptions['sber'][] = ["sber_scope", Loc::getMessage("ARTURGOLUBEV_CHATGPT_SBER_SCOPE"), "", ["selectbox", [
	'GIGACHAT_API_PERS' => Loc::getMessage("ARTURGOLUBEV_CHATGPT_SBER_SCOPE_PERS"),
	'GIGACHAT_API_B2B' => Loc::getMessage("ARTURGOLUBEV_CHATGPT_SBER_SCOPE_B2B"),
	'GIGACHAT_API_CORP' => Loc::getMessage("ARTURGOLUBEV_CHATGPT_SBER_SCOPE_CORP"),
]]];
$arOptions['sber'][] = ["sber_authorization", Loc::getMessage("ARTURGOLUBEV_CHATGPT_SBER_AUTHORIZATION"), "", ["textarea"]];
$arOptions['sber'][] = Loc::getMessage("ARTURGOLUBEV_CHATGPT_ALGORITM_SETTINGS");
$arOptions['sber'][] = ["sber_model", Loc::getMessage("ARTURGOLUBEV_CHATGPT_SBER_ALG_MODEL"), "GigaChat", ["selectbox", [
	'GigaChat-2' => Loc::getMessage("ARTURGOLUBEV_CHATGPT_MODEL_GIGACHAT_2_LITE"),
	'GigaChat-2-Pro' => Loc::getMessage("ARTURGOLUBEV_CHATGPT_MODEL_GIGACHAT_2_PRO"),
	'GigaChat-2-Max' => Loc::getMessage("ARTURGOLUBEV_CHATGPT_MODEL_GIGACHAT_2_MAX"),
]]];
$arOptions['sber'][] = ["sber_role", Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_ALG_ROLE"), "user", ["selectbox", $arRoles]];
$arOptions['sber'][] = ["sber_temperature", Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_ALG_TEMPERATURE"), "0.8", ["selectbox", $arTemperatures]];
$arOptions['sber'][] = ["sber_max_tokens", Loc::getMessage("ARTURGOLUBEV_CHATGPT_SBER_ALG_MAX_TOKENS"), '4096', ["text"]];

$arOptions['deepseek'] = [];
$arOptions['deepseek'][] = Loc::getMessage("ARTURGOLUBEV_CHATGPT_CONNECTION_SETTINGS");
$arOptions['deepseek'][] = ["deepseek_api_key", Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_DEEPSEEK_API_KEY"), "", ["textarea"]];
$arOptions['deepseek'][] = ["deepseek_custom_base", Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_DEEPSEEK_CUSTOM_LINK"), "https://api.deepseek.com/v1", ["text"], 'N', Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_DEEPSEEK_CUSTOM_LINK_MORE")];
$arOptions['deepseek'][] = Loc::getMessage("ARTURGOLUBEV_CHATGPT_ALGORITM_SETTINGS");
$arOptions['deepseek'][] = ["deepseek_model", Loc::getMessage("ARTURGOLUBEV_CHATGPT_SBER_ALG_MODEL"), "GigaChat", ["selectbox", [
	'deepseek-chat' => Loc::getMessage("ARTURGOLUBEV_CHATGPT_MODEL_DS_deepseek-chat"),
	'deepseek-reasoner' => Loc::getMessage("ARTURGOLUBEV_CHATGPT_MODEL_DS_deepseek-reasoner"),
]]];
$arOptions['deepseek'][] = ["deepseek_role", Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_ALG_ROLE"), "user", ["selectbox", $arRoles]];
$arOptions['deepseek'][] = ["deepseek_temperature", Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_ALG_TEMPERATURE"), "1.0", ["selectbox", $arTemperatures]];



// $arOptions['yandex'] = [];
// $arOptions['yandex'][] = Loc::getMessage("ARTURGOLUBEV_CHATGPT_ALGORITM_SETTINGS");
// $arOptions['yandex'][] = ["yandex_model", Loc::getMessage("ARTURGOLUBEV_CHATGPT_SBER_ALG_MODEL"), "yandexgpt", ["selectbox", [
// 	'yandexgpt' => 'YandexGPT Pro',
// 	'yandexgpt-lite' => 'YandexGPT Lite',
// ]]];
// $arOptions['yandex'][] = ["yandex_temperature", Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_ALG_TEMPERATURE"), "0.4", ["selectbox", $arTemperatures]];
// $arOptions['yandex'][] = ["yandex_max_tokens", Loc::getMessage("ARTURGOLUBEV_CHATGPT_SBER_ALG_MAX_TOKENS"), '4096', ["text"]];

// additional
$arOptions['additional'] = [];
$arOptions['additional'][] = Loc::getMessage("ARTURGOLUBEV_CHATGPT_DEFAULT_FORM_SETTINGS");
$arOptions['additional'][] = ["show_query", Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_SHOW_QUERY"), "N", ["checkbox"]];
$arOptions['additional'][] = ["show_tokens", Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_SHOW_TOKENS"), "N", ["checkbox"]];

$arOptions['additional'][] = Loc::getMessage("ARTURGOLUBEV_CHATGPT_SYSTEM_SETTINGS");
/* $arOptions['additional'][] = ["httpclient", Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_HTTP_CLIENT"), 180, ["selectbox", [
	'' => 'curl',
	'bitrix' => 'bitrix',
]]]; */
$arOptions['additional'][] = ["auto_convert_markdown", Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_AUTO_CONVERT_MARKDOWN"), "N", ["checkbox"]];
$arOptions['additional'][] = ["max_wait_time", Loc::getMessage("ARTURGOLUBEV_CHATGPT_OPTION_MAX_WAIT_TIME"), 180, ["text"]];

// rights
$arOptions['rights'] = [];
$arOptions["rights"][] = ["rights_settings", Loc::getMessage("ARTURGOLUBEV_CHATGPT_RIGHTS_SETTINGS"), Loc::getMessage("ARTURGOLUBEV_CHATGPT_RIGHTS_SETTINGS_TEXT"), ["statictext"]];
$arOptions["rights"][] = ["rights_question", Loc::getMessage("ARTURGOLUBEV_CHATGPT_RIGHTS_QUESTION"), "", ["multiselectbox", $arRightsList]];
$arOptions["rights"][] = ["rights_tasks", Loc::getMessage("ARTURGOLUBEV_CHATGPT_RIGHTS_TASKS"), "", ["multiselectbox", $arRightsList]];

$arTabs = [
	["DIV" => $module_name."_chatgpt_".$arSite["ID"], "TAB" => Loc::getMessage("ARTURGOLUBEV_CHATGPT_SETTING_CHATGPT_TAB"), "TITLE" => Loc::getMessage("ARTURGOLUBEV_CHATGPT_SETTING_CHATGPT_TAB_TITLE"), "OPTIONS"=> 'chatgpt'],
	["DIV" => $module_name."_sber_".$arSite["ID"], "TAB" => Loc::getMessage("ARTURGOLUBEV_CHATGPT_SETTING_SBER_TAB"), "TITLE" => Loc::getMessage("ARTURGOLUBEV_CHATGPT_SETTING_SBER_TAB_TITLE"), "OPTIONS"=> 'sber'],
	["DIV" => $module_name."_deepseek_".$arSite["ID"], "TAB" => Loc::getMessage("ARTURGOLUBEV_CHATGPT_SETTING_DEEPSEEK_TAB"), "TITLE" => Loc::getMessage("ARTURGOLUBEV_CHATGPT_SETTING_DEEPSEEK_TAB_TITLE"), "OPTIONS"=> 'deepseek'],

	// ["DIV" => $module_name."_yandex_".$arSite["ID"], "TAB" => Loc::getMessage("ARTURGOLUBEV_CHATGPT_SETTING_YANDEX_TAB"), "TITLE" => Loc::getMessage("ARTURGOLUBEV_CHATGPT_SETTING_YANDEX_TAB"), "OPTIONS"=> 'yandex'],
	["DIV" => $module_name."_additional_".$arSite["ID"], "TAB" => Loc::getMessage("ARTURGOLUBEV_CHATGPT_SETTING_ADDITIONAL_TAB"), "TITLE" => Loc::getMessage("ARTURGOLUBEV_CHATGPT_SETTING_ADDITIONAL_TAB"), "OPTIONS"=> 'additional'],
	["DIV" => $module_name."_rights_".$arSite["ID"], "TAB" => Loc::getMessage("ARTURGOLUBEV_CHATGPT_SETTING_RIGHTS_TAB"), "TITLE" => Loc::getMessage("ARTURGOLUBEV_CHATGPT_SETTING_RIGHTS_TAB"), "OPTIONS"=> 'rights']
];

$tabControl = new CAdminTabControl("tabControl", $arTabs);

// ****** SaveBlock
if($REQUEST_METHOD=="POST" && strlen($Update.$Apply)>0 && check_bitrix_sessid())
{
	foreach ($arOptions as $aOptGroup) {
		foreach ($aOptGroup as $option) {
			__AdmSettingsSaveOption($module_id, $option);
		}
	}
	
    if (strlen($Update) > 0 && strlen($_REQUEST["back_url_settings"]) > 0)
        LocalRedirect($_REQUEST["back_url_settings"]);
    else
        LocalRedirect($APPLICATION->GetCurPage() . "?mid=" . urlencode($mid) . "&lang=" . urlencode(LANGUAGE_ID) . "&back_url_settings=" . urlencode($_REQUEST["back_url_settings"]) . "&" . $tabControl->ActiveTabParam());
}

SHelper::baseWorkers();
?>

<div class="ag_options">
	<?SHelper::checkModuleRules();?>
	<form method="post" action="<?echo $APPLICATION->GetCurPage()?>?mid=<?=urlencode($mid)?>&amp;lang=<?=LANGUAGE_ID?>">
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
</div>


<?Settings::showInitUI();?>

<style>
	textarea {
		min-width: 400px;
		min-height: 60px;
	}
	#input_row_alg_model_other {
		display: none;
	}
</style>

<script>
	var modelInput = document.querySelector('select[name="alg_model"]'), modelRow = document.querySelector('#input_row_alg_model_other');
	modelInput.addEventListener('change', function(){
		checkRowView();
	});
	document.addEventListener("DOMContentLoaded", (event) => {
		checkRowView();
	});

	function checkRowView(){
		if(modelInput.value == 'other'){
			modelRow.style.display = 'table-row';
		}else{
			modelRow.style.display = 'none';
		}
	}
</script>

<div class="help_note_wrap">
	<?= BeginNote();?>
		<p class="title"><?=Loc::getMessage("ARTURGOLUBEV_CHATGPT_HELP_TAB_TITLE")?></p>
		<p><?=Loc::getMessage("ARTURGOLUBEV_CHATGPT_HELP_TAB_VALUE")?></p>
	<?= EndNote();?>
</div>
