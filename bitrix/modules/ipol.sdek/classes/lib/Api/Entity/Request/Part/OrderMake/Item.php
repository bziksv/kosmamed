<?php
namespace Ipolh\SDEK\Api\Entity\Request\Part\OrderMake;

class Item extends \Ipolh\SDEK\Api\Entity\UniversalPart\Item
{
    /**
     * Marking code. If used, $amount should be set to 1
     * @var string|null
     */
    protected $marking;

    /**
     * @return string|null
     */
    public function getMarking()
    {
        return $this->marking;
    }

    /**
     * @param string|null $marking
     * @return Item
     */
    public function setMarking($marking)
    {
        $this->marking = $marking;
        return $this;
    }
}