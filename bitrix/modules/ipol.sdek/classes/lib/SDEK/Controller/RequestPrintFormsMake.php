<?php
namespace Ipolh\SDEK\SDEK\Controller;

use Ipolh\SDEK\Api\BadRequestException;
use Ipolh\SDEK\Api\Entity\UniversalPart\Order;
use Ipolh\SDEK\Api\Entity\UniversalPart\OrderList;

trait RequestPrintFormsMake
{
    /**
     * @var string[]|null
     */
    protected $uuids;

    /**
     * @var int[]|null
     */
    protected $cdekNumbers;

    /**
     * @return OrderList|null
     * @throws BadRequestException
     */
    protected function generateOrders()
    {
        $uuids = $this->uuids;
        $cdekNumbers = $this->cdekNumbers;
        $orderList = new OrderList();

        if (!empty($uuids)) {
            foreach ($uuids as $uuid) {
                $order = new Order();
                $order->setOrderUuid($uuid);
                $orderList->add($order);
            }
        }

        if (!empty($cdekNumbers)) {
            foreach ($cdekNumbers as $cdekNumber) {
                $order = new Order();
                $order->setCdekNumber($cdekNumber);
                $orderList->add($order);
            }
        }

        if ($orderList->getQuantity()) {
            return $orderList;
        }

        throw new BadRequestException('Not order uuids or cdekNumbers given');
    }
}