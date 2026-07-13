<?php

namespace Rbs\MoyskladStocks\Services;

use Bitrix\Main\Loader;

use Rbs\MoyskladStocks\ApiNew;
use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\Internals\ProductFinder\ProductIdentifier;

Loader::includeModule('iblock');

class PropertiesImportUtils
{
    /**
     * Get property values
     * @param array $arItems Items array
     * @param array $propList Properties list
     * @param int $catalogIblockId Iblock ID
     * @return array
     */
    public static function getPropsValues($arItems = [], $propList = [], $catalogIblockId = 0): array
    {
        if(count($arItems) <= 0 || count($propList) <= 0 || (int)$catalogIblockId <= 0){
            return [];
        }

        $arPropsResult = [];
        $arXmlSearch = [];
        foreach($arItems as $xmlId => $currentItem){
            $arPropsResult[$currentItem['BX']['ID']] = [
                'PROPERTIES' => []
            ];
            if (ProductIdentifier::isVariantCompositeMode($currentItem['MS']->meta->type)) {
                $arXmlSearch[] = '%#' . $xmlId;
            } else {
                $arXmlSearch[] = $xmlId;
            }
        }
        
        \CIBlockElement::GetPropertyValuesArray($arPropsResult, $catalogIblockId, ['XML_ID' => $arXmlSearch], ['ID' => array_keys($propList)], ['USE_PROPERTY_ID' => 'Y', 'GET_RAW_DATA' => 'Y']);
        $arPropsForAllItems = [];
        foreach($arPropsResult as $itemId => $data){
            foreach($data['PROPERTIES'] as $propId => $prop){
                $arPropsForAllItems[$itemId][$propId] = [
                    'VALUE' => $prop['VALUE'],
                    'VALUE_ENUM' => $prop['VALUE_ENUM'],
                    'VALUE_XML_ID' => $prop['VALUE_XML_ID'],
                    'DESCRIPTION' => $prop['DESCRIPTION'],
                    'PROPERTY_VALUE_ID' => $prop['PROPERTY_VALUE_ID']
                ];
            }
        }
        unset($arPropsResult);

        return $arPropsForAllItems;
    }

