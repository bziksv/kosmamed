<?php

    namespace Ipol\Robokassa\Start;

    use Bitrix\Main\ArgumentException;

    final class Payment
    {

        public const PAYMENT_URL = 'https://auth.robokassa.ru/Merchant/Index.aspx';

        /**
         * Создание полей оплаты
         *
         * @param array $order
         * @param array $options
         * @return array
         *
         * @throws ArgumentException
         * @throws \JsonException
         */
        public static function createPaymentFields(array $order, array $options): array
        {

            /** @var array $signatureParams */
            $signatureParams = [
                $options['SHOPLOGIN'],
                $order['PRICE'],
                $order['ORDER_ID']
            ];

            $receipt = [
                'items' => \array_map(
                    static function(array $basketItem) use ($options)
                    {
                        return [
                            'name' => (function_exists('mb_substr') && defined("BX_UTF")) ? mb_substr($basketItem['PRODUCT_NAME'], 0, 128) : substr($basketItem['PRODUCT_NAME'], 0, 128),
                            'quantity' => $basketItem['QUANTITY'],
                            'sum' => round($basketItem['QUANTITY'] * $basketItem['PRICE'], 2),
                            'tax' => $options['VAT'],
                            'cost' => round($basketItem['PRICE'], 2),
                            'payment_method' => $options['PAYMENT_METHOD'],
                            'payment_object' => $options['PAYMENT_OBJECT']
                        ];
                    },
                    $order['BASKET']
                ),
                'sno' => $options['SNO']
            ];

            /** @var string $receipt */
            $receiptEncode = !\defined('BX_UTF')
                ? \Bitrix\Main\Web\Json::encode($receipt)
                : \json_encode($receipt, \JSON_THROW_ON_ERROR)
            ;

            $signatureParams[] = $receiptEncode;

            $signatureParams[] = (($options['TEST'] ?? 'N') !== 'Y') ? $options['SHOPPASSWORD']: $options['TEST_SHOPPASSWORD'];
            $signatureParams[] = 'SHP_LABEL=official_bitrix';
            $signatureParams[] = 'SHP_ORDER_ID=' . $order['ORDER_ID'];
            $signatureParams[] = 'SHP_START=true';

            /** @var string $signatureValue */
            $signatureValue = md5(implode(':', $signatureParams));

            return [
                'URL' => self::PAYMENT_URL,
                'SIGNATURE_FILEDS' => $signatureParams,
                'SIGNATURE_VALUE' => $signatureValue,
                'RECEIPT' => urlencode($receiptEncode),
            ];
        }

        /**
         * Проверка подписи входящего запроса
         *
         * @param array $fields
         * @param array $options
         *
         * @return bool
         */
        public static function checkResultPaymentSignature(array $fields, array $options): bool
        {

            $hash = [
                $fields['OutSum'],
                $fields['InvId'],
                (($options['TEST'] ?? 'N') !== 'Y' ? $options['SHOPPASSWORD2'] : $options['TEST_SHOPPASSWORD2']),
                'SHP_LABEL=official_bitrix',
                'SHP_ORDER_ID=' . $fields['SHP_ORDER_ID'],
                'SHP_START=true',
            ];

            return \mb_strtoupper($fields['SignatureValue']) === \mb_strtoupper(\md5(\implode(':', $hash)));
        }
    }