<?php
namespace Ipolh\SDEK\Api\Entity\Request;

use Ipolh\SDEK\Api\Entity\Request\Part\OrderInfoNumberMulti\OrderInfoNumberList;

/**
 * Class OrderInfoNumberMulti
 * @package Ipolh\SDEK\Api
 * @subpackge Request
 */
class OrderInfoNumberMulti extends AbstractRequest
{
    /**
     * Data collection for multi Order Info by CDEK number call, each request similar to OrderInfoNumber
     * @var OrderInfoNumberList
     */
    protected $multiRequests;

    /**
     * @return OrderInfoNumberList
     */
    public function getMultiRequests()
    {
        return $this->multiRequests;
    }

    /**
     * @param OrderInfoNumberList $multiRequests
     * @return OrderInfoNumberMulti
     */
    public function setMultiRequests($multiRequests)
    {
        $this->multiRequests = $multiRequests;
        return $this;
    }
}