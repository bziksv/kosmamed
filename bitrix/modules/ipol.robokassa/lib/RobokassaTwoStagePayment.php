<?php

    namespace Ipol\Robokassa;

    use Bitrix\Main\Config\Option;
    use Bitrix\Main\Localization\Loc;
    use Bitrix\Sale;
    use CJSCore;
    use Ipol\Robokassa;

    class RobokassaTwoStagePayment
    {
        public static function init()
        {

            if(Option::get(Robokassa\RobokassaPaymentService::$moduleId, 'USE_TWO_STAGE_PAYMENT', 'N') !== 'Y')
            {
                return [];
            }

            return [
                "TABSET" => self::class,
                "GetTabs" => [self::class, "tabConfiguration"],
                "ShowTab" => [self::class, "tabContent"],
            ];
        }

        /**
         * @param $arArgs
         * @return array[]
         */
        public static function tabConfiguration($arArgs): array
        {
            return [
                [
                    "DIV" => "robokassa-hold",
                    "TAB" => \Bitrix\Main\Localization\Loc::getMessage('IPOL_ROBOKASSA_SALE_VIEW_TAB_TWO_STAGE_PAYMENT_LABEL'),
                    "ICON" => "sale",
                    "TITLE" => \Bitrix\Main\Localization\Loc::getMessage('IPOL_ROBOKASSA_SALE_VIEW_TAB_TWO_STAGE_PAYMENT_TITLE'),
                    "SORT" => 1
                ]
            ];
        }

        /**
         * @param $divName
         * @param $arArgs
         * @param $bVarsFromForm
         * @return void
         */
        public static function tabContent($divName, $arArgs, $bVarsFromForm)
        {
            if ($divName == "robokassa-hold")
            {

                \Bitrix\Main\Loader::includeModule('order');

                /** @var Sale\Order $order */
                $order = Sale\Order::load($arArgs['ID']);

                /** @var array{payment: Sale\Payment[], twoStagePayment:array}[]  $orderPayments */
                $orderPayments = [];

                $hasPayments = false;
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
                foreach($order->getPaymentCollection() as $payment)
                {

                    if($payment->isInner())
                    {
                        continue;
                    }

                    if(!\in_array($payment->getPaymentSystemId(), $paymentSystems))
                    {
                        continue;
                    }

                    $twoStagePayment = Robokassa\Internals\TwoStagePaymentTable::findOneByPayment($payment);

                    if(empty($twoStagePayment))
                    {
                        $hasPayments = true;
                        continue;
                    }

                    $orderPayments[] = ['payment' => $payment, 'twoStagePayment' => $twoStagePayment];
                }

                if(empty($orderPayments) && $hasPayments)
                {
                    echo '
                    <tr>
                        <td colspan="2">
                            '.\Bitrix\Main\Localization\Loc::getMessage('IPOL_ROBOKASSA_SALE_VIEW_TAB_TWO_STAGE_PAYMENT_CLIENT_NO_START_PAYMENT').'
                        </td>
                    </tr>
                ';
                }

                if(empty($orderPayments) && !$hasPayments)
                {
                    echo '
                    <tr>
                        <td colspan="2">
                            '.\Bitrix\Main\Localization\Loc::getMessage('IPOL_ROBOKASSA_SALE_VIEW_TAB_TWO_STAGE_PAYMENT_EMPTY_PAYMENTS').'
                        </td>
                    </tr>
                ';
                }

                if(!empty($orderPayments)):?>

                    <?php
                    CJSCore::Init(['jquery3', 'window']);
                    ?>

                    <script>
                        $(document).ready(
                            function()
                            {
                                $('.js-accept-payment[data-order-id][data-payment-id]').click(
                                    function()
                                    {

                                        if(!confirm('<?=Loc::getMessage('IPOL_ROBOKASSA_SALE_VIEW_TAB_TWO_STAGE_PAYMENT_CLIENT_CONFIRM_ACCEPT_PAYMENT');?>'))
                                        {
                                            return;
                                        }

                                        BX.ajax
                                            .runAction(
                                                'ipol:robokassa.api.TwoStagePayment.applyTwoStagePayment',
                                                {
                                                    method: 'post',
                                                    data: {
                                                        orderId: $(this).data('orderId'),
                                                        paymentId: $(this).data('paymentId')
                                                    }
                                                }
                                            )
                                            .then(
                                                /**
                                                 * success
                                                 * @param response
                                                 */
                                                function(response)
                                                {
                                                    document.location.reload();
                                                },
                                                /**
                                                 * errors
                                                 * @param response
                                                 */
                                                function(response)
                                                {

                                                    if(response['status'] === 'error')
                                                    {
                                                        new BX.CDialog(
                                                            {
                                                                title: '<?=Loc::getMessage('IPOL_ROBOKASSA_SALE_VIEW_TAB_TWO_STAGE_PAYMENT_CLIENT_POPUP_ERROR_TITLE');?>',
                                                                content: response['errors'][0]['message'],
                                                                height: 40,
                                                                width: 300,
                                                                resizable: false,
                                                                buttons: [
                                                                    BX.CDialog.prototype.btnClose
                                                                ]
                                                            }
                                                        ).Show();
                                                    }
                                                }
                                            )
                                        ;
                                    }
                                );

                                $('.js-cancel-payment[data-order-id][data-payment-id]').click(
                                    function()
                                    {

                                        if(!confirm('<?=Loc::getMessage('IPOL_ROBOKASSA_SALE_VIEW_TAB_TWO_STAGE_PAYMENT_CLIENT_CONFIRM_CANCEL_PAYMENT');?>'))
                                        {
                                            return;
                                        }

                                        BX.ajax
                                            .runAction(
                                                'ipol:robokassa.api.TwoStagePayment.cancelTwoStagePayment',
                                                {
                                                    method: 'post',
                                                    data: {
                                                        orderId: $(this).data('orderId'),
                                                        paymentId: $(this).data('paymentId')
                                                    }
                                                }
                                            )
                                            .then(
                                                /**
                                                 * success
                                                 * @param response
                                                 */
                                                function(response)
                                                {
                                                    document.location.reload();
                                                },
                                                /**
                                                 * errors
                                                 * @param response
                                                 */
                                                function(response)
                                                {

                                                    if(response['status'] === 'error')
                                                    {
                                                        new BX.CDialog(
                                                            {
                                                                title: '<?=Loc::getMessage('IPOL_ROBOKASSA_SALE_VIEW_TAB_TWO_STAGE_PAYMENT_CLIENT_POPUP_ERROR_TITLE');?>',
                                                                content: response['errors'][0]['message'],
                                                                height: 40,
                                                                width: 300,
                                                                resizable: false,
                                                                buttons: [
                                                                    BX.CDialog.prototype.btnClose
                                                                ]
                                                            }
                                                        ).Show();
                                                    }
                                                }
                                            )
                                        ;
                                    }
                                );
                            }
                        );
                    </script>

                    <?php

                    /**
                     * @param array{payment: Sale\Payment, twoStagePayment: array} $orderPayment
                     */
                    foreach ($orderPayments as $orderPayment):
                        ?>

                        <tr>
                            <td colspan="2">

                                <div class="adm-bus-component-content-container">
                                    <div class="adm-bus-pay-section">
                                        <div class="adm-bus-pay-section-title-container">
                                            <div class="adm-bus-pay-section-title" id="payment_<?=$orderPayment['payment']->getId();?>">

                                                <?=Loc::getMessage(
                                                    'SALE_ORDER_PAYMENT_BLOCK_EDIT_PAYMENT_TITLE',
                                                    array(
                                                        '#ID#' => $orderPayment['payment']->getId(),
                                                        '#DATE_BILL#' => $payment->getField('DATE_BILL')->format('d.m.Y H:i:s')
                                                    )
                                                );?>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="adm-bus-pay-section-content">
                                        <div class="adm-bus-section-container-section-content">
                                            <?php
                                            if(
                                                empty($orderPayment['twoStagePayment']['TWO_STAGE_PAYMENT_RESULT'])
                                                || $orderPayment['twoStagePayment']['TWO_STAGE_PAYMENT_RESULT'] === Robokassa\Internals\TwoStagePaymentTable::TWO_STAGE_PAYMENT_RESULT_NO_PAID
                                            ):?>
                                                <?=Loc::getMessage('IPOL_ROBOKASSA_SALE_VIEW_TAB_TWO_STAGE_PAYMENT_CLIENT_NO_PAYMENT');?>
                                            <?php else:?>

                                                <?php if($orderPayment['twoStagePayment']['TWO_STAGE_PAYMENT_RESULT'] === Robokassa\Internals\TwoStagePaymentTable::TWO_STAGE_PAYMENT_RESULT_PAID):?>

                                                    <?php if($orderPayment['twoStagePayment']['TWO_STAGE_PAYMENT_STATUS'] === Robokassa\Internals\TwoStagePaymentTable::TWO_STAGE_PAYMENT_STATUS_NONE):?>

                                                        <div class="ui-btn ui-btn-success js-accept-payment" data-order-id="<?=$order->getId();?>" data-payment-id="<?=$orderPayment['payment']->getId();?>">
                                                            <?=Loc::getMessage('IPOL_ROBOKASSA_SALE_VIEW_TAB_TWO_STAGE_PAYMENT_CLIENT_ACCEPT_PAYMENT');?>
                                                        </div>

                                                        <div class="ui-btn ui-btn-danger js-cancel-payment" data-order-id="<?=$order->getId();?>" data-payment-id="<?=$orderPayment['payment']->getId();?>">
                                                            <?=Loc::getMessage('IPOL_ROBOKASSA_SALE_VIEW_TAB_TWO_STAGE_PAYMENT_CLIENT_CANCEL_PAYMENT');?>
                                                        </div>
                                                    <?php else:?>

                                                        <?php if($orderPayment['twoStagePayment']['TWO_STAGE_PAYMENT_STATUS'] == Robokassa\Internals\TwoStagePaymentTable::TWO_STAGE_PAYMENT_STATUS_ACCEPT): ?>
                                                            <div class="ui-btn ui-btn-success">
                                                                <?php echo Loc::getMessage('IPOL_ROBOKASSA_SALE_VIEW_TAB_TWO_STAGE_PAYMENT_CLIENT_CONFIRM_ACCEPTED_PAYMENT');?>
                                                            </div>
                                                        <?php endif;?>
                                                        <?php if($orderPayment['twoStagePayment']['TWO_STAGE_PAYMENT_STATUS'] == Robokassa\Internals\TwoStagePaymentTable::TWO_STAGE_PAYMENT_STATUS_CANCELED): ?>
                                                            <div class="ui-btn ui-btn-danger">
                                                                <?php echo Loc::getMessage('IPOL_ROBOKASSA_SALE_VIEW_TAB_TWO_STAGE_PAYMENT_CLIENT_CONFIRM_CANCELED_PAYMENT');?>
                                                            </div>
                                                        <?php endif;?>
                                                    <?php endif;?>
                                                <?php endif;?>
                                            <?php endif;?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach;?>

                <?php endif;
            }
        }

        /**
         * Обработка resultUrl2
         * @param string $requestData
         * @param int $orderId
         * @param int $paymentId
         * @return void
         * @throws Exception
         */
        public static function processResult2Request(string $requestData, int $orderId, int $paymentId)
        {

            list($header, $content, $key) = \explode('.', $requestData);

            $paymentIdModificationCount = (int) Option::get(
                RobokassaPaymentService::$moduleId,
                'IPOL_ROBOKASSA_OPTIONS_TAB_PAYMENT_ID_MODIFICATION_COUNT',
                0
            );

            $fields = \json_decode(
                \base64_decode($content),
                true
            );

            if(empty($fields['data']['invId']))
            {
                throw new \Exception('wrong invId');
            }

            \Bitrix\Main\Loader::includeModule('sale');

            $order = Sale\Order::load($orderId);
            /** @var Sale\Payment $payment */
            foreach($order->getPaymentCollection() as $payment)
            {

                if($payment->getId() == $paymentId)
                {

                    $twoStagePayment = Robokassa\Internals\TwoStagePaymentTable::findOneByPayment($payment);

                    if(empty($twoStagePayment))
                    {

                        $result = Robokassa\Internals\TwoStagePaymentTable::add(
                            [
                                'ORDER_ID' => $orderId,
                                'PAYMENT_ID' => $paymentId,
                            ]
                        );

                        if(!$result->isSuccess())
                        {
                            throw new \Exception(
                                \print_r($result->getErrorMessages(), true)
                            );
                        }

                        $twoStagePayment = Robokassa\Internals\TwoStagePaymentTable::findOneByPayment($payment);
                    }

                    if(
                        !empty($fields['data']['state'])
                        && \in_array(
                            $fields['data']['state'],
                            [
                                'OK',
                                'HOLD',
                            ]
                        )
                    )
                    {
                        Robokassa\Internals\TwoStagePaymentTable::update(
                            $twoStagePayment['ID'],
                            [
                                'TWO_STAGE_PAYMENT_RESULT' => Robokassa\Internals\TwoStagePaymentTable::TWO_STAGE_PAYMENT_RESULT_PAID
                            ]
                        );

                        $orderStatus = Option::get(Robokassa\RobokassaPaymentService::$moduleId, 'TAB_TWO_STAGE_PAYMENT_STATUS_ID', null);

                        if(!empty($orderStatus))
                        {
                            $order->setField('STATUS_ID', $orderStatus);
                            $order->save();
                        }
                    }
                }
            }
        }
    }