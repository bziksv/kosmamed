<?php

use Rbs\MoyskladStocks\HlCache\ExtCodes;
use Rbs\MoyskladStocks\HlCache\PFolder;
use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Import\Type\BundleStocks;
use Rbs\MoyskladStocks\ApiNew;
use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\Debug;
use Rbs\MoyskladStocks\LangMsg;
use Rbs\MoyskladStocks\AgentManager;
use Rbs\MoyskladStocks\Process\Helper as ProcessHelper;

use Rbs\MoyskladStocks\Services\MoyskladImportUtils;
use Rbs\MoyskladStocks\Services\PropertiesImportUtils;
use Rbs\MoyskladStocks\Services\SectionImportUtils;
use Rbs\MoyskladStocks\Services\ImportParamsConfig;

use Rbs\MoyskladStocks\Import\Entity\UpdateEntityItems;
use Rbs\MoyskladStocks\Import\Entity\CreateEntityItems;
use Rbs\MoyskladStocks\Import\Entity\ImportBundle;
use Rbs\MoyskladStocks\Internals\ProductFinder\ProductIdentifier;
use Rbs\MoyskladStocks\Internals\ProductFinder\EntityProductFinder;

\Bitrix\Main\Loader::includeModule('catalog');
\Bitrix\Main\Loader::includeModule('iblock');
\Bitrix\Main\Loader::includeModule('highloadblock');

class CRbsMoyskladStocks
{

    public static function import_entity(string $entity = 'product')
    {
        $process = new \Rbs\MoyskladStocks\Process\ImportEntity($entity);
        $process->execute();
        return $process->getResult();
    }

    public static function import_bundle_stocks($apiType = 'default')
    {
        $logger = new Debug\Loger();
        $agentManager = new AgentManager($apiType === 'current' ? 'bundle_stocks_current' : 'bundle_stocks');
        $agentManager->setConfigValue('limit', 100);
        $agentManager->setOnlyFullUpdate();

        try {

            if(Config::getImportBundleType() === 'BUNDLE') {
                throw new \Bitrix\Main\SystemException(LangMsg::get('WARNING_BUNDLE_STOCKS_IMPORT'));
            }
           
            $params = [
                'limit' => $agentManager->getLimit(),
                'offset' => $agentManager->getOffset(),
                'expand' => 'components',
                'filter' => self::buildAgentFilterStringForEntity('bundle', $agentManager)
            ];

            if (!empty($params['filter'])) {
                $logger->addInfoMessage(LangMsg::buildAgentFilterMessage($params['filter']));
            }

            ApiNew::refreshCountRequests();
            $msResult = ApiNew::get('/entity/bundle', $params);

            if (Utils::is_success($msResult)) {

                if (!empty($msResult->{'meta'}->{'size'})) {
                    $agentManager->setSize($msResult->{'meta'}->{'size'});
                }

                if (Utils::array_exists($msResult)) {

                    foreach ($msResult->{'rows'} as $key => $row) {
                        $msResult->{'rows'}[$key]->{'meta'}->{'href'} = explode('?', $row->{'meta'}->{'href'})[0];
                    }

                    BundleStocks::update($msResult->{'rows'}, $logger, $apiType);

                    $logger->addSuccessMessage(LangMsg::get('AGENT_IMPORT_BUNDLE_STOCKS_SUCCESS', [
                        '#COUNT#' => $msResult->{'meta'}->{'size'}
                    ]));

                } else {
                    if ($agentManager->isFullUpdate()) {
                        $logger->addWarningMessage(LangMsg::get('WARNING_EMPTY_ROWS'));
                    } else {
                        $logger->addInfoMessage(LangMsg::get('INFO_EMPTY_ROWS'));
                    }
                }

                if (!empty($msResult->{'meta'}->{'nextHref'})) {
                    $agentManager->setNextStepOffset();
                } else {
                    $agentManager->setFinalStepParams();
                }

            } else {
                if (Utils::has_errors($msResult)) {
                    $logger->addErrorMessageArray($msResult->{'errors'});
                } else {
                    throw new \Bitrix\Main\SystemException(LangMsg::get('EXCEPTION_API_ERROR'));
                }
            }

        } catch (\Throwable $e) {
            $logger->addErrorMessage(Utils::build_exception_message($e));
        }

        $logger->addFinishMessage(LangMsg::buildAgentFinishMessage($logger->getLogTime()));
        $logger->exportLog(LangMsg::buildAgentHeadMessage($agentManager));

        return (object)[
            'logger' => $logger,
            'agentManager' => $agentManager
        ];
    }

