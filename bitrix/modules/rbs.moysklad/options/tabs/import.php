<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

use \Rbs\Moysklad\Utils;
use \Rbs\Moysklad\LangMsg;
use \Rbs\Moysklad\Internals\OptionUtils;

$arAllOptions['import_orders'][] = ['note' => GetMessage('ORDER_API_IMPORT_NOTE')];

$arAllOptions['import_orders'][] = ["import_order", GetMessage("ORDER_API_IMPORT_PARAM"), '', ['checkbox', "N"]];

$arAllOptions['import_orders'][] = GetMessage('ORDER_IMPORT_TYPES');
$arAllOptions['import_orders'][] = ['note' => GetMessage('ORDER_IMPORT_TYPES_NOTE')];
$arAllOptions['import_orders'][] = ["import_type", GetMessage("ORDER_IMPORT_TYPES_SELECT"), '', ['selectbox', [
	'CREATE' => GetMessage("ORDER_IMPORT_FROM_CREATE"),
	'UPDATE' => GetMessage("ORDER_IMPORT_FROM_UPDATE")
]]];
if(Utils::is_count($selectPropsSkladBool)){
	$arAllOptions['import_orders'][] = ["import_type_update_flag", GetMessage('ORDER_IMPORT_FROM_UPDATE_FIELD_CHECK'), '', ['selectbox', $selectPropsSkladBool]];
} else {
	$arAllOptions['import_orders'][] = ['note' => GetMessage('ORDER_IMPORT_NEED_BOOL')];
}

$arAllOptions['import_orders'][] = GetMessage('ORDER_STANDART_FIELDS');
$arAllOptions['import_orders'][] = ["import_site", GetMessage('ORDER_API_IMPORT_SITE'), '', ['selectbox', $siteArray]];
$arAllOptions['import_orders'][] = ["import_order_pay", GetMessage("ORDER_API_IMPORT_PAY"), '', ['selectbox', $allPaysystemsServices]];
$arAllOptions['import_orders'][] = ["import_order_delivery", GetMessage("ORDER_API_IMPORT_DELIVERY"), '', ['selectbox', $allDeliveryServices]];
$arAllOptions['import_orders'][] = ["import_user_id", GetMessage('ORDER_USER_ID_IMPORT'), '', ['text', '']];

$arAllOptions['import_orders'][] = ["import_static_html", GetMessage("OTHER_IMPORT_FEATURES"), '', ['statichtml', ""]];
$arAllOptions['import_orders'][] = ["import_dont_shipment", GetMessage("IMPORT_DONT_CREATE_SHIPMENT"), '', ['checkbox', "N"]];
$arAllOptions['import_orders'][] = ["import_dont_payment", GetMessage("IMPORT_DONT_CREATE_PAYMENT"), '', ['checkbox', "N"]];

if ($isCrmEnable) {
	$arAllOptions['import_orders'][] = ["import_order_contact", GetMessage("ORDER_API_IMPORT_CONTACT"), '', ['checkbox', "N"]];
	$arAllOptions['import_orders'][] = ['note' => GetMessage('ORDER_API_IMPORT_CONTACT_NOTE')];
}

$importSiteId = $request->get('import_site') ?: \Rbs\Moysklad\Config::getDefaultImportSiteId();

$arAllOptions['import_orders'][] = GetMessage('ORDER_NUMERING_FROM_MS');
if (
	isset($arPersonalTypesBySiteIdBx[$importSiteId]) &&
	Utils::is_count($arPersonalTypesBySiteIdBx[$importSiteId]) &&
	Utils::is_count($arOrderStrPropsByPersonal)
) {
	$arAllOptions['import_orders'][] = ["bx_order_ms_name_enabled", GetMessage("ORDER_API_MS_NAME_ENABLED"), '', ['checkbox', "N"]];
	$personalIdsBySiteId = array_keys($arPersonalTypesBySiteIdBx[$importSiteId]);
	foreach ($arOrderStrPropsByPersonal as $personalId => $propsStr) {
		if (in_array($personalId, $personalIdsBySiteId)) {
			$arAllOptions['import_orders'][] = ["bx_order_ms_name_{$personalId}", "[{$arPersonalTypesBx[$personalId]['LID']}] {$arPersonalTypesBx[$personalId]['NAME']}", '', ['selectbox', $propsStr]];
		}
	}
} else {
	$arAllOptions['import_orders'][] = ['note' => GetMessage('EMPTY_STR_FIELDS_FOR_ORDER_BX')];
}

