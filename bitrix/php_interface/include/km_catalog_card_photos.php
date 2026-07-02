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
	}
	if ($src === '') {
		return false;
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

function kmCatalogCardResizePhoto($fileId, $width = 588, $height = 784)
{
	$arFileTmpp = CFile::ResizeImageGet(
		$fileId,
		array('width' => $width, 'height' => $height),
		BX_RESIZE_IMAGE_PROPORTIONAL,
		true
	);
	if (!$arFileTmpp || empty($arFileTmpp['src'])) {
		return null;
	}
	return array(
		'SRC' => $arFileTmpp['src'],
		'WIDTH' => $arFileTmpp['width'],
		'HEIGHT' => $arFileTmpp['height'],
		'ID' => (int)$fileId,
	);
}

/**
 * Фото карточки каталога: инфографика → превью → MORE_PHOTO, без дублей по ID файла.
 *
 * @return array{photos: array, hasInfographics: bool}
 */
function kmCatalogCardPhotos(array $arElement, $maxPhotos = 5)
{
	$photos = array();
	$seenIds = array();
	$iblockId = (int)$arElement['IBLOCK_ID'];
	$elementId = (int)$arElement['ID'];

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
		if (!empty($arElement['PREVIEW_PICTURE']['SRC'])) {
			kmCatalogCardAppendPhoto($photos, $seenIds, array(
				'SRC' => $arElement['PREVIEW_PICTURE']['SRC'],
				'WIDTH' => $arElement['PREVIEW_PICTURE']['WIDTH'],
				'HEIGHT' => $arElement['PREVIEW_PICTURE']['HEIGHT'],
			), (int)($arElement['PREVIEW_PICTURE']['ID'] ?? 0));
		} elseif (!empty($arElement['DETAIL_PICTURE']['SRC'])) {
			kmCatalogCardAppendPhoto($photos, $seenIds, array(
				'SRC' => $arElement['DETAIL_PICTURE']['SRC'],
				'WIDTH' => $arElement['DETAIL_PICTURE']['WIDTH'],
				'HEIGHT' => $arElement['DETAIL_PICTURE']['HEIGHT'],
			), (int)($arElement['DETAIL_PICTURE']['ID'] ?? 0));
		}
	}

	if (count($photos) < $maxPhotos) {
		$morePhoto = kmCatalogCardLoadPropertyValues($iblockId, $elementId, 'MORE_PHOTO');
		foreach ($morePhoto as $fileId) {
			if (count($photos) >= $maxPhotos) {
				break;
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
	);
}
