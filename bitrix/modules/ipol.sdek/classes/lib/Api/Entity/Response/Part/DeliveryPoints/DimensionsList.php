<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\DeliveryPoints;

use Ipolh\SDEK\Api\Entity\AbstractCollection;

/**
 * Class DimensionsList
 * @package Ipolh\SDEK\Api\Entity\Response
 * @method Dimensions getFirst
 * @method Dimensions getNext
 * @method Dimensions getLast
 */
class DimensionsList extends AbstractCollection
{
    protected $Dimensions;

    public function __construct()
    {
        parent::__construct('Dimensions');
        $this->setChildClass(Dimensions::class);
    }
}