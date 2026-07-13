<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\Common;

use Ipolh\SDEK\Api\Entity\AbstractEntity;
use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;

/**
 * Class Entity
 * @package Ipolh\SDEK\Api\Entity\Response\Part\Common
 */
class Entity extends AbstractEntity
{
    use AbstractResponsePart;

    /**
     * @var string
     */
    protected $uuid;

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     * @return Entity
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
        return $this;
    }
}