<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\Common;

use Ipolh\SDEK\Api\Entity\AbstractCollection;

/**
 * Class ErrorList
 * @package Ipolh\SDEK\Api
 * @subpackage Response
 * @method Error getFirst
 * @method Error getNext
 * @method Error getLast
 */
class ErrorList extends AbstractCollection
{
    protected $Errors;

    public function __construct()
    {
        parent::__construct('Errors');
    }
}