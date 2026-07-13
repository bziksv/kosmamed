<?php

use Bitrix\Main\Loader;
use Bitrix\Main\Application;
use Bitrix\Main\SiteTable;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;

use Viamodo\Telegramsalenotify\Util;

Loc::loadMessages(__FILE__);

$rsSites = SiteTable::getList([
	'order' => ['SORT' => 'ASC'],
	'filter' => ['=ACTIVE' => 'Y'],
]);
$arSites = $rsSites->fetchAll();

if (!Loader::includeModule('viamodo.telegramsalenotify')) {
	ShowError('Cant load module viamodo.telegramsalenotify');
	return;
}

$request = Application::getInstance()->getContext()->getRequest();

$currentChats = Option::get($mid, 'chat_ids', '');
$arCurrentChats = explode(',', $currentChats);

$addChatId = (int) $request->getQuery('addChat');
if (!empty($addChatId)) {
	$username = (string) $request->getQuery('username');
	if (!empty($currentChats) && !empty($arCurrentChats)) {
		$arCurrentChats[] = $addChatId;
		$arCurrentChats = array_unique($arCurrentChats);
		$newValue = implode(',', $arCurrentChats);
	} else {
		$newValue = $addChatId;
	}
	Option::set($mid, 'chat_ids', $newValue);
	Option::set($mid, 'chat_'.$addChatId, $username);
	LocalRedirect($APPLICATION->GetCurPage().'?mid='.urlencode($mid).'&amp;lang='.LANGUAGE_ID);
}

$removeChatId = (int) $request->getQuery('removeChat');
if (!empty($removeChatId)) {
	$arNewChats = array_filter(
		$arCurrentChats,
		function ($v, $k) use($removeChatId) { return $v != $removeChatId; },
		ARRAY_FILTER_USE_BOTH
	);
	$newValue = implode(',', $arNewChats);
	Option::set($mid, 'chat_ids', $newValue);
	LocalRedirect($APPLICATION->GetCurPage().'?mid='.urlencode($mid).'&amp;lang='.LANGUAGE_ID);
}

$aTabs = [];
$aTabs[] = [
	'DIV' => 'viamodo_tn_tab_settings',
	'TAB' => Loc::getMessage('VIAMODO_TSN_TAB_NAME'),
	'ICON' => '',
	'TITLE' => Loc::getMessage('VIAMODO_TSN_TAB_TITLE'),
];
$aTabs[] = [
	'DIV' => 'viamodo_tn_tab_sites',
	'TAB' => Loc::getMessage('VIAMODO_TSN_TAB_SITES_NAME'),
	'ICON' => '',
	'TITLE' => Loc::getMessage('VIAMODO_TSN_TAB_SITES_TITLE'),
];
$aTabs[] = [
	'DIV' => 'viamodo_tn_tab_subscribers',
	'TAB' => Loc::getMessage('VIAMODO_TSN_TAB_SUBSCRIBERS_NAME'),
	'ICON' => '',
	'TITLE' => Loc::getMessage('VIAMODO_TSN_TAB_SUBSCRIBERS_TITLE'),
];
$aTabs[] = [
	'DIV' => 'viamodo_tn_tab_help',
	'TAB' => Loc::getMessage('VIAMODO_TSN_TAB_HELP_NAME'),
	'ICON' => '',
	'TITLE' => Loc::getMessage('VIAMODO_TSN_TAB_HELP_TITLE'),
];

$arAllOptions = [];

