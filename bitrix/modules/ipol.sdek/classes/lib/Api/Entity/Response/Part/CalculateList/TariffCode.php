<?php

namespace Ipolh\SDEK\Api\Entity\Response\Part\CalculateList;

use Ipolh\SDEK\Api\Entity\AbstractEntity;
use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;

class TariffCode extends AbstractEntity
{
    use AbstractResponsePart;

    /**
     * @var int
     */
    protected $tariff_code;

    /**
     * @var string
     */
    protected $tariff_name;

    /**
     * @var string
     */
    protected $tariff_description;

    /**
     * @var int
     */
    protected $delivery_mode;

    /**
     * @var float
     */
    protected $delivery_sum;

    /**
     * @var int
     */
    protected $period_min;

    /**
     * @var int
     */
    protected $period_max;

    /**
     * @return int
     */
    public function getTariffCode()
    {
        return $this->tariff_code;
    }

    /**
     * @param int $tariff_code
     * @return TariffCode
     */
    public function setTariffCode($tariff_code)
    {
        $this->tariff_code = $tariff_code;
        return $this;
    }

    /**
     * @return string
     */
    public function getTariffName()
    {
        return $this->tariff_name;
    }

    /**
     * @param string $tariff_name
     * @return TariffCode
     */
    public function setTariffName($tariff_name)
    {
        $this->tariff_name = $tariff_name;
        return $this;
    }

    /**
     * @return string
     */
    public function getTariffDescription()
    {
        return $this->tariff_description;
    }

    /**
     * @param string $tariff_description
     * @return TariffCode
     */
    public function setTariffDescription($tariff_description)
    {
        $this->tariff_description = $tariff_description;
        return $this;
    }

    /**
     * @return int
     */
    public function getDeliveryMode()
    {
        return $this->delivery_mode;
    }

    /**
     * @param int $delivery_mode
     * @return TariffCode
     */
    public function setDeliveryMode($delivery_mode)
    {
        $this->delivery_mode = $delivery_mode;
        return $this;
    }

    /**
     * @return float
     */
    public function getDeliverySum()
    {
        return $this->delivery_sum;
    }

    /**
     * @param float $delivery_sum
     * @return TariffCode
     */
    public function setDeliverySum($delivery_sum)
    {
        $this->delivery_sum = $delivery_sum;
        return $this;
    }

    /**
     * @return int
     */
    public function getPeriodMin()
    {
        return $this->period_min;
    }

    /**
     * @param int $period_min
     * @return TariffCode
     */
    public function setPeriodMin($period_min)
    {
        $this->period_min = $period_min;
        return $this;
    }

    /**
     * @return int
     */
    public function getPeriodMax()
    {
        return $this->period_max;
    }

    /**
     * @param int $period_max
     * @return TariffCode
     */
    public function setPeriodMax($period_max)
    {
        $this->period_max = $period_max;
        return $this;
    }

}