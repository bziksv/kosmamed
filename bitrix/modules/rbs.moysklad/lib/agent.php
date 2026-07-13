<?php
namespace Rbs\Moysklad;

use \Rbs\Moysklad\LangMsg;
use \Rbs\Moysklad\Internals\ModifyUser;
use \Rbs\Moysklad\Internals\TimezoneState;
use \Rbs\Moysklad\Debug\Loger;
use \Rbs\Moysklad\Services\OrderFilter;

class Agent
{

    public static function getAgentProfileName($agentName = '')
    {
        if (Config::getProfileId() > 0) {
            if(mb_strpos($agentName, '()') !== false) {
                $agentName = explode(')', $agentName)[0] . Config::getProfileId() . ');';
            } else {
                $agentName = explode(')', $agentName)[0] . ',' . Config::getProfileId() . ');';
            }
        }
        return $agentName;
    }

    public static function set(string $agentName = '', int $sort = 100, int $time = -1)
    {
        $agentNameFull = '\\' . __CLASS__ . '::' . self::getAgentProfileName($agentName);

        $res = \CAgent::GetList(["ID" => "DESC"], ["NAME" => $agentNameFull]);

        $agentInterval = $time !== -1 ? $time : (int)Config::getAgentInterval();
        $culture = \Bitrix\Main\Context::getCurrent()->getCulture();
        $phpDateTime = new \DateTime();
        $dateTime = \Bitrix\Main\Type\DateTime::createFromPhp($phpDateTime->modify('+'.$agentInterval.' second'));

        if ($obAgent = $res->GetNext()) {
            \CAgent::Update($obAgent['ID'], [
                'NAME' => $agentNameFull,
                'AGENT_INTERVAL' => $agentInterval,
                'SORT' => $sort
            ]);
        } else {
            \CAgent::AddAgent($agentNameFull, Config::getModuleId(true), "N", $agentInterval, $dateTime->toString($culture), "Y", $dateTime->toString($culture), $sort);
        }
    }

    public static function get($agentName = '')
    {
        $agentNameFull = '\\' . __CLASS__ . '::' . self::getAgentProfileName($agentName);
        $res = \CAgent::GetList(["ID" => "DESC"], ["NAME" => $agentNameFull]);
        if ($ob = $res->GetNext()) {
            return $ob['ID'];
        }
        return null;
    }

    public static function getInfo($agentName = '')
    {
        $agentNameFull = '\\' . __CLASS__ . '::' . self::getAgentProfileName($agentName);
        $res = \CAgent::GetList(["ID" => "DESC"], ["NAME" => $agentNameFull]);
        $result = [];
        if ($obAgent = $res->GetNext()) {
            foreach ($obAgent as $field => $val) {
                if ($field === 'LAST_EXEC' && empty($val)) {
                    $val = LangMsg::get('AGENT_NON_EXEC');
                }
                $result["#{$field}#"] = $val;
            }
            return $result;
        }
        return false;
    }

    public static function isEnabledAgent($agentName = ''): bool
    {
        $agentNameFull = '\\' . __CLASS__ . '::' . self::getAgentProfileName($agentName);
        $res = \CAgent::GetList(["ID" => "DESC"], ["NAME" => $agentNameFull]);
        if ($res->GetNext()) {
            return true;
        }
        return false;
    }

    public static function delete($agentName = '')
    {
        if ($agentId = self::get($agentName)) {
            \CAgent::delete($agentId);
        }
    }

    public static function check_module_agents($profileId = 0)
    {       
        if (Config::checkFeature('modulesync')) {
            $res = \CAgent::GetList(["ID" => "DESC"], ["MODULE_ID" => Config::getModuleId(true), 'ACTIVE' => 'N']);
            while ($obAgent = $res->GetNext()) {
                \CAgent::Update($obAgent['ID'], [
                    'ACTIVE' => 'Y'
                ]);
            }
        }

        if((int)$profileId > 0){
            return false;
        }

        return '\\' . __METHOD__ . "();";
    }

