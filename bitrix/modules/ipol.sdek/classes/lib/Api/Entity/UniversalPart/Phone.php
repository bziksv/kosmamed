<?php
namespace Ipolh\SDEK\Api\Entity\UniversalPart;

use Ipolh\SDEK\Api\Entity\AbstractEntity;

/**
 * Class Phone
 * @package Ipolh\SDEK\Api\Entity\UniversalPart
 */
class Phone extends AbstractEntity
{
    /**
     * @var string
     */
    protected $number;
    /**
     * @var string|null
     */
    protected $additional;

    public function __construct($number = null)
    {
        parent::__construct();

        if (!empty($number))
            $this->setNumber($number);
    }

    /**
     * @return string
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * @param string $number
     * @return Phone
     */
    public function setNumber($number)
    {
        $this->number = $number;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getAdditional()
    {
        return $this->additional;
    }

    /**
     * @param string|null $additional
     * @return Phone
     */
    public function setAdditional($additional)
    {
        $this->additional = $additional;
        return $this;
    }

}