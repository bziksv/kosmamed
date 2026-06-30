<?php
namespace Rbs\MoyskladStocks\Debug;

class Reader
{
    public static function parseLogFile(string $logFileName)
    {
		$fileLog = new File($logFileName);
		$fileContent = $fileLog->getContent();
		$arrLog = [];

		if (!empty($fileContent)) {
			$fileParseMessages = explode('<<<>>>', $fileContent);
			array_pop($fileParseMessages);

			$fileParseMessages = array_reverse($fileParseMessages);
			foreach ($fileParseMessages as $k => $message) {
				$parseMessage = explode("\n", $message);
				if (empty($parseMessage[0])) {
					array_shift($parseMessage);
				}
				array_pop($parseMessage);
				$arrLog[$k] = [
					'DATE' => trim(explode('>>>', $parseMessage[0])[1]),
					'HEAD' => trim(explode('>>>', $parseMessage[0])[0])
				];
				$typeLogMessages = [];
				foreach ($parseMessage as $j => $subMsg) {
					if ($j > 0) {
						$type = trim(explode('>>>', $subMsg)[1]);
						$arrLog[$k]['MESSAGES'][] = [
							'TEXT' => str_replace('///n', "\n", trim(explode('>>>', $subMsg)[0])),
							'TYPE' => $type
						];
						$typeLogMessages[] = $type;
					}
				}

				$arrLog[$k]['HAS_ERRORS'] = false;
				$arrLog[$k]['HAS_WARNINGS'] = false;
				$arrLog[$k]['HAS_SUCCESS'] = false;
				foreach ($typeLogMessages as $type) {
					switch ($type) {
						case 'error':
							$arrLog[$k]['HAS_ERRORS'] = true;
							break;
						case 'warning':
							$arrLog[$k]['HAS_WARNINGS'] = true;
							break;
						case 'success':
							$arrLog[$k]['HAS_SUCCESS'] = true;
							break;
					}
				}

				$arrLog[$k]['TYPE'] = 'info';
				if ($arrLog[$k]['HAS_ERRORS']) {
					$arrLog[$k]['TYPE'] = 'error';
				} else if ($arrLog[$k]['HAS_WARNINGS']) {
					$arrLog[$k]['TYPE'] = 'warning';
				} else if ($arrLog[$k]['HAS_SUCCESS']) {
					$arrLog[$k]['TYPE'] = 'success';
				}
			}
		} else {
			$arrLog = [
				[
					'DATE' => '',
					'HEAD' => ''
				]
			];
		}

		return !empty($arrLog) && count($arrLog) > 0 ? $arrLog : false;
    }

	public static function getHtmlLog(string $logFileName, string $htmlId = 'ajax_log'): string
	{
		$arrLog = self::parseLogFile($logFileName);

		$htmlLog = '<div><div id="' . $htmlId . '">';
		if ($arrLog = self::parseLogFile($logFileName)) {
			foreach ($arrLog as $logMsg) {
				if (!empty($logMsg['HEAD'])) {
					$htmlLog .= '<div class="log-block-messages">';
					$htmlLog .= '<div class="log-message log-main status-' . $logMsg['TYPE'] . '">';
					$htmlLog .= '<span>' . $logMsg['HEAD'] . '</span>';
					$htmlLog .= '<span>' . $logMsg['DATE'] . '</span>';
					$htmlLog .= '</div>';
					$htmlLog .= '<div class="log-toggle-block" style="display:none">';
					foreach ($logMsg['MESSAGES'] as $subLogMsg) {
						$htmlLog .= '<div class="log-submessage log-message log-' . $subLogMsg['TYPE'] . ' opened">';
						$htmlLog .= '<span>' . $subLogMsg['TEXT'] . '</span>';
						$htmlLog .= '</div>';
					}
					$htmlLog .= '</div>';
					$htmlLog .= '</div>';
				}
			}
		}
		$htmlLog .= '</div></div>';

		return $htmlLog;
	}

	public static function getFileList(int $limit = 50): array
	{
		$fileController = FileController::getInstance();

		$result = [];
		$logFiles = $fileController->getValidFilesFromLogDir();
		if (empty($logFiles)) {
			return $result;
		}

		usort($logFiles, function ($a, $b) {
			return filemtime($b) - filemtime($a);
		});
		$logFiles = array_slice($logFiles, 0, $limit);

		$defaultLogFileName = $fileController->getDefaultLogFileName();
		$logFullDir = $fileController->getLogDirPath();

		if (!empty($logFiles)) {

			foreach ($logFiles as $logFile) {
				$fileName = basename($logFile);
				$fileTime = filemtime($logFile);
				$isDefault = $fileName === $defaultLogFileName;
				$result[$fileName] = [
					'IS_DEFAULT' => $isDefault,
					'NAME' => $fileName,
					'DATE' => date('d.m.Y H:i:s', $fileTime),
					'TIMESTAMP' => !$isDefault ? $fileTime : time(),
				];
			}

			if (!isset($result[$defaultLogFileName])) {
				$defaultLogPath = $logFullDir . $defaultLogFileName;
				if (file_exists($defaultLogPath)) {
					$defaultLogTime = filemtime($defaultLogPath);
					array_unshift($result, [
						'IS_DEFAULT' => true,
						'NAME' => $defaultLogFileName,
						'DATE' => date('d.m.Y H:i:s', $defaultLogTime),
						'TIMESTAMP' => time()
					]);
				}
			}

			usort($result, function ($a, $b) {
				return $b['TIMESTAMP'] - $a['TIMESTAMP'];
			});
		}

		return $result;
	}
}
