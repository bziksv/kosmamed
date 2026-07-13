<?php
namespace Ipolh\SDEK\SDEK\Controller;

use Ipolh\SDEK\Api\Entity\Request\OrderInfoNumber;
use Ipolh\SDEK\SDEK\Entity\OrderInfoResult as ResultObj;

/**
 * Class RequestOrderInfoByNumber
 * @package Ipolh\SDEK\SDEK\Controller
 */
class RequestOrderInfoByNumber extends AutomatedCommonRequest
{
    /**
     * RequestOrderInfoByNumber constructor.
     * @param ResultObj $resultObj
     * @param string $sdekOrderNumber
     */
	public function __construct($resultObj, $sdekOrderNumber)
	{
	    parent::__construct($resultObj);
		$this->requestObj = new OrderInfoNumber();
        $this->requestObj->setCdekNumber($sdekOrderNumber);
	}
}