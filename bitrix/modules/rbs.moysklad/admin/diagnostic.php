<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';

IncludeModuleLangFile(__FILE__);

$APPLICATION->SetTitle(GetMessage("DIAGNOSTIC_PAGE_TITLE"));

if (!$USER->IsAdmin())
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_admin_after.php");

\Bitrix\Main\Loader::includeModule('rbs.moysklad');

$assetsLoader = \Rbs\Moysklad\Services\AssetsLoader::getInstance('diagnostic');

?>
<?= $assetsLoader->renderApp() ?>
<?php
require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
?>