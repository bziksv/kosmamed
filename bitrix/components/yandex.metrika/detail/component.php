<?

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

if (empty($arParams['PRODUCT_ID'])) {
	return;
}

$productData = \Yandex\Metrika\Ecommerce::getEcProductData($arParams['PRODUCT_ID'], [], empty($arParams['OFFER_CART_PROPS']));

if (!empty($arParams['OFFER_CART_PROPS'])) {
	$propsValues = \Yandex\Metrika\Ecommerce::getOfferPropsValues($arParams['PRODUCT_ID'], $arParams['OFFER_CART_PROPS']);
	if ($propsValues) {
		$productData['variant'] = implode(', ', $propsValues);
	}
}

$arResult['ACTION'] = \Yandex\Metrika\Ecommerce::buildAction('detail', [
	$productData
]);


$this->IncludeComponentTemplate();

