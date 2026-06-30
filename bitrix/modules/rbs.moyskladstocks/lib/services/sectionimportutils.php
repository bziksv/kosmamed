<?php

namespace Rbs\MoyskladStocks\Services;

use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\ApiNew;
use Rbs\MoyskladStocks\Config;

use Rbs\MoyskladStocks\Services\ImportParamsConfig;
use Rbs\MoyskladStocks\Internals\ProductFinder\ProductIdentifier;

use Bitrix\Main\Loader;
Loader::includeModule('iblock');

class SectionImportUtils
{
    /**
     * Import sections
     * @param array $items Items array
     * @param array $paramList Import parameters
     * @param array $importParamList Import parameters
     * @param int $cacheTime Cache time
     * @return array
     */
    public static function importSections($items = [], $paramList = [], $importParamList = [], $cacheTime = -1): array
    {
        if ($cacheTime === intval(-1)) {
            $cacheTime = 86400;
        }

        $result = [];

        $productFoldersArray = [];
        foreach ($items as $xmlId => $item) {
            if (!empty($item->{'productFolder'}->{'meta'}->{'href'})) {
                $productFoldersArray[$item->{'productFolder'}->{'meta'}->{'href'}] = $item->{'productFolder'};
            }
        }

        if (!Utils::is_count($productFoldersArray)) {
            return $result;
        }

        $importParamListSection = ['code_from_pf' => false];
        if ($importParamList['code_from_pf']) {
            $importParamListSection = ImportParamsConfig::getImportParams('productfolder');
            $importParamListSection['code_from_pf'] = true;
        }
        
        foreach ($productFoldersArray as $productFolderHref => $productFolder) {

            if (isset($result[$productFolderHref]) && (int)$result[$productFolderHref] > 0) {
                continue;
            }

            if (empty(ProductIdentifier::getSectionIdentifierValue($productFolder))) {
                $productFolder = ApiNew::get($productFolderHref, [], $cacheTime);
            }
                
            $sectionItemBxId = 0;
            $sectionTree = SectionImportUtils::getSectionTree($productFolder, $paramList, $importParamList, $cacheTime);

            if (Utils::is_count($sectionTree) > 0) {
                $sectionItemBxId = SectionImportUtils::getSectionBxFromSectionTree($sectionTree, $paramList, $importParamListSection);
            }

            if ((int)$sectionItemBxId > 0) {
                $result[$productFolderHref] = $sectionItemBxId;
            }

        }

        return $result;
    }

    /**
     * Get section tree
     * @param object $productFolder Section object
     * @param array $paramList Import parameters
     * @param array $importParamList Import parameters
     * @param int $cacheTime Cache time
     * @return array
     */
    public static function getSectionTree($productFolder, $paramList = [], $importParamList = [], $cacheTime = 0): array
    {
        $sectionTree = [];

        do {

            $nextProductFolder = false;
        
            $sectionXmlId = ProductIdentifier::getSectionIdentifierValue($productFolder);
            if (!Utils::is_success($productFolder) || empty($sectionXmlId) || empty($productFolder->{'id'}) || empty($productFolder->{'name'})) {
                break;
            }

            //skip if current section is root section
            $isSkipTree = false;
            if (isset($paramList['GROUP_ITEM'])) {

                if($paramList['GROUP_ITEM']->{'id'} === $productFolder->{'id'}) {
                    $isSkipTree = $importParamList['ms_section_root'];
                } else {
                    if (mb_strpos($productFolder->{'pathName'}, $paramList['GROUP_ITEM']->{'name'}) !== intval(0)) {
                        $isSkipTree = true;
                    }
                }

            }

            if (!$isSkipTree) {
                $sectionTree[] = [
                    'XML_ID' => $sectionXmlId,
                    'NAME' => $productFolder->{'name'},
                    'ACTIVE' => (bool)$productFolder->{'archived'} ? 'N' : 'Y',
                    'DESCRIPTION' => property_exists($productFolder, 'description') ? ($productFolder->{'description'} ?? '') : '',
                ];
            }
        
            if (!empty($productFolder->{'productFolder'}->{'meta'}->{'href'})) {
                $productFolder = ApiNew::get($productFolder->{'productFolder'}->{'meta'}->{'href'}, [], $cacheTime);
                $nextProductFolder = true;
            }

        } while ($nextProductFolder);

        krsort($sectionTree);

        return $sectionTree;
    }

