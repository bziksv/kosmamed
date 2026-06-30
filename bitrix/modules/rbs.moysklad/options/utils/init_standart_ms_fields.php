<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

use \Rbs\Moysklad\Utils;
use \Rbs\Moysklad\ApiNew;

$demandStatesMs = ApiNew::get('/entity/demand/metadata', [], 10);
$demandStates = [];
if (Utils::is_success($demandStatesMs) && Utils::array_exists($demandStatesMs, 'states')) {
	foreach ($demandStatesMs->states as $state) {
		$demandStates[$state->id] = $state->name;
	}
}

$counterpartyStatesMs = ApiNew::get('/entity/counterparty/metadata', [], 10);
$counterpartyStates = [];
if (Utils::is_success($counterpartyStatesMs) && Utils::array_exists($counterpartyStatesMs, 'states')) {
	foreach ($counterpartyStatesMs->states as $state) {
		$counterpartyStates[$state->id] = $state->name;
	}
}

$demandAttrsMs = ApiNew::get('/entity/demand/metadata/attributes', [], 10);
$demandAttrs = [];
$demandAttrsFields = [];
if (Utils::is_success($demandAttrsMs) && Utils::array_exists($demandAttrsMs)) {
	foreach ($demandAttrsMs->rows as $row) {
		$type = in_array($row->type, ['string', 'text', 'link']) ? 'string' : $row->type;
		$demandAttrs[$type][$row->id] = "[{$row->type}] {$row->name}";
		$demandAttrsFields[$row->id] = $row;
	}
}

$projectsMs = ApiNew::get('/entity/project', [], 10);
$projects = [];
if (Utils::is_success($projectsMs) && Utils::array_exists($projectsMs)) {
	foreach ($projectsMs->rows as $row) {
		$projects[$row->id] = $row->name;
	}
}

$groupsMs = ApiNew::get('/entity/group', [], 10);
$selectGroupsMs = [];
if (Utils::is_success($groupsMs) && Utils::array_exists($groupsMs)) {
	foreach ($groupsMs->rows as $row) {
		$selectGroupsMs[$row->id] = $row->name;
	}
}

$employeeMs = ApiNew::get('/entity/employee', [], 10);
$selectEmployeeMs = [];
if (Utils::is_success($employeeMs) && Utils::array_exists($employeeMs)) {
	foreach ($employeeMs->rows as $row) {
		$selectEmployeeMs[$row->id] = $row->name;
	}
}

$salesChannelsMs = ApiNew::get('/entity/saleschannel', [], 10);
$selectSalesChannelMs = [];
if (Utils::is_success($salesChannelsMs) && Utils::array_exists($salesChannelsMs)) {
	foreach ($salesChannelsMs->rows as $row) {
		$selectSalesChannelMs[$row->id] = $row->name;
	}
}

$orgMs = ApiNew::get('/entity/organization', [], 10);
$selectOrgMs = [];
$selectOrgAccMs = [];
if (Utils::is_success($orgMs) && Utils::array_exists($orgMs)) {
	foreach ($orgMs->rows as $row) {
		$selectOrgMs[$row->id] = $row->name;
		$accounts = ApiNew::get('/entity/organization/' . $row->id .'/accounts', [], 10);
		if (Utils::is_success($accounts) && Utils::array_exists($accounts)) {
			foreach ($accounts->rows as $rowAcc) {
				$selectOrgAccMs[$row->id][$rowAcc->id] = $rowAcc->accountNumber;
			}
		}
	}
}

$storeMs = ApiNew::get('/entity/store', [], 10);
$storeMsOptions = [];
if (Utils::is_success($storeMs) && Utils::array_exists($storeMs)) {
	foreach ($storeMs->rows as $storeRow) {
		$storeMsOptions[$storeRow->id] = $storeRow->name;
	}
}

try {
	$metaOrder = \CRbsMoyskladHelper::getMetadataWithAttrs('customerorder');
} catch (\Bitrix\Main\SystemException $e) {
	CAdminMessage::ShowMessage([
		'MESSAGE' => $e->getMessage(),
		'HTML' => true
	]);
}


