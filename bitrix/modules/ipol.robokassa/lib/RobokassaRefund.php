<?php

    namespace Ipol\Robokassa;

    use Bitrix\Main;
    use Bitrix\Main\Config\Option;
    use Bitrix\Sale;

    /**
     * Class RobokassaRefund
     * @package Ipol\Robokassa
     */
    final class RobokassaRefund
    {

        public const TIMEOUT = 30;

        public static string $opStateExtUrl = 'https://auth.robokassa.ru/Merchant/WebService/Service.asmx/OpStateExt';

        /**
         * Инициация возврата по операции
         * @var string
         */
        public static string $refundUrl = 'https://services.robokassa.ru/RefundService/Refund/Create';

        /**
         * @param Sale\Payment $payment
         * @return void
         */
        public static function refundPayment(Sale\Payment $payment): void
        {

            $httpClient = new Main\Web\HttpClient(
                [
                    "redirect" => true,
                    "redirectMax" => 5,
                    "waitResponse" => true,
                    "socketTimeout" => self::TIMEOUT,
                    "streamTimeout" => self::TIMEOUT,
                    "disableSslVerification" => true,
                ]
            );

            Main\Localization\Loc::loadLanguageFile(__FILE__);

            $paymentIdModificationCount = (int) Main\Config\Option::get(
                RobokassaPaymentService::$moduleId,
                'IPOL_ROBOKASSA_OPTIONS_TAB_PAYMENT_ID_MODIFICATION_COUNT',
                0
            );

            $enablePaymentByOrderId = Option::get(
                RobokassaPaymentService::$moduleId,
                'ENABLE_PAYMENT_BY_ORDER_ID',
                'N'
            ) === 'Y';

            if($payment->isInner())
            {
                return;
            }

            if($payment->getPaySystem()->getField('ACTION_FILE') !== 'robokassapayment')
            {
                return;
            }

            if(!$payment->isPaid())
            {
                return;
            }

            $order = $payment->getOrder();

            /** @var array|null $refund */
            $refund = Internals\RefundOrderTable::getList(
                [
                    'filter' => [
                        'ORDER_ID' => $order->getId(),
                        'PAYMENT_ID' => $payment->getId(),
                    ],
                    'limit' => 1,
                ]
            )->fetch();

            if(empty($refund))
            {
                $result = Internals\RefundOrderTable::add(
                    [
                        'ORDER_ID' => $order->getId(),
                        'PAYMENT_ID' => $payment->getId(),
                        'PRICE' => $payment->getSumPaid() ?? $payment->getSum(),
                    ]
                );

                if(!$result->isSuccess())
                {
                    throw new Main\SystemException(
                        \implode(
                            ', ',
                            $result->getErrorMessages()
                        )
                    );
                }

                /** @var array|null $refund */
                $refund = Internals\RefundOrderTable::getList(
                    [
                        'filter' => [
                            'ORDER_ID' => $order->getId(),
                            'PAYMENT_ID' => $payment->getId(),
                        ],
                        'limit' => 1,
                    ]
                )->fetch();
            }

            /** @var array $params */
            $params = $payment->getPaySystem()->getParamsBusValue($payment);

            if(empty($refund['OPKEY']))
            {

                $request = [
                    'MerchantLogin' => $params['SHOPLOGIN'],
                    'InvoiceID' => $payment->getId() + $paymentIdModificationCount,
                ];

                if($enablePaymentByOrderId)
                {
                    $request['InvoiceID'] = $payment->getOrderId() + $paymentIdModificationCount;
                }

                $request['Signature'] = \implode(
                    ':',
                    [
                        $request['MerchantLogin'],
                        $request['InvoiceID'],
                        ($params['PS_IS_TEST'] === 'N' ? $params['SHOPPASSWORD2'] : $params['SHOPPASSWORD_TEST2'])
                    ]
                );

                $request['Signature'] = \md5($request['Signature']);

                $httpClient->query(
                    Main\Web\HttpClient::HTTP_GET,
                    self::$opStateExtUrl
                    . '?'
                    . \http_build_query($request)
                );

                require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/xml.php");

                $xml = new \CDataXML();
                $xml->LoadString($httpClient->getResult());

                if (
                    !empty($xml->GetTree()->elementsByName('Result')[0])
                    && !empty($xml->GetTree()->elementsByName('Result')[0]->elementsByName('Description')[0])
                    && !empty($xml->GetTree()->elementsByName('Result')[0]->elementsByName('Description')[0]->content)
                )
                {
                    throw new Main\SystemException(
                        $xml->GetTree()->elementsByName('Result')[0]->elementsByName('Description')[0]->content
                    );
                }

                if (!empty($xml->GetTree()->elementsByName('Info')[0]->elementsByName('OpKey')[0]->content))
                {
                    $refund['OPKEY'] = $xml->GetTree()->elementsByName('Info')[0]->elementsByName('OpKey')[0]->content;
                }

                if(empty($refund['OPKEY']))
                {
                    throw new Main\SystemException('fail get opkey');
                }
            }

            if($refund['REFUND_STATUS'] === 'Y')
            {
                return;
            }

            $fields = [
                'header' => [
                    'alg' => 'HS256',
                    'typ' => 'JWT',
                ],
                'payload' => [
                    'OpKey' => $refund['OPKEY'],
                    'InvoiceItems' => self::loadReceiptData($payment)
                ],
            ];

            $event = new \Bitrix\Main\Event(
                \Ipol\Robokassa\Event\Events::MODULE_ID,
                \Ipol\Robokassa\Event\Events::AFTER_FORMAT_REFUND_DATA,
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

            $jwtContent = RobokassaRefundJWT::encode(
                $fields['payload'],
                $params['SHOPPASSWORD3'],
                $fields['header']['alg']
            );

            $curl = \curl_init();

            \curl_setopt_array(
                $curl,
                [
                    \CURLOPT_URL => self::$refundUrl,
                    \CURLOPT_TIMEOUT => self::TIMEOUT,
                    \CURLOPT_POST => true,
                    \CURLOPT_POSTFIELDS => RobokassaRefundJWT::content($jwtContent),
                    \CURLOPT_RETURNTRANSFER => true,
                    \CURLOPT_HTTPHEADER => array(
                        'Content-Type: application/json'
                    )
                ]
            );

            $response = \curl_exec($curl);

            if(!$response)
            {
                throw new Main\SystemException('fail post jwt data');
            }

            $responseFields = Main\Web\Json::decode($response);
            \curl_close($curl);

            $refund = [
                ...$refund,
                ...[
                    'TYPE' => Internals\RefundOrderTable::REFUND_TYPE_FULL,
                    'REFUND_DATE' => new Main\Type\DateTime(),
                    'REFUND_STATUS' => (int) $responseFields['success'] ?? 0 === 1 ? 'Y' : 'N',
                    'REFUND_MESSAGE' => $responseFields['message'],
                    'REFUND_REQUEST_ID' => $responseFields['requestId'],
                ],
            ];

            Internals\RefundOrderTable::update($refund['ID'], $refund);

            if($refund['REFUND_STATUS'] === 'N')
            {
                \Bitrix\Sale\PaySystem\Logger::addDebugInfo(
                    Main\Localization\Loc::getMessage(
                        'ROBOKASSA_LIB_REFUND_LOG_MESSAGE',
                        [
                            '#PAYMENT_ID#' => $payment->getId() + $paymentIdModificationCount,
                            '#MESSAGE#' => $refund['REFUND_MESSAGE'],
                        ]
                    ),
                );

                \CSaleOrderChange::Add(
                    [
                        "ORDER_ID" => $order->getId(),
                        "TYPE" => "PAYMENT_UPDATE",
                        "DATA" => \serialize(
                            [
                                'MESSAGE' => Main\Localization\Loc::getMessage(
                                    'ROBOKASSA_LIB_REFUND_LOG_MESSAGE',
                                    [
                                        '#PAYMENT_ID#' => $payment->getId() + $paymentIdModificationCount,
                                        '#MESSAGE#' => $refund['REFUND_MESSAGE'],
                                    ]
                                )
                            ]
                        ),
                        "USER_ID" => $GLOBALS['USER']->getId() ?? null,
                        "ENTITY" => 'PAYMENT',
                        "ENTITY_ID" => $payment->getId(),
                    ]
                );

                return;
            }

            if($refund['REFUND_STATUS'] === 'Y')
            {

                $payment->setFields(
                    [
                        'IS_RETURN' => Sale\Payment::RETURN_PS,
                        'PAID' => 'N',
                        'EMP_RETURN_ID' => $GLOBALS['USER']?->GetId() ?? null,
                        'PAY_RETURN_DATE' => new Main\Type\Date(),
                    ]
                );

                \CSaleOrderChange::Add(
                    [
                        "ORDER_ID" => $order->getId(),
                        "TYPE" => "PAYMENT_PAID",
                        "DATA" => \serialize(
                            [
                                'PAID' => 'N',
                                'IS_RETURN' => Sale\Payment::RETURN_PS,
                                'ID' => $payment->getId(),
                                'PAY_SYSTEM_NAME' => $payment->getPaymentSystemName(),
                            ]
                        ),
                        "USER_ID" => $GLOBALS['USER']->getId() ?? null,
                        "ENTITY" => 'PAYMENT',
                        "ENTITY_ID" => $payment->getId(),
                    ]
                );
            }
        }

        /**
         * Возврат всех оплат по заказу
         *
         * @param Sale\Order $order
         * @return void
         *
         * @throws Main\ArgumentException
         * @throws Main\ArgumentNullException
         * @throws Main\ArgumentOutOfRangeException
         * @throws Main\ArgumentTypeException
         * @throws Main\ObjectException
         * @throws Main\ObjectPropertyException
         * @throws Main\SystemException
         */
        public static function refundOrder(Sale\Order $order): void
        {

            /** @var Sale\Payment $payment */
            foreach ($order->getPaymentCollection() as $payment)
            {

                if($payment->isInner())
                {
                    continue;
                }

                if($payment->getPaySystem()->getField('ACTION_FILE') !== 'robokassapayment')
                {
                    continue;
                }

                if(!$payment->isPaid())
                {
                    continue;
                }

                self::refundPayment($payment);
            }

            $order->save();
        }

        /**
         * Обработка события смены статуса для возврата оплаты
         *
         * @param Main\Event $event
         */
        public static function saleOrderStatusChangeEvent(Main\Event $event)
        {

            Main\Loader::includeModule('sale');

            try
            {

                $jsonString = \htmlspecialcharsback(
                    Main\Config\Option::get(
                        RobokassaPaymentService::$moduleId,
                        'REFUND_PAYMENT_STATUSES',
                        null
                    )
                );

                $refundPaymentStatuses = \json_decode($jsonString, true, 512, \JSON_THROW_ON_ERROR);

                if(!\is_array($refundPaymentStatuses) || \json_last_error() !== \JSON_ERROR_NONE)
                {
                    $refundPaymentStatuses = [];
                }
            }
            catch(\Exception $e)
            {
                $refundPaymentStatuses = [];
            }

            /** @var \Bitrix\Sale\Order $order */
            $order = $event->getParameter("ENTITY");


            if(
                empty($refundPaymentStatuses[$order->getSiteId()])
                || !\in_array(
                    $order->getField("STATUS_ID"),
                    $refundPaymentStatuses[$order->getSiteId()] ?? []
                )
            )
            {
                return;
            }

            try
            {
                self::refundOrder($order);
            }
            catch (Main\SystemException $exception)
            {
                return $event->addResult(
                    new Main\EventResult(
                        Main\EventResult::ERROR,
                        Sale\ResultError::create(
                            new Main\Error($exception->getMessage())
                        ),
                        'sale'
                    )
                );
            }
        }

        /**
         * Формирование полного чека из заказа
         *
         * @param Sale\Payment $payment
         *
         * @return array
         *
         * @throws Main\ArgumentNullException
         * @throws Main\SystemException
         */
        public static function loadReceiptData(Sale\Payment $payment): array
        {

            $businessValues = RobokassaPaymentService::getPaymentBusinessValues($payment);

            $items = RobokassaPaymentService::formReceiptData(
                $payment,
                $payment->getSum(),
                null,
                $businessValues['COUNTRY_CODE'],
                $businessValues,
            );

            return \array_map(
                function ($item)
                {
                    return [
                        'Name' => $item['name'],
                        'Quantity' => $item['quantity'],
                        'Cost' => $item['cost'],
                        'Tax' => $item['tax'],
                        'PaymentMethod' => $item['payment_method'],
                        'PaymentObject' => $item['payment_object'],
                    ];
                },
                $items
            );
        }
    }