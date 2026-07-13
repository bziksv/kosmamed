<?php
namespace Ipolh\SDEK\Api\Entity\Request;

class OrderInfoByImNumber extends AbstractRequest
{
    /**
     * @var string CMS order number
     */
    protected $im_number;

    /**
     * @return string
     */
    public function getImNumber()
    {
        return $this->im_number;
    }

    /**
     * @param string $im_number
     * @return OrderInfoByImNumber
     */
    public function setImNumber($im_number)
    {
        $this->im_number = $im_number;
        return $this;
    }
}