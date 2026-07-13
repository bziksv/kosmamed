<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

$arAllOptions['basket'][] = GetMessage('BASKET_CREATE_SETTINGS');
$arAllOptions['basket'][] = ['note' => GetMessage('BASKET_CREATE_SETTINGS_NOTE')];
$arAllOptions['basket'][] = ["basket_create_items", GetMessage('BASKET_CREATE_ITEMS'), '', ['checkbox', "N"]];

$arAllOptions['basket'][] = ["basket_create_group", GetMessage('GROUP_ID'), '', ['selectbox', ['N' => GetMessage('ROOT_SECTION')] + $selectFolders]];
$arAllOptions['basket'][] = ["basket_create_price", GetMessage('PRODUCT_CREATE_PRICE'), '', ['selectbox', $priceTypesSync]];

$arAllOptions['basket'][] = ["basket_create_id_attr", GetMessage('PRODUCT_CREATE_ID_ATTR'), '', ['selectbox', 
   ['N' => GetMessage('PRODUCT_CREATE_DESCRIPTION_N')] + $selectPropsProductStr + $selectPropsProductNumber
]];

$arAllOptions['basket'][] = ["basket_create_code", GetMessage('PRODUCT_CREATE_CODE_FIELD'), '', ['text', '']];
$arAllOptions['basket'][] = ["basket_create_article", GetMessage('PRODUCT_CREATE_ARTICLE_FIELD'), '', ['text', '']];
$arAllOptions['basket'][] = ["basket_create_prev_pic", GetMessage('PRODUCT_CREATE_PREV_PIC'), '', ['checkbox', "N"]];
$arAllOptions['basket'][] = ["basket_create_detail_pic", GetMessage('PRODUCT_CREATE_DETAIL_PIC'), '', ['checkbox', "N"]];
$arAllOptions['basket'][] = ["basket_create_description", GetMessage('PRODUCT_CREATE_DESCRIPTION'), '', ['selectbox', [
	'N' => GetMessage('PRODUCT_CREATE_DESCRIPTION_N'),
	'PREVIEW_TEXT' => GetMessage('PRODUCT_CREATE_DESCRIPTION_PREVIEW'),
	'DETAIL_TEXT' => GetMessage('PRODUCT_CREATE_DESCRIPTION_DETAIL'),
]]];

$arAllOptions['basket'][] = GetMessage('BASKET_SETTINGS');
$arAllOptions['basket'][] = ["basket_enabled", GetMessage('BASKET_ENABLED'), '', ['checkbox', "N"]];
$arAllOptions['basket'][] = ["basket_enabled_bx", GetMessage('BASKET_ENABLED_BX'), '', ['checkbox', "N"]];
$arAllOptions['basket'][] = ['note' => GetMessage('BASKET_NOTE')];
$arAllOptions['basket'][] = ["basket_doubles", GetMessage('BASKET_DOUBLES'), '', ['checkbox', "N"]];
$arAllOptions['basket'][] = ['note' => GetMessage('BASKET_DOUBLES_NOTE')];
$arAllOptions['basket'][] = ["basket_all_add", GetMessage('BASKET_ALL_ADD'), '', ['checkbox', "N"]];
$arAllOptions['basket'][] = ['note' => GetMessage('BASKET_ALL_ADD_NOTE')];
$arAllOptions['basket'][] = ["basket_archived", GetMessage('BASKET_ARCHIVED'), '', ['checkbox', "N"]];
$arAllOptions['basket'][] = ['note' => GetMessage('BASKET_ARCHIVED_NOTE')];
$arAllOptions['basket'][] = ["basket_modif_enabled", GetMessage('BASKET_MODIF_ENABLED'), '', ['checkbox', "N"]];
$arAllOptions['basket'][] = ['note' => GetMessage('BASKET_MODIF_NOTE')];
$arAllOptions['basket'][] = ["basket_reserved_edit", GetMessage('BASKET_RESERVED_EDIT'), '', ['checkbox', "N"]];
$arAllOptions['basket'][] = ['note' => GetMessage('BASKET_RESERVED_EDIT_NOTE')];
$arAllOptions['basket'][] = ["basket_vat", GetMessage('BASKET_VAT'), '', ['checkbox', "N"]];
$arAllOptions['basket'][] = ['note' => GetMessage('BASKET_VAT_NOTE')];
$arAllOptions['basket'][] = ["basket_bundle_enabled", GetMessage('BASKET_BUNDLE_ENABLED'), '', ['checkbox', "N"]];
$arAllOptions['basket'][] = ['note' => GetMessage('BASKET_BUNDLE_NOTE')];
$arAllOptions['basket'][] = ["basket_bundle_recalc", GetMessage('BASKET_BUNDLE_RECALC'), '', ['checkbox', "N"]];
$arAllOptions['basket'][] = ['note' => GetMessage('BASKET_BUNDLE_RECALC_NOTE')];
$arAllOptions['basket'][] = ["basket_shipment_refresh_enabled", GetMessage('BASKET_SHIPMENT_REFRESH_ENABLED'), '', ['checkbox', "N"]];
$arAllOptions['basket'][] = ['note' => GetMessage('BASKET_SHIPMENT_REFRESH_NOTE')];
$arAllOptions['basket'][] = ["basket_recalc_enabled", GetMessage('BASKET_RECALC_ENABLED'), '', ['checkbox', "N"]];
$arAllOptions['basket'][] = ['note' => GetMessage('BASKET_RECALC_NOTE')];
$arAllOptions['basket'][] = ["basket_hard_add_item", GetMessage('BASKET_HARD_ADD_ITEM'), '', ['checkbox', "N"]];
$arAllOptions['basket'][] = ['note' => GetMessage('BASKET_HARD_ADD_ITEM_NOTE')];
$arAllOptions['basket'][] = ["basket_currency", GetMessage('BASKET_CURRENCY'), '', ['checkbox', "N"]];
$arAllOptions['basket'][] = ['note' => GetMessage('BASKET_CURRENCY_NOTE')];
$arAllOptions['basket'][] = ["basket_ext_code_source", GetMessage('BASKET_EXT_CODE_SOURCE'), '', ['checkbox', "N"]];
$arAllOptions['basket'][] = ['note' => GetMessage('BASKET_EXT_CODE_SOURCE_NOTE')];
$arAllOptions['basket'][] = ["basket_provider_off", GetMessage('BASKET_PROVIDER_OFF'), '', ['checkbox', "N"]];
$arAllOptions['basket'][] = ['note' => GetMessage('BASKET_PROVIDER_OFF_NOTE')];
$arAllOptions['basket'][] = ["basket_provider_class", GetMessage('BASKET_PROVIDER_CLASS'), '', ['selectbox', [
	'OLD' => 'CCatalogProductProvider',
	'NEW' => '\Bitrix\Catalog\Product\CatalogProvider'
]]];
$arAllOptions['basket'][] = ['note' => GetMessage('BASKET_PROVIDER_CLASS_NOTE')];

if (!empty($arAllOptions['basket'])) {
	$aTabs[] = [
		"DIV" => "basket",
		"TAB" => GetMessage("BASKET_HEAD"),
		"ICON" => "order_settings",
		"TITLE" => GetMessage("BASKET_HEAD")
	];
}
