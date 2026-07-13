<?php


namespace Ipolh\SDEK\Core\Order;


use Ipolh\SDEK\Core\Entity\Collection;

/**
 * Class OrderCollection
 * @package Ipolh\SDEK\Core
 * @subpackage Order
 * @method false|Order getFirst
 * @method false|Order getNext
 * @method false|Order getLast
 */
class OrderCollection extends Collection
{
    /**
     * @var array
     */
    protected $orders;

    /**
     * OrderCollection constructor.
     */
    public function __construct()
    {
        parent::__construct('orders');
    }

}