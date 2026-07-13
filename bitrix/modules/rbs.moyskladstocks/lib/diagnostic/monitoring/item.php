<?php

namespace Rbs\MoyskladStocks\Diagnostic\Monitoring;

class Item	
{
	private $id;
	private $value; // mixed
	private $status; // info, success, error, warning
	private $type; // string, boolean
	private $show = true; // bool

	public const STATUS_INFO = 'info';
	public const STATUS_SUCCESS = 'success';
	public const STATUS_WARNING = 'warning';
	public const STATUS_ERROR = 'error';

	private const TYPE_STRING = 'string';
	private const TYPE_BOOLEAN = 'boolean';
	private const TYPE_DATE = 'date';

	private const COLOR_INFO = 'blue';
	private const COLOR_SUCCESS = 'green';
	private const COLOR_WARNING = 'yellow';
	private const COLOR_ERROR = 'red';

	private function __construct(string $id, $value, string $type = self::TYPE_STRING, string $status = self::STATUS_INFO)
	{
		$this->id = $id;
		$this->value = $value;
		$this->type = $type;
		$this->status = $status;
	}

	public static function createItem(string $id): self
	{
		return new self($id, '', self::TYPE_STRING, self::STATUS_INFO);
	}

	public function setValue($value): self
	{
		$this->value = $value;
		return $this;
	}

	public function setStatus(string $status): self
	{
		$this->status = self::getValidStatus($status);
		return $this;
	}

	public function setType(string $type): self
	{
		$this->type = self::getValidType($type);
		return $this;
	}

	public function setShow(bool $show): self
	{
		$this->show = $show;
		return $this;
	}

	public static function createBoolItem(string $id, bool $value, string $status = ''): self
	{
		if (empty($status)) {
			$status = $value ? self::STATUS_SUCCESS : self::STATUS_ERROR;
		} else {
			$status = self::getValidStatus($status);
		}
		return new self($id, $value, 'boolean', $status);
	}

	public static function createStringItem(string $id, string $value, string $status = self::STATUS_INFO): self
	{
		return new self($id, $value, 'string', $status);
	}

	public static function createDateItem(string $id, string $value, string $status = self::STATUS_INFO): self
	{
		$value = date('d.m.Y H:i:s', strtotime($value));
		return new self($id, $value, 'date', $status);
	}

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'show' => $this->show,
			'type' => $this->type,
			'value' => $this->value,
			'status' => $this->status,
			'color' => $this->getColor(),
		];
	}

	private function getColor(): string
	{
		switch ($this->status) {
			case self::STATUS_INFO:
				return self::COLOR_INFO;
			case self::STATUS_SUCCESS:
				return self::COLOR_SUCCESS;
			case self::STATUS_WARNING:
				return self::COLOR_WARNING;
			case self::STATUS_ERROR:
				return self::COLOR_ERROR;
			default:
				return self::COLOR_INFO;
		}
	}

	private static function getValidStatus(string $status): string
	{
		if(in_array($status, [self::STATUS_INFO, self::STATUS_SUCCESS, self::STATUS_WARNING, self::STATUS_ERROR])) {
			return $status;
		}
		return self::STATUS_INFO;
	}

	private static function getValidType(string $type): string
	{
		if(in_array($type, [self::TYPE_STRING, self::TYPE_BOOLEAN, self::TYPE_DATE])) {
			return $type;
		}
		return self::TYPE_STRING;
	}
}