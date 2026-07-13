<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

use \Rbs\Moysklad\Utils;
use \Rbs\Moysklad\Config;

if (\Bitrix\Main\Loader::includeModule('despi.moyskladusers')) {
	$arAllOptions['counter'][] = GetMessage('COUNTER_DESPI_MOYSKLADUSERS_INTEGRATION');
	$arAllOptions['counter'][] = ["counter_dmu_enable",  GetMessage('COUNTER_DESPI_MOYSKLADUSERS_INTEGRATION_ENABLE'), '', ['checkbox', '']];

	$arAllOptions['counter'][] = ['', GetMessage('EXPORT_DESPI_MSUSERS_PARAMS'), '<hr/>', ['statichtml']]; 
	
	$arAllOptions['counter'][] = ["counter_dmu_search",  GetMessage('COUNTER_DESPI_MOYSKLADUSERS_INTEGRATION_SEARCH_ENABLE'), '', ['checkbox', '']];
	$arAllOptions['counter'][] = ["counter_dmu_add",  GetMessage('COUNTER_DESPI_MOYSKLADUSERS_INTEGRATION_ADD_ENABLE'), '', ['checkbox', '']];
	
	$arAllOptions['counter'][] = ['', GetMessage('IMPORT_DESPI_MSUSERS_PARAMS'), '<hr/>', ['statichtml']];

	$arAllOptions['counter'][] = ["counter_dmu_import",  GetMessage('COUNTER_DESPI_MOYSKLADUSERS_INTEGRATION_IMPORT_ENABLE'), '', ['checkbox', '']];

	$arAllOptions['counter'][] = ['note' => GetMessage('COUNTER_EXT_CODECOUNTER_DESPI_MOYSKLADUSERS_INTEGRATION_NOTE')];
}

$isPersonalDivide = $isSaveHit ? $request->get("counter_personal_divide") === 'Y' : Config::checkFeature('counterpersonaldivide');

$personTypeBx = \Bitrix\Sale\Internals\PersonTypeTable::getList(['filter' => ['ENTITY_REGISTRY_TYPE' => 'ORDER'], 'select' => ['*']])->fetchAll();

$getCounterPartyFieldsSearch = \Rbs\Moysklad\Config::getCounterPartyFieldsSearch();

$arAllOptions['counter'][] = GetMessage('COUNTER_EXT_CODE_DEFAULT_OPTIONS');

$arAllOptions['counter'][] = ["counter_default_hard_set",  GetMessage('COUNTER_DEFAULT_HARD_SET'), '', ['checkbox', '']];

if (Utils::is_count($personTypeBx)) {
	foreach ($personTypeBx as $pType) {
		$arAllOptions['counter'][] = ["counter_ext_code_{$pType['ID']}",  GetMessage('COUNTER_EXT_CODE_FOR_PTYPE', ['#PERSON_TYPE#' => "[{$pType['ID']}] {$pType['NAME']}"]), 'DEFAULT_RBS_MOYSKLAD', ['text', '']]; 
	}
} else {
	$arAllOptions['counter'][] = ["counter_ext_code_default",  GetMessage('COUNTER_EXT_CODE_DEFAULT'), 'DEFAULT_RBS_MOYSKLAD', ['text', '']];
}

$arAllOptions['counter'][] = ['note' => GetMessage('COUNTER_EXT_CODE_DEFAULT_NOTE')];


$arAllOptions['counter'][] = GetMessage('COUNTER_SETTINGS_API');

