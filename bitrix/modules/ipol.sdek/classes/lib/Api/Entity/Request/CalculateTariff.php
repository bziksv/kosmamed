<?php
namespace Ipolh\SDEK\Api\Entity\Request;

use Ipolh\SDEK\Api\Entity\UniversalPart\CdekLocation;
use Ipolh\SDEK\Api\Entity\UniversalPart\PackageList;
use Ipolh\SDEK\Api\Entity\UniversalPart\ServiceList;

/**
 * Class CalculateTariff
 * @package Ipolh\SDEK\Api
 * @subpackge Request
 */
class CalculateTariff extends AbstractRequest
{
    /**
     * @var string|null - date and time of package departure
     */
    protected $date;

    /**
     * @var int|null 1 - E-Shop 2 - regular shipping (1 is default in API)
     */
    protected $type;

    /**
     * @var int|null
     */
    protected $currency;

    /**
     * @var int CDEK tariff number
     */
    protected $tariff_code;

    /**
     * @var CdekLocation
     */
    protected $from_location;

    /**
     * @var CdekLocation
     */
    protected $to_location;

    /**
     * @var ServiceList|null
     */
    protected $services;

    /**
     * @var PackageList
     */
    protected $packages;

    /**
     * @return string|null
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param string|null $date
     * @return CalculateTariff
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int|null $type
     * @return CalculateTariff
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param int|null $currency
     * @return CalculateTariff
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
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
     * @return CalculateTariff
     */
    public function setTariffCode($tariff_code)
    {
        $this->tariff_code = $tariff_code;
        return $this;
    }

    /**
     * @return CdekLocation
     */
    public function getFromLocation()
    {
        return $this->from_location;
    }

    /**
     * @param CdekLocation $from_location
     * @return CalculateTariff
     */
    public function setFromLocation($from_location)
    {
        $this->from_location = $from_location;
        return $this;
    }

    /**
     * @return CdekLocation
     */
    public function getToLocation()
    {
        return $this->to_location;
    }

    /**
     * @param CdekLocation $to_location
     * @return CalculateTariff
     */
    public function setToLocation($to_location)
    {
        $this->to_location = $to_location;
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
     * @param ServiceList|null $services
     * @return CalculateTariff
     */
    public function setServices($services)
    {
        $this->services = $services;
        return $this;
    }

    /**
     * @return PackageList
     */
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * @param PackageList $packages
     * @return CalculateTariff
     */
    public function setPackages($packages)
    {
        $this->packages = $packages;
        return $this;
    }
}