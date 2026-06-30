<?php
namespace Rbs\MoyskladStocks\Debug;

class File
{
    private $fileName;

    private $fullFilePath = '';
    private $fOpenFileName = '';

    private $fileController;

    public function __construct($fileName = '')
    {
        $this->fileController = FileController::getInstance();
        if (!empty($fileName)) {
            $this->fileName = $fileName;
        } else {
            $this->fileName = $this->fileController->getDefaultLogFileName();
        }
        $this->fullFilePath = $this->fileController->getLogDirPath() . $this->fileName;
    }

    public function open()
    {
        $this->fOpenFileName = fopen($this->fullFilePath, 'a');
    }

    public function write(string $string = '')
    {
        fwrite($this->fOpenFileName, $string . "\n");
    }

    public function writeString(string $string = '')
    {
        $tempFile = fopen($this->fullFilePath, "a");
        fwrite($tempFile, $string . "\n");
        fclose($tempFile);
    }

    public function close()
    {
        fclose($this->fOpenFileName);
    }

    public function refreshFile()
    {
        if ($this->fileController->renameFile($this->fileName)) {
            $this->fOpenFileName = null;
            $this->open();
            $this->close();
        }
    }

    public function getContent()
    {
        return file_get_contents($this->fullFilePath);
    }

    public function isMaxFileSize(): bool
    {
        if (\file_exists($this->fullFilePath)) {
            if (filesize($this->fullFilePath) / 1024 >= $this->fileController->getMaxFileSize()) {
                return true;
            }
        }
        return false;
    }

    /** @deprecated */
    public static function getLog($fileName = '')
    {
        return Reader::parseLogFile($fileName);
    }

    /** @deprecated */
    public static function getFileList(int $limit = 50): array
    {
        return Reader::getFileList($limit);
    }

    /** @deprecated */
    public static function getLogFullDir(): string
    {
        return FileController::getInstance()->getLogDirPath();
    }

    /** @deprecated */
    public static function getLogDirHref(): string
    {
        return FileController::getInstance()->getLogDirHref();
    }

    /** @deprecated */
    public static function getDefaultLogFileName(): string
    {
        return FileController::getInstance()->getDefaultLogFileName();
    }

    /** @deprecated */
    public static function getTxtFileListFromLogDir(): array
    {
        return FileController::getInstance()->getValidFilesFromLogDir();
    }
    
}