    public static function webhook_tasker_worker($profileId = 0)
    {
        if (Config::isProfilesOn()) {
            Config::setProfileId((int)$profileId);
        } else {
            $profileId = 0;
        }

        TimezoneState::setTimeZone();
        Config::setIgnorePushToMs(true);

        if (Config::checkFeature('modulesync')) {
            $worker = new \Rbs\Moysklad\Controller\Tasker\WebhookTaskerWorker($profileId);
            $worker->execute();
            if($profileId == 0) {
                \Rbs\Moysklad\Controller\Tasker\WebhookTaskerWorker::resetTaskerIndex();
            }
        }

        Config::setIgnorePushToMs(false);

        if ($profileId > 0) {
            return '\\' . __METHOD__ . "({$profileId});";   
        } else {
            return '\\' . __METHOD__ . "();";
        }
    }

    public static function clear_logs($profileId = 0)
    {
        if (Config::isProfilesOn()) {
            Config::setProfileId((int)$profileId);
        } else {
            $profileId = 0;
        }

        if (Config::checkFeature('modulesync')) {
            Debug\FileController::getInstance()->clearFileProcess();
        }

        if ($profileId > 0) {
            return '\\' . __METHOD__ . "({$profileId});";   
        } else {
            return '\\' . __METHOD__ . "();";
        }
    }

    public static function check_orders_from_ms($profileId = 0)
    {
        if ($profileId > 0) {
            Config::setProfileId($profileId);
        }

        TimezoneState::setTimeZone();

        Config::setIgnorePushToMs(true);

        ModifyUser::authorize();

        $configTag = 'check_orders_from_ms';
        $limit = 100;
        $offset = (int)Config::getOption($configTag . '_offset');
        $logger = new Loger();

        $isEmptyList = false;

        if (Config::checkFeature('modulesync')) {

            $lastDateUpdate = Config::getLastDateUpdate($configTag);

            $params = [
                'limit' => $limit,
                'offset' => $offset,
                'filter' => 'updated>=' . $lastDateUpdate,
                'expand' => 'positions.assortment,agent,state,files,store,salesChannel,demands.positions,rate.currency',
            ];

            $logger->addInfoMessage(LangMsg::get('LOG_AGENT_ORDER_FILTER', [
                '#FILTER#' => $params['filter'],
            ]));
            
            $orders = ApiNew::get('/entity/customerorder', $params);
            if (Utils::is_success($orders)) {

                //execute orders
                if(Utils::array_exists($orders)) {

                    foreach($orders->rows as $orderMs) {

                        $customerorder = Customerorder::createFromFullMsObject($orderMs);

                        try {
                            
                            if ($customerorder->isLoaded()) {
                                $customerorder->checkUpdateHook();
                            }
                            //todo task: https://despi.kaiten.ru/space/131051/card/33590821
                            //add some functions from webhook.php and change order in MS as array
                            if ($customerorder->isChangedSum()) {
                                if (
                                    Config::checkFeature('paysync') &&
                                    Config::getPaySyncType() === 'full'
                                ) {
                                    $customerorder->checkAllPayments();
                                }
                            }

                        } catch (\Bitrix\Main\SystemException $e) {
                            $logger->addErrorMessage($e->getMessage());
                        }

                        $logger->addMessageArray($customerorder->getLogList());
                    }
                    
                    $logger->addSuccessMessage(LangMsg::get('AGENT_CHECK_ORDERS_SUCCESS', [
                        '#COUNT#' => count($orders->rows)
                    ]));

                } else {
                    $logger->addInfoMessage(LangMsg::get('EMPTY_ENTITY_LIST'));
                    $isEmptyList = true;
                }
                
                if (!empty($orders->meta->nextHref)) {
                    Config::setLastDateUpdate($configTag, $lastDateUpdate);
                    $offset += $limit;
                } else {
                    if (Utils::array_exists($orders)) {
                        Config::setLastDateUpdate($configTag);
                    }
                    $offset = 0;
                }

                Config::setOption($configTag . '_offset', (int)$offset);

            } else {

                if(Utils::has_errors($orders)) {
                    foreach($orders->errors as $error) {
                        $logger->addErrorMessage($error);
                    }
                } else {
                    $logger->addErrorMessage(LangMsg::get('API_ERROR_ALL'));
                }

            }

            if (!$isEmptyList) {
                $logger->exportLog(LangMsg::get('AGENT_CHECK_ORDERS_START', [
                    '#OFFSET#' => $offset
                ]));
            }

        }

        ModifyUser::logout();

        Config::setIgnorePushToMs(false);

        if ($profileId > 0) {
            return '\\' . __METHOD__ . "({$profileId});";
        } else {
            return '\\' . __METHOD__ . "();";
        }
    }

