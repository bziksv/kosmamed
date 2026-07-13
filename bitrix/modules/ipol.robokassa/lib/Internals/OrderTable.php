<?php

    namespace Ipol\Robokassa\Internals;

    use \Bitrix\Main\Entity;
    use Bitrix\Main\Entity\ReferenceField;
    use Bitrix\Main\Type\DateTime;

    /**
     * Class OrderTable
     * @package Ipol\Robokassa\Internals
     */
    final class OrderTable extends Entity\DataManager
    {

        /**
         * @return string
         */
        public static function getTableName(): string
        {
            return 'ipol_robokassa_orders';
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
                    'ORDER_ID'
                ),
                new Entity\DatetimeField(
                    'CREATED_AT',
                    [
                        'default_value' => new DateTime(),
                    ]
                ),
                new Entity\DatetimeField(
                    'UPDATED_AT',
                    [
                        'default_value' => new DateTime(),
                    ]
                ),
                new Entity\StringField(
                    'EMAIL',
                    [
                        'required' => false,
                        'size' => 255,
                    ]
                ),
                new Entity\StringField(
                    'NAME',
                    [
                        'required' => false,
                        'size' => 255,
                    ]
                ),
                new Entity\StringField(
                    'PHONE',
                    [
                        'required' => false,
                        'size' => 255,
                    ]
                ),
                new Entity\BooleanField(
                    'PAYED',
                    array(
                        'values' => array('N', 'Y'),
                        'default' => 'N'
                    )
                ),
                new Entity\DateTimeField(
                    'PAYED_DATE',
                    array(
                        'required' => false,
                    )
                ),
                new Entity\FloatField(
                    'PRICE',
                    array(
                        'required' => false,
                    )
                ),
            ];
        }
    }