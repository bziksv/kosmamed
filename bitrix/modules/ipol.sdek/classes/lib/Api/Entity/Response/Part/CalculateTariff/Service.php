<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\CalculateTariff;

use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;

class Service extends \Ipolh\SDEK\Api\Entity\UniversalPart\Service
{
    use AbstractResponsePart;

    /**
     * @var float
     */
    protected $sum;

    /**
     * @return float
     */
    public function getSum()
    {
        return $this->sum;
    }

    /**
     * @param float $sum
     * @return Service
     */
    public function setSum($sum)
    {
        $this->sum = $sum;
        return $this;
    }
}