$arAllOptions['import_orders'][] = GetMessage('ORDER_NUMERING_PUSH_MS');
$arAllOptions['import_orders'][] = ["ms_push_bx_order_id", GetMessage("ORDER_NUMERING_PUSH_MS_ENABLED"), '', ['checkbox', "N"]];
$arAllOptions['import_orders'][] = ["ms_push_bx_order_id_field_bx", GetMessage("ORDER_NUMERING_PUSH_MS_FIELD_BX"), '', ['selectbox', [
	'ID' => GetMessage("ORDER_NAME_BX_FIELD_ID"),
	'ACCOUNT_NUMBER' => GetMessage("ORDER_NAME_BX_FIELD_ACCOUNT_NUMBER")
]]];
if (Utils::is_count($selectPropsSkladStr)) {
	$arAllOptions['import_orders'][] = ["ms_push_bx_order_id_field_ms", GetMessage('ORDER_NUMERING_PUSH_MS_FIELD_MS'), '', ['selectbox', $selectPropsSkladStr]];
} else {
	$arAllOptions['import_orders'][] = ['note' => GetMessage('ORDER_NEED_STR_PROP')];
}

$arAllOptions['import_orders'][] = GetMessage('ORDER_PUSH_MS_ORDER_COMMENT');

$arAllOptions['import_orders'][] = ["ms_push_order_comment_id", GetMessage("ORDER_PUSH_MS_ORDER_COMMENT_ORDER_ID"), '', ['checkbox', "N"]];
$arAllOptions['import_orders'][] = ["ms_push_order_comment_id_field", GetMessage("ORDER_PUSH_MS_ORDER_COMMENT_ORDER_ID_FIELD"), '', ['selectbox', [
	'ID' => GetMessage("ORDER_NAME_BX_FIELD_ID"),
	'ACCOUNT_NUMBER' => GetMessage("ORDER_NAME_BX_FIELD_ACCOUNT_NUMBER")
]]];
$arAllOptions['import_orders'][] = ["ms_push_order_comment_id_template", GetMessage('ORDER_PUSH_MS_ORDER_COMMENT_ORDER_ID_TEMPLATE'), LangMsg::get('ORDER_COMMENT_TEMPLATE'), ['textarea', '']];

$arAllOptions['import_orders'][] = GetMessage('ORDER_NUMERING_PUSH_MS_ORDER_NAME');
$arAllOptions['import_orders'][] = ["ms_push_order_name", GetMessage("ORDER_NUMERING_PUSH_MS_ORDER_NAME_ENABLED"), '', ['checkbox', "N"]];
$arAllOptions['import_orders'][] = ["ms_push_order_name_field", GetMessage("ORDER_NUMERING_PUSH_MS_ORDER_NAME_FIELD"), '', ['selectbox', [
	'ID' => GetMessage("ORDER_NAME_BX_FIELD_ID"),
	'ACCOUNT_NUMBER' => GetMessage("ORDER_NAME_BX_FIELD_ACCOUNT_NUMBER")
]]];

OptionUtils::buildImportOnceButton($arAllOptions['import_orders'], 'import_once_customerorder');

if (!empty($arAllOptions['import_orders'])) {
	$aTabs[] = [
		"DIV" => "import_orders",
		"TAB" => GetMessage("ORDER_IMPORT_HEAD"),
		"ICON" => "order_settings",
		"TITLE" => GetMessage("ORDER_IMPORT_TITLE")
	];
}
