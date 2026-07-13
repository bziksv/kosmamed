<?php
namespace Ipolh\SDEK\Api\Adapter;

interface AdapterInterface
{
    public function post($method, array $dataPost = []);
}