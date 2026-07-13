<?php

namespace Ipolh\SDEK\Bitrix\Adapter;


use Ipolh\SDEK\Bitrix\Entity\Options;

class Receiver
{
    protected $coreReceiver;
    protected $options;

    public function __construct(Options $options)
    {
        $this->coreReceiver = new \Ipolh\SDEK\Core\Order\Receiver();
        $this->options      = $options;
    }

    public function fromOrder($bId)
    {
        /*
        if(!\CModule::includeModule('sale'))
        {
            throw new \Exception('No sale-module');
        }

        $order = \Ipol\Ozon\Bitrix\Handler\Order::getOrderById($bId);
        if(!$order)
        {
            throw new \Exception('Order '.$bId.' not found');
        }

        $arConnector = array();
        foreach(array('firstName','email','phone') as $code)
        {
            $arConnector[$this->options->fetchOption($code)] = $code;
            $method = 'set'.ucfirst($code);
            $this->getCoreReceiver()->$method(false);
        }

        // $arProps = $order->loadPropertyCollection()->getArray();
        $arProps = $order->getPropertyCollection ()->getArray();

        foreach($arProps['properties'] as $property)
        {
            if(
                array_key_exists($property['CODE'],$arConnector) &&
                $arConnector[$property['CODE']]                  &&
                $value = array_pop($property['VALUE'])
            )
            {
                $method = 'set'.ucfirst($arConnector[$property['CODE']]);
                $this->getCoreReceiver()->$method($value);
            }
        }

        $this->getCoreReceiver()->setField('PersonType','NaturalPerson');
        */
    }

    public function fromArray($array)
    {
        $arPossFields = array('name','email','phone');
        foreach($array as $key => $value){
            if(in_array($key,$arPossFields)) {
                $action = 'set' . ucfirst($key);
                $this->getCoreReceiver()->$action($value);
            } else {
                $this->getCoreReceiver()->setField($key,$value);
            }
        }
        return $this;
    }

    /**
     * @return \Ipolh\SDEK\Core\Order\Receiver
     */
    public function getCoreReceiver()
    {
        return $this->coreReceiver;
    }
}