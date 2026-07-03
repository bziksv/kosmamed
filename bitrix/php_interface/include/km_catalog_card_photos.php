<?php

function kmCatalogCardPhotoFileExists($fileId, $src = '')
{
	$fileId = (int)$fileId;
	if ($fileId > 0) {
		$file = CFile::GetFileArray($fileId);
		if (!is_array($file) || empty($file['SRC'])) {
			return false;
		}
		$src = $file['SRC'];
		// Файлы MoySklad могут отсутствовать на локальном диске, но отдаваться сайтом/прокси.
		if (strpos($src, '/upload/rbs.moyskladstocks/') === 0) {
			return true;
		}
	}
	if ($src === '') {
		return false;
	}
	if (strpos($src, '/upload/rbs.moyskladstocks/') === 0) {
		return true;
	}
	return is_file($_SERVER['DOCUMENT_ROOT'] . $src);
}

function kmCatalogCardAppendPhoto(array &$photos, array &$seenIds, array $entry, $fileId = 0)
{
	$fileId = (int)$fileId;
	if ($fileId > 0 && !kmCatalogCardPhotoFileExists($fileId)) {
		return;
	}
	if ($fileId <= 0 && !kmCatalogCardPhotoFileExists(0, $entry['SRC'] ?? '')) {
		return;
	}
	if ($fileId > 0) {
		if (isset($seenIds[$fileId])) {
			return;
		}
		$seenIds[$fileId] = true;
		$entry['ID'] = $fileId;
	}
	$photos[] = $entry;
}

function kmCatalogCardLoadPropertyValues($iblockId, $elementId, $code)
{
	$values = array();
	$res = CIBlockElement::GetProperty($iblockId, $elementId, 'sort', 'asc', array('CODE' => $code));
	while ($ob = $res->GetNext()) {
		if (!empty($ob['VALUE'])) {
			$values[] = $ob['VALUE'];
		}
	}
	return $values;
}

/**
 * PREVIEW для галереи карточки: CFile::ResizeImageGet иногда отдаёт width/height=0
 * (файлы MoySklad и др.) — тогда миниатюра в DOM есть, но img 0×0 и не видна.
 */
function kmDetailGalleryPreviewMeta(array $arFile, array $arFileTmp, array $arParams = array())
{
	$preview = array(
		'SRC' => $arFileTmp['src'] ?? '',
		'WIDTH' => (int)($arFileTmp['width'] ?? 0),
		'HEIGHT' => (int)($arFileTmp['height'] ?? 0),
	);
	if ($preview['WIDTH'] > 0 && $preview['HEIGHT'] > 0) {
		return $preview;
	}

	$maxW = (int)($arParams['DISPLAY_MORE_PHOTO_WIDTH'] ?: 146);
	$maxH = (int)($arParams['DISPLAY_MORE_PHOTO_HEIGHT'] ?: 146);
	$srcW = (int)($arFile['WIDTH'] ?? 0);
	$srcH = (int)($arFile['HEIGHT'] ?? 0);

	if (($srcW <= 0 || $srcH <= 0) && $preview['SRC'] !== '') {
		$path = $_SERVER['DOCUMENT_ROOT'] . $preview['SRC'];
		if (is_file($path) && ($info = @getimagesize($path))) {
			$srcW = (int)$info[0];
			$srcH = (int)$info[1];
		}
	}

	if ($srcW > 0 && $srcH > 0) {
		$ratio = min($maxW / $srcW, $maxH / $srcH, 1);
		$preview['WIDTH'] = max(1, (int)round($srcW * $ratio));
		$preview['HEIGHT'] = max(1, (int)round($srcH * $ratio));
	} else {
		$preview['WIDTH'] = $maxW;
		$preview['HEIGHT'] = $maxH;
	}

	return $preview;
}

