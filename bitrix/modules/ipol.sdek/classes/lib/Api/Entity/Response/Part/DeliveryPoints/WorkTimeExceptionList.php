<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\DeliveryPoints;

use Ipolh\SDEK\Api\Entity\AbstractCollection;

/**
 * Class WorkTimeExceptionList
 * @package Ipolh\SDEK\Api\Entity\Response
 * @method WorkTimeException getFirst
 * @method WorkTimeException getNext
 * @method WorkTimeException getLast
 */
class WorkTimeExceptionList extends AbstractCollection
{
    protected $WorkTimeExceptions;

    public function __construct()
    {
        parent::__construct('WorkTimeExceptions');
    }
}