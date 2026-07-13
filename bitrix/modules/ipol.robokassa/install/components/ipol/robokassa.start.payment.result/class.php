<?php

    use Bitrix\Main\HttpApplication;

    if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
    {
        die();
    }

    /**
     * Class RobokassaStartPaymentComponent
     */
    final class RobokassaStartPaymentComponent extends \CBitrixComponent
    {

        /**
         * @param $arParams
         * @return array|mixed
         * @throws \Bitrix\Main\ArgumentNullException
         */
        public function onPrepareComponentParams($arParams)
        {

            $options = \Bitrix\Main\Config\Option::getForModule('ipol.robokassa');

            foreach($options as $key => $value)
            {
                if(\str_contains($key, 'START_FUNCTION_OPTIONS'))
                {
                    $this->arParams['ROBOKASSA_OPTION'][\strtr($key, ['START_FUNCTION_OPTIONS_' => ''])] = $value;
                }
            }

            return $this->arParams;
        }

        /**
         * @return void
         * @throws \Bitrix\Main\LoaderException
         */
        public function executeComponent()
        {

            \Bitrix\Main\Loader::includeModule('ipol.robokassa');

            $request = HttpApplication::getInstance()->getContext()->getRequest();

            $fields = [
                'OutSum' => $request->getPost('OutSum'),
                'InvId' => $request->getPost('InvId'),
                'SignatureValue' => $request->getPost('SignatureValue'),
                'SHP_ORDER_ID' => $request->getPost('SHP_ORDER_ID'),
            ];

            if(
                !\Ipol\Robokassa\Start\Payment::checkResultPaymentSignature(
                    $fields,
                    $this->arParams['ROBOKASSA_OPTION']
                )
            )
            {
                echo 'BAD SIGNATURE';
                exit;
            }

            try

            {
                \Ipol\Robokassa\Start\Order::paymentOrder($fields['SHP_ORDER_ID']);
                echo 'OK' . $fields['InvId'];
            }
            catch (\Exception $e)
            {
                echo $e->getMessage();
            }

            exit;
        }
    }