    /**
     * Get SKU properties
     * @param int $iblockId Iblock ID
     * @return array ['PROP_IDS' => array, 'PROP_ENUMS' => array, 'PROP_TYPES' => array]
     */
    public static function getSkuProps($iblockId = 0): array
    {
        $result = [];

        $propsMs = ApiNew::get('/entity/variant/metadata', [],  86400);
        $isPropVariant = Config::checkFeature('variantprops');

        $enablePropertyFeatures = \Bitrix\Iblock\Model\PropertyFeature::isEnabledFeatures();
                                
        if (!$propsMs->{'hasErrors'} && (int)$iblockId > intval(0)) {
            if (Utils::array_exists($propsMs, 'characteristics')) {
                foreach ($propsMs->{'characteristics'} as $prop) {
                    if ($isPropVariant) {
                        $result[$prop->{'id'}] = Config::getVariantProps($prop->{'id'});

                        if ((int)$result[$prop->{'id'}] > 0) {
                            continue;
                        }

                        if ($result[$prop->{'id'}] === 'N') {
                            unset($result[$prop->{'id'}]);
                            continue;
                        }
                    }

                    $rsProperty = \Bitrix\Iblock\PropertyTable::getList(array(
                        'filter' => array('IBLOCK_ID'=> $iblockId, 'XML_ID' => $prop->{'id'})
                    ))->fetch();

                    if ((int)$rsProperty['ID'] <= 0) {
                        $rsProperty = \Bitrix\Iblock\PropertyTable::add(array(
                            'IBLOCK_ID'=> $iblockId,
                            'XML_ID' => $prop->{'id'},
                            'NAME' => $prop->{'name'},
                            'PROPERTY_TYPE' => 'L',
                            'CODE' => mb_strtoupper(\CUtil::translit($prop->{'name'}, 'ru'))
                        ));
                        if ($rsProperty->isSuccess()) {
                            $propId = $rsProperty->getId();

                            if ($enablePropertyFeatures) {
                                $featureResult = \Bitrix\Iblock\Model\PropertyFeature::addFeatures($propId, [[
                                    'IS_ENABLED'    =>  'Y',
                                    'MODULE_ID'     =>  'catalog',
                                    'FEATURE_ID'    =>  'OFFER_TREE'
                                ]]);
                            }

                            $result[$prop->{'id'}] = $propId;
                        }
                    } else {
                        $result[$prop->{'id'}] = $rsProperty['ID'];
                    }

                    if ($isPropVariant) {
                        if ((int)$result[$prop->{'id'}] > 0) {
                            Config::setVariantProps($prop->{'id'}, (int)$result[$prop->{'id'}]);
                        }
                    }
                }
            }
        }

        $enumResult = [];
        if (count($result) > 0) {
            $propBxTypes = PropertiesImportUtils::getPropertiesTypesBx((int)$iblockId);

            foreach ($result as $propIdMs => $propId) {
                if (!in_array($propBxTypes[$propId]['TYPE'], ['S', 'N', 'L', 'E', 'S:directory'])) {
                    unset($result[$propIdMs]);
                }
            }

            if (count($result) <= 0) {
                return [];
            }

            foreach ($result as $propIdMs => $propId) {
                switch ($propBxTypes[$propId]['TYPE']) {
                            case 'L':
                                $rsEnum = \Bitrix\Iblock\PropertyEnumerationTable::getList(array('filter' => array('PROPERTY_ID'=> $propId)));
                                while ($arEnum = $rsEnum->fetch()) {
                                    $enumResult[$propIdMs][mb_strtoupper($arEnum['VALUE'])] = $arEnum['ID'];
                                }
                            break;
                            case 'E':
                                if ($propBxTypes[$propId]['PARAM'] > 0) {
                                    $rsEnum = \Bitrix\Iblock\ElementTable::getList(array('filter' => array('IBLOCK_ID'=> $propBxTypes[$propId]['PARAM'])));
                                    while ($arEnum = $rsEnum->fetch()) {
                                        $enumResult[$propIdMs][mb_strtoupper($arEnum['NAME'])] = $arEnum['ID'];
                                    }
                                }
                            break;
                            case 'S:directory':
                                if (!empty($propBxTypes[$propId]['PARAM']) && $propBxTypes[$propId]['HL_CLASS'] !== null) {
                                    $rsEnum = $propBxTypes[$propId]['HL_CLASS']::getList();
                                    while ($arEnum = $rsEnum->fetch()) {
                                        $enumResult[$propIdMs][mb_strtoupper($arEnum['UF_NAME'])] = $arEnum['UF_XML_ID'];
                                    }
                                }
                            break;
                        }
            }
        }
                
        return ['PROP_IDS' => $result, 'PROP_ENUMS' => $enumResult, 'PROP_TYPES' => $propBxTypes];
    }

