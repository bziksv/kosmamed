<?php
namespace Sale\Handlers\PaySystem;

require_once ($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/r52.qrcode/lib/CreateQrcode.php');

use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Request;
use Bitrix\Sale\Payment;
use Bitrix\Sale\PaySystem;

use Bitrix\Main\File;
use Bitrix\Main\Application;
use Bitrix\Main\Context;
use Bitrix\Main\Text;
use Bitrix\Main\IO;
use Bitrix\Sale\BusinessValue;
use Bitrix\Sale\PaySystem\Manager;
use Bitrix\Sale\PaySystem\ServiceResult;

Loc::loadMessages(__FILE__);

class r52qrcodeHandler extends PaySystem\BaseServiceHandler {

    private static $paymentList = ['Name', 'PersonalAcc', 'Purpose', 'BankName', 'BIC', 'CorrespAcc',
        'Sum', 'OrderNum', 'PayeeINN', 'PayerINN', 'DrawerStatus',
        'KPP', 'CBC', 'OKTMO', 'PaytReason', 'TaxPeriod', 'DocNo',
        'DocDate', 'TaxPaytKind', 'LastName', 'FirstName', 'MiddleName',
        'PayerAddress', 'PersonalAccount', 'Docldx', 'PensAcc',
        'Contract', 'PersAcc', 'Flat', 'Phone', 'PayerldType',
        'PayerldNum', 'ChildFio', 'BirthDate', 'PaymTerm', 'PaymPeriod',
        'Category', 'ServiceName', 'Counterld', 'CounterVal', 'Quittld',
        'QuittDate', 'InstNum', 'ClassNum', 'SpecFio', 'AddAmount',
        'Ruleld', 'Execld', 'RegType', 'UIN', 'TechCode'];

    public function getPaymentIdFromRequest(Request $request)
    {
        $dbPayment = \Bitrix\Sale\Internals\PaymentTable::getList(array(
            "filter" => array("ORDER_ID" => $this->getOrderID($request['descr'])),
            "select" => array("ID"),
        ));

        if ($arPayment = $dbPayment->fetch()) {
            return $arPayment["ID"];
        }
        return false;
    }
    private function getOrderID($number)
    {
        $dbOrder = \Bitrix\Sale\Internals\OrderTable::getList(array(
            "filter" => array("ACCOUNT_NUMBER" => $number),
            "select" => array("ID"),
        ));
        if ($arOrder = $dbOrder->fetch()) {
            $result = $arOrder["ID"];
        }
        return $result;
    }
    public function getCurrencyList()
    {
        return array('RUB');
    }
    protected function validSetting(&$params)
    {
        $result = array();
        if (strlen(trim($params['Name'])) == 0) {
            $result[] = Loc::getMessage('R52_QRCODE_VALID_Name');
        }
        if (strlen(trim($params['PersonalAcc'])) == 0) {
            $result[] = Loc::getMessage('R52_QRCODE_VALID_PersonalAcc');
        }
        if (strlen(trim($params['BankName'])) == 0) {
            $result[] = Loc::getMessage('R52_QRCODE_VALID_BankName');
        }
        if (strlen(trim($params['BIC'])) == 0) {
            $result[] = Loc::getMessage('R52_QRCODE_VALID_BIC');
        }
        if (strlen(trim($params['CorrespAcc'])) == 0) {
            $result[] = Loc::getMessage('R52_QRCODE_VALID_CorrespAcc');
        }
        if (!strlen(trim($params['LEVEL']))){
            $params['LEVEL'] = 'M';
        }
        if (strlen(trim($params['SIZE']))){
            $params['SIZE'] = preg_replace('/[^0-9]/', '', $params['SIZE']);
        } else {
            $params['SIZE'] = 3;
        }
        if (strlen(trim($params['MARGIN']))){
            $params['MARGIN'] = preg_replace('/[^0-9]/', '', $params['MARGIN']);
        } else {
            $params['MARGIN'] = 3;
        }
        return $result;
    }
    public function initiatePay(Payment $payment, Request $request = null)
    {
        $params = $this->getParamsBusValue($payment);

        $arError = $this->validSetting($params);
        $params['ERROR'] = $arError;
        if (count($arError) == 0) {
            $params['Summ'] = $params["Sum"];
            $params['Sum'] = $this->getSum($params["Sum"]);

            $strToParseQr = '';
            $isFirstItem = true;
            $qrCode = new \R52\QrCode\CreateQrcode;

            foreach ($params as $paymentDetail => $paymentValue) {
                if (in_array($paymentDetail , self::$paymentList) && strlen(trim($paymentValue))) {
                    if($paymentDetail == 'Purpose' && $params['SAMPLE']) {

                        $res = preg_replace('|({).*(})|Uis', $paymentValue, $params['SAMPLE']);

                        if ($isFirstItem) {
                            $strToParseQr = 'ST0001' . ($params['ENCODING'] ? $params['ENCODING'] : 2) . '|';
                            $strToParseQr .= $paymentDetail . '=' . trim($res);
                            $isFirstItem = false;
                        } else {
                            $strToParseQr .= '|';
                            $strToParseQr .= $paymentDetail . '=' . trim($res);
                        }

                    }else {
                        if ($isFirstItem) {
                            $strToParseQr = 'ST0001' . ($params['ENCODING'] ? $params['ENCODING'] : 2) . '|';
                            $strToParseQr .= $paymentDetail . '=' . trim($paymentValue);
                            $isFirstItem = false;
                        } else {
                            $strToParseQr .= '|';
                            $strToParseQr .= $paymentDetail . '=' . trim($paymentValue);
                        }
                    }

                }
            }
            $params['qrString'] = $strToParseQr;

            $rs = $qrCode->create($strToParseQr, $params['LEVEL'], $params['SIZE'], $params['MARGIN']);
            $res = $qrCode->getQrLogo($rs);
            $params['qrB64Image'] = $res;
//            $params['qrB64Image'] = \QRcode::b64($strToParseQr,
//                false ,
//                $params['LEVEL'] ? $params['LEVEL'] : 'M',
//                $params['SIZE'] ? $params['SIZE'] : 3,
//                $params['MARGIN'] ? $params['MARGIN'] : 3);

            $template = 'template';
            if($params['SHOW_TABLE'] == 1) {
                $template = 'table_template';
            }

            if($params['SHOW_QR_EVENT'] == 1) {
                $params['png'] = $qrCode->saveQrLogo($rs, $this->getOrderID($params['OrderNum']));
//                $params['png'] = $this->saveQr($strToParseQr, $this->getOrderID($params['OrderNum']));
            }

        } else {
            $params = array(
                "ERROR" => $arError,
            );
        }

        $this->setExtraParams($params);

        return $this->showTemplate($payment, $template);
    }
    /**
     * @param Payment|null $payment
     * @param string $template
     * @return ServiceResult
     */
    public function saveQr($str, $orderNum) {

        \QRcode::png($str, $_SERVER['DOCUMENT_ROOT'] . '/upload/qrcode/'. $orderNum .'.png');

        return '/upload/qrcode/'.$orderNum.'.png';
    }

    public function showTemplate(Payment $payment = null, $template = '')
    {

        $result = new ServiceResult();

        global $APPLICATION, $USER, $DB;

        $templatePath = $this->searchTemplate($template);

        if ($templatePath != '' && IO\File::isFileExists($templatePath))
        {
            $params = array_merge($this->getParamsBusValue($payment), $this->getExtraParams());

            if ($this->initiateMode == self::STREAM)
            {
                require($templatePath);

                if ($this->service->getField('ENCODING') != '')
                {
                    define("BX_SALE_ENCODING", $this->service->getField('ENCODING'));
                    AddEventHandler('main', 'OnEndBufferContent', array($this, 'OnEndBufferContent'));
                }
            }
            elseif ($this->initiateMode == self::STRING)
            {
                ob_start();
                $content = require($templatePath);

                $buffer = ob_get_contents();
                if ($buffer <> '')
                    $content = $buffer;

                if ($this->service->getField('ENCODING') != '')
                {
                    $encoding = Context::getCurrent()->getCulture()->getCharset();
                    $content = Text\Encoding::convertEncoding($content, $encoding, $this->service->getField('ENCODING'));
                }

                $result->setTemplate($content);
                ob_end_clean();
            }
        }
        else
        {
            $result->addError(new Error(Loc::getMessage('SALE_PS_BASE_SERVICE_TEMPLATE_ERROR')));
        }

        return $result;
    }

    private function searchTemplate($template)
    {

        $documentRoot = Application::getDocumentRoot();
        $siteTemplate = \CSite::GetCurTemplate();
        $template = Manager::sanitize($template);
        $handlerName = static::getName();

        $folders = array();

        $folders[] = '/local/templates/'.$siteTemplate.'/payment/'.$handlerName.'/'.$template;
        if ($siteTemplate !== '.default')
            $folders[] = '/local/templates/.default/payment/'.$handlerName.'/'.$template;

        $folders[] = '/bitrix/templates/'.$siteTemplate.'/payment/'.$handlerName.'/'.$template;
        if ($siteTemplate !== '.default')
            $folders[] = '/bitrix/templates/.default/payment/'.$handlerName.'/'.$template;

        $baseFolders = Manager::getHandlerDirectories();
        $folders[] = $baseFolders[$this->handlerType].$handlerName.'/'.$template;

        foreach ($folders as $folder)
        {
            $templatePath = $documentRoot.$folder.'/template.php';

            if (IO\File::isFileExists($templatePath)) {

                return $templatePath;

            }

        }

        return '';
    }

    protected function getSum($sum, $bitrixDought = '.', $dought = false)
    {
        $result = "";
        $sum = explode($bitrixDought, $sum);
        if (strlen($sum[1]) > 2) {
            $result = $sum[0] . $dought . substr($sum[1], 0, 2);
        } elseif (strlen($sum[1]) == 2) {
            $result = $sum[0] . $dought . $sum[1];
        } elseif (strlen($sum[1]) == 1) {
            $result = $sum[0] . $dought . $sum[1] . "0";
        } else {
            $result = $sum[0] . $dought .  "00";
        }
        return $result;
    }
}
