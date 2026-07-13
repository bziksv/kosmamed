<?php
namespace Rbs\Moysklad\Services;

use Rbs\Moysklad\Customerorder;
use Rbs\Moysklad\Config;
use Rbs\Moysklad\Utils;
use Rbs\Moysklad\LangMsg;
use Rbs\Moysklad\ApiNew;

class BasketUtils
{
    public static function createMsBasketSource(Customerorder &$customerOrder, bool &$errorsBasket) : array
    {
        $isModifSync = Config::checkFeature('basketmodifsync');
        $extCodeSource = Config::checkFeature('basketextcodessource');
        $vatIncluded = $customerOrder->{'order'}->{'vatIncluded'};
        $canDoublesPositions = Config::checkFeature('basketdoublesync');

        $orderPositions = $customerOrder->{'order'}->{'positions'};

        $arMsBasket = [];

        $limit = intval(100);
        $offset = intval(0);

        $currencyCode = \Bitrix\Currency\CurrencyManager::getBaseCurrency();
        if (Config::checkFeature('setcurrency')) {
            $currencyCode = $customerOrder->{'order'}->{'rate'}->{'currency'}->{'isoCode'};
        }

        do {

            $nextHref = false;

            if ($offset > intval(0)) {

                $positionsMs = ApiNew::get($orderPositions->{'meta'}->{'href'}, ['expand' => 'assortment', 'limit' => $limit, 'offset' => $offset]);

                if (Utils::has_errors($positionsMs)) {
                    $errorsBasket = true;
                    $customerOrder->addWarningMessage(LangMsg::get('ERROR_LOAD_ITEM', ['#ID#' => $orderPositions->{'meta'}->{'href'}]));
                    break;
                }

            } else {
                $positionsMs = $orderPositions;
            }

            if (!empty($positionsMs->{'meta'}->{'nextHref'})) {
                $offset += $limit;
                $nextHref = true;
            }

            if(Utils::array_exists($positionsMs)) {

                foreach ($positionsMs->{'rows'} as $key => $positionRow) {
                    $assortmentItem = $positionRow->{'assortment'};
                    if (!empty($assortmentItem->{'externalCode'})) {

                        $finalDiscountPrice = \CRbsMoyskladHelper::getPositionFinalPrice($positionRow);

                        if (!$extCodeSource && $isModifSync && $assortmentItem->{'meta'}->{'type'} === 'variant') {
                            $parentItem = ApiNew::get($assortmentItem->{'product'}->{'meta'}->{'href'}, [], 86400);
                            if (Utils::is_success($parentItem) && !empty($parentItem->{'externalCode'})) {
                                $assortmentItem->{'externalCode'} = $parentItem->{'externalCode'} . '#' . $assortmentItem->{'externalCode'};
                            }
                        }

                        $keyOfPosition = ($canDoublesPositions && $assortmentItem->{'externalCode'} !== 'ORDER_DELIVERY') ? $key : $assortmentItem->{'externalCode'};

                        $arMsBasket[$keyOfPosition] = [
                            'XML_ID' => $assortmentItem->{'externalCode'},
                            'NAME' => $assortmentItem->{'name'},
                            'BASE_PRICE' => (float)$positionRow->{'price'}, 
                            'PRICE' => (float)$finalDiscountPrice,
                            'DISCOUNT_PRICE' => (float)$positionRow->{'price'} - (float)$finalDiscountPrice,
                            'QUANTITY' => (float)$positionRow->{'quantity'},
                            'DISCOUNT_VALUE' => (float)$positionRow->{'discount'},
                            'VAT_RATE' => (float)$positionRow->{'vat'},
                            'VAT_INCLUDED' => $vatIncluded ? 'Y' : 'N',
                            'CURRENCY' => $currencyCode,
                            'WEIGHT' => !empty($assortmentItem->{'weight'}) ? $assortmentItem->{'weight'} : 0,
                            'RESERVE_QUANTITY' => (float)$positionRow->{'reserve'},
                        ];
                    }
                }

            }
            
        } while ($nextHref);

        Utils::send_bx_event(Config::getModuleId(true), 'OnCreateMsBasket', [
            'orderPositions' => $orderPositions,
            'arMsBasket' => $arMsBasket,
            'msOrder' => $customerOrder->{'order'}
        ], $arMsBasket);

        return $arMsBasket;
    }

