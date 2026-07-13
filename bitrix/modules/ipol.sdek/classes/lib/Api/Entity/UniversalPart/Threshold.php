<?php
namespace Ipolh\SDEK\Api\Entity\UniversalPart;

use Ipolh\SDEK\Api\Entity\AbstractEntity;

/**
 * Class Threshold
 * @package Ipolh\SDEK\Api\Entity\UniversalPart
 */
class Threshold extends AbstractEntity
{
    /**
     * @var int
     */
    protected $threshold;
    /**
     * @var float
     */
    protected $sum;
    /**
     * @var null|float
     */
    protected $vat_sum;
    /**
     * @var null|int
     */
    protected $vat_rate;

    /**
     * @return int
     */
    public function getThreshold()
    {
        return $this->threshold;
    }

    /**
     * @param int $threshold
     * @return Threshold
     */
    public function setThreshold($threshold)
    {
        $this->threshold = $threshold;
        return $this;
    }

    /**
     * @return float
     */
    public function getSum()
    {
        return $this->sum;
    }

    /**
     * @param float $sum
     * @return Threshold
     */
    public function setSum($sum)
    {
        $this->sum = $sum;
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
     * @return Threshold
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
     * @return Threshold
     */
    public function setVatRate($vat_rate)
    {
        $this->vat_rate = $vat_rate;
        return $this;
    }

}