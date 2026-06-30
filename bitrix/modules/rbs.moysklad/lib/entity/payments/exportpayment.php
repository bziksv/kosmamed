<?php
namespace Rbs\Moysklad\Entity\Payments;

use \Bitrix\Sale\Payment;
use \Rbs\Moysklad\ApiNew;
use \Rbs\Moysklad\Config;
use \Rbs\Moysklad\Customerorder;
use \Rbs\Moysklad\Helper;
use \Rbs\Moysklad\LangMsg;
use \Rbs\Moysklad\Utils;

/**
 * Export payments from BX to MS (full sync mode by externalCode)
 */
class ExportPayment
{
    /** @var Customerorder */
    private $co;

    public function __construct(Customerorder $customerOrder)
    {
        $this->co = $customerOrder;
    }

    /**
     * Check specific payment by ID
     * @see Customerorder::checkPaymentById()
     */
    public function checkPaymentById(int $paymentId)
    {
        if ($this->co->isLoaded()) {
            if ($paymentList = $this->co->orderBx->getPaymentCollection()) {
                foreach ($paymentList as $payment) {
                    if ((int)$paymentId === (int)$payment->getId()) {
                        return $this->checkPayment($payment);
                    }
                }
            }
        }
    }

    /**
     * Full payment sync BX ? MS (full mode)
     * @see Customerorder::checkPayment() — 'full' branch
     */
    public function checkPayment(Payment $payment)
    {
        if ($this->co->isLoaded() && !$this->co->hasErrors()) {
            $msPayment = $this->findPaymentByExternalCode($payment);
            if ($msPayment === false) {
                return;
            } else {
                if ($msPayment === null) {
                    $msPayment = $this->setPayment($payment, $payment->isPaid());
                }
                if (!empty($msPayment->meta->href)) {
                    $this->checkDiffOfPayments($msPayment, $payment);
                }
            }
        }
    }

    /**
     * Check all order payments (export)
     * @see Customerorder::checkAllPayments()
     */
    public function checkAllPayments()
    {
        if ($this->co->isLoaded() && !$this->co->hasErrors()) {
            if ($paymentCollection = $this->co->orderBx->getPaymentCollection()) {
                foreach ($paymentCollection as $payment) {
                    $this->checkPayment($payment);
                }
            }
        }
    }

