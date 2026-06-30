<?php

namespace Sale\Handlers\PaySystem;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\HttpApplication;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Main\Type\DateTime;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem;
use Bitrix\Sale\PriceMaths;
use Ipol\Robokassa;

Loc::loadMessages(__FILE__);
\CModule::IncludeModule("ipol.robokassa");

class RobokassaPaymentHandler extends PaySystem\ServiceHandler
{

    public const PAYMENT_URL_RU = 'https://auth.robokassa.ru/Merchant/Index.aspx';
    public const PAYMENT_URL_KZ = 'https://auth.robokassa.kz/Merchant/Index.aspx';

	/**
	 * @param Payment $payment
	 * @param Request|null $request
	 * @return PaySystem\ServiceResult
	 */
	public function initiatePay(Payment $payment, Request $request = null)
	{
		$test = '';

        $paymentIdModificationCount = (int) Option::get(Robokassa\RobokassaPaymentService::$moduleId,'IPOL_ROBOKASSA_OPTIONS_TAB_PAYMENT_ID_MODIFICATION_COUNT', 0);

		if ($this->isTestMode($payment)) {
			$test = '_TEST';
		}

		$paymentShouldPay = (float) $payment->getField('SUM') - $payment->getSumPaid();

		/** @var array $signatureParams */
		$signatureParams = [
			$this->getBusinessValue($payment, 'SHOPLOGIN'),
			$paymentShouldPay,
            ((int) $this->getBusinessValue($payment, 'PAYMENT_ID') + $paymentIdModificationCount),
		];

		if(
			$this->getBusinessValue($payment, 'OUT_CURRENCY') != ''
			&& !(
				$this->getBusinessValue($payment, 'COUNTRY_CODE') == "KZ"
				&& $this->getBusinessValue($payment, 'OUT_CURRENCY') == "KZT"
			)
		)
		{
			$signatureParams[] = $this->getBusinessValue($payment, 'OUT_CURRENCY');
		}
		
		$receipt = [
			'items' => Robokassa\RobokassaPaymentService::formReceiptData(
				$payment,
				$paymentShouldPay,
				$this,
				$this->getBusinessValue($payment, 'COUNTRY_CODE')
			)
		];
		
		if($this->getBusinessValue($payment, 'COUNTRY_CODE') !== 'KZ')
		{
			$receipt['sno'] = $this->getBusinessValue($payment, 'SNO');
		}

        $ResultUrl2 = null;
		
		/** @var string $receipt */
        $receipt = !\defined('BX_UTF')
            ? \Bitrix\Main\Web\Json::encode($receipt)
            : \json_encode($receipt, \JSON_THROW_ON_ERROR)
        ;
		$signatureParams[] = $receipt;

        if(Option::get(Robokassa\RobokassaPaymentService::$moduleId, 'USE_TWO_STAGE_PAYMENT', 'N') === 'Y')
        {

            $server = HttpApplication::getInstance()->getContext()->getServer();

            if (
                $server->getRequestScheme() == 'https'
                || (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
                || ($server->getServerPort() == '443')
            )
            {
                $ResultUrl2 = 'https://';
            }
            else
            {
                $ResultUrl2 = 'http://';
            }

            $ResultUrl2 .= $server->getHttpHost()
                . '/bitrix/tools/sale_ps_robokassa_two_stage_payment.php?'
                . \http_build_query(
                    [
                        'ORDER_ID' => $payment->getOrderId(),
                        'PAYMENT_ID' => ($payment->getId() + $paymentIdModificationCount)
                    ]
                )
            ;

            $signatureParams[] = 'true';
            $signatureParams[] = $ResultUrl2;
        }

		$signatureParams[] = $this->getBusinessValue($payment, 'SHOPPASSWORD' . $test);
		$signatureParams[] = 'SHP_BX_PAYSYSTEM_CODE=' . $payment->getPaymentSystemId();
		$signatureParams[] = 'SHP_HANDLER=ROBOKASSA.PAYMENT';
		$signatureParams[] = 'Shp_label=official_bitrix';

		/** @var string $signatureValue */
		$signatureValue = md5(implode(':', $signatureParams));

        $url = self::PAYMENT_URL_RU;
        
        if($this->getBusinessValue($payment, 'COUNTRY_CODE') == "KZ")
        {
            $url = self::PAYMENT_URL_KZ;
        }
        
		$params = array(
            'IPOL_ROBOKASSA_OPTIONS_TAB_PAYMENT_ID_MODIFICATION_COUNT' => $paymentIdModificationCount,
			'URL' => $url,
			'SIGNATURE_VALUE' => $signatureValue,
			'RECEIPT' => urlencode($receipt),
			'BX_PAYSYSTEM_CODE' => $payment->getPaymentSystemId(),
			'PAYMENT_SHOULD_PAY' => $paymentShouldPay,
			'OUT_CURRENCY' => $this->getBusinessValue($payment, 'OUT_CURRENCY'),
			'IFRAME_STATUS' => $this->getBusinessValue($payment, 'IFRAME_STATUS') == 'Y',
            'USE_TWO_STAGE_PAYMENT' => (Option::get(Robokassa\RobokassaPaymentService::$moduleId, 'USE_TWO_STAGE_PAYMENT', 'N') === 'Y'),
            'ResultUrl2' => $ResultUrl2
		);

		if ($this->getBusinessValue($payment, 'LOG_REQUESTS') == 'Y') {
			$this->logToFile('GENERATE FORM', $params);
		}

		$this->setExtraParams($params);

		return $this->showTemplate($payment, "template");
	}

	/**
	 * @return array
	 */
	public function getCurrencyList()
	{
		return array('RUB');
	}

	/**
	 * @return array
	 */
	public static function getIndicativeFields()
	{
		return array('SHP_HANDLER' => 'ROBOKASSA.PAYMENT');
	}

	/**
	 * @param Request $request
	 * @param $paySystemId
	 * @return bool
	 */
	protected static function isMyResponseExtended(Request $request, $paySystemId)
	{
		$id = $request->get('SHP_BX_PAYSYSTEM_CODE');
		return $id == $paySystemId;
	}

	/**
	 * @param Payment $payment
	 * @param $request
	 * @return bool
	 */
	private function checkHash(Payment $payment, Request $request)
	{
		$test = '';
		if ($this->isTestMode($payment)) {
			$test = '_TEST';
		}

		$hash = md5($request->get('OutSum') . ":" . $request->get('InvId') . ":" . $this->getBusinessValue($payment, 'SHOPPASSWORD2' . $test) . ':SHP_BX_PAYSYSTEM_CODE=' . $payment->getPaymentSystemId() . ':SHP_HANDLER=ROBOKASSA.PAYMENT' . ':Shp_label=official_bitrix');

		return ToUpper($hash) == ToUpper($request->get('SignatureValue'));
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return bool
	 */
	private function checkSum(Payment $payment, Request $request)
	{
		return true;
	}

	/**
	 * @param Request $request
	 * @return mixed
	 */
	public function getPaymentIdFromRequest(Request $request)
	{
        $paymentIdModificationCount = (int) Option::get(ADMIN_MODULE_NAME,'IPOL_ROBOKASSA_OPTIONS_TAB_PAYMENT_ID_MODIFICATION_COUNT', 0);
		return (((int) $request->get('InvId')) - $paymentIdModificationCount);
	}


	/**
	 * @param Payment $payment
	 * @return string
	 */
	public function getPaymentMethod(Payment $payment)
	{
		$paymentMethod = $this->getBusinessValue($payment, 'PAYMENT_METHOD');
		if (!$paymentMethod) {$paymentMethod = 'full_prepayment';}
		return $paymentMethod;
	}

	/**
	 * @param Payment $payment
	 * @return string
	 */
	public function getPaymentObject(Payment $payment)
	{

		$paymentObject = $this->getBusinessValue($payment, 'PAYMENT_OBJECT');

		if (!$paymentObject) {$paymentObject = 'commodity';}

		return $paymentObject;
	}

	/**
	 * @param Payment $payment
	 * @return string
	 */
	public function getPaymentObjectDelivery(Payment $payment)
	{

		$paymentObjectDelivery = $this->getBusinessValue($payment, 'PAYMENT_OBJECT_DELIVERY');
		if (!$paymentObjectDelivery) {$paymentObjectDelivery = 'commodity';}

		return $paymentObjectDelivery;
	}

	/**
	 * @return mixed
	 */
	protected function getUrlList()
	{
		return array(
			'pay' => array(
				self::ACTIVE_URL => 'https://auth.robokassa.ru/Merchant/Index.aspx',
			),
		);
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return PaySystem\ServiceResult
	 */
	public function processRequest(Payment $payment, Request $request)
	{

		if ($this->getBusinessValue($payment, 'LOG_REQUESTS') == 'Y') {
			$this->logToFile('CALLBACK', $_POST);
		}

		$result = new PaySystem\ServiceResult();

		if ($this->checkHash($payment, $request)) {
			return $this->processNoticeAction($payment, $request);
		} else {
			PaySystem\ErrorLog::add(array(
				'ACTION'  => 'processRequest',
				'MESSAGE' => 'Incorrect hash',
			));
			$result->addError(new Error('Incorrect hash'));
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @param Request $request
	 * @return PaySystem\ServiceResult
	 */
	private function processNoticeAction(Payment $payment, Request $request)
	{
		$result = new PaySystem\ServiceResult();

		$psStatusDescription = Loc::getMessage('SALE_HPS_ROBOXCHANGE_RES_NUMBER') . ": " . $request->get('InvId');
		$psStatusDescription .= "; " . Loc::getMessage('SALE_HPS_ROBOXCHANGE_RES_DATEPAY') . ": " . date("d.m.Y H:i:s");

		if ($request->get("IncCurrLabel") !== null) {
			$psStatusDescription .= "; " . Loc::getMessage('SALE_HPS_ROBOXCHANGE_RES_PAY_TYPE') . ": " . $request->get("IncCurrLabel");
		}

		$fields = array(
			"PS_STATUS"             => "Y",
			"PS_STATUS_CODE"        => "-",
			"PS_STATUS_DESCRIPTION" => $psStatusDescription,
			"PS_STATUS_MESSAGE"     => Loc::getMessage('SALE_HPS_ROBOXCHANGE_RES_PAYED'),
			"PS_SUM"                => $request->get('OutSum'),
			"PS_CURRENCY"           => $this->getBusinessValue($payment, "PAYMENT_CURRENCY"),
			"PS_RESPONSE_DATE"      => new DateTime(),
		);

		$result->setPsData($fields);

		if ($this->checkSum($payment, $request)) {
			$result->setOperationType(PaySystem\ServiceResult::MONEY_COMING);
            
            if(!empty($payment->getOrder()->getSiteId()))
            {
                
                $orderStatus = Robokassa\RobokassaPaymentService::getAfterPaymentOrderStatus($payment->getOrder()->getSiteId());
                
                if(!empty($orderStatus))
                {
                    $payment->getOrder()->setField('STATUS_ID', $orderStatus);
                }
            }
            
		} else {
			PaySystem\ErrorLog::add(array(
				'ACTION'  => 'processNoticeAction',
				'MESSAGE' => 'Incorrect sum',
			));
			$result->addError(new Error('Incorrect sum'));
		}

		return $result;
	}

	/**
	 * @param Payment $payment
	 * @return bool
	 */
	protected function isTestMode(Payment $payment = null)
	{
		return ($this->getBusinessValue($payment, 'PS_IS_TEST') == 'Y');
	}

	/**
	 * @param PaySystem\ServiceResult $result
	 * @param Request $request
	 * @return mixed
	 */
	public function sendResponse(PaySystem\ServiceResult $result, Request $request)
	{
		global $APPLICATION;
		if ($result->isResultApplied()) {
			$APPLICATION->RestartBuffer();
			echo 'OK' . $request->get('InvId');
		}
	}

	/**
	 * @param  string $message
	 * @param  array  $data
	 * @return void
	 */
	private function logToFile($message, $data = []) {
		$message = '['.date('Y-m-d H:i:s').'] ACTION '.$message;
		if ($data) {
			$message .= ". DATA:\n".print_r($data, true);
		}
		$message .= "\n";
		file_put_contents(
			$_SERVER['DOCUMENT_ROOT'].'/robokassa.log',
			$message,
			FILE_APPEND
		);
	}
}
