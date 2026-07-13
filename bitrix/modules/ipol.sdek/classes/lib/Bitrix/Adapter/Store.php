<?php
namespace Ipolh\SDEK\Bitrix\Adapter;

use Ipolh\SDEK\Bitrix\Entity\Options;
use Ipolh\SDEK\Bitrix\Tools;
use Ipolh\SDEK\Core\Order\Address;
use Ipolh\SDEK\Core\Order\Sender;
use Ipolh\SDEK\StoresTable;

/**
 * Class Store
 * @package Ipolh\SDEK\Bitrix\Adapter
 */
class Store implements \JsonSerializable
{
    /**
     * @var int|null
     */
    protected $id;

    /**
     * @var bool
     */
    protected $isActive;

    /**
     * @var Address - address, times for courier intake and from_location
     */
    protected $coreAddress;

    /**
     * @var Sender - sender data
     */
    protected $coreSender;

    /**
     * @var Sender - seller data
     */
    protected $coreSeller;

    /**
     * @var Options
     */
    protected $options;

    public function __construct(Options $options)
    {
        $this->id          = null;
        $this->isActive    = true;
        $this->coreAddress = new Address();
        $this->coreSender  = new Sender();
        $this->coreSeller  = new Sender();
        $this->options     = $options;
    }

    /**
     * Returns Store filled by default values
     * @return Store
     */
    public function newStore()
    {
        $this->setDefaultFields();
        $this->addCityData($this->coreAddress->getCode());

        return $this;
    }

    /**
     * Returns Store filled from $_REQUEST data (StoreForm AJAX call)
     * @return Store
     */
    public function requestStore()
    {
        // Deal with cp1251
        if (Tools::isModuleAjaxRequest()) {
            $_REQUEST = Tools::encodeFromUTF8($_REQUEST);
        }

        $this->fromArray(self::fromRequest());

        return $this;
    }

    /**
     * Returns Store filled from StoresTable data
     * @param int $id
     * @return Store
     */
    public function uploadedStore($id)
    {
        $this->id = $id;
        $this->setDefaultFields();

        $store = StoresTable::getByPrimaryId($id);
        if ($store) {
            $this->fromArray(self::fromDB($store));
        }

        $this->addCityData($this->coreAddress->getCode());

        return $this;
    }

    /**
     * Sets default values
     * @return Store
     */
    protected function setDefaultFields()
    {
        $this->coreAddress
            ->setField('name', '')
            ->setField('isDefaultForLocation', false)
            ->setField('isAddressDataSent', true)
            ->setCode(\sdekHelper::getCity($this->options->fetchDeparture(), false))
            ->setField('intakeTimeFrom', '10:00')
            ->setField('intakeTimeTo', '18:00')
            ->setField('lunchTimeFrom', '')
            ->setField('lunchTimeTo', '');

        $this->coreSender
            ->setField('isSenderDataSent', true)
            ->setField('phoneAdditional', '')
            ->setField('needCall', false)
            ->setField('powerOfAttorney', false)
            ->setField('identityCard', false);

        $this->coreSeller
            ->setField('isSellerDataSent', true)
            ->setField('address', '');

        return $this;
    }

