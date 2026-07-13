<?php

namespace Viamodo\Telegramsalenotify;

use Bitrix\Main\Web\Json;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\ArgumentNullException;

class Request
{

    protected $url = 'https://api.telegram.org/bot';

    public function __construct(array $options = [])
    {
        $this->token = $options['token'];
        if (empty($this->token)) {
            $this->token = Option::get('viamodo.telegramsalenotify', 'token', '');
        }

        if (empty($this->token)) {
            throw new ArgumentNullException('token');
        }

        $this->httpClient = new HttpClient();
    }

    public function getToken(): string
    {
        return $this->token;
    }

    public function getApiUrl(): string
    {
        return $this->url;
    }

    public function setCommand(string $command): Request
    {
        $this->command = $command;
        return $this;
    }

    public function getCommand(): string
    {
        return $this->command;
    }

    public function setData(array $data): Request
    {
        $this->data = $data;
        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function exec(): array
    {
        $response = $this->httpClient->post(
            $this->getUrl(),
            $this->getData()
        );

        return Json::decode($response);
    }

    public function getUrl(): string
    {
        return $this->getApiUrl() . $this->getToken() . '/' . $this->getCommand();
    }

}
