<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

use \Rbs\Moysklad\Utils;
use \Rbs\Moysklad\Config;

$arLocalParams = [];

$arLocalParams[] = GetMessage('ORDER_STANDART_FIELDS');
if (Utils::is_count($selectOrgMs)) {

	$arLocalParams[] = ["order_organization", GetMessage('ORDER_ORG'), '', ['selectbox', $selectOrgMs]];

	$selectedOrgId = $request->get('order_organization') ? : Config::getOption('order_organization', '');
	if (isset($selectOrgAccMs[$selectedOrgId]) && Utils::is_count($selectOrgAccMs[$selectedOrgId])) {
		$arLocalParams[] = ["order_organization_acc", GetMessage('ORDER_ORG_ACC'), '', ['selectbox', $selectOrgAccMs[$selectedOrgId]]];
	}
	unset($selectedOrgId);

} else {
	$arLocalParams[] = ['note' => GetMessage('ORDER_ORG_REQUIRED_NOTE')];
}

$arLocalParams[] = ["order_group", GetMessage('ORDER_GROUP'), '', ['selectbox', ['N' => GetMessage('NON_SYNC')] + $selectGroupsMs]];
$arLocalParams[] = ["order_employee", GetMessage('ORDER_EMPLOYEE'), '', ['selectbox', ['N' => GetMessage('NON_SYNC')] + $selectEmployeeMs]];
$arLocalParams[] = ["order_sale_channel", GetMessage('ORDER_SALES_CHANNEL'), '', ['selectbox', ['N' => GetMessage('NON_SYNC')] + $selectSalesChannelMs]];
$arLocalParams[] = ["order_proj", GetMessage('ORDER_PROJECT'), '', ['selectbox', ['N' => GetMessage('NON_SYNC')] + $projects]];
$arLocalParams[] = ["order_store", GetMessage('ORDER_STORE'), '', ['selectbox', ['N' => GetMessage('NON_SYNC')] + $storeMsOptions]];
$arLocalParams[] = ["is_order_shared", GetMessage("ORDER_SHARED"), '', ['checkbox', "N"]];
$arLocalParams[] = ["is_order_reserved", GetMessage("ORDER_RESERVED_CREATE"), '', ['checkbox', "N"]];
$arLocalParams[] = ["is_order_vatenabled", GetMessage("ORDER_VAT_ENABLED"), '', ['checkbox', "N"]];
$arLocalParams[] = ["is_order_vatincluded", GetMessage("ORDER_VAT_INCLUDED"), '', ['checkbox', "N"]];
$arLocalParams[] = ["order_time_set_bx", GetMessage("ORDER_TIME_SET_BX"), '', ['checkbox', "N"]];
$arLocalParams[] = ["order_unset_applicable", GetMessage("ORDER_UNSET_APPLICABLE"), '', ['checkbox', "N"]];

$arLocalParams[] =  GetMessage('ORDER_NUMERING_AND_SEARCH');

$arLocalParams[] = ["order_sync_id", GetMessage("ORDER_SYNC_ID"), '', ['checkbox', "N"]];
if(Utils::is_count($selectPropsSkladStr + $selectPropsSkladNumber)){
	$arLocalParams[] = ["order_sync_prop_id", GetMessage('ORDER_SYNC_PROP_ID'), '', ['selectbox', $selectPropsSkladStr + $selectPropsSkladNumber]];
} else {
	$arLocalParams[] = ['note' => GetMessage('ORDER_NEED_STR_OR_NUMBER_PROP')];
}

$arLocalParams[] = ['<hr>', '<hr>', '', ['statichtml']];

$arLocalParams[] = ["order_sync_account_num", GetMessage("ORDER_SYNC_ACCOUNT_NUM"), '', ['checkbox', "N"]];
if(Utils::is_count($selectPropsSkladStr)){
	$arLocalParams[] = ["order_sync_prop_account_num", GetMessage('ORDER_SYNC_PROP_ACCOUNT_NUM'), '', ['selectbox', $selectPropsSkladStr]];
} else {
	$arLocalParams[] = ['note' => GetMessage('ORDER_NEED_STR_PROP')];
}

