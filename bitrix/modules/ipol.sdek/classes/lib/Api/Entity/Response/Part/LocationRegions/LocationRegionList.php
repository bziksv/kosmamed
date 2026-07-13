<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\LocationRegions;

use Ipolh\SDEK\Api\Entity\AbstractCollection;

/**
 * Class LocationRegionList
 * @package Ipolh\SDEK\Api\Entity\Response
 * @method LocationRegion getFirst
 * @method LocationRegion getNext
 * @method LocationRegion getLast
 */
class LocationRegionList extends AbstractCollection
{
    protected $LocationRegions;

    public function __construct()
    {
        parent::__construct('LocationRegions');
    }
}