    /**
     * Set SKU properties
     * @param array $itemBx Iblock element array
     * @param object $itemMs SKU properties object from MoySklad
     * @param array &$propSkuList SKU properties list
     * @param bool $isUpdateFacet Property index update flag
     * @return void
     */
    public static function setSkuProps($itemBx = [], $itemMs = null, &$propSkuList, $isUpdateFacet = false): void
    {
        $arResultSku = [];
        foreach ($itemMs->{'characteristics'} as $prop) {
            if (!isset($propSkuList['PROP_IDS'][$prop->{'id'}])) {
                continue;
            }
            $propIdBx = $propSkuList['PROP_IDS'][$prop->{'id'}];

            $enumKeyValue = mb_strtoupper($prop->{'value'});

            switch ($propSkuList['PROP_TYPES'][$propIdBx]['TYPE']) {
                case 'S':
                    $arResultSku[$propIdBx] = $prop->{'value'};
                break;

                case 'N':
                    $arResultSku[$propIdBx] = (float)$prop->{'value'};
                break;

                case 'L':
                    if (!isset($propSkuList['PROP_ENUMS'][$prop->{'id'}][$enumKeyValue])) {
                        $enum = \Bitrix\Iblock\PropertyEnumerationTable::add(array('PROPERTY_ID'=> $propIdBx, 'XML_ID' =>  md5($prop->{'value'}), 'VALUE' => $prop->{'value'}));
                        if($enum->isSuccess()) {
                            $propSkuList['PROP_ENUMS'][$prop->{'id'}][$enumKeyValue] = $enum->getId();
                        }                                
                    }
                    $enumId = $propSkuList['PROP_ENUMS'][$prop->{'id'}][$enumKeyValue];
                    if ((int)$enumId > 0) {
                        $arResultSku[$propIdBx] = $enumId;
                    }
                break;

                case 'E':
                    $el = new \CIblockElement;
                    if ((int)$propSkuList['PROP_TYPES'][$propIdBx]['PARAM'] > 0) {
                        if (!isset($propSkuList['PROP_ENUMS'][$prop->{'id'}][$enumKeyValue])) {
                            $enumId =  $el->Add([
                                'IBLOCK_ID' => $propSkuList['PROP_TYPES'][$propIdBx]['PARAM'],
                                'NAME' => $prop->{'value'},
                                'CODE' => \CUtil::translit($prop->{'value'}, 'ru')
                            ]);
                            $propSkuList['PROP_ENUMS'][$prop->{'id'}][$enumKeyValue] = $enumId;
                        }
                        $enumId = $propSkuList['PROP_ENUMS'][$prop->{'id'}][$enumKeyValue];
                        if ((int)$enumId > 0) {
                            $arResultSku[$propIdBx] = $enumId;
                        }
                    }
                    
                break;

                case 'S:directory':

                    if (!empty($propSkuList['PROP_TYPES'][$propIdBx]['PARAM']) && $propSkuList['PROP_TYPES'][$propIdBx]['HL_CLASS'] !== null) {
                        if (!isset($propSkuList['PROP_ENUMS'][$prop->{'id'}][$enumKeyValue])) {
                            $enumId = $propSkuList['PROP_TYPES'][$propIdBx]['HL_CLASS']::add([
                                'UF_NAME' => $prop->{'value'},
                                'UF_XML_ID' => \CUtil::translit($prop->{'value'}, 'ru')
                            ]);
                            $propSkuList['PROP_ENUMS'][$prop->{'id'}][$enumKeyValue] = \CUtil::translit($prop->{'value'}, 'ru');
                        }
                        $enumId = $propSkuList['PROP_ENUMS'][$prop->{'id'}][$enumKeyValue];
                        if (!empty($enumId)) {
                            $arResultSku[$propIdBx] = $enumId;
                        }
                    }

                break;
            }
        }

        $event = new \Bitrix\Main\Event(Config::getModuleId(true), "OnSetSkuProps", array(
            'itemBx' => $itemBx,
            'itemMs' => $itemMs,
            'propSkuList' => $propSkuList,
            'arResultSku' => $arResultSku,
        ));

        $event->send();

        if ($event->getResults()) {
            foreach ($event->getResults() as $eventResult) {
                if ($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS) {
                    $arResultSku = $eventResult->getParameters();
                }
            }
        }

        if (count($arResultSku) > 0) {
            \CIBlockElement::SetPropertyValuesEx($itemBx['ID'], $itemBx['IBLOCK_ID'], $arResultSku);
            if ($isUpdateFacet) {
                \Bitrix\Iblock\PropertyIndex\Manager::updateElementIndex($itemBx['IBLOCK_ID'], $itemBx['ID']);
            }
        }
    }

