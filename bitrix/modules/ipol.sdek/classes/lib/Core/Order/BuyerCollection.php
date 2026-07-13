<?php


namespace Ipolh\SDEK\Core\Order;


use Ipolh\SDEK\Core\Entity\Collection;

/**
 * Class BuyerCollection
 * @package Ipolh\SDEK\Core
 * @subpackage Order
 * @method false|Buyer getFirst
 * @method false|Buyer getNext
 * @method false|Buyer getLast
 */
class BuyerCollection extends Collection
{
    /**
     * @var array
     */
    protected $receivers;

    /**
     * BuyerCollection constructor.
     */
    public function __construct()
    {
        parent::__construct('buyers');
    }

}