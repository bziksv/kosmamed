<?php
namespace Rbs\Moysklad\Services;

use Rbs\Moysklad\Config;
use Rbs\Moysklad\Utils;
use Rbs\Moysklad\LangMsg;

class MsPropertyProcess
{
    public static function setPropertyToMsOrder(&$customerOrder)
    {
        $attributes = [];
        $shipmentAddress = [];
        $metaAttrs = \CRbsMoyskladHelper::getMetadataWithAttrs('customerorder', 86400 * 7);

        if (empty($customerOrder)) {
            return;
        }

        $order = $customerOrder->getOrderEntity();

        //CREATE STANDART FIELDS ARRAY
        $fieldIds = Config::getOrderFieldIds();
        if (Utils::is_count($fieldIds)) {
            foreach ($fieldIds as $fieldId => $propIdMs) {
                $value = '';
                switch ($fieldId) {
                    case 'PUBLICK_LINK':
                        if (\Bitrix\Sale\Helpers\Order::isAllowGuestView($order)) {
                            $value = \Bitrix\Sale\Helpers\Order::getPublicLink($order);
                        }
                    break;
                    case 'COUPON_LIST':
                        $value = '';
                        $discountData = $order->getDiscount()->getApplyResult();
                        if (Utils::is_count($discountData['COUPON_LIST'])) {
                            foreach ($discountData['COUPON_LIST'] as $coupon) {
                                if ($coupon['APPLY'] === 'Y') {
                                    $value .= $coupon['COUPON'] . "\n";
                                    break;
                                }
                            }
                        }
                    break;
                    case 'LID':
                        $value = $order->getSiteId($fieldId);
                    break;
                }
                if (!empty($value)) {
                    $attributes[$propIdMs] = [
                        'id' => $propIdMs,
                        'value' => $value
                    ];
                }
            }
        }

        if ($propertyCollection = $order->getPropertyCollection()) {
            //CREATE ENUM PROPS
            $propEnumIds = Config::getEnumPropsIds(['PERSON_TYPE_ID' => $order->getPersonTypeId()]);
            if (Utils::is_count($propEnumIds)) {
                foreach ($propEnumIds as $propIdBx) {
                    $propValueBx = null;
                    if ($property = $propertyCollection->getItemByOrderPropertyId($propIdBx)) {
                        $propValueBx = $property->getValue();
                        if ($property->getType() === 'ENUM') {

                            $propBxEnumValue = \CRbsMoyskladHelper::getEnumBxPropValue($order, $propIdBx);

                            if(!empty($propBxEnumValue)) {

                                $propEnumParams = Config::getCustomEntityProp($propIdBx);
                                $propIdMs = $propEnumParams[intval(0)];

                                if (Utils::count($propEnumParams) === intval(2)) {

                                    $propEntityMsId = $propEnumParams[1];

                                    $propEntityValueMs = \CRbsMoyskladHelper::getCustomEntityValue($propBxEnumValue, $propEntityMsId);
                                    
                                    if(Utils::property_exists($propEntityValueMs, ['meta'])) {
                                        $attributes[$propIdMs] = [
                                            'id' => $propIdMs,
                                            'value' => $propEntityValueMs
                                        ];
                                    }

                                }

                                if (Utils::count($propEnumParams) === intval(1)) {
                                    if(isset($metaAttrs->{'attributesById'}[$propIdMs])) {
                                        if(in_array($metaAttrs->{'attributesById'}[$propIdMs]->{'type'}, [
                                            'string', 'link', 'text'
                                        ])) {
                                            $attributes[$propIdMs] = [
                                                'id' => $propIdMs,
                                                'value' => (string)$propBxEnumValue
                                            ];
                                        }
                                    }
                                }
                            }
                            

                        }
                    }
                }
            }

            //CREATE LOCATION PROPS
            $propLocIds = Config::getLocationPropIds();
            if (Utils::is_count($propLocIds)) {
                foreach ($propLocIds as $propIdBx => $propLoc) {
                    $locationFullName = [];
                    foreach ($propLoc as $locType => $propIdMs) {
                        $propValueBx = null;
                        if ($property = $propertyCollection->getItemByOrderPropertyId($propIdBx)) {
                            $propValueBx = $property->getValue();
                            if ($property->getType() === 'LOCATION' && !empty($propValueBx)) {

                                $locRes = \Bitrix\Sale\Location\LocationTable::getList(array(
                                    'filter' => array(
                                        '=CODE' => $propValueBx,
                                        '=PARENTS.NAME.LANGUAGE_ID' => LANGUAGE_ID,
                                        '=PARENTS.TYPE.NAME.LANGUAGE_ID' => LANGUAGE_ID,
                                        '=PARENTS.TYPE.CODE' => $locType === 'CITY' ? ['CITY', 'VILLAGE'] : $locType
                                    ),
                                    'select' => array(
                                        'NAME_LOCATION' => 'PARENTS.NAME.NAME',
                                    ),
                                    'order' => array(
                                        'PARENTS.DEPTH_LEVEL' => 'asc'
                                    )
                                ));
                                
                                if ($itemLoc = $locRes->fetch()) {
                                    $locationValueBx = $itemLoc['NAME_LOCATION'];
                                    if (mb_strpos($propIdMs, 'ADDR_') === intval(0)) {
                                        $addrField = array_pop(explode('_', $propIdMs));
                                        if (in_array($addrField, ['country', 'region'])) {
                                            $shipmentAddress[$addrField] = \CRbsMoyskladHelper::getMetaLocationFromMs($addrField, (string)$locationValueBx);
                                        } else {
                                            $shipmentAddress[$addrField] = (string)$locationValueBx;
                                        }
                                        if (isset($shipmentAddress[$addrField]) && empty($shipmentAddress[$addrField])) {
                                            unset($shipmentAddress[$addrField]);
                                        }
                                    } else {
                                        $attributes[$propIdMs] = [
                                            'id' => $propIdMs,
                                            'value' => (string)$locationValueBx
                                        ];
                                    }
                                }
                            }
                        }
                    }
                }
            }

            //CREATE OTHER PROPS
            $propIds = Config::getPropsIds();
            if (Utils::is_count($propIds)) {
                foreach ($propIds as $propIdBx => $propIdMs) {

                    $propValueBx = null;

                    $currentAttrMeta = Utils::property_exists($metaAttrs, ['attributesById']) && !empty($metaAttrs->{'attributesById'}[$propIdMs]) ? $metaAttrs->{'attributesById'}[$propIdMs] :  (object)[
                        'type' => 'empty'
                    ];

                    if ($property = $propertyCollection->getItemByOrderPropertyId($propIdBx)) {

                        $propValueBx = $property->getValue();
                        if (empty($propValueBx)) {
                            continue;
                        }

                        $propEntityMsId = '';
                        if(mb_strpos($propIdMs, ';') !== false) {
                            $propEnumTmpInfo = explode(';', $propIdMs);
                            $propIdMs = $propEnumTmpInfo[0];
                            $propEntityMsId = $propEnumTmpInfo[1];
                        }

                        $propMsIdType = '';
                        if(isset($metaAttrs->{'attributesById'}[$propIdMs])) {
                            $propMsIdType = $metaAttrs->{'attributesById'}[$propIdMs]->{'type'};
                        }

                        //standart ms address value

                        if (mb_strpos($propIdMs, 'ADDR_') === intval(0)) {
                            $addrField = array_pop(explode('_', $propIdMs));
                            if(!isset($shipmentAddress[$addrField]) && $property->getType() !== 'LOCATION') {
                                if (in_array($addrField, ['country', 'region'])) {
                                    $shipmentAddress[$addrField] = \CRbsMoyskladHelper::getMetaLocationFromMs($addrField, (string)$propValueBx);
                                } else {
                                    $shipmentAddress[$addrField] = (string)$propValueBx;
                                }
                            }
                            if (isset($shipmentAddress[$addrField]) && empty($shipmentAddress[$addrField])) {
                                unset($shipmentAddress[$addrField]);
                            }
                            continue;
                        }

                        switch ($property->getType()) {
                            case 'LOCATION':

                                $locationFullName = \CRbsMoyskladHelper::getLocationStringFromBx((string)$propValueBx);
                                if (!empty($locationFullName)) {
                                    $attributes[$propIdMs] = [
                                        'id' => $propIdMs,
                                        'value' => (string)$locationFullName
                                    ];
                                }
                                
                            break;
                            case 'FILE':
                                
                                if (
                                    is_array($propValueBx) &&
                                    ((int)$propValueBx['ID'] > intval(0) || !empty($propValueBx['tmp_name'])) &&
                                    (!isset($propValueBx['DELETE']) || $propValueBx['DELETE'] !== 'on')
                                ) {
                                    $file = $propValueBx['tmp_name'] ? : ((int)$propValueBx['ID'] > intval(0) ? $_SERVER['DOCUMENT_ROOT'] . \CFile::GetPath($propValueBx['ID']) : '');
                                    $fileName = $propValueBx['name'] ? : $propValueBx['ORIGINAL_NAME'];
                                    if (file_exists($file)) {
                                        $attributes[$propIdMs] = [
                                            'id' => $propIdMs,
                                            'file' => (object)[
                                                'filename' => $fileName,
                                                'content' => base64_encode(file_get_contents($file))
                                            ]
                                        ];
                                    }
                                }

                            break;
                            case 'Y/N':

                                if($currentAttrMeta->{'type'} === 'boolean') {
                                    $attributes[$propIdMs] = [
                                        'id' => $propIdMs,
                                        'value' => $propValueBx === 'Y' ? true : false
                                    ];
                                } else { //only str props
                                    $attributes[$propIdMs] = [
                                        'id' => $propIdMs,
                                        'value' => $propValueBx
                                    ];
                                }
                                

                            break;
                            case 'NUMBER':

                                $attributes[$propIdMs] = [
                                    'id' => $propIdMs,
                                    'value' => (float)$propValueBx
                                ];

                            break;
                            case 'DATE':

                                try {
                                    $date = Config::getDateTime($propValueBx);
                                    if ($propIdMs === 'PLANNED_DATE') {
                                        $customerOrder->setOrderChangeStack('deliveryPlannedMoment', $date->format('Y-m-d H:i:s'));
                                    } else {
                                        $attributes[$propIdMs] = [
                                            'id' => $propIdMs,
                                            'value' => (string)$date->format('Y-m-d H:i:s')
                                        ];
                                    }
                                } catch (\Exception $e) {
                                    $customerOrder->addWarningMessage(LangMsg::get('ERROR_DATE_INPUT'));
                                }

                            break;
                            case 'STRING':

                                if($propMsIdType === 'customentity') {

                                    if (!empty($propEntityMsId)) {

                                        $propEntityValueMs = \CRbsMoyskladHelper::getCustomEntityValue($propValueBx, $propEntityMsId);

                                        if (Utils::property_exists($propEntityValueMs, ['meta'])) {
                                            $attributes[$propIdMs] = [
                                                'id' => $propIdMs,
                                                'value' => $propEntityValueMs
                                            ];
                                        }
                                    }

                                } else {
                                    $attributes[$propIdMs] = [
                                        'id' => $propIdMs,
                                        'value' => (string)$propValueBx
                                    ];
                                }                                    
                                
                            break;
                        }
                    }
                }
            }
        }
        
        if (Utils::is_count($shipmentAddress)) {
            $customerOrder->setOrderChangeStack('shipmentAddressFull', $shipmentAddress);
        }

        if (Utils::is_count($attributes)) {
            $customerOrder->setOrderChangeStack('attributes', $attributes);
        }
    }
}