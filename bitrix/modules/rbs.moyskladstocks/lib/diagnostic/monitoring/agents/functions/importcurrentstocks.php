<?php

namespace Rbs\MoyskladStocks\Diagnostic\Monitoring\Agents\Functions;

use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Diagnostic\Monitoring\Item as MonitoringItem;

class ImportCurrentStocks extends BaseFunction
{
	private $isBundleStocksEnabled = false;
	
	public function isBundleStocksEnabled(): bool
	{
		return $this->isBundleStocksEnabled;
	}

	public function getFunctionMonitoringData(): array
	{
		$agentMonitoringItem = $this->getAgentItem();

		$this->addSectionItem('base_state', 'enabled_agent', $agentMonitoringItem->isAgentEnabled());
		$this->addSectionItem('base_state', 'agent_execution', $agentMonitoringItem->isAgentExecuting());

		$currentStocksParams = Config::getCurrentStocksParams();
		
		$entityListEnabled = [];
		foreach ($currentStocksParams['entity_type'] as $entity) {
			$sections['entity_list'][$entity] = true;
			$entityListEnabled[$entity] = true;
			if ($entity === 'bundle') {
				$this->isBundleStocksEnabled = true;
			}
		}

		if (count($entityListEnabled) === 0 && !$agentMonitoringItem->isAgentEnabledAndExecuting()) {
			$this->addBoolMainInfoItem('entity_list_disabled', true, MonitoringItem::STATUS_WARNING);
			$this->setProcessDisabled(true);
		}

		if(!$this->isProcessDisabled()) {

			if (
				(count($entityListEnabled) === 0 && $agentMonitoringItem->isAgentEnabledAndExecuting()) ||
				(count($entityListEnabled) > 0 && !$agentMonitoringItem->isAgentEnabledAndExecuting())
			) {
				$this->addBoolMainInfoItem('process_error', true, MonitoringItem::STATUS_ERROR);
			}

			if (count($entityListEnabled) > 0 && $agentMonitoringItem->isAgentEnabledAndExecuting()) {
				$this->addBoolMainInfoItem('process_success', true, MonitoringItem::STATUS_SUCCESS);
			}

			if (count($entityListEnabled) > 0) {
				foreach ($entityListEnabled as $entity => $enabled) {
					$this->addBoolMainInfoItem($entity, $enabled, $this->hasProcessError() ? MonitoringItem::STATUS_WARNING : MonitoringItem::STATUS_SUCCESS);
				}
			}

		}

		$this->addAgentParamsSectionItems();
		$this->addImportParamsSectionItems(false);
		if ($agentMonitoringItem->isAgentExecuting()) {
			$this->addLogStatusToMainInfo();
		}

		return [
			'id' => $this->getAgentName(),
			'function' => $this->getAgentFunction(),
			'name' => $this->getAgentItem()->getAgentLangName(),
			'status' => self::getAgentStatus(),
			'is_executing' => $agentMonitoringItem->isAgentEnabledAndExecuting(),
			'main_info' => $this->getMainInfo(),
			'sections' => $this->getSections(),
			'logs' => $this->getLogs(),
			'disabled' => $this->isProcessDisabled(),
		];
	}
}