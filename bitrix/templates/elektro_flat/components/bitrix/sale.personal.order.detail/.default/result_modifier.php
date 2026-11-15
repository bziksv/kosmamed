<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use \Bitrix\Main\Loader;
use \Bitrix\Sale\Delivery\ExtraServices\Manager;

global $arSetting;

if(!Loader::includeModule('iblock') || !Loader::includeModule('catalog') || !Loader::includeModule('sale'))
	return;
	
foreach($arResult['SHIPMENT'] as $keyS => $shipment) {
	$arListExtraService = Manager::getValuesForShipment($shipment['ID'], $shipment['DELIVERY']['ID']);
	$extraServiceManager = new Manager($shipment['DELIVERY']['ID']);
	$extraService = $extraServiceManager->getItems();
	foreach ($extraService as $itemId => $item) {
		if(array_key_exists($itemId, $arListExtraService)) {
			$arResult['SHIPMENT'][$keyS]['EXTRA_SERVICE'][$itemId] = array(
				'ID' => $itemId,
				'NAME' => $item->getName(),
				'DESCRIPTION' => $item->getDescription(),
				'PARAMS' => $item->getParams(),
				'VALUE' => $arListExtraService[$itemId]
			);
		}
	}
	unset($arListExtraService, $extraServiceManager, $extraService);
}

if (!function_exists('kmOrderItemPicture')) {
	function kmOrderItemPicture($elementId)
	{
		$elementId = (int)$elementId;
		if ($elementId <= 0) {
			return null;
		}
		$element = CIBlockElement::GetList(
			array(),
			array('ID' => $elementId),
			false,
			false,
			array('ID', 'IBLOCK_ID', 'DETAIL_PICTURE', 'PREVIEW_PICTURE')
		)->Fetch();
		if (!$element) {
			$sku = CCatalogSku::GetProductInfo($elementId);
			if (is_array($sku)) {
				return kmOrderItemPicture($sku['ID']);
			}
			return null;
		}
		$fileId = (int)($element['DETAIL_PICTURE'] ?: $element['PREVIEW_PICTURE']);
		if ($fileId <= 0) {
			$photo = CIBlockElement::GetProperty(
				$element['IBLOCK_ID'],
				$element['ID'],
				array('sort' => 'asc'),
				array('CODE' => 'MORE_PHOTO')
			)->Fetch();
			if ($photo && (int)$photo['VALUE'] > 0) {
				$fileId = (int)$photo['VALUE'];
			}
		}
		if ($fileId <= 0) {
			$sku = CCatalogSku::GetProductInfo($elementId);
			if (is_array($sku) && (int)$sku['ID'] !== $elementId) {
				return kmOrderItemPicture($sku['ID']);
			}
			return null;
		}
		$file = CFile::GetFileArray($fileId);
		if (!$file || empty($file['SRC'])) {
			return null;
		}
		$absolute = $_SERVER['DOCUMENT_ROOT'].$file['SRC'];
		if (is_file($absolute)) {
			$resized = CFile::ResizeImageGet(
				$fileId,
				array('width' => 64, 'height' => 64),
				BX_RESIZE_IMAGE_PROPORTIONAL,
				true
			);
			if (!empty($resized['src'])) {
				return $resized;
			}
		}
		return array(
			'src' => $file['SRC'],
			'width' => 64,
			'height' => 64,
		);
	}
}

foreach($arResult["BASKET"] as $key => $arBasketItems) {
	$picture = kmOrderItemPicture($arBasketItems["PRODUCT_ID"]);
	if ($picture) {
		$arResult["BASKET"][$key]["DETAIL_PICTURE"] = $picture;
	}
	if(in_array("OFFERS_LINK_SHOW", $arSetting["GENERAL_SETTINGS"]["VALUE"]) && is_array(CCatalogSku::GetProductInfo($arBasketItems["PRODUCT_ID"]))) {
		$arResult["BASKET"][$key]["DETAIL_PAGE_URL"] .= "?offer=".$arBasketItems["PRODUCT_ID"];
	}
}?>