<?php
namespace Sale\Handlers\Delivery;

use Bitrix\Sale;
use Bitrix\Main\Error;
use Bitrix\Sale\Result;
use Bitrix\Main\Loader;
use Bitrix\Main\Text\Encoding;
use Bitrix\Main\Localization\Loc;
use DellinShipping\NetworkService;
use Sale\Handlers\Delivery\Spsr\Request;
use Bitrix\Sale\Delivery\Tracking\Statuses;
use Bitrix\Sale\Delivery\Tracking\StatusResult;
use Bitrix\Sale\Delivery\Services;
use CJSCore;
use Bitrix\Main\Page\Asset;

Loc::loadMessages(__FILE__);

//\Bitrix\Main\Loader::includeModule('dellindev.shipment');
/**
 * Class SpsrTracking
 * @package \Sale\Handlers\Delivery;
 */
class DellinTracking extends \Bitrix\Sale\Delivery\Tracking\Base
{
    
    protected $deliveryService;


//     public function __construct(array $params, Services\Base $deliveryService)
//     {
//         global $APPLICATION;



//         $this->params = $params;
//         $this->deliveryService = $deliveryService;
//     //    $request =  $_REQUEST;//TODO ������� ���������� ������� ������

// //
// //        var_dump($deliveryService);
// //        die();

// //        if (strpos($APPLICATION->GetCurPage(),'/bitrix/admin/sale_order_shipment_edit.php') !== false) {
// //                $this->includeJSinAdminPage($deliveryService->getId(), $deliveryService->getConfigValues(), $request);
// //
// //        }


//     }


    /**
     * @return string
     */
    public function getClassTitle()
    {
        return Loc::getMessage("DELLINDEV_DELLIN");
    }

    /**
     * @return string
     */
    public function getClassDescription()
    {
        return Loc::getMessage("DELLINDEV_TRACKING");

    }

    /**
     * @param array $shipmentData
     * @return StatusResult.
     */

    public function getStatusShipment($shipmentData)
    {

        $result = $this->getDellinStatusInfo($shipmentData);


        return $result;
    }


    public function getDellinStatusInfo($shipmentData){

        $currentDeliveryMethod = $shipmentData['DELIVERY_ID'];
        $trackingID = $shipmentData['TRACKING_NUMBER'];

        $apikey = $this->getConfigApiKey($currentDeliveryMethod);

        if(isset($currentDeliveryMethod) && isset($trackingID) && isset($apikey)){
            $response = NetworkService::getShipmentStatus($apikey, $trackingID);

            $result = new StatusResult();

            $result->status = Statuses::NO_INFORMATION;
            $result->lastChangeTimestamp = self::getDate();
            $result->description = $response->state_name;
            $result->trackingNumber = $response->order_id;

            return $result;

        }

        throw new \Exception(Loc::getMessage("DELLINDEV_STATUS_IS_UNDEFINED"));
    }

    private function getConfigApiKey($method_id){
        if(isset($method_id)){
            return \Bitrix\Sale\Delivery\Services\Manager::getById($method_id)['CONFIG']['MAIN']['APIKEY'];
        }

        throw new \Exception(Loc::getMessage("DELLINDEV_APIKEY_IS_UNDEFINED"));
    }
    /**
     * @param array $shipmentsData
     * @return Result
     */
    public function getStatusesShipment(array $shipmentsData)
    {

        $result = new Result();

        if(empty($shipmentsData))
            return $result;

        /** @var SpsrHandler $parentService */
        $parentService = $this->deliveryService->getParentService();

        if(!$parentService)
            return $result;

        $reqParams = array();

        foreach($shipmentsData as $shipmentFields)
        {
            $shipment = $this->getShipment($shipmentFields['ORDER_ID'], $shipmentFields['SHIPMENT_ID']);

            /** @var \Bitrix\Sale\Result $res */
            $res = $parentService->getSidResult($shipment);

            if($res->isSuccess())
            {
                $data = $res->getData();
                $sid = $data[0];
            }
            else
            {
                $sid = "";
            }

            $icn = $parentService->getICN($shipment);

            if(!is_array($reqParams[$sid.'_'.$icn]))
                $reqParams[$sid.'_'.$icn] = array( 'SID' => $sid, 'ICN' => $icn, 'TRACK_NUMBERS' => array());

            $reqParams[$sid.'_'.$icn]['TRACK_NUMBERS'][] = $shipmentFields['TRACKING_NUMBER'];
        }

        foreach($reqParams as $params)
        {
            $res = $this->requestStatuses($params['SID'], $params['ICN'], $params['TRACK_NUMBERS']);

            if(!$res->isSuccess())
                $result->addErrors($res->getErrors());

            $result->setData($res->getData());
        }

        return $result;
    }

