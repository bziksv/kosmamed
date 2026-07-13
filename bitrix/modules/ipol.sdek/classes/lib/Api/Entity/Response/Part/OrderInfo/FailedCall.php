<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo;

use Ipolh\SDEK\Api\Entity\AbstractEntity;
use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;

class FailedCall extends AbstractEntity
{
    use AbstractResponsePart;

    /**
     * // TODO: check request data type
     * @var string
     */
    protected $date_time;

    /**
     * @var int
     */
    protected $reason_code;

    /**
     * @return string
     */
    public function getDateTime()
    {
        return $this->date_time;
    }

    /**
     * @param string $date_time
     * @return FailedCall
     */
    public function setDateTime($date_time)
    {
        $this->date_time = $date_time;
        return $this;
    }

    /**
     * @return int
     */
    public function getReasonCode()
    {
        return $this->reason_code;
    }

    /**
     * @param int $reason_code
     * @return FailedCall
     */
    public function setReasonCode($reason_code)
    {
        $this->reason_code = $reason_code;
        return $this;
    }
}