    /**
     * Process section tree
     * @param array $sectionTree Section tree
     * @param array $paramList Import parameters
     * @param array $importParamList Import parameters
     * @param array $whParamList Import parameters
     * @return void
     */
    public static function sectionTreeProccess($sectionTree = [], $paramList = [], $importParamList = [], $whParamList = []): void
    {
        $sectionClass = new \CIBlockSection;
        $lastSectionIdParent = false;

        $translitParams = [];
        if ($paramList['IBLOCK_ID'] > 0 && $importParamList['translit']) {
            $translitParams = Config::getTranslitParamsIblockSectiton($paramList['IBLOCK_ID']);
        }
            
        foreach ($sectionTree as $sectionParams) {

            $rsSection = \Bitrix\Iblock\SectionTable::getList([
                'filter' => ['=XML_ID' => $sectionParams['XML_ID'], 'IBLOCK_ID' => $paramList['IBLOCK_ID']],
                'select' => ['ID', 'NAME', 'IBLOCK_SECTION_ID', 'CODE', 'ACTIVE', 'DESCRIPTION', 'DESCRIPTION_TYPE']
            ])->fetchAll();

            if (count($rsSection) > 0) {
                $sectionBx = array_pop($rsSection);

                $arUpdateFields = [];

                if ($whParamList['structure']) {
                    if ((int)$lastSectionIdParent != (int)$sectionBx['ID'] && (int)$sectionBx['IBLOCK_SECTION_ID'] != (int)$lastSectionIdParent) {
                        $arUpdateFields['IBLOCK_SECTION_ID'] = $sectionBx['IBLOCK_SECTION_ID'] = $lastSectionIdParent;
                    }
                }

                $lastSectionIdParent = $sectionBx['ID'];

                if ($whParamList['name']) {
                    if ($sectionParams['NAME'] !== $sectionBx['NAME']) {
                        $arUpdateFields['NAME'] = $sectionParams['NAME'];
                    }
                }

                if ($whParamList['code']) {
                    $sectionCodeTranslit = \CRbsMoyskladStocks::getTranslitCode($sectionParams['NAME'], $translitParams, $importParamList['trim']);
                    if ($importParamList['code_uniq']) {
                        $parentSectionForCheckCode = $importParamList['code_uniq_parent'] ? (int)$sectionBx['IBLOCK_SECTION_ID'] : 0;
                        $sectionCodeTranslit = SectionImportUtils::getSectionUniqCode($paramList['IBLOCK_ID'], $sectionCodeTranslit, $sectionBx['ID'], $parentSectionForCheckCode);
                    }
                    if ($sectionCodeTranslit !== $sectionBx['CODE']) {
                        $arUpdateFields['CODE'] = $sectionCodeTranslit;
                    }
                }

                if ($whParamList['descr']) {
                    $descrType = $importParamList['descr_type'] === 'html' ? 'html' : 'text';
                    $description = $sectionParams['DESCRIPTION'] ?? '';
                    if (!empty($description)) {
                        if ($sectionBx['DESCRIPTION'] !== $description || $sectionBx['DESCRIPTION_TYPE'] !== $descrType) {
                            $arUpdateFields['DESCRIPTION'] = $description;
                            $arUpdateFields['DESCRIPTION_TYPE'] = $descrType;
                        }
                    } elseif ($importParamList['descr_delete'] && !empty($sectionBx['DESCRIPTION'])) {
                        $arUpdateFields['DESCRIPTION'] = '';
                    }
                }

                if (count($arUpdateFields) > 0) {
                    $sectionClass->Update($lastSectionIdParent, $arUpdateFields);
                }
            } else {
                $sectionAddFields = [
                    'IBLOCK_ID' => $paramList['IBLOCK_ID'],
                    'XML_ID' => $sectionParams['XML_ID'],
                    'NAME' => $sectionParams['NAME'],
                    'ACTIVE' => $sectionParams['ACTIVE']
                ];

                if ($importParamList['descr'] && !empty($sectionParams['DESCRIPTION'])) {
                    $sectionAddFields['DESCRIPTION'] = $sectionParams['DESCRIPTION'];
                    $sectionAddFields['DESCRIPTION_TYPE'] = $importParamList['descr_type'] === 'html' ? 'html' : 'text';
                }

                if ($importParamList['code'] && $importParamList['code_uniq']) {
                    $sectionAddFields['CODE'] = $sectionParams['XML_ID'];
                }
                if ($importParamList['code'] && !$importParamList['code_uniq']) {
                    $sectionAddFields['CODE'] = \CRbsMoyskladStocks::getTranslitCode($sectionParams['NAME'], $translitParams, $importParamList['trim']);
                }

                if (isset($paramList['SECTION_ID']) && !$lastSectionIdParent) {
                    $sectionAddFields['IBLOCK_SECTION_ID'] = $paramList['SECTION_ID'];
                } elseif ($lastSectionIdParent > 0) {
                    $sectionAddFields['IBLOCK_SECTION_ID'] = $lastSectionIdParent;
                }

                if ($lastSectionIdParent = $sectionClass->Add($sectionAddFields)) {
                    if ($importParamList['code'] && $importParamList['code_uniq']) {
                        $translitCode = \CRbsMoyskladStocks::getTranslitCode($sectionParams['NAME'], $translitParams, $importParamList['trim']);
                        $parentSectionForCheckCode = $importParamList['code_uniq_parent'] ? (int)$sectionAddFields['IBLOCK_SECTION_ID'] : 0;
                        SectionImportUtils::checkSectionUniqCode($paramList['IBLOCK_ID'], $translitCode, $lastSectionIdParent, $parentSectionForCheckCode);
                    }
                }
            }
        }
    }

