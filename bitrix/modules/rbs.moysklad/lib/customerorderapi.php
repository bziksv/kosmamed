<?php
namespace Rbs\Moysklad;

use \Bitrix\Sale\Order;
use \Rbs\Moysklad\LangMsg;
use \Rbs\Moysklad\Debug\Message as LogMsg;

\Bitrix\Main\Loader::includeModule('sale');
\Bitrix\Main\Loader::includeModule('catalog');
\Bitrix\Main\Loader::includeModule('iblock');

class Customerorderapi
{
    public $orderBx= null;
    public $order = null;

    private $orderChangesStack = [];
    private $orderMetaData;
    
    private $log = [];
    private $hasErrors = false;
    
    private $isLoaded = false;

    private $chunckedBasket = [];

    private static $limitPositions = 1000;
    private static $cacheTime = 8640000;

    public function __construct($orderId = 0)
    {
        if (empty($orderId)) {
            $this->addErrorMessage(LangMsg::get('LOG_AGENT_ORDER_FIND_BX_FAIL'));
            return;
        }

        try {
            
            if ($order = Order::load($orderId)) {
                        
                $this->orderBx = $order;

                $this->orderMetaData = \CRbsMoyskladHelper::getMetadataWithAttrs('customerorder', self::$cacheTime);
                //find counterparty from ms by fields (default externalCode)

                if(Config::checkFeature('counter_default_hard_set')) {
                    $counterParty = Counterparty::getDefaultCounterParty($order->getPersonTypeId());
                } else {
                    $counterParty = Counterparty::createForOrder($order);
                    if (!$counterParty->isLoaded()) {
                        $this->mergeLog($counterParty->getLogList());
                        $this->addWarningMessage(LangMsg::get('USE_DEFAULT_COUNTERPARTY'));
                        $counterParty = Counterparty::getDefaultCounterParty($order->getPersonTypeId());
                    }
                }

                $this->mergeLog($counterParty->getLogList());

                if ($counterParty->isLoaded()) {

                    //standart fields
                    $this->setOrganization();
                    $this->setGroup();
                    $this->setEmployee();
                    $this->setShared();
                    $this->setProject();
                    $this->setSalesChannel();

                    //reqprops
                    $this->setRequiredProps();

                    $this->orderChangesStack['agent'] = $counterParty->getMeta();
                    $this->orderChangesStack['externalCode'] = (string)$orderId;

                    if (Config::checkFeature('customextfield')) {
                        $orderExtField = Config::getExtFieldId();
                        $this->orderChangesStack['externalCode'] = (string)$this->orderBx->getField($orderExtField);
                    }

                    //response
                    if (Config::checkFeature('responsesync')) {
                        $this->setResponsePerson();
                    }
                    if (Config::checkFeature('responsesyncprop')) {
                        $this->setResponsePropPerson();
                    }

                    //applicable
                    if(Config::checkFeature('order_unset_applicable')) {
                        $this->unsetApplicable();
                    }

                    //name
                    if (Config::checkFeature('ordernumberbx')) {
                        $this->setOrderName();
                    }
                    //props
                    if (Config::checkFeature('propssync')) {
                        $this->setProps();
                    }
                    if (Config::checkFeature('orderidsync')) {
                        $this->setOrderId();
                    }
                    if (Config::checkFeature('orderaccountsync')) {
                        $this->setOrderAccountNum();
                    }
                    //comments
                    if (Config::checkFeature('commentsync')) {
                        $this->setDescription();
                    }
                    if (Config::checkFeature('commentusersync')) {
                        $this->setUserDescription();
                    }
                    //delivery
                    if (Config::checkFeature('deliverynamesync')) {
                        $this->setDeliveryName();
                    }
                    if (Config::checkFeature('deliverytypesync')) {
                        $this->setDeliveryType();
                    }
                    if (Config::checkFeature('storesync')) {
                        $this->setStore();
                    }
                    //pay
                    if (Config::checkFeature('paymenttypesync')) {
                        $this->setPaymentType();
                    }
                    if (Config::checkFeature('paynamesync')) {
                        $this->setPaysystemName();
                    }
                    if (Config::checkFeature('payinfosync')) {
                        $this->setPaysystemInfo();
                    }
                    //status
                    if (Config::checkFeature('statussync') && Config::checkVectorFromBxToMs('states', 'FULL')) {
                        $this->setStatus();
                    }
                    if (Config::checkFeature('cancelsync')) {
                        $this->setCancel();
                    }
                    //basket
                    $this->setBasket();

                    if (Config::checkFeature('setcurrency')) {
                        $this->setCurrency();
                    }

                    if (Config::checkFeature('tracksync')) {
                        $this->setTrack();
                    }

                    $this->checkEmptyField();
                    $this->checkBasketSize();
                    $this->setMoment();
                    $this->setVatOptions();

                    $orderChangesStackForEvent = is_null($this->orderChangesStack) ? [] : $this->orderChangesStack;

                    Utils::send_bx_event(Config::getModuleId(true), 'OnBeforeOrderApiCreate', [
                        //old variant
                        0 => $orderId,
                        1 => $this->orderChangesStack,
                        //new variant
                        'orderId' => $orderId,
                        'orderBx' => $order,
                        'orderChangesStack' => $this->orderChangesStack
                    ], $orderChangesStackForEvent);

                    $this->orderChangesStack = $orderChangesStackForEvent;
                    unset($orderChangesStackForEvent);


                    //recalc attrubites for 1.2 api
                    if (isset($this->orderChangesStack['attributes']) && Utils::is_count($this->orderChangesStack['attributes'])) {
                        $this->orderChangesStack['attributes'] = \CRbsMoyskladHelper::convertAttributesToNewApi((array)$this->orderChangesStack['attributes']);
                    }

                    if($this->hasErrors()) {
                        return;
                    }

                    //cache for check payment for new order
                    Utils::is_exsists_cache(md5($order->getField('XML_ID')), 'order_api_create', Config::getCacheHookTime());

                    $orderMs = ApiNew::post('/entity/customerorder/', $this->orderChangesStack);
                    if (Utils::is_success($orderMs)) {

                        $this->order = $orderMs;
                        $this->isLoaded = true;

                        $this->checkBasketChuncked();
                        //$this->checkVat();
                        
                        Utils::send_bx_event(Config::getModuleId(true), 'OnAfterOrderApiCreate', [
                            //old variant
                            0 => $orderId,
                            1 => $orderMs,
                            //new variant
                            'orderId' => $orderId,
                            'orderMs' => $orderMs,
                        ]);

                    } else if(Utils::has_errors($orderMs)) {
                        $this->addErrorMessageArray($orderMs->errors);
                    }

                    return $orderMs;

                } 

            } else {
                $this->addErrorMessage(LangMsg::get('LOG_AGENT_ORDER_FIND_BX_FAIL'));
            }

        } catch (\Bitrix\Main\SystemException $e) {
            $this->addErrorMessage($e->getMessage());
        }
        
    }