if (Utils::is_count($getCounterPartyFieldsSearch)) {

	if (Utils::is_count($personTypeBx)) {
		$arAllOptions['counter'][] = ["counter_personal_divide",  GetMessage('COUNTER_PERSONAL_DIVIDE'), '', ['checkbox', '']];
		$showAlertForSaveCheckbox[] = 'counter_personal_divide';
	}

	if ($isPersonalDivide) {

		if (Utils::is_count($personTypeBx)) {

			$arAllOptions['counter'][] = ['<hr>', '<hr>', '', ['statichtml']]; 

			foreach ($personTypeBx as $pType) {

				$arAllOptions['counter'][] = ["counter",  GetMessage('COUNTER_TYPE_SUBHEADER', [
						'#ID#' => $pType['ID'],
						'#NAME#' => $pType['NAME'],
						'#SITE_ID#' => $pType['LID']
					]), '', ['statichtml', ""]
				];

				$arAllOptions['counter'][] = ["search_only_props_{$pType['ID']}",  GetMessage
				('SEARCH_ONLY_PROPS'), '', ['checkbox', '']];
				$showAlertForSaveCheckbox[] = "search_only_props_{$pType['ID']}";

				$isOnlyPropsSearch = $isSaveHit ? $request->get("search_only_props_{$pType['ID']}") === 'Y' : \Rbs\Moysklad\Config::isOnlyPropsSearchPtype($pType['ID']);

				if ($isOnlyPropsSearch) {
					unset($getCounterPartyFieldsSearch['externalCode']);
				}

				$arAllOptions['counter'][] = ["search_without_ctype_{$pType['ID']}",  GetMessage('SEARCH_WITHOUT_CTYPE'), '', ['checkbox', '']];
				
				$arAllOptions['counter'][] = ["counter_search_fields_ptype_{$pType['ID']}", GetMessage('COUNTER_SEARCH_FIELD'), '', ['multiselectbox', $getCounterPartyFieldsSearch]];
				$showAlertForSaveSelect[] = "counter_search_fields_ptype_{$pType['ID']}";

				$counterSearchFields = $request->get("counter_search_fields_ptype_{$pType['ID']}") ?: \Rbs\Moysklad\Config::getSearchCounterpartyPropsDivide($pType['ID']);

				if (Utils::is_count($counterSearchFields)) {

					$arPriorsSearch = [];
					foreach ($counterSearchFields as $k => $searchField) {
						$arPriorsSearch[] = $k + 1;
					}

					foreach ($counterSearchFields as $k => $searchField) {

						if(!isset($getCounterPartyFieldsSearch[$searchField])) {
							continue;
						}

						$arAllOptions['counter'][] = [
							"counter",  GetMessage('COUNTER_SERACH_FIELD_HEADER', [
								'#PROP#' => $getCounterPartyFieldsSearch[$searchField],
							]), '', ['statichtml', ""]
						];

						$arAllOptions['counter'][] = ["counter_search_field_{$searchField}_ptype_{$pType['ID']}",  GetMessage('COUNTER_SEARCH_FIELD_PRIORITY'), $k, ['selectbox', $arPriorsSearch]];

						if ($isOnlyPropsSearch || \Rbs\Moysklad\Config::getAssociatedUserField($searchField) === 'PROPERTY_ORDER') {
							if (isset($arOrderPropsBx[$pType['ID']]) && Utils::is_count($arOrderPropsBx[$pType['ID']])) {
								$arAllOptions['counter'][] = ["counter_search_field_{$searchField}_ptype_{$pType['ID']}_prop",  GetMessage('COUNTER_SEARCH_FIELD_PROPERTY'), '', ['selectbox', $arOrderPropsBx[$pType['ID']]]];
							}
						}

					}

				}

				$arAllOptions['counter'][] = ['<hr>', '<hr>', '', ['statichtml']]; 

			}

			$arAllOptions['counter'][] = ['note' => GetMessage('COUNTER_SEARCH_FIELD_NOTE')];
			$arAllOptions['counter'][] = ['note' => GetMessage('COUNTER_SKIP_EX_NOTE')];

			$arAllOptions['counter'][] = ["counter_search_ip",  GetMessage('COUNTER_SEARCH_IP'), '', ['checkbox', '']];
			$arAllOptions['counter'][] = ['note' => GetMessage('COUNTER_SEARCH_IP_NOTE')];
		} 

	} else {

		unset($getCounterPartyFieldsSearch['inn']);

		$arAllOptions['counter'][] = ["counter_search_fields",  GetMessage('COUNTER_SEARCH_FIELD'), '', ['multiselectbox', $getCounterPartyFieldsSearch]];

		$counterSearchFields = $request->get("counter_search_fields") ?: \Rbs\Moysklad\Config::getSearchCounterpartyProps();

		if (Utils::is_count($counterSearchFields)) {
			$arPriorsSearch = [];
			foreach ($counterSearchFields as $k => $searchField) {
				$arPriorsSearch[] = $k + 1;
			}
			foreach ($counterSearchFields as $k => $searchField) {

				$arAllOptions['counter'][] = [
					"counter",  GetMessage('COUNTER_SERACH_FIELD_HEADER', [
						'#PROP#' => $getCounterPartyFieldsSearch[$searchField],
					]), '', ['statichtml', ""]
				];

				$arAllOptions['counter'][] = ["counter_search_field_{$searchField}",  GetMessage('COUNTER_SEARCH_FIELD_PRIORITY'), $k, ['selectbox', $arPriorsSearch]];
			}
		}
		$arAllOptions['counter'][] = ['note' => GetMessage('COUNTER_SEARCH_FIELD_NOTE')];
	}

	$arAllOptions['counter'][] = ["counter_search_phone",  GetMessage('COUNTER_SEARCH_PHONE'), '', ['selectbox', \Rbs\Moysklad\Config::getSearchTypes()]];
	$arAllOptions['counter'][] = ['note' => GetMessage('COUNTER_SEARCH_PHONE_NOTE')];

	$arAllOptions['counter'][] = GetMessage('COUNTER_SETTINGS_CREATE_FIELDS');
	if (Utils::is_count($personTypeBx)) {

		$counterPartyTypes = \Rbs\Moysklad\Config::getCounterPartyTypes();
		foreach ($personTypeBx as $pType) {

			$pTypeId = $pType['ID'];

			$arAllOptions['counter'][] = ["counter",  GetMessage('COUNTER_TYPE_SUBHEADER', [
				'#ID#' => $pTypeId,
				'#NAME#' => $pType['NAME'],
				'#SITE_ID#' => $pType['LID']
			]), '', ['statichtml', ""]];

			$arAllOptions['counter'][] = ["counter_type_{$pTypeId}",  GetMessage('COUNTERT_TYPE_MS'), '', ['selectbox', $counterPartyTypes]];

			if (Utils::is_count($counterpartyStates)) {
				$counterpartyStatesSelect = ['N' => GetMessage('COUNTER_STATE_MS_NONE')] + $counterpartyStates;
				$arAllOptions['counter'][] = ["counter_state_{$pTypeId}",  GetMessage('COUNTER_STATE_MS'), '', ['selectbox', $counterpartyStatesSelect]];
			}

			$arAllOptions['counter'][] = ["skip_ex_code_{$pTypeId}",  GetMessage('SKIP_EX_CODE'), '', ['checkbox', '']];
			$arAllOptions['counter'][] = ["skip_code_{$pTypeId}",  GetMessage('SKIP_CODE'), '', ['checkbox', '']];

			$arAllOptions['counter'][] = ["counter_tags_{$pTypeId}",  GetMessage('COUNTER_TAGS'), '', ['textarea', '']];

			$arAllOptions['counter'][] = ['<hr>', '<hr>', '', ['statichtml']]; 

		}

		

	} else {
		$arAllOptions['counter'][] = ['note' => GetMessage('COUNTER_NEED_CREATE_PERSONAL_TYPES')];
	}

	$arAllOptions['counter'][] = ["counter",  GetMessage('COUNTER_CREATE_SHARED_FIELDS'), '', ['statichtml', ""]];

	$arAllOptions['counter'][] = ["counter_format_phone", GetMessage('COUNTER_FROMAT_PHONE'), '', ['checkbox', '']];
	$arAllOptions['counter'][] = ["counter_format_phone_type",  GetMessage('COUNTER_FROMAT_PHONE_TYPE'), '', ['selectbox', [
		'WITH_PLUS' => GetMessage('COUNTER_FROMAT_PHONE_TYPE_WITH_PLUS'),
		'WITHOUT_PLUS' => GetMessage('COUNTER_FROMAT_PHONE_TYPE_WITHOUT_PLUS'),
	]]];
}

