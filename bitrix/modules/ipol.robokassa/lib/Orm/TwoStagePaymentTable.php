<?php

    namespace Ipol\Robokassa\Orm;

    use \Bitrix\Main\Entity;
    use Bitrix\Main\Entity\ReferenceField;

    /**
     * Class TwoStagePaymentTable
     * @package lib
     */
    final class TwoStagePaymentTable extends Entity\DataManager
    {

        /**
         * Платеж принят
         */
        public const TWO_STAGE_PAYMENT_STATUS_ACCEPT = 'accept';
        /**
         * Платеж не обработан
         */
        public const TWO_STAGE_PAYMENT_STATUS_NONE = 'none';
        /**
         * Платеж отменен
         */
        public const TWO_STAGE_PAYMENT_STATUS_CANCELED = 'canceled';

        /**
         * Холдирование прошло
         */
        public const TWO_STAGE_PAYMENT_RESULT_PAID = 'paid';
        /**
         * Холдирование не прошло
         */
        public const TWO_STAGE_PAYMENT_RESULT_NO_PAID = 'no-paid';

        /**
         * @return string
         */
        public static function getTableName()
        {
            return 'ipol_robokassa_two_stage_payment';
        }

        /**
         * @return Entity\IntegerField[]
         * @throws \Bitrix\Main\SystemException
         */
        public static function getMap()
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
                new Entity\IntegerField(
                    'PAYMENT_ID',
                    [
                        'required' => true,
                    ]
                ),
                /**
                 * Статус оплаты при 2ух этапной оплаты (внутренний)
                 */
                new Entity\StringField(
                    'TWO_STAGE_PAYMENT_STATUS',
                    [
                        'default_value' => self::TWO_STAGE_PAYMENT_STATUS_NONE,
                    ]
                ),
                /**
                 * Результат оплаты при 2ух этапной оплаты (от робокассы)
                 */
                new Entity\StringField(
                    'TWO_STAGE_PAYMENT_RESULT',
                    [
                        'default_value' => self::TWO_STAGE_PAYMENT_RESULT_NO_PAID,
                    ]
                ),
            ];
        }

        /**
         * @param \Bitrix\Sale\Payment $payment
         * @return array|false
         * @throws \Bitrix\Main\ArgumentException
         * @throws \Bitrix\Main\ObjectPropertyException
         * @throws \Bitrix\Main\SystemException
         */
        public static function findOneByPayment(\Bitrix\Sale\Payment $payment)
        {
            return self::getList(
                [
                    'filter' => [
                        'ORDER_ID' => $payment->getOrderId(),
                        'PAYMENT_ID' => $payment->getId(),
                    ]
                ]
            )->fetch();
        }
    }