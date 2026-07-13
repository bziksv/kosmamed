<?php

namespace Viamodo\Telegramsalenotify;

use Bitrix\Main;
use Bitrix\Main\Config\Option;

class Handler
{

    public static function OnSaleOrderSaved(Main\Event $event): void
    {
        $isNew = $event->getParameter('IS_NEW');
        $order = $event->getParameter('ENTITY');
        $worker = new Worker($order);

        $siteId = $order->getSiteId();

        $bNewOrder = Option::get('viamodo.telegramsalenotify', 'new_order_' . $siteId, 'N');
        $bUpdateOrder = Option::get('viamodo.telegramsalenotify', 'update_order_' . $siteId, 'N');

        $bWork = false;

        if ($isNew == true && $bNewOrder == 'Y') {
            $worker->setAction('newOrder');
            $bWork = true;
        } elseif ($bUpdateOrder == 'Y') {
            $worker->setAction('updateOrder');
            $bWork = true;
        }

        if ($bWork === true) {
            $worker->exec();
        }
    }

    public static function OnSaleOrderPaid(Main\Event $event): void
    {
        $order = $event->getParameter('ENTITY');

        $siteId = $order->getSiteId();

        $bOrderPaid = Option::get('viamodo.telegramsalenotify', 'order_paid_' . $siteId, 'N');

        if ($bOrderPaid != 'Y') {
            return;
        }

        if ($order->isPaid() == false) {
            return;
        }

        $worker = new Worker($order);
        $worker->setAction('updateOrderPaid');
        $worker->exec();
    }

}
