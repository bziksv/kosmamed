<?php
namespace Ipolh\SDEK\SDEK\Controller;

use DateTime;
use Ipolh\SDEK\Api\Entity\Request\CalculateTariff as RequestObj;
use Ipolh\SDEK\Api\Entity\UniversalPart\ServiceList;
use Ipolh\SDEK\Api\Entity\UniversalPart\Service;
use Ipolh\SDEK\SDEK\Entity\CalculateTariffResult as ResultObj;
use Ipolh\SDEK\Core\Delivery\Shipment;

/**
 * Class RequestCalculateTariff
 * @package Ipolh\SDEK\SDEK
 * @subpackage Controller
 * @method RequestObj getRequestObj
 */
class RequestCalculateTariff extends AutomatedCommonRequest
{
    use RequestCalculate;

    /**
     * RequestCalculateTariff constructor.
     * @param ResultObj $resultObj
     * @param Shipment $coreShipment
     * @param int $tariff_code CDEK tariff number
     * @param DateTime|null $date
     * @param int|null $type
     * @param int|null $currency
     */
    public function __construct(
        $resultObj,
        $coreShipment,
        $tariff_code,
        $date,
        $type,
        $currency
    )
    {
        parent::__construct($resultObj);
        $this->coreShipment = $coreShipment;

        $this->requestObj = new RequestObj();
        $this->requestObj
            ->setTariffCode($tariff_code)
            ->setDate(($date) ? $date->format(DATE_ISO8601) : null)
            ->setType($type)
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
            ->setServices($this->generateServices())
            ->setPackages($this->generatePackages());

        return $this;
    }

    /**
     * @return ServiceList|null
     */
    protected function generateServices()
    {
        if ($coreServices = $this->coreShipment->getField('services')) {
            if (!empty($coreServices) && is_array($coreServices)) {
                $services = new ServiceList();

                foreach ($coreServices as $code => $parameter) {
                    $service = new Service();
                    $service->setCode($code)->setParameter($parameter);
                    $services->add($service);
                }

                return $services;
            }
        }
        return null;
    }
}