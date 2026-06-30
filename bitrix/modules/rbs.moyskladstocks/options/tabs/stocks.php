<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
   die();
}

use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\Internals\OptionUtils;

$arAllOptions['stores'][] = GetMessage("MAIN_SETTINGS_DOC", ['#LINK#' => '/rbs-moyskladstocks/settings/stocks']);
$arAllOptions['stores'][] = GetMessage('STORES_HEAD_ASSOC' , ['#LINK#' => '/rbs-moyskladstocks/settings/stocks/conformity']);

$storesBx = \Bitrix\Catalog\StoreTable::getList([
   'filter' => [
      'ACTIVE' => 'Y'
   ],
   'cache' => [
      'ttl' => 0
   ]
])->fetchAll();

$storesMs = ['N' => GetMessage('NON_SYNC'), 'all' => GetMessage('ALL_STOCKS_MS_QTY')];

$storeBxSelect = ['ALL' => GetMessage('ALL_QTY'), 'ALL_MS' => GetMessage('ALL_MS_QTY'), 'ALL_BX' => GetMessage('ALL_BX_QTY')];

if(Utils::is_count($storesBx)){

   $storesMsGet = \Rbs\MoyskladStocks\ApiNew::get('/entity/store', [], 60);
   if(Utils::is_success($storesMsGet) && Utils::array_exists($storesMsGet)){
      foreach ($storesMsGet->rows as $store) {
         $storesMs[$store->id] = $store->name;
      }
   }
   
   foreach($storesBx as $storeBx){
      $arAllOptions['stores'][] = ["store_" . $storeBx['ID'], "[{$storeBx['ID']}] {$storeBx['TITLE']}", '', ['selectbox', $storesMs]];
      $storeBxSelect[$storeBx['ID']] = "[{$storeBx['ID']}] {$storeBx['TITLE']}";
   }

   $arAllOptions['stores'][] = ["store_parents", GetMessage('STORE_PARENTS'), '', ['checkbox', "N", $paramsCheckBox]];

} else {

   $arAllOptions['stores'][] = ['note' => GetMessage("STORES_NEED_CREATE_NOTE")];

}

//filter
$arAllOptions['stores'][] = GetMessage('STORES_STOCK_FILTER', ['#LINK#' => '/rbs-moyskladstocks/settings/stocks/filter']);
$arAllOptions['stores'][] = ["stock_filter_enable", GetMessage('STORES_STOCK_FILTER_ENABLE'), '', ['checkbox', "N", $paramsCheckBox]];
$arAllOptions['stores'][] = ["stock_filter_group", GetMessage('STORES_STOCK_FILTER_GROUP'), '', ['selectbox',  $selectGroup]];

$arAllOptions['stores'][] = ["stock_filter_flag", GetMessage('STORES_STOCK_FILTER_FLAG'), '', ['selectbox', ['N' => GetMessage('NON_SYNC')] + $selectBoolProps]];
$arAllOptions['stores'][] = ["im_stocks_filter_prop_value", GetMessage('FILTER_PROP_VALUE'), 'Y', ['selectbox', ['Y' => GetMessage('FILTER_PROP_Y'), 'N' => GetMessage('FILTER_PROP_N')]]];

//count
$arAllOptions['stores'][] = ['note' => GetMessage("STOCKS_SYNC_COUNT")];


$typeSync = [
   'S' => GetMessage('STOCKS_TYPE_S'),
   'A' => GetMessage('STOCKS_TYPE_A'),
   'T' => GetMessage('STOCKS_TYPE_T')
];

//PRODUCT
$arAllOptions['stores'][] = GetMessage('PRODUCT_HEAD_STOCKS' , ['#LINK#' => '/rbs-moyskladstocks/settings/stocks/import']);

