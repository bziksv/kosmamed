<?php
namespace Ipolh\SDEK\Api\Entity\Request;

/**
 * Class LocationRegions
 * @package Ipolh\SDEK\Api
 * @subpackage Request
 */
class LocationRegions extends AbstractRequest
{
    /**
     * @var string|null ISO_3166-1_alpha-2 codes like 'RU' or 'RU,BY,KZ'
     */
    protected $country_codes;

    /**
     * @var int|null deprecated
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
     * @var int|null required if $page set. CDEK default is 1000
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
     * @return string|null
     */
    public function getCountryCodes()
    {
        return $this->country_codes;
    }

    /**
     * @param string|null $country_codes
     * @return LocationRegions
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
     * @return LocationRegions
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
     * @return LocationRegions
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
     * @return LocationRegions
     */
    public function setFiasRegionGuid($fias_region_guid)
    {
        $this->fias_region_guid = $fias_region_guid;
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
     * @return LocationRegions
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
     * @return LocationRegions
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
     * @return LocationRegions
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
        return $this;
    }
}