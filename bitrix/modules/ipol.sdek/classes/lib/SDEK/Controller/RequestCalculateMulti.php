<?php
namespace Ipolh\SDEK\SDEK\Controller;

use DateTime;
use Ipolh\SDEK\Api\Entity\Request\CalculateMulti as RequestObj;
use Ipolh\SDEK\Api\Entity\UniversalPart\ServiceList;
use Ipolh\SDEK\Api\Entity\UniversalPart\Service;
use Ipolh\SDEK\Api\Entity\Request\Part\CalculateMulti\CalculateTariff;
use Ipolh\SDEK\Api\Entity\Request\Part\CalculateMulti\CalculateTariffList;
use Ipolh\SDEK\SDEK\Entity\CalculateMultiResult as ResultObj;
use Ipolh\SDEK\Core\Delivery\Shipment;

/**
 * Class RequestCalculateMulti
 * @package Ipolh\SDEK\SDEK
 * @subpackage Controller
 * @method RequestObj getRequestObj
 */
class RequestCalculateMulti extends AutomatedCommonRequest
{
    use RequestCalculate;

    /**
     * @var int[]
     */
    protected $tariff_codes;

    /**
     * @var DateTime|null
     */
    protected $date;

    /**
     * @var int|null
     */
    protected $type;

    /**
     * @var int|null
     */
    protected $currency;

    /**
     * RequestCalculateMulti constructor.
     * @param ResultObj $resultObj
     * @param Shipment $coreShipment
     * @param int[] $tariff_codes CDEK tariff numbers
     * @param DateTime|null $date
     * @param int|null $type
     * @param int|null $currency
     */
    public function __construct(
        $resultObj,
        $coreShipment,
        $tariff_codes,
        $date,
        $type,
        $currency
    )
    {
        parent::__construct($resultObj);
        $this->coreShipment = $coreShipment;
        $this->tariff_codes = $tariff_codes;
        $this->date         = $date;
        $this->type         = $type;
        $this->currency     = $currency;

        $this->requestObj = new RequestObj();
    }

    public function getSelfHash()
    {
        return $this->getSelfHashByRequestObj().$this->coreShipment->getHash().md5(serialize([$this->tariff_codes, $this->date, $this->type, $this->currency]));
    }

    /**
     * @return $this
     */
    public function convert()
    {
        $calculateTariffs = new CalculateTariffList();
        foreach ($this->tariff_codes as $tariff_code) {
            $calculateTariff = new CalculateTariff();

            $calculateTariff
                ->setTariffCode($tariff_code)
                ->setDate(($this->date) ? $this->date->format(DATE_ISO8601) : null)
                ->setType($this->type)
                ->setCurrency($this->currency)
                ->setFromLocation($this->generateLocationFrom())
                ->setToLocation($this->generateLocationTo())
                ->setServices($this->generateServices())
                ->setPackages($this->generatePackages());

            $calculateTariffs->add($calculateTariff);
        }

        $this->requestObj->setMultiRequests($calculateTariffs);

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