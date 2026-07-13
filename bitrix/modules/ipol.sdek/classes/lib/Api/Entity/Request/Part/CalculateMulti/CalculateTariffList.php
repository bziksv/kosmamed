<?php
namespace Ipolh\SDEK\Api\Entity\Request\Part\CalculateMulti;

use Ipolh\SDEK\Api\Entity\AbstractCollection;

/**
 * Class CalculateTariffList
 * @package Ipolh\SDEK\Api
 * @subpackage Request
 * @method CalculateTariff getFirst
 * @method CalculateTariff getNext
 * @method CalculateTariff getLast
 */
class CalculateTariffList extends AbstractCollection
{
    protected $CalculateTariffs;

    public function __construct()
    {
        parent::__construct('CalculateTariffs');
    }
}