<?php

namespace Rbs\MoyskladStocks\Internals;

use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\LangMsg;

class PropertyOption
{
	private $entity;
	private $iblockId;

	private $propertyBxNames = [];
	private $propertyBxTypes = [];
	private $selectPropsForProps = [];

	private $selectAllFields = [];
	private $selectNumFields = [];
	private $selectFileFieldMulti = [];

	public function __construct(string $entity, int $iblockId)
	{
		$this->entity = $entity;
		$this->iblockId = $iblockId;
		$this->buildPropNamesAndTypes();
		$this->buildSelectPropsForProps();
		$this->buildFieldList();		
	}

	public function hasBxProps(): bool
	{
		return count($this->propertyBxNames) > 0;
	}

	public function getBxPropertyNames(): array
	{
		return $this->propertyBxNames ?? [];
	}

	public function getBxPropertyName(int $propId): string
	{
		return isset($this->propertyBxNames[$propId]) ? (string)$this->propertyBxNames[$propId] : '';
	}

	public function getPropertyVariantsForPropId(int $propId): array
	{
		$propType = isset($this->propertyBxTypes[$propId]) ? $this->propertyBxTypes[$propId] : '';

		$result = ['N' => LangMsg::get('AJAX_NON_SYNC')];

		switch ($propType) {
			case 'S':
			case 'L':
			case 'E':
			case 'S:directory':
			case 'S:HTML':
				if (count($this->selectAllFields) > 0) {
					$result += $this->selectAllFields;
				}
				break;

			case 'N':
				if (count($this->selectNumFields) > 0) {
					$result += $this->selectNumFields;
				}
				if (count($this->selectPropsForProps['N']) > 0) {
					$result += $this->selectPropsForProps['N'];
				}
				break;

			case 'S:Date':
			case 'S:DateTime':
				if (count($this->selectPropsForProps['SD']) > 0) {
					$result += $this->selectPropsForProps['SD'];
				}
				break;

			case 'F':
				if (count($this->selectPropsForProps['F']) > 0) {
					$result += $this->selectPropsForProps['F'];
				}
				break;
			case 'F:Multiple':
				$result += $this->selectFileFieldMulti;
				break;
		}

		return $result;
	}	

		private function buildPropNamesAndTypes()
		{
			$propsStr = [];

			if ($this->entity !== 'variant') {
				$propsStr = array_merge(

					\Bitrix\Iblock\PropertyTable::getList(['filter' => ['IBLOCK_ID' => $this->iblockId, 'PROPERTY_TYPE' => ['S', 'L', 'N', 'E', 'F'], 'MULTIPLE' => 'N']])->fetchAll(),
					
					\Bitrix\Iblock\PropertyTable::getList(['filter' => ['IBLOCK_ID' => $this->iblockId, 'PROPERTY_TYPE' => 'F', 'MULTIPLE' => 'Y']])->fetchAll()

				);
			} else {
				$propsStr = \Bitrix\Iblock\PropertyTable::getList(['filter' => ['IBLOCK_ID' => $this->iblockId, 'PROPERTY_TYPE' => ['S', 'L', 'N', 'E', 'F'], 'MULTIPLE' => 'N']])->fetchAll();
			}

			foreach ($propsStr as $prop) {

				$propIdForName = $prop['CODE'] ?: $prop['ID'];

				if ($prop['PROPERTY_TYPE'] === 'S' && !empty($prop['USER_TYPE'])) {
					if (!in_array($prop['USER_TYPE'], ['directory', 'Date', 'DateTime', 'HTML'])) {
						continue;
					}
				}

				if ($prop['MULTIPLE'] == 'Y') {
					$this->propertyBxTypes[$prop['ID']] = $prop['PROPERTY_TYPE'] . ':Multiple';
				} else {
					$this->propertyBxTypes[$prop['ID']] = !empty($prop['USER_TYPE']) ? $prop['PROPERTY_TYPE'] . ':' . $prop['USER_TYPE'] : $prop['PROPERTY_TYPE'];
				}

				$propType = LangMsg::get('PROPERTY_CASE_' . $this->propertyBxTypes[$prop['ID']]);

				$this->propertyBxNames[$prop['ID']] = "[{$propIdForName}] {$prop['NAME']} ({$propType})";

			}
		}

		private function buildSelectPropsForProps()
		{
			$optionsPropsArray = \Rbs\MoyskladStocks\Services\MoyskladImportUtils::getOptionAttributesArray([], 180);
			$this->selectPropsForProps = $optionsPropsArray['selectPropsForProps'];
		}

		private function buildFieldList()
		{
			$fieldList = new \Rbs\MoyskladStocks\Internals\FieldList($this->entity);
			$this->selectAllFields = $fieldList->getFieldNamesForSelect('field');
			$this->selectNumFields = $fieldList->getFieldNamesForSelect('field', ['int']);

			if ($this->entity !== 'variant') {
				if (Utils::is_count($this->selectPropsForProps['S'])) {
					$this->selectAllFields = array_merge($this->selectAllFields, $this->selectPropsForProps['S']);
					if (isset($this->selectAllFields['DEFAULT'])) {
						unset($this->selectAllFields['DEFAULT']);
					}
				}
			}

			$this->selectFileFieldMulti = ['field_file' => LangMsg::get('AJAX_ITEM_FILE_FIELD')];
		}
	
}