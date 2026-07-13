<?php
namespace Ipolh\SDEK\Api\Entity\UniversalPart;

use Ipolh\SDEK\Api\Entity\AbstractEntity;

/**
 * Class CdekLocation
 * @package Ipolh\SDEK\Api\Entity\UniversalPart
 */
class CdekLocation extends AbstractEntity
{
    /**
     * @var int
     */
    protected $code;
    /**
     * @var null|string
     */
    protected $fias_guid;
    /**
     * @var null|string
     */
    protected $postal_code;
    /**
     * @var null|float
     */
    protected $longitude;
    /**
     * @var null|float
     */
    protected $latitude;
    /**
     * @var null|string
     */
    protected $country_code;
    /**
     * @var null|string
     */
    protected $region;
    /**
     * @var null|int
     * TODO: looks unuseful cause not found in API documentation
     */
    protected $region_code;
    /**
     * @var null|string
     */
    protected $sub_region;
    /**
     * @var null|string
     */
    protected $city;
    /**
     * @var null|string
     */
    protected $kladr_code;
    /**
     * @var null|string
     */
    protected $address;

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $code
     * @return CdekLocation
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFiasGuid()
    {
        return $this->fias_guid;
    }

    /**
     * @param string|null $fias_guid
     * @return CdekLocation
     */
    public function setFiasGuid($fias_guid)
    {
        $this->fias_guid = $fias_guid;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPostalCode()
    {
        return $this->postal_code;
    }

    /**
     * @param string|null $postal_code
     * @return CdekLocation
     */
    public function setPostalCode($postal_code)
    {
        $this->postal_code = $postal_code;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param float|null $longitude
     * @return CdekLocation
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param float|null $latitude
     * @return CdekLocation
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCountryCode()
    {
        return $this->country_code;
    }

    /**
     * @param string|null $country_code
     * @return CdekLocation
     */
    public function setCountryCode($country_code)
    {
        $this->country_code = $country_code;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param string|null $region
     * @return CdekLocation
     */
    public function setRegion($region)
    {
        $this->region = $region;
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
     * @return CdekLocation
     */
    public function setRegionCode($region_code)
    {
        $this->region_code = $region_code;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSubRegion()
    {
        return $this->sub_region;
    }

    /**
     * @param string|null $sub_region
     * @return CdekLocation
     */
    public function setSubRegion($sub_region)
    {
        $this->sub_region = $sub_region;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     * @return CdekLocation
     */
    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getKladrCode()
    {
        return $this->kladr_code;
    }

    /**
     * @param string|null $kladr_code
     * @return CdekLocation
     */
    public function setKladrCode($kladr_code)
    {
        $this->kladr_code = $kladr_code;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     * @return CdekLocation
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

}