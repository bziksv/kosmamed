<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\Common;

use Ipolh\SDEK\Api\Entity\AbstractCollection;

/**
 * Class RequestList
 * @package Ipolh\SDEK\Api
 * @subpackage Response
 * @method Request getFirst
 * @method Request getNext
 * @method Request getLast
 */
class RequestList extends AbstractCollection
{
    protected $Requests;

    public function __construct()
    {
        parent::__construct('Requests');
    }
}