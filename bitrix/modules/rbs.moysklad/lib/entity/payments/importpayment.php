<?php
namespace Rbs\Moysklad\Entity\Payments;

use \Bitrix\Sale\Order;
use \Rbs\Moysklad\Config;
use \Rbs\Moysklad\Customerorder;
use \Rbs\Moysklad\Utils;

/**
 * Import payments from MS to BX (full sync by externalCode)
 */
class ImportPayment
{
    /** @var Customerorder */
    private $co;

    /** @var ExportPayment */
    private $export;

    public function __construct(Customerorder $customerOrder)
    {
        $this->co = $customerOrder;
        $this->export = new ExportPayment($customerOrder);
    }

    /**
     * Create BX payment from MS document
     * @see Customerorder::createBxPayment()
     */
    public function createBxPayment($paymentItem, $operation)
    {
        if ($this->co->isLoaded() && !empty($paymentItem)) {
            if ($paymentCollection = $this->co->orderBx->getPaymentCollection()) {
                $payment = $paymentCollection->createItem();

                $defaultPaySystemId = $this->export->getPaySystemIdFromPaymentMs($paymentItem);

                if ((int)$defaultPaySystemId <= 0) {
                    return false;
                }

                if ($paySystemService = \Bitrix\Sale\PaySystem\Manager::getObjectById($defaultPaySystemId)) {
                    $payment->setFields(array(
                        'PAY_SYSTEM_ID' => $paySystemService->getField("PAY_SYSTEM_ID"),
                        'PAY_SYSTEM_NAME' => $paySystemService->getField("NAME"),
                        'SUM' => $operation->linkedSum / 100,
                        'PAID' => $paymentItem->applicable ? 'Y' : 'N',
                        'XML_ID' => $paymentItem->externalCode
                    ));

                    $this->co->saveResult();

                    return $payment;
                }
            }
        }

        return false;
    }

    /**
     * Update BX payment from MS document
     * @see Customerorder::updateBxPayment()
     */
    public function updateBxPayment($paymentItem, $opertaion)
    {
        $paymentResult = null;

        if ($this->co->isLoaded() && !empty($paymentItem->externalCode)) {

            if ($paymentItem->deleted !== null) {
                $this->deleteBxPayment($paymentItem);
                if (!$this->co->orderBx->isPaid()) {
                    $this->recalcFirstPayment();
                }
                return;
            }

            $isFindedPayment = false;
            $paymentCountFind = 0;

            if ($paymentCollection = $this->co->orderBx->getPaymentCollection()) {
                foreach ($paymentCollection as $payment) {
                    $paymentCountFind++;
                    if ((string)$payment->getField(Config::getSearchFieldId('payment')) === (string)$paymentItem->externalCode) {
                        $isFindedPayment = true;
                        $paymentChangeStack = [];

                        if ($payment->isPaid() && !$paymentItem->applicable) {
                            $paymentChangeStack['PAID'] = 'N';
                        } elseif (!$payment->isPaid() && $paymentItem->applicable) {
                            $paymentChangeStack['PAID'] = 'Y';
                        }

                        $paymentSum = (int)round($payment->getSum() * 100, 0);
                        if ($paymentSum !== (int)$opertaion->linkedSum) {
                            $paymentChangeStack['SUM'] = $opertaion->linkedSum / 100;
                        }

                        if (Config::checkFeature('paysmethodsync')) {
                            $defaultPaySystemId = $this->export->getPaySystemIdFromPaymentMs($paymentItem);
                            if ($defaultPaySystemId > 0) {
                                if ((int)$defaultPaySystemId !== (int)$payment->getField('PAY_SYSTEM_ID')) {
                                    if ($paySystemService = \Bitrix\Sale\PaySystem\Manager::getObjectById($defaultPaySystemId)) {
                                        $paymentChangeStack['PAY_SYSTEM_ID'] = $paySystemService->getField("PAY_SYSTEM_ID");
                                        $paymentChangeStack['PAY_SYSTEM_NAME'] = $paySystemService->getField("NAME");
                                    }
                                }
                            }
                        }

                        $payment->setFields($paymentChangeStack);
                        $this->co->saveResult();

                        $paymentResult = $payment;
                    }
                }
            }

            if (!$isFindedPayment && !$this->co->orderBx->isPaid()) {
                $paymentResult = $this->createBxPayment($paymentItem, $opertaion);
                $paymentCountFind++;
            }

            if ($paymentCountFind > 1) {
                $this->recalcFirstPayment();
            }
        }

        return $paymentResult;
    }

