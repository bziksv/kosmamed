<?php
namespace Rbs\MoyskladStocks\Diagnostic;

use Rbs\MoyskladStocks\LangMsg;
use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Internals\Profiles;

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
		$result = [];
		$profiles = Profiles::getProfiles();
		foreach ($profiles as $profileId => $profileName) {
			$result[] = [
				'id' => $profileId,
				'name' => $profileName,
			];
		}
		return $result;
	}

	private static function getTabs(): array
	{
		return [
			'logs' => [
				'id' => 'logs',
				'label' => LangMsg::get('DIAG_APP_TAB_logs'),
				'sections' => self::getLogsSections(),
			],
			'monitoring' => [
				'id' => 'monitoring',
				'label' => LangMsg::get('DIAG_APP_TAB_monitoring'),
			],
			'checklist' => [
				'id' => 'checklist',
				'label' => LangMsg::get('DIAG_APP_TAB_checklist'),
			],
			'tools' => [
				'id' => 'tools',
				'label' => LangMsg::get('DIAG_APP_TAB_tools'),
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
			'monitoring' => self::getMonitoringMessages(),
			'tools' => self::getToolsMessages(),
			'ui' => self::getUiMessages(),
			'csv_export' => self::getCsvExportMessages(),
			'table' => self::getTableMessages(),
			'pagination' => self::getPaginationMessages(),
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

	private static function getMonitoringMessages(): array
	{
		return [
			'module_on' => LangMsg::get('DIAG_APP_MESSAGE_module_on'),
			'module_off' => LangMsg::get('DIAG_APP_MESSAGE_module_off'),
			'module_on_note' => LangMsg::get('DIAG_APP_MESSAGE_module_on_note'),
			'agents' => Monitoring\Agents\Monitoring::getLangMessages(),
		];
	}

	private static function getToolsMessages(): array
	{
		return [
			'tool_label_list' => [
				'iblock_element_analyzer' => LangMsg::get('DIAG_APP_TOOL_LABEL_IBLOCK_ELEMENT_ANALYZER'),
			],
			'tool_error_list' => [
				'empty_iblock_id_list' => LangMsg::get('DIAG_APP_TOOL_ERROR_EMPTY_IBLOCK_ID_LIST'),
			],
			'iblock_analyzer' => [
				'select_iblock' => LangMsg::get('DIAG_APP_IBLOCK_ANALYZER_SELECT_IBLOCK'),
				'tab_stats' => LangMsg::get('DIAG_APP_IBLOCK_ANALYZER_TAB_STATS'),
				'tab_double_elements' => LangMsg::get('DIAG_APP_IBLOCK_ANALYZER_TAB_DOUBLE_ELEMENTS'),
				'tab_double_sections' => LangMsg::get('DIAG_APP_IBLOCK_ANALYZER_TAB_DOUBLE_SECTIONS'),
				'tab_external_code_match' => LangMsg::get('DIAG_APP_IBLOCK_ANALYZER_TAB_EXTERNAL_CODE_MATCH'),
			],
			'iblock_stats' => [
				'elements' => LangMsg::get('DIAG_APP_IBLOCK_STATS_ELEMENTS'),
				'sections' => LangMsg::get('DIAG_APP_IBLOCK_STATS_SECTIONS'),
				'total' => LangMsg::get('DIAG_APP_IBLOCK_STATS_TOTAL'),
				'active' => LangMsg::get('DIAG_APP_IBLOCK_STATS_ACTIVE'),
				'inactive' => LangMsg::get('DIAG_APP_IBLOCK_STATS_INACTIVE'),
				'duplicates_by_external_code' => LangMsg::get('DIAG_APP_IBLOCK_STATS_DUPLICATES_BY_EXTERNAL_CODE'),
				'tooltip_duplicate_elements' => LangMsg::get('DIAG_APP_IBLOCK_STATS_TOOLTIP_DUPLICATE_ELEMENTS'),
				'tooltip_duplicate_sections' => LangMsg::get('DIAG_APP_IBLOCK_STATS_TOOLTIP_DUPLICATE_SECTIONS'),
			],
			'double_table' => [
				'error_loading' => LangMsg::get('DIAG_APP_DOUBLE_TABLE_ERROR_LOADING'),
				'starting_export' => LangMsg::get('DIAG_APP_DOUBLE_TABLE_STARTING_EXPORT'),
				'export_success' => LangMsg::get('DIAG_APP_DOUBLE_TABLE_EXPORT_SUCCESS'),
				'no_data_export' => LangMsg::get('DIAG_APP_DOUBLE_TABLE_NO_DATA_EXPORT'),
				'error_export' => LangMsg::get('DIAG_APP_DOUBLE_TABLE_ERROR_EXPORT'),
				'export_error_status' => LangMsg::get('DIAG_APP_DOUBLE_TABLE_EXPORT_ERROR_STATUS'),
				'tooltip_grouping' => LangMsg::get('DIAG_APP_DOUBLE_TABLE_TOOLTIP_GROUPING'),
				'group_by_external_code' => LangMsg::get('DIAG_APP_DOUBLE_TABLE_GROUP_BY_EXTERNAL_CODE'),
				'duplicates_found' => LangMsg::get('DIAG_APP_DOUBLE_TABLE_DUPLICATES_FOUND'),
				'exporting' => LangMsg::get('DIAG_APP_DOUBLE_TABLE_EXPORTING'),
				'export_to_csv' => LangMsg::get('DIAG_APP_DOUBLE_TABLE_EXPORT_TO_CSV'),
				'no_data' => LangMsg::get('DIAG_APP_DOUBLE_TABLE_NO_DATA'),
				'modal_title' => LangMsg::get('DIAG_APP_DOUBLE_TABLE_MODAL_TITLE'),
				'close' => LangMsg::get('DIAG_APP_DOUBLE_TABLE_CLOSE'),
				'active_yes' => LangMsg::get('DIAG_APP_DOUBLE_TABLE_ACTIVE_YES'),
				'active_no' => LangMsg::get('DIAG_APP_DOUBLE_TABLE_ACTIVE_NO'),
			],
			'external_code_table' => [
				'header_ms_external_code' => LangMsg::get('DIAG_APP_EXTERNAL_CODE_TABLE_HEADER_MS_EXTERNAL_CODE'),
				'header_ms_name' => LangMsg::get('DIAG_APP_EXTERNAL_CODE_TABLE_HEADER_MS_NAME'),
				'header_bx_id' => LangMsg::get('DIAG_APP_EXTERNAL_CODE_TABLE_HEADER_BX_ID'),
				'header_bx_name' => LangMsg::get('DIAG_APP_EXTERNAL_CODE_TABLE_HEADER_BX_NAME'),
				'header_bx_active' => LangMsg::get('DIAG_APP_EXTERNAL_CODE_TABLE_HEADER_BX_ACTIVE'),
				'header_bx_iblock_id' => LangMsg::get('DIAG_APP_EXTERNAL_CODE_TABLE_HEADER_BX_IBLOCK_ID'),
				'header_bx_doubles_count' => LangMsg::get('DIAG_APP_EXTERNAL_CODE_TABLE_HEADER_BX_DOUBLES_COUNT'),
				'error_loading' => LangMsg::get('DIAG_APP_EXTERNAL_CODE_TABLE_ERROR_LOADING'),
				'starting_export' => LangMsg::get('DIAG_APP_EXTERNAL_CODE_TABLE_STARTING_EXPORT'),
				'export_success' => LangMsg::get('DIAG_APP_EXTERNAL_CODE_TABLE_EXPORT_SUCCESS'),
				'no_mismatches_export' => LangMsg::get('DIAG_APP_EXTERNAL_CODE_TABLE_NO_MISMATCHES_EXPORT'),
				'error_export' => LangMsg::get('DIAG_APP_EXTERNAL_CODE_TABLE_ERROR_EXPORT'),
				'export_error_status' => LangMsg::get('DIAG_APP_EXTERNAL_CODE_TABLE_EXPORT_ERROR_STATUS'),
				'tooltip_description' => LangMsg::get('DIAG_APP_EXTERNAL_CODE_TABLE_TOOLTIP_DESCRIPTION'),
				'only_mismatches' => LangMsg::get('DIAG_APP_EXTERNAL_CODE_TABLE_ONLY_MISMATCHES'),
				'mismatches_found' => LangMsg::get('DIAG_APP_EXTERNAL_CODE_TABLE_MISMATCHES_FOUND'),
				'duplicates_found' => LangMsg::get('DIAG_APP_EXTERNAL_CODE_TABLE_DUPLICATES_FOUND'),
				'exporting' => LangMsg::get('DIAG_APP_EXTERNAL_CODE_TABLE_EXPORTING'),
				'export_to_csv' => LangMsg::get('DIAG_APP_EXTERNAL_CODE_TABLE_EXPORT_TO_CSV'),
				'active_yes' => LangMsg::get('DIAG_APP_EXTERNAL_CODE_TABLE_ACTIVE_YES'),
				'active_no' => LangMsg::get('DIAG_APP_EXTERNAL_CODE_TABLE_ACTIVE_NO'),
				'no_mismatches' => LangMsg::get('DIAG_APP_EXTERNAL_CODE_TABLE_NO_MISMATCHES'),
				'no_data' => LangMsg::get('DIAG_APP_EXTERNAL_CODE_TABLE_NO_DATA'),
				'modal_title' => LangMsg::get('DIAG_APP_EXTERNAL_CODE_TABLE_MODAL_TITLE'),
				'close' => LangMsg::get('DIAG_APP_EXTERNAL_CODE_TABLE_CLOSE'),
			],
			'meta_info' => [
				'error_loading' => LangMsg::get('DIAG_APP_META_INFO_ERROR_LOADING'),
			],
		];
	}

	private static function getCsvExportMessages(): array
	{
		return [
			'preparing' => LangMsg::get('DIAG_APP_CSV_EXPORT_PREPARING'),
			'failed_to_get_data' => LangMsg::get('DIAG_APP_CSV_EXPORT_FAILED_TO_GET_DATA'),
			'loaded_of' => LangMsg::get('DIAG_APP_CSV_EXPORT_LOADED_OF'),
			'error_loading' => LangMsg::get('DIAG_APP_CSV_EXPORT_ERROR_LOADING'),
			'already_exporting' => LangMsg::get('DIAG_APP_CSV_EXPORT_ALREADY_EXPORTING'),
			'starting' => LangMsg::get('DIAG_APP_CSV_EXPORT_STARTING'),
			'no_data' => LangMsg::get('DIAG_APP_CSV_EXPORT_NO_DATA'),
			'processing' => LangMsg::get('DIAG_APP_CSV_EXPORT_PROCESSING'),
			'no_data_after_filter' => LangMsg::get('DIAG_APP_CSV_EXPORT_NO_DATA_AFTER_FILTER'),
			'generating_csv' => LangMsg::get('DIAG_APP_CSV_EXPORT_GENERATING_CSV'),
			'success' => LangMsg::get('DIAG_APP_CSV_EXPORT_SUCCESS'),
			'error' => LangMsg::get('DIAG_APP_CSV_EXPORT_ERROR'),
		];
	}

	private static function getTableMessages(): array
	{
		return [
			'no_data' => LangMsg::get('DIAG_APP_TABLE_NO_DATA'),
		];
	}

	private static function getPaginationMessages(): array
	{
		return [
			'showing' => LangMsg::get('DIAG_APP_PAGINATION_SHOWING'),
			'prev' => LangMsg::get('DIAG_APP_PAGINATION_PREV'),
			'next' => LangMsg::get('DIAG_APP_PAGINATION_NEXT'),
			'per_page' => LangMsg::get('DIAG_APP_PAGINATION_PER_PAGE'),
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