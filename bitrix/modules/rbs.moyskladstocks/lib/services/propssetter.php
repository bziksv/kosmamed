<?php

namespace Rbs\MoyskladStocks\Services;

use Rbs\MoyskladStocks\ApiNew;
use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\HlCache\ExtCodes;
use Rbs\MoyskladStocks\Internals\FieldList;
use Rbs\MoyskladStocks\Internals\ProductFinder\ProductIdentifier;
use Rbs\MoyskladStocks\Internals\Enums\TrackingType;

class PropsSetter
{
	private $itemBx;
	private $itemMs;
	private $propList;
	private $propBxTypes;
	private $isEmptyImport;
	private $arAllPropsValues;
	private $arPropsSets = [];
	private $fieldList;

	public function __construct(
		$itemBx = [],
		$itemMs = null,
		$propList = [],
		$propBxTypes = [],
		$isEmptyImport = false,
		$arAllPropsValues = []
	) {
		$this->itemBx = $itemBx;
		$this->itemMs = $itemMs;
		$this->propList = $propList;
		$this->propBxTypes = $propBxTypes;
		$this->isEmptyImport = $isEmptyImport;
		$this->arAllPropsValues = $arAllPropsValues;

		$this->fieldList = (new FieldList($itemMs->meta->type))->getFieldList();
	}

	public function getArPropsSets()
	{
		return $this->arPropsSets;
	}

	public function processProperties()
	{
		$attrMsList = $this->getAllAttrList($this->itemMs->{'attributes'});

		foreach ($this->propList as $propBx => $propMs) {

			if ($propMs === 'N' || empty($propMs)) {
				continue;
			}

			$isField = mb_strpos($propMs, 'field_') !== false;

			if (!isset($this->propBxTypes[$propBx])) {
				continue;
			}

			if (!isset($attrMsList[$propMs]) && !$isField) {
				$this->handleEmptyPropCase($propBx);
				continue;
			}

			if (!$isField) {
				$this->processAttribute($propBx, $attrMsList);
			} else {
				$this->processField($propBx);
			}
		}

		return $this->arPropsSets;
	}

	private function getAllAttrList($attributes)
	{
		$attrList = [];
		if (!empty($attributes)) {
			foreach ($attributes as $attr) {
				$attrList[$attr->{'id'}] = $attr;
			}
		}
		return $attrList;
	}

	private function handleEmptyPropCase($propBx)
	{
		if ($this->isEmptyImport) {
			$this->handleEmptyImportCase($propBx);
		}
	}

	private function handleEmptyImportCase($propBx)
	{
		$propType = $this->propBxTypes[$propBx]['TYPE'];
		$currentValue = $this->getCurrentValue($propBx, $propType);

		if (!empty($currentValue)) {
			$this->arPropsSets[$propBx] = $this->getEmptyValue($propType);
		}
	}

	private function getCurrentValue($propBx, $propType)
	{
		$arAllPropsValues = $this->arAllPropsValues;

		switch ($propType) {
			case 'S:HTML':
				return isset($arAllPropsValues[$propBx]['VALUE']['TEXT'])
					? (string)$arAllPropsValues[$propBx]['VALUE']['TEXT']
					: '';
			case 'S':
				return (string)$arAllPropsValues[$propBx]['VALUE'];
			case 'S:Date':
			case 'S:DateTime':
			case 'N':
			case 'L':
			case 'E':
			case 'S:directory':
				return $arAllPropsValues[$propBx]['VALUE'] ?? '';
			case 'F':
				return $arAllPropsValues[$propBx]['VALUE'] ?? '';
			default:
				return '';
		}
	}

	private function getEmptyValue($propType)
	{
		switch ($propType) {
			case 'S':
			case 'S:HTML':
			case 'S:Date':
			case 'S:DateTime':
			case 'N':
			case 'L':
			case 'E':
			case 'S:directory':
				return false;
			case 'F':
				return ['VALUE' => false, 'DESCRIPTION' => ''];
			default:
				return false;
		}
	}