    /**
     * Delete BX payment
     * @see Customerorder::deleteBxPayment()
     */
    public function deleteBxPayment($paymentItem)
    {
        if ($this->co->isLoaded() && !empty($paymentItem->externalCode)) {
            if ($paymentCollection = $this->co->orderBx->getPaymentCollection()) {
                $searchField = Config::getSearchFieldId('payment');
                foreach ($paymentCollection as $payment) {
                    if ((string)$payment->getField($searchField) === (string)$paymentItem->externalCode) {
                        $payment->setField('PAID', 'N');
                        $payment->delete();
                        $this->co->saveResult();
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Recalculate first payment sum in BX
     * @see Customerorder::recalcFirstPayment()
     */
    public function recalcFirstPayment()
    {
        if ($this->co->isLoaded() && Config::checkFeature('paysrecalc')) {
            if ($paymentCollection = $this->co->orderBx->getPaymentCollection()) {
                $allPaymentsSum = 0;
                $countPayment = 0;
                $firstOrderPayment = null;

                foreach ($paymentCollection as $payment) {
                    $countPayment++;
                    if ($firstOrderPayment === null) {
                        $firstOrderPayment = $payment;
                    } else {
                        $allPaymentsSum += $payment->getField('SUM');
                    }
                }

                $firstShouldSum = $this->co->orderBx->getPrice() - $allPaymentsSum;

                if ($firstShouldSum <= 0) {
                    $this->export->deletePaymentByExternalCode((string)$firstOrderPayment->getField(Config::getSearchFieldId('payment')));
                    $paymentItem = (object)[
                        'externalCode' => (string)$firstOrderPayment->getField(Config::getSearchFieldId('payment'))
                    ];
                    $this->deleteBxPayment($paymentItem);
                }

                if (
                    $firstShouldSum > 0 &&
                    $countPayment > 1 &&
                    $firstOrderPayment !== null &&
                    !$firstOrderPayment->isPaid() &&
                    (double)$firstShouldSum !== (double)$firstOrderPayment->getField('SUM')
                ) {
                    $firstOrderPayment->setFields([
                        'SUM' => $firstShouldSum
                    ]);
                    $this->co->saveResult();
                    $this->export->checkPayment($firstOrderPayment);
                }
            }
        }
    }

    /**
     * Check payments across all orders (remove duplicates)
     * @see Customerorder::checkAllOrdersPayments()
     */
    public static function checkAllOrdersPayments($paymentItem = null, $orderBxChecked)
    {
        if (Utils::is_count($orderBxChecked)) {

            $searchField = Config::getSearchFieldId('payment');

            $payments = \Bitrix\Sale\Internals\PaymentTable::getList([
                'filter' => [
                    $searchField => (string)$paymentItem->externalCode,
                    '!ORDER_ID' => $orderBxChecked
                ]
            ])->fetchAll();

            if (Utils::is_count($payments)) {
                foreach ($payments as $payment) {
                    if($order = Order::load($payment['ORDER_ID'])) {
                        if ($paymentCollection = $order->getPaymentCollection()) {
                            foreach ($paymentCollection as $paymentItem) {
                                if ((string)$payment[$searchField] === (string)$paymentItem->getField($searchField)) {
                                    $paymentItem->delete();
                                    $order->save();
                                }
                            }
                        }
                    }
                }
            }

        }
    }

    /**
     * Get BX payment system ID from MS payment data
     * @see ExportPayment::getPaySystemIdFromPaymentMs()
     */
    public function getPaySystemIdFromPaymentMs($paymentItem)
    {
        return $this->export->getPaySystemIdFromPaymentMs($paymentItem);
    }
}
