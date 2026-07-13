<?php
namespace Ipolh\SDEK\Api\Entity\UniversalPart;

use Ipolh\SDEK\Api\Entity\AbstractEntity;

/**
 * Class Sender
 * @package Ipolh\SDEK\Api\Entity\UniversalPart
 */
class Sender extends AbstractEntity
{
    /**
     * @var string
     */
    protected $company;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var PhoneList
     */
    protected $phones;

    /**
     * @return string
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param string $company
     * @return Sender
     */
    public function setCompany($company)
    {
        $this->company = $company;
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
     * @return Sender
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * @param PhoneList $phoneList
     * @return Sender
     */
    public function setPhones($phoneList)
    {
        $this->phones = $phoneList;
        return $this;
    }
}