<?php

namespace Rbs\MoyskladStocks\Diagnostic\Monitoring\Agents\Functions;

use Rbs\MoyskladStocks\Diagnostic\Monitoring\Item as MonitoringItem;

class ImportBundleStocks extends BaseFunction
{
	private $hasParentProcessError;
	private $parentProcessName;

	public function __construct(string $agentName, string $agentFunction, string $enableConfig, bool $hasParentProcessError = false, string $parentProcessName = '')
	{
		parent::__construct($agentName, $agentFunction, $enableConfig);
		$this->hasParentProcessError = $hasParentProcessError;
		$this->parentProcessName = $parentProcessName;
	}

	public function getFunctionMonitoringData(): array
	{

		$agentMonitoringItem = $this->getAgentItem();

		if (!$agentMonitoringItem->isAgentExecuting() && !$agentMonitoringItem->isImportEnabled()) {
			$this->addBoolMainInfoItem('process_disabled', true, MonitoringItem::STATUS_INFO);
			$this->setProcessDisabled(true);
		}

		if(!$this->isProcessDisabled()) {

			$this->addStringMainInfoItem('parent_process_name', $this->parentProcessName, MonitoringItem::STATUS_INFO);
			if ($this->hasParentProcessError) {
				$this->addBoolMainInfoItem('parent_process_error', true, MonitoringItem::STATUS_ERROR);
			} else {
				$this->addBoolMainInfoItem('parent_process_success', true, MonitoringItem::STATUS_SUCCESS);
			}

			if (
				(!$agentMonitoringItem->isAgentExecuting() && $agentMonitoringItem->isImportEnabled()) ||
				($agentMonitoringItem->isAgentExecuting() && !$agentMonitoringItem->isImportEnabled())
			) {
				$this->addBoolMainInfoItem('process_error', true, MonitoringItem::STATUS_ERROR);
			}

			if ($agentMonitoringItem->isAgentExecuting() && $agentMonitoringItem->isImportEnabled()) {
				$this->addBoolMainInfoItem('process_success', true, MonitoringItem::STATUS_SUCCESS);
			}

			$this->addSectionItem('base_state', 'enabled_import', $agentMonitoringItem->isImportEnabled());
			$this->addSectionItem('base_state', 'agent_execution', $agentMonitoringItem->isAgentExecuting());

		}

		$this->addAgentParamsSectionItems();
		$this->addImportParamsSectionItems();
		if($agentMonitoringItem->isAgentExecuting()) {
			$this->addLogStatusToMainInfo();
		}

		return [
			'id' => $this->getAgentName(),
			'function' => $this->getAgentFunction(),
			'name' => $this->getAgentItem()->getAgentLangName(),
			'status' => self::getAgentStatus(),
			'sections' => $this->getSections(),
			'main_info' => $this->getMainInfo(),
			'logs' => $this->getLogs(),
			'disabled' => $this->isProcessDisabled(),
		];
	}
}