function kmCatalogCardResizePhoto($fileId, $width = 588, $height = 784)
{
	$file = CFile::GetFileArray((int)$fileId);
	$arFileTmpp = CFile::ResizeImageGet(
		$fileId,
		array('width' => $width, 'height' => $height),
		BX_RESIZE_IMAGE_PROPORTIONAL,
		true
	);
	if (!$arFileTmpp || empty($arFileTmpp['src'])) {
		return null;
	}
	$meta = kmDetailGalleryPreviewMeta(
		is_array($file) ? $file : array(),
		$arFileTmpp,
		array('DISPLAY_MORE_PHOTO_WIDTH' => $width, 'DISPLAY_MORE_PHOTO_HEIGHT' => $height)
	);
	return array(
		'SRC' => $meta['SRC'],
		'WIDTH' => $meta['WIDTH'],
		'HEIGHT' => $meta['HEIGHT'],
		'ID' => (int)$fileId,
	);
}

function kmCatalogCardPhotoPixelArea(array $file = null)
{
	if (!is_array($file)) {
		return 0;
	}
	$width = (int)($file['WIDTH'] ?? 0);
	$height = (int)($file['HEIGHT'] ?? 0);
	if ($width > 0 && $height > 0) {
		return $width * $height;
	}
	$fileId = (int)($file['ID'] ?? 0);
	if ($fileId <= 0) {
		return 0;
	}
	$loaded = CFile::GetFileArray($fileId);
	if (!is_array($loaded)) {
		return 0;
	}
	return (int)$loaded['WIDTH'] * (int)$loaded['HEIGHT'];
}

function kmCatalogCardHasIblockPhoto(array $arElement): bool
{
	$previewId = (int)($arElement['PREVIEW_PICTURE']['ID'] ?? 0);
	$detailId = (int)($arElement['DETAIL_PICTURE']['ID'] ?? 0);
	if ($previewId > 0 || $detailId > 0) {
		return true;
	}

	$iblockId = (int)($arElement['IBLOCK_ID'] ?? 0);
	$elementId = (int)($arElement['ID'] ?? 0);
	if ($elementId <= 0 || $iblockId <= 0) {
		return false;
	}

	$res = CIBlockElement::GetList(
		array(),
		array('IBLOCK_ID' => $iblockId, 'ID' => $elementId),
		false,
		false,
		array('ID', 'PREVIEW_PICTURE', 'DETAIL_PICTURE')
	);
	if ($row = $res->Fetch()) {
		return ((int)$row['PREVIEW_PICTURE'] > 0 || (int)$row['DETAIL_PICTURE'] > 0);
	}

	return false;
}

function kmCatalogCardFileContentHash(array $file)
{
	$size = (int)($file['FILE_SIZE'] ?? 0);
	if ($size <= 0 || empty($file['SRC'])) {
		return null;
	}
	$path = $_SERVER['DOCUMENT_ROOT'] . $file['SRC'];
	if (!is_file($path)) {
		return null;
	}
	return md5_file($path);
}

function kmCatalogCardPrimaryPhotoFiles(array $arElement): array
{
	$files = array();
	foreach (array('DETAIL_PICTURE', 'PREVIEW_PICTURE') as $key) {
		$file = $arElement[$key] ?? null;
		if (is_array($file) && !empty($file['ID'])) {
			$files[(int)$file['ID']] = $file;
		}
	}
	$primaryId = kmCatalogCardPrimaryPhotoId($arElement);
	if ($primaryId > 0) {
		$loaded = CFile::GetFileArray($primaryId);
		if (is_array($loaded)) {
			$files[(int)$primaryId] = $loaded;
		}
	}
	return array_values($files);
}

/**
 * Дубль MORE_PHOTO: тот же ID, тот же контент (md5) или тот же размер, что у главного фото iblock.
 * У плакатов MoySklad часто кладёт копию DETAIL_PICTURE (одинаковый FILE_SIZE);
 * у STOMATOL доп. ракурсы другого размера — их оставляем.
 */
