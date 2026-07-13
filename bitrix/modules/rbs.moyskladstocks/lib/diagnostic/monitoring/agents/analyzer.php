<?php

namespace Rbs\MoyskladStocks\Diagnostic\Monitoring\Agents;

use Rbs\MoyskladStocks\Diagnostic\Monitoring\Agents\Item as AgentItem;

class Analyzer	
{
	private $agent;

	public function __construct(AgentItem $agentMonitoringItem) {
		$this->agent = $agentMonitoringItem;
	}

	public function isExecutingProcess(): bool {
		$importEnabled = $this->agent->isImportEnabled();
		$agentEnabled = $this->agent->isAgentEnabled();
		if ($this->agent->isCronAgent()) {
			$isExecuting = $this->agent->isCronExecutingScript();
			return $importEnabled || $agentEnabled || $isExecuting;
		} else {
			$isActiveFunction = $this->agent->isActiveAgentFunction();
			return $importEnabled || $agentEnabled || $isActiveFunction;
		}
	}

	public function isBitrixAgentExecuting(): bool {
		$importEnabled = $this->agent->isImportEnabled();
		$agentEnabled = $this->agent->isAgentEnabled();
		$isActiveFunction = $this->agent->isActiveAgentFunction();
		
		return $importEnabled && $agentEnabled && $isActiveFunction;
	}

	public function hasExecutingErrors(): bool {
		$importEnabled = $this->agent->isImportEnabled();
		$agentEnabled = $this->agent->isAgentEnabled();
		
		if ($this->agent->isCronAgent()) {

			$isExecuting = $this->agent->isCronExecutingScript();
			
			if ($importEnabled && $agentEnabled && $isExecuting) {
				return false;
			}
			if (!$importEnabled && !$agentEnabled) {
				return false;
			}
			
			return true;

		} else {

			$isActiveFunction = $this->agent->isActiveAgentFunction();
			
			if ($this->isBitrixAgentExecuting()) {
				return false;
			}

			if (!$importEnabled && !$agentEnabled && !$isActiveFunction) {
				return false;
			}
			
			return true;
		}
	}
}