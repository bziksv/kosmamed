<?php
namespace Ipolh\SDEK\Core\Entity\Result;

/**
 * Class Message
 * @package Ipolh\SDEK\Core
 * @subpackage Entity
 */
class Message extends Info
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