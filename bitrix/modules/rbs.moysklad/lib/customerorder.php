<?php
namespace Rbs\Moysklad;

use \Bitrix\Sale\Order;
use \Bitrix\Sale\Payment;
use \Rbs\Moysklad\LangMsg;
use \Rbs\Moysklad\Internals\State;
use \Rbs\Moysklad\Services\OrderFilter;
use \Rbs\Moysklad\Debug\Message as LogMsg;
use \Rbs\Moysklad\Entity\Payments\ExportPayment;
use \Rbs\Moysklad\Entity\Payments\ImportPayment;

\Bitrix\Main\Loader::includeModule('sale');
\Bitrix\Main\Loader::includeModule('catalog');
\Bitrix\Main\Loader::includeModule('iblock');

class Customerorder
{
    /**
     * orderBx - Bitrix Order Entity Obj
     * order - MoySklad Order Entity Obj
     */
    public $orderBx = null;
    public $order = null;

    /**
     * true - all objects loaded
     */
    private $isLoaded = false;
    private $isFinalError = false;
    /**
     * string attrs from $order
     */
    private $strAttrs = [];
    /**
     * customentity attrs from $order
     */
    private $customEntityAttrs = [];
    private $customEntityAttrsDefault = [];
    private $boolAttrs = [];
    private $fileAttrs = [];
    /**
     * stack for change fields in $order
     */
    private $orderChangesStack = [];

    private $log = [];
    private $hasErrors = false;
    private $hasWarnings = false;
    private $canCreateOrderInMs = false;

    /**track */
    private $tracks = [];
    private $trackMain = '';

    private $isChangedSum = false;
    private $expandFields = 'positions.assortment,agent,state,files,store,salesChannel,demands.positions,rate.currency';


    private static $limitPositions = 1000;
    /**
     * Main constructor
     *
     * @param integer $orderId
     */
    public function __construct($orderId = 0)
    {
        if ($orderId !== null && (int)$orderId > 0 && (string)(int)$orderId === (string)$orderId) {
            if ($order = Order::load($orderId)) {

                $this->setActualOrderEntity($order);

                if(mb_strpos($order->getField('XML_ID'), 'MS_') !== false) {
                    $msOrderId = str_replace('MS_', '', $order->getField('XML_ID'));
                    if(mb_strlen($msOrderId) === 36) {
                        $orderGet = ApiNew::get('/entity/customerorder', ['limit' => 1, 'expand' => $this->getExpandFields(), 'filter' => 'id=' . $msOrderId]);
                    } else {
                        //fake success response
                        $orderGet = (object)[
                            'meta' => (object)[
                                'size' => 0
                            ],
                            'rows' => [],
                            'hasErrors' => false,
                        ];
                    }
                }

                if(Utils::is_success($orderGet) && (int)$orderGet->meta->size === 0) {
                    if (Config::checkFeature('customextfield')) {
                        $orderGet = ApiNew::get('/entity/customerorder', ['limit' => 1, 'expand' => $this->getExpandFields(), 'filter' => 'externalCode=' . $order->getField(Config::getExtFieldId())]);
                    } else {
                        $orderGet = ApiNew::get('/entity/customerorder', ['limit' => 1, 'expand' => $this->getExpandFields(), 'filter' => 'externalCode=' . $order->getId()]);
                    }
                }

                if (Utils::is_success($orderGet)) {

                    if ((int)$orderGet->meta->size >= 1) {

                        if ((int)$orderGet->meta->size > 1) {
                            $this->addWarningMessage(LangMsg::get('CONSTRUCT_ORDER_FIND_NOT_ONE', ['#SIZE#' => $orderGet->meta->size]));
                        }

                        $orderMs = $orderGet->rows[0];

                        if(is_null($orderMs) || empty($orderMs->meta->href)) {
                            $this->addWarningMessage(LangMsg::get('CONSTRUCT_ORDER_FIND_ZERO'));
                            return;
                        }
                        
                        $this->setOrder($orderMs);
                        $this->setLoadedOrder(true);

                    } else {
                        $this->addWarningMessage(LangMsg::get('CONSTRUCT_ORDER_FIND_ZERO'));
                    }

                    $this->canCreateOrderInMs = true;

                } else if(Utils::has_errors($orderGet)) {
                    $this->addErrorMessageArray($orderGet->errors);
                }

            } else {
                $this->addErrorMessage(LangMsg::get('ENTITY_LOAD_BX_ERROR', ['#ID#' => $orderId]));
            }

        } else {
            $this->addWarningMessage(LangMsg::get('ENTITY_LOAD_BX_INPUT_DATA_WRONG', ['#ID#' => $orderId]));
        }
    }

    public function setOrder($orderMs = null)
    {
        $this->order = $orderMs;
        $this->order->meta->href = explode('?', $this->order->meta->href)[0];
        $this->setAttributes();
        $this->setChangedSum();
    }

    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Static constructor
     *
     * @param string $href
     * @return Customerorder
     */
    public static function createFromHref(string $href)
    {
        $obj = new Customerorder;

        $obj->clearLog();
        $obj->setLoadedOrder(false);

        if (empty($href)) {
            $obj->addWarningMessage(LangMsg::get('EMPTY_HREF'));
            return $obj;
        }
        
        $order = null;

        $orderMs = ApiNew::get($href . '?expand=' . $obj->getExpandFields());
        if (Utils::has_errors($orderMs)) {
            $obj->addErrorMessageArray($orderMs->errors);
            return $obj;
        }

        $obj->setOrder($orderMs);

        if (is_object($orderMs) && !empty($orderMs->id)) {
            if (Config::checkFeature('importorder')) {
                $r = \CSaleOrder::GetList([], ['=XML_ID' => 'MS_' . $orderMs->id]);
                if ($ob = $r->GetNext()) {
                    $order = Order::load($ob['ID']);
                }
            }

            if ($order === null) {
                $orderAccountId = $orderMs->externalCode;
                if (Config::checkFeature('customextfield') && Config::getExtFieldId() != 'ID') {
                    $r = \CSaleOrder::GetList([], ['=' . Config::getExtFieldId() => $orderMs->externalCode]);
                    if ($ob = $r->GetNext()) {
                        $order = Order::load($ob['ID']);
                    }
                } elseif ($orderAccountId > 0 && (string)(int)$orderAccountId === (string)$orderAccountId) {
                    $order = Order::load($orderAccountId);
                }
            }

            if ($order !== null && $order !== false) {
                $obj->setActualOrderEntity($order);
                $obj->setLoadedOrder(true);
                $obj->setAttributes();
                $obj->setChangedSum();
            } else {
                $obj->addWarningMessage(LangMsg::get('EMPTY_ORDER_BX'));
            }
        } else {
            $obj->addErrorMessageArray($orderMs->errors);
        }

        return $obj;
    }

    public static function createFromFullMsObject($orderMs)
    {
        $obj = new Customerorder;

        $obj->clearLog();
        $obj->setLoadedOrder(false);

        if (empty($orderMs)) {
            $obj->addWarningMessage(LangMsg::get('EMPTY_HREF'));
            return $obj;
        }

        $obj->setOrder($orderMs);

        if (is_object($orderMs) && !empty($orderMs->id)) {

            if (Config::checkFeature('importorder')) {
                $r = \CSaleOrder::GetList([], ['=XML_ID' => 'MS_' . $orderMs->id]);
                if ($ob = $r->GetNext()) {
                    $order = Order::load($ob['ID']);
                }
            }

            if ($order === null) {
                $orderAccountId = $orderMs->externalCode;
                if (Config::checkFeature('customextfield') && Config::getExtFieldId() != 'ID') {
                    $r = \CSaleOrder::GetList([], ['=' . Config::getExtFieldId() => $orderMs->externalCode]);
                    if ($ob = $r->GetNext()) {
                        $order = Order::load($ob['ID']);
                    }
                } elseif ($orderAccountId > 0 && (string)(int)$orderAccountId === (string)$orderAccountId) {
                    $order = Order::load($orderAccountId);
                }
            }

            if ($order !== null && $order !== false) {

                $obj->setActualOrderEntity($order);
                $obj->setLoadedOrder(true);
                $obj->setAttributes();
                $obj->setChangedSum();

            } else {

                $obj->addWarningMessage(LangMsg::get('EMPTY_ORDER_BX'));

            }
        } else {

            $obj->addErrorMessageArray($orderMs->errors);
            
        }

        return $obj;
    }

