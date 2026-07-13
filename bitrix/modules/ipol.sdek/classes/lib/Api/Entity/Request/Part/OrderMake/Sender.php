<?php
namespace Ipolh\SDEK\Api\Entity\Request\Part\OrderMake;

/**
 * Class Sender
 * @package Ipolh\SDEK\Api\Entity\Request\Part\OrderMake
 */
class Sender extends \Ipolh\SDEK\Api\Entity\UniversalPart\Sender
{
    /**
     * @var string
     */
    protected $email;

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Sender
     */
    public function setEmail($email)
    {
        $this->email = $email;
        return $this;
    }
}