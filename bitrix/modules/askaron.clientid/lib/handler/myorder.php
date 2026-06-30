<?php


namespace Askaron\ClientId\Handler;

use Askaron\ClientId\CommonUid;
use Askaron\ClientId\QueueManager;
use Bitrix\Sale;

class MyOrder
{
	public static function OnSaleOrderBeforeSaved(\Bitrix\Main\Event $event)
	{
		$order = $event->getParameter("ENTITY");
		$isNew = $order->isNew();
		$siteID = $order->getField('LID');
		if (!$isNew)
		{
			$oldOrder = Sale\Order::load($order->getId());
		}
		$clientId = new CommonUid();
		$metaData = $clientId->detectMetaData();
		$payerType = $order->getField('PERSON_TYPE_ID');
		$dataCode = [];
		$inputShortCode = 0;
		$oldInputShortCode = 0;

		$generatedProps = self::createOrderPropsForPayerType($payerType);
		$shortCodePropId = $generatedProps['SC_PROPS'];;

		$propertyCollection = $order->getPropertyCollection();
		if (!$isNew)
		{
			$oldPropertyCollection = $oldOrder->getPropertyCollection();
		}

		$shortCodeProp = $propertyCollection->getItemByOrderPropertyId($shortCodePropId);
		if (!$isNew)
		{
			$oldShortCodeProp = $oldPropertyCollection->getItemByOrderPropertyId($shortCodePropId);
		}
		if ($oldShortCodeProp && $oldInputShortCode == 0)
		{
			$oldInputShortCode = $oldShortCodeProp->getValue();
		}
		if ($shortCodeProp)
		{
			$shortCodeProp = $shortCodeProp->getValue();
			if ((int)$shortCodeProp > 0)
			{
				$inputShortCode = $shortCodeProp;
			}
		}

		if ($inputShortCode == 0)//if short code not inputed
		{
			if ($isNew)
			{
				if ($clientId->codeInSession($metaData))//if short code saved in session
				{
					$shortCode = $clientId->codeInSession($metaData);
					$dataCode = $clientId->getDataByShortCode($shortCode);
				}
				else//if not saved
				{
					if ($clientId->isYMCodeExist($metaData['YM_CODE']))//try to find codes in database
					{
						$dataCode = $clientId->getDataByYMCode($metaData['YM_CODE']);
						$shortCode = $dataCode['SHORT_CODE'];
					}
					else if ($clientId->isGACodeExist($metaData['GA_CODE']))
					{
						$shortCode = $dataCode['SHORT_CODE'];
						$dataCode = $clientId->getDataByGACode($metaData['YM_CODE']);
					}
					else//if codes not in database
					{
						$shortCode = $clientId->saveCodeInDb($metaData);
						$dataCode = $clientId->getDataByShortCode($shortCode);
					}
				}
			}
		}
		else//if short code inputed
		{
			if ((int)$oldInputShortCode != (int)$inputShortCode)
			{
				$dataCode = $clientId->getDataByShortCode($inputShortCode);
				$convData = ['ENTITY_TYPE' => 'ORDER',
					'ENTITY_ID' => $order->getId(),
					'CLIENT_ID' => $dataCode['YM_CODE'],
					'SITE_ID' => $siteID,
					'TARGET' => \Bitrix\Main\Config\Option::get('askaron.clientid', "ym_new_order" . $siteID)];
				$convData['CONVERSION_DATE'] = $order->getField('DATE_INSERT');

				QueueManager::addToQueue($convData);
			}
		}
		if (is_array($dataCode) && count($dataCode) > 0)
		{
			$ymProp = $propertyCollection->getItemByOrderPropertyId($generatedProps['YM_PROPS']);
			if ($ymProp)
			{
				$ymProp->setValue($dataCode['YM_CODE']);
			}

			$gaProp = $propertyCollection->getItemByOrderPropertyId($generatedProps['GA_PROPS']);
			if ($gaProp)
			{
				$gaProp->setValue($dataCode['GA_CODE']);
			}

		}
	}

	public static function createOrderPropsForPayerType($payerType)
	{
		$ymPropId = 0;
		$gaPropIds = 0;
		$shortCodePropId = 0;
		$gaPropExist = false;
		$ymPropExist = false;
		$shortPropExist = false;
		$dbRes = \CSaleOrderProps::GetList([], ["@CODE" => ['YM_CODE', 'GA_CODE', 'SHORT_CODE'], 'PERSON_TYPE_ID' => $payerType], false, false, ['CODE', 'ID']);

		while ($prop = $dbRes->Fetch())
		{
			switch ($prop['CODE'])
			{
				case 'YM_CODE':
					$ymPropExist = true;
					$ymPropId = $prop['ID'];
					break;
				case 'GA_CODE':
					$gaPropExist = true;
					$gaPropId = $prop['ID'];
					break;
				case 'SHORT_CODE':
					$shortPropExist = true;
					$shortCodePropId = $prop['ID'];
					break;
			}
		}
		if ((int)$payerType > 0)
		{

			$arFields = [
				"PERSON_TYPE_ID" => $payerType,
				"NAME" => GetMessage("ASKARON_CLIENTID_SHORTCODE_PROP_NAME"),
				"CODE" => "SHORT_CODE",
				"UTIL" => 'Y',
				"TYPE" => "STRING",
				"PROPS_GROUP_ID" => 1,
				"REQUIED" => "N",
				"DEFAULT_VALUE" => "",
				"SORT" => 100,
				"USER_PROPS" => "N",
				"IS_LOCATION" => "N",
				"IS_LOCATION4TAX" => "N",
				"SIZE1" => 0,
				"SIZE2" => 0,
				"DESCRIPTION" => "",
				"IS_EMAIL" => "N",
				"IS_PROFILE_NAME" => "N",
				"IS_PAYER" => "N",
			];
			if (!$shortPropExist)
			{
				$ID = \CSaleOrderProps::Add($arFields);
				if ($ID)
				{
					$shortCodePropIds = $ID;
				}
			}
			$arFields['NAME'] = GetMessage("ASKARON_CLIENTID_YMCODE_PROP_NAME");
			$arFields['CODE'] = 'YM_CODE';
			if (!$ymPropExist)
			{
				$ID = \CSaleOrderProps::Add($arFields);
				if ($ID)
				{
					$ymPropIds = $ID;
				}
			}
			$arFields['NAME'] = GetMessage("ASKARON_CLIENTID_GACODE_PROP_NAME");
			$arFields['CODE'] = 'GA_CODE';
			if (!$gaPropExist)
			{
				$ID = \CSaleOrderProps::Add($arFields);
				if ($ID)
				{
					$gaPropIds = $ID;
				}
			}
		}
		return ['YM_PROPS' => $ymPropId, 'GA_PROPS' => $gaPropId, 'SC_PROPS' => $shortCodePropId];
	}
}