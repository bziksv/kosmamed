<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

use \Rbs\Moysklad\Utils;
use \Rbs\Moysklad\Config;

if(!function_exists('buildDeliveryTypeSelectList')) {
	function buildDeliveryTypeSelectList(&$arTabParams, $customEntityMetaHref, $deliveryList, $parentIds = [], $paramPrefix = 'delivery_sync_prop_')
	{
		$arDeliveryTypesEntityValues = [];
		if (!empty($customEntityMetaHref)) {
			$arDeliveryTypesEntityValues = CRbsMoyskladHelper::getAttributeEntityVariantForSelect($customEntityMetaHref);
		}

		if (Utils::is_count($arDeliveryTypesEntityValues)) {
			$arDeliveryTypesEntityValuesWithZero = array_merge(['N' => GetMessage('PROPS_DISABLE')], $arDeliveryTypesEntityValues);
			foreach ($parentIds as $parentId => $childrens) {
				if (isset($deliveryList[$parentId])) {
					$arTabParams[] = ["parent_delivery_{$deliveryList[$parentId]['ID']}",  '<b>' . GetMessage('NAME_PATTERN', ['#ID#' => $deliveryList[$parentId]['ID'], '#NAME#' => $deliveryList[$parentId]['NAME']]) . '</b>', '', ['statichtml', ""]];
				} else {
					$arTabParams[] = ["parent_delivery", "<b>" . GetMessage('NAME_PATTERN', ['#ID#' => 0, '#NAME#' => GetMessage('EMPTY_GROUP')]) . "</b>", '', ['statichtml', ""]];
				}
				foreach ($childrens as $childId) {
					if (isset($deliveryList[$childId]) && !isset($parentIds[$childId])) {
						$delivery = $deliveryList[$childId];
						$arTabParams[] = [$paramPrefix . $delivery['ID'],  GetMessage('NAME_PATTERN', ['#ID#' => $delivery['ID'], '#NAME#' => $delivery['NAME']]), '', ['selectbox', $arDeliveryTypesEntityValuesWithZero]];
					}
				}
			}
		} else {
			$arTabParams[] = ['note' => GetMessage('EMPTY_CUSTOM_ENTITY_PROPS_VARIANT_DELIVERY')];
		}
	}
}

$arTabParams[] = GetMessage('DELIVERY_TYPE_SYNC');
if (Utils::is_count($selectPropSkladCustomEntity) && Utils::is_count($parentIds)) {

	$arTabParams[] = ["delivery_sync_enabled", GetMessage("DELIVERY_SYNC_ENABLED"), '', ['checkbox', "N"]];
	$arTabParams[] = ["delivery_sync_prop", GetMessage("DELIVERY_SYNC_PROP"), '', ['selectbox', ['N' => GetMessage('NON_SYNC')] + $selectPropSkladCustomEntity]];

	$customEntityMetaHref = $request->get('delivery_sync_prop') ? : Config::getOption('delivery_sync_prop', '');
	if(!empty($customEntityMetaHref) && $customEntityMetaHref !== 'N') {
		buildDeliveryTypeSelectList($arTabParams, $customEntityMetaHref, $deliveryList, $parentIds);
	}
	
	$arTabParams[] = ['note' => GetMessage('DELIVERY_SYNC_NOTE')];
} else {
	$arTabParams[] = ['note' => GetMessage('EMPTY_CUSTOM_ENTITY_PROPS')];
}

$arTabParams[] = GetMessage('DELIVERY_PRICE_SETTINGS');

$arTabParams[] = ["vector_delivery_price", GetMessage("EXCHANGE_VECTOR"), 'FULL', ['selectbox', $vector]];
$arTabParams[] = ["dprice_sync_enabled", GetMessage("DELIVERY_PRICE_SYNC_ENABLED"), '', ['checkbox', "N"]];
$arTabParams[] = ['note' => GetMessage('DELIVERY_PRICE_SYNC_NOTE')];
$arTabParams[] = ["dprice_vat", GetMessage("DELIVERY_DELIVERY_SYNC_PRICE_VAT"), '', ['selectbox', $taxRatesSelect]];
$arTabParams[] = ['note' => GetMessage('DELIVERY_DELIVERY_SYNC_PRICE_VAT_NOTE')];


