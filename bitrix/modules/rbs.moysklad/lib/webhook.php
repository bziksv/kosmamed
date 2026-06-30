<?php
namespace Rbs\Moysklad; 

use \Rbs\Moysklad\LangMsg;
use \Rbs\Moysklad\Utils;
use \Rbs\Moysklad\Internals\ModifyUser;
use \Rbs\Moysklad\Internals\TimezoneState;
use \Rbs\Moysklad\Debug\Loger;
use \Rbs\Moysklad\Controller\Tasker\WebhookTasker;

\Bitrix\Main\Loader::includeModule('sale');
\Bitrix\Main\Loader::includeModule('catalog');
\Bitrix\Main\Loader::includeModule('iblock');

class Webhook
{
    public static function isCacheEntity($eventHook)
    {
        $cacheId = md5($eventHook->meta->type . $eventHook->action . $eventHook->meta->href);
        return Utils::is_exsists_cache($cacheId, 'hookque', Config::getCacheHookTime());
    }

    public static function cacheUpdateEntity($href = '', $type = 'customerorder', $action = 'UPDATE')
    {
        $href = explode('?', $href)[0];
        return self::isCacheEntity((object)[
            'meta' => (object)[
                'type' => $type,
                'href' => $href
            ],
            'action' => $action
        ]);
    }

    public static function processHook($inputData = false)
    {
        Utils::say_ok(); 

        TimezoneState::setTimeZone();

        if(!Config::checkFeature('modulesync') || !is_object($inputData) || Utils::count($inputData->events) <= 0) return;

        $instantHooks = [];
        $queueHooks = [];
        $mixedHooksGroups = [];
        
        foreach($inputData->events as $eventHook) {
            $executionType = self::getWebhookExecutionType($eventHook);
            
            switch ($executionType) {
                case 'instant':
                    $instantHooks[] = $eventHook;
                    break;
                    
                case 'queue':
                    $queueHooks[] = $eventHook;
                    break;
                    
                case 'mixed':
                    $groupKey = ($eventHook->meta->type ?? '') . '_' . ($eventHook->action ?? '');
                    if (!isset($mixedHooksGroups[$groupKey])) {
                        $mixedHooksGroups[$groupKey] = [];
                    }
                    $mixedHooksGroups[$groupKey][] = $eventHook;
                    break;
                    
                default:
                    $instantHooks[] = $eventHook;
                    break;
            }
        }
        
        foreach($mixedHooksGroups as $group) {
            if (count($group) > 0) {
                $instantHooks[] = array_shift($group);
                $queueHooks = array_merge($queueHooks, $group);
            }
        }

        if (count($queueHooks) > 0) {
            $logger = new Loger();
            $tasker = WebhookTasker::getInstance(Config::getProfileId());
            try {
                foreach($queueHooks as $eventHook) {
                    $tasker->addTask($eventHook);
                }
                $logger->addSuccessMessage(LangMsg::get('WEBHOOK_TASKER_ADD_TASKS', ['#COUNT#' => count($queueHooks)]));
            } catch (\Throwable $e) {
                $logger->addErrorMessage(LangMsg::get('WEBHOOK_TASKER_ADD_TASKS_ERROR', ['#ERROR#' => $e->getMessage()]));
            } finally {
                $logger->exportLog(LangMsg::get('WEBHOOK_TASKER_ADD_TASKS_LOG'));
            }
        }
        
        if (count($instantHooks) > 0) {
            $hookLimit = Config::getWebHookLimitCount();
            
            if (count($instantHooks) > $hookLimit) {
                $overflowHooks = array_splice($instantHooks, $hookLimit);
                
                if (count($overflowHooks) > 0) {
                    $logger = new Loger();
                    $tasker = WebhookTasker::getInstance(Config::getProfileId());
                    try {
                        foreach($overflowHooks as $eventHook) {
                            $tasker->addTask($eventHook);
                        }
                        $logger->addSuccessMessage(LangMsg::get('WEBHOOK_OVERFLOW_HOOKS', ['#COUNT#' => count($overflowHooks)]));
                    } catch (\Throwable $e) {
                        $logger->addErrorMessage(LangMsg::get('WEBHOOK_OVERFLOW_HOOKS_ERROR', ['#ERROR#' => $e->getMessage()]));
                    } finally {
                        $logger->exportLog(LangMsg::get('WEBHOOK_OVERFLOW_HOOKS_LOG'));
                    }
                }
            }
            
            if (count($instantHooks) > 0) {
                ModifyUser::authorize();
                self::processHookItemArray($instantHooks);
                ModifyUser::logout();
            }
        }
    }

