<?php

use \Rbs\Moysklad\Config;
use \Rbs\Moysklad\Customerorder;
use \Rbs\Moysklad\Counterparty;

use \Rbs\Moysklad\Agent;
use \Rbs\Moysklad\LangMsg;
use \Rbs\Moysklad\Services\OrderFilter;
use \Rbs\Moysklad\ApiNew;
use \Rbs\Moysklad\Utils;

use \Rbs\Moysklad\Helper;
use \Rbs\Moysklad\Debug\Loger;

use \Rbs\Moysklad\Controller\ExceptionRuler;

use \Bitrix\Main\Event;

if (!class_exists('CRbsMoysklad')) {
    class CRbsMoysklad
    {
        public static function onSalePaymentEntitySaved(Event $event): void
        {
            Utils::send_bx_event(Config::getModuleId(true), 'OnBeforeEvent', [
                'eventName' => 'onSalePaymentEntitySaved',
                'eventFrom' => $event
            ]);

            if (Config::isIgnorePushToMs() || !Config::checkFeature('modulesync')) {
                return;
            }

            $payment = $event->getParameter("ENTITY");
            $paymentCollection = $payment->getCollection();
            $orderId = $payment->getField('ORDER_ID');
            $paymentId = $payment->getId();
            $order = $paymentCollection->getOrder();

            if ($agentId = Agent::get("createOrderApi({$orderId});")) {
                if ((int)$agentId > 0) {
                    return;
                }
            }

            if (OrderFilter::isFiltred($orderId, $order)) {
                return;
            }

            if (Config::isDisableOrderIdSync($orderId)) {
                return;
            }
        
            if ($order->isNew()) {
                return;
            }
       
            $arLookForChangeFields = ['PAY_SYSTEM_ID', 'PAID', 'SUM', 'PAY_SYSTEM_NAME', 'PAY_VOUCHER_DATE', 'DATE_PAID'/* , 'PAY_VOUCHER_NUM', 'COMMENTS' */];
            $isChanged = Helper::isChangedValues($payment->getFields()->getChangedValues(), $arLookForChangeFields);
            if (!$isChanged) {
                return;
            }
        
            $customerOrder = new Customerorder($payment->getField('ORDER_ID'));
            if ($customerOrder->isLoaded()) {

                try {

                    $customerOrder->setActualOrderEntity($order);

                    if (Config::checkFeature('paysync')) {
                        $customerOrder->checkPayment($payment);
                    }

                    self::updateOrder($customerOrder); 

                } catch (\Bitrix\Main\SystemException $e) {
                    $customerOrder->addErrorMessage($e->getMessage());
                    Agent::set("onSalePaymentEntitySavedDelay({$orderId}, {$paymentId});");
                }

                \CRbsMoyskladHelper::exportLog($customerOrder, 'LOG_UPDATE_PAYMENT');

            } else {
                Agent::set("onSalePaymentEntitySavedDelay({$orderId}, {$paymentId});");
            }
        }

        public static function onBeforeSalePaymentDeleted(Event $event): void
        {
            Utils::send_bx_event(Config::getModuleId(true), 'OnBeforeEvent', [
                'eventName' => 'onBeforeSalePaymentDeleted',
                'eventFrom' => $event
            ]);

            if (Config::isIgnorePushToMs() || !Config::checkFeature('modulesync') || Config::getPaySyncType() !== 'full') {
                return;
            }

            $params = $event->getParameter("VALUES");
            if ((int)$params['ID'] > intval(0)) {

                $allPayments = \Bitrix\Sale\Internals\PaymentTable::getList(['filter' => ['ID' => $params['ID']]])->fetchAll();
                $payment = array_pop($allPayments);

                if ($payment['ORDER_ID'] > intval(0)) {

                    if (OrderFilter::isFiltred($payment['ORDER_ID'])) {
                        return;
                    }

                    if (Config::isDisableOrderIdSync($payment['ORDER_ID'])) {
                        return;
                    }
                    
                    $customerOrder = new Customerorder($payment['ORDER_ID']);
                    if ($customerOrder->isLoaded()) {
                        try {
                            if (Config::checkFeature('paysync')) {
                                $customerOrder->deletePaymentByExternalCode((string)$payment[Config::getSearchFieldId('payment')]);
                            }
                            self::updateOrder($customerOrder);
                           
                        } catch (\Bitrix\Main\SystemException $e) {
                            $customerOrder->addErrorMessage($e->getMessage());
                        }
                        \CRbsMoyskladHelper::exportLog($customerOrder, 'LOG_DELETE_PAYMENT');
                    }
                }
            }
        }

        public static function onSaleShipmentEntitySaved(Event $event): void
        {
            Utils::send_bx_event(Config::getModuleId(true), 'OnBeforeEvent', [
                'eventName' => 'onSaleShipmentEntitySaved',
                'eventFrom' => $event
            ]);

            if (Config::isIgnorePushToMs() || !Config::checkFeature('modulesync')) {
                return;
            }

            $shipment = $event->getParameter("ENTITY");

            if($shipment->isSystem()) {
                return;
            }

            $shipmentCollection = $shipment->getCollection();
            $order = $shipmentCollection->getOrder();
            $orderId = $order->getField('ID');
        
            if ($order->isNew()) {
                return;
            }

            if ($agentId = Agent::get("createOrderApi({$orderId});")) {
                if ((int)$agentId > 0) {
                    return;
                }
            }

            if (OrderFilter::isFiltred($orderId, $order)) {
                return;
            }

            if (Config::isDisableOrderIdSync($orderId)) {
                return;
            }

            $needPushDemandsToMs = Config::getOption('demand_exchange_type', 'N') === 'full' && Config::checkVectorFromBxToMs('demand');
        
            $arLookForChangeFields = ['TRACKING_NUMBER', 'DELIVERY_ID', 'DELIVERY_NAME', 'DEDUCTED'];
            $isChangedForOrder = ((int)$shipment->getStoreId() > intval(0)) || Helper::isChangedValues($shipment->getFields()->getChangedValues(), $arLookForChangeFields);
            if (!$isChangedForOrder && !$needPushDemandsToMs) {
                return;
            }

            $customerOrder = new Customerorder($orderId);
            if ($customerOrder->isLoaded()) {

                try {

                    $customerOrder->setActualOrderEntity($order);

                    if($needPushDemandsToMs) {
                        
                        $demand = new \Rbs\Moysklad\Entity\Demand($customerOrder);
                        $demand->exportShipmentToDemand($shipment);

                    }

                    if($isChangedForOrder) {
                        self::updateOrder($customerOrder);
                    }

                } catch (\Bitrix\Main\SystemException $e) {
                    $customerOrder->addErrorMessage($e->getMessage());
                    Agent::set("onSaleOrderEntitySavedDelay({$orderId});");
                }

                \CRbsMoyskladHelper::exportLog($customerOrder, 'LOG_UPDATE_SHIPMENT');

            } else {
                Agent::set("onSaleOrderEntitySavedDelay({$orderId});");
            }
        }

        public static function onBeforeSaleShipmentDeleted(Event $event): void
        {
            Utils::send_bx_event(Config::getModuleId(true), 'OnBeforeEvent', [
                'eventName' => 'onBeforeSaleShipmentDeleted',
                'eventFrom' => $event
            ]);

            if(Config::isIgnorePushToMs() || !Config::checkFeature('modulesync')) {
                return;
            }

            $demandType = Config::getOption('demand_exchange_type', 'N');
            if ($demandType !== 'full' || !Config::checkVectorFromBxToMs('demand') || Config::getOption('demand_delete_bx_type', 'N') === 'N') {
                return;
            }
            
            $params = $event->getParameter("VALUES");
            if ((int)$params['ID'] > intval(0)) {

                $shipment = array_pop(\Bitrix\Sale\Internals\ShipmentTable::getList(['filter' => ['ID' => $params['ID']]])->fetchAll());

                if ($shipment['ORDER_ID'] > intval(0)) {

                    if (empty($shipment['XML_ID'])) {
                        return;
                    }

                    if (OrderFilter::isFiltred($shipment['ORDER_ID'])) {
                        return;
                    }

                    if (Config::isDisableOrderIdSync($shipment['ORDER_ID'])) {
                        return;
                    }

                    $customerOrder = new Customerorder($shipment['ORDER_ID']);
                    if ($customerOrder->isLoaded()) {
                        try {
                            $demand = new \Rbs\Moysklad\Entity\Demand($customerOrder);
                            $demand->deleteDemandFromShipmentExternalCode($shipment['XML_ID']);
                        } catch (\Bitrix\Main\SystemException $e) {
                            $customerOrder->addErrorMessage($e->getMessage());
                        }

                        \CRbsMoyskladHelper::exportLog($customerOrder, 'LOG_DELETE_SHIPMENT');
                    }
                }
            }
        }

        public static function onSaleOrderEntitySaved(Event $event): void
        {
            Utils::send_bx_event(Config::getModuleId(true), 'OnBeforeEvent', [
                'eventName' => 'onSaleOrderEntitySaved',
                'eventFrom' => $event
            ]);

            if (Config::isIgnorePushToMs() || !Config::checkFeature('modulesync')) {
                return;
            }
        
            $order = $event->getParameter("ENTITY");
            $orderId = $order->getField('ID');

            if ($agentId = Agent::get("createOrderApi({$orderId});")) {
                if ((int)$agentId > 0) {
                    return;
                }
            }

            if (OrderFilter::isFiltred($orderId, $order)) {
                return;
            }

            if (Config::isDisableOrderIdSync($orderId)) {
                return;
            }
          
            if ($order->isNew()) {
                if (!OrderFilter::isFiltredByStatus($order->getField('STATUS_ID'))) {
                    Agent::set("createOrderApi({$orderId});");
                }
                return;
            }
        
            $arLookForChangeFields = ['PAY_SYSTEM_ID', 'RESPONSIBLE_ID', 'DELIVERY_ID', 'USER_ID', 'STATUS_ID', 'PRICE_DELIVERY', 'PRICE', 'SUM_PAID', 'USER_DESCRIPTION', 'COMMENTS', 'CANCELED'];
            $isChanged = Helper::isChangedValues($order->getFields()->getChangedValues(), $arLookForChangeFields);
       
            $propCollection = $order->getPropertyCollection();
            foreach ($propCollection as $prop) {
                if (Utils::is_count($prop->getFields()->getChangedValues())) {
                    $isChanged = true;
                    break;
                }
            }
        
            if (!$isChanged) {
                return;
            }

            $customerOrder = new Customerorder($order->getField('ID'));
            if ($customerOrder->isLoaded()) {
                try {
                    $customerOrder->setActualOrderEntity($order);
                    self::updateOrder($customerOrder);
                    self::updateAgent($customerOrder);
                } catch (\Bitrix\Main\SystemException $e) {
                    Agent::set("onSaleOrderEntitySavedDelay({$orderId});");
                    $customerOrder->addErrorMessage($e->getMessage());
                }               
                \CRbsMoyskladHelper::exportLog($customerOrder, 'LOG_UPDATE_ORDER');
            } else {
                Agent::set("onSaleOrderEntitySavedDelay({$orderId});");
            }
        }

        public static function onSaleStatusOrderChange(Event $event): void
        {
            Utils::send_bx_event(Config::getModuleId(true), 'OnBeforeEvent', [
                'eventName' => 'onSaleStatusOrderChange',
                'eventFrom' => $event
            ]);

            if (!Config::checkFeature('statussync') || Config::isIgnorePushToMs() || !Config::checkFeature('modulesync')) {
                return;
            }

            $order = $event->getParameter("ENTITY");
            if ($order->isNew() || $order->isCanceled()) {
                return;
            }

            $orderId = $order->getField('ID');

            if (OrderFilter::isFiltred($orderId, $order)) {
                return;
            }

            if (Config::isDisableOrderIdSync($orderId)) {
                return;
            }

            if(!Config::checkVectorFromBxToMs('states', 'FULL') && Config::getOption('status_export_type', 'N') === 'N') {
                return;
            }

            $customerOrder = new Customerorder($orderId);
            if ($customerOrder->isLoaded()) {

                if(Config::checkVectorFromBxToMs('states', 'FULL')) {
                    try {
                        $customerOrder->setStatus();
                        $customerOrder->saveOrderChanges();
                    } catch (\Bitrix\Main\SystemException $e) {
                        $customerOrder->addErrorMessage($e->getMessage());
                    }                    
                    \CRbsMoyskladHelper::exportLog($customerOrder, 'LOG_UPDATE_ORDER_STATUS');
                }
                
            } else {

                if (!OrderFilter::isFiltredByStatus($order->getField('STATUS_ID'))) {
                    Agent::set("createOrderApi({$orderId});");
                } 

            }
        }

        public static function onSaleOrderCanceled(Event $event): void
        {
            Utils::send_bx_event(Config::getModuleId(true), 'OnBeforeEvent', [
                'eventName' => 'onSaleOrderCanceled',
                'eventFrom' => $event
            ]);

            if (!Config::checkFeature('cancelsync') || Config::isIgnorePushToMs() || !Config::checkFeature('modulesync')) {
                return;
            }

            $order = $event->getParameter("ENTITY");
            $orderId = $order->getField('ID');

            if (OrderFilter::isFiltred($orderId, $order)) {
                return;
            }

            if (Config::isDisableOrderIdSync($orderId)) {
                return;
            }

            $customerOrder = new Customerorder($orderId);

            if ($customerOrder->isLoaded()) {

                try {
                    $customerOrder->setCancel();
                    $customerOrder->saveOrderChanges();
                } catch (\Bitrix\Main\SystemException $e) {
                    $customerOrder->addErrorMessage($e->getMessage());
                }  

                if($order->isCanceled()) {
                    \CRbsMoyskladHelper::exportLog($customerOrder, 'LOG_UPDATE_ORDER_CANCEL');
                } else {
                    \CRbsMoyskladHelper::exportLog($customerOrder, 'LOG_UPDATE_ORDER_CANCEL_CANCEL');
                }
                
            }
        }

        public static function updateOrder(Customerorder &$customerOrder): void
        {
            Utils::send_bx_event(Config::getModuleId(true), 'OnBeforeUpdateOrder', ['customerOrder' => $customerOrder]);

            if (Config::checkFeature('responsesync')) {
                $customerOrder->setResponsePerson();
            }
            if (Config::checkFeature('responsesyncprop')) {
                $customerOrder->setResponsePropPerson();
            }
            if (Config::checkFeature('propssync')) {
                $customerOrder->setProps();
            }
            if (Config::checkFeature('commentsync')) {
                $customerOrder->setDescription();
            }
            if (Config::checkFeature('commentusersync')) {
                $customerOrder->setUserDescription();
            }
            if (Config::checkFeature('deliverynamesync')) {
                $customerOrder->setDeliveryName();
            }
            if (Config::checkFeature('deliverytypesync')) {
                $customerOrder->setDeliveryType();
            }
            if (Config::checkFeature('tracksync')) {
                $customerOrder->setTrack();
            }
            if (Config::checkFeature('storesync')) {
                $customerOrder->setStore();
            }
            if (Config::checkFeature('paymenttypesync')) {
                $customerOrder->setPaymentType();
            }
            if (Config::checkFeature('paynamesync')) {
                $customerOrder->setPaysystemName();
            }
            if (Config::checkFeature('payinfosync')) {
                $customerOrder->setPaysystemInfo();
            }
            if (Config::checkFeature('statussync') && Config::checkVectorFromBxToMs('states', 'FULL')) {
                $customerOrder->setStatus();
            }
            if (Config::checkFeature('sales_channel_enabled') && Config::checkVectorFromBxToMs('saleschannel', 'FULL')) {
                $customerOrder->setSalesChannel();
            }
            if (Config::checkFeature('cancelsync')) {
                $customerOrder->setCancel();
            }
            if (Config::checkFeature('basketsyncbx')) {
                $customerOrder->setBasket();
            } else if (Config::checkFeature('deliverypricesync') && Config::checkVectorFromBxToMs('delivery_price', 'FULL')) {
                $customerOrder->setDeliveryPrice();
            }
            if (Config::checkFeature('setcurrency')) {
                $customerOrder->setCurrency();
            }

            //check other docs
            if (Config::getOption('demand_exchange_type', 'N') === 'default') {
                $customerOrder->checkDemand();
            }

            $customerOrder->saveOrderChanges();
        }

        public static function updateAgent(Customerorder $customerOrder): void
        {
            Utils::send_bx_event(Config::getModuleId(true), 'OnBeforeUpdateAgent', ['customerOrder' => $customerOrder]);

            if (
                !Config::checkFeature('counter_default_hard_set') && 
                Config::checkFeature('countersaveforce') && 
                $customerOrder->isLoaded()
            ) {
                $counterParty = Counterparty::createFromObjectAndOrder($customerOrder->getAgent(), $customerOrder->getOrderEntity());
                if ($counterParty->isLoaded()) {
                    $counterParty->checkChanges();
                }
            }
        }
    }
}

