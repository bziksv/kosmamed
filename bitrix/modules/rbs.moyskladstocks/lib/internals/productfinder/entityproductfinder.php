<?php
namespace Rbs\MoyskladStocks\Internals\ProductFinder;

/**
 * Finds BUS products during entity import from MoySklad
 *
 * Used in: CRbsMoyskladStocks::importItems(), checkUpdateHook(), Entity\Base
 */
class EntityProductFinder
{
    /**
     * Splits items into existing (for update) and new (for creation)
     *
     * @param array $arItems [externalCode => msObject, ...]
     * @param string $entity Entity type (product, variant, bundle, service)
     * @param int $catalogIblockId Catalog iblock ID
     * @return array ['found' => [xmlId => ['MS' => ..., 'BX' => ...]], 'new' => [xmlId => msObject]]
     */
    public static function findExistingElements(array $arItems, string $entity, int $catalogIblockId): array
    {
        $filter = ProductIdentifier::buildBatchFilter(array_keys($arItems), $entity);
        $filter['IBLOCK_ID'] = $catalogIblockId;

        $select = ['ID', 'IBLOCK_ID', 'NAME', 'CODE', 'XML_ID', 'DETAIL_TEXT', 'PREVIEW_TEXT', 'ACTIVE', 'PREVIEW_PICTURE', 'DETAIL_PICTURE', 'SORT'];

        $rsIblock = \CIblockElement::GetList(['ID' => 'DESC'], $filter, false, false, $select);

        $arFoundItems = [];
        while ($ob = $rsIblock->GetNext()) {
            $xmlId = ProductIdentifier::extractXmlId($ob['XML_ID'], $entity);
            if (isset($arItems[$xmlId])) {
                $arFoundItems[$xmlId] = [
                    'MS' => $arItems[$xmlId],
                    'BX' => $ob
                ];
                unset($arItems[$xmlId]);
            }
        }

        return [
            'found' => $arFoundItems,
            'new' => $arItems
        ];
    }
}
