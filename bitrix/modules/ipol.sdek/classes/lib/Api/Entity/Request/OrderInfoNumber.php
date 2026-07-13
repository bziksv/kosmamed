<?php
namespace Ipolh\SDEK\Api\Entity\Request;

class OrderInfoNumber extends AbstractRequest
{
    /**
     * @var string
     */
    protected $cdek_number;

    /**
     * @return string
     */
    public function getCdekNumber()
    {
        return $this->cdek_number;
    }

    /**
     * @param string $cdek_number
     * @return OrderInfoNumber
     */
    public function setCdekNumber($cdek_number)
    {
        $this->cdek_number = $cdek_number;
        return $this;
    }
}