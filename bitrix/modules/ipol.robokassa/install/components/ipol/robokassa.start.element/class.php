<?php

    if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
    {
        die();
    }

    use Ipol\Robokassa\Start;

    /**
     * Class RobokassaStartElementComponent
     */
    final class RobokassaStartElementComponent extends \CBitrixComponent
    {

        /**
         * @param $arParams
         * @return array|mixed
         * @throws \Bitrix\Main\ArgumentNullException
         */
        public function onPrepareComponentParams($arParams)
        {

            $this->arParams = $arParams;
            $options = \Bitrix\Main\Config\Option::getForModule('ipol.robokassa');

            foreach($options as $key => $value)
            {
                if(\str_contains($key, 'START_FUNCTION'))
                {
                    $this->arParams['ROBOKASSA_OPTION'][\strtr($key, ['START_FUNCTION_' => ''])] = $value;
                }
            }

            return $this->arParams;
        }

        /**
         * @return false|mixed|null
         * @throws \Bitrix\Main\LoaderException
         */
        public function executeComponent()
        {

            if(!\Bitrix\Main\Loader::includeModule('iblock'))   return false;
            if(!\Bitrix\Main\Loader::includeModule('ipol.robokassa'))  return false;

            $product = \CIBlockElement::GetList(
                [],
                [
                    'ACTIVE' => 'Y',
                    'ACTIVE_DATE' => 'Y',
                    '=ID' => $this->arParams['ELEMENT_ID'],
                    '=IBLOCK_ID' => $this->arParams['ROBOKASSA_OPTION']['IBLOCK_ID'],
                ],
                false,
                [
                    'nPageSize' => 1,
                ],
                [
                    'ID',
                    'NAME',
                    'IBLOCK_ID',
                    'PROPERTY_' . Start\Product::IBLOCK_PRICE_PROPERTY_CODE,
                    'PROPERTY_' . Start\Product::IBLOCK_QUANTITY_PROPERTY_CODE
                ]
            )->GetNext(true, false);

            if(empty($product)) return false;

            $this->arResult['PRODUCT'] = [
                'ID' => $product['ID'],
                'NAME' => $product['NAME'],
                'PRICE' => (double) $product['PROPERTY_' . Start\Product::IBLOCK_PRICE_PROPERTY_CODE . '_VALUE'],
                'QUANTITY' => (int) $product['PROPERTY_' . Start\Product::IBLOCK_QUANTITY_PROPERTY_CODE . '_VALUE'],
            ];

            $this->arResult['PRODUCT']['PRINT_PRICE'] = Start\Product::formatPrice($this->arResult['PRODUCT']['PRICE']);

            return $this->includeComponentTemplate();
        }

    }