    public static function createBxBasketSource(\Bitrix\Sale\Basket $basketItems) : array
    {
        $arBxBasket = [];

        $key = 0;
        foreach ($basketItems as $basketItem) {

            $currXmlId = \CRbsMoyskladHelper::getXmlIdFromBasketItem($basketItem);

            $arBxBasket[$key] = [
                'ID' => $basketItem->getId(),
                'XML_ID' => $currXmlId,
                'BASE_PRICE' => (float)$basketItem->getField('BASE_PRICE') * 100,
                'PRICE' => (float)$basketItem->getField('PRICE') * 100,
                'DISCOUNT_PRICE' => (float)$basketItem->getField('DISCOUNT_PRICE') * 100,
                'QUANTITY' => (float)$basketItem->getField('QUANTITY'),
                'VAT_RATE' => (float)$basketItem->getField('VAT_RATE') * 100,
                'VAT_INCLUDED' => $basketItem->getField('VAT_INCLUDED'),
                'RESERVE_QUANTITY' => Config::checkFeature('basketreservededit') ? (float)$basketItem->getField('QUANTITY') : (float)$basketItem->getField('RESERVE_QUANTITY')
            ];

            if($order = $basketItems->getOrder()) {
                $isCancelReserved = \CRbsMoyskladHelper::isCancelReservedBasket($order);
                if($isCancelReserved) {
                    $arBxBasket[$key]['RESERVE_QUANTITY'] = floatval(0);
                }
            }

            $key++;
        }

        return $arBxBasket;
    }

    public static function hasCancelReservedBasket(\Bitrix\Sale\Order $order): bool
    {
        $result = false;

        if (Config::checkFeature('cancelreserve')) {
            $result = (bool)$order->isCanceled();
        }

        if (!$result && Config::checkFeature('statuscancelreserve')) {
            $reserveOffWithStatusList = Config::getOptionArray('status_reserve', []);
            $result = Utils::is_count($reserveOffWithStatusList) && in_array($order->getField('STATUS_ID'), $reserveOffWithStatusList);
        }

        return $result;
    }

