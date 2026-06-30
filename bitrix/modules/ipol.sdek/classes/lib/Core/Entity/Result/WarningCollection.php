<?php
namespace Ipolh\SDEK\Core\Entity\Result;

/**
 * Class WarningCollection
 * @package Ipolh\SDEK\Core
 * @subpackage Entity
 * @method Warning getFirst
 * @method Warning getNext
 * @method Warning getLast
 */
class WarningCollection extends InfoCollection
{
    protected $Warnings;

    public function __construct()
    {
        parent::__construct('Warnings');
    }
}