<?php

namespace Ipolh\SDEK\Bitrix\Adapter;


class AddressFrom extends Address
{
    public function fromDefaults($orderDelivery)
    {
        /*
        $warehouseAddress = $this->getCoreAddress()->setCode($this->options->fetchFromPlaceId());;
        if($orderDelivery){
            $warehouseAddress = Deliveries::getWarehouseByDeliveryId($orderDelivery);
        }

        $this->getCoreAddress()->setCode($warehouseAddress);
        */
    }
}