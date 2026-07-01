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
