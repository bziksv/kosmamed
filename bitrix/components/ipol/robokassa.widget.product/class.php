<?php

    use Ipol\Robokassa;
    use Bitrix\Catalog;

    if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true)
    {
        die();
    }

    /**
     * Class RobokassaWidgetComponent
     */
    final class RobokassaWidgetComponent extends \CBitrixComponent
    {

        /**
         * @param $arParams
         * @return array
         */
        public function onPrepareComponentParams($arParams)
        {

            \Bitrix\Main\Loader::includeModule('ipol.robokassa');

            $this->arParams = [
                ...['WIDGET_PARAMS' => Robokassa\RobokassaWidget::loadConfiguration()],
                ...$arParams
            ];

            return $this->arParams;
        }

        /**
         * @return void
         * @throws \Bitrix\Main\LoaderException
         */
        public function executeComponent()
        {

            \Bitrix\Main\Loader::includeModule('ipol.robokassa');
            \Bitrix\Main\Loader::includeModule('catalog');

            global $USER;

            if(isset($this->arParams['PRICE']))
            {
                $this->arResult['PRICE'] = (double) $this->arParams['PRICE'];
                return $this->includeComponentTemplate();
            }

            $product = Catalog\Model\Product::getList(
                [
                    'select' => [
                        'ID', 'QUANTITY', 'QUANTITY_RESERVED',
                        'PRICE_TYPE', 'TYPE', 'BUNDLE',
                    ],
                    'filter' => ['=ID' => $this->arParams['ELEMENT_ID']],
                    'limit' => 1
                ]
            )->fetch();

            switch ((int) $product['TYPE'])
            {
                case Catalog\ProductTable::TYPE_OFFER:
                case Catalog\ProductTable::TYPE_PRODUCT:

                    $price = \CCatalogProduct::GetOptimalPrice(
                        $product['ID'],
                        1,
                        $USER->IsAuthorized() ? $USER->GetUserGroupArray() : []
                    );

                    if($price)
                    {
                        $this->arResult['PRICE'] = $price['PRICE']['PRICE'];
                    }
                    break;
                case Catalog\ProductTable::TYPE_SKU:

                    $element = \CIBlockElement::GetList(
                        [],
                        [
                            'ID' => $this->arParams['ELEMENT_ID'],
                            'IBLOCK_ID' => $this->arParams['IBLOCK_ID']
                        ],
                        false,
                        [
                            'nPageSize' => 1
                        ],
                        [
                            'ID',
                            'IBLOCK_ID',
                        ]
                    )->GetNext(true, false);

                    if(empty($element))
                    {
                        break;
                    }

                    $catalogIblockId = \CCatalog::GetByID($this->arParams['IBLOCK_ID']);

                    if(
                        empty($catalogIblockId)
                        || empty($catalogIblockId['OFFERS_IBLOCK_ID'])
                        || empty($catalogIblockId['OFFERS_PROPERTY_ID'])
                    )
                    {
                        break;
                    }

                    $elements = \CIBlockElement::GetList(
                        [],
                        [
                            'ACTIVE' => 'Y',
                            'ACTIVE_DATE' => 'Y',
                            'IBLOCK_ID' => $catalogIblockId['OFFERS_IBLOCK_ID'],
                            '=PROPERTY_' . $catalogIblockId['OFFERS_PROPERTY_ID'] => $element['ID'],
                        ],
                        false,
                        false,
                        [
                            'ID',
                            'IBLOCK_ID',
                        ]
                    );
                    while($element = $elements->GetNext(true, false))
                    {
                        $price = \CCatalogProduct::GetOptimalPrice(
                            $element['ID'],
                            1,
                            $USER->IsAuthorized() ? $USER->GetUserGroupArray() : []
                        );

                        if($price['PRICE']['PRICE'] > $this->arParams['PRICE'])
                        {
                            $this->arResult['PRICE'] = $price['PRICE']['PRICE'];
                        }
                    }
                    break;
            }

            $this->includeComponentTemplate();
        }
    }