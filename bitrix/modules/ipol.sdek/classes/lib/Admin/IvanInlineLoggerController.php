<?php
namespace Ipolh\SDEK\Admin;

use Ipolh\SDEK\Api\Logger\InlineRoute;
use Ipolh\SDEK\Api\Logger\Logger;
use Ipolh\SDEK\Api\Logger\Psr\Log\LogLevel;

/**
 * Class IvanInlineLoggerController
 * @package Ipolh\SDEK\Admin
 */
class IvanInlineLoggerController extends Logger
{
    /**
     * @var string
     */
    protected $curlTemplate = '{method}' . ' ' . '{process}' . PHP_EOL . '{content}';

    /**
     * Class constructor
     */
    public function __construct()
    {
        $route = new InlineRoute();
        $route->enable();
        parent::__construct([$route]);
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message = '', array $context = [])
    {
        if ($level === LogLevel::DEBUG && array_key_exists('method', $context)) {
            parent::log($level, $this->interpolate($this->curlTemplate, $context), []);
        } else
            parent::log($level, $message, $context);
    }
}