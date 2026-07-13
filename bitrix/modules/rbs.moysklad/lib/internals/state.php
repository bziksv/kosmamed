<?php
namespace Rbs\Moysklad\Internals;

class State
{

	protected static $instance;
	protected $state = [];
	private function __construct() {}
	private function __clone() {}
	private function __wakeup() {}

	public static function getInstance()
	{
		if (is_null(self::$instance)) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	public function setState(string $stateVar = '', $value)
	{
		$this->state[$stateVar] = $value;
	}

	public function getState(string $stateVar = '')
	{
		return isset($this->state[$stateVar]) ? $this->state[$stateVar] : null;
	}

	public function unSetState(string $stateVar = '')
	{
		if (isset($this->state[$stateVar])) {
			unset($this->state[$stateVar]);
		}
	}

	public function isHookScript()
	{
		return (bool)$this->getState('isHookScript');
	}

	public function setHookScript()
	{
		$this->setState('isHookScript', true);
	}

	public function unSetHookScript()
	{
		$this->unSetState('isHookScript');
	}
}
