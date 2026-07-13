<?php


namespace Ipolh\SDEK\Core\Order;


use Ipolh\SDEK\Core\Entity\Money;
use Ipolh\SDEK\Core\Entity\FieldsContainer;

/**
 * Class Payment
 * @package Ipolh\SDEK\Core
 * @subpackage Order
 */
class Payment extends FieldsContainer
{

    /**
     * @var Money
     * Payment for delivery
     */
    protected $delivery;
    /**
     * @var Money
     * Payment for order goods
     */
    protected $goods;
    /**
     * @var Money
     * Estimated order cost for insurance
     */
    protected $estimated;
    /**
     * @var Money
     * how many was already payed online, etc
     */
    protected $payed;

    /**
     * @var int|null
     * default goods VAT percentage
     */
    protected $ndsDefault;
    /**
     * @var int|null
     * delivery VAT percentage
     */
    protected $ndsDelivery;

    /**
     * @var string - 'Cash','Card','Bill','other', etc
     * Payment type
     */
    protected $type;

    /**
     * @var bool
     * 1 for cashless, 0 for cash
     */
    protected $isBeznal;

    /**
     * @return Money
     * Complete price check: if payment is Cashless - returns Money(0), else - (delivery cost + goods price - already payed)
     */
    public function getPrice()
    {
        return ($this->getIsBeznal())?
            new Money(0) :
            $this->getNominalPrice();
    }

    /**
     * @return Money
     */
    public function getNominalPrice()
    {
        $tmpSum = Money::sum(array($this->getDelivery(),$this->getGoods()));
        return Money::subtract(array($tmpSum,$this->getPayed()));
    }

    /**
     * @return Money
     */
    public function getCost()
    {
        return ($this->getEstimated())? : $this->getGoods();
    }

    /**
     * @return Money|null - null=not set, Money can be set 0 currency_units (RUB|USD) for reason
     */
    public function getEstimated()
    {
        return $this->estimated;
    }

    /**
     * @param Money|null $estimated
     * @return $this
     */
    public function setEstimated($estimated)
    {
        $this->estimated = $estimated;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsBeznal()
    {
        return $this->isBeznal;
    }

    /**
     * @param bool $isBeznal
     * @return $this
     */
    public function setIsBeznal($isBeznal)
    {
        $this->isBeznal = $isBeznal;

        return $this;
    }

    /**
     * @return Money
     */
    public function getDelivery()
    {
        return ($this->delivery) ? $this->delivery : new Money(0);
    }

    /**
     * @param Money $delivery
     * @return $this
     */
    public function setDelivery($delivery)
    {
        $this->delivery = $delivery;

        return $this;
    }

    /**
     * @return Money
     */
    public function getGoods()
    {
        return ($this->goods) ? $this->goods : new Money(0);
    }

    /**
     * @param Money $goods
     * @return $this
     */
    public function setGoods($goods)
    {
        $this->goods = $goods;

        return $this;
    }

    /**
     * @return Money
     */
    public function getPayed()
    {
        return ($this->payed) ? $this->payed : new Money(0); //TODO bad practice to return money with static currency
    }

    /**
     * @param Money $payed
     * @return $this
     */
    public function setPayed($payed)
    {
        $this->payed = $payed;

        return $this;
    }


    /**
     * @return string
     */
    public function getType()
    {
        return ($this->type) ? : 'other';
    }

    /**
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        $arTypes = array('Cash','Card','Bill','other');

        $this->type = (in_array($type, $arTypes)) ? $type : 'other';

        return $this;
    }

    /**
     * @return int|null
     */
    public function getNdsDefault()
    {
        return $this->ndsDefault;
    }

    /**
     * @param int|null $ndsDefault
     * @return $this
     */
    public function setNdsDefault($ndsDefault)
    {
        $this->ndsDefault = $ndsDefault;

        return $this;
    }

    /**
     * @return int|null
     */
    public function getNdsDelivery()
    {
        return $this->ndsDelivery;
    }

    /**
     * @param int|null $ndsDelivery
     * @return $this
     */
    public function setNdsDelivery($ndsDelivery)
    {
        $this->ndsDelivery = $ndsDelivery;

        return $this;
    }
}