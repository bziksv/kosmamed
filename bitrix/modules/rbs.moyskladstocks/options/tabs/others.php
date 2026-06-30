<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\Internals\OptionUtils;

$arAllOptions['others'][] = GetMessage("MAIN_SETTINGS_DOC", ['#LINK#' => '/rbs-moyskladstocks/settings/others']);

$arAllOptions['others'][] = GetMessage('BETA_FUNCTIONS');

$arAllOptions['others'][] = ["identify_by_id", GetMessage('IDENTIFY_BY_ID'), '', ['checkbox', "N", $paramsCheckBox]];
$arAllOptions['others'][] = ['note' => GetMessage('IDENTIFY_BY_ID_NOTE')];

$arAllOptions['others'][] = ["identify_sections_by_id", GetMessage('IDENTIFY_SECTIONS_BY_ID'), '', ['checkbox', "N", $paramsCheckBox]];
$arAllOptions['others'][] = ['note' => GetMessage('IDENTIFY_SECTIONS_BY_ID_NOTE')];

$selectTimeZoneList = Rbs\MoyskladStocks\Internals\Enums\Timezones::getTimeZoneList();
$arAllOptions['others'][] = ["global_timezone", GetMessage('GLOBAL_TIMEZONE'), 'N', ['selectbox', ['N' => GetMessage('GLOBAL_TIMEZONE_N')] + $selectTimeZoneList]];
$arAllOptions['others'][] = ['note' => GetMessage('GLOBAL_TIMEZONE_NOTE')];

$arAllOptions['others'][] = GetMessage('TIMEZONE_PARAMS');
$arAllOptions['others'][] = ["is_eu_msk_timezone", GetMessage("IS_EU_MSK_TIMEZONE"), 'Y', ['checkbox', "N", $paramsCheckBox]];
$arAllOptions['others'][] = ['note' => GetMessage('IS_EU_MSK_TIMEZONE_NOTE')];

$arAllOptions['others'][] = GetMessage('API_ATTEMPTS');

$arAllOptions['others'][] = ["attempt_api_error_count", GetMessage("ATTEMPT_API_ERROR_COUNT"), '3', ['text', 30]];
$arAllOptions['others'][] = ["attempt_api_error_delay", GetMessage("ATTEMPT_API_ERROR_DELAY_MS"), '500', ['text', 30]];

$arAllOptions['others'][] = ['note' => GetMessage('ATTEMPT_API_ERROR_COUNT_NOTE')];

$arAllOptions['others'][] = GetMessage('CACHE_HEAD');
$arAllOptions['others'][] = ["cache_refresh_api", GetMessage("CACHE_REFRESH_API"), '', ['checkbox', "N", $paramsCheckBox]];

if ($_REQUEST["cache_refresh_api"] === 'Y') {
	\Rbs\MoyskladStocks\Config::refreshApiCacheId();
	unset($_REQUEST["cache_refresh_api"]);
}

$apiIdCache = \Rbs\MoyskladStocks\Config::getApiCacheId();
$arAllOptions['others'][] = ['note' => GetMessage('CACHE_REFRESH_API_NOTE', ['#CACHE_API_ID#' => $apiIdCache])];

$arAllOptions['others'][] = ["cache_clear", GetMessage("CACHE_CLEAR"), '', ['checkbox', "N", $paramsCheckBox]];
if ($_REQUEST["cache_clear"] === 'Y') {
	if (is_dir($_SERVER['DOCUMENT_ROOT'] . '/bitrix/cache/' . $mid . '/')) {
		Utils::delete_tree($_SERVER['DOCUMENT_ROOT'] . '/bitrix/cache/' . $mid . '/');
	}
	unset($_REQUEST["cache_clear"]);
}
$arAllOptions['others'][] = ['note' => GetMessage('CACHE_CLEAR_NOTE', ['#SIZE#' => Utils::get_human_filesize(Utils::get_dir_size($_SERVER['DOCUMENT_ROOT'] . '/bitrix/cache/' . $mid . '/'))])];


$arAllOptions['others'][] = ["cache_webhook_time", GetMessage('CACHE_WEBHOOK_TIME'), '5', ['text', '']];

//CACHE_TAG
$selectCatalogTags = $selectCatalog;
if (isset($selectCatalogTags['N'])) {
	unset($selectCatalogTags['N']);
}
if (count($selectCatalogTags) > 0) {
	$arAllOptions['others'][] = GetMessage('CACHE_TAG_STORES');
	$arAllOptions['others'][] = ["cleartagcachestores", GetMessage("CACHE_TAG_STORES_ENABLE"), '', ['checkbox', "N", $paramsCheckBox]];
	$arAllOptions['others'][] = ["tag_cache_iblocks_stores", GetMessage("CACHE_TAG_STORES_IBLOCKS"), '', ['multiselectbox', $selectCatalogTags]];
	$arAllOptions['others'][] = ['note' => GetMessage('CACHE_TAG_STORES_NOTE')];
}

$arAllOptions['others'][] = GetMessage('IMAGE_WORK');
$arAllOptions['others'][] = ["image_clear_prev", GetMessage("IMAGE_CLEAR_PREV"), '', ['checkbox', "N", $paramsCheckBox]];
$arAllOptions['others'][] = ['note' => GetMessage('IMAGE_CLEAR_PREV_NOTE')];
$arAllOptions['others'][] = ["image_clear_size", GetMessage("IMAGE_CLEAR_SIZE"), '', ['checkbox', "N", $paramsCheckBox]];
$arAllOptions['others'][] = ['note' => GetMessage('IMAGE_CLEAR_SIZE_NOTE')];

$arAllOptions['others'][] = GetMessage('SERVICE_AGENT_WORK_HEAD');

if($isSaveHit) {
	\Rbs\MoyskladStocks\Agent::set("check_module_agents");
	\Rbs\MoyskladStocks\Agent::set("clear_logs", 86400);
}

//agent
OptionUtils::buildAgentOptionArray($arAllOptions['others'], 'update_ext_codes', $paramsCheckBox, false, ['limit', 'offset', 'updated']);
if ($isSaveHit) {
	OptionUtils::saveAgentAction('update_ext_codes', 'update_ext_codes');
}

$arAllOptions['others'][] = GetMessage('AGENT_ON_MODULE_HEAD');
$arAllOptions['others'][] = ['note' => GetMessage('AGENT_ON_MODULE_NOTE_DOC')];
$arAllOptions['others'][] = ['note' => GetMessage('AGENT_ON_MODULE_NOTE', ['#PROFILE_ID#' => \Rbs\MoyskladStocks\Config::getProfileId()])];

if (!empty($arAllOptions['others'])) {
	$aTabs[] = [
		"DIV" => "others",
		"TAB" => GetMessage("OTHERS_HEAD"),
		"ICON" => "order_settings",
		"TITLE" => GetMessage("OTHERS_HEAD")
	];
}