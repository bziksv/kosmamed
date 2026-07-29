<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

/** @global CMain $APPLICATION */
/** @global CUser $USER */

$moduleId = 'prime.alerts';

Loc::loadMessages(__FILE__);

if (!$USER->IsAdmin()) {
	return;
}

Loader::includeModule($moduleId);

$note = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
	$boolKeys = [
		'enabled',
		'policy_enabled',
		'policy_register',
		'policy_order',
	];
	foreach ($boolKeys as $key) {
		Option::set($moduleId, $key, !empty($_POST[$key]) && $_POST[$key] === 'Y' ? 'Y' : 'N');
	}

	Option::set($moduleId, 'support_email', trim((string)($_POST['support_email'] ?? '')));
	Option::set($moduleId, 'support_phone', trim((string)($_POST['support_phone'] ?? '')));
	Option::set($moduleId, 'extra_domains', trim((string)($_POST['extra_domains'] ?? '')));

	$note = Loc::getMessage('PRIME_ALERTS_SAVED');
}

$aTabs = [
	[
		'DIV' => 'edit1',
		'TAB' => Loc::getMessage('PRIME_ALERTS_TAB'),
		'TITLE' => Loc::getMessage('PRIME_ALERTS_TAB_TITLE'),
	],
];

$tabControl = new CAdminTabControl('primeAlertsTabControl', $aTabs);

if ($note !== '') {
	CAdminMessage::ShowNote($note);
}

$get = static function (string $name, string $default = '') use ($moduleId): string {
	return (string) Option::get($moduleId, $name, $default);
};

$checked = static function (string $name, string $default = 'N') use ($get): string {
	return $get($name, $default) === 'Y' ? ' checked' : '';
};
?>
<form method="post" action="<?= $APPLICATION->GetCurPage() ?>?mid=<?= urlencode($moduleId) ?>&lang=<?= LANGUAGE_ID ?>">
	<?= bitrix_sessid_post() ?>
	<?php $tabControl->Begin(); ?>
	<?php $tabControl->BeginNextTab(); ?>

	<tr>
		<td width="40%"><?= Loc::getMessage('PRIME_ALERTS_ENABLED') ?>:</td>
		<td width="60%"><input type="checkbox" name="enabled" value="Y"<?= $checked('enabled', 'Y') ?>></td>
	</tr>

	<tr class="heading"><td colspan="2">Политика e-mail</td></tr>
	<tr>
		<td valign="top">
			<?= Loc::getMessage('PRIME_ALERTS_POLICY_ENABLED') ?>:<br>
			<small><?= Loc::getMessage('PRIME_ALERTS_POLICY_ENABLED_HINT') ?></small>
		</td>
		<td valign="top"><input type="checkbox" name="policy_enabled" value="Y"<?= $checked('policy_enabled', 'Y') ?>></td>
	</tr>
	<tr>
		<td><?= Loc::getMessage('PRIME_ALERTS_POLICY_REGISTER') ?>:</td>
		<td><input type="checkbox" name="policy_register" value="Y"<?= $checked('policy_register', 'Y') ?>></td>
	</tr>
	<tr>
		<td><?= Loc::getMessage('PRIME_ALERTS_POLICY_ORDER') ?>:</td>
		<td><input type="checkbox" name="policy_order" value="Y"<?= $checked('policy_order', 'Y') ?>></td>
	</tr>
	<tr>
		<td><?= Loc::getMessage('PRIME_ALERTS_SUPPORT_EMAIL') ?>:</td>
		<td><input type="text" name="support_email" size="40" value="<?= htmlspecialcharsbx($get('support_email', 'info@kosmamed.ru')) ?>"></td>
	</tr>
	<tr>
		<td><?= Loc::getMessage('PRIME_ALERTS_SUPPORT_PHONE') ?>:</td>
		<td><input type="text" name="support_phone" size="40" value="<?= htmlspecialcharsbx($get('support_phone', '8-800-100-37-97')) ?>"></td>
	</tr>
	<tr>
		<td valign="top">
			<?= Loc::getMessage('PRIME_ALERTS_EXTRA_DOMAINS') ?>:<br>
			<small><?= Loc::getMessage('PRIME_ALERTS_EXTRA_DOMAINS_HINT') ?></small>
		</td>
		<td valign="top">
			<input type="text" name="extra_domains" size="50" value="<?= htmlspecialcharsbx($get('extra_domains')) ?>">
		</td>
	</tr>

	<?php $tabControl->Buttons(); ?>
	<input type="submit" name="save" value="Сохранить" class="adm-btn-save">
	<?php $tabControl->End(); ?>
</form>