/************************ tab ***************************/
$arAllOptions['viamodo_tn_tab_settings'][] = [
	'token',
	Loc::getMessage('VIAMODO_TSN_OPTIONS_TOKEN'),
	null,
	['text'],
];
$arAllOptions['viamodo_tn_tab_settings'][] = [
	'chat_ids',
	Loc::getMessage('VIAMODO_TSN_OPTIONS_CHAT_IDS'),
	null,
	['text'],
	'Y'
];
/************************ tab ***************************/
if (!empty($arSites)) {
	$arAllOptions['viamodo_tn_tab_sites'][] = ['note' => Loc::getMessage('VIAMODO_TSN_TAB_SITES_NOTE')];
	foreach ($arSites as $arSite) {
		$arAllOptions['viamodo_tn_tab_sites'][] = $arSite['NAME'].' ['.$arSite['LID'].']';
		$arAllOptions['viamodo_tn_tab_sites'][] = [
			'new_order_'.$arSite['LID'],
			Loc::getMessage('VIAMODO_TSN_TAB_SITES_BIND_NEW_ORDER'),
			null,
			['checkbox'],
		];
		$arAllOptions['viamodo_tn_tab_sites'][] = [
			'update_order_'.$arSite['LID'],
			Loc::getMessage('VIAMODO_TSN_TAB_SITES_BIND_UPDATE_ORDER'),
			null,
			['checkbox'],
		];
		$arAllOptions['viamodo_tn_tab_sites'][] = [
			'order_paid_'.$arSite['LID'],
			Loc::getMessage('VIAMODO_TSN_TAB_SITES_BIND_ORDER_PAID'),
			null,
			['checkbox'],
		];
	}
}
/************************ tab ***************************/
$arAllOptions['viamodo_tn_tab_help'][] = Loc::getMessage('VIAMODO_TSN_TAB_HELP_GET_TOKEN_TITLE');
$arAllOptions['viamodo_tn_tab_help'][] = [
	'',
	Loc::getMessage('VIAMODO_TSN_TAB_HELP_GET_TOKEN_BODY'),
	['statichtml'],
];
$arAllOptions['viamodo_tn_tab_help'][] = Loc::getMessage('VIAMODO_TSN_TAB_HELP_GET_CHAT_IDS_TITLE');
$arAllOptions['viamodo_tn_tab_help'][] = [
	'',
	Loc::getMessage('VIAMODO_TSN_TAB_HELP_GET_CHAT_IDS_BODY'),
	['statichtml'],
];
/************************ tab ***************************/

if (
	(isset($_REQUEST['save']) || isset($_REQUEST['apply']))
	&& check_bitrix_sessid()
) {
	__AdmSettingsSaveOptions($mid, $arAllOptions['viamodo_tn_tab_settings']);
	__AdmSettingsSaveOptions($mid, $arAllOptions['viamodo_tn_tab_sites']);

    LocalRedirect('settings.php?mid='.$mid.'&lang='.LANG);
}

$tabControl = new \CAdminTabControl('tabControl', $aTabs);

?><form method="post" action="<?=$APPLICATION->GetCurPage()?>?mid=<?=urlencode($mid)?>&amp;lang=<?=LANGUAGE_ID?>"><?
	echo bitrix_sessid_post();

	$tabControl->Begin();

	$tabControl->BeginNextTab();
	__AdmSettingsDrawList($mid, $arAllOptions['viamodo_tn_tab_settings']);

	$tabControl->BeginNextTab();
	__AdmSettingsDrawList($mid, $arAllOptions['viamodo_tn_tab_sites']);

	$tabControl->BeginNextTab();
	$refreshLink = $APPLICATION->GetCurPage();
	$refreshLink.= '?mid='.urlencode($mid).'&amp;lang='.LANGUAGE_ID;
	$refreshLink.= '&amp;update=yes';

	$addChatLink = $APPLICATION->GetCurPage();
	$addChatLink.= '?mid='.urlencode($mid).'&amp;lang='.LANGUAGE_ID;
	$addChatLink.= '&amp;addChat=#CHAT_ID#';
	$addChatLink.= '&amp;username=#USERNAME#';

	$removeChatLink = $APPLICATION->GetCurPage();
	$removeChatLink.= '?mid='.urlencode($mid).'&amp;lang='.LANGUAGE_ID;
	$removeChatLink.= '&amp;removeChat=#CHAT_ID#';
	?>
<td>
<table width="100%"><tr class="heading">
	<td colspan="2"><?=Loc::getMessage('VIAMODO_TSN_TAB_SUBSCRIBERS_TITLE_NEW_SUBSCRIBERS')?></td>
