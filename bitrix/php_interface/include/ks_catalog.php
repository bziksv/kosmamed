<?php
/**
 * Алфавитная сортировка названий (русская локаль, если доступна).
 */
function ksCollatorCompare($a, $b)
{
	static $collator = null;
	if ($collator === null) {
		$collator = class_exists('Collator') ? new Collator('ru_RU') : false;
	}
	if ($collator) {
		return $collator->compare((string)$a, (string)$b);
	}
	return strnatcasecmp((string)$a, (string)$b);
}

function ksSortByNameKey(array &$items)
{
	uksort($items, 'ksCollatorCompare');
}

function ksSortSectionsByName(array &$sections, $nameKey = 'NAME')
{
	uasort($sections, function ($a, $b) use ($nameKey) {
		return ksCollatorCompare($a[$nameKey] ?? '', $b[$nameKey] ?? '');
	});
	foreach ($sections as &$section) {
		if (!empty($section['CHILDREN']) && is_array($section['CHILDREN'])) {
			ksSortSectionsByName($section['CHILDREN'], $nameKey);
		}
	}
	unset($section);
}

/**
 * Алфавитная сортировка плоского меню bitrix:menu (с сохранением вложенности).
 */
function ksSortMenuFlat(array $items)
{
	if (count($items) < 2) {
		return $items;
	}
	return ksSortMenuFlatLevel($items, 0, count($items), 1);
}

function ksSortMenuFlatLevel(array $items, $start, $end, $depth)
{
	$blocks = array();
	$i = $start;
	while ($i < $end) {
		if ($items[$i]['DEPTH_LEVEL'] < $depth) {
			break;
		}
		if ($items[$i]['DEPTH_LEVEL'] != $depth) {
			$i++;
			continue;
		}
		$blockStart = $i;
		$i++;
		while ($i < $end && $items[$i]['DEPTH_LEVEL'] > $depth) {
			$i++;
		}
		$blocks[] = array($blockStart, $i);
	}
	usort($blocks, function ($a, $b) use ($items) {
		return ksCollatorCompare($items[$a[0]]['TEXT'], $items[$b[0]]['TEXT']);
	});
	$result = array();
	foreach ($blocks as $block) {
		$bs = $block[0];
		$be = $block[1];
		$result[] = $items[$bs];
		if ($be - $bs > 1) {
			$result = array_merge($result, ksSortMenuFlatLevel($items, $bs + 1, $be, $depth + 1));
		}
	}
	return $result;
}

/**
 * ID раздела на заданной глубине цепочки (1 — корень каталога, 2 — подкатегория в меню).
 */
function kmCatalogSectionIdAtDepth($sectionId, $depth = 2, $iblockId = 24)
{
	static $cache = array();
	$sectionId = (int)$sectionId;
	$depth = max(1, (int)$depth);
	$iblockId = (int)$iblockId;
	if ($sectionId <= 0) {
		return 0;
	}

	$cacheKey = $iblockId . ':' . $sectionId . ':' . $depth;
	if (isset($cache[$cacheKey])) {
		return $cache[$cacheKey];
	}

	$chain = array();
	$nav = CIBlockSection::GetNavChain($iblockId, $sectionId);
	while ($row = $nav->Fetch()) {
		$chain[(int)$row['DEPTH_LEVEL']] = (int)$row['ID'];
	}

	if (empty($chain)) {
		return $cache[$cacheKey] = $sectionId;
	}

	if (isset($chain[$depth])) {
		return $cache[$cacheKey] = $chain[$depth];
	}

	$pick = $sectionId;
	for ($level = min($depth, max(array_keys($chain))); $level >= 1; $level--) {
		if (isset($chain[$level])) {
			$pick = $chain[$level];
			break;
		}
	}

	return $cache[$cacheKey] = $pick;
}

/**
 * Оставляет не более $maxPerSection товаров из каждого раздела (порядок сохраняется).
 * $sectionDepth > 0 — группировка по уровню в дереве (2 = «Тонометры», «Анатомические плакаты»).
 */
function kmCatalogLimitItemsPerSection(array $items, $maxPerSection = 2, $maxItems = 8, $sectionDepth = 0, $iblockId = 24)
{
	$maxPerSection = max(1, (int)$maxPerSection);
	$maxItems = max(1, (int)$maxItems);
	$sectionDepth = (int)$sectionDepth;
	$iblockId = (int)$iblockId;
	$filtered = array();
	$counts = array();

	foreach ($items as $item) {
		if (count($filtered) >= $maxItems) {
			break;
		}
		$sectionId = (int)($item['IBLOCK_SECTION_ID'] ?? 0);
		if ($sectionId <= 0 && !empty($item['ID'])) {
			$sectionId = kmCatalogElementMainSectionId((int)$item['ID']);
		}
		if ($sectionDepth > 0 && $sectionId > 0) {
			$sectionId = kmCatalogSectionIdAtDepth($sectionId, $sectionDepth, $iblockId);
		}
		$counts[$sectionId] = ($counts[$sectionId] ?? 0) + 1;
		if ($counts[$sectionId] <= $maxPerSection) {
			$filtered[] = $item;
		}
	}

	return $filtered;
}

function kmCatalogElementMainSectionId($elementId)
{
	static $cache = array();
	$elementId = (int)$elementId;
	if ($elementId <= 0) {
		return 0;
	}
	if (isset($cache[$elementId])) {
		return $cache[$elementId];
	}

	$sectionId = 0;
	$res = CIBlockElement::GetList(
		array(),
		array('ID' => $elementId),
		false,
		false,
		array('ID', 'IBLOCK_SECTION_ID')
	);
	if ($row = $res->Fetch()) {
		$sectionId = (int)($row['IBLOCK_SECTION_ID'] ?? 0);
	}

	$cache[$elementId] = $sectionId;
	return $sectionId;
}
