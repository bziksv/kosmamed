<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\CalculateTariff;

use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\AbstractEntity;
use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;
use Ipolh\SDEK\Api\Entity\Response\Part\Common\ErrorList;

class Tariff extends AbstractEntity
{
    use AbstractResponsePart;

    /**
     * @var float|null
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
     * @var int gram - calculated weight
     */
    protected $weight_calc;

    /**
     * @var ServiceList|null
     */
    protected $services;

    /**
     * @var float delivery price with services included
     */
    protected $total_sum;

    /**
     * @var string
     */
    protected $currency;

    /**
     * @var ErrorList|null
     */
    protected $errors;

    /**
     * @return float|null
     */
    public function getDeliverySum()
    {
        return $this->delivery_sum;
    }

    /**
     * @param float|null $delivery_sum
     * @return Tariff
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
     * @return Tariff
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
     * @return Tariff
     */
    public function setPeriodMax($period_max)
    {
        $this->period_max = $period_max;
        return $this;
    }

    /**
     * @return int
     */
    public function getWeightCalc()
    {
        return $this->weight_calc;
    }

    /**
     * @param int $weight_calc
     * @return Tariff
     */
    public function setWeightCalc($weight_calc)
    {
        $this->weight_calc = $weight_calc;
        return $this;
    }

    /**
     * @return ServiceList|null
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @param array $array
     * @return Tariff
     * @throws BadResponseException
     */
    public function setServices($array)
    {
        $collection = new ServiceList();
        $this->services = $collection->fillFromArray($array);
        return $this;
    }

    /**
     * @return float
     */
    public function getTotalSum()
    {
        return $this->total_sum;
    }

    /**
     * @param float $total_sum
     * @return Tariff
     */
    public function setTotalSum($total_sum)
    {
        $this->total_sum = $total_sum;
        return $this;
    }

    /**
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param string $currency
     * @return Tariff
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return ErrorList|null
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param array $array
     * @return Tariff
     * @throws BadResponseException
     */
    public function setErrors($array)
    {
        $collection = new ErrorList();
        $this->errors = $collection->fillFromArray($array);
        return $this;
    }
}