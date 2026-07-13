<?php


namespace Ipolh\SDEK\Core\Delivery;


use Ipolh\SDEK\Core\Entity\Collection;

/**
 * Class TariffCollection
 * @package Ipolh\SDEK\Core
 * @subpackage Delivery
 * Set of tariffs, for case when shipment can be delivered in more than one way (and API allows to calculate them in one request)
 * @method false|Tariff getFirst
 * @method false|Tariff getNext
 * @method false|Tariff getLast
 */
class TariffCollection extends Collection
{
    /**
     * @var array
     */
    protected $Tariffs;

    /**
     * TariffCollection constructor.
     */
    public function __construct()
    {
        parent::__construct('Tariffs');
    }

}