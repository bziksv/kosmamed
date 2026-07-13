<?php
namespace Ipolh\SDEK\Api\Entity\Request;

/**
 * Class LocationCities
 * @package Ipolh\SDEK\Api
 * @subpackage Request
 */
class LocationCities extends AbstractRequest
{
    /**
     * @var string|null ISO_3166-1_alpha-2 codes like 'RU' or 'RU,BY,KZ'
     */
    protected $country_codes;

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
     * @var string|null deprecated
     */
    protected $kladr_code;

    /**
     * @var string|null
     */
    protected $fias_guid;

    /**
     * @var string|null
     */
    protected $postal_code;

    /**
     * @var int|null CDEK city code
     */
    protected $code;

    /**
     * @var string|null
     */
    protected $city;

    /**
     * @var int|null required if $page set. CDEK default is 500
     */
    protected $size;

    /**
     * @var int|null CDEK default is 0
     */
    protected $page;

    /**
     * @var string|null CDEK default is 'rus'
     */
    protected $lang;

    /**
     * @var float|null deprecated. -1 means no limit, 0 means no COD at all, >0 means COD limit lesser when given limit
     */
    protected $payment_limit;

    /**
     * @return string|null
     */
    public function getCountryCodes()
    {
        return $this->country_codes;
    }

    /**
     * @param string|null $country_codes
     * @return LocationCities
     */
    public function setCountryCodes($country_codes)
    {
        $this->country_codes = $country_codes;
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
     * @return LocationCities
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
     * @return LocationCities
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
     * @return LocationCities
     */
    public function setFiasRegionGuid($fias_region_guid)
    {
        $this->fias_region_guid = $fias_region_guid;
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
     * @return LocationCities
     */
    public function setKladrCode($kladr_code)
    {
        $this->kladr_code = $kladr_code;
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
     * @return LocationCities
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
     * @return LocationCities
     */
    public function setPostalCode($postal_code)
    {
        $this->postal_code = $postal_code;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int|null $code
     * @return LocationCities
     */
    public function setCode($code)
    {
        $this->code = $code;
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
     * @return LocationCities
     */
    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param int|null $size
     * @return LocationCities
     */
    public function setSize($size)
    {
        $this->size = $size;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * @param int|null $page
     * @return LocationCities
     */
    public function setPage($page)
    {
        $this->page = $page;
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
     * @return LocationCities
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
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
     * @return LocationCities
     */
    public function setPaymentLimit($payment_limit)
    {
        $this->payment_limit = $payment_limit;
        return $this;
    }
}