<?php

    namespace Ipol\Robokassa;

    use Bitrix\Catalog\CatalogIblockTable;
    use Bitrix\Main\Config\Option;
    use Bitrix\Main;
    use Bitrix\Main\Loader;
    use Bitrix\Sale;


    /**
     * Класс-помощник для работы с формированием запроса к робокассе
     * с поддержкой ФЗ-54
     */
    final class RobokassaPaymentService
    {

        const NO_VAT = 'none';
        const VAT_0 = 'vat0';
        const VAT_5 = 'vat5';
        const VAT_7 = 'vat7';
        const VAT_10 = 'vat10';
        const VAT_18 = 'vat18';
        const VAT_20 = 'vat20';
        const VAT_22 = 'vat22';
        const VAT_105 = 'vat105';
        const VAT_107= 'vat107';
        const VAT_110 = 'vat110';
        const VAT_120 = 'vat120';
        const VAT_122 = 'vat122';
        const VAT_8 = 'vat8';
        const VAT_12 = 'vat12';

        const SECOND_CHECK_URL = 'https://ws.roboxchange.com/RoboFiscal/Receipt/Attach';

        static $moduleId = 'ipol.robokassa';

        /**
         * Возвращает ID модуля
         * @return string
         */
        public static function getModuleId()
        {
            return self::$moduleId;
        }

        /**
         * Генерация блока Receipt
         * @param Sale\Payment $payment
         * @param integer $paymentShouldPay
         * @param \Sale\Handlers\PaySystem\RobokassaPaymentHandler $handler
         * @return array
         * @throws \Bitrix\Main\ArgumentNullException
         */
        public static function formReceiptData($payment, $paymentShouldPay, $handler = null, $country = 'RU', array $paymentBusinessValues = [])
        {

            $useProductObjectType = Option::get(self::$moduleId, 'IPOL_ROBOKASSA_OPTIONS_ENABLE_PROPERTY_PRODUCT_OBJECT_TYPE', 'N') === 'Y';

            if($useProductObjectType)
            {
                Loader::includeModule('iblock');
                Loader::includeModule('catalog');
            }

            $items = array();

            $shipmentCollection =
                $payment->getCollection()->getOrder()->getShipmentCollection();

            //получаем настройки платежки

            if($country === 'RU')
            {
                $paymentMethod = 'full_prepayment';
                $paymentObject = 'commodity';
            }

            if(!empty($handler))
            {
                $paymentMethod = $handler->getPaymentMethod($payment);
                $paymentObject = $handler->getPaymentObject($payment);
                $paymentObjectDelivery = $handler->getPaymentObjectDelivery($payment);
            }

            if(empty($handler) && !empty($paymentBusinessValues))
            {
                $paymentMethod = $paymentBusinessValues['PAYMENT_METHOD'] ?? 'full_prepayment';
                $paymentObject = $paymentBusinessValues['PAYMENT_OBJECT'] ?? 'commodity';
                $paymentObjectDelivery = $paymentBusinessValues['PAYMENT_OBJECT_DELIVERY'] ?? 'commodity';
            }

            foreach ($shipmentCollection as $shipmentItem) {

                $shipmentItemColletion = $shipmentItem->getShipmentItemCollection();

                foreach ($shipmentItemColletion as $elem) {

                    $basketItem = $elem->getBasketItem();

                    if ($basketItem->isBundleChild()) {
                        continue;
                    }

                    if (!$basketItem->getFinalPrice()) {
                        continue;
                    }

                    $item = [
                        'name' => (function_exists('mb_substr') && defined("BX_UTF")) ? mb_substr($basketItem->getField('NAME'), 0, 128) : substr($basketItem->getField('NAME'), 0, 128),
                        'quantity' => $elem->getQuantity(),
                        'sum' => Sale\PriceMaths::roundPrecision($basketItem->getFinalPrice()),
                        'tax' => self::getProductTax($basketItem, $country),
                        'cost' => Sale\PriceMaths::roundPrecision($basketItem->getFinalPrice() / $elem->getQuantity()),
                    ];

                    if($country === 'RU')
                    {

                        $item['payment_method'] = $paymentMethod;
                        $item['payment_object'] = $paymentObject;

                        if($useProductObjectType)
                        {

                            $element = \CIBlockElement::GetByID($basketItem->getProductId())->GetNextElement();
                            $objectTypeProperty = $element->GetProperties(
                                [],
                                [
                                    'CODE' => \Ipol\Robokassa\Options::IBLOCK_PROPERTY_PAYMENT_TYPE_CODE
                                ]
                            )[\Ipol\Robokassa\Options::IBLOCK_PROPERTY_PAYMENT_TYPE_CODE];

                            if(!empty($objectTypeProperty['VALUE_XML_ID']))
                            {
                                $item['payment_object'] = $objectTypeProperty['VALUE_XML_ID'];
                            }
                            else
                            {
                                $iblock = CatalogIblockTable::getList(
                                    [
                                        'filter' => [
                                            'IBLOCK_ID' => $element->GetFields()['IBLOCK_ID']
                                        ]
                                    ]
                                )->fetch();

                                if(!empty($iblock['PRODUCT_IBLOCK_ID']) && !empty($iblock['SKU_PROPERTY_ID']))
                                {
                                    $cmlProperty = $element->GetProperties(
                                        [],
                                        [
                                            'ID' => $iblock['SKU_PROPERTY_ID']
                                        ]
                                    );

                                    if(!empty(current($cmlProperty)['VALUE']))
                                    {

                                        $element = \CIBlockElement::GetByID(current($cmlProperty)['VALUE'])->GetNextElement();
                                        $objectTypeProperty = $element->GetProperties(
                                            [],
                                            [
                                                'CODE' => \Ipol\Robokassa\Options::IBLOCK_PROPERTY_PAYMENT_TYPE_CODE
                                            ]
                                        )[\Ipol\Robokassa\Options::IBLOCK_PROPERTY_PAYMENT_TYPE_CODE];

                                        if(!empty($objectTypeProperty['VALUE_XML_ID']))
                                        {
                                            $item['payment_object'] = $objectTypeProperty['VALUE_XML_ID'];
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if(Option::get('ipol.robokassa', 'EXPLODE_RECIPIENT_PRODUCT_BY_QUANTITY', 'N') === 'Y')
                    {
                        for($i = 0; $i < $elem->getQuantity(); $i++)
                        {
                            $items[] = \array_merge(
                                $item,
                                [
                                    'quantity' => 1,
                                    'sum' => $item['cost']
                                ]
                            );
                        }
                    }
                    else
                    {
                        $items[] = $item;
                    }
                }

                if (!$shipmentItem->isSystem() && $shipmentItem->getPrice())
                {

                    $item = [
                        'name' => (function_exists('mb_substr') && defined("BX_UTF")) ? mb_substr($shipmentItem->getDeliveryName(), 0, 128) : substr($shipmentItem->getDeliveryName(), 0, 128),
                        'quantity' => 1,
                        'sum' => Sale\PriceMaths::roundPrecision($shipmentItem->getPrice()),
                        'tax' => self::getShipmentTax($shipmentItem, $country),
                    ];

                    if($country === 'RU')
                    {
                        $item['cost'] = Sale\PriceMaths::roundPrecision($shipmentItem->getPrice());
                        $item['payment_method'] = $paymentMethod;
                        $item['payment_object'] = $paymentObjectDelivery;
                    }

                    $items[] = $item;
                }
            }

            $event = new \Bitrix\Main\Event(
                \Ipol\Robokassa\Event\Events::MODULE_ID,
                \Ipol\Robokassa\Event\Events::AFTER_FORMAT_RECEIPT_ITEMS_DATA,
                [
                    'items' => $items,
                    'payment' => $payment,
                    'country' => ($country = 'RU'),
                    'paymentBusinessValues' => $paymentBusinessValues
                ]
            );

            $event->send();

            if ($event->getResults())
            {
                foreach($event->getResults() as $evenResult)
                {
                    if($evenResult->getType() === \Bitrix\Main\EventResult::SUCCESS)
                    {
                        $items = $evenResult->getParameters();
                    }
                }
            }

            return self::sumCorrection($items, $paymentShouldPay);
        }

        /**
         * Конвертация налоговой ставки товара в понятный робокассе
         * @param  Sale\BasketItem $basketItem
         * @return string
         */
        public static function getProductTax($basketItem, $country)
        {
            if (\Bitrix\Main\Loader::includeModule('catalog')) {
                $result = \CCatalogProduct::GetVATInfo($basketItem->getProductId());
                $bitrixVat = $result->Fetch();
                return self::toRobokassaTax($bitrixVat, $country);
            }
            return self::NO_VAT;
        }

        /**
         * Конвертация налоговой ставки доставки в понятный робокассе
         * @param  Sale\Shipment $shipmentItem
         * @return string
         */
        public static function getShipmentTax($shipmentItem, $country)
        {

            $delivery = Sale\Delivery\Services\Manager::getById(
                $shipmentItem->getDeliveryId()
            );

            if(is_null($delivery['VAT_ID'])){
                return self::NO_VAT;
            }

            $bitrixVat = \CCatalogVat::GetByID($delivery['VAT_ID'])->Fetch();
            return self::toRobokassaTax($bitrixVat, $country);
        }

        /**
         * Конвертация налоговой ставки в понятный робокассе строковой
         * @param CCatalogVat $bitrixVat
         * @return string
         */
        public static function toRobokassaTax($bitrixVat, $country = 'RU')
        {

            $replace0Vat = Option::get(self::$moduleId, 'REPLACE_0_VAT', '') === 'Y';
            $replace5Vat = Option::get(self::$moduleId, 'REPLACE_5_VAT', '') === 'Y';
            $replace7Vat = Option::get(self::$moduleId, 'REPLACE_7_VAT', '') === 'Y';
            $replace10Vat = Option::get(self::$moduleId, 'REPLACE_10_VAT', '') === 'Y';
            $replace20Vat = Option::get(self::$moduleId, 'REPLACE_20_VAT', '') === 'Y';
            $replace22Vat = Option::get(self::$moduleId, 'REPLACE_22_VAT', '') === 'Y';

            if ($bitrixVat['NAME'] == GetMessage('ROBOKASSA.NO_NDS')){
                $convertedVat = self::NO_VAT;
            } else {
                $rate = intval($bitrixVat['RATE']);
                switch ($rate) {

                    case 0:
                        $convertedVat = $replace0Vat ? self::NO_VAT : self::VAT_0;
                        break;

                    case 5:
                        $convertedVat = $replace5Vat ? self::VAT_105 : self::VAT_5;
                        break;

                    case 7:
                        $convertedVat = $replace7Vat ? self::VAT_107 : self::VAT_7;
                        break;

                    case 10:
                        $convertedVat = $replace10Vat ? self::VAT_110 : self::VAT_10;
                        break;

                    case 18:
                    case 20:
                        $convertedVat = $replace20Vat ? self::VAT_120 : self::VAT_20;
                        break;

                    case 22:
                        $convertedVat = $replace22Vat ? self::VAT_122 : self::VAT_22;
                        break;

                    case 8:
                        $convertedVat = self::VAT_8;
                        break;
                    case 12:
                        $convertedVat = self::VAT_12;
                        break;

                    default:
                        $convertedVat = self::NO_VAT;
                        break;
                }
            }
            return $convertedVat;
        }

        /**
         * Сверка сумм и пересчет если нужно
         * @param  array $items
         * @param  float|int $paymentShouldPay
         * @return array
         */
        public static function sumCorrection($items, $paymentShouldPay)
        {
            $result = array();
            $totalSum = 0;
            foreach($items as $item){
                $totalSum += $item['sum'];
            }

            if (abs($totalSum - $paymentShouldPay) > 0.0001) {
                $roundedSum = 0;


                $rate = $paymentShouldPay / $totalSum;
                $lastIndex = count($items) - 1;

                foreach ($items as $key => $item)
                {
                    if ($key == $lastIndex)
                    {
                        $roundedValue = round($paymentShouldPay - $roundedSum, 2);
                        $item['sum'] = number_format($roundedValue, '2', '.', '');
                        $item['cost'] = number_format($roundedValue / $item['quantity'], '2', '.', '');
                        $result[]= $item;
                    }
                    else
                    {
                        $roundedValue = round($item['sum'] * $rate, 2);
                        $item['sum'] = number_format($roundedValue, '2', '.', '');
                        $item['cost'] = number_format($roundedValue / $item['quantity'], '2', '.', '');
                        $roundedSum += $roundedValue;
                        $result[] = $item;
                    }
                }
            } else {
                $result = $items;
            }
            return $result;
        }

        /**
         * Подготовка строки перед кодированием в base64
         * @param $string
         * @return string
         */
        protected static function formatSignReplace($string)
        {
            return \strtr(
                $string,
                [
                    '+' => '-',
                    '/' => '_',
                ]
            );
        }

        /**
         * Подготовка строки после кодирования в base64
         * @param $string
         * @return string
         */
        protected static function formatSignFinish($string)
        {
            return \preg_replace('/^(.*?)(=*)$/', '$1', $string);
        }

        /**
         * Отправка 2‑го чека
         */
        public static function sendSecondCheck(int $orderId, string $orderStatus)
        {

            if(
                Option::get(
                    self::getModuleId(),
                    'SECOND_CHECK_STATUS_ID',
                    ''
                ) !== $orderStatus
            )
            {
                return;
            }

            \Bitrix\Main\Loader::includeModule('sale');

            /** @var Sale\Order $order */
            $order = Sale\Order::load($orderId);

            \Bitrix\Main\Loader::includeModule('iblock');

            $paymentIdModificationCount = (int) Option::get(
                self::$moduleId,
                'IPOL_ROBOKASSA_OPTIONS_TAB_PAYMENT_ID_MODIFICATION_COUNT',
                0
            );

            $enablePaymentByOrderId = Option::get(
                self::$moduleId,
                'ENABLE_PAYMENT_BY_ORDER_ID',
                'N'
            ) === 'Y';

            $useProductObjectType = Option::get(
                self::$moduleId,
                'IPOL_ROBOKASSA_OPTIONS_ENABLE_PROPERTY_PRODUCT_OBJECT_TYPE',
                'N'
            ) === 'Y';

            if($useProductObjectType)
            {
                \Bitrix\Main\Loader::includeModule('catalog');
            }

            /** @var Sale\Payment $payment */
            foreach ($order->getPaymentCollection() as $payment)
            {

                if(
                    $payment->getPaySystem()->getField('ACTION_FILE') === 'robokassapayment'
                    && $payment->isPaid()
                )
                {

                    /** @var array $params */
                    $params = $payment->getPaySystem()->getParamsBusValue($payment);

                    /** @var array $fields */
                    $fields = [
                        'merchantId' => $params['SHOPLOGIN'],
                        'id' => $payment->getId() + 1 + $paymentIdModificationCount,
                        'originId' => $payment->getId() + $paymentIdModificationCount,
                        'operation' => 'sell',
                        'sno' => $params['SNO'],
                        'url' => \urlencode('http://' . $_SERVER['HTTP_HOST']),
                        'total' => $payment->getSum(),
                        'items' => [],
                        'client' => [
                            'email' => $order->getPropertyCollection()->getUserEmail()->getValue(),
                            'phone' => $order->getPropertyCollection()->getPhone()->getValue(),
                        ],
                        'payments' => [
                            [
                                'type' => 2,
                                'sum' => $payment->getSum()
                            ]
                        ],
                        'vats' => []
                    ];

                    if($enablePaymentByOrderId)
                    {
                        $fields['id'] = $payment->getOrderId() + 1 + $paymentIdModificationCount;
                        $fields['originId'] = $payment->getOrderId() + $paymentIdModificationCount;
                    }

                    /** @var Sale\Shipment $shipment */
                    foreach($order->getShipmentCollection() as $shipment)
                    {

                        if (!$shipment->isSystem() && $shipment->getPrice())
                        {
                            $fields['items'][] = [
                                'name' => (function_exists('mb_substr') && defined("BX_UTF")) ? mb_substr($shipment->getDeliveryName(), 0, 128) : substr($shipment->getDeliveryName(), 0, 128),
                                'quantity' => 1,
                                'sum' => Sale\PriceMaths::roundPrecision($shipment->getPrice()),
                                'tax' => self::getShipmentTax($shipment, $params['COUNTRY_CODE']),
                                'payment_method' => 'full_payment',
                                'payment_object' => $params['PAYMENT_OBJECT_DELIVERY'],
                            ];
                        }

                        /** @var Sale\ShipmentItem $shipmentItem */
                        foreach($shipment->getShipmentItemCollection() as $shipmentItem)
                        {

                            $basketItem = $shipmentItem->getBasketItem();
                            $productTax = self::getProductTax($basketItem, $params['COUNTRY_CODE']);

                            $price = $basketItem->getFinalPrice() / $basketItem->getQuantity();

                            if($shipmentItem->getShipmentItemStoreCollection()?->count() < $basketItem->getQuantity())
                            {
                                $paymentObject = $params['PAYMENT_OBJECT'];

                                if($useProductObjectType)
                                {

                                    $element = \CIBlockElement::GetByID($basketItem->getProductId())->GetNextElement();
                                    $objectTypeProperty = $element->GetProperties(
                                        [],
                                        [
                                            'CODE' => \Ipol\Robokassa\Options::IBLOCK_PROPERTY_PAYMENT_TYPE_CODE
                                        ]
                                    )[\Ipol\Robokassa\Options::IBLOCK_PROPERTY_PAYMENT_TYPE_CODE];

                                    if(!empty($objectTypeProperty['VALUE_XML_ID']))
                                    {
                                        $paymentObject = $objectTypeProperty['VALUE_XML_ID'];
                                    }
                                    else
                                    {
                                        $iblock = CatalogIblockTable::getList(
                                            [
                                                'filter' => [
                                                    'IBLOCK_ID' => $element->GetFields()['IBLOCK_ID']
                                                ]
                                            ]
                                        )->fetch();

                                        if(!empty($iblock['PRODUCT_IBLOCK_ID']) && !empty($iblock['SKU_PROPERTY_ID']))
                                        {
                                            $cmlProperty = $element->GetProperties(
                                                [],
                                                [
                                                    'ID' => $iblock['SKU_PROPERTY_ID']
                                                ]
                                            );

                                            if(!empty(current($cmlProperty)['VALUE']))
                                            {

                                                $element = \CIBlockElement::GetByID(current($cmlProperty)['VALUE'])->GetNextElement();
                                                $objectTypeProperty = $element->GetProperties(
                                                    [],
                                                    [
                                                        'CODE' => \Ipol\Robokassa\Options::IBLOCK_PROPERTY_PAYMENT_TYPE_CODE
                                                    ]
                                                )[\Ipol\Robokassa\Options::IBLOCK_PROPERTY_PAYMENT_TYPE_CODE];

                                                if(!empty($objectTypeProperty['VALUE_XML_ID']))
                                                {
                                                    $paymentObject = $objectTypeProperty['VALUE_XML_ID'];
                                                }
                                            }
                                        }
                                    }
                                }

                                $fields['items'][] = [
                                    'name' => self::formatSignReplace(
                                        (function_exists('mb_substr') && defined("BX_UTF"))
                                            ? mb_substr($basketItem->getField('NAME'), 0, 128)
                                            : substr($basketItem->getField('NAME'), 0, 128)
                                        ,
                                    ),
                                    'quantity' => $basketItem->getQuantity() - $shipmentItem->getShipmentItemStoreCollection()->count(),
                                    'sum' => Sale\PriceMaths::roundPrecision($price * ($basketItem->getQuantity() - $shipmentItem->getShipmentItemStoreCollection()->count())),
                                    'tax' => $productTax,
                                    'payment_method' => 'full_payment',
                                    'payment_object' => $paymentObject,
                                ];

                                switch ($productTax)
                                {

                                    case self::VAT_0:
                                    case self::NO_VAT:
                                        $fields['vats'][] = ['type' => $productTax, 'sum' => 0];
                                        break;

                                    default:
                                        $fields['vats'][] = ['type' => self::NO_VAT, 'sum' => 0];
                                        break;

                                    case self::VAT_5:
                                    case self::VAT_7:
                                    case self::VAT_10:
                                    case self::VAT_18:
                                    case self::VAT_20:
                                    case self::VAT_22:
                                        $fields['vats'][] = ['type' => $productTax, 'sum' => $basketItem->getVat()];
                                        break;
                                }
                            }

                            /** @var Sale\ShipmentItemStore $shipmentItemStore */
                            foreach($shipmentItem->getShipmentItemStoreCollection() as $shipmentItemStore)
                            {

                                $product = [
                                    'name' => self::formatSignReplace(
                                        (function_exists('mb_substr') && defined("BX_UTF"))
                                            ? mb_substr($basketItem->getField('NAME'), 0, 128)
                                            : substr($basketItem->getField('NAME'), 0, 128),
                                    ),
                                    'quantity' => $shipmentItemStore->getQuantity(),
                                    'sum' => Sale\PriceMaths::roundPrecision($price * $shipmentItemStore->getQuantity()),
                                    'tax' => $productTax,
                                    'payment_method' => 'full_payment',
                                    'payment_object' => $params['PAYMENT_OBJECT'],
                                ];

                                $storeFields = $shipmentItemStore->getFieldValues();

                                if(!empty($storeFields))
                                {
                                    $product['nomenclature_code'] = mb_convert_encoding($storeFields['MARKING_CODE'], 'UTF-8');
                                }

                                $fields['items'][] = $product;

                                switch ($productTax)
                                {

                                    case self::VAT_0:
                                    case self::NO_VAT:
                                        $fields['vats'][] = ['type' => $productTax, 'sum' => 0];
                                        break;

                                    default:
                                        $fields['vats'][] = ['type' => self::NO_VAT, 'sum' => 0];
                                        break;

                                    case self::VAT_5:
                                    case self::VAT_7:
                                    case self::VAT_10:
                                    case self::VAT_18:
                                    case self::VAT_20:
                                    case self::VAT_22:
                                        $fields['vats'][] = ['type' => $productTax, 'sum' => $basketItem->getVat()];
                                        break;
                                }
                            }
                        }
                    }

                    $event = new \Bitrix\Main\Event(
                        \Ipol\Robokassa\Event\Events::MODULE_ID,
                        \Ipol\Robokassa\Event\Events::AFTER_FORMAT_SECOND_CHECK_DATA,
                        [
                            'fields' => $fields,
                        ]
                    );

                    $event->send();

                    if ($event->getResults())
                    {
                        foreach($event->getResults() as $evenResult)
                        {
                            if($evenResult->getType() === \Bitrix\Main\EventResult::SUCCESS)
                            {
                                $fields = $evenResult->getParameters();
                            }
                        }
                    }

                    /** @var string $startupHash */
                    $startupHash = self::formatSignFinish(
                        \base64_encode(
                            !\defined('BX_UTF')
                                ? \Bitrix\Main\Web\Json::encode($fields)
                                : \json_encode($fields, \JSON_THROW_ON_ERROR)
                        )
                    );

                    /** @var string $sign */
                    $sign = self::formatSignFinish(
                        \base64_encode(
                            \md5(
                                $startupHash .
                                ($params['PS_IS_TEST'] === 'Y' ? $params['SHOPPASSWORD_TEST'] : $params['SHOPPASSWORD'])
                            )
                        )
                    );

                    $client = new \Bitrix\Main\Web\HttpClient();
                    $response = $client->post(self::SECOND_CHECK_URL, $startupHash . '.' . $sign);

                    $event = new \Bitrix\Main\Event(
                        \Ipol\Robokassa\Event\Events::MODULE_ID,
                        \Ipol\Robokassa\Event\Events::RESPONSE_SECOND_CHECK_DATA,
                        [
                            'response' => $response,
                        ]
                    );

                    $event->send();
                }
            }
        }

        /**
         * @param string $lid
         * @return mixed|null
         */
        public static function getAfterPaymentOrderStatus(string $lid)
        {

            try
            {
                $changeSiteStatus = \json_decode(
                    \htmlspecialcharsback(
                        Option::get(
                            self::getModuleId(),
                            'CHANGE_SITE_STATUS',
                            null
                        )
                    ),
                    true,
                    512,
                    JSON_THROW_ON_ERROR
                );

                if(!is_array($changeSiteStatus))
                {
                    return null;
                }

                return empty($changeSiteStatus[$lid]) ? null : $changeSiteStatus[$lid];
            }
            catch(\Exception $e)
            {
                return null;
            }
        }

        /**
         * @param Sale\Payment $payment
         * @return array
         * @throws Main\SystemException
         */
        public static function getPaymentBusinessValues(Sale\Payment $payment): array
        {
            $consumers = Sale\BusinessValue::getConsumers();
            $params = array();
            $consumer = 'PAYSYSTEM_' . $payment->getPaymentSystemId();

            if (is_array($consumers[$consumer]['CODES']) && $consumers[$consumer]['CODES'])
            {
                foreach ($consumers[$consumer]['CODES'] as $key => $val)
                {
                    $map = \Bitrix\Sale\BusinessValue::getMapping($key, $consumer, $payment->getOrder()->getPersonTypeId());
                    $params[$key] = (!empty($map)) ? $map['PROVIDER_VALUE'] : $val['VALUE'];
                }
            }

            return $params;
        }
    }