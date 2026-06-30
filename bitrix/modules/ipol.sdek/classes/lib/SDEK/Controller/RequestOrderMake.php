<?php
namespace Ipolh\SDEK\SDEK\Controller;

use DateTime;
use Ipolh\SDEK\Api\BadRequestException;
use Ipolh\SDEK\Api\Entity\Request\OrderMake;
use Ipolh\SDEK\Api\Entity\Request\Part\OrderMake\Item;
use Ipolh\SDEK\Api\Entity\Request\Part\OrderMake\ItemList;
use Ipolh\SDEK\Api\Entity\Request\Part\OrderMake\Package;
use Ipolh\SDEK\Api\Entity\Request\Part\OrderMake\PackageList;
use Ipolh\SDEK\Api\Entity\Request\Part\OrderMake\Sender;
use Ipolh\SDEK\Api\Entity\UniversalPart\CdekLocation;
use Ipolh\SDEK\Api\Entity\UniversalPart\Money;
use Ipolh\SDEK\Api\Entity\UniversalPart\Phone;
use Ipolh\SDEK\Api\Entity\UniversalPart\PhoneList;
use Ipolh\SDEK\Api\Entity\UniversalPart\Recipient;
use Ipolh\SDEK\Api\Entity\UniversalPart\Seller;
use Ipolh\SDEK\Api\Entity\UniversalPart\Service;
use Ipolh\SDEK\Api\Entity\UniversalPart\ServiceList;
use Ipolh\SDEK\Core\Order\Address;
use Ipolh\SDEK\Core\Order\ItemCollection;
use Ipolh\SDEK\Core\Order\Order;
use Ipolh\SDEK\SDEK\Entity\OrderMakeResult as ResultObj;
use Exception;

/**
 * Class RequestOrderMake
 * @package Ipolh\SDEK\SDEK\Controller
 */
class RequestOrderMake extends AutomatedCommonRequest
{
    /**
     * @var Order
     */
    protected $coreOrder;
    
    /**
     * @var string|null $developerKey
     */
    protected $developerKey;

    /**
     * RequestOrderMake constructor.
     * @param ResultObj $resultObj
     * @param Order $order
     * @param int|null $type 1 - IM | 2 - regular shipping. Default on CDEK server is 1
     * @param string|null $developerKey
     */
    public function __construct($resultObj, $order, $type = null, $developerKey = null)
    {
        parent::__construct($resultObj);
        $this->coreOrder = $order;
        $this->developerKey = $developerKey;
        $this->requestObj = new OrderMake();
        $this->requestObj
            ->setType($type)
            ->setDeveloperKey($developerKey);
    }

    public function getSelfHash()
    {
        return $this->getSelfHashByRequestObj().md5(serialize($this->coreOrder)); // TODO: make it better
    }

    /**
     * @return $this
     * @throws BadRequestException
     */
    public function convert()
    {
        $coreOrder = $this->coreOrder;

        $this->setPointOrLocationFrom();
        $this->setPointOrLocationTo();
        $this->setInternationalFields();

        $this->requestObj
            ->setNumber($coreOrder->getNumber())
            ->setTariffCode($coreOrder->getField('tariffCode'))
            ->setComment($coreOrder->getField('comment'))

            ->setDeliveryRecipientCost($this->generateDeliveryRecipientCost())
            ->setDeliveryRecipientCostAdv(null) // TODO: implement deliveryRecipientCostAdv

            ->setRecipient($this->generateRecipient())

            ->setServices($this->generateServices()) // Additional services

            ->setPackages($this->generatePackages());

        $sender = $this->generateSender();
        if($sender){
            $this->requestObj->setSender($sender);
        }
        $seller = $this->generateSeller(); // Real seller
        if($seller){
            $this->requestObj->setSeller($seller);
        }

        if (in_array($coreOrder->getField('print'), ['barcode', 'waybill']))
            $this->requestObj->setPrint($coreOrder->getField('print'));

        return $this;
    }

    /**
     * Set shipment point or location from
     */
    private function setPointOrLocationFrom()
    {
        $coreOrder = $this->coreOrder;
        if ($coreAddress = $coreOrder->getAddressFrom()) {
            if ($pointId = $coreAddress->getField('pointId')) {
                $this->requestObj->setShipmentPoint($pointId);
            } else {
                $this->requestObj->setFromLocation($this->makeCdekLocation($coreAddress));
            }
        }
    }

