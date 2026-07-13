<?php

namespace Rbs\MoyskladStocks\Compitable;

use Rbs\MoyskladStocks\HlCache\ExtCodes;
use Rbs\MoyskladStocks\HlCache\Stocks as StocksHl;
use Rbs\MoyskladStocks\HlCache\Bundles;
use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\Services\ConfigurationUtils;
use Rbs\MoyskladStocks\Internals\ProductFinder\ProductIdentifier;

\Bitrix\Main\Loader::includeModule('catalog');
\Bitrix\Main\Loader::includeModule('iblock');

class ImportStocksClass
{
    public static function updateAllBundle()
    {
        $entity = 'bundle';

        $arBundles = Bundles::getAll();

        if (!$arBundles || (is_array($arBundles) && count($arBundles) <= 0)) {
            return false;
        }

        $storeList = ConfigurationUtils::getStoreList();
        if (!$storeList || (is_array($storeList) && count($storeList) <= 0)) {
            return false;
        }

        $catalogIblockId = 0;
        $fModifIblock = '>';
        if (Config::isIblockRequired($entity)) {
            $catalogIblockId = Config::getIblockId($entity);
            $fModifIblock = '=';
            if ((int)$catalogIblockId <= 0) {
                return false;
            }
        }

        $typeStocks = Config::getTypeStockSync($entity);
        $qtyStocks = Config::getQtyStockSync($entity);

        $arrLog = [
            '#ENTITY#' => $entity,
            '#FINDED#' => 0,
            '#UPDATED#' => 0,
            '#ADDED#' => 0,
            '#ALL#' => count($arBundles)
        ];

        foreach ($arBundles as $bundle) {
            $bundleXmlId = ProductIdentifier::getIdentifierValue($bundle);
            if (!empty($bundle->href) && !empty($bundleXmlId) && Utils::array_exists($bundle, 'components')) {

                $filter = ProductIdentifier::buildSingleFilter($bundleXmlId, 'bundle');
                $filter[$fModifIblock . 'IBLOCK_ID'] = $catalogIblockId;
                $element = \Bitrix\Iblock\ElementTable::getList(['filter' => $filter])->fetch();

                $filterProduct = [];
                if ((int)$element['ID'] <= 0) {
                    continue;
                } else {
                    $filterProduct['ID'] = $element['ID'];
                }

                $rsProduct = \Bitrix\Catalog\ProductTable::getList([
                    'filter' => $filterProduct,
                    'select' => ['ID', 'QUANTITY']
                ])->fetchAll();

                if (count($rsProduct) > 0) {

                    $productBx = array_pop($rsProduct);

                    $arrLog['#FINDED#']++;

                    $componentsStores = [];
                    foreach ($bundle->components as $productHref => $quantity) {
                        if ($stocks = StocksHl::get($productHref)) {
                            foreach ($stocks as $storeId => $stockInfo) {

                                switch ($typeStocks) {
                                    case 'A':
                                        $finalStocks = $stockInfo['q'];
                                        break;
                                    case 'S':
                                        $finalStocks = $stockInfo['s'];
                                        break;
                                    case 'T':
                                        $finalStocks = $stockInfo['t'];
                                        break;
                                }

                                $stock = floor($finalStocks / $quantity);
                                if ($stock < 0) {
                                    $stock = 0;
                                }

                                if (isset($componentsStores[$storeId])) {
                                    if ($stock < $componentsStores[$storeId]) {
                                        $componentsStores[$storeId] = $stock;
                                    }
                                } else {
                                    $componentsStores[$storeId] = $stock;
                                }
                            }
                        }
                    }

                    $currentStocks = \Bitrix\Catalog\StoreProductTable::getList([
                        'filter' => [
                            'PRODUCT_ID' => $productBx['ID'],
                            'STORE_ID' => array_keys($storeList)
                        ]
                    ])->fetchAll();

                    $currentFindStocks = [];
                    $qtyByStocks = ['ALL' => 0];
                    foreach ($currentStocks as $stockInfoBx) {
                        $currentFindStocks[] = $stockInfoBx['STORE_ID'];

                        if (!isset($storeList[$stockInfoBx['STORE_ID']])) continue;
                        if (!isset($componentsStores[$storeList[$stockInfoBx['STORE_ID']]])) continue;

                        $finalStocks = $componentsStores[$storeList[$stockInfoBx['STORE_ID']]];

                        if ((float)$finalStocks !== (float)$stockInfoBx['AMOUNT']) {
                            \Bitrix\Catalog\StoreProductTable::update($stockInfoBx['ID'], ['AMOUNT' => $finalStocks]);
                            $arrLog['#UPDATED#']++;
                        }

                        $qtyByStocks[$stockInfoBx['STORE_ID']] = $finalStocks;
                        $qtyByStocks['ALL'] += $finalStocks;
                    }

                    if (count($currentFindStocks) !== count($storeList)) {
                        foreach ($storeList as $storeId => $storeMsId) {
                            if (!in_array($storeId, $currentFindStocks)) {

                                if (isset($componentsStores[$storeList[$storeId]])) {
                                    $finalStocks = $componentsStores[$storeList[$storeId]];

                                    \Bitrix\Catalog\StoreProductTable::add([
                                        'STORE_ID' => $storeId,
                                        'PRODUCT_ID' => $productBx['ID'],
                                        'AMOUNT' => $finalStocks
                                    ]);
                                    $arrLog['#ADDED#']++;
                                    $qtyByStocks[$storeId] = $finalStocks;
                                    $qtyByStocks['ALL'] += $finalStocks;
                                }
                            }
                        }
                    }

                    if (isset($qtyByStocks[$qtyStocks]) && (float)$productBx['QUANTITY'] !== (float)$qtyByStocks[$qtyStocks]) {
                        \Bitrix\Catalog\Model\Product::update($productBx['ID'], [
                            'QUANTITY' => (float)$qtyByStocks[$qtyStocks]
                        ]);
                    }
                }
            }
        }
    }

