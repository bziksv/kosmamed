<?php
namespace Ipolh\SDEK\Api\Entity\UniversalPart;

use Ipolh\SDEK\Api\Entity\AbstractEntity;

/**
 * Class Item
 * @package Ipolh\SDEK\Api\Entity\UniversalPart
 */
class Item extends AbstractEntity
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $ware_key;

    /**
     * @var Money
     */
    protected $payment;

    /**
     * @var float
     */
    protected $cost;

    /**
     * @var integer
     */
    protected $weight;

    /**
     * @var integer|null
     */
    protected $weight_gross;

    /**
     * @var integer
     */
    protected $amount;

    /**
     * @var string|null
     */
    protected $name_i18n;

    /**
     * @var string|null
     */
    protected $brand;

    /**
     * @var string|null
     */
    protected $country_code;

    /**
     * @var string|null
     */
    protected $material;

    /**
     * @var bool|null
     */
    protected $wifi_gsm;

    /**
     * @var string|null
     */
    protected $url;

    /**
     * @var string|null
     */
    protected $feacn_code;

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Item
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string
     */
    public function getWareKey()
    {
        return $this->ware_key;
    }

    /**
     * @param string $ware_key
     * @return Item
     */
    public function setWareKey($ware_key)
    {
        $this->ware_key = $ware_key;
        return $this;
    }

    /**
     * @return Money
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param array $payment
     * @return Item
     */
    public function setPayment($payment)
    {
        $money = new Money();
        $money->setFields($payment);
        $this->payment = $money;
        return $this;
    }

    /**
     * @param Money $payment
     * @return Item
     */
    public function setPaymentFromObject($payment)
    {
        $this->payment = $payment;
        return $this;
    }

    /**
     * @return float
     */
    public function getCost()
    {
        return $this->cost;
    }

    /**
     * @param float $cost
     * @return Item
     */
    public function setCost($cost)
    {
        $this->cost = $cost;
        return $this;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param int $weight
     * @return Item
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getWeightGross()
    {
        return $this->weight_gross;
    }

    /**
     * @param int|null $weight_gross
     * @return Item
     */
    public function setWeightGross($weight_gross)
    {
        $this->weight_gross = $weight_gross;
        return $this;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     * @return Item
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNameI18n()
    {
        return $this->name_i18n;
    }

    /**
     * @param string|null $name_i18n
     * @return Item
     */
    public function setNameI18n($name_i18n)
    {
        $this->name_i18n = $name_i18n;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getBrand()
    {
        return $this->brand;
    }

    /**
     * @param string|null $brand
     * @return Item
     */
    public function setBrand($brand)
    {
        $this->brand = $brand;
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
     * @return Item
     */
    public function setCountryCode($country_code)
    {
        $this->country_code = $country_code;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getMaterial()
    {
        return $this->material;
    }

    /**
     * @param string|null $material
     * @return Item
     */
    public function setMaterial($material)
    {
        $this->material = $material;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isWifiGsm()
    {
        return $this->wifi_gsm;
    }

    /**
     * @param bool|null $wifi_gsm
     * @return Item
     */
    public function setWifiGsm($wifi_gsm)
    {
        $this->wifi_gsm = $wifi_gsm;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string|null $url
     * @return Item
     */
    public function setUrl($url)
    {
        $this->url = $url;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getFeacnCode()
    {
        return $this->feacn_code;
    }

    /**
     * @param string|null $feacn_code
     * @return Item
     */
    public function setFeacnCode($feacn_code)
    {
        $this->feacn_code = $feacn_code;
        return $this;
    }
}