    /**
     * Create payment document in MS
     * @see Customerorder::setPayment()
     */
    public function setPayment(Payment $payment, $isApplicable)
    {
        if ($payment->getSum() && $this->co->isLoaded()) {
            $syncData = Config::getPaymentSyncData($payment->getField('PAY_SYSTEM_ID'));
            if (Utils::is_count($syncData)) {

                $paymentType = Config::getPaymentType($payment->getField('PAY_SYSTEM_ID'));

                if ($paymentType === 'N') {
                    return;
                }

                if ($paymentType === 'cashin' || empty($syncData['organizationAccount'])) {
                    unset($syncData['organizationAccount']);
                }

                $paymentData = [];
                foreach ($syncData as $entity => $entityId) {

                    if (empty($entityId) || $entityId === 'N') {
                        continue;
                    }

                    switch ($entity) {
                        case 'owner':
                            $currentEntity = ApiNew::get("/entity/employee/{$entityId}");
                        break;
                        case 'organizationAccount':
                            if (!empty($syncData['organization'])) {
                                $org = $syncData['organization'];
                                $currentEntity = ApiNew::get("/entity/organization/{$org}/accounts/{$entityId}");
                            }
                        break;
                        default:
                            $currentEntity = ApiNew::get("/entity/{$entity}/{$entityId}");
                    }

                    if (Utils::is_success($currentEntity) && Utils::property_exists($currentEntity, ['meta'])) {
                        $paymentData[$entity] = (object)['meta' => $currentEntity->meta];
                    } else if(Utils::has_errors($currentEntity)) {
                        $this->co->addErrorMessageArray($currentEntity->errors);
                        return;
                    }
                }

                $paymentData['agent'] = (object)['meta' => $this->co->order->agent->meta];
                $paymentData['sum'] = round($payment->getSum() * 100, 0);
                $paymentData['operations'] = [
                        (object)[
                            'meta' => $this->co->order->meta,
                            'linkedSum' => $paymentData['sum']
                        ]
                    ];

                if ($paymentType === 'paymentin') {
                    if (!empty($payment->getField('PAY_VOUCHER_NUM'))) {
                        $paymentData['incomingNumber'] = $payment->getField('PAY_VOUCHER_NUM');
                    }

                    $payDateFromPayment = $payment->getField('DATE_PAID');
                    if ($payDateFromPayment instanceof \Bitrix\Main\Type\DateTime) {
                        $paymentData['incomingDate'] = Config::getDateTime($payDateFromPayment->format('Y-m-d H:i:s'))->format('Y-m-d H:i:s');
                    } else {
                        $date = Config::getDateTime();
                        $paymentData['incomingDate'] = $date->format('Y-m-d H:i:s');
                    }
                }

                $paymentDateType = Config::getOption('payment_date_type', 'DATE_BILL');
                if(!empty($paymentDateType) && $paymentDateType !== 'N') {
                    if (!empty($payment->getField($paymentDateType))) {
                        $momentDate = $payment->getField($paymentDateType);
                        if ($momentDate instanceof \Bitrix\Main\Type\Date) {
                            $paymentData['moment'] = Config::getDateTime($momentDate->format('Y-m-d H:i:s'))->format('Y-m-d H:i:s');
                        }
                    }
                }

                $syncId = Config::getOption('payment_sync_id', 'N');
                if (!empty($syncId) && $syncId !== 'N') {
                    $paymentData['name'] = \CRbsMoyskladHelper::getDocumentUniqName('demand', $payment->getField($syncId));
                }

                $syncIdComment = Config::getOption('payment_sync_id_comment', 'N');
                if (!empty($syncIdComment) && $syncIdComment !== 'N') {
                    $paymentData['description'] = LangMsg::get(
                        'COMMENT_WITH_DOC_ID',
                        [
                            '#TYPE#' => LangMsg::get('COMMENT_WITH_DOC_ID_TYPE_PAYMENT'),
                            '#ID#' => $payment->getField($syncIdComment)
                        ]
                    );
                }

                if (!$isApplicable) {
                    $paymentData['applicable'] = false;
                }

                if (!empty($payment->getField(Config::getSearchFieldId('payment')))) {
                    $paymentData['externalCode'] = (string)$payment->getField(Config::getSearchFieldId('payment'));
                }

                $stateHref = Config::getPaymentStateHref($paymentType);
                if (!empty($stateHref)) {
                    $paymentData['state'] = Config::getMetaDataStateNew($stateHref, $paymentType);
                }

                $projId = Config::getPaymentProjId($paymentType);
                if (!empty($projId)) {
                    $paymentData['project'] = Config::getMetaData('project', $projId);
                }

                if (Config::checkFeature('setcurrency')) {
                    $currencyBx = $payment->getField('CURRENCY');
                    if (!empty($currencyBx)) {
                        $currency = ApiNew::get('/entity/currency', ['filter' => 'isoCode=' . $currencyBx], 86400);
                        if (Utils::is_success($currency) && Utils::array_exists($currency)) {
                            $paymentData['rate'] = (object)[
                                'currency' => (object)[
                                    'meta' => $currency->rows[0]->meta
                                ]
                            ];
                        }
                    }
                }

                if (Config::checkFeature('paysmethodsync')) {
                    if ($propId = Config::getPayMethodPropId($paymentType)) {
                        $propHref = Config::getPayMethodPropHref($paymentType);
                        $payIds = Config::getPayMethodIds($paymentType);
                        if (
                            Utils::is_count($payIds)  &&
                            isset($payIds[$payment->getField('PAY_SYSTEM_ID')]) &&
                            !empty($payIds[$payment->getField('PAY_SYSTEM_ID')]) &&
                            !empty($propHref)
                        ) {
                            $customEntityId = array_pop(explode('/', $propHref));
                            $customEntityPaymentType = ApiNew::get('/entity/customentity/' . $customEntityId);
                            if (Utils::is_success($customEntityPaymentType) && Utils::array_exists($customEntityPaymentType)) {
                                foreach ($customEntityPaymentType->rows as $row) {
                                    if ($row->id === $payIds[$payment->getField('PAY_SYSTEM_ID')]) {
                                        $paymentData['attributes'] = \CRbsMoyskladHelper::convertAttributesToNewApi([
                                            (object)[
                                                'id' => $propId,
                                                'value' => $row
                                            ]
                                        ], $paymentType);
                                        break;
                                    }
                                }
                            } else if(Utils::has_errors($customEntityPaymentType)) {
                                $this->co->addErrorMessageArray($customEntityPaymentType->errors);
                            }
                        }
                    }
                }

                if (Config::checkFeature('payment_vatsum_first')) {
                    $isFirstPayment = false;
                    if ($paymentCollection = $this->co->orderBx->getPaymentCollection()) {
                        foreach ($paymentCollection as $firstPay) {
                            $isFirstPayment = (int)$firstPay->getId() === (int)$payment->getId();
                            break;
                        }
                    }

                    if ($isFirstPayment) {
                        $paymentSumKopecks = (int)round($payment->getSum() * 100, 0);
                        if (
                            isset($this->co->order->sum) &&
                            $paymentSumKopecks === (int)$this->co->order->sum &&
                            isset($this->co->order->vatSum) &&
                            $this->co->order->vatSum > 0
                        ) {
                            $paymentData['vatSum'] = (int)round($this->co->order->vatSum, 0);
                        }
                    }
                }

                if (!Utils::is_exsists_cache(md5(serialize(["/entity/{$paymentType}", $paymentData])), 'setPaymentMethod', 5)) {
                    $response = ApiNew::post("/entity/{$paymentType}", $paymentData);
                    if (Utils::has_errors($response)) {
                        $this->co->addErrorMessageArray($response->errors);
                    } else {
                        return $response;
                    }
                }
            }
        }

        return (object)[
            'meta' => (object)[
                'href' => ''
            ]
        ];
    }

