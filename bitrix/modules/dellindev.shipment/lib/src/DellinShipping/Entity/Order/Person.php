<?php
/**
 * Переиспользуемая сущность Person. Определяет параметры для направления груза(куда) и лица принимающего груз.
 * Объектно-ориентированное представление для работы с сборщиком параметров для отправки запросов.
 * Данные из сущности используются в основной сущности Order, как частный случай со своими вспомогательными методами.
 * @author: Vadim Lazev
 * @company: BIA-Tech
 * @year: 2021
 */

namespace DellinShipping\Entity\Order;

use DellinShipping\NetworkService;
use DellinShipping\Entity\Order\ServiceOrder;
use Sale\Handlers\Delivery\DellinBlockAdmin;

class   Person
{
    public $type;
    public $full_name;
    public $phone;
    public $country;
    public $state;
    public $city;
    public $address;
    public $zip;
    public $email;
    public $terminal_id;
    public $KLADRToArrival;

    /**
     * Person constructor.
     * @param $type
     * @param $full_name
     * @param $email
     * @param $phone
     * @param $country
     * @param $state
     * @param $city
     * @param $address
     * @param $zip
     * @param $terminal_id
     */
    public function __construct($type, $full_name, $email, $phone, $country,
                                $state, $city, $address, $zip, $terminal_id)
    {
        $this->setType($type);
        $this->setFullName($full_name);
        $this->setPhone($phone);
        $this->setCountry($country);
        $this->setState($state);
        $this->setCity($city);
        $this->setAddress($address);
        $this->setZip($zip);
        $this->setEmail($email);
        $this->setTerminalId($terminal_id);
    }

    /**
     * @return mixed
     */
    public function getTerminalId()
    {
        return $this->terminal_id;
    }

    /**
     * @param mixed $terminal_id
     */
    public function setTerminalId($terminal_id): void
    {
        $this->terminal_id = $terminal_id;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type): void
    {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getFullName()
    {
        return $this->full_name;
    }

    /**
     * @param mixed $full_name
     */
    public function setFullName($full_name): void
    {
        $this->full_name = $full_name;
    }

    /**
     * @return mixed
     */
    public function getPhone()
    {
        $phone = DellinBlockAdmin::changeFormatPhone($this->phone);
        return $phone;
    }

    /**
     * @param mixed $phone
     */
    public function setPhone($phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return mixed
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param mixed $country
     */
    public function setCountry($country): void
    {
        $this->country = $country;
    }

    /**
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param mixed $state
     */
    public function setState($state): void
    {
        $this->state = $state;
    }

    /**
     * @return mixed
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param mixed $city
     */
    public function setCity($city): void
    {
        $this->city = $city;
    }

    /**
     * @return mixed
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param mixed $address
     */
    public function setAddress($address): void
    {
        $this->address = $address;
    }

    /**
     * @return mixed
     */
    public function getZip()
    {
        return $this->zip;
    }

    /**
     * @param mixed $zip
     */
    public function setZip($zip): void
    {
        $this->zip = $zip;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    public function getFullAddress()
    {
        $address = $this->getCountry().', '.$this->getState().', '. $this->getCity().', '.$this->getAddress();
        return $address;
    }

    public function setKLADRToArrival($kladr){
        $this->KLADRToArrival = $kladr;
    }

    public function getKLADRToArrival()
    {

    return   $this->KLADRToArrival;

// Устаревшее поведение.
//        $city = $this->getCity();
//        $region = $this->getState();
//        $zip = $this->getZip();
//
//
//        return NetworkService::GetCityKLADRCode($city, $region, $zip);
    }

}