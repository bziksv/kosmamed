<?php
namespace Ipolh\SDEK\Api\Entity\Response;

use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\Response\Part\CalculateList\TariffCodesList;
use Ipolh\SDEK\Api\Entity\Response\Part\Common\ErrorList;

/**
 * Class CalculateList
 * @package Ipolh\SDEK\Api\Entity\Response
 */
class CalculateList extends AbstractResponse
{
    /**
     * @var TariffCodesList|null
     */
    protected $tariff_codes;
    /**
     * @var ErrorList|null
     */
    protected $errors;

    /**
     * @return TariffCodesList|null
     */
    public function getTariffCodes()
    {
        return $this->tariff_codes;
    }

    /**
     * @param array $array
     * @return CalculateList
     * @throws BadResponseException
     */
    public function setTariffCodes($array)
    {
        $collection = new TariffCodesList();
        $this->tariff_codes = $collection->fillFromArray($array);
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
     * @param array $array
     * @return CalculateList
     * @throws BadResponseException
     */
    public function setErrors($array)
    {

        $collection = new ErrorList();
        $this->errors = $collection->fillFromArray($array);
        return $this;

    }

}