    /**
     * Set delivery point or location to
     */
    private function setPointOrLocationTo()
    {
        $coreOrder = $this->coreOrder;
        if ($coreAddress = $coreOrder->getAddressTo()) {
            if ($pointId = $coreAddress->getField('pointId')) {
                $this->requestObj->setDeliveryPoint($pointId);
            } else {
                $this->requestObj->setToLocation($this->makeCdekLocation($coreAddress));
            }
        }
    }

    /**
     * Makes CDEK location from Core Address
     * @param Address $coreAddress
     * @return CdekLocation
     */
    private function makeCdekLocation($coreAddress)
    {
        $location = new CdekLocation();
        $location
            ->setCode($coreAddress->getCode())
            ->setFiasGuid($coreAddress->getField('cityFiasGuid'))
            ->setPostalCode($coreAddress->getZip())
            ->setLongitude($coreAddress->getLng())
            ->setLatitude($coreAddress->getLat())
            ->setCountryCode($coreAddress->getField('countryCode')) // ISO_3166-1_alpha-2
            ->setRegion($coreAddress->getRegion())
            ->setRegionCode($coreAddress->getField('regionCode')) // CDEK region code
            ->setSubRegion($coreAddress->getField('subRegion'))
            ->setCity($coreAddress->getCity())
            ->setKladrCode($coreAddress->getField('kladrCode'))
            ->setAddress($coreAddress->getAddress() ?: null);

        return $location;
    }

    /**
     * Set specific fields for international delivery
     */
    private function setInternationalFields()
    {
        if (!$this->coreOrder->getField('isInternational')) {
            return;
        }

        /**@var $dateInvoice DateTime*/
        $dateInvoice = $this->coreOrder->getField('dateInvoice');

        if ($coreSender = $this->coreOrder->getSender()) {
            $this->requestObj
                ->setDateInvoice($dateInvoice->format('Y-m-d'))
                ->setShipperName($coreSender->getFullName())
                ->setShipperAddress($coreSender->getField('shipperAddress'));
        }
    }

    /**
     * @return Money|null
     */
    private function generateDeliveryRecipientCost()
    {
        if (!$payment = $this->coreOrder->getPayment()) {
            return null;
        }

        if ($payment->getIsBeznal() || $payment->getField('usualDelivery') === false) {
            return null;
        }

        $apiMoney = new Money();
        $apiMoney->setValue($payment->getDelivery()->getAmount());

        if (!is_null($payment->getNdsDelivery())) {
            // VAT rate 0+
            $apiMoney
                ->setVatRate($payment->getNdsDelivery())
                ->setVatSum($payment->getDelivery()->getAmount() * ($payment->getNdsDelivery() / (100 + $payment->getNdsDelivery())));
        }

        return $apiMoney;
    }

    /**
     * @throws BadRequestException
     */
    private function generateSender()
    {
        $coreOrder = $this->coreOrder;

        if ($coreSender = $coreOrder->getSender()) {
            if($coreSender->getPhone()) {
                $sender = new Sender();
                try {
                    $senderPhone = new Phone($coreSender->getPhone());
                    $senderPhone->setAdditional($coreSender->getField('phoneAdditional'));

                    $senderPhoneList = new PhoneList();
                    $senderPhoneList->add($senderPhone);

                    $sender
                        ->setCompany($coreSender->getCompany())
                        ->setName($coreSender->getFullName())
                        ->setEmail($coreSender->getEmail())
                        ->setPhones($senderPhoneList);
                } catch (Exception $e) {
                    throw new BadRequestException('Not all sender fields are set');
                }
            } else {
                $sender=false;
            }

            return $sender;
        } else {
            throw new BadRequestException('Not all sender fields are set');
        }
    }

    /**
     * @return Seller|null
     * @throws BadRequestException
     */
    private function generateSeller()
    {
        if (in_array($this->requestObj->getType(), [null, 1], true)) {
            // Seller required only for international IM delivery
            if ($coreSender = $this->coreOrder->getSender()) {
                if ($coreSender->getField('sellerCompany') || $coreSender->getField('sellerPhone') || $coreSender->getField('sellerAddress')) {
                    $seller = new Seller();
                    $seller
                        ->setName($coreSender->getField('sellerCompany'))
                        ->setInn($coreSender->getField('inn'))
                        ->setPhone($coreSender->getField('sellerPhone'))
                        ->setOwnershipForm($coreSender->getField('ownershipForm'))
                        ->setAddress($coreSender->getField('sellerAddress'));
                    return $seller;
                } else {
                    return null;
                }
            } else {
                throw new BadRequestException('Not all seller fields are set');
            }
        }
        return null;
    }

