<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\DeliveryPoints;

use Ipolh\SDEK\Api\Entity\AbstractCollection;

/**
 * Class DeliveryPointList
 * @package Ipolh\SDEK\Api\Entity\Response
 * @method DeliveryPoint getFirst
 * @method DeliveryPoint getNext
 * @method DeliveryPoint getLast
 */
class DeliveryPointList extends AbstractCollection
{
    protected $DeliveryPoints;

    public function __construct()
    {
        parent::__construct('DeliveryPoints');
    }
}