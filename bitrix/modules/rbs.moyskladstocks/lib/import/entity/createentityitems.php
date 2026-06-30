<?php

namespace Rbs\MoyskladStocks\Import\Entity;

use Rbs\MoyskladStocks\HlCache\ExtCodes;
use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\ApiNew;

use Rbs\MoyskladStocks\Services\ConfigurationUtils;
use Rbs\MoyskladStocks\Services\TrackingTypeSetter;
use Rbs\MoyskladStocks\Services\BarcodeSetter;
use Rbs\MoyskladStocks\Services\MoyskladImportUtils;
use Rbs\MoyskladStocks\Services\PropertiesImportUtils;
use Rbs\MoyskladStocks\Services\CatalogUtils;
use Rbs\MoyskladStocks\Services\ImportParamsConfig;
use Rbs\MoyskladStocks\Internals\ProductFinder\ProductIdentifier;

class CreateEntityItems
{
    public static function create($entity = 'product', $arItems = [], $arSectionsItems = [], $paramList = [], &$arrLog = []): void
    {
        $importParamList = ImportParamsConfig::getImportParams($entity);
            
        $propList = ImportParamsConfig::getImportPropList($entity);
        $propBxTypes = PropertiesImportUtils::getPropertiesTypesBx((int)$paramList['IBLOCK_ID']);

        $isVariant = $entity === 'variant';
        $uid = Config::getUserId();

        $isBundle = $entity === 'bundle';
        $isImportBundle = Config::getImportBundleType() === 'BUNDLE';
        $bundlePartIblockId = Config::getBundlePartIblockId();
        $sortProp = Config::getPropSort($entity);
        $ratioProp = Config::getPropRatio($entity);
        $vatList = ConfigurationUtils::getVatList();
        
        $isIgnoreProductType = (bool)$importParamList['ignore_prodtype'];
        $isPreviewPicture = (bool)$importParamList['img'];
        $isDetailPicture = (bool)$importParamList['img_full'];
        $isOtherPicture = (bool)$importParamList['img_prop'] && (int)$importParamList['img_more_prop'] > 0;
        $canImportAllPicInProp = (bool)$importParamList['img_more_all'];
        $isSomePicture = $isPreviewPicture || $isDetailPicture || $isOtherPicture;
        $isBarcode = (bool)$importParamList['barcode'];

        $isEnableActiveProp = Config::isEnableActiveProp($entity);
        $activePropId = Config::getActivePropId($entity);

        $measureList = [];
        if ($importParamList['uom']) {
            $measureList = ConfigurationUtils::getMeasureList();
        }

        $weightM = Config::getWeightM($entity);

        $isVatIncludedCatalog = \COption::getOptionString('catalog', 'default_product_vat_included') === 'Y';

        $translitParams = $importParamList['translit'] ? ConfigurationUtils::getIblockElementTranslitParams((int)$paramList['IBLOCK_ID']) : [];

        $el = new \CIblockElement;

        $parentProductsHrefs = [];
        if ($isVariant) {
            $propSkuList = PropertiesImportUtils::getSkuProps($paramList['IBLOCK_ID']);
            foreach ($arItems as $xmlId => $item) {
                $parentProductsHrefs[$xmlId] = $item->{'product'}->{'meta'}->{'href'};
            }
        }
            
        $parentItemList['ITEMS'] = [];
        if (count($parentProductsHrefs) > 0 && (ProductIdentifier::isExtCodesRequired() ? ExtCodes::isExsist() : true)) {
            $parentXmlIds = ProductIdentifier::resolveXmlIdsFromHrefs(array_keys(array_flip($parentProductsHrefs)));
            foreach ($arItems as $xmlId => $item) {
                if (isset($parentXmlIds[$item->{'product'}->{'meta'}->{'href'}])) {
                    $parentItemList['ITEMS'][$xmlId] = $parentXmlIds[$item->{'product'}->{'meta'}->{'href'}];
                }
            }
            if ((int)$paramList['PRODUCT_IBLOCK_ID'] > 0 && count($parentXmlIds) > 0) {
                $parentIdsByXmlIds = CatalogUtils::getProductIdsByXmlIds(array_values($parentXmlIds), $paramList['PRODUCT_IBLOCK_ID']);
                if (count($parentIdsByXmlIds) > 0) {
                    $parentItemList['PARENTS_ID'] = $parentIdsByXmlIds;
                    $parentItemList['PARENTS'] = CatalogUtils::getProductArray(array_values($parentIdsByXmlIds), (bool)$importParamList['parent_tracking']);
                    if($importParamList['parent_ratio']) {
                        $parentItemList['MEASURE_RATIO_PARENTS'] = CatalogUtils::getMeasureRatioList(array_values($parentIdsByXmlIds));
                    }
                }
            }
        }
            
        foreach ($arItems as $xmlId => $item) {

            $event = new \Bitrix\Main\Event(Config::getModuleId(true), "OnBeforeAddItem", array(
                'xmlId' => $xmlId,
                'item' => $item,
            ));

            $event->send();

            if ($event->getResults()) {
                foreach ($event->getResults() as $eventResult) {
                    if ($eventResult->getType() == \Bitrix\Main\EventResult::ERROR) {
                        continue 2;
                    }
                }
            }

            $arItemFields = [
                'NAME' => $item->{'name'},
                'IBLOCK_ID' => $paramList['IBLOCK_ID'],
                'XML_ID' => ProductIdentifier::getIdentifierValue($item),
            ];

            if ($item->{'archived'}) {
                if($importParamList['include_archived']){
                    $arItemFields['ACTIVE'] = 'N';
                } else {
                    continue;
                }                
            }

            $attrList = MoyskladImportUtils::getAttrList($item->{'attributes'});
            $attrAllList = MoyskladImportUtils::getAllAttrList($item->{'attributes'});

            if($isEnableActiveProp) {
                if(isset($attrList['boolean'][$activePropId]) && $attrList['boolean'][$activePropId] === true) {
                    $arItemFields['ACTIVE'] = 'Y';
                } else {
                    $arItemFields['ACTIVE'] = 'N';
                }
            }

            if ($importParamList['code'] && !$importParamList['code_uniq']) {
                $arItemFields['CODE'] = \CRbsMoyskladStocks::getTranslitCode($item->{'name'}, $translitParams, $importParamList['trim']);
            }
                
            //generate fake code
            if ($importParamList['code'] && $importParamList['code_uniq']) {
                $arItemFields['CODE'] = ProductIdentifier::getIdentifierValue($item);
            }

            if($importParamList['sort'] && isset($attrAllList[$sortProp]) && is_object($attrAllList[$sortProp])){
                $arItemFields['SORT'] = (int)$attrAllList[$sortProp]->{'value'};
            }

            if ($uid > 0) {
                $arItemFields['CREATED_BY'] = $uid;
                $arItemFields['MODIFIED_BY'] = $uid;
            }

            $parentItem = [];
            if ($isVariant) {
                if (
                        isset($parentItemList['ITEMS'][$xmlId]) &&
                        isset($parentItemList['PARENTS_ID'][$parentItemList['ITEMS'][$xmlId]])
                    ) {
                    $paramList['PARENT_XML_ID'] = $parentItemList['ITEMS'][$xmlId];
                    $paramList['PARENT_ID'] = $parentItemList['PARENTS_ID'][$paramList['PARENT_XML_ID']];
                    $arItemFields['XML_ID'] = ProductIdentifier::buildVariantXmlId(
                        $paramList['PARENT_XML_ID'],
                        ProductIdentifier::getIdentifierValue($item)
                    );
                } else {
                    continue;
                }
            }

            if (!$importParamList['section_off'] && (int)$arSectionsItems[$item->{'productFolder'}->{'meta'}->{'href'}] > 0) {
                $sectionItemBxId = (int)$arSectionsItems[$item->{'productFolder'}->{'meta'}->{'href'}];
            }
                    
            if ((int)$sectionItemBxId <= 0 && !$importParamList['section_off'] && isset($paramList['SECTION_ID'])) {
                $sectionItemBxId = $paramList['SECTION_ID'];
            }

            if ($importParamList['section_off'] && isset($paramList['SECTION_ID'])) {
                $arItemFields['IBLOCK_SECTION_ID'] = $paramList['SECTION_ID'];
            } elseif ((int)$sectionItemBxId > 0) {
                $arItemFields['IBLOCK_SECTION_ID'] = $sectionItemBxId;
            }
    
            $descriptionCheckArray = [
                'descr' => 'PREVIEW_TEXT',
                'descr_full' => 'DETAIL_TEXT'
            ];

            foreach ($descriptionCheckArray as $option => $bxField) {

                if (!$importParamList[$option]) continue;

                $isDefaultSource = $importParamList[$option . '_source'] === 'DEFAULT' || empty($importParamList[$option . '_source']) || $isVariant;

                $descrSource = !empty($item->{'description'}) ? (string)$item->{'description'} : '';
                if (!$isDefaultSource) {
                    $descrSource = !empty($attrList['string'][$importParamList[$option . '_source']]) ? (string)$attrList['string'][$importParamList[$option . '_source']] : '';
                }

                if (!empty($descrSource)) {
                    $arItemFields[$bxField] = $descrSource;
                }

                $descrType = in_array($importParamList[$option . '_type'], ['text', 'html']) ? $importParamList[$option . '_type'] : '';

                if (!empty($descrType)) {
                    $arItemFields[$bxField . '_TYPE'] = $descrType;
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
                        $arItemFields['PREVIEW_PICTURE'] = $firstFile->getBxFileArrayFromFileId();
                    }
                    if ($isDetailPicture) {
                        $arItemFields['DETAIL_PICTURE'] = $firstFile->getBxFileArrayFromFileId();
                    }
                }
            }
                
            $elId = $el->add($arItemFields, $importParamList['workflow'], $importParamList['updsearch'], $importParamList['resizepic']);

            if ($elId > 0) {

                if ($importParamList['code'] && $importParamList['code_uniq']) {
                    $wishCode = \CRbsMoyskladStocks::getTranslitCode($item->{'name'}, $translitParams, $importParamList['trim']);
                    $wishCodeUniq = \CRbsMoyskladStocks::getElementUniqCode($paramList['IBLOCK_ID'], $wishCode, $elId);
                    if (!empty($wishCodeUniq)) {
                        $el->Update($elId, ['CODE' => $wishCodeUniq]);
                    }
                }

                if($importParamList['tracking_type']) {
                    if(!empty($item->{'trackingType'})) {
                        TrackingTypeSetter::getInstance()->setTrackingType($elId, $item->{'trackingType'});
                    }
                }

                if ($isVariant) {
                    
                    if ((int)$paramList['SKU_PROPERTY_ID'] > 0 && (int)$paramList['PARENT_ID'] > 0) {
                        \CIBlockElement::SetPropertyValuesEx($elId, $arItemFields['IBLOCK_ID'], [$paramList['SKU_PROPERTY_ID'] => $paramList['PARENT_ID']]);
                    }

                    $productUpdate = ["ID" => $elId, 'VAT_INCLUDED' => $isVatIncludedCatalog ? 'Y' : 'N'];

                    if (isset($parentItemList['PARENTS'][$paramList['PARENT_ID']])) {

                        $parentItem = $parentItemList['PARENTS'][$paramList['PARENT_ID']];
                        $parentRatio = !empty($parentItemList['MEASURE_RATIO_PARENTS'][$paramList['PARENT_ID']]) ? $parentItemList['MEASURE_RATIO_PARENTS'][$paramList['PARENT_ID']] : [];

                        if ($importParamList['parent_sizes']) {
                            foreach (['width', 'length', 'height'] as $gabb) {
                                $productUpdate[mb_strtoupper($gabb)] = isset($parentItem[mb_strtoupper($gabb)]) ? (float)$parentItem[mb_strtoupper($gabb)] : 0;
                            }
                        }

                        if($importParamList['parent_tracking']) {
                            if(!empty($parentItem['UF_PRODUCT_GROUP'])) {
                                TrackingTypeSetter::getInstance()->setTrackingTypeFromId($elId, (int)$parentItem['UF_PRODUCT_GROUP']);
                            }
                        }

                        if ($importParamList['parent_weight']) {
                            $productUpdate['WEIGHT'] = isset($parentItem['WEIGHT']) ? (float)$parentItem['WEIGHT'] : 0;
                        }

                        if ($importParamList['parent_measure'] && isset($parentItem['MEASURE'])) {
                            $productUpdate['MEASURE'] = $parentItem['MEASURE'];
                        }

                        if($importParamList['parent_ratio'] && !empty($parentRatio['RATIO'])) {
                            \Bitrix\Catalog\MeasureRatioTable::add([
                                'PRODUCT_ID' => $elId,
                                'RATIO' => (float)$parentRatio['RATIO'],
                                'IS_DEFAULT' => 'Y'
                            ]);
                        }

                        if ((bool)$importParamList['vat'] && (int)$parentItem['VAT_ID'] > 0) {
                            $productUpdate['VAT_ID'] = (int)$parentItem['VAT_ID'];
                        }

                        if ((bool)$importParamList['vat_inc']) {
                            $productUpdate['VAT_INCLUDED'] = $parentItem['VAT_INCLUDED'];
                        }

                    }

                    $result = \Bitrix\Catalog\Model\Product::add($productUpdate);
                    if(!$result->isSuccess()) {
                        $arrLog['#ERROR#']++;
                        $arrLog['ERROR_LIST'][$item->{'externalCode'}] = "[" . $item->{'externalCode'} . "] " . implode(', ', $result->getErrorMessages());
                    } else {
                        if($isBarcode && Utils::array_exists($item, 'barcodes')) {
                            BarcodeSetter::add($elId, $item->{'barcodes'}, false, $uid);
                        }
                    }

                    PropertiesImportUtils::setSkuProps(["ID" => $elId, 'IBLOCK_ID' => $arItemFields['IBLOCK_ID']], $item, $propSkuList);

                } else {

                    $productUpdate = ["ID" => $elId, 'VAT_INCLUDED' => $isVatIncludedCatalog ? 'Y' : 'N'];

                    if ($importParamList['sizes']) {
                        if ((float)$item->{'weight'} > 0) {
                            $productUpdate['WEIGHT'] = (float)$item->{'weight'} * $weightM;
                        }
                        foreach (['width', 'length', 'height'] as $gabb) {
                            if (isset($attrAllList[$importParamList[$gabb]])) {
                                $productUpdate[mb_strtoupper($gabb)] = (float)$attrAllList[$importParamList[$gabb]]->{'value'};
                            }
                        }
                    }

                    if ($importParamList['uom'] && count($measureList) > 0 && !empty($item->{'uom'}->{'meta'}->{'href'})) {
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

                    if ($ratioProp !== 'N' && isset($attrAllList[$ratioProp]) && is_object($attrAllList[$ratioProp]) && $importParamList['ratio']) {
                        if ((float)$attrAllList[$ratioProp]->{'value'} > intval(0)) {
                            \Bitrix\Catalog\MeasureRatioTable::add([
                                'PRODUCT_ID' => $elId,
                                'RATIO' => (float)$attrAllList[$ratioProp]->{'value'},
                                'IS_DEFAULT' => 'Y'
                            ]);
                        }
                    }

                    $currentVat = !empty($item->{'effectiveVat'}) ? (float)round((float)$item->{'effectiveVat'}, 2) : intval(0);
                    $currentVatInc = !empty($item->{'effectiveVatEnabled'}) ? (bool)$item->{'effectiveVatEnabled'} : false;
                    $isZeroVat = $currentVat === 0 && !$currentVatInc;
                    if ($isZeroVat) {
                        $item->{'effectiveVat'} = 'N';
                    }
                    
                    $currentVatId = isset($vatList[$item->{'effectiveVat'}]) && (int)$vatList[$item->{'effectiveVat'}] > 0 ? (int)$vatList[$item->{'effectiveVat'}] : 0;

                    if ((bool)$importParamList['vat'] && $currentVatId > 0) {
                        $productUpdate['VAT_ID'] = $currentVatId;
                    }

                    if ((bool)$importParamList['vat_inc']) {
                        $productUpdate['VAT_INCLUDED'] = $currentVatInc ? 'Y' : 'N';
                    }
                    
                    if(!$isIgnoreProductType) {
                        if ((int)$item->{'variantsCount'} > 0) {
                            $productUpdate['TYPE'] = \Bitrix\Catalog\ProductTable::TYPE_SKU;
                        }
                    }
                    
                    $result = \Bitrix\Catalog\Model\Product::add($productUpdate);
                    if(!$result->isSuccess()) {
                        $arrLog['#ERROR#']++;
                        $arrLog['ERROR_LIST'][$item->{'externalCode'}] = "[" . $item->{'externalCode'} . "] " . implode(', ', $result->getErrorMessages());
                    } else {
                        if ($isBundle && $isImportBundle) {
                            ImportBundle::import($elId, $item, false, $bundlePartIblockId, $arrLog);
                        }
                        if($isBarcode && Utils::is_count($item->{'barcodes'})) {
                            BarcodeSetter::add($elId, $item->{'barcodes'}, true, $uid);
                        }
                    }

                    
                }
                    
                if ($isOtherPicture) {
                    PropertiesImportUtils::importFilesInProp($currentMsImagesArray, ['ID' => $elId, 'IBLOCK_ID' => $arItemFields['IBLOCK_ID']], [], (int)$importParamList['img_more_prop'], false, true);
                }
    
                if ($importParamList['props'] && Utils::is_count($propList)) {
                    PropertiesImportUtils::setPropsForItem(['ID' => $elId, 'IBLOCK_ID' => $paramList['IBLOCK_ID']], $item, $propList, $propBxTypes, $importParamList['update_facet'], false, [], true);
                }
    
                $arrLog['#ADD#']++;
                
            } else {

                $arrLog['#ERROR#']++;
                $arrLog['ERROR_LIST'][$item->{'externalCode'}] = "[" . $item->{'externalCode'} . "] " . $el->{'LAST_ERROR'};

            }
        }

        $event = new \Bitrix\Main\Event(Config::getModuleId(true), "OnAfterImportItems", array('entity' => $entity, 'items' => $arItems));
        $event->send();
    }
}