<?php
namespace Ipolh\SDEK\Api\Entity\UniversalPart;

use Ipolh\SDEK\Api\Entity\AbstractEntity;

/**
 * Class Money
 * @package Ipolh\SDEK\Api\Entity\UniversalPart
 */
class Money extends AbstractEntity
{
    /**
     * @var float
     */
    protected $value;
    /**
     * @var null|float
     */
    protected $vat_sum;
    /**
     * @var null|int - 0, 10, 18, 20. null - without VAT
     */
    protected $vat_rate;

    /**
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param float $value
     * @return Money
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return float|null
     */
    public function getVatSum()
    {
        return $this->vat_sum;
    }

    /**
     * @param float|null $vat_sum
     * @return Money
     */
    public function setVatSum($vat_sum)
    {
        $this->vat_sum = $vat_sum;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getVatRate()
    {
        return $this->vat_rate;
    }

    /**
     * @param int|null $vat_rate
     * @return Money
     */
    public function setVatRate($vat_rate)
    {
        $this->vat_rate = $vat_rate;
        return $this;
    }

}