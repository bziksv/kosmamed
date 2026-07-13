<?php
namespace Ipolh\SDEK\SDEK\Controller;

use Ipolh\SDEK\Api\Entity\Request\OrderInfoNumberMulti as RequestObj;
use Ipolh\SDEK\Api\Entity\Request\Part\OrderInfoNumberMulti\OrderInfoNumber;
use Ipolh\SDEK\Api\Entity\Request\Part\OrderInfoNumberMulti\OrderInfoNumberList;
use Ipolh\SDEK\SDEK\Entity\OrderInfoMultiResult as ResultObj;

/**
 * Class RequestOrderInfoByNumberMulti
 * @package Ipolh\SDEK\SDEK
 * @subpackage Controller
 * @method RequestObj getRequestObj
 */
class RequestOrderInfoByNumberMulti extends AutomatedCommonRequest
{
    /**
     * @var string[]
     */
    protected $cdek_numbers;

    /**
     * RequestOrderInfoByNumberMulti constructor.
     * @param ResultObj $resultObj
     * @param string[] $cdek_numbers
     */
    public function __construct($resultObj, $cdek_numbers)
    {
        parent::__construct($resultObj);
        $this->cdek_numbers = $cdek_numbers;

        $this->requestObj = new RequestObj();
    }

    public function getSelfHash()
    {
        return $this->getSelfHashByRequestObj().md5(serialize([$this->cdek_numbers]));
    }

    /**
     * @return $this
     */
    public function convert()
    {
        $orderInfoNumbers = new OrderInfoNumberList();
        foreach ($this->cdek_numbers as $cdek_number) {
            $orderInfoNumber = new OrderInfoNumber();
            $orderInfoNumber->setCdekNumber($cdek_number);

            $orderInfoNumbers->add($orderInfoNumber);
        }

        $this->requestObj->setMultiRequests($orderInfoNumbers);

        return $this;
    }
}