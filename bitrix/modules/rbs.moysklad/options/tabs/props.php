<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

use \Rbs\Moysklad\Utils;
use \Rbs\Moysklad\ApiNew;


$arAddressFields = [
	'ADDR_comment' => GetMessage('MS_FIELD_ADDR_COMMENT'),
	'ADDR_postalCode' => GetMessage('MS_FIELD_ADDR_INDEX'),
	'ADDR_country' => GetMessage('MS_FIELD_ADDR_COUNTRY'),
	'ADDR_region' => GetMessage('MS_FIELD_ADDR_REGION'),
	'ADDR_city' => GetMessage('MS_FIELD_ADDR_CITY'),
	'ADDR_street' => GetMessage('MS_FIELD_ADDR_STREET'),
	'ADDR_house' => GetMessage('MS_FIELD_ADDR_HOUSE'),
	'ADDR_apartment' => GetMessage('MS_FIELD_ADDR_ROOM'),
	'ADDR_addInfo' => GetMessage('MS_FIELD_ADDR_OTHER'),
];

$arPlannedDate = ['PLANNED_DATE' => GetMessage('MS_FIELD_PLANNED_DATE')];

$selectNoneOption = ['N' => GetMessage('PROPS_DISABLE')];

$arAllOptions['props'][] = GetMessage('PROPS_MAIN_SETTINGS');
$arAllOptions['props'][] = ["props_enabled", GetMessage('PROPS_ENABLED'), '', ['checkbox', "N"]];

$arAllOptions['props'][] = ["props_reverse_enabled", GetMessage('PROPS_REVERSE_ENABLED'), '', ['checkbox', "N"]];
$arAllOptions['props'][] = ['note' => GetMessage('PROPS_REVERSE_NOTE')];


if (Utils::is_count($requiredProps)) {

	$arAllOptions['props'][] = GetMessage('PROPS_REQS');
	foreach ($requiredProps as $type => $props) {
		switch ($type) {
			case 'double':
			case 'long':
				foreach ($props as $prop) {
					$arAllOptions['props'][] = ["prop_req_{$prop->id}", "[{$prop->type}] " . $prop->name, '', ['text', "30"]];
				}
				break;
			case 'time':
				foreach ($props as $prop) {
					$arAllOptions['props'][] = ["prop_req_{$prop->id}", "[{$prop->type}] " . $prop->name, '', ['selectbox', ['now' => GetMessage('DATE_NOW')]]];
				}
				break;
			case 'string':
			case 'text':
			case 'link':
				foreach ($props as $prop) {
					$arAllOptions['props'][] = ["prop_req_{$prop->id}", "[{$prop->type}] " . $prop->name, '', ['text', "30"]];
				}
				break;
			case 'employee':
			case 'project':
			case 'counterparty':
			case 'product':
			case 'store':
			case 'contract':
				$filter = ['limit' => 100];
				$entityList = ApiNew::get('/entity/' . $type, $filter);
				if (Utils::is_success($entityList) && Utils::array_exists($entityList)) {
					$employers = [];
					foreach ($entityList->rows as $row) {
						$employers[$row->id] = $row->name;
					}
					foreach ($props as $prop) {
						$arAllOptions['props'][] = ["prop_req_{$prop->id}", "[{$prop->type}] " . $prop->name, '', ['selectbox', $employers]];
					}
				}

				break;
			case 'customentity':
				foreach ($props as $prop) {
					$customEntity = ApiNew::get($prop->customEntityMeta->href);
					if (Utils::is_success($customEntity)) {
						$entityValues = ApiNew::get($customEntity->entityMeta->href);
						if (Utils::is_success($entityValues) && Utils::array_exists($entityValues)) {
							$entityValuesBx = [];
							foreach ($entityValues->rows as $row) {
								$entityValuesBx[$row->id] = $row->name;
							}
							$arAllOptions['props'][] = ["prop_req_{$prop->id}", "[{$prop->type}] " . $prop->name, '', ['selectbox', $entityValuesBx]];
						}
					}
				}
				break;
		}
	}
}

