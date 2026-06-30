<?php

    use Bitrix\Main\Localization\Loc;

    if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
    {
        die();
    }

    Loc::loadMessages(__FILE__);

    /**
     * @var array $arResult
     */
?>
<!DOCTYPE html>
<html>
<head>
    <title></title>
</head>
<body>

    <form action="<?=$arResult['PAYMENT_FIELDS']['URL'];?>" method="post" id="paymentForm">
        <input type="submit" />

        <input type="hidden" name="MrchLogin" value="<?=htmlspecialcharsbx($arResult['OPTIONS']['SHOPLOGIN']);?>">
        <input type="hidden" name="OutSum" value="<?=htmlspecialcharsbx($arResult['ORDER']['PRICE']);?>">
        <input type="hidden" name="InvId" value="<?=((int)$arResult['ORDER']['ID'] + (int) ($arResult['OPTIONS']['PAYMENT_ID_MODIFICATION_COUNT'] ?? 0));?>">
        <input type="hidden" name="Desc" value="<?=htmlspecialcharsbx(Loc::getMessage("IPOL_ROBOKASSA_START.PAYMENT.ORDER_DESC", ['#ORDER_ID#' => ((int)$arResult['ORDER']['ID'] + (int) ($arResult['OPTIONS']['PAYMENT_ID_MODIFICATION_COUNT'] ?? 0))]));?>">
        <input type="hidden" name="SignatureValue" value="<?=$arResult['PAYMENT_FIELDS']['SIGNATURE_VALUE'];?>">
        <input type="hidden" name="Email" value="<?=htmlspecialcharsbx($arResult['ORDER']['EMAIL'])?>">
        <input type="hidden" name="Receipt" value="<?=$arResult['PAYMENT_FIELDS']['RECEIPT']?>">

        <input type="hidden" name="SHP_ORDER_ID" value="<?=$arResult['ORDER']['ORDER_ID'];?>">
        <input type="hidden" name="SHP_LABEL" value="official_bitrix">
        <input type="hidden" name="SHP_START" value="true">

        <?php if($arResult['OPTIONS']['TEST'] === 'Y'):?>
            <input type="hidden" name="IsTest" value="1">
        <?php endif;?>
    </form>
    <script>
        document.getElementById('paymentForm').submit();
    </script>
</body>
</html>