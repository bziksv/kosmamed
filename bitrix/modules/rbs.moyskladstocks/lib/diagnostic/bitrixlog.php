<?php
namespace Rbs\MoyskladStocks\Diagnostic;

use Rbs\MoyskladStocks\LangMsg;

class BitrixLog
{
	public static function getBitrixLog()
	{
		$result = [];

		$exceptionHandling = \Bitrix\Main\Config\Configuration::getValue('exception_handling');
		if (!$exceptionHandling['debug']) {
			throw new \Exception(LangMsg::get('DIAG_APP_MODE_DISABLED'));
		}

		if (!isset($exceptionHandling['log']) || !isset($exceptionHandling['log']['settings']) || !isset($exceptionHandling['log']['settings']['file'])) {
			throw new \Exception(LangMsg::get('DIAG_APP_LOG_NOT_CONFIGURED'));
		}

		$logPath = $exceptionHandling['log']['settings']['file'];
		$result['log_file_path'] = (mb_strpos($logPath, '/var/log/') !== false)
			? $logPath
			: \Bitrix\Main\Application::getDocumentRoot() . '/' . $logPath;
		if (!file_exists($result['log_file_path'])) {
			throw new \Exception(LangMsg::get('DIAG_APP_LOG_FILE_NOT_EXISTS'));
		}

		$result['file_content'] = file_get_contents($result['log_file_path']);
		if (empty($result['file_content'])) {
			throw new \Exception(LangMsg::get('DIAG_APP_LOG_FILE_EMPTY'));
		}

		return $result;
	}
}