	private function processAttribute($propBx, $attrMsList)
	{
		$attrMs = $attrMsList[$this->propList[$propBx]];
		$propType = $this->propBxTypes[$propBx]['TYPE'];
		$currentValue = $this->getCurrentValue($propBx, $propType);

		switch ($propType) {
			case 'S':
			case 'S:HTML':
				switch ($attrMs->{'type'}) {
					case 'string':
					case 'text':
					case 'double':
					case 'long':
					case 'link':
						if ($currentValue != $attrMs->{'value'}) {
							$this->arPropsSets[$propBx] = $attrMs->{'value'};
						}
						break;

					case 'time':
						try {
							$culture = \Bitrix\Main\Context::getCurrent()->getCulture();
							$date = Config::getDateTime($attrMs->{'value'});
							$dateTime = \Bitrix\Main\Type\DateTime::createFromPhp($date);
							if ($currentValue != $dateTime->toString($culture)) {
								$this->arPropsSets[$propBx] = $dateTime->toString($culture);
							}
						} catch (\Exception $e) {
							// Log or handle exception if needed
						}
						break;

					case 'boolean':
						$needValue = $attrMs->{'value'} === true ? 'Y' : 'N';
						if ($currentValue != $needValue) {
							$this->arPropsSets[$propBx] = $needValue;
						}
						break;

					case 'customentity':
					case 'counterparty':
					case 'employee':
					case 'product':
					case 'project':
					case 'store':
					case 'contract':
						if ($currentValue != $attrMs->{'value'}->{'name'}) {
							$this->arPropsSets[$propBx] = $attrMs->{'value'}->{'name'};
						}
						break;
				}
				break;
			case 'S:Date':
			case 'S:DateTime':
				try {
					$culture = \Bitrix\Main\Context::getCurrent()->getCulture();
					$date = Config::getDateTime($attrMs->{'value'});
					$dateTime = \Bitrix\Main\Type\DateTime::createFromPhp($date);
					$value = $dateTime->toString($culture);
					if ($propType === 'S:Date') {
						$value = current(explode(' ', $value));
					}
					if ($currentValue != $value) {
						$this->arPropsSets[$propBx] = $value;
					}
				} catch (\Exception $e) {
					// Log or handle exception if needed
				}
				break;

			case 'N':
				switch ($attrMs->{'type'}) {
					case 'double':
					case 'long':
						if ((float)$currentValue != (float)$attrMs->{'value'}) {
							$this->arPropsSets[$propBx] = (float)$attrMs->{'value'};
						}
						break;
				}
				break;
			case 'F':
				switch ($attrMs->{'type'}) {
					case 'file':
						$fileUniqId = array_pop(explode('/', $attrMs->{'download'}->{'href'}));
						if ($this->arAllPropsValues[$propBx]['DESCRIPTION'] != $fileUniqId) {
							$filePath = ApiNew::downloadFile($attrMs->{'download'}->{'href'}, $attrMs->{'value'}, "prop_files/{$fileUniqId}");
							if (!empty($filePath)) {
								$this->arPropsSets[$propBx] = [
									'VALUE' => \CFile::MakeFileArray($filePath),
									'DESCRIPTION' => $fileUniqId
								];
							}
						}
						break;
				}
				break;

			case 'L':
			case 'E':
			case 'S:directory':
				$variant = $this->createVariantForAttr($attrMs);
				if (!empty($variant)) {
					$variant['ID'] = $this->getEnumIdValue($variant, $propBx);
				}
				if (!empty($variant['ID'])) {
					$this->arPropsSets[$propBx] = $variant['ID'] === 'false' ? false : $variant['ID'];
				}
				break;
		}
	}

