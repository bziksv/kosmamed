<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$kmViewedLimit = 19;
if (!empty($arResult['ITEMS']) && count($arResult['ITEMS']) > $kmViewedLimit) {
	$arResult['ITEMS'] = array_slice($arResult['ITEMS'], 0, $kmViewedLimit, true);
}

foreach ($arResult['ITEMS'] as $key => $arItem) {
	$picture = null;

	if (function_exists('kmCatalogCardPhotos')) {
		$cardPhotos = kmCatalogCardPhotos($arItem, 1);
		if (!empty($cardPhotos['photos'][0]['SRC'])) {
			$picture = $cardPhotos['photos'][0];
		}
	}

	if (!$picture) {
		$file = null;
		if (function_exists('kmCatalogCardPrimaryPhotoId')) {
			$photoId = kmCatalogCardPrimaryPhotoId($arItem);
			if ($photoId > 0) {
				$file = CFile::GetFileArray($photoId);
			}
		}
		if (!$file && is_array($arItem['DETAIL_PICTURE'] ?? null) && !empty($arItem['DETAIL_PICTURE']['ID'])) {
			$file = $arItem['DETAIL_PICTURE'];
		}
		if (!$file && is_array($arItem['PREVIEW_PICTURE'] ?? null) && !empty($arItem['PREVIEW_PICTURE']['ID'])) {
			$file = $arItem['PREVIEW_PICTURE'];
		}
		if ($file) {
			$resized = CFile::ResizeImageGet(
				$file,
				array('width' => 65, 'height' => 65),
				BX_RESIZE_IMAGE_PROPORTIONAL,
				true
			);
			if (!empty($resized['src'])) {
				$picture = array(
					'SRC' => $resized['src'],
					'WIDTH' => $resized['width'],
					'HEIGHT' => $resized['height'],
				);
			}
		}
	}

	if ($picture) {
		$arResult['ITEMS'][$key]['PICTURE'] = $picture;
	}
}
