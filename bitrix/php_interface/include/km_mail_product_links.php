<?php

use Bitrix\Main\Loader;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\Order;

if (!function_exists('kmMailSiteHost')) {
	function kmMailSiteHost(): string
	{
		static $host = null;
		if ($host !== null) {
			return $host;
		}
		$row = \Bitrix\Main\SiteTable::getList([
			'filter' => ['=LID' => 's1', '=ACTIVE' => 'Y'],
			'select' => ['SERVER_NAME'],
			'limit' => 1,
		])->fetch();
		$host = trim((string)($row['SERVER_NAME'] ?? 'kosmamed.ru')) ?: 'kosmamed.ru';
		return $host;
	}
}

if (!function_exists('kmMailAbsoluteUrl')) {
	function kmMailAbsoluteUrl(string $path): string
	{
		$path = trim($path);
		if ($path === '') {
			return '';
		}
		if (preg_match('#^https?://#i', $path)) {
			return $path;
		}
		return 'https://' . kmMailSiteHost() . (str_starts_with($path, '/') ? $path : '/' . $path);
	}
}

if (!function_exists('kmMailProductLinkFields')) {
	function kmMailProductLinkFields(string $name, string $url): array
	{
		$name = trim($name);
		$url = trim($url);
		if ($url === '') {
			return [
				'PRODUCT' => $name,
				'PRODUCT_URL' => '',
			];
		}
		$safeName = htmlspecialcharsbx($name, ENT_QUOTES | ENT_SUBSTITUTE, SITE_CHARSET);
		$safeUrl = htmlspecialcharsbx($url, ENT_QUOTES | ENT_SUBSTITUTE, SITE_CHARSET);
		return [
			'PRODUCT' => '<a href="' . $safeUrl . '">' . $safeName . '</a>',
			'PRODUCT_URL' => $safeUrl,
		];
	}
}

	function kmFormProductUrl(int $elementId): string
	{
		if ($elementId <= 0 || !Loader::includeModule('iblock')) {
			return '';
		}
		$row = CIBlockElement::GetList(
			[],
			['ID' => $elementId],
			false,
			['nTopCount' => 1],
			['ID', 'DETAIL_PAGE_URL']
		)->GetNext();
		if (!$row || empty($row['DETAIL_PAGE_URL'])) {
			return '';
		}
		return kmMailAbsoluteUrl((string)$row['DETAIL_PAGE_URL']);
	}
}

if (!function_exists('kmOrderBasketListWithLinks')) {
	function kmOrderBasketListWithLinks(int $orderId, string $separator = '<br/>'): string
	{
		if ($orderId <= 0 || !Loader::includeModule('sale')) {
			return '';
		}
		$order = Order::load($orderId);
		if (!$order) {
			return '';
		}
		$basket = $order->getBasket();
		if (!$basket) {
			return '';
		}

		$lines = [];
		/** @var BasketItem $basketItem */
		foreach ($basket as $basketItem) {
			$name = (string)$basketItem->getField('NAME');
			$url = kmMailAbsoluteUrl((string)$basketItem->getField('DETAIL_PAGE_URL'));
			if ($url !== '') {
				$label = htmlspecialcharsbx($name, ENT_QUOTES | ENT_SUBSTITUTE, SITE_CHARSET);
				$line = '<a href="' . htmlspecialcharsbx($url, ENT_QUOTES | ENT_SUBSTITUTE, SITE_CHARSET) . '">' . $label . '</a>';
			} else {
				$line = $name;
			}

			$props = [];
			$propCollection = $basketItem->getPropertyCollection();
			if ($propCollection) {
				foreach ($propCollection as $propItem) {
					$code = (string)$propItem->getField('CODE');
					if (in_array($code, ['PRODUCT.XML_ID', 'CATALOG.XML_ID', 'SUM_OF_CHARGE'], true)) {
						continue;
					}
					$value = trim((string)$propItem->getField('VALUE'));
					if ($value === '') {
						continue;
					}
					$props[] = trim((string)$propItem->getField('NAME')) . ': ' . $value;
				}
			}
			if ($props) {
				$line .= ' [' . implode('; ', $props) . ']';
			}

			$measure = trim((string)$basketItem->getField('MEASURE_NAME'));
			if ($measure === '') {
				$measure = 'шт';
			}
			$line .= ' - ' . BasketItem::formatQuantity($basketItem->getQuantity()) . ' ' . $measure
				. ' x ' . SaleFormatCurrency($basketItem->getPrice(), $basketItem->getCurrency());

			$lines[] = $line;
		}

		return implode($separator, $lines);
	}
}

AddEventHandler('sale', 'OnOrderNewSendEmail', 'kmOnOrderNewSendEmailProductLinks');
function kmOnOrderNewSendEmailProductLinks($orderId, &$eventName, &$fields)
{
	$list = kmOrderBasketListWithLinks((int)$orderId);
	if ($list !== '') {
		$fields['ORDER_LIST'] = $list;
	}
}
