<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

use \Rbs\Moysklad\Utils;
use \Rbs\Moysklad\Config;

//payments
$arAllOptions['pays'][] = GetMessage('PAYS_ATTRIBUTE_HEAD');
if(Utils::is_count($selectPropSkladCustomEntity)){
	$arAllOptions['pays'][] = ["pays_prop_enabled", GetMessage('PAYS_ATTRIBUTE_ENABLED'), '', ['checkbox', "N"]];
	$arAllOptions['pays'][] = ["pays_sync_prop", GetMessage("PAYS_SYNC_PROP"), '', ['selectbox', ['N' => GetMessage('NON_SYNC')] + $selectPropSkladCustomEntity]];

	$customEntityMetaHref = $request->get('pays_sync_prop') ? : Config::getOption("pays_sync_prop", '');
	$arPaysTypesEntityValues = [];
	if(!empty($customEntityMetaHref) &&  $customEntityMetaHref !== 'N') {
		$arPaysTypesEntityValues = CRbsMoyskladHelper::getAttributeEntityVariantForSelect($customEntityMetaHref);
		if (Utils::is_count($arPaysTypesEntityValues)) {
			$arPaysTypesEntityValuesWithZero = array_merge(['N' => GetMessage('PROPS_DISABLE')], $arPaysTypesEntityValues);
			foreach ($allPaysystemsServices as $pId => $pName) {
				$arAllOptions['pays'][] = ["pays_sync_prop_" . $pId,  $pName, '', ['selectbox', $arPaysTypesEntityValuesWithZero]];
			}
		} else {
			$arAllOptions['pays'][] = ['note' => GetMessage('EMPTY_CUSTOM_ENTITY_PROPS_VARIANT_PAYS')];
		}
	}

} else {
	$arAllOptions['pays'][] = ['note' => GetMessage('EMPTY_CUSTOM_ENTITY_PROPS')];
}

$arAllOptions['pays'][] = GetMessage('PAYS_DOCUMENT_HEAD');
$arAllOptions['pays'][] = ["pays_enabled", GetMessage('PAYS_ENABLED'), '', ['checkbox', "N"]];
$arAllOptions['pays'][] = ['note' => GetMessage('PAYS_NOTE')];
$arAllOptions['pays'][] = ["pays_sync_type", GetMessage("PAYS_SYNC_TYPE_OPTION"), '', ['selectbox', ['default' => GetMessage('PAYS_SYNC_TYPE_OPTION_DEFAULT'), 'full' => GetMessage('PAYS_SYNC_TYPE_OPTION_FULL')]]];
$arAllOptions['pays'][] = ['note' => GetMessage('PAYS_SYNC_TYPE_NOTE')];

$paysSyncType = $request->get('pays_sync_type') ? : Config::getOption("pays_sync_type", '');

if ($paysSyncType === 'full') {

	$arAllOptions['pays'][] = ["pays", GetMessage('DEMAND_TYPE_HEAD', [
		'#TYPE#' => GetMessage('PAYS_SYNC_TYPE_OPTION_FULL')
	]), '<hr>', ['statichtml', ""]];

	$arAllOptions['pays'][] = ["pays", "<b>" . GetMessage('PAYS_SYNC_METHOD_PAYSYS_DEFAULT') . "</b>", '', ['statichtml', ""]];

	if (Utils::is_count($allPaysystemsServices)) {
		$arAllOptions['pays'][] = ["def_payid_paymentin", GetMessage('DEF_PAYID_PAYMENTIN'), '', ['selectbox', $allPaysystemsServices]];
		$arAllOptions['pays'][] = ["def_payid_cashin", GetMessage('DEF_PAYID_CASHIN'), '', ['selectbox', $allPaysystemsServices]];
	}

	$arAllOptions['pays'][] = ['note' => GetMessage('PAYS_SYNC_METHOD_PAYSYS_DEFAULT_NOTE')];

	$arAllOptions['pays'][] = ["recalc_pays", GetMessage('PAYS_RECALC'), '', ['checkbox', "N"]];
	$arAllOptions['pays'][] = ['note' => GetMessage('PAYS_RECALC_NOTE')];
}

