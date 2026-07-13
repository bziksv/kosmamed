<?php
namespace Ipolh\SDEK\Core\Order;


/**
 * Class Receiver
 * @package Ipolh\SDEK\Core
 * @subpackage Order
 */
class Receiver extends AbstractPerson
{
    /**
     * @var string
     */
    protected $timeCall;
    /**
     * @var string
     */
    protected $additionalPhone;

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @return mixed
     */
    public function getTimeCall()
    {
        return $this->timeCall;
    }

    /**
     * @param mixed $timeCall
     * @return $this
     */
    public function setTimeCall($timeCall)
    {
        $this->timeCall = $timeCall;

        return $this;
    }


    /**
     * @return null|string
     */
    public function getAdditionalPhone()
    {
        return $this->additionalPhone;
    }

    /**
     * @param string $additionalPhone
     * @return $this
     */
    public function setAdditionalPhone($additionalPhone)
    {
        $this->additionalPhone = $additionalPhone;

        return $this;
    }
}