    public static function onSalePaymentEntitySavedDelay($orderId = 0, $paymentId = 0, $profileId = 0)
    {
        if ((int)$profileId > 0) {
            Config::setProfileId($profileId);
        }

        TimezoneState::setTimeZone();

        Utils::send_bx_event(Config::getModuleId(true), 'OnBeforeAgent', [
            'agentName' => 'onSalePaymentEntitySavedDelay',
            'orderId' => $orderId,
            'paymentId' => $paymentId
        ]);

        if (!Config::checkFeature('modulesync')) {
            return false;
        }

        if ((int)$orderId <= 0 || (int)$paymentId <= 0) {
            return false;
        }

        $logger = new Loger();
        
        try {

            $customerOrder = new Customerorder($orderId);
            if ($customerOrder->isLoaded()) {

                $logger->addInfoMessage(LangMsg::get('AGENT_ON_SALE_PAYMENT_ENTITY_SAVED_DELAY_ORDER_LOADED'));

                try {
                    
                    if (Config::checkFeature('paysync')) {
                        $customerOrder->checkPaymentById($paymentId);
                    }
                    \CRbsMoysklad::updateOrder($customerOrder);

                    $logger->addSuccessMessage(LangMsg::get('AGENT_ON_SALE_PAYMENT_ENTITY_SAVED_DELAY_PAYMENT_UPDATED'));

                    return false;

                } catch (\Bitrix\Main\SystemException $e) {

                    $logger->addErrorMessage($e->getMessage());
                    $logger->addMessageArray($customerOrder->getLogList());

                    if ((int)$profileId > 0) {
                        return '\\' . __METHOD__ . "({$orderId}, {$paymentId}, {$profileId});";
                    } else {
                        return '\\' . __METHOD__ . "({$orderId}, {$paymentId});";
                    }
                }
                

            } else {

                $logger->addInfoMessage(LangMsg::get('AGENT_ON_SALE_PAYMENT_ENTITY_SAVED_DELAY_ORDER_NOT_FOUND_MS'));

                if ($order = \Bitrix\Sale\Order::load($orderId)) {

                    $logger->addInfoMessage(LangMsg::get('AGENT_ON_SALE_PAYMENT_ENTITY_SAVED_DELAY_ORDER_FOUND_BX'));

                    if (OrderFilter::isFiltredByStatus($order->getField('STATUS_ID'))) {
                        $logger->addWarningMessage(LangMsg::get('AGENT_ON_SALE_PAYMENT_ENTITY_SAVED_DELAY_FILTERED_BY_STATUS'));
                        return false;
                    }

                    $dateUpdateOrderBx = new \DateTime($order->getDateInsert()->format('Y-m-d H:i:s'));
                    $dateUpdateOrderMs = new \DateTime();
                    $isDelayChange = $dateUpdateOrderMs->format('U') - $dateUpdateOrderBx->format('U') > Config::getAgentLiveTime();

                    if ($isDelayChange) {
                        $logger->addInfoMessage(LangMsg::get('AGENT_ON_SALE_PAYMENT_ENTITY_SAVED_DELAY_ORDER_BX_DELAY_FINISH'));
                        return false;
                    } else {
                        $logger->addInfoMessage(LangMsg::get('AGENT_ON_SALE_PAYMENT_ENTITY_SAVED_DELAY_ORDER_RESTART'));
                    }

                } else {
                    $logger->addErrorMessage(LangMsg::get('AGENT_ON_SALE_PAYMENT_ENTITY_SAVED_DELAY_ORDER_NOT_FOUND_BX'));
                    return false;
                }

                if ((int)$profileId > 0) {
                    return '\\' . __METHOD__ . "({$orderId}, {$paymentId}, {$profileId});";
                } else {
                    return '\\' . __METHOD__ . "({$orderId}, {$paymentId});";
                }
            }

        } catch (\Throwable $e) {
            $logger->addErrorMessage($e->getMessage());
        } finally {
            $logger->exportLog(LangMsg::get('AGENT_ON_SALE_PAYMENT_ENTITY_SAVED_DELAY_START', ['#ORDER_ID#' => $orderId, '#PAYMENT_ID#' => $paymentId]));
        }
    }