    public static function buildAgentFilterStringForEntity(string $entity = '', AgentManager $agentManager, array $customFilter = [], bool $skipBuildStandardFilterString = false): string
    {
        if (empty($entity)) {
            throw new \Bitrix\Main\SystemException(LangMsg::get('WARNING_EMPTY_ENTITY'));
        }  

        if (!$agentManager->isFullUpdate()) {
            $customFilter[] = 'updated>=' . $agentManager->getLastDateUpdate();
        }

        $customFilterStr = '';
        if (count($customFilter) > 1) {
            $customFilterStr = implode(';', $customFilter);
        } elseif (count($customFilter) === intval(1)) {
            $customFilterStr = current($customFilter);
        }

        if($skipBuildStandardFilterString) {
            return $customFilterStr;
        }

        return self::getFilterString($entity, $customFilterStr);
    }

    public static function getArrLog($entity = 'product', $limit = 0, $offset = 0)
    {
        return  [
            '#ENTITY#' => $entity,
            '#LIMIT#' => $limit,
            '#OFFSET#' => $offset,
            '#ITEMS#' => 0,
            '#FIND#' => 0,
            '#ADD#' => 0,
            '#UPDATE#' => 0,
            '#ERROR#' => 0,
            'ERROR_LIST' => [],
            'INFO_LIST' => [],
            '#API_COUNT#' => 0,
            '#API_TIME#' => 0
        ];
    }

    public static function getParamList($entity = 'productfolder')
    {
        $paramList = [];

        $catalogIblockId = Config::getIblockId($entity);
        $paramList = ['IBLOCK_ID' => $catalogIblockId];

        if(ProcessHelper::isNeedGroupItem($entity)) {
            $groupItem = self::getGroupItem($entity);
            if (Utils::property_exists($groupItem, ['meta', 'href'])) {
                $paramList['GROUP_ITEM'] = $groupItem;
            }
        }        

        $sectionId = Config::getSectionId($entity);
        if ($sectionId > 0) {
            $rsSection = \Bitrix\Iblock\SectionTable::getList([
                'filter' => ['ID' => $sectionId, 'IBLOCK_ID' => $catalogIblockId],
                'select' => ['ID']
            ])->fetchAll();
            if (count($rsSection) > 0) {
                $paramList['SECTION_ID'] = $sectionId;
            }
        }

        return $paramList;
    }

    public static function checkUpdateHook($item = null)
    {
        if (!is_object($item)) {
            return false;
        }

        $itemXmlId = ProductIdentifier::getIdentifierValue($item);
        $filter = ProductIdentifier::buildSingleFilter($itemXmlId, $item->{'meta'}->{'type'});
        
        $catalogIblockId = Config::getIblockId($item->{'meta'}->{'type'});
        if ((int)$catalogIblockId <= 0) {
            return;
        }

        $filter['IBLOCK_ID'] = $catalogIblockId;

        if (empty($itemXmlId)) {
            return;
        }

        $rsItem = \Bitrix\Iblock\ElementTable::getList([
            'order' => ['ID' => 'DESC'],
            'filter' => $filter,
            'select' => ['*'],
            'cache' => ['ttl' => 0]
        ])->fetch();
  
        $whParamList = ImportParamsConfig::getWhParams($item->{'meta'}->{'type'});
        if (empty($whParamList)) {
            return false;
        }

        if ((int)$rsItem['ID'] > 0) {
            $arItems = [
                $itemXmlId => [
                    'BX' => $rsItem,
                    'MS' => $item
                ]
            ];

            $importParamList = ImportParamsConfig::getImportParams($item->{'meta'}->{'type'});

            $paramList = self::getParamList($item->{'meta'}->{'type'});

            if(ProcessHelper::isNeedGroupItem($item->{'meta'}->{'type'})) {
                if(!isset($paramList['GROUP_ITEM'])) {
                    return false;
                }
            }

            $paramList['IS_SKU'] = $item->{'meta'}->{'type'} === 'variant';
            if ($paramList['IS_SKU']) {
                $skuParamIblock = \Bitrix\Catalog\CatalogIblockTable::getList(['filter' => ['IBLOCK_ID' => $paramList['IBLOCK_ID']]])->fetch();
                if (
                    isset($skuParamIblock['PRODUCT_IBLOCK_ID']) && isset($skuParamIblock['SKU_PROPERTY_ID']) &&
                    (int)$skuParamIblock['PRODUCT_IBLOCK_ID'] > 0 && (int)$skuParamIblock['SKU_PROPERTY_ID'] > 0
                ) {
                    $paramList['IS_SKU'] = true;
                    $paramList['PRODUCT_IBLOCK_ID'] = $skuParamIblock['PRODUCT_IBLOCK_ID'];
                    $paramList['SKU_PROPERTY_ID'] = $skuParamIblock['SKU_PROPERTY_ID'];
                }
            }
            

            $arSectionsItems = [];
            if ($whParamList['folder'] && !$paramList['IS_SKU'] && !$importParamList['section_off']) {
                $arSectionsItems = SectionImportUtils::importSections([$item], $paramList, $importParamList);
            }

            $arrLog = self::getArrLog($item->{'meta'}->{'type'}, 1, 0);
            UpdateEntityItems::update($item->{'meta'}->{'type'}, $arItems, $whParamList, $arSectionsItems, $paramList, $arrLog);
        }
    }