$arAllOptions['pays'][] = ["pays", GetMessage('PAYMENT_ALL_TYPE_HEAD'), '<hr>', ['statichtml', ""]];

$arAllOptions['pays'][] = ["payment_vatsum_first", GetMessage("PAYMENT_VATSUM_FIRST_PAYMENT"), '', ['checkbox', "N"]];

$arAllOptions['pays'][] = ["payment_sync_id", GetMessage("PAYMENT_PAYMENT_SYNC_ID"), '', ['selectbox', [
	'N' => GetMessage("NON_SET"),
	'ID' => GetMessage("PAYMENT_NAME_BX_FIELD_ID"),
	'ACCOUNT_NUMBER' => GetMessage("PAYMENT_NAME_BX_FIELD_ACC")
]]];

$arAllOptions['pays'][] = ["payment_sync_id_comment", GetMessage("PAYMENT_PAYMENT_SYNC_ID_COMMENT"), '', ['selectbox', [
	'N' => GetMessage("NON_GIVE"),
	'ID' => GetMessage("PAYMENT_NAME_BX_FIELD_ID"),
	'ACCOUNT_NUMBER' => GetMessage("PAYMENT_NAME_BX_FIELD_ACC")
]]];

$arAllOptions['pays'][] = ["payment_date_type", GetMessage('PAYMENT_DATE_TYPE'), 'DATE_BILL', ['selectbox', [
	'DATE_BILL' => GetMessage('PAYMENT_DATE_TYPE_DATE_BILL'),
	'DATE_PAID' => GetMessage('PAYMENT_DATE_TYPE_DATE_PAID'),
	'PAY_VOUCHER_DATE' => GetMessage('PAYMENT_DATE_TYPE_PAY_VOUCHER_DATE'),
]]];


$arAllOptions['pays'][] = ["pays", GetMessage('PAYS_SEARCH_HEAD'), '', ['statichtml', ""]];
$arAllOptions['pays'][] = ["pays_ext_ms", GetMessage("PAYS_EXT_MS"), '', ['checkbox', "N"]];
$arAllOptions['pays'][] = ["pays_ext_ms_field", GetMessage('PAYS_EXT_MS_FIELD'), '', ['selectbox', [
	'ID' => GetMessage("PAYS_NAME_BX_FIELD_ID"),
	'XML_ID' => GetMessage("ORDER_NAME_BX_FIELD_XML_ID"),
]]];

$arAllOptions['pays'][] = GetMessage('PAYS_FIELDS_HEAD');
try {
	$paymentInMeta = \CRbsMoyskladHelper::getMetadataWithAttrs('paymentin', 86400 * 7);
} catch (\Bitrix\Main\SystemException $e) {
	CAdminMessage::ShowMessage([
		'MESSAGE' => $e->getMessage(),
		'HTML' => true
	]);
}
if (Utils::is_success($paymentInMeta) && Utils::array_exists($paymentInMeta, 'states')) {
	$selectPaymentIntates = [];
	foreach ($paymentInMeta->states as $paymentInState) {
		$selectPaymentIntates[$paymentInState->id] = $paymentInState->name;
	}
	$arAllOptions['pays'][] = ["pays_sync_status_paymentin", GetMessage("PAYS_SYNC_STATUS_PAYMENTIN"), '', ['selectbox', $selectPaymentIntates]];
}

try {
	$cashInMeta = \CRbsMoyskladHelper::getMetadataWithAttrs('cashin', 86400 * 7);
} catch (\Bitrix\Main\SystemException $e) {
	CAdminMessage::ShowMessage([
		'MESSAGE' => $e->getMessage(),
		'HTML' => true
	]);
}

if (Utils::is_success($cashInMeta) && Utils::array_exists($cashInMeta, 'states')) {
	$selectPaymentInStates = [];
	foreach ($cashInMeta->states as $paymentInState) {
		$selectPaymentInStates[$paymentInState->id] = $paymentInState->name;
	}
	$arAllOptions['pays'][] = ["pays_sync_status_cashin", GetMessage("PAYS_SYNC_STATUS_CASHIN"), '', ['selectbox', $selectPaymentInStates]];
}

