<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\DeliveryPoints;

use DateTime;
use Exception;
use Ipolh\SDEK\Api\Entity\AbstractEntity;
use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;

/**
 * Class WorkTimeException
 * @package Ipolh\SDEK\Api\Entity\Response\Part\DeliveryPoints
 */
class WorkTimeException extends AbstractEntity
{
    use AbstractResponsePart;

    /**
     * @var DateTime
     */
    protected $date;
    /**
     * @var string(20)|null
     */
    protected $time;
    /**
     * @var boolean
     */
    protected $is_working;

    /**
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param string $date
     * @return WorkTimeException
     * @throws Exception
     */
    public function setDate($date)
    {
        $this->date = new DateTime($date);
        return $this;
    }

    /**
     * @return string|null
     */
    public function getTime()
    {
        return $this->time;
    }

    /**
     * @param string|null $time
     * @return WorkTimeException
     */
    public function setTime($time)
    {
        $this->time = $time;
        return $this;
    }

    /**
     * @return bool
     */
    public function isIsWorking()
    {
        return $this->is_working;
    }

    /**
     * @param bool $is_working
     * @return WorkTimeException
     */
    public function setIsWorking($is_working)
    {
        $this->is_working = $is_working;
        return $this;
    }

}