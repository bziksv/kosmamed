<?php
namespace Ipolh\SDEK\Api;

use Exception;
use Ipolh\SDEK\Api\Adapter\ResponseHeadersTrait;

/**
 * Class ApiLevelException
 * @package Ipolh\SDEK\Api
 */
class ApiLevelException extends Exception
{
    use ResponseHeadersTrait;

    /**
     * @var string
     */
    protected $url;
    /**
     * @var array|string|null
     */
    protected $request;
    /**
     * @var string
     */
    protected $answer;

    /**
     * ApiLevelException constructor.
     * @param string $message
     * @param int $code
     * @param string $url
     * @param string|array|null $request
     * @param string $answer
     * @param array $responseHeaders
     */
    public function __construct($message,
                                $code = 0,
                                $url = "",
                                $request = null,
                                $answer = '',
                                array $responseHeaders = []
    ) {
        parent::__construct($message, $code);
        $this->url = $url;
        $this->request = $request;
        $this->answer = $answer;
        $this->setHeaders($responseHeaders);
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return array|string|null - sent request data (or string description of it's origin instead)
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @return string - may be empty
     */
    public function getAnswer()
    {
        return $this->answer;
    }
}