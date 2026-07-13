<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\DeliveryPoints;

use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\AbstractEntity;
use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;
use Ipolh\SDEK\Api\Entity\Response\Part\Common\ErrorList;
use Ipolh\SDEK\Api\Entity\UniversalPart\PhoneList;

/**
 * Class DeliveryPoints
 * @package Ipolh\SDEK\Api\Entity\Response
 */
class DeliveryPoint extends AbstractEntity
{
    use AbstractResponsePart;

    /**
     * @var string(10)
     */
    protected $code;
    /**
     * @var string(50)
     */
    protected $name;
    /**
     * @var Location
     */
    protected $location;
    /**
     * @var string(255)|null
     */
    protected $address_comment;
    /**
     * @var string(50)
     */
    protected $nearest_station;
    /**
     * @var string(50)|null
     */
    protected $nearest_metro_station;
    /**
     * @var string(100)
     */
    protected $work_time;
    /**
     * @var PhoneList
     */
    protected $phones;
    /**
     * @var string(255)
     */
    protected $email;
    /**
     * @var string(255)|null
     */
    protected $note;
    /**
     * @var string(8) )('PVZ' / 'POSTAMAT')
     */
    protected $type;
    /**
     * @var string(6) ('cdek' / 'InPost')
     */
    protected $owner_code;
    /**
     * @var boolean
     */
    protected $take_only;
    /**
     * @var boolean
     */
    protected $is_handout;
    /**
     * @var boolean
     */
    protected $is_reception;
    /**
     * @var boolean
     */
    protected $is_dressing_room;
    /**
     * @var boolean
     */
    protected $have_cashless;
    /**
     * @var boolean
     */
    protected $have_cash;
    /**
     * @var boolean
     */
    protected $allowed_cod;
    /**
     * @var string(255)|null
     */
    protected $site;
    /**
     * @var OfficeImageList|null
     */
    protected $office_image_list;
    /**
     * @var WorkTimeList
     */
    protected $work_time_list;
    /**
     * @var WorkTimeExceptionList|null
     */
    protected $work_time_exceptions;
    /**
     * @var float|null
     * kg
     */
    protected $weight_min;
    /**
     * @var float|null
     * kg
     */
    protected $weight_max;
    /**
     * @var boolean
     */
    protected $fulfillment;
    /**
     * @var DimensionsList|null
     */
    protected $dimensions;
    /**
     * @var ErrorList|null
     */
    protected $errors;

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return DeliveryPoint
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return DeliveryPoint
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return Location
     */
    public function getLocation()
    {
        return $this->location;
    }

