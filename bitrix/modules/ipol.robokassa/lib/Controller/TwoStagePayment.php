<?php

    namespace Ipol\Robokassa\Controller;

    use Bitrix\Main;
    use Bitrix\Main\ArgumentException;
    use Bitrix\Main\Config\Option;
    use Bitrix\Sale;
    use Ipol\Robokassa;

    /**
     * Class TwoStagePayment
     * @package Ipol\Robokassa\Controller
     */
    final class TwoStagePayment  extends Main\Engine\Controller
    {

        public const ACCEPT_TWO_STAGE_PAYMENT_API_URL = 'https://auth.robokassa.ru/Merchant/Payment/Confirm';
        public const CANCEL_TWO_STAGE_PAYMENT_API_URL = 'https://auth.robokassa.ru/Merchant/Payment/Cancel';

        private bool $enablePaymentByOrderId;

        private int $paymentIdModificationCount;

        /**
         * @param Main\Request|null $request
         */
        public function __construct(Main\Request $request = null)
        {
            parent::__construct($request);

            try
            {
                Main\Loader::includeModule('sale');
            }
            catch (Main\LoaderException $e)
            {
                $this->errorCollection[] = new Main\Error($e->getMessage());
                return;
            }

            if($this->getRequest()->getRequestMethod() !== 'POST')
            {
                $this->errorCollection[] = new Main\Error('only post allowed');
                return;
            }

            $this->enablePaymentByOrderId = Option::get(
                Robokassa\RobokassaPaymentService::$moduleId,
                'ENABLE_PAYMENT_BY_ORDER_ID',
                'N'
            ) === 'Y';

            $this->paymentIdModificationCount = (int) Option::get(
                Robokassa\RobokassaPaymentService::$moduleId,
                'IPOL_ROBOKASSA_OPTIONS_TAB_PAYMENT_ID_MODIFICATION_COUNT',
                0
            );
        }

        /**
         * @param $orderId
         * @param $paymentId
         * @return Sale\Payment|null
         * @throws Main\ArgumentException
         */
        private function getPayment($orderId, $paymentId)
        {
            
            if($this->getCurrentUser() === null)
            {
                $this->errorCollection[] = new Main\Error(
                    Main\Localization\Loc::getMessage('IPOL_ROBOKASSA_SALE_VIEW_TAB_TWO_STAGE_PAYMENT_CLIENT_POPUP_ERROR_ONLY_AUTH')
                );
                return null;
            }

            if(!$this->getCurrentUser()->isAdmin())
            {
                $this->errorCollection[] = new Main\Error(
                    Main\Localization\Loc::getMessage('IPOL_ROBOKASSA_SALE_VIEW_TAB_TWO_STAGE_PAYMENT_CLIENT_POPUP_ERROR_ONLY_ADMIN')
                );
                return null;
            }

            try
            {
                
                $order = Sale\Order::load($orderId);

                $paymentSystems = [];
                
                $paySystems = Sale\PaySystem\Manager::getList(
                    [
                        'filter' => [
                            'ACTION_FILE' => 'robokassapayment'
                        ],
                        'select' => [
                            'PAY_SYSTEM_ID'
                        ]
                    ]
                )->fetchAll();

                foreach($paySystems as $paySystem)
                {
                    $paymentSystems[] = $paySystem['PAY_SYSTEM_ID'];
                }
                
                /** @var Sale\Payment $payment */
                foreach ($order->getPaymentCollection() as $payment)
                {
                    if(
                        $payment->getId() == $paymentId
                        && \in_array($payment->getPaymentSystemId(), $paymentSystems)
                    )
                    {
                        return $payment;
                    }
                }
            }
            catch (Main\ArgumentNullException $e)
            {
                $this->errorCollection[] = new Main\Error(
                    $e->getMessage()
                );
                return null;
            }

            $this->errorCollection[] = new Main\Error(
                Main\Localization\Loc::getMessage('IPOL_ROBOKASSA_SALE_VIEW_TAB_TWO_STAGE_PAYMENT_CLIENT_POPUP_ERROR_PAYMENT')
            );
            
            return null;
        }

        /**
         * Принятие оплаты при 2ух стадийном платеже
         * @return array|null
         * @throws ArgumentException
         */
        public function applyTwoStagePaymentAction(): ?array
        {
            $request = $this->getRequest();

            /** @var Sale\Payment|null $payment */
            $payment = $this->getPayment(
                (int) $request->getPost('orderId'),
                (int) $request->getPost('paymentId')
            );

            if(empty($payment))
            {
                return null;
            }

            $paymentBusinessValues = Robokassa\RobokassaPaymentService::getPaymentBusinessValues($payment);

            $curl = \curl_init(self::ACCEPT_TWO_STAGE_PAYMENT_API_URL);

            $signatureValue = [
                $paymentBusinessValues['SHOPLOGIN'],
                ($payment->getSum() - $payment->getSumPaid()),
            ];

            $fields = [
                'MerchantLogin' => $paymentBusinessValues['SHOPLOGIN'],
                'InvoiceID' => $payment->getId() + $this->paymentIdModificationCount,
                'OutSum' => ($payment->getSum() - $payment->getSumPaid()),
            ];

            if($this->enablePaymentByOrderId)
            {
                $fields['InvoiceID'] = $payment->getOrderId() + $this->paymentIdModificationCount;
                $signatureValue[] = $payment->getOrderId() + $this->paymentIdModificationCount;
            }
            else
            {
                $signatureValue[] = $payment->getId() + $this->paymentIdModificationCount;
            }

            $fields['Receipt'] = [
                'items' => Robokassa\RobokassaPaymentService::formReceiptData(
                    $payment,
                    $fields['OutSum'],
                    null,
                    $paymentBusinessValues['COUNTRY_CODE'],
                    $paymentBusinessValues
                )
            ];

            if($paymentBusinessValues['COUNTRY_CODE'] !== 'KZ')
            {
                $fields['Receipt']['sno'] = $paymentBusinessValues['SNO'];
            }

            $fields['Receipt'] = \Bitrix\Main\Web\Json::encode($fields['Receipt']);

            $signatureValue[] = $fields['Receipt'];
            $signatureValue[] = ($paymentBusinessValues['PS_IS_TEST'] === 'N' ? $paymentBusinessValues['SHOPPASSWORD'] : $paymentBusinessValues['SHOPPASSWORD_TEST']);

            $fields['SignatureValue'] = \md5(
                \implode(
                    ':',
                    $signatureValue
                )
            );

            \curl_setopt_array(
                $curl,
                [
                    CURLOPT_POST => 1,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_HEADER => false,
                    CURLOPT_POSTFIELDS => $fields
                ]
            );

            $response = \curl_exec($curl);
            \curl_close($curl);

            if(\preg_match('#success: true#', $response))
            {
                $entity = Robokassa\Internals\TwoStagePaymentTable::findOneByPayment($payment);

                if(!empty($entity))
                {
                    Robokassa\Internals\TwoStagePaymentTable::update(
                        $entity['ID'],
                        [
                            'TWO_STAGE_PAYMENT_STATUS' => Robokassa\Internals\TwoStagePaymentTable::TWO_STAGE_PAYMENT_STATUS_ACCEPT
                        ]
                    );
                }
            }

            return [];
        }

        /**
         * Отмена оплаты при 2ух стадийном платеже
         * @return array|null
         * @throws ArgumentException
         */
        public function cancelTwoStagePaymentAction(): ?array
        {
            $request = $this->getRequest();

            /** @var Sale\Payment|null $payment */
            $payment = $this->getPayment(
                (int) $request->getPost('orderId'),
                (int) $request->getPost('paymentId')
            );

            if(empty($payment))
            {
                return null;
            }

            $paymentBusinessValues = Robokassa\RobokassaPaymentService::getPaymentBusinessValues($payment);

            $curl = \curl_init(self::CANCEL_TWO_STAGE_PAYMENT_API_URL);

            $signatureValueString = \implode(
                ':',
                [
                    $paymentBusinessValues['SHOPLOGIN'],
                    null,
                    (($this->enablePaymentByOrderId ? $payment->getOrderId() : $payment->getId()) + $this->paymentIdModificationCount),
                    $paymentBusinessValues['PS_IS_TEST'] === 'N' ? $paymentBusinessValues['SHOPPASSWORD'] : $paymentBusinessValues['SHOPPASSWORD_TEST'],
                ]
            );

            $fields = [
                'MerchantLogin' => $paymentBusinessValues['SHOPLOGIN'],
                'InvoiceID' => $payment->getId() + $this->paymentIdModificationCount,
                'SignatureValue' => \md5($signatureValueString),
                'OutSum' => $payment->getSum() - $payment->getSumPaid(),
            ];

            if($this->enablePaymentByOrderId)
            {
                $fields['InvoiceID'] = $payment->getOrderId() + $this->paymentIdModificationCount;
            }

            \curl_setopt_array(
                $curl,
                [
                    CURLOPT_POST => 1,
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_HEADER => false,
                    CURLOPT_POSTFIELDS => $fields
                ]
            );

            $response = \curl_exec($curl);
            \curl_close($curl);

            if(\preg_match('#success: true#', $response))
            {

                $entity = Robokassa\Internals\TwoStagePaymentTable::findOneByPayment($payment);

                if(!empty($entity))
                {
                    Robokassa\Internals\TwoStagePaymentTable::update(
                        $entity['ID'],
                        [
                            'TWO_STAGE_PAYMENT_STATUS' => Robokassa\Internals\TwoStagePaymentTable::TWO_STAGE_PAYMENT_STATUS_CANCELED
                        ]
                    );
                }
            }

            return [];
        }
    }