    /**
     * Returns structured array using $_REQUEST data
     * @return array
     */
    protected static function fromRequest()
    {
        return array(
            'address' => array(
                'name'                 => $_REQUEST['name'],
                'isDefaultForLocation' => (isset($_REQUEST['isDefaultForLocation']) && $_REQUEST['isDefaultForLocation']),
                'isAddressDataSent'    => (isset($_REQUEST['isAddressDataSent']) && $_REQUEST['isAddressDataSent']),
                'code'                 => $_REQUEST['cityCode'],
                'street'               => $_REQUEST['street'],
                'house'                => $_REQUEST['house'],
                'flat'                 => $_REQUEST['flat'],
                'comment'              => $_REQUEST['comment'],
                'intakeTimeFrom'       => $_REQUEST['intakeTimeFrom'],
                'intakeTimeTo'         => $_REQUEST['intakeTimeTo'],
                'lunchTimeFrom'        => $_REQUEST['lunchTimeFrom'],
                'lunchTimeTo'          => $_REQUEST['lunchTimeTo'],
            ),
            'sender' => array(
                'isSenderDataSent'     => (isset($_REQUEST['isSenderDataSent']) && $_REQUEST['isSenderDataSent']),
                'company'              => $_REQUEST['company'],
                'fullName'             => $_REQUEST['fullName'],
                'phone'                => $_REQUEST['phone'],
                'phoneAdditional'      => $_REQUEST['phoneAdditional'],
                'needCall'             => (isset($_REQUEST['needCall']) && $_REQUEST['needCall']),
                'powerOfAttorney'      => (isset($_REQUEST['powerOfAttorney']) && $_REQUEST['powerOfAttorney']),
                'identityCard'         => (isset($_REQUEST['identityCard']) && $_REQUEST['identityCard']),
            ),
            'seller' => array(
                'isSellerDataSent'     => (isset($_REQUEST['isSellerDataSent']) && $_REQUEST['isSellerDataSent']),
                'company'              => $_REQUEST['sellerCompany'],
                'phone'                => $_REQUEST['sellerPhone'],
                'address'              => $_REQUEST['sellerAddress'],
            ),
            'common' => array(
                'id'                   => (!empty($_REQUEST['storeId']) && (int)$_REQUEST['storeId']) ? (int)$_REQUEST['storeId'] : null,
                'isActive'             => (isset($_REQUEST['isActive']) && $_REQUEST['isActive']),
            ),
        );
    }

    /**
     * Returns structured array using given StoresTable data
     * @param array $data
     * @return array
     */
    protected static function fromDB($data)
    {
        return array(
            'address' => array(
                'name'                 => $data['NAME'],
                'isDefaultForLocation' => ($data['IS_DEFAULT_FOR_LOCATION'] === 'Y'),
                'isAddressDataSent'    => ($data['IS_ADDRESS_DATA_SENT'] === 'Y'),
                'code'                 => $data['FROM_LOCATION_CODE'],
                'street'               => $data['FROM_LOCATION_STREET'],
                'house'                => $data['FROM_LOCATION_HOUSE'],
                'flat'                 => $data['FROM_LOCATION_FLAT'],
                'comment'              => $data['COMMENT'],
                'intakeTimeFrom'       => $data['INTAKE_TIME_FROM'],
                'intakeTimeTo'         => $data['INTAKE_TIME_TO'],
                'lunchTimeFrom'        => $data['LUNCH_TIME_FROM'],
                'lunchTimeTo'          => $data['LUNCH_TIME_TO'],
            ),
            'sender' => array(
                'isSenderDataSent'     => ($data['IS_SENDER_DATA_SENT'] === 'Y'),
                'company'              => $data['SENDER_COMPANY'],
                'fullName'             => $data['SENDER_NAME'],
                'phone'                => $data['SENDER_PHONE_NUMBER'],
                'phoneAdditional'      => $data['SENDER_PHONE_ADDITIONAL'],
                'needCall'             => ($data['NEED_CALL'] === 'Y'),
                'powerOfAttorney'      => ($data['POWER_OF_ATTORNEY'] === 'Y'),
                'identityCard'         => ($data['IDENTITY_CARD'] === 'Y'),
            ),
            'seller' => array(
                'isSellerDataSent'     => ($data['IS_SELLER_DATA_SENT'] === 'Y'),
                'company'              => $data['SELLER_NAME'],
                'phone'                => $data['SELLER_PHONE'],
                'address'              => $data['SELLER_ADDRESS'],
            ),
            'common' => array(
                'id'                   => $data['ID'],
                'isActive'             => ($data['IS_ACTIVE'] === 'Y'),
            ),
        );
    }

