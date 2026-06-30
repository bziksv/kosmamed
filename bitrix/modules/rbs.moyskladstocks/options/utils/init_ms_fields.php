<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

$optionsPropsArray = \Rbs\MoyskladStocks\Services\MoyskladImportUtils::getOptionAttributesArray();

$selectStrPropsMs = $optionsPropsArray['selectStrPropsMs'];
$selectGabbPropsMs = $optionsPropsArray['selectGabbPropsMs'];
$selectBoolProps = $optionsPropsArray['selectBoolProps'];
$selectPropsForProps = $optionsPropsArray['selectPropsForProps'];


$selectGroup = \Rbs\MoyskladStocks\Config::getOptionGroupsArray(['filter' => 'pathName='], ['N' => GetMessage('ROOT_SECTION')]);