<?php
namespace Ipolh\SDEK\Bitrix\Handler;

use Ipolh\SDEK\Bitrix\Tools;

class goodsPicker
{
    /**
     * Add marking codes to basket goods data
     * @param array $arGoods array of basket goods
     * @param int $orderId Bitrix order id
     */
    public static function addGoodsQRs(&$arGoods, $bitrixId)
    {
        if (Tools::isConverted()) {
            $isMarkingAvailable = method_exists('\\Bitrix\\Sale\\ShipmentItemStore', 'getMarkingCode');
            $order = \Bitrix\Sale\Order::load($bitrixId);

            $shipments = $order->getShipmentCollection();
            foreach ($shipments as $shipment) {
                $items = $shipment->getShipmentItemCollection();
                foreach ($items as $item) {
                    /** @var \Bitrix\Sale\BasketItem $basketItem */
                    $basketItem = $item->getBasketItem();
                    $stores     = $item->getShipmentItemStoreCollection();
                    foreach ($stores as $store) {
                        /** @var \Bitrix\Sale\ShipmentItemStore $store */
                        $mark = ($isMarkingAvailable) ? $store->getMarkingCode() : '';

                        foreach ($arGoods as $key => $stuff) {
                            if ((int)$arGoods[$key]['PRODUCT_ID'] === $basketItem->getProductId()) {
                                if (!array_key_exists('QR', $arGoods[$key])) {
                                    $arGoods[$key]['QR'] = array();
                                }
                                $arGoods[$key]['QR'] [] = $mark;
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Add property values to basket goods data
     * @param array $goods array of basket goods
     * @param string[] $propertyCodes array of IBlock element property codes
     */
    public static function addBasketGoodProperties(&$goods, $propertyCodes)
    {
        if (\CModule::IncludeModule('iblock')) {
            $itemsProperties = [];
            $itemsToIblocks  = [];
            $itemsIds        = [];
            $offersToParents = [];
            $propertyCodes   = array_values(array_filter($propertyCodes));

            foreach ($goods as $good) {
                // Search for iblock required
                $itemsIds[] = $good['PRODUCT_ID'];

                // Already know where they are
                if ($parent = \CCatalogSku::GetProductInfo($good['PRODUCT_ID'])) {
                    $itemsToIblocks[$parent['IBLOCK_ID']][$parent['ID']]['SKU_CHILDS'][] = $good['PRODUCT_ID'];
                    $offersToParents[$good['PRODUCT_ID']] = $parent['ID'];
                }
            }

            $elementsDB = \CIBlockElement::GetList([], ['=ID' => $itemsIds], false, false, ['ID', 'IBLOCK_ID']);
            while ($tmp = $elementsDB->fetch()) {
                $itemsToIblocks[$tmp['IBLOCK_ID']][$tmp['ID']] = [];

                if (array_key_exists($tmp['ID'], $offersToParents)) {
                    $itemsToIblocks[$tmp['IBLOCK_ID']][$tmp['ID']]['SKU_PARENT'] = $offersToParents[$tmp['ID']];
                }
            }
            unset($elementsDB);

            // Collect property values for all elements
            foreach ($itemsToIblocks as $iblockId => $elements) {
                $propsData = self::getElementPropertyValues($iblockId, array_keys($elements), $propertyCodes);

                foreach ($elements as $elementId => $data) {
                    $itemsProperties[$elementId] = $itemsToIblocks[$iblockId][$elementId];

                    if (!empty($propsData) && is_array($propsData[$elementId])) {
                        $itemsProperties[$elementId]['PROPERTIES'] = $propsData[$elementId];
                    }
                }
            }
            unset($itemsToIblocks);

            // Assign property values
            foreach ($goods as $key => $arGood) {
                $goods[$key]['PROPERTIES'] = [];

                $hasOwnProps = is_array($itemsProperties[$arGood['PRODUCT_ID']]['PROPERTIES']);
                foreach ($propertyCodes as $propertyCode) {
                    // Take own property value first
                    $goods[$key]['PROPERTIES'][$propertyCode] = ($hasOwnProps && array_key_exists($propertyCode, $itemsProperties[$arGood['PRODUCT_ID']]['PROPERTIES'])) ?
                        $itemsProperties[$arGood['PRODUCT_ID']]['PROPERTIES'][$propertyCode] : '';

                    // Try SKU parent property if own property are empty and parent exists
                    if (empty($goods[$key]['PROPERTIES'][$propertyCode]) && array_key_exists('SKU_PARENT', $itemsProperties[$arGood['PRODUCT_ID']])) {
                        $parentId = $itemsProperties[$arGood['PRODUCT_ID']]['SKU_PARENT'];
                        if (is_array($itemsProperties[$parentId]['PROPERTIES']) && array_key_exists($propertyCode, $itemsProperties[$parentId]['PROPERTIES'])) {
                            $goods[$key]['PROPERTIES'][$propertyCode] = $itemsProperties[$parentId]['PROPERTIES'][$propertyCode];
                        }
                    }
                }
            }
        }
    }

    /**
     * Get iblock element property values
     * @param int $iblockId IBlock Id
     * @param int[] $elementIds array of element Ids
     * @param string[] $propertyCodes array of IBlock element property codes
     * @return array
     */
    public static function getElementPropertyValues($iblockId, $elementIds, $propertyCodes)
    {
        $result = [];

        if (\CModule::IncludeModule('iblock')) {
            $propertyResult = array_fill_keys($elementIds, ['PROPERTIES' => []]);
            $filter         = ['=ID' => $elementIds];
            $propertyFilter = ['CODE' => $propertyCodes];
            $options        = ['USE_PROPERTY_ID' => 'N', 'GET_RAW_DATA' => 'Y', 'PROPERTY_FIELDS' => ['DEFAULT_VALUE', 'MULTIPLE']];

            \CIBlockElement::GetPropertyValuesArray($propertyResult, (int)$iblockId, $filter, $propertyFilter, $options);

            foreach ($propertyResult as $elementId => $elementData) {
                if (!empty($elementData['PROPERTIES'])) {
                    foreach ($propertyCodes as $propertyCode) {
                        $result[$elementId][$propertyCode] = (array_key_exists($propertyCode, $propertyResult[$elementId]['PROPERTIES'])) ?
                            $propertyResult[$elementId]['PROPERTIES'][$propertyCode]['VALUE'] : '';

                        // Take first value if prop are multiple (normally no multiple props supported cause API handle only scalar values)
                        if ($propertyResult[$elementId]['PROPERTIES'][$propertyCode]['MULTIPLE'] === 'Y' && !empty($result[$elementId][$propertyCode])) {
                            $result[$elementId][$propertyCode] = array_shift($result[$elementId][$propertyCode]);
                        }
                    }
                }
            }
        }

        return $result;
    }
}