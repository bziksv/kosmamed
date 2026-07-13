<?php

namespace Rbs\MoyskladStocks\Diagnostic\Monitoring\Agents\Functions;

use Rbs\MoyskladStocks\Diagnostic\Monitoring\Agents\Item as AgentItem;
use Rbs\MoyskladStocks\Diagnostic\Monitoring\Agents\Analyzer as Analyzer;
use Rbs\MoyskladStocks\Diagnostic\Monitoring\Item as MonitoringItem;

abstract class BaseFunction
{
	protected $agentName;
	protected $agentFunction;

	protected $agentItem;

	protected $mainInfo;
	protected $sections;
	protected $logs;

	public function __construct(string $agentName, string $agentFunction, string $enableConfig = '')
	{
		$this->agentName = $agentName;
		$this->agentFunction = $agentFunction;

		$this->agentItem = new AgentItem($this->agentName, $this->agentFunction, $enableConfig);

		$this->mainInfo = [];
		$this->sections = [];
		$this->logs = $this->agentItem->getLogMonitoringInfo();
	}

	public function getAgentItem(): AgentItem
	{
		return $this->agentItem;
	}

	public function setImportEnabledAgentItem(bool $isImportEnabled): void
	{
		$this->agentItem->setIsImportEnabled($isImportEnabled);
	}

	public function getAnalyzer(): Analyzer
	{
		return new Analyzer($this->agentItem);
	}

	public function getAgentName(): string
	{
		return $this->agentName;
	}

	public function getAgentFunction(): string
	{
		return $this->agentFunction;
	}

	public function getMainInfo(): array
	{
		return $this->mainInfo;
	}

	public function getLogs(): array
	{
		return $this->logs;
	}

	public function addMainInfoItem(array $mainInfoItem): void
	{
		$this->mainInfo[] = $mainInfoItem;
	}

	private $processError = false;

	public function hasProcessError(): bool
	{
		return $this->processError;
	}

	private $processDisabled = false;

	public function isProcessDisabled(): bool
	{
		return $this->processDisabled;
	}

	public function setProcessDisabled(bool $processDisabled): void
	{
		$this->processDisabled = $processDisabled;
	}

	public function addBoolMainInfoItem(string $itemName, bool $itemValue, string $itemStatus): void
	{
		if($itemStatus === MonitoringItem::STATUS_ERROR) {
			$this->processError = true;
		}
		$this->addMainInfoItem(MonitoringItem::createBoolItem($itemName, $itemValue, $itemStatus)->toArray());
	}

	public function addStringMainInfoItem(string $itemName, string $itemValue, string $itemStatus): void
	{
		$this->addMainInfoItem(MonitoringItem::createStringItem($itemName, $itemValue, $itemStatus)->toArray());
	}

	public function addSectionItem(string $sectionName, string $itemName, $itemValue): void
	{
		$this->sections[$sectionName][$itemName] = $itemValue;
	}

	public function addAgentParamsSectionItems(): void
	{
		$agentMonitoringItem = $this->getAgentItem();
		if (!$agentMonitoringItem->isCronAgent() && $agentMonitoringItem->isSetAgentFunction()) {
			$this->addSectionItem('agent_params', 'agent_set', $agentMonitoringItem->isSetAgentFunction());
			$this->addSectionItem('agent_params', 'agent_active', $agentMonitoringItem->isActiveAgentFunction());
			$this->addSectionItem('agent_params', 'agent_last_execution', $agentMonitoringItem->getAgentFunctionLastExec());
			$this->addSectionItem('agent_params', 'agent_next_execution', $agentMonitoringItem->getAgentFunctionNextExec());
			$this->addSectionItem('agent_params', 'agent_interval', $agentMonitoringItem->getAgentFunctionInterval());
		}
	}

	public function addImportParamsSectionItems(bool $withLimitOffset = true): void
	{
		$agentMonitoringItem = $this->getAgentItem();
		if ($agentMonitoringItem->isAgentEnabled()) {
			
			if ($withLimitOffset) {
				$this->addSectionItem('import_params', 'import_limit', $agentMonitoringItem->getImportLimit());
				$this->addSectionItem('import_params', 'import_offset', $agentMonitoringItem->getImportOffset());
			}

			if ($agentMonitoringItem->isImportOnlyUpdated()) {
				$this->addSectionItem('import_params', 'import_only_updated', $agentMonitoringItem->isImportOnlyUpdated());
				if ($agentMonitoringItem->isImportFullOnce()) {
					$this->addSectionItem('import_params', 'import_full_once', $agentMonitoringItem->isImportFullOnce());
				}
				$this->addSectionItem('import_params', 'import_full_time', $agentMonitoringItem->getImportLastFullUpdate());
				$this->addSectionItem('import_params', 'import_last_full_update', $agentMonitoringItem->getImportLastFullUpdate());
				$this->addSectionItem('import_params', 'import_last_update', $agentMonitoringItem->getImportLastUpdate());
				$this->addSectionItem('import_params', 'import_full_time', $agentMonitoringItem->getImportFullTimeUpdate());
			}
		}
	}

	protected function getSections(): array
	{
		$sectionValuesArray = [];

		if(count($this->sections) > 0) {
			foreach ($this->sections as $sectionName => $sectionValues) {
				$sectionValuesArray[$sectionName] = [
					'id' => $sectionName,
					'items' => [],
				];
				foreach ($sectionValues as $itemName => $itemValue) {
					if (gettype($itemValue) === 'boolean') {
						$sectionValuesArray[$sectionName]['items'][] = MonitoringItem::createBoolItem($itemName, $itemValue)->toArray();
					} else {
						$sectionValuesArray[$sectionName]['items'][] = MonitoringItem::createStringItem($itemName, $itemValue)->toArray();
					}
				}
			}
		}

		return array_values($sectionValuesArray);
	}

	protected function addLogStatusToMainInfo(): void
	{
		$logMonitoringInfo = $this->logs;

		$logStatusItem = MonitoringItem::STATUS_SUCCESS;

		if ($logMonitoringInfo['count_warnings'] > 0) {
			$logStatusItem = MonitoringItem::STATUS_WARNING;
		}

		if ($logMonitoringInfo['count_errors'] > 0) {
			$logStatusItem = MonitoringItem::STATUS_ERROR;
		}

		$this->addBoolMainInfoItem('log_'.$logStatusItem, true, $logStatusItem);
	}

	protected function getAgentStatus(): string
	{
		if($this->isProcessDisabled()) {
			return 'disabled';
		}

		$agentStatus = 'success';

		if(count($this->mainInfo) > 0) {
			foreach ($this->mainInfo as $item) {
				if ($item['status'] === MonitoringItem::STATUS_WARNING) {
					$agentStatus = 'warning';
					break;
				}
				if ($item['status'] === MonitoringItem::STATUS_ERROR) {
					$agentStatus = 'error';
					break;
				}
			}
		}

		return $agentStatus;
	}

	abstract public function getFunctionMonitoringData(): array;

}