<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\EventManager;
use Bitrix\Main\Application;

Loc::loadLanguageFile(__FILE__);

EventManager::getInstance()->addEventHandler("main", "OnBuildGlobalMenu", function (&$arGlobalMenu, &$arModuleMenu) {

	$application = Application::getInstance();
	$mid = 'rbs.moyskladstocks';
	$lang = urlencode(LANGUAGE_ID);

	if (!isset($arGlobalMenu['global_menu_despi_moysklad'])) {
		$arGlobalMenu['global_menu_despi_moysklad'] = [
			'menu_id' => 'global_menu_despi_moysklad',
			'text' => Loc::getMessage('GLOBAL_MENU_NAME'),
			'sort' => 450,
			'items_id' => 'global_menu_despi_moysklad_items',
			'items' => []
		];
	}

	$menuCssFileName = 'menu.css';
	$cssDir = $application->getDocumentRoot() . "/bitrix/css/{$mid}";
	if (!file_exists("{$cssDir}/{$menuCssFileName}")) {
		if (!is_dir($cssDir)) {
			mkdir($cssDir);
		}
		$installCssDir = __DIR__ . '/../install/assets/css';
		if (file_exists("{$installCssDir}/{$menuCssFileName}") && is_dir($cssDir)) {
			CopyDirFiles($installCssDir, $cssDir, true, true);
		}
	}
	if (file_exists("{$cssDir}/{$menuCssFileName}")) {
		$GLOBALS['APPLICATION']->SetAdditionalCss("/bitrix/css/{$mid}/{$menuCssFileName}");
	}

	$moduleTypeDir = is_dir($application->getDocumentRoot() . '/local/modules/' . $mid) ? 'local' : 'bitrix';

	foreach (['diagnostic.php'] as $origFileName) {
		
		$adminFilePath = $application->getDocumentRoot() . '/bitrix/admin/' . $mid . '_' . $origFileName;

		$needUpdate = !file_exists($adminFilePath);
		if (!$needUpdate) {
			$fileContent = file_get_contents($adminFilePath);
			$needUpdate = mb_strpos($fileContent, "{$moduleTypeDir}/modules/{$mid}/admin/{$origFileName}") === false;
		}

		if ($needUpdate) {
			$fileContent = "<?php\n";
			$fileContent .= "require_once(\$_SERVER['DOCUMENT_ROOT'] . '/" . $moduleTypeDir . "/modules/" . $mid . "/admin/" . $origFileName . "');\n";
			$fileContent .= "?>";
			file_put_contents($adminFilePath, $fileContent);
		}
		
	}

	if (!isset($arGlobalMenu['global_menu_despi_moysklad']['items'][$mid])) {
		if ($GLOBALS['APPLICATION']->GetGroupRight($mid) >= 'R') {

			$arSubSettingsMenu = [];

			foreach ([
				'main', 'webhook', 'stocks', 'current_stocks', 'prices', 'discount', 'import_product', 'import_variant', 'import_bundle', 'import_service', 'import_productfolder', 'others', 'rights'
			] as $key => $tab) {
				$arSubSettingsMenu[] = [
					'text' => Loc::getMessage('MODULE_SETTINGS_TAB_' . $tab),
					'title' => Loc::getMessage('MODULE_SETTINGS_TAB_' . $tab),
					'sort' => $key * 10,
					'url' => "/bitrix/admin/settings.php?mid={$mid}&lang={$lang}&tab={$tab}",
					'icon' => '',
					'page_icon' => '',
					'items_id' => $mid . '_items',
				];
			}

			$arMenu = [
				'menu_id' => $mid,
				'text' => Loc::getMessage('MODULE_NAME_RBS_MOYSKLADSTOCKS'),
				'title' => Loc::getMessage('MODULE_NAME_RBS_MOYSKLADSTOCKS'),
				'sort' => 100,
				'items_id' => $mid . '_items',
				'icon' => 'rbs_moyskladstocks_icon',
				'items' => [
					[
						'text' => Loc::getMessage('MODULE_SETTINGS'),
						'title' => Loc::getMessage('MODULE_SETTINGS'),
						'sort' => 10,
						'url' => "/bitrix/admin/settings.php?mid={$mid}&lang={$lang}&tab=main",
						'icon' => 'sys_menu_icon',
						'page_icon' => 'sys_menu_icon',
						'items_id' => $mid,
						'items' => $arSubSettingsMenu
					],
					[
						'text' => Loc::getMessage('MODULE_SETTINGS_TAB_diagnostic'),
						'title' => Loc::getMessage('MODULE_SETTINGS_TAB_diagnostic'),
						'sort' => 20,
						'url' => "/bitrix/admin/{$mid}_diagnostic.php",
						'icon' => 'util_menu_icon',
						'page_icon' => 'util_menu_icon',
						'items_id' => $mid,
					],
					[
						'text' => Loc::getMessage('MODULE_SETTINGS_TAB_doc_link'),
						'title' => Loc::getMessage('MODULE_SETTINGS_TAB_doc_link'),
						'sort' => 40,
						'url' => 'https://docs.despi.ru/rbs-moyskladstocks',
						'icon' => 'update_marketplace',
						'page_icon' => 'update_marketplace',
						'items_id' => $mid,
					],
				],
			];

			$arGlobalMenu['global_menu_despi_moysklad']['items'][$mid] = $arMenu;
		}
	}
});