    public static function importFilesInProp($currentMsImagesArray = [], $itemBx = [], $bxPropValue = [], $bxPropId = 0, $canDelete = false, $isMultipleProp = true): void
    {
        if ((int)$itemBx['IBLOCK_ID'] <= 0 || (int)$itemBx['ID'] <= 0 || (int)$bxPropId <= 0) {
            return;
        }

        $bxPropFiles = [];
        if ($isMultipleProp) {
            if (Utils::is_count($bxPropValue['VALUE'])) {
                foreach ($bxPropValue['VALUE'] as $i => $fileId) {
                    if ((int)$bxPropValue['PROPERTY_VALUE_ID'][$i] > 0) {
                        $bxPropFiles[(int)$bxPropValue['PROPERTY_VALUE_ID'][$i]] = (int)$fileId;
                    }
                }
            }
        } else {
            if ((int)$bxPropValue['VALUE'] > 0 && (int)$bxPropValue['PROPERTY_VALUE_ID'] > 0) {
                $bxPropFiles[(int)$bxPropValue['PROPERTY_VALUE_ID']] = (int)$bxPropValue['VALUE'];
            }
        }
            

        $needUpdProp = false;
        $picPropUpdateArray = [];
        if (count($currentMsImagesArray) <= 0) {
            if ($canDelete && count($bxPropFiles) > 0) {
                foreach ($bxPropFiles as $propValId => $fileId) {
                    $needUpdProp = true;
                    $picPropUpdateArray[$propValId] = ['del' => 'Y'];
                }
            }
        } else {
            if (count($bxPropFiles) > 0) {
                foreach ($bxPropFiles as $propValId => $fileId) {
                    if (count($currentMsImagesArray) > 0) {
                        $currentMsImage = array_shift($currentMsImagesArray);
                        if (intval($fileId) !== intval($currentMsImage->getBxId())) {
                            $needUpdProp = true;
                        }
                        $picPropUpdateArray[$propValId] = ['VALUE' => $currentMsImage->getBxId()];
                    } else {
                        $needUpdProp = true;
                        $picPropUpdateArray[$propValId] = ['del' => 'Y'];
                    }
                }
            }
            if ($isMultipleProp) {
                if (count($currentMsImagesArray) > 0) {
                    foreach ($currentMsImagesArray as $i => $currentMsImage) {
                        $needUpdProp = true;
                        $picPropUpdateArray["n{$i}"] = ['VALUE' => $currentMsImage->getBxId()];
                    }
                }
            }
        }
                     
        if ($needUpdProp && count($picPropUpdateArray) > 0) {
            if(version_compare(phpversion(), '8.0.0', '>') || method_exists('CDiskQuota', 'recalculateDb')) {
                \Rbs\MoyskladStocks\Compitable\Bitrix\CIblockElement::SetPropertyValues($itemBx['ID'], $itemBx['IBLOCK_ID'], $isMultipleProp ? $picPropUpdateArray : current($picPropUpdateArray), (int)$bxPropId);
            } else {
                \CIBlockElement::SetPropertyValues($itemBx['ID'], $itemBx['IBLOCK_ID'], $isMultipleProp ? $picPropUpdateArray : current($picPropUpdateArray), (int)$bxPropId);
            }
        }
    }

    /**
     * Set element properties
     * @param array $itemBx Iblock element array
     * @param object $itemMs Element properties object from MoySklad
     * @param array $propList Properties list
     * @param array $propBxTypes Property types list
     * @param bool $isUpdateFacet Property index update flag
     * @param bool $isEmptyImport Empty import flag
     * @param array $arAllPropsValues All properties list
     * @param bool $isNew New element flag
     * @return void
     */
    public static function setPropsForItem($itemBx = [], $itemMs = null, $propList = [], $propBxTypes = [], $isUpdateFacet = false, $isEmptyImport = false, $arAllPropsValues = [], $isNew = false): void
    {
        $arPropsSets = [];

        $propSetter = new \Rbs\MoyskladStocks\Services\PropsSetter($itemBx, $itemMs, $propList, $propBxTypes, $isEmptyImport, $arAllPropsValues);
        $propSetter->processProperties();
        $arPropsSets = $propSetter->getArPropsSets();

        $event = new \Bitrix\Main\Event(Config::getModuleId(true), "OnSetPropsForItem", array(
            'itemBx' => $itemBx,
            'itemMs' => $itemMs,
            'propList' => $propList,
            'arAllPropsValues' => $arAllPropsValues,
            'arPropsSets' => $arPropsSets,
            'isNew' => $isNew
        ));

        $event->send();

        if ($event->getResults()) {
            foreach ($event->getResults() as $eventResult) {
                if ($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS) {
                    $arPropsSets = $eventResult->getParameters();
                }
            }
        }
            
        if (count($arPropsSets) > 0) {
            $flags = [];
            if ($isNew) {
                $flags['NewElement'] = 'Y';
            }
            \CIBlockElement::SetPropertyValuesEx($itemBx['ID'], $itemBx['IBLOCK_ID'], $arPropsSets, $flags);
            if ($isUpdateFacet) {
                \Bitrix\Iblock\PropertyIndex\Manager::updateElementIndex($itemBx['IBLOCK_ID'], $itemBx['ID']);
            }
        }
    }

