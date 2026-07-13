<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\Common;

use Ipolh\SDEK\Api\Entity\AbstractEntity;
use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;

/**
 * Class Error
 * @package Ipolh\SDEK\Api\Entity\Response\Part\Common
 */
class Error extends AbstractEntity
{
    use AbstractResponsePart;

    /**
     * @var string
     */
    protected $code;
    /**
     * @var string
     */
    protected $message;

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Error
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param string $message
     * @return Error
     */
    public function setMessage($message)
    {
        $this->message = $message;
        return $this;
    }

}