$arAllOptions['counter'][] = GetMessage('COUNTER_SETTINGS_DEF');

$arAllOptions['counter'][] = ["save_force_enabled", GetMessage('COUNTER_SAVE_FORCE'), '', ['checkbox', "N"]];
$arAllOptions['counter'][] = ['note' => GetMessage('COUNTER_SAVE_FORCE_NOTE')];

$arAllOptions['counter'][] = ["counter_ext_write",  GetMessage('COUNTER_EXT_WRITE'), '', ['checkbox', '']];
$arAllOptions['counter'][] = ['note' => GetMessage('COUNTER_EXT_WRITE_NOTE')];

$counterPartyDefaultFields = \Rbs\Moysklad\Config::getCounterPartyFields();
if (Utils::is_count($arOrderPropsBx) && Utils::is_count($counterPartyDefaultFields)) {

	foreach ($arOrderPropsBx as $typeId => $prop) {

		if (!isset($arPersonalTypesBx[$typeId])) {
			continue;
		}

		$arAllOptions['counter'][] = "[{$arPersonalTypesBx[$typeId]['LID']}] {$arPersonalTypesBx[$typeId]['NAME']}";

		if(Utils::is_count($arOrderStringPropsByPersonal[$typeId])) {
			$arAllOptions['counter'][] = ["counter_name_field_{$typeId}", GetMessage('COUNTER_NAME_FIELD'), '', ['selectbox', ['N' => GetMessage('NON_USE')] + $arOrderStringPropsByPersonal[$typeId]]];
			$arAllOptions['counter'][] = ['<hr>', '<hr>', '', ['statichtml']]; 
		}
		
		foreach ($prop as $propId => $propName) {
			switch ($arOrderPropsBxMeta[$propId]['TYPE']) {
				case 'STRING':
				case 'ENUM':
				case 'NUMBER':
				case 'LOCATION':
					$arAllOptions['counter'][] = ["counter_fields_{$propId}", $propName, '', ['selectbox', $counterPartyDefaultFields]];
				break;
			}
		}

	}

}