$arAllOptions['pays'][] = ["pays_proj_cashin", GetMessage("PAYS_PROJ_CASHIN"), '', ['selectbox', ['N' => GetMessage('NON_SYNC')] + $projects]];
$arAllOptions['pays'][] = ["pays_proj_paymentin", GetMessage("PAYS_PROJ_PAYMENTIN"), '', ['selectbox', ['N' => GetMessage('NON_SYNC')] + $projects]];


$arAllOptions['pays'][] = GetMessage('PAYS_NAME_EXPORT_SETTINGS');

$arAllOptions['pays'][] = ["pays_info_enabled", GetMessage("PAYS_INFO_OPTION_ENABLED"), '', ['checkbox', "N"]];
if (Utils::is_count($selectPropsSkladStr)) {
	$arAllOptions['pays'][] = ["pays_info", GetMessage("PAYS_INFO_OPTION"), '', ['selectbox', ['N' => GetMessage('NON_SYNC')] + $selectPropsSkladStr]];
	$arAllOptions['pays'][] = ['note' => GetMessage('ORDER_PAYS_INFO_NOTE')];
} else {
	$arAllOptions['pays'][] = ['note' => GetMessage('EMPTY_STR_ALL_PROPS')];
}


$arAllOptions['pays'][] = ["pays_name_export_enabled", GetMessage("PAYS_NAME_EXPORT_OPTION_ENABLED"), '', ['checkbox', "N"]];
if (Utils::is_count($selectPropsSkladStr)) {
	$arAllOptions['pays'][] = ["pays_name_export", GetMessage("PAYS_NAME_EXPORT_OPTION"), '', ['selectbox', ['N' => GetMessage('NON_SYNC')] + $selectPropsSkladStr]];
	$arAllOptions['pays'][] = ['note' => GetMessage('ORDER_PAYS_NAME_EXPORT_NOTE')];
} else {
	$arAllOptions['pays'][] = ['note' => GetMessage('EMPTY_STR_TEXT_PROPS')];
}

$arAllOptions['pays'][] = GetMessage('PAYS_PAYSYS_GROUP_SETTINGS');
$arAllOptions['pays'][] = ["paysys_group_default", GetMessage('PAYS_PAYSYS_GROUP'), '', ['selectbox', ['N' => GetMessage('NON_SYNC')] + $selectGroupsMs]];

$arAllOptions['pays'][] = ["paysys_employee_default", GetMessage('PAYS_PAYSYS_EMPLOYEE'), '', ['selectbox', ['N' => GetMessage('NON_SYNC')] + $selectEmployeeMs]];

$arAllOptions['pays'][] = GetMessage('PAYS_PAYSYS_TYPE');
$selectTypePay = ['N' => GetMEssage('PROPS_DISABLE'), 'cashin' => GetMessage('PAYS_PAYSYS_CASHIN'), 'paymentin' => GetMessage('PAYS_PAYSYS_PAYMENTIN')];
$paySystemResult = \Bitrix\Sale\PaySystem\Manager::getList(['filter' => ['ACTIVE' => 'Y']]);
foreach ($allPaysystemsServices as $pId => $pName) {
	$arAllOptions['pays'][] = ["paysys_type_" . $pId, $pName, '', ['selectbox', $selectTypePay]];
}


if (Utils::is_count($selectOrgMs)) {
	$arAllOptions['pays'][] = GetMessage('PAYS_PAYSYS_ORG');
	if (Utils::is_count($allPaysystemsServices)) {
		foreach ($allPaysystemsServices as $pId => $pName) {
			$optionId = "paysys_org_" . $pId;
			$arAllOptions['pays'][] = [$optionId, $pName, '', ['selectbox', $selectOrgMs]];

			$orgId = $request->get($optionId) ? : Config::getOption($optionId, '');
			if (isset($selectOrgAccMs[$orgId]) && Utils::is_count($selectOrgAccMs[$orgId])) {
				$arAllOptions['pays'][] = ["paysys_acc_" . $pId, GetMessage('PAYS_PAYSYS_ACC'), '', ['selectbox', $selectOrgAccMs[$orgId]]];
			}
		}
	}
}

