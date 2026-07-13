<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\Common;

use Ipolh\SDEK\Api\Entity\AbstractCollection;

/**
 * Class RelatedEntityList
 * @package Ipolh\SDEK\Api
 * @subpackage Response
 * @method RelatedEntity getFirst
 * @method RelatedEntity getNext
 * @method RelatedEntity getLast
 */
class RelatedEntityList extends AbstractCollection
{
    protected $RelatedEntities;

    public function __construct()
    {
        parent::__construct('RelatedEntities');
        $this->setChildClass(RelatedEntity::class);
    }
}