    /**
     * Find payment in MS by externalCode
     * @see Customerorder::findPaymentByExternalCode()
     */
    public function findPaymentByExternalCode(Payment $payment)
    {
        $paymentType = Config::getPaymentType($payment->getField('PAY_SYSTEM_ID'));

        if ($paymentType === 'N') {
            return false;
        }

        $externalCode = (string)$payment->getField(Config::getSearchFieldId('payment'));
        if (empty($externalCode)) {
            return false;
        }

        $paymentsMs = ApiNew::get('/entity/' . $paymentType, ['filter' => 'externalCode=' . $externalCode]);
        if (Utils::is_success($paymentsMs) && Utils::array_exists($paymentsMs)) {
            return array_pop($paymentsMs->rows);
        }

        return null;
    }

    /**
     * Compare and update payment differences in MS
     * @see Customerorder::checkDiffOfPyments()
     */
    public function checkDiffOfPayments($msPayment, Payment $payment)
    {
        $paymentChangeStack = [];

        if ($payment->isPaid() && !$msPayment->applicable) {
            $paymentChangeStack['applicable'] = true;
        } elseif (!$payment->isPaid() && $msPayment->applicable) {
            $paymentChangeStack['applicable'] = false;
        }

        $paymentSum = (int)round($payment->getSum() * 100, 0);
        if ($paymentSum !== (int)$msPayment->sum) {
            $paymentChangeStack['sum'] = $paymentSum;
            $paymentChangeStack['operations'] = [
                    (object)[
                        'meta' => $this->co->order->meta,
                        'linkedSum' => $paymentSum
                    ]
                ];
        }

        if (Config::checkFeature('paysmethodsync')) {
            $defaultPaySystemId = $this->getPaySystemIdFromPaymentMs($msPayment);
            if ((int)$payment->getField('PAY_SYSTEM_ID') !== (int)$defaultPaySystemId) {
                if ($propId = Config::getPayMethodPropId($msPayment->meta->type)) {
                    $value = $this->getPaySystemIdFromPaymentBx($msPayment, $payment->getField('PAY_SYSTEM_ID'));
                    if ($value) {
                        $paymentChangeStack['attributes'] = \CRbsMoyskladHelper::convertAttributesToNewApi([
                            (object)[
                                'id' => $propId,
                                'value' => $value
                            ]
                        ], $msPayment->meta->type);
                    }
                }
            }
        }

        $paymentDateType = Config::getOption('payment_date_type', 'DATE_BILL');
        if (!empty($paymentDateType) && $paymentDateType !== 'N') {
            if (!empty($payment->getField($paymentDateType))) {
                $momentDate = $payment->getField($paymentDateType);
                if ($momentDate instanceof \Bitrix\Main\Type\Date) {
                    $paymentChangeStack['moment'] = Config::getDateTime($momentDate->format('Y-m-d H:i:s'))->format('Y-m-d H:i:s');
                }
            }
        }

        if (Utils::is_count($paymentChangeStack)) {
            ApiNew::put($msPayment->meta->href, $paymentChangeStack);
        }
    }

