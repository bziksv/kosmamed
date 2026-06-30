<?php
namespace Rbs\MoyskladStocks\Diagnostic\Monitoring\Agents;

use Rbs\MoyskladStocks\LangMsg;

class Monitoring
{
		
	private static $standartAgentMonitoringList = [

		'product' => [
			'agent_function' => 'import_product',
			'enable_config' => 'im_product_enable',
		],
		'variant' => [
			'agent_function' => 'import_variant',
			'enable_config' => 'im_variant_enable',
		],
		'service' => [
			'agent_function' => 'import_service',
			'enable_config' => 'im_service_enable',
		],
		'bundle' => [
			'agent_function' => 'import_bundle',
			'enable_config' => 'im_bundle_enable',
		],
		'productfolder' => [
			'agent_function' => 'import_productfolder',
			'enable_config' => 'im_productfolder_enable',
		],

		'product_price' => [
			'agent_function' => 'import_prices_product',
			'enable_config' => 'product_prices_sync',
		],
		'variant_price' => [
			'agent_function' => 'import_prices_variant',
			'enable_config' => 'variant_prices_sync',
		],
		'service_price' => [
			'agent_function' => 'import_prices_service',
			'enable_config' => 'service_prices_sync',
		],
		'bundle_price' => [
			'agent_function' => 'import_prices_bundle',
			'enable_config' => 'bundle_prices_sync',
		],

		'discount' => [
			'agent_function' => 'import_discount',
			'enable_config' => 'ds_sync',
		],

	];

	public static function getLangMessages(): array
	{
		return [
			'heading' => [
				'section_title' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_heading_section_title'),
			],
			'filter' => [
				'title' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_filter_title'),
				'active' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_filter_active'),
				'all' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_filter_all'),
				'success' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_filter_success'),
				'warning' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_filter_warning'),
				'error' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_filter_error'),
				'disabled' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_filter_disabled'),
			],
			'other' => [
				'open_logs' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_other_open_logs'),
				'details' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_other_details'),
			],
			'sections' => [
				'base_state' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_sections_base_state'),
				'entity_list' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_sections_entity_list'),
				'agent_params' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_sections_agent_params'),
				'agent_bundle_params' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_sections_agent_bundle_params'),
				'import_params' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_sections_import_params'),
			],
			'main_info' => [
				'parent_process_name' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_main_info_parent_process_name'),
				'parent_process_error' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_main_info_parent_process_error'),
				'parent_process_success' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_main_info_parent_process_success'),
				'process_disabled' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_main_info_process_disabled'),
				'process_error' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_main_info_process_error'),
				'process_success' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_main_info_process_success'),
				'log_success' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_main_info_log_success'),
				'log_error' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_main_info_log_error'),
				'log_warning' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_main_info_log_warning'),
				'entity_list_disabled' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_main_info_entity_list_disabled'),
				'product' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_main_info_product'),
				'variant' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_main_info_variant'),
				'bundle' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_main_info_bundle'),
				'process_bundle_disabled' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_main_info_process_bundle_disabled'),
				'process_bundle_error' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_main_info_process_bundle_error'),
				'process_bundle_success' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_main_info_process_bundle_success'),
			],
			'params' => [
				'enabled_import' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_params_enabled_import'),
				'enabled_agent' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_params_enabled_agent'),
				'agent_execution' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_params_agent_execution'),
				'agent_set' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_params_agent_set'),
				'agent_active' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_params_agent_active'),
				'agent_last_execution' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_params_agent_last_execution'),
				'agent_next_execution' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_params_agent_next_execution'),
				'agent_interval' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_params_agent_interval'),
				'import_limit' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_params_import_limit'),
				'import_offset' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_params_import_offset'),
				'import_only_updated' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_params_import_only_updated'),
				'import_full_once' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_params_import_full_once'),
				'import_full_time' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_params_import_full_time'),
				'import_last_full_update' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_params_import_last_full_update'),
				'import_last_update' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_params_import_last_update'),
				'product' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_params_product'),
				'variant' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_params_variant'),
				'bundle' => LangMsg::get('DIAG_APP_MESSAGE_agmonitor_params_bundle'),
			],
		];
	}

	public static function getData(): array
	{
		$monitoringData = array_merge(
			self::getStockMonitoring(),
			self::getStandartAgentMonitoring(),
		);

		return $monitoringData;
	}

	private static function getStockMonitoring(): array
	{
		$monitoringData = [];

		$importStocksMonitoringFunction = new Functions\ImportStocks(
			'stocks',
			'import_stocks',
			['product', 'variant', 'bundle'],
			'#entity#_stocks_sync'
		);
		$monitoringData[] = $importStocksMonitoringFunction->getFunctionMonitoringData();

		$importBundleStocksMonitoringFunction = new Functions\ImportBundleStocks(
			'bundle_stocks',
			'import_bundle_stocks',
			'bundle_stocks_sync',
			$importStocksMonitoringFunction->hasProcessError(),
			$importStocksMonitoringFunction->getAgentItem()->getAgentLangName()
		);
		$monitoringData[] = $importBundleStocksMonitoringFunction->getFunctionMonitoringData();

		$importCurrentStocksMonitoringFunction = new Functions\ImportCurrentStocks(
			'curr_stocks',
			'import_current_stocks'
		);
		$monitoringData[] = $importCurrentStocksMonitoringFunction->getFunctionMonitoringData();

		$importBundleStocksMonitoringFunction = new Functions\ImportBundleStocks(
			'bundle_stocks_current',
			'import_bundle_current_stocks',
			'',
			$importCurrentStocksMonitoringFunction->hasProcessError(),
			$importCurrentStocksMonitoringFunction->getAgentItem()->getAgentLangName()
		);
		$importBundleStocksMonitoringFunction->setImportEnabledAgentItem($importCurrentStocksMonitoringFunction->isBundleStocksEnabled());
		$monitoringData[] = $importBundleStocksMonitoringFunction->getFunctionMonitoringData();

		return $monitoringData;
	}

	private static function getStandartAgentMonitoring(): array
	{
		$monitoringData = [];

		foreach (self::$standartAgentMonitoringList as $agentName => $agentData) {
			$importEntityMonitoringFunction = new Functions\ImportEntity($agentName, $agentData['agent_function'], $agentData['enable_config']);
			$monitoringData[] = $importEntityMonitoringFunction->getFunctionMonitoringData();
		}

		return $monitoringData;
	}
	
}