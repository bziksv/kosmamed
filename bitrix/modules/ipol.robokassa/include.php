<?php

    \Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

    CModule::AddAutoloadClasses(
        'ipol.robokassa',
        [
            Ipol\Robokassa\Internals\TwoStagePaymentTable::class => 'lib/Internals/TwoStagePaymentTable.php',
            Ipol\Robokassa\Internals\RefundOrderTable::class => 'lib/Internals/RefundOrderTable.php',
            Ipol\Robokassa\RobokassaTwoStagePayment::class => 'lib/RobokassaTwoStagePayment.php',
            Ipol\Robokassa\RobokassaWidget::class => 'lib/RobokassaWidget.php',
            Ipol\Robokassa\RobokassaRefund::class => 'lib/RobokassaRefund.php',
            Ipol\Robokassa\RobokassaPaymentService::class => 'lib/RobokassaPaymentService.php',
            Ipol\Robokassa\Internals\OrderTable::class => 'lib/Internals/OrderTable.php',
            Ipol\Robokassa\Internals\BasketItemTable::class => 'lib/Internals/BasketItemTable.php',
            Ipol\Robokassa\Start\Order::class => 'lib/Start/Order.php',
            Ipol\Robokassa\Start\Basket::class => 'lib/Start/Basket.php',
            Ipol\Robokassa\Start\Product::class => 'lib/Start/Product.php',
            Ipol\Robokassa\Start\Configuration::class => 'lib/Start/Configuration.php',
            Ipol\Robokassa\Start\Payment::class => 'lib/Start/Payment.php',
            Ipol\Robokassa\Options::class => 'lib/Options.php',
        ]
    );