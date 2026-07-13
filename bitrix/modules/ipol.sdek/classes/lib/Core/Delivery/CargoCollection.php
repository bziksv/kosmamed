<?php


namespace Ipolh\SDEK\Core\Delivery;


use Ipolh\SDEK\Core\Entity\Collection;
use Ipolh\SDEK\Core\Entity\Money;
use Ipolh\SDEK\Core\Entity\Packing\MebiysDimMerger;

/**
 * Class CargoCollection
 * @package Ipolh\SDEK\Core
 * @subpackage Delivery
 * @method false|Cargo getFirst
 * @method false|Cargo getNext
 * @method false|Cargo getLast
 * Collection of Cargoes - used for keeping record of dividing for places
 */
class CargoCollection extends Collection
{
    /**
     * @var array
     */
    protected $Cargoes;
    /**
     * @var MebiysDimMerger|mixed
     */
    protected $packer;

    /**
     * CargoCollection constructor.
     * @param string $packer - full class name
     */
    public function __construct($packer = false)
    {
        parent::__construct('Cargoes');
        $this->packer = $packer ? new $packer : new MebiysDimMerger();
    }

    /**
     * @return Money
     * Get estimated value of the goods in all cargoes
     */
    public function getTotalPrice()
    {
        $this->reset();
        $ttlPrice = new Money(0);
        while ($obCargo = $this->getNext()) {
            $ttlPrice = Money::sum(array($ttlPrice, $obCargo->getTotalPrice()));
        }

        return $ttlPrice;
    }

    /**
     * @return Money
     * Get how much money is need to be payed
     */
    public function getTotalCost()
    {
        $this->reset();
        $ttlCost = new Money(0);
        while ($obCargo = $this->getNext()) {
            $ttlCost = Money::sum(array($ttlCost,$obCargo->getTotalCost()));
        }

        return $ttlCost;
    }

    /**
     * @return int
     */
    public function getTotalWeight()
    {
        $this->reset();
        $weight = 0;

        while ($obCargo = $this->getNext()) {
            $weight += $obCargo->getWeight();
        }

        return $weight;
    }

    /**
     * @return float
     */
    public function getTotalVolume()
    {
        $this->reset();
        $volume = 0;

        while ($obCargo = $this->getNext()) {
            $volume += $obCargo->getVolume();
        }

        return $volume;
    }

    /**
     * @return array|int[]|string[]
     */
    public function getTotalDimensions()
    {
        $arGabs = array();
        $this->reset();

        while ($obCargo = $this->getNext()) {
            $cargoGabarites = $obCargo->getDimensions();
            $arGabs[] = array($cargoGabarites['L'], $cargoGabarites['W'], $cargoGabarites['H'], 1);
        }

        $packer = $this->packer;

        return $packer::getSumDimensions($arGabs);
    }
}