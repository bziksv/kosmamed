<?php


namespace Askaron\ClientId;


use Askaron\ClientId\ORM\QueueTable;
use Bitrix\Main\Type\DateTime;

class QueueManager
{
	const STATUS_NOT_SENDED = 'N';
	const STATUS_SENDED = 'S';
	const STATUS_ERROR = 'E';

	public static function addToQueue($data)
	{
		$dateNow = new DateTime();
		$dbRes = QueueTable::getList([
			'filter' => [
				'ENTITY_ID' => $data['ENTITY_ID'],
			],
			'select' => ['*'],
		]);

		if ($dataExist = $dbRes->fetch())
		{
			if ($dataExist['CLIENT_ID'] != $data['CLIENT_ID'])
			{
				$dbRes = QueueTable::update(
					$dataExist['ID'],
					[
						'CLIENT_ID' => $data['CLIENT_ID'],
						'STATUS' => self::STATUS_NOT_SENDED,
						'TIMESTAMP_X' => $dateNow,
					]
				);
			}
		}
		else
		{
			$dbRes = QueueTable::add([
				'DATE_CREATE' => $dateNow,
				'TIMESTAMP_X' => $dateNow,
				'STATUS' => self::STATUS_NOT_SENDED,
				'SITE_ID' => $data['SITE_ID'],
				'ENTITY_TYPE' => $data['ENTITY_TYPE'],
				'ENTITY_ID' => $data['ENTITY_ID'],
				'CLIENT_ID' => $data['CLIENT_ID'],
				'CONVERSION_DATE' => $data['CONVERSION_DATE'],
				'TARGET' => $data['TARGET'],
			]);
		}
	}

	public static function getNotSended()
	{
		$dbRes = QueueTable::getList(
			[
				"filter" => [
					'!STATUS' => self::STATUS_SENDED,
				],
				"select" => ["*"],
			]
		);
		$result=[];
		foreach ($dbRes->fetchAll() as $queueItem){
			$result[$queueItem['SITE_ID']][]=$queueItem;
		}
		return $result;
	}

	public static function changeStatus($arItems, $status, $comment = '')
	{
		if (is_array($arItems) && count($arItems) > 0)
		{
			foreach ($arItems as $item)
			{
				$dbRes = QueueTable::update(
					$item['ID'],
					['STATUS' => $status, 'COMMENT' => $comment]
				);
			}
		}
	}

	public static function sendQueue()
	{
		$queueList = self::getNotSended();

		foreach ($queueList as $siteQueue){
			$convGen = new Conversion\YMGenerator();
			$convResult = json_decode($convGen->submitConversion($siteQueue));
			if (is_array($convResult->errors))
			{
				self::changeStatus($siteQueue, self::STATUS_ERROR, $convResult->message);
			}
			else
			{
				self::changeStatus($siteQueue, self::STATUS_SENDED, json_encode($convResult));
			}
		}
		return '\\Askaron\\ClientId\\QueueManager::sendQueue();';
	}
}