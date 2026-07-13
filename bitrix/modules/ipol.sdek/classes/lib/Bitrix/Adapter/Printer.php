<?php
namespace Ipolh\SDEK\Bitrix\Adapter;

use Ipolh\SDEK\Bitrix\Tools;
use Ipolh\SDEK\Core\Entity\Result\Error;
use Ipolh\SDEK\Core\Entity\Result\Result;
use Ipolh\SDEK\Core\Entity\Result\Warning;
use Ipolh\SDEK\PrintHandler;

use Bitrix\Sale\Shipment;

class Printer
{
    /**
     * @param array $orders
     * @return Result
     */
    public static function getPrintOrders($orders)
    {
        $result = new Result();
        $data   = ['EXISTED' => [PrintHandler::WORK_MODE_ORDER => [], PrintHandler::WORK_MODE_SHIPMENT => []], 'NOT_FOUND' => []];

        $dbOrders = \sqlSdekOrders::select([], ['ORDER_ID' => $orders, 'SOURCE' => 0]);
        while ($tmp = $dbOrders->Fetch()) {
            if (!$tmp['SDEK_ID'])
                $data['NOT_FOUND'][$tmp['ORDER_ID']] = true;
            else
                $data['EXISTED'][PrintHandler::WORK_MODE_ORDER][] = $tmp['ORDER_ID'];
        }

        foreach ($orders as $orderId) {
            if (!in_array($orderId, $data['EXISTED'][PrintHandler::WORK_MODE_ORDER]))
                $data['NOT_FOUND'][$orderId] = true;
        }

        if (count($data['NOT_FOUND']) && \sdekHelper::isConverted()) {
            $shipments = [];
            $dbShipments = Shipment::getList(['filter' => ['=ORDER_ID' => array_keys($data['NOT_FOUND'])], 'select' => ['ID', 'ORDER_ID']]);
            while ($tmp = $dbShipments->Fetch()) {
                $shipments[$tmp['ID']] = $tmp['ORDER_ID'];
            }

            $dbOrders = \sqlSdekOrders::select([], ['ORDER_ID' => array_keys($shipments), 'SOURCE' => 1]);
            while ($tmp = $dbOrders->Fetch()) {
                if ($tmp['SDEK_ID']) {
                    $data['EXISTED'][PrintHandler::WORK_MODE_SHIPMENT][] = $tmp['ORDER_ID'];
                    unset($data['NOT_FOUND'][$shipments[$tmp['ORDER_ID']]]);
                }
            }
        }

        if (empty($data['EXISTED'][PrintHandler::WORK_MODE_ORDER]) && empty($data['EXISTED'][PrintHandler::WORK_MODE_SHIPMENT])) {
            $result->addError(new Error(Tools::getMessage('ERR_NOT_FOUND_ALL_ORDERS').implode(', ', array_keys($data['NOT_FOUND']))));
        } else if (!empty($data['NOT_FOUND'])) {
            $result->addWarning(new Warning(Tools::getMessage('ERR_NOT_FOUND_SOME_ORDERS').implode(', ', array_keys($data['NOT_FOUND']))));
        }

        $result->setData($data);

        return $result;

    }

    /**
     * @param array $orders
     * @return Result
     */
    public static function prepareOrdersData($orders)
    {
        $result = new Result();
        $data   = ['ORDERS' => [], 'EXISTED' => [], 'NOT_FOUND' => []];

        $defaultAccountId = \sdekHelper::getBasicAuth(true);

        foreach ($orders as $workMode => $ids) {
            if (!is_array($ids)) {
                $orders[$workMode] = [$ids];
            }

            $data['EXISTED'][$workMode] = [];

            $dbOrders = \sqlSdekOrders::select([], ['ORDER_ID' => $ids, 'SOURCE' => ($workMode == PrintHandler::WORK_MODE_ORDER) ? 0 : 1]);
            while ($tmp = $dbOrders->Fetch()) {
                if ($tmp['SDEK_ID']) {
                    $accountId = (int)(isset($tmp['ACCOUNT']) ? $tmp['ACCOUNT'] : $defaultAccountId);

                    $data['ORDERS'][$accountId][] = $tmp['SDEK_ID'];
                    $data['EXISTED'][$workMode][] = $tmp['ORDER_ID'];
                }
            }

            $data['NOT_FOUND'][$workMode] = array_diff($orders[$workMode], $data['EXISTED'][$workMode]);

            if (!empty($data['NOT_FOUND'][$workMode])) {
                $warning = ($workMode == PrintHandler::WORK_MODE_ORDER) ? Tools::getMessage('ERR_NOT_FOUND_ORDERS') : Tools::getMessage('ERR_NOT_FOUND_SHIPMENTS');
                $result->addWarning(new Warning($warning.implode(', ', $data['NOT_FOUND'][$workMode])));
            }
        }

        if (empty($data['ORDERS'])) {
            $result->addError(new Error('No orders data founded.'));
        }

        $result->setData($data);

        return $result;
    }
}