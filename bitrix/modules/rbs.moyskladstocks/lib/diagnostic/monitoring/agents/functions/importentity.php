<?php

namespace Rbs\MoyskladStocks\Diagnostic\Monitoring\Agents\Functions;

use Rbs\MoyskladStocks\Diagnostic\Monitoring\Item as MonitoringItem;

final class ImportEntity extends BaseFunction
{
	public function getFunctionMonitoringData(): array
	{
		$agentMonitoringItem = $this->getAgentItem();
		$analyzer = $this->getAnalyzer();

		if (!$analyzer->isExecutingProcess()) {
			$this->addBoolMainInfoItem('process_disabled', true, MonitoringItem::STATUS_INFO);
			$this->setProcessDisabled(true);
		}

		if(!$this->isProcessDisabled()) {
			if ($analyzer->hasExecutingErrors()) {
				$this->addBoolMainInfoItem('process_error', true, MonitoringItem::STATUS_ERROR);
			} else {
				$this->addBoolMainInfoItem('process_success', true, MonitoringItem::STATUS_SUCCESS);
			}
			$this->addSectionItem('base_state', 'enabled_import', $agentMonitoringItem->isImportEnabled());
			$this->addSectionItem('base_state', 'enabled_agent', $agentMonitoringItem->isAgentEnabled());
			$this->addSectionItem('base_state', 'agent_execution', $agentMonitoringItem->isAgentExecuting());
		}

		$this->addAgentParamsSectionItems();
		$this->addImportParamsSectionItems();
		if ($agentMonitoringItem->isAgentExecuting()) {
			$this->addLogStatusToMainInfo();
		}

		return [
			'id' => $this->getAgentName(),
			'function' => $this->getAgentFunction(),
			'name' => $this->getAgentItem()->getAgentLangName(),
			'status' => $this->getAgentStatus(),
			'is_executing' => $this->getAnalyzer()->isExecutingProcess(),
			'main_info' => $this->getMainInfo(),
			'sections' => $this->getSections(),
			'logs' => $this->getLogs(),
			'disabled' => $this->isProcessDisabled(),
		];
	}
}