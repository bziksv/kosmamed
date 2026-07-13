<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\LocationCities;

use Ipolh\SDEK\Api\Entity\AbstractCollection;

/**
 * Class LocationCityList
 * @package Ipolh\SDEK\Api\Entity\Response
 * @method LocationCity getFirst
 * @method LocationCity getNext
 * @method LocationCity getLast
 */
class LocationCityList extends AbstractCollection
{
    protected $LocationCities;

    public function __construct()
    {
        parent::__construct('LocationCities');
        $this->setChildClass(LocationCity::class);
    }
}