    public function requestStatuses($sid, $icn, $trackingNumbers)
    {
        $result = new Result();
        $request = new Request();
        $resultData = array();
        $reqRes = $request->getInvoicesInfo(
            $sid,
            $icn,
            LANGUAGE_ID,
            $trackingNumbers
        );

        /** @var \Bitrix\Sale\Result $reqRes */
        if($reqRes->isSuccess())
        {
            $invoicesInfo = $reqRes->getData();

            if(!empty($invoicesInfo['root']['#']['Invoices'][0]['#']['Invoice']) && is_array($invoicesInfo['root']['#']['Invoices'][0]['#']['Invoice']))
            {
                foreach($invoicesInfo['root']['#']['Invoices'][0]['#']['Invoice'] as $invoice)
                {
                    if(!in_array($invoice['@']['InvoiceNumber'], $trackingNumbers))
                        continue;

                    $r = new StatusResult();

                    if(!empty($invoice['#']['events'][0]['#']['event']) && is_array($invoice['#']['events'][0]['#']['event']))
                    {
                        $lastEvent = end($invoice['#']['events'][0]['#']['event']);
                        $r->status = $this->translateStatus($lastEvent['@']['EventNumCode']);
                        $r->description = $lastEvent['@']['EventName'];
                        $r->lastChangeTimestamp = $this->translateDate($lastEvent['@']['Date']);
                        $r->trackingNumber = $invoice['@']['InvoiceNumber'];
                    }
                    else
                    {
                        $r->addError(new Error(Loc::getMessage('SALE_DLV_SRV_SPSR_T_ERROR_DATA')));
                    }

                    $resultData[] = $r;
                }
            }
            elseif(!empty($invoicesInfo['root']['#']['NotFound'][0]['#']['Invoice']) && is_array($invoicesInfo['root']['#']['NotFound'][0]['#']['Invoice']))
            {
                foreach($invoicesInfo['root']['#']['NotFound'][0]['#']['Invoice'] as $invoice)
                {
                    $r = new StatusResult();
                    $r->trackingNumber = $invoice['@']['InvoiceNumber'];
                    $r->addError(
                        new Error(
                            self::utfDecode(
                                $invoice['@']['ErrorMessage']
                            )
                        )
                    );
                    $resultData[] = $r;
                }
            }
            else
            {
                $result->addError(new Error(Loc::getMessage('SALE_DLV_SRV_SPSR_T_ERROR_DATA')));
            }
        }
        else
        {
            $result->addErrors($reqRes->getErrors());

        }

        if(!empty($resultData))
            $result->setData($resultData);

        return $result;
    }

    protected static function translateStatus($externalStatus)
    {
        if($externalStatus == '')
            return Statuses::UNKNOWN;

        $statusMaps = array(
            Statuses::WAITING_SHIPMENT => array(),
            Statuses::ON_THE_WAY => array(2, 4, 6, 12, 13, 14, 17, 29, 30, 33, 34, 35, 39, 40, 41, 42, 43, 44, 45, 46,
                47, 48, 49, 50, 51, 53, 54, 105, 106, 107, 108, 109, 110, 111, 115, 119, 120, 122, 100, 32, 63, 64, 66,
                67, 74, 75, 76, 78, 79, 81, 84, 85, 86, 96),
            Statuses::ARRIVED => array(1, 8, 26, 31, ),
            Statuses::HANDED => array(15, 16, 27, 37, 55, 56, 57, 58, 59, 60, 61, 62, 92, 93, 112, 114, 116	),
            Statuses::PROBLEM => array(5, 7, 9, 10, 11, 18, 19, 20, 21, 22, 23, 24, 25, 28, 36, 38, 52, 113, 117, 123,
                124, 125, 126, 127, 128,129, 130, 131, 132, 133, 134, 135, 136, 137, 138, 139, 140, 141, 142, 65, 68, 69,
                70, 71, 72, 73, 77, 80, 82, 83, 87, 88, 89, 90, 91, 94, 95, 97, 98, 99, 101, 102, 103, 104, 143, 144,
                145, 146, 147, 148, 150, 175),
        );

        foreach($statusMaps as $internalStatus => $map)
            if(in_array($externalStatus, $map))
                return $internalStatus;

        return Statuses::UNKNOWN;
    }

    protected static function getDate()
    {
        $date = new \DateTime();
        return $date->getTimestamp();
    }

    /**
     * @return array
     */
    public function getParamsStructure()
    {
        return array();
    }

    protected static function utfDecode($str)
    {
        if(mb_strtolower(SITE_CHARSET) != 'utf-8')
            $str = Encoding::convertEncoding($str, 'UTF-8', SITE_CHARSET);

        return $str;
    }

    /**
     * @param string $trackingNumber
     * @return string Url were we can see tracking information
     */
    public function getTrackingUrl($trackingNumber = '')
    {
        return 'https://www.dellin.ru/tracker/?mode=search&rwID='.$trackingNumber;
    }




    /**
     * @param string $paramKey
     * @param string $inputName
     * @return string Html
     * @throws \Bitrix\Main\SystemException
     */
//     public function getEditHtml($paramKey, $inputName)
//     {
// //         $paramsStructure = $this->getParamsStructure();
// // //        var_dump($paramKey);
// // //        var_dump($inputName);
// // //        die();
// //         return \Bitrix\Sale\Internals\Input\Manager::getEditHtml(
// //             $inputName,
// //             $paramsStructure[$paramKey],
// //             $this->params[$paramKey]
// //         );
//     }
}