<?php
namespace Ipolh\SDEK\Api\Adapter;

use Exception;
use Ipolh\SDEK\Api\ApiLevelException;
use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Client\curl;

/**
 * Class CurlAdapter
 * @package Ipolh\SDEK\Api\Adapter
 */
class CurlAdapter extends AbstractAdapter
{
    /**
     * @var curl
     */
    protected $curl;
    /**
     * @var array
     */
    private $allowedCodeArr;
    /**
     * @var array
     */
    private $validErrorCodeArr;
    /**
     * @var string
     */
    protected $url;
    /**
     * @var string
     */
    protected $requestType;
    /**
     * @var array
     */
    protected $headers = [];
    /**
     * @var string
     */
    protected $contentType = 'Content-Type: application/json; charset=utf-8';
    /**
     * @var string
     */
    protected $method = 'unconfigured_request';

    /**
     * CurlAdapter constructor.
     * @param int $timeout
     * @throws Exception if curl not installed
     */
    public function __construct($timeout = 15)
    {
        parent::__construct();
        $this->allowedCodeArr = ['200', '202', '400', '403', '404', '500'];
        $this->validErrorCodeArr = [];
        $this->curl = new curl(false, array(
            CURLOPT_TIMEOUT_MS     => $timeout * 1000,
        ));
    }

    /**
     * @param array $dataPost
     * @param string $urlImplement
     * @param array $dataGet
     * @return mixed
     * @throws ApiLevelException
     * @throws BadResponseException
     */
    public function form(array $dataPost = [], $urlImplement = "", array $dataGet = [])
    {
        $this->curl->setOpt(CURLOPT_RETURNTRANSFER, true);

        $getStr = (!empty($dataGet)) ? "?" . http_build_query($dataGet) : "";

        $this->curl->setUrl($this->getUrl() . $urlImplement . $getStr);

        $this->log->debug('', [
            'method' => $this->method,
            'process' => 'REQUEST',
            'content' => [
                'URL' => $this->curl->getUrl(),
                'DATA' => $dataPost,
                'FORM' => http_build_query($dataPost, JSON_UNESCAPED_UNICODE)
            ],
        ]);

        $this->applyHeaders()->curl->post(http_build_query($dataPost));

        $this->log->debug('', [
            'method' => $this->method,
            'process' => 'RESPONSE',
            'content' => [
                'CODE' => $this->curl->getCode(),
                'BODY' => $this->curl->getAnswer()
            ],
        ]);

        $this->afterCheck($dataPost);

        return $this->curl->getAnswer();
    }

    /**
     * @param array $dataPost
     * @param string $urlImplement
     * @param array $dataGet
     * @return mixed
     * @throws ApiLevelException
     * @throws BadResponseException
     */
    public function post(array $dataPost = [], $urlImplement = "", array $dataGet = [])
    {
        $this->curl->setOpt(CURLOPT_RETURNTRANSFER, true);

        $getStr = (!empty($dataGet)) ? "?" . http_build_query($dataGet) : "";

        $this->curl->setUrl($this->getUrl() . $urlImplement . $getStr);

        $this->log->debug('', [
            'method' => $this->method,
            'process' => 'REQUEST',
            'content' => [
                'URL' => $this->curl->getUrl(),
                'DATA' => $dataPost,
                'JSON' => json_encode($dataPost, JSON_UNESCAPED_UNICODE)
            ],
        ]);

        $this->applyHeaders()->curl->post(json_encode($dataPost, JSON_UNESCAPED_UNICODE));

        $this->log->debug('', [
            'method' => $this->method,
            'process' => 'RESPONSE',
            'content' => [
                'CODE' => $this->curl->getCode(),
                'BODY' => $this->curl->getAnswer()
            ],
        ]);

        $this->afterCheck($dataPost);

        return $this->curl->getAnswer();
    }

    /**
     * @param string $urlImplement
     * @param array $dataGet
     * @return mixed
     * @throws ApiLevelException
     * @throws BadResponseException
     */
    public function get($urlImplement = "", array $dataGet = [])
    {
        $this->curl->setOpt(CURLOPT_RETURNTRANSFER, true);

        $this->curl->setUrl($this->getUrl() . $urlImplement);

        $getStr = empty($dataGet) ? '' : '?' . http_build_query($dataGet); //only for logging, imitating inner curl.php process

        $this->log->debug('', [
            'method' => $this->method,
            'process' => 'REQUEST',
            'content' => ['URL' => $this->curl->getUrl() . $getStr],
        ]);

        $this->applyHeaders()->curl->get($dataGet);

        $this->log->debug('', [
            'method' => $this->method,
            'process' => 'RESPONSE',
            'content' => [
                'CODE' => $this->curl->getCode(),
                'BODY' => $this->curl->getAnswer()
            ],
        ]);

        $this->afterCheck('get request');

        return $this->curl->getAnswer();
    }

    /**
     * @param array $dataPut
     * @param string $urlImplement
     * @param array $dataGet
     * @return mixed
     * @throws ApiLevelException
     * @throws BadResponseException
     */
    public function put(array $dataPut = [], $urlImplement = "", array $dataGet = [])
    {
        $this->curl->setOpt(CURLOPT_RETURNTRANSFER, true);

        $getStr = (!empty($dataGet)) ? "?" . http_build_query($dataGet) : "";

        $this->curl->setUrl($this->getUrl() . $urlImplement . $getStr);

        $this->log->debug('', [
            'method' => $this->method,
            'process' => 'REQUEST',
            'content' => [
                'URL' => $this->curl->getUrl(),
                'DATA' => $dataPut,
                'JSON' => json_encode($dataPut, JSON_UNESCAPED_UNICODE)
            ],
        ]);

        $this->applyHeaders()->curl->put(json_encode($dataPut, JSON_UNESCAPED_UNICODE));

        $this->log->debug('', [
            'method' => $this->method,
            'process' => 'RESPONSE',
            'content' => [
                'CODE' => $this->curl->getCode(),
                'BODY' => $this->curl->getAnswer()
            ],
        ]);

        $this->afterCheck($dataPut);

        return $this->curl->getAnswer();
    }