    public static function createEmptyObjectClass($orderId = 0)
    {
        $obj = new self(0);
        $obj->clearLog();

        if ($order = Order::load($orderId)) {
            $obj->setActualOrderEntity($order);
        }

        return $obj;
    }

    public function getOrder()
    {
        return $this->order;
    }

    public function setOrganization()
    {
        $orgId = Config::getOrderOrganization();
        if (empty($orgId)) {
            $this->addWarningMessage(LangMsg::get('ORG_CANT_FIND'));
            $orgEntity = ApiNew::get("/entity/organization/", ['limit' => 1]);
            if (Utils::is_success($orgEntity)) {
                if(Utils::array_exists($orgEntity)) {
                    $orgEntity = current($orgEntity->rows);
                    $orgEntity->hasErrors = false;
                }
            } else if (Utils::has_errors($orgEntity)) {
                $this->addErrorMessageArray($orgEntity->errors);
            }
        } else {
            $orgEntity = ApiNew::get("/entity/organization/{$orgId}", [], self::$cacheTime);
        }

        if (Utils::is_success($orgEntity)) {
            $this->orderChangesStack['organization'] = (object)['meta' => $orgEntity->meta];
            $orgAcc = Config::getOrderAcHelperCount();
            if (!empty($orgAcc) && $orgAcc !== 'N') {
                $accEntity = ApiNew::get("/entity/organization/{$orgId}/accounts/{$orgAcc}", [], self::$cacheTime);
                if (Utils::is_success($accEntity)) {
                    $this->orderChangesStack['organizationAccount'] = (object)['meta' => $accEntity->meta];
                } else if (Utils::has_errors($accEntity)) {
                    $this->addErrorMessageArray($accEntity->errors);
                }
            }
        } else if(Utils::has_errors($orgEntity)) {
            $this->addErrorMessageArray($orgEntity->errors);
        }
    }

