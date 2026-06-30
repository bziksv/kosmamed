<?php
namespace Rbs\MoyskladStocks\Diagnostic\Tools;

use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\LangMsg;

\Bitrix\Main\Loader::includeModule('iblock');

class IblockElementAnalyzer
{
    public static function getEntityIblockList(): array
    {
        $entityList = ['product', 'variant', 'bundle', 'service'];

        $entityTitles = [
            'product' => LangMsg::get('IBLOCK_ELEMENT_ANALYZER_ENTITY_PRODUCT'),
            'variant' => LangMsg::get('IBLOCK_ELEMENT_ANALYZER_ENTITY_VARIANT'),
            'bundle' => LangMsg::get('IBLOCK_ELEMENT_ANALYZER_ENTITY_BUNDLE'),
            'service' => LangMsg::get('IBLOCK_ELEMENT_ANALYZER_ENTITY_SERVICE'),
        ];

        $result = [];
        foreach ($entityList as $entity) {
            $iblockId = Config::getIblockId($entity);
            if ($iblockId > 0 && ($iblock = \CIBlock::GetList([], ['ID' => $iblockId], false, false, ['ID', 'NAME', 'TYPE'])->Fetch())) {
                $result[] = [
                    'entity' => $entity,
                    'title' => $entityTitles[$entity] . " | [" . $iblockId . "] " . $iblock['NAME'],
                    'iblock_id' => $iblockId,
                    'iblock_type' => $iblock['TYPE'],
                ];
            }
        }

        return $result;
    }

    public static function getIblockElementsMetaInfo(int $iblockId = 0): array
    {
        $result = [
            'elements_count' => 0,
            'duplicate_elements_count' => 0,
            'active_elements_count' => 0,
            'inactive_elements_count' => 0,
            'sections_count' => 0,
            'active_sections_count' => 0,
            'inactive_sections_count' => 0,
            'duplicate_sections_count' => 0,
        ];

        $result['elements_count'] = \Bitrix\Iblock\ElementTable::getCount(['IBLOCK_ID' => $iblockId]);
        $result['active_elements_count'] = \Bitrix\Iblock\ElementTable::getCount(['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y']);
        $result['inactive_elements_count'] = \Bitrix\Iblock\ElementTable::getCount(['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'N']);

        $result['sections_count'] = \Bitrix\Iblock\SectionTable::getCount(['IBLOCK_ID' => $iblockId]);
        $result['active_sections_count'] = \Bitrix\Iblock\SectionTable::getCount(['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y']);
        $result['inactive_sections_count'] = \Bitrix\Iblock\SectionTable::getCount(['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'N']);

        $result['duplicate_elements_count'] = self::getDuplicates('element', $iblockId, 1, 0)['total_count'];
        $result['duplicate_sections_count'] = self::getDuplicates('section', $iblockId, 1, 0)['total_count'];

        return $result;
    }

        private static function getDuplicates(string $type, int $iblockId = 0, $limit = 1000, $offset = 0): array
        {
            $tableClass = $type === 'element'
                ? \Bitrix\Iblock\ElementTable::class
                : \Bitrix\Iblock\SectionTable::class;

            $res = $tableClass::getList([
                'select' => [
                    'XML_ID',
                    'CNT'
                ],
                'filter' => [
                    '=IBLOCK_ID' => $iblockId,
                    '!XML_ID' => false,
                    '>CNT' => 1,
                ],
                'limit' => $limit,
                'offset' => $offset,
                'count_total' => true,
                'runtime' => [
                    new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)')
                ],
                'group' => ['XML_ID'],
            ]);

            $duplicates = [];

            while ($row = $res->fetch()) {
                $duplicates[$row['XML_ID']] = $row['CNT'];
            }

            return [
                'items' => $duplicates,
                'total_count' => $res->getCount(),
            ];
        }

    public static function buildDuplicatesList(string $type, int $iblockId = 0, $limit = 100, $offset = 0): array
    {
        $tableClass = $type === 'element'
            ? \Bitrix\Iblock\ElementTable::class
            : \Bitrix\Iblock\SectionTable::class;

        $duplicates = self::getDuplicates($type, $iblockId, $limit, $offset);

        $xmlIds = array_keys($duplicates['items']);

        $queryParams = [
            'select' => [
                'ID',
                'NAME',
                'ACTIVE',
                'XML_ID',
            ],
            'filter' => [
                '=IBLOCK_ID' => $iblockId,
                '=XML_ID' => $xmlIds,
            ],
        ];

        if ($type === 'element') {
            $queryParams['order'] = ['ID' => 'ASC'];
        }

        $res = $tableClass::getList($queryParams);

        $result = [];
        while ($row = $res->fetch()) {
            $result[] = $row;
        }

        return [
            'headers' => [
                'ID' => 'ID',
                'NAME' => LangMsg::get('IBLOCK_ELEMENT_ANALYZER_HEADER_NAME'),
                'ACTIVE' => LangMsg::get('IBLOCK_ELEMENT_ANALYZER_HEADER_ACTIVE'),
                'XML_ID' => LangMsg::get('IBLOCK_ELEMENT_ANALYZER_HEADER_XML_ID'),
            ],
            'items' => $result,
            'total_count' => $duplicates['total_count'],
        ];
    }

    public static function getElementXmlIdsList(int $iblock_id = 0, array $xmlIds = []): array
    {
        // 1) Get duplicate count by XML_ID
        $duplicatesRes = \Bitrix\Iblock\ElementTable::getList([
            'select' => [
                'XML_ID',
                'CNT'
            ],
            'filter' => [
                '=IBLOCK_ID' => $iblock_id, 
                '=XML_ID' => $xmlIds,
                '!XML_ID' => false,
            ],
            'runtime' => [
                new \Bitrix\Main\Entity\ExpressionField('CNT', 'COUNT(*)')
            ],
            'group' => ['XML_ID'],
        ]);

        $duplicatesCount = [];
        while ($row = $duplicatesRes->fetch()) {
            $duplicatesCount[$row['XML_ID']] = (int)$row['CNT'];
        }

        // 2) Get elements and add DOUBLES_COUNT field
        $res = \Bitrix\Iblock\ElementTable::getList([
            'filter' => ['=XML_ID' => $xmlIds, '=IBLOCK_ID' => $iblock_id],
            'select' => ['ID', 'NAME', 'XML_ID', 'ACTIVE', 'IBLOCK_ID']
        ]);

        $items = [];
        while ($row = $res->fetch()) {
            $row['DOUBLES_COUNT'] = $duplicatesCount[$row['XML_ID']] ?? 0;
            $items[] = $row;
        }

        return [
            'items' => $items,
            'total_count' => count($items),
        ];
    }

}