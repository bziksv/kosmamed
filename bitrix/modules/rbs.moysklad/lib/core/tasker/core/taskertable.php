<?php
namespace Rbs\Moysklad\Core\Tasker\Core;

use Bitrix\Main\ORM\Fields;

class TaskerTable extends \Bitrix\Main\ORM\Data\DataManager
{

    public const PREFIX_TABLE = 'rbs_moysklad_';

	public static function getTableName()
	{
		return self::PREFIX_TABLE . 'tasker_table';
	}

	public static function getMap()
	{
		return [

			new Fields\IntegerField('ID', [
				'primary' => true,
				'autocomplete' => true,
			]),

			//task line identifier, required for task grouping
			new Fields\StringField('LINE_ID', [
				'required' => true,
				'default_value' => 'DEFAULT',
			]),
			//arbitrary task tag, required for grouping tasks within a line or for filtering tasks
			new Fields\StringField('TAG', []),

            //task status
			new Fields\EnumField('STATUS', array(
				'values' => array('PENDING', 'PROCESSING', 'COMPLETED', 'FAILED'),
				'default_value' => 'PENDING',
			)),

			//abstract task data
			new Fields\TextField('DATA', [
				'required' => true,
				'serialized' => true,
			]),
			//abstract task execution result data
			new Fields\TextField('RESULT', [
				'required' => false,
				'serialized' => true,
			]),

			//task configuration, including the maximum number of task execution attempts
			new Fields\TextField('CONFIG', [
				'required' => true,
				'serialized' => true,
			]),

			//task lock flag
			new Fields\BooleanField('IS_LOCKED', [
				'required' => true,
				'default_value' => false,
				'values' => array('N', 'Y'),
			]),

			//current task execution attempt counter
			new Fields\IntegerField('ATTEMPT', [
				'required' => true,
				'default_value' => 0,
			]),

			//last task execution error
			new Fields\StringField('LAST_ERROR', []),

			//task creation date
			new Fields\DatetimeField('CREATED_AT', [
				'default_value' => new \Bitrix\Main\Type\DateTime()
			]),

			//date of last task execution attempt
			new Fields\DatetimeField('UPDATED_AT', [
				'default_value' => new \Bitrix\Main\Type\DateTime()
			]),

		];
	}

}