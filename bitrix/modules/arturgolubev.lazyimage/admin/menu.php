<?
global $USER;
if(!is_object($USER)){
	$USER = new \CUser();
}

if($USER->IsAdmin()){
	IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/arturgolubev.lazyimage/menu.php");

	$arSubmenu[] = [
		'text' => GetMessage("ARTURGOLUBEV_LAZYIMAGE_MENU_SETTINGS"),
		'more_url' => [],
		'url' => '/bitrix/admin/settings.php?lang='.LANG.'&mid=arturgolubev.lazyimage',
		'icon' => 'sys_menu_icon',
	];

	$arSubmenu[] = [
		'text' => GetMessage("ARTURGOLUBEV_LAZYIMAGE_DOCUMENTATIONS"),
		'icon' => 'update_marketplace',
		'items' => [
			[
				'text' => GetMessage("ARTURGOLUBEV_LAZYIMAGE_DOCUMENTATIONS_INSTALLATION"),
				'more_url' => [],
				'url' => 'javascript: window.open("https://arturgolubev.ru/knowledge/course19/", "_blank");void(0);',
				'icon' => '',
			],
			[
				'text' => GetMessage("ARTURGOLUBEV_LAZYIMAGE_DOCUMENTATIONS_BUY"),
				'more_url' => [],
				'url' => 'javascript: window.open("https://arturgolubev.ru/knowledge/course1/chapter064/", "_blank");void(0);',
				'icon' => '',
			],
			[
				'text' => GetMessage("ARTURGOLUBEV_LAZYIMAGE_DOCUMENTATIONS_SUPPORT"),
				'more_url' => [],
				'url' => 'javascript: window.open("https://arturgolubev.ru/knowledge/course1/", "_blank");void(0);',
				'icon' => '',
			],
		]
	];

	$aMenu = [
		'parent_menu' => 'global_menu_services',
		'section' => 'ARTURGOLUBEV_LAZYIMAGE',
		'sort' => 2,
		'text' => GetMessage("ARTURGOLUBEV_LAZYIMAGE_MENU_MAIN"),
		'icon' => 'arturgolubev_lazyimage_icon_main',
		'items_id' => 'ag_lazyimage_icon_main',
		'items' => $arSubmenu,
	];

	return $aMenu;
}