    public static function getPropertiesTypesBx(int $iblockId = 0): array
    {
        $propsStr = array_merge(\Bitrix\Iblock\PropertyTable::getList(['filter' => ['IBLOCK_ID' => $iblockId, 'PROPERTY_TYPE' => ['S', 'L', 'N', 'E', 'F'], 'MULTIPLE' => 'N']])->fetchAll() , \Bitrix\Iblock\PropertyTable::getList(['filter' => ['IBLOCK_ID' => $iblockId, 'PROPERTY_TYPE' => 'F', 'MULTIPLE' => 'Y']])->fetchAll());

        $propTypes = [];

        if(Utils::is_count($propsStr)) {
            foreach ($propsStr as $prop) {

                if (empty($prop) || !is_array($prop)) {
                    continue;
                }

                $tableName = '';
                if(!empty($prop['USER_TYPE_SETTINGS_LIST']['TABLE_NAME'])) {
                    $tableName = $prop['USER_TYPE_SETTINGS_LIST']['TABLE_NAME'];
                }

                if(!isset($prop['LINK_IBLOCK_ID']) || empty($prop['LINK_IBLOCK_ID'])){
                    $prop['LINK_IBLOCK_ID'] = 0;
                }

                if ($prop['PROPERTY_TYPE'] === 'S' && !empty($prop['USER_TYPE'])) {
                    if (!in_array($prop['USER_TYPE'], ['directory', 'Date', 'DateTime', 'HTML'])) {
                        continue;
                    }
                }
                if ($prop['MULTIPLE'] == 'Y') {
                    $propTypes[$prop['ID']] = [
                        'TYPE' => $prop['PROPERTY_TYPE'] . ':Multiple',
                        'PARAM' => $prop['PROPERTY_TYPE'] === 'E' ? $prop['LINK_IBLOCK_ID'] : $tableName
                    ];
                } else {
                    $propTypes[$prop['ID']] = [
                        'TYPE' => !empty($prop['USER_TYPE']) ? $prop['PROPERTY_TYPE'] . ':' . $prop['USER_TYPE'] : $prop['PROPERTY_TYPE'],
                        'PARAM' => $prop['PROPERTY_TYPE'] === 'E' ? $prop['LINK_IBLOCK_ID'] : $tableName
                    ];
                }


                if ($prop['PROPERTY_TYPE'] === 'E') {
                    $propTypes[$prop['ID']]['PARAM'] = $prop['LINK_IBLOCK_ID'];
                }

                if ($prop['PROPERTY_TYPE'] === 'S' && $prop['USER_TYPE'] === 'directory' && !empty($tableName) && \Bitrix\Main\Loader::includeModule('highloadblock')) {

                    $propTypes[$prop['ID']]['PARAM'] = $tableName;
                    $propTypes[$prop['ID']]['HL_CLASS'] = null;

                    $rsData = \Bitrix\Highloadblock\HighloadBlockTable::getList([
                        'filter' => [
                            '=TABLE_NAME' => $tableName
                        ]
                    ]);

                    if ($obTable = $rsData->fetch()) {
                        $entity = \Bitrix\Highloadblock\HighloadBlockTable::compileEntity($obTable);
                        $propTypes[$prop['ID']]['HL_CLASS'] = $entity->getDataClass();
                    }
                }
            }
        }

        return $propTypes;
    }
}