    /**
     * Fills Store using structured data array
     * @param array $data @see Store::fromRequest()
     * @return $this
     */
    public function fromArray($data)
    {
        $this->id       = $data['common']['id'];
        $this->isActive = $data['common']['isActive'];

        $nativeFields = ['code', 'line', 'street', 'house', 'flat', 'comment'];
        foreach ($data['address'] as $key => $val) {
            if (in_array($key, $nativeFields)) {
                $action = 'set'.ucfirst($key);
                $this->coreAddress->$action($val);
            } else {
                $this->coreAddress->setField($key, $val);
            }
        }

        $nativeFields = ['company', 'fullName', 'phone'];
        foreach ($data['sender'] as $key => $val) {
            if (in_array($key, $nativeFields)) {
                $action = 'set'.ucfirst($key);
                $this->coreSender->$action($val);
            } else {
                $this->coreSender->setField($key, $val);
            }
        }

        $nativeFields = ['company', 'phone'];
        foreach ($data['seller'] as $key => $val) {
            if (in_array($key, $nativeFields)) {
                $action = 'set'.ucfirst($key);
                $this->coreSeller->$action($val);
            } else {
                $this->coreSeller->setField($key, $val);
            }
        }

        return $this;
    }

    /**
     * Adds city region and name to coreAddress
     * @param int $cdekId
     */
    public function addCityData($cdekId)
    {
        $city = \sqlSdekCity::getBySId($cdekId);
        if (is_array($city)) {
            $this->coreAddress->setRegion($city['REGION']);
            $this->coreAddress->setCity($city['NAME']);
        }
    }

    /**
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array
     */
    public function jsonSerialize()
    {
        return Tools::encodeToUTF8([
            'storeId'              => $this->id,
            'isActive'             => $this->isActive,
            'name'                 => $this->coreAddress->getField('name'),

            'isSenderDataSent'     => $this->coreSender->getField('isSenderDataSent'),
            'company'              => $this->coreSender->getCompany(),
            'fullName'             => $this->coreSender->getFullName(),
            'phone'                => $this->coreSender->getPhone(),
            'phoneAdditional'      => $this->coreSender->getField('phoneAdditional'),
            'needCall'             => $this->coreSender->getField('needCall'),
            'powerOfAttorney'      => $this->coreSender->getField('powerOfAttorney'),
            'identityCard'         => $this->coreSender->getField('identityCard'),

            'isDefaultForLocation' => $this->coreAddress->getField('isDefaultForLocation'),
            'isAddressDataSent'    => $this->coreAddress->getField('isAddressDataSent'),
            'cityCode'             => $this->coreAddress->getCode(),
            'city'                 => $this->coreAddress->getCity(),
            'region'               => $this->coreAddress->getRegion(),
            'street'               => $this->coreAddress->getStreet(),
            'house'                => $this->coreAddress->getHouse(),
            'flat'                 => $this->coreAddress->getFlat(),
            'address'              => implode(', ', [$this->coreAddress->getStreet(), $this->coreAddress->getHouse(), $this->coreAddress->getFlat()]),
            'comment'              => $this->coreAddress->getComment(),
            'intakeTimeFrom'       => $this->coreAddress->getField('intakeTimeFrom'),
            'intakeTimeTo'         => $this->coreAddress->getField('intakeTimeTo'),
            'lunchTimeFrom'        => $this->coreAddress->getField('lunchTimeFrom'),
            'lunchTimeTo'          => $this->coreAddress->getField('lunchTimeTo'),

            'isSellerDataSent'     => $this->coreSeller->getField('isSellerDataSent'),
            'sellerCompany'        => $this->coreSeller->getCompany(),
            'sellerPhone'          => $this->coreSeller->getPhone(),
            'sellerAddress'        => $this->coreSeller->getField('address'),
        ]);
    }

    /**
     * @return int|null
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int|null $id
     * @return Store
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     * @return Store
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
        return $this;
    }

    /**
     * @return Address
     */
    public function getCoreAddress()
    {
        return $this->coreAddress;
    }

    /**
     * @return Sender
     */
    public function getCoreSender()
    {
        return $this->coreSender;
    }

    /**
     * @return Sender
     */
    public function getCoreSeller()
    {
        return $this->coreSeller;
    }
}