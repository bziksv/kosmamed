<?php
namespace Rbs\MoyskladStocks\Internals;

use Rbs\MoyskladStocks\LangMsg;
use Rbs\MoyskladStocks\Utils;

class FieldList
{
	private $entity;

	public function __construct(string $entity)
	{
		$this->entity = $entity;
	}

	public function getFieldNamesForSelect(string $prefix = '', array $filterTypes = []): array
	{
		$result = [];
		$fieldLangName = LangMsg::get('AJAX_FIELD');
		foreach($this->getFieldList() as $fieldId => $field) {

			if(!empty($filterTypes)) {
				if(!in_array($field['type'], $filterTypes)){
					continue;
				}
			}

			$nameForType = in_array($field['type'], ['array', 'meta']) ? 'string' : $field['type'];

			$fieldKey = !empty($prefix) ? "{$prefix}_{$fieldId}" : $fieldId;
			$result[$fieldKey] = "[{$fieldLangName}] {$field['description']} ({$nameForType})";

			if($field['type'] === 'array' && isset($field['values']) && Utils::is_count($field['values'])) {
				$valuePrefix = $fieldKey;
				if (!empty($field['values_prefix'])) {
					$valuePrefix = !empty($prefix) ? "{$prefix}_{$field['values_prefix']}" : $field['values_prefix'];
				}
				foreach($field['values'] as $valueId => $valueName) {
					$result["{$valuePrefix}_{$valueId}"] = "[{$fieldLangName}] {$valueName} ({$nameForType})";
				}
			}
			
		}
		return $result;
	}

	public function getFieldList()
	{
		$filterKeys = self::$fieldIdsForEntity[$this->entity];

		if(!empty($filterKeys)) {
			return array_filter(
				self::getAllFieldList(),
				function ($key) use ($filterKeys) {
					return in_array($key, $filterKeys);
				},
				ARRAY_FILTER_USE_KEY
			);
		}
		
		return self::getAllFieldList();
	}

	private static $fieldIdsForEntity = [
		'product' => [],
		'bundle' => [],
		'variant' => [
			'accountId',
			'archived',
			'barcodes',
			'code',
			'description',
			'discountProhibited',
			'externalCode',
			'id',
			'name',
		],		
		'service' => [
			'accountId',
			'archived',
			'barcodes',
			'code',
			'description',
			'discountProhibited',
			'externalCode',
			'id',
			'name',
			'group',
			'owner',
			'productFolder',
			'uom',
			'useParentVat',
			'vat',
			'vatEnabled'
		],		
	];

