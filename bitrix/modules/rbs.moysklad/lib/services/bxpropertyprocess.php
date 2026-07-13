<?php
namespace Rbs\Moysklad\Services;

use Rbs\Moysklad\Config;
use Rbs\Moysklad\Utils;
use Rbs\Moysklad\LangMsg;
use Rbs\Moysklad\ApiNew;
use Rbs\Moysklad\Helper;

class BxPropertyProcess
{
    public static function setPropertyToBxOrder(&$customerOrder)
    {
        if (!($customerOrder instanceof \Rbs\Moysklad\Customerorder) || !$customerOrder->isLoaded()) {
            return;
        }

        $order = $customerOrder->getOrderEntity();
        $orderMs = $customerOrder->getOrder();

        if (Utils::array_exists($orderMs, 'attributes') || Utils::property_exists($orderMs, ['shipmentAddressFull'])) {

            if ($propertyCollection = $order->getPropertyCollection()) {

                //IMPORT ENUM PROPS
                $entityAttrs = $customerOrder->getAttributesByType(array_merge(['customentity'], Config::getStandartEntityNamesForEnumProp()));
                $strAttrs = $customerOrder->getAttributesByType(['text', 'string', 'link']);
                $numberAttrs = $customerOrder->getAttributesByType(['double', 'long']);
                $dateAttrs = $customerOrder->getAttributesByType(['time']);
                $fileAttrs = $customerOrder->getAttributesByType(['file']);
                $boolAttrs = $customerOrder->getAttributesByType(['boolean']);

                $metaAttrs = \CRbsMoyskladHelper::getMetadataWithAttrs('customerorder', 86400 * 7);

                //ENUM PROPS
                $propEnumIds = Config::getEnumPropsIds(['PERSON_TYPE_ID' => $order->getPersonTypeId()]);

                if (Utils::is_count($propEnumIds)) {
                    
                    foreach ($propEnumIds as $propIdBx) {
                        if ($property = $propertyCollection->getItemByOrderPropertyId($propIdBx)) {
                            if ($property->getType() === 'ENUM') {

                                $propEnumParams = Config::getCustomEntityProp($propIdBx);
                                $propIdMs = $propEnumParams[intval(0)];

                                $propValueBx = \CRbsMoyskladHelper::getEnumBxPropValue($order, $propIdBx);
                                $propValueMs = '';
                                $propTypeMs = '';

                                if (isset($metaAttrs->{'attributesById'}[$propIdMs])) {
                                    $propTypeMs = $metaAttrs->{'attributesById'}[$propIdMs]->{'type'};
                                }

                                if(!empty($propTypeMs)) {
                                    if(in_array($propTypeMs, ['string', 'text', 'link'])) {
                                        if (isset($strAttrs[$propIdMs]) && !is_object($strAttrs[$propIdMs])) {
                                            $propValueMs = (string)$strAttrs[$propIdMs];
                                        }
                                    } else if(isset($entityAttrs[$propIdMs])){
                                        $propValueMs = (string)$entityAttrs[$propIdMs]->{'name'};
                                    }
                                    
                                }

                                if (empty($propValueBx) || $propValueBx !== $propValueMs) {
                                    $enumId = \CRbsMoyskladHelper::getBxPropEnumValue($propIdBx, $propValueMs);
                                    if($enumId > 0) {
                                        $property->setValue($enumId);
                                    }
                                }

                            }
                        }
                    }

                }

                //IMPORT STR PROPS
                $propIds = Config::getPropsIds('customerorder', ['PERSON_TYPE_ID' => $order->getPersonTypeId()]);
                if (Utils::is_count($propIds)) {

                    $bxPropsIfno = \Bitrix\Sale\Property::getList(['select' => ['ID', 'TYPE', 'CODE', 'NAME'], 'filter' => ['ID' => array_keys($propIds)]])->fetchAll();
                    if(Utils::is_count($bxPropsIfno)) {

                        foreach($bxPropsIfno as $propInfo) {

                            $propIdBx = $propInfo['ID'];
                            $propIdMs = $propIds[$propIdBx];

                            if (mb_strpos($propIdMs, ';') !== false) {
                                $propEnumTmpInfo = explode(';', $propIdMs);
                                $propIdMs = $propEnumTmpInfo[0];
                                $propEntityMsId = $propEnumTmpInfo[1];
                            }

                            $currentAttrMeta = Utils::property_exists($metaAttrs, ['attributesById']) && !empty($metaAttrs->{'attributesById'}[$propIdMs]) ? $metaAttrs->{'attributesById'}[$propIdMs] :  (object)[
                                'type' => 'empty'
                            ];

                            $propValueBx = null;
                            $property = $propertyCollection->getItemByOrderPropertyId($propIdBx);
                            if (!is_null($property)) {
                                $propValueBx = $property->getValue();
                            } else {
                                $property = $propertyCollection->createItem($propInfo);
                            }

                            switch($propInfo['TYPE']) {
                                case 'STRING':
                                    if($currentAttrMeta->{'type'} === 'customentity') {
                                        if (isset($entityAttrs[$propIdMs])) {
                                            if ($propValueBx != $entityAttrs[$propIdMs]->{'name'}) {
                                                $property->setValue($entityAttrs[$propIdMs]->{'name'});
                                            }
                                        }
                                    } else {
                                        if (isset($strAttrs[$propIdMs])) {
                                            if ($propValueBx != $strAttrs[$propIdMs]) {
                                                $property->setValue($strAttrs[$propIdMs]);
                                            }
                                        }
                                    }
                                    
                                    break;
                                case 'NUMBER':
                                    if (isset($numberAttrs[$propIdMs])) {
                                        if ((float)$propValueBx !== (float)$numberAttrs[$propIdMs]) {
                                            $property->setValue((float)$numberAttrs[$propIdMs]);
                                        }
                                    }
                                    break;
                                case 'Y/N':
                                    $propValueBx = $propValueBx === 'Y' ? 1 : 0;
                                    if($currentAttrMeta->{'type'} === 'boolean') {
                                        if (isset($boolAttrs[$propIdMs])) {
                                            if ($propValueBx !== intval($boolAttrs[$propIdMs])) {
                                                $property->setValue((bool)$boolAttrs[$propIdMs] ? 'Y' : 'N');
                                            }
                                        }
                                    } else {
                                        if (isset($strAttrs[$propIdMs])) {
                                            $tmpValue = $strAttrs[$propIdMs] === 'N' ? 0 : 1;
                                            if ($propValueBx !== $tmpValue) {
                                                $property->setValue((bool)$tmpValue ? 'Y' : 'N');
                                            }
                                            unset($tmpValue);
                                        }
                                    }
                                    
                                    break;
                                case 'FILE':

                                    if (isset($fileAttrs[$propIdMs]) && Utils::property_exists($fileAttrs[$propIdMs], ['download', 'href'])) {
                                        $fileDownload = $fileAttrs[$propIdMs]->{'download'}->{'href'};
                                        $fileExtId = md5(serialize([$propIdBx, $propIdMs, $orderMs->{'id'}]));
                                        $filePath = ApiNew::download($fileDownload, $fileAttrs[$propIdMs]->{'value'}, 'tmp/' . $fileExtId);
                                        if(\file_exists($filePath)) {
                                            $isEqualFile = false;
                                            if(is_array($propValueBx) && !empty($propValueBx['SRC'])) {
                                                $isEqualFile = md5(file_get_contents($filePath)) == md5(file_get_contents($_SERVER['DOCUMENT_ROOT'] . $propValueBx['SRC']));
                                            }
                                            if(!$isEqualFile) {
                                                $fileArray = \CFile::MakeFileArray($filePath);
                                                if (is_array($fileArray)) {
                                                    $fileArray['MODULE_ID'] = Config::getModuleId(true);
                                                    $fileId = \CFile::SaveFile($fileArray,  $fileArray['MODULE_ID']);
                                                    if (is_numeric($fileId) && $fileId > intval(0)) {
                                                        $fileId = is_array($fileId) ? $fileId['ID'] : $fileId;
                                                        $property->setValue($fileId);
                                                    }
                                                }
                                            }
                                            unlink($filePath);
                                        }
                                    }

                                    break;
                                case 'DATE':
                                    try {

                                        $culture = \Bitrix\Main\Context::getCurrent()->getCulture();
                                        if (!empty($propValueBx)) {
                                            $dateValueBx = Config::getDateTime($propValueBx);
                                        } else {
                                            $dateValueBx = Config::getDateTime();
                                        }
                                        $dateBx = \Bitrix\Main\Type\DateTime::createFromPhp($dateValueBx);

                                        $dateValueMs = null;
                                        if ($propIdMs === 'PLANNED_DATE') {
                                            if (!empty($orderMs->{'deliveryPlannedMoment'})) {
                                                $dateValueMs = Config::getDateTime($orderMs->{'deliveryPlannedMoment'});
                                            }
                                        } else {
                                            if (isset($dateAttrs[$propIdMs])) {
                                                $dateValueMs = Config::getDateTime($dateAttrs[$propIdMs]);
                                            }
                                        }

                                        $dateMs = '';
                                        if ($dateValueMs !== null) {
                                            $dateMs = \Bitrix\Main\Type\DateTime::createFromPhp($dateValueMs);
                                        }

                                        if (empty($dateMs) && !empty($propValueBx)) {
                                            $property->setValue('');
                                        }

                                        if ($dateMs !== '') {
                                            if ($dateMs->toString($culture) !== $dateBx->toString($culture)) {
                                                $property->setValue($dateMs->toString($culture));
                                            }
                                        }

                                    } catch (\Exception $e) {
                                        $customerOrder->addWarningMessage(LangMsg::get('ERROR_DATE_INPUT'));
                                    }

                                    break;
                            }

                            if (mb_strpos($propIdMs, 'ADDR_') === intval(0)) {
                                $addrField = str_replace('ADDR_', '', $propIdMs);
                                if (!in_array($addrField, ['country', 'region'])) {
                                    if (Utils::property_exists($orderMs, ['shipmentAddressFull', $addrField]) && !empty($orderMs->{'shipmentAddressFull'}->{$addrField})) {

                                        if($propInfo['TYPE'] === 'LOCATION' && $addrField === 'city') {

                                            $locCode = \CRbsMoyskladHelper::findLocationCodeFromShipmentAddress($orderMs->{'shipmentAddressFull'});
                                            if(!empty($locCode)) {
                                                $property->setValue($locCode);
                                            }
                                            
                                        } else {
                                            if (Helper::isDifferentStringFields($propValueBx, $orderMs->{'shipmentAddressFull'}->{$addrField})) {
                                                $property->setValue($orderMs->{'shipmentAddressFull'}->{$addrField});
                                            }
                                        }

                                    }
                                }
                            }

                        }
                    }

                }
            }

        }
    }
}