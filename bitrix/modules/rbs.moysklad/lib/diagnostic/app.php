<?php
namespace Rbs\Moysklad\Diagnostic;

use Rbs\Moysklad\LangMsg;
use Rbs\Moysklad\Config;
use Rbs\Moysklad\Internals\Profiles;

class App
{
	public static function getAppState()
	{
		$result = [
			'config' => [
				'global_enabled' => Config::getOption('global_enabled', 'N'),
			],
			'doclink' => [
				'label' => LangMsg::get('DIAG_APP_DOCLINK_TEXT'),
				'href' => LangMsg::get('DIAG_APP_DOCLINK_VALUE'),
			],
		];

		$result['profiles'] = self::getProfiles();
		$result['tabs'] = self::getTabs();
		$result['messages'] = self::getMessages();
		
		return $result;
	}

	private static function getProfiles(): array
	{
		/* $result = [];
		$profiles = Profiles::getProfiles();
		foreach ($profiles as $profileId => $profileName) {
			$result[] = [
				'id' => $profileId,
				'name' => $profileName,
			];
		}
		return $result; */
		return [];
	}

	private static function getTabs(): array
	{
		return [
			'logs' => [
				'id' => 'logs',
				'label' => LangMsg::get('DIAG_APP_TAB_logs'),
				'sections' => self::getLogsSections(),
			],
			'checklist' => [
				'id' => 'checklist',
				'label' => LangMsg::get('DIAG_APP_TAB_checklist'),
			],
		];
	}

	private static function getLogsSections(): array
	{
		return [
			[
				'id' => 'module',
				'label' => LangMsg::get('DIAG_APP_SUB_TAB_module'),
			],
			[
				'id' => 'bitrix',
				'label' => LangMsg::get('DIAG_APP_SUB_TAB_bitrix'),
			],
		];
	}

	private static function getMessages(): array
	{
		return [
			'module_logs' => self::getModuleLogsMessages(),
			'bitrix_logs' => self::getBitrixLogsMessages(),
			'ui' => self::getUiMessages(),
		];
	}

	private static function getModuleLogsMessages(): array
	{
		return [
			'currentLog' => LangMsg::get('DIAG_APP_MESSAGE_currentLog'),
			'collapseAll' => LangMsg::get('DIAG_APP_MESSAGE_collapseAll'),
			'expandAll' => LangMsg::get('DIAG_APP_MESSAGE_expandAll'),
			'update' => LangMsg::get('DIAG_APP_MESSAGE_update'),
			'clear' => LangMsg::get('DIAG_APP_MESSAGE_clear'),
			'search' => LangMsg::get('DIAG_APP_MESSAGE_search'),
			'error' => LangMsg::get('DIAG_APP_MESSAGE_error'),
			'warning' => LangMsg::get('DIAG_APP_MESSAGE_warning'),
			'success' => LangMsg::get('DIAG_APP_MESSAGE_success'),
			'noLogs' => LangMsg::get('DIAG_APP_MESSAGE_noLogs'),
			'noFilteredLogs' => LangMsg::get('DIAG_APP_MESSAGE_noFilteredLogs'),
		];
	}

	private static function getBitrixLogsMessages(): array
	{
		return [
			'logs_description' => LangMsg::get('DIAG_APP_MESSAGE_logs_description'),
			'no_module_errors' => LangMsg::get('DIAG_APP_MESSAGE_no_module_errors'),
			'has_module_errors' => LangMsg::get('DIAG_APP_MESSAGE_has_module_errors'),
		];
	}

	private static function getUiMessages(): array
	{
		return [
			'more' => LangMsg::get('DIAG_APP_MESSAGE_more'),
			'close' => LangMsg::get('DIAG_APP_MESSAGE_close'),
		];
	}
}