    public static function createBxOrder($orderHref = '', $action = 'CREATE', $orderSource = null)
    {
        $obj = new Customerorder;

        $obj->clearLog();
        $obj->setLoadedOrder(false);

        if (empty($orderHref)) {
            $obj->addErrorMessage(LangMsg::get('EMPTY_HREF'));
            $obj->setFinalError();
            return $obj;
        }

        $order = null;

        $orderMs = $orderSource === null ? ApiNew::get($orderHref . '?expand=' . $obj->getExpandFields()) : $orderSource;
        
        if (Utils::has_errors($orderMs)) {
            $obj->addErrorMessageArray($orderMs->errors);
            $obj->setFinalError();
            return $obj;
        }

        $obj->setOrder($orderMs);

        if ($obj->isDisableOrderPropMs()) {
            $obj->addWarningMessage(LangMsg::get('WEBHOOK_CO_DISABLED'));
            $obj->setFinalError();
            return $obj;
        }

        if(Config::getImportType() === 'UPDATE' && $action === 'UPDATE'){
            if (!$obj->checkOrderForImportTypeUpdate()) {
                $obj->addWarningMessage(LangMsg::get('WEBHOOK_IMPORT_TYPE_UPDATE_FLAG_DISABLED'));
                $obj->setFinalError();
                return $obj;
            }
        }

        $event = new \Bitrix\Main\Event(Config::getModuleId(true), "OnBeforeImportBxOrder", array(
            'msOrder' => $obj->getOrder()
        ));
        $event->send();

        if ($event->getResults()) {
            foreach ($event->getResults() as $eventResult) {
                if ($eventResult->getType() == \Bitrix\Main\EventResult::ERROR) {
                    $obj->addWarningMessage(LangMsg::get('EVENT_BEFORE_IMPORT_STOP'));
                    $obj->setFinalError();
                    return $obj;
                }
            }
        }

        if (!empty($orderMs->id)) {
            $findedOrderId = 0;
            if (Config::checkFeature('customextfield') && Config::getExtFieldId() != 'ID') {
                $r = \CSaleOrder::GetList([], ['=' . Config::getExtFieldId() => $orderMs->externalCode]);
                if ($ob = $r->GetNext()) {
                    $obj->addSuccessMessage(LangMsg::get('ORDER_ALREADY_CREATED', ['#ID#' => $ob['ID']]));
                    $findedOrderId = $ob['ID'];
                }
            } elseif ((string)(int)$orderMs->externalCode === (string)$orderMs->externalCode && (int)$orderMs->externalCode > 0) {
                $r = \CSaleOrder::GetList([], ['=ID' => (int)$orderMs->externalCode]);
                if ($ob = $r->GetNext()) {
                    $obj->addSuccessMessage(LangMsg::get('ORDER_ALREADY_CREATED', ['#ID#' => $ob['ID']]));
                    $findedOrderId = $ob['ID'];
                }
            }
                        
            $r = \CSaleOrder::GetList([], ['=XML_ID' => 'MS_' . $orderMs->id]);
            if ($ob = $r->GetNext()) {
                $obj->addSuccessMessage(LangMsg::get('ORDER_ALREADY_CREATED', ['#ID#' => $ob['ID']]));
                $findedOrderId = $ob['ID'];
            }

            if ((int)$findedOrderId > 0) {
                if ($order = \Bitrix\Sale\Order::load((int)$findedOrderId)) {
                    $obj->setActualOrderEntity($order);
                    $obj->setLoadedOrder(true);
                    return $obj;
                }
            }

            $user = Counterparty::getUserBxFromAgentHref($orderMs->agent);
            if ((int)$user['USER_ID'] > 0) {

                if (!empty($user['LAST_ERROR'])) {
                    $obj->addWarningMessage(LangMsg::get('ERROR_WHILE_IMPORT_USER_SET_DEFAULT', ['#ERROR#' => $user['LAST_ERROR']]));
                }

                $order = \Bitrix\Sale\Order::create(Config::getDefaultImportSiteId(), (int)$user['USER_ID']);
                $obj->setActualOrderEntity($order);
                $obj->orderBx->setPersonTypeId((int)$user['PERSON_TYPE']);
                $obj->orderBx->setField('XML_ID', 'MS_' . $obj->order->id);

                if(!empty($obj->order->moment)) {
                    $obj->orderBx->setField('DATE_INSERT', new \Bitrix\Main\Type\DateTime($obj->order->moment, 'Y-m-d H:i:s'));
                }
                $obj->setBxBasket();

                if(!Config::checkFeature('import_dont_shipment')) {
                    $obj->setBxShipmentCollection();
                }
                if (!Config::checkFeature('import_dont_payment')) {
                    $obj->setBxPaymentCollection();
                }
                
                $obj->setBxPropertyCollection($user);

                \CRbsMoyskladHelper::setBxProps($obj);
                
                if (\Bitrix\Main\Loader::includeModule('crm') && Config::checkFeature('importcontact')) {
                    if ((int)$user['CONTACT_ID'] > 0) {
                        $communications = $obj->orderBx->getContactCompanyCollection();
                        $itemCom = $communications->createContact();
                        $itemCom ->setField('ENTITY_ID', (int)$user['CONTACT_ID']);
                    }
                }

                Utils::send_bx_event(Config::getModuleId(true), 'OnBeforeCreateBxOrder', [
                    'bxOrder' => $obj->getOrderEntity(),
                    'msOrder' => $obj->getOrder()
                ]);

                $result = $obj->saveResult();
                
                if ($result->isSuccess()) {

                    $obj->setLoadedOrder(true);

                    $isImportOnceProcess = State::getInstance()->getState('import_once_process') === 'customerorder';

                    if($isImportOnceProcess) {
                        
                        $obj->checkUpdateHook();

                    } else {

                        $arPushParamsMs = [];

                        if (Config::checkFeature('pushmsorderid')) {
                            $bxPushOrderField = Config::getPushOrderBxField();
                            $msPushOrderField = Config::getPushOrderMsField();
                            if (!empty($bxPushOrderField) && !empty($msPushOrderField)) {
                                $arPushParamsMs['attributes'] = \CRbsMoyskladHelper::convertAttributesToNewApi([
                                    [
                                        'id' => $msPushOrderField,
                                        'value' => (string)$obj->orderBx->getField($bxPushOrderField)
                                    ]
                                ]);
                            }
                        }

                        if (Config::checkFeature('pushmsordername')) {
                            $nameField = Config::getPushOrderNameField();
                            $arPushParamsMs['name'] = (string)$obj->orderBx->getField($nameField);
                        }

                        if (Config::checkFeature('ms_push_order_comment_id')) {
                            $orderId = $order->getField(Config::getOption('ms_push_order_comment_id_field', 'ID'));
                            if (!empty($orderId)) {
                                $template = Config::getOption('ms_push_order_comment_id_template', '');
                                if (empty($template)) {
                                    $template = LangMsg::get('ORDER_PUSH_MS_ORDER_COMMENT_ORDER_ID_TEMPLATE');
                                }
                                $arPushParamsMs['description'] = '';
                                if (!empty($obj->order->description)) {
                                    $arPushParamsMs['description'] = $obj->order->description . PHP_EOL;
                                }
                                $arPushParamsMs['description'] .= str_replace('#ORDER_ID#', $orderId, $template);
                            }
                        }

                        if (Utils::is_count($arPushParamsMs)) {
                            ApiNew::put('/entity/customerorder/' . $obj->order->id, $arPushParamsMs);
                        }

                    }
                    
                } else {
                    $obj->addErrorMessageArray($result->getErrorMessages());
                }
            } else {

                if(!empty($user['LAST_ERROR'])) {
                    $obj->addErrorMessage(LangMsg::get('EMPTY_ORDER_UID_ERROR', ['#ERROR#' => $user['LAST_ERROR']]));
                } else {
                    $obj->addErrorMessage(LangMsg::get('EMPTY_ORDER_UID'));
                }

                $obj->setFinalError();
            }
        } else {
            $obj->addErrorMessageArray($orderMs->errors);
            $obj->setFinalError();
        }

        return $obj;
    }

    private function setBxPropertyCollection(array $user = [])
    {
        if ($propertyCollection = $this->orderBx->getPropertyCollection()) {
            if ((int)$user['PROFILE_ID'] > 0) {
                if (!$user['IS_NEW_PROFILE']) {
                    $rsProfileProps = \CSaleOrderUserPropsValue::GetList([], ['USER_PROPS_ID' => (int)$user['PROFILE_ID']]);
                    while ($obProp = $rsProfileProps->GetNext()) {
                        if ($property = $propertyCollection->getItemByOrderPropertyId($obProp['PROP_ID'])) {
                            if ('LOCATION' === $obProp['PROP_TYPE']) {
                                $loc = \CSaleLocation::GetById($obProp['VALUE']);
                                if (!empty($loc['CODE'])) {
                                    $obProp['VALUE'] = $loc['CODE'];
                                } else {
                                    continue;
                                }
                            }
            
                            $property->setValue($obProp['VARIANT_ID'] ?? $obProp['VALUE']);
                        }
                    }
                } else {
                    $propsCounter = Config::getPropsIds('counterpartyfields');
                    if (Utils::is_count($propsCounter)) {

                        $orderPropsFromUser = \Bitrix\Sale\Internals\OrderPropsTable::getList([
                            'filter' => [
                                'PERSON_TYPE_ID' => (int)$user['PERSON_TYPE'],
                                'USER_PROPS' => 'Y'
                            ]
                        ])->fetchAll();

                        foreach ($orderPropsFromUser as $userProp) {
                            if (isset($propsCounter[$userProp['ID']])) {
                                if (!empty($user['AGENT']->{$propsCounter[$userProp['ID']]})) {
                                    $propVal = $user['AGENT']->{$propsCounter[$userProp['ID']]};
                                    \CSaleOrderUserPropsValue::Add([
                                        'USER_PROPS_ID' => $user['PROFILE_ID'],
                                        'ORDER_PROPS_ID' => $userProp['ID'],
                                        'NAME' => $userProp['NAME'],
                                        'VALUE' => $propVal
                                    ]);
                                    if ($property = $propertyCollection->getItemByOrderPropertyId($userProp['ID'])) {
                                        if ($userProp['TYPE'] === 'STRING') {
                                            $property->setValue($propVal);
                                        }
                                    }
                                }
                            }
                        }

                    }
                }
            } else {
                $userFields = \Bitrix\Main\UserTable::getList(['filter' => ['ID' => $user['USER_ID']]])->fetch();
                if (!empty($userFields['NAME'])) {
                    if ($nameProp = $propertyCollection->getPayerName()) {
                        $nameProp->setValue($userFields['NAME']);
                    }
                }
            }
    
            if (Config::checkFeature('savemsname') && Config::getMsNamePropId((int)$user['PERSON_TYPE']) > 0) {
                if ($property = $propertyCollection->getItemByOrderPropertyId(Config::getMsNamePropId((int)$user['PERSON_TYPE']))) {
                    $property->setValue($this->order->name);
                }
            }
        }
    }

