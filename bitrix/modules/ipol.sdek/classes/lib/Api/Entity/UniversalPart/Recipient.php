<?php
namespace Ipolh\SDEK\Api\Entity\UniversalPart;

use Ipolh\SDEK\Api\Entity\AbstractEntity;

/**
 * Class Recipient
 * @package Ipolh\SDEK\Api\Entity\UniversalPart
 */
class Recipient extends AbstractEntity
{
    /**
     * @var string|null
     */
    protected $company;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string|null
     */
    protected $passport_series;

    /**
     * @var string|null
     */
    protected $passport_number;

    /**
     * @var string|null (yyyy-MM-dd)
     */
    protected $passport_date_of_issue;

    /**
     * @var string|null
     */
    protected $passport_organization;

    /**
     * @var string|null
     */
    protected $tin;

    /**
     * @var string|null (yyyy-MM-dd)
     */
    protected $passport_date_of_birth;

    /**
     * @var string|null
     */
    protected $email;

    /**
     * @var PhoneList
     */
    protected $phones;

    /**
     * @return string|null
     */
    public function getCompany()
    {
        return $this->company;
    }

    /**
     * @param string|null $company
     * @return Recipient
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
     * @return Recipient
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassportSeries()
    {
        return $this->passport_series;
    }

    /**
     * @param string|null $passport_series
     * @return Recipient
     */
    public function setPassportSeries($passport_series)
    {
        $this->passport_series = $passport_series;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassportNumber()
    {
        return $this->passport_number;
    }

    /**
     * @param string|null $passport_number
     * @return Recipient
     */
    public function setPassportNumber($passport_number)
    {
        $this->passport_number = $passport_number;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassportDateOfIssue()
    {
        return $this->passport_date_of_issue;
    }

    /**
     * @param string|null $passport_date_of_issue
     * @return Recipient
     */
    public function setPassportDateOfIssue($passport_date_of_issue)
    {
        $this->passport_date_of_issue = $passport_date_of_issue;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassportOrganization()
    {
        return $this->passport_organization;
    }

    /**
     * @param string|null $passport_organization
     * @return Recipient
     */
    public function setPassportOrganization($passport_organization)
    {
        $this->passport_organization = $passport_organization;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTin()
    {
        return $this->tin;
    }

    /**
     * @param string|null $tin
     * @return Recipient
     */
    public function setTin($tin)
    {
        $this->tin = $tin;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getPassportDateOfBirth()
    {
        return $this->passport_date_of_birth;
    }

    /**
     * @param string|null $passport_date_of_birth
     * @return Recipient
     */
    public function setPassportDateOfBirth($passport_date_of_birth)
    {
        $this->passport_date_of_birth = $passport_date_of_birth;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string|null $email
     * @return Recipient
     */
    public function setEmail($email)
    {
        $this->email = $email;
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
     * @param PhoneList $phones
     * @return Recipient
     */
    public function setPhones($phones)
    {
        $this->phones = $phones;
        return $this;
    }
}