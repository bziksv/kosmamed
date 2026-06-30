<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use \Rbs\MoyskladStocks\Internals\OptionUtils;

$arAllOptions['discount'][] = GetMessage("MAIN_SETTINGS_DOC", ['#LINK#' => '/rbs-moyskladstocks/settings/discount']);

$arAllOptions['discount'][] = GetMessage('DISCOUNT_TYPE_HEAD', ['#LINK#' => '/rbs-moyskladstocks/settings/discount/import']);
$arAllOptions['discount'][] = ["ds_sync", GetMessage('DISCOUNT_SYNC'), '', ['checkbox', "N", $paramsCheckBox]];
$arAllOptions['discount'][] = ["ds_module", GetMessage('DISCOUNT_MODULE'), 'CATALOG', ['selectbox',  [
    //'catalog' => GetMessage('DISCOUNT_MODULE_CATALOG'),
    'SALE' => GetMessage('DISCOUNT_MODULE_SALE')
]]];

if(count($selectSites) > 0){
    $arAllOptions['discount'][] = ["ds_site_id", GetMessage('DISCOUNT_SITE_ID'), '', ['selectbox',  $selectSites]];
}
if(count($currencyBxSelect) > 0){
    $arAllOptions['discount'][] = ["ds_currency", GetMessage('DISCOUNT_CURRENCY'), '', ['selectbox',  $currencyBxSelect]];
}
if(count($selectGroups) > 0){
    $arAllOptions['discount'][] = ["ds_user_groups", GetMessage('DISCOUNT_USER_GROUPS'), '', ['multiselectbox',  $selectGroups]];
}
if(count($selectPrices) > 0){
   $arAllOptions['discount'][] = ["ds_price_type", GetMessage('DISCOUNT_PRICE_TYPE'), '', ['multiselectbox',  $selectPrices]];
}

$arAllOptions['discount'][] = ["ds_last_level", GetMessage('DISCOUNT_LAST_LEVEL'), '', ['checkbox', "N", $paramsCheckBox]];
$arAllOptions['discount'][] = ["ds_last", GetMessage('DISCOUNT_LAST'), '', ['checkbox', "N", $paramsCheckBox]];
$arAllOptions['discount'][] = ["ds_priority", GetMessage('DISCOUNT_SORT'), '1', ['text', '']];
$arAllOptions['discount'][] = ["ds_sort", GetMessage('DISCOUNT_PRIORITY'), '100', ['text', '']];

if(count($multiSelectCatalog) > 0){
    $arAllOptions['discount'][] = ['statichtml', GetMessage('DISCOUNT_ITEM_SEARCH_HEAD')];
    $arAllOptions['discount'][] = ["ds_iblock_id", GetMessage('IBLOCK_ID'), '', ['multiselectbox',  $multiSelectCatalog]];
}

//agent
OptionUtils::buildAgentOptionArray($arAllOptions['discount'], 'discount', $paramsCheckBox, true, ['full_once', 'full_time', 'updated', 'last_update']);
if ($isSaveHit) {
    OptionUtils::saveAgentAction('discount', 'import_discount');
}

OptionUtils::buildImportOnceButton($arAllOptions['discount'], 'import_once_discount');

if(!empty($arAllOptions['discount'])){
    $aTabs[] = [
        "DIV" => "discount",
        "TAB" => GetMessage('DISCOUNT_HEAD'),
        "ICON" => "order_settings",
        "TITLE" => GetMessage('DISCOUNT_HEAD')
    ];
} 