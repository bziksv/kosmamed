<?php
namespace Rbs\MoyskladStocks;

use \Rbs\MoyskladStocks\Config;
use \Rbs\MoyskladStocks\LangMsg;

class AgentManager
{
	private $configTag = '';

	private $size = 0;
	private $currentStep = 0;
	private static $configReplacer = '#CONFIG_TAG#';
	private $configPattern = [
		'enabled' 			=> 	'ag_#CONFIG_TAG#_enabled',
		'interval' 			=> 	'ag_#CONFIG_TAG#_interval',
		'limit' 			=> 	'ag_#CONFIG_TAG#_limit',
		'offset' 			=> 	'ag_#CONFIG_TAG#_offset',
		'full_once' 		=> 	'ag_#CONFIG_TAG#_full_once',
		'full_time' 		=> 	'ag_#CONFIG_TAG#_full_time',
		'updated' 			=> 	'ag_#CONFIG_TAG#_updated',
		'last_full_update' 	=> 	'ag_#CONFIG_TAG#_last_full',
		'last_update' 		=> 	'ag_#CONFIG_TAG#_last',
		'is_cron' 			=> 	'ag_#CONFIG_TAG#_is_cron',
		'tag'				=> 	'ag_#CONFIG_TAG#_tag',
		'time_zone_msk' 	=> 	'is_eu_msk_timezone'
	];
	private $maxDiffUpdateHours = 0;
	private $maxDiffUpdateMinutes = 0;

	public function __construct($configTag = '')
	{
		$this->configTag = !empty($configTag) ? (string)$configTag : (string)time();
		$this->currentStep = $this->getOffset();
		$this->setGlobalTimeZone();
	}

	private function setGlobalTimeZone()
	{
		$globalTimeZone = Config::getOption('global_timezone', 'N');
		if ($globalTimeZone !== 'N') {
			date_default_timezone_set($globalTimeZone);
		}
	}

	public function getConfigTag(): string
	{
		return $this->configTag;
	}

	public function getAgentLangName(): string
	{
		return LangMsg::get('AGENT_MANAGER_NAME_' . $this->configTag) ? : $this->configTag;
	}

	public function isEnableAgentForCron(): bool
	{
		return $this->checkFeature('enabled') && $this->checkFeature('is_cron');
	}

	public function isEnabled(): bool
	{
		return $this->checkFeature('enabled');
	}

	public function isCronEnabled(): bool
	{
		return $this->checkFeature('is_cron');
	}

	public function getCurrentStep()
	{
		return ($this->currentStep + $this->getLimit()) > $this->getSize() ? $this->getSize() : $this->currentStep + $this->getLimit();
	}

	public function getAllParamsAsArray()
	{
		$result = [];
		foreach($this->configPattern as $paramCode => $paramTag) {
			$result[$paramCode] = $this->getConfigValue($paramCode, '');
		}
		return $result;
	}

	public function setMaxDiffUpdateHours(int $hours = 0)
	{
		if($hours > 0) {
			$this->maxDiffUpdateHours = $hours; 
		}
	}

	public function setMaxDiffUpdateMinutes(int $minutes = 0)
	{
		if ($minutes > 0) {
			$this->maxDiffUpdateMinutes = $minutes;
		}
	}

	public function setOnlyUpdated()
	{
		$this->setConfigValue('updated', 'Y');
	}

	public function setFullOnce()
	{
		$this->setConfigValue('full_once', 'Y');
	}

	public function setTag(string $tag = '')
	{
		$this->setConfigValue('tag', $tag);
	}

	public function getTag(): string
	{
		return (string)$this->getConfigValue('tag', '');
	}

	public function unSetTag()
	{
		$this->setConfigValue('tag', '');
	}

	public function setOnlyFullUpdate()
	{
		$this->setConfigValue('updated', 'N');
	}

	public function setConfigPattern($configPattern = [])
	{
		foreach($configPattern as $key => $val) {
			if(isset($this->configPattern[$key]) && mb_strpos($val, self::$configReplacer) !== false) {
				$this->configPattern[$key] = $val;
			}
		}
	}

	public function setSize($size)
	{
		$this->size = (int)$size;
	}

	public function getSize(): int
	{
		return $this->size;
	}

	public function getLimit(int $defaultValue = 100): int
	{
		return (int)$this->getConfigValue('limit', $defaultValue);
	}

	public function getOffset(int $defaultValue = 0): int
	{
		return (int)$this->getConfigValue('offset', $defaultValue);
	}

	public function setNextStepOffset()
	{
		$this->setConfigValue('offset', $this->getLimit() + $this->getOffset());
	}

	public function refreshOffset()
	{
		$this->setConfigValue('offset', 0);
	}

	public function isFullUpdate(): bool
	{
		if($this->checkFeature('updated')) {
			if (!$this->checkFeature('full_once') && $this->isFullUpdateByTime()) {
				$this->setConfigValue('full_once', 'Y');
			}
			return $this->checkFeature('full_once');
		}
		return true;
	}

