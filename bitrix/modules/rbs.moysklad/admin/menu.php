<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\EventManager;
use Bitrix\Main\Application;

Loc::loadLanguageFile(__FILE__);

EventManager::getInstance()->addEventHandler("main", "OnBuildGlobalMenu", function (&$arGlobalMenu, &$arModuleMenu) {

	$application = Application::getInstance();
	$mid = 'rbs.moysklad';
	$lang = urlencode(LANGUAGE_ID);

	$globalMenuId = 'global_menu_despi_moysklad';

	if (!isset($arGlobalMenu[$globalMenuId])) {
		$arGlobalMenu[$globalMenuId] = [
			'menu_id' => $globalMenuId,
			'text' => Loc::getMessage('GLOBAL_MENU_NAME'),
			'sort' => 450,
			'items_id' => $globalMenuId . '_items',
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

	if(!isset($arGlobalMenu[$globalMenuId]['items'][$mid])) {
		if ($GLOBALS['APPLICATION']->GetGroupRight($mid) >= 'R') {

			$arSubSettingsMenu = [];

			foreach([
				'main', 'webhook', 'export_orders', 'import_orders', 'status','basket', 'props', 'counterparty', 'payment', 'delivery', 'service_settings', 'rights'
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
				'module_id' => $mid,
				'menu_id' => $mid,
				'text' => Loc::getMessage('MODULE_NAME_RBS_MOYSKLAD'),
				'title' => Loc::getMessage('MODULE_NAME_RBS_MOYSKLAD'),
				'sort' => 200,
				'items_id' => $mid . '_items',
				'icon' => 'rbs_moysklad_icon',
				'items' => [
					[
						'text' => Loc::getMessage('MODULE_SETTINGS'),
						'title' => Loc::getMessage('MODULE_SETTINGS'),
						'sort' => 20,
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
						'sort' => 30,
						'url' => 'https://docs.despi.ru/rbs-moysklad',
						'icon' => 'update_marketplace',
						'page_icon' => 'update_marketplace',
						'items_id' => $mid,
					],
				]
			];

			$arGlobalMenu[$globalMenuId]['items'][$mid] = $arMenu;
		}
	}

});