    public static function processHookItemArray(array $eventsArray, bool $isFromTasker = false)
    {
        $logCollection = [];
        $logHeadMessages = [];
        foreach($eventsArray as $key => $eventHook){

            $logCollection[$key] = new Loger();
            $logHeadMessages[$key] = LangMsg::get('WEBHOOK_START' . ($isFromTasker ? '_FROM_TASKER' : ''), [
                '#TYPE#' => $eventHook->meta->type,
                '#ACTION#' => $eventHook->action,
                '#ID#' => array_pop(explode('/', $eventHook->meta->href))
            ]);

            if (self::isCacheEntity($eventHook)) {
                $logCollection[$key]->addInfoMessage(LangMsg::get('WEBHOOK_CACHED'));
                continue;
            }
    
            $event = new \Bitrix\Main\Event(Config::getModuleId(true), "OnBeforeWebhookProcess", array('data' => $eventHook));
            $event->send(); 
            foreach ($event->getResults() as $eventResult){
                if($eventResult->getType() === \Bitrix\Main\EventResult::ERROR){
                    $logCollection[$key]->addWarningMessage(LangMsg::get('WEBHOOK_EVENT_BEFORE_ERROR_RESULT'));
                    continue;
                } 
            }
            
            try {

                switch ((string)$eventHook->meta->type) {
                    case 'customerorder':

                        if ($eventHook->action === 'DELETE') {
                            continue;
                        }

                        $customerOrder = null;
                        $canCreateWithUpdateHook = Config::getImportType() === 'UPDATE' && !empty(Config::getImportTypeUpdateFlag()) && $eventHook->action === 'UPDATE';

                        if (
                            Config::checkFeature('importorder') &&
                            (
                                $eventHook->action === 'CREATE' ||
                                $canCreateWithUpdateHook
                            )
                        ) {

                            $canCreate = $eventHook->action === 'CREATE';

                            if (!$canCreate && $canCreateWithUpdateHook) {
                                $customerOrder = Customerorder::createFromHref($eventHook->meta->href);
                                $canCreate = !$customerOrder->isLoaded();
                            }

                            if ($canCreate) {

                                $numOfTrying = 5;
                                $currentTry = 1;
                                do {

                                    $currentTry++;

                                    $logCollection[$key]->addInfoMessage(LangMsg::get('WEBHOOK_CO_IMPORT_START'));

                                    $customerOrder = Customerorder::createBxOrder($eventHook->meta->href, $eventHook->action);
                                    $logCollection[$key]->addMessageArray($customerOrder->getLogList());

                                    if ($customerOrder->isLoaded()) {

                                        if ($order = $customerOrder->getOrderEntity()) {

                                            if (!Utils::is_exsists_cache(md5($order->getField('XML_ID')), 'order_api_create', Config::getCacheHookTime())) {

                                                $logCollection[$key]->addSuccessMessage(LangMsg::get('WEBHOOK_CO_IMPORT_SUCCESS', ['#ID#' => $order->getId()]));

                                                if (
                                                    Config::checkFeature('paysync') &&
                                                    Config::getPaySyncType() === 'full'
                                                ) {
                                                    $customerOrder->checkAllPayments();
                                                }

                                                $needPushDemandsToBx = Config::getOption('demand_exchange_type', 'N') === 'full' && Config::checkVectorFromBxToMs('demand');
                                                if ($needPushDemandsToBx) {
                                                    $demandEntity = new \Rbs\Moysklad\Entity\Demand($customerOrder);
                                                    $demandEntity->exportAllShipmentsToDemands();
                                                }

                                                Utils::send_bx_event(Config::getModuleId(true), 'OnCreateWebhookOrder', [
                                                    'bxOrder' => $customerOrder->getOrderEntity(),
                                                    'msOrder' => $customerOrder->getOrder()
                                                ]);
                                            }

                                            break;
                                        }
                                    }

                                    if ($customerOrder->isFinalError()) {
                                        break;
                                    }
                                } while ($currentTry <= $numOfTrying);

                                continue;
                            }
                        }

                        $logCollection[$key]->addInfoMessage(LangMsg::get('WEBHOOK_CO_SEARCH_START'));

                        if ($customerOrder === null) {
                            $customerOrder = Customerorder::createFromHref($eventHook->meta->href);
                        }

                        if (!$customerOrder->isLoaded()) {
                            $logCollection[$key]->addMessageArray($customerOrder->getLogList());
                            continue;
                        } else {

                            if ($order = $customerOrder->getOrderEntity()) {
                                $logCollection[$key]->addSuccessMessage(LangMsg::get('WEBHOOK_CO_SEARCH_SUCCESS', ['#ID#' => $order->getId()]));
                            } else {
                                $logCollection[$key]->addMessageArray($customerOrder->getLogList());
                                continue;
                            }
                        }

                        if ($customerOrder->isDisableOrderSync()) {
                            $logCollection[$key]->addInfoMessage(LangMsg::get('WEBHOOK_CO_DISABLED'));
                            continue;
                        }

                        switch ($eventHook->action) {
                            case 'UPDATE':

                                $customerOrder->checkUpdateHook();
                                $logCollection[$key]->addMessageArray($customerOrder->getLogList());

                                if ($customerOrder->isChangedSum()) {

                                    $needSaveOrder = false;
                                    if (Config::checkFeature('paynamesync')) {
                                        $customerOrder->setPaysystemName();
                                        $needSaveOrder = true;
                                    }
                                    if (Config::checkFeature('payinfosync')) {
                                        $customerOrder->setPaysystemInfo();
                                        $needSaveOrder = true;
                                    }
                                    if (Config::checkFeature('basketrecalc') && Config::checkFeature('basketsyncbx')) {
                                        if (!Utils::is_exsists_cache($eventHook->meta->href, 'recalcBasketCache', 30)) {
                                            $customerOrder->setBasket();
                                            $needSaveOrder = true;
                                        }
                                    }

                                    if ($needSaveOrder) {
                                        $customerOrder->saveOrderChanges();
                                    }

                                    if (
                                        Config::checkFeature('paysync') &&
                                        Config::getPaySyncType() === 'full'
                                    ) {
                                        $customerOrder->checkAllPayments();
                                    }
                                }

                                Utils::send_bx_event(Config::getModuleId(true), 'OnUpdateWebhookOrder', [
                                    'bxOrder' => $customerOrder->getOrderEntity(),
                                    'msOrder' => $customerOrder->getOrder(),
                                    'customerorder' => $customerOrder
                                ]);

                                break;
                        }

                        break;
                    case 'paymentin':
                    case 'cashin':

                        $needPushPaymentsToBx = Config::checkFeature('paysync') && Config::getPaySyncType() === 'full';
                        if ($needPushPaymentsToBx) {

                            $paymentItem = ApiNew::get($eventHook->meta->href);
                            if (Utils::is_success($paymentItem)) {

                                $orderBxChecked = [];
                                if (is_array($paymentItem->operations)) {

                                    if (Utils::array_exists($paymentItem, 'operations')) {
                                        foreach ($paymentItem->operations as $opertaion) {

                                            if (mb_strpos($opertaion->meta->href, '/customerorder/')) {

                                                $customerOrder = Customerorder::createFromHref($opertaion->meta->href);

                                                if (!$customerOrder->isLoaded()) {
                                                    continue;
                                                }

                                                if (!$customerOrder->isDisableOrderSync()) {
                                                    switch ($eventHook->action) {
                                                        case 'CREATE':
                                                            $customerOrder->updateBxPayment($paymentItem, $opertaion);
                                                            break;
                                                        case 'UPDATE':
                                                            $customerOrder->updateBxPayment($paymentItem, $opertaion);
                                                            break;
                                                        case 'DELETE':
                                                            $customerOrder->deleteBxPayment($paymentItem);
                                                            break;
                                                    }
                                                }

                                                if ($order = $customerOrder->getOrderEntity()) {
                                                    $orderBxChecked[] = $order->getField('ID');
                                                }

                                                $logCollection[$key]->addMessageArray($customerOrder->getLogList());
                                            }
                                        }
                                    }

                                    if (Utils::is_count($orderBxChecked)) {
                                        Customerorder::checkAllOrdersPayments($paymentItem, $orderBxChecked);
                                    }
                                }
                            }
                        }

                        break;

                    case 'demand':

                        $needPushDemandsToBx = Config::getOption('demand_exchange_type', 'N') === 'full' && Config::checkVectorFromMsToBx('demand');
                        if ($needPushDemandsToBx) {

                            $demandItem = ApiNew::get($eventHook->meta->href, ['expand' => 'positions.assortment, customerOrder.positions.assortment, state,store', 'limit' => 1]);
                            if (Utils::is_success($demandItem)) {
                                if (!empty($demandItem->customerOrder) && is_object($demandItem->customerOrder)) {

                                    $customerOrder = Customerorder::createFromFullMsObject($demandItem->customerOrder);

                                    if (!$customerOrder->isLoaded() || $customerOrder->isDisableOrderSync()) {
                                        continue;
                                    }

                                    $demandEntity = new \Rbs\Moysklad\Entity\Demand($customerOrder);
                                    switch ($eventHook->action) {
                                        case 'CREATE':
                                        case 'UPDATE':
                                            $demandEntity->updateShipment($demandItem);
                                            break;
                                    }

                                    if ($demandEntity->isChangedDeliveryPrice() && Config::checkFeature('deliverypricesync')) {

                                        if (Config::checkFeature('paynamesync')) {
                                            $customerOrder->setPaysystemName();
                                        }
                                        if (Config::checkFeature('payinfosync')) {
                                            $customerOrder->setPaysystemInfo();
                                        }

                                        $customerOrder->setDeliveryPrice();
                                        $customerOrder->saveOrderChanges();

                                        if (
                                            Config::checkFeature('paysync') &&
                                            Config::getPaySyncType() === 'full'
                                        ) {
                                            $customerOrder->checkAllPayments();
                                        }
                                    }

                                    $logCollection[$key]->addMessageArray($customerOrder->getLogList());
                                }
                            }
                        }

                        break;
                }

            } catch (\Bitrix\Main\SystemException $e) {
                $logCollection[$key]->addErrorMessage($e->getMessage());
            }

            Utils::send_bx_event(Config::getModuleId(true), 'OnAfterWebhookProcess', ['data' => $eventHook]);

            usleep(Config::getWebHookLimitCountInterval() * 1000);
        }
        if(Utils::is_count($logCollection)) {
            foreach ($logCollection as $key => $logger) {
                $logger->exportLog($logHeadMessages[$key]);
            }
        }
    }


    private static function getWebhookExecutionType($eventHook)
    {
        $type = $eventHook->meta->type ?? '';
        $action = $eventHook->action ?? '';
        
        if (empty($type) || empty($action)) {
            return 'instant';
        }
        
        $optionKey = "webh_execution_{$type}_{$action}";
        return Config::getOption($optionKey, 'instant');
    }

    /** @deprecated */
    public static function sayOkForMs()
    {
        return false;
    }
}