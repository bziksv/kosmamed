<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
   die();
}

use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\Internals\OptionUtils;

$arAllOptions['prices'][] = GetMessage("MAIN_SETTINGS_DOC", ['#LINK#' => '/rbs-moyskladstocks/settings/prices']);

$arAllOptions['prices'][] = GetMessage('PRICES_HEAD_ASSOC', ['#LINK#' => '/rbs-moyskladstocks/settings/prices/conformity']);

$metaDataProduct = \Rbs\MoyskladStocks\ApiNew::get('/entity/product/metadata', [], 60);
$priceTypes = \Rbs\MoyskladStocks\ApiNew::get('/context/companysettings/pricetype/', [], 60);

$pricesBx = \Bitrix\Catalog\GroupTable::getList()->fetchAll();

$pricesMs = ['N' => GetMessage('NON_SYNC'), 'minPrice' => GetMessage('MIN_PRICE'), 'buyPrice' => GetMessage('BUY_PRICE')];

if(Utils::is_count($pricesBx)){

   if(Utils::is_count($priceTypes)){
      foreach ($priceTypes as $priceType) {
         $pricesMs[$priceType->id] = $priceType->name;
      }
   }

   $arAllOptions['prices'][] = ["price_purchase", GetMessage('BUY_PRICE'), '', ['selectbox', $pricesMs]];
   foreach($pricesBx as $priceBx){
      $arAllOptions['prices'][] = ["price_" . $priceBx['ID'], "[{$priceBx['ID']}] {$priceBx['NAME']}", '', ['selectbox', $pricesMs]];
   }

}

$currencies = \Rbs\MoyskladStocks\ApiNew::get('/entity/currency', [], 60);
$arCurrencies = [];
$arCurrenciesIds = [];
if(Utils::is_success($currencies) && Utils::array_exists($currencies)){
   foreach ($currencies->rows as $row) {
      $arCurrencies[$row->id] = "{$row->isoCode} {$row->fullName}";
      $arCurrenciesIds[$row->isoCode] = $row->id;
   }
}

$currencyBx = \Bitrix\Currency\CurrencyLangTable::getList([
   'filter' => [
      '=LID' => 'ru'
   ]
])->fetchAll();
$currencyBxSelect = [];
if(count($currencyBx) > 0 && count($arCurrencies) > 0){

   $arAllOptions['prices'][] = GetMessage('CURRENCY_SYNC', ['#LINK#' => '/rbs-moyskladstocks/settings/prices/currency']);

   $arCurrencies = array_merge(['N' => GetMessage('NON_SYNC')], $arCurrencies);
   
   foreach($currencyBx as $currency){
      $arAllOptions['prices'][] = ["currency_{$currency['CURRENCY']}", "[{$currency['CURRENCY']}] {$currency['FULL_NAME']}", $arCurrenciesIds[$currency['CURRENCY']], ['selectbox', $arCurrencies]];
      $currencyBxSelect[$currency['CURRENCY']] = $currency['CURRENCY'];
   }

}

foreach(['product', 'variant', 'service', 'bundle'] as $entity){
   
   $entityUpper = mb_strtoupper($entity);

   $arAllOptions['prices'][] = GetMessage('IMPORT_HEAD_PRICES_' . $entity, ['#LINK#' => '/rbs-moyskladstocks/settings/prices/import']);
   
   $arAllOptions['prices'][] = ["{$entity}_prices_sync", GetMessage('PRICES_SYNC'), '', ['checkbox', "N", $paramsCheckBox]];
   $arAllOptions['prices'][] = ["{$entity}_prices_clear_zero", GetMessage('PRICES_CLEAR_ZERO'), '', ['checkbox', "N", $paramsCheckBox]];
   $arAllOptions['prices'][] = ["{$entity}_prices_range_first", GetMessage('PRICES_RANGE_FIRST_USE'), '', ['checkbox', "N", $paramsCheckBox]];
   $arAllOptions['prices'][] = ["{$entity}_prices_range_all", GetMessage('PRICES_RANGE_ALL_USE'), '', ['checkbox', "N", $paramsCheckBox]];
   $arAllOptions['prices'][] = ["{$entity}_prices_double_type", GetMessage('STOCKS_DOUBLE_TYPE'), 'ASC', ['selectbox', [
      'ASC' => GetMessage('STOCKS_DOUBLE_TYPE_ASC'),
      'DESC' => GetMessage('STOCKS_DOUBLE_TYPE_DESC'),
      'ALL' => GetMessage('STOCKS_DOUBLE_TYPE_ALL'),
      'SKIP' => GetMessage('STOCKS_DOUBLE_TYPE_SKIP'),
   ]]];
   $arAllOptions['prices'][] = ["{$entity}_prices_update_element", GetMessage('UPDATE_IBLOCK_ELEMENT_PARAMS'), 'AUTO', ['selectbox', [
      'N' => GetMessage('UPDATE_IBLOCK_ELEMENT_PARAMS_NONE'),
      'AUTO' => GetMessage('UPDATE_IBLOCK_ELEMENT_PARAMS_AUTO'),
      'UPDATE' => GetMessage('UPDATE_IBLOCK_ELEMENT_PARAMS_UPDATE'),
   ]]];

   //agent
   if($entity == 'variant'){
      $arAllOptions['prices'][] = ['note' => GetMessage('NOTE_PRICES_VARIANT_TEXT')];
   }
   OptionUtils::buildAgentOptionArray($arAllOptions['prices'], "{$entity}_price", $paramsCheckBox, false, []);
   if ($isSaveHit) {
      OptionUtils::saveAgentAction("{$entity}_price", "import_prices_{$entity}");
   }

   OptionUtils::buildImportOnceButton($arAllOptions['prices'], "import_once_{$entity}_price", false);

}


if(!empty($arAllOptions['prices'])){
   $aTabs[] = [
      "DIV" => "prices",
      "TAB" => GetMessage('PRICES_HEAD'),
      "ICON" => "order_settings",
      "TITLE" => GetMessage('PRICES_HEAD')
   ];
} 