<?php
namespace Rbs\MoyskladStocks\Internals\ProductFinder;

use Rbs\MoyskladStocks\Utils;

/**
 * Finds BUS products during stocks/prices import from MoySklad
 *
 * Used in: Import\Type\Stocks, CurrentStocks, Prices
 */
class StocksProductFinder
{
    /**
     * Finds elements by XML_ID grouped by duplicates
     *
     * @param array $xmlIds externalCode values
     * @param string $entity Entity type (product, variant, bundle, service)
     * @param array $additionalFilter Extra filter params (IBLOCK_ID, etc.)
     * @param string $doubleType Sort order for duplicate priority (ASC|DESC)
     * @return array [externalCode => [elementId => '', ...], ...]
     */
    public static function findElementsWithDoubles(
        array $xmlIds,
        string $entity,
        array $additionalFilter = [],
        string $doubleType = 'ASC'
    ): array
    {
        $filter = ProductIdentifier::buildBatchFilter($xmlIds, $entity);
        $filter = array_merge($filter, $additionalFilter);

        $rsIblockElements = \Bitrix\Iblock\ElementTable::getList([
            'order' => ['ID' => $doubleType === 'DESC' ? 'DESC' : 'ASC'],
            'filter' => $filter,
            'select' => ['ID', 'XML_ID']
        ])->fetchAll();

        if (!Utils::is_count($rsIblockElements)) {
            return [];
        }

        $doublesCheckList = [];
        foreach ($rsIblockElements as $element) {
            $xmlId = ProductIdentifier::extractXmlId($element['XML_ID'], $entity);
            $doublesCheckList[$xmlId][$element['ID']] = '';
        }

        return $doublesCheckList;
    }
}