$arCounterPropsStrMs = [];
try {
	$metaCounterParty = \CRbsMoyskladHelper::getMetadataWithAttrs('counterparty', 86400 * 7);
} catch (\Bitrix\Main\SystemException $e) {
	CAdminMessage::ShowMessage([
		'MESSAGE' => $e->getMessage(),
		'HTML' => true
	]);
}

if (Utils::array_exists($metaCounterParty, 'attributes')) {
	foreach ($metaCounterParty->attributes as $attrib) {
		if (in_array($attrib->type, ['string', 'text'])) {
			$arCounterPropsStrMs[$attrib->id] = $attrib->name;
		}
	}
}

if (Utils::is_count($arOrderPropsBx) && Utils::is_count($arCounterPropsStrMs)) {
	$arAllOptions['counter'][] = GetMessage('COUNTER_SETTINGS_PROPS');
	/* $arAllOptions['counter'][] = ["counter_props_enabled", GetMessage('COUNTER_PROPS_ENABLED'), '', ['checkbox', "N"]]; */
	$arCounterPropsStrMsWithZero = array_merge(['N' => GetMessage('PROPS_DISABLE')], $arCounterPropsStrMs);
	foreach ($arOrderPropsBx as $typeId => $prop) {
		$arAllOptions['counter'][] = "[{$arPersonalTypesBx[$typeId]['LID']}] {$arPersonalTypesBx[$typeId]['NAME']}";
		foreach ($prop as $propId => $propName) {
			switch ($arOrderPropsBxMeta[$propId]['TYPE']) {
				case 'STRING':
				case 'ENUM':
				case 'NUMBER':
				case 'LOCATION':
					$arAllOptions['counter'][] = ["counter_prop_{$propId}", $propName, '', ['selectbox', $arCounterPropsStrMsWithZero]];
					break;
			}
		}
	}
	$arAllOptions['counter'][] = ['note' => GetMessage('COUNTER_PROPS_NOTE')];
}

$arAllOptions['counter'][] = GetMessage('COUNTER_IMPORT_SETTINGS');
$arAllOptions['counter'][] = ["change_user_by_cp", GetMessage('CHANGE_USER_BY_COUNTER'), '', ['checkbox', "N"]];

if (!empty($arAllOptions['counter'])) {
	$aTabs[] = [
		"DIV" => "counterparty",
		"TAB" => GetMessage("COUNTER_HEAD"),
		"ICON" => "order_settings",
		"TITLE" => GetMessage("COUNTER_HEAD")
	];
}