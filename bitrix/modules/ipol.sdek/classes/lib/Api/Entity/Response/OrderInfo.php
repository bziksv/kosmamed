<?php
namespace Ipolh\SDEK\Api\Entity\Response;

use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\Response\Part\Common\RequestList;
use Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo\Entity;
use Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo\RelatedEntityList;

/**
 * Class OrderInfo
 * @package Ipolh\SDEK\Api\Entity\Response
 */
class OrderInfo extends AbstractResponse
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
     * @return OrderInfo
     */
    public function setEntity(array $entity)
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
     * @return OrderInfo
     * @throws BadResponseException
     */
    public function setRequests(array $array)
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
     * @return OrderInfo
     * @throws BadResponseException
     */
    public function setRelatedEntities(array $array)
    {
        $collection = new RelatedEntityList();
        $this->related_entities = $collection->fillFromArray($array);
        return $this;
    }
}