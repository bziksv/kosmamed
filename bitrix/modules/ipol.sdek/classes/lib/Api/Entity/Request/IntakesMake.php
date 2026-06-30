<?php
namespace Ipolh\SDEK\Api\Entity\Request;

use Ipolh\SDEK\Api\Entity\UniversalPart\CdekLocation;
use Ipolh\SDEK\Api\Entity\UniversalPart\Sender;

/**
 * Class IntakesMake
 * @package Ipolh\SDEK\Api\Entity\Request
 */
class IntakesMake extends AbstractRequest
{
    /**
     * @var int|null Null for consolidated cargo pickup
     */
    protected $cdek_number;

    /**
     * @var string|null Null for consolidated cargo pickup
     */
    protected $order_uuid;

    /**
     * @var string yyyy-MM-dd
     */
    protected $intake_date;

    /**
     * @var string hh:mm, 9:00 or greater
     */
    protected $intake_time_from;

    /**
     * @var string hh:mm, 22:00 or lesser
     */
    protected $intake_time_to;

    /**
     * @var string|null hh:mm, between $intake_time_from and $intake_time_to
     */
    protected $lunch_time_from;

    /**
     * @var string|null hh:mm, between $intake_time_from and $intake_time_to
     */
    protected $lunch_time_to;

    /**
     * @var string|null Required for consolidated cargo, if no $cdek_number / $order_uuid set
     */
    protected $name;

    /**
     * @var int|null gram. Required for consolidated cargo, if no $cdek_number / $order_uuid set
     */
    protected $weight;

    /**
     * @var int|null cm. Required for consolidated cargo, if no $cdek_number / $order_uuid set
     */
    protected $length;

    /**
     * @var int|null cm. Required for consolidated cargo, if no $cdek_number / $order_uuid set
     */
    protected $width;

    /**
     * @var int|null cm. Required for consolidated cargo, if no $cdek_number / $order_uuid set
     */
    protected $height;

    /**
     * @var string|null
     */
    protected $comment;

    /**
     * @var Sender|null Required for consolidated cargo, if no $cdek_number / $order_uuid set
     */
    protected $sender;

    /**
     * @var CdekLocation|null Required for consolidated cargo, if no $cdek_number / $order_uuid set
     */
    protected $from_location;

    /**
     * @var bool|null CDEK default is false
     */
    protected $need_call;

    /**
     * @var bool|null CDEK default is false
     */
    protected $courier_power_of_attorney;

    /**
     * @var bool|null CDEK default is false
     */
    protected $courier_identity_card;

    /**
     * @return int|null
     */
    public function getCdekNumber()
    {
        return $this->cdek_number;
    }

    /**
     * @param int|null $cdek_number
     * @return IntakesMake
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
     * @return IntakesMake
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
     * @return IntakesMake
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
     * @return IntakesMake
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
     * @return IntakesMake
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
     * @return IntakesMake
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
     * @return IntakesMake
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
     * @return IntakesMake
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
     * @return IntakesMake
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
     * @return IntakesMake
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
     * @return IntakesMake
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
     * @return IntakesMake
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
     * @return IntakesMake
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
     * @param Sender|null $sender
     * @return IntakesMake
     */
    public function setSender($sender)
    {
        $this->sender = $sender;
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
     * @return IntakesMake
     */
    public function setFromLocation($from_location)
    {
        $this->from_location = $from_location;
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
     * @return IntakesMake
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
     * @return IntakesMake
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
     * @return IntakesMake
     */
    public function setCourierIdentityCard($courier_identity_card)
    {
        $this->courier_identity_card = $courier_identity_card;
        return $this;
    }
}