$arLocalParams[] = ['<hr>', '<hr>', '', ['statichtml']];

$arLocalParams[] = ["order_name_bx", GetMessage("ORDER_NAME_BX"), '', ['checkbox', "N"]];
$arLocalParams[] = ["order_name_bx_field", GetMessage('ORDER_NAME_BX_FIELD'), '', ['selectbox', [
	'ID' => GetMessage("ORDER_NAME_BX_FIELD_ID"),
	'ACCOUNT_NUMBER' => GetMessage("ORDER_NAME_BX_FIELD_ACCOUNT_NUMBER")
]]];

$arLocalParams[] = ['<hr>', '<hr>', '', ['statichtml']];

$arLocalParams[] = ["order_ext_ms", GetMessage("ORDER_EXT_MS"), '', ['checkbox', "N"]];
$arLocalParams[] = ["order_ext_ms_field", GetMessage('ORDER_EXT_MS_FIELD'), '', ['selectbox', [
	'XML_ID' => GetMessage("ORDER_NAME_BX_FIELD_XML_ID"),
	'ID' => GetMessage("ORDER_NAME_BX_FIELD_ID"),
	'ACCOUNT_NUMBER' => GetMessage("ORDER_NAME_BX_FIELD_ACCOUNT_NUMBER")
]]];

$arLocalParams[] = ['<hr>', '<hr>', '', ['statichtml']];

$arLocalParams[] = ["order_find_by_ext_code", GetMessage("ORDER_FIND_BY_EXT_CODE_ENABLED"), 'Y', ['checkbox', "N", 'disabled']];
$arOptionsHardSet["order_find_by_ext_code"] = 'Y';

$arLocalParams[] = GetMessage('ORDER_EXCEPTIONS_SETTINGS');

$arLocalParams[] = ["order_start_exchange", GetMessage("ORDER_START_EXCHANGE"), '', ['text', "30"]];

if (Utils::is_count($selectPropsSkladBool)) {
	$selectPropsSkladBoolWithZero = array_merge(['N' => GetMessage('PROPS_DISABLE')], $selectPropsSkladBool);
	$arLocalParams[] = ["order_disable_prop", GetMessage('PROPS_DISABLE_PROP'), '', ['selectbox', $selectPropsSkladBoolWithZero]];
	$arLocalParams[] = ['note' => GetMessage('PROPS_DISABLE_NOTE')];
}

/**FILTER */
$arLocalParams[] = GetMessage('ORDER_FILTER_BX_SETTINGS');
$arLocalParams[] = ["order_filter_bx", GetMessage("ORDER_FILTER_BX"), '', ['checkbox', "N"]];
$arLocalParams[] = ['note' => GetMessage('ORDER_FILTER_BX_NOTE')];
/**FILTER */

$rsSites = \Bitrix\Main\SiteTable::getList();
$siteArray = [];
while ($arSite = $rsSites->fetch()) {
	$siteArray[$arSite['LID']] = "[{$arSite['LID']}] {$arSite['NAME']}";
}
$arLocalParams[] = ["f_site_id", GetMessage('ORDER_FILTER_SITE_ID'), '', ['selectbox', ['NON_FILTER' => GetMessage('NON_FILTER')] + $siteArray]];

$selectStateSklad = [];
if (Utils::array_exists($metaOrder, 'states')) {
	foreach ($metaOrder->states as  $state) {
		$selectStateSklad[$state->id] = $state->name;
	}
}

