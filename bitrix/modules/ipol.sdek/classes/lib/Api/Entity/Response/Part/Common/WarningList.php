<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\Common;

use Ipolh\SDEK\Api\Entity\AbstractCollection;

/**
 * Class WarningList
 * @package Ipolh\SDEK\Api
 * @subpackage Response
 * @method Warning getFirst
 * @method Warning getNext
 * @method Warning getLast
 */
class WarningList extends AbstractCollection
{
    protected $Warnings;

    public function __construct()
    {
        parent::__construct('Warnings');
    }
}