	private function createVariantForAttr($attrMs)
	{
		$variant = [];
		switch ($attrMs->{'type'}) {
			case 'string':
			case 'text':
			case 'double':
			case 'long':
			case 'link':
				$variant['NAME'] = $attrMs->{'value'};
				$variant['XML_ID'] = md5($attrMs->{'value'});
				break;

			case 'time':
				try {
					$culture = \Bitrix\Main\Context::getCurrent()->getCulture();
					$date = Config::getDateTime($attrMs->{'value'});
					$dateTime = \Bitrix\Main\Type\DateTime::createFromPhp($date);

					$variant['NAME'] = $dateTime->toString($culture);
					$variant['XML_ID'] = md5($dateTime->toString($culture));
				} catch (\Exception $e) {
					// Log or handle exception if needed
				}
				break;

			case 'boolean':
				$variant['NAME'] = $attrMs->{'value'} === true ? 'Y' : 'N';
				$variant['XML_ID'] = $variant['NAME'];
				break;

			case 'customentity':
			case 'counterparty':
			case 'employee':
			case 'product':
			case 'project':
			case 'store':
			case 'contract':
				$variant['NAME'] = $attrMs->{'value'}->{'name'};
				$variant['XML_ID'] = md5($attrMs->{'value'}->{'name'});

				if ($attrMs->{'type'} === 'product') {
					$resolvedXmlId = ProductIdentifier::resolveXmlIdFromHref($attrMs->{'value'}->{'meta'}->{'href'});
					if (!empty($resolvedXmlId)) {
						$variant['PRODUCT_XML_ID'] = $resolvedXmlId;
					}
				}
				break;
		}

		return $variant;
	}

	private function getEnumIdValue($variant, $propBx)
	{
		$arAllPropsValues = $this->arAllPropsValues;
		$propBxTypes = $this->propBxTypes;

		if ($propBxTypes[$propBx]['TYPE'] === 'E' && (int)$propBxTypes[$propBx]['PARAM'] > 0) {

			$currentVariant = [
				'ID' => 0
			];

			if (isset($variant['PRODUCT_XML_ID']) && !empty($variant['PRODUCT_XML_ID'])) {
				$currentVariant = \Bitrix\Iblock\ElementTable::getList([
					'select' => ['ID'],
					'filter' => [
						'=IBLOCK_ID' => $propBxTypes[$propBx]['PARAM'],
						'=XML_ID' =>  $variant['PRODUCT_XML_ID']
					]
				])->fetch();
			}

			if ($currentVariant['ID'] <= 0) {
				$currentVariant = \Bitrix\Iblock\ElementTable::getList([
					'select' => ['ID'],
					'filter' => [
						'=IBLOCK_ID' => $propBxTypes[$propBx]['PARAM'],
						'=NAME' =>  $variant['NAME']
					]
				])->fetch();
			}

			if ($currentVariant['ID'] > 0) {
				if (intval($arAllPropsValues[$propBx]['VALUE']) !== intval($currentVariant['ID'])) {
					$variant['ID'] = $currentVariant['ID'];
				}
			} else {
				$el = new \CIblockElement;
				$variant['ID'] = $el->Add([
					'IBLOCK_ID' => $propBxTypes[$propBx]['PARAM'],
					'NAME' => $variant['NAME'],
					'XML_ID' => isset($variant['PRODUCT_XML_ID']) && !empty($variant['PRODUCT_XML_ID']) ? $variant['PRODUCT_XML_ID'] : $variant['XML_ID'],
					'CODE' => \CUtil::translit($variant['NAME'], 'ru')
				]);
			}
		}

		if ($propBxTypes[$propBx]['TYPE'] === 'S:directory' && !empty($propBxTypes[$propBx]['PARAM']) && $propBxTypes[$propBx]['HL_CLASS'] !== null) {
			//hl-block search
			$currentVariant = $propBxTypes[$propBx]['HL_CLASS']::getList([
				'filter' => [
					'=UF_NAME' => $variant['NAME']
				],
				'cache' => [
					'ttl' => 86400 * 7
				]
			])->fetch();

			if ($currentVariant['ID'] > 0) {

				if(empty($currentVariant['UF_XML_ID'])) {

					$currentVariant['UF_XML_ID'] = md5($currentVariant['UF_NAME']);

					$propBxTypes[$propBx]['HL_CLASS']::update($currentVariant['ID'], [
						'UF_XML_ID' => $currentVariant['UF_XML_ID']
					]);

				} else {

					$currentVariantByXmlIdList = $propBxTypes[$propBx]['HL_CLASS']::getList([
						'filter' => [
							'=UF_XML_ID' => $currentVariant['UF_XML_ID']
						],
						'cache' => [
							'ttl' => 86400 * 7
						]
					])->fetchAll();

					if (count($currentVariantByXmlIdList) > 1) {
						foreach ($currentVariantByXmlIdList as $currentVariantByXmlIdItem) {
							if (mb_strtolower($currentVariantByXmlIdItem['UF_NAME']) !== mb_strtolower($currentVariant['UF_NAME'])) {
								$propBxTypes[$propBx]['HL_CLASS']::update($currentVariantByXmlIdItem['ID'], [
									'UF_XML_ID' => md5($currentVariantByXmlIdItem['NAME'])
								]);
							}
						}
					}

				}				

				if ((string)$arAllPropsValues[$propBx]['VALUE'] !== (string)$currentVariant['UF_XML_ID']) {
					$variant['ID'] = $currentVariant['UF_XML_ID'];
				}

			} else {

				$result = $propBxTypes[$propBx]['HL_CLASS']::add([
					'UF_NAME' => $variant['NAME'],
					'UF_XML_ID' => $variant['XML_ID']
				]);
				if ($result->isSuccess()) {
					$variant['ID'] = $result->getId();
					if ($variant['ID'] > 0) {
						$variant['ID'] = $variant['XML_ID'];
					}
				}

			}
		}

		if ($propBxTypes[$propBx]['TYPE'] === 'L' && !empty($variant['NAME'])) {
			if ($variant['NAME'] === 'N') {
				if ($arAllPropsValues[$propBx]['VALUE'] == 'Y') {
					$variant['ID'] = 'false';
				}
			} else {
				$currentVariant = \Bitrix\Iblock\PropertyEnumerationTable::getList([
					'filter' => [
						'=PROPERTY_ID' => $propBx,
						'=VALUE' => $variant['NAME']
					]
				])->fetch();
				if ($currentVariant['ID'] > 0) {
					if ($arAllPropsValues[$propBx]['VALUE'] != $currentVariant['VALUE']) {
						$variant['ID'] = $currentVariant['ID'];
					}
				} else {
					$enum = \Bitrix\Iblock\PropertyEnumerationTable::add([
						'PROPERTY_ID' => $propBx,
						'XML_ID' => md5($propBx . $variant['NAME'] . $variant['XML_ID']),
						'VALUE' => $variant['NAME']
					]);
					$variant['ID'] = $enum->getId();
				}
			}
		}

		return $variant['ID'];
	}

