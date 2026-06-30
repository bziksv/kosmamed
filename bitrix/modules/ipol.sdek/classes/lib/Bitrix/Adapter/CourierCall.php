<?php
namespace Ipolh\SDEK\Bitrix\Adapter;

use Ipolh\SDEK\Api\Entity\Request\IntakesMake;
use Ipolh\SDEK\Api\Entity\UniversalPart\CdekLocation;
use Ipolh\SDEK\Api\Entity\UniversalPart\Phone;
use Ipolh\SDEK\Api\Entity\UniversalPart\PhoneList;
use Ipolh\SDEK\Api\Entity\UniversalPart\Sender;

use Ipolh\SDEK\Bitrix\Entity\Options;
use Ipolh\SDEK\Bitrix\Tools;
use Ipolh\SDEK\Core\Order\Goods;
use Ipolh\SDEK\CourierCallsTable;

/**
 * Class CourierCall
 * @package Ipolh\SDEK\Bitrix\Adapter
 */
class CourierCall implements \JsonSerializable
{
    // Call types
    const TYPE_ORDER = 1;
    const TYPE_CONSOLIDATION = 2;

    /**
     * @var int|null
     */
    protected $id;

    /**
     * @var int|null
     */
    protected $account;

    /**
     * @var string|null
     */
    protected $status;

    /**
     * @var string[]|null
     */
    protected $message;

    /**
     * @var string|null
     */
    protected $intake_uuid;

    /**
     * @var int|null
     */
    protected $intake_number;

    /**
     * @var string|null
     */
    protected $status_code;

    /**
     * @var string|null
     */
    protected $status_date;

    /**
     * @var string|null
     */
    protected $state_code;

    /**
     * @var string|null
     */
    protected $state_date;

    /**
     * @var int Call type: consolidation, single order, @see TYPE_* constants
     */
    protected $type;

    /**
     * @var int|null CDEK order ID
     */
    protected $order_id;

    /**
     * @var string|null CDEK order UUID
     */
    protected $order_uuid;

    /**
     * @var string|null
     */
    protected $intake_date;

    /**
     * @var Store
     */
    protected $store;

    /**
     * @var Goods
     */
    protected $pack;

    /**
     * @var Options
     */
    protected $options;

    public function __construct(Options $options)
    {
        $this->id = null;
        $this->intake_uuid = null;
        $this->intake_number = null;
        $this->status_code = null;
        $this->status_date = null;
        $this->state_code = null;
        $this->state_date = null;

        $this->type = self::TYPE_ORDER;
        $this->order_id = null;
        $this->order_uuid = null;

        $this->store = new Store($options);
        $this->pack = new Goods();

        $this->options = $options;
    }

    /**
     * Returns CourierCall filled by default values
     * @param int $type
     * @return CourierCall
     */
    public function newCourierCall($type = self::TYPE_ORDER)
    {
        $this->setDefaultFields();
        $this->store->newStore();
        $this->status = 'NEW';

        switch ((int)$type) {
            case self::TYPE_ORDER:
            default:
                $this->type = self::TYPE_ORDER;
                break;
            case self::TYPE_CONSOLIDATION:
                $this->type = self::TYPE_CONSOLIDATION;
                break;
        }

        return $this;
    }

    /**
     * Returns CourierCall filled from $_REQUEST data (CourierCallsForm AJAX call)
     * @return CourierCall
     */
    public function requestCourierCall()
    {
        // Deal with cp1251
        if (Tools::isModuleAjaxRequest()) {
            $_REQUEST = Tools::encodeFromUTF8($_REQUEST);
        }

        $this->fromArray(self::fromRequest());

        return $this;
    }

    /**
     * Returns CourierCall filled from CourierCallsTable data
     * @param int $id
     * @return CourierCall
     */
    public function uploadedCourierCall($id)
    {
        $this->id = $id;
        $this->setDefaultFields();

        $call = CourierCallsTable::getByPrimaryId($id);
        if ($call) {
            $this->fromArray(self::fromDB($call));
        }

        $this->getStore()->addCityData($this->getStore()->getCoreAddress()->getCode());

        return $this;
    }

    /**
     * Sets default values
     * @return CourierCall
     */
    protected function setDefaultFields()
    {
        $basicAccount = \sdekHelper::getBasicAuth(true);
        $this->account = $basicAccount ? $basicAccount : null;

        $this->intake_date = time();

        $this->pack
            ->setWeight(null)
            ->setLength(null)
            ->setWidth(null)
            ->setHeight(null)
            ->setDetails('');

        return $this;
    }

