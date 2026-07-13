<?php


namespace Ipolh\SDEK\Core\Order;


use Ipolh\SDEK\Core\Entity\Collection;

/**
 * Class ReceiverCollection
 * @package Ipolh\SDEK\Core
 * @subpackage Order
 * @method false|Receiver getFirst
 * @method false|Receiver getNext
 * @method false|Receiver getLast
 */
class ReceiverCollection extends Collection
{
    /**
     * @var array
     */
    protected $receivers;

    /**
     * ReceiverCollection constructor.
     */
    public function __construct()
    {
        parent::__construct('receivers');
    }

}