    public static function checkUpdateHookSection($item = null)
    {
        if (!is_object($item)) {
            return false;
        }
  
        $importParamList = ImportParamsConfig::getImportParams($item->{'meta'}->{'type'});
        $whParamList = ImportParamsConfig::getWhParams($item->{'meta'}->{'type'});
        
        if (empty($whParamList)) {
            return false;
        }

        self::updateSection($item, $importParamList, $whParamList);
    }

    public static function updateSection($folder = null, $importParamList = [], $whParamList = [])
    {
        $productFolder = $folder;
        $paramList = self::getParamList('productfolder');
        if (ProcessHelper::isNeedGroupItem('productfolder')) {
            if (!isset($paramList['GROUP_ITEM'])) {
                return false;
            }
        }
        $sectionTree = SectionImportUtils::getSectionTree($productFolder, $paramList, $importParamList);
        if (count($sectionTree) > 0) {
            SectionImportUtils::sectionTreeProccess($sectionTree, $paramList, $importParamList, $whParamList);
        }
    }

    public static function importOne($product = null)
    {
        if (Utils::is_success($product)) {
            $entity = $product->{'meta'}->{'type'};
            if (Config::checkFeature('import_' . $entity)) {
                if(ProcessHelper::isNeedGroupItem($entity)) {
                    $groupItem = self::getGroupItem($entity);
                    if(Utils::property_exists($groupItem, ['name'])) {
                        if (mb_strpos($product->{'pathName'}, $groupItem->{'name'}) !== intval(0)) {
                            return false;
                        }
                    } else {
                        return false;  
                    }                    
                }
                $arrLog = self::getArrLog($entity, 1, 0);
                self::importItems([$product], $entity, $arrLog);
            }
        }
    }

    public static function importOneSection($folder = null)
    {
        if (Utils::is_success($folder)) {
            $entity = $folder->{'meta'}->{'type'};
            $importParamList = ImportParamsConfig::getImportParams($entity);
            if (Config::checkFeature('import_' . $entity)) {
                if (PFolder::isExsist()) {
                    PFolder::update([$folder]);
                }

                if (ProcessHelper::isNeedGroupItem($entity)) {
                    $groupItem = self::getGroupItem($entity);
                    if(Utils::property_exists($groupItem, ['name'])) {
                        if (empty($folder->{'pathName'})) {
                            if ($folder->{'name'} === $groupItem->{'name'} && $importParamList['ms_section_root']) {
                                return false;
                            }
                        }
                        if (mb_strpos($folder->{'pathName'}, $groupItem->{'name'}) !== intval(0)) {
                            return false;
                        }
                    } else {
                        return false;
                    }
                }

                self::importSection($folder, $importParamList);
            }
        }
    }

