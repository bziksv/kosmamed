<?php

namespace Askaron\ClientId;


use Askaron\ClientId\ORM\CommonUidTable,
	Bitrix\Main\Type\DateTime;


class CommonUid
{

	public function getDataByShortCode($shortCode)
	{
		return $this->getDataByField('SHORT_CODE', $shortCode);
	}

	public function getDataByYMCode($ymCode)
	{
		return $this->getDataByField('YM_CODE', $ymCode);
	}

	public function getDataByGACode($gaCode)
	{
		return $this->getDataByField('GA_CODE', $gaCode);
	}

	public function isShortCodeExist($shortCode)
	{
		return $this->isCodeExist('SHORT_CODE', $shortCode);
	}

	public function isYMCodeExist($ymCode)
	{
		return $this->isCodeExist('YM_CODE', $ymCode);
	}

	public function isGACodeExist($gaCode)
	{
		return $this->isCodeExist('GA_CODE', $gaCode);
	}

	/**
	 * @param $fieldName
	 * @param $fieldValue
	 * @return bool
	 *
	 * common method to check code existence
	 */
	private function isCodeExist($fieldName, $fieldValue)
	{
		if (mb_strlen($fieldValue) > 0)
		{
			$dbRes = CommonUidTable::getList(
				[
					"filter" => [$fieldName => $fieldValue],
					"select" => ["ID"],
					//'cache' => 60 * 60 * 24 * 7, // OnEndBufferContent possible cache error

				]
			);
			if ($dbRes->fetch())
			{
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param $fieldName
	 * @param $fieldValue
	 * @return array
	 *
	 * common method to get data by field
	 */
	private function getDataByField($fieldName, $fieldValue)
	{
		$dbRes = CommonUidTable::getList(
			[
				"filter" => [
					$fieldName => $fieldValue,
				],
				"select" => ["*"],
				//'cache' => 60 * 60 * 24 * 7, // OnEndBufferContent possible cache error

			]
		);
		$dataResult = [];
		$data = $dbRes->fetch();
		if (mb_strlen($data['SHORT_CODE']) > 0)
		{
			$dataResult['YM_CODE'] = $data['YM_CODE'];
			$dataResult['GA_CODE'] = $data['GA_CODE'];
			$dataResult['SHORT_CODE'] = $data['SHORT_CODE'];
		}
		return $dataResult;
	}

	/**
	 * @param $dataCode
	 * @return bool|int
	 */
	public function saveCodeInDb($dataCode)
	{
		if (mb_strlen($dataCode['YM_CODE']) < 1 && mb_strlen($dataCode['GA_CODE']) < 1)
		{
			return false;
		}
		else
		{
			$shortCode = $this->generateShortCode();
			$dateNow = new DateTime();
			$dbRes = CommonUidTable::add(
				[
					'SHORT_CODE' => $shortCode,
					'YM_CODE' => $dataCode['YM_CODE'],
					'GA_CODE' => $dataCode['GA_CODE'],
					'DATE_CREATE' => $dateNow,
					'LAST_USE' => $dateNow,
				]
			);

			if ($dbRes->isSuccess())
			{
				$dataCode['SHORT_CODE']=$shortCode;
				$this->saveCodeInSession($dataCode);
				return $shortCode;
			}
			else
			{
				return false;
			}
		}
	}
	/**
	 * @param $dataCode
	 */
	public function saveCodeInSession($dataCode)
	{
		if (!$this->codeInSession($dataCode))
		{
			$_SESSION['ASKARON_SESSION_INFO'] = $dataCode;
		}
	}

	/**
	 * @param $metaData
	 * @return string|bool
	 */
	public function codeInSession($metaData)
	{
		$sessInfo=$_SESSION['ASKARON_SESSION_INFO'];
		if (!$sessInfo)
			$sessInfo=[];
		if ($metaData['YM_CODE']==$sessInfo['YM_CODE'] && $metaData['GA_CODE']==$sessInfo['GA_CODE'] && array_key_exists('SHORT_CODE',$sessInfo))
		{
			return $sessInfo['SHORT_CODE'];
		}else{
			return false;
		}
	}

	/**
	 * @param bool $checkUnique
	 * @return int
	 */
	public function generateShortCode($checkUnique = true){
		$shortCode = rand(100000, 999999);
		if ($checkUnique){
			if ($this->isShortCodeExist($shortCode)){
				$this->generateShortCode();
			}else{
				return $shortCode;
			}
		}else{
			return $shortCode;
		}
	}

	/**
	 * @return array
	 */
	public function detectMetaData(){
		$arMetaData = array(
			'YM_CODE' => $this->detectOriginalYMCode(),
			'GA_CODE' => $this->detectOriginalGACode(),
		);
		return $arMetaData;
	}
	private function detectOriginalYMCode()
	{
		if (array_key_exists('_ym_uid', $_COOKIE))
		{
			return $_COOKIE['_ym_uid'];
		}else{
			return '';
		}
	}

	/**
	 * @return string|string[]
	 */
	private function detectOriginalGACode()
	{
		if (array_key_exists('_ga', $_COOKIE))
		{
			return str_replace('GA1.2.','',$_COOKIE['_ga']);
		}else{
			return '';
		}
	}
}