    public static function updateAll($xmlIds = [], $entity = '')
    {
        if (empty($entity)) return;

        $stocks = StocksHl::getArray(array_keys($xmlIds));
        if (!Utils::is_count($stocks)) {
            return false;
        }

        $storeList = Config::getStores();
        if (!Utils::is_count($storeList)) {
            return false;
        }

        if (!ExtCodes::isExsist()) {
            return false;
        }

        $catalogIblockId = "-1";

        if (Config::isIblockRequired($entity)) {
            $catalogIblockId = Config::getIblockId($entity);
            if ((int)$catalogIblockId <= 0) {
                return false;
            }
        }

        $typeStocks = Config::getTypeStockSync($entity);
        $qtyStocks = Config::getQtyStockSync($entity);

        $arrLog = [
            '#ENTITY#' => $entity,
            '#FINDED#' => 0,
            '#UPDATED#' => 0,
            '#ADDED#' => 0,
            '#ALL#' => count($stocks)
        ];

        foreach ($stocks as $stockInfo) {

            if (!empty($stockInfo->href)) {

                $extCode = $xmlIds[$stockInfo->href];
                if (empty($extCode)) {
                    continue;
                }

                ImportStocksClass::updateRow($extCode, $catalogIblockId, $entity, $typeStocks, $qtyStocks,  $storeList, $stockInfo->stocks, $arrLog);
            }
        }
    }

