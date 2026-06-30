<?php

namespace Rbs\MoyskladStocks\Import\Entity;

use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\LangMsg;
use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Internals\ProductFinder\ProductIdentifier;
use Bitrix\Main\Loader;

Loader::includeModule('catalog');

class ImportBundle
{
    public static function import($productId = 0, $item = null, $checkUpdate = false, $bundlePartIblockId = [], &$arrLog = [])
    {
        if(!empty($item->{'components'}) && !empty($item->{'externalCode'}) && Utils::array_exists($item->{'components'}, 'rows')) {
            $bundleCache = (object)[
                'href' => $item->{'meta'}->{'href'},
                'externalCode' => $item->{'externalCode'},
                'components' => []
            ];
            foreach($item->{'components'}->{'rows'} as $componentRow) {
                if(!empty($componentRow->{'assortment'}->{'meta'}->{'href'})) {
                    $bundleCache->{'components'}[$componentRow->{'assortment'}->{'meta'}->{'href'}] = $componentRow->{'quantity'};
                }
            }
        } else {
            $arrLog['#ERROR#']++;
            $arrLog['ERROR_LIST'][$item->{'externalCode'}] = "[" . $item->{'externalCode'} . "] " . LangMsg::get('WARNING_EMPTY_BUNDLE_COMPONENTS');
        }
        
        if (Utils::array_exists($bundleCache, 'components')) {

            $currentComponentsArray = [];
            $currentSetArray = [];
            if ($checkUpdate) {
                $currentSetArray = \CCatalogProductSet::getAllSetsByProduct($productId, \CCatalogProductSet::TYPE_SET);
                if (Utils::is_count($currentSetArray)) {
                    $currentSetArray = array_pop($currentSetArray);
                    if (is_array($currentSetArray['ITEMS']) && count($currentSetArray) > 0) {
                        $currentComponentsArray = $currentSetArray['ITEMS'];
                    }
                }
            }
                
            $isAllComponentLoaded = true;
            $componentsArray = [];

            $event = new \Bitrix\Main\Event(Config::getModuleId(true), "OnImportBundle", array(
                'productId' => $productId,
                'itemMs' => $item,
                'bundleCache' => $bundleCache,
                'currentComponentsArray' => $currentComponentsArray,
                'bundlePartIblockId' => $bundlePartIblockId,
            ));
    
            $event->send();
    
            $isEventProcessing = false;

            if ($event->getResults()) {
                foreach ($event->getResults() as $eventResult) {
                    if ($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS) {
                        $eventParameters = $eventResult->getParameters();
                        $isAllComponentLoaded = $eventParameters['isAllComponentLoaded'];
                        $componentsArray = $eventParameters['componentsArray'];
                        $isEventProcessing = true;
                    }
                }
            }

            if(!$isEventProcessing) {
                $componentAssortments = [];
                foreach ($item->{'components'}->{'rows'} as $componentRow) {
                    if (!empty($componentRow->{'assortment'}->{'meta'}->{'href'})) {
                        $componentAssortments[$componentRow->{'assortment'}->{'meta'}->{'href'}] = $componentRow->{'assortment'};
                    }
                }

                foreach ($bundleCache->{'components'} as $productHref => $qty) {

                    $componentAssortment = $componentAssortments[$productHref] ?? null;
                    if ($componentAssortment && (int)($componentAssortment->{'variantsCount'} ?? 0) > 0) {
                        $arrLog['ERROR_LIST'][$item->{'externalCode'}] = LangMsg::get('ERROR_BUNDLE_ADD_COMPONENTS', [
                            '#PRODUCT_ID#' => $productId,
                            '#XML_ID#' => $item->{'externalCode'},
                            '#ERROR#' => LangMsg::get('ERROR_BUNDLE_COMPONENT_IS_SKU', [
                                '#COMPONENT_ID#' => $componentAssortment->{'name'} ?? $productHref,
                            ])
                        ]);
                        $isAllComponentLoaded = false;
                        break;
                    }

                    $productExtCode = ProductIdentifier::resolveXmlIdFromHref($productHref);

                    if (empty($productExtCode)) {
                        $isAllComponentLoaded = false;
                        break;
                    }

                    $componentEntity = ProductIdentifier::extractEntityFromHref($productHref);
                    $filter = ProductIdentifier::buildSingleFilter($productExtCode, $componentEntity);
                    if (count($bundlePartIblockId) > 0) {
                        $filter['=IBLOCK_ID'] = $bundlePartIblockId;
                    }

                    $currentProduct = \Bitrix\Iblock\ElementTable::getList([
                        'select' => ['ID'],
                        'filter' => $filter
                    ])->fetch();

                    if ((int)$currentProduct['ID'] > 0) {
                        $componentsArray[] = [
                            'ITEM_ID'          => $currentProduct['ID'],
                            'QUANTITY'         => $qty,
                            'DISCOUNT_PERCENT' => 0,
                            'SORT'             => 100
                        ];
                    } else {
                        $isAllComponentLoaded = false;
                        break;
                    }

                }
            }
                
            if ($isAllComponentLoaded && count($componentsArray) === count($bundleCache->{'components'})) {
                
                $arSaveSet = array(
                    'TYPE'    => \CCatalogProductSet::TYPE_SET,
                    'ITEM_ID' => $productId,
                    'ITEMS'   => $componentsArray
                );

                if (count($currentComponentsArray) === intval(0)) {
                    $currentSetArray['SET_ID'] = \CCatalogProductSet::add($arSaveSet);
                    
                    if ($ex = $GLOBALS['APPLICATION']->GetException()){
                        $strError = $ex->GetString();
                        if(!empty($strError)) {
                            $arrLog['ERROR_LIST'][$item->{'externalCode'}] = LangMsg::get('ERROR_BUNDLE_ADD_COMPONENTS', ['#PRODUCT_ID#' => $productId, '#XML_ID#' => $item->{'externalCode'}, '#ERROR#' => $strError]);
                        }
                    }

                } elseif ($checkUpdate) {
                    $isComponentNeedUpdate = count($currentComponentsArray) !== count($componentsArray);
                    if (!$isComponentNeedUpdate) {
                        foreach ($currentComponentsArray as $itemSet) {
                            if (
                                    !isset($componentsArray[$itemSet['ITEM_ID']]) ||
                                    (isset($componentsArray[$itemSet['ITEM_ID']]) && $componentsArray[$itemSet['ITEM_ID']]['QUANTITY'] != $itemSet['QUANTITY'])
                                ) {
                                $isComponentNeedUpdate = true;
                                break;
                            }
                        }
                    }
                    if ($isComponentNeedUpdate && (int)$currentSetArray['SET_ID'] > 0) {
                        \CCatalogProductSet::update((int)$currentSetArray['SET_ID'], $arSaveSet);
                    }
                }   

                if ((int)$currentSetArray['SET_ID'] > 0) {
                    \CCatalogProductSet::recalculateSetsByProduct($productId);
                }

            } elseif (!isset($arrLog['ERROR_LIST'][$item->{'externalCode'}])) {
                $arrLog['ERROR_LIST'][$item->{'externalCode'}] = LangMsg::get('ERROR_BUNDLE_SEARCH_COMPONENTS', ['#PRODUCT_ID#' => $productId, '#XML_ID#' => $item->{'externalCode'}]);
            }
        }
    }
}