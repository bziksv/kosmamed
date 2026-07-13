<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo;

use Ipolh\SDEK\Api\Entity\AbstractCollection;

/**
 * Class FailedCallList
 * @package Ipolh\SDEK\Api
 * @subpackage Response
 * @method FailedCall getFirst()
 * @method FailedCall getNext()
 */
class FailedCallList extends AbstractCollection
{
    protected $FailedCalls;

    public function __construct()
    {
        parent::__construct('FailedCalls');
        $this->setChildClass(FailedCall::class);
    }
}