    /**
     * Returns structured array using $_REQUEST data
     * @return array
     */
    protected static function fromRequest()
    {
        return array(
            'store' => array(
                'address' => array(
                    'name'            => '',
                    'code'            => $_REQUEST['cityCode'],
                    'line'            => $_REQUEST['address'],
                    'comment'         => $_REQUEST['comment'],
                    'intakeTimeFrom'  => $_REQUEST['intakeTimeFrom'],
                    'intakeTimeTo'    => $_REQUEST['intakeTimeTo'],
                    'lunchTimeFrom'   => $_REQUEST['lunchTimeFrom'],
                    'lunchTimeTo'     => $_REQUEST['lunchTimeTo'],
                ),
                'sender' => array(
                    'company'         => $_REQUEST['company'],
                    'fullName'        => $_REQUEST['fullName'],
                    'phone'           => $_REQUEST['phone'],
                    'phoneAdditional' => $_REQUEST['phoneAdditional'],
                    'needCall'        => (isset($_REQUEST['needCall']) && $_REQUEST['needCall']),
                    'powerOfAttorney' => (isset($_REQUEST['powerOfAttorney']) && $_REQUEST['powerOfAttorney']),
                    'identityCard'    => (isset($_REQUEST['identityCard']) && $_REQUEST['identityCard']),
                ),
                'common' => array(
                    'id'              => (!empty($_REQUEST['storeId']) && (int)$_REQUEST['storeId'] > 0) ? (int)$_REQUEST['storeId'] : null, // ID of previously saved stores > 0
                    'isActive'        => true,
                ),
            ),
            'pack' => array(
                'packName'        => $_REQUEST['packName'],
                'packWeight'      => (!empty($_REQUEST['packWeight']) && (int)$_REQUEST['packWeight']) ? (int)$_REQUEST['packWeight'] : null,
                'packLength'      => (!empty($_REQUEST['packLength']) && (int)$_REQUEST['packLength']) ? (int)$_REQUEST['packLength'] : null,
                'packWidth'       => (!empty($_REQUEST['packWidth']) && (int)$_REQUEST['packWidth']) ? (int)$_REQUEST['packWidth'] : null,
                'packHeight'      => (!empty($_REQUEST['packHeight']) && (int)$_REQUEST['packHeight']) ? (int)$_REQUEST['packHeight'] : null,
            ),
            'common' => array(
                'callId'          => (!empty($_REQUEST['callId']) && (int)$_REQUEST['callId']) ? (int)$_REQUEST['callId'] : null,
                'type'            => $_REQUEST['callType'],
                'orderId'         => isset($_REQUEST['orderId']) ? $_REQUEST['orderId'] : null,
                'orderUuid'       => isset($_REQUEST['orderUuid']) ? $_REQUEST['orderUuid'] : null,
                'account'         => $_REQUEST['account'],
                'intakeDate'      => $_REQUEST['intakeDate'],
            ),
        );
    }

    /**
     * Returns structured array using given CourierCallsTable data
     * @param array $data
     * @return array
     */
    protected static function fromDB($data)
    {
        return array(
            'store' => array(
                'address' => array(
                    'name'            => '',
                    'code'            => $data['FROM_LOCATION_CODE'],
                    'line'            => $data['FROM_LOCATION_ADDRESS'],
                    'comment'         => $data['COMMENT'],
                    'intakeTimeFrom'  => $data['INTAKE_TIME_FROM'],
                    'intakeTimeTo'    => $data['INTAKE_TIME_TO'],
                    'lunchTimeFrom'   => $data['LUNCH_TIME_FROM'],
                    'lunchTimeTo'     => $data['LUNCH_TIME_TO'],
                ),
                'sender' => array(
                    'company'         => $data['SENDER_COMPANY'],
                    'fullName'        => $data['SENDER_NAME'],
                    'phone'           => $data['SENDER_PHONE_NUMBER'],
                    'phoneAdditional' => $data['SENDER_PHONE_ADDITIONAL'],
                    'needCall'        => ($data['NEED_CALL'] === 'Y'),
                    'powerOfAttorney' => ($data['POWER_OF_ATTORNEY'] === 'Y'),
                    'identityCard'    => ($data['IDENTITY_CARD'] === 'Y'),
                ),
                'common' => array(
                    'id'              => isset($data['STORE_ID']) ? $data['STORE_ID'] : null,
                    'isActive'        => true,
                ),
            ),
            'pack' => array(
                'packName'        => $data['PACK_NAME'],
                'packWeight'      => $data['PACK_WEIGHT'],
                'packLength'      => $data['PACK_LENGTH'],
                'packWidth'       => $data['PACK_WIDTH'],
                'packHeight'      => $data['PACK_HEIGHT'],
            ),
            'common' => array(
                'callId'          => $data['ID'],
                'status'          => $data['STATUS'],
                'message'         => !empty($data['MESSAGE']) ? unserialize($data['MESSAGE'], ['allowed_classes' => false]) : [],

                'intakeUuid'      => $data['INTAKE_UUID'],
                'intakeNumber'    => $data['INTAKE_NUMBER'],
                'statusCode'      => $data['STATUS_CODE'],
                'statusDate'      => !empty($data['STATUS_DATE']) ? $data['STATUS_DATE']->getTimestamp() : null,
                'stateCode'       => $data['STATE_CODE'],
                'stateDate'       => !empty($data['STATE_DATE']) ? $data['STATE_DATE']->getTimestamp() : null,

                'type'            => $data['TYPE'],
                'orderId'         => $data['CDEK_ORDER_ID'],
                'orderUuid'       => $data['CDEK_ORDER_UUID'],

                'account'         => $data['ACCOUNT'],

                'intakeDate'      => $data['INTAKE_DATE']->getTimestamp(),
            ),
        );
    }

