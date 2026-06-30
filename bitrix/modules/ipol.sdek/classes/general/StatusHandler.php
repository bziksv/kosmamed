<?php
namespace Ipolh\SDEK;

use Ipolh\SDEK\Bitrix\Controller\Order;
use Ipolh\SDEK\Bitrix\Entity\cache;
use Ipolh\SDEK\Bitrix\Entity\encoder;
use Ipolh\SDEK\Bitrix\Entity\Options;
use Ipolh\SDEK\Bitrix\Tools;
use Ipolh\SDEK\Core\Entity\BasicResponse;
use Ipolh\SDEK\Core\Entity\Collection;
use Ipolh\SDEK\Core\Entity\Result\Error;
use Ipolh\SDEK\Core\Entity\Result\ErrorCollection;
use Ipolh\SDEK\Core\Entity\Result\Result;
use Ipolh\SDEK\Core\Entity\Result\Warning;
use Ipolh\SDEK\SDEK\SdekApplication;

use Bitrix\Main\Type\DateTime;

class StatusHandler extends abstractGeneral
{
    /**
     * Choose all orders with uid's and without sdek_id to fill it or cry loud
     */
    public static function getSendedOrdersState()
    {
        $obOrders = \sqlSdekOrders::select(array(),array('!SDEK_UID'=>false));
        $arOrders = array();
        while ($obOrder = $obOrders->getNext()){
            if(!$obOrder['SDEK_ID']){
                $acc = \sqlSdekLogs::getById($obOrder['ACCOUNT']);

                if($acc && $acc['ACTIVE'] === 'Y') {
                    $arOrders [$obOrder['ID']] = array(
                        'uid' => $obOrder['SDEK_UID'],
                        'acc' => $acc['ACCOUNT'],
                        'scr' => $acc['SECURE'],
                        'accID' => $obOrder['ACCOUNT'],
                        'oid' => $obOrder['ORDER_ID'],
                        'src' => $obOrder['SOURCE']
                    );
                }
            }
        }

        $arControllers = array();

        if(!empty($arOrders)){
            foreach ($arOrders as $dbId => $arOrder){
                if(!array_key_exists($arOrder['accID'],$arControllers)) {
                    $app = self::makeApplication($arOrder['acc'], $arOrder['scr']);
                    $arControllers[$arOrder['accID']] = new Order($app);
                }

                $result = self::_getSenderOrderState($arOrder,$arControllers[$arOrder['accID']]);
            }
        }
    }

    /**
     * @param $oId
     * @param string $mode
     * @return BasicResponse
     * checks either order with corr oId was accepted by sdek - or smth is wrong
     */
    public static function getSendedOrderStateByOid($oId, $mode='order')
    {
        $arOrder = ($mode==='order') ? \sqlSdekOrders::GetByOI($oId) : \sqlSdekOrders::GetBySI($oId);
        if($arOrder && $arOrder['SDEK_UID']){
            $result = self::getSendedOrderState($arOrder['SDEK_UID']);
        } else {
            $result = new BasicResponse();
            $errCol = new Collection('error');
            $errCol->add('No order with filled uid found with id'.$oId);
            $result->setSuccess(false)->setResponse(false)->setError($errCol);
        }

        return $result;
    }

    /**
     * Checks either order with uid was accepted by sdek - or something is wrong
     * @param $uid
     * @return BasicResponse
     */
    public static function getSendedOrderState($uid)
    {
        $obOrder = \sqlSdekOrders::GetByUId($uid);
        if($obOrder){
            $acc = \sqlSdekLogs::getById($obOrder['ACCOUNT']);

            $arOrder = array(
                'uid' => $obOrder['SDEK_UID'],
                'acc' => $acc['ACCOUNT'],
                'scr' => $acc['SECURE'],
                'accID' => $obOrder['ACCOUNT'],
                'oid' => $obOrder['ORDER_ID'],
                'src' => $obOrder['SOURCE']
            );

            $app = self::makeApplication($arOrder['acc'], $arOrder['scr']);
            $controller = new Order($app);

            $result = self::_getSenderOrderState($arOrder,$controller);
        } else {
            $result = new BasicResponse();
            $errCol = new Collection('error');
            $errCol->add('No order found with uid '.$uid);
            $result->setSuccess(false)->setResponse(false)->setError($errCol);
        }

        return $result;
    }

