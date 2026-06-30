<?php
namespace Ipolh\SDEK\Core\Entity\Result;

/**
 * Class ErrorCollection
 * @package Ipolh\SDEK\Core
 * @subpackage Entity
 * @method Error getFirst
 * @method Error getNext
 * @method Error getLast
 */
class ErrorCollection extends InfoCollection
{
    protected $Errors;

    public function __construct()
    {
        parent::__construct('Errors');
    }
}