    /**
     * @return Recipient
     */
    private function generateRecipient()
    {
        $coreOrder = $this->coreOrder;
        $recipient = new Recipient();
        $phones = new PhoneList();

        if ($receiver = $coreOrder->getReceivers()->getFirst()) {
            if ($receiver->getPhone()) {
                $phone = new Phone();
                $phone->setNumber($receiver->getPhone());
                $phones->add($phone);
            }

            if ($receiver->getAdditionalPhone()) {
                $additionalPhone = new Phone();
                $additionalPhone->setNumber($receiver->getAdditionalPhone());
                $phones->add($additionalPhone);
            }

            $recipient
                ->setCompany($receiver->getField('company'))
                ->setName($receiver->getFullName())
                ->setPassportSeries($receiver->getField('passport_series'))
                ->setPassportNumber($receiver->getField('passport_number'))
                ->setPassportDateOfIssue(($receiver->getField('passport_date_of_issue')) ? $receiver->getField('passport_date_of_issue')->format('Y-m-d') : null)
                ->setPassportOrganization($receiver->getField('passport_organization'))
                ->setTin($receiver->getField('tin')) // inn
                ->setPassportDateOfBirth(($receiver->getField('passport_date_of_birth')) ? $receiver->getField('passport_date_of_birth')->format('Y-m-d') : null)
                ->setEmail($receiver->getEmail())
                ->setPhones($phones);
        }

        return $recipient;
    }

    /**
     * @return ServiceList|null
     */
    private function generateServices()
    {
        $coreOrder = $this->coreOrder;

        if ($coreServices = $coreOrder->getField('services')) {
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

    /**
     * @return PackageList
     */
    private function generatePackages()
    {
        $coreOrder = $this->coreOrder;

        $packageList = new PackageList();

        // TODO: multi package ?
        $items = $coreOrder->getItems();
        $goods = $coreOrder->getGoods();
        if($coreOrder->getField('packages')){
            foreach ($coreOrder->getField('packages') as $packageIndex => $packageData){
                $package = new Package();
                $package
                    ->setNumber($packageIndex)
                    ->setWeight((int)(ceil($packageData['WEIGHT']) ?: 1))
                    ->setLength((int)(ceil($packageData['LENGTH']) ?: 1))
                    ->setHeight((int)(ceil($packageData['HEIGHT']) ?: 1))
                    ->setWidth((int)(ceil($packageData['WIDTH']) ?: 1));

                $this->setPackageItems($package, $packageData['GOODS']);

                $packageList->add($package);
            }
        } elseif ($items && $goods) {
            $package = new Package();
            $package
                ->setNumber(1)
                ->setWeight((int)(ceil($goods->getWeight()) ?: 1))
                ->setLength((int)(ceil($goods->getLength()) ?: 1))
                ->setHeight((int)(ceil($goods->getHeight()) ?: 1))
                ->setWidth((int)(ceil($goods->getWidth()) ?: 1))
                ->setComment($goods->getDetails());

            $this->setPackageItems($package, $items);

            $packageList->add($package);
        }

        return $packageList;
    }

    /**
     * @param Package $package
     * @param ItemCollection $coreItems
     */
    private function setPackageItems($package, $coreItems)
    {
        $items = new ItemList();

        $coreItems->reset();
        while ($coreItem = $coreItems->getNext()) {
            $item = new Item();

            $apiMoney = new Money();
            $apiMoney->setValue($coreItem->getPrice()->getAmount());

            if (!is_null($coreItem->getVatRate())) {
                // VAT rate 0+
                $apiMoney
                    ->setVatRate($coreItem->getVatRate())
                    ->setVatSum($coreItem->getVatSum()->getAmount());
            }

            $item
                ->setName($coreItem->getName())
                ->setWareKey($coreItem->getArticul())
                ->setMarking($coreItem->getField('marking'))
                ->setPaymentFromObject($apiMoney)
                ->setCost($coreItem->getCost()->getAmount())
                ->setWeight((int)(ceil($coreItem->getWeight()) ?: 1))
                ->setWeightGross($coreItem->getField('weightGross'))
                ->setAmount($coreItem->getQuantity())
                ->setNameI18n($coreItem->getField('nameI18n'))
                ->setBrand($coreItem->getField('brand'))
                ->setCountryCode($coreItem->getField('countryCode'))
                ->setMaterial($coreItem->getField('material'))
                ->setWifiGsm($coreItem->getField('wifiGsm'))
                ->setUrl($coreItem->getField('url'))
                ->setFeacnCode($coreItem->getField('feacn'));

            $items->add($item);
        }

        $package->setItemsFromObject($items);
    }
}