    public function setMoment()
    {
        $momentString = '';
        $dateInsert = $this->orderBx->getField('DATE_INSERT');
        if (($dateInsert instanceof \Bitrix\Main\Type\DateTime) && Config::checkFeature('order_time_set_bx')) {
            $momentString = (Config::getDateTime($dateInsert->format('Y-m-d H:i:s')))->format('Y-m-d H:i:s');
        } else {
            $momentString = (Config::getDateTime())->format('Y-m-d H:i:s');
        }
        $this->orderChangesStack['moment'] = $momentString;
    }

    public function setProject()
    {
        $projectId = Config::getOption('order_proj');
        if (empty($projectId) || $projectId == 'N') {
            return;
        }
        $this->orderChangesStack['project'] = Config::getMetaData('project', $projectId);
    }

    public function setGroup()
    {
        $groupId = Config::getOption('order_group');
        if (empty($groupId) || $groupId == 'N') {
            return;
        }
        $this->orderChangesStack['group'] = Config::getMetaData('group', $groupId);
    }

    public function setEmployee()
    {
        $ownerId = Config::getOption('order_employee');
        if (empty($ownerId) || $ownerId == 'N') {
            return;
        }
        $this->orderChangesStack['owner'] = Config::getMetaData('employee', $ownerId);
    }

    public function setSalesChannel()
    {
        $this->orderChangesStack['salesChannel'] = null;

        if (
            Config::checkFeature('sales_channel_enabled') && 
            Config::checkVectorFromBxToMs('saleschannel', 'FULL')
        ) {
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

        if(is_null($this->orderChangesStack['salesChannel'])) {
            $salesChannelId = Config::getOption('order_sale_channel');
            if (empty($salesChannelId) || $salesChannelId == 'N') {
                return;
            }
            $this->orderChangesStack['salesChannel'] = Config::getMetaData('saleschannel', $salesChannelId);
        }
    }

    public function setShared()
    {
        if (Config::getOption('is_order_shared') === 'Y') {
            $this->orderChangesStack['shared'] = true;
        }
    }

    public function unsetApplicable()
    {
        $this->orderChangesStack['applicable'] = false;
    }


    public function setRequiredProps()
    {
        if (Utils::array_exists($this->orderMetaData, 'attributes')) {
            foreach ($this->orderMetaData->attributes as $attr) {
                if ($attr->required) {
                    $defaultVal = Config::getOption('prop_req_' . $attr->id);
                    if (!empty($defaultVal)) {
                        switch ($attr->type) {
                            case 'time':
                                $this->orderChangesStack['attributes'][$attr->id] = [
                                    'id' => $attr->id,
                                    'value' => Utils::get_date_ms_string('now', true, Config::checkFeature('is_eu_msk_timezone'))
                                ];
                            break;
                            case 'double':
			                case 'long':
                                $this->orderChangesStack['attributes'][$attr->id] = [
                                    'id' => $attr->id,
                                    'value' => (float)$defaultVal
                                ];
                            break;
                            case 'string':
                            case 'text':
                            case 'link':
                                $this->orderChangesStack['attributes'][$attr->id] = [
                                    'id' => $attr->id,
                                    'value' => (string)$defaultVal
                                ];
                            break;
                            case 'employee':
                            case 'project':
                            case 'counterparty':
                            case 'product':
                            case 'store':
                            case 'contract':
                                $this->orderChangesStack['attributes'][$attr->id] = [
                                    'id' => $attr->id,
                                    'value' => (object)[
                                        'meta' => (object)[
                                            'href' => Config::getBaseHrefLinkNew($attr->type) . $defaultVal,
                                            'type' => $attr->type
                                        ]
                                    ]
                                ];
                            break;
                            case 'customentity':
                                $this->orderChangesStack['attributes'][$attr->id] = [
                                    'id' => $attr->id,
                                    'value' => (object)[
                                        'meta' => (object)[
                                            'href' => $attr->customEntityMeta->href . '/' . $defaultVal,
                                            'type' => $attr->type
                                        ]
                                    ]
                                ];
                            break;
                        }
                    }
                }
            }
        }
    }

    public function setResponsePerson()
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

        if (empty($user['ID']) || empty($user['EMAIL'])) {
            return;
        }

        $employee = ApiNew::get('/entity/employee', ['filter' => 'email=' . $user['EMAIL']]);
        if (Utils::is_success($employee)) {
            if(Utils::array_exists($employee)) {
                $employee = $employee->rows[0];
                $this->orderChangesStack['owner'] = (object)[
                    'meta' => $employee->meta
                ];
                if (Config::checkFeature('employeegroupsync')) {
                    $this->orderChangesStack['group'] = (object)[
                        'meta' => $employee->group->meta
                    ];
                }
            } else {
                $this->addWarningMessage(LangMsg::get('EMPTY_EMPLOYEE_LIST', [
                    '#EMAIL#' => $user['EMAIL']
                ]));
            }
        } else if (Utils::has_errors($employee)) {
            $this->addErrorMessageArray($employee->errors);
        }
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
        if (empty($user['ID']) || empty($user['EMAIL']) || empty($propId)) {
            return;
        }
        
        $employee = ApiNew::get('/entity/employee', ['filter' => 'email=' . $user['EMAIL']]);
        if (Utils::is_success($employee)) {
            if(Utils::array_exists($employee)) {
                $employee = $employee->rows[0];
                $this->orderChangesStack['attributes'][$propId] = [
                    'id' => $propId,
                    'value' => (object)['meta' => $employee->meta]
                ];
            } else {
                $this->addWarningMessage(LangMsg::get('EMPTY_EMPLOYEE_LIST', [
                    '#EMAIL#' => $user['EMAIL']
                ]));
            }        
        } else if(Utils::has_errors($employee)) {
            $this->addErrorMessageArray($employee->errors);
        }
    }

