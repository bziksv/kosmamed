<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

use \Bitrix\Main\Application;

$arLocalParams = [];

$arLocalParams[] = GetMessage('BETA_FUNCTIONS');

$selectTimeZoneList = Rbs\Moysklad\Internals\Enums\Timezones::getTimeZoneList();
$arLocalParams[] = ["global_timezone", GetMessage('GLOBAL_TIMEZONE'), 'N', ['selectbox', ['N' => GetMessage('GLOBAL_TIMEZONE_N')] + $selectTimeZoneList]];
$arLocalParams[] = ['note' => GetMessage('GLOBAL_TIMEZONE_NOTE')];

$arLocalParams[] = GetMessage('TIMEZONE_PARAMS');
$arLocalParams[] = ["is_eu_msk_timezone", GetMessage("IS_EU_MSK_TIMEZONE"), 'Y', ['checkbox', "N", $paramsCheckBox]];
$arLocalParams[] = ['note' => GetMessage('IS_EU_MSK_TIMEZONE_NOTE')];

$arLocalParams[] = GetMessage('API_ATTEMPTS');

$arLocalParams[] = ["attempt_api_error_count", GetMessage("ATTEMPT_API_ERROR_COUNT"), '3', ['text', 30]];
$arLocalParams[] = ["attempt_api_error_delay", GetMessage("ATTEMPT_API_ERROR_DELAY_MS"), '500', ['text', 30]];
$arLocalParams[] = ['note' => GetMessage('ATTEMPT_API_ERROR_COUNT_NOTE')];

$arLocalParams[] = GetMessage('LOGGER_NOTIFY');

$arLocalParams[] = ["logger_apiexchange_notify", GetMessage("LOGGER_API_EXCHANGE_NOTIFY"), 'Y', ['checkbox', "Y"]];
$arLocalParams[] = ['logger_apiexchange_byemail', GetMessage("LOGGER_API_EX_BY_MAIL"), '', ['checkbox', "Y"]];
$arLocalParams[] = ["logger_apiexchange_mail", GetMessage("LOGGER_API_EX_MAIL"), COption::GetOptionString("main", "email_from"), ['text', 30]];

//CACHE
$arLocalParams[] = GetMessage('CACHE_HEAD');
$arLocalParams[] = ["cache_basket_bx_items", GetMessage("CACHE_BASKET_BX_ITEMS"), '86400', ['text', 30]];
$arLocalParams[] = ['note' => GetMessage('CACHE_BASKET_BX_ITEMS_NOTE')];

$arLocalParams[] = ["cache_basket_ms_items", GetMessage("CACHE_BASKET_MS_ITEMS"), '86400', ['text', 30]];
$arLocalParams[] = ['note' => GetMessage('CACHE_BASKET_MS_ITEMS_NOTE')];

$arLocalParams[] = ["cache_refresh_api", GetMessage("CACHE_REFRESH_API"), '', ['checkbox', "N"]];
$apiIdCache = \Rbs\Moysklad\Config::getApiCacheId();
$arLocalParams[] = ['note' => GetMessage('CACHE_REFRESH_API_NOTE', ['#CACHE_API_ID#' => $apiIdCache])];

if ($request->get('cache_refresh_api') === 'Y') {
	\Rbs\Moysklad\Config::refreshApiCacheId();
	unset($_REQUEST["cache_refresh_api"]);
}

$arLocalParams[] = ["cache_clear", GetMessage("CACHE_CLEAR"), '', ['checkbox', "N", $paramsCheckBox]];
$cacheDir = Application::getDocumentRoot() . '/bitrix/cache/' . $mid . '/';
if ($request->get('cache_clear') === 'Y') {
	if (is_dir($cacheDir)) {
		delTree($cacheDir);
	}
	unset($_REQUEST["cache_clear"]);
}
$arLocalParams[] = ['note' => GetMessage('CACHE_CLEAR_NOTE', ['#SIZE#' => human_filesize((int)getDirSize($cacheDir))])];
   
$arLocalParams[] = ["cache_webhook_time", GetMessage('CACHE_WEBHOOK_TIME'), '5', ['text', '']];
$arLocalParams[] = ['note' => GetMessage('CACHE_WEBHOOK_TIME_NOTE')];

//import agent
$arLocalParams[] = GetMessage('AGENT_HEAD');

$arLocalParams[] = ["agent", GetMessage('AGENT_CHECK_ORDERS'), '', ['statichtml', ""]];

$arLocalParams[] = ["set_agent_check_orders", GetMessage("SET_AGENT"), 'Y', ['checkbox', "N", $paramsCheckBox]];
$arLocalParams[] = ["last_date_refresh_minutes", GetMessage("CHECK_ORDERS_UPDATED_FROM_MIN"), '30', ['text', 30]];


if ($isSaveHit) {
	if ($_REQUEST["set_agent_check_orders"] === 'Y') {
		\Rbs\Moysklad\Agent::set("check_orders_from_ms();");
	}
}

$arLocalParams[] = getAgentInfo('check_orders_from_ms();');

$arLocalParams[] = ['note' => GetMessage('AGENT_CHECK_ORDERS_NOTE')];

//export agent

$arLocalParams[] = ["agent", GetMessage('AGENT_FROM_EVENTS'), '', ['statichtml', ""]];

$arLocalParams[] = ["agent_interval", GetMessage("AGENT_INTERVAL"), '60', ['text', 30]];
$arLocalParams[] = ["agent_live_time", GetMessage("AGENT_LIVE_TIME"), '86400', ['text', 30]];
$arLocalParams[] = ['note' => GetMessage('AGENT_FROM_EVENTS_NOTE')];




if (!empty($arLocalParams)) {
	$arAllOptions['service_settings'] = $arLocalParams;
	$aTabs[] = [
		"DIV" => "service_settings",
		"TAB" => GetMessage("SERVICE_SETTINGS_HEAD"),
		"ICON" => "order_settings",
		"TITLE" => GetMessage("SERVICE_SETTINGS_HEAD")
	];
	unset($arLocalParams);
}