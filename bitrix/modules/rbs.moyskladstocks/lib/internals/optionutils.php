<?php

namespace Rbs\MoyskladStocks\Internals;

use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\LangMsg;
use Rbs\MoyskladStocks\AgentManager;
use Rbs\MoyskladStocks\Agent;
use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Services\TrackingTypeSetter;

use Bitrix\Main\Context;

class OptionUtils
{
	public static function getImportOnceProcessList(): array
	{
		$result = ['import_once_discount', 'import_once_productfolder', 'import_once_stocks', 'import_once_bundle_stocks', 'import_once_update_ext_codes'];
		$entityList = ['product', 'variant', 'service', 'bundle'];
		foreach($entityList as $entity) {
			$result[] = "import_once_{$entity}";
			$result[] = "import_once_{$entity}_price";
		}
		return $result;
	}

	public static function getCustomProcessList(): array
	{
		$result = [];
		$event = new \Bitrix\Main\Event(Config::getModuleId(true), "OnBuildCustomProcessList", []);
		$event->send();

		if ($event->getResults()) {
			foreach ($event->getResults() as $eventResult) {
				if ($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS) {
					$eventParameters = $eventResult->getParameters();
					$eventResult = $eventParameters['result'];
					if (is_array($eventResult)) {
						foreach ($eventResult as $process) {
							if (is_string($process) && preg_match('/^[a-zA-Z_]+$/', $process)) {
								$result[] = $process;
							}
						}
					}
				}
			}
		}
		return $result;
	}
	
	public static function getOptionValueForOptionPage(string $optionName = '', string $defaultValue = '', bool $isSaveHit = false)
	{
		$request = Context::getCurrent()->getRequest();
		return $isSaveHit ? $request->get($optionName) : Config::getOption($optionName, $defaultValue);
	}

	public static function buildImportOnceButton(&$localTab = [], $process = '', $withHeader = true)
	{
		if($withHeader) {
			$localTab[] = LangMsg::get('OPTION_UTILS_IMPORT_ONCE_HEAD');
		} else {
			$localTab[] = [
				"import_once",
				'',
				'',
				['statichtml']
			];
		}
		
		$localTab[] = [
			"import_once", 
			GetMessage(
				'OPTION_UTILS_IMPORT_ONCE_BTN', 
				[
					'#PROCESS#' => $process,
					'#PROFILE_ID#' => Config::getProfileId()
				]
			),
			GetMessage('OPTION_UTILS_IMPORT_ONCE_NOTE'),
			['statichtml']
		];

	}

	public static function canTrackingTypeImport(): bool
	{
		return TrackingTypeSetter::getInstance()->canMarking();
	}