    public function setVatOptions()
    {
        $vatIncluded = Config::checkFeature('vatincluded');
        $vatEnabled = Config::checkFeature('vatenabled');
        $this->orderChangesStack['vatIncluded'] = $vatIncluded;
        $this->orderChangesStack['vatEnabled'] = $vatEnabled;
    }

    public function checkVat()
    {
        $vatChanges = [];
        if ((bool)$this->order->vatIncluded !== Config::checkFeature('vatincluded')) {
            $vatChanges['vatIncluded'] = Config::checkFeature('vatincluded');
        }
        if ((bool)$this->order->vatEnabled !== Config::checkFeature('vatenabled')) {
            $vatChanges['vatEnabled'] = Config::checkFeature('vatenabled');
        }

        if (Utils::is_count($vatChanges)) {
            ApiNew::put('/entity/customerorder/' . $this->order->id, $vatChanges);
        }
    }

    public function checkBasketSize()
    {
        if (Utils::count($this->orderChangesStack['positions']) > self::$limitPositions) {
            $this->chunckedBasket = array_chunk($this->orderChangesStack['positions'], self::$limitPositions);
            $this->orderChangesStack['positions'] = array_shift($this->chunckedBasket);
        }
    }

    public function checkBasketChuncked()
    {
        if (Utils::is_count($this->chunckedBasket)) {
            foreach ($this->chunckedBasket as $part) {
                ApiNew::post($this->order->positions->meta->href, $part);
            }
        }
    }

    public function setOrderName()
    {
        $this->orderChangesStack['name'] = \CRbsMoyskladHelper::getDocumentUniqName('customerorder', $this->orderBx->getField(Config::getOrderNumField()));
    }

    public function setCurrency()
    {
        $currencyBx = $this->orderBx->getCurrency();
        if (!empty($currencyBx)) {
            $currency = ApiNew::get('/entity/currency', ['filter' => 'isoCode=' . $currencyBx], self::$cacheTime);
            if (Utils::is_success($currency)) {
                if(Utils::array_exists($currency)) {
                    $this->orderChangesStack['rate'] = (object)[
                        'currency' => (object)[
                            'meta' => $currency->rows[0]->meta
                        ]
                    ];
                } else {
                    $this->addWarningMessage(LangMsg::get('EMPTY_CURRENCY_LIST', [
                        '#ISO#' => $currencyBx
                    ]));
                }
            } else if (Utils::has_errors($currency)) {
                $this->addErrorMessageArray($currency->errors);
            }
        }
    }