</tr></table>
<div>
	<a href="<?=$refreshLink?>"><?=Loc::getMessage('VIAMODO_TSN_TAB_SUBSCRIBERS_BTN_UPDATE')?></a>
</div>
<?php
$bShowNew = $request->getQuery('update') == 'yes' ? true : false;
if ($bShowNew === true) {
	try {
		$response = Util::getLastUpdates();

		$rows = $response['result'];

		if (empty($rows)) {
			echo Loc::getMessage('VIAMODO_TSN_TAB_SUBSCRIBERS_ERROR_NO_NEW_MESSAGES');
		} else {
?>
<br>
<table class="internal">
	<thead>
		<tr class="heading">
			<td><?=Loc::getMessage('VIAMODO_TSN_TAB_SUBSCRIBERS_TABLE_THEAD_CHAT_ID')?></td>
			<td><?=Loc::getMessage('VIAMODO_TSN_TAB_SUBSCRIBERS_TABLE_THEAD_NAME')?></td>
			<td><?=Loc::getMessage('VIAMODO_TSN_TAB_SUBSCRIBERS_TABLE_THEAD_ADD')?></td>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($rows as $row):
		$username = $row['message']['chat']['first_name'].' '.$row['message']['chat']['last_name'].' ['.$row['message']['chat']['username'].']';
		$link = $addChatLink;
		$link = str_replace('#CHAT_ID#', $row['message']['chat']['id'], $link);
		$link = str_replace('#USERNAME#', $username, $link);
		?>
		<tr>
			<td><?=$row['message']['chat']['id']?></td>
			<td><?=$username?></td>
			<td><a href="<?=$link?>"><?=Loc::getMessage('VIAMODO_TSN_TAB_SUBSCRIBERS_TABLE_TBODY_ADD')?></a></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<?php
		}
	} catch (Exception $e) {
		ShowError('Ошибка: '.$e->getMessage());
	}
} // $bShow === true
?>

<br>
<table width="100%"><tr class="heading">
	<td colspan="2"><?=Loc::getMessage('VIAMODO_TSN_TAB_SUBSCRIBERS_TITLE_CURRENT_SUBSCRIBERS')?></td>
</tr></table>
<?php if (!empty($currentChats) && !empty($arCurrentChats)): ?>
<table class="internal">
	<thead>
		<tr class="heading">
			<td><?=Loc::getMessage('VIAMODO_TSN_TAB_SUBSCRIBERS_TABLE_THEAD_CHAT_ID')?></td>
			<td><?=Loc::getMessage('VIAMODO_TSN_TAB_SUBSCRIBERS_TABLE_THEAD_NAME')?></td>
			<td><?=Loc::getMessage('VIAMODO_TSN_TAB_SUBSCRIBERS_TABLE_THEAD_REMOVE')?></td>
		</tr>
	</thead>
	<tbody>
	<?php foreach ($arCurrentChats as $chatId):
		$userName = Option::get($mid, 'chat_'.$chatId, '');
		$link = $removeChatLink;
		$link = str_replace('#CHAT_ID#', $chatId, $link);
		?>
		<tr>
			<td><?=$chatId?></td>
			<td><?=$userName?></td>
			<td><a href="<?=$link?>"><?=Loc::getMessage('VIAMODO_TSN_TAB_SUBSCRIBERS_TABLE_TBODY_REMOVE')?></a></td>
		</tr>
	<?php endforeach; ?>
	</tbody>
</table>
<?php
else:
	echo Loc::getMessage('VIAMODO_TSN_TAB_SUBSCRIBERS_ERROR_NO_CURRENT_SUBSCRIBERS');
endif;
?>
</td>
<?

	$tabControl->BeginNextTab();
	__AdmSettingsDrawList($mid, $arAllOptions['viamodo_tn_tab_help']);

	$tabControl->Buttons([]);
	$tabControl->End();

?></form>