	public static function buildAgentOptionArray(&$localTab = [], $agentId = '', $paramsCheckBox = '', $withHeader = true, array $disabledOptions = [])
	{
		$agentManager = new AgentManager($agentId);
		$entityForLink = $agentId == 'curr_stocks' ? 'current_stocks' : $agentId;

		if ($withHeader) {
			$localTab[] = LangMsg::get('OPTION_UTILS_AGENT_HEAD', ['#NAME#' => $agentManager->getAgentLangName(), '#ENTITY#' => $entityForLink]);
		} else {
			$localTab[] = ["static", LangMsg::get('OPTION_UTILS_AGENT_SUB_HEAD'), '<b>"'. $agentManager->getAgentLangName().'"</b>', ['statichtml']];
		}

		$localTab[] = ["static", LangMsg::get('OPTION_UTILS_AGENT_NODE_START_HTML', ['#ID#' => $agentId]), '', ['statichtml']];

		$defaultLimit = 100;
		if(in_array($agentId, ['stocks'])) {
			$defaultLimit = 500;
		}

		
		$agentOptionList = self::buildAgentOptionParamList([
			'limit' => [
				'type' => 'text',
				'default' => $defaultLimit,
			],
			'offset' => [
				'type' => 'text',
				'default' => $agentManager->getOffset()
			],
			'last_full_update' => [
				'type' => 'text',
				'default' => $agentManager->getConfigValue('last_full_update', '')
			],
			'last_update' => [
				'type' => 'text',
				'default' => $agentManager->getConfigValue('last_update', '')
			],
		]);

		if (Utils::is_count($disabledOptions)){
			foreach($disabledOptions as $optionId){
				if(isset($agentOptionList[$optionId])) {
					unset($agentOptionList[$optionId]);
				}
			}
		}

		foreach($agentOptionList as $optionId => $optionParams) {
			$optionArray = [];
			$configParamId = $agentManager->getConfigParamName($optionId);
			$configParamName = LangMsg::get("OPTION_UTILS_AGENT_{$optionId}_OPTION");
			switch($optionParams['type']) {
				case 'checkbox':
					$optionArray = [$configParamId, $configParamName, $optionParams['default'], ['checkbox', "N", $paramsCheckBox]];
					break;
				case 'selectbox':
					$optionArray = [$configParamId, $configParamName, $optionParams['default'], ['selectbox', $optionParams['list']]];
					break;
				default: //text
					$optionArray = [$configParamId, $configParamName, $optionParams['default'], ['text', 30]];
					break;
			}
			$localTab[] = $optionArray;
		}

		$localTab[] = ["static", LangMsg::get('OPTION_UTILS_AGENT_NODE_FINISH_HTML'), '', ['statichtml']];
	}

	public static function saveAgentAction(string $agentId = '', string $agentFunctionName = '')
	{
		$request = Context::getCurrent()->getRequest();
		Agent::delete($agentFunctionName);
		$agentManager = new AgentManager($agentId);

		$isCronAgent = $request->get($agentManager->getConfigParamName('is_cron')) === 'Y';
		$isEnabled = $request->get($agentManager->getConfigParamName('enabled')) === 'Y';

		if(in_array($agentId, ['product', 'variant', 'service', 'bundle'])) {
			Agent::delete('deactivate_' . $agentId);
			Agent::delete('deactivate_by_filter_' . $agentId);
			Agent::delete('deactivate_by_folder_' . $agentId);
		}
        if (in_array($agentId, ['curr_stocks'])) {
            Agent::delete("import_bundle_current_stocks");
        }

		if ($isEnabled && !$isCronAgent) {
			$interval = (int)$request->get($agentManager->getConfigParamName('interval'));
			if ((int)$interval <= 0) {
				$interval = 120;
			}
			Agent::set($agentFunctionName, $interval);

			if (in_array($agentId, ['product', 'variant', 'service', 'bundle'])) {
				if (!$request->get("im_{$agentId}_p_include_archived") && $request->get("im_{$agentId}_up_archived")) {
					Agent::set("deactivate_{$agentId}", 360);
				}
				if ($request->get("im_{$agentId}_filter_prop") !== 'N' && $request->get("im_{$agentId}_up_active_by_filter")) {
					Agent::set("deactivate_by_filter_{$agentId}", 360);
				}

				$isNeedGroupItem = $request->get("im_{$agentId}_group") !== 'N';
				$isOuterSec = $request->get("im_{$agentId}_up_outer_sec") === 'Y';

				if ($isNeedGroupItem && $isOuterSec) {
					Agent::set("deactivate_by_folder_{$agentId}", 360);
				}
			}

			if (!empty($request->get("curr_stocks_p_entity_type")) && in_array($agentId, ['curr_stocks'])) {
				if (in_array('bundle', $request->get("curr_stocks_p_entity_type"))) {
					Agent::set("import_bundle_current_stocks", 60);
				}
			}

		}
	}