    /**
     * Fills CourierCall using structured data array
     * @param array $data @see CourierCall::fromRequest()
     * @return $this
     */
    protected function fromArray($data)
    {
        $this->id            = $data['common']['callId'];
        $this->account       = $data['common']['account'];
        $this->status        = isset($data['common']['status']) ? $data['common']['status'] : null;
        $this->message       = isset($data['common']['message']) ? $data['common']['message'] : null;

        $this->intake_uuid   = isset($data['common']['intakeUuid']) ? $data['common']['intakeUuid'] : null;
        $this->intake_number = isset($data['common']['intakeNumber']) ? $data['common']['intakeNumber'] : null;
        $this->status_code   = isset($data['common']['statusCode']) ? $data['common']['statusCode'] : null;
        $this->status_date   = isset($data['common']['statusDate']) ? $data['common']['statusDate'] : null;
        $this->state_code    = isset($data['common']['stateCode']) ? $data['common']['stateCode'] : null;
        $this->state_date    = isset($data['common']['stateDate']) ? $data['common']['stateDate'] : null;

        switch ((int)$data['common']['type']) {
            case self::TYPE_ORDER:
            default:
                $this->type = self::TYPE_ORDER;
                break;
            case self::TYPE_CONSOLIDATION:
                $this->type = self::TYPE_CONSOLIDATION;
                break;
        }
        $this->order_id   = $data['common']['orderId'];
        $this->order_uuid = $data['common']['orderUuid'];

        $this->intake_date = $data['common']['intakeDate'];

        $this->store->fromArray($data['store']);

        $this->pack
            ->setWeight($data['pack']['packWeight'])
            ->setLength($data['pack']['packLength'])
            ->setWidth($data['pack']['packWidth'])
            ->setHeight($data['pack']['packHeight'])
            ->setDetails($data['pack']['packName']);

        return $this;
    }

    /**
     * Returns CourierCall as API request object
     * @return IntakesMake
     */
    public function toRequestObject()
    {
        $intakesMake = new IntakesMake();

        $store = $this->getStore();

        switch ($this->type) {
            case self::TYPE_ORDER:
            default:
                $intakesMake
                    ->setCdekNumber($this->getOrderId())
                    ->setOrderUuid($this->getOrderUuid());

                $cdekSender = null;
                $cdekLocation = null;
                break;
            case self::TYPE_CONSOLIDATION:
                $intakesMake
                    ->setName($this->getPack()->getDetails())
                    ->setWeight($this->getPack()->getWeight())
                    ->setLength($this->getPack()->getLength())
                    ->setWidth($this->getPack()->getWidth())
                    ->setHeight($this->getPack()->getHeight());

                $cdekSender = new Sender();
                $cdekSender
                    ->setCompany($store->getCoreSender()->getCompany())
                    ->setName($store->getCoreSender()->getFullName());

                if ($store->getCoreSender()->getPhone()) {
                    $senderPhone = new Phone($store->getCoreSender()->getPhone());
                    $senderPhone->setAdditional($store->getCoreSender()->getField('phoneAdditional'));

                    $senderPhoneList = new PhoneList();
                    $senderPhoneList->add($senderPhone);

                    $cdekSender->setPhones($senderPhoneList);
                }

                $cdekLocation = new CdekLocation();
                $cdekLocation
                    ->setCode($store->getCoreAddress()->getCode())
                    ->setAddress($store->getCoreAddress()->getLine());

                break;
        }

        $intakesMake
            ->setIntakeDate(date('Y-m-d', $this->getIntakeDate()))
            ->setIntakeTimeFrom($store->getCoreAddress()->getField('intakeTimeFrom'))
            ->setIntakeTimeTo($store->getCoreAddress()->getField('intakeTimeTo'))
            ->setLunchTimeFrom($store->getCoreAddress()->getField('lunchTimeFrom'))
            ->setLunchTimeTo($store->getCoreAddress()->getField('lunchTimeTo'))

            ->setComment($store->getCoreAddress()->getComment())

            ->setNeedCall($store->getCoreSender()->getField('needCall'))
            ->setCourierPowerOfAttorney($store->getCoreSender()->getField('powerOfAttorney'))
            ->setCourierIdentityCard($store->getCoreSender()->getField('identityCard'))

            ->setSender($cdekSender)
            ->setFromLocation($cdekLocation);

        return $intakesMake;
    }

