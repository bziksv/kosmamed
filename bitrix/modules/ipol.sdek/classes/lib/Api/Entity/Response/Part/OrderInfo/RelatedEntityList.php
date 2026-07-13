<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo;

/**
 * Class RelatedEntityList
 * @package Ipolh\SDEK\Api\
 * @subpackage Response
 * @method RelatedEntity getFirst
 * @method RelatedEntity getNext
 * @method RelatedEntity getLast
 */
class RelatedEntityList extends \Ipolh\SDEK\Api\Entity\Response\Part\Common\RelatedEntityList
{
    public function __construct()
    {
        parent::__construct();
        $this->setChildClass(RelatedEntity::class);
    }
}