    public static function importSection($folder = null, $importParamList = [])
    {
        $productFolder = $folder;        
        $paramList = self::getParamList('productfolder');
        if (ProcessHelper::isNeedGroupItem('productfolder')) {
            if (!isset($paramList['GROUP_ITEM'])) {
                return false;
            }
        }
        $sectionTree = SectionImportUtils::getSectionTree($productFolder, $paramList, $importParamList);
        if (count($sectionTree) > 0) {
            SectionImportUtils::sectionTreeProccess($sectionTree, $paramList, $importParamList);
        }
    }

    public static function getFilterString($entity = '', $customFilterStr = '', $disableVariantFilter = false)
    {
        $filterArray = [];

        if (!empty($customFilterStr)) {
            $filterArray[] = $customFilterStr;
        }

        //temporary
        $isDisableVariantFilter = $entity === 'variant' && $disableVariantFilter;
        if($isDisableVariantFilter) {
            return count($filterArray) > 0 ? implode(';', $filterArray) : '';
        }

        if(ProcessHelper::isNeedGroupItem($entity)) {
            if($entity === 'variant') {
                $groupId = Config::getGroupId($entity);
                $entityBuilder = new \Rbs\MoyskladStocks\Services\EntityMetaBuilder('productfolder', $groupId);
                $filterArray[] = 'productFolder=' . $entityBuilder->getMetaHref();
            } else {
                $groupItem = self::getGroupItem($entity);
                if(Utils::property_exists($groupItem, ['name'])) {
                    $filterArray[] = 'pathName~=' . $groupItem->{'name'};
                } else {
                    throw new \Bitrix\Main\SystemException(LangMsg::get('EXCEPTION_GET_FILTER_STRING_GROUP'));
                }
            }
        }  

        $filterPropId = Config::getFilterPropId($entity);
        if ($filterPropId !== 'N') {
            $filterPropValue = Config::getFilterPropValue($entity);
            $filterArray[] = Config::getFilterPropString($filterPropId, $filterPropValue, $entity);
        }

        return count($filterArray) > 0 ? implode(';', $filterArray) : '';
    }

