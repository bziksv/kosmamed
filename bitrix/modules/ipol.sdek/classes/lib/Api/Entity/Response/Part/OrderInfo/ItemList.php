<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo;

/**
 * Class ItemList
 * @package Ipolh\SDEK\Api\
 * @subpackage Response
 * @method Item getFirst
 * @method Item getNext
 * @method Item getLast
 */
class ItemList extends \Ipolh\SDEK\Api\Entity\UniversalPart\ItemList
{
    public function __construct()
    {
        parent::__construct();
        $this->setChildClass(Item::class);
    }
}