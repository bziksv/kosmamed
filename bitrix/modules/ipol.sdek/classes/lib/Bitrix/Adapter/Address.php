<?php

namespace Ipolh\SDEK\Bitrix\Adapter;


use Ipolh\SDEK\Bitrix\Entity\Options;

class Address
{
    protected $coreAddress;
    protected $options;

    public function __construct(Options $options)
    {
        $this->coreAddress = new \Ipolh\SDEK\Core\Order\Address();
        $this->options      = $options;
    }

    public function fromArray($array)
    {
        $arSetters = array('zip','country','region','city','line','comment','code');
        foreach($arSetters as $part)
        {
            if(array_key_exists($part,$array))
            {
                $method = 'set'.ucfirst($part);
                $this->getCoreAddress()->$method($array[$part]);
            }
        }

        return $this;
    }

    /**
     * @return \Ipolh\SDEK\Core\Order\Address
     */
    public function getCoreAddress()
    {
        return $this->coreAddress;
    }
}