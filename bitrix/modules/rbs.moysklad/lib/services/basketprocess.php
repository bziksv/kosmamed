<?php
namespace Rbs\Moysklad\Services;

use Rbs\Moysklad\Config;


class BasketProcess
{
    public static function fillBxBasketFromMsBasket(\Bitrix\Sale\Basket &$basket, array $arMsBasket = [], array $arBxBasket = [], &$needBackCanBuyZero, $currency): void
    {
        if(gettype($needBackCanBuyZero) !== 'array') {
            $needBackCanBuyZero = [];
        }

        $isHardAdd = Config::checkFeature('baskethardadd');
        $providerOff = Config::checkFeature('basketprovideroff');
        $isAllAdd = Config::checkFeature('basket_all_add');
        $siteId = $basket->getOrder()->getSiteId();
        
        if(isset($arMsBasket[Config::getDeliveryExternalCode()])) {
            unset($arMsBasket[Config::getDeliveryExternalCode()]);
        }

        $uniqBxBasket = [];
        foreach ($arBxBasket as $item) {
            $uniqBxBasket[$item['XML_ID']][] = $item;
        }
        $uniqMsBasket = [];
        foreach ($arMsBasket as $item) {
            $uniqMsBasket[$item['XML_ID']][] = $item;
        }
        
        //DELETE ITEMS
        $deleteBasketItems = [];
        foreach($uniqBxBasket as $itemXmlId => $bxBasketItems) {
            if(!isset($uniqMsBasket[$itemXmlId])) {
                foreach($bxBasketItems as $bxBasketItem) {
                    if (!empty($bxBasketItem['ID'])) {
                        $deleteBasketItems[$bxBasketItem['ID']] = $bxBasketItem['ID'];
                    }
                }
                unset($uniqBxBasket[$itemXmlId]);
            } else if (count($uniqMsBasket[$itemXmlId]) < count($bxBasketItems)) {
                $needDeleteItems = count($bxBasketItems) - count($uniqMsBasket[$itemXmlId]);
                if($needDeleteItems > intval(0)) {
                    for($i = intval(0); $i < $needDeleteItems; $i++) {
                        if(count($uniqBxBasket[$itemXmlId]) > intval(0)) {
                            $bxBasketItem = array_pop($uniqBxBasket[$itemXmlId]);
                            if(!empty($bxBasketItem['ID'])) {
                                $deleteBasketItems[$bxBasketItem['ID']] = $bxBasketItem['ID'];
                            }
                        }
                    }
                }
            }
        }
        if(count($deleteBasketItems) > intval(0)) {
            foreach ($basket as $basketItem) {
                if(isset($deleteBasketItems[$basketItem->getId()])) {
                    $basketItem->delete();
                }
            }
        }
        //DELETE ITEMS

        $checkFields = [
            'QUANTITY', 'BASE_PRICE', 'PRICE',  'DISCOUNT_PRICE'
        ];

        //if(Config::checkFeature('setcurrency')) {
            //$checkFields[] = 'CURRENCY';
        //}

        if(Config::checkFeature('basketvatrate')) {
            $checkFields[] = 'VAT_RATE';
            $checkFields[] = 'VAT_INCLUDED';
        }

        $productList = []; //use for cache

        if(count($uniqMsBasket) > intval(0)) {
            foreach ($uniqMsBasket as $xmlId => $msBasketItems) {
                foreach($msBasketItems as $key => $msBasketItem) {

                    $currentBasketItem = null;
                    if(!isset($productList[$xmlId])) {
                        $product = \CRbsMoyskladHelper::getProductBxParamsByXmlId($xmlId);
                        if(!empty($product['ID'])) {
                                $productList[$xmlId] = $product;
                        }
                    } else {
                        $product = $productList[$xmlId];
                    }

                    if ($isHardAdd && !empty($product['ID'])) {
                        $qtyCount = (float)$product['QUANTITY'] - (float)$msBasketItem['QUANTITY'];
                        if (
                            $product['CAN_BUY_ZERO'] === 'N' &&
                            ($product['AVAILABLE'] === 'N' || $qtyCount <= intval(0)) &&
                            $product['MODULE_ID'] === 'catalog' &&
                            !isset($needBackCanBuyZero[$product['ID']])
                        ) {
                            $needBackCanBuyZero[$product['ID']] = $product['ID'];
                            \Bitrix\Catalog\ProductTable::update($product['ID'], [
                                'CAN_BUY_ZERO' => 'Y'
                            ]);
                        }
                    }

                    if (!isset($uniqBxBasket[$xmlId][$key])) {

                        if (empty($product['ID'])) {
                            if ($isAllAdd) {
                                $product = [
                                    'ID' => $xmlId,
                                    'XML_ID' => $xmlId,
                                    'NAME' => $msBasketItem['NAME'],
                                    'WEIGHT' => intval(0),
                                    'QUANTITY' => (float)$msBasketItem['QUANTITY'],
                                    'MODULE_ID' => Config::getModuleId(true)
                                ];
                            }
                        }

                        if(!empty($product['MODULE_ID']) && !empty($product['ID'])) {

                            $currentBasketItem = $basket->createItem($product['MODULE_ID'], $product['ID']);

                            $arSetNewBasketItemFields = [
                                'PRODUCT_XML_ID' => $xmlId,
                                'NAME' => $product['NAME'],
                                'WEIGHT' => (float)$product['WEIGHT'] > intval(0) ? (float)$product['WEIGHT'] : intval(0),
                                'LID' => $siteId,
                                'CURRENCY' =>  $currency,
                                'PRODUCT_PROVIDER_CLASS' => Config::getOption('basket_provider_class', 'NEW') === 'NEW' ? '\Bitrix\Catalog\Product\CatalogProvider' : 'CCatalogProductProvider'
                            ];

                            if (
                                $providerOff ||
                                $product['MODULE_ID'] !== 'catalog' ||
                                isset($needBackCanBuyZero[$product['ID']])
                            ) {
                                if (isset($arSetNewBasketItemFields['PRODUCT_PROVIDER_CLASS'])) {
                                    unset($arSetNewBasketItemFields['PRODUCT_PROVIDER_CLASS']);
                                }
                            }

                            $currentBasketItem->setFields($arSetNewBasketItemFields);
                        }
                    } else {
                        foreach ($basket as $basketItem) {
                            if($uniqBxBasket[$xmlId][$key]['ID'] == $basketItem->getId()) {
                                $currentBasketItem = $basketItem;
                                break; 
                            }
                        }
                    }

                    if($currentBasketItem instanceof \Bitrix\Sale\BasketItem) {
                        foreach($checkFields as $fieldId) {
                            switch($fieldId) {
                                case 'VAT_RATE':
                                    if ((float)$currentBasketItem->getField($fieldId) * 100 !== (float)$msBasketItem[$fieldId]) {
                                        $currentBasketItem->setField($fieldId, (float)($msBasketItem[$fieldId] / 100));
                                    }
                                break;
                                case 'QUANTITY':
                                    if((float)$currentBasketItem->getField($fieldId) !== (float)$msBasketItem[$fieldId]) {
                                        $currentBasketItem->setField($fieldId, (float)$msBasketItem[$fieldId]);
                                    }
                                break;
                                case 'VAT_INCLUDED':
                                //case 'CURRENCY':
                                    if ((string)$currentBasketItem->getField($fieldId) !== (string)$msBasketItem[$fieldId]) {
                                        $currentBasketItem->setField($fieldId, (string)$msBasketItem[$fieldId]);
                                    }
                                break;
                                case 'PRICE':
                                    if ((float)$currentBasketItem->getField($fieldId) !== (float)($msBasketItem[$fieldId] / 100)) {
                                        $currentBasketItem->setField('CUSTOM_PRICE', 'Y');
                                        $currentBasketItem->setField($fieldId, (float)$msBasketItem[$fieldId] / 100);
                                    } else if($currentBasketItem->getField('CUSTOM_PRICE' === 'Y' && (float)$msBasketItem['DISCOUNT_VALUE'] == intval(0))) {
                                        $currentBasketItem->setField($fieldId, (float)$msBasketItem[$fieldId] / 100);
                                        $currentBasketItem->setField('CUSTOM_PRICE', 'N');
                                    }
                                break;
                                case 'BASE_PRICE':
                                case 'DISCOUNT_PRICE':
                                    if ((float)$currentBasketItem->getField($fieldId) !== (float)($msBasketItem[$fieldId] / 100)) {
                                        $currentBasketItem->setField($fieldId, (float)$msBasketItem[$fieldId] / 100);
                                    }
                            }
                        }
                    }
                }
            }
        }

        unset($productList);
    }
}