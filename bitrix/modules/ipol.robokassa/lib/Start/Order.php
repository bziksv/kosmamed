<?php

    namespace Ipol\Robokassa\Start;

    use Ipol\Robokassa\Internals;

    /**
     * Class Order
     * @package Ipol\Robokassa\Start
     */
    final class Order
    {

        /**
         * Создание заказа
         * 
         * @param array $fields
         * @return int
         *
         * @throws \Exception
         */
        public static function createOrder(array $fields): array
        {

            $basket = Basket::getInstance();

            if(empty($basket->list()))
            {
                throw new \RuntimeException('Корзина пуста');
            }

            $order = [
                'PAYED' => $fields['PAYED'] ?? false,
                'PRICE' => array_sum(
                    \array_map(
                        static function(array $basketItem)
                        {
                            return $basketItem['price'] * $basketItem['quantity'];
                        },
                        $basket->list()
                    )
                )
            ];

            if(!empty($fields['EMAIL']) && check_email($fields['EMAIL']))
            {
                $order['EMAIL'] = $fields['EMAIL'];
            }

            if(!empty($fields['NAME']))
            {
                $order['NAME'] = $fields['NAME'];
            }

            if(!empty($fields['PHONE']))
            {
                $order['PHONE'] = $fields['PHONE'];
            }

            $result = Internals\OrderTable::add($order);

            if(!$result->isSuccess())
            {
                throw new \RuntimeException(
                    \implode(
                        ', ',
                        $result->getErrorMessages()
                    )
                );
            }

            $createdOrderId = (int) $result->getId();

            foreach($basket->list() as $basketItem)
            {
                Internals\BasketItemTable::add(
                    [
                        'ORDER_ID' => $createdOrderId,
                        'PRODUCT_ID' => $basketItem['productId'],
                        'PRODUCT_NAME' => $basketItem['name'],
                        'PRICE' => $basketItem['price'],
                        'QUANTITY' => $basketItem['quantity'],
                    ]
                );
            }

            $orderModificationCount = (int) \Bitrix\Main\Config\Option::get(
                'ipol.robokassa',
                'START_FUNCTION_OPTIONS_PAYMENT_ID_MODIFICATION_COUNT',
                0
            );

            $modificationOrderId = $createdOrderId;

            if($orderModificationCount > 0)
            {
                $modificationOrderId = $createdOrderId +  $orderModificationCount;
            }

            Internals\OrderTable::update(
                $createdOrderId,
                [
                    'ORDER_ID' => $modificationOrderId
                ]
            );

            $basket->clear();

            $eventFields = [
                'ORDER_ID' => $modificationOrderId,
            ];

            if(!empty($order['EMAIL']) && check_email($order['EMAIL']))
            {
                $eventFields['CLIENT_EMAIL'] = $order['EMAIL'];
            }

            \CEvent::Send(
                'ROBOKASSA_SIMPLE_ORDER_NEW_ORDER',
                \SITE_ID,
                $eventFields
            );

            return [
                'ID' => $createdOrderId,
                'ORDER_ID' => $modificationOrderId
            ];
        }

        /**
         * Получение информации по заказу
         *
         * @param $orderId
         * @return array
         *
         * @throws \Bitrix\Main\ArgumentException
         * @throws \Bitrix\Main\ObjectPropertyException
         * @throws \Bitrix\Main\SystemException
         */
        public static function findOneOrderById($orderId): array
        {
            $order = \Ipol\Robokassa\Internals\OrderTable::getList(
                [
                    'filter' => [
                        'ID' => $orderId,
                    ],
                    'limit' => 1
                ]
            )->fetch();

            if(empty($order))
            {
                throw new \RuntimeException('ORDER_NOT_FOUND');
            }

            $order['BASKET'] = \Ipol\Robokassa\Internals\BasketItemTable::getList(
                [
                    'filter' => [
                        'ORDER_ID' => $order['ID'],
                    ]
                ]
            )->fetchAll();

            return $order;
        }

        /**
         * Получение информации по заказу
         *
         * @param $orderId
         * @return array
         *
         * @throws \Bitrix\Main\ArgumentException
         * @throws \Bitrix\Main\ObjectPropertyException
         * @throws \Bitrix\Main\SystemException
         */
        public static function findOneOrderByOrderId($orderId): array
        {
            $order = \Ipol\Robokassa\Internals\OrderTable::getList(
                [
                    'filter' => [
                        'ORDER_ID' => $orderId,
                    ],
                    'limit' => 1
                ]
            )->fetch();

            if(empty($order))
            {
                throw new \RuntimeException('ORDER_NOT_FOUND');
            }

            $order['BASKET'] = \Ipol\Robokassa\Internals\BasketItemTable::getList(
                [
                    'filter' => [
                        'ORDER_ID' => $order['ID'],
                    ]
                ]
            )->fetchAll();

            return $order;
        }

        /**
         * Оплата заказа
         *
         * @param int $orderId
         *
         * @return void
         */
        public static function paymentOrder(int $orderId)
        {

            $order = self::findOneOrderByOrderId($orderId);

            if($order['PAYED'] === 'Y')
            {
                throw new \RuntimeException('ORDER_IS_PAYED');
            }

            $order['PAYED'] = 'Y';
            $order['PAYED_DATE'] = new \Bitrix\Main\Type\DateTime();
            $order['UPDATED_AT'] = new \Bitrix\Main\Type\DateTime();

            \Ipol\Robokassa\Internals\OrderTable::update($order['ID'], $order);

            $eventFields = [
                'ORDER_ID' => $order['ORDER_ID'],
            ];

            if(!empty($order['EMAIL']) && check_email($order['EMAIL']))
            {
                $eventFields['CLIENT_EMAIL'] = $order['EMAIL'];
            }

            \CEvent::Send(
                'ROBOKASSA_SIMPLE_ORDER_PAYED',
                \SITE_ID,
                $eventFields
            );
        }
    }