<?php


namespace Ipolh\SDEK\Core\Delivery;


use Exception;
use Ipolh\SDEK\Core\Entity\Collection;
use Ipolh\SDEK\Core\Entity\Money;
use Ipolh\SDEK\Core\Entity\Packing\MebiysDimMerger;

/**
 * Class Cargo
 * @package Ipolh\SDEK\Core
 * @subpackage Delivery
 * Cargo description, consist of basic goods
 * @method false|CargoItem getFirst
 * @method false|CargoItem getNext
 * @method false|CargoItem getLast
 */
class Cargo extends Collection
{
    /**
     * @var array
     */
    protected $Items;
    /**
     * @var MebiysDimMerger|mixed
     */
    protected $packer;

    /**
     * Cargo constructor.
     * @param $packer
     */
    public function __construct($packer = false)
    {
        parent::__construct('Items');
        $this->packer = $packer? new $packer : new MebiysDimMerger();
    }

    /**
     * @param CargoItem $item
     * @return $this
     * @throws Exception
     */
    public function add($item)
    {
        if($item->ready()) {
            parent::add($item);
        }else
            throw new Exception('CargoItem is not ready in '.get_class());

        return $this;
    }

    /**
     * @return array (L, W, H)
     */
    public function getDimensions()
    {
        $arGabs = array();

        $this->reset();
        while($obItem = $this->getNext())
        {
            $arGabs[] = array($obItem->getLength(), $obItem->getWidth(), $obItem->getHeight(), $obItem->getQuantity());
        }

        $packer = $this->packer;

        return $packer::getSumDimensions($arGabs);
    }

    /**
     * @return float
     */
    public function getVolume()
    {
        $volume = 0;

        $this->reset();
        while($obItem = $this->getNext())
        {
            $volume += $obItem->giveVolume() * $obItem->getQuantity();
        }

        return $volume;
    }

    /**
     * @return int
     */
    public function getWeight()
    {
        $weight = 0;

        $this->reset();
        while($obItem = $this->getNext())
        {
            $weight += $obItem->getWeight() * $obItem->getQuantity();
        }

        return $weight;
    }

    /**
     * @return array
     */
    public function getGabs()
    {
        return array('W'=>$this->getWeight(), 'V'=>$this->getVolume(), 'G'=>$this->getDimensions());
    }

    /**
     * @return Money
     * returns total estimated price
     */
    public function getTotalPrice()
    {
        $price = new Money(0);

        $this->reset();
        while($obItem = $this->getNext())
        {
            if($obItem->getPrice())
                $price = Money::sum(array($price,Money::multiply($obItem->getPrice(), $obItem->getQuantity())));
        }

        return $price;
    }

    /**
     * @return Money
     * Returns price to be payed for goods
     */
    public function getTotalCost()
    {
        $cost = new Money(0);

        $this->reset();
        while($obItem = $this->getNext())
        {
            if($obItem->getPrice())
                $cost = Money::sum(array($cost,Money::multiply($obItem->getPrice(), $obItem->getQuantity())));
        }

        return $cost;
    }

    /**
     * @return bool
     */
    public function checkOverSize()
    {
        $this->reset();
        while($obItem = $this->getNext())
        {
            if($obItem->getOverSize())
                return true;
        }

        return false;
    }
}