	private static function getAllFieldList(): array
	{
		return [

			'name' => [
				'type' => 'string',
				'description' => LangMsg::get('MS_FIELD_name')
			],
			'productFolder' => [
				'type' => 'meta',
				'description' => LangMsg::get('MS_FIELD_productFolder')
			],
			'id' => [
				'type' => 'string',
				'description' => LangMsg::get('MS_FIELD_id')
			],
			'description' => [
				'type' => 'string',
				'description' => LangMsg::get('MS_FIELD_description')
			],
			'externalCode' => [
				'type' => 'string',
				'description' => LangMsg::get('MS_FIELD_externalCode')
			],
			'archived' => [
				'type' => 'boolean',
				'description' => LangMsg::get('MS_FIELD_archived')
			],
			'article' => [
				'type' => 'string',
				'description' => LangMsg::get('MS_FIELD_article')
			],
			/* 'attributes' => [
				'type' => 'array',
				'description' => LangMsg::get('MS_FIELD_attributes')
			], */
			'barcodes' => [
				'type' => 'array',
				'description' => LangMsg::get('MS_FIELD_barcodes'),
				'values_prefix' => 'barcode',
				'values' => [
					'ean8' => LangMsg::get('MS_FIELD_barcodes_ean8'),
					'ean13' => LangMsg::get('MS_FIELD_barcodes_ean13'),
					'code128' => LangMsg::get('MS_FIELD_barcodes_code128'),
					'gtin' => LangMsg::get('MS_FIELD_barcodes_gtin'),
					'upc' => LangMsg::get('MS_FIELD_barcodes_upc'),
				]
			],
			/* 'buyPrice' => [
				'type' => 'object',
				'description' => LangMsg::get('MS_FIELD_buyPrice')
			], */
			'code' => [
				'type' => 'string',
				'description' => LangMsg::get('MS_FIELD_code')
			],
			'country' => [
				'type' => 'meta',
				'description' => LangMsg::get('MS_FIELD_country')
			],

			'owner' => [
				'type' => 'meta',
				'description' => LangMsg::get('MS_FIELD_owner')
			],
			'group' => [
				'type' => 'meta',
				'description' => LangMsg::get('MS_FIELD_group')
			],
			'supplier' => [
				'type' => 'meta',
				'description' => LangMsg::get('MS_FIELD_supplier')
			],

			'uom' => [
				'type' => 'meta',
				'description' => LangMsg::get('MS_FIELD_uom')
			],
			'volume' => [
				'type' => 'int',
				'description' => LangMsg::get('MS_FIELD_volume')
			],
			'weight' => [
				'type' => 'int',
				'description' => LangMsg::get('MS_FIELD_weight')
			],

			'pathName' => [
				'type' => 'string',
				'description' => LangMsg::get('MS_FIELD_pathName')
			],

			'discountProhibited' => [
				'type' => 'boolean',
				'description' => LangMsg::get('MS_FIELD_discountProhibited')
			],
			'minimumBalance' => [
				'type' => 'int',
				'description' => LangMsg::get('MS_FIELD_minimumBalance')
			],

			/* 'accountId' => [
				'type' => 'string',
				'description' => LangMsg::get('MS_FIELD_accountId')
			], */
			/* 'alcoholic' => [
				'type' => 'object',
				'description' => LangMsg::get('MS_FIELD_alcoholic')
			], */

			/* 'files' => [
				'type' => 'metaarray',
				'description' => LangMsg::get('MS_FIELD_files')
			], */
			/* 'images' => [
				'type' => 'metaarray',
				'description' => LangMsg::get('MS_FIELD_images')
			], */

			/* 'meta' => [
				'type' => 'meta',
				'description' => LangMsg::get('MS_FIELD_meta')
			], */
			/* 'minPrice' => [
				'type' => 'object',
				'description' => LangMsg::get('MS_FIELD_minPrice')
			], */
			/* 'packs' => [
				'type' => 'array',
				'description' => LangMsg::get('MS_FIELD_packs')
			], */
			/* 'partialDisposal' => [
				'type' => 'boolean',
				'description' => LangMsg::get('MS_FIELD_partialDisposal')
			], */
			/* 'paymentItemType' => [
				'type' => 'enum',
				'description' => LangMsg::get('MS_FIELD_paymentItemType')
			], */
			/* 'ppeType' => [
				'type' => 'enum',
				'description' => LangMsg::get('MS_FIELD_ppeType')
			], */

			/* 'salePrices' => [
				'type' => 'array',
				'description' => LangMsg::get('MS_FIELD_salePrices')
			], */
			/* 'shared' => [
				'type' => 'boolean',
				'description' => LangMsg::get('MS_FIELD_shared')
			], */
			/* 'syncId' => [
				'type' => 'string',
				'description' => LangMsg::get('MS_FIELD_syncId')
			], */
			/* 'taxSystem' => [
				'type' => 'enum',
				'description' => LangMsg::get('MS_FIELD_taxSystem')
			], */
			/* 'things' => [
				'type' => 'array',
				'description' => LangMsg::get('MS_FIELD_things')
			], */
			'tnved' => [
				'type' => 'string',
				'description' => LangMsg::get('MS_FIELD_tnved')
			],
			'trackingType' => [
				'type' => 'enum',
				'description' => LangMsg::get('MS_FIELD_trackingType')
			],
			
			/* 'updated' => [
				'type' => 'datetime',
				'description' => LangMsg::get('MS_FIELD_updated')
			], */
			/* 'useParentVat' => [
				'type' => 'boolean',
				'description' => LangMsg::get('MS_FIELD_useParentVat')
			], */
			/* 'variantsCount' => [
				'type' => 'int',
				'description' => LangMsg::get('MS_FIELD_variantsCount')
			], */

			'isSerialTrackable' => [
				'type' => 'boolean',
				'description' => LangMsg::get('MS_FIELD_isSerialTrackable')
			],
			'effectiveVat' => [
				'type' => 'int',
				'description' => LangMsg::get('MS_FIELD_effectiveVat')
			],
			'effectiveVatEnabled' => [
				'type' => 'boolean',
				'description' => LangMsg::get('MS_FIELD_effectiveVatEnabled')
			],
			'vat' => [
				'type' => 'int',
				'description' => LangMsg::get('MS_FIELD_vat')
			],
			'vatEnabled' => [
				'type' => 'boolean',
				'description' => LangMsg::get('MS_FIELD_vatEnabled')
			],
		];
	}

}