<?php
namespace Ipolh\SDEK\Api\Entity\Request;

use Ipolh\SDEK\Api\Entity\UniversalPart\OrderList;

/**
 * Class PrintOrdersMake
 * @package Ipolh\SDEK\Api
 * @subpackge Request
 */
class PrintOrdersMake extends AbstractRequest
{
    /**
     * @var OrderList
     */
    protected $orders;

    /**
     * @var int|null CDEK default 2
     */
    protected $copy_count;

    /**
     * @var string|null 'tpl_china' | 'tpl_armenia'
     */
    protected $type;

    /**
     * @return OrderList
     */
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * @param OrderList $orders
     * @return PrintOrdersMake
     */
    public function setOrders($orders)
    {
        $this->orders = $orders;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCopyCount()
    {
        return $this->copy_count;
    }

    /**
     * @param int|null $copy_count
     * @return PrintOrdersMake
     */
    public function setCopyCount($copy_count)
    {
        $this->copy_count = $copy_count;
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
     * @return PrintOrdersMake
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }
}