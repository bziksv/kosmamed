<?php

    namespace Ipol\Robokassa;

    use Bitrix\Catalog\CatalogIblockTable;
    use Bitrix\Iblock\PropertyTable;
    use Bitrix\Main\Loader;
    use Bitrix\Main\Localization\Loc;

    /**
     * Class Options
     * @package Ipol\Robokassa
     */
    final class Options
    {

        public const IBLOCK_PROPERTY_PAYMENT_TYPE_CODE = 'ROBOKASSA_PAYMENT_TYPE';

        /**
         * Добавление свойства выбора типа предмета расчета к товарам каталогов
         * @return void
         * @throws \Bitrix\Main\LoaderException
         */
        public static function installIblockProperties(): void
        {

            if(
                !Loader::includeModule("iblock")
                || !Loader::includeModule("catalog")
            )
            {
                return;
            }

            $iblocks = CatalogIblockTable::getList(
                [
                    'select' => ['IBLOCK_ID'],
                ]
            )->fetchAll();

            $properties = new \CIBlockProperty();

            foreach($iblocks as $iblock)
            {

                $hasProperty = \CIblockProperty::getList(
                    [],
                    [
                        'IBLOCK_ID' => $iblock['IBLOCK_ID'],
                        'CODE' => self::IBLOCK_PROPERTY_PAYMENT_TYPE_CODE,
                    ]
                )->GetNext(true, false);

                if(!$hasProperty)
                {
                    $properties->Add(
                        [
                            'NAME' => Loc::getMessage('IPOL_ROBOKASSA_OPTIONS_TAB_EXTRA_PARAM.PROPERTY_PRODUCT_OBJECT_TYPE_PROPERTY_NAME'),
                            'IBLOCK_ID' => $iblock['IBLOCK_ID'],
                            'CODE' => self::IBLOCK_PROPERTY_PAYMENT_TYPE_CODE,
                            'PROPERTY_TYPE' => PropertyTable::TYPE_LIST,
                            'VALUES' => [
                                [
                                    'XML_ID' => 'commodity',
                                    'VALUE' => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_EXTRA_PARAM.PROPERTY_PRODUCT_OBJECT_TYPE.OPTION_OBJECT_COMMODITY"),
                                ],
                                [
                                    'XML_ID' => 'excise',
                                    'VALUE' => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_EXTRA_PARAM.PROPERTY_PRODUCT_OBJECT_TYPE.OPTION_OBJECT_USN_EXCISE"),
                                ],
                                [
                                    'XML_ID' => 'job',
                                    'VALUE' => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_EXTRA_PARAM.PROPERTY_PRODUCT_OBJECT_TYPE.OPTION_OBJECT_USN_INCOME_JOB"),
                                ],
                                [
                                    'XML_ID' => 'service',
                                    'VALUE' => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_EXTRA_PARAM.PROPERTY_PRODUCT_OBJECT_TYPE.OPTION_OBJECT_SERVICE"),
                                ],
                                [
                                    'XML_ID' => 'gambling_bet',
                                    'VALUE' => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_EXTRA_PARAM.PROPERTY_PRODUCT_OBJECT_TYPE.OPTION_OBJECT_GAMBLING_BET"),
                                ],
                                [
                                    'XML_ID' => 'gambling_prize',
                                    'VALUE' => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_EXTRA_PARAM.PROPERTY_PRODUCT_OBJECT_TYPE.OPTION_OBJECT_GAMBLING_PRIZE"),
                                ],
                                [
                                    'XML_ID' => 'lottery',
                                    'VALUE' => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_EXTRA_PARAM.PROPERTY_PRODUCT_OBJECT_TYPE.OPTION_OBJECT_LOTTERY"),
                                ],
                                [
                                    'XML_ID' => 'lottery_prize',
                                    'VALUE' => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_EXTRA_PARAM.PROPERTY_PRODUCT_OBJECT_TYPE.OPTION_OBJECT_LOTTERY_PRIZE"),
                                ],
                                [
                                    'XML_ID' => 'intellectual_activity',
                                    'VALUE' => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_EXTRA_PARAM.PROPERTY_PRODUCT_OBJECT_TYPE.OPTION_OBJECT_INTELLECTUAL_ACTIVITY"),
                                ],
                                [
                                    'XML_ID' => 'payment',
                                    'VALUE' => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_EXTRA_PARAM.PROPERTY_PRODUCT_OBJECT_TYPE.OPTION_OBJECT_PAYMENT"),
                                ],
                                [
                                    'XML_ID' => 'agent_commission',
                                    'VALUE' => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_EXTRA_PARAM.PROPERTY_PRODUCT_OBJECT_TYPE.OPTION_OBJECT_AGENT_COMMISSION"),
                                ],
                                [
                                    'XML_ID' => 'composite',
                                    'VALUE' => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_EXTRA_PARAM.PROPERTY_PRODUCT_OBJECT_TYPE.OPTION_OBJECT_COMPOSITE"),
                                ],
                                [
                                    'XML_ID' => 'another',
                                    'VALUE' => Loc::getMessage("IPOL_ROBOKASSA_OPTIONS_TAB_EXTRA_PARAM.PROPERTY_PRODUCT_OBJECT_TYPE.OPTION_OBJECT_ANOTHER"),
                                ]
                            ]
                        ]
                    );
                }
            }
        }
    }