    private function setBxPaymentCollection()
    {
        $paymentCollection = $this->orderBx->getPaymentCollection();
        $payment = $paymentCollection->createItem();
        $paySysId = Config::getDefaultImportPaysys() ?: 1;
        $paySystemArray = \Bitrix\Sale\PaySystem\Manager::getList(['filter' => ['ID' => $paySysId]])->fetch();

        if (Config::checkFeature('paymenttypesync')) {
            $paymentIds = array_flip(Config::getPaymentIds());
            $paymentProp = Config::getPaymentProp();
            if (Utils::is_count($this->customEntityAttrs) && !empty($paymentProp) && Utils::is_count($paymentIds)) {
                if ($currentDeliveryTypeMs = Helper::getCurrentDeliveryTypeMs($this->customEntityAttrs, $paymentProp)) {
                    $current = array_pop(explode('/', $currentDeliveryTypeMs['value']->href));
                    if (isset($paymentIds[$current]) && (int)$paymentIds[$current] > 0) {
                        $paySystemArray = \Bitrix\Sale\PaySystem\Manager::getById($paymentIds[$current]);
                    }
                }
            }
        }

        if (isset($paySystemArray['ID']) && (int)$paySystemArray['ID'] > 0) {
            $payment->setFields(array(
                'PAY_SYSTEM_ID' => $paySystemArray["ID"],
                'PAY_SYSTEM_NAME' => $paySystemArray["NAME"],
                'SUM' => $this->orderBx->getPrice(),
                'PAID' => (int)$this->order->payedSum > 0 && (int)$this->order->payedSum === (int)$this->order->sum ? 'Y' : 'N'
            ));
        }
    }

    private function setBxShipmentCollection()
    {
        $shipmentCollection = $this->orderBx->getShipmentCollection();
        $shipment = $shipmentCollection->createItem();
        $bxDeliveryId = Config::getDefaultImportDelivery() ? : \Bitrix\Sale\Delivery\Services\EmptyDeliveryService::getEmptyDeliveryServiceId();
        $service = \Bitrix\Sale\Delivery\Services\Manager::getById($bxDeliveryId);
        
        if (Config::checkFeature('deliverytypesync')) {
            $deliveryIds = Config::getDeliveryIds();
            $deliveryProp = Config::getDeliveryProp();
            if (
                Utils::is_count($this->customEntityAttrs) && 
                !empty($deliveryProp) && 
                Utils::is_count($deliveryIds) &&
                isset($deliveryIds[$bxDeliveryId])
            ) {
                if ($currentDeliveryTypeMs = Helper::getCurrentDeliveryTypeMs($this->customEntityAttrs, $deliveryProp)) {
                    $current = array_pop(explode('/', $currentDeliveryTypeMs['value']->href));
                    $selectBxDeliveryType = $deliveryIds[$bxDeliveryId];
                    if ($current !== $selectBxDeliveryType && isset(array_flip($deliveryIds)[$current])) {
                        $service = \Bitrix\Sale\Delivery\Services\Manager::getById(array_flip($deliveryIds)[$current]);
                        if ((int)$service['PARENT_ID'] > 0) {
                            $serviceParent = \Bitrix\Sale\Delivery\Services\Manager::getById((int)$service['PARENT_ID']);
                            if ($serviceParent['CLASS_NAME'] !== '\Bitrix\Sale\Delivery\Services\Group') {
                                $service['NAME'] = LangMsg::get('DELIVERY_PARENT_NAME', ['#PARENT#' => $serviceParent['NAME'], '#CURRENT#' => $service['NAME']]);
                            }
                        }
                    }
                }
            }
        }

        $shipmentItemCollection = $shipment->getShipmentItemCollection();
        foreach ($this->orderBx->getBasket() as $item) {
            $shipmentItem = $shipmentItemCollection->createItem($item);
            $shipmentItem->setQuantity($item->getQuantity());
        }
        
        $shipment->setFields(array(
            'DELIVERY_ID' => $service['ID'],
            'DELIVERY_NAME' => $service['NAME'],
        ));

        if (Config::checkFeature('deliverypricesync') && Config::checkVectorFromMsToBx('delivery_price', 'FULL')) {
            $errorsBasket = false;
            $arMsBasket = \CRbsMoyskladHelper::createArMsBasket($this, $errorsBasket);
            if (!$errorsBasket) {
                $isDeliverySet = Config::checkFeature('basketshipmentrefresh');
                if (isset($arMsBasket[Config::getDeliveryExternalCode()])) {
                    $orderDeliveryItem = $arMsBasket[Config::getDeliveryExternalCode()];
                    $orderDeliveryPrice = $this->orderBx->getDeliveryPrice() * 100;
                    unset($arMsBasket[Config::getDeliveryExternalCode()]);
                    if ((float)$orderDeliveryPrice !== (float)$orderDeliveryItem['PRICE']) {
                        $isDeliverySet = true;
                    }
                }
                if ($isDeliverySet) {

                    $priceDelivery = (int)$orderDeliveryItem['PRICE'] / 100;
                    if (Config::checkFeature('basketshipmentrefresh')) {
                        $priceDelivery = $shipment->calculateDelivery();
                    } 
                    $shipment->setField('BASE_PRICE_DELIVERY', $priceDelivery);
                    $shipment->setField('PRICE_DELIVERY', $priceDelivery);
                    $shipment->setField('CUSTOM_PRICE_DELIVERY', 'Y');

                }
            }
        }
    }

    private function setBxBasket()
    {
        $needBackCanBuyZero = [];
        $basket = \Bitrix\Sale\Basket::create($this->orderBx->getSiteId());
        $this->orderBx->setBasket($basket);

        if ($basketItems = $this->orderBx->getBasket()) {

            $errorsBasket = false;
            $arMsBasket = \CRbsMoyskladHelper::createArMsBasket($this, $errorsBasket);

            $currency = \Bitrix\Currency\CurrencyManager::getBaseCurrency();
            if (Config::checkFeature('setcurrency') && !empty($this->order->rate->currency->isoCode)) {
                $currency = $this->order->rate->currency->isoCode;
            }

            if (!$errorsBasket) {

                \CRbsMoyskladHelper::buildBxBasket($basketItems, $arMsBasket, [], $needBackCanBuyZero, $currency);

                if (Utils::is_count($needBackCanBuyZero)) {
                    foreach ($needBackCanBuyZero as $pId) {
                        \Bitrix\Catalog\ProductTable::update($pId, [
                            'CAN_BUY_ZERO' => 'N'
                        ]);
                    }
                }

            }
        }
    }

    public function getOrderEntity()
    {
        return $this->orderBx;
    }

    public function setActualOrderEntity(Order $order)
    {
        $this->orderBx = $order;
    }

    public function getAgent()
    {
        $result = (object)[];
        if ($this->isLoaded() && !empty((string)$this->order->agent->meta->href)) {
            $result = $this->order->agent;
        } 
        return $result;
    }

    public function getAgentHref(): string
    {
        if ($this->isLoaded()) {
            return !empty((string)$this->order->agent->meta->href) ? (string)$this->order->agent->meta->href : '';
        }
        return '';
    }

    public function setResponsePropPerson()
    {
        $rId = $this->orderBx->getField('RESPONSIBLE_ID');
        if ((int)$rId <= 0) {
            return;
        }

        $user = \Bitrix\Main\UserTable::getList([
            'filter' => [
                '=ID' => $rId
            ]
        ])->fetch();

        $propId = Config::getResponsePropId();
        if ((int)$user['ID'] <= 0 || empty($user['EMAIL']) || empty($propId)) {
            return;
        }
        
        $employee = ApiNew::get('/entity/employee', ['filter' => 'email=' . $user['EMAIL']]);
        if (Utils::is_success($employee) && Utils::array_exists($employee)) {
            $employee = $employee->rows[0];
            if(!empty($employee->meta)) {
                $this->orderChangesStack['attributes'][] = [
                    'id' => $propId,
                    'value' => (object)[
                        'meta' => $employee->meta
                    ]
                ];
            }
        }
    }