$customEntityAttrsPaymentIn = [];
if (Utils::array_exists($paymentInMeta, 'attributes')) {
	foreach ($paymentInMeta->attributes as $attr) {
		if ($attr->type === 'customentity') {
			$customEntityAttrsPaymentIn[$attr->id . ';' . $attr->customEntityMeta->href] = $attr->name;
		}
	}
}

$customEntityAttrsCashIn = [];
if (Utils::array_exists($cashInMeta, 'attributes')) {
	foreach ($cashInMeta->attributes as $attr) {
		if ($attr->type === 'customentity') {
			$customEntityAttrsCashIn[$attr->id . ';' . $attr->customEntityMeta->href] = $attr->name;
		}
	}
}


if ($paysSyncType === 'full' && (Utils::is_count($customEntityAttrsPaymentIn) || Utils::is_count($customEntityAttrsCashIn))) {
	$arAllOptions['pays'][] = GetMessage('PAYS_SYNC_METHOD_OPTION');

	$arAllOptions['pays'][] = ["pays_sync_method_enabled", GetMessage("PAYS_SYNC_METHOD_ENABLED"), '', ['checkbox', "N"]];
	$arAllOptions['pays'][] = ["note" => GetMessage("PAYS_SYNC_METHOD_NOTE")];

	foreach (['PAYMENTIN' => 'customEntityAttrsPaymentIn', 'CASHIN' => 'customEntityAttrsCashIn'] as $paymentType => $paymentPropVariable) {
		$attrs = ${$paymentPropVariable};
		$lowerPaymentType = mb_strtolower($paymentType);
		if (Utils::is_count($attrs)) {
			$arAllOptions['pays'][] = ["pays_sync_method_prop_note_" . $lowerPaymentType, "<b>" . GetMessage('PAYS_PAYSYS_' . $paymentType) . "</b>", '', ['statichtml', ""]];
			$arAllOptions['pays'][] = ["pays_sync_method_prop_" . $lowerPaymentType, GetMessage("PAYS_SYNC_METHOD_PROP"), '', ['selectbox', $attrs]];

			$requestVal = $request->get("pays_sync_method_prop_" . $lowerPaymentType) ? array_pop(explode(';', $request->get("pays_sync_method_prop_" . $lowerPaymentType))) : '';
			$customEntityAttrsPaymentHref = \Rbs\Moysklad\Config::getPayMethodPropHref($lowerPaymentType) ?  \Rbs\Moysklad\Config::getPayMethodPropHref($lowerPaymentType) : $requestVal;
			$arPaymentTypesEntityValues = [];
			if (!empty($customEntityAttrsPaymentHref)) {
				$paymentTypeEntity = \Rbs\Moysklad\ApiNew::get($customEntityAttrsPaymentHref);
				if (Utils::is_success($paymentTypeEntity)) {
					$paymentTypeEntityValues = \Rbs\Moysklad\ApiNew::get($paymentTypeEntity->entityMeta->href);
					if (Utils::is_success($paymentTypeEntityValues) && Utils::array_exists($paymentTypeEntityValues)) {
						foreach ($paymentTypeEntityValues->rows as $row) {
							$arPaymentTypesEntityValues[$row->id] = $row->name;
						}
					}
				}
			}

			if (Utils::is_count($arPaymentTypesEntityValues)) {
				$arPaymentTypesEntityValues = array_merge(['N' => GetMessage('PROPS_DISABLE')], $arPaymentTypesEntityValues);
				if (Utils::is_count($allPaysystemsServices)) {
					$arAllOptions['pays'][] = ["pays_sync_method_prop_title_" . $lowerPaymentType, "<b>" . GetMessage('PAYS_SYNC_METHOD_OPTION') . "</b>", '', ['statichtml', ""]];
					foreach ($allPaysystemsServices as $pId => $pName) {
						$arAllOptions['pays'][] = ["pays_sync_method_prop_{$lowerPaymentType}_{$pId}", $pName, '', ['selectbox', $arPaymentTypesEntityValues]];
					}
				}
			}
		}
	}
}

if (!empty($arAllOptions['pays'])) {
	$aTabs[] = [
		"DIV" => "payment",
		"TAB" => GetMessage("PAYS_HEAD"),
		"ICON" => "order_settings",
		"TITLE" => GetMessage("PAYS_HEAD")
	];
}