$arLocalParams[] = GetMessage('ORDER_SALES_CHANNEL_SETTINGS');
if(Utils::is_count($selectSalesChannelMs)) {
	$arLocalParams[] = ["sales_channel_enabled", GetMessage("SALES_CHANNEL_EXCHANGE"), '', ['checkbox', "N"]];
	$arLocalParams[] = ["vector_saleschannel", GetMessage("EXCHANGE_VECTOR"), 'FULL', ['selectbox', $vector]];
	if(Utils::is_count($arPersonalTypesBx)) {
		$arLocalParams[] = ["status_block",  "<hr>", '', ['statichtml', ""]];
		foreach($arPersonalTypesBx as $personTypeId => $personalTypeParams) {
			if(isset($arOrderEnumPropsByPersonal[$personTypeId]) && Utils::is_count($arOrderEnumPropsByPersonal[$personTypeId])) {
				$arLocalParams[] = ["sales_channel_for_{$personTypeId}", GetMessage('SALES_CHANNEL_BX_PROP', ['#PERSON_TYPE#' => "[{$personalTypeParams['ID']}] {$personalTypeParams['NAME']}"]), '', ['selectbox', $arOrderEnumPropsByPersonal[$personTypeId]]];
			} else {
				$arLocalParams[] = ['note' => GetMessage('WARNING_EMPTY_PERSON_TYPES_PROPS_ENUM', [
					'#PERSON_TYPE#' => "[{$personalTypeParams['ID']}] {$personalTypeParams['NAME']}"
				])];
			}
		}
		$arLocalParams[] = ["status_block",  "<hr>", '', ['statichtml', ""]];
	} else {
		$arLocalParams[] = ['note' => GetMessage('WARNING_EMPTY_PERSON_TYPES')];
	}
	
} else {
	$arLocalParams[] = ['note' => GetMessage('WARNING_EMPTY_SALES_CHANNELS')];
}


$arLocalParams[] = GetMessage('ORDER_RESPONSIBLE_SETTINGS');
$arLocalParams[] = ["order_r_sync", GetMessage("ORDER_RESPONSIBLE_SYNC"), '', ['checkbox', "N"]];
$arLocalParams[] = ["order_rg_sync", GetMessage("ORDER_RESPONSIBLE_GROUP_SYNC"), '', ['checkbox', "N"]];
$arLocalParams[] = ['note' => GetMessage('ORDER_RESPONSIBLE_NOTE')];
$arLocalParams[] = ["order_r_prop", GetMessage("ORDER_RESPONSIBLE_PROP"), '', ['checkbox', "N"]];
if(Utils::is_count($selectEmployeProp)){
	$arLocalParams[] = ["order_r_prop_id", GetMessage("ORDER_RESPONSIBLE_PROP_ID"), '', ['selectbox', $selectEmployeProp]];
	$arLocalParams[] = ['note' => GetMessage('ORDER_RESPONSIBLE_PROP_NOTE')];
} else {
	$arLocalParams[] = ['note' => GetMessage('ORDER_NEED_EMPLOYEE_PROP')];
}

$arLocalParams[] = GetMessage('ORDER_COMMENT_SETTINGS');
$arLocalParams[] = ["order_comment_enabled", GetMessage('ORDER_COMMENT_ENABLED'), '', ['checkbox', "N"]];
$arLocalParams[] = ['note' => GetMessage('ORDER_COMMENT_NOTE')];

$arLocalParams[] = ["order_comment_user_enabled", GetMessage('ORDER_COMMENT_USER_ENABLED'), '', ['checkbox', "N"]];
$arLocalParams[] = ["order_comment_user_prop", GetMessage('ORDER_COMMENT_USER_PROP'), '', ['selectbox', ['ADDR_comment' => GetMessage('MS_FIELD_ADDR_COMMENT')] + $selectPropsSkladStr]];
$arLocalParams[] = ['note' => GetMessage('ORDER_COMMENT_USER_NOTE')];

if (!empty($arLocalParams)) {
	$arAllOptions['orders'] = $arLocalParams;
	$aTabs[] = [
		"DIV" => "export_orders",
		"TAB" => GetMessage("ORDER_SETTINGS_HEAD"),
		"ICON" => "order_settings",
		"TITLE" => GetMessage("ORDER_SETTINGS_TITLE")
	];
	unset($arLocalParams);
}