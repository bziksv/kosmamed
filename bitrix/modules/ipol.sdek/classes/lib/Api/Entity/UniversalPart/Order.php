<?php
namespace Ipolh\SDEK\Api\Entity\UniversalPart;

use Ipolh\SDEK\Api\Entity\AbstractEntity;

/**
 * Class Order
 * @package Ipolh\SDEK\Api\Entity\UniversalPart
 */
class Order extends AbstractEntity
{
    /**
     * @var string|null UUID
     */
    protected $order_uuid;

    /**
     * @var integer|null
     */
    protected $cdek_number;

    /**
     * @return string|null
     */
    public function getOrderUuid()
    {
        return $this->order_uuid;
    }

    /**
     * @param string|null $order_uuid
     * @return Order
     */
    public function setOrderUuid($order_uuid)
    {
        $this->order_uuid = $order_uuid;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCdekNumber()
    {
        return $this->cdek_number;
    }

    /**
     * @param int|null $cdek_number
     * @return Order
     */
    public function setCdekNumber($cdek_number)
    {
        $this->cdek_number = $cdek_number;
        return $this;
    }
}