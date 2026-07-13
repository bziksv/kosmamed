<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo;

/**
 * Class Status
 * @package Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo
 */
class Status extends \Ipolh\SDEK\Api\Entity\Response\Part\Common\Status
{
    /**
     * @var string|null
     */
    protected $reason_code;

    /**
     * @var string
     */
    protected $city;

    /**
     * @return string|null
     */
    public function getReasonCode()
    {
        return $this->reason_code;
    }

    /**
     * @param string|null $reason_code
     * @return Status
     */
    public function setReasonCode($reason_code)
    {
        $this->reason_code = $reason_code;
        return $this;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     * @return Status
     */
    public function setCity($city)
    {
        $this->city = $city;
        return $this;
    }
}