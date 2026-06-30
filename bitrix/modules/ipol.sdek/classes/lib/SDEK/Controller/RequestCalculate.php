<?php
namespace Ipolh\SDEK\SDEK\Controller;

use Ipolh\SDEK\Api\Entity\UniversalPart\CdekLocation;
use Ipolh\SDEK\Api\Entity\UniversalPart\PackageList;
use Ipolh\SDEK\Api\Entity\UniversalPart\Package;
use Ipolh\SDEK\Core\Delivery\Shipment;

trait RequestCalculate
{
    /**
     * @var Shipment
     */
    protected $coreShipment;

    /**
     * @return string
     */
    public function getSelfHash()
    {
        return $this->getSelfHashByRequestObj() . $this->coreShipment->getHash();
    }

    /**
     * @return CdekLocation
     */
    protected function generateLocationFrom()
    {
        $coreFrom = $this->coreShipment->getFrom();
        $cdekFrom = new CdekLocation();
        $cdekFrom
            ->setCode($coreFrom->getId())
            ->setPostalCode($coreFrom->getZip())
            ->setCountryCode($coreFrom->getField('countryCode')) // ISO_3166-1_alpha-2
            ->setCity($coreFrom->getName())
            ->setAddress($coreFrom->getField('fullAddress'));

        return $cdekFrom;
    }

    /**
     * @return CdekLocation
     */
    protected function generateLocationTo()
    {
        $coreTo = $this->coreShipment->getTo();
        $cdekTo = new CdekLocation();
        $cdekTo
            ->setCode($coreTo->getId())
            ->setPostalCode($coreTo->getZip())
            ->setCountryCode($coreTo->getField('countryCode')) // ISO_3166-1_alpha-2
            ->setCity($coreTo->getName())
            ->setAddress($coreTo->getField('fullAddress'));

        return $cdekTo;
    }

    /**
     * @return PackageList
     */
    protected function generatePackages()
    {
        $packCollection = new PackageList();

        $this->coreShipment->getCargoes()->reset();
        while($cargo = $this->coreShipment->getCargoes()->getNext()){
            $pack = new Package();
            $pack->setHeight((int)(ceil($cargo->getDimensions()['H']/10) ?: 1))
                ->setWidth((int)(ceil($cargo->getDimensions()['W']/10) ?: 1))
                ->setLength((int)(ceil($cargo->getDimensions()['L']/10) ?: 1))
                ->setWeight((int)(ceil($cargo->getWeight()) ?: 1));
            $packCollection->add($pack);
        }

        return $packCollection;
    }
}