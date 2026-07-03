<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();
//use Bitrix\Main\Localization\Loc;

use Bitrix\Currency;
use Bitrix\Main\Application;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale;
use Bitrix\Sale\Affiliate;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Delivery;
use Bitrix\Sale\DiscountCouponsManager;
use Bitrix\Sale\Sale\Fuser;
use Bitrix\Sale\Order;
use Bitrix\Sale\PaySystem;

Loader::includeModule("sale");

\Bitrix\Main\Loader::includeModule('r52.qrcode');
Loc::loadMessages(__FILE__);

///// -----------
$qr = new \R52\QrCode\CreateQrcode;
$associated = [
    'Name' => 'SELLER_COMPANY_NAME',
    'PersonalAcc' => 'SELLER_COMPANY_BANK_ACCOUNT',
    'BankName' => 'SELLER_COMPANY_BANK_NAME',
    'BIC' => 'SELLER_COMPANY_BANK_BIC',
    'CorrespAcc' => 'SELLER_COMPANY_BANK_ACCOUNT_CORR',
    'Purpose' => 'BILL_ORDER_SUBJECT',
    'PayeeINN' => 'SELLER_COMPANY_INN',
    'KPP' => 'SELLER_COMPANY_KPP'
];

function getSum($sum, $bitrixDought = '.', $dought = false)
{
    $result = "";
    $sum = explode($bitrixDought, $sum);
    if (strlen($sum[1]) > 2) {
        $result = $sum[0] . $dought . substr($sum[1], 0, 2);
    } elseif (strlen($sum[1]) == 2) {
        $result = $sum[0] . $dought . $sum[1];
    } elseif (strlen($sum[1]) == 1) {
        $result = $sum[0] . $dought . $sum[1] . "0";
    } else {
        $result = $sum[0] . $dought .  "00";
    }
    return $result;
}

function buildStr($params, $arr) {
    $str = 'ST00012|OrderNum=' . $params['ACCOUNT_NUMBER'] . '|Sum=' . getSum($params['SUM']);
    foreach ($arr as $code => $value) {
		if($value === 'BILL_ORDER_SUBJECT')
		{
			preg_match('/({)(.*)(})/Uis', $params[$value], $matches);
			if(isset($matches[0]) && isset($matches[2]))
			{
				$params[$value] = str_replace($matches[0], $params[$matches[2]], $params[$value]);
			}
		}
		
        if($params[$value]) {
		if($code=='Purpose' && $params['TAXES'][0]['ID']) {
		
				$order = Order::load($params['TAXES'][0]['ORDER_ID']);
				
				$tax = $order->getField("TAX_VALUE");
		
				$tax = number_format($tax, 2, '.', ' ');
		
		            $str .= '|' . $code . '=' . $params[$value].' '.$tax;
		}else{
		            $str .= '|' . $code . '=' . $params[$value];
		}
        }
    }
    return $str;
}

$params['QR_STR'] = buildStr($params, $associated);