    public static function onSaleOrderEntitySavedDelay($orderId = 0, $profileId = 0)
    {
        if ((int)$profileId > 0) {
            Config::setProfileId($profileId);
        }

        TimezoneState::setTimeZone();

        Utils::send_bx_event(Config::getModuleId(true), 'OnBeforeAgent', [
            'agentName' => 'onSaleOrderEntitySavedDelay',
            'orderId' => $orderId
        ]);

        if (!Config::checkFeature('modulesync')) {
            return false;
        }

        if ((int)$orderId <= 0) {
            return false;
        }

        $logger = new Loger();

        try {
            $customerOrder = new Customerorder($orderId);
            if ($customerOrder->isLoaded()) {

                $logger->addInfoMessage(LangMsg::get('AGENT_ON_SALE_ORDER_ENTITY_SAVED_DELAY_ORDER_LOADED'));

                try {

                    \CRbsMoysklad::updateOrder($customerOrder);
                    \CRbsMoysklad::updateAgent($customerOrder);

                    if (Config::checkFeature('paysync')) {
                        $customerOrder->checkAllPayments();
                    }

                    $needPushDemandsToMs = Config::getOption('demand_exchange_type', 'N') === 'full' && Config::checkVectorFromBxToMs('demand');
                    if ($needPushDemandsToMs) {
                        $demand = new \Rbs\Moysklad\Entity\Demand($customerOrder);
                        $demand->exportAllShipmentsToDemands();
                    }

                    $logger->addSuccessMessage(LangMsg::get('AGENT_ON_SALE_ORDER_ENTITY_SAVED_DELAY_ORDER_UPDATED'));

                    return false;

                }  catch (\Bitrix\Main\SystemException $e) {

                    $logger->addErrorMessage($e->getMessage());
                    $logger->addMessageArray($customerOrder->getLogList());

                    if ((int)$profileId > 0) {
                        return '\\' . __METHOD__ . "({$orderId}, {$profileId});";
                    } else {
                        return '\\' . __METHOD__ . "({$orderId});";
                    }
                    
                }

            } else {

                $logger->addInfoMessage(LangMsg::get('AGENT_ON_SALE_ORDER_ENTITY_SAVED_DELAY_ORDER_NOT_FOUND_MS'));

                if ($order = \Bitrix\Sale\Order::load($orderId)) {
                    
                    $logger->addInfoMessage(LangMsg::get('AGENT_ON_SALE_ORDER_ENTITY_SAVED_DELAY_ORDER_FOUND_BX'));

                    if (OrderFilter::isFiltredByStatus($order->getField('STATUS_ID'))) {
                        $logger->addWarningMessage(LangMsg::get('AGENT_ON_SALE_ORDER_ENTITY_SAVED_DELAY_FILTERED_BY_STATUS'));
                        return false;
                    }
                    
                    $dateUpdateOrderBx = new \DateTime($order->getDateInsert()->format('Y-m-d H:i:s'));
                    $dateUpdateOrderMs = new \DateTime();
                    $isDelayChange = $dateUpdateOrderMs->format('U') - $dateUpdateOrderBx->format('U') > Config::getAgentLiveTime();
                    
                    if ($isDelayChange) {
                        $logger->addInfoMessage(LangMsg::get('AGENT_ON_SALE_ORDER_ENTITY_SAVED_DELAY_ORDER_BX_DELAY_FINISH'));
                        return false;
                    } else {
                        $logger->addInfoMessage(LangMsg::get('AGENT_ON_SALE_ORDER_ENTITY_SAVED_DELAY_ORDER_RESTART'));
                    }

                } else {
                    $logger->addErrorMessage(LangMsg::get('AGENT_ON_SALE_ORDER_ENTITY_SAVED_DELAY_ORDER_NOT_FOUND_BX'));
                    return false;
                }

                if ((int)$profileId > 0) {
                    return '\\' . __METHOD__ . "({$orderId}, {$profileId});";
                } else {
                    return '\\' . __METHOD__ . "({$orderId});";
                }
            }

        } catch (\Throwable $e) {
            $logger->addErrorMessage($e->getMessage());
        } finally {
            $logger->exportLog(LangMsg::get('AGENT_ON_SALE_ORDER_ENTITY_SAVED_DELAY_START', ['#ORDER_ID#' => $orderId]));
        }
    }

