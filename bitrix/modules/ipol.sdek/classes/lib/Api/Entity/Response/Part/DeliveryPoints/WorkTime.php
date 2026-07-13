<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\DeliveryPoints;

use Ipolh\SDEK\Api\Entity\AbstractEntity;
use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;

/**
 * Class WorkTime
 * @package Ipolh\SDEK\Api\Entity\Response\Part\DeliveryPoints
 */
class WorkTime extends AbstractEntity
{
    use AbstractResponsePart;

    /**
     * @var integer
     * 1 = monday, ... 7 = sunday
     */
    protected $day;
    /**
     * @var string(20)
     */
    protected $time;

    /**
     * @return int
     */
    public function getDay()
    {
        return $this->day;
    }

    /**
     * @param int $day
     * @return WorkTime
     */
    public function setDay($day)
    {
        $this->day = $day;
        return $this;
    }

    /**
     * @return string
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param string $time
     * @return WorkTime
     */
    public function setTime($time)
    {
        $this->time = $time;
        return $this;
    }
}