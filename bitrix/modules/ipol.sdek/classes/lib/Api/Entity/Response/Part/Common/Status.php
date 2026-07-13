<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\Common;

use DateTime;
use Exception;
use Ipolh\SDEK\Api\Entity\AbstractEntity;
use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;

/**
 * Class Status
 * @package Ipolh\SDEK\Api\Entity\Response\Part\Common
 */
class Status extends AbstractEntity
{
    use AbstractResponsePart;

    /**
     * @var string
     */
    protected $code;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var DateTime
     */
    protected $date_time;

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Status
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Status
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getDateTime()
    {
        return $this->date_time;
    }

    /**
     * @param string $date_time
     * @return Status
     * @throws Exception
     */
    public function setDateTime($date_time)
    {
        $this->date_time = new DateTime($date_time);
        return $this;
    }
}