    public static function hasDiffBasketArrays(array $arMsBasket = [], array $arBxBasket = []): bool
    {
        if(isset($arMsBasket[Config::getDeliveryExternalCode()])) {
            unset($arMsBasket[Config::getDeliveryExternalCode()]);
        }

        $hasChanges = count($arBxBasket) !== count($arMsBasket);

        if (!$hasChanges) {

            $uniqBxBasket = [];
            foreach($arBxBasket as $item) {
                $uniqBxBasket[$item['XML_ID']][] = $item;
            }
            $uniqMsBasket = [];
            foreach ($arMsBasket as $item) {
                $uniqMsBasket[$item['XML_ID']][] = $item;
            }

            $hasChanges = count($uniqBxBasket) !== count($uniqMsBasket);
            if(!$hasChanges) {
                
                $checkedItemBasketFields = [
                    'PRICE', 'BASE_PRICE', 'DISCOUNT_PRICE', 'QUANTITY', 'RESERVE_QUANTITY'
                ];
                if(Config::checkFeature('basketvatrate')){
                    $checkedItemBasketFields[] = 'VAT_RATE';
                    //$checkedItemBasketFields[] = 'VAT_INCLUDED';
                }
                
                foreach($uniqBxBasket as $xmlId => $itemsBx) {
                    if(!$hasChanges) {
                        if (isset($uniqMsBasket[$xmlId])) {
                            $itemsMs = $uniqMsBasket[$xmlId];
                            if(count($itemsMs) === count($itemsBx)) {
                                if(count($itemsMs) > intval(1)){
                                    if(!Config::checkFeature('basketdoublesync')) {
                                        $hasChanges = true;
                                    } else {
                                        $hasChanges = true;
                                        //to do compate if can double positions
                                    }
                                } else {
                                    $itemBx = $itemsBx[intval(0)];
                                    $itemMs = $itemsMs[intval(0)];
                                    //$compareList = [];
                                    foreach($checkedItemBasketFields as $field) {
                                        /* $compareList[$xmlId][$field] = [
                                            'BX' =>  $itemBx[$field],
                                            'MS' => $itemMs[$field]
                                        ]; */
                                        if(!$hasChanges) {
                                            switch ($field) {
                                                case 'VAT_INCLUDED':
                                                    if ((string)$itemBx[$field] !== (string)$itemMs[$field]) {
                                                        $hasChanges = true;
                                                    }
                                                    break;
                                                default:
                                                    if ((float)$itemBx[$field] !== (float)$itemMs[$field]) {
                                                        $hasChanges = true;
                                                    }
                                            }
                                        } else {
                                            break;
                                        }
                                    }
                                    /* return $compareList; */
                                }
                            } else {
                                $hasChanges = true;
                            }
                        } else {
                            $hasChanges = true;
                        }
                    } else {
                        break;
                    }
                }
            }
        }

        return $hasChanges;
    }

    public static function hasDiffDeliveryPriceInOrders(\Bitrix\Sale\Order $order, object $orderMs): bool
    {
        $bxDeliveryPrice = (float)($order->getDeliveryPrice() * 100);
        $msDeliveryPrice = floatval(0);

        if(Utils::property_exists($orderMs, ['positions']) && Utils::array_exists($orderMs->{'positions'})) {
            foreach($orderMs->{'positions'}->{'rows'} as $position) {
                if(!empty($position->{'assortment'}->{'externalCode'})) {
                    if($position->{'assortment'}->{'externalCode'} === Config::getDeliveryExternalCode()) {
                        $msDeliveryPrice = \CRbsMoyskladHelper::getPositionFinalPrice($position);
                    }
                }
            }
        }

        return $bxDeliveryPrice !== $msDeliveryPrice;
    }

    public static function getMsPositionFinalPrice(object $position = null): float
    {
        $finalPrice = 0;

        if(Utils::property_exists($position, ['price']) && Utils::property_exists($position, ['quantity'])) {
            
            $price = (float)$position->{'price'};
            $qty = (float)$position->{'quantity'};
            $discount = (float)$position->{'discount'};

            $currentAllPrice = $price * $qty - (($price * $qty * ($discount / 100)));

            if ($qty > intval(0)) {
                $finalPrice = $currentAllPrice / $qty;
            } else {
                $finalPrice = $currentAllPrice;
            }
        }

        return (float)$finalPrice;
    }

    public static function getBxProductXmlIdFromBasketItem(\Bitrix\Sale\BasketItem $basketItem): string
    {
        $result = !empty($basketItem->getField('PRODUCT_XML_ID')) ? (string)$basketItem->getField('PRODUCT_XML_ID') : '';
        $needReadXmlIdFromIblock = Config::checkFeature('basketextcodessource') || empty($result);
        if ($needReadXmlIdFromIblock && (int)$basketItem->getField('PRODUCT_ID') > intval(0)) {
            $bitrixElement = \Bitrix\Iblock\ElementTable::getList([
                'filter' => ['=ID' => $basketItem->getField('PRODUCT_ID')],
                'select' => ['XML_ID'],
                'cache' => ['ttl' => 300]
            ])->fetch();
            if (!empty($bitrixElement['XML_ID'])) {
                $result = (string)$bitrixElement['XML_ID'];
            }
        }
        return $result;
    }
}