	private function processField($propBx)
	{
		$field = str_replace('field_', '', $this->propList[$propBx]);
		$propType = $this->propBxTypes[$propBx]['TYPE'];
		$currentValue = $this->getCurrentValue($propBx, $propType);

		switch ($propType) {
			case 'S':
			case 'S:HTML':
				$this->processStringFieldProperty($propBx, $field, $currentValue);
				break;
			case 'F:Multiple':
				$this->processMultipleFileFieldProperty($propBx, $field);
				break;
			case 'N':
				$this->processNumberFieldProperty($propBx, $field, $currentValue);
				break;
			case 'L':
			case 'E':
			case 'S:directory':
				$this->processEnumFieldProperty($propBx, $field, $currentValue);
				break;
		}
	}

	private function processStringFieldProperty($propBx, $field, $currentValue)
	{
		$fieldId = mb_strpos($field, 'barcode_') === 0 ? 'barcodes' : $field;
		$fieldType = $this->fieldList[$fieldId]['type'];

		switch($fieldType) {
			case 'string':
			case 'int':
				if (!empty($this->itemMs->{$field})) {
					if ($currentValue != $this->itemMs->{$field}) {
						$this->arPropsSets[$propBx] = $this->itemMs->{$field};
					}
				} else {
					if ($this->isEmptyImport && !empty($currentValue)) {
						$this->arPropsSets[$propBx] = false;
					}
				}
				break;
			case 'boolean':
				$this->arPropsSets[$propBx] = (bool)$this->itemMs->{$field} ? 'Y' : 'N';
			case 'meta':
				$this->processEntityStringProperty($propBx, $field, $currentValue);
			case 'array':
				switch ($field) {
					case 'barcodes':
						$this->processBarcodeStringProperty($propBx, $currentValue);
						break;
					case 'barcode_ean8':
					case 'barcode_ean13':
					case 'barcode_code128':
					case 'barcode_gtin':
					case 'barcode_upc':
						$this->processSpecificBarcodeProperty($propBx, $field, $currentValue);
						break;
				}
				break;
			case 'enum':
				switch ($field) {
					case 'trackingType':
						if (!empty($this->itemMs->{$field})) {
							$trackingTypeValue = TrackingType::getTrackingTypeValue($this->itemMs->{$field});
							if ($currentValue != $trackingTypeValue) {
								$this->arPropsSets[$propBx] = $trackingTypeValue;
							}
						} else {
							if ($this->isEmptyImport && !empty($currentValue)) {
								$this->arPropsSets[$propBx] = false;
							}
						}
						break;
				}
				break;
		}
	}

