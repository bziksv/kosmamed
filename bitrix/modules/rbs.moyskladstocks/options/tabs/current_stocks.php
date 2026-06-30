<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
   die();
}

use Rbs\MoyskladStocks\Internals\OptionUtils;
use Rbs\MoyskladStocks\Utils;

$arAllOptions['current_stocks'][] = GetMessage("MAIN_SETTINGS_DOC", ['#LINK#' => '/rbs-moyskladstocks/settings/current_stocks']);

$arAllOptions['current_stocks'][] = ['note' => GetMessage("CURRENT_STOCKS_NOTE")];

$arAllOptions['current_stocks'][] = GetMessage('STORES_HEAD_ASSOC' , ['#LINK#' => '/rbs-moyskladstocks/settings/current_stocks/conformity']);

$storesBx = \Bitrix\Catalog\StoreTable::getList([
   'filter' => [
      'ACTIVE' => 'Y'
   ],
   'cache' => [
      'ttl' => 0
   ]
])->fetchAll();

$storesMs = ['N' => GetMessage('NON_SYNC'), 'all_stocks' => GetMessage('ALL_STOCKS_MS_QTY')];

$storeBxSelect = ['ALL' => GetMessage('ALL_QTY'), /* 'ALL_MS' => GetMessage('ALL_MS_QTY'), */ 'ALL_BX' => GetMessage('ALL_BX_QTY')];

if(Utils::is_count($storesBx)){

   $storesMsGet = \Rbs\MoyskladStocks\ApiNew::get('/entity/store', [], 60);
   if(Utils::is_success($storesMsGet) && Utils::array_exists($storesMsGet)){
      foreach ($storesMsGet->rows as $store) {
         $storesMs[$store->id] = $store->name;
      }
   }
   
   foreach($storesBx as $storeBx){
      $arAllOptions['current_stocks'][] = ["curr_stocks_store_" . $storeBx['ID'], "[{$storeBx['ID']}] {$storeBx['TITLE']}", '', ['selectbox', $storesMs]];
      $storeBxSelect[$storeBx['ID']] = "[{$storeBx['ID']}] {$storeBx['TITLE']}";
   }

   $arAllOptions['current_stocks'][] = ["curr_stocks_store_parents", GetMessage('STORE_PARENTS'), '', ['checkbox', "N", $paramsCheckBox]];

} else {

   $arAllOptions['current_stocks'][] = ['note' => GetMessage("STORES_NEED_CREATE_NOTE")];

}

$arAllOptions['current_stocks'][] = GetMessage('CURRENT_STOCKS_PARAMS', ['#LINK#' => '/rbs-moyskladstocks/settings/current_stocks/params']);

$typeSyncCurrStocks = [
   'stock' => GetMessage('STOCKS_TYPE_S'),
   'quantity' => GetMessage('STOCKS_TYPE_A'),
   'freeStock' => GetMessage('STOCKS_TYPE_T')
];

$arAllOptions['current_stocks'][] = ["curr_stocks_p_stock_type", GetMessage("CURRENT_STOCKS_IMPORT_TYPE"), 'quantity', ['selectbox', $typeSyncCurrStocks]];

$arAllOptions['current_stocks'][] = ["curr_stocks_p_qty_type", GetMessage('STOCKS_QTY'), 'ALL', ['selectbox', $storeBxSelect]];

$arAllOptions['current_stocks'][] = ["curr_stocks_p_entity_type", GetMessage('STOCKS_ENTITY'), '', ['multiselectbox', [
   'product' => GetMessage('IMPORT_HEAD_product'),
   'variant' => GetMessage('IMPORT_HEAD_variant'),
   'bundle' => GetMessage('IMPORT_HEAD_bundle'),
]]];

$arAllOptions['current_stocks'][] = ["curr_stocks_p_double_type", GetMessage('STOCKS_DOUBLE_TYPE'), 'A', ['selectbox', [
   'ASC' => GetMessage('STOCKS_DOUBLE_TYPE_ASC'),
   'DESC' => GetMessage('STOCKS_DOUBLE_TYPE_DESC'),
   'ALL' => GetMessage('STOCKS_DOUBLE_TYPE_ALL'),
   'SKIP' => GetMessage('STOCKS_DOUBLE_TYPE_SKIP'),
]]];

$arAllOptions['current_stocks'][] = ["curr_stocks_update_element", GetMessage('UPDATE_IBLOCK_ELEMENT_PARAMS'), 'N', ['selectbox', [
   'N' => GetMessage('UPDATE_IBLOCK_ELEMENT_PARAMS_NONE'),
   //'AUTO' => GetMessage('UPDATE_IBLOCK_ELEMENT_PARAMS_AUTO'),
   'UPDATE' => GetMessage('UPDATE_IBLOCK_ELEMENT_PARAMS_UPDATE'),
]]];

$currentStocksRowsLimits = [];
for($i = 500; $i <= 10000; $i += 500) {
   $currentStocksRowsLimits[$i] = $i;
}
$arAllOptions['current_stocks'][] = ["curr_stocks_p_limit", GetMessage('CURR_STOCKS_QTY_LIMIT'), '2000', ['selectbox', Utils::build_number_array(500, 5000, 500)]];
$arAllOptions['current_stocks'][] = ["curr_stocks_p_full_limit", GetMessage('CURR_STOCKS_FULL_QTY_LIMIT'), '10000', ['selectbox', Utils::build_number_array(5000, 50000, 5000)]];
$arAllOptions['current_stocks'][] = ["curr_stocks_max_diff_seconds", GetMessage("CURR_STOCKS_MAX_DIFF_SECONDS"), '1439', ['text', 30]];

//agent
OptionUtils::buildAgentOptionArray($arAllOptions['current_stocks'], 'curr_stocks', $paramsCheckBox, true, ['limit', 'offset', 'updated']);
if ($isSaveHit) {
   OptionUtils::saveAgentAction('curr_stocks', 'import_current_stocks');
}


if(!empty($arAllOptions['current_stocks'])){
   $aTabs[] = [
      "DIV" => "current_stocks",
      "TAB" => GetMessage('CURRENT_STOCKS_HEAD'),
      "ICON" => "order_settings",
      "TITLE" => GetMessage('CURRENT_STOCKS_HEAD')
   ];
}