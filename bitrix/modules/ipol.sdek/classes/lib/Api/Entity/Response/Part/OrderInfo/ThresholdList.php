<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo;

/**
 * Class ThresholdList
 * @package Ipolh\SDEK\Api\
 * @subpackage Response
 * @method Threshold getFirst
 * @method Threshold getNext
 * @method Threshold getLast
 */
class ThresholdList extends \Ipolh\SDEK\Api\Entity\UniversalPart\ThresholdList
{
    public function __construct()
    {
        parent::__construct();
        $this->setChildClass(Threshold::class);
    }
}