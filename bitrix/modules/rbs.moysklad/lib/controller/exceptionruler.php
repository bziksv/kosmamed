<?php

namespace Rbs\Moysklad\Controller;

use Rbs\Moysklad\LangMsg;
use Rbs\Moysklad\Utils;
use Rbs\Moysklad\Config;

class ExceptionRuler
{
	public static function throwApiResponseException($apiResponse = null, array $exceptionParams = [])
	{
		if(Utils::property_exists($apiResponse, ['errors'])) {
			
			$messId = $exceptionParams['id'] . '_' . $exceptionParams['action'];
			$errorText = self::buildErrorMessage($messId, $apiResponse->errors);

			/* if(!empty($exceptionParams['entity'])) {
				$errorText .= " ({$exceptionParams['entity']})";
			} */

			throw new \Bitrix\Main\SystemException($errorText);

		}
	}

	public static function checkApiResponseErrors($apiResponse = null, array $exceptionParams = [])
	{
		if(Utils::property_exists($apiResponse, ['errors'])) {
			foreach($apiResponse->errors as $code => $error) {
				if (Config::isRetryErrorCode($code)) {
					return self::throwApiResponseException($apiResponse, $exceptionParams);
				}
			}
		}
	}

	private static function buildErrorMessage($messId = '', array $errorList = [])
	{
		$errorListStr = '';
		if(Utils::is_count($errorList)) {
			$errorListStr = implode('; ', array_values($errorList));
		}		
		return !empty($errorListStr) ? LangMsg::get('EXCEPTION_' . $messId) . ' : ' . $errorListStr : LangMsg::get('EXCEPTION_' . $messId);
	}
}