    /**
     * Delete payment in MS by externalCode
     * @see Customerorder::deletePaymentByExternalCode()
     */
    public function deletePaymentByExternalCode(string $externalCode = '')
    {
        if ($this->co->isLoaded() && !empty($externalCode)) {
            if ($paymentCollection = $this->co->orderBx->getPaymentCollection()) {
                foreach ($paymentCollection as $payment) {
                    if ((string)$payment->getField(Config::getSearchFieldId('payment')) === $externalCode) {
                        $msPayment = $this->findPaymentByExternalCode($payment);
                        if (!empty($msPayment->meta->href)) {
                            ApiNew::delete($msPayment->meta->href);
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * Sync payment type (MS custom field)
     * @see Customerorder::setPaymentType()
     */
    public function setPaymentType()
    {
        if ($this->co->isLoaded()) {
            $paymentIds = Config::getPaymentIds();
            $paymentProp = Config::getPaymentProp();

            $bxPaymentId = 0;
            if ($paymentCollection = $this->co->orderBx->getPaymentCollection()) {
                foreach ($paymentCollection as $payment) {
                    $bxPaymentId = $payment->getField('PAY_SYSTEM_ID');
                    break;
                }
            }

            if ((int)$bxPaymentId > 0 && Utils::is_count($paymentIds) && !empty($paymentProp) && isset($paymentIds[$bxPaymentId])) {
                $selectBxPaymentType = $paymentIds[$bxPaymentId];
                if (Utils::is_count($this->co->getCustomEntityAttrs()) > 0 && !empty($selectBxPaymentType)) {
                    if ($currentDeliveryTypeMs = Helper::getCurrentDeliveryTypeMs($this->co->getCustomEntityAttrs(), $paymentProp)) {
                        $current = array_pop(explode('/', $currentDeliveryTypeMs['value']->href));
                        if ($current !== $selectBxPaymentType) {
                            $attrMsInfo = ApiNew::get(str_replace($current, $selectBxPaymentType, $currentDeliveryTypeMs['value']->href), [], 86400);
                            if (Utils::is_success($attrMsInfo) && Utils::property_exists($attrMsInfo, ['meta'])) {
                                $this->co->addAttributeToOrderChangeStack([
                                        'id' => $currentDeliveryTypeMs['id'],
                                        'value' => (object)['meta' => $attrMsInfo->meta]
                                    ]);
                            }
                        }
                    }
                }
            }
        }
    }

    /**
     * Sync payment system name to MS custom field
     * @see Customerorder::setPaysystemName()
     */
    public function setPaysystemName()
    {
        if ($this->co->isLoaded()) {
            $propPaysystemNameId = Config::getPaysystemNamePropertyId();
            if (!empty($propPaysystemNameId)) {
                $currentPaysystemName = "";
                $strAttrs = $this->co->getStrAttrs();
                if (isset($strAttrs[$propPaysystemNameId]) && !empty($strAttrs[$propPaysystemNameId])) {
                    $currentPaysystemName = Helper::clearAllSpaces($strAttrs[$propPaysystemNameId]);
                }

                $newPaysystemNameArr = [];
                if ($paymentCollection = $this->co->orderBx->getPaymentCollection()) {
                    foreach ($paymentCollection as $payment) {
                        $newPaysystemNameArr[] = "{$payment->getField('ID')}: {$payment->getField('PAY_SYSTEM_NAME')} / {$payment->getField('SUM')} / {$payment->getField('PAID')}";
                    }
                }

                if (!empty($currentPaysystemName) && Utils::is_count($newPaysystemNameArr)) {
                    if ($currentPaysystemName === Helper::makeClearStringFromArray($newPaysystemNameArr)) {
                        return;
                    }
                }
                $this->co->addAttributeToOrderChangeStack([
                        'id' => $propPaysystemNameId,
                        'value' => implode("\n", $newPaysystemNameArr)
                    ]);
            }
        }
    }

    /**
     * Sync payment info to MS custom field
     * @see Customerorder::setPaysystemInfo()
     */
    public function setPaysystemInfo()
    {
        if ($this->co->isLoaded()) {
            $propPaysystemInfoId = Config::getPaysystemInfoPropertyId();

            if (!empty($propPaysystemInfoId)) {
                $currentPaysystemInfo = "";
                $strAttrs = $this->co->getStrAttrs();
                if (isset($strAttrs[$propPaysystemInfoId]) && !empty($strAttrs[$propPaysystemInfoId])) {
                    $currentPaysystemInfo = $strAttrs[$propPaysystemInfoId];
                }

                $newPaysystemInfo = LangMsg::get('PAY_INFO', [
                    '#SUM#' => \round($this->co->orderBx->getPrice(), 2),
                    '#PAID#' => $this->co->orderBx->getSumPaid(),
                    '#NEED_PAID#' => \round($this->co->orderBx->getPrice() - $this->co->orderBx->getSumPaid(), 2),
                ]);

                if (!empty($currentPaysystemInfo)) {
                    if ($currentPaysystemInfo === $newPaysystemInfo) {
                        return;
                    }
                }
                $this->co->addAttributeToOrderChangeStack([
                        'id' => $propPaysystemInfoId,
                        'value' => $newPaysystemInfo
                    ]);
            }
        }
    }

    /**
     * Get BX payment system ID from MS payment data
     * @see Customerorder::getPaySystemIdFromPaymentMs()
     */
    public function getPaySystemIdFromPaymentMs($paymentItem)
    {
        if (Config::checkFeature('paysmethodsync')) {
            $propHref = Config::getPayMethodPropHref($paymentItem->meta->type);
            $payIds = Config::getPayMethodIds($paymentItem->meta->type);
            if (Utils::is_count($payIds) && !empty($propHref) && Utils::array_exists($paymentItem, 'attributes')) {
                if ($currentPayTypeMs = Helper::getCurrentPayTypeMs($paymentItem->attributes, $propHref)) {
                    $current = array_pop(explode('/', $currentPayTypeMs['value']->meta->href));
                    if (!in_array($current, $payIds)) {
                        return false;
                    }
                    $currentBxPsId = array_flip($payIds)[$current];
                    if ((int)$currentBxPsId > 0) {
                        return (int)$currentBxPsId;
                    } else {
                        return false;
                    }
                }
            }
            return false;
        } else {
            return Config::getDefaultPaysystemId($paymentItem->meta->type);
        }
    }

    /**
     * Get MS custom field value for payment method by BX payment system ID
     * @see Customerorder::getPaySystemIdFromPaymentBx()
     */
    public function getPaySystemIdFromPaymentBx($paymentItem, $paySystemIdBx = 0)
    {
        if (Config::checkFeature('paysmethodsync') && $paySystemIdBx > 0) {
            $propHref = Config::getPayMethodPropHref($paymentItem->meta->type);
            $payIds = Config::getPayMethodIds($paymentItem->meta->type);
            if (
                Utils::is_count($payIds) && isset($payIds[$paySystemIdBx]) && !empty($payIds[$paySystemIdBx]) && !empty($propHref)
            ) {
                $customEntityId = array_pop(explode('/', $propHref));
                $entity = ApiNew::get('/entity/customentity/' . $customEntityId);
                if (Utils::is_success($entity) && Utils::array_exists($entity)) {
                    foreach ($entity->rows as $row) {
                        if ($row->id === $payIds[$paySystemIdBx]) {
                            return $row;
                        }
                    }
                }
            }
            return false;
        }
    }
}