    public static function updateRow($xmlId = '', $catalogIblockId = '', $entity = '', $typeStocks = 'A', $qtyStocks = 'ALL', $storeList = [], $stocks = [], &$arrLog = [])
    {
        $filter = ProductIdentifier::buildSingleFilter($xmlId, $entity);
        $filter['IBLOCK_ID'] = $catalogIblockId;

        if ($catalogIblockId === "-1" && isset($filter['IBLOCK_ID'])) {
            unset($filter['IBLOCK_ID']);
        }

        $element = \Bitrix\Iblock\ElementTable::getList(['filter' => $filter])->fetch();
        $filterProduct = [];
        if ((int)$element['ID'] <= 0) {
            return;
        } else {
            $filterProduct['ID'] = $element['ID'];
        }

        $rsProduct = \Bitrix\Catalog\ProductTable::getList([
            'order' => ['ID' => 'DESC'],
            'filter' => $filterProduct,
            'select' => ['ID', 'QUANTITY']
        ])->fetch();

        if ((int)$rsProduct['ID'] > 0) {
            $productBx = $rsProduct;

            $arrLog['#FINDED#']++;

            $currentStocks = \Bitrix\Catalog\StoreProductTable::getList([
                'filter' => [
                    'PRODUCT_ID' => $productBx['ID'],
                    'STORE_ID' => array_keys($storeList)
                ]
            ])->fetchAll();

            $currentFindStocks = [];

            $qtyByStocks = ['ALL' => 0, 'ALL_MS' => 0];

            switch ($typeStocks) {
                case 'A':
                    $qtyByStocks['ALL_MS'] = (float)$stocks['all']['q'];
                    break;
                case 'S':
                    $qtyByStocks['ALL_MS'] = (float)$stocks['all']['s'];
                    break;
                case 'T':
                    $qtyByStocks['ALL_MS'] = (float)$stocks['all']['t'];
                    break;
            }

            foreach ($currentStocks as $stockInfoBx) {
                $currentFindStocks[] = $stockInfoBx['STORE_ID'];

                if (!isset($storeList[$stockInfoBx['STORE_ID']])) continue;
                if (!isset($stocks[$storeList[$stockInfoBx['STORE_ID']]])) continue;

                $currentStockMs = $stocks[$storeList[$stockInfoBx['STORE_ID']]];
                switch ($typeStocks) {
                    case 'A':
                        $finalStocks = $currentStockMs['q'];
                        break;
                    case 'S':
                        $finalStocks = $currentStockMs['s'];
                        break;
                    case 'T':
                        $finalStocks = $currentStockMs['t'];
                        break;
                }

                if ((float)$finalStocks !== (float)$stockInfoBx['AMOUNT']) {
                    \Bitrix\Catalog\StoreProductTable::update($stockInfoBx['ID'], ['AMOUNT' => $finalStocks]);
                    $arrLog['#UPDATED#']++;
                }

                $qtyByStocks[$stockInfoBx['STORE_ID']] = $finalStocks;
                $qtyByStocks['ALL'] += $finalStocks;
            }

            if (count($currentFindStocks) !== count($storeList)) {
                foreach ($storeList as $storeId => $storeMsId) {
                    if (!in_array($storeId, $currentFindStocks)) {

                        if (isset($stocks[$storeList[$storeId]])) {
                            $currentStockMs = $stocks[$storeList[$storeId]];
                            switch ($typeStocks) {
                                case 'A':
                                    $finalStocks = $currentStockMs['q'];
                                    break;
                                case 'S':
                                    $finalStocks = $currentStockMs['s'];
                                    break;
                                case 'T':
                                    $finalStocks = $currentStockMs['t'];
                                    break;
                            }

                            \Bitrix\Catalog\StoreProductTable::add([
                                'STORE_ID' => $storeId,
                                'PRODUCT_ID' => $productBx['ID'],
                                'AMOUNT' => $finalStocks
                            ]);

                            $arrLog['#ADDED#']++;

                            $qtyByStocks[$storeId] = $finalStocks;
                            $qtyByStocks['ALL'] += $finalStocks;
                        }
                    }
                }
            }

            if ($qtyStocks == 'ALL_BX') {
                $currentStocks = \Bitrix\Catalog\StoreProductTable::getList([
                    'filter' => [
                        'PRODUCT_ID' => $productBx['ID']
                    ]
                ])->fetchAll();
                $qtyByStocks[$qtyStocks] = 0;
                foreach ($currentStocks as $stockInfoBx) {
                    $qtyByStocks[$qtyStocks] += (float)$stockInfoBx['AMOUNT'];
                }
            }

            $arUpdateProductFields = [];

            if (isset($qtyByStocks[$qtyStocks]) && (float)$productBx['QUANTITY'] !== (float)$qtyByStocks[$qtyStocks]) {
                $arUpdateProductFields['QUANTITY'] = (float)$qtyByStocks[$qtyStocks];
            }

            $event = new \Bitrix\Main\Event(Config::getModuleId(true), "OnBeforeUpdateProductInStockRow", array(
                'arUpdateProductFields' => $arUpdateProductFields,
                'productBx' => $productBx,
                'currentStocks' => $currentStocks
            ));

            $event->send();

            if ($event->getResults()) {
                foreach ($event->getResults() as $eventResult) {
                    if ($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS) {
                        $arUpdateProductFields = $eventResult->getParameters();
                    }
                }
            }

            if (count($arUpdateProductFields) > 0) {
                \Bitrix\Catalog\Model\Product::update($productBx['ID'], $arUpdateProductFields);
            }
        }
    }
}