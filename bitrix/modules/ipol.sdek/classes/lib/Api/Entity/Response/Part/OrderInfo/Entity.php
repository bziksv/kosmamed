<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo;

use DateTime;
use Exception;
use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;
use Ipolh\SDEK\Api\Entity\Response\Part\Common\CdekLocation;

/**
 * Class Entity
 * @package Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo
 */
class Entity extends \Ipolh\SDEK\Api\Entity\Response\Part\Common\Entity
{
    use AbstractResponsePart;

    /**
     * @var bool
     */
    protected $is_return;

    /**
     * @var bool
     */
    protected $is_reverse;

    /**
     * 1 - IM | 2 - regular shipping
     * @var int
     */
    protected $type;

    /**
     * @var string|null
     */
    protected $cdek_number;

    /**
     * @var string|null
     */
    protected $number;

    /**
     * @var string
     */
    protected $delivery_mode;

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
     * @var string|null
     */
    protected $shipment_point;

    /**
     * @var string|null
     */
    protected $delivery_point;

    /**
     * @var DateTime|null (yyyy-MM-dd)
     */
    protected $date_invoice;

    /**
     * @var string|null
     */
    protected $shipper_name;

    /**
     * @var string|null
     */
    protected $shipper_address;

    /**
     * @var Money|null
     */
    protected $delivery_recipient_cost;

    /**
     * @var ThresholdList|null
     */
    protected $delivery_recipient_cost_adv;

    /**
     * @var Sender
     */
    protected $sender;

    /**
     * @var Seller|null
     */
    protected $seller;

    /**
     * @var Recipient
     */
    protected $recipient;

    /**
     * @var CdekLocation
     */
    protected $from_location;

    /**
     * @var CdekLocation
     */
    protected $to_location;

    /**
     * @var ServiceList|null
     */
    protected $services;

    /**
     * @var PackageList
     */
    protected $packages;

    /**
     * @var DeliveryProblemList|null
     */
    protected $delivery_problem;

    /**
     * @var DeliveryDetail|null
     */
    protected $delivery_detail;

    /**
     * @var bool|null
     */
    protected $transacted_payment;

    /**
     * @var StatusList
     */
    protected $statuses;

    /**
     * @var Calls|null
     */
    protected $calls;

    /**
     * @return bool
     */
    public function getIsReturn()
    {
        return $this->is_return;
    }

    /**
     * @param bool $is_return
     * @return Entity
     */
    public function setIsReturn($is_return)
    {
        $this->is_return = $is_return;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsReverse()
    {
        return $this->is_reverse;
    }

    /**
     * @param bool $is_reverse
     * @return Entity
     */
    public function setIsReverse($is_reverse)
    {
        $this->is_reverse = $is_reverse;
        return $this;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param int $type
     * @return Entity
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCdekNumber()
    {
        return $this->cdek_number;
    }

    /**
     * @param string|null $cdek_number
     * @return Entity
     */
    public function setCdekNumber($cdek_number)
    {
        $this->cdek_number = $cdek_number;
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
     * @return Entity
     */
    public function setNumber($number)
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @return string
     */
    public function getDeliveryMode()
    {
        return $this->delivery_mode;
    }

    /**
     * @param string $delivery_mode
     * @return Entity
     */
    public function setDeliveryMode($delivery_mode)
    {
        $this->delivery_mode = $delivery_mode;
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
     * @return Entity
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
     * @return Entity
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
     * @return Entity
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
     * @return Entity
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
     * @return Entity
     */
    public function setDeliveryPoint($delivery_point)
    {
        $this->delivery_point = $delivery_point;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getDateInvoice()
    {
        return $this->date_invoice;
    }

    /**
     * @param string $date_invoice
     * @return Entity
     * @throws Exception
     */
    public function setDateInvoice($date_invoice)
    {
        $this->date_invoice = new DateTime($date_invoice);
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
     * @return Entity
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
     * @return Entity
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
     * @param array $delivery_recipient_cost
     * @return Entity
     */
    public function setDeliveryRecipientCost($delivery_recipient_cost)
    {
        $this->delivery_recipient_cost = new Money($delivery_recipient_cost);
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
     * @param array $array
     * @return Entity
     * @throws BadResponseException
     */
    public function setDeliveryRecipientCostAdv($array)
    {
        $collection = new ThresholdList();
        $this->delivery_recipient_cost_adv = $collection->fillFromArray($array);
        return $this;
    }

    /**
     * @return Sender
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param array $sender
     * @return Entity
     */
    public function setSender($sender)
    {
        $this->sender = new Sender($sender);
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
     * @param array $seller
     * @return Entity
     */
    public function setSeller($seller)
    {
        $this->seller = new Seller($seller);
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
     * @param array $recipient
     * @return Entity
     */
    public function setRecipient($recipient)
    {
        $this->recipient = new Recipient($recipient);
        return $this;
    }

    /**
     * @return CdekLocation
     */
    public function getFromLocation()
    {
        return $this->from_location;
    }

    /**
     * @param array $from_location
     * @return Entity
     */
    public function setFromLocation($from_location)
    {
        $this->from_location = new CdekLocation($from_location);
        return $this;
    }

    /**
     * @return CdekLocation
     */
    public function getToLocation()
    {
        return $this->to_location;
    }

    /**
     * @param array $to_location
     * @return Entity
     */
    public function setToLocation($to_location)
    {
        $this->to_location = new CdekLocation($to_location);
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
     * @param array $array
     * @return Entity
     * @throws BadResponseException
     */
    public function setServices($array)
    {
        $collection = new ServiceList();
        $this->services = $collection->fillFromArray($array);
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
     * @param array $array
     * @return Entity
     * @throws BadResponseException
     */
    public function setPackages($array)
    {
        $collection = new PackageList();
        $this->packages = $collection->fillFromArray($array);
        return $this;
    }

    /**
     * @return DeliveryProblemList|null
     */
    public function getDeliveryProblem()
    {
        return $this->delivery_problem;
    }

    /**
     * @param array $array
     * @return Entity
     * @throws BadResponseException
     */
    public function setDeliveryProblem($array)
    {
        $collection = new DeliveryProblemList();
        $this->delivery_problem = $collection->fillFromArray($array);
        return $this;
    }

    /**
     * @return DeliveryDetail|null
     */
    public function getDeliveryDetail()
    {
        return $this->delivery_detail;
    }

    /**
     * @param array $delivery_detail
     * @return Entity
     */
    public function setDeliveryDetail($delivery_detail)
    {
        $this->delivery_detail = new DeliveryDetail($delivery_detail);
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getTransactedPayment()
    {
        return $this->transacted_payment;
    }

    /**
     * @param bool|null $transacted_payment
     * @return Entity
     */
    public function setTransactedPayment($transacted_payment)
    {
        $this->transacted_payment = $transacted_payment;
        return $this;
    }

    /**
     * @return StatusList
     */
    public function getStatuses()
    {
        return $this->statuses;
    }

    /**
     * @param array $array
     * @return Entity
     * @throws BadResponseException
     */
    public function setStatuses($array)
    {
        $collection = new StatusList();
        $this->statuses = $collection->fillFromArray($array);
        return $this;
    }

    /**
     * @return Calls|null
     */
    public function getCalls()
    {
        return $this->calls;
    }

    /**
     * @param array $calls
     * @return Entity
     */
    public function setCalls($calls)
    {
        $this->calls = new Calls($calls);
        return $this;
    }
}