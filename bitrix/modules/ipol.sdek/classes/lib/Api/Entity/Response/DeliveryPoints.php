<?php
namespace Ipolh\SDEK\Api\Entity\Response;

use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\Response\Part\DeliveryPoints\DeliveryPointList;

/**
 * Class DeliveryPoints
 * @package Ipolh\SDEK\Api\Entity\Response
 */
class DeliveryPoints extends AbstractResponse
{
    /**
     * @var DeliveryPointList
     */
    protected $pointList;

    /**
     * @return DeliveryPointList
     */
    public function getPointList()
    {
        return $this->pointList;
    }

    /**
     * @param array $array
     * @return DeliveryPoints
     * @throws BadResponseException
     */
    public function setPointList($array)
    {

        $collection = new DeliveryPointList();
        $this->pointList = $collection->fillFromArray($array);
        return $this;

    }

    public function setFields($fields)
    {
        return parent::setFields(['pointList' => $fields]);
    }
}