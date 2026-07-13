<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo;

use Ipolh\SDEK\Api\Entity\AbstractEntity;
use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;

class RescheduledCall extends AbstractEntity
{
    use AbstractResponsePart;

    /**
     * // TODO: check request data type
     * @var string
     */
    protected $date_time;

    /**
     * // TODO: check request data type
     * @var string
     */
    protected $date_next;

    /**
     * // TODO: check request data type
     * @var string
     */
    protected $time_next;

    /**
     * @var string|null
     */
    protected $comment;

    /**
     * @return string
     */
    public function getDateTime()
    {
        return $this->date_time;
    }

    /**
     * @param string $date_time
     * @return RescheduledCall
     */
    public function setDateTime($date_time)
    {
        $this->date_time = $date_time;
        return $this;
    }

    /**
     * @return string
     */
    public function getDateNext()
    {
        return $this->date_next;
    }

    /**
     * @param string $date_next
     * @return RescheduledCall
     */
    public function setDateNext($date_next)
    {
        $this->date_next = $date_next;
        return $this;
    }

    /**
     * @return string
     */
    public function getTimeNext()
    {
        return $this->time_next;
    }

    /**
     * @param string $time_next
     * @return RescheduledCall
     */
    public function setTimeNext($time_next)
    {
        $this->time_next = $time_next;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @param string|null $comment
     * @return RescheduledCall
     */
    public function setComment($comment)
    {
        $this->comment = $comment;
        return $this;
    }
}