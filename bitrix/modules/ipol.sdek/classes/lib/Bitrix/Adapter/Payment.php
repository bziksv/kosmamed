<?php

namespace Ipolh\SDEK\Bitrix\Adapter;


use Ipolh\SDEK\Bitrix\Entity\Options;
use Ipolh\SDEK\Core\Entity\Money;

class Payment
{
    protected $corePayment;
    protected $options;

    public function __construct(Options $options)
    {
        $this->corePayment = new \Ipolh\SDEK\Core\Order\Payment();
        $this->options     = $options;
    }

    public function fromOrder($bitrixId)
    {/*
        if (!\CModule::includeModule('sale')) {
            throw new \Exception('No sale-module');
        }

        $order = \CSaleOrder::getByID($bitrixId);

        $obMoneyDelivery  = new Money($order['PRICE_DELIVERY']);
        $obMoneyGoods     = new Money($order['PRICE'] - $order['PRICE_DELIVERY']);
        $obMoneyEstimated = new Money($order['PRICE'] - $order['PRICE_DELIVERY']);
        $obMoneyPayed     = new Money($order['SUM_PAID']);

        $this->getCorePayment()->setDelivery($obMoneyDelivery)
            ->setGoods($obMoneyGoods)
            ->setPayed($obMoneyPayed)
            ->setEstimated($obMoneyEstimated);

        $nOrder    = \Ipolh\SDEK\Bitrix\Handler\Order::getOrderById($bitrixId);
        $payNal    = false;
        $payCard   = false;
        $allPayed  = false;
        if($nOrder && is_object($nOrder)) {
            $paymentCollection = $nOrder->getPaymentCollection();
    */
            /** @var \Bitrix\Sale\Payment $payment */
        /*
            foreach ($paymentCollection as $payment) {
                if(!$payNal && is_array($this->options->fetchPayNal())){
                    $payNal = (in_array($payment->getPaymentSystemId(), $this->options->fetchPayNal()));
                }
                if(!$payCard && is_array($this->options->fetchPayCard())){
                    $payCard = (in_array($payment->getPaymentSystemId(), $this->options->fetchPayCard()));
                }
                if(!$allPayed){
                    $allPayed = $payment->isPaid();
                }
            }
        } else {
            $payNal  = (is_array($this->options->fetchPayNal()) && in_array($order['PAY_SYSTEM_ID'], $this->options->fetchPayNal()));
            $payCard = (is_array($this->options->fetchPayCard()) && in_array($order['PAY_SYSTEM_ID'], $this->options->fetchPayCard()));
        }

        if($payNal){
            $this->getCorePayment()->setType('Cash');
        } elseif($payCard){
            $this->getCorePayment()->setType('Card');
        } else {
            $this->getCorePayment()->setType('Bill');
        }

        // either payed in Bitrix or not card/nal && checking option "checkPayed"
        if (
            $order['SUM_PAID'] == $order['PRICE'] ||
            $order['PAYED'] == 'Y' ||
            (
                !$payNal  &&
                !$payCard &&
                (
                    $this->options->fetchCheckPayed() !== 'Y' ||
                    $allPayed
                )
            )
        ) {
            $this->getCorePayment()->setIsBeznal(true);
        } else {
            $this->getCorePayment()->setIsBeznal(false);
        }

        $this->getCorePayment()->setNdsDefault($this->options->fetchNdsDefault());

        $order = \Ipol\Ozon\Bitrix\Handler\Order::getOrderById($bitrixId);
        $shipmentCollection = $order->getShipmentCollection();
        $shipmentCollection->rewind();
        $setted = false;
        */
        /** @var Shipment $obShipment */
        /*
        while($obShipment = $shipmentCollection->next()){
            if ($obShipment->isSystem() && $setted) {
                continue; // TODO: WTF?!
            }

            $setted = Deliveries::getVatRateByDeliveryId($obShipment->getField('DELIVERY_ID'));
        }
        $shipmentCollection->rewind();

        $this->getCorePayment()->setNdsDelivery(($setted)?$setted:0);// TODO: checkNDSPelivery
        */
    }

    public function fromArray($array)
    {
        $obMoneyDelivery  = new Money(($array['delivery'])?$array['delivery']:0);
        $obMoneyGoods     = new Money(($array['goods'])?$array['goods']:0);
        $obMoneyEstimated = new Money(($array['estimated'])?$array['estimated']:0);
        $obMoneyPayed     = new Money(($array['payed'])?$array['payed']:0);

        //$type = Adapter::convertPaymentTypes($array['type'],true); TODO: check
        $this->getCorePayment()->setIsBeznal((array_key_exists('isBeznal',$array) && $array['isBeznal']&& $array['isBeznal']!=='N'))
            ->setGoods($obMoneyGoods)
            ->setEstimated($obMoneyEstimated)
            ->setDelivery($obMoneyDelivery)
            ->setPayed($obMoneyPayed)
            ->setNdsDelivery($array['ndsDelivery'])
        ;

        return $this;
    }

    /**
     * @return \Ipolh\SDEK\Core\Order\Payment
     */
    public function getCorePayment()
    {
        return $this->corePayment;
    }
}