<?php

namespace Rbs\MoyskladStocks\Diagnostic\Monitoring\Agents\Functions;

use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Diagnostic\Monitoring\Item as MonitoringItem;

class ImportStocks extends BaseFunction
{
	private $entityList;
	private $enableConfigPrefix;

	public function __construct(string $agentName, string $agentFunction, array $entityList, string $enableConfigPrefix)
	{
		parent::__construct($agentName, $agentFunction, '');
		$this->entityList = $entityList;
		$this->enableConfigPrefix = $enableConfigPrefix;
	}

	public function getFunctionMonitoringData(): array
	{
		$agentMonitoringItem = $this->getAgentItem();

		$this->addSectionItem('base_state', 'enabled_agent', $agentMonitoringItem->isAgentEnabled());
		$this->addSectionItem('base_state', 'agent_execution', $agentMonitoringItem->isAgentExecuting());

		$entityListEnabled = [];

		foreach ($this->entityList as $entity) {
			$isEnabled = Config::checkFeature(str_replace('#entity#', $entity, $this->enableConfigPrefix));
			if ($isEnabled) {
				$entityListEnabled[$entity] = $isEnabled;
			}
			$this->addSectionItem('entity_list', $entity, $isEnabled);
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
		$this->addImportParamsSectionItems();
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