<?php

    use Ipol\Robokassa\RobokassaTwoStagePayment;

    require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

    $request = \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();

    \Bitrix\Main\Loader::includeModule('sale');
    \Bitrix\Main\Loader::includeModule('ipol.robokassa');

    if(!empty($request::getInput()))
    {
        RobokassaTwoStagePayment::processResult2Request(
            $request::getInput(),
            intval($request->getQuery('ORDER_ID')),
            intval($request->getQuery('PAYMENT_ID'))
        );
    }