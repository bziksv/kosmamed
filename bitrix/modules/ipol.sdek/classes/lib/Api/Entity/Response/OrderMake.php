<?php
namespace Ipolh\SDEK\Api\Entity\Response;

use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\Response\Part\Common\Entity;
use Ipolh\SDEK\Api\Entity\Response\Part\Common\RelatedEntityList;
use Ipolh\SDEK\Api\Entity\Response\Part\Common\RequestList;

/**
 * Class OrderMake
 * @package Ipolh\SDEK\Api\Entity\Response
 */
class OrderMake extends AbstractResponse
{
    /**
     * @var Entity|null
     */
    protected $entity;

    /**
     * @var RequestList
     */
    protected $requests;

    /**
     * @var RelatedEntityList|null
     */
    protected $related_entities;

    /**
     * @return Entity|null
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @param array $entity
     * @return OrderMake
     */
    public function setEntity($entity)
    {
        $this->entity = new Entity($entity);
        return $this;
    }

    /**
     * @return RequestList
     */
    public function getRequests()
    {
        return $this->requests;
    }

    /**
     * @param array $array
     * @return OrderMake
     * @throws BadResponseException
     */
    public function setRequests($array)
    {
        $collection = new RequestList();
        $this->requests = $collection->fillFromArray($array);
        return $this;
    }

    /**
     * @return RelatedEntityList|null
     */
    public function getRelatedEntities()
    {
        return $this->related_entities;
    }

    /**
     * @param array $array
     * @return OrderMake
     * @throws BadResponseException
     */
    public function setRelatedEntities($array)
    {
        $collection = new RelatedEntityList();
        $this->related_entities = $collection->fillFromArray($array);
        return $this;
    }
}