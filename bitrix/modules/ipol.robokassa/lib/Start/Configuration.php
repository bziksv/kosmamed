<?php

    namespace Ipol\Robokassa\Start;

    use Bitrix\Iblock\PropertyTable;
    use Bitrix\Main\Config\Option;
    use Bitrix\Main\EventManager;
    use Bitrix\Main\Loader;
    use Bitrix\Main\Localization\Loc;
    use Ipol\Robokassa;

    /**
     * Class Configuration
     * @package Ipol\Robokassa\Start
     */
    final class Configuration
    {

        public const BUY_BUTTON_POSITION_TOP_LEFT = 'top-left';
        public const BUY_BUTTON_POSITION_TOP_CENTER = 'top-center';
        public const BUY_BUTTON_POSITION_TOP_RIGHT = 'top-right';
        public const BUY_BUTTON_POSITION_MIDDLE_LEFT = 'middle-left';
        public const BUY_BUTTON_POSITION_MIDDLE_RIGHT = 'middle-right';
        public const BUY_BUTTON_POSITION_BOTTOM_LEFT = 'bottom-left';
        public const BUY_BUTTON_POSITION_BOTTOM_CENTER = 'bottom-center';
        public const BUY_BUTTON_POSITION_BOTTOM_RIGHT = 'bottom-right';
        public const BUY_BUTTON_SET = [
            self::BUY_BUTTON_POSITION_TOP_LEFT,
            self::BUY_BUTTON_POSITION_TOP_CENTER,
            self::BUY_BUTTON_POSITION_TOP_RIGHT,
            self::BUY_BUTTON_POSITION_MIDDLE_LEFT,
            self::BUY_BUTTON_POSITION_MIDDLE_RIGHT,
            self::BUY_BUTTON_POSITION_BOTTOM_LEFT,
            self::BUY_BUTTON_POSITION_BOTTOM_CENTER,
            self::BUY_BUTTON_POSITION_BOTTOM_RIGHT
        ];

        /**
         * Код свойства цены в инфоблоке
         */
        public const IBLOCK_PRICE_PROPERTY_CODE = 'PRODUCT_PRICE';

        /**
         * Код свойства остатков в инфоблоке
         */
        public const IBLOCK_QUANTITY_PROPERTY_CODE = 'PRODUCT_QUANTITY';

        /**
         * Свойство установки обработчика страницы
         */
        public const EVENT_HANDLER_INSTALL = 'EVENT_HANDLER_INSTALL';

        /**
         * Создание свойств для товаров в инфоблоке
         * @param int $iblockId
         * @return void
         */
        public static function createIblockProperties(int $iblockId): void
        {

            if(!Loader::includeModule("iblock"))
            {
                return;
            }

            $properties = new \CIBlockProperty();

            $hasProperty = \CIblockProperty::getList(
                [],
                [
                    'IBLOCK_ID' => $iblockId,
                    'CODE' => self::IBLOCK_PRICE_PROPERTY_CODE,
                ]
            )->GetNext(true, false);

            if(!$hasProperty)
            {
                $properties->Add(
                    [
                        'NAME' => Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START_IBLOCK_PROPERTY_PRICE_TITLE'),
                        'IBLOCK_ID' => $iblockId,
                        'CODE' => self::IBLOCK_PRICE_PROPERTY_CODE,
                        'PROPERTY_TYPE' => PropertyTable::TYPE_NUMBER,
                    ]
                );
            }

            $hasProperty = \CIblockProperty::getList(
                [],
                [
                    'IBLOCK_ID' => $iblockId,
                    'CODE' => self::IBLOCK_QUANTITY_PROPERTY_CODE,
                ]
            )->GetNext(true, false);

            if(!$hasProperty)
            {
                $properties->Add(
                    [
                        'NAME' => Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_START_IBLOCK_PROPERTY_QUANTITY_TITLE'),
                        'IBLOCK_ID' => $iblockId,
                        'CODE' => self::IBLOCK_QUANTITY_PROPERTY_CODE,
                        'PROPERTY_TYPE' => PropertyTable::TYPE_NUMBER,
                    ]
                );
            }
        }

        /**
         * Добавление обработки url для добавления кнопок покупки
         * @return void
         */
        public static function registerPageProductHandler(): void
        {
            if(
                Option::get(
                    Robokassa\RobokassaPaymentService::$moduleId,
                    self::EVENT_HANDLER_INSTALL,
                    'N'
                ) === 'N'
            )
            {
                EventManager::getInstance()->registerEventHandler(
                    'main',
                    'onEpilog',
                    Robokassa\RobokassaPaymentService::$moduleId,
                    Robokassa\Start\Product::class,
                    'onEpilog'
                );
            }
        }

        /**
         * Удаление обработки url для добавления кнопок покупки
         * @return void
         */
        public static function unRegisterPageProductHandler(): void
        {
            if(
                Option::get(
                    Robokassa\RobokassaPaymentService::$moduleId,
                    self::EVENT_HANDLER_INSTALL,
                    'N'
                ) === 'Y'
            )
            {
                EventManager::getInstance()->unRegisterEventHandler(
                    'main',
                    'onEpilog',
                    Robokassa\RobokassaPaymentService::$moduleId,
                    Robokassa\Start\Product::class,
                    'onEpilog'
                );
            }
        }
    }