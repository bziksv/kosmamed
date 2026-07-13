<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo;

use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\AbstractEntity;
use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;

/**
 * Class Calls
 * @package Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo
 */
class Calls extends AbstractEntity
{
    use AbstractResponsePart;

    /**
     * @var FailedCallList|null
     */
    protected $failed_calls;

    /**
     * @var RescheduledCallList|null
     */
    protected $rescheduled_calls;

    /**
     * @return FailedCallList|null
     */
    public function getFailedCalls()
    {
        return $this->failed_calls;
    }

    /**
     * @param array $array
     * @return Calls
     * @throws BadResponseException
     */
    public function setFailedCalls($array)
    {
        $collection = new FailedCallList();
        $this->failed_calls = $collection->fillFromArray($array);
        return $this;
    }

    /**
     * @return RescheduledCallList|null
     */
    public function getRescheduledCalls()
    {
        return $this->rescheduled_calls;
    }

    /**
     * @param array $array
     * @return Calls
     * @throws BadResponseException
     */
    public function setRescheduledCalls($array)
    {
        $collection = new RescheduledCallList();
        $this->rescheduled_calls = $collection->fillFromArray($array);
        return $this;
    }
}