$arTabParams[] = GetMessage('DEMAND_EXCHANGE_HEAD');
$arTabParams[] = ["demand_exchange_type",  GetMessage('DEMAND_EXCHANGE_ENABLED_TYPE'), '', ['selectbox', [
	'N' => GetMessage('NON_SYNC'),
	'default' => GetMessage('default_DEMAND_TYPE'),
	'full' => GetMessage('full_DEMAND_TYPE'),
]]];

$demandTypeSelected = !empty($_REQUEST['demand_exchange_type']) ? $_REQUEST['demand_exchange_type'] : Config::getOption('demand_exchange_type');
if($demandTypeSelected !== 'N') {

	$arTabParams[] = ["demand_static_html", GetMessage("DEMAND_TYPE_HEAD", ['#TYPE#' => GetMessage("{$demandTypeSelected}_DEMAND_TYPE")]), '<hr>', ['statichtml', ""]];

	if($demandTypeSelected === 'default') {

		$arTabParams[] = ["demand_default_update", GetMessage("DELIVERY_DELIVERY_SYNC_UPD_ENABLED"), '', ['checkbox', "N"]];
		$arTabParams[] = ['note' => GetMessage('DELIVERY_DELIVERY_SYNC_NOTE')];
		$arTabParams[] = ["demand_sync_price", GetMessage("DELIVERY_DELIVERY_SYNC_PRICE"), '', ['checkbox', "N"]];
		$arTabParams[] = ['note' => GetMessage('DELIVERY_DELIVERY_SYNC_PRICE_NOTE')];

	} else if ($demandTypeSelected === 'full') {

		$arTabParams[] = ["vector_demand", GetMessage("EXCHANGE_VECTOR"), '', ['selectbox', $vector]];

		$arTabParams[] = ["demand_static_html", GetMessage("DEMAND_DATE_DEDUCTED_BLOCK"), '', ['statichtml', ""]];
		$arTabParams[] = ["demand_date_deducted_bx", GetMessage("DEMAND_DATE_DEDUCTED_FROM_BX"), 'Y', ['checkbox', "N"]];

		$arTabParams[] = ["demand_static_html", GetMessage("DEMAND_DELETE_BLOCK"), '', ['statichtml', ""]];
		if (empty($demandAttrs['boolean'])) {
			$arTabParams[] = ["demand_delete_attr", GetMessage("DELIVERY_DELETE_PROP"), '', ['selectbox', ['N' => GetMessage('NON_DELETE')]]];
			$arTabParams[] = ['note' => GetMessage('DEMAND_DELETE_EMPTY_BLOCK')];
		} else {
			$arTabParams[] = ["demand_delete_attr", GetMessage("DELIVERY_DELETE_PROP"), '', ['selectbox', ['N' => GetMessage('NON_DELETE')] + $demandAttrs['boolean']]];
		}

		$arTabParams[] = ["demand_static_html", GetMessage("SHIPMENT_DELETE_BLOCK"), '', ['statichtml', ""]];
		$arTabParams[] = ["demand_delete_bx_type", GetMessage("DELIVERY_DELETEBX_TYPE"), '', ['selectbox', [
			'N' => GetMessage('NON_DELETE'),
			'DELETE' => GetMessage('DELETE_DEMAND'),
			'ATTR' => GetMessage('CHECK_ATTR_DELETE'),
		]]];

		$arTabParams[] = ["demand_static_html", GetMessage("DEMAND_STATE_BLOCK"), '', ['statichtml', ""]];

		if(!Utils::is_count($demandStates) || !Utils::is_count($statusShipment)){
			if(!Utils::is_count($demandStates)) {
				$arTabParams[] = ['note' => GetMessage('DEMAND_STATE_BLOCK_EMPTY_MS_NOTE')];
			}
			if (!Utils::is_count($statusShipment)) {
				$arTabParams[] = ['note' => GetMessage('DEMAND_STATE_BLOCK_EMPTY_BX_NOTE')];
			}
		} else {
			foreach($statusShipment as $statusId => $statusName) {
				$arTabParams[] = ["demand_state_{$statusId}", $statusName, '', ['selectbox', ['N' => GetMessage('NON_SYNC')] + $demandStates]];
			}
		}

		$arTabParams[] = ["demand_static_html", GetMessage("DEMAND_ALLOW_DELIVERY_BLOCK"), '', ['statichtml', ""]];
		if(empty($demandAttrs['boolean'])) {
			$arTabParams[] = ['note' => GetMessage('DEMAND_ALLOW_DELIVERY_EMPTY_BLOCK')];
		} else {
			$arTabParams[] = ["demand_allow_attr", GetMessage("EXCHANGE_FIELD"), '', ['selectbox', ['N' => GetMessage('NON_SYNC')] + $demandAttrs['boolean']]];
		}

		$arTabParams[] = ["demand_static_html", GetMessage("DEMAND_PRICE_BLOCK"), '', ['statichtml', ""]];
		$arTabParams[] = ["demand_price_type", GetMessage("DEMAND_PRICE_TYPE"), '', ['selectbox', [
			'N' => GetMessage('NON_SYNC'),
			'ATTR' => GetMessage('DEMAND_PRICE_TYPE_ATTR'),
			'SERVICE' => GetMessage('DEMAND_PRICE_TYPE_SERVICE'),
		]]];

		$demandPriceTypeSelected = !empty($_REQUEST['demand_price_type']) ? $_REQUEST['demand_price_type'] : Config::getOption('demand_price_type');
		if ($demandPriceTypeSelected !== 'N') {
			if($demandPriceTypeSelected === 'ATTR') {
				if (empty($demandAttrs['double'])) {
					$arTabParams[] = ['note' => GetMessage('DEMAND_PRICE_ATTR_DELIVERY_EMPTY_NOTE')];
				} else {
					$arTabParams[] = ["demand_price_attr", GetMessage("EXCHANGE_FIELD"), '', ['selectbox', ['N' => GetMessage('NON_SYNC')] + $demandAttrs['double']]];
				}
			}
		}

		$arTabParams[] = ["demand_static_html", GetMessage("DEMAND_TRACK_DELIVERY_BLOCK"), '', ['statichtml', ""]];
		if (empty($demandAttrs['string'])) {
			$arTabParams[] = ['note' => GetMessage('DEMAND_TRACK_DELIVERY_EMPTY_BLOCK')];
		} else {
			$arTabParams[] = ["demand_track_attr", GetMessage("EXCHANGE_FIELD"), '', ['selectbox', ['N' => GetMessage('NON_SYNC')] + $demandAttrs['string']]];
		}

		$arTabParams[] = ["demand_static_html", GetMessage("DEMAND_STORE_DELIVERY_BLOCK"), '', ['statichtml', ""]];
		$arTabParams[] = ["demand_store_ext_code", GetMessage("DELIVERY_STORE_EXT_CODE"), '', ['checkbox', "N"]];
		$arTabParams[] = ['note' => GetMessage('DELIVERY_STORE_EXT_CODE_NOTE')];

		$arTabParams[] = ["demand_static_html", GetMessage("DEMAND_DELIVERY_TYPE_BLOCK"), '', ['statichtml', ""]];
		if (empty($demandAttrs['customentity'])) {
			$arTabParams[] = ['note' => GetMessage('DEMAND_DELIVERY_TYPE_EMPTY_NOTE')];
		} else {
			$arTabParams[] = ["demand_delivery_type_attr", GetMessage("EXCHANGE_FIELD"), '', ['selectbox', ['N' => GetMessage('NON_SYNC')] + $demandAttrs['customentity']]];
		}
		$demandDeliveryTypeSelected = !empty($_REQUEST['demand_delivery_type_attr']) ? $_REQUEST['demand_delivery_type_attr'] : Config::getOption('demand_delivery_type_attr');
		if ($demandDeliveryTypeSelected !== 'N' && Utils::is_count($parentIds) && isset($demandAttrsFields[$demandDeliveryTypeSelected]) && !empty($demandAttrsFields[$demandDeliveryTypeSelected]->customEntityMeta->href)) {
			$arTabParams[] = ["demand_static_html", "<hr>", '', ['statichtml', ""]];
			$arOptionsHardSet['demand_delivery_type_attr_entity_id'] = array_pop(explode('/', $demandAttrsFields[$demandDeliveryTypeSelected]->customEntityMeta->href));
			buildDeliveryTypeSelectList($arTabParams, $demandAttrsFields[$demandDeliveryTypeSelected]->customEntityMeta->href, $deliveryList, $parentIds, 'demand_delivery_type_var_');
			$arTabParams[] = ["demand_static_html", "<hr>", '', ['statichtml', ""]];
		}
		
	}

	$arTabParams[] = ["demand_static_html", GetMessage("DEMAND_ALL_TYPE_HEAD"), '<hr>', ['statichtml', ""]];

	$arTabParams[] = ["demand_sync_id", GetMessage("DELIVERY_DELIVERY_SYNC_ID"), '', ['selectbox', [
		'N' => GetMessage("NON_SET"),
		'ID' => GetMessage("SHIPMENT_NAME_BX_FIELD_ID"),
		'ACCOUNT_NUMBER' => GetMessage("SHIPMENT_NAME_BX_FIELD_ACC")
	]]];

	$arTabParams[] = ["demand_sync_id_comment", GetMessage("DELIVERY_DELIVERY_SYNC_ID_COMMENT"), '', ['selectbox', [
		'N' => GetMessage("NON_GIVE"),
		'ID' => GetMessage("SHIPMENT_NAME_BX_FIELD_ID"),
		'ACCOUNT_NUMBER' => GetMessage("SHIPMENT_NAME_BX_FIELD_ACC")
	]]];

	$vatTypes = [
		'from_order' => GetMessage('VAT_TYPE_from_order'),
		'hard_set' => GetMessage('VAT_TYPE_hard_set'),
		'hard_unset' => GetMessage('VAT_TYPE_hard_unset'),
	];
	$arTabParams[] = ["demand_sync_vat",  GetMessage('DELIVERY_DELIVERY_SYNC_VAT'), '', ['selectbox', $vatTypes]];
	$arTabParams[] = ["demand_sync_vat_inc",  GetMessage('DELIVERY_DELIVERY_SYNC_VAT_INC'), '', ['selectbox', $vatTypes]];

	$arTabParams[] = ["demand_sync_store_default",  GetMessage('DELIVERY_DELIVERY_SYNC_STORE_DEFAULT'), '', ['selectbox', $storeMsOptions]];
}

