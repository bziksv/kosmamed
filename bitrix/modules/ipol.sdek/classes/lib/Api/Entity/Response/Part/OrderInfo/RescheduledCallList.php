<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo;

use Ipolh\SDEK\Api\Entity\AbstractCollection;

/**
 * Class RescheduledCallList
 * @package Ipolh\SDEK\Api
 * @subpackage Response
 * @method RescheduledCall getFirst()
 * @method RescheduledCall getNext()
 */
class RescheduledCallList extends AbstractCollection
{
    protected $RescheduledCalls;

    public function __construct()
    {
        parent::__construct('RescheduledCalls');
        $this->setChildClass(RescheduledCall::class);
    }
}