    /**
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array
     */
    public function jsonSerialize()
    {
        $store = $this->getStore();

        $intakeDate = \Bitrix\Main\Type\DateTime::createFromTimestamp($this->intake_date);
        $statusDate = !empty($this->status_date) ? \Bitrix\Main\Type\DateTime::createFromTimestamp($this->status_date) : null;
        $stateDate  = !empty($this->state_date)  ? \Bitrix\Main\Type\DateTime::createFromTimestamp($this->state_date) : null;

        if (!empty($this->status)) {
            $statusSign = Tools::getMessage('STATUS_COURIER_CALL_'.$this->status);
        }

        return Tools::encodeToUTF8([
            'callId'          => $this->id,
            'account'         => $this->account,
            'status'          => $this->status,
            'statusSign'      => $statusSign ?: '',
            'message'         => $this->message,

            'intakeUuid'      => $this->intake_uuid,
            'intakeNumber'    => $this->intake_number,
            'statusCode'      => $this->status_code,
            'statusDate'      => $this->status_date,
            'statusDateSign'  => $statusDate ? $statusDate->toString() : null,
            'stateCode'       => $this->state_code,
            'stateDate'       => $this->state_date,
            'stateDateSign'   => $stateDate ? $stateDate->toString() : null,

            'type'            => $this->type,
            'orderId'         => $this->order_id,
            'orderUuid'       => $this->order_uuid,

            'intakeDate'      => $this->intake_date,
            'intakeDateSign'  => trim(substr($intakeDate, 0, strpos($intakeDate, ' '))),

            'packName'        => $this->pack->getDetails(),
            'packWeight'      => $this->pack->getWeight(),
            'packLength'      => $this->pack->getLength(),
            'packWidth'       => $this->pack->getWidth(),
            'packHeight'      => $this->pack->getHeight(),

            'storeId'         => $store->getId(),
            'name'            => $store->getCoreAddress()->getField('name'),

            'company'         => $store->getCoreSender()->getCompany(),
            'fullName'        => $store->getCoreSender()->getFullName(),
            'phone'           => $store->getCoreSender()->getPhone(),
            'phoneAdditional' => $store->getCoreSender()->getField('phoneAdditional'),
            'needCall'        => $store->getCoreSender()->getField('needCall'),
            'powerOfAttorney' => $store->getCoreSender()->getField('powerOfAttorney'),
            'identityCard'    => $store->getCoreSender()->getField('identityCard'),

            'cityCode'        => $store->getCoreAddress()->getCode(),
            'city'            => $store->getCoreAddress()->getCity(),
            'region'          => $store->getCoreAddress()->getRegion(),
            'address'         => $store->getCoreAddress()->getLine(),
            'comment'         => $store->getCoreAddress()->getComment(),
            'intakeTimeFrom'  => $store->getCoreAddress()->getField('intakeTimeFrom'),
            'intakeTimeTo'    => $store->getCoreAddress()->getField('intakeTimeTo'),
            'lunchTimeFrom'   => $store->getCoreAddress()->getField('lunchTimeFrom'),
            'lunchTimeTo'     => $store->getCoreAddress()->getField('lunchTimeTo'),
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
     * @return int|null
     */
    public function getAccount()
    {
        return $this->account;
    }

    /**
     * @return string|null
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return string[]|null
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @return string|null
     */
    public function getIntakeUuid()
    {
        return $this->intake_uuid;
    }

    /**
     * @return int|null
     */
    public function getIntakeNumber()
    {
        return $this->intake_number;
    }

    /**
     * @return string|null
     */
    public function getStatusCode()
    {
        return $this->status_code;
    }

    /**
     * @return string|null
     */
    public function getStatusDate()
    {
        return $this->status_date;
    }

    /**
     * @return string|null
     */
    public function getStateCode()
    {
        return $this->state_code;
    }

    /**
     * @return string|null
     */
    public function getStateDate()
    {
        return $this->state_date;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return int|null
     */
    public function getOrderId()
    {
        return $this->order_id;
    }

    /**
     * @return string|null
     */
    public function getOrderUuid()
    {
        return $this->order_uuid;
    }

    /**
     * @return string|null
     */
    public function getIntakeDate()
    {
        return $this->intake_date;
    }

    /**
     * @return Store
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * @return Goods
     */
    public function getPack()
    {
        return $this->pack;
    }
}