    /**
     * Checks state for chosen order, sets stuff for tracking number, adds in table
     * @param $arOrder - array of type uid,oid,src
     * @param Order $controller
     */
    protected static function _getSenderOrderState($arOrder, $controller)
    {
        /** @var BasicResponse $check */
        $check = $controller->checkOrderSendState($arOrder['uid']);

        $obRet = new BasicResponse();
        $obRet->setSuccess($check->isSuccess());

        if($check->isSuccess())
        {
            $sdekNumber = $check->getResponse()->getField('cdekNumber');
            if(
                $check->getResponse()->getField('state') === 'SUCCESSFUL' &&
                $sdekNumber
            ){
                \sqlSdekOrders::updateStatus(array(
                    "ORDER_ID" => $arOrder['oid'],
                    "SOURCE"   => $arOrder['src'],
                    "STATUS"   => "OK",
                    "SDEK_ID"  => $sdekNumber,
                    "MESSAGE"  => "",
                    "OK"       => true
                ));
                $obRet->setResponse($sdekNumber);

                $statusOption = !$arOrder['src'] ? 'statusOK' : 'stShipmentOK';
                $status = option::get($statusOption);
                if ($status) {
                    if (!$arOrder['src']) {
                        $order = \CSaleOrder::GetByID($arOrder['oid']);
                        if ($order['STATUS_ID'] != $status) {
                            \CSaleOrder::StatusOrder($arOrder['oid'], $status);
                        }
                    } else if (\sdekHelper::isConverted()) {
                        $shipment = \Bitrix\Sale\Shipment::getList(array('filter' => array('ID' => $arOrder['oid'])))->Fetch();
                        if ($shipment['STATUS_ID'] != $status) {
                            $order = \Bitrix\Sale\Order::load($shipment['ORDER_ID']);
                            $shipmentCollection = $order->getShipmentCollection();
                            $shipment = $shipmentCollection->getItemById($arOrder['oid']);
                            $shipment->setField('STATUS_ID', $status);
                            $order->save();
                        }
                    }
                }
                \sdekdriver::setOrderTrackingNumber($arOrder['oid'],(!$arOrder['src'])?'order':'shipment',$sdekNumber);
            } elseif($check->getResponse()->getField('state') === 'INVALID'){
                $obRet->setError($check->getError());
                $obRet->setResponse(false);

                $arErrors = array();
                if($check->getError()){
                    $check->getError()->reset();
                    while($obErr = $check->getError()->getNext()){
                        $arErrors [] = $obErr;
                    }
                }

                \sqlSdekOrders::updateStatus(array(
                    "ORDER_ID" => $arOrder['oid'],
                    "SOURCE"   => $arOrder['src'],
                    "STATUS"   => "ERROR",
                    "SDEK_ID"  => false,
                    "SDEK_UID" => '',
                    "MESSAGE"  => serialize(\sdekHelper::zaDEjsonit($arErrors)),
                    "OK"       => false
                ));
            }
        } else {
            $obRet->setError($check->getError());
        }

        return $obRet;
    }

    public static function checkCdekNumber(){
        $oId    = ($_REQUEST['mode'] === 'order') ? $_REQUEST['orderId'] : $_REQUEST['shipment'];
        $return = self::getSendedOrderStateByOid($oId,$_REQUEST['mode']);

        $obReturn = array('cdek_number'=>false,'error'=>false);
        if($return->isSuccess() && $return->getResponse()){
            $obReturn['cdek_number'] = $return->getResponse();
        } elseif($return->getError()){
            $arError = array();
            $return->getError()->reset();
            while ($obErr = $return->getError()->getNext()){
                $arError []= $obErr;
            }

            $obReturn['error'] = $arError;
        }

        echo json_encode(\sdekHelper::zaDEjsonit($obReturn));
    }

    /**
     * @return int
     * returns number of active orders - which we need to check via syncronization
     */
    public static function getNumberOfActiveOrders(){
        $orderStatusesUptime = (int)option::get('orderStatusesUptime');
        if ($orderStatusesUptime < 1)
            $orderStatusesUptime = 60;

        $dbOrders = \sqlSdekOrders::select(
            array('UPTIME', 'ASC'),
            array('OK' => true, 'STATUS' => array('OK', 'DELETE', 'STORE', 'TRANZT', 'CORIER', 'PVZ'), '>UPTIME' => strtotime('-'.$orderStatusesUptime.' days'))
        );

        return $dbOrders->SelectedRowsCount();
    }

