<?php

    namespace Ipol\Robokassa;

    use Bitrix\Main;
    use JetBrains\PhpStorm\NoReturn;

    /**
     * Class RobokassaWidget
     * @package Ipol\Robokassa
     */
    final class RobokassaWidget
    {

        private const OPTION_KEY = 'ROBOKASSA_WDGET_';

        /**
         * Не использовать виджеты
         */
        public const USE_WIDGET_TYPE_NONE = 0;

        /**
         * Использовать информационные блоки
         */
        public const USE_WIDGET_TYPE_BADGE = 1;

        /**
         * Использовать виджеты
         */
        public const USE_WIDGET_TYPE_WIDGET = 2;

        /**
         * Цветовая тема - светлая
         */
        public const BADGE_THEME_LIGHT = 'light';

        /**
         * Цветовая тема - темная
         */
        public const BADGE_THEME_DARK = 'dark';

        /**
         * Цветовая тема - светлая
         */
        public const WIDGET_THEME_LIGHT = 'light';

        /**
         * Цветовая тема - темная
         */
        public const WIDGET_THEME_DARK = 'dark';

        /**
         * Цветовая схема - none
         */
        public const BADGE_COLOR_SCHEME_NONE = '';

        /**
         * Цветовая схема - primary
         */
        public const BADGE_COLOR_SCHEME_PRIMARY = 'primary';

        /**
         * Цветовая схема - secondary
         */
        public const BADGE_COLOR_SCHEME_SECONDARY = 'secondary';

        /**
         * Цветовая схема - accent
         */
        public const BADGE_COLOR_SCHEME_ACCENT = 'accent';

        /**
         * Размер компонента - S
         */
        public const BADGE_SIZE_S = 's';

        /**
         * Размер компонента - M
         */
        public const BADGE_SIZE_M = 'm';

        /**
         * Размер компонента - S
         */
        public const WIDGET_SIZE_S = 's';

        /**
         * Размер компонента - M
         */
        public const WIDGET_SIZE_M = 'm';

        /**
         * Положение описания относительно содержимого - left
         */
        public const WIDGET_DESCRIPTION_POSITION_LEFT = 'left';

        /**
         * Положение описания относительно содержимого - right
         */
        public const WIDGET_DESCRIPTION_POSITION_RIGHT = 'right';

        /**
         * Тип виджета. Все
         */
        public const WIDGET_TYPE_ALL = null;

        /**
         * Тип виджета. bnpl — «покупка сейчас, плати позже» (BNPL/рассрочка)
         */
        public const WIDGET_TYPE_BNPL = 'bnpl';

        /**
         * Тип виджета. credit — классический кредит.
         */
        public const WIDGET_TYPE_CREDIT = 'credit';

        public static function loadConfiguration(): array
        {

            $configuration = [];

            foreach(
                Main\Config\Option::getForModule(RobokassaPaymentService::$moduleId)
                as $key => $value
            )
            {
                if(!str_contains($key, self::OPTION_KEY))
                {
                    continue;
                }

                $configuration[\strtr($key, [self::OPTION_KEY => ''])] = $value;
            }

            return $configuration;
        }

        #[NoReturn]
        public static function updateConfiguration(array $configuration): void
        {

            if(isset($configuration['USE_TYPE']))
            {
                if(
                    \in_array(
                        $configuration['USE_TYPE'],
                        [
                            self::USE_WIDGET_TYPE_NONE,
                            self::USE_WIDGET_TYPE_BADGE,
                            self::USE_WIDGET_TYPE_WIDGET,
                        ]
                    )
                )
                {
                    Main\Config\Option::set(
                        RobokassaPaymentService::$moduleId,
                        self::OPTION_KEY . 'USE_TYPE',
                        $configuration['USE_TYPE']
                    );
                }
            }

            if(isset($configuration['MERCHANT']))
            {
                Main\Config\Option::set(
                    RobokassaPaymentService::$moduleId,
                    self::OPTION_KEY . 'MERCHANT',
                    $configuration['MERCHANT']
                );
            }

            if(isset($configuration['BADGE_THEME']))
            {
                if(
                    \in_array(
                        $configuration['BADGE_THEME'],
                        [
                            self::BADGE_THEME_DARK,
                            self::BADGE_THEME_LIGHT
                        ]
                    )
                )
                {
                    Main\Config\Option::set(
                        RobokassaPaymentService::$moduleId,
                        self::OPTION_KEY . 'BADGE_THEME',
                        $configuration['BADGE_THEME']
                    );
                }
            }

            if(isset($configuration['BADGE_COLOR']))
            {
                if(
                    \in_array(
                        $configuration['BADGE_COLOR'],
                        [
                            self::BADGE_COLOR_SCHEME_NONE,
                            self::BADGE_COLOR_SCHEME_ACCENT,
                            self::BADGE_COLOR_SCHEME_PRIMARY,
                            self::BADGE_COLOR_SCHEME_SECONDARY,
                        ]
                    )
                )
                {
                    Main\Config\Option::set(
                        RobokassaPaymentService::$moduleId,
                        self::OPTION_KEY . 'BADGE_COLOR',
                        $configuration['BADGE_COLOR']
                    );
                }
            }

            if(isset($configuration['BADGE_SIZE']))
            {
                if(
                    \in_array(
                        $configuration['BADGE_SIZE'],
                        [
                            self::BADGE_SIZE_M,
                            self::BADGE_SIZE_S
                        ]
                    )
                )
                {
                    Main\Config\Option::set(
                        RobokassaPaymentService::$moduleId,
                        self::OPTION_KEY . 'BADGE_SIZE',
                        $configuration['BADGE_SIZE']
                    );
                }
            }

            if(isset($configuration['BADGE_SHOW_LOGO']))
            {
                Main\Config\Option::set(
                    RobokassaPaymentService::$moduleId,
                    self::OPTION_KEY . 'BADGE_SHOW_LOGO',
                    $configuration['BADGE_SHOW_LOGO'] === 'Y' ? 'Y' : 'N'
                );
            }

            if(isset($configuration['WIDGET_THEME']))
            {
                if(
                    \in_array(
                        $configuration['WIDGET_THEME'],
                        [
                            self::WIDGET_THEME_DARK,
                            self::WIDGET_THEME_LIGHT
                        ]
                    )
                )
                {
                    Main\Config\Option::set(
                        RobokassaPaymentService::$moduleId,
                        self::OPTION_KEY . 'WIDGET_THEME',
                        $configuration['WIDGET_THEME']
                    );
                }
            }

            if(isset($configuration['WIDGET_SIZE']))
            {
                if(
                    \in_array(
                        $configuration['WIDGET_SIZE'],
                        [
                            self::WIDGET_SIZE_M,
                            self::WIDGET_SIZE_S
                        ]
                    )
                )
                {
                    Main\Config\Option::set(
                        RobokassaPaymentService::$moduleId,
                        self::OPTION_KEY . 'WIDGET_SIZE',
                        $configuration['WIDGET_SIZE']
                    );
                }
            }

            if(isset($configuration['WIDGET_DESCRIPTION_POSITION']))
            {
                if(
                    \in_array(
                        $configuration['WIDGET_DESCRIPTION_POSITION'],
                        [
                            self::WIDGET_DESCRIPTION_POSITION_LEFT,
                            self::WIDGET_DESCRIPTION_POSITION_RIGHT
                        ]
                    )
                )
                {
                    Main\Config\Option::set(
                        RobokassaPaymentService::$moduleId,
                        self::OPTION_KEY . 'WIDGET_DESCRIPTION_POSITION',
                        $configuration['WIDGET_DESCRIPTION_POSITION']
                    );
                }
            }

            if(isset($configuration['WIDGET_WIDGET_TYPE']))
            {
                if(
                    \in_array(
                        $configuration['WIDGET_WIDGET_TYPE'],
                        [
                            self::WIDGET_TYPE_BNPL,
                            self::WIDGET_TYPE_CREDIT,
                            self::WIDGET_TYPE_ALL,
                        ]
                    )
                )
                {
                    Main\Config\Option::set(
                        RobokassaPaymentService::$moduleId,
                        self::OPTION_KEY . 'WIDGET_WIDGET_TYPE',
                        $configuration['WIDGET_WIDGET_TYPE']
                    );
                }
            }

            if(isset($configuration['MERCHANT']))
            {
                Main\Config\Option::set(
                    RobokassaPaymentService::$moduleId,
                    self::OPTION_KEY . 'MERCHANT',
                    $configuration['MERCHANT']
                );
            }

            if(isset($configuration['WIDGET_SHOW_LOGO']))
            {
                Main\Config\Option::set(
                    RobokassaPaymentService::$moduleId,
                    self::OPTION_KEY . 'WIDGET_SHOW_LOGO',
                    $configuration['WIDGET_SHOW_LOGO'] === 'Y' ? 'Y' : 'N'
                );
            }

            if(isset($configuration['WIDGET_HAS_SECOND_LINE']))
            {
                Main\Config\Option::set(
                    RobokassaPaymentService::$moduleId,
                    self::OPTION_KEY . 'WIDGET_HAS_SECOND_LINE',
                    $configuration['WIDGET_HAS_SECOND_LINE'] === 'Y' ? 'Y' : 'N'
                );
            }

            if(isset($configuration['WIDGET_BORDER_RADIUS']))
            {

                $configuration['WIDGET_BORDER_RADIUS'] = (int) $configuration['WIDGET_BORDER_RADIUS'];

                if($configuration['WIDGET_BORDER_RADIUS'] >= 10 && $configuration['WIDGET_BORDER_RADIUS'] <= 48)
                {
                    Main\Config\Option::set(
                        RobokassaPaymentService::$moduleId,
                        self::OPTION_KEY . 'WIDGET_BORDER_RADIUS',
                        $configuration['WIDGET_BORDER_RADIUS']
                    );
                }
            }
        }
    }