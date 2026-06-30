<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?><?
use Bitrix\Main\Type\DateTime,
    Bitrix\Main\Localization\Loc;
include __DIR__.'/lang/ru/template.php';

Loc::loadMessages(__FILE__);

$ORDER_ID = intval($params['OrderNum']);
if (!is_array($arOrder))
    $arOrder = CSaleOrder::GetByID($ORDER_ID);

?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
    <title><?=$MESS['SCHET']?></title>
    <meta http-equiv="Content-Type" content="text/html; charset=<?=LANG_CHARSET?>">
    <style>
        table { border-collapse: collapse; }
        table.acc td { border: 1pt solid #000000; padding: 0pt 3pt; line-height: 21pt; }
        table.it td { border: 1pt solid #000000; padding: 0pt 3pt; }
        table.sign td { font-weight: bold; vertical-align: bottom; }
        table.header td { padding: 0pt; vertical-align: top; }
    </style>
</head>

<body style="margin: 0pt; padding: 0pt;"<? if ($_REQUEST['PRINT'] == 'Y') { ?> onload="setTimeout(window.print, 0);"<? } ?>>

<div style="margin: 0pt; ">

    <table class="header">
        <tr>
            <td style="padding-right: 5pt; padding-bottom: 5pt; width: 160px; ">
                <?=$params['qrB64Image']?>
            </td>
            <td style="padding-top: 10px;text-align: end;">
                <b><?=$params['Name']; ?></b><br>
                <? if ($params["SELLER_ADDRESS"]) { ?>
                    <b><?=$params["SELLER_ADDRESS"]; ?></b><br>
                <? } ?>
                <? if ($params["SELLER_PHONE"]) { ?>
                    <b><?=sprintf($MESS['PHONE'].": %s", $params["SELLER_PHONE"]); ?></b><br>
                <? } ?>
            </td>
        </tr>
    </table>

    <?

    $objDateTime = new DateTime();
    ?>
    <table class="acc 88" width="100%">
        <colgroup>
            <col width="29%">
            <col width="29%">
            <col width="10%">
            <col width="32%">
        </colgroup>
        <tr>
            <td>
                <? if ($params['PayeeINN']) { ?>
                    <?=sprintf($MESS['INN']." %s", $params['PayeeINN']); ?>
                <? } else { ?>
                    &nbsp;
                <? } ?>
            </td>
            <td>
                <? if ($params['KPP']) { ?>
                    <?=sprintf($MESS['KPP']." %s", $params['KPP']); ?>
                <? } else { ?>
                    &nbsp;
                <? } ?>
            </td>
            <td rowspan="2">
                <br>
                <br>
                <?=$MESS['SC']?>
            </td>
            <td rowspan="2">
                <br>
                <br>
                <?=$params['PersonalAcc']; ?>
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <?=$MESS['USER']?>
                <?=$params['Name']; ?>
            </td>
        </tr>
        <tr style="border-bottom: 1px solid #000">
            <td colspan="2">
                <?=$MESS['BANK']?>
                <?=$params['BankName']; ?>
            </td>
            <td>
                <?=$MESS['BIK']?>
                <!--                ??. ?-->
            </td>
            <td>
                <?=$params['BIC']; ?><br>
                <!--                --><?//=CSalePaySystemAction::GetParamValue("SELLER_KS", false); ?>
            </td>
        </tr>
    </table>

    <br>
    <br>

    <table width="100%">
        <colgroup>
            <col width="50%">
            <col width="0">
            <col width="50%">
        </colgroup>
        <tr>
            <td></td>
            <td style="font-size: 2em; font-weight: bold; text-align: center"><nobr><?=sprintf(
                        $MESS["SCHET"]." %s ".$MESS["OT"]." %s",
                        htmlspecialcharsbx($params['OrderNum']),
                        $objDateTime->format("d.m.Y")
                    ); ?></nobr></td>
            <td></td>
        </tr>

    </table>

    <br>

    <?

    $arBasketItems = CSalePaySystemAction::GetParamValue("BASKET_ITEMS", false);
    if(!is_array($arBasketItems))
    {
        $arBasketItems = array();
        $dbBasket = CSaleBasket::GetList(
            array("DATE_INSERT" => "ASC", "NAME" => "ASC"),
            array("ORDER_ID" => $ORDER_ID),
            false, false,
            array("ID", "PRICE", "CURRENCY", "QUANTITY", "NAME", "VAT_RATE", "MEASURE_NAME")
        );
        while ($arBasket = $dbBasket->Fetch())
        {
            // props in product basket
            $arProdProps = array();
            $dbBasketProps = CSaleBasket::GetPropsList(
                array("SORT" => "ASC", "ID" => "DESC"),
                array(
                    "BASKET_ID" => $arBasket["ID"],
                    "!CODE" => array("CATALOG.XML_ID", "PRODUCT.XML_ID")
                ),
                false,
                false,
                array("ID", "BASKET_ID", "NAME", "VALUE", "CODE", "SORT")
            );
            while ($arBasketProps = $dbBasketProps->GetNext())
            {
                if (!empty($arBasketProps) && $arBasketProps["VALUE"] != "")
                    $arProdProps[] = $arBasketProps;
            }
            $arBasket["PROPS"] = $arProdProps;
            $arBasketItems[] = $arBasket;
        }
    }

    if (!empty($arBasketItems))
    {
        $arCells = array();
        $arProps = array();

        $n = 0;
        $sum = 0.00;
        $vat = 0;
        foreach($arBasketItems as &$arBasket)
        {
            $productName = $arBasket["NAME"];
            if ($productName == "OrderDelivery")
                $productName = $MESS["DELIVERY"];
            else if ($productName == "OrderDiscount")
                $productName = $MESS["SALE"];

            $arCells[++$n] = array(
                1 => $n,
                htmlspecialcharsbx($productName),
                roundEx($arBasket["QUANTITY"], SALE_VALUE_PRECISION),
                $arBasket["MEASURE_NAME"] ? htmlspecialcharsbx($arBasket["MEASURE_NAME"]) : $MESS["SHT"],
                SaleFormatCurrency($arBasket["PRICE"], $arBasket["CURRENCY"], true),
                roundEx($arBasket["VAT_RATE"]*100, SALE_VALUE_PRECISION) . "%",
                SaleFormatCurrency(
                    $arBasket["PRICE"] * $arBasket["QUANTITY"],
                    $arBasket["CURRENCY"],
                    true
                )
            );

            if(isset($arBasket["PROPS"]))
            {
                $arProps[$n] = array();
                foreach ($arBasket["PROPS"] as $vv)
                    $arProps[$n][] = htmlspecialcharsbx(sprintf("%s: %s", $vv["NAME"], $vv["VALUE"]));
            }

            $sum += doubleval($arBasket["PRICE"] * $arBasket["QUANTITY"]);
            $vat = max($vat, $arBasket["VAT_RATE"]);
        }
        unset($arBasket);

        if (DoubleVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["PRICE_DELIVERY"]) > 0)
        {
            $arDelivery_tmp = CSaleDelivery::GetByID($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DELIVERY_ID"]);

            $sDeliveryItem = $MESS["DELIVERY"];
            if ($arDelivery_tmp["NAME"] <> '')
                $sDeliveryItem .= sprintf(" (%s)", $arDelivery_tmp["NAME"]);
            $arCells[++$n] = array(
                1 => $n,
                htmlspecialcharsbx($sDeliveryItem),
                1,
                '',
                SaleFormatCurrency(
                    $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["PRICE_DELIVERY"],
                    $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
                    true
                ),
                roundEx($vat*100, SALE_VALUE_PRECISION) . "%",
                SaleFormatCurrency(
                    $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["PRICE_DELIVERY"],
                    $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
                    true
                )
            );

            $sum += doubleval($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["PRICE_DELIVERY"]);
        }

        $items = $n;

        if ($sum < $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["PRICE"])
        {
            $arCells[++$n] = array(
                1 => null,
                null,
                null,
                null,
                null,
                $MESS['PLAT'],
                SaleFormatCurrency($sum, $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"], true)
            );
        }

        $taxes = false;
        $dbTaxList = CSaleOrderTax::GetList(
            array("APPLY_ORDER" => "ASC"),
            array("ORDER_ID" => $ORDER_ID)
        );

        while ($arTaxList = $dbTaxList->Fetch())
        {
            $taxes = true;

            $arCells[++$n] = array(
                1 => null,
                null,
                null,
                null,
                null,
                htmlspecialcharsbx(sprintf(
                    "%s%s%s:",
                    ($arTaxList["IS_IN_PRICE"] == "Y") ? $MESS["V_COUNT"] : "",
                    $arTaxList["TAX_NAME"],
                    ($vat <= 0 && $arTaxList["IS_PERCENT"] == "Y")
                        ? sprintf(' (%s%%)', roundEx($arTaxList["VALUE"],SALE_VALUE_PRECISION))
                        : ""
                )),
                SaleFormatCurrency(
                    $arTaxList["VALUE_MONEY"],
                    $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
                    true
                )
            );
        }

        if (!$taxes)
        {
            $arCells[++$n] = array(
                1 => null,
                null,
                null,
                null,
                null,
                htmlspecialcharsbx($MESS["NDS_"]),
                htmlspecialcharsbx($MESS["NDS_NO"])
            );
        }

        if (DoubleVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SUM_PAID"]) > 0)
        {
            $arCells[++$n] = array(
                1 => null,
                null,
                null,
                null,
                null,
                $MESS['PLAT_EARLE'],
                SaleFormatCurrency(
                    $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["SUM_PAID"],
                    $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
                    true
                )
            );
        }

        if (DoubleVal($GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DISCOUNT_VALUE"]) > 0)
        {
            $arCells[++$n] = array(
                1 => null,
                null,
                null,
                null,
                null,
                $MESS['SALE'],
                SaleFormatCurrency(
                    $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["DISCOUNT_VALUE"],
                    $GLOBALS["SALE_INPUT_PARAMS"]["ORDER"]["CURRENCY"],
                    true
                )
            );
        }

        $arCells[++$n] = array(
            1 => null,
            null,
            null,
            null,
            null,
            $MESS['ITOG'],
            $params['Summ']
        );
    }


    ?>
    <table class="it 777777777777 22222" width="100%">
        <tr>
            <td><nobr><?=$MESS["NUMBER"]?></nobr></td>
            <td><nobr><?=$MESS['NAME']?></nobr></td>
            <td><nobr><?=$MESS['COUNT']?></nobr></td>
            <td><nobr><?=$MESS['CT']?></nobr></td>
            <td><nobr><?=$MESS['PRICE']?>, <?=$MESS['RUB']; ?></nobr></td>
            <? if ($vat > 0) { ?>
                <td><nobr><?=$MESS['NDS']?></nobr></td>
            <? } ?>
            <td><nobr><?=$MESS['SUMM']?>, <?=$MESS['RUB']; ?></nobr></td>
        </tr>
        <?

        $rowsCnt = count($arCells);
        for ($n = 1; $n <= $rowsCnt; $n++)
        {
            $accumulated = 0;

            ?>
            <tr valign="top">
                <? if (!is_null($arCells[$n][1])) { ?>
                    <td align="center"><?=$arCells[$n][1]; ?></td>
                <? } else {
                    $accumulated++;
                } ?>
                <? if (!is_null($arCells[$n][2])) { ?>
                    <td align="left"
                        style="word-break: break-word; word-wrap: break-word; <? if ($accumulated) {?>border-width: 0pt 1pt 0pt 0pt; <? } ?>"
                        <? if ($accumulated) { ?>colspan="<?=($accumulated+1); ?>"<? $accumulated = 0; } ?>>
                        <?=$arCells[$n][2]; ?>
                        <? if (isset($arProps[$n]) && is_array($arProps[$n])) { ?>
                            <? foreach ($arProps[$n] as $property) { ?>
                                <br>
                                <small><?=$property; ?></small>
                            <? } ?>
                        <? } ?>
                    </td>
                <? } else {
                    $accumulated++;
                } ?>
                <? for ($i = 3; $i <= 7; $i++) { ?>
                    <? if (!is_null($arCells[$n][$i])) { ?>
                        <? if ($i != 6 || $vat > 0 || is_null($arCells[$n][2])) { ?>
                            <td align="right"
                                <? if ($accumulated) { ?>
                                    style="border-width: 0pt 1pt 0pt 0pt"
                                    colspan="<?=(($i == 6 && $vat <= 0) ? $accumulated : $accumulated+1); ?>"
                                    <? $accumulated = 0; } ?>>
                                <nobr><?=$arCells[$n][$i]; ?></nobr>
                            </td>
                        <? }
                    } else {
                        $accumulated++;
                    }
                } ?>
            </tr>
            <?

        }

        ?>
    </table>
    <br>

    <?=sprintf(
        $MESS['ALL_SUMM'],
        $items,
        $params['Summ']. ' '.$MESS['RUB']
    ); ?>
    <br>



</div>

</body>
</html>