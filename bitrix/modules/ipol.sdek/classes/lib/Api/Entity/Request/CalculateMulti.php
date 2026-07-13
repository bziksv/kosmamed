<?php
namespace Ipolh\SDEK\Api\Entity\Request;

use Ipolh\SDEK\Api\Entity\Request\Part\CalculateMulti\CalculateTariffList;

/**
 * Class CalculateMulti
 * @package Ipolh\SDEK\Api
 * @subpackge Request
 */
class CalculateMulti extends AbstractRequest
{
    /**
     * Data collection for multi tariff calculation call, each request similar to CalculateTariff
     * @var CalculateTariffList
     */
    protected $multiRequests;

    /**
     * @return CalculateTariffList
     */
    public function getMultiRequests()
    {
        return $this->multiRequests;
    }

    /**
     * @param CalculateTariffList $multiRequests
     * @return CalculateMulti
     */
    public function setMultiRequests($multiRequests)
    {
        $this->multiRequests = $multiRequests;
        return $this;
    }
}