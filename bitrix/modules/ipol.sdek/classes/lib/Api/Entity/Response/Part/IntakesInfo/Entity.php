<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\IntakesInfo;

use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\Response\Part\Common\CdekLocation;

/**
 * Class Entity
 * @package Ipolh\SDEK\Api\Entity\Response\Part\IntakeInfo
 */
class Entity extends \Ipolh\SDEK\Api\Entity\Response\Part\Common\Entity
{
    /**
     * @var int|null
     */
    protected $intake_number;

    /**
     * @var int|null
     */
    protected $cdek_number;

    /**
     * @var string|null
     */
    protected $order_uuid;

    /**
     * @var string yyyy-MM-dd
     */
    protected $intake_date;

    /**
     * @var string hh:mm
     */
    protected $intake_time_from;

    /**
     * @var string hh:mm
     */
    protected $intake_time_to;

    /**
     * @var string|null hh:mm
     */
    protected $lunch_time_from;

    /**
     * @var string|null hh:mm
     */
    protected $lunch_time_to;

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var int|null
     */
    protected $weight;

    /**
     * @var int|null
     */
    protected $length;

    /**
     * @var int|null
     */
    protected $width;

    /**
     * @var int|null
     */
    protected $height;

    /**
     * @var string|null
     */
    protected $comment;

    /**
     * @var Sender|null
     */
    protected $sender;

    /**
     * @var CdekLocation
     */
    protected $from_location;

    /**
     * @var bool|null
     */
    protected $need_call;

    /**
     * @var bool|null
     */
    protected $courier_power_of_attorney;

    /**
     * @var bool|null
     */
    protected $courier_identity_card;

    /**
     * @var StatusList
     */
    protected $statuses;

    /**
     * @return int|null
     */
    public function getIntakeNumber()
    {
        return $this->intake_number;
    }

    /**
     * @param int|null $intake_number
     * @return Entity
     */
    public function setIntakeNumber($intake_number)
    {
        $this->intake_number = $intake_number;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getCdekNumber()
    {
        return $this->cdek_number;
    }

    /**
     * @param int|null $cdek_number
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
    public function getOrderUuid()
    {
        return $this->order_uuid;
    }

    /**
     * @param string|null $order_uuid
     * @return Entity
     */
    public function setOrderUuid($order_uuid)
    {
        $this->order_uuid = $order_uuid;
        return $this;
    }

    /**
     * @return string
     */
    public function getIntakeDate()
    {
        return $this->intake_date;
    }

    /**
     * @param string $intake_date
     * @return Entity
     */
    public function setIntakeDate($intake_date)
    {
        $this->intake_date = $intake_date;
        return $this;
    }

    /**
     * @return string
     */
    public function getIntakeTimeFrom()
    {
        return $this->intake_time_from;
    }

    /**
     * @param string $intake_time_from
     * @return Entity
     */
    public function setIntakeTimeFrom($intake_time_from)
    {
        $this->intake_time_from = $intake_time_from;
        return $this;
    }

    /**
     * @return string
     */
    public function getIntakeTimeTo()
    {
        return $this->intake_time_to;
    }

    /**
     * @param string $intake_time_to
     * @return Entity
     */
    public function setIntakeTimeTo($intake_time_to)
    {
        $this->intake_time_to = $intake_time_to;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLunchTimeFrom()
    {
        return $this->lunch_time_from;
    }

    /**
     * @param string|null $lunch_time_from
     * @return Entity
     */
    public function setLunchTimeFrom($lunch_time_from)
    {
        $this->lunch_time_from = $lunch_time_from;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getLunchTimeTo()
    {
        return $this->lunch_time_to;
    }

    /**
     * @param string|null $lunch_time_to
     * @return Entity
     */
    public function setLunchTimeTo($lunch_time_to)
    {
        $this->lunch_time_to = $lunch_time_to;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return Entity
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param int|null $weight
     * @return Entity
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getLength()
    {
        return $this->length;
    }

    /**
     * @param int|null $length
     * @return Entity
     */
    public function setLength($length)
    {
        $this->length = $length;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param int|null $width
     * @return Entity
     */
    public function setWidth($width)
    {
        $this->width = $width;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param int|null $height
     * @return Entity
     */
    public function setHeight($height)
    {
        $this->height = $height;
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
     * @return Sender|null
     */
    public function getSender()
    {
        return $this->sender;
    }

    /**
     * @param array $sender
     * @return Entity
     */
    public function setSender(array $sender)
    {
        $this->sender = new Sender($sender);
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
    public function setFromLocation(array $from_location)
    {
        $this->from_location = new CdekLocation($from_location);
        return $this;
    }

    /**
     * @return bool|null
     */
    public function isNeedCall()
    {
        return $this->need_call;
    }

    /**
     * @param bool|null $need_call
     * @return Entity
     */
    public function setNeedCall($need_call)
    {
        $this->need_call = $need_call;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getCourierPowerOfAttorney()
    {
        return $this->courier_power_of_attorney;
    }

    /**
     * @param bool|null $courier_power_of_attorney
     * @return Entity
     */
    public function setCourierPowerOfAttorney($courier_power_of_attorney)
    {
        $this->courier_power_of_attorney = $courier_power_of_attorney;
        return $this;
    }

    /**
     * @return bool|null
     */
    public function getCourierIdentityCard()
    {
        return $this->courier_identity_card;
    }

    /**
     * @param bool|null $courier_identity_card
     * @return Entity
     */
    public function setCourierIdentityCard($courier_identity_card)
    {
        $this->courier_identity_card = $courier_identity_card;
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
    public function setStatuses(array $array)
    {
        $collection = new StatusList();
        $this->statuses = $collection->fillFromArray($array);
        return $this;
    }
}