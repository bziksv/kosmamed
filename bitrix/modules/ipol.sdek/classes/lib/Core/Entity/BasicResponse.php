<?php

namespace Ipolh\SDEK\Core\Entity;

use Exception;
use Ipolh\SDEK\Api\Entity\Response\AbstractResponse;

/**
 * Class BasicResponse
 * @package Ipolh\SDEK\Core
 * @subpackage Entity
 * Helper-object for processing basic server-responses such as pdf-printing etc.
 */
class BasicResponse extends BasicEntity
{
    /**
     * @var bool
     * Was request successfully finished?
     */
    protected $success;
    /**
     * @var mixed
     * Stores API-answer
     */
    protected $response;
    /**
     * @var false|Exception
     * Exception object
     */
    protected $error;
    /**
     * @var int
     * Stores error code
     */
    protected $code;

    public function __construct()
    {
        $this->success  = false;
        $this->error    = false;
        $this->response = false;
        $this->fields   = array();
    }

    /**
     * @return bool
     */
    public function isSuccess()
    {
        return $this->success;
    }

    /**
     * @param bool $success
     * @return $this
     */
    public function setSuccess($success)
    {
        $this->success = $success;

        return $this;
    }

    /**
     * @return mixed|AbstractResponse
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param mixed|AbstractResponse $response
     * @return $this
     */
    public function setResponse($response)
    {
        $this->response = $response;

        return $this;
    }

    /**
     * @return false|Exception|Collection
     */
    public function getError()
    {
        return $this->error;
    }

    public function isError()
    {
        return (!$this->isSuccess() && $this->getError());
    }

    /**
     * @param mixed $error
     * @return $this
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }
}