    /**
     * @param string $urlImplement
     * @return mixed
     * @throws ApiLevelException
     * @throws BadResponseException
     */
    public function delete($urlImplement = "")
    {
        $this->curl->setOpt(CURLOPT_RETURNTRANSFER, true);

        $this->applyHeaders()->curl->setUrl($this->getUrl() . $urlImplement);

        $this->log->debug('', [
            'method' => $this->method,
            'process' => 'REQUEST',
            'content' => [
                'URL' => $this->curl->getUrl(),
            ]
        ]);

        $this->curl->delete();

        $this->log->debug('', [
            'method' => $this->method,
            'process' => 'RESPONSE',
            'content' => [
                'CODE' => $this->curl->getCode(),
                'BODY' => $this->curl->getAnswer(),
            ],
        ]);

        $this->afterCheck('delete request');

        return $this->curl->getAnswer();
    }

    /**
     * @param string $urlImplement
     * @param array $dataGet
     * @return mixed
     * @throws ApiLevelException
     * @throws BadResponseException
     */
    public function getMulti($urlImplement = "", array $dataGet = [])
    {
        $this->curl->setUrl($this->getUrl().$urlImplement);

        $qs = [];
        if (!empty($dataGet['multiRequests']) && is_array($dataGet['multiRequests'])) {
            foreach ($dataGet['multiRequests'] as $val) {
                $qs[] = empty($val) ? '' : '?'.http_build_query($val);
            }
        }

        $this->log->debug('', [
            'method' => $this->method,
            'process' => 'REQUEST',
            'content' => [
                'URL' => $this->curl->getUrl(),
                'DATA' => $dataGet,
                'QUERY' => $qs
            ],
        ]);

        $this->applyHeaders()->curl->getMulti($dataGet['multiRequests']);

        $this->log->debug('', [
            'method' => $this->method,
            'process' => 'RESPONSE',
            'content' => [
                'CODE' => $this->curl->getCode(),
                'BODY' => $this->curl->getAnswer()
            ],
        ]);

        $this->afterCheck('get multi request');

        return $this->curl->getAnswer();
    }

    /**
     * @param array $dataPost
     * @param string $urlImplement
     * @param array $dataGet
     * @return mixed
     * @throws ApiLevelException
     * @throws BadResponseException
     */
    public function postMulti(array $dataPost, $urlImplement = "", array $dataGet = [])
    {
        $getStr = (!empty($dataGet)) ? "?" . http_build_query($dataGet) : "";

        $this->curl->setUrl($this->getUrl() . $urlImplement . $getStr);

        $jsons = [];
        if (!empty($dataPost['multiRequests']) && is_array($dataPost['multiRequests'])) {
            foreach ($dataPost['multiRequests'] as $val) {
                $jsons[] = json_encode($val, JSON_UNESCAPED_UNICODE);
            }
        }

        $this->log->debug('', [
            'method' => $this->method,
            'process' => 'REQUEST',
            'content' => [
                'URL' => $this->curl->getUrl(),
                'DATA' => $dataPost,
                'JSON' => $jsons
            ],
        ]);

        $this->applyHeaders()->curl->postMulti($dataPost['multiRequests']);

        $this->log->debug('', [
            'method' => $this->method,
            'process' => 'RESPONSE',
            'content' => [
                'CODE' => $this->curl->getCode(),
                'BODY' => $this->curl->getAnswer()
            ],
        ]);

        $this->afterCheck($dataPost);

        return $this->curl->getAnswer();
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return CurlAdapter
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getRequestType()
    {
        return $this->requestType;
    }

    /**
     * @param string $requestType
     * @return CurlAdapter
     */
    public function setRequestType($requestType)
    {
        $this->requestType = $requestType;
        return $this;
    }

    /**
     * @param string $contentType
     * @return $this
     */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
        return $this;
    }

    /**
     * @param array $headers
     * @return CurlAdapter
     */
    public function appendHeaders(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);
        return $this;
    }

    /**
     * @return $this
     */
    protected function applyHeaders()
    {
        $this->appendHeaders([$this->contentType]);
        $this->curl->config([CURLOPT_HTTPHEADER => $this->headers]);
        return $this;
    }

    /**
     * @return curl
     */
    public function getCurl()
    {
        return $this->curl;
    }

    /**
     * @param string $method
     * @return CurlAdapter
     */
    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    /**
     * @param $sentData
     * @throws ApiLevelException
     * @throws BadResponseException
     */
    protected function afterCheck($sentData)
    {
        if ($this->curl->getCurlErrNum() == CURLE_OPERATION_TIMEDOUT) {
            throw new BadResponseException('Connection timed out', $this->curl->getCurlErrNum());
        }
        if (!in_array($this->curl->getCode(), $this->allowedCodeArr)) {
            if (in_array($this->curl->getCode(), $this->validErrorCodeArr)) {
                throw new ApiLevelException('Request error',
                    $this->curl->getCode(),
                    $this->curl->getUrl(),
                    $sentData,
                    $this->curl->getAnswer(),
                    $this->curl->getArrResponseHeaders());
            } else {
                throw new BadResponseException('Bad server answer: ' . $this->curl->getAnswer(),
                    $this->curl->getCode(),
                    $this->curl->getArrResponseHeaders()
                );
            }
        }
    }
}