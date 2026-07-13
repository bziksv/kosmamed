<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\OrderInfoMulti;

use Ipolh\SDEK\Api\Entity\AbstractCollection;

/**
 * Class OrderInfoList
 * @package Ipolh\SDEK\Api\
 * @subpackage Response
 * @method OrderInfo getFirst
 * @method OrderInfo getNext
 * @method OrderInfo getLast
 */
class OrderInfoList extends AbstractCollection
{
    protected $OrderInfos;

    public function __construct()
    {
        parent::__construct('OrderInfos');
    }
}