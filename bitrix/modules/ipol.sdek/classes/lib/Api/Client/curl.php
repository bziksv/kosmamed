<?php
namespace Ipolh\SDEK\Api\Client;

use Exception;

/**
 * Class curl
 * @package Ipolh\SDEK\Api\Client
 */
class curl
{
    /**
     * cURL handle
     * @var null|resource
     */
    private $client;

    /**
     * @var mixed
     */
    private $answer;

    /**
     * @var null|int
     */
    private $code;

    /**
     * @var string
     */
    private $url = '';

    /**
     * @var array
     */
    private $config = [];

    /**
     * @var int
     */
    private $curlErrNum = 0;

    /**
     * @var array
     */
    private $arrResponseHeaders = [];

    /**
     * curl constructor.
     * @param string $url
     * @param array $config
     * @throws Exception
     */
    public function __construct($url = false, array $config = [])
    {
        if (!function_exists('curl_init')) {
            throw new Exception('No CURL library');
        }
        $this->client = curl_init();
        if ($url) {
            $this->setUrl($url);
        }
        if ($config) {
            $this->config($config);
        }
    }

    /**
     * @param string $data
     * @return $this
     */
    public function post($data = '')
    {
        $this->setOpt(CURLOPT_POST, TRUE);
        if($data) {
            $this->setOpt(CURLOPT_POSTFIELDS, $data);
        }
        curl_setopt($this->client, CURLOPT_HEADERFUNCTION, [$this, 'responseHeaderParser']);
        $this->request();
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function get(array $data = [])
    {
        if ($data) {
            if (strpos($this->url, '?') !== false) {
                $this->url = substr($this->url, 0, strpos($this->url, '?'));
            }
            $this->url .= '?' . http_build_query($data);
        }
        $this->request();

        return $this;
    }

    /**
     * @param string $data
     * @return $this
     */
    public function put($data = '')
    {
        curl_setopt($this->client,CURLOPT_CUSTOMREQUEST, 'PUT');
        if($data) {
            $this->setOpt(CURLOPT_POSTFIELDS, $data);
        }
        $this->request();

        return $this;
    }

    /**
     * @return $this
     */
    public function delete()
    {
        $this->setOpt(CURLOPT_CUSTOMREQUEST, "DELETE");

        $this->request();

        return $this;
    }

    /**
     * @param array $args
     * @return $this
     */
    public function config(array $args)
    {
        $this->config = $args;
        curl_setopt_array($this->client, $args);

        return $this;
    }

    /**
     * @param int $opt
     * @param mixed $val
     * @return $this
     */
    public function setOpt($opt, $val)
    {
        curl_setopt($this->client, $opt, $val);

        return $this;
    }

    /**
     * @return int|null
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return mixed
     */
    public function getAnswer()
    {
        return $this->answer;
    }

    /**
     * @return array
     */
    public function getRequest()
    {
        return array('code' => $this->getCode(), 'answer' => $this->getAnswer());
    }

    /**
     * @param string $url
     * @return $this
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @return $this
     */
    public function flee()
    {
        if ($this->client) {
            curl_close($this->client);
        }
        return $this;
    }

    /**
     * @param bool $close
     * @return $this
     */
    private function request($close = true)
    {
        $this->setOpt(CURLOPT_URL, $this->url);
        $this->answer = curl_exec($this->client);
        $this->code = curl_getinfo($this->client, CURLINFO_HTTP_CODE);
        if ($this->code === 0) {
            $this->curlErrNum = curl_errno($this->client);
        }
        if ($close) {
            $this->flee();
        }
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function getMulti(array $data)
    {
        $curlHandles = [];
        $codes       = [];
        $results     = [];

        if (!is_array($data)) {
            throw new Exception('Unknown data type given for cURL multi GET request');
        }

        $nodes = count($data);
        if ($nodes < 1) {
            throw new Exception('Empty data array given for cURL multi GET request');
        }

        $curlMulti = curl_multi_init();

        for ($i = 0; $i < $nodes; $i++) {
            $url = $this->url.(empty($data[$i]) ? '' : '?'.http_build_query($data[$i]));

            $curlHandles[$i] = curl_init($url);

            if (!empty($this->config)) {
                curl_setopt_array($curlHandles[$i], $this->config);
            }
            curl_setopt($curlHandles[$i], CURLOPT_URL, $url);
            curl_setopt($curlHandles[$i], CURLOPT_RETURNTRANSFER, true);
            if (!empty($data[$i])) {
                $results[$i]['request'] = $data[$i];
            }

            curl_multi_add_handle($curlMulti, $curlHandles[$i]);
        }

        $running = null;
        do {
            curl_multi_exec($curlMulti, $running);
            /*
            $i = 0;
            while (false !== ($info = curl_multi_info_read($curlMulti))) {
                $codes[$i] = curl_getinfo($info['handle'], CURLINFO_HTTP_CODE);
                $i++;
            }
            */
        } while ($running);

        for ($i = 0; $i < $nodes; $i++) {
            $results[$i]['response'] = curl_multi_getcontent($curlHandles[$i]);
            $results[$i]['code']     = curl_getinfo($curlHandles[$i], CURLINFO_HTTP_CODE);
            curl_multi_remove_handle($curlMulti, $curlHandles[$i]);

            $codes[] = $results[$i]['code'];
        }
        curl_multi_close($curlMulti);

        $this->answer = $results;

        // HTTP status magic crutches
        $codes = array_unique($codes);
        if (in_array('200', $codes)) {
            // At least one request successful
            $this->code = 200;
        } else {
            // Returns da lower existing HTTP status
            sort($codes);
            $this->code = $codes[0];
        }

        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function postMulti(array $data)
    {
        $curlHandles = [];
        $codes       = [];
        $results     = [];

        if (!is_array($data)) {
            throw new Exception('Unknown data type given for cURL multi POST request');
        }

        $nodes = count($data);
        if ($nodes < 1) {
            throw new Exception('Empty data array given for cURL multi POST request');
        }

        $curlMulti = curl_multi_init();

        for ($i = 0; $i < $nodes; $i++) {
            $curlHandles[$i] = curl_init($this->url);

            if (!empty($this->config)) {
                curl_setopt_array($curlHandles[$i], $this->config);
            }
            curl_setopt($curlHandles[$i], CURLOPT_URL, $this->url);
            curl_setopt($curlHandles[$i], CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curlHandles[$i], CURLOPT_POST, true);
            if (!empty($data[$i])) {
                $json = json_encode($data[$i], JSON_UNESCAPED_UNICODE);
                curl_setopt($curlHandles[$i], CURLOPT_POSTFIELDS, $json);
                $results[$i]['request'] = $json;
            }

            curl_multi_add_handle($curlMulti, $curlHandles[$i]);
        }

        $running = null;
        do {
            curl_multi_exec($curlMulti, $running);
            /*
            $i = 0;
            while (false !== ($info = curl_multi_info_read($curlMulti))) {
                $codes[$i] = curl_getinfo($info['handle'], CURLINFO_HTTP_CODE);
                $i++;
            }
            */
        } while ($running);

        for ($i = 0; $i < $nodes; $i++) {
            $results[$i]['response'] = curl_multi_getcontent($curlHandles[$i]);
            $results[$i]['code']     = curl_getinfo($curlHandles[$i], CURLINFO_HTTP_CODE);
            curl_multi_remove_handle($curlMulti, $curlHandles[$i]);

            $codes[] = $results[$i]['code'];
        }
        curl_multi_close($curlMulti);

        $this->answer = $results;

        // HTTP status magic crutches
        $codes = array_unique($codes);
        if (in_array('200', $codes)) {
            // At least one request successful
            $this->code = 200;
        } else {
            // Returns da lower existing HTTP status
            sort($codes);
            $this->code = $codes[0];
        }

        return $this;
    }

    private function responseHeaderParser($curl, $header)
    {// this function is called by curl for each header received
        $len = strlen($header);
        $header = explode(':', $header, 2);
        if (count($header) < 2) { // ignore invalid headers
            return $len;
        }

        $this->arrResponseHeaders[strtolower(trim($header[0]))][] = trim($header[1]);
        return $len;
    }

    /**
     * @return int
     */
    public function getCurlErrNum()
    {
        return $this->curlErrNum;
    }

    /**
     * @return array
     */
    public function getArrResponseHeaders()
    {
        return $this->arrResponseHeaders;
    }
}