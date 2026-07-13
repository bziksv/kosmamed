<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo;

/**
 * Class ServiceList
 * @package Ipolh\SDEK\Api\
 * @subpackage Response
 * @method Service getFirst
 * @method Service getNext
 * @method Service getLast
 */
class ServiceList extends \Ipolh\SDEK\Api\Entity\UniversalPart\ServiceList
{
    public function __construct()
    {
        parent::__construct();
        $this->setChildClass(Service::class);
    }
}