<?php
namespace Ipolh\SDEK\Api\Entity\Request;

/**
 * Class DeliveryPoints
 * @package Ipolh\SDEK\Api\Entity\Request
 */
class DeliveryPoints extends AbstractRequest
{
    /**
     * @var integer|null
     */
    protected $postal_code;
    /**
     * @var integer|null
     * have priority above postal_code, if both set
     */
    protected $city_code;
    /**
     * @var string|null
     * 'PVZ' | 'POSTAMAT' | 'ALL' default on server is 'ALL'
     */
    protected $type;
    /**
     * @var string|null
     * ISO_3166-1_alpha-2
     */
    protected $country_code;
    /**
     * @var integer|null
     * region code in SDEK database
     */
    protected $region_code;
    /**
     * @var boolean|null
     */
    protected $have_cashless;
    /**
     * @var boolean|null
     */
    protected $have_cash;
    /**
     * @var boolean|null
     */
    protected $allowed_cod;
    /**
     * @var boolean|null
     */
    protected $is_dressing_room;
    /**
     * @var integer|null
     * kg
     */
    protected $weight_max;
    /**
     * @var integer|null
     * kg
     */
    protected $weight_min;
    /**
     * @var string|null
     * 'rus' is default on server
     */
    protected $lang;
    /**
     * @var bool|null
     */
    protected $take_only;
    /**
     * @var bool|null
     */
    protected $is_handout;
    /**
     * @var bool|null
     */
    protected $is_reception;
    /**
     * @var string|null
     */
    protected $fias_guid;

    /**
     * @return int|null
     */
    public function getPostalCode()
    {
        return $this->postal_code;
    }

    /**
     * @param int|null $postal_code
     * @return DeliveryPoints
     */
    public function setPostalCode($postal_code)
    {
        $this->postal_code = $postal_code;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCityCode()
    {
        return $this->city_code;
    }

    /**
     * @param int|null $city_code
     * @return DeliveryPoints
     */
    public function setCityCode($city_code)
    {
        $this->city_code = $city_code;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string|null $type
     * @return DeliveryPoints
     */
    public function setType($type)
    {
        $this->type = $type;
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
     * @return DeliveryPoints
     */
    public function setCountryCode($country_code)
    {
        $this->country_code = $country_code;
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
     * @return DeliveryPoints
     */
    public function setRegionCode($region_code)
    {
        $this->region_code = $region_code;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isHaveCashless()
    {
        return $this->have_cashless;
    }

    /**
     * @param bool|null $have_cashless
     * @return DeliveryPoints
     */
    public function setHaveCashless($have_cashless)
    {
        $this->have_cashless = $have_cashless;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isHaveCash()
    {
        return $this->have_cash;
    }

    /**
     * @param bool|null $have_cash
     * @return DeliveryPoints
     */
    public function setHaveCash($have_cash)
    {
        $this->have_cash = $have_cash;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isAllowedCod()
    {
        return $this->allowed_cod;
    }

    /**
     * @param bool|null $allowed_cod
     * @return DeliveryPoints
     */
    public function setAllowedCod($allowed_cod)
    {
        $this->allowed_cod = $allowed_cod;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isIsDressingRoom()
    {
        return $this->is_dressing_room;
    }

    /**
     * @param bool|null $is_dressing_room
     * @return DeliveryPoints
     */
    public function setIsDressingRoom($is_dressing_room)
    {
        $this->is_dressing_room = $is_dressing_room;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getWeightMax()
    {
        return $this->weight_max;
    }

    /**
     * @param int|null $weight_max
     * @return DeliveryPoints
     */
    public function setWeightMax($weight_max)
    {
        $this->weight_max = $weight_max;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getWeightMin()
    {
        return $this->weight_min;
    }

    /**
     * @param int|null $weight_min
     * @return DeliveryPoints
     */
    public function setWeightMin($weight_min)
    {
        $this->weight_min = $weight_min;
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
     * @return DeliveryPoints
     */
    public function setLang($lang)
    {
        $this->lang = $lang;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getTakeOnly()
    {
        return $this->take_only;
    }

    /**
     * @param bool|null $take_only
     * @return DeliveryPoints
     */
    public function setTakeOnly($take_only)
    {
        $this->take_only = $take_only;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsHandout()
    {
        return $this->is_handout;
    }

    /**
     * @param bool|null $is_handout
     * @return DeliveryPoints
     */
    public function setIsHandout($is_handout)
    {
        $this->is_handout = $is_handout;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getIsReception()
    {
        return $this->is_reception;
    }

    /**
     * @param bool|null $is_reception
     * @return DeliveryPoints
     */
    public function setIsReception($is_reception)
    {
        $this->is_reception = $is_reception;
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
     * @return DeliveryPoints
     */
    public function setFiasGuid($fias_guid)
    {
        $this->fias_guid = $fias_guid;
        return $this;
    }
}