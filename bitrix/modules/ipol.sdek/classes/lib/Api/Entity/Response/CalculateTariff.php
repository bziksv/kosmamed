<?php
namespace Ipolh\SDEK\Api\Entity\Response;

use Ipolh\SDEK\Api\Entity\Response\Part\CalculateTariff\Tariff;

/**
 * Class CalculateTariff
 * @package Ipolh\SDEK\Api\Entity\Response
 */
class CalculateTariff extends AbstractResponse
{
    /**
     * @var Tariff
     */
    protected $tariff;

    /**
     * @return Tariff
     */
    public function getTariff()
    {
        return $this->tariff;
    }

    /**
     * @param array $array
     * @return CalculateTariff
     */
    public function setTariff($array)
    {
        $this->tariff = new Tariff($array);
        return $this;
    }

    public function setFields($fields)
    {
        return parent::setFields(['tariff' => $fields]);
    }
}