function kmCatalogCardIsDuplicateMorePhoto($fileId, array $arElement): bool
{
	$fileId = (int)$fileId;
	$file = CFile::GetFileArray($fileId);
	if (!$file) {
		return true;
	}

	$primaries = kmCatalogCardPrimaryPhotoFiles($arElement);
	foreach ($primaries as $primary) {
		if ((int)($primary['ID'] ?? 0) === $fileId) {
			return true;
		}
	}

	$isMoysklad = strpos($file['SRC'], '/upload/rbs.moyskladstocks/') === 0;
	if (!$isMoysklad || !kmCatalogCardHasIblockPhoto($arElement)) {
		return false;
	}

	$fileSize = (int)($file['FILE_SIZE'] ?? 0);
	$fileHash = kmCatalogCardFileContentHash($file);

	foreach ($primaries as $primary) {
		$primarySize = (int)($primary['FILE_SIZE'] ?? 0);
		if ($primarySize <= 0 || $fileSize !== $primarySize) {
			continue;
		}

		$primaryHash = kmCatalogCardFileContentHash($primary);
		if ($fileHash !== null && $primaryHash !== null) {
			if ($fileHash === $primaryHash) {
				return true;
			}
			continue;
		}

		// Файл MoySklad может быть только на prod/прокси — совпадение размера с DETAIL достаточно.
		return true;
	}

	return false;
}

function kmCatalogCardPosterRootSectionId()
{
	return 13971;
}

function kmCatalogCardPosterSectionIds()
{
	static $ids = null;
	if ($ids !== null) {
		return $ids;
	}

	$ids = array();
	$rootId = kmCatalogCardPosterRootSectionId();
	$root = CIBlockSection::GetList(
		array(),
		array('IBLOCK_ID' => 24, 'ID' => $rootId),
		false,
		array('ID', 'LEFT_MARGIN', 'RIGHT_MARGIN')
	)->Fetch();
	if (!$root) {
		$ids[$rootId] = true;
		return $ids;
	}

	$res = CIBlockSection::GetList(
		array('LEFT_MARGIN' => 'ASC'),
		array(
			'IBLOCK_ID' => 24,
			'>=LEFT_MARGIN' => (int)$root['LEFT_MARGIN'],
			'<=RIGHT_MARGIN' => (int)$root['RIGHT_MARGIN'],
		),
		false,
		array('ID')
	);
	while ($row = $res->Fetch()) {
		$ids[(int)$row['ID']] = true;
	}

	return $ids;
}

function kmCatalogCardIsPoster(array $arElement)
{
	static $posterSections = null;
	if ($posterSections === null) {
		$posterSections = kmCatalogCardPosterSectionIds();
	}

	$inPosterSection = function ($sectionId) use ($posterSections) {
		return isset($posterSections[(int)$sectionId]);
	};

	foreach (array('IBLOCK_SECTION_ID', 'SECTION_ID') as $key) {
		if (!empty($arElement[$key]) && $inPosterSection($arElement[$key])) {
			return true;
		}
	}

	$elementId = (int)($arElement['ID'] ?? 0);
	if ($elementId <= 0) {
		return false;
	}

	static $elementPosterCache = array();
	if (isset($elementPosterCache[$elementId])) {
		return $elementPosterCache[$elementId];
	}

	$isPoster = false;
	$groups = CIBlockElement::GetElementGroups($elementId, true);
	while ($group = $groups->Fetch()) {
		if ($inPosterSection($group['ID'])) {
			$isPoster = true;
			break;
		}
	}

	$elementPosterCache[$elementId] = $isPoster;
	return $isPoster;
}

