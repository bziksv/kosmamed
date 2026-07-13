<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo;

use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;

class Service extends \Ipolh\SDEK\Api\Entity\UniversalPart\Service
{
    use AbstractResponsePart;

    /**
     * @var float|null
     */
    protected $sum;

    /**
     * @return float|null
     */
    public function getSum()
    {
        return $this->sum;
    }

    /**
     * @param float|null $sum
     * @return Service
     */
    public function setSum($sum)
    {
        $this->sum = $sum;
        return $this;
    }
}