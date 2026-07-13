<?php
namespace Ipolh\SDEK\Api\Logger;

/**
 * Class FileRoute
 * @package Ipolh\SDEK\Api
 * @subpackage Logger
 */
class FileRoute extends Route
{
    /**
     * @var string Path to file
     */
    public $filePath;

    /**
     * FileRoute constructor.
     * @param string $filePath
     */
    public function __construct($filePath)
    {
        $this->filePath = $filePath;
    }

    /**
     * @param string $dataString
     */
    public function log($dataString)
    {
        if (!file_exists(dirname($this->filePath))) {
            mkdir(dirname($this->filePath), 0777, 1);
        }

        file_put_contents($this->filePath,
            trim($dataString) . PHP_EOL . '--------------------------------------------' . PHP_EOL,
            FILE_APPEND);
    }

    public function read()
    {
        if (file_exists($this->filePath)) {
            return (string) file_get_contents($this->filePath);
        } else {
            return '';
        }
    }
}