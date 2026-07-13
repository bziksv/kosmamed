<?php
namespace Ipolh\SDEK\Api\Entity\Request;

use Ipolh\SDEK\Api\Entity\Request\Part\OrderMake\PackageList;
use Ipolh\SDEK\Api\Entity\Request\Part\OrderMake\Sender;
use Ipolh\SDEK\Api\Entity\UniversalPart\CdekLocation;
use Ipolh\SDEK\Api\Entity\UniversalPart\Money;
use Ipolh\SDEK\Api\Entity\UniversalPart\Recipient;
use Ipolh\SDEK\Api\Entity\UniversalPart\Seller;
use Ipolh\SDEK\Api\Entity\UniversalPart\ServiceList;
use Ipolh\SDEK\Api\Entity\UniversalPart\ThresholdList;

/**
 * Class OrderMake
 * @package Ipolh\SDEK\Api\Entity\Request
 */
class OrderMake extends AbstractRequest
{
    /**
     * 1 - IM (default) | 2 - regular shipping
     * @var int|null
     */
    protected $type;

    /** Only for e-commerce. Order number in seller's system. If not sent will be used SDEK's uuid
     * @var string|null
     */
    protected $number;

    /**
     * @var int
     */
    protected $tariff_code;

    /**
     * @var string|null
     */
    protected $comment;

    /**
     * @var string|null
     */
    protected $developer_key;

    /**
     * PVZ-id from. Can't be used simultaneously with $from_location
     * @var string|null
     */
    protected $shipment_point;

    /**
     * PVZ-id to. Can't be used simultaneously with $to_location
     * @var string|null
     */
    protected $delivery_point;

    /**
     * Required for international
     * @var string|null (yyyy-MM-dd)
     */
    protected $date_invoice;

    /**
     * Required for international
     * @var string|null
     */
    protected $shipper_name;

    /**
     * Required for international
     * @var string|null
     */
    protected $shipper_address;

    /**
     * Only for IM (type 1)
     * @var Money|null
     */
    protected $delivery_recipient_cost;

    /**
     * @var ThresholdList|null
     */
    protected $delivery_recipient_cost_adv;

    /**
     * Required for regular shipping (type 2)
     * @var Sender|null
     */
    protected $sender;

    /**
     * Required for international sell. Only for IM (type 1)
     * @var Seller|null
     */
    protected $seller;

    /**
     * @var Recipient
     */
    protected $recipient;

    /**
     * Required for tariffs "door-*". Can't be used simultaneously with $shipment_point
     * @var CdekLocation|null
     */
    protected $from_location;

    /**
     * Required for tariffs "*-door". Can't be used simultaneously with $delivery_point
     * @var CdekLocation|null
     */
    protected $to_location;

    /**
     * @var ServiceList|null
     */
    protected $services;

    /**
     * Total number of packages can be from 1 to 255
     * @var PackageList
     */
    protected $packages;

    /**
     * 'barcode' | 'waybill'
     * @var string|null
     */
    protected $print;

    /**
     * @return int|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int|null $type
     * @return OrderMake
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string|null $number
     * @return OrderMake
     */
    public function setNumber($number)
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @return int
     */
    public function getTariffCode()
    {
        return $this->tariff_code;
    }

    /**
     * @param int $tariff_code
     * @return OrderMake
     */
    public function setTariffCode($tariff_code)
    {
        $this->tariff_code = $tariff_code;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string|null $comment
     * @return OrderMake
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDeveloperKey()
    {
        return $this->developer_key;
    }

    /**
     * @param string|null $developer_key
     * @return OrderMake
     */
    public function setDeveloperKey($developer_key)
    {
        $this->developer_key = $developer_key;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getShipmentPoint()
    {
        return $this->shipment_point;
    }

    /**
     * @param string|null $shipment_point
     * @return OrderMake
     */
    public function setShipmentPoint($shipment_point)
    {
        $this->shipment_point = $shipment_point;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDeliveryPoint()
    {
        return $this->delivery_point;
    }

    /**
     * @param string|null $delivery_point
     * @return OrderMake
     */
    public function setDeliveryPoint($delivery_point)
    {
        $this->delivery_point = $delivery_point;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getDateInvoice()
    {
        return $this->date_invoice;
    }

    /**
     * @param string|null $date_invoice
     * @return OrderMake
     */
    public function setDateInvoice($date_invoice)
    {
        $this->date_invoice = $date_invoice;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getShipperName()
    {
        return $this->shipper_name;
    }

    /**
     * @param string|null $shipper_name
     * @return OrderMake
     */
    public function setShipperName($shipper_name)
    {
        $this->shipper_name = $shipper_name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getShipperAddress()
    {
        return $this->shipper_address;
    }

    /**
     * @param string|null $shipper_address
     * @return OrderMake
     */
    public function setShipperAddress($shipper_address)
    {
        $this->shipper_address = $shipper_address;
        return $this;
    }

    /**
     * @return Money|null
     */
    public function getDeliveryRecipientCost()
    {
        return $this->delivery_recipient_cost;
    }

    /**
     * @param Money|null $delivery_recipient_cost
     * @return OrderMake
     */
    public function setDeliveryRecipientCost($delivery_recipient_cost)
    {
        $this->delivery_recipient_cost = $delivery_recipient_cost;
        return $this;
    }

    /**
     * @return ThresholdList|null
     */
    public function getDeliveryRecipientCostAdv()
    {
        return $this->delivery_recipient_cost_adv;
    }

    /**
     * @param ThresholdList|null $delivery_recipient_cost_adv
     * @return OrderMake
     */
    public function setDeliveryRecipientCostAdv($delivery_recipient_cost_adv)
    {
        $this->delivery_recipient_cost_adv = $delivery_recipient_cost_adv;
        return $this;
    }

    /**
     * @return Sender|null
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param Sender|null $sender
     * @return OrderMake
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
        return $this;
    }

    /**
     * @return Seller|null
     */
    public function getSeller()
    {
        return $this->seller;
    }

    /**
     * @param Seller|null $seller
     * @return OrderMake
     */
    public function setSeller($seller)
    {
        $this->seller = $seller;
        return $this;
    }

    /**
     * @return Recipient
     */
    public function getRecipient()
    {
        return $this->recipient;
    }

    /**
     * @param Recipient $recipient
     * @return OrderMake
     */
    public function setRecipient($recipient)
    {
        $this->recipient = $recipient;
        return $this;
    }

    /**
     * @return CdekLocation|null
     */
    public function getFromLocation()
    {
        return $this->from_location;
    }

    /**
     * @param CdekLocation|null $from_location
     * @return OrderMake
     */
    public function setFromLocation($from_location)
    {
        $this->from_location = $from_location;
        return $this;
    }

    /**
     * @return CdekLocation|null
     */
    public function getToLocation()
    {
        return $this->to_location;
    }

    /**
     * @param CdekLocation|null $to_location
     * @return OrderMake
     */
    public function setToLocation($to_location)
    {
        $this->to_location = $to_location;
        return $this;
    }

    /**
     * @return ServiceList|null
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * @param ServiceList|null $services
     * @return OrderMake
     */
    public function setServices($services)
    {
        $this->services = $services;
        return $this;
    }

    /**
     * @return PackageList
     */
    public function getPackages()
    {
        return $this->packages;
    }

    /**
     * @param PackageList $packages
     * @return OrderMake
     */
    public function setPackages($packages)
    {
        $this->packages = $packages;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPrint()
    {
        return $this->print;
    }

    /**
     * @param string|null $print
     * @return OrderMake
     */
    public function setPrint($print)
    {
        $this->print = $print;
        return $this;
    }
}