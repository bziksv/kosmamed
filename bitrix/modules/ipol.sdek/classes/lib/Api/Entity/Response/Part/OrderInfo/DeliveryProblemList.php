<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo;

use Ipolh\SDEK\Api\Entity\AbstractCollection;

/**
 * Class DeliveryProblemList
 * @package Ipolh\SDEK\Api
 * @subpackage Response
 * @method DeliveryProblem getFirst()
 * @method DeliveryProblem getNext()
 */
class DeliveryProblemList extends AbstractCollection
{
    protected $DeliveryProblems;

    public function __construct()
    {
        parent::__construct('DeliveryProblems');
        $this->setChildClass(DeliveryProblem::class);
    }
}