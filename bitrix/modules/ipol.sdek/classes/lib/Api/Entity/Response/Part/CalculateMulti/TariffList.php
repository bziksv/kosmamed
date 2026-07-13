<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\CalculateMulti;

use Ipolh\SDEK\Api\Entity\AbstractCollection;

/**
 * Class TariffList
 * @package Ipolh\SDEK\Api\
 * @subpackage Response
 * @method Tariff getFirst
 * @method Tariff getNext
 * @method Tariff getLast
 */
class TariffList extends AbstractCollection
{
    protected $Tariffs;

    public function __construct()
    {
        parent::__construct('Tariffs');
    }
}