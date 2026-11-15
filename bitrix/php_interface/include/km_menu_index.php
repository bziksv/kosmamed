<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

/**
 * How the left catalog menu should look on the current page.
 * def    — product page, second level hidden from index (default and «first level»)
 * parent — second level open only on the product's top section
 * all    — second level open everywhere
 * off    — not a product page
 */
function kmMenuIndexState(): array
{
	static $state = null;
	if ($state !== null) {
		return $state;
	}

	$state = ['mode' => 'off', 'open' => [], 'key' => 'off'];
	global $APPLICATION;
	if (!is_object($APPLICATION)) {
		return $state;
	}

	$parts = explode('/', trim($APPLICATION->GetCurDir(), '/'));
	if (($parts[0] ?? '') !== 'product' || ($parts[1] ?? '') === '') {
		return $state;
	}
	if (!CModule::IncludeModule('iblock')) {
		$state['mode'] = 'def';
		$state['key'] = 'def';
		return $state;
	}

	$element = CIBlockElement::GetList(
		[],
		['IBLOCK_ID' => 24, '=CODE' => $parts[1]],
		false,
		['nTopCount' => 1],
		['ID', 'IBLOCK_SECTION_ID']
	)->Fetch();
	if (!$element || (int)$element['IBLOCK_SECTION_ID'] <= 0) {
		$state['mode'] = 'def';
		$state['key'] = 'def';
		return $state;
	}

	$sectionId = (int)$element['IBLOCK_SECTION_ID'];
	$chain = [];
	$mode = 'def';
	for ($guard = 0; $sectionId > 0 && $guard < 20; $guard++) {
		$section = CIBlockSection::GetList(
			[],
			['IBLOCK_ID' => 24, 'ID' => $sectionId],
			false,
			['ID', 'IBLOCK_SECTION_ID', 'UF_INDEX_MENU_P']
		)->Fetch();
		if (!$section) {
			break;
		}
		$chain[] = (int)$section['ID'];
		$flag = (int)$section['UF_INDEX_MENU_P'];
		if ($flag === 11) {
			$mode = 'all';
		} elseif ($flag === 10 && $mode !== 'all') {
			$mode = 'parent';
		}
		$sectionId = (int)$section['IBLOCK_SECTION_ID'];
	}

	$rootId = $chain ? (int)end($chain) : 0;
	$state['mode'] = $mode;
	$state['open'] = ($mode === 'parent') ? $chain : [];
	$state['key'] = $mode === 'parent' ? ('p' . $rootId) : $mode;
	return $state;
}
