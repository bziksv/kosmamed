<?php

namespace Rbs\MoyskladStocks\Debug;

use \Rbs\MoyskladStocks\Config;
use \Rbs\MoyskladStocks\Utils;
use \Rbs\MoyskladStocks\LangMsg;
use \Bitrix\Main\Application;

class FileController
{
    private static $instance = [];
	private function __clone() {}
	private function __wakeup() {}
	
    public static function getInstance(): self
    {
		$profile = Config::getProfileId();

        if (!isset(self::$instance[$profile])) {
            self::$instance[$profile] = new self();
        }

        return self::$instance[$profile];
    }

	private $profile = 0;
	private $logDirPath = '';
	private $logDirHref = '';

	private function __construct() 
	{
		$this->profile = Config::getProfileId();
		$this->buildLogDirPath();
		$this->buildLogDirHref();
	}

	private static $defaultLogFileName = 'main_log.txt';
	private static $maxFileSize = 256;

	private function buildLogDirPath(): void
	{
		$directoryName = \realpath(__DIR__) . '/../../logs/profile_' . $this->profile . '/';
		if (!is_dir($directoryName)) {
			mkdir($directoryName, 0755, true);
		}
		if (!is_dir($directoryName)) {
			throw new \RuntimeException('Log directory not found');
		}
		$this->logDirPath = $directoryName;
	}

	private function buildLogDirHref(): void
	{
		$docRoot = Application::getDocumentRoot();
		$href = str_replace([$docRoot, '/lib/debug/../..'], '', $this->getLogDirPath());
		$this->logDirHref = '/bitrix/admin/fileman_admin.php?lang=' . LANG . '&path=' . urlencode($href);
	}

	public function getLogDirPath(): string
	{
		return $this->logDirPath;
	}

	public function getLogDirHref(): string
	{
		return $this->logDirHref;
	}

	public function getDefaultLogFileName(): string
	{
		return self::$defaultLogFileName;
	}

	public function getMaxFileSize(): int
	{
		return self::$maxFileSize;
	}

	public function getValidFilesFromLogDir(): array
	{
		return $this->getFileListFromLogDir('*.txt');
	}

	private function getFileListFromLogDir(string $mask): array
	{
		$fileList = glob($this->getLogDirPath() . $mask);
		if ($fileList === false) {
			return [];
		}
		return $fileList;
	}

	public function renameFile(string $fileName): bool
	{
		$fullFilePath = $this->getLogDirPath() . $fileName;
		if (file_exists($fullFilePath)) {
			$newFileName = $fileName . "__from__" . time();
			return rename($fullFilePath, str_replace($fileName, $newFileName, $fullFilePath) . '.txt');
		}
		return false;
	}

	public function clearDir()
	{
		$files = $this->getValidFilesFromLogDir();
		if (is_array($files) && count($files) > 0) {
			foreach ($files as $file) {
				$this->unlinkFile($file);
			}
		}
	}

	public function clearDirByTime(int $days = 7): array
	{
		$files = $this->getValidFilesFromLogDir();
		$deletedFiles = [];

		if ($days < 7) {
			return $deletedFiles;
		}

		if (is_array($files) && count($files) > 0) {
			$currentTime = (new \DateTime('now'))->getTimestamp();
			$daysInSeconds = $days * 24 * 60 * 60;
			foreach ($files as $file) {
				$fileTime = filemtime($file);
				$fileInfo = $this->getFileInfo($file);
				if (($currentTime - $fileTime) > $daysInSeconds) {
					if ($this->unlinkFile($file)) {
						$deletedFiles[] = $fileInfo;
					}
				}
			}
		}

		return $deletedFiles;
	}

	public function clearDirByCount(int $count = 5): array
	{
		$files = $this->getValidFilesFromLogDir();
		$deletedFiles = [];

		if ($count < 5) {
			return $deletedFiles;
		}

		if (is_array($files) && count($files) > $count) {

			usort($files, function ($a, $b) {
				return filemtime($a) - filemtime($b);
			});

			$deleteCount = count($files) - $count;

			for ($i = 0; $i < $deleteCount; $i++) {
				$file = $files[$i];
				$fileInfo = $this->getFileInfo($file);
				if ($this->unlinkFile($file)) {
					$deletedFiles[] = $fileInfo;
				}
			}
		}

		return $deletedFiles;
	}

	private function getFileInfo(string $filePath): array
	{
		$fileSize = round(filesize($filePath) / 1024 / 1024, 2);
		$fileDate = date('d.m.Y H:i:s', filemtime($filePath));
		$fileName = basename($filePath);

		return [
			'NAME' => $fileName,
			'DATE' => $fileDate,
			'SIZE' => $fileSize,
		];
	}

	private function unlinkFile(string $filePath): bool
	{
		if (
			file_exists($filePath) &&
			(
				pathinfo($filePath, PATHINFO_EXTENSION) === 'txt' ||
				mb_strpos($filePath, '__from__') !== false
			)
		) {
			return unlink($filePath);
		}
		return false;
	}

	public function clearFileProcess(): void
	{
		$clearTypes = [
			'clearDirByTime' => 15,
			'clearDirByCount' => 250,
		];

		foreach($clearTypes as $type => $value) {
			
			$logger = new Loger();
			$deletedFiles = [];
			$deletedSize = 0;
			
			try {
				$deletedFiles = $this->$type($value);
				if(count($deletedFiles) > 0) {
					foreach($deletedFiles as $file) {
						$deletedSize += $file['SIZE'];
						$logger->addInfoMessage(LangMsg::get('DEBUG_FILE_CONTROLLER_CLEAR_FILE_INFO', $file));
					}
				}
			} catch (\Throwable $e) {
				$logger->addErrorMessage(Utils::build_exception_message($e));
			}

			$logger->exportLog(LangMsg::get('DEBUG_FILE_CONTROLLER_CLEAR_FILE_PROCESS_' . $type, [
				'#VALUE#' => $value,
				'#DELETE_COUNT#' => count($deletedFiles),
				'#DELETE_SIZE#' => $deletedSize,
			]));

		}

	}

}
