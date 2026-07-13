<?php
namespace Ipolh\SDEK\Api;

use Exception;
use Ipolh\SDEK\Api\Adapter\ResponseHeadersTrait;

class BadResponseException extends Exception
{
    use ResponseHeadersTrait;

    public function __construct($message = "", $code = 0, array $responseHeaders = [])
    {
        parent::__construct($message, $code);
        $this->setHeaders($responseHeaders);
    }
}