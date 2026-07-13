<?php
namespace Ipolh\SDEK\SDEK\Controller;

use Ipolh\SDEK\Api\Entity\Request\OrderInfoByImNumber;
use Ipolh\SDEK\SDEK\Entity\OrderInfoResult as ResultObj;

/**
 * Class RequestOrderInfoByImNumber
 * @package Ipolh\SDEK\SDEK\Controller
 */
class RequestOrderInfoByImNumber extends AutomatedCommonRequest
{
    /**
     * RequestOrderInfoByImNumber constructor.
     * @param ResultObj $resultObj
     * @param string $imOrderNumber
     */
    public function __construct($resultObj, $imOrderNumber)
    {
        parent::__construct($resultObj);
        $this->requestObj = new OrderInfoByImNumber();
        $this->requestObj->setImNumber($imOrderNumber);
    }
}