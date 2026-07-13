<?php

namespace Ipolh\SDEK\Bitrix\Adapter;


class AddressTo extends Address
{

    public function fromOrder($bId,$deliveryType='courier')
    {
        /*
        $order = \Ipolh\SDEK\Bitrix\Handler\Order::getOrderById($bId);

        if($deliveryType === 'courier') {
            $locationTo = $order->getPropertyCollection()->getDeliveryLocation()->getValue();
            if ($locationTo) {
                $location = new \Ipolh\SDEK\Bitrix\Adapter\Location($locationTo);
                if ($location && $location->getCoreLocation()) {
                    $this->getCoreAddress()->setCountry($location->getCoreLocation()->getCountry())
                        ->setRegion($location->getCoreLocation()->getRegion())
                        ->setCity($location->getCoreLocation()->getName());
                }
            }
        }

        if(!$order)
        {
            throw new \Exception('Order '.$bId.' not found');
        }

        $arConnector = array();
        foreach(array('zip','line','street','house','flat') as $code)
        {
            $arConnector[$this->options->fetchOption($code)] = $code;
        }

        // $arProps = $order->loadPropertyCollection()->getArray();
        $arProps = $order->getPropertyCollection ()->getArray();
        $prepPVZ = false;

        foreach($arProps['properties'] as $property)
        {
            if(array_key_exists($property['CODE'],$arConnector))
            {
                $method = 'set'.ucfirst($arConnector[$property['CODE']]);
                if($value = array_pop($property['VALUE']))
                {
                    $this->getCoreAddress()->$method($value);
                }
            }

            if($property['CODE'] == OrderPropsHandler::getMODULELBL() . OrderPropsHandler::getPVZprop()){
                $prepPVZ = array_pop($property['VALUE']);
            }
        }

        $this->getCoreAddress()->setComment($order->GetField('USER_DESCRIPTION'));

        if(in_array($deliveryType,array('pickup','postamat')) && $prepPVZ){
            $possPVZ = VariantsTable::getByDeliveryVariantId($prepPVZ);
            if($possPVZ){
                $this->getCoreAddress()->setLine($possPVZ['ADDRESS']);
            } else {
                PvzWidgetHandler::getMODULELBL();
                $this->getCoreAddress()->setLine(Tools::getMessage('WIDJET_PVZTYPE_'.$deliveryType));
            }
        }

        return $this;
        */
    }
}