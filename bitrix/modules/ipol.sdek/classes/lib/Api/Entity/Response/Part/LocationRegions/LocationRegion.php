<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\LocationRegions;

use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\AbstractEntity;
use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;
use Ipolh\SDEK\Api\Entity\Response\Part\Common\ErrorList;

/**
 * Class LocationRegion
 * @package Ipolh\SDEK\Api\Entity\Response
 */
class LocationRegion extends AbstractEntity
{
    use AbstractResponsePart;

    /**
     * @var string ISO_3166-1_alpha-2
     */
    protected $country_code;

    /**
     * @var string
     */
    protected $country;

    /**
     * @var string
     */
    protected $region;

    /**
     * @var string|null deprecated
     */
    protected $prefix;

    /**
     * @var int|null
     */
    protected $region_code;

    /**
     * @var string|null deprecated
     */
    protected $kladr_region_code;

    /**
     * @var string|null deprecated
     */
    protected $fias_region_guid;

    /**
     * @var ErrorList|null
     */
    protected $errors;

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->country_code;
    }

    /**
     * @param string $country_code
     * @return LocationRegion
     */
    public function setCountryCode($country_code)
    {
        $this->country_code = $country_code;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     * @return LocationRegion
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param string $region
     * @return LocationRegion
     */
    public function setRegion($region)
    {
        $this->region = $region;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * @param string|null $prefix
     * @return LocationRegion
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getRegionCode()
    {
        return $this->region_code;
    }

    /**
     * @param int|null $region_code
     * @return LocationRegion
     */
    public function setRegionCode($region_code)
    {
        $this->region_code = $region_code;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getKladrRegionCode()
    {
        return $this->kladr_region_code;
    }

    /**
     * @param string|null $kladr_region_code
     * @return LocationRegion
     */
    public function setKladrRegionCode($kladr_region_code)
    {
        $this->kladr_region_code = $kladr_region_code;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFiasRegionGuid()
    {
        return $this->fias_region_guid;
    }

    /**
     * @param string|null $fias_region_guid
     * @return LocationRegion
     */
    public function setFiasRegionGuid($fias_region_guid)
    {
        $this->fias_region_guid = $fias_region_guid;
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
     * @param array $errors
     * @return LocationRegion
     * @throws BadResponseException
     */
    public function setErrors($errors)
    {
        $collection = new ErrorList();
        $this->errors = $collection->fillFromArray($errors);
        return $this;
    }
}
