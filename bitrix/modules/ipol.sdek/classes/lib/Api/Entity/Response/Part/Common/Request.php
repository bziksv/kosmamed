<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\Common;

use DateTime;
use Exception;
use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\AbstractEntity;
use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;

/**
 * Class Request
 * @package Ipolh\SDEK\Api\Entity\Response\Part\Common
 */
class Request extends AbstractEntity
{
    use AbstractResponsePart;

    /**
     * @var string|null
     */
    protected $request_uuid;

    /**
     * @var string (CREATE, UPDATE, DELETE, AUTH, GET)
     */
    protected $type;

    /**
     * @var DateTime (yyyy-MM-dd'T'HH:mm:ssZ)
     */
    protected $date_time;

    /**
     * @var string (ACCEPTED, WAITING, SUCCESSFUL, INVALID)
     */
    protected $state;

    /**
     * @var ErrorList|null
     */
    protected $errors;

    /**
     * @var WarningList|null
     */
    protected $warnings;

    /**
     * @return string|null
     */
    public function getRequestUuid()
    {
        return $this->request_uuid;
    }

    /**
     * @param string|null $request_uuid
     * @return Request
     */
    public function setRequestUuid($request_uuid)
    {
        $this->request_uuid = $request_uuid;
        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     * @return Request
     */
    public function setType($type)
    {
        $this->type = $type;
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
     * @return Request
     * @throws Exception
     */
    public function setDateTime($date_time)
    {
        $this->date_time = new DateTime($date_time);
        return $this;
    }

    /**
     * @return string
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * @param string $state
     * @return Request
     */
    public function setState($state)
    {
        $this->state = $state;
        return $this;
    }

    /**
     * @return ErrorList|null
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param array $array
     * @return Request
     * @throws BadResponseException
     */
    public function setErrors(array $array)
    {
        $collection = new ErrorList();
        $this->errors = $collection->fillFromArray($array);
        return $this;
    }

    /**
     * @return WarningList|null
     */
    public function getWarnings()
    {
        return $this->warnings;
    }

    /**
     * @param array $array
     * @return Request
     * @throws BadResponseException
     */
    public function setWarnings(array $array)
    {
        $collection = new WarningList();
        $this->warnings = $collection->fillFromArray($array);
        return $this;
    }
}