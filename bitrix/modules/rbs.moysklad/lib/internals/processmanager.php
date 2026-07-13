<?php
namespace Rbs\Moysklad\Internals;

use Rbs\Moysklad\Config;

use Bitrix\Main\Context;

class ProcessManager
{
	public const PROCESS_BEFORE_START = 'process_before_start';
	public const PROCESS_START = 'process_start';
	public const PROCESS_WORK = 'process_work';
	public const PROCESS_FINISH = 'process_finish';

	private $baseParams = [];
	private $processParams = [];

	private $processName = '';

	public function __construct(string $processName = '', string $processStartPhase = '')
	{
		
		$this->processName = $processName;
		$this->baseParams = [
			'mid' => Config::getModuleId(true),
			'lang' => $this->getRequest()->get('lang'),
			'profile_id' => Config::getProfileId()
		];

		if(empty($processStartPhase)) {
			$processStartPhase = self::PROCESS_START;
		}

		$this->processParams = [
			'process' => 'Y',
			'process_name' => $processName,
			'process_state' => $this->getRequest()->get('process_state') ?: $processStartPhase
		];
	}

	public function getBaseParams()
	{
		return $this->baseParams;
	}

	public function getProcessParams()
	{
		return $this->processParams;
	}

	public function setProcessState(string $processStartPhase = '')
	{
		if (empty($processStartPhase)) {
			$processStartPhase = self::PROCESS_START;
		}
		$this->processParams['process_state'] = $processStartPhase;
	}

	public function switchModuleOff()
	{
		$this->saveStateParam('module_work', Config::getOption('global_enabled', 'N'));
		Config::setOption('global_enabled', 'N');
	}

	public function switchModuleOn()
	{
		Config::setOption('global_enabled', $this->getStateParam('module_work', 'N'));
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
		header("Refresh:1; url=" . $this->getRefreshUrl(false, [
			'tab' => 'log_window'
		]));
	}

	public function nextStep()
	{
		header("Refresh:1; url=" . $this->getRefreshUrl());
	}

	private function getRefreshUrl(bool $withProcess = true, array $customParams  = [])
	{
		return '/bitrix/admin/settings.php?' . http_build_query($this->baseParams + ($withProcess ? $this->processParams : []) + $customParams);
	}
}