foreach(['product', 'variant'] as $entityForStock){
   if($entityForStock === 'variant'){
      $arAllOptions['stores'][] = GetMessage('VARIANT_HEAD_STOCKS');
   }
   $arAllOptions['stores'][] = ["{$entityForStock}_stocks_sync", GetMessage('STOCKS_SYNC'), '', ['checkbox', "N", $paramsCheckBox]];
   $arAllOptions['stores'][] = ["{$entityForStock}_stocks_qty", GetMessage('STOCKS_QTY'), '', ['selectbox', $storeBxSelect]];
   $arAllOptions['stores'][] = ["{$entityForStock}_stocks_type", GetMessage('STOCKS_TYPE'), 'A', ['selectbox', $typeSync]];
   $arAllOptions['stores'][] = ["{$entityForStock}_stocks_double_type", GetMessage('STOCKS_DOUBLE_TYPE'), 'A', ['selectbox', [
      'ASC' => GetMessage('STOCKS_DOUBLE_TYPE_ASC'),
      'DESC' => GetMessage('STOCKS_DOUBLE_TYPE_DESC'),
      'ALL' => GetMessage('STOCKS_DOUBLE_TYPE_ALL'),
      'SKIP' => GetMessage('STOCKS_DOUBLE_TYPE_SKIP'),
   ]]];
   $arAllOptions['stores'][] = ["{$entityForStock}_stocks_update_element", GetMessage('UPDATE_IBLOCK_ELEMENT_PARAMS'), 'AUTO', ['selectbox', [
      'N' => GetMessage('UPDATE_IBLOCK_ELEMENT_PARAMS_NONE'),
      'AUTO' => GetMessage('UPDATE_IBLOCK_ELEMENT_PARAMS_AUTO'),
      'UPDATE' => GetMessage('UPDATE_IBLOCK_ELEMENT_PARAMS_UPDATE'),
   ]]];
}
unset($entityForStock);

//agent
OptionUtils::buildAgentOptionArray($arAllOptions['stores'], 'stocks', $paramsCheckBox, true, ['full_once', 'full_time', 'updated', 'last_update']);
if ($isSaveHit) {
   OptionUtils::saveAgentAction('stocks', 'import_stocks');
}

//import_once
OptionUtils::buildImportOnceButton($arAllOptions['stores'], 'import_once_stocks');


//BUNDLE
unset($storeBxSelect['ALL_MS']);
unset($storeBxSelect['ALL_BX']);
$arAllOptions['stores'][] = GetMessage('BUNDLE_HEAD_STOCKS');

$arAllOptions['stores'][] = ['note' => GetMessage("BUNDLE_STOCKS_NOTE")];

$arAllOptions['stores'][] = ["bundle_stocks_sync", GetMessage('STOCKS_SYNC'), '', ['checkbox', "N", $paramsCheckBox]];
$arAllOptions['stores'][] = ["bundle_stocks_qty", GetMessage('STOCKS_QTY'), '', ['selectbox', $storeBxSelect]];
$arAllOptions['stores'][] = ["bundle_stocks_type", GetMessage('STOCKS_TYPE'), 'A', ['selectbox',  $typeSync]];
$arAllOptions['stores'][] = ["bundle_stocks_double_type", GetMessage('STOCKS_DOUBLE_TYPE'), 'A', ['selectbox', [
   'ASC' => GetMessage('STOCKS_DOUBLE_TYPE_ASC'),
   'DESC' => GetMessage('STOCKS_DOUBLE_TYPE_DESC'),
   'ALL' => GetMessage('STOCKS_DOUBLE_TYPE_ALL'),
   'SKIP' => GetMessage('STOCKS_DOUBLE_TYPE_SKIP'),
]]];
$arAllOptions['stores'][] = ["bundle_stocks_update_element", GetMessage('UPDATE_IBLOCK_ELEMENT_PARAMS'), 'AUTO', ['selectbox', [
   'N' => GetMessage('UPDATE_IBLOCK_ELEMENT_PARAMS_NONE'),
   'AUTO' => GetMessage('UPDATE_IBLOCK_ELEMENT_PARAMS_AUTO'),
   'UPDATE' => GetMessage('UPDATE_IBLOCK_ELEMENT_PARAMS_UPDATE'),
]]];

OptionUtils::buildAgentOptionArray($arAllOptions['stores'], 'bundle_stocks', $paramsCheckBox, false, ['limit','full_once', 'full_time', 'updated', 'last_update']);
if ($isSaveHit) {
   OptionUtils::saveAgentAction('bundle_stocks', "import_bundle_stocks");
}

OptionUtils::buildImportOnceButton($arAllOptions['stores'], 'import_once_bundle_stocks', true);

if(!empty($arAllOptions['stores'])){
   $aTabs[] = [
      "DIV" => "stocks",
      "TAB" => GetMessage('STORES_HEAD'),
      "ICON" => "order_settings",
      "TITLE" => GetMessage('STORES_HEAD')
   ];
}