	public static function buildAgentJsControllerImportFunctionState(): array
	{
		$paramsCurrentStocks = Config::getCurrentStocksParams();

		$result = [
			'stocks' => Config::checkFeature('productstocks') || Config::checkFeature('variantstocks') || Config::checkFeature('bundlestocks'),
			'curr_stocks' => Utils::is_count($paramsCurrentStocks['entity_type']),
			'discount' => Config::checkFeature('ds_sync'),
			'bundle_stocks' => Config::checkFeature('bundlestocks'),
			'productfolder' => Config::checkFeature('import_productfolder'),
			'update_ext_codes' => true
		];

		foreach(['product', 'variant', 'service', 'bundle'] as $entity) {
			$result[$entity] = Config::checkFeature('import_' . $entity);
			$result[$entity . '_price'] = Config::checkFeature($entity . 'prices');
		}

		return $result;
	}

	public static function buildAgentJsControllerLangMessages(): array
	{
		$result = [

			'tab_params' => LangMsg::get("AGENT_JS_CONTROLLER_TAB_PARAMS"),
			'tab_info' => LangMsg::get("AGENT_JS_CONTROLLER_TAB_INFO"),
			'tab_once_process' => LangMsg::get("AGENT_JS_CONTROLLER_TAB_ONCE_PROCESS"),

			'note_empty_agent' => LangMsg::get("AGENT_JS_CONTROLLER_NOTE_EMPTY_AGENT"),
			'note_cron_agent' => LangMsg::get("AGENT_JS_CONTROLLER_NOTE_CRON_AGENT"),
			'note_cant_find_agent' => LangMsg::get("AGENT_JS_CONTROLLER_NOTE_CANT_FIND_AGENT"),

			'agent_info' => LangMsg::get("AGENT_JS_CONTROLLER_AGENT_INFO"),
			'agent_info_id' => LangMsg::get("AGENT_JS_CONTROLLER_AGENT_INFO_ID"),
			'agent_info_active' => LangMsg::get("AGENT_JS_CONTROLLER_AGENT_INFO_ACTIVE"),
			'agent_info_last_exec' => LangMsg::get("AGENT_JS_CONTROLLER_AGENT_INFO_LAST_EXEC"),
			'agent_info_next_exec' => LangMsg::get("AGENT_JS_CONTROLLER_AGENT_INFO_NEXT_EXEC"),

			'agent_manager_info' => LangMsg::get("AGENT_JS_CONTROLLER_AGENT_MANAGER_INFO"),
			'agent_manager_info_offset' => LangMsg::get("AGENT_JS_CONTROLLER_AGENT_MANAGER_INFO_OFFSET"),
			'agent_manager_info_last_full_update' => LangMsg::get("AGENT_JS_CONTROLLER_AGENT_MANAGER_INFO_LAST_FULL_UPDATE"),
			'agent_manager_info_last_update' => LangMsg::get("AGENT_JS_CONTROLLER_AGENT_MANAGER_INFO_LAST_UPDATE"),

			'alert_module_work' => LangMsg::get('AGENT_JS_CONTROLLER_ALERT_MODULE_WORK'),
			'alert_import_work' => LangMsg::get('AGENT_JS_CONTROLLER_ALERT_IMPORT_WORK'),
			'alert_stocks_work' => LangMsg::get('AGENT_JS_CONTROLLER_ALERT_STOCKS_WORK'),
			'alert_success_work' => LangMsg::get('AGENT_JS_CONTROLLER_ALERT_SUCCESS_WORK'),

			//'lang_entity_'

			'btn_save' =>
			LangMsg::get("UNIVERSAL_LANG_SAVE"),
			'btn_save_changes' =>
			LangMsg::get("UNIVERSAL_LANG_SAVE_CHANGES"),
			'btn_save_changes_agent' =>
			LangMsg::get("UNIVERSAL_LANG_SAVE_CHANGES_AGENT"),
			'btn_update' =>
			LangMsg::get("UNIVERSAL_LANG_UPDATE"),
		];

		$agentOptionList = self::buildAgentOptionParamList();
		foreach($agentOptionList as $optionId => $params){
			$result[$optionId] = LangMsg::get("OPTION_UTILS_AGENT_{$optionId}_OPTION");
			$result[$optionId . '_hint'] = LangMsg::get("OPTION_UTILS_AGENT_{$optionId}_OPTION_HINT");
		}

		

		return $result;
	}

