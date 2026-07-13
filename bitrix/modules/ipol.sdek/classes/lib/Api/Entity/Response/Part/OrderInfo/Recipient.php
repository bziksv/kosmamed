<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo;

use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;

/**
 * Class Recipient
 * @package Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo
 */
class Recipient extends \Ipolh\SDEK\Api\Entity\UniversalPart\Recipient
{
    use AbstractResponsePart;

    /**
     * @var bool|null
     */
    protected $passport_requirements_satisfied;

    /**
     * @return bool|null
     */
    public function isPassportRequirementsSatisfied()
    {
        return $this->passport_requirements_satisfied;
    }

    /**
     * @param bool|null $passport_requirements_satisfied
     * @return Recipient
     */
    public function setPassportRequirementsSatisfied($passport_requirements_satisfied)
    {
        $this->passport_requirements_satisfied = $passport_requirements_satisfied;
        return $this;
    }

    /**
     * @param array $phones
     * @return Recipient
     * @throws BadResponseException
     */
    public function setPhones($phones)
    {
        $collection = new PhoneList();
        parent::setPhones($collection->fillFromArray($phones));
        return $this;
    }
}