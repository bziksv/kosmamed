<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo;

use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;

/**
 * Class Sender
 * @package Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo
 */
class Sender extends \Ipolh\SDEK\Api\Entity\UniversalPart\Sender
{
    use AbstractResponsePart;

    /**
     * @var bool|null
     */
    protected $passport_requirements_satisfied;

    /**
     * @var string|null
     */
    protected $email;

    /**
     * @return bool|null
     */
    public function isPassportRequirementsSatisfied()
    {
        return $this->passport_requirements_satisfied;
    }

    /**
     * @param bool|null $passport_requirements_satisfied
     * @return Sender
     */
    public function setPassportRequirementsSatisfied($passport_requirements_satisfied)
    {
        $this->passport_requirements_satisfied = $passport_requirements_satisfied;
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
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @param array $phoneList
     * @return Sender
     * @throws BadResponseException
     */
    public function setPhones($phoneList)
    {
        $collection = new PhoneList();
        parent::setPhones($collection->fillFromArray($phoneList));
        return $this;
    }
}