if($params['QR_STR']) {
    $res = $qr->create($params['QR_STR']);

    $params['QR_CODE'] = $qr->getQrLogo($res);
}
//---------
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html>
<head>
<title>СЧЕТ № <?=$params['ACCOUNT_NUMBER']?></title>
<meta http-equiv="Content-Type" content="text/html; charset=<?=LANG_CHARSET?>">
<style type="text/css">
	table { border-collapse: collapse; }
	table.acc td { border: 1pt solid #000000; padding: 0pt 3pt; line-height: 21pt; }
	table.it td { border: 1pt solid #000000; padding: 0pt 3pt; }
	table.sign td { font-weight: bold; vertical-align: bottom; }
	table.header td { padding: 0pt; vertical-align: top; }
	.bill_print {
		width: 138px;
	    height: 29px;
	    margin: 0px;
	    padding: 0px;
	    line-height: 29px;
	    color: #fff !important;
	    background: #96a0b8;
	    display: block;
	    text-decoration: none !important;
	    text-align: center;
	    border-radius: 4px;
	    float: right;
	}
	.bill_print:hover{
	    background: #575b71;
	    cursor: pointer;
	}
</style>
</head>
<style>
    .bill img {
        width: 180px;

    }
</style>
<?

if ($_REQUEST['BLANK'] == 'Y')
	$blank = true;

$pageWidth  = 595.28;
$pageHeight = 841.89;

$background = '#ffffff';
if ($params['BILL_BACKGROUND'])
{
	$path = $params['BILL_BACKGROUND'];
	if (intval($path) > 0)
	{
		if ($arFile = CFile::GetFileArray($path))
			$path = $arFile['SRC'];
	}

	$backgroundStyle = $params['BILL_BACKGROUND_STYLE'];
	if (!in_array($backgroundStyle, array('none', 'tile', 'stretch')))
		$backgroundStyle = 'none';

	if ($path)
	{
		switch ($backgroundStyle)
		{
			case 'none':
				$background = "url('" . $path . "') 0 0 no-repeat";
				break;
			case 'tile':
				$background = "url('" . $path . "') 0 0 repeat";
				break;
			case 'stretch':
				$background = sprintf(
					"url('%s') 0 0 repeat-y; background-size: %.02fpt %.02fpt",
					$path, $pageWidth, $pageHeight
				);
				break;
		}
	}
}

$margin = array(
	'top' => intval($params['BILL_MARGIN_TOP'] ?: 15) * 72/25.4,
	'right' => intval($params['BILL_MARGIN_RIGHT'] ?: 15) * 72/25.4,
	'bottom' => intval($params['BILL_MARGIN_BOTTOM'] ?: 15) * 72/25.4,
	'left' => intval($params['BILL_MARGIN_LEFT'] ?: 20) * 72/25.4
);

$width = $pageWidth - $margin['left'] - $margin['right'];


$aNum = explode('/',$params["ACCOUNT_NUMBER"]);

$original_order_id = $_REQUEST['ORDER_ID'];


$dCss ='';
if($params["ACCOUNT_NUMBER"] && !$_REQUEST['ORDER_ID']) $dCss= 'padding-left:0;';

if($params["ACCOUNT_NUMBER"]) $_REQUEST['ORDER_ID'] =$aNum[0];

if($_REQUEST['accountNumber']) $_REQUEST['ORDER_ID'] =$_REQUEST['accountNumber'];
?>


<body style=" <?=$_REQUES['ORDER_ID'];?> margin: 0pt; padding: 0pt; background: <?=$background; ?>"<? if ($_REQUEST['print'] == 'y') { ?> onload="setTimeout(window.print, 0);"<? } ?>>
<?if($_REQUEST['print'] != 'y'):?>
<?if($_REQUEST['PAYMENT_ID']):?>
	<a class="bill_print" href="?PAYMENT_ID=<?=$_REQUEST['PAYMENT_ID']?>&ORDER_ID=<?=$original_order_id?>&print=y">напечатать счет</a>
<div style="clear: both;"></div>
<?else:?>
	<a class="bill_print" href="/personal/order/make/?ORDER_ID=<?=$original_order_id?>&print=y">напечатать счет</a>
	<div style="clear: both;"></div>
<?endif;?>
<?endif;?>
<div style="margin: 0pt; padding: <?=join('pt ', $margin); ?>pt; width: <?=$width; ?>pt; background: <?=$background; ?>; <?=$dCss?>">


<?if ($params['BILL_HEADER_SHOW'] == 'Y'):?>
	<table class="header" style="width: 100%; margin-bottom: 10px;"">
		<tr style="d">
            <td style="text-align: center; width: 100%" colspan="2">
				<b>Если Вы физическое лицо, и оплачиваете с банка в РФ, самый простой способ оплата по QR-коду</b><br><br>							
            </td>

		</tr>

		<tr style="d">
			<? if ($params["BILL_PATH_TO_LOGO"]) { ?>
			<td style="padding-right: 5pt; padding-bottom: 5pt; display: flex; flex-direction: column;">
				<? $imgParams = CFile::_GetImgParams($params['BILL_PATH_TO_LOGO']);
					$dpi = intval($params['BILL_LOGO_DPI']) ?: 96;
					$imgWidth = $imgParams['WIDTH'] * 96 / $dpi;
					if ($imgWidth > $pageWidth)
						$imgWidth = $pageWidth * 0.6;
				?>
				<img src="<?=$imgParams['SRC']; ?>" width="<?=$imgWidth; ?>" />

                <b><?=htmlspecialcharsbx($params["SELLER_COMPANY_NAME"]); ?></b><br><?
                if ($params["SELLER_COMPANY_ADDRESS"]) {
                    $sellerAddr = $params["SELLER_COMPANY_ADDRESS"];
                    if (is_array($sellerAddr))
                        $sellerAddr = implode(', ', $sellerAddr);
                    else
                        $sellerAddr = str_replace(array("\r\n", "\n", "\r"), ', ', strval($sellerAddr));
                    ?><b><?=htmlspecialcharsbx($sellerAddr);?></b><br><?
                } ?>
                <? if ($params["SELLER_COMPANY_PHONE"]) { ?>
                    <b><?=Loc::getMessage('SALE_HPS_BILL_SELLER_COMPANY_PHONE', array('#PHONE#' => htmlspecialcharsbx($params["SELLER_COMPANY_PHONE"])));?></b><br>
                <? } ?>
			</td>
			<? } ?>
            <td style="text-align: left; width: 378px">
				1. Зайдите в приложение Вашего банка.<br>
				2. Найдите оплата по QR-коду.<br>
				3. Наведите камеру на QR-код справа.<br>
				4. Проверьте реквизиты, которые указаны в вашем приложении 
				и в этом счете.<br><br>
				Обратите внимание, чтобы мы могли найти ваш платеж среди других, <b>Фамилия в выставленном счете</b> должна соответствовать <b>Фамилии в вашем банке</b> с которого производится оплата.<br><br>
            </td>
            <td style="text-align: end; width: 200px">
                <span class="bill"><? echo $params['QR_CODE']; ?></span>
            </td>

		</tr>
		<tr style="d">
            <td style="text-align: left; width: 100%" colspan="2">
				Если <b>в течении 1 рабочего дня</b>, Вы не получили уведомление об оплате от нашего менеджера, пожалуйста позвоните по указанным выше телефонам.	<br><br>							
            </td>

		</tr>	
		<tr style="d">
            <td style="text-align: left; width: 100%" colspan="2">
				Вы можете оплатить стандартным способом, используя в банке раздел оплата Юридическим лицам по реквизитам. Данные для оплаты ниже.								
            </td>

		</tr>		
	</table>

	<?
	if ($params["SELLER_COMPANY_BANK_NAME"])
	{
		$sellerBankCity = '';
		if ($params["SELLER_COMPANY_BANK_CITY"])
		{
			$sellerBankCity = $params["SELLER_COMPANY_BANK_CITY"];
			if (is_array($sellerBankCity))
				$sellerBankCity = implode(', ', $sellerBankCity);
			else
				$sellerBankCity = str_replace(array("\r\n", "\n", "\r"), ', ', strval($sellerBankCity));
		}
		$sellerBank = sprintf(
			"%s %s",
			$params["SELLER_COMPANY_BANK_NAME"],
			htmlspecialcharsbx($sellerBankCity)
		);
		$sellerRs = $params["SELLER_COMPANY_BANK_ACCOUNT"];
	}
	else
	{
		$rsPattern = '/\s*\d{10,100}\s*/';

		$sellerBank = trim(preg_replace($rsPattern, ' ', $params["SELLER_COMPANY_BANK_ACCOUNT"]));

		preg_match($rsPattern, $params["SELLER_COMPANY_BANK_ACCOUNT"], $matches);
		$sellerRs = trim($matches[0]);
	}

	?>
	<table class="acc" width="100%">
		<colgroup>
			<col width="29%">
			<col width="29%">
			<col width="10%">
			<col width="32%">
		</colgroup>
		<tr>
			<td>
				<? if ($params["SELLER_COMPANY_INN"]) { ?>
				<?=Loc::getMessage('SALE_HPS_BILL_INN', array('#INN#' => htmlspecialcharsbx($params["SELLER_COMPANY_INN"])));?>
				<? } else { ?>
				&nbsp;
				<? } ?>
			</td>
			<td>
				<? if ($params["SELLER_COMPANY_KPP"]) { ?>
				<?=Loc::getMessage('SALE_HPS_BILL_KPP', array('#KPP#' => htmlspecialcharsbx($params["SELLER_COMPANY_KPP"])));?>
				<? } else { ?>
				&nbsp;
				<? } ?>
			</td>
			<td rowspan="2">
				<br>
				<br>
				<?=Loc::getMessage("SALE_HPS_BILL_SELLER_ACC"); ?>
			</td>
			<td rowspan="2">
				<br>
				<br>
				<?=htmlspecialcharsbx($sellerRs);?>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<?=Loc::getMessage('SALE_HPS_BILL_SELLER_NAME')?><br>
				<?=htmlspecialcharsbx($params["SELLER_COMPANY_NAME"]);?>
			</td>
		</tr>
		<tr>
			<td colspan="2">
				<?=Loc::getMessage('SALE_HPS_BILL_SELLER_BANK_NAME')?><br>
				<?=htmlspecialcharsbx($sellerBank);?>
			</td>
			<td>
				<?=Loc::getMessage('SALE_HPS_BILL_SELLER_BANK_BIK');?><br>
				<?=Loc::getMessage('SALE_HPS_BILL_SELLER_ACC_CORR')?><br>
			</td>
			<td>
				<?=htmlspecialcharsbx($params["SELLER_COMPANY_BANK_BIC"]); ?><br>
				<?=htmlspecialcharsbx($params["SELLER_COMPANY_BANK_ACCOUNT_CORR"]);?>
			</td>
		</tr>

		<tr >
            <td colspan="4" style="text-align: left; width: 100%;font-size: 14px;">
				<b>В комментариях к платежу обязательно указываем номер счета: <?=$params["ACCOUNT_NUMBER"]?></b>							
						
            </td>

		</tr>	
	</table>
<?endif;?>
<br>
<br>

	<?$cDate = explode(' ',$params["PAYMENT_DATE_INSERT"]);?>

	<?$params["PAYMENT_DATE_INSERT"]=$cDate[0]?>


<table width="100%">
	<colgroup>
		<col width="50%">
		<col width="0">
		<col width="50%">
	</colgroup>
<?if ($params['BILL_HEADER']):?>
	<tr>
		<td></td>
		<td style="font-size: 2em; font-weight: bold; text-align: center">
			<nobr>
				<?=htmlspecialcharsbx($params['BILL_HEADER']);?> <?=Loc::getMessage('SALE_HPS_BILL_SELLER_TITLE', array('#PAYMENT_NUM#' => htmlspecialcharsbx($params["ACCOUNT_NUMBER"]), '#PAYMENT_DATE#' => htmlspecialcharsbx($params["PAYMENT_DATE_INSERT"])));?>
			</nobr>
		</td>
		<td></td>
	</tr>
<?endif;?>

<? if ($params["PAYMENT_DATE_PAY_BEFORE"]) { ?>
	<tr>
		<td></td>
		<td>
			<?=Loc::getMessage('SALE_HPS_BILL_SELLER_DATE_END', array('#PAYMENT_DATE_END#' => ConvertDateTime($params["PAYMENT_DATE_PAY_BEFORE"], FORMAT_DATE) ?: htmlspecialcharsbx($params["PAYMENT_DATE_PAY_BEFORE"])));?>
		</td>
		<td></td>
	</tr>
<? } ?>
</table>

<br>
<?

if ($params['BILL_PAYER_SHOW'] == 'Y'):
	if ($params["BUYER_PERSON_COMPANY_NAME"]) {
		echo Loc::getMessage('SALE_HPS_BILL_BUYER_NAME', array('#BUYER_NAME#' => htmlspecialcharsbx($params["BUYER_PERSON_COMPANY_NAME"])));
		if ($params["BUYER_PERSON_COMPANY_INN"])
			echo ', '.Loc::getMessage('SALE_HPS_BILL_BUYER_INN', array('#INN#' => htmlspecialcharsbx($params["BUYER_PERSON_COMPANY_INN"])));
		if ($params["BUYER_PERSON_COMPANY_ADDRESS"])
		{
			$buyerAddr = $params["BUYER_PERSON_COMPANY_ADDRESS"];
			if (is_array($buyerAddr))
				$buyerAddr = implode(', ', $buyerAddr);
			else
				$buyerAddr = str_replace(array("\r\n", "\n", "\r"), ', ', strval($buyerAddr));
			echo sprintf(", %s", htmlspecialcharsbx($buyerAddr));
		}
		if ($params["BUYER_PERSON_COMPANY_PHONE"])
			echo sprintf(", %s", htmlspecialcharsbx($params["BUYER_PERSON_COMPANY_PHONE"]));
		if ($params["BUYER_PERSON_COMPANY_FAX"])
			echo sprintf(", %s", htmlspecialcharsbx($params["BUYER_PERSON_COMPANY_FAX"]));
		if ($params["BUYER_PERSON_COMPANY_NAME_CONTACT"])
			echo sprintf(", %s", htmlspecialcharsbx($params["BUYER_PERSON_COMPANY_NAME_CONTACT"]));
	}
endif;
?>

<br>
<br>

<?php
$arCurFormat = CCurrencyLang::GetFormatDescription($params['CURRENCY']);
$currency = preg_replace('/(^|[^&])#/', '${1}', $arCurFormat['FORMAT_STRING']);

$cells = array();
$props = array();

$n = 0;
$sum = 0.00;
$vat = 0;
$cntBasketItem = 0;

$columnList = array('NUMBER', 'NAME', 'QUANTITY', 'MEASURE', 'PRICE', 'VAT_RATE', 'SUM');
$arCols = array();
$vatRateColumn = 0;
foreach ($columnList as $column)
{
	if ($params['BILL_COLUMN_'.$column.'_SHOW'] == 'Y')
	{
		$caption = $params['BILL_COLUMN_'.$column.'_TITLE'];
		$caption = htmlspecialcharsbx($caption, ENT_COMPAT, false);
		if (in_array($column, ['PRICE', 'SUM']))
		{
			$caption .= ', '.$currency;
		}

		$arCols[$column] = array(
			'NAME' => $caption,
			'SORT' => $params['BILL_COLUMN_'.$column.'_SORT']
		);
	}
}
if ($params['USER_COLUMNS'])
{
	$columnList = array_merge($columnList, array_keys($params['USER_COLUMNS']));
	foreach ($params['USER_COLUMNS'] as $id => $val)
	{
		$arCols[$id] = array(
			'NAME' => htmlspecialcharsbx($val['NAME'], ENT_COMPAT, false),
			'SORT' => $val['SORT']
		);
	}
}

uasort($arCols, function ($a, $b) {return ($a['SORT'] < $b['SORT']) ? -1 : 1;});

$arColumnKeys = array_keys($arCols);
$columnCount = count($arColumnKeys);

if ($params['BASKET_ITEMS'])
{
	foreach ($params['BASKET_ITEMS'] as $basketItem)
	{
		$productName = $basketItem["NAME"];
		if ($productName == "OrderDelivery")
			$productName = Loc::getMessage('SALE_HPS_BILL_DELIVERY');
		else if ($productName == "OrderDiscount")
			$productName = Loc::getMessage('SALE_HPS_BILL_DISCOUNT');

		if ($basketItem['IS_VAT_IN_PRICE'])
			$basketItemPrice = $basketItem['PRICE'];
		else
			$basketItemPrice = $basketItem['PRICE']*(1 + $basketItem['VAT_RATE']);

		$cells[++$n] = array();
		foreach ($arCols as $columnId => $caption)
		{
			$data = null;

			switch ($columnId)
			{
				case 'NUMBER':
					$data = $n;
					break;
				case 'NAME':
					$data = htmlspecialcharsbx($productName);
					break;
				case 'QUANTITY':
					$data = roundEx($basketItem['QUANTITY'], SALE_VALUE_PRECISION);
					break;
				case 'MEASURE':
					$data = $basketItem["MEASURE_NAME"] ? htmlspecialcharsbx($basketItem["MEASURE_NAME"]) : Loc::getMessage('SALE_HPS_BILL_BASKET_MEASURE_DEFAULT');
					break;
				case 'PRICE':
					$data = SaleFormatCurrency($basketItem['PRICE'], $basketItem['CURRENCY'], true);
					break;
				case 'VAT_RATE':
					$data = roundEx($basketItem['VAT_RATE'] * 100, SALE_VALUE_PRECISION)."%";
					break;
				case 'SUM':
					$data = SaleFormatCurrency($basketItemPrice * $basketItem['QUANTITY'], $basketItem['CURRENCY'], true);
					break;
				default :
					$data = ($basketItem[$columnId]) ?: '';
			}
			if ($data !== null)
				$cells[$n][$columnId] = $data;
		}
		$props[$n] = array();
		/** @var \Bitrix\Sale\BasketPropertyItem $basketPropertyItem */
		if ($basketItem['PROPS'])
		{
			foreach ($basketItem['PROPS'] as $basketPropertyItem)
			{
				if ($basketPropertyItem['CODE'] == 'CATALOG.XML_ID' || $basketPropertyItem['CODE'] == 'PRODUCT.XML_ID')
					continue;
				$props[$n][] = htmlspecialcharsbx(sprintf("%s: %s", $basketPropertyItem["NAME"], $basketPropertyItem["VALUE"]));
			}
		}
		$sum += doubleval($basketItem['PRICE'] * $basketItem['QUANTITY']);
		$vat = max($vat, $basketItem['VAT_RATE']);

	}
}

if ($vat <= 0)
{
	//unset($arCols['VAT_RATE']);
	$columnCount = count($arCols);
	$arColumnKeys = array_keys($arCols);
	//foreach ($cells as $i => $cell)
		//unset($cells[$i]['VAT_RATE']);
}

if ($params['DELIVERY_PRICE'] > 0)
{
	$deliveryItem = Loc::getMessage('SALE_HPS_BILL_DELIVERY');

	if ($params['DELIVERY_NAME'])
		$deliveryItem .= sprintf(" (%s)", htmlspecialcharsbx($params['DELIVERY_NAME']));
	$cells[++$n] = array();
	foreach ($arCols as $columnId => $caption)
	{
		$data = null;

		switch ($columnId)
		{
			case 'NUMBER':
				$data = $n;
				break;
			case 'NAME':
				$data = htmlspecialcharsbx($deliveryItem);
				break;
			case 'QUANTITY':
				$data = 1;
				break;
			case 'MEASURE':
				$data = 'усл';
				break;
			case 'PRICE':
				$data = SaleFormatCurrency($params['DELIVERY_PRICE'], $params['CURRENCY'], true);
				break;
			case 'VAT_RATE':
				$data = roundEx($params['DELIVERY_VAT_RATE'] * 100, SALE_VALUE_PRECISION)."%";
				break;
			case 'SUM':
				$data = SaleFormatCurrency($params['DELIVERY_PRICE'], $params['CURRENCY'], true);
				break;
		}
		if ($data !== null)
			$cells[$n][$columnId] = $data;
	}
	$sum += doubleval($params['DELIVERY_PRICE']);
$vat = max($vat, $params['VAT_RATE']);
}

if ($params['BILL_TOTAL_SHOW'] == 'Y')
{
	$cntBasketItem = $n;
	$eps = 0.0001;
	if ($params['SUM'] - $sum > $eps)
	{
		$cells[++$n] = array();
		for ($i = 0; $i < $columnCount; $i++)
			$cells[$n][$arColumnKeys[$i]] = null;

		$cells[$n][$arColumnKeys[$columnCount-2]] = Loc::getMessage('SALE_HPS_BILL_SUBTOTAL');
		$cells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($sum, $params['CURRENCY'], true);
	}

	if ($params['TAXES'])
	{
		foreach ($params['TAXES'] as $tax)
		{
			$cells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$cells[$n][$arColumnKeys[$i]] = null;

			$cells[$n][$arColumnKeys[$columnCount-2]] = htmlspecialcharsbx(sprintf(
					"%s%s%s:",
					($tax["IS_IN_PRICE"] == "Y") ? Loc::getMessage('SALE_HPS_BILL_INCLUDING') : "",
					$tax["TAX_NAME"],
					($vat <= 0 && $tax["IS_PERCENT"] == "Y")
							? sprintf(' (%s%%)', roundEx($tax["VALUE"], SALE_VALUE_PRECISION))
							: ""
			));
			$cells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($tax["VALUE_MONEY"], $params['CURRENCY'], true);
		}
	}

	if ($params['DELIVERY_VAT_RATE'])
	{
		//foreach ($params['TAXES'] as $tax)
		//{
			$cells[++$n] = array();
			for ($i = 0; $i < $columnCount; $i++)
				$cells[$n][$arColumnKeys[$i]] = null;

			$cells[$n][$arColumnKeys[$columnCount-2]] = htmlspecialcharsbx(sprintf(
					"%s%s%s:",
					($tax["IS_IN_PRICE"] == "Y") ? Loc::getMessage('SALE_HPS_BILL_INCLUDING') : "",
					$tax["TAX_NAME"],
					($vat <= 0 && $tax["IS_PERCENT"] == "Y")
							? sprintf(' (%s%%)', roundEx($tax["VALUE"], SALE_VALUE_PRECISION))
							: ""
			));
			$cells[$n][$arColumnKeys[$columnCount-2]] = Loc::getMessage('SALE_HPS_BILL_TOTAL_VAT_RATE');
			$cells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency( ($params["DELIVERY_PRICE"]/(1+$params["DELIVERY_VAT_RATE"]))*$params["DELIVERY_VAT_RATE"], $params['CURRENCY'], true);
		//}
	}

	if (!$params['TAXES'] && !$params['DELIVERY_VAT_RATE'])
	{
		$cells[++$n] = array();
		for ($i = 0; $i < $columnCount; $i++)
			$cells[$n][$i] = null;

		$cells[$n][$arColumnKeys[$columnCount-2]] = Loc::getMessage('SALE_HPS_BILL_TOTAL_VAT_RATE');
		$cells[$n][$arColumnKeys[$columnCount-1]] = Loc::getMessage('SALE_HPS_BILL_TOTAL_VAT_RATE_NO');
	}

	if ($params['SUM_PAID'] > 0)
	{
		$cells[++$n] = array();
		for ($i = 0; $i < $columnCount; $i++)
			$cells[$n][$arColumnKeys[$i]] = null;

		$cells[$n][$arColumnKeys[$columnCount-2]] = Loc::getMessage('SALE_HPS_BILL_TOTAL_PAID');
		$cells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($params['SUM_PAID'], $params['CURRENCY'], true);
	}
	if ($params['DISCOUNT_PRICE'] > 0)
	{
		$cells[++$n] = array();
		for ($i = 0; $i < $columnCount; $i++)
			$cells[$n][$arColumnKeys[$i]] = null;

		$cells[$n][$arColumnKeys[$columnCount-2]] = Loc::getMessage('SALE_HPS_BILL_TOTAL_DISCOUNT');
		$cells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($params['DISCOUNT_PRICE'], $params['CURRENCY'], true);
	}

	$cells[++$n] = array();
	for ($i = 0; $i < $columnCount; $i++)
		$cells[$n][$arColumnKeys[$i]] = null;

	$cells[$n][$arColumnKeys[$columnCount-2]] = Loc::getMessage('SALE_HPS_BILL_TOTAL_SUM');
	$cells[$n][$arColumnKeys[$columnCount-1]] = SaleFormatCurrency($params['SUM'], $params['CURRENCY'], true);
}
?>

<table class="it" width="100%">
	<tr>
	<?foreach ($arCols as $columnId => $col):?>
		<?if($col['NAME']=="5%") $col['NAME']='НДС';?>
		<td><?=$col['NAME'];?></td>
	<?endforeach;?>
	</tr>
<?

$rowsCnt = count($cells);
for ($n = 1; $n <= $rowsCnt; $n++):

	$accumulated = 0;
	//if (in_array('В том числе НДС (5%):',$cells[$n])) $n++;
	if (in_array('НДС:',$cells[$n])) $n++;
?>
	<tr valign="top" >

	<?foreach ($arCols as $columnId => $col):?>
		<?
			if (!is_null($cells[$n][$columnId]))
			{
				if ($columnId === 'NUMBER')
				{?>
					<td align="center"><?=$cells[$n][$columnId];?></td>
				<?}
				elseif ($columnId === 'NAME')
				{
				?>
					<td align="<?=($n > $cntBasketItem) ? 'right' : 'left';?>"
						style="word-break: break-word; word-wrap: break-word; <? if ($accumulated) {?>border-width: 0pt 1pt 0pt 0pt; <? } ?>"
						<? if ($accumulated) { ?>colspan="<?=($accumulated+1); ?>"<? $accumulated = 0; } ?>>
						<?=$cells[$n][$columnId]; ?>
						<? if (isset($props[$n]) && is_array($props[$n])) { ?>
						<? foreach ($props[$n] as $property) { ?>
						<br>
						<small><?=$property; ?></small>
						<? } ?>
						<? } ?>
					</td>
				<?}
				else
				{
					if (!is_null($cells[$n][$columnId]))
					{
						
						if ($columnId != 'VAT_RATE_' || $vat > 0 || is_null($cells[$n][$columnId]) || $n > $cntBasketItem)
						{ ?>
							<td align="right"
								<? if ($accumulated) { ?>
								style="border-width: 0pt 1pt 0pt 0pt"
								colspan="<?=(($columnId == 'VAT_RATE' && $vat <= 0) ? $accumulated+1 : $accumulated+1); ?>"
								<? $accumulated = 0; } ?>>


								<?if ($columnId == 'SUM' || $columnId == 'PRICE'):?>
									<nobr><?=$cells[$n][$columnId];?></nobr>
								<?else:?>
<?if ($columnId == 'VAT_RATE'):?>
<?
	if($cells[$n][$columnId] == '0%'){ 
		echo 'Без НДС';
	}else{
		echo $cells[$n][$columnId];
	}
?>
<?elseif ($columnId == 'MEASURE'):?>
<?
	if(!$cells[$n][$columnId]){ 
		echo 'усл';
	}else{
		echo $cells[$n][$columnId];
	}
?>
<?else:?>
									<?=$cells[$n][$columnId]; ?>
<?endif;?>

								<?endif;?>
							</td>
						<? }
					
					}
					else
					{
						$accumulated++;
					}
				}
			}
			else
			{
				$accumulated++;
			}
		?>
	<?endforeach;?>
	</tr>

<?endfor;?>
</table>
<br>

<?if ($params['BILL_TOTAL_SHOW'] == 'Y'):?>
	<?=Loc::getMessage(
			'SALE_HPS_BILL_BASKET_TOTAL',
			array(
					'#BASKET_COUNT#' => $cntBasketItem,
					'#BASKET_PRICE#' => SaleFormatCurrency($params['SUM'], $params['CURRENCY'], false),
			)
	);?>
	<br>

	<b>
	<?

	if (in_array($params['CURRENCY'], array("RUR", "RUB")))
	{
		echo Number2Word_Rus($params['SUM']);
	}
	else
	{
		echo SaleFormatCurrency(
			$params['SUM'],
			$params['CURRENCY'],
			false
		);
	}

	?>
	</b>
<?endif;?>
<br>
<br>

<div class="only_print"></div>
<div class="only_print"></div>
<? if ($params["BILL_COMMENT1"] || $params["BILL_COMMENT2"]) { ?>
<b><?=Loc::getMessage('SALE_HPS_BILL_COND_COMM')?></b>
<br>
	<? if ($params["BILL_COMMENT1"]) { ?>
	<?=nl2br(HTMLToTxt(preg_replace(
		array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
		htmlspecialcharsback($params["BILL_COMMENT1"])
	), '', array(), 0)); ?>
	<br>
	<br>
	<? } ?>
	<? if ($params["BILL_COMMENT2"]) { ?>
	<?=nl2br(HTMLToTxt(preg_replace(
		array('#</div>\s*<div[^>]*>#i', '#</?div>#i'), array('<br>', '<br>'),
		htmlspecialcharsback($params["BILL_COMMENT2"])
	), '', array(), 0)); ?>
	<br>
	<br>
	<? } ?>
<? } ?>

<br>
<br>

<?if ($params['BILL_SIGN_SHOW'] == 'Y'):?>
	<? if (!$blank) { ?>
	<div style="position: relative; "><?=CFile::ShowImage(
			$params["BILL_PATH_TO_STAMP"],
		160, 160,
		'style="position: absolute; left: 40pt; "'
	); ?></div>
	<? } ?>

	<div style="position: relative">
		<table class="sign">
			<? if ($params["SELLER_COMPANY_DIRECTOR_POSITION"]) { ?>
			<tr>
				<td style="width: 150pt; "><?=htmlspecialcharsbx($params["SELLER_COMPANY_DIRECTOR_POSITION"]); ?></td>
				<td style="width: 160pt; border: 1pt solid #000000; border-width: 0pt 0pt 1pt 0pt; text-align: center; ">
					<? if (!$blank) { ?>
					<?=CFile::ShowImage($params["SELLER_COMPANY_DIR_SIGN"], 200, 50); ?>
					<? } ?>
				</td>
				<td>
					<? if ($params["SELLER_COMPANY_DIRECTOR_NAME"]) { ?>
					(<?=htmlspecialcharsbx($params["SELLER_COMPANY_DIRECTOR_NAME"]); ?>)
					<? } ?>
				</td>
			</tr>
			<tr><td colspan="3">&nbsp;</td></tr>
			<? } ?>
			<? if ($params["SELLER_COMPANY_ACCOUNTANT_POSITION"]) { ?>
			<tr>
				<td style="width: 150pt; "><?=htmlspecialcharsbx($params["SELLER_COMPANY_ACCOUNTANT_POSITION"]); ?></td>
				<td style="width: 160pt; border: 1pt solid #000000; border-width: 0pt 0pt 1pt 0pt; text-align: center; ">
					<? if (!$blank) { ?>
					<?=CFile::ShowImage($params["SELLER_COMPANY_ACC_SIGN"], 200, 50); ?>
					<? } ?>
				</td>
				<td>
					<? if ($params["SELLER_COMPANY_ACCOUNTANT_NAME"]) { ?>
					(<?=htmlspecialcharsbx($params["SELLER_COMPANY_ACCOUNTANT_NAME"]); ?>)
					<? } ?>
				</td>
			</tr>
			<? } ?>
		</table>
	</div>
<?endif;?>

</div>
<?if($_REQUEST['print'] != 'y'):?>
<?if($_REQUEST['PAYMENT_ID']):?>
	<a class="bill_print" href="?PAYMENT_ID=<?=$_REQUEST['PAYMENT_ID']?>&ORDER_ID=<?=$original_order_id?>&print=y">напечатать счет</a>
<div style="clear: both;"></div>
<?else:?>
	<a class="bill_print" href="/personal/order/make/?ORDER_ID=<?=$original_order_id?>&print=y">напечатать счет</a>
	<div style="clear: both;"></div>
<?endif;?>
<?endif;?>
</body>
</html>