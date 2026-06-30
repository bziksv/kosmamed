<?php
namespace Rbs\MoyskladStocks\Debug;

class Counter
{
	private $name = 'counter';

	private $errors = 0;
	private $count = 0;
	private $add = 0;
	private $update = 0;
	private $delete = 0;
	
	private $loger = null;

	function __construct($counterName = '')
	{
		if(!empty($counterName)){
			$this->name = $counterName;
		} else {
			$this->name = 'counter_' . time();
		}
		
		$this->loger = new Loger();
	}

	public function getReport(): array
	{
		return [
			'name' => $this->name,
			'count' => $this->count,
			'add' => $this->add,
			'update' => $this->update,
			'delete' => $this->delete,
			'errors' => $this->errors,
		];
	}

	public function hasErrors()
	{
		return $this->errors > 0;
	}

	public function getErrorMessageArray(): array
	{
		return $this->loger->getErrorMessageArray();
	}

	public function error($errorMsg = '')
	{
		if(!empty($errorMsg)){
			$this->loger->addMessage($errorMsg, Message::TYPE_ERROR);
		}
		$this->errors++;
	}

	public function set_count($cnt = 0)
	{
		$this->count = is_numeric($cnt) ? $cnt : -1;
	}

	public function count()
	{
		$this->count++;
	}

	public function add()
	{
		$this->add++;
	}

	public function update()
	{
		$this->update++;
	}

	public function delete()
	{
		$this->delete++;
	}
}
