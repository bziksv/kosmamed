<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var array $arParams
 * @var array $arResult
 * @var SaleOrderAjax $component
 */

$arParams['SERVICES_IMAGES_SCALING'] = (string)($arParams['SERVICES_IMAGES_SCALING'] ?? 'adaptive');

if (function_exists('kmApplyOrderBasketPictures')) {
	if (!empty($arResult['GRID'])) {
		kmApplyOrderBasketPictures($arResult['GRID'], 110, 110);
	}
	if (!empty($arResult['JS_DATA']['GRID'])) {
		kmApplyOrderBasketPictures($arResult['JS_DATA']['GRID'], 110, 110);
	}
}

$component = $this->__component;
$component::scaleImages($arResult['JS_DATA'], $arParams['SERVICES_IMAGES_SCALING']);
