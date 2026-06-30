<?php


namespace Askaron\ClientId\Handler;


use Askaron\ClientId\CommonUid;

class MyAuth
{
	public static function OnAfterUserLoginHandler($arFields)
	{
		if (\Bitrix\Main\Config\Option::get("askaron.clientid", "consider_user_authorize")=='Y'){
			self::saveCodesToUserProps($arFields['USER_ID']);
		}
	}

	public static function saveCodesToUserProps($userId){
		$gaCodeSaved=false;
		$ymCodeSaved=false;
		$clientId= new CommonUid();
		$metaData = $clientId->detectMetaData();

		$by="personal_country";
		$order="desc";

		$rsUsers = \CUser::GetList(
			$by,
			$order,
			['ID'=>$userId],['SELECT'=>['UF_USER_ASKARON_CLIENT_CODE_YM','UF_USER_ASKARON_CLIENT_CODE_GA']]
		);
		$userFields = $rsUsers->Fetch();
		if (is_array($userFields['UF_USER_ASKARON_CLIENT_CODE_YM']))
		{
			if (in_array($metaData['YM_CODE'], $userFields['UF_USER_ASKARON_CLIENT_CODE_YM']))
			{
				$ymCodeSaved = true;
			}
		}
		if (is_array($userFields['UF_USER_ASKARON_CLIENT_CODE_YM']))
		{
			if (in_array($metaData['GA_CODE'], $userFields['UF_USER_ASKARON_CLIENT_CODE_GA']))
			{
				$gaCodeSaved = true;
			}
		}
		if(!$userFields['UF_USER_ASKARON_CLIENT_CODE_YM'])
			$userFields['UF_USER_ASKARON_CLIENT_CODE_YM'] =[];

		if(!$userFields['UF_USER_ASKARON_CLIENT_CODE_GA'])
			$userFields['UF_USER_ASKARON_CLIENT_CODE_GA'] =[];

		$user = new \CUser;
		if (!$ymCodeSaved){
			array_push($userFields['UF_USER_ASKARON_CLIENT_CODE_YM'],$metaData['YM_CODE']);
		}
		if (!$gaCodeSaved){
			array_push($userFields['UF_USER_ASKARON_CLIENT_CODE_GA'],$metaData['GA_CODE']);
		}

		$user->Update($userId,
			[
				'UF_USER_ASKARON_CLIENT_CODE_YM'=>$userFields['UF_USER_ASKARON_CLIENT_CODE_YM'],
				'UF_USER_ASKARON_CLIENT_CODE_GA'=>$userFields['UF_USER_ASKARON_CLIENT_CODE_GA']
			]
		);
	}
}