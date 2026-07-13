<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\DeliveryPoints;

use Ipolh\SDEK\Api\Entity\AbstractCollection;

/**
 * Class WorkTimeList
 * @package Ipolh\SDEK\Api\Entity\Response
 * @method WorkTime getFirst
 * @method WorkTime getNext
 * @method WorkTime getLast
 */
class WorkTimeList extends AbstractCollection
{
    protected $WorkTimes;

    public function __construct()
    {
        parent::__construct('WorkTimes');
    }
}