    public function saveOrderChanges()
    {
        if (!empty($this->orderChangesStack) && is_array($this->orderChangesStack) && !$this->isDisableOrderSync()) {

            $orderChangesStackForEvent = $this->orderChangesStack;
            
            Utils::send_bx_event(Config::getModuleId(true), 'OnBeforeSaveOrderChanges', [
                'orderMs' => $this->getOrder(),
                'orderBx' => $this->getOrderEntity(),
                'orderId' => $this->getOrderBxId(),
                'orderChangesStack' => $orderChangesStackForEvent
            ], $orderChangesStackForEvent);

            $this->orderChangesStack = $orderChangesStackForEvent;
            unset($orderChangesStackForEvent);

            if (isset($this->orderChangesStack['attributes']) && Utils::is_count($this->orderChangesStack['attributes'])) {
                $this->orderChangesStack['attributes'] = \CRbsMoyskladHelper::convertAttributesToNewApi($this->orderChangesStack['attributes']);
            }
            
            Webhook::cacheUpdateEntity($this->order->meta->href, $this->order->meta->type, 'UPDATE');

            if (!empty($this->orderChangesStack['positions']) && Utils::count($this->orderChangesStack['positions']) > self::$limitPositions) {

                $chunked = array_chunk($this->orderChangesStack['positions'], self::$limitPositions);
                
                $this->orderChangesStack['positions'] = array_shift($chunked);
                $result = ApiNew::put($this->order->meta->href, $this->orderChangesStack);

                if (Utils::array_exists($result, 'errors')) {
                    $this->addErrorMessageArray($result->errors);
                }

                foreach ($chunked as $part) {
                    $result = ApiNew::post($this->order->positions->meta->href, $part);
                    if (Utils::array_exists($result, 'errors')) {
                        $this->addErrorMessageArray($result->errors);
                    }
                }

            } else {

                $result = ApiNew::put($this->order->meta->href, $this->orderChangesStack);
                
                if(Utils::array_exists($result, 'errors')) {
                    $this->addErrorMessageArray($result->errors);
                } else {
                    $this->addSuccessMessage(LangMsg::get('SUCCESS_ORDER_SAVE_IN_MS', [
                        '#ORDER_ID#' => $this->getOrderBxId()
                    ]));
                }

            }
        }
    }

    public function isDisableOrderSync()
    {
        if ($this->isLoaded()) {
            if (
                $this->isDisableOrderPropMs() ||
                Config::isDisableOrderIdSync($this->getOrderBxId()) ||
                OrderFilter::isFiltred($this->getOrderBxId())
            ) {
                return true;
            }
        }

        return false;
    }

    public function isDisableOrderPropMs()
    {
        if ($disableOrderProp = Config::getDisableOrderProp()) {
            if (isset($this->boolAttrs[$disableOrderProp])) {
                return (bool)$this->boolAttrs[$disableOrderProp];
            }
        }
        return false;
    }

    public function checkOrderForImportTypeUpdate()
    {
        if ($updateFlag = Config::getImportTypeUpdateFlag()) {
            if (isset($this->boolAttrs[$updateFlag])) {
                return (bool)$this->boolAttrs[$updateFlag];
            }
        }
        return false;
    }

    /**check hooks */
    public function checkUpdateHook()
    {
        if ($this->isDisableOrderSync()) {
            return false;
        }

        if (Config::checkFeature('tracksync')) {
            if ($shipmentCollection = $this->orderBx->getShipmentCollection()) {
                if ($this->parseTracks()) {
                    if (Utils::is_count($this->tracks)) {
                        foreach ($shipmentCollection as $shipment) {
                            if (!$shipment->isSystem() && isset($this->tracks[$shipment->getField('ID')])) {
                                if ((string)$this->tracks[$shipment->getField('ID')] !== (string)$shipment->getField('TRACKING_NUMBER')) {
                                    $shipment->setFields([
                                        'TRACKING_NUMBER' => $this->tracks[$shipment->getField('ID')]
                                    ]);
                                }
                            }
                        }
                    } elseif (!empty($this->trackMain)) {
                        foreach ($shipmentCollection as $shipment) {
                            if (!$shipment->isSystem()) {
                                if ((string)$this->trackMain !== (string)$shipment->getField('TRACKING_NUMBER')) {
                                    $shipment->setFields([
                                        'TRACKING_NUMBER' => $this->trackMain
                                    ]);
                                }
                            }
                        }
                    }
                } elseif (empty($this->trackMain) && !Utils::is_count($this->tracks)) {
                    foreach ($shipmentCollection as $shipment) {
                        if (!$shipment->isSystem()) {
                            if (!empty($shipment->getField('TRACKING_NUMBER'))) {
                                $shipment->setFields([
                                    'TRACKING_NUMBER' => ''
                                ]);
                            }
                        }
                    }
                }
            }
        }
        
        if (Config::checkFeature('storesyncreverse')) {
            if ($shipmentCollectionStoreSync = $this->orderBx->getShipmentCollection()) {
                foreach ($shipmentCollectionStoreSync as $shipment) {
                    if (!$shipment->isSystem()) {

                        $storeId = (int)$shipment->getStoreId();
                        
                        if ($storeId > 0) {

                            $store = \Bitrix\Catalog\StoreTable::getList([
                                'filter' => [
                                    'ID' => $storeId
                                ],
                                'cache' => [
                                    'ttl' => 86400
                                ]
                            ])->fetch();
                            
                            if (!empty($store['XML_ID'])) {

                                $storeMs = $this->order->store;

                                if (!empty($storeMs->externalCode)) {
                                    if ((string)$storeMs->externalCode !== (string)$store['XML_ID']) {

                                        $storeCurrent = \Bitrix\Catalog\StoreTable::getList([
                                            'filter' => [
                                                '=XML_ID' => $storeMs->externalCode
                                            ],
                                            'cache' => [
                                                'ttl' => 86400
                                            ]
                                        ])->fetch();

                                        if (!empty($storeCurrent['ID']) && (int)$storeCurrent['ID'] > 0) {
                                            \Bitrix\Sale\Delivery\ExtraServices\Manager::saveStoreIdForShipment($shipment->getId(), $shipment->getDeliveryId(), $storeCurrent['ID']);
                                        }
                                    }
                                }

                            }
                        }
                        break;
                    }
                }
            }
        }

        if (Config::checkFeature('deliverytypesync')) {
            $deliveryIds = Config::getDeliveryIds();
            $deliveryProp = Config::getDeliveryProp();
            if (Utils::is_count($this->customEntityAttrs) && !empty($deliveryProp) && Utils::is_count($deliveryIds)) {
                if ($currentDeliveryTypeMs = Helper::getCurrentDeliveryTypeMs($this->customEntityAttrs, $deliveryProp)) {
                    $current = array_pop(explode('/', $currentDeliveryTypeMs['value']->href));
                    if ($shipmentCollectionSync = $this->orderBx->getShipmentCollection()) {
                        foreach ($shipmentCollectionSync as $shipment) {
                            if (!$shipment->isSystem()) {
                                $bxDeliveryId = $shipment->getField('DELIVERY_ID');
                                if(isset($deliveryIds[$bxDeliveryId]) && !$shipment->isShipped()) {
                                    $selectBxDeliveryType = $deliveryIds[$bxDeliveryId];
                                    if ($current !== $selectBxDeliveryType && isset(array_flip($deliveryIds)[$current])) {
                                        $service = \Bitrix\Sale\Delivery\Services\Manager::getById(array_flip($deliveryIds)[$current]);

                                        if ((int)$service['PARENT_ID'] > 0) {
                                            $serviceParent = \Bitrix\Sale\Delivery\Services\Manager::getById((int)$service['PARENT_ID']);
                                            if ($serviceParent['CLASS_NAME'] !== '\Bitrix\Sale\Delivery\Services\Group') {
                                                $service['NAME'] = LangMsg::get('DELIVERY_PARENT_NAME', ['#PARENT#' => $serviceParent['NAME'], '#CURRENT#' => $service['NAME']]);
                                            }
                                        }

                                        $shipment->setFields(array(
                                            'DELIVERY_ID' => $service['ID'],
                                            'DELIVERY_NAME' => $service['NAME'],
                                        ));
                                    }
                                }                                
                                break;
                            }
                        }
                    }
                }
            }
        }

        if (Config::checkFeature('paymenttypesync')) {
            $paymentIds = Config::getPaymentIds();
            $paymentProp = Config::getPaymentProp();
            if (Utils::is_count($this->customEntityAttrs) && !empty($paymentProp) && Utils::is_count($paymentIds)) {
                if ($currentDeliveryTypeMs = Helper::getCurrentDeliveryTypeMs($this->customEntityAttrs, $paymentProp)) {
                    $current = array_pop(explode('/', $currentDeliveryTypeMs['value']->href));
                    if ($paymentCollection = $this->orderBx->getPaymentCollection()) {
                        foreach ($paymentCollection as $payment) {
                            if(!$payment->isPaid()) {
                                $bxPaymentId = $payment->getField('PAY_SYSTEM_ID');
                                if($paymentIds[$bxPaymentId]) {
                                    $selectBxPaymentType = $paymentIds[$bxPaymentId];
                                    if ($current !== $selectBxPaymentType && isset(array_flip($paymentIds)[$current])) {
                                        $service = \Bitrix\Sale\PaySystem\Manager::getById(array_flip($paymentIds)[$current]);
                                        $payment->setFields(array(
                                            'PAY_SYSTEM_ID' => $service['ID'],
                                            'PAY_SYSTEM_NAME' => $service['NAME'],
                                        ));
                                    }
                                }                                
                            }                            
                            break;
                        }
                    }
                }
            }
        }

        if (Config::checkFeature('commentsync')) {
            if (Helper::isDifferentCommentsText($this->orderBx->getField('COMMENTS'), $this->order->description)) {
                $this->orderBx->setField('COMMENTS', trim($this->order->description));
            }
        }

        if (Config::checkFeature('commentusersync')) {
            $userCommentProp = Config::getUserCommentProp();
            if (!empty($userCommentProp) && isset($this->strAttrs[$userCommentProp])) {
                if (Helper::isDifferentCommentsText($this->orderBx->getField('USER_DESCRIPTION'), $this->strAttrs[$userCommentProp])) {
                    $this->orderBx->setField('USER_DESCRIPTION', trim($this->strAttrs[$userCommentProp]));
                    $changed = true;
                }
            }
        }

        if (Config::checkFeature('statussync') && Config::checkVectorFromMsToBx('states', 'FULL')) {
            $exStatusId = Config::getExternalStatusId($this->orderBx->getField('STATUS_ID'));
            if (empty($exStatusId) || $exStatusId === false) {
                $this->addWarningMessage(LangMsg::get('ERROR_LOAD_STATUS', ['#ID#' => $this->orderBx->getField('STATUS_ID')]));
            } else {
                $exStatusId = Config::getBaseHrefLinkNew('state') . $exStatusId;
                if ($this->order->state->meta->href !== $exStatusId) {
                    $newExStatusId = Helper::clearBaseHrefLink($this->order->state->meta->href, 'state');
                    $statusId = Config::getStatusIdByExternalStatusId($newExStatusId);
                    if (empty($statusId) || $statusId === false) {
                        $this->addWarningMessage(LangMsg::get('ERROR_LOAD_STATUS', ['#ID#' => $exStatusId]));
                    } else {
                        $this->orderBx->setField('STATUS_ID', $statusId);
                    }
                }
            }
        }

        if (Config::checkFeature('cancelsync')) {
            $exStatusId = Config::getExternalCancelStatusId();
            if (empty($exStatusId) || $exStatusId === false) {
                $this->addWarningMessage(LangMsg::get('ERROR_LOAD_STATUS', ['#ID#' => $this->orderBx->getField('STATUS_ID')]));
            } else {
                $exStatusId = Config::getBaseHrefLinkNew('state') . $exStatusId;
                if ($this->order->state->meta->href === $exStatusId && !$this->orderBx->isCanceled()) {
                    $this->orderBx->setField('CANCELED', 'Y');
                    $this->orderBx->setField('UPDATED_1C', 'Y');
                }
                if ($this->order->state->meta->href !== $exStatusId && $this->orderBx->isCanceled()) {
                    $this->orderBx->setField('CANCELED', 'N');
                    $this->orderBx->setField('UPDATED_1C', 'N');
                }
            }
        }

        if (Config::checkFeature('propsreversesync')) {
            \CRbsMoyskladHelper::setBxProps($this);
        }

        if (Config::checkFeature('sales_channel_enabled') && Config::checkVectorFromMsToBx('saleschannel', 'FULL')) {
            $this->setSalesChannelBx();
        }

        if(Config::checkFeature('change_user_by_cp')) {
            $this->updateUserByCounterParty();
        }

        $errorsBasket = false;
        $arMsBasket = \CRbsMoyskladHelper::createArMsBasket($this, $errorsBasket);

        $orderDeliveryItem = [];
        if (isset($arMsBasket[Config::getDeliveryExternalCode()])) {
            $orderDeliveryItem = $arMsBasket[Config::getDeliveryExternalCode()];
            unset($arMsBasket[Config::getDeliveryExternalCode()]);
        }

        if (Config::checkFeature('deliverypricesync') && Config::checkVectorFromMsToBx('delivery_price', 'FULL')) {
            if ($shipmentCollectionBasket = $this->orderBx->getShipmentCollection()) {
                foreach ($shipmentCollectionBasket as $shipment) {
                    if (!$shipment->isSystem()) {
                        if(!$shipment->isShipped()) {
                            $currentMsPrice = 0;
                            if (count($orderDeliveryItem) > 0) {
                                $currentMsPrice = (float)(($orderDeliveryItem['PRICE'] * $orderDeliveryItem['QUANTITY']) / 100);
                            }
                            if (Config::checkFeature('basketshipmentrefresh')) {
                                $calculated = $shipment->calculateDelivery();
                                $shipment->setField('BASE_PRICE_DELIVERY', $calculated->getPrice());
                                $shipment->setField('PRICE_DELIVERY', $calculated->getPrice());
                            } else if ((float)$currentMsPrice !== (float)$this->orderBx->getDeliveryPrice()) {
                                $shipment->setField('BASE_PRICE_DELIVERY', $currentMsPrice);
                                $shipment->setField('PRICE_DELIVERY', $currentMsPrice);
                                $shipment->setField('CUSTOM_PRICE_DELIVERY', 'Y');
                            }
                        }
                        break;
                    }
                }
            }
        }

        $needBackCanBuyZero = [];

        if (Config::checkFeature('basketsync')) {

            if ($basketItems = $this->orderBx->getBasket()) {

                $arBxBasket = \CRbsMoyskladHelper::createArBxBasket($basketItems);
                $hasChangedBasket = \CRbsMoyskladHelper::hasBasketChanges($arMsBasket, $arBxBasket);

                $currency = \Bitrix\Currency\CurrencyManager::getBaseCurrency();
                if (Config::checkFeature('setcurrency') && !empty($this->order->rate->currency->isoCode)) {
                    $currency = $this->order->rate->currency->isoCode;
                }

                if (!$errorsBasket && $hasChangedBasket) {
                    \CRbsMoyskladHelper::buildBxBasket($basketItems, $arMsBasket, $arBxBasket, $needBackCanBuyZero, $currency);
                }

            }
        }

        if (\CRbsMoyskladHelper::hasOrderChanges($this->orderBx)) { 

            $this->saveResult();

            if (Utils::is_count($needBackCanBuyZero)) {
                foreach ($needBackCanBuyZero as $pId) {
                    \Bitrix\Catalog\ProductTable::update($pId, [
                        'CAN_BUY_ZERO' => 'N'
                    ]);
                }
            }

        }
    }

