<?php

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

            \Bitrix\Main\Localization\Loc::loadMessages(__FILE__);

            $options = \Bitrix\Main\Config\Option::getForModule('ipol.robokassa');

            $this->arParams['ORDER_ID'] = $this->arResult['ORDER_ID'] = (int) $arParams['ORDER_ID'];

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
         */
        public function executeComponent(): void
        {

            \Bitrix\Main\Loader::includeModule('ipol.robokassa');

            try
            {

                $this->arResult['ORDER'] = \Ipol\Robokassa\Start\Order::findOneOrderByOrderId($this->arParams['ORDER_ID']);

                if($this->arResult['ORDER']['PAYED'] === 'Y')
                {
                    throw new \RuntimeException('ORDER_IS_PAYED');
                }

                $this->arResult['PAYMENT_FIELDS'] = \Ipol\Robokassa\Start\Payment::createPaymentFields(
                    $this->arResult['ORDER'],
                    $this->arParams['ROBOKASSA_OPTION']
                );

                $this->arResult['OPTIONS'] = $this->arParams['ROBOKASSA_OPTION'];

                $this->includeComponentTemplate();
            }
            catch(Exception $e)
            {
                $this->arResult['ERROR_MESSAGE'] = 'IPOL_ROBOKASSA_START.PAYMENT.ERROR.' . $e->getMessage();
                $this->includeComponentTemplate('error');
            }
        }
    }