<?php
namespace Ipolh\SDEK\Api\Entity\UniversalPart;

use Ipolh\SDEK\Api\Entity\AbstractEntity;

/**
 * Class Seller
 * @package Ipolh\SDEK\Api\Entity\UniversalPart
 */
class Seller extends AbstractEntity
{
    /**
     * @var null|string
     */
    protected $name;
    /**
     * @var null|string
     */
    protected $inn;
    /**
     * @var null|string
     */
    protected $phone;
    /**
     * @var null|int
     */
    protected $ownership_form;
    /**
     * @var null|string
     */
    protected $address;

    /**
     * @return string|null
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     * @return Seller
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getInn()
    {
        return $this->inn;
    }

    /**
     * @param string|null $inn
     * @return Seller
     */
    public function setInn($inn)
    {
        $this->inn = $inn;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * @param string|null $phone
     * @return Seller
     */
    public function setPhone($phone)
    {
        $this->phone = $phone;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getOwnershipForm()
    {
        return $this->ownership_form;
    }

    /**
     * @param int|null $ownership_form
     * @return Seller
     */
    public function setOwnershipForm($ownership_form)
    {
        $this->ownership_form = $ownership_form;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @param string|null $address
     * @return Seller
     */
    public function setAddress($address)
    {
        $this->address = $address;
        return $this;
    }

}