    private function parseTracks()
    {
        $propTrackId = Config::getTrackNumberPropertyId();
        if (isset($this->strAttrs[$propTrackId]) && !empty($this->strAttrs[$propTrackId])) {
            $trackNumberStr = $this->strAttrs[$propTrackId];
            $tracks = Helper::getTracksFromString($trackNumberStr);

            if (Utils::is_count($tracks)) {
                $this->tracks = $tracks;
            } else {
                $this->trackMain = $trackNumberStr;
            }

            return true;
        }

        return false;
    }

    private function setAttributes()
    {
        if ($this->order !== null && $this->order->attributes) {
            foreach ($this->order->attributes as $attr) {
                if(in_array($attr->type, Config::getStandartEntityNamesForEnumProp())) {
                    $this->customEntityAttrsDefault[$attr->id] = $attr->value->meta;
                } else {
                    switch ($attr->type) {
                        case 'string':
                        case 'text':
                        case 'link':
                            $this->strAttrs[$attr->id] = $attr->value;
                            break;
                        case 'customentity':
                            $this->customEntityAttrs[$attr->id] = $attr->value->meta;
                            break;
                        case 'boolean':
                            $this->boolAttrs[$attr->id] = $attr->value;
                            break;
                        case 'file':
                            $this->fileAttrs[$attr->id] = $attr->value;
                            break;
                    }
                }
            }
        }

        //set default attrs if empty
        $metaOrder = \CRbsMoyskladHelper::getMetadataWithAttrs('customerorder', 86400 * 7);
        if (Utils::array_exists($metaOrder, 'attributes')) {
            foreach ($metaOrder->attributes as $attr) {
                switch ($attr->type) {
                    case 'string':
                    case 'text':
                    case 'link':
                        if (!isset($this->strAttrs[$attr->id])) {
                            $this->strAttrs[$attr->id] = '';
                        }
                    break;
                    case 'customentity':
                        if (!isset($this->customEntityAttrs[$attr->id])) {
                            //hack for set delivery type with empty customentity value in order ms
                            $entityId = array_pop(explode('/', $attr->customEntityMeta->href));
                            $this->customEntityAttrs[$attr->id] = (object)[
                                'href' => ApiNew::getApiEndPointUrl() . "/entity/customentity/{$entityId}/empty",
                                'metadataHref' => $attr->customEntityMeta->href
                            ];
                        }
                    break;
                    case 'boolean':
                        if (!isset($this->boolAttrs[$attr->id])) {
                            $this->boolAttrs[$attr->id] = false;
                        }
                    break;
                }
            }
        }
    }

    public function updateUserByCounterParty()
    {
        $userParams = \Rbs\Moysklad\Counterparty::getUserBxFromAgentHref($this->order->agent);
        if(!empty($userParams['LAST_ERROR'])) {
            $this->addErrorMessage($userParams['LAST_ERROR']);
        }
        if(!empty($userParams['USER_ID']) && (int)$userParams['USER_ID'] > 0) {
            $this->orderBx->setFieldNoDemand('USER_ID', $userParams['USER_ID']);
            $this->setBxPropertyCollection($userParams);
        }        
    }

    public function getAttributesByType($attrTypes = ['string']): array
    {
        $result = [];
        if (Utils::array_exists($this->order, 'attributes')) {
            foreach ($this->order->attributes as $attr) {
                if (in_array($attr->type, $attrTypes)) {
                    if ($attr->type === 'file') {
                        $result[$attr->id] = $attr;
                    } else {
                        $result[$attr->id] = $attr->value;
                    }
                }
            }
        }
        return $result;
    }

    public function setBasket()
    {
        if (Config::checkFeature('basket_bx_ms_ignore_enabled')) {
            $ignoreBasketList = Config::getOptionArray('ignore_bx_ms_basket_list', []);
            if (Utils::is_count($ignoreBasketList) && !empty($this->order->state->id)) {
                if (in_array($this->order->state->id, $ignoreBasketList)) {
                    return;
                }
            }
        }

        $hasChangedBasket = true;
        
        if ($basketItems = $this->orderBx->getBasket()) {
            $errorbasket = false;
            $arMsBasket = \CRbsMoyskladHelper::createArMsBasket($this, $errorbasket);
            if(!$errorbasket) {
                $arBxBasket = \CRbsMoyskladHelper::createArBxBasket($basketItems);
                $hasChangedBasket = \CRbsMoyskladHelper::hasBasketChanges($arMsBasket, $arBxBasket);
            }
        }

        $hasDeliveryPriceChanges = \CRbsMoyskladHelper::hasDeliveryPriceChanges($this->orderBx, $this->order);

        if($hasChangedBasket || $hasDeliveryPriceChanges) {
            $rbsMsOrder = new \CRbsMoyskladBasketOrder($this, false);
            $rbsMsOrder->setBasketPositions();
        }
        
    }

