<?php

    namespace Ipol\Robokassa\Internals;

    use \Bitrix\Main\Entity;
    use Bitrix\Main\Entity\ReferenceField;
    use Bitrix\Main\Type\DateTime;

    /**
     * Class RefundOrderTable
     * @package Ipol\Robokassa\Internals
     */
    final class RefundOrderTable extends Entity\DataManager
    {

        /**
         * Полный возврат
         */
        public const REFUND_TYPE_FULL = 'full';

        /**
         * Частичный возврат
         */
        public const REFUND_TYPE_PARTIAL = 'partial';

        /**
         * @return string
         */
        public static function getTableName(): string
        {
            return 'ipol_robokassa_refund_orders';
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
                new Entity\IntegerField(
                    'PAYMENT_ID'
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
                new Entity\DateTimeField(
                    'PAYED_DATE',
                    array(
                        'required' => false,
                    )
                ),
                new Entity\DateTimeField(
                    'REFUND_DATE',
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
                new Entity\StringField(
                    'TYPE',
                    array(
                        'size' => 30,
                        'required' => false,
                    )
                ),
                new Entity\StringField(
                    'OPKEY',
                    array(
                        'size' => 500,
                        'required' => false,
                    )
                ),
                new Entity\StringField(
                    'REFUND_STATUS',
                    array(
                        'size' => 500,
                        'required' => false,
                    )
                ),
                new Entity\StringField(
                    'REFUND_MESSAGE',
                    array(
                        'size' => 500,
                        'required' => false,
                    )
                ),
                new Entity\StringField(
                    'REFUND_REQUEST_ID',
                    array(
                        'size' => 100,
                        'required' => false,
                    )
                ),
            ];
        }
    }