    /**
     * @param array $location
     * @return DeliveryPoint
     */
    public function setLocation($location)
    {
        $this->location = new Location($location);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddressComment()
    {
        return $this->address_comment;
    }

    /**
     * @param string|null $address_comment
     * @return DeliveryPoint
     */
    public function setAddressComment($address_comment)
    {
        $this->address_comment = $address_comment;
        return $this;
    }

    /**
     * @return string
     */
    public function getNearestStation()
    {
        return $this->nearest_station;
    }

    /**
     * @param string $nearest_station
     * @return DeliveryPoint
     */
    public function setNearestStation($nearest_station)
    {
        $this->nearest_station = $nearest_station;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNearestMetroStation()
    {
        return $this->nearest_metro_station;
    }

    /**
     * @param string|null $nearest_metro_station
     * @return DeliveryPoint
     */
    public function setNearestMetroStation($nearest_metro_station)
    {
        $this->nearest_metro_station = $nearest_metro_station;
        return $this;
    }

    /**
     * @return string
     */
    public function getWorkTime()
    {
        return $this->work_time;
    }

    /**
     * @param string $work_time
     * @return DeliveryPoint
     */
    public function setWorkTime($work_time)
    {
        $this->work_time = $work_time;
        return $this;
    }

    /**
     * @return PhoneList
     */
    public function getPhones()
    {
        return $this->phones;
    }

    /**
     * @param array $phones
     * @return DeliveryPoint
     * @throws BadResponseException
     */
    public function setPhones($phones)
    {

        $collection = new PhoneList();
        $this->phones = $collection->fillFromArray($phones);
        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return DeliveryPoint
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * @param string|null $note
     * @return DeliveryPoint
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return DeliveryPoint
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return string
     */
    public function getOwnerCode()
    {
        return $this->owner_code;
    }

    /**
     * @param string $owner_code
     * @return DeliveryPoint
     */
    public function setOwnerCode($owner_code)
    {
        $this->owner_code = $owner_code;
        return $this;
    }

    /**
     * @return bool
     */
    public function isTakeOnly()
    {
        return $this->take_only;
    }

    /**
     * @param bool $take_only
     * @return DeliveryPoint
     */
    public function setTakeOnly($take_only)
    {
        $this->take_only = $take_only;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsHandout()
    {
        return $this->is_handout;
    }

    /**
     * @param bool $is_handout
     * @return DeliveryPoint
     */
    public function setIsHandout($is_handout)
    {
        $this->is_handout = $is_handout;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsReception()
    {
        return $this->is_reception;
    }

    /**
     * @param bool $is_reception
     * @return DeliveryPoint
     */
    public function setIsReception($is_reception)
    {
        $this->is_reception = $is_reception;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsDressingRoom()
    {
        return $this->is_dressing_room;
    }

    /**
     * @param bool $is_dressing_room
     * @return DeliveryPoint
     */
    public function setIsDressingRoom($is_dressing_room)
    {
        $this->is_dressing_room = $is_dressing_room;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHaveCashless()
    {
        return $this->have_cashless;
    }

    /**
     * @param bool $have_cashless
     * @return DeliveryPoint
     */
    public function setHaveCashless($have_cashless)
    {
        $this->have_cashless = $have_cashless;
        return $this;
    }

    /**
     * @return bool
     */
    public function isHaveCash()
    {
        return $this->have_cash;
    }

    /**
     * @param bool $have_cash
     * @return DeliveryPoint
     */
    public function setHaveCash($have_cash)
    {
        $this->have_cash = $have_cash;
        return $this;
    }

    /**
     * @return bool
     */
    public function isAllowedCod()
    {
        return $this->allowed_cod;
    }

    /**
     * @param bool $allowed_cod
     * @return DeliveryPoint
     */
    public function setAllowedCod($allowed_cod)
    {
        $this->allowed_cod = $allowed_cod;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * @param string|null $site
     * @return DeliveryPoint
     */
    public function setSite($site)
    {
        $this->site = $site;
        return $this;
    }

    /**
     * @return OfficeImageList|null
     */
    public function getOfficeImageList()
    {
        return $this->office_image_list;
    }

    /**
     * @param array $array
     * @return DeliveryPoint
     * @throws BadResponseException
     */
    public function setOfficeImageList($array)
    {

        $collection = new OfficeImageList();
        $this->office_image_list = $collection->fillFromArray($array);
        return $this;

    }

    /**
     * @return WorkTimeList
     */
    public function getWorkTimeList()
    {
        return $this->work_time_list;
    }

    /**
     * @param array $array
     * @return DeliveryPoint
     * @throws BadResponseException
     */
    public function setWorkTimeList($array)
    {

        $collection = new WorkTimeList();
        $this->work_time_list = $collection->fillFromArray($array);
        return $this;

    }

    /**
     * @return WorkTimeExceptionList|null
     */
    public function getWorkTimeExceptions()
    {
        return $this->work_time_exceptions;
    }

    /**
     * @param array $array
     * @return DeliveryPoint
     * @throws BadResponseException
     */
    public function setWorkTimeExceptions($array)
    {

        $collection = new WorkTimeExceptionList();
        $this->work_time_exceptions = $collection->fillFromArray($array);
        return $this;

    }

    /**
     * @return float|null
     */
    public function getWeightMin()
    {
        return $this->weight_min;
    }

    /**
     * @param float|null $weight_min
     * @return DeliveryPoint
     */
    public function setWeightMin($weight_min)
    {
        $this->weight_min = $weight_min;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getWeightMax()
    {
        return $this->weight_max;
    }

    /**
     * @param float|null $weight_max
     * @return DeliveryPoint
     */
    public function setWeightMax($weight_max)
    {
        $this->weight_max = $weight_max;
        return $this;
    }

    /**
     * @return bool
     */
    public function isFulfillment()
    {
        return $this->fulfillment;
    }

    /**
     * @param bool $fulfillment
     * @return DeliveryPoint
     */
    public function setFulfillment($fulfillment)
    {
        $this->fulfillment = $fulfillment;
        return $this;
    }

    /**
     * @return DimensionsList|null
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * @param array $array
     * @return DeliveryPoint
     * @throws BadResponseException
     */
    public function setDimensions($array)
    {

        $collection = new DimensionsList();
        $this->dimensions = $collection->fillFromArray($array);
        return $this;

    }

    /**
     * @return ErrorList|null
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param array $errors
     * @return DeliveryPoint
     * @throws BadResponseException
     */
    public function setErrors($errors)
    {
        $collection = new ErrorList();
        $this->errors = $collection->fillFromArray($errors);
        return $this;
    }
}