    public function setCurrency()
    {
        if ($this->isLoaded()) {
            $currencyBx = $this->orderBx->getCurrency();
            if (!empty($currencyBx)) {
                $currency = ApiNew::get('/entity/currency', ['filter' => 'isoCode=' . $currencyBx], 86400);
                if (Utils::is_success($currency) && Utils::array_exists($currency)) {
                    $this->orderChangesStack['rate'] = (object)[
                        'currency' => (object)[
                            'meta' => $currency->rows[0]->meta
                        ]
                    ];
                }
            }
        }
    }

    public function setStatus()
    {
        if ($this->isLoaded()) {
            $this->changeStatus(Config::getExternalStatusId($this->orderBx->getField('STATUS_ID')));
        }
    }

    public function setSalesChannel()
    {
        if ($this->isLoaded() && $this->getOrderEntity()) {
            $pTypeId = $this->getOrderEntity()->getPersonTypeId();
            if((int)$pTypeId > 0) {
                $propId = (int)Config::getOption('sales_channel_for_' . $pTypeId, '');
                $propValue = \CRbsMoyskladHelper::getEnumBxPropValue($this->getOrderEntity(), $propId);
                if(!empty($propValue)) {
                    $salesChannel = ApiNew::get('/entity/saleschannel', ['filter' => 'name=' . $propValue], 86400);
                    if (Utils::is_success($salesChannel) && Utils::array_exists($salesChannel)) {
                        $this->orderChangesStack['salesChannel'] = (object)[
                            'meta' => $salesChannel->rows[0]->meta
                        ];
                    }
                } else {
                    $this->orderChangesStack['salesChannel'] = null;
                }
            }
        }
    }

    public function setSalesChannelBx()
    {
        if ($this->isLoaded() && $this->getOrderEntity()) {
            $pTypeId = $this->getOrderEntity()->getPersonTypeId();
            if ((int)$pTypeId > 0) {
                $propId = (int)Config::getOption('sales_channel_for_' . $pTypeId, '');
                $salesChannelBx = mb_strtolower(\CRbsMoyskladHelper::getEnumBxPropValue($this->getOrderEntity(), $propId));
                $salesChannelMs = '';
                if(Utils::property_exists($this->order, ['salesChannel'])) {
                    $salesChannelMs = mb_strtolower($this->order->salesChannel->name);
                }
                if($salesChannelBx !== $salesChannelMs){
                    \CRbsMoyskladHelper::setBxOneProp($this, $propId, $salesChannelMs);
                }
            }
        }
    }

    public function setCancel()
    {
        if ($this->isLoaded()) {
            if ($this->orderBx->isCanceled()) {
                $this->changeStatus(Config::getExternalCancelStatusId());
            }
        }
    }

    private function changeStatus($exStatusId = '')
    {
        if ($exStatusId === 'N') {
            return;
        }

        if (empty($exStatusId) || $exStatusId === false) {
            $this->addWarningMessage(LangMsg::get('ERROR_LOAD_STATUS', ['#ID#' => $this->orderBx->getField('STATUS_ID')]));
            return;
        }

        if (Config::checkFeature('states_bx_ms_ignore_enabled')) {
            $ignoreStateList = Config::getOptionArray('ignore_bx_ms_state_list', []);
            if (Utils::is_count($ignoreStateList) && !empty($this->order->state->id)) {
                if(in_array($this->order->state->id, $ignoreStateList)) {
                    return;
                }
            }
        }

        $exStatusId = Config::getBaseHrefLinkNew('state') . $exStatusId;
        if ($this->order->state->meta->href !== $exStatusId) {
            $this->order->state->meta->href = $exStatusId;
            $this->orderChangesStack['state'] = (object)[
                'meta' => $this->order->state->meta
            ];
        }
    }

    public function setTrack()
    {
        if ($this->isLoaded()) {
            $propTrackId = Config::getTrackNumberPropertyId();
            if (empty($propTrackId)) {
                return;
            }

            $resultTracks = [];
            if ($shipmentCollection = $this->orderBx->getShipmentCollection()) {
                foreach ($shipmentCollection as $shipment) {
                    if (!$shipment->isSystem()) {
                        if (!empty($shipment->getField('TRACKING_NUMBER'))) {
                            $resultTracks[$shipment->getField('ID')] = (string)$shipment->getField('TRACKING_NUMBER');
                        }
                    }
                }
            }
 
            if ($this->parseTracks()) {
                if (Utils::is_count($this->tracks)) {
                    if (Utils::count($resultTracks) === Utils::count($this->tracks)) {
                        $hasChange = false;
                        foreach ($resultTracks as $shId => $trackNum) {
                            if (!isset($this->tracks[$shId])) {
                                $hasChange = true;
                            } else {
                                if ($this->tracks[$shId] !== $trackNum) {
                                    $hasChange = true;
                                }
                            }
                        }
                        if (!$hasChange) {
                            return;
                        }
                    }
                } elseif (!empty($this->trackMain) && Utils::count($resultTracks) === 1) {
                    if ((string)$this->trackMain === (string)reset($resultTracks)) {
                        return;
                    }
                } elseif (empty($this->trackMain) && Utils::count($resultTracks) === 0) {
                    return;
                }
            }

            
            $resultArrStr = [];
            if (Utils::count($resultTracks) === 1) {
                $resultArrStr[] = current($resultTracks);
            } else {
                foreach ($resultTracks as $shId => $trackNum) {
                    $resultArrStr[] = "{$shId}: {$trackNum}";
                }
            }
           

            $this->orderChangesStack['attributes'][] = [
                'id' => $propTrackId,
                'value' => Utils::is_count($resultArrStr) ? implode("\n", $resultArrStr) : ''
            ];
        }
    }

    public function setStore()
    {
        if ($this->isLoaded()) {
            if ($shipmentCollection = $this->orderBx->getShipmentCollection()) {
                foreach ($shipmentCollection as $shipment) {
                    if (!$shipment->isSystem()) {
                        $storeId = (int)$shipment->getStoreId();
                        if ($storeId > 0) {

                            $store = \Bitrix\Catalog\StoreTable::getList([
                                'filter' => [
                                    'ID' => $storeId
                                ],
                                'cache' => [
                                    'ttl' => 86400
                                ]
                            ])->fetch();
                            
                            if (!empty($store['XML_ID'])) {
                                $storeMs = ApiNew::get('/entity/store', ['filter' => 'externalCode=' . $store['XML_ID']], 86400);
                                if (Utils::is_success($storeMs) && Utils::array_exists($storeMs)) {
                                    $storeMsObj = array_pop($storeMs->rows);
                                    if ((string)$storeMsObj->meta->href !== (string)$this->order->store->meta->href) {
                                        $this->orderChangesStack['store'] = (object)['meta' => ['href' => $storeMsObj->meta->href, 'type' => 'store']];
                                    }
                                }
                            }

                        }
                        break;
                    }
                }
            }
        }
    }

    public function setDeliveryName()
    {
        if ($this->isLoaded()) {
            $propDeliveryNameId = Config::getDeliveryNamePropertyId();
            if (!empty($propDeliveryNameId)) {
                $currentDeliveryName = "";
                if (isset($this->strAttrs[$propDeliveryNameId]) && !empty($this->strAttrs[$propDeliveryNameId])) {
                    $currentDeliveryName = Helper::clearAllSpaces($this->strAttrs[$propDeliveryNameId]);
                }

                $newDeliveryNameArr = [];
                if ($shipmentCollection = $this->orderBx->getShipmentCollection()) {
                    foreach ($shipmentCollection as $shipment) {
                        if (!$shipment->isSystem()) {
                            $newDeliveryNameArr[] = "{$shipment->getField('ID')}: {$shipment->getField('DELIVERY_NAME')}";
                        }
                    }
                }
               
                if (!empty($currentDeliveryName) && Utils::is_count($newDeliveryNameArr)) {
                    if ($currentDeliveryName === Helper::makeClearStringFromArray($newDeliveryNameArr)) {
                        return;
                    }
                }
                $this->orderChangesStack['attributes'][] = [
                    'id' => $propDeliveryNameId,
                    'value' => implode("\n", $newDeliveryNameArr)
                ];
            }
        }
    }

