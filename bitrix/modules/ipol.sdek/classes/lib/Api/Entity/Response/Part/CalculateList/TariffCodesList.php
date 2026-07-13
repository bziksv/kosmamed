<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\CalculateList;

use Ipolh\SDEK\Api\Entity\AbstractCollection;

/**
 * Class TariffCodesList
 * @package Ipolh\SDEK\Api\
 * @subpackage Response
 * @method TariffCode getFirst
 * @method TariffCode getNext
 * @method TariffCode getLast
 */
class TariffCodesList extends AbstractCollection
{
    protected $TariffCodes;

    public function __construct()
    {
        parent::__construct('TariffCodes');
    }
}