	private function processNumberFieldProperty($propBx, $field, $currentValue)
	{
		$fieldType = $this->fieldList[$field]['type'];

		switch ($fieldType) {
			case 'int':
				if (!empty($this->itemMs->{$field})) {
					if ((float)$currentValue != (float)$this->itemMs->{$field}) {
						$this->arPropsSets[$propBx] = (float)$this->itemMs->{$field};
					}
				} else {
					if ($this->isEmptyImport && !empty($currentValue)) {
						$this->arPropsSets[$propBx] = false;
					}
				}
				break;
		}
	}

	private function processBarcodeStringProperty($propBx, $currentValue)
	{
		if (Utils::array_exists($this->itemMs, 'barcodes')) {
			$propValue = $this->itemMs->{'barcodes'}[0];
			if (is_object($propValue)) {
				$propValue = (array)$propValue;
				$barCodeFirst = current($propValue);
				if ((string)$currentValue !== (string)$barCodeFirst) {
					$this->arPropsSets[$propBx] = $barCodeFirst;
				}
			}
		} else {
			if ($this->isEmptyImport && !empty($currentValue)) {
				$this->arPropsSets[$propBx] = false;
			}
		}
	}

	private function processSpecificBarcodeProperty($propBx, $field, $currentValue)
	{
		$hasBarcode = false;
		$barcodeType = array_pop(explode('_', $field));

		if (Utils::array_exists($this->itemMs, 'barcodes')) {
			foreach ($this->itemMs->{'barcodes'} as $barcode) {
				if (property_exists($barcode, $barcodeType) && !empty($barcode->{$barcodeType})) {
					$hasBarcode = true;
					if ($currentValue !== $barcode->{$barcodeType}) {
						$this->arPropsSets[$propBx] = $barcode->{$barcodeType};
					}
				}
			}
		}

		if (!$hasBarcode) {
			if ($this->isEmptyImport && !empty($currentValue)) {
				$this->arPropsSets[$propBx] = false;
			}
		}
	}

	private function processEntityStringProperty($propBx, $field, $currentValue)
	{
		$fieldEntity = $this->itemMs->{$field};

		if (!empty($this->itemMs->{$field}->{'meta'}->{'href'})) {
			if (!property_exists($fieldEntity, 'name')) {
				$fieldEntity = ApiNew::get($this->itemMs->{$field}->{'meta'}->{'href'}, [], 86400 * 365 * 10);
			} else {
				$fieldEntity->{'hasErrors'} = false;
			}

			if (Utils::is_success($fieldEntity)) {
				if ($currentValue != $fieldEntity->{'name'}) {
					$this->arPropsSets[$propBx] = $fieldEntity->{'name'};
				}
			}
		} else {
			if ($this->isEmptyImport && !empty($currentValue)) {
				$this->arPropsSets[$propBx] = false;
			}
		}
	}

	private function processMultipleFileFieldProperty($propBx, $field)
	{
		switch ($field) {
			case 'file':
				\CRbsMoyskladStocks::importFilesInProp(
					\CRbsMoyskladStocks::getMsFilesArray($this->itemMs->{'files'}),
					$this->itemBx,
					$this->arAllPropsValues[$propBx],
					$propBx,
					$this->isEmptyImport,
					true
				);
				break;
		}
	}