    public static function importItems($items = [], $entity = '', &$arrLog = [], $isWebhookUpdate = false)
    {
        //check catalog id
        $catalogIblockId = Config::getIblockId($entity);
        if ((int)$catalogIblockId <= 0) {
            $arrLog['ERROR_LIST'][] = LangMsg::get('WARNING_EMPTY_CATALOG_IBLOCK');
            $arrLog['#ERROR#']++;
            return;
        }
        
        $paramList = ['IBLOCK_ID' => $catalogIblockId];

        if(ProcessHelper::isNeedGroupItem($entity)) {
            $groupItem = self::getGroupItem($entity);
            if (Utils::property_exists($groupItem, ['meta', 'href'])) {
                $paramList['GROUP_ITEM'] = $groupItem;
            } else {
                $arrLog['ERROR_LIST'][] = LangMsg::get('WARNING_EMPTY_GROUP_ITEM');
                $arrLog['#ERROR#']++;
                return;
            }
        }

        //check sku props
        $paramList['IS_SKU'] = false;
        if ($entity === 'variant') {
            $skuParamIblock = \Bitrix\Catalog\CatalogIblockTable::getList(['filter' => ['IBLOCK_ID' => $paramList['IBLOCK_ID']]])->fetch();
            if (
                isset($skuParamIblock['PRODUCT_IBLOCK_ID']) && isset($skuParamIblock['SKU_PROPERTY_ID']) &&
                (int)$skuParamIblock['PRODUCT_IBLOCK_ID'] > 0 && (int)$skuParamIblock['SKU_PROPERTY_ID'] > 0
            ) {
                $paramList['IS_SKU'] = true;
                $paramList['PRODUCT_IBLOCK_ID'] = $skuParamIblock['PRODUCT_IBLOCK_ID'];
                $paramList['SKU_PROPERTY_ID'] = $skuParamIblock['SKU_PROPERTY_ID'];
            } else {
                $arrLog['ERROR_LIST'][] = LangMsg::get('WARNING_EMPTY_CATALOG_PARENT_FOR_SKU');
                $arrLog['#ERROR#']++;
                return;
            }
        }

        //check section from bx
        $sectionId = (int)Config::getSectionId($entity);
        if ($sectionId > 0) {
            $rsSection = \Bitrix\Iblock\SectionTable::getList([
                'filter' => ['ID' => $sectionId, 'IBLOCK_ID' => $catalogIblockId],
                'select' => ['ID']
            ])->fetchAll();
            if (!Utils::is_count($rsSection)) {
                $arrLog['ERROR_LIST'][] = LangMsg::get('WARNING_EMPTY_SECTION_IMPORT');
                $arrLog['#ERROR#']++;
                return;
            }
            $paramList['SECTION_ID'] = $sectionId;
        } else {
            $sectionId = null;
        }

        //read importparam list
        $importParamList = ImportParamsConfig::getImportParams($entity);
        
        //set ext codes for items
        $arItems = [];
        foreach ($items as $row) {
            $row->{'meta'}->{'href'} = explode('?', $row->{'meta'}->{'href'})[0];
            $arItems[ProductIdentifier::getIdentifierValue($row)] = $row;
        }

        $findResult = EntityProductFinder::findExistingElements($arItems, $entity, $catalogIblockId);
        $arFindedItems = $findResult['found'];
        $arItems = $findResult['new'];
        
        $arSectionsItems = [];
        if (count($arItems) > 0 && !Config::isOnlyUpdate($entity)) {

            //refresh extcodes
            if (ProductIdentifier::isExtCodesRequired() && ExtCodes::isExsist()) {
                ExtCodes::update($arItems);
            }

            //import sections from ms
            if (!$paramList['IS_SKU'] && !$importParamList['section_off']) {
                $arSectionsItems = SectionImportUtils::importSections($arItems, $paramList, $importParamList);
            }
            
            //import new items
            CreateEntityItems::create($entity, $arItems, $arSectionsItems, $paramList, $arrLog);
        }

        if(Config::isOnlyUpdate($entity)) {
            $arrLog['INFO_LIST'] = LangMsg::get('INFO_ONLY_UPDATE_ITEMS');
        }

        //update finded items
        if (count($arFindedItems) > 0) {

            $updateParamList = ImportParamsConfig::getUpParams($entity);
            if ($isWebhookUpdate) {
                $updateParamList = ImportParamsConfig::getWhParams($entity);
            }

            if ($updateParamList['folder'] && !$paramList['IS_SKU'] && !$importParamList['section_off']) {
                $arUpdatedItems = [];
                foreach ($arFindedItems as $xmlId => $item) {
                    $arUpdatedItems[$xmlId] = $item['MS'];
                }

                if (count($arUpdatedItems) > 0) {
                    $arSectionsItems = SectionImportUtils::importSections($arUpdatedItems, $paramList, $importParamList);
                }
            }
            
            $arrLog['#FIND#'] += count($arFindedItems);
            UpdateEntityItems::update($entity, $arFindedItems, $updateParamList, $arSectionsItems, $paramList, $arrLog);

        }
    }

    public static function getTranslitCode($name = '', $translitParams = [], $trim = false)
    {
        return $trim ? \CUtil::translit($name, 'ru', $translitParams) : \CUtil::translit(trim($name), 'ru', $translitParams);
    }

    public static function getElementUniqCode($iblockId = 0, $translitCode = '', $elementId = 0)
    {
        if ($iblockId > 0 && $elementId > 0 && !empty($translitCode)) {
            $rs = \CIblockElement::GetList([], ['IBLOCK_ID' => $iblockId, '=CODE' => $translitCode, '!ID' => $elementId]);
            if ($rs->SelectedRowsCount() > 0) {
                $translitCode .= "_" . $elementId;
            }
        }
        return $translitCode;
    }    
    
    public static function getGroupItem($entity = '')
    {
        if (ProcessHelper::isNeedGroupItem($entity)) {
            $groupId = Config::getGroupId($entity);
            $result = ApiNew::get('/entity/productfolder/' . $groupId, [], 86400);
            if(Utils::is_success($result) && Utils::property_exists($result, ['meta', 'href'])) {
                return $result;
            }
        }
        return false;
    }

