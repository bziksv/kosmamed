<?php
namespace Ipolh\SDEK\SDEK\Entity;

use Ipolh\SDEK\Api\Entity\Response\OrderInfo as ObjResponse;
use Ipolh\SDEK\Core\Order\Order;

/**
 * Class OrderInfoResult
 * @package Ipolh\SDEK\SDEK
 * @subpackage Entity
 * @method ObjResponse getResponse
 */
class OrderInfoResult extends AbstractResult
{
    /**
     * TODO: refactor this madness
     * @return Order
     */
    public function getOrder()
    {
        $order = new Order();

        /*
        if (!$this->isSuccess())
            return $order;
        
        $entity = $this->getResponse()->getEntity();
        
        $order->setShipperName($entity->getShipperName())
            ->setShipperAddress($entity->getShipperAddress())
            ->setDeliveryRecipientCostAdv($entity->getDeliveryRecipientCostAdv())
            ->setDeliveryRecipientCost($entity->getDeliveryRecipientCost())
            ->setSeller($entity->getSeller())
            ->setDeliveryPoint($entity->getDeliveryPoint())
            ->setShipmentPoint($entity->getShipmentPoint())
            ->setInvoiceDate($entity->getDateInvoice())
            ->setType($entity->getType())
            ->setSender($entity->getSender())
            ->setTariffCode($entity->getTariffCode())
            ->setComment($entity->getComment())
            ->setNumber($entity->getNumber())
            ->setSdekNum($entity->getCdekNumber());
        
        if ($entity->getFromLocation()) {
            $address = new Address();
            $address->setCountryCode($entity->getFromLocation()->getCountryCode())
                ->setZip($entity->getFromLocation()->getPostalCode())
                ->setSdekAddress($entity->getFromLocation()->getAddress())
                ->setRegion($entity->getFromLocation()->getRegion());
            $order->setAddressFrom($address);
        }
        if ($entity->getToLocation()) {
            $address = new Address();
            $address->setCountryCode($entity->getToLocation()->getCountryCode())
                ->setZip($entity->getToLocation()->getPostalCode())
                ->setSdekAddress($entity->getToLocation()->getAddress())
                ->setRegion($entity->getToLocation()->getRegion());
            $order->setAddressTo($address);
        }
        $newOrderItems = new ItemCollection();
        $goods = new Goods();
        $weight = 0;
        $width = 0;
        $length = 0;
        $height = 0;
        $comments = '';
        if ($entity->getPackages()) {
            $entity->getPackages()->reset();
            $packages = $entity->getPackages()->getFirst();
            do {
                if($packages->getItems()) {
                    $items = $packages->getItems();
                    $items->reset();
                    $item = $items->getFirst();
                    do {
                        $newOrderItem = new Item();
                        $newOrderItem->setQuantity($item->getAmount())
                            ->setCost(new Money($item->getCost()))
                            ->setWeight($item->getWeight())
                            ->setField('country_code', $item->getCountryCode())
                            ->setField('brand', $item->getBrand())
                            ->setField('material', $item->getMaterial())
                            ->setField('url', $item->getUrl())
                            ->setName($item->getName())
                            ->setArticul($item->getWareKey());
                        
                        $newOrderItems->add($newOrderItem);
                        
                    } while($item = $items->getNext());
                    
                    $weight += intval($item->getWeight());
                    $comments .= '\n'.$packages->getComment();
                    
                    if( intval($packages->getWidth()) > $width ) {
                        $width = intval($packages->getWidth());
                    }
                    if( intval($packages->getHeight()) > $height ) {
                        $height = intval($packages->getHeight());
                    }
                    if( intval($packages->getLength()) > $length ) {
                        $length = intval($packages->getLength());
                    }
                }
                
            } while($entity->getPackages()->getNext());
        }
        $goods->setWeight($weight);
        $goods->setDetails($comments);
        $goods->setWidth($width);
        $goods->setHeight($height);
        $goods->setLength($length);
        
        $order->setItems($newOrderItems);
        $order->setGoods($goods);
        
        $personCollection = new ReceiverCollection();
        if( $entity->getRecipient() ) {
            $person = new Receiver();
            $person->setFullName($entity->getRecipient()->getName());
            $person->setEmail($entity->getRecipient()->getEmail());
            
            if($entity->getRecipient()->getPhones()) {
                $person->setPhone($entity->getRecipient()->getPhones()->getFirst()->getNumber());
                $person->setAdditionalPhone($entity->getRecipient()->getPhones()->getFirst()->getAdditional());
            }
            $person->setField('company',$entity->getRecipient()->getCompany());
            $person->setField('passport_date_of_birth',$entity->getRecipient()->getPassportDateOfBirth());
            $person->setField('passport_date_of_issue',$entity->getRecipient()->getPassportDateOfIssue());
            $person->setField('passport_number',$entity->getRecipient()->getPassportNumber());
            $person->setField('passport_organization',$entity->getRecipient()->getPassportOrganization());
            $person->setField('passport_series',$entity->getRecipient()->getPassportSeries());
            
            $personCollection->add($person);
        }
        $order->setReceivers($personCollection);
        */

        return $order;
    }
}