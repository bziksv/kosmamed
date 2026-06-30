<?php

namespace Rbs\MoyskladStocks\Process;

use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\Internals\FieldList;
use Rbs\MoyskladStocks\Services\ImportParamsConfig;

class Helper
{
    public static function buildExpandParams(string $entity): string
    {
		$expandArray = ['images', 'files', 'country', 'productFolder', 'uom', 'supplier'];
		if ($entity === 'bundle') {
			$expandArray[] = 'components.assortment';
		}

		$propFieldsForChange = [];
		$propList = ImportParamsConfig::getImportPropList($entity);
		foreach ($propList as $propBx => $propMs) {
			$isField = mb_strpos($propMs, 'field_') !== false;
			if ($isField) {
				$propFieldsForChange[] = str_replace('field_', '', $propMs);
			}
		}

		if (Utils::is_count($propFieldsForChange)) {
			$fieldListItem = new FieldList($entity);
			$fieldList = $fieldListItem->getFieldList();
			foreach ($propFieldsForChange as $fieldId) {
				if (isset($fieldList[$fieldId]['type']) && $fieldList[$fieldId]['type'] === 'meta') {
					if (!in_array($fieldId, $expandArray)) {
						$expandArray[] = $fieldId;
					}
				}
			}
		}

		return implode(',', $expandArray);
    }

	public static function isNeedGroupItem(string $entity): bool
	{
		$groupId = Config::getGroupId($entity);
		return $groupId !== 'N' && !empty($groupId);
	}
}