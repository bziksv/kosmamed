<?php
namespace Rbs\MoyskladStocks\Diagnostic\Monitoring\Agents;

use Rbs\MoyskladStocks\Agent;
use Rbs\MoyskladStocks\AgentManager;
use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\Debug;

class Item
{
	private $agentName;
	private $agentFunction;

	private $agentManager;
	private $agentInfo;

	private static $timeIntervalsForFullUpd = [];
	private static $isCronExecutingAgent = null;
	private static $logFileData = [];

	private $isImportEnabled = false;

	public function __construct(string $agentName, string $agentFunction, string $enableConfig = '')
	{
		if (empty(self::$timeIntervalsForFullUpd)) {
			self::$timeIntervalsForFullUpd = Utils::build_time_interval_array();
		}

		if(self::$isCronExecutingAgent === null) {
			self::$isCronExecutingAgent = Config::getLastCronInitDate() > 0;
		}

		if (empty(self::$logFileData)) {
			self::$logFileData = Debug\Reader::parseLogFile(Debug\FileController::getInstance()->getDefaultLogFileName());
		}

		if(!empty($enableConfig)) {
			$this->isImportEnabled = Config::checkFeature($enableConfig);
		}

		$this->agentName = $agentName;
		$this->agentFunction = $agentFunction;

		$this->agentManager = new AgentManager($this->agentName);

		if ($this->agentName === 'productfolder') {
			$this->agentManager->setConfigValue('limit', 1000);
		}

		$agentInfo = Agent::get($this->agentFunction);
		$this->agentInfo = is_array($agentInfo) ? $agentInfo : [];
	}

	public function setIsImportEnabled(bool $isImportEnabled): void
	{
		$this->isImportEnabled = $isImportEnabled;
	}

	public function isCronAgent(): bool
	{
		return $this->agentManager->isEnableAgentForCron();
	}

	public function isCronExecutingScript(): bool
	{
		return self::$isCronExecutingAgent;
	}

	public function isImportEnabled(): bool
	{
		return $this->isImportEnabled;
	}

	public function isAgentEnabledAndExecuting(): bool
	{
		return $this->isAgentEnabled() && ($this->isCronAgent() ? $this->isCronExecutingScript() : $this->isActiveAgentFunction());
	}

	public function isAgentExecuting(): bool
	{
		return $this->isCronAgent() ? $this->isCronExecutingScript() : $this->isActiveAgentFunction();
	}

	public function isAgentEnabled(): bool
	{
		return $this->agentManager->isEnabled();
	}

	public function isSetAgentFunction(): bool
	{
		return isset($this->agentInfo['ID']) && $this->agentInfo['ID'] > 0;
	}

	public function isActiveAgentFunction(): bool
	{
		return isset($this->agentInfo['ACTIVE']) && $this->agentInfo['ACTIVE'] === 'Y';
	}

	public function getAgentFunctionLastExec(): string
	{
		return isset($this->agentInfo['LAST_EXEC']) ? $this->agentInfo['LAST_EXEC'] : '-';
	}

	public function getAgentFunctionNextExec(): string
	{
		return isset($this->agentInfo['NEXT_EXEC']) ? $this->agentInfo['NEXT_EXEC'] : '-';
	}

	public function getAgentFunctionInterval(): int
	{
		return isset($this->agentInfo['AGENT_INTERVAL']) ? $this->agentInfo['AGENT_INTERVAL'] : 0;
	}

	public function getAgentLangName(): string
	{
		return $this->agentManager->getAgentLangName();
	}

	public function getImportLimit(): int
	{
		return (int)$this->agentManager->getConfigValue('limit', 0);
	}

	public function getImportOffset(): int
	{
		return (int)$this->agentManager->getConfigValue('offset', 0);
	}

	public function isImportOnlyUpdated(): bool
	{
		return $this->agentManager->checkFeature('updated');
	}

	public function isImportFullOnce(): bool
	{
		return $this->agentManager->checkFeature('full_once');
	}

	public function getImportLastFullUpdate(): string
	{
		return $this->agentManager->getConfigValue('last_full_update', 0);
	}

	public function getImportLastUpdate(): string
	{
		return $this->agentManager->getConfigValue('last_update', 0);
	}

	public function getImportFullTimeUpdate(): string
	{
		$fullTime = $this->agentManager->getConfigValue('full_time', 0);
		return isset(self::$timeIntervalsForFullUpd[$fullTime]) ? self::$timeIntervalsForFullUpd[$fullTime] : '-';
	}

	public function getLogMonitoringInfo(): array
	{
		$logItems = [];

		$countErrors = 0;
		$countWarnings = 0;

		if(is_array(self::$logFileData)) {
			foreach (self::$logFileData as $logItem) {
				if (mb_strpos($logItem['HEAD'], '<b>' . $this->agentManager->getAgentLangName() . '</b>') !== false) {
				$logItems[] = $logItem;
				if ($logItem['TYPE'] === 'error') {
					$countErrors++;
				}
				if ($logItem['TYPE'] === 'warning') {
						$countWarnings++;
					}
				}
			}
		}

		return [
			'messages' => $logItems,
			'type' => $countErrors > 0 ? 'error' : (($countWarnings > 0 || count($logItems) === 0) ? 'warning' : 'success'),
			'count_errors' => $countErrors,
			'count_warnings' => $countWarnings,
		];
	}
}