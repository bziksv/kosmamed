<?php
namespace Ipolh\SDEK\Api\Entity\UniversalPart;

use Ipolh\SDEK\Api\Entity\AbstractEntity;

class Service extends AbstractEntity
{
    /**
     * @var string
     */
    protected $code;

    /**
     * @var string|null
     */
    protected $parameter;

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     * @return Service
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getParameter()
    {
        return $this->parameter;
    }

    /**
     * @param string|null $parameter
     * @return Service
     */
    public function setParameter($parameter)
    {
        $this->parameter = $parameter;
        return $this;
    }

}