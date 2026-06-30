<?php

namespace Rbs\MoyskladStocks\Import\Entity;

use Bitrix\Main\Application;

use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\ApiNew;

use Rbs\MoyskladStocks\Services\ConfigurationUtils;
use Rbs\MoyskladStocks\Services\BitrixFeatures;
use Rbs\MoyskladStocks\Services\TrackingTypeSetter;
use Rbs\MoyskladStocks\Services\BarcodeSetter;
use Rbs\MoyskladStocks\Services\MoyskladImportUtils;
use Rbs\MoyskladStocks\Services\PropertiesImportUtils;
use Rbs\MoyskladStocks\Services\CatalogUtils;
use Rbs\MoyskladStocks\Services\ImportParamsConfig;

class UpdateEntityItems
{
    public static function update($entity = 'product', $arItems = [], $whParamList = [], $arSectionsItems = [], $paramList = [], &$arrLog = []): void
    {
        $isAllEmpty = true;

        foreach ($whParamList as $param) {
            if ($param) {
                $isAllEmpty = false;
                break;
            }
        }

        if ($isAllEmpty && $entity !== 'variant') {
            return;
        }

        $importParamList = ImportParamsConfig::getImportParams($entity);
        $propList = ImportParamsConfig::getImportPropList($entity);
        $catalogIblockId = Config::getIblockId($entity);
        $weightM = Config::getWeightM($entity);
        $propBxTypes = PropertiesImportUtils::getPropertiesTypesBx((int)$catalogIblockId);
        $sortProp = Config::getPropSort($entity);
        $ratioProp = Config::getPropRatio($entity);
        $vatList = ConfigurationUtils::getVatList();

        $isIgnoreProductType = (bool)$importParamList['ignore_prodtype'];
        $canDeleteDescr = (bool)$importParamList['descr_delete'];
        $canDeletePicture = (bool)$importParamList['img_del'];
        $isPreviewPicture = (bool)$whParamList['img'];
        $isDetailPicture = (bool)$whParamList['img_full'];
        $isOtherPicture = (bool)$whParamList['img_prop'] && (int)$importParamList['img_more_prop'] > 0;
        $canImportAllPicInProp = (bool)$importParamList['img_more_all'];
        $isTrackingType = (bool)$importParamList['tracking_type'];
        $isBarcode = (bool)$importParamList['barcode'];
        $isSomePicture = $isPreviewPicture || $isDetailPicture || $isOtherPicture;

        $isBundle = $entity === 'bundle';
        $isImportBundle = Config::getImportBundleType() === 'BUNDLE';
        $bundlePartIblockId = Config::getBundlePartIblockId();

        $isEnableFilterProp = Config::isEnableFilterProp($entity);
        $isEnableActiveProp = Config::isEnableActiveProp($entity);
        $activePropId = Config::getActivePropId($entity);

        $isVariant = $entity === 'variant';
        $uid = Config::getUserId();

        $measureList = [];
        if ($whParamList['uom']) {
            $measureList = ConfigurationUtils::getMeasureList();
        }

        $translitParams = $importParamList['translit'] ? ConfigurationUtils::getIblockElementTranslitParams((int)$catalogIblockId) : [];

        $itemBxIds = [];
        foreach ($arItems as $xmlId => $currentItem) {
            $itemBxIds[$xmlId] = $currentItem['BX']['ID'];
        }

        $measureRatioList = [];
        if (($ratioProp !== 'N' && $whParamList['ratio']) || $whParamList['parent_ratio']) {
            $measureRatioList = CatalogUtils::getMeasureRatioList($itemBxIds);
        }
           
        $productArray = CatalogUtils::getProductArray($itemBxIds, (bool)$isTrackingType || (bool)$whParamList['parent_tracking']);
        
        if($isBarcode && BitrixFeatures::isBarCodeTableExist()) {
            $barCodeList = CatalogUtils::getBarcodeList($itemBxIds);
            if(Utils::is_count($barCodeList)) {
                foreach($barCodeList as $elId => $barcodeList) {
                    $productArray[$elId]['BARCODE_LIST'] = $barcodeList;
                }
            }
            unset($barCodeList);
        }

        if (($whParamList['props'] || $isOtherPicture) && count($arItems) > 0) {

            if ($isOtherPicture) {
                $propList[(int)$importParamList['img_more_prop']] = '';
            }

            if (Utils::is_count($propList)) {
                $event = new \Bitrix\Main\Event(Config::getModuleId(true), "OnBeforeGetPropsValues", [
                    'entity' => $entity,
                    'propList' => $propList
                ]);    
                $event->send();
                if ($event->getResults()) {
                    foreach ($event->getResults() as $eventResult) {
                        if ($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS) {
                            $propList = $eventResult->getParameters();
                        }
                    }
                }
                $arPropsForAllItems = PropertiesImportUtils::getPropsValues($arItems, $propList, $catalogIblockId);
            }
        }
            
        $parentItemList['ITEMS'] = [];
        if ($isVariant) {
            $propSkuList = PropertiesImportUtils::getSkuProps($catalogIblockId);
            if (
                    $whParamList['parent_tracking'] ||
                    $whParamList['parent_weight'] ||
                    $whParamList['parent_ratio'] ||
                    $whParamList['parent_measure'] ||
                    $whParamList['parent_sizes'] ||
                    $whParamList['vat'] ||
                    $whParamList['vat_inc']
                ) {
                    $arPropsCml2Link = PropertiesImportUtils::getPropsValues($arItems, [$paramList['SKU_PROPERTY_ID'] => 'CML2_LINK'], $catalogIblockId);
                    if (count($arPropsCml2Link) > 0) {
                        foreach ($itemBxIds as $xmlId => $itemBxId) {
                            if (
                                    isset($arPropsCml2Link[$itemBxId][$paramList['SKU_PROPERTY_ID']]['VALUE']) &&
                                    (int)$arPropsCml2Link[$itemBxId][$paramList['SKU_PROPERTY_ID']]['VALUE'] > 0
                                ) {
                                $parentItemList['ITEMS'][$itemBxId] = (int)$arPropsCml2Link[$itemBxId][$paramList['SKU_PROPERTY_ID']]['VALUE'];
                            }
                        }
                        $parentItemIds = array_keys(array_flip($parentItemList['ITEMS']));
                        $parentItemList['PARENTS'] = CatalogUtils::getProductArray($parentItemIds, (bool)$whParamList['parent_tracking']);
                        if ($whParamList['parent_ratio']) {
                            $parentItemList['MEASURE_RATIO_PARENTS'] = CatalogUtils::getMeasureRatioList($parentItemIds);
                        }
                    }
            }
        }

        $allFilesIds = [];
        if ($isSomePicture) {
            foreach ($arItems as $xmlId => $currentItem) {
                $itemBx = $currentItem['BX'];
                if ($isPreviewPicture && (int)$itemBx['PREVIEW_PICTURE'] > 0) {
                    $allFilesIds[] = (int)$itemBx['PREVIEW_PICTURE'];
                }
                if ($isDetailPicture && (int)$itemBx['DETAIL_PICTURE'] > 0) {
                    $allFilesIds[] = (int)$itemBx['DETAIL_PICTURE'];
                }
            }
        }

        $fileExtIds = [];
        $imageClearPrev = Config::checkFeature('image_clear_prev');
        if (count($allFilesIds) > 0) {
            $fileTable = \Bitrix\Main\FileTable::getList([
                'select' => [
                    'ID', 'EXTERNAL_ID', 'SUBDIR', 'FILE_NAME'
                ],
                'filter' => [
                    'ID' => $allFilesIds
                ]
            ])->fetchAll();
            $uploadDir = Application::getDocumentRoot() . '/' . \COption::GetOptionString("main", "upload_dir", "upload") . '/';
            if (count($fileTable) > 0) {
                foreach ($fileTable as $file) {
                    if($imageClearPrev) {
                        $filePath = $uploadDir . $file['SUBDIR'] . '/' . $file['FILE_NAME'];
                        if (!\file_exists($filePath)) {
                            \CFile::Delete($file['ID']);
                            continue;
                        }
                    }
                    $fileExtIds[$file['ID']] =  $file['EXTERNAL_ID'];
                }
            }
            unset($allFilesIds);
        }

        $isSectionKeep = $whParamList['folder'] && !$importParamList['section_off'] && $importParamList['section_keep'];
        $elementExistingSections = [];
        if ($isSectionKeep) {
            $allItemBxIdsValues = array_values($itemBxIds);
            if (count($allItemBxIdsValues) > 0) {
                $rsSectionElements = \Bitrix\Iblock\SectionElementTable::getList([
                    'select' => ['IBLOCK_SECTION_ID', 'IBLOCK_ELEMENT_ID'],
                    'filter' => ['IBLOCK_ELEMENT_ID' => $allItemBxIdsValues]
                ])->fetchAll();
                foreach ($rsSectionElements as $row) {
                    $elementExistingSections[(int)$row['IBLOCK_ELEMENT_ID']][] = (int)$row['IBLOCK_SECTION_ID'];
                }
            }
        }

        $el = new \CIblockElement;

        foreach ($arItems as $xmlId => $currentItem) {

            $itemBx = $currentItem['BX'];
            $item = $currentItem['MS'];

            $attrList = MoyskladImportUtils::getAttrList($item->{'attributes'});
            $attrAllList = MoyskladImportUtils::getAllAttrList($item->{'attributes'});

            $arUpdateItemFields = [];
            if ($uid > 0) {
                $arUpdateItemFields['MODIFIED_BY'] = $uid;
            }

            if($isEnableActiveProp) {
                if(isset($attrList['boolean'][$activePropId]) && $attrList['boolean'][$activePropId] === true) {
                    $arUpdateItemFields['ACTIVE'] = 'Y';
                } else {
                    $arUpdateItemFields['ACTIVE'] = 'N';
                }
            } else {
                if ($whParamList['archived']) {
                    if ($itemBx['ACTIVE'] === 'Y' && $item->{'archived'}) {
                        $arUpdateItemFields['ACTIVE'] = 'N';
                    }
                    if ($itemBx['ACTIVE'] === 'N' && !$item->{'archived'}) {
                        $arUpdateItemFields['ACTIVE'] = 'Y';
                    }
                }
                if ($whParamList['active_by_filter']) {
                    if ($isEnableFilterProp && $itemBx['ACTIVE'] === 'N') {
                        $arUpdateItemFields['ACTIVE'] = 'Y';
                    }
                }
            }

            if ($whParamList['folder'] && !$importParamList['section_off']) {
                $sectionItemBxId = (int)$arSectionsItems[$item->{'productFolder'}->{'meta'}->{'href'}];
                $skipSectionUpdate = $isSectionKeep && isset($elementExistingSections[$itemBx['ID']]) && count($elementExistingSections[$itemBx['ID']]) > 1;
                if (!$skipSectionUpdate && $sectionItemBxId !== (int)$itemBx['IBLOCK_SECTION_ID']) {
                    $arUpdateItemFields['IBLOCK_SECTION_ID'] = intval($sectionItemBxId) > 0 ? intval($sectionItemBxId) : false;
                }
            }
            
            if($whParamList['sort'] && isset($attrAllList[$sortProp]) && is_object($attrAllList[$sortProp])){
                if((int)$attrAllList[$sortProp]->{'value'} !== (int)$itemBx['SORT']){
                    $arUpdateItemFields['SORT'] = (int)$attrAllList[$sortProp]->{'value'};
                }
            }

            $descriptionCheckArray = [
                'descr' => 'PREVIEW_TEXT',
                'descr_full' => 'DETAIL_TEXT'
            ];

            foreach ($descriptionCheckArray as $option => $bxField) {
                
                if(!$whParamList[$option]) continue;

                $isDefaultSource = $importParamList[$option . '_source'] === 'DEFAULT' || empty($importParamList[$option . '_source']) || $isVariant;

                $descrSource = !empty($item->{'description'}) ? (string)$item->{'description'} : '';
                if(!$isDefaultSource) {
                    $descrSource = !empty($attrList['string'][$importParamList[$option . '_source']]) ? (string)$attrList['string'][$importParamList[$option . '_source']] : '';
                }

                if (!empty($descrSource) || $canDeleteDescr) {
                    $arUpdateItemFields[$bxField] = $descrSource;
                }

                $descrType = in_array($importParamList[$option . '_type'], ['text', 'html']) ? $importParamList[$option . '_type'] : '';

                if(!empty($descrType)) {
                    $arUpdateItemFields[$bxField . '_TYPE'] = $descrType;
                }

            }

            if ($whParamList['name'] && $item->{'name'} !== $itemBx['NAME']) {
                $arUpdateItemFields['NAME'] = $item->{'name'};
            }
        
            if ($whParamList['code']) {
                $code = \CRbsMoyskladStocks::getTranslitCode($item->{'name'}, $translitParams, $importParamList['trim']);
                if ($importParamList['code_uniq']) {
                    $code = \CRbsMoyskladStocks::getElementUniqCode($catalogIblockId, $code, $itemBx['ID']);
                }
                if ($code !== $itemBx['CODE']) {
                    $arUpdateItemFields['CODE'] = $code;
                }
            }

            $currentMsImagesArray = [];
            if ($isSomePicture) {
                $currentMsImagesArray = MoyskladImportUtils::getMsFilesArray($item->{'images'}, !$isOtherPicture ? 1 : 0);
            }

            if ($isPreviewPicture || $isDetailPicture) {
                if (count($currentMsImagesArray) > 0) {

                    $firstFile = $canImportAllPicInProp ? current($currentMsImagesArray) : array_shift($currentMsImagesArray);
                    if ($isPreviewPicture) {

                        if(
                            empty($fileExtIds[$itemBx['PREVIEW_PICTURE']]) ||
                            $fileExtIds[$itemBx['PREVIEW_PICTURE']] !== $firstFile->getExtId() ||
                            $firstFile->isDeletedNow()
                        ) {
                            $arUpdateItemFields['PREVIEW_PICTURE'] = $firstFile->getBxFileArrayFromFileId();
                        }

                    }
                    if ($isDetailPicture) {
                        if (
                            empty($fileExtIds[$itemBx['DETAIL_PICTURE']]) ||
                            $fileExtIds[$itemBx['DETAIL_PICTURE']] !== $firstFile->getExtId() ||
                            $firstFile->isDeletedNow()
                        ) {
                            $arUpdateItemFields['DETAIL_PICTURE'] = $firstFile->getBxFileArrayFromFileId();
                        }
                    }

                } elseif ($canDeletePicture) {

                    if ($isPreviewPicture) {
                        $arUpdateItemFields['PREVIEW_PICTURE'] = ['del' => 'Y'];
                    }
                    if ($isDetailPicture) {
                        $arUpdateItemFields['DETAIL_PICTURE'] = ['del' => 'Y'];
                    }

                }
            }
        
            if (count($arUpdateItemFields) > 0) {

                $event = new \Bitrix\Main\Event(Config::getModuleId(true), "OnBeforeUpdateItem", array(
                    'entity' => $entity,
                    'currentItem' => $currentItem,
                    'arUpdateItemFields' => $arUpdateItemFields
                ));

                $event->send();

                if ($event->getResults()) {
                    foreach ($event->getResults() as $eventResult) {
                        if ($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS) {
                            $arUpdateItemFields = $eventResult->getParameters();
                        }
                    }
                }

                if ($el->Update($itemBx['ID'], $arUpdateItemFields, $importParamList['workflow'], $importParamList['updsearch'], $importParamList['resizepic'])) {
                    $arrLog['#UPDATE#']++;
                } else {
                    $arrLog['#ERROR#']++;
                    $arrLog['ERROR_LIST'][$item->{'externalCode'}] = "[" . $item->{'externalCode'} . "] " . $el->{'LAST_ERROR'};
                }
            }

            if ($isOtherPicture) {
                PropertiesImportUtils::importFilesInProp($currentMsImagesArray, $itemBx, $arPropsForAllItems[$itemBx['ID']][(int)$importParamList['img_more_prop']], (int)$importParamList['img_more_prop'], $canDeletePicture, true);
            }

            if($isTrackingType) {
                TrackingTypeSetter::getInstance()->setTrackingType($itemBx['ID'], $item->{'trackingType'}, isset($productArray[$itemBx['ID']]['UF_PRODUCT_GROUP']) ? (int)$productArray[$itemBx['ID']]['UF_PRODUCT_GROUP'] : 0);
            }

            if($isBarcode) {
                BarcodeSetter::setBarCodes($itemBx['ID'], Utils::array_exists($item, 'barcodes') ? $item->{'barcodes'} : [], isset($productArray[$itemBx['ID']]['BARCODE_LIST']) ? $productArray[$itemBx['ID']]['BARCODE_LIST'] : [], $entity !== 'variant', $uid);
            }
            
            if ($whParamList['props']) {
                if (Utils::is_count($propList)) {
                    PropertiesImportUtils::setPropsForItem($itemBx, $item, $propList, $propBxTypes, $whParamList['update_facet'], $importParamList['emptyim'], $arPropsForAllItems[$itemBx['ID']]);
                }
            }
                
            if (!$isVariant) {

                $productUpdate = [];

                if ($whParamList['sizes'] && isset($productArray[$itemBx['ID']])) {
                    $product = $productArray[$itemBx['ID']];
                    if ((float)$product['WEIGHT'] !== (float)$item->{'weight'} * $weightM) {
                        $productUpdate['WEIGHT'] = (float)$item->{'weight'} * $weightM;
                    }
                    foreach (['width', 'length', 'height'] as $gabb) {
                        if (
                                isset($attrAllList[$importParamList[$gabb]]) &&
                                (float)$product[mb_strtoupper($gabb)] !== (float)$attrAllList[$importParamList[$gabb]]->{'value'}
                            ) {
                            $productUpdate[mb_strtoupper($gabb)] = (float)$attrAllList[$importParamList[$gabb]]->{'value'};
                        }
                    }
                }

                if(!$isIgnoreProductType) {

                    if (
                        $entity == 'product' &&
                        (int)$item->{'variantsCount'} > 0 &&
                        $productArray[$itemBx['ID']]['TYPE'] != \Bitrix\Catalog\ProductTable::TYPE_SKU
                    ) {
                        $productUpdate['TYPE'] = \Bitrix\Catalog\ProductTable::TYPE_SKU;
                    }

                    if (
                        $entity == 'product' &&
                        (int)$item->{'variantsCount'} <= 0 &&
                        $productArray[$itemBx['ID']]['TYPE'] == \Bitrix\Catalog\ProductTable::TYPE_SKU
                    ) {
                        $productUpdate['TYPE'] = \Bitrix\Catalog\ProductTable::TYPE_PRODUCT;
                    }

                }

                if ($whParamList['uom'] && count($measureList) > 0 && !empty($item->{'uom'}->{'meta'}->{'href'})) {

                    $measureMs = $item->{'uom'};
                    if (property_exists($measureMs, 'code')) {
                        $measureMs->{'hasErrors'} = false;
                    } else {
                        $measureMs = ApiNew::get($item->{'uom'}->{'meta'}->{'href'}, [], 86400 * 365 * 10);
                    }

                    if (Utils::is_success($measureMs)) {
                        if (!empty($measureMs->{'code'}) && (int)$measureList[(int)$measureMs->{'code'}] > 0) {
                            $productUpdate['MEASURE'] = (int)$measureList[(int)$measureMs->{'code'}];
                        }
                    }
                }

                if ($ratioProp !== 'N' && isset($attrAllList[$ratioProp]) && is_object($attrAllList[$ratioProp]) && $whParamList['ratio']) {
                    if((float)$attrAllList[$ratioProp]->{'value'} > intval(0)) {
                        if(isset($measureRatioList[$itemBx['ID']]) && !empty($measureRatioList[$itemBx['ID']]['ID'])) {
                            $currentRatioItem = $measureRatioList[$itemBx['ID']];
                            if ((float)$attrAllList[$ratioProp]->{'value'} !== (float)$currentRatioItem['RATIO']) {
                                \Bitrix\Catalog\MeasureRatioTable::update($currentRatioItem['ID'], [
                                    'RATIO' => (float)$attrAllList[$ratioProp]->{'value'},
                                    'IS_DEFAULT' => 'Y'
                                ]);
                            }
                        } else {
                            \Bitrix\Catalog\MeasureRatioTable::add([
                                'PRODUCT_ID' => $itemBx['ID'],
                                'RATIO' => (float)$attrAllList[$ratioProp]->{'value'},
                                'IS_DEFAULT' => 'Y'
                            ]);
                        }
                    }
                }

                $currentVat = !empty($item->{'effectiveVat'}) ? (float)round((float)$item->{'effectiveVat'}, 2) : intval(0);
                $currentVatInc = !empty($item->{'effectiveVatEnabled'}) ? (bool)$item->{'effectiveVatEnabled'} : false;
                $isZeroVat = $currentVat === 0 && !$currentVatInc;
                if($isZeroVat) {
                    $item->{'effectiveVat'} = 'N';
                }
                $productArray[$itemBx['ID']]['VAT_INCLUDED'] = $productArray[$itemBx['ID']]['VAT_INCLUDED'] === 'Y';

                $currentVatId = isset($vatList[$item->{'effectiveVat'}]) && (int)$vatList[$item->{'effectiveVat'}] > 0 ? (int)$vatList[$item->{'effectiveVat'}] : 0;

                if(
                    (bool)$whParamList['vat'] && $currentVatId > 0 &&
                    $productArray[$itemBx['ID']]['VAT_ID'] != $currentVatId
                ) {
                    $productUpdate['VAT_ID'] = $currentVatId;
                }

                if ((bool)$whParamList['vat_inc'] && $productArray[$itemBx['ID']]['VAT_INCLUDED'] !== $currentVatInc) {
                    $productUpdate['VAT_INCLUDED'] = $currentVatInc ? 'Y' : 'N';
                }

                $event = new \Bitrix\Main\Event(Config::getModuleId(true), "OnBeforeUpdateItemProduct", array(
                    'entity' => $entity,
                    'currentItem' => $currentItem,
                    'productCurrentItem' => $productArray[$itemBx['ID']],
                    'productUpdate' => $productUpdate
                ));

                $event->send();

                if ($event->getResults()) {
                    foreach ($event->getResults() as $eventResult) {
                        if ($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS) {
                            $productUpdate = $eventResult->getParameters();
                        }
                    }
                }

                if (count($productUpdate) > 0) {
                    \Bitrix\Catalog\Model\Product::update($itemBx['ID'], $productUpdate);
                }

                if ($isBundle && $isImportBundle) {
                    ImportBundle::import($itemBx['ID'], $item, true, $bundlePartIblockId, $arrLog);
                }

            } else {
                
                if (isset($parentItemList['ITEMS'][$itemBx['ID']]) && (int)$parentItemList['ITEMS'][$itemBx['ID']] > 0) {

                    $parentId = $parentItemList['ITEMS'][$itemBx['ID']];
                    $parentItem = $parentItemList['PARENTS'][$parentId];
                    $parentItemRatio = !empty($parentItemList['MEASURE_RATIO_PARENTS'][$parentId]) ? $parentItemList['MEASURE_RATIO_PARENTS'][$parentId] : [];
                    $product = $productArray[$itemBx['ID']];

                    if ($parentItem['ID'] > 0) {

                        $productUpdate = [];

                        if ($whParamList['parent_sizes']) {
                            foreach (['width', 'length', 'height'] as $gabb) {
                                if ((float)$product[mb_strtoupper($gabb)] !== (float)$parentItem[mb_strtoupper($gabb)]) {
                                    $productUpdate[mb_strtoupper($gabb)] = (float)$parentItem[mb_strtoupper($gabb)];
                                }
                            }
                        }

                        if($whParamList['parent_tracking']) {
                            TrackingTypeSetter::getInstance()->setTrackingTypeFromId($itemBx['ID'], (int)$parentItem['UF_PRODUCT_GROUP'], isset($productArray[$itemBx['ID']]['UF_PRODUCT_GROUP']) ? (int)$productArray[$itemBx['ID']]['UF_PRODUCT_GROUP'] : 0);
                        }

                        if (
                            $whParamList['parent_weight'] &&
                            (float)$product['WEIGHT'] != (float)$parentItem['WEIGHT']
                        ) {
                            $productUpdate['WEIGHT'] = (float)$parentItem['WEIGHT'];
                        }

                        if (
                            $whParamList['parent_measure'] &&
                            (float)$product['MEASURE'] != (float)$parentItem['MEASURE']
                        ) {
                            $productUpdate['MEASURE'] = $parentItem['MEASURE'];
                        }

                        if ($whParamList['parent_ratio'] && !empty($parentItemRatio['RATIO'])) {
                            if(!empty($measureRatioList[$itemBx['ID']])) {
                                $currentRatioItem = $measureRatioList[$itemBx['ID']];
                                if((float)$parentItemRatio['RATIO'] !== (float)$currentRatioItem['RATIO']){
                                    \Bitrix\Catalog\MeasureRatioTable::update($currentRatioItem['ID'], [
                                        'RATIO' => (float)$parentItemRatio['RATIO'],
                                        'IS_DEFAULT' => 'Y'
                                    ]);
                                }
                            } else {
                                \Bitrix\Catalog\MeasureRatioTable::add([
                                    'PRODUCT_ID' => $itemBx['ID'],
                                    'RATIO' => (float)$parentItemRatio['RATIO'],
                                    'IS_DEFAULT' => 'Y'
                                ]);
                            }
                        }

                        if ((bool)$whParamList['vat'] && (int)$product['VAT_ID'] !==(int)$parentItem['VAT_ID'] && (int)$parentItem['VAT_ID'] > 0) {
                            $productUpdate['VAT_ID'] = (int)$parentItem['VAT_ID'];
                        }

                        if ((bool)$whParamList['vat_inc'] && $product['VAT_INCLUDED'] !== $parentItem['VAT_INCLUDED']) {
                            $productUpdate['VAT_INCLUDED'] = $parentItem['VAT_INCLUDED'];
                        }

                        $event = new \Bitrix\Main\Event(Config::getModuleId(true), "OnBeforeUpdateItemVariant", array(
                            'entity' => $entity,
                            'currentItem' => $currentItem,
                            'parentItem' => [
                                'parentId' => $parentId,
                                'parentProductParams' => $parentItem
                            ],
                            'productCurrentItem' => $product,
                            'productUpdate' => $productUpdate
                        ));

                        $event->send();

                        if ($event->getResults()) {
                            foreach ($event->getResults() as $eventResult) {
                                if ($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS) {
                                    $productUpdate = $eventResult->getParameters();
                                }
                            }
                        }

                        if (count($productUpdate) > 0) {
                            \Bitrix\Catalog\Model\Product::update($itemBx['ID'], $productUpdate);
                        }
                    }
                }

                PropertiesImportUtils::setSkuProps($itemBx, $item, $propSkuList);
            }

            if ($whParamList['seocache']) {
                $ipropValues = new \Bitrix\Iblock\InheritedProperty\ElementValues($itemBx['IBLOCK_ID'], $itemBx['ID']);
                $ipropValues->clearValues();
            }
        }

        $event = new \Bitrix\Main\Event(Config::getModuleId(true), "OnAfterUpdateItems", array('entity' => $entity, 'items' => $arItems, 'products' => $productArray));
        $event->send();
    }
}