<?php


namespace Ipolh\SDEK\SDEK\Entity;


use Exception;
use Ipolh\SDEK\Api\Entity\Response\AbstractResponse;

class AbstractResult
{
    /**
     * @var bool
     * Was request successfully finished?
     */
    protected $success = false;
    /**
     * @var AbstractResponse|mixed|null
     * Stores API-answer
     */
    protected $response;
    /**
     * @var Exception|mixed|null
     * Exception object, or object, that extend Exception (AppLevelException etc)
     */
    protected $error;

    public function __construct()
    {
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
     * @return Exception|null
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param Exception|mixed $error
     * @return $this
     */
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * @return bool true, if result considered unsuccessful due to errors
     */
    public function isError()
    {
        if (!$this->isSuccess() && $this->error) {
            return true;
        }
        return false; //no errors, or they were not critical
    }

    /**
     * Method can be used in non-abstract Result objects,
     * to manipulate field values after successfully receiving response
     */
    public function parseFields()
    {
    }

}