	private function processEnumFieldProperty($propBx, $field)
	{
		$variant = [];

		$fieldType = $this->fieldList[$field]['type'];

		switch ($fieldType) {
			case 'string':
			case 'int':
				if (!empty($this->itemMs->{$field})) {
					$variant['NAME'] = $this->itemMs->{$field};
					$variant['XML_ID'] = md5($this->itemMs->{$field});
				} else {
					if ($this->isEmptyImport && !empty($this->arAllPropsValues[$propBx]['VALUE'])) {
						$this->arPropsSets[$propBx] = false;
					}
				}
				break;
			case 'boolean':
				$currValue = (bool)$this->itemMs->{$field} ? 'Y' : 'N';
				$variant['NAME'] = $currValue;
				$variant['XML_ID'] = md5($currValue);
			case 'meta':
				$this->processEntityEnumProperty($field, $variant);
			case 'array':
				switch ($field) {
					case 'barcodes':
						$this->processBarcodeEnumProperty($variant);
						break;
					case 'barcode_ean8':
					case 'barcode_ean13':
					case 'barcode_code128':
					case 'barcode_gtin':
					case 'barcode_upc':
						$this->processSpecificBarcodeEnumProperty($propBx, $field, $variant);
						break;
				}
				break;
			case 'enum':
				switch ($field) {
					case 'trackingType':
						if (!empty($this->itemMs->{$field})) {
							$variant['NAME'] = TrackingType::getTrackingTypeValue($this->itemMs->{$field});
							$variant['XML_ID'] = md5($variant['NAME']);
						} else {
							if ($this->isEmptyImport && !empty($this->arAllPropsValues[$propBx]['VALUE'])) {
								$this->arPropsSets[$propBx] = false;
							}
						}
						break;
				}
				break;
		}

		if (!empty($variant)) {
			$variant['ID'] = $this->getEnumIdValue($variant, $propBx);
		}

		if (!empty($variant['ID'])) {
			$this->arPropsSets[$propBx] = $variant['ID'] === 'false' ? false : $variant['ID'];
		}
	}

	private function processBarcodeEnumProperty(&$variant)
	{
		if (Utils::array_exists($this->itemMs, 'barcodes')) {
			$propValue = $this->itemMs->{'barcodes'}[0];
			if (is_object($propValue)) {
				$propValue = (array)$propValue;
				$variant['NAME'] = current($propValue);
				$variant['XML_ID'] = md5($variant['NAME']);
			}
		}
	}

	private function processSpecificBarcodeEnumProperty($propBx, $field, &$variant)
	{
		$hasBarcode = false;
		$barcodeType = array_pop(explode('_', $field));

		if (Utils::array_exists($this->itemMs, 'barcodes')) {
			foreach ($this->itemMs->{'barcodes'} as $barcode) {
				if (property_exists($barcode, $barcodeType) && !empty($barcode->{$barcodeType})) {
					$variant['NAME'] = $barcode->{$barcodeType};
					$variant['XML_ID'] = md5($barcode->{$barcodeType});
					$hasBarcode = true;
				}
			}
		}

		if (!$hasBarcode) {
			if ($this->isEmptyImport && !empty($this->arAllPropsValues[$propBx]['VALUE'])) {
				$this->arPropsSets[$propBx] = false;
			}
		}
	}

	private function processEntityEnumProperty($field, &$variant)
	{
		$fieldEntity = $this->itemMs->{$field};

		if (!empty($this->itemMs->{$field}->{'meta'}->{'href'})) {
			if (!property_exists($fieldEntity, 'name')) {
				$fieldEntity = ApiNew::get($this->itemMs->{$field}->{'meta'}->{'href'}, [], 86400 * 365 * 10);
			}

			if (Utils::is_success($fieldEntity)) {
				$variant['NAME'] = $fieldEntity->{'name'};
				$variant['XML_ID'] = md5($fieldEntity->{'name'});
			}
		}
	}

}
