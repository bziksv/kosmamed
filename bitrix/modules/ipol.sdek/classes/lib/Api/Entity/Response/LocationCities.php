<?php
namespace Ipolh\SDEK\Api\Entity\Response;

use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\Response\Part\LocationCities\LocationCityList;

/**
 * Class LocationCities
 * @package Ipolh\SDEK\Api\Entity\Response
 */
class LocationCities extends AbstractResponse
{
    /**
     * @var LocationCityList
     */
    protected $citiesList;

    /**
     * @return LocationCityList
     */
    public function getCitiesList()
    {
        return $this->citiesList;
    }

    /**
     * @param array $array
     * @return LocationCities
     * @throws BadResponseException
     */
    public function setCitiesList($array)
    {
        $collection = new LocationCityList();
        $this->citiesList = $collection->fillFromArray($array);
        return $this;
    }

    public function setFields($fields)
    {
        return parent::setFields(['citiesList' => $fields]);
    }
}