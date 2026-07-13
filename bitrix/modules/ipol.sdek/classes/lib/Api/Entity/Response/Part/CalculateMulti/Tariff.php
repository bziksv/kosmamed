<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\CalculateMulti;

class Tariff extends \Ipolh\SDEK\Api\Entity\Response\Part\CalculateTariff\Tariff
{
    /**
     * @var int
     */
    protected $http_status;

    /**
     * @var int
     */
    protected $tariff_code;

    /**
     * @return int
     */
    public function getHttpStatus()
    {
        return $this->http_status;
    }

    /**
     * @param int $http_status
     * @return Tariff
     */
    public function setHttpStatus($http_status)
    {
        $this->http_status = $http_status;
        return $this;
    }

    /**
     * @return int
     */
    public function getTariffCode()
    {
        return $this->tariff_code;
    }

    /**
     * @param int $tariff_code
     * @return Tariff
     */
    public function setTariffCode($tariff_code)
    {
        $this->tariff_code = $tariff_code;
        return $this;
    }
}