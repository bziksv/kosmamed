<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\OrderInfoMulti;

use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\AbstractEntity;
use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;
use Ipolh\SDEK\Api\Entity\Response\Part\Common\RequestList;
use Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo\Entity;
use Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo\RelatedEntityList;

class OrderInfo extends AbstractEntity
{
    use AbstractResponsePart;

    /**
     * @var int
     */
    protected $http_status;

    /**
     * @var string
     */
    protected $cdek_number;

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
     * @return int
     */
    public function getHttpStatus()
    {
        return $this->http_status;
    }

    /**
     * @param int $http_status
     * @return OrderInfo
     */
    public function setHttpStatus($http_status)
    {
        $this->http_status = $http_status;
        return $this;
    }

    /**
     * @return string
     */
    public function getCdekNumber()
    {
        return $this->cdek_number;
    }

    /**
     * @param string $cdek_number
     * @return OrderInfo
     */
    public function setCdekNumber($cdek_number)
    {
        $this->cdek_number = $cdek_number;
        return $this;
    }

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