    public static function createOrderApi($orderId = 0, $profileId = 0)
    {
        if ((int)$profileId > 0) {
            Config::setProfileId($profileId);
        }

        TimezoneState::setTimeZone();

        Utils::send_bx_event(Config::getModuleId(true), 'OnBeforeAgent', [
            'agentName' => 'createOrderApi',
            'orderId' => $orderId
        ]);

        if (!Config::checkFeature('modulesync')) {
            return false;
        }

        if ((int)$orderId <=0) {
            return false;
        }

        $logger = new Loger();

        try {

            if($order = \Bitrix\Sale\Order::load($orderId)) {

                if (OrderFilter::isFiltredByStatus($order->getField('STATUS_ID'))) {
                    $logger->addWarningMessage(LangMsg::get('LOG_AGENT_FILTERED_BY_STATUS'));
                    return false;
                }
                
                $customerOrder = new Customerorder($orderId);
    
                if ($customerOrder->isLoaded()) {
                    $logger->addMessageArray($customerOrder->getLogList());
                    $logger->addWarningMessage(LangMsg::get('LOG_AGENT_ORDER_FIND'));
                    return false;
                }
    
                if ($customerOrder->canCreateOrderInMs()) {
    
                    $customerOrderApi = new Customerorderapi($orderId);
    
                    $logger->addMessageArray($customerOrderApi->getLogList());
    
                    if ($customerOrderApi->isLoaded()) {
    
                        $needRefreshOrder = false;
    
                        $customerOrder = new Customerorder($orderId);
                        if ($customerOrder->isLoaded()) {
    
                            try {
    
                                if (Config::checkFeature('paysync')) {
                                    $customerOrder->checkAllPayments();
                                }
    
                                if (Config::getOption('demand_exchange_type', 'N') === 'default') {
                                    $customerOrder->checkDemand();
                                } else {
                                    $needPushDemandsToMs = Config::getOption('demand_exchange_type', 'N') === 'full' && Config::checkVectorFromBxToMs('demand');
                                    if ($needPushDemandsToMs) {
                                        $demand = new \Rbs\Moysklad\Entity\Demand($customerOrder);
                                        $demand->exportAllShipmentsToDemands();
                                    }
                                }
    
                            } catch (\Bitrix\Main\SystemException $e) {
                                $logger->addErrorMessage($e->getMessage());
                                $needRefreshOrder = true;
                            }
                            
                        } else {
                            $needRefreshOrder = true;
                        }
    
                        if($needRefreshOrder) {
                            Agent::set("onSaleOrderEntitySavedDelay({$orderId});");
                        }
    
                        $logger->addSuccessMessage(LangMsg::get('CREATED_IN_MS', ['#ID#' => $customerOrder->getOrder()->name]));
    
                        return false;
                    }
                } else {
                    $logger->addMessageArray($customerOrder->getLogList());
                }
    
    
                $logger->addInfoMessage(LangMsg::get('LOG_AGENT_ORDER_FIND_BX'));
    
                if ($order = \Bitrix\Sale\Order::load($orderId)) {
    
                    $dateUpdateOrderBx = new \DateTime($order->getDateInsert()->format('Y-m-d H:i:s'));
                    $dateUpdateOrderMs = new \DateTime();
                    $isDelayChange = $dateUpdateOrderMs->format('U') - $dateUpdateOrderBx->format('U') > Config::getAgentLiveTime();
    
                    if ($isDelayChange) {
                        $logger->addInfoMessage(LangMsg::get('LOG_AGENT_ORDER_BX_DELAY_FINISH'));
                        return false;
                    } else {
                        $logger->addInfoMessage(LangMsg::get('LOG_AGENT_ORDER_RESTART'));
                    }
                } else {
                    $logger->addErrorMessage(LangMsg::get('LOG_AGENT_ORDER_FIND_BX_FAIL'));
                    return false;
                }

                if ((int)$profileId > 0) {
                    return '\\' . __METHOD__ . "({$orderId}, {$profileId});";
                } else {
                    return '\\' . __METHOD__ . "({$orderId});";
                }
    
            } else {
                $logger->addErrorMessage(LangMsg::get('LOG_AGENT_ORDER_FIND_BX_FAIL'));
                return false;
            }
            
        } catch (\Throwable $e) {
            $logger->addErrorMessage($e->getMessage());
        } finally {
            $logger->exportLog(LangMsg::get('LOG_AGENT_ORDER_START', ['#ORDER_ID#' => $orderId]));
        }
    }
}
