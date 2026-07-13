<?php
namespace Ipolh\SDEK\Api\Entity\Request;

use Ipolh\SDEK\Api\Entity\UniversalPart\CdekLocation;
use Ipolh\SDEK\Api\Entity\UniversalPart\PackageList;

/**
 * Class CalculatorList
 * @package Ipolh\SDEK\Api\Entity\Request
 */
class CalculateList extends AbstractRequest
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
     * @var string|null - rus, eng,  for delivery-info in response (rus is default in API)
     */
    protected $lang;

    /**
     * @var CdekLocation
     */
    protected $from_location;

    /**
     * @var CdekLocation
     */
    protected $to_location;

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
     * @return CalculateList
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
     * @return CalculateList
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
     * @return CalculateList
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLang()
    {
        return $this->lang;
    }

    /**
     * @param string|null $lang
     * @return CalculateList
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
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
     * @return CalculateList
     */
    public function setFromLocation(CdekLocation $from_location)
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
     * @return CalculateList
     */
    public function setToLocation(CdekLocation $to_location)
    {
        $this->to_location = $to_location;
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
     * @return CalculateList
     */
    public function setPackages(PackageList $packages)
    {
        $this->packages = $packages;
        return $this;
    }
}