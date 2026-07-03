<?php
use Bitrix\Main\Application;
use Bitrix\Main\Loader;

Loader::includeModule('iblock');

function kmXmlIdParseKodType(?string $kod): ?string
{
    if ($kod === null || $kod === '') {
        return null;
    }
    $parts = explode('+', $kod);
    return $parts[count($parts) - 1] ?? null;
}

function kmXmlIdParseComplectNum(?string $kod): ?int
{
    if ($kod === null || $kod === '') {
        return null;
    }
    $parts = explode('+', $kod);
    if (($parts[count($parts) - 1] ?? '') !== 'Комплект') {
        return null;
    }
    $complectParts = explode('-', $parts[count($parts) - 2] ?? '');
    $num = (int)($complectParts[count($complectParts) - 1] ?? 0);
    return $num > 0 ? $num : null;
}

/**
 * Пометить «обработано» пачкой через SQL (без тысяч SetPropertyValues).
 */
function kmXmlIdBulkMarkWorkedOut(int $iblockId, int $limit = 5000): int
{
    $connection = Application::getConnection();
    $iblockId = (int)$iblockId;
    $limit = max(1, (int)$limit);

    $sql = "
        SELECT e.ID
        FROM b_iblock_element e
        INNER JOIN b_iblock_element_property art
            ON art.IBLOCK_ELEMENT_ID = e.ID
            AND art.IBLOCK_PROPERTY_ID = 118
            AND art.VALUE IS NOT NULL
            AND art.VALUE != ''
        LEFT JOIN b_iblock_element_property wo
            ON wo.IBLOCK_ELEMENT_ID = e.ID
            AND wo.IBLOCK_PROPERTY_ID = 231
        WHERE e.IBLOCK_ID = {$iblockId}
            AND e.ACTIVE = 'Y'
            AND (wo.ID IS NULL OR wo.VALUE IS NULL OR wo.VALUE = '' OR wo.VALUE != '2777')
        LIMIT {$limit}
    ";

    $ids = [];
    $res = $connection->query($sql);
    while ($row = $res->fetch()) {
        $ids[] = (int)$row['ID'];
    }
    if ($ids === []) {
        return 0;
    }

    $idList = implode(',', $ids);
    $connection->queryExecute("
        DELETE FROM b_iblock_element_property
        WHERE IBLOCK_PROPERTY_ID = 231
            AND IBLOCK_ELEMENT_ID IN ({$idList})
    ");
    $values = [];
    foreach ($ids as $id) {
        $values[] = '(' . $id . ', 231, \'2777\', 2777)';
    }
    $connection->queryExecute('
        INSERT INTO b_iblock_element_property (IBLOCK_ELEMENT_ID, IBLOCK_PROPERTY_ID, VALUE, VALUE_ENUM)
        VALUES ' . implode(',', $values)
    );

    return count($ids);
}

function kmXmlIdLoadDuplicateGroups(int $iblockId): array
{
    $connection = Application::getConnection();
    $iblockId = (int)$iblockId;

    $sql = "
        SELECT
            art.VALUE AS ARTICLE,
            e.ID,
            kod.VALUE AS KOD
        FROM b_iblock_element e
        INNER JOIN b_iblock_element_property art
            ON art.IBLOCK_ELEMENT_ID = e.ID
            AND art.IBLOCK_PROPERTY_ID = 118
            AND art.VALUE IS NOT NULL
            AND art.VALUE != ''
        INNER JOIN (
            SELECT art2.VALUE
            FROM b_iblock_element e2
            INNER JOIN b_iblock_element_property art2
                ON art2.IBLOCK_ELEMENT_ID = e2.ID
                AND art2.IBLOCK_PROPERTY_ID = 118
                AND art2.VALUE IS NOT NULL
                AND art2.VALUE != ''
            WHERE e2.IBLOCK_ID = {$iblockId}
                AND e2.ACTIVE = 'Y'
            GROUP BY art2.VALUE
            HAVING COUNT(DISTINCT e2.ID) > 1
        ) dup ON dup.VALUE = art.VALUE
        LEFT JOIN b_iblock_element_property kod
            ON kod.IBLOCK_ELEMENT_ID = e.ID
            AND kod.IBLOCK_PROPERTY_ID = 229
        WHERE e.IBLOCK_ID = {$iblockId}
            AND e.ACTIVE = 'Y'
        ORDER BY art.VALUE, e.ID
    ";

    $groups = [];
    $res = $connection->query($sql);
    while ($row = $res->fetch()) {
        $article = (string)$row['ARTICLE'];
        $groups[$article][] = [
            'ID' => (int)$row['ID'],
            'KOD' => (string)($row['KOD'] ?? ''),
        ];
    }

    return $groups;
}

function kmXmlIdProcessDuplicateGroup(int $iblockId, array $items): int
{
    $changes = 0;
    $el = new CIBlockElement();

    foreach ($items as $item) {
        if (kmXmlIdParseKodType($item['KOD']) !== 'Товар') {
            continue;
        }
        if (!$el->Update($item['ID'], ['ACTIVE' => 'N'], false, false, false)) {
            continue;
        }
        $changes++;
    }

    $complects = [];
    foreach ($items as $item) {
        $num = kmXmlIdParseComplectNum($item['KOD']);
        if ($num !== null) {
            $complects[$item['ID']] = $num;
        }
    }
    if ($complects === []) {
        return $changes;
    }

    $maxComplect = max($complects);
    foreach ($complects as $elementId => $num) {
        if ($num === $maxComplect) {
            continue;
        }
        CIBlockElement::SetPropertyValues($elementId, $iblockId, 2776, 'NOINDEX');
        $changes++;
    }

    return $changes;
}

function xml_id()
{
    if (!defined('BX_NO_ACCELERATOR_RESET')) {
        define('BX_NO_ACCELERATOR_RESET', true);
    }

    $iblockId = 24;
    $changes = 0;

    foreach (kmXmlIdLoadDuplicateGroups($iblockId) as $items) {
        if (count($items) < 2) {
            continue;
        }
        $changes += kmXmlIdProcessDuplicateGroup($iblockId, $items);
    }

    // Догоняем очередь WORKED_OUT пачками; за один запуск — не больше 5k строк.
    kmXmlIdBulkMarkWorkedOut($iblockId, 5000);

    if ($changes > 0) {
        $log = date('Y-m-d H:i:s') . ' изменений: ' . $changes;
        file_put_contents($_SERVER['DOCUMENT_ROOT'] . '/log_hide_el.log', $log . PHP_EOL, FILE_APPEND);
    }

    return 'xml_id();';
}