    public static function getLicenseData(bool $isDebug = false): string
    {
        $result = '';
        $logger = new \Rbs\MoyskladStocks\Debug\Loger();

        try {

            $hashKey = '';
            try {
                
                $application = \Bitrix\Main\Application::getInstance();
                if (method_exists($application, 'getLicense')) {
                    $license = $application->getLicense();
                    if (method_exists($license, 'getPublicHashKey')) {
                        $hashKey = $license->getPublicHashKey();
                    }
                }

                if (empty($hashKey)) {
                    require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/classes/general/update_client_partner.php");
                    $hashKey = md5("BITRIX" . \CUpdateClientPartner::GetLicenseKey() . "LICENCE");
                }
                
                $logger->addInfoMessage(
                    LangMsg::get('LICENSE_DATA_MESSAGE_HASH_KEY', ['#HASH#' => $hashKey])
                );

            } catch (\Throwable  $e) {
                throw new \Exception(
                    LangMsg::get('LICENSE_DATA_ERROR_GET_HASH', ['#ERROR#' => $e->getMessage()])
                );
            } finally {
                if (empty($hashKey)) {
                    throw new \Exception(
                        LangMsg::get('LICENSE_DATA_ERROR_EMPTY_HASH')
                    );
                }
            }

            $http = new \Bitrix\Main\Web\HttpClient();
            $http->setHeader('Content-Type', 'application/json');

            $requestData = json_encode([
                'license_key' => $hashKey,
                'is_dev_site' => \COption::GetOptionString("main", "update_devsrv", "") == "Y",
                'domain' => $_SERVER['SERVER_NAME'],
                'module_id' => Config::getModuleId(true),
            ]);

            $response = $http->post('https://api.despi.ru/v1/check', $requestData);

            $encodedResponse = json_decode($response, true);
            if (isset($encodedResponse['data']) && is_string($encodedResponse['data'])) {
                $result = $encodedResponse['data'];
                $logger->addSuccessMessage(
                    LangMsg::get('LICENSE_DATA_SUCCESS_LICENSE')
                );
            } else {
                $logger->addErrorMessage(
                    LangMsg::get('LICENSE_DATA_ERROR_REQUEST', ['#REQUEST#' => json_encode($requestData)])
                );
                $logger->addErrorMessage(
                    LangMsg::get('LICENSE_DATA_ERROR_RESPONSE', ['#RESPONSE#' => $response])
                );
            }

        } catch (\Throwable $e) {

            $logger->addErrorMessage(
                LangMsg::get('LICENSE_DATA_ERROR_GET_DATA', ['#ERROR#' => $e->getMessage()])
            );
            $result = '';

        } finally {
            if ($isDebug) {
                $logger->exportLog(
                    LangMsg::get('LICENSE_DATA_LOG_MODULE_CHECK', ['#MODULE#' => Config::getModuleId(true)])
                );
            }
        }

        return $result;
    }

    /** @deprecated */
    public static function importBundle($productId = 0, $item = null, $checkUpdate = false, $bundlePartIblockId = [], &$arrLog = [])
    {
        ImportBundle::import($productId, $item, $checkUpdate, $bundlePartIblockId, $arrLog);
    }

    /** @deprecated */
    public static function importNewItems($entity = 'product', $arItems = [], $arSectionsItems = [], $paramList = [], &$arrLog = [])
    {
        CreateEntityItems::create($entity, $arItems, $arSectionsItems, $paramList, $arrLog);
    }

    /** @deprecated */
    public static function updateNewItems($entity = 'product', $arItems = [], $whParamList = [], $arSectionsItems = [], $paramList = [], &$arrLog = [])
    {
        UpdateEntityItems::update($entity, $arItems, $whParamList, $arSectionsItems, $paramList, $arrLog);
    }

    /** @deprecated */
    public static function importSections($items = [], $paramList = [], $importParamList = [], $cacheTime = -1): array
    {
        return SectionImportUtils::importSections($items, $paramList, $importParamList, $cacheTime);
    }

    /** @deprecated */
    public static function checkSectionUniqCode($iblockId = 0, $translitCode = '', $currentSectionId = 0, $parentSection = 0): void
    {
        SectionImportUtils::checkSectionUniqCode($iblockId, $translitCode, $currentSectionId, $parentSection);
    }

    /** @deprecated */
    public static function getSectionUniqCode($iblockId = 0, $translitCode = '', $currentSectionId = 0, $parentSection = 0): string
    {
        return SectionImportUtils::getSectionUniqCode($iblockId, $translitCode, $currentSectionId, $parentSection);
    }

