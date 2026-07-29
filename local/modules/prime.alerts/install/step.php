<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__DIR__ . '/index.php');

echo '<p>' . htmlspecialcharsbx(Loc::getMessage('PRIME_ALERTS_MODULE_NAME')) . ' — установлен.</p>';
echo '<p><a href="/bitrix/admin/settings.php?lang=' . LANGUAGE_ID . '&mid=prime.alerts">Настройки модуля</a></p>';