$arTabParams[] = GetMessage('DEMAND_OTHER_HEAD');

$arTabParams[] = ["delivery_name_export_enabled", GetMessage("DELIVERY_NAME_EXPORT_OPTION_ENABLED"), '', ['checkbox', "N"]];
if(Utils::is_count($selectPropsSkladStr)) {
	$arTabParams[] = ["delivery_name_export", GetMessage("DELIVERY_NAME_EXPORT_OPTION"), '', ['selectbox', ['N' => GetMessage('NON_SYNC')] + $selectPropsSkladStr]];
	$arTabParams[] = ['note' => GetMessage('ORDER_DELIVERY_NAME_EXPORT_NOTE')];
} else {
	$arTabParams[] = ['note' => GetMessage('EMPTY_STR_TEXT_PROPS')];
}

$arTabParams[] = ["delivery_track_enabled", GetMessage("DELIVERY_TRACK_OPTION_ENABLED"), '', ['checkbox', "N"]];
if (Utils::is_count($selectPropsSkladStr)) {
	$arTabParams[] = ["delivery_track", GetMessage("DELIVERY_TRACK_OPTION"), '', ['selectbox', ['N' => GetMessage('NON_SYNC')] + $selectPropsSkladStr]];
	$arTabParams[] = ['note' => GetMessage('ORDER_DELIVERY_TRACK_NOTE')];
} else {
	$arTabParams[] = ['note' => GetMessage('EMPTY_STR_TEXT_PROPS')];
}
 
$arTabParams[] = GetMessage('DELIVERY_STORE_SETTINGS');

$arTabParams[] = ["delivery_store_enabled", GetMessage("DELIVERY_STORE_OPTION_ENABLED"), '', ['checkbox', "N"]];
$arTabParams[] = ["delivery_store_reverse", GetMessage("DELIVERY_STORE_REVERSE_OPTION_ENABLED"), '', ['checkbox', "N"]];
$arTabParams[] = ['note' => GetMessage('ORDER_DELIVERY_STORE_NOTE')];

if (!empty($arTabParams)) {
	$arAllOptions['delivery'] = $arTabParams;
	$aTabs[] = [
		"DIV" => "delivery",
		"TAB" => GetMessage("DELIVERY_HEAD"),
		"ICON" => "order_settings",
		"TITLE" => GetMessage("DELIVERY_HEAD")
	];
	unset($arTabParams);
}