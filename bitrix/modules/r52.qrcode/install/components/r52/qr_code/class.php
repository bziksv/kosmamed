<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
use Bitrix\Sale;
\Bitrix\Main\Loader::includeModule('r52.qrcode');


class CQRCode extends CBitrixComponent
{

    protected $associated = [
        'Name' => 'NAME',
        'PersonalAcc' => 'PERSONAL_ACC',
        'BankName' => 'BANK_MAME',
        'BIC' => 'BIC',
        'CorrespAcc' => 'CORRESP_ACC',
        'Purpose' => 'PURPOSE',
        'PayeeINN' => 'PAYEE_INN',
        'KPP' => 'KPP'
    ];

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

    protected function getOrder($id, $sum = '') {

        $this->arResult['ORDER']['ID'] = $id;
        $this->arResult['ORDER']['ACCOUNT_NUMBER'] = $id;
        $this->arResult['ORDER']['PRICE'] = $sum;

    /*    $order = Sale\Order::load($id);
        $paymentCollection = $order->getPaymentCollection();
        foreach ($paymentCollection as $payment)
        {
            if (!$payment->isInner())
                break;
        }

        if ($payment)
        {
            $this->arResult['ORDER']['ID'] = $id;
            $this->arResult['ORDER']['ACCOUNT_NUMBER'] = $payment->getField('ACCOUNT_NUMBER');
            $this->arResult['ORDER']['PRICE'] = $payment->getField('SUM');
        }*/

    }

    protected function buildStr() {
        $this->arResult['STR'] = 'ST0001' . ($this->arParams['ENCODING'] ? $this->arParams['ENCODING'] : 2) . '|OrderNum=' . $this->arResult['ORDER']['ACCOUNT_NUMBER'] . '|Sum=' . $this->getSum($this->arResult['ORDER']['PRICE']);

        foreach ($this->associated as $code => $value) {

            if($this->arParams[$value]) {

                if($code == 'Purpose') {
                    $res = preg_replace('|({).*(})|Uis', $this->arResult['ORDER']['ACCOUNT_NUMBER'], $this->arParams[$value]);
                    $this->arResult['STR'] .= '|' . $code . '=' . $res;
                }else {
                    $this->arResult['STR'] .= '|' . $code . '=' . $this->arParams[$value];
                }
            }
        }
    }

    protected function init() {
        $this->getOrder($this->arParams['ORDER_ID'] ? $this->arParams['ORDER_ID'] : $_REQUEST['ORDER_ID'], $this->arParams['SUM']);
        $this->buildStr();
        $this->generate($this->arParams);
    }

    public function generate($data)
    {
        $qr = new \R52\QrCode\CreateQrcode;
        $res = $qr->create($this->arParams['TYPE'] == 'Y' ? $this->arResult['STR'] : $this->arParams['CUSTOM_URL'], $this->arParams['LEVEL'], $this->arParams['SIZE'], $this->arParams['MARGIN']);
        $this->arResult['QR_CODE'] = $qr->getQrLogo($res);
    }
    public function executeComponent()
    {
        if($this->startResultCache())
        {
            $this->init();
            $this->includeComponentTemplate();

        }
        return $this->arResult;
    }
}?>