    public function checkEmptyField()
    {
        if (empty($this->orderChangesStack['store'])) {
            $storeIdDefault = Config::getStoreDefault();
            if (!empty($storeIdDefault) && $storeIdDefault !== 'N') {
                $this->orderChangesStack['store'] = Config::getMetaData('store', $storeIdDefault);
            }
        }
    }

    public function setProps()
    {
        \CRbsMoyskladHelper::setMsProps($this);
    }

    public function setDescription()
    {
        if (!empty(trim($this->orderBx->getField('COMMENTS')))) {
            $this->orderChangesStack['description'] = trim($this->orderBx->getField('COMMENTS'));
        }
    }

    public function setOrderId()
    {
        $propId = Config::getPropOrderId();
        if (!empty($propId) && $propId !== 'N') {
            $this->orderChangesStack['attributes'][$propId] = [
                'id' => $propId,
                'value' => $this->orderBx->getField('ID')
            ];
        }
    }

    public function setOrderAccountNum()
    {
        $propId = Config::getPropOrderAccountNum();
        if (!empty($propId) && $propId !== 'N') {
            $this->orderChangesStack['attributes'][$propId] = [
                'id' => $propId,
                'value' => $this->orderBx->getField('ACCOUNT_NUMBER')
            ];
        }
    }

