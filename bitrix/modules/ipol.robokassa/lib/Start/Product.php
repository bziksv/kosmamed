<?php

    namespace Ipol\Robokassa\Start;

    use Bitrix\Iblock\IblockTable;
    use Bitrix\Main\Config\Option;
    use Bitrix\Main\HttpApplication;
    use Bitrix\Main\Loader;
    use Bitrix\Main\Localization\Loc;
    use Ipol\Robokassa;

    Loc::loadMessages(__FILE__);

    /**
     * Class Product
     * @package Ipol\Robokassa\Start
     */
    final class Product
    {

        /**
         * Код свойства цены в инфоблоке
         */
        public const IBLOCK_PRICE_PROPERTY_CODE = Configuration::IBLOCK_PRICE_PROPERTY_CODE;

        /**
         * Код свойства остатков в инфоблоке
         */
        public const IBLOCK_QUANTITY_PROPERTY_CODE = Configuration::IBLOCK_QUANTITY_PROPERTY_CODE;

        /**
         * iblock.id
         * @var int|null
         */
        private ?int $iblockId = null;

        /**
         * element.id
         * @var int|null
         */
        private ?int $elementId = null;

        /**
         * section.id
         * @var int|null
         */
        private ?int $sectionId = null;

        /**
         * События окончания генерации страницы
         * @return void
         */
        public static function onEpilog(): void
        {

            $instance = new self();

            if(\strpos('bitrix/admin', HttpApplication::getInstance()->getContext()->getRequest()->getRequestedPage()))
            {
                return;
            }

            if(!$instance->loadIblocks())
            {
                return;
            }

            if((int) $instance->elementId > 0)
            {

                $GLOBALS['APPLICATION']->IncludeComponent(
                    'ipol:robokassa.start.element',
                    '',
                    [
                        'IBLOCK_ID' => $instance->iblockId,
                        'SECTION_ID' => $instance->sectionId,
                        'ELEMENT_ID' => $instance->elementId,
                    ]
                );
            }
        }

        private function loadIblocks(): bool
        {

            if(!Loader::includeModule("iblock"))
            {
                return false;
            }

            $this->iblockId = (int) Option::get(
                Robokassa\RobokassaPaymentService::$moduleId,
                'START_FUNCTION_IBLOCK_ID',
                0
            );

            if(empty($this->iblockId))
            {
                return false;
            }

            $iblock = IblockTable::getList(
                [
                    'filter' => [
                        'ACTIVE' => 'Y',
                        'ID' => $this->iblockId
                    ]
                ]
            )->fetch();

            $site = \CSite::GetByID(SITE_ID)->GetNext(true, false);

            $componentSefMode = Option::get(
                Robokassa\RobokassaPaymentService::$moduleId,
                'START_FUNCTION_COMPONENT_SEF_MODE',
                'N'
            ) === 'Y';

            $componentSefFolder = Option::get(
                Robokassa\RobokassaPaymentService::$moduleId,
                'START_FUNCTION_COMPONENT_SEF_FOLDER',
                ''
            );

            $arDefaultUrlTemplates404 = array(
                'element' => '#SECTION_ID#/#ELEMENT_ID#/',
            );

            $arDefaultVariableAliases = array();

            $arComponentVariables = array(
                "SECTION_ID",
                "SECTION_CODE",
                "ELEMENT_ID",
                "ELEMENT_CODE",
                "action",
            );

            $engine = new \CComponentEngine();

            $engine->addGreedyPart("#SECTION_CODE_PATH#");
            $engine->addGreedyPart("#SMART_FILTER_PATH#");
            $engine->setResolveCallback(array("CIBlockFindTools", "resolveComponentEngine"));

            $iblock['DETAIL_PAGE_URL'] = \strtr(
                $iblock['DETAIL_PAGE_URL'],
                [
                    '#SITE_DIR#' => $site['DIR'],
                ]
            );

            $iblock['DETAIL_PAGE_URL'] = str_replace('//', '/', $iblock['DETAIL_PAGE_URL']);
            $iblock['DETAIL_PAGE_URL'] = str_replace($componentSefFolder, '', $iblock['DETAIL_PAGE_URL']);

            if($componentSefMode)
            {
                $arUrlTemplates = \CComponentEngine::makeComponentUrlTemplates(
                    $arDefaultUrlTemplates404,
                    [
                        'element' => $iblock['DETAIL_PAGE_URL'],
                    ]
                );

                $componentPage = $engine->guessComponentPath(
                    $componentSefFolder,
                    $arUrlTemplates,
                    $arVariables
                );

                if($componentPage === 'element')
                {

                    $this->iblockId = (int) $iblock['ID'];

                    $this->sectionId = \CIBlockFindTools::GetSectionID(
                        $arVariables['SECTION_ID'],
                        $arVariables['SECTION_CODE'],
                        [
                            'GLOBAL_ACTIVE' => 'Y',
                            'IBLOCK_ID' => $this->iblockId
                        ]
                    );

                    if(!empty($arVariables['SECTION_CODE_PATH']))
                    {
                        if(\CIBlockFindTools::checkSection($this->iblockId, $arVariables))
                        {
                            $this->sectionId = $arVariables['SECTION_ID'];
                        }
                    }

                    if((int) $this->sectionId === 0)
                    {
                        $this->sectionId = null;
                    }

                    $this->elementId = \CIBlockFindTools::GetElementID(
                        $arVariables["ID"] ?? $arVariables["ELEMENT_ID"],
                        $arVariables["ELEMENT_CODE"],
                        ($this->sectionId ?? false),
                        false,
                        array(
                            "IBLOCK_ID" => $this->iblockId,
                            "IBLOCK_LID" => SITE_ID,
                            "IBLOCK_ACTIVE" => "Y",
                            "ACTIVE_DATE" => "Y",
                            "CHECK_PERMISSIONS" => "Y",
                        )
                    );

                    if($this->elementId === null || $this->elementId === 0)
                    {
                        return false;
                    }

                    return true;
                }
            }
            else
            {
                $arVariableAliases = \CComponentEngine::makeComponentVariableAliases(
                    $arDefaultVariableAliases,
                    [
                        'ELEMENT_ID' => 'ELEMENT_ID',
                        'SECTION_ID' => 'SECTION_ID',
                    ]
                );

                \CComponentEngine::initComponentVariables(
                    false,
                    $arComponentVariables,
                    $arVariableAliases,
                    $arVariables
                );

                if((int) $arVariables['ELEMENT_ID'] > 0)
                {

                    $this->elementId = (empty($arVariables['ELEMENT_ID'])) ? null : (int) $arVariables['ELEMENT_ID'];
                    $this->sectionId = (empty($arVariables['SECTION_ID'])) ? null : (int) $arVariables['SECTION_ID'];

                    return true;
                }
            }


            return false;
        }

        public static function formatPrice($price): string
        {
            return Loc::getMessage(
                'IPOL_ROBOKASSA_PRODUCT_PRICE_FORMAT',
                [
                    '#PRICE#' => number_format(
                        $price,
                        2,
                        '.',
                        '&nbsp;'
                    )
                ]
            );
        }
    }