    /**
     * Checks state of given Order. Order must be successfully sent to CDEK before check.
     * @param array $request
     * @return Result
     */
    public static function getOrderState($request)
    {
        $result = new Result();
        $resultData = [];

        if (!empty($request['DispatchNumber'])) {
            $order = \sqlSdekOrders::select([], ['SDEK_ID' => $request['DispatchNumber']])->Fetch();
            if (!empty($order) && !empty($order['ID'])) {
                $account = \sqlSdekLogs::getById($order['ACCOUNT']);
                if (!empty($account)) {
                    // 2.0 only
                    $application = new \Ipolh\SDEK\SDEK\SdekApplication(
                        $account['ACCOUNT'], $account['SECURE'],
                        false,
                        10,
                        new \Ipolh\SDEK\Bitrix\Entity\encoder(),
                        new \Ipolh\SDEK\Bitrix\Entity\cache()
                        //, new \Ipolh\SDEK\Admin\IvanInlineLoggerController()
                    );
                    $controller = new \Ipolh\SDEK\Bitrix\Controller\Order($application);

                    $getInfoResult = $controller->getOrderInfoByNumber($order['SDEK_ID']);
                    if ($getInfoResult->isSuccess()) {
                        // This one for \sdekOption::setOrderStates()
                        $successfulOrders = [];

                        $data = $getInfoResult->getData();
                        if (!empty($data['STATUSES']) && is_array($data['STATUSES'])) {
                            $resultData['STATUS_CODE'] = $data['STATUSES'][0]['STATUS'];
                            $resultData['STATUS_DATE'] = DateTime::createFromTimestamp($data['STATUSES'][0]['DATETIME']);
                            $resultData['NUMBER']      = $data['NUMBER'];

                            // 2.0 to 1.5 compatibility
                            $compatibleStatus = self::getStatusNumberByCode($data['STATUSES'][0]['STATUS']);
                            if (!empty($compatibleStatus)) {
                                $successfulOrders[] = [
                                    'DispatchNumber' => $order['SDEK_ID'],
                                    'State' => $compatibleStatus['ID'],
                                    'Number' => $data['NUMBER'],
                                    'Description' => $compatibleStatus['DESCR']
                                ];
                            }
                        } else {
                            $result->addError(new Error('Empty statuses list returns for CDEK number '.$order['SDEK_ID']));
                        }

                        if (!empty($successfulOrders)) {
                            \sdekOption::setOrderStates($successfulOrders);
                        }
                    } else {
                        $result->addErrors($getInfoResult->getErrors());
                    }
                } else {
                    $result->addError(new Error(Tools::getMessage('MESS_ORDER_INFO_UNKNOWN_ACCOUNT')));
                }
            } else {
                $result->addError(new Error('Get state failed cause no Order found by given CDEK_ID.'));
            }
        } else {
            $result->addError(new Error('Get state failed cause no Order CDEK_ID given.'));
        }
        $result->setData($resultData);

        return $result;
    }

    /**
     * Ajax wrapper for getOrderState
     * @param array $request
     * @return void
     */
    public static function getOrderStateRequest($request)
    {
        $result = ['success' => false, 'errors' => 'Unknown error.', 'status' => false];

        if (Tools::isModuleAjaxRequest()) {
            $stateResult = self::getOrderState($request);

            $result['success'] = $stateResult->isSuccess();
            $result['errors']  = $stateResult->getErrors()->isEmpty() ? '' : $stateResult->getErrorsString(Result::SEPARATOR_NEW_LINE);
            $result['status']  = $stateResult->getData()['STATUS_CODE'] ?: false;
        }

        echo Tools::jsonEncode($result);
    }

