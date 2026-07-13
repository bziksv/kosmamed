<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\DeliveryPoints;

use Ipolh\SDEK\Api\Entity\AbstractEntity;
use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;

/**
 * Class Location
 * @package Ipolh\SDEK\Api\Entity\Response\Part\DeliveryPoints
 */
class Location extends AbstractEntity
{
    use AbstractResponsePart;

    /**
     * @var string(2)
     */
    protected $country_code;
    /**
     * @var integer
     */
    protected $region_code;
    /**
     * @var string(50)
     */
    protected $region;
    /**
     * @var integer
     */
    protected $city_code;
    /**
     * @var string(50)
     */
    protected $city;
    /**
     * @var string|null
     */
    protected $fias_guid;
    /**
     * @var string(6)
     */
    protected $postal_code;
    /**
     * @var float
     */
    protected $longitude;
    /**
     * @var float
     */
    protected $latitude;
    /**
     * @var string(255)
     */
    protected $address;
    /**
     * @var string(255)
     */
    protected $address_full;

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->country_code;
    }

    /**
     * @param string $country_code
     * @return Location
     */
    public function setCountryCode($country_code)
    {
        $this->country_code = $country_code;
        return $this;
    }

    /**
     * @return int
     */
    public function getRegionCode()
    {
        return $this->region_code;
    }

    /**
     * @param int $region_code
     * @return Location
     */
    public function setRegionCode($region_code)
    {
        $this->region_code = $region_code;
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
     * @return Location
     */
    public function setRegion($region)
    {
        $this->region = $region;
        return $this;
    }

    /**
     * @return int
     */
    public function getCityCode()
    {
        return $this->city_code;
    }

    /**
     * @param int $city_code
     * @return Location
     */
    public function setCityCode($city_code)
    {
        $this->city_code = $city_code;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return Location
     */
    public function setCity($city)
    {
        $this->city = $city;
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
     * @return Location
     */
    public function setFiasGuid($fias_guid)
    {
        $this->fias_guid = $fias_guid;
        return $this;
    }

    /**
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postal_code;
    }

    /**
     * @param string $postal_code
     * @return Location
     */
    public function setPostalCode($postal_code)
    {
        $this->postal_code = $postal_code;
        return $this;
    }

    /**
     * @return float
     */
    public function getLongitude()
    {
        return $this->longitude;
    }

    /**
     * @param float $longitude
     * @return Location
     */
    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
        return $this;
    }

    /**
     * @return float
     */
    public function getLatitude()
    {
        return $this->latitude;
    }

    /**
     * @param float $latitude
     * @return Location
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string $address
     * @return Location
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

    /**
     * @return string
     */
    public function getAddressFull()
    {
        return $this->address_full;
    }

    /**
     * @param string $address_full
     * @return Location
     */
    public function setAddressFull($address_full)
    {
        $this->address_full = $address_full;
        return $this;
    }
}