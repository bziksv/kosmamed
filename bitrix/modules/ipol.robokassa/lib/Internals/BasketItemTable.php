<?php

    namespace Ipol\Robokassa\Internals;

    use \Bitrix\Main\Entity;
    use Bitrix\Main\Entity\ReferenceField;

    /**
     * Class BasketItemTable
     * @package Ipol\Robokassa\Internals
     */
    final class BasketItemTable extends Entity\DataManager
    {

        /**
         * @return string
         */
        public static function getTableName(): string
        {
            return 'ipol_robokassa_basket_item';
        }

        /**
         * @return array
         */
        public static function getMap(): array
        {
            return [
                new Entity\IntegerField(
                    'ID',
                    [
                        'primary' => true,
                        'autocomplete' => true,
                    ]
                ),
                new Entity\IntegerField(
                    'ORDER_ID',
                    [
                        'required' => true,
                    ]
                ),
                new Entity\StringField(
                    'PRODUCT_ID',
                    [
                        'required' => true,
                    ]
                ),
                new Entity\StringField(
                    'PRODUCT_NAME',
                    [
                        'required' => true,
                    ]
                ),
                new Entity\FloatField(
                    'PRICE',
                    [
                        'required' => true,
                    ]
                ),
                new Entity\FloatField(
                    'QUANTITY',
                    [
                        'required' => true,
                    ]
                ),
            ];
        }
    }