    public function setDeliveryType()
    {
        if ($this->isLoaded()) {
            $deliveryIds = Config::getDeliveryIds();
            $deliveryProp = Config::getDeliveryProp();

            $bxDeliveryId = 0;
            if ($shipmentCollectionSync = $this->orderBx->getShipmentCollection()) {
                foreach ($shipmentCollectionSync as $shipment) {
                    if (!$shipment->isSystem()) {
                        $bxDeliveryId = $shipment->getField('DELIVERY_ID');
                        if (!isset($deliveryIds[$bxDeliveryId])) {
                            $bxDeliveryId = 0;
                        }
                        break;
                    }
                }
            }

            if ((int)$bxDeliveryId > 0 && Utils::is_count($deliveryIds) && !empty($deliveryProp)) {
                $selectBxDeliveryType = $deliveryIds[$bxDeliveryId];
                if (Utils::is_count($this->customEntityAttrs) && !empty($selectBxDeliveryType)) {
                    if ($currentDeliveryTypeMs = Helper::getCurrentDeliveryTypeMs($this->customEntityAttrs, $deliveryProp)) {
                        $current = array_pop(explode('/', $currentDeliveryTypeMs['value']->href));
                        if ($current !== $selectBxDeliveryType) {
                            $attrMsInfo = ApiNew::get(str_replace($current, $selectBxDeliveryType, $currentDeliveryTypeMs['value']->href), [], 86400);
                            if (Utils::is_success($attrMsInfo) && Utils::property_exists($attrMsInfo, ['meta'])) {
                                $this->orderChangesStack['attributes'][] = [
                                    'id' => $currentDeliveryTypeMs['id'],
                                    'value' => (object)['meta' => $attrMsInfo->meta]
                                ];
                            }
                        }
                    }
                }
            }
        }
    }

    public function setPaymentType()
    {
        (new ExportPayment($this))->setPaymentType();
    }

    public function setDeliveryPrice()
    {
        if ($this->isLoaded()) {

            $orderDeliveryPrice = (float)$this->orderBx->getDeliveryPrice() * 100;
            $currentDeliveryMsPrice = 0;

            $positions = [];
            foreach($this->order->positions->rows as $key => $row) {

                $keyOfPosition = $key;

                if($row->assortment->externalCode === Config::getDeliveryExternalCode()) {
                    $currentDeliveryMsPrice = \CRbsMoyskladHelper::getPositionFinalPrice($row);
                    $keyOfPosition = Config::getDeliveryExternalCode();
                }

                $positions[$keyOfPosition] = [
                    "quantity" => (float)$row->quantity,
                    "reserve" => (float)$row->reserve,
                    "price" => (float)$row->price,
                    "discount" => (float)$row->discount,
                    'assortment' => (object)[
                        'meta' => $row->assortment->meta
                    ],
                    'vat' => (float)$row->vat,
                    'vatEnabled' => (bool)$row->vatEnabled
                ];

                if(!empty($row->pack)) {
                    $positions[$keyOfPosition]['pack'] = $row->pack;
                }
                
            }

            if($orderDeliveryPrice !== $currentDeliveryMsPrice) {
                if(isset($positions[Config::getDeliveryExternalCode()])) {
                    $positions[Config::getDeliveryExternalCode()]['quantity'] = 1;
                    $positions[Config::getDeliveryExternalCode()]['discount'] = 0;
                    $positions[Config::getDeliveryExternalCode()]['price'] = $orderDeliveryPrice;
                    //set
                } else if ($orderDeliveryPrice > 0) {
                    $serviceResonse = ApiNew::get('/entity/service', ['filter' => 'externalCode=' . Config::getDeliveryExternalCode()], Config::cacheTime('basket_items_ms'));
                    if (Utils::is_success($serviceResonse)) {

                        if (!Utils::array_exists($serviceResonse)) {
                            $deliveryService = \CRbsMoyskladHelper::createDeliveryService();
                            $this->addInfoMessage(LangMsg::get('ADDED_SERVICE_MS', ['#HREF#' => $deliveryService->{'meta'}->{'uuidHref'}, '#ID#' => Config::getDeliveryExternalCode(), '#NAME#' => LangMsg::get('DELIVERY_SERVICE_NAME')]));
                        } else {
                            $deliveryService = current($serviceResonse->{'rows'});
                        }

                        if(Utils::property_exists($deliveryService, ['meta', 'href'])) {
                            $positions[Config::getDeliveryExternalCode()] = [
                                "quantity" => (float)1,
                                "reserve" => Config::checkFeature('basketreservededit') ? (float)1 : (float)0,
                                "price" => (float)$orderDeliveryPrice,
                                "discount" => (float)0,
                                'assortment' => (object)[
                                    'meta' => $deliveryService->meta
                                ],
                                'vat' => (int)Config::getOption('dprice_vat'),
                            ];
                        }
                    }
                }
            }

            if(count($positions) > 0) {
                $this->setOrderChangeStack('positions', array_values($positions));
            }
        }
    }

    public function setPaysystemName()
    {
        (new ExportPayment($this))->setPaysystemName();
    }

    public function setPaysystemInfo()
    {
        (new ExportPayment($this))->setPaysystemInfo();
    }

    public function setDescription()
    {
        if ($this->isLoaded()) {
            if (Helper::isDifferentCommentsText($this->orderBx->getField('COMMENTS'), $this->order->description)) {
                $this->orderChangesStack['description'] = trim($this->orderBx->getField('COMMENTS'));
            }
        }
    }

    public function setUserDescription()
    {
        if ($this->isLoaded()) {
            $userCommentProp = Config::getUserCommentProp();
            if (!empty($userCommentProp)) {
                if ($userCommentProp === 'ADDR_comment') {
                    $this->orderChangesStack['shipmentAddressFull']['comment'] = trim($this->orderBx->getField('USER_DESCRIPTION'));
                } else {
                    $this->orderChangesStack['attributes'][$userCommentProp] = [
                        'id' => $userCommentProp,
                        'value' => trim($this->orderBx->getField('USER_DESCRIPTION'))
                    ];
                }
            }
        }
    }

    public function setResponsePerson()
    {
        if ($this->isLoaded()) {
            $rId = $this->orderBx->getField('RESPONSIBLE_ID');
            if ((int)$rId <= 0) {
                return;
            }

            $user = \Bitrix\Main\UserTable::getList([
                'filter' => [
                    '=ID' => $rId
                ]
            ])->fetch();

            if ((int)$user['ID'] <= 0 || empty($user['EMAIL'])) {
                return;
            }

            $employee = ApiNew::get('/entity/employee', ['filter' => 'email=' . $user['EMAIL']]);
            if(Utils::is_success($employee) && Utils::array_exists($employee)) {
                $employee = $employee->rows[0];
                if ($employee->meta->href !== $this->order->owner->meta->href) {
                    $this->orderChangesStack['owner'] = (object)[
                        'meta' => $employee->meta
                    ];
                }
                if (Config::checkFeature('employeegroupsync')) {
                    if ($employee->group->meta->href !== $this->order->group->meta->href) {
                        $this->orderChangesStack['group'] = (object)[
                            'meta' => $employee->group->meta
                        ];
                    }
                }
            }
        }
    }

    /**props */

    public function setProps()
    {
        \CRbsMoyskladHelper::setMsProps($this);
    }

    /**work with demands */

    //default demand create
    public function checkDemand() 
    {
        if ($this->isLoaded()) {

            if ($shipmentCollectionStoreSync = $this->orderBx->getShipmentCollection()) {
                foreach ($shipmentCollectionStoreSync as $shipment) {
                    if (!$shipment->isSystem() && $shipment->isShipped()) {

                        $isPriceInc = Config::checkFeature('demand_sync_price');
                        $vatdd = Config::getOption('demand_sync_vat', 'from_order');
                        $vatddInc = Config::getOption('demand_sync_vat_inc', 'from_order');
                        $syncId = Config::getOption('demand_sync_id', 'N');
                        $syncIdComment = Config::getOption('demand_sync_id_comment', 'N');

                        $demandFields = [
                            'customerOrder' => (object)[
                                'meta' => $this->order->meta
                            ],
                            'agent' => (object)[
                                'meta' => $this->order->agent->meta
                            ],
                            'organization' => (object)[
                                'meta' => $this->order->organization->meta
                            ],
                            'externalCode' => $shipment->getField('XML_ID') ? (string)$shipment->getField('XML_ID') : (string)$shipment->getField('ID'),
                            'vatEnabled' => ($vatdd === 'from_order') ? (bool)$this->order->vatEnabled : ($vatdd === 'hard_set' ? true : false),
                            'vatIncluded' => ($vatddInc === 'from_order') ? (bool)$this->order->vatIncluded : ($vatddInc === 'hard_set' ? true : false)
                        ];

                        if(!empty($this->order->store->meta)) {
                            $demandFields['store'] = (object)[
                                'meta' => $this->order->store->meta
                            ];
                        }

                        if (empty($demandFields['store'])) {
                            $storeDefault = Config::getOption('demand_sync_store_default', '');
                            if (empty($storeDefault)) {
                                $storeMs = ApiNew::get('/entity/store', ['limit' => 1], 86400);
                                if (Utils::is_success($storeMs) && Utils::array_exists($storeMs)) {
                                    $storeDefault = $storeMs->rows[0]->id;
                                }
                            }
                            if (!empty($storeDefault)) {
                                $demandFields['store'] = Config::getMetaData('store', $storeDefault);
                            }
                        }

                        if (!empty($syncId) && $syncId !== 'N') {
                            $demandFields['name'] = \CRbsMoyskladHelper::getDocumentUniqName('demand', $shipment->getField($syncId));
                        }
                        if (!empty($syncIdComment) && $syncIdComment !== 'N') {
                            $demandFields['description'] = LangMsg::get(
                                'COMMENT_WITH_DOC_ID',
                                [
                                    '#TYPE#' => LangMsg::get('COMMENT_WITH_DOC_ID_TYPE_DEMAND'),
                                    '#ID#' => $shipment->getField($syncIdComment)
                                ]
                            );
                        }

                        $positionsMs = ApiNew::get($this->order->positions->meta->href);

                        if (Utils::is_success($positionsMs) && Utils::array_exists($positionsMs)) {
                            foreach ($positionsMs->rows as $row) {

                                if (!$isPriceInc) {
                                    if ($row->assortment->meta->type === 'service') {
                                        $assortmentItem = ApiNew::get($row->assortment->meta->href, [], 86400);
                                        if (Utils::is_success($assortmentItem) && !empty($assortmentItem->externalCode)) {
                                            if ($assortmentItem->externalCode === Config::getDeliveryExternalCode()) {
                                                continue;
                                            }
                                        }
                                    }
                                }

                                $demandFields['positions'][] = [
                                    'quantity' => $row->quantity,
                                    'price' => $row->price,
                                    'discount' => $row->discount,
                                    'vat' => $row->vat,
                                    'assortment' => $row->assortment,
                                ];
                            }
                        }

                        if (Config::checkFeature('demand_default_update')) {
                            $currentDemandMs = ApiNew::get('/entity/demand/', ['filter' => 'externalCode=' . (string)$demandFields['externalCode']]);
                            if (Utils::is_success($currentDemandMs)) {
                                if (Utils::array_exists($currentDemandMs)) {
                                    $currentDemand = $currentDemandMs->rows[0];
                                    if (!empty($currentDemand->meta->href)) {
                                        $demandPost = ApiNew::put($currentDemand->meta->href, $demandFields);
                                    }
                                } else {
                                    $demandPost = ApiNew::post('/entity/demand/', $demandFields);
                                }
                            }
                        } else {
                            if (!empty($this->order->demands) && Utils::is_count($this->order->demands)) {
                                return;
                            }
                            $demandPost = ApiNew::post('/entity/demand/', $demandFields);
                        }

                        if (Utils::has_errors($demandPost)) {
                            $this->addErrorMessageArray($demandPost->errors);
                        }

                        break;
                    }
                }
            }
        }
    }

