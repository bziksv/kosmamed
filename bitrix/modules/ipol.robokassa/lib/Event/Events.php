<?php

    namespace Ipol\Robokassa\Event;

    /**
     * Class Events
     * @package Ipol\Robokassa\lib\Event
     */
    class Events
    {

        public const MODULE_ID = 'ipol.robokassa';

        /**
         * Событие после формирования товарных данных для чека
         */
        public const AFTER_FORMAT_RECEIPT_ITEMS_DATA = 'AFTER_FORMAT_RECEIPT_ITEMS_DATA';

        /**
         * Событие после формирования данных для чека
         */
        public const AFTER_FORMAT_RECEIPT_DATA = 'AFTER_FORMAT_RECEIPT_DATA';

        /**
         * json формирование строки для чека
         */
        public const FORMAT_RECEIPT_JSON = 'FORMAT_RECEIPT_JSON';

        /**
         * Событие перед сборкой подписи
         */
        public const BEFORE_SIGNATURE = 'BEFORE_SIGNATURE';

        /**
         * Событие модификации данных перед отправкой в шаблон на рендер
         */
        public const TEMPLATE_PAYMENT_PARAMS = 'TEMPLATE_PAYMENT_PARAMS';

        /**
         * Событие после формирования данных для второго чека
         */
        public const AFTER_FORMAT_SECOND_CHECK_DATA = 'AFTER_FORMAT_SECOND_CHECK_DATA';

        /**
         * Возврат ответа после отправки второго чека
         */
        public const RESPONSE_SECOND_CHECK_DATA = 'RESPONSE_SECOND_CHECK_DATA';

        /**
         * Событие после формирования данных для возврата оплат по заказу
         */
        public const AFTER_FORMAT_REFUND_DATA = 'AFTER_FORMAT_REFUND_DATA';
    }