<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

//SITE LIST
$rsSites = CSite::GetList($by="sort", $order="asc");
$selectSites = [];
while($site = $rsSites->GetNext()){
    $selectSites[$site['ID']] = "[{$site['ID']}] {$site['NAME']}";
}

//USER GROUPS
$userGroups = \Bitrix\Main\GroupTable::getList()->fetchAll();
$selectGroups = [];
foreach($userGroups as $group){
    $selectGroups[$group['ID']] = "[{$group['ID']}] {$group['NAME']}";
}

//PRICE TYPES
$pricesBxForSelect = \Bitrix\Catalog\GroupTable::getList()->fetchAll();
$selectPrices = [];
foreach($pricesBxForSelect as $priceType){
    $selectPrices[$priceType['ID']] = "[{$priceType['ID']}] {$priceType['NAME']}";
}

//CATALOG IBLOCK IDs
$allCatalogs = \Bitrix\Catalog\CatalogIblockTable::getList()->fetchAll();
$catalogIblockIds = [];
$skuIblockIds = [];
$selectCatalog = ['N' => GetMessage('NON_SYNC')];
$multiSelectCatalog = [];
if(count($allCatalogs) > 0){

    foreach($allCatalogs as $catalog){
        $catalogIblockIds[] = $catalog['IBLOCK_ID'];
        if($catalog['PRODUCT_IBLOCK_ID'] > 0 && $catalog['SKU_PROPERTY_ID'] > 0){
            $skuIblockIds[$catalog['PRODUCT_IBLOCK_ID']] = [
                'IBLOCK_ID' => $catalog['IBLOCK_ID'],
                'SKU_PROPERTY_ID' => $catalog['SKU_PROPERTY_ID']
            ];
        }
    }

    $allIblocks = \Bitrix\Iblock\IblockTable::getList()->fetchAll();
    foreach($allIblocks as $iblock){
        if(in_array($iblock['ID'], $catalogIblockIds)){
            $selectCatalog[$iblock['ID']] = "[{$iblock['ID']}] {$iblock['NAME']} ({$iblock['LID']})";
            $multiSelectCatalog[$iblock['ID']] = "[{$iblock['ID']}] {$iblock['NAME']} ({$iblock['LID']})";
        }
    }
}

$allIblockList = \Bitrix\Iblock\IblockTable::getList()->fetchAll();
$arIblockTypeList = [];
if(count($allIblockList) > 0){
    foreach($allIblockList as $iblockParams) {
        $arIblockTypeList[$iblockParams['ID']] = $iblockParams['IBLOCK_TYPE_ID'];
    }
}
//text \ html

$descrTextTypes  = [
    'default' => GetMessage('DESCR_TEXT_TYPE_DEFAULT'),
    'html' => GetMessage('DESCR_TEXT_TYPE_HTML'),
    'text' => GetMessage('DESCR_TEXT_TYPE_TEXT')
];

//LIMITS
$stockLimits = $baseLimits = [1 => 1, 2 => 2, 3 => 3, 4 => 4, 5 => 5];

$expandLimits = [];
for($i = 10; $i <= 90; $i += 10){
   $stockLimits[$i] = $i;
   $expandLimits[$i] = $i;
}
$expandLimits[100] = 100;

for($i = 100; $i <= 1000; $i += 100){
   $stockLimits[$i] = $i;
}


$timeIntervalsForFullUpd = [];
for ($i = 0; $i <= 23; $i++) {
    $startTime = $i > 9 ? (string)$i : '0' . $i;
    $finishTime = ($i + 1 > 9) ? (string)($i + 1) : '0' . (string)($i + 1);
    $finishTime = (int)$finishTime === 24 ? '00' : $finishTime;
    $timeIntervalsForFullUpd[$i] = $startTime . ':00' . ' - ' . $finishTime . ':00';
}