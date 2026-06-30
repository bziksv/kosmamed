<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

use \Rbs\Moysklad\Utils;

$isCrmEnable = \Bitrix\Main\Loader::includeModule('crm');

$paySystemResult = \Bitrix\Sale\PaySystem\Manager::getList(['filter' => ['ACTIVE' => 'Y', 'ENTITY_REGISTRY_TYPE' => 'ORDER']]);
$allPaysystemsServices = [];
while ($paySystem = $paySystemResult->fetch()) {
	$allPaysystemsServices[$paySystem['ID']] = "[{$paySystem['ID']}] {$paySystem['NAME']}";
}

$deliveryList = \Bitrix\Sale\Delivery\Services\Manager::getActiveList();
$allDeliveryServices = [];
foreach ($deliveryList as $k => $v) {
	if ($v['CLASS_NAME'] !== '\Bitrix\Sale\Delivery\Services\Group') {
		$allDeliveryServices[$v['ID']] = "[{$v['ID']}] {$v['NAME']}";
	}
}

/**props block params */
$orderPersonalTypesBx = \Bitrix\Sale\Internals\PersonTypeTable::getList([
	'filter' => [
		'ENTITY_REGISTRY_TYPE' => 'ORDER'
	]
]);
$arPersonalTypesBx = [];
while ($obOrderTypeBx = $orderPersonalTypesBx->fetch()) {
	$arPersonalTypesBx[$obOrderTypeBx['ID']] = $obOrderTypeBx;
}

$orderPersonalTypesBySiteIdBx = \Bitrix\Sale\Internals\PersonTypeTable::getList([
	'filter' => [
		'ENTITY_REGISTRY_TYPE' => 'ORDER'
	]
]);
$arPersonalTypesBySiteIdBx = [];
while ($obOrderTypeBx = $orderPersonalTypesBySiteIdBx->fetch()) {
	$arPersonalTypesBySiteIdBx[$obOrderTypeBx['LID']][$obOrderTypeBx['ID']] = $obOrderTypeBx;
}

$orderPropsBx = \Bitrix\Sale\Internals\OrderPropsTable::getList([
	'filter' => [
		'ENTITY_REGISTRY_TYPE' => 'ORDER'
	]
]);
$arOrderPropsBx = [];
$arOrderPropsBxMeta = [];
$arOrderStrPropsByPersonal = [];
$arOrderEnumPropsByPersonal = [];
$arOrderStringPropsByPersonal = [];
while ($obOrderPropBx = $orderPropsBx->fetch()) {
	$arOrderPropsBx[$obOrderPropBx['PERSON_TYPE_ID']][$obOrderPropBx['ID']] = "[{$obOrderPropBx['ID']}] {$obOrderPropBx['NAME']}";
	$arOrderPropsBxMeta[$obOrderPropBx['ID']] = $obOrderPropBx;

	if ($obOrderPropBx['TYPE'] === 'STRING') {
		$arOrderStrPropsByPersonal[$obOrderPropBx['PERSON_TYPE_ID']][$obOrderPropBx['ID']] = "[{$obOrderPropBx['ID']}] {$obOrderPropBx['NAME']}";
	}

	if ($obOrderPropBx['TYPE'] === 'ENUM') {
		$arOrderEnumPropsByPersonal[$obOrderPropBx['PERSON_TYPE_ID']][$obOrderPropBx['ID']] = "[{$obOrderPropBx['ID']}] {$obOrderPropBx['NAME']}";
	}

	if ($obOrderPropBx['TYPE'] === 'STRING') {
		$arOrderStringPropsByPersonal[$obOrderPropBx['PERSON_TYPE_ID']][$obOrderPropBx['ID']] = "[{$obOrderPropBx['ID']}] {$obOrderPropBx['NAME']}";
	}
}
/**props block params */

$statusResult = \Bitrix\Sale\Internals\StatusLangTable::getList(array(
	'order' => array('STATUS.SORT' => 'ASC'),
	'filter' => array('STATUS.TYPE' => 'O', 'LID' => LANGUAGE_ID),
	'select' => array('STATUS_ID', 'NAME', 'DESCRIPTION'),
));
$statusSite = [];
while ($obStatus = $statusResult->fetch()) {
	$statusSite[$obStatus['STATUS_ID']] = "[{$obStatus['STATUS_ID']}] {$obStatus['NAME']}";
}

$statusResult = \Bitrix\Sale\Internals\StatusLangTable::getList(array(
	'order' => array('STATUS.SORT' => 'ASC'),
	'filter' => array('STATUS.TYPE' => 'D', 'LID' => LANGUAGE_ID),
	'select' => array('STATUS_ID', 'NAME', 'DESCRIPTION'),
));
$statusShipment = [];
while ($obStatus = $statusResult->fetch()) {
	$statusShipment[$obStatus['STATUS_ID']] = "[{$obStatus['STATUS_ID']}] {$obStatus['NAME']}";
}

$deliveryList = \Bitrix\Sale\Delivery\Services\Manager::getActiveList();
$parentIds = [];
$groupsIds = [];
foreach ($deliveryList as $k => $v) {
	if ($v['CLASS_NAME'] === '\Bitrix\Sale\Delivery\Services\Group') {
		unset($deliveryList[$k]);
		$groupsIds[] = $k;
	}
	$parentIds[(int)$v['PARENT_ID']][] = $v['ID'];
}

if (Utils::is_count($groupsIds)) {
	foreach ($parentIds as $parentId => $childrens) {
		if (in_array($parentId, $groupsIds)) {
			if (Utils::is_count($parentIds[0])) {
				$parentIds[0] = array_merge($parentIds[0], $childrens);
			} else {
				$parentIds[0] = $childrens;
			}
			unset($parentIds[$parentId]);
		}
	}
}

$vector = [
	'FULL' => GetMessage('VECTOR_FULL'),
	'BX_MS' => GetMessage('VECTOR_BX_MS'),
	'MS_BX' => GetMessage('VECTOR_MS_BX'),
];