    /**
     * Checks states of Orders
     * @return Result
     */
    public static function getOrderStates()
    {
        $result = new Result();
        $resultData = ['ORDERS' => []];

        if (\sqlSdekOrders::getDataCount() > 0) {
            $orderStatusesLimit = (int)option::get('orderStatusesLimit');
            if ($orderStatusesLimit < 1)
                $orderStatusesLimit = 100;

            $orderStatusesUptime = (int)option::get('orderStatusesUptime');
            if ($orderStatusesUptime < 1)
                $orderStatusesUptime = 60;

            // Maximum orders processed per one call of Order::getOrderInfoByNumberMulti()
            $orderMultiLimit = 10;

            $dbData = \sqlSdekOrders::select(
                ['UPTIME', 'ASC'],
                ['OK' => true, 'STATUS' => ['OK', 'DELETE', 'STORE', 'TRANZT', 'CORIER', 'PVZ'], '>UPTIME' => strtotime('-'.$orderStatusesUptime.' days')],
                ['nPageSize' => $orderStatusesLimit, 'iNumPage' => 1]
            );

            $tmpOrders = [];
            while ($tmp = $dbData->Fetch()) {
                if (!empty($tmp['ACCOUNT']) && !empty($tmp['SDEK_ID'])) {
                    $tmpOrders[$tmp['ACCOUNT']][] = $tmp['SDEK_ID'];
                }
            }
            unset($dbData);

            $orders = [];
            foreach ($tmpOrders as $accountId => $cdekIds) {
                $page = 0;
                $i = 0;
                foreach ($cdekIds as $cdekId) {
                    $orders[$accountId][$page][] = $cdekId;
                    $i++;

                    if ($i === $orderMultiLimit) {
                        $i = 0;
                        $page++;
                    }
                }
            }
            unset($tmpOrders);

            if (!empty($orders)) {
                foreach ($orders as $accountId => $ordersData) {
                    $account = \sqlSdekLogs::getById($accountId);
                    if (!empty($account)) {
                        // 2.0 only
                        $application = new SdekApplication(
                            $account['ACCOUNT'], $account['SECURE'],
                            false,
                            10,
                            new \Ipolh\SDEK\Bitrix\Entity\encoder(),
                            new \Ipolh\SDEK\Bitrix\Entity\cache()
                            // , new \Ipolh\SDEK\Admin\IvanInlineLoggerController()
                        );
                        $controller = new Order($application);

                        foreach ($ordersData as $page => $ordersPack) {
                            // This one for \sdekOption::setOrderStates()
                            $successfulOrders = [];

                            $getInfoResult = $controller->getOrderInfoByNumberMulti($ordersPack);

                            foreach ($ordersPack as $cdekNumber) {
                                $resultData['ORDERS'][$cdekNumber] = ['IS_SUCCESS' => $getInfoResult->isSuccess(), 'ERRORS' => new ErrorCollection()];

                                if ($getInfoResult->isSuccess()) {
                                    $ordersResults = $getInfoResult->getData()['ORDERS'];
                                    if (array_key_exists($cdekNumber, $ordersResults)) {
                                        $orderResult = $ordersResults[$cdekNumber];

                                        if (!empty($orderResult['STATUSES']) && is_array($orderResult['STATUSES'])) {
                                            $resultData['ORDERS'][$cdekNumber]['STATUS_CODE'] = $orderResult['STATUSES'][0]['STATUS'];
                                            $resultData['ORDERS'][$cdekNumber]['STATUS_DATE'] = DateTime::createFromTimestamp($orderResult['STATUSES'][0]['DATETIME']);

                                            $resultData['ORDERS'][$cdekNumber]['NUMBER'] = $orderResult['NUMBER'];

                                            // 2.0 to 1.5 compatibility
                                            $compatibleStatus = self::getStatusNumberByCode($orderResult['STATUSES'][0]['STATUS']);
                                            if (!empty($compatibleStatus)) {
                                                $successfulOrders[] = [
                                                    'DispatchNumber' => $cdekNumber,
                                                    'State' => $compatibleStatus['ID'],
                                                    'Number' => $orderResult['NUMBER'],
                                                    'Description' => $compatibleStatus['DESCR']
                                                ];
                                            }
                                        } else if (!$orderResult['ERRORS']->isEmpty()) {
                                            $resultData['ORDERS'][$cdekNumber]['ERRORS']->append($orderResult['ERRORS']);
                                            $resultData['ORDERS'][$cdekNumber]['IS_SUCCESS'] = false;
                                        } else {
                                            $resultData['ORDERS'][$cdekNumber]['ERRORS']->add(new Error('Empty error list and requests data returns for CDEK number '.$cdekNumber));
                                            $resultData['ORDERS'][$cdekNumber]['IS_SUCCESS'] = false;
                                        }
                                    } else {
                                        // Near impossible, but who cares
                                        $resultData['ORDERS'][$cdekNumber]['ERRORS']->add(new Error('No order info returns for CDEK number '.$cdekNumber));
                                    }
                                } else {
                                    // Some serious troubles with app, like bad auth data
                                    $resultData['ORDERS'][$cdekNumber]['ERRORS']->append($getInfoResult->getErrors());
                                }
                            }

                            if (!empty($successfulOrders)) {
                                \sdekOption::setOrderStates($successfulOrders);
                            }
                        }
                    } else {
                        $result->addError(new Error(Tools::getMessage('MESS_ORDER_INFO_UNKNOWN_ACCOUNT')));
                    }
                }
            }
        } else {
            $result->addWarning(new Warning('CDEK Orders table empty.'));
        }
        $result->setData($resultData);

        if (!\sdekOption::$ERROR_REF) {
            option::set('statCync', time());
        }

        return $result;
    }

