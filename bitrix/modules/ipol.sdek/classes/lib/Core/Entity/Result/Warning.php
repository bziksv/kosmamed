<?php
namespace Ipolh\SDEK\Core\Entity\Result;

/**
 * Class Warning
 * @package Ipolh\SDEK\Core
 * @subpackage Entity
 */
class Warning extends Info
{
    /**
     * @param string $message
     * @param int|string|null $code
     */
    public function __construct($message, $code = null)
    {
        parent::__construct($message, $code);
    }
}