<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

use \Rbs\MoyskladStocks\Config;
use \Rbs\MoyskladStocks\AgentManager;
use \Rbs\MoyskladStocks\Agent;

$arAllOptions['main'][] = GetMessage('MAIN_SETTINGS_HL_TABLES', ['#LINK#' => '/rbs-moyskladstocks/settings/main/hl-cache']);

$hlTableList = ['Stocks', 'CurrentStocks', 'ExtCodes', 'PFolder'];

foreach($hlTableList as $tableType) {
	$tableFullClass = "\\Rbs\\MoyskladStocks\\HlCache\\{$tableType}";
	if (!$tableFullClass::isExsist()) {
		$entityId = $tableFullClass::createTable();
		if($entityId <= 0) {
			$arAllOptions['main'][] = ['note' => GetMessage("HL_CACHE_TABLE_CREATE_ERROR_NOTE", ['#TYPE#' => GetMessage('HL_CACHE_TABLE_NAME_' . $tableType)])];
		} else {
			if($tableType === 'ExtCodes') {
				(new AgentManager('update_ext_codes'))->setConfigValue('enabled', 'Y');
				Agent::set('update_ext_codes');
			}
		}
	}
	if ($table = $tableFullClass::getTableInfo()) {

		$messId = 'HL_CACHE_TABLE_NOTE';
		$paramMessId = [
			'#TYPE#' => GetMessage('HL_CACHE_TABLE_NAME_' . $tableType),
			'#NAME#' => $table['NAME'],
			'#ID#' => $table['ID'],
		];
		switch($tableType){
			case 'ExtCodes':
				$messId = 'HL_CACHE_TABLE_NOTE_LINK';
				$paramMessId['#LINK#'] = '/bitrix/admin/settings.php?lang=ru&mid=rbs.moyskladstocks&process=Y&process_name=import_once_update_ext_codes';
				if(Config::getProfileId() > 0) {
					$paramMessId['#LINK#'] = '/bitrix/admin/settings.php?lang=ru&mid=rbs.moyskladstocks&process=Y&process_name=import_once_update_ext_codes&profile_id=' . Config::getProfileId();
				}
				break;
		}

		$arAllOptions['main'][] = ['note' => GetMessage($messId, $paramMessId)];
	}
}