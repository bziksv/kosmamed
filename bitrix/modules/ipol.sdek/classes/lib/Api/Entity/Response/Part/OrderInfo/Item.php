<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo;

/**
 * Class Item
 * @package Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo
 */
class Item extends \Ipolh\SDEK\Api\Entity\UniversalPart\Item
{
    /**
     * @var int|null
     */
    protected $delivery_amount;

    /**
     * @return int|null
     */
    public function getDeliveryAmount()
    {
        return $this->delivery_amount;
    }

    /**
     * @param int|null $delivery_amount
     * @return Item
     */
    public function setDeliveryAmount($delivery_amount)
    {
        $this->delivery_amount = $delivery_amount;
        return $this;
    }
}