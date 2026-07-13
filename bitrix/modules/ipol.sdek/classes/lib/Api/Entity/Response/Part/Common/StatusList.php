<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\Common;

use Ipolh\SDEK\Api\Entity\AbstractCollection;

/**
 * Class StatusList
 * @package Ipolh\SDEK\Api
 * @subpackage Response
 * @method Status getFirst
 * @method Status getNext
 * @method Status getLast
 */
class StatusList extends AbstractCollection
{
    protected $Statuses;

    public function __construct()
    {
        parent::__construct('Statuses');
        //$this->setChildClass(Status::class);
    }
}