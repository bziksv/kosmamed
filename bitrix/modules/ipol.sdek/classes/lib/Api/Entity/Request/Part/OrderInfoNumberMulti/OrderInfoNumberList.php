<?php
namespace Ipolh\SDEK\Api\Entity\Request\Part\OrderInfoNumberMulti;

use Ipolh\SDEK\Api\Entity\AbstractCollection;

/**
 * Class OrderInfoNumberList
 * @package Ipolh\SDEK\Api
 * @subpackage Request
 * @method OrderInfoNumber getFirst
 * @method OrderInfoNumber getNext
 * @method OrderInfoNumber getLast
 */
class OrderInfoNumberList extends AbstractCollection
{
    protected $OrderInfoNumbers;

    public function __construct()
    {
        parent::__construct('OrderInfoNumbers');
    }
}