function kmCatalogCardPrimaryPhotoId(array $arElement)
{
	$preview = is_array($arElement['PREVIEW_PICTURE'] ?? null) ? $arElement['PREVIEW_PICTURE'] : array();
	$detail = is_array($arElement['DETAIL_PICTURE'] ?? null) ? $arElement['DETAIL_PICTURE'] : array();
	$iblockId = (int)($arElement['IBLOCK_ID'] ?? 0);
	$elementId = (int)($arElement['ID'] ?? 0);

	if (empty($detail['ID']) && $elementId > 0 && $iblockId > 0) {
		$res = CIBlockElement::GetList(
			array(),
			array('IBLOCK_ID' => $iblockId, 'ID' => $elementId),
			false,
			false,
			array('ID', 'DETAIL_PICTURE')
		);
		if ($row = $res->Fetch()) {
			$detailFileId = (int)($row['DETAIL_PICTURE'] ?? 0);
			if ($detailFileId > 0) {
				$loadedDetail = CFile::GetFileArray($detailFileId);
				if (is_array($loadedDetail)) {
					$detail = $loadedDetail;
				}
			}
		}
	}

	$previewId = (int)($preview['ID'] ?? 0);
	$detailId = (int)($detail['ID'] ?? 0);
	$previewArea = kmCatalogCardPhotoPixelArea($preview);
	$detailArea = kmCatalogCardPhotoPixelArea($detail);

	if ($detailId > 0 && $detailArea >= $previewArea) {
		return $detailId;
	}
	if ($previewId > 0) {
		return $previewId;
	}
	return $detailId;
}

/**
 * Фото карточки каталога: инфографика → превью → MORE_PHOTO, без дублей по ID файла.
 *
 * @return array{photos: array, hasInfographics: bool, isPoster: bool, wideImage: bool}
 */
function kmCatalogCardPhotos(array $arElement, $maxPhotos = 5)
{
	$photos = array();
	$seenIds = array();
	$iblockId = (int)$arElement['IBLOCK_ID'];
	$elementId = (int)$arElement['ID'];

	$isPoster = kmCatalogCardIsPoster($arElement);

	$infographics = kmCatalogCardLoadPropertyValues($iblockId, $elementId, 'INFOGRAPHICS');
	$hasInfographics = !empty($infographics[0]);

	if ($hasInfographics) {
		foreach ($infographics as $idx => $fileId) {
			if (count($photos) >= $maxPhotos) {
				break;
			}
			$entry = kmCatalogCardResizePhoto($fileId);
			if ($entry) {
				kmCatalogCardAppendPhoto($photos, $seenIds, $entry, $fileId);
			}
			if ($idx >= 4) {
				break;
			}
		}
	}

	if (count($photos) < $maxPhotos) {
		$primaryPhotoId = kmCatalogCardPrimaryPhotoId($arElement);
		if ($primaryPhotoId > 0) {
			$entry = kmCatalogCardResizePhoto($primaryPhotoId);
			if ($entry) {
				kmCatalogCardAppendPhoto($photos, $seenIds, $entry, $primaryPhotoId);
			}
		} else {
			$preview = is_array($arElement['PREVIEW_PICTURE'] ?? null) ? $arElement['PREVIEW_PICTURE'] : array();
			$detail = is_array($arElement['DETAIL_PICTURE'] ?? null) ? $arElement['DETAIL_PICTURE'] : array();
			if (!empty($preview['SRC'])) {
				kmCatalogCardAppendPhoto($photos, $seenIds, array(
					'SRC' => $preview['SRC'],
					'WIDTH' => $preview['WIDTH'],
					'HEIGHT' => $preview['HEIGHT'],
				));
			} elseif (!empty($detail['SRC'])) {
				kmCatalogCardAppendPhoto($photos, $seenIds, array(
					'SRC' => $detail['SRC'],
					'WIDTH' => $detail['WIDTH'],
					'HEIGHT' => $detail['HEIGHT'],
				));
			}
		}
	}

	if (count($photos) < $maxPhotos) {
		$morePhoto = kmCatalogCardLoadPropertyValues($iblockId, $elementId, 'MORE_PHOTO');
		foreach ($morePhoto as $fileId) {
			if (count($photos) >= $maxPhotos) {
				break;
			}
			if (kmCatalogCardIsDuplicateMorePhoto($fileId, $arElement)) {
				continue;
			}
			$entry = kmCatalogCardResizePhoto($fileId);
			if ($entry) {
				kmCatalogCardAppendPhoto($photos, $seenIds, $entry, $fileId);
			}
		}
	}

	return array(
		'photos' => $photos,
		'hasInfographics' => $hasInfographics,
		'isPoster' => $isPoster,
		'wideImage' => $hasInfographics || $isPoster,
	);
}