    public function setUserDescription()
    {
        if (!empty(trim($this->orderBx->getField('USER_DESCRIPTION')))) {
            $userCommentProp = Config::getUserCommentProp();
            if (!empty($userCommentProp) && $userCommentProp !== 'N') {
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

    public function setDeliveryName()
    {
        $propDeliveryNameId = Config::getDeliveryNamePropertyId();
        if (!empty($propDeliveryNameId)) {
            $newDeliveryNameArr = [];
            if ($shipmentCollection = $this->orderBx->getShipmentCollection()) {
                foreach ($shipmentCollection as $shipment) {
                    if (!$shipment->isSystem()) {
                        $newDeliveryNameArr[] = "{$shipment->getField('ID')}: {$shipment->getField('DELIVERY_NAME')}";
                    }
                }
            }
           
            if (Utils::is_count($newDeliveryNameArr)) {
                $this->orderChangesStack['attributes'][$propDeliveryNameId] = [
                    'id' => $propDeliveryNameId,
                    'value' => implode("\n", $newDeliveryNameArr)
                ];
            }
        }
    }

    public function setPaymentType()
    {
        $paymentIds = Config::getPaymentIds();
        $paymentProp = Config::getPaymentProp();

        if(empty($paymentProp) || mb_strpos($paymentProp, ApiNew::getApiEndPointUrl()) === false) {
            $this->addWarningMessage(LangMsg::get('EMPTY_PAY_PROP_ID'));
            return;
        }

        $entityPayment = ApiNew::get($paymentProp, [], self::$cacheTime);
        if (Utils::has_errors($entityPayment)) {
            $this->addErrorMessageArray($entityPayment->errors);
            return;
        }

        $entityMetaPayment = ApiNew::get($entityPayment->entityMeta->href, [], self::$cacheTime);
        if (Utils::has_errors($entityMetaPayment)) {
            $this->addErrorMessageArray($entityMetaPayment->errors);
            return;
        }
        if (!Utils::array_exists($entityMetaPayment)) {
            $this->addWarningMessage(LangMsg::get('EMPTY_CUSTOMENTITY_LIST'));
            return;
        }

        $attrs = $this->orderMetaData->attributes ? : [];
        $attrId = false;

        if (Utils::is_count($attrs) && !empty($paymentProp) && Utils::is_count($paymentIds)) {

            foreach ($attrs as $attr) {
                if ($attr->type === 'customentity') {
                    if ($attr->customEntityMeta->href === $paymentProp) {
                        $attrId = $attr->id;
                    }
                }
            }

            if ($attrId) {
                if ($paymentCollectionSync = $this->orderBx->getPaymentCollection()) {
                    foreach ($paymentCollectionSync as $payment) {
                        $bxPaymentId = $payment->getField('PAY_SYSTEM_ID');
                        if (!isset($paymentIds[$bxPaymentId])) {
                            break;
                        }
                        $selectBxPaymentType = $paymentIds[$bxPaymentId];
                        if ($selectBxPaymentType) {
                            foreach ($entityMetaPayment->rows as $row) {
                                if ($row->id === $selectBxPaymentType) {
                                    $this->orderChangesStack['attributes'][$attrId] = [
                                        'id' => $attrId,
                                        'value' => $row
                                    ];
                                    break;
                                }
                            }
                        }
                        break;
                    }
                }
            }

        }
    }

    public function setDeliveryType()
    {
        $deliveryIds = Config::getDeliveryIds();
        $deliveryProp = Config::getDeliveryProp();

        if (empty($deliveryProp) || mb_strpos($deliveryProp, ApiNew::getApiEndPointUrl()) === false) {
            $this->addWarningMessage(LangMsg::get('EMPTY_DELIVERY_PROP_ID'));
            return;
        }

        $entityDelivery = ApiNew::get($deliveryProp, [], self::$cacheTime);
        if (Utils::has_errors($entityDelivery)) {
            $this->addErrorMessageArray($entityDelivery->errors);
            return;
        }

        $entityMetaDelivery = ApiNew::get($entityDelivery->entityMeta->href, [], self::$cacheTime);

        if (Utils::has_errors($entityMetaDelivery)) {
            $this->addErrorMessageArray($entityMetaDelivery->errors);
            return;
        }

        if (!Utils::array_exists($entityMetaDelivery)) {
            $this->addWarningMessage(LangMsg::get('EMPTY_CUSTOMENTITY_LIST'));
            return;
        }

        $attrs = $this->orderMetaData->attributes ? : [];
        $attrId = false;

        if (Utils::is_count($attrs) && !empty($deliveryProp) && Utils::is_count($deliveryIds)) {

            foreach ($attrs as $attr) {
                if ($attr->type === 'customentity') {
                    if ($attr->customEntityMeta->href === $deliveryProp) {
                        $attrId = $attr->id;
                    }
                }
            }

            if ($attrId) {
                if ($shipmentCollectionSync = $this->orderBx->getShipmentCollection()) {
                    foreach ($shipmentCollectionSync as $shipment) {
                        if (!$shipment->isSystem()) {
                            $bxDeliveryId = $shipment->getField('DELIVERY_ID');
                            if (!isset($deliveryIds[$bxDeliveryId])) {
                                break;
                            }
                            $selectBxDeliveryType = $deliveryIds[$bxDeliveryId];
                            if ($selectBxDeliveryType) {
                                foreach ($entityMetaDelivery->rows as $row) {
                                    if ($row->id === $selectBxDeliveryType) {
                                        $this->orderChangesStack['attributes'][$attrId] = [
                                            'id' => $attrId,
                                            'value' => $row
                                        ];
                                        break;
                                    }
                                }
                            }
                            break;
                        }
                    }
                }
            }

        }
    }

    public function setStore()
    {
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
                                'ttl' => self::$cacheTime
                            ]
                        ])->fetch();

                        if (!empty($store['XML_ID'])) {
                            $storeMs = ApiNew::get('/entity/store', ['filter' => 'externalCode=' . $store['XML_ID']], self::$cacheTime);
                            if (Utils::is_success($storeMs)) {
                                if(Utils::array_exists($storeMs)) {
                                    $storeMsObj = array_pop($storeMs->rows);
                                    if ((string)$storeMsObj->meta->href !== (string)$this->order->store->meta->href) {
                                        $this->orderChangesStack['store'] = (object)['meta' => ['href' => $storeMsObj->meta->href, 'type' => 'store']];
                                    }
                                }                                
                            } else if (Utils::has_errors($storeMs)) {
                                $this->addErrorMessageArray($storeMs->errors);
                            }
                        }
                    }
                    break;
                }
            }
        }
    }

    public function setPaysystemName()
    {
        $propPaysystemNameId = Config::getPaysystemNamePropertyId();
        if (!empty($propPaysystemNameId)) {
            $newPaysystemNameArr = [];
            if ($paymentCollection = $this->orderBx->getPaymentCollection()) {
                foreach ($paymentCollection as $payment) {
                    $newPaysystemNameArr[] = "{$payment->getField('ID')}: {$payment->getField('PAY_SYSTEM_NAME')} / {$payment->getField('SUM')} / {$payment->getField('PAID')}";
                }
            }
            
            if (Utils::is_count($newPaysystemNameArr)) {
                $this->orderChangesStack['attributes'][$propPaysystemNameId] = [
                    'id' => $propPaysystemNameId,
                    'value' => implode("\n", $newPaysystemNameArr)
                ];
            }
        }
    }

    public function setPaysystemInfo()
    {
        $propPaysystemInfoId = Config::getPaysystemInfoPropertyId();
            
        if (!empty($propPaysystemInfoId)) {
            $newPaysystemInfo = LangMsg::get('PAY_INFO', [
                '#SUM#' => round($this->orderBx->getPrice(), 2),
                '#PAID#' => $this->orderBx->getSumPaid(),
                '#NEED_PAID#' => round($this->orderBx->getPrice() - $this->orderBx->getSumPaid(), 2),
            ]);
            
            $this->orderChangesStack['attributes'][$propPaysystemInfoId] = [
                'id' => $propPaysystemInfoId,
                'value' => $newPaysystemInfo
            ];
        }
    }

    public function setStatus()
    {
        $this->changeStatus(Config::getExternalStatusId($this->orderBx->getField('STATUS_ID')));
    }
    
        public function setCancel()
        {
            if ($this->orderBx->isCanceled()) {
                $this->changeStatus(Config::getExternalCancelStatusId());
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
        $exStatusId = Config::getBaseHrefLinkNew('state') . $exStatusId;
        $this->orderChangesStack['state'] = (object)['meta' => ['href' => $exStatusId, 'type' => 'state']];
    }

    public function setBasket()
    {
        $rbsMsOrder = new \CRbsMoyskladBasketOrder($this, true);
        $rbsMsOrder->setBasketPositions();
    }

    public function setTrack()
    {
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
        
        $resultArrStr = [];
        foreach ($resultTracks as $shId => $trackNum) {
            $resultArrStr[] = "{$shId}: {$trackNum}";
        }

        if (Utils::is_count($resultArrStr)) {
            $this->orderChangesStack['attributes'][$propTrackId] = [
                'id' => $propTrackId,
                'value' => implode("\n", $resultArrStr)
            ];
        }
    }

    public function isDisableOrderSync()
    {
        if ($this->isLoaded()) {
            if ($orderIdStart = Config::getOrderIdStart()) {
                if ($orderIdStart > 0 && $this->getOrderBxId() <  $orderIdStart) {
                    return true;
                }
            }
        }
        return false;
    }

    public function saveResult(): \Bitrix\Main\Result
    {
        $result = $this->orderBx->save();
        if (!$result->isSuccess()) {
            $this->addErrorMessageArray($result->getErrorMessages());
        } else if (count($result->getWarningMessages()) > 0) {
            $this->addWarningMessageArray($result->getWarningMessages());
        } else {
            $this->addSuccessMessage(LangMsg::get('SUCCESS_ORDER_SAVE'));
        }
        return $result;
    }

    public function getOrderEntity()
    {
        return $this->orderBx;
    }

    public function setActualOrderEntity(Order $order)
    {
        $this->orderBx = $order;
    }

    public function isLoaded()
    {
        return $this->isLoaded;
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

    public function getOrderBxId(): int
    {
        if ($this->orderBx instanceof \Bitrix\Sale\Order) {
            return $this->orderBx->getId();
        }
        return -1;
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
        if (!$this->hasErrors) {
            $this->hasErrors = true;
        }
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

    public function mergeLog($log = []) 
    {
        if(Utils::is_count($log)) {
            foreach($log as $logMsg){
                if($logMsg instanceof LogMsg) {
                    $this->log[] = $logMsg;
                }
            }
        }
    }

    public function hasErrors()
    {
        return $this->hasErrors;
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
    public function createDeliveryService()
    {
        return \CRbsMoyskladHelper::createDeliveryService();
    }
}
