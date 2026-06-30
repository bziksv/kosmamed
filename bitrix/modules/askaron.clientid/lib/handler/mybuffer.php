<?php


namespace Askaron\ClientId\Handler;

class MyBuffer
{
	public static function OnEndBufferContentHandler(&$content)
	{
		if ( !defined( "BX_BUFFER_SHUTDOWN" ) ) // if not fatal error
		{
			global $USER;
			$userId = $USER->GetId();
			$shortCode = '';

			$clientId = new \Askaron\ClientId\CommonUid();
			$metaData = $clientId->detectMetaData();
			if ($clientId->codeInSession($metaData))//if short code in session
			{
				$shortCode = $clientId->codeInSession($metaData);
			}
			else//use common UID table
			{
				if (\Bitrix\Main\Config\Option::get("askaron.clientid", "consider_user_authorize") == 'Y')
				{
					MyAuth::saveCodesToUserProps($userId);
				}
				$metaData = $clientId->detectMetaData();
				if ($clientId->isYMCodeExist($metaData['YM_CODE']))//if YM_CODE exist in db
				{
					$dataCode = $clientId->getDataByYMCode($metaData['YM_CODE']);
					$clientId->saveCodeInSession($dataCode);
					$shortCode = $dataCode['SHORT_CODE'];
				}
				else
				{
					if ($clientId->isGACodeExist($metaData['GA_CODE']))//if GA_CODE exist in db
					{
						$dataCode = $clientId->getDataByGACode($metaData['GA_CODE']);
						$clientId->saveCodeInSession($dataCode);
						$shortCode = $dataCode['SHORT_CODE'];
					}
					else //add new data in db
					{
						$shortCode = $clientId->saveCodeInDb($metaData);
					}
				}
			}

			if( !defined( "ADMIN_SECTION" ) || ADMIN_SECTION !== true )
			{
				if (
					mb_strpos($content, '#ASKARON_CLIENTID_CODE#') !== false
				)
				{
					$content = str_replace('#ASKARON_CLIENTID_CODE#', $shortCode, $content);
				}
			}
		}
	}
}