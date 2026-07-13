<?php
namespace Ipolh\SDEK\SDEK\Controller;

use DateTime;
use Ipolh\SDEK\Api\Entity\Request\CalculateList as RequestObj;
use Ipolh\SDEK\SDEK\Entity\CalculateListResult as ResultObj;
use Ipolh\SDEK\Core\Delivery\Shipment;

/**
 * Class RequestCalculateList
 * @package Ipolh\SDEK\SDEK
 * @subpackage Controller
 * @method RequestObj getRequestObj
 */
class RequestCalculateList extends AutomatedCommonRequest
{
    use RequestCalculate;

    /**
     * RequestCalculateList constructor.
     * @param ResultObj $resultObj
     * @param Shipment $coreShipment
     * @param DateTime|null $date
     * @param string|null $responseLang
     * @param int|null $currency
     * @param int|null $deliveryType
     */
    public function __construct(
        $resultObj,
        $coreShipment,
        $date,
        $responseLang,
        $currency,
        $deliveryType
    )
    {
        parent::__construct($resultObj);
        $this->coreShipment = $coreShipment;

        $this->requestObj = new RequestObj();
        $this->requestObj->setDate(($date) ? $date->format(DATE_ISO8601) : null)
            ->setType($deliveryType)
            ->setLang($responseLang)
            ->setCurrency($currency);
    }

    /**
     * @return $this
     */
    public function convert()
    {
        $this->requestObj
            ->setFromLocation($this->generateLocationFrom())
            ->setToLocation($this->generateLocationTo())
            ->setPackages($this->generatePackages());

        return $this;
    }
}