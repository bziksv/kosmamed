<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\Common;

use Ipolh\SDEK\Api\Entity\AbstractEntity;
use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;

/**
 * Class RelatedEntity
 * @package Ipolh\SDEK\Api\Entity\Response\Part\Common
 */
class RelatedEntity extends AbstractEntity
{
    use AbstractResponsePart;

    /**
     * 'barcode' | 'waybill' for OrderMake
     * 'return_order' | 'direct_order' | 'waybill' | 'barcode' | 'reverse_order' | 'delivery' for OrderInfo
     * @var string
     */
    protected $type;

    /**
     * @var string
     */
    protected $uuid;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return RelatedEntity
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * @param string $uuid
     * @return RelatedEntity
     */
    public function setUuid($uuid)
    {
        $this->uuid = $uuid;
        return $this;
    }
}