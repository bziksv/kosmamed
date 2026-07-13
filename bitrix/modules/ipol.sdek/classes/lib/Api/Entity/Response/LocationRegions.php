<?php
namespace Ipolh\SDEK\Api\Entity\Response;

use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\Response\Part\LocationRegions\LocationRegionList;

/**
 * Class LocationRegions
 * @package Ipolh\SDEK\Api\Entity\Response
 */
class LocationRegions extends AbstractResponse
{
    /**
     * @var LocationRegionList
     */
    protected $regionsList;

    /**
     * @return LocationRegionList
     */
    public function getRegionsList()
    {
        return $this->regionsList;
    }

    /**
     * @param array $array
     * @return LocationRegions
     * @throws BadResponseException
     */
    public function setRegionsList($array)
    {
        $collection = new LocationRegionList();
        $this->regionsList = $collection->fillFromArray($array);
        return $this;
    }

    public function setFields($fields)
    {
        return parent::setFields(['regionsList' => $fields]);
    }
}