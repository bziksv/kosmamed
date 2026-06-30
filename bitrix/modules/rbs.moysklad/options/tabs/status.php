<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

$arLocalParams = [];

$arLocalParams[] = ["order_state_func_enabled", GetMessage("ORDER_STATUS_ENABLED"), '', ['checkbox', "N"]];
$arLocalParams[] = ["vector_states", GetMessage("EXCHANGE_VECTOR"), 'FULL', ['selectbox', $vector]];

if (!empty($selectStateSklad) && !empty($statusSite)) {

	$arLocalParams[] = GetMessage('ORDER_STATUS_MATCH');

	foreach ($statusSite as $statId => $statName) {
		$arLocalParams[] = ["order_state_" . $statId, $statName, '', ['selectbox', ['N' => GetMessage('NON_SYNC')] + $selectStateSklad]];
	}
 
	$arLocalParams[] = GetMessage('STATUS_EXPORT_TITLE');

	$arLocalParams[] = ["status_export_type", GetMessage('STATUS_EXPORT_TYPE'), 'NON_SYNC', ['selectbox', [
		'NON_SYNC' => GetMessage('NON_SYNC_WITH_STATUS'),
		'UPPER' => GetMessage('STATUS_EXPORT_TYPE_UPPER'),
		'EQUAL' => GetMessage('STATUS_EXPORT_TYPE_EQUAL'),
	]]];
	$arLocalParams[] = ["status_export", GetMessage('STATUS_EXPORT_VALUE'), '', ['selectbox', $statusSite]];
	$arLocalParams[] = ['note' => GetMessage('STATUS_EXPORT_NOTE')];

	$arLocalParams[] = GetMessage('STATUS_RESERVE_TITLE');

	$arLocalParams[] = ["status_reserve_enable", GetMessage('STATUS_CANCEL_RESERVE'), '', ['checkbox', 'N']];
	$arLocalParams[] = ["status_reserve", GetMessage('STATUS_CANCEL_RESERVE_VALUE'), '', ['multiselectbox', $statusSite]];
	$arLocalParams[] = ['note' => GetMessage('STATUS_CANCEL_RESERVE_NOTE')];

	$arLocalParams[] = GetMessage('STATUS_IGNORE_TITLE');

	$arLocalParams[] = ["states_bx_ms_ignore_enabled", GetMessage('IGNORE_STATES_ENABLED'), '', ['checkbox', 'N']];
	$arLocalParams[] = ["ignore_bx_ms_state_list", GetMessage('IGNORE_STATES_LIST'), '', ['multiselectbox', $selectStateSklad]];
	$arLocalParams[] = ['note' => GetMessage('IGNORE_STATES_NOTE')];

	$arLocalParams[] = ["basket_bx_ms_ignore_enabled", GetMessage('IGNORE_BASKET_ENABLED'), '', ['checkbox', 'N']];
	$arLocalParams[] = ["ignore_bx_ms_basket_list", GetMessage('IGNORE_BASKET_LIST'), '', ['multiselectbox', $selectStateSklad]];
	$arLocalParams[] = ['note' => GetMessage('IGNORE_BASKET_NOTE')];

	//CANCEL
	$arLocalParams[] = GetMessage('ORDER_CANCEL_SETTINGS');

	$arLocalParams[] = ["order_cancel_enabled", GetMessage('ORDER_CANCEL_ENABLED'), '', ['checkbox', "N"]];
	$arLocalParams[] = ["order_cancel", GetMessage('ORDER_CANCEL_OPTION'), '', ['selectbox', $selectStateSklad]];
	$arLocalParams[] = ['note' => GetMessage('ORDER_CANCEL_NOTE')];

	//CANCEL RESERVE
	$arLocalParams[] = ["order_cancel_reserve", GetMessage('ORDER_CANCEL_RESERVE_OPTION'), '', ['checkbox', 'N']];
	$arLocalParams[] = ['note' => GetMessage('ORDER_CANCEL_RESERVE_NOTE')];

} else {

	$arLocalParams[] = ['note' => GetMessage('WARNING_EMPTY_STATES')];

}


if (!empty($arLocalParams)) {
	$arAllOptions['status'] = $arLocalParams;
	$aTabs[] = [
		"DIV" => "status",
		"TAB" => GetMessage("ORDER_STATUS_TAB"),
		"ICON" => "order_settings",
		"TITLE" => GetMessage("ORDER_STATUS_TAB")
	];
	unset($arLocalParams);
}