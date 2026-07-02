<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

if (!function_exists('kmSectionPreviewFileId')) {
	/**
	 * Preview for catalog menu: first available product image in section subtree,
	 * then parent sections (direct products only). Mirrors tools/section-previews/generate.php.
	 */
	function kmSectionPreviewFileId(int $sectionId): int
	{
		static $cache = [];

		if ($sectionId <= 0) {
			return 0;
		}
		if (array_key_exists($sectionId, $cache)) {
			return $cache[$sectionId];
		}

		$cacheDir = '/km_section_preview';
		$cacheId = 'file_id_' . $sectionId;
		$obCache = new CPHPCache();
		if ($obCache->InitCache(86400, $cacheId, $cacheDir)) {
			$vars = $obCache->GetVars();
			return $cache[$sectionId] = (int)($vars['fileId'] ?? 0);
		}

		if (!CModule::IncludeModule('iblock')) {
			return $cache[$sectionId] = 0;
		}

		if (!$obCache->StartDataCache()) {
			return $cache[$sectionId] = 0;
		}

		$fileId = kmSectionPreviewFileIdInSection($sectionId, true);
		if ($fileId <= 0) {
			$section = CIBlockSection::GetByID($sectionId)->Fetch();
			$parentId = $section ? (int)$section['IBLOCK_SECTION_ID'] : 0;
			while ($parentId > 0 && $fileId <= 0) {
				$fileId = kmSectionPreviewFileIdInSection($parentId, false);
				if ($fileId > 0) {
					break;
				}
				$parent = CIBlockSection::GetByID($parentId)->Fetch();
				$parentId = $parent ? (int)$parent['IBLOCK_SECTION_ID'] : 0;
			}
		}

		$obCache->EndDataCache(['fileId' => $fileId]);
		return $cache[$sectionId] = $fileId;
	}
}

if (!function_exists('kmSectionPreviewFileIdInSection')) {
	function kmSectionPreviewFileIdInSection(int $sectionId, bool $includeSubsections): int
	{
		$rs = CIBlockElement::GetList(
			['SORT' => 'ASC', 'ID' => 'ASC'],
			[
				'IBLOCK_ID' => 24,
				'SECTION_ID' => $sectionId,
				'INCLUDE_SUBSECTIONS' => $includeSubsections ? 'Y' : 'N',
				'ACTIVE' => 'Y',
			],
			false,
			['nTopCount' => 15],
			['ID', 'PREVIEW_PICTURE', 'DETAIL_PICTURE']
		);

		while ($el = $rs->Fetch()) {
			$fileId = kmSectionPreviewElementFileId($el);
			if ($fileId > 0) {
				return $fileId;
			}
		}

		return 0;
	}
}

if (!function_exists('kmSectionPreviewElementFileId')) {
	function kmSectionPreviewElementFileId(array $element): int
	{
		$fileId = (int)($element['PREVIEW_PICTURE'] ?? 0) ?: (int)($element['DETAIL_PICTURE'] ?? 0);
		if (kmSectionPreviewIsFileUsable($fileId)) {
			return $fileId;
		}

		$rs = CIBlockElement::GetProperty(24, (int)$element['ID'], ['sort' => 'asc'], ['CODE' => 'MORE_PHOTO']);
		while ($prop = $rs->Fetch()) {
			if (!empty($prop['VALUE']) && kmSectionPreviewIsFileUsable((int)$prop['VALUE'])) {
				return (int)$prop['VALUE'];
			}
		}

		return 0;
	}
}

if (!function_exists('kmSectionPreviewIsFileUsable')) {
	function kmSectionPreviewIsFileUsable(int $fileId): int
	{
		if ($fileId <= 0) {
			return 0;
		}

		$file = CFile::GetFileArray($fileId);
		if (empty($file['SRC'])) {
			return 0;
		}

		$path = $_SERVER['DOCUMENT_ROOT'] . $file['SRC'];
		if (is_file($path)) {
			return $fileId;
		}

		// On dev, /upload/ may 302 to prod — still emit resize URL for storefront.
		if (strpos($file['SRC'], '/upload/') === 0) {
			return $fileId;
		}

		return 0;
	}
}

if (!function_exists('kmSectionPreviewResize')) {
	function kmSectionPreviewResize(int $fileId): ?array
	{
		if ($fileId <= 0) {
			return null;
		}

		$resized = CFile::ResizeImageGet(
			$fileId,
			['width' => 50, 'height' => 50],
			BX_RESIZE_IMAGE_PROPORTIONAL,
			true
		);

		if (empty($resized['src'])) {
			return null;
		}

		return [
			'SRC' => $resized['src'],
			'WIDTH' => (int)$resized['width'],
			'HEIGHT' => (int)$resized['height'],
		];
	}
}