$selectPropsSkladStr = [];
$selectPropSkladCustomEntity = [];
$selectPropSkladCustomEntityIds = [];
$selectPropSkladCustomEntityFieldsIds = [];
$selectPropsSkladBool = [];
$selectPropsSkladNumber = [];
$selectPropsSkladDate = [];
$selectPropsSkladFile = [];
$selectEmployeProp = [];
$requiredProps = [];

if (Utils::array_exists($metaOrder, 'attributes')) {

	foreach ($metaOrder->attributes as $attrKey => $attrib) {

		$typeName = GetMessage('PROP_MS_TYPE_' . $attrib->type);

		if (in_array($attrib->type, ['string', 'text', 'link'])) {
			$selectPropsSkladStr[$attrib->id] = "[{$typeName}] " . $attrib->name;
		}
		if (in_array($attrib->type, ['customentity'])) {
			$selectPropSkladCustomEntity[$attrib->customEntityMeta->href] = $attrib->name;
			$selectPropSkladCustomEntityIds[$attrib->id . ';' . array_pop(explode('/', $attrib->customEntityMeta->href))] = "[{$typeName}] " . $attrib->name;
		}
		if (in_array($attrib->type, ['boolean'])) {
			$selectPropsSkladBool[$attrib->id] = "[{$typeName}] " . $attrib->name;
		}
		if (in_array($attrib->type, ['time'])) {
			$selectPropsSkladDate[$attrib->id] = "[{$typeName}] " . $attrib->name;
		}
		if (in_array($attrib->type, ['long', 'double'])) {
			$selectPropsSkladNumber[$attrib->id] = "[{$typeName}] " . $attrib->name;
		}
		if (in_array($attrib->type, ['file'])) {
			$selectPropsSkladFile[$attrib->id] = "[{$typeName}] " . $attrib->name;
		}

		if (in_array($attrib->type, ['employee', 'project', 'store', 'product', 'counterparty', 'contract'])) {
			$selectPropSkladCustomEntityFieldsIds[$attrib->id . ';' . $attrib->type] = "[{$typeName}] " . $attrib->name;
		}

		if (in_array($attrib->type, ['employee'])) {
			$selectEmployeProp[$attrib->id] = $attrib->name;
		}

		if ($attrib->required) {
			$requiredProps[$attrib->type][$attrib->id] = $attrib;
		}
	}

}

$taxRates = \Rbs\MoySklad\Entity\TaxRate::getTaxRateArray();
$taxRatesSelect = [
	'N' => GetMessage('VAT_OFF')
];
foreach($taxRates as $taxRateId => $rate) {
	$taxRatesSelect[$rate] = $rate . '%';
}

$selectFolders = [];
$msFolders = \Rbs\Moysklad\ApiNew::get('/entity/productfolder', ['filter' => 'pathName='], 60);
if (Utils::is_success($msFolders) && Utils::array_exists($msFolders)) {
	foreach ($msFolders->rows as $row) {
		$selectFolders[$row->id] = $row->name;
	}
}

$priceTypes = \Rbs\Moysklad\ApiNew::get('/context/companysettings/pricetype/', [], 60);
$priceTypesSync = [];
if (Utils::is_count($priceTypes)) {
	foreach ($priceTypes as $priceType) {
		$priceTypesSync[$priceType->name] = $priceType->name;
	}
} 

try {
	$metaProduct = \CRbsMoyskladHelper::getMetadataWithAttrs('product');
} catch (\Bitrix\Main\SystemException $e) {
	CAdminMessage::ShowMessage([
		'MESSAGE' => $e->getMessage(),
		'HTML' => true
	]);
}

$selectPropsProductStr = [];
$selectPropsProductNumber = [];

if (Utils::array_exists($metaProduct, 'attributes')) {
	foreach ($metaProduct->attributes as $attrKey => $attrib) {
		$typeName = GetMessage('PROP_MS_TYPE_' . $attrib->type);
		if (in_array($attrib->type, ['string', 'text', 'link'])) {
			$selectPropsProductStr['string:' . $attrib->id] = "[{$typeName}] " . $attrib->name;
		}
		if (in_array($attrib->type, ['long'])) {
			$selectPropsProductNumber['integer:' . $attrib->id] = "[{$typeName}] " . $attrib->name;
		}
	}
}