if (!class_exists('CRbsMoyskladBasketOrder')) {
    class CRbsMoyskladBasketOrder
    {
        public static $customerorder = null;
        public static $isCreateOrderApi = null;

        public static $paramList = [];
        public static $positions = [];

        public function __construct(&$customerorder, $isNewOrder = false)
        {
            $this->{'customerorder'} = &$customerorder;
            $this->{'isCreateOrderApi'} = $isNewOrder;

            $this->{'paramList'} = [
                'reserved' => $this->isNewOrder() ? Config::checkFeature('orderreserved') : Config::checkFeature('basketreservededit'),
                'cacheTime' => (int)Config::cacheTime('basket_items_ms'),
                'isModifSync' => Config::checkFeature('basketmodifsync'),
                'canDoublesPositions' => Config::checkFeature('basketdoublesync'),
                'isBundleSync' => Config::checkFeature('basketbundlesync'),
                'bundleRecalc' => Config::checkFeature('basketbundlerecalc'),
                'vatRate' => Config::checkFeature('basketvatrate'),
                'vatDelivery' => (int)Config::getOption('dprice_vat'),
                'isCreateItemsMs' => Config::checkFeature('basketcreateitem'),
                'extCodeSource' => Config::checkFeature('basketextcodessource'),
                'basketarchived' =>  Config::checkFeature('basketarchived'),
                'deliveryPriceSync' => Config::checkFeature('deliverypricesync') && Config::checkVectorFromBxToMs('delivery_price', 'FULL'),
                'cancelreserved' => false
            ];

            $this->{'paramList'}['cancelreserved'] =  \CRbsMoyskladHelper::isCancelReservedBasket($this->getBitrixOrder());
            if (!$this->isNewOrder() && !Config::checkFeature('basketreservededit')) {
                $msOrder = $this->{'customerorder'}->getOrder();
                $this->{'paramList'}['reserved'] = (float)$msOrder->{'reservedSum'} > intval(0);
            }

            if ($this->getParam('isCreateItemsMs')) {
                $newItemFolder = Config::getNewItemsFolder();
                if ($newItemFolder !== 'N') {
                    $newItemFolder = Config::getMetaData('productfolder', $newItemFolder);
                } else {
                    $newItemFolder = false;
                }
                $newItemPrice = Config::getOption('basket_create_price');

                $this->{'paramList'}['newItemFolder'] = $newItemFolder;
                $this->{'paramList'}['newItemPrice'] = $newItemPrice;

                $this->{'paramList'}['newItemIdAttr'] = Config::getOption('basket_create_id_attr', 'N');
                $this->{'paramList'}['newItemCodeField'] = Config::getOption('basket_create_code', '');
                $this->{'paramList'}['newItemArticleField'] = Config::getOption('basket_create_article', '');
                $this->{'paramList'}['newItemPrevPic'] = Config::checkFeature('basket_create_prev_pic');
                $this->{'paramList'}['newItemDetailPic'] = Config::checkFeature('basket_create_detail_pic');
                $this->{'paramList'}['newItemDescription'] = Config::getOption('basket_create_description', 'N');
            }
        }

        public function getParam($paramName = '')
        {
            return $this->{'paramList'}[$paramName];
        }

        public function isNewOrder()
        {
            return $this->{'isCreateOrderApi'};
        }

        public function getBitrixOrder()
        {
            return $this->{'customerorder'}->{'orderBx'};
        }

        public function getMoySkladOrder()
        {
            return $this->{'customerorder'}->{'order'};
        }

        public function setPositionsToOrderChangeStack()
        {
            if (Utils::is_count($this->{'positions'})) {
                $this->{'customerorder'}->setOrderChangeStack('positions', array_values($this->{'positions'}));
            }
        }

        public function getBasketItemXmlId($basketItem): string
        {
            return \CRbsMoyskladHelper::getXmlIdFromBasketItem($basketItem);
        }

        public function addPosition($basketItem, $discount = 0)
        {
            $reserved = $this->getParam('reserved');
            $cancelreserved = $this->getParam('cancelreserved');
            $vatRate = $this->getParam('vatRate');

            $currXmlId = $this->getBasketItemXmlId($basketItem);
            $productObj = $this->getProductMs($currXmlId);
            if (!$productObj && $this->getParam('isCreateItemsMs')) {
                $productObj = $this->createProductMs($basketItem);
            }

            if ($productObj) {

                if ($discount <= intval(0)) {
                    if (
                            $basketItem->getField('PRICE') <> $basketItem->getField('BASE_PRICE') &&
                            $basketItem->getField('BASE_PRICE') > intval(0)
                        ) {
                        $discount = (float)(1 - ($basketItem->getField('PRICE') / $basketItem->getField('BASE_PRICE'))) * 100;
                    }
                }

                $reserveQty = $reserved ? (float)$basketItem->getField('QUANTITY') : (float)$basketItem->getField('RESERVE_QUANTITY');
                if(($this->isNewOrder() && !$reserved) || $cancelreserved) {
                    $reserveQty = intval(0);
                }

                $positionParams = [
                    "quantity" => (float)$basketItem->getField('QUANTITY'),
                    "reserve" => (float)$reserveQty,
                    "price" => (float)$basketItem->getField('BASE_PRICE') * 100,
                    "discount" => (float)$discount,
                    'assortment' => (object)[
                        'meta' => $productObj->{'meta'}
                    ],
                    'vat' => $vatRate ? round($basketItem->getField('VAT_RATE') * 100, 0) : 0
                ];

                if ($vatRate && $basketItem->getField('VAT_INCLUDED') === 'Y') {
                    $positionParams['vatEnabled'] = true;
                }

                Utils::send_bx_event(Config::getModuleId(true), 'OnBeforeAddPosition', [
                    'orderId' => $this->getBitrixOrder()->getField('ID'),
                    'order' => $this->getBitrixOrder(),
                    'basketItem' => $basketItem,
                    'currXmlId' => $currXmlId,
                    'positionParams' => $positionParams,
                    'productObj' => $productObj
                ], $positionParams);
                    
                if($this->getParam('canDoublesPositions')){
                    $this->{'positions'}[] = $positionParams;
                } else {
                    if(isset($this->{'positions'}[$productObj->{'externalCode'}])) {
                        $this->{'positions'}[$productObj->{'externalCode'}]['quantity'] += $positionParams['quantity'];
                    } else {
                        $this->{'positions'}[$productObj->{'externalCode'}] = $positionParams;
                    }
                }                
            }
        }

        
        public function setBasketPositions()
        {
            if ($basketItems = $this->getBitrixOrder()->getBasket()) {
                foreach ($basketItems as $basketItem) {
                    if ($basketItem->isBundleParent() && $this->getParam('isBundleSync')) {
                        $this->setBundleBasketPositions($basketItem);
                        continue;
                    }
                    $this->addPosition($basketItem);
                }
            }

            if ($this->getParam('deliveryPriceSync')) {
                $this->setBasketDeliveryPrice();
            }

            $positions = is_null($this->{'positions'}) ? [] : $this->{'positions'};
           
            Utils::send_bx_event(Config::getModuleId(true), 'OnAfterSetBasketPositions', [
                'orderId' => $this->getBitrixOrder()->getField('ID'),
                'order' => $this->getBitrixOrder(),
                'positions' => $this->{'positions'}
            ], $positions);

            $this->{'positions'} = $positions;

            $this->setPositionsToOrderChangeStack();
        }

        public function setBasketDeliveryPrice()
        {
            $cacheTime = $this->getParam('cacheTime');
            $reserved = $this->getParam('reserved');
            $vatRate = $this->getParam('vatRate');
            $vatDelivery = $this->getParam('vatDelivery');

            $orderDeliveryPrice = $this->getBitrixOrder()->getDeliveryPrice() * 100;
                
            if ((float)$orderDeliveryPrice > intval(0)) {

                $serviceResonse = ApiNew::get('/entity/service', ['filter' => 'externalCode=' . Config::getDeliveryExternalCode()], $cacheTime);
                   
                if (Utils::is_success($serviceResonse)) {

                    if (!Utils::array_exists($serviceResonse)) {
                        $deliveryService = \CRbsMoyskladHelper::createDeliveryService();
                        $this->{'customerorder'}->addInfoMessage(LangMsg::get('ADDED_SERVICE_MS', ['#HREF#' => $deliveryService->{'meta'}->{'uuidHref'}, '#ID#' => Config::getDeliveryExternalCode(), '#NAME#' => LangMsg::get('DELIVERY_SERVICE_NAME')]));
                    } else {
                        $deliveryService = current($serviceResonse->{'rows'});
                    }

                    if (!empty($deliveryService->{'externalCode'}) && (string)$deliveryService->{'externalCode'} === Config::getDeliveryExternalCode()) {
                        $this->{'positions'}[$deliveryService->{'externalCode'}] =  (object)[
                            'quantity' => intval(1),
                            'price' => $orderDeliveryPrice,
                            'discount' => intval(0),
                            'assortment' => (object)[
                                'meta' => $deliveryService->{'meta'}
                            ],
                            'reserve' => $reserved ? intval(1) : intval(0),
                            'vat' => $vatRate ? $vatDelivery : intval(0)
                        ];
                    } else {
                        $this->{'customerorder'}->addWarningMessage(LangMsg::get('CANT_FIND_BASKET_ITEMS_MS', ['#ID#' => 'ORDER_DELIVERY']));
                    }
                } else {
                    ExceptionRuler::throwApiResponseException($serviceResonse, [
                        'id' => 'delivery',
                        'action' => 'search'
                    ]);
                }
            }
        }

        public function setBundleBasketPositions($basketItem)
        {
            $bundlePrice = (float)$basketItem->getField('PRICE');
            $bundleQty = (float)$basketItem->getField('QUANTITY');
            $bundleFullPrice = $bundlePrice * $bundleQty;

            $recalcBundleItems = [];
            $diff = intval(0);
            $bundleSumByItems = intval(0);

            $bundleRecalc = $this->getParam('bundleRecalc');

            if ($bundleRecalc && $bundleFullPrice > intval(0)) {
                foreach ($basketItem->getBundleCollection() as $bundleItem) {
                    $bundleFullItemPrice = (float)$bundleItem->getField('PRICE') * (float)$bundleItem->getField('QUANTITY');
                    $bundleSumByItems += $bundleFullItemPrice;
                }
                if($bundleSumByItems == intval(0)) {
                    $bundleRecalc = false;
                } else {
                    $diff = $bundleFullPrice - $bundleSumByItems;
                    if ($diff <> intval(0)) {
                        foreach ($basketItem->getBundleCollection() as $bundleItem) {
                            $currXmlId = $this->getBasketItemXmlId($bundleItem);
                            $bundleFullItemPrice = (float)$bundleItem->getField('PRICE') * (float)$bundleItem->getField('QUANTITY');
                            $recalcBundleItems[$currXmlId] = $bundleFullItemPrice / $bundleSumByItems;
                        }
                    } else {
                        $bundleRecalc = false;
                    }
                }
            } else {
                $bundleRecalc = false;
            }
            
            foreach ($basketItem->getBundleCollection() as $bundleItem) {
                $discount = intval(0);
                if ($bundleRecalc && $diff !== intval(0)) {
                    $currXmlId = $this->getBasketItemXmlId($bundleItem);
                    $newPrice = $bundleItem->getField('PRICE') + ($recalcBundleItems[$currXmlId] * $diff / (float)$bundleItem->getField('QUANTITY'));
                    $discount = (float)(1 - ($newPrice / $bundleItem->getField('PRICE'))) * 100;
                }
                $this->addPosition($bundleItem, $discount);
            }
        }

        public function getProductMs($currXmlId = '')
        {
            $isModifSync = $this->getParam('isModifSync');
            $cacheTime = $this->getParam('cacheTime');
            $archived = $this->getParam('basketarchived');

            $archived = $archived ? ';archived=true;archived=false' : '';

            $eventProduct = (object)[];
            Utils::send_bx_event(Config::getModuleId(true), 'OnBeforeGetProductMs', [
                'isModifSync' => $isModifSync,
                'archived' => $archived,
                'currXmlId' => $currXmlId,
            ], $eventProduct);
            if (is_object($eventProduct) && property_exists($eventProduct, 'meta')) {
                return $eventProduct;
            }

            if ($isModifSync && mb_strpos($currXmlId, '#') !== false) {
                $tmpXmlAr = explode('#', $currXmlId);
                if (Utils::count($tmpXmlAr) === intval(2)) {
                    $currXmlId = array_pop($tmpXmlAr);
                    if (!empty($currXmlId)) {
                        $product = ApiNew::get('/entity/variant', ['filter' => 'externalCode=' . $currXmlId . $archived], $cacheTime);
                    }
                }
            } else {
                $product = ApiNew::get('/entity/assortment', ['filter' => 'externalCode=' . $currXmlId . $archived], $cacheTime);
            }

            if (
                Utils::is_success($product) &&
                !Utils::array_exists($product) && 
                $isModifSync && 
                $this->getParam('extCodeSource') && 
                !empty($currXmlId)
            ) {
                $product = ApiNew::get('/entity/variant', ['filter' => 'externalCode=' . $currXmlId . $archived], $cacheTime);
            }

            if (Utils::has_errors($product)) {
                ExceptionRuler::throwApiResponseException($product, [
                    'id' => 'basket_item',
                    'action' => 'search'
                ]);
            }

            if (!Utils::array_exists($product)) {
                $this->{'customerorder'}->addWarningMessage(LangMsg::get('CANT_FIND_BASKET_ITEMS_MS', ['#ID#' => $currXmlId]));
                return false;
            }

            if (Utils::array_exists($product)) {
                return array_shift($product->{'rows'});
            }

            return false;
        }

        public function createProductMs($basketItem, $entityType = 'product')
        {
            $currXmlId = $this->getBasketItemXmlId($basketItem);

            $createItemArray = [
                'name' => (string)$basketItem->getField('NAME'),
                'externalCode' => (string)$currXmlId
            ];

            $newItemIdAttr = $this->getParam('newItemIdAttr');
            if (!empty($newItemIdAttr) && $newItemIdAttr != 'N') {
                $newItemIdAttr = explode(':', $newItemIdAttr);
                $newItemIdAttrId = $newItemIdAttr[1];
                $newItemIdAttrType = $newItemIdAttr[0];
                $valueId = $newItemIdAttrType == 'string' ? (string)$basketItem->getField('PRODUCT_ID') : (int)$basketItem->getField('PRODUCT_ID');
                $createItemArray['attributes'] = \CRbsMoyskladHelper::convertAttributesToNewApi([[
                    'id' => $newItemIdAttrId,
                    'value' => $valueId
                ]], 'product');
            }

            $iblockElement = \CRbsMoyskladHelper::getIblockElementByProductId($basketItem->getField('PRODUCT_ID'));

            $newItemCodeField = $this->getParam('newItemCodeField');
            if (
                !empty($newItemCodeField) && 
                isset($iblockElement['PROPERTIES'][$newItemCodeField]['VALUE']) && 
                !empty($iblockElement['PROPERTIES'][$newItemCodeField]['VALUE'])
            ) {
                $createItemArray['code'] = $iblockElement['PROPERTIES'][$newItemCodeField]['VALUE'];
            }

            $newItemArticleField = $this->getParam('newItemArticleField');
            if (
                !empty($newItemArticleField) && 
                isset($iblockElement['PROPERTIES'][$newItemArticleField]['VALUE']) && 
                !empty($iblockElement['PROPERTIES'][$newItemArticleField]['VALUE'])
            ) {
                $createItemArray['article'] = $iblockElement['PROPERTIES'][$newItemArticleField]['VALUE'];
            }

            $newItemDescription = $this->getParam('newItemDescription');
            if (!empty($newItemDescription) && $newItemDescription != 'N') {
                if (isset($iblockElement['FIELDS'][$newItemDescription]) && !empty($iblockElement['FIELDS'][$newItemDescription])) {
                    $createItemArray['description'] = strip_tags($iblockElement['FIELDS'][$newItemDescription]);
                }
            }

            $picFields = ['newItemPrevPic', 'newItemDetailPic'];
            foreach($picFields as $picField) {
                $picFlag = $this->getParam($picField);
                if ($picFlag) {
                    $pictureFields = $picField == $picFields[0] ? 'PREVIEW_PICTURE' : 'DETAIL_PICTURE';
                    if (isset($iblockElement['FIELDS'][$pictureFields]) && !empty($iblockElement['FIELDS'][$pictureFields])) {
                        $picId = $iblockElement['FIELDS'][$pictureFields];
                        if($picId > intval(0)) {
                            if($file = \CFile::GetByID($picId)->Fetch()) {
                                $filePath = $_SERVER['DOCUMENT_ROOT'] . $file['SRC'];
                                if(file_exists($filePath) && !empty($file['ORIGINAL_NAME'])) {
                                    $createItemArray['images'][] = (object)[
                                        'filename' => $file['ORIGINAL_NAME'],
                                        'content' => base64_encode(file_get_contents($filePath))
                                    ];
                                }
                            }
                        }
                    }
                }
            }
                  
            $newItemFolder = $this->getParam('newItemFolder');
            if (is_object($newItemFolder)) {
                $createItemArray['productFolder'] = $newItemFolder; 
            }
             
            $newItemPrice = $this->getParam('newItemPrice');
            if (!empty($newItemPrice)) {

                $priceType = ApiNew::get('/context/companysettings/pricetype/', ['filter' => 'name=' . $newItemPrice], 86400 * 365);

                if (Utils::is_count($priceType) && !empty($basketItem->getField('CURRENCY'))) {
                    $currency = ApiNew::get('/entity/currency', ['filter' => 'isoCode=' . $basketItem->getField('CURRENCY')], 86400 * 365);
                    if (Utils::is_success($currency) && Utils::array_exists($currency)) {
                        $createItemArray['salePrices'][] = (object)[
                            'value' => (int)$basketItem->getField('PRICE') * 100,
                            'currency' => $currency->{'rows'}[intval(0)],
                            'priceType' =>  $priceType[intval(0)]
                        ];
                    } else {
                        $this->{'customerorder'}->addErrorMessageArray($currency->{'errors'});
                    }

                } else if(Utils::has_errors($priceType)) {
                    $this->{'customerorder'}->addErrorMessageArray($priceType->{'errors'});
                }
            }

            Utils::send_bx_event(Config::getModuleId(true), 'OnBeforeCreateProductMs', [
                'entityType' => $entityType,
                'basketItem' => $basketItem,
                'currXmlId' => $currXmlId,
                'createItemArray' => $createItemArray
            ], $createItemArray);

            $returnPost = ApiNew::post('/entity/' . $entityType, $createItemArray);
            if (Utils::is_success($returnPost)) {
                $this->{'customerorder'}->addInfoMessage(LangMsg::get('ADDED_PRODUCT_MS', ['#HREF#' => $returnPost->{'meta'}->{'uuidHref'}, '#ID#' => $currXmlId, '#NAME#' => $basketItem->getField('NAME')]));
                return $returnPost;
            } else if(Utils::has_errors($returnPost)) {
                ExceptionRuler::throwApiResponseException($returnPost, [
                    'id' => 'basket_item',
                    'action' => 'create'
                ]);
            }
        }
    }
}

