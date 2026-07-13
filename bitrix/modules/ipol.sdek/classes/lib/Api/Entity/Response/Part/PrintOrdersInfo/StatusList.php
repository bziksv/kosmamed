<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\PrintOrdersInfo;

/**
 * Class StatusList
 * @package Ipolh\SDEK\Api\
 * @subpackage Response
 * @method Status getFirst
 * @method Status getNext
 * @method Status getLast
 */
class StatusList extends \Ipolh\SDEK\Api\Entity\Response\Part\Common\StatusList
{
    public function __construct()
    {
        parent::__construct();
        $this->setChildClass(Status::class);
    }
}