$arAllOptions['props'][] = GetMessage('PROPS_MATCHING');

if (Utils::is_count($arOrderPropsBx)) {
	foreach ($arOrderPropsBx as $typeId => $prop) {

		if (!isset($arPersonalTypesBx[$typeId])) {
			continue;
		}

		$arAllOptions['props'][] = "[{$arPersonalTypesBx[$typeId]['LID']}] {$arPersonalTypesBx[$typeId]['NAME']}";

		foreach ($prop as $propId => $propName) {

			$typeName = GetMessage('PROP_BX_TYPE_' . $arOrderPropsBxMeta[$propId]['TYPE']);

			$propName = "[{$typeName}] {$propName}";
			
			switch ($arOrderPropsBxMeta[$propId]['TYPE']) {
				case 'ENUM':
					$arAllOptions['props'][] = ["prop_bx_enum_{$propId}", $propName, '', ['selectbox', $selectNoneOption + $selectPropSkladCustomEntityIds + $selectPropSkladCustomEntityFieldsIds + $selectPropsSkladStr]];
					break;
				case 'Y/N':
					$arAllOptions['props'][] = ["prop_bx_{$propId}", $propName, '', ['selectbox', $selectNoneOption + $selectPropsSkladBool + $selectPropsSkladStr]];
					break;
				case 'LOCATION':
					$arAllOptions['props'][] = ["prop_bx_{$propId}", $propName, '', ['selectbox', $selectNoneOption + $selectPropsSkladStr]];
					foreach (['COUNTRY', 'REGION', 'CITY'] as $locationType) {

						$defaultValue = 'ADDR_' . mb_strtolower($locationType);

						$arAllOptions['props'][] = ["loc_{$locationType}_prop_bx_{$propId}", "[" . GetMessage("LOC_" . $locationType) . "] " . $propName, $defaultValue, ['selectbox', $selectNoneOption + $arAddressFields]];
						
					}
					break;
				case 'NUMBER':
					$arAllOptions['props'][] = ["prop_bx_{$propId}", $propName, '', ['selectbox', $selectNoneOption + $selectPropsSkladNumber]];
					break;
				case 'STRING':
					$arAllOptions['props'][] = ["prop_bx_{$propId}", $propName, '', ['selectbox', $selectNoneOption + $selectPropsSkladStr + $selectPropSkladCustomEntityIds + $arAddressFields]];
					break;
				case 'DATE':
					$arAllOptions['props'][] = ["prop_bx_{$propId}", $propName, '', ['selectbox', $selectNoneOption + $arPlannedDate + $selectPropsSkladDate]];
					break;
				case 'FILE':
					$arAllOptions['props'][] = ["prop_bx_{$propId}", $propName, '', ['selectbox', $selectNoneOption + $selectPropsSkladFile]];
					break;
			}
		}
	}
} else {
	$arAllOptions['props'][] = ['note' => GetMessage('WARNING_EMPTY_BX_PROPS_FOR_MATCHING')];
}

$arAllOptions['props'][] = GetMessage('DEF_FIELDS_PROPS');

if (Utils::is_count($selectPropsSkladStr)) {
	$arDefaultBxFields = \Rbs\Moysklad\Config::geOrdertFieldsBx();
	$selectPropsSkladStrWithZero = array_merge(['N' => GetMessage('PROPS_DISABLE')], $selectPropsSkladStr);
	foreach ($arDefaultBxFields as $defFieldId => $defFieldName) {
		$arAllOptions['props'][] = ["field_bx_{$defFieldId}", $defFieldName, '', ['selectbox', $selectPropsSkladStrWithZero]];
	}
} else {
	$arAllOptions['props'][] = ['note' => GetMessage('EMPTY_STR_ALL_PROPS')];
}

if (!empty($arAllOptions['props'])) {
	$aTabs[] = [
		"DIV" => "props",
		"TAB" => GetMessage("PROPS_HEAD"),
		"ICON" => "order_settings",
		"TITLE" => GetMessage("PROPS_HEAD")
	];
}