use Rbs\Moysklad\Services\BasketUtils;
use Rbs\Moysklad\Services\BasketProcess;
use Rbs\Moysklad\Services\BxPropertyProcess;
use Rbs\Moysklad\Services\MsPropertyProcess;
use Rbs\Moysklad\Services\LocationUtils;

if (!class_exists('CRbsMoyskladHelper')) {
    class CRbsMoyskladHelper
    {

        public static function getLicenseData(bool $isDebug = false): string
        {
            $result = '';
            $logger = new Loger();

            try {

                $hashKey = '';
                try {
                    
                    $application = \Bitrix\Main\Application::getInstance();
                    if (method_exists($application, 'getLicense')) {
                        $license = $application->getLicense();
                        if (method_exists($license, 'getPublicHashKey')) {
                            $hashKey = $license->getPublicHashKey();
                        }
                    }

                    if (empty($hashKey)) {
                        require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client_partner.php");
                        $hashKey = md5("BITRIX" . \CUpdateClientPartner::GetLicenseKey() . "LICENCE");
                    }

                    $logger->addInfoMessage(
                        LangMsg::get('LICENSE_DATA_MESSAGE_HASH_KEY', ['#HASH#' => $hashKey])
                    );
                } catch (\Throwable  $e) {
                    throw new \Exception(
                        LangMsg::get('LICENSE_DATA_ERROR_GET_HASH', ['#ERROR#' => $e->getMessage()])
                    );
                } finally {
                    if (empty($hashKey)) {
                        throw new \Exception(
                            LangMsg::get('LICENSE_DATA_ERROR_EMPTY_HASH')
                        );
                    }
                }

                $http = new \Bitrix\Main\Web\HttpClient();
                $http->setHeader('Content-Type', 'application/json');

                $requestData = json_encode([
                    'license_key' => $hashKey,
                    'is_dev_site' => \COption::GetOptionString("main", "update_devsrv", "") == "Y",
                    'domain' => $_SERVER['SERVER_NAME'],
                    'module_id' => Config::getModuleId(true),
                ]);

                $response = $http->post('https://api.despi.ru/v1/check', $requestData);

                $encodedResponse = json_decode($response, true);
                if (isset($encodedResponse['data']) && is_string($encodedResponse['data'])) {
                    $result = $encodedResponse['data'];
                    $logger->addSuccessMessage(
                        LangMsg::get('LICENSE_DATA_SUCCESS_LICENSE')
                    );
                } else {
                    $logger->addErrorMessage(
                        LangMsg::get('LICENSE_DATA_ERROR_REQUEST', ['#REQUEST#' => json_encode($requestData)])
                    );
                    $logger->addErrorMessage(
                        LangMsg::get('LICENSE_DATA_ERROR_RESPONSE', ['#RESPONSE#' => $response])
                    );
                }

            } catch (\Throwable $e) {

                $logger->addErrorMessage(
                    LangMsg::get('LICENSE_DATA_ERROR_GET_DATA', ['#ERROR#' => $e->getMessage()])
                );
                $result = '';

            } finally {
                if ($isDebug) {
                    $logger->exportLog(
                        LangMsg::get('LICENSE_DATA_LOG_MODULE_CHECK', ['#MODULE#' => Config::getModuleId(true)])
                    );
                }
            }

            return $result;
        }

        /** @deprecated */
        public static function createArMsBasket(Customerorder &$customerOrder, bool &$errorsBasket) : array
        {
            return BasketUtils::createMsBasketSource($customerOrder, $errorsBasket);
        }

        /** @deprecated */
        public static function createArBxBasket(\Bitrix\Sale\Basket $basketItems) : array
        {
            return BasketUtils::createBxBasketSource($basketItems);
        }

        /** @deprecated */
        public static function isCancelReservedBasket(\Bitrix\Sale\Order $order): bool
        {
            return BasketUtils::hasCancelReservedBasket($order);
        }

        /** @deprecated */
        public static function setBxProps(&$customerOrder)
        {
            BxPropertyProcess::setPropertyToBxOrder($customerOrder);
        }

        /** @deprecated */
        public static function findLocationCodeFromShipmentAddress(object $shipmentAddressFull = null): string
        {
            return LocationUtils::getLocationCodeFromShipmentAddress($shipmentAddressFull);
        }

        /** @deprecated */
        public static function getRegionNameByMetaObject(object $regionMeta = null): string
        {
            return LocationUtils::getRegionNameByRegionMetaObject($regionMeta);
        }

        /** @deprecated */
        public static function getCountryNameByMetaObject(object $countryMeta = null): string
        {
            return LocationUtils::getCountryNameByCountryMetaObject($countryMeta);
        }

        /** @deprecated */
        public static function setMsProps(&$customerOrder)
        {
            MsPropertyProcess::setPropertyToMsOrder($customerOrder);
        }

        public static function getBxPropValue(\Bitrix\Sale\Order $order, int $propId = 0): string
        {
            $result = '';
            if ($propId > intval(0) && ($propertyCollection = $order->getPropertyCollection())) {
                if ($property = $propertyCollection->getItemByOrderPropertyId($propId)) {
                    $result = (string)$property->getValue();
                }
            }
            return $result;
        }

        public static function getBxPropEnumValue($propIdBx = 0, $propValue = ''): string
        {
            $result = '';

            if((int)$propIdBx > 0 && !empty($propValue)) {
                $rsVariant = \CSaleOrderPropsVariant::GetList([], ['ORDER_PROPS_ID' => (int)$propIdBx, 'NAME' => (string)$propValue]);
                if($obVariant = $rsVariant->getNext()) {
                    $result = $obVariant['VALUE'];
                } else {

                    $value = md5($propIdBx . $propValue . 'value');
                    $arParams = [
                        "ORDER_PROPS_ID" => (int)$propIdBx,
                        "VALUE" => $value,
                        "NAME" => $propValue,
                        "SORT" => 100,
                        "XML_ID" => md5($propIdBx . $propValue . 'xml_id')
                    ];

                    if(\CSaleOrderPropsVariant::Add($arParams)) {
                        $result = $value;
                    }
                }
            }

            return (string)$result;
        }

        public static function getEnumBxPropValue(\Bitrix\Sale\Order $order, int $propId = 0): string
        {
            $result = '';
            if ($propId > intval(0) && ($propertyCollection = $order->getPropertyCollection())) {
                if ($property = $propertyCollection->getItemByOrderPropertyId($propId)) {
                    if($value = $property->getValue()) {
                        $propOptions = $property->getProperty()['OPTIONS'];
                        if(isset($propOptions[$value]) && !empty($propOptions[$value])) {
                            $result = (string)$propOptions[$value];
                        }
                    }   
                }
            }
            return $result;
        }

        public static function setBxOneProp(&$customerOrder, int $propIdBx = 0, string $value = '')
        {
            $order = $customerOrder->getOrderEntity();
            if ($propertyCollection = $order->getPropertyCollection()) {
                if ($property = $propertyCollection->getItemByOrderPropertyId($propIdBx)) {
                    if ($property->getType() === 'ENUM') {
                        $propOptions = array_flip(array_map('mb_strtolower', $property->getProperty()['OPTIONS']));
                        if (isset($propOptions[$value])) {
                            $value = $propOptions[$value];
                        }
                    }
                    $property->setValue($value);
                }
            }
        }

        /** @deprecated */
        public static function getMetaLocationFromMs($type = '', $value = '', $filterSearchField = 'name')
        {
            return LocationUtils::getLocationMetaDataFromMs($type, $value, $filterSearchField);
        }

        /** @deprecated */
        public static function convertCityNameFromMs(string $cityName = ''): array
        {
            return LocationUtils::convertCityNameToMs($cityName);
        }

        /** @deprecated */
        public static function convertRegionName(string $regionName = '', string $convertVector = 'MS'): string
        {
            return LocationUtils::convertRegionNameToMs($regionName, $convertVector);
        }

        /** @deprecated */
        public static function hasDeliveryPriceChanges(\Bitrix\Sale\Order $order, object $orderMs): bool
        {
            return BasketUtils::hasDiffDeliveryPriceInOrders($order, $orderMs);
        }

        /** @deprecated */
        public static function hasBasketChanges(array $arMsBasket = [], array $arBxBasket = []): bool
        {
            return BasketUtils::hasDiffBasketArrays($arMsBasket, $arBxBasket);
        }

        /** @deprecated */
        public static function buildBxBasket(\Bitrix\Sale\Basket &$basket, array $arMsBasket = [], array $arBxBasket = [], &$needBackCanBuyZero, $currency): void
        {
            BasketProcess::fillBxBasketFromMsBasket($basket, $arMsBasket, $arBxBasket, $needBackCanBuyZero, $currency);
        }

        /** @deprecated */
        public static function getPositionFinalPrice(object $position = null): float
        {
            return BasketUtils::getMsPositionFinalPrice($position);
        }

            public static function getProductBxParamsByXmlId(string $xmlId = ''): array
            {
                $result = [];

                $product = \Bitrix\Catalog\ProductTable::getList([
                    'filter' => ['=XML_ID' => $xmlId],
                    'select' => [
                        '*',
                        'XML_ID' => 'IBLOCK_ELEMENT.XML_ID',
                        'IBLOCK_ID' => 'IBLOCK_ELEMENT.IBLOCK_ID',
                        'NAME' => 'IBLOCK_ELEMENT.NAME'
                    ],
                    'cache' => [
                        'ttl' => Config::checkFeature('basketdoublesync') ? intval(0) : Config::cacheTime('basket_items_bx')
                    ]
                ])->fetch();
                if (is_array($product) && !empty($product['ID'])) {
                    $product['MODULE_ID'] = 'catalog';
                    $result = $product;
                }

                return $result;
            }

        public static function hasOrderChanges(\Bitrix\Sale\Order $order)
        {
            $hasChanged = $order->isChanged();

            if(!$hasChanged) {
                if($propCollection = $order->getPropertyCollection()) {
                    foreach ($propCollection as $prop) {
                        if (Utils::is_count($prop->getFields()->getChangedValues())) {
                            $hasChanged = true;
                            break;
                        }
                    }
                }
            }

            if(!$hasChanged) {
                if ($paymentCollection = $order->getPaymentCollection()) {
                    foreach ($paymentCollection as $payment) {
                        if (Utils::is_count($payment->getFields()->getChangedValues())) {
                            $hasChanged = true;
                            break;
                        }
                    }
                }
            }

            if (!$hasChanged) {
                if ($shipmentCollection = $order->getPaymentCollection()) {
                    foreach ($shipmentCollection as $shipment) {
                        if (Utils::is_count($shipment->getFields()->getChangedValues())) {
                            $hasChanged = true;
                            break;
                        }
                    }
                }
            }

            return true;
        }

        /** @deprecated */
        public static function getLocationStringFromBx(string $locationCode = '') : string
        {
            return LocationUtils::getLocationStringByCodeFromBx($locationCode);
        }

        public static function convertAttributesToNewApi(array $attributes = [], string $entityType = 'customerorder') : array
        {
            $result = [];

            if (Utils::is_count($attributes)) {
                $attrs = array_values($attributes);
                foreach ($attrs as $attr) {
                    if (is_object($attr)) {
                        $attr = (array)$attr;
                    }
                    if (isset($attr['meta'])) {
                        continue;
                    }
                    $id = $attr['id'];
                    if (empty($id)) {
                        continue;
                    }
                    if (isset($attr['value'])) {
                        $result[] = [
                            'meta' => Config::getAttributeMetaLink($id, $entityType),
                            'value' => $attr['value']
                        ];
                    }
                    if (isset($attr['file'])) {
                        $result[] = [
                            'meta' => Config::getAttributeMetaLink($id, $entityType),
                            'file' => $attr['file']
                        ];
                    }
                }
            }

            return $result;
        }

        public static function getMetadataWithAttrs($entity = 'product', $caheTime = 0)
        {
            $getMeta = \Rbs\Moysklad\ApiNew::get('/entity/'.$entity.'/metadata', [], $caheTime);

            if (Utils::is_success($getMeta)) {
                
                $getMeta->{'attributes'} = [];
                $attrs = \Rbs\Moysklad\ApiNew::get('/entity/'.$entity.'/metadata/attributes', [], $caheTime);
                if (Utils::is_success($attrs)) {
                    if(Utils::array_exists($attrs)) {
                        $getMeta->{'attributes'} = $attrs->{'rows'};
                        $attributesById = [];
                        foreach ($getMeta->{'attributes'} as $attr) {
                            $attributesById[$attr->{'id'}] = $attr;
                        }
                        $getMeta->{'attributesById'} = $attributesById;
                        unset($attributesById);
                    }                    
                } else {
                    ExceptionRuler::throwApiResponseException($attrs, [
                        'id' => 'metadata',
                        'action' => 'get_attrs',
                        'entity' => $entity
                    ]);
                }

            } else {
                ExceptionRuler::throwApiResponseException($getMeta, [
                    'id' => 'metadata',
                    'action' => 'get',
                    'entity' => $entity
                ]);
            }
            
            return $getMeta;
        }

        public static function getAttributeEntityVariantForSelect($customEntityMetaHref = ''): array
        {
            $result = [];
            if (!empty($customEntityMetaHref)) {
                $msSource = \Rbs\Moysklad\ApiNew::get($customEntityMetaHref);
                if (Utils::is_success($msSource)) {
                    $msValues = \Rbs\Moysklad\ApiNew::get($msSource->{'entityMeta'}->{'href'});
                    if (Utils::is_success($msValues) && Utils::array_exists($msValues)) {
                        foreach ($msValues->{'rows'} as $row) {
                            $result[$row->{'id'}] = $row->{'name'};
                        }
                    }
                }
            }
            return $result;
        }

        /** @deprecated */
        public static function getXmlIdFromBasketItem(\Bitrix\Sale\BasketItem $basketItem): string
        {
            return BasketUtils::getBxProductXmlIdFromBasketItem($basketItem);
        }

        public static function getIblockElementByProductId(int $productId): array
        {
            $result = [];

            if ($productId > 0 && \Bitrix\Main\Loader::includeModule('iblock')) {
                if ($iblockElement = \CIblockElement::getList([], ['=ID' => $productId])->GetNextElement()) {
                    $result['FIELDS'] = $iblockElement->GetFields();
                    $result['PROPERTIES'] = $iblockElement->GetProperties();
                }
            }

            return $result;
        }

        public static function createDeliveryService(): object
        {
            $createItemArray = [
                'name' => LangMsg::get('DELIVERY_SERVICE_NAME'),
                'externalCode' => Config::getDeliveryExternalCode()
            ];

            $returnPost = ApiNew::post('/entity/service', $createItemArray);

            if (Utils::has_errors($returnPost)) {
                ExceptionRuler::throwApiResponseException($returnPost, [
                    'id' => 'delivery',
                    'action' => 'create',
                    'entity' => 'service'
                ]);
            }

            return $returnPost;
        }

        public static function getProductMsByExternalCode(string $currXmlId = '')
        {
            $isModifSync = Config::checkFeature('basketmodifsync');
            $cacheTime = (int)Config::cacheTime('basket_items_ms');
            $archived = Config::checkFeature('basketarchived');
            $extCodeSource =  Config::checkFeature('basketextcodessource');

            $archived = $archived ? ';archived=true;archived=false' : '';

            if ($isModifSync && mb_strpos($currXmlId, '#') !== false) {
                $tmpXmlAr = explode('#', $currXmlId);
                if (Utils::count($tmpXmlAr) === intval(2)) {
                    $currXmlId = array_pop($tmpXmlAr);
                    if (!empty($currXmlId)) {
                        $product = ApiNew::get('/entity/variant', ['filter' => 'externalCode=' . $currXmlId . $archived], $cacheTime);
                    }
                }
            } else {
                $product = ApiNew::get('/entity/assortment', ['filter' => 'externalCode=' . $currXmlId . $archived], $cacheTime);
            }

            if (
                !Utils::array_exists($product) &&
                $isModifSync &&
                $extCodeSource &&
                !empty($currXmlId)
            ) {
                $product = ApiNew::get('/entity/variant', ['filter' => 'externalCode=' . $currXmlId . $archived], $cacheTime);
            }

            if (Utils::array_exists($product)) {
                return array_shift($product->{'rows'});
            }

            return false;
        }

        public static function getCustomEntityValue($value = '', $entityId = '')
        {
            $result = false;
            
            if(!empty($value) && !empty($entityId)) {

                $isStandartEntityId = in_array($entityId, Config::getStandartEntityNamesForEnumProp());

                if($isStandartEntityId) {
                    $msResult = ApiNew::get(Config::getBaseHrefLinkNew($entityId), ['filter' => 'name=' . $value], 86400);
                } else {
                    $msResult = ApiNew::get(Config::getBaseHrefLinkNew('customEntity') . $entityId, ['filter' => 'name=' . $value], 86400);
                }

                if (Utils::is_success($msResult)) {
                    if(Utils::array_exists($msResult)) {
                        $result = (object)[
                            'meta' => $msResult->{'rows'}[intval(0)]->{'meta'}
                        ];
                    } else if(!$isStandartEntityId){
                        $msResult = ApiNew::post(
                            Config::getBaseHrefLinkNew('customEntity') . $entityId,
                            [
                                'name' => $value
                            ]
                        );
                        if (Utils::is_success($msResult)) {
                            $result = (object)[
                                'meta' => $msResult->{'meta'}
                            ];
                        }
                    }                    
                }
            }

            return $result;
        }

        public static function getDocumentUniqName(string $entity = '', string $name = ''): string
        {
            if(empty($entity) || empty($name)){
                return '';
            }

            $nameResult = $name;
            $isUniqName = false;
            $cnt = 0;

            do {

                $isUniqName = true;
                $orders = ApiNew::get('/entity/' . $entity, ['filter' => 'name=' . $nameResult]);

                if (Utils::is_success($orders)) {
                    if(Utils::array_exists($orders)) {
                        $cnt++;
                        $nameResult = $name . " ({$cnt})";
                        $isUniqName = false;
                    }
                } else if (Utils::has_errors($orders)) {
                    ExceptionRuler::throwApiResponseException($orders, [
                        'id' => 'order_num',
                        'action' => 'search',
                        'entity' => $entity
                    ]);
                }

                if(!$isUniqName && $cnt > 3) {
                    $nameModificator = time();
                    $nameResult = $name . " ({$nameModificator})";
                    $isUniqName = false;
                }
                
            } while (!$isUniqName);

            return $nameResult;
        }

        public static function exportLog(Customerorder $customerOrder, string $headMessageId = '')
        {
            $logger = new Loger();
            $logger->addMessageArray($customerOrder->getLogList());
            $mainMessage = LangMsg::get($headMessageId, ['#ORDER_ID#' => $customerOrder->getOrderBxId()]);
            $logger->exportLog($mainMessage);
        }
    }
}
?>