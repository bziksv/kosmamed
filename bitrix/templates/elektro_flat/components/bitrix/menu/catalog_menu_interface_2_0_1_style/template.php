<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$this->setFrameMode(true);

global $arSettings;

$isSiteClosed = false;
if(COption::GetOptionString("main", "site_stopped") == "Y" && !$USER->CanDoOperation("edit_other_settings"))
	$isSiteClosed = true;

$obName = 'ob'.preg_replace('/[^a-zA-Z0-9_]/', 'x', $this->GetEditAreaId($this->randString()));
$containerName = 'catalog-menu-'.$obName;

if(!$isSiteClosed && !empty($arResult)) {
	$cssRules = '';
	foreach($arResult as $arItem) {
		if(!$arItem["SHOW"] || ($arItem["DEPTH_LEVEL"]>1 && $arItem["PARAMS"]["HIDE_MENU_INDEX"] && $arItem["PARAMS"]["URL"]=='product')) {
			$arCode = explode('/', $arItem["LINK"]);
			if (empty($arCode[2])) {
				continue;
			}
			$sectionCode = preg_replace('/[^a-zA-Z0-9_-]/', '', $arCode[2]);
			if ($sectionCode === '') {
				continue;
			}
			$label = str_replace(["\\", "'"], ["\\\\", "\\'"], (string)$arItem["TEXT"]);
			$cssRules .= ".show_parent_{$sectionCode} div a:before{content:'';}\n";
			$cssRules .= ".show_parent_{$sectionCode} a:before{content:'{$label}';}\n";
			$cssRules .= ".show_{$sectionCode}:before{content:'{$label}';}\n";
		}
	}

	if ($cssRules !== '') {
		$cacheDir = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/cache/km_menu_css';
		if (!is_dir($cacheDir) && !@mkdir($cacheDir, 0755, true) && !is_dir($cacheDir)) {
			echo '<style>' . $cssRules . '</style>';
		} else {
			$cacheName = md5($cssRules) . '.css';
			$cacheFile = $cacheDir . '/' . $cacheName;
			if (!is_file($cacheFile)) {
				file_put_contents($cacheFile, $cssRules);
				@chmod($cacheFile, 0644);
			}
			$cssHref = '/bitrix/cache/km_menu_css/' . $cacheName;
			// На /product/ подписи пунктов flyout — через ::before; defer ломает hover до загрузки CSS
			global $APPLICATION;
			$kmMenuCssSync = is_object($APPLICATION) && strpos((string)$APPLICATION->GetCurDir(), '/product/') === 0;
			if (function_exists('kmDeferStylesheet') && !$kmMenuCssSync) {
				kmDeferStylesheet($cssHref . '?v=' . filemtime($cacheFile));
			} else {
				\Bitrix\Main\Page\Asset::getInstance()->addCss($cssHref . '?v=' . filemtime($cacheFile));
			}
		}
	}
}
?>
