<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\LocationCities;

use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\AbstractEntity;
use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;
use Ipolh\SDEK\Api\Entity\Response\Part\Common\ErrorList;

/**
 * Class LocationCity
 * @package Ipolh\SDEK\Api\Entity\Response
 */
class LocationCity extends AbstractEntity
{
    use AbstractResponsePart;

    /**
     * @var int CDEK city code
     */
    protected $code;

    /**
     * @var string
     */
    protected $city;

    /**
     * @var string|null
     */
    protected $fias_guid;

    /**
     * @var string|null deprecated
     */
    protected $kladr_code;

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
     * @var int|null
     */
    protected $region_code;

    /**
     * @var string|null deprecated
     */
    protected $fias_region_guid;

    /**
     * @var string|null deprecated
     */
    protected $kladr_region_code;

    /**
     * @var string|null
     */
    protected $sub_region;

    /**
     * @var string[]|null
     */
    protected $postal_codes;

    /**
     * @var float|null
     */
    protected $longitude;

    /**
     * @var float|null
     */
    protected $latitude;

    /**
     * @var string|null
     */
    protected $time_zone;

    /**
     * @var float|null deprecated, lol
     */
    protected $payment_limit;

    /**
     * @var ErrorList|null
     */
    protected $errors;

    /**
     * @return int
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $code
     * @return LocationCity
     */
    public function setCode($code)
    {
        $this->code = $code;
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
     * @return LocationCity
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
     * @return LocationCity
     */
    public function setFiasGuid($fias_guid)
    {
        $this->fias_guid = $fias_guid;
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
     * @return LocationCity
     */
    public function setKladrCode($kladr_code)
    {
        $this->kladr_code = $kladr_code;
        return $this;
    }

    /**
     * @return string
     */
    public function getCountryCode()
    {
        return $this->country_code;
    }

    /**
     * @param string $country_code
     * @return LocationCity
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
     * @return LocationCity
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
     * @return LocationCity
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
     * @return LocationCity
     */
    public function setRegionCode($region_code)
    {
        $this->region_code = $region_code;
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
     * @return LocationCity
     */
    public function setFiasRegionGuid($fias_region_guid)
    {
        $this->fias_region_guid = $fias_region_guid;
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
     * @return LocationCity
     */
    public function setKladrRegionCode($kladr_region_code)
    {
        $this->kladr_region_code = $kladr_region_code;
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
     * @return LocationCity
     */
    public function setSubRegion($sub_region)
    {
        $this->sub_region = $sub_region;
        return $this;
    }

    /**
     * @return string[]|null
     */
    public function getPostalCodes()
    {
        return $this->postal_codes;
    }

    /**
     * @param string[]|null $postal_codes
     * @return LocationCity
     */
    public function setPostalCodes($postal_codes)
    {
        $this->postal_codes = $postal_codes;
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
     * @return LocationCity
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
     * @return LocationCity
     */
    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTimeZone()
    {
        return $this->time_zone;
    }

    /**
     * @param string|null $time_zone
     * @return LocationCity
     */
    public function setTimeZone($time_zone)
    {
        $this->time_zone = $time_zone;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getPaymentLimit()
    {
        return $this->payment_limit;
    }

    /**
     * @param float|null $payment_limit
     * @return LocationCity
     */
    public function setPaymentLimit($payment_limit)
    {
        $this->payment_limit = $payment_limit;
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
     * @return LocationCity
     * @throws BadResponseException
     */
    public function setErrors($errors)
    {
        $collection = new ErrorList();
        $this->errors = $collection->fillFromArray($errors);
        return $this;
    }
}