    /**
     * Get corresponding 1.5 CDEK status for given 2.0 status code
     * @param string $cdekStatusCode
     * @return array
     */
    protected static function getStatusNumberByCode($cdekStatusCode)
    {
        $map = [
            // 'ACCEPTED' => null,

            'CREATED' => 1,
            'REMOVED' => 2,
            'RECEIVED_AT_SHIPMENT_WAREHOUSE' => 3,
            'DELIVERED' => 4,
            'NOT_DELIVERED' => 5,

            'READY_TO_SHIP_AT_SENDING_OFFICE' => 6,
            'READY_FOR_SHIPMENT_IN_TRANSIT_CITY' => 6,
            'READY_FOR_SHIPMENT_IN_SENDER_CITY' => 6,

            'PASSED_TO_CARRIER_AT_SENDING_OFFICE' => 7,
            'TAKEN_BY_TRANSPORTER_FROM_SENDER_CITY' => 7,

            'SENT_TO_RECIPIENT_CITY' => 8,

            'MET_AT_RECIPIENT_OFFICE' => 9,
            'ACCEPTED_IN_RECIPIENT_CITY' => 9,

            'ACCEPTED_AT_RECIPIENT_CITY_WAREHOUSE' => 10,
            'TAKEN_BY_COURIER' => 11,
            'ACCEPTED_AT_PICK_UP_POINT' => 12,
            'ACCEPTED_AT_TRANSIT_WAREHOUSE' => 13,
            'RETURNED_TO_SENDER_CITY_WAREHOUSE' => 16,
            'RETURNED_TO_TRANSIT_WAREHOUSE' => 17,
            'RETURNED_TO_RECIPIENT_CITY_WAREHOUSE' => 18,
            'READY_TO_SHIP_IN_TRANSIT_OFFICE' => 19,

            'PASSED_TO_CARRIER_AT_TRANSIT_OFFICE' => 20,
            'TAKEN_BY_TRANSPORTER_FROM_TRANSIT_CITY' => 20,

            'SEND_TO_TRANSIT_OFFICE' => 21,
            'SENT_TO_TRANSIT_CITY' => 21,

            'MET_AT_TRANSIT_OFFICE' => 22,
            'ACCEPTED_IN_TRANSIT_CITY' => 22,

            'SENT_TO_SENDER_CITY' => 27,
            'MET_AT_SENDING_OFFICE' => 28,

            'ENTERED_TO_OFFICE_TRANSIT_WAREHOUSE' => 1000,
            'ENTERED_TO_DELIVERY_WAREHOUSE' => 1000,
            'ENTERED_TO_WAREHOUSE_ON_DEMAND' => 1000,
            'IN_CUSTOMS_INTERNATIONAL' => 1000,
            'SHIPPED_TO_DESTINATION' => 1000,
            'PASSED_TO_TRANSIT_CARRIER' => 1000,
            'IN_CUSTOMS_LOCAL' => 1000,
            'CUSTOMS_COMPLETE' => 1000,

            'POSTOMAT_POSTED' => 12, // CDEK original is 1000
            'POSTOMAT_SEIZED' => 1000,
            'POSTOMAT_RECEIVED' => 4, // CDEK original is 1000

            // 'ACCEPTED_IN_SENDER_CITY' => null,
        ];

        return (array_key_exists($cdekStatusCode, $map) ? ['ID' => $map[$cdekStatusCode], 'DESCR' => Tools::getMessage('STATUS_ORDER_'.$cdekStatusCode)] : []);
    }
}