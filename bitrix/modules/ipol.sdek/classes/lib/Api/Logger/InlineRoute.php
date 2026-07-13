<?php
namespace Ipolh\SDEK\Api\Logger;

/**
 * Class InlineRoute
 * @package Ipolh\SDEK\Api
 * @subpackage Logger
 */
class InlineRoute extends Route
{
    /**
     * @param string $dataString
     */
    public function log($dataString)
    {
        echo $dataString . PHP_EOL . '--------------------------------------------' . PHP_EOL;
    }

    public function read()
    {
        //you can read it where you log it. It's INLINE logger after all
        return '';
    }
}