		private function isFullUpdateByTime(): bool
		{
			$currentTime = new \DateTime('now');
			$lastFullUpdateTimeDefault = new \DateTime('now');
			$fullTimeUpd = (int)$this->getConfigValue('full_time', '3');
			if ($fullTimeUpd === (int)$currentTime->format('G')) {
				$lastFullUpdatedByTime = (string)$this->getConfigValue('last_full_update', $lastFullUpdateTimeDefault->modify('-2 days')->format('Y-m-d H'));
				$lastFullDate = substr($lastFullUpdatedByTime, 0, 10);
				$lastFullHour = (int)(substr($lastFullUpdatedByTime, 11) ?: -1);
				return $lastFullDate !== $currentTime->format('Y-m-d') || $lastFullHour !== $fullTimeUpd;
			}
			return false;
		}

	public function getLastDateUpdate(): string
	{
		$lastDateUpdate = $this->getConfigValue('last_update', '');
		if(empty($lastDateUpdate)) {
			$lastDateUpdate = $this->getCurrentDateUpdate();
			$this->setConfigValue('last_update', $lastDateUpdate);
		}

		$maxDiffs = [
			'hours' => $this->maxDiffUpdateHours,
			'minutes' => $this->maxDiffUpdateMinutes
		];

		foreach($maxDiffs as $unit => $maxDiff) {
			if($this->isMaxDiffUpdate($maxDiff, $unit)) {
				$lastDateUpdate = $this->getCurrentDateUpdate("-{$maxDiff} {$unit}");
				$this->setConfigValue('last_update', $lastDateUpdate);
				break;
			}
		}

		return (string)$lastDateUpdate;
	}

		private function isMaxDiffUpdate(int $maxDiff, string $unit): bool
		{
			$lastDateUpdate = $this->getConfigValue('last_update', '');
			if ($maxDiff > 0 && !empty($lastDateUpdate)) {

				$lastDateUpdatePhpObject = new \DateTime($lastDateUpdate, new \DateTimeZone($this->getTimeZone()));
				$currentDatePhpObject = new \DateTime('now', new \DateTimeZone($this->getTimeZone()));
				$interval = $currentDatePhpObject->diff($lastDateUpdatePhpObject);

				if ($unit === 'hours') {
					$diffValue = $interval->h + ($interval->days * 24);
				} elseif ($unit === 'minutes') {
					$diffValue = ($interval->days * 24 * 60) + ($interval->h * 60) + $interval->i;
				} else {
					return false;
				}

				return $diffValue >= $maxDiff;
			}
			return false;
		}

		private function getCurrentDateUpdate(string $modificator = '-5 minutes'): string
		{
			$lastDateUpdate = (new \DateTime('', new \DateTimeZone($this->getTimeZone())))->modify($modificator)->format('Y-m-d H:i:s');
			return $lastDateUpdate;
		}

		private function getTimeZone(): string
		{
			$timeZone = date_default_timezone_get();
			if ($this->checkFeature('time_zone_msk')) {
				$timeZone = 'Europe/Moscow';
			}
			return $timeZone;
		}

	public function setNextStepParams()
	{
		$this->setNextStepOffset();
	}

	public function setFinalStepParams()
	{
		$currentTime = new \DateTime('now', new \DateTimeZone($this->getTimeZone()));

		$this->refreshOffset();

		if($this->checkFeature('full_once')) {
			$this->setConfigValue('full_once', 'N');
			$this->setConfigValue('last_full_update', $currentTime->format('Y-m-d H'));
		}
		
		if($this->size > 0) {
			$this->setConfigValue('last_update', $currentTime->format('Y-m-d H:i:s'));
		}
		
	}

		public function checkFeature(string $paramName): bool
		{
			$configParamName = $this->getConfigParamName($paramName);
			if (!empty($configParamName)) {
				return Config::getOption($configParamName, 'N') === 'Y';
			}
			return false;
		}

		public function getConfigValue(string $paramName, $defaultValue)
		{
			$configParamName = $this->getConfigParamName($paramName);
			if (!empty($configParamName)) {
				return Config::getOption($configParamName, $defaultValue);
			}
			return $defaultValue;
		}

		public function setConfigValue(string $paramName, $value)
		{
			$configParamName = $this->getConfigParamName($paramName);
			if (!empty($configParamName)) {
				Config::setOption($configParamName, $value);
			}
		}

		public function getConfigParamName(string $paramName): string
		{
			$result = '';
			if (isset($this->configPattern[$paramName])) {
				$configParamName = str_replace(self::$configReplacer, $this->configTag, $this->configPattern[$paramName]);
				if (!empty($configParamName)) {
					$result = $configParamName;
				}
			}
			return $result;
		}
}