<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\DeliveryPoints;

use Ipolh\SDEK\Api\Entity\AbstractCollection;

/**
 * Class OfficeImageList
 * @package Ipolh\SDEK\Api\Entity\Response
 * @method OfficeImage getFirst
 * @method OfficeImage getNext
 * @method OfficeImage getLast
 */
class OfficeImageList extends AbstractCollection
{
    protected $OfficeImages;

    public function __construct()
    {
        parent::__construct('OfficeImages');
    }
}