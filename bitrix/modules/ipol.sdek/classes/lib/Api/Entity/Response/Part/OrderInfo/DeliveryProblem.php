<?php
namespace Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo;

use Ipolh\SDEK\Api\Entity\AbstractEntity;
use Ipolh\SDEK\Api\Entity\Response\Part\AbstractResponsePart;

class DeliveryProblem extends AbstractEntity
{
    use AbstractResponsePart;

    /**
     * @var string|null
     */
    protected $code;

    /**
     * // TODO: check request data type
     * @var string|null
     */
    protected $create_date;

    /**
     * @return string|null
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string|null $code
     * @return DeliveryProblem
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * @return string|null
     */
    public function getCreateDate()
    {
        return $this->create_date;
    }

    /**
     * @param string|null $create_date
     * @return DeliveryProblem
     */
    public function setCreateDate($create_date)
    {
        $this->create_date = $create_date;
        return $this;
    }
}