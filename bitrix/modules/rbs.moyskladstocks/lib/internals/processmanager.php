<?php
namespace Rbs\MoyskladStocks\Internals;

use Rbs\MoyskladStocks\Config;
use Bitrix\Main\Context;

class ProcessManager
{
	public const PROCESS_START = 'process_start';
	public const PROCESS_WORK = 'process_work';
	public const PROCESS_FINISH = 'process_finish';

	private $baseParams = [];
	private $processParams = [];

	private $processName = '';

	public function __construct(string $processName = '')
	{
		$this->processName = $processName;
		$this->baseParams = [
			'mid' => Config::getModuleId(true),
			'lang' => $this->getRequest()->get('lang'),
			'profile_id' => Config::getProfileId()
		];
		$this->processParams = [
			'process' => 'Y',
			'process_name' => $processName,
			'process_state' => $this->getRequest()->get('process_state') ?: self::PROCESS_START
		];
	}

	public function switchModuleOff()
	{
		$currentState = Config::getOption('global_enabled', 'N');
		$this->saveStateParam('module_work', $currentState);
		if($currentState === 'Y'){
			Config::setOption('last_import_once_step_time', time());
			ModuleWorkSwitcher::delaySetModuleWork();
		}
		ModuleWorkSwitcher::switchModuleWork(false, ModuleWorkSwitcher::REASON_IMPORT_ONCE);
	}

	public function switchModuleOn()
	{
		$moduleWork = $this->getStateParam('module_work', 'N') === 'Y';
		if($moduleWork) {
			ModuleWorkSwitcher::disableDelaySetModuleWork();
		}
		ModuleWorkSwitcher::switchModuleWork($moduleWork, ModuleWorkSwitcher::REASON_IMPORT_ONCE);
	}

	public function saveStateParam(string $paramName = '', string $value = '')
	{
		$this->setConfigValue("state_pr_{$this->processName}_{$paramName}", $value);	
	}

	public function getStateParam(string $paramName = '', string $defaultValue = ''): string
	{
		return (string)$this->getConfigValue("state_pr_{$this->processName}_{$paramName}", $defaultValue);
	}

		private function getConfigValue(string $paramName, $defaultValue)
		{
			return Config::getOption($paramName, $defaultValue);
		}

		private function setConfigValue(string $paramName, $value)
		{
			Config::setOption($paramName, $value);
		}

	public function getRequest()
	{
		return Context::getCurrent()->getRequest();
	}

	public function getProcessState()
	{
		return $this->processParams['process_state'];
	}

	public function setProcessWork()
	{
		$this->processParams['process_state'] = self::PROCESS_WORK;
		$this->nextStep();
	}

	public function setProcessFinish()
	{
		$this->processParams['process_state'] = self::PROCESS_FINISH;
		$this->nextStep();
	}

	public function finishProcess()
	{
		$diagnosticUrl = '/bitrix/admin/rbs.moyskladstocks_diagnostic.php';
		if(Config::getProfileId() > 0) {
			$diagnosticUrl .= '?profile_id=' . Config::getProfileId();
		}
		header("Refresh:1; url=" . $diagnosticUrl);
	}

	public function nextStep()
	{
		Config::setOption('last_import_once_step_time', time());
		header("Refresh:1; url=" . $this->getRefreshUrl());
	}

	private function getRefreshUrl(bool $withProcess = true, array $customParams  = [])
	{
		return '/bitrix/admin/settings.php?' . http_build_query($this->baseParams + ($withProcess ? $this->processParams : []) + $customParams);
	}
}