    /**Work with payments */

    public function checkPaymentById(int $paymentId)
    {
        if ($this->isLoaded()) {
            if ($paymentList = $this->orderBx->getPaymentCollection()) {
                foreach ($paymentList as $payment) {
                    if ((int)$paymentId === (int)$payment->getId()) {
                        return $this->checkPayment($payment);
                    }
                }
            }
        }
    }

    public function checkPayment(Payment $payment)
    {
        if ($this->isLoaded() && !$this->hasErrors()) {
            if (Config::getPaySyncType() === 'default') {
                $msPaymentHref = $this->findPaymentBySum($payment->getSum());
                if ($payment->isPaid()) {
                    if (!$msPaymentHref) {
                        $this->setPayment($payment, true);
                    }
                } else {
                    if ($msPaymentHref) {
                        ApiNew::delete($msPaymentHref);
                    }
                }
            } elseif (Config::getPaySyncType() === 'full') {
                (new ExportPayment($this))->checkPayment($payment);
            }
        }
    }

    public function checkAllPayments()
    {
        if ($this->isLoaded() && !$this->hasErrors()) {
            if ($paymentCollection = $this->orderBx->getPaymentCollection()) {
                foreach ($paymentCollection as $payment) {
                    $this->checkPayment($payment);
                }
            }
        }
    }

    public function createBxPayment($paymentItem, $operation)
    {
        return (new ImportPayment($this))->createBxPayment($paymentItem, $operation);
    }

    public static function checkAllOrdersPayments($paymentItem = null, $orderBxChecked)
    {
        ImportPayment::checkAllOrdersPayments($paymentItem, $orderBxChecked);
    }

    public function updateBxPayment($paymentItem, $opertaion)
    {
        return (new ImportPayment($this))->updateBxPayment($paymentItem, $opertaion);
    }

    public function recalcFirstPayment()
    {
        (new ImportPayment($this))->recalcFirstPayment();
    }

    public function deleteBxPayment($paymentItem)
    {
        return (new ImportPayment($this))->deleteBxPayment($paymentItem);
    }

    public function getPaySystemIdFromPaymentMs($paymentItem)
    {
        return (new ExportPayment($this))->getPaySystemIdFromPaymentMs($paymentItem);
    }

    public function getPaySystemIdFromPaymentBx($paymentItem, $paySystemIdBx = 0)
    {
        return (new ExportPayment($this))->getPaySystemIdFromPaymentBx($paymentItem, $paySystemIdBx);
    }

    private function findPaymentBySum(float $sum)
    {
        if ($this->order->payments) {
            foreach ($this->order->payments as $payment) {
                if ((int)$payment->linkedSum === (int)round($sum * 100, 0)) {
                    return $payment->meta->href;
                }
            }
        }
        return false;
    }

    private function setPayment(Payment $payment, $isApplicable)
    {
        return (new ExportPayment($this))->setPayment($payment, $isApplicable);
    }

    public function deletePaymentByExternalCode(string $externalCode = '')
    {
        return (new ExportPayment($this))->deletePaymentByExternalCode($externalCode);
    }

    public function getOrderBxId(): int
    {
        if($this->orderBx instanceof \Bitrix\Sale\Order) {
            return $this->orderBx->getId();
        }
        return -1;
    }

    public function saveResult(): \Bitrix\Main\Result
    {
        
        if (!$this->orderBx instanceof \Bitrix\Sale\Order) {
            $result = new \Bitrix\Main\Result();
            $result->addError(new \Bitrix\Main\Error("Argument type error"));
            return $result;
        }

        $this->orderBx->doFinalAction(true); 
        $result = $this->orderBx->save();
        if (!$result->isSuccess()) {
            $this->addErrorMessageArray($result->getErrorMessages());
        } else if (count($result->getWarningMessages()) > 0) {
            $this->addWarningMessageArray($result->getWarningMessages());
        } else {
            $this->addSuccessMessage(LangMsg::get('SUCCESS_ORDER_SAVE_IN_BX', [
                '#ORDER_ID#' => $this->getOrderBxId()
            ]));
        }
        return $result;
    }

    public function canCreateOrderInMs()
    {
        return $this->canCreateOrderInMs;
    }

    public function isLoaded()
    {
        return $this->isLoaded;
    }

    public function setChangedSum()
    {
        if ($this->isLoaded()) {
            $this->isChangedSum = (double)$this->orderBx->getPrice() !== (double)($this->order->sum / 100);
        }
    }

    public function isChangedSum()
    {
        return $this->isChangedSum;
    }

    public function isFinalError()
    {
        return $this->isFinalError;
    }

    public function setFinalError($isError = true)
    {
        $this->isFinalError = $isError;
    }

    public function setOrderChangeStack($property = '', $value = null)
    {
        if (!empty($property)) {
            switch ($property) {
                case 'attributes':
                    if (!isset($this->orderChangesStack[$property])) {
                        $this->orderChangesStack[$property] = [];
                    }
                    $this->orderChangesStack[$property] = $value + $this->orderChangesStack[$property];
                break;
                default:
                    $this->orderChangesStack[$property] = $value;
            }
        }
    }

    public function addAttributeToOrderChangeStack(array $attribute)
    {
        if (!isset($this->orderChangesStack['attributes'])) {
            $this->orderChangesStack['attributes'] = [];
        }
        $this->orderChangesStack['attributes'][] = $attribute;
    }

    public function setLoadedOrder($isLoaded = false)
    {
        $this->isLoaded = $isLoaded;
    }

    public function clearLog()
    {
        $this->log = [];
    }

    public function addMainMessage($msg = '')
    {
        $this->log[] = LogMsg::addMain($msg);
    }

    public function addErrorMessage($msg = '', $code = '')
    {
        $this->hasErrors = true;
        $this->log[] = LogMsg::addError($msg, $code);
    }

    public function addErrorMessageArray($errorArrayMsg = [])
    {
        foreach ($errorArrayMsg as $code => $msg) {
            $this->addErrorMessage($msg, $code);
        }
    }

    public function addWarningMessage($msg = '')
    {
        $this->hasWarnings = true;
        $this->log[] = LogMsg::addWarning($msg);
    }

    public function addWarningMessageArray($warningArrayMsg = [])
    {
        foreach ($warningArrayMsg as $code => $msg) {
            $this->addWarningMessage($msg, $code);
        }
    }

    public function addInfoMessage($msg = '')
    {
        $this->log[] = LogMsg::addInfo($msg);
    }

    public function addSuccessMessage($msg = '')
    {
        $this->log[] = LogMsg::addSuccess($msg);
    }

    public function getExpandFields()
    {
        return $this->expandFields;
    }

    public function hasErrors()
    {
        return $this->hasErrors;
    }

    public function hasWarnings()
    {
        return $this->hasWarnings;
    }

    /**
     * @return array of \Rbs\Moysklad\Debug\Message
     */
    public function getLogList(): array
    {
        $result = [];

        foreach ($this->log as $log) {
            if ($log instanceof LogMsg) {
                $result[] = $log;
            }
        }

        return $result;
    }

    /** @deprecated */
    public function checkRecalcBasket()
    {
        if ($this->isLoaded()) {
            $orderSumMs = (float)($this->order->sum / 100);
            $orderSumBx = (float)$this->orderBx->getPrice();

            if ($orderSumMs !== $orderSumBx) {
                $this->setBasket();
                $this->saveOrderChanges();
            }
        }
    }

    /** @deprecated */
    public function createDeliveryService()
    {
        return \CRbsMoyskladHelper::createDeliveryService();
    }

    /**
     * @return array
     */
    public function getCustomEntityAttrs(): array
    {
        return $this->customEntityAttrs;
    }

    /**
     * @return array
     */
    public function getStrAttrs(): array
    {
        return $this->strAttrs;
    }

}