    /**
     * Get section ID from section tree
     * @param array $sectionTree Section tree
     * @param array $paramList Import parameters
     * @param array $importParamListSection Import parameters
     * @return int
     */
    public static function getSectionBxFromSectionTree($sectionTree, $paramList, $importParamListSection): int
    {
        $sectionClass = new \CIBlockSection;
        $lastSectionIdParent = false;

        $sectionItemBxId = 0;

        $translitParams = [];
        if ($paramList['IBLOCK_ID'] > 0 && $importParamListSection['translit']) {
            $translitParams = Config::getTranslitParamsIblockSectiton($paramList['IBLOCK_ID']);
        }

        foreach ($sectionTree as $sectionParams) {

            $rsSection = \Bitrix\Iblock\SectionTable::getList([
                'filter' => ['=XML_ID' => $sectionParams['XML_ID'], '=IBLOCK_ID' => $paramList['IBLOCK_ID']],
                'select' => ['ID', 'NAME', 'IBLOCK_SECTION_ID']
            ])->fetchAll();

            if (count($rsSection) > 0) {
                $sectionBx = array_pop($rsSection);
                $lastSectionIdParent = $sectionBx['ID'];
            } else {
                
                $sectionAddFields = [
                    'IBLOCK_ID' => $paramList['IBLOCK_ID'],
                    'XML_ID' => $sectionParams['XML_ID'],
                    'NAME' => $sectionParams['NAME'],
                    'ACTIVE' => $sectionParams['ACTIVE']
                ];

                if (isset($paramList['SECTION_ID']) && !$lastSectionIdParent) {
                    $sectionAddFields['IBLOCK_SECTION_ID'] = $paramList['SECTION_ID'];
                } elseif ($lastSectionIdParent > 0) {
                    $sectionAddFields['IBLOCK_SECTION_ID'] = $lastSectionIdParent;
                }

                if ($importParamListSection['code_from_pf']) {
                    if ($importParamListSection['code'] && !$importParamListSection['code_uniq']) {
                        $sectionAddFields['CODE'] = \CRbsMoyskladStocks::getTranslitCode($sectionParams['NAME'], $translitParams, $importParamListSection['trim']);
                    }
                    if ($importParamListSection['code'] && $importParamListSection['code_uniq']) {
                        $sectionAddFields['CODE'] = $sectionParams['XML_ID'];
                    }
                } else {
                    $sectionAddFields['CODE'] = $sectionParams['XML_ID'];
                }

                if ($lastSectionIdParent = $sectionClass->Add($sectionAddFields)) {
                    if ($importParamListSection['code_from_pf']) {
                        if ($importParamListSection['code'] && $importParamListSection['code_uniq']) {
                            $parentSectionForCheckCode = $importParamListSection['code_uniq_parent'] ? $sectionAddFields['IBLOCK_SECTION_ID'] : 0;
                            $sectionCodeForCheckCode = \CRbsMoyskladStocks::getTranslitCode($sectionParams['NAME'], $translitParams, $importParamListSection['trim']);
                            SectionImportUtils::checkSectionUniqCode($paramList['IBLOCK_ID'], $sectionCodeForCheckCode, $lastSectionIdParent, $parentSectionForCheckCode);
                        }
                    } else {
                        $sectionCodeTranslit = \CUtil::translit($sectionParams['NAME'], 'ru', $translitParams);
                        SectionImportUtils::checkSectionUniqCode($paramList['IBLOCK_ID'], $sectionCodeTranslit, $lastSectionIdParent);
                    }
                }
            }

            $sectionItemBxId = $lastSectionIdParent;
        }

        return $sectionItemBxId;
    }

    public static function checkSectionUniqCode($iblockId = 0, $translitCode = '', $currentSectionId = 0, $parentSection = 0): void
    {
        if ($iblockId > 0 && $currentSectionId > 0 && !empty($translitCode)) {
            $sectionClass = new \CIblockSection;
            $sectionClass->Update($currentSectionId, [
                'CODE' => SectionImportUtils::getSectionUniqCode($iblockId, $translitCode, $currentSectionId, $parentSection)
            ]);
        }
    }

    public static function getSectionUniqCode($iblockId = 0, $translitCode = '', $currentSectionId = 0, $parentSection = 0): string
    {
        if ($iblockId > 0 && $currentSectionId > 0 && !empty($translitCode)) {
            if ($parentSection > 0) {
                $rs = \CIblockSection::GetList([], ['IBLOCK_ID' => $iblockId, '=CODE' => $translitCode, '!ID' => $currentSectionId, 'SECTION_ID' => $parentSection]);
            } else {
                $rs = \CIblockSection::GetList([], ['IBLOCK_ID' => $iblockId, '=CODE' => $translitCode, '!ID' => $currentSectionId]);
            }
            if ($rs->SelectedRowsCount() > 0) {
                $translitCode .= "_" . $currentSectionId;
            }
        }
        return $translitCode;
    }
}