    /** @deprecated */
    public static function getSectionBxFromSectionTree($sectionTree, $paramList, $importParamListSection): int
    {
        return SectionImportUtils::getSectionBxFromSectionTree($sectionTree, $paramList, $importParamListSection);
    }

    /** @deprecated */
    public static function sectionTreeProccess($sectionTree = [], $paramList = [], $importParamList = [], $whParamList = []): void
    {
        SectionImportUtils::sectionTreeProccess($sectionTree, $paramList, $importParamList, $whParamList);
    }

    /** @deprecated */
    public static function getSectionTree($productFolder, $paramList = [], $importParamList = [], $cacheTime = 0): array
    {
        return SectionImportUtils::getSectionTree($productFolder, $paramList, $importParamList, $cacheTime);
    }

    /** @deprecated */
    public static function setPropsForItem($itemBx = [], $itemMs = null, $propList = [], $propBxTypes = [], $isUpdateFacet = false, $isEmptyImport = false, $arAllPropsValues = [], $isNew = false)
    {
        PropertiesImportUtils::setPropsForItem($itemBx, $itemMs, $propList, $propBxTypes, $isUpdateFacet, $isEmptyImport, $arAllPropsValues, $isNew);
    }

    /** @deprecated */
    public static function importFilesInProp($currentMsImagesArray = [], $itemBx = [], $bxPropValue = [], $bxPropId = 0, $canDelete = false, $isMultipleProp = true)
    {
        PropertiesImportUtils::importFilesInProp($currentMsImagesArray, $itemBx, $bxPropValue, $bxPropId, $canDelete, $isMultipleProp);
    }

    /** @deprecated */
    public static function getMsFilesArray($filesObject = null, $size = 0): array
    {
        return MoyskladImportUtils::getMsFilesArray($filesObject, $size);
    }

    /** @deprecated */    
    public static function getAllAttrList($attributes = []): array
    {
        return MoyskladImportUtils::getAllAttrList($attributes);
    }

    /** @deprecated */
    public static function getAttrList($attributes = []): array
    {
        return MoyskladImportUtils::getAttrList($attributes);
    }

    /** @deprecated */
    public static function getSkuProps($iblockId = 0): array
    {
        return PropertiesImportUtils::getSkuProps($iblockId);
    }

    /** @deprecated */
    public static function setSkuProps($itemBx = [], $itemMs = null, &$propSkuList, $isUpdateFacet = false): void
    {
        PropertiesImportUtils::setSkuProps($itemBx, $itemMs, $propSkuList, $isUpdateFacet);
    }

    /** @deprecated */
    public static function deactivate_entity(string $entity = '', bool $isFilterDeactivate = false) {}
    
    /** @deprecated */
    private static function deactivateItemsByEntityItemRows($entity, $catalogIblockId, $entityItems, &$loger, $successMsgId = 'AGENT_IMPORT_ARCHIVED_ITEMS_SUCCESS') {}
    
    /** @deprecated */
    public static function createProductFolderCache(){}
    
    /** @deprecated */
    public static function getVariantParentItem($item){}
    
    /** @deprecated */
    public static function importArchivedItems($entity = 'product', $limit = 100, &$offset = 0){}
    
    /** @deprecated */
    public static function deactivateEntityByFilter($entity = 'product', $limit = 100, &$offset = 0){}
    
    /** @deprecated */
    public static function importAll($limit = 100, &$offset = 0, $entity = ''){}
    
    /** @deprecated */
    public static function importComponents($limit = 10, &$offset = 0, $entity = ''){}
    
    /** @deprecated */
    public static function get_expand_params(string $entity): string
    {
        return ProcessHelper::buildExpandParams($entity);
    }
    
    /** @deprecated */
    public static function isNeedGroupItem($entity = ''): bool
    {
        return ProcessHelper::isNeedGroupItem($entity);
    }
    
    /** @deprecated */
    public static function importByApiRequest($entity = 'product', $customFilter = '', $offset = 0, $isWebhookUpdate = false)
    {
        return \Rbs\MoyskladStocks\Compitable\IncludeClass::importByApiRequest($entity, $customFilter, $offset, $isWebhookUpdate);
    }    

}
?>