	public static function buildAgentJsControllerAgentListWithParams(): array
	{
		$result = [];

		$agentIdList = self::getAgentIdList();
		foreach ($agentIdList as $agentId) {
			$result[$agentId] = (new AgentManager($agentId))->getAllParamsAsArray() + self::buildAgentIdInputParams($agentId);
		}

		return $result;
	}

	public static function buildAgentIdInputParams(string $agentId = ''): array
	{
		$result = [];
		switch ($agentId) {
			case 'product':
			case 'bundle':
			case 'service':
			case 'product':
				$result['input_params'] = [
					'limit' => [
						'min' => 100,
						'max' => 1000,
						'step' => 100
					]
				];
				break;
			case 'discount':
				$result['input_params'] = [
					'limit' => [
						'min' => 10,
						'max' => 100,
						'step' => 10
					]
				];
				break;
			default:
				$result['input_params'] = [
					'limit' => [
						'min' => 50,
						'max' => 1000,
						'step' => 50
					]
				];
				break;
		}
		return $result;
	}

	public static function buildAgentJsControllerAgentFunctionStateList(): array
	{
		$result = [];
		$agentIdList = self::getAgentIdList();
		foreach ($agentIdList as $agentId) {
			$result[$agentId] = [
				'ID' => 0
			];
			$agentFunction = isset(Agent::$agentFunctionList[$agentId]) ? Agent::$agentFunctionList[$agentId] : '';
			if(!empty($agentFunction)) {
				$result[$agentId] = Agent::getInfo($agentFunction);
			}
		}
		return $result;
	}

		private static function getAgentIdList(): array
		{
			return [
				'stocks', 'curr_stocks', 'discount',
				'product', 'variant', 'service', 'bundle', 'productfolder',
				'product_price', 'variant_price', 'service_price', 'bundle_price',
				'bundle_stocks', 'update_ext_codes'
			];
		}

		private static function buildAgentOptionParamList(array $replaceParams = []): array
		{
			$agentOptionList = [
				'enabled' => [
					'type' => 'checkbox',
					'default' => 'N'
				],
				'interval' => [
					'type' => 'text',
					'default' => '120'
				],
				'limit' => [
					'type' => 'selectbox',
					'default' => '0',
					'list' => []
				],
				'offset' => [
					'type' => 'text',
					'default' => '0'
				],
				'updated' => [
					'type' => 'checkbox',
					'default' => 'N'
				],
				'full_once' => [
					'type' => 'checkbox',
					'default' => 'N'
				],
				'full_time' => [
					'type' => 'selectbox',
					'default' => '-1',
					'list' => [-1 => LangMsg::get('OPTION_UTILS_NON_FULL_TIME')] + Utils::build_time_interval_array()
				],
				'last_full_update' => [
					'type' => 'text',
					'default' => ''
				],
				'last_update' => [
					'type' => 'text',
					'default' => ''
				],
				'is_cron' => [
					'type' => 'checkbox',
					'default' => 'N'
				],
			];

			if (utils::is_count($replaceParams)) {
				foreach ($replaceParams as $paramId => $params) {
					if (isset($agentOptionList[$paramId])) {
						$agentOptionList[$paramId] = $params;
					}
				}
			}

			return $agentOptionList;
		}

	public static function getStandartFieldListForImportTableParams()
	{
		return [
			'active_by_filter',
			'archived',
			'code',
			'descr',
			'descr_full',
			'folder',
			'img',
			'img_full',
			'img_prop',
			'name',
			'outer_sec',
			'props',
			'ratio',
			'seocache',
			'sizes',
			'sort',
			'uom',
			'update_facet',
			'vat',
			'vat_inc',
			'tracking_type',
			'barcode'
		];
	}

	public static function getHintsForDespiImportFieldSettingsTable()
	{
		$data = self::getStandartFieldListForImportTableParams();

		$result = [];
		foreach($data as $field) {
			$result[$field] = LangMsg::get("OPTION_UTILS_HINT_IMPORT_FIELDS_{$field}");
		}

		return $result;
	}
}