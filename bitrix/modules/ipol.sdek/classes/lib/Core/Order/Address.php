<?php


namespace Ipolh\SDEK\Core\Order;


use Ipolh\SDEK\Core\Entity\FieldsContainer;

/**
 * Class Address
 * @package Ipolh\SDEK\Core
 * @subpackage Order
 */
class Address extends FieldsContainer
{

    /**
     * @var string
     */
    protected $zip;
    /**
     * @var string
     */
    protected $country;
    /**
     * @var string
     */
    protected $region;
    /**
     * @var string
     */
    protected $city;
    /**
     * @var string
     */
    protected $code;
    /**
     * @var string
     */
    protected $line;
    /**
     * @var string
     */
    protected $street;
    /**
     * @var string | int
     */
    protected $house;
    /**
     * @var string | int
     */
    protected $building;
    /**
     * @var string
     */
    protected $flat;
    /**
     * @var string
     */
    protected $entrance;
    /**
     * @var string
     */
    protected $intercom;
    /**
     * @var string | int
     */
    protected $floor;
    /**
     * @var string
     */
    protected $comment;
    /**
     * @var float
     */
    protected $lat;
    /**
     * @var float
     */
    protected $lng;

    /**
     * @return string
     */
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param $region
     * @return $this
     */
    public function setRegion($region)
    {
        $this->region = $region;

        return $this;
    }

    /**
     * @return int|string
     */
    public function getFloor()
    {
        return $this->floor;
    }

    /**
     * @param $floor
     * @return $this
     */
    public function setFloor($floor)
    {
        $this->floor = $floor;

        return $this;
    }

    /**
     * @return false|string
     */
    public function getAddress()
    {
        if(isset($this->line))
        {
            return $this->getLine();
        }
        elseif(isset($this->street) && isset($this->house))
        {
            $addrStr = $this->getStreet().", ".$this->getHouse();
            if(isset($this->building))
                $addrStr .= '/'.$this->getBuilding();
            if(isset($this->flat))
                $addrStr .= " ".$this->getFlat();
            return $addrStr;
        }
        else
        {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getLine()
    {
        return $this->line;
    }

    /**
     * @param $line
     * @return $this
     */
    public function setLine($line)
    {
        $this->line = $line;

        return $this;
    }

    /**
     * @return string
     */
    public function getStreet()
    {
        return $this->street;
    }

    /**
     * @param $street
     * @return $this
     */
    public function setStreet($street)
    {
        $this->street = $street;

        return $this;
    }

    /**
     * @return int|string
     */
    public function getHouse()
    {
        return $this->house;
    }

    /**
     * @param $house
     * @return $this
     */
    public function setHouse($house)
    {
        $this->house = $house;

        return $this;
    }

    /**
     * @return int|string
     */
    public function getBuilding()
    {
        return $this->building;
    }

    /**
     * @param $building
     * @return $this
     */
    public function setBuilding($building)
    {
        $this->building = $building;

        return $this;
    }

    /**
     * @return string
     */
    public function getFlat()
    {
        return $this->flat;
    }

    /**
     * @param $flat
     * @return $this
     */
    public function setFlat($flat)
    {
        $this->flat = $flat;

        return $this;
    }

    /**
     * @return string
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param $zip
     * @return $this
     */
    public function setZip($zip)
    {
        $this->zip = $zip;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param $country
     * @return $this
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param $city
     * @return $this
     */
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param $code
     * @return $this
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getEntrance()
    {
        return $this->entrance;
    }

    /**
     * @param $entrance
     * @return $this
     */
    public function setEntrance($entrance)
    {
        $this->entrance = $entrance;

        return $this;
    }

    /**
     * @return string
     */
    public function getIntercom()
    {
        return $this->intercom;
    }

    /**
     * @param $intercom
     * @return $this
     */
    public function setIntercom($intercom)
    {
        $this->intercom = $intercom;

        return $this;
    }

    /**
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param $comment
     * @return $this
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * @return float
     */
    public function getLat()
    {
        return $this->lat;
    }

    /**
     * @param float $lat
     * @return Address
     */
    public function setLat($lat)
    {
        $this->lat = $lat;
        return $this;
    }

    /**
     * @return float
     */
    public function getLng()
    {
        return $this->lng;
    }

    /**
     * @param float $lng
     * @return Address
     */
    public function setLng($lng)
    {
        $this->lng = $lng;
        return $this;
    }


}