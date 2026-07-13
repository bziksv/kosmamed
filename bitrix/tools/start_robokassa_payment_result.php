<?php

    require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

    global $APPLICATION;

    \Bitrix\Main\Loader::includeModule('ipol.robokassa');
    $request = \Bitrix\Main\HttpApplication::getInstance()->getContext()->getRequest();

    $APPLICATION->IncludeComponent(
        'ipol:robokassa.start.payment.result',
        '',
        array(
            'REQUEST' => $request->getPostList()
        )
    );