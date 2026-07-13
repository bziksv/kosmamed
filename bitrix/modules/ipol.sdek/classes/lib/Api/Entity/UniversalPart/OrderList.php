<?php
namespace Ipolh\SDEK\Api\Entity\UniversalPart;

use Ipolh\SDEK\Api\Entity\AbstractCollection;

/**
 * Class OrderList
 * @package Ipolh\SDEK\Api
 * @subpackage Entity\UniversalPart
 * @method Order getFirst()
 * @method Order getNext()
 */
class OrderList extends AbstractCollection
{
    protected $Orders;

    public function __construct()
    {
        parent::__construct('Orders');
    }
}