<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo;

use Ipolh\SDEK\Api\Entity\AbstractEntity;
use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;

class PaymentInfo extends AbstractEntity
{
    use AbstractResponsePart;

    /**
     * 'CARD' | 'CASH'
     * @var string
     */
    protected $type;

    /**
     * @var float
     */
    protected $sum;

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return PaymentInfo
     */
    public function setType($type)
    {
        $this->type = $type;
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
     * @return PaymentInfo
     */
    public function setSum($sum)
    {
        $this->sum = $sum;
        return $this;
    }
}