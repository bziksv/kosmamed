<?php
namespace Rbs\Moysklad\Services;

use Rbs\Moysklad\Utils;
use Rbs\Moysklad\LangMsg;
use Rbs\Moysklad\ApiNew;

class LocationUtils
{
    public static function getLocationStringByCodeFromBx(string $locationCode = '') : string
    {
        $result = '';
        
        if (!empty($locationCode)) {
            $locationFullName = [];
            $locRes = \Bitrix\Sale\Location\LocationTable::getList(array(
                'filter' => array(
                    '=CODE' => $locationCode,
                    '=PARENTS.NAME.LANGUAGE_ID' => LANGUAGE_ID,
                    '=PARENTS.TYPE.NAME.LANGUAGE_ID' => LANGUAGE_ID,
                ),
                'select' => array(
                    'NAME_LOCATION' => 'PARENTS.NAME.NAME',
                ),
                'order' => array(
                    'PARENTS.DEPTH_LEVEL' => 'asc'
                )
            ));
            while ($itemLoc = $locRes->fetch()) {
                $locationFullName[] = $itemLoc['NAME_LOCATION'];
            }
            if (!empty($locationFullName)) {
                $result = implode(', ', $locationFullName);
            }
        }
        
        return $result;
    }

    public static function getLocationCodeFromShipmentAddress(object $shipmentAddressFull = null): string
    {
        if($shipmentAddressFull === null) {
            return '';
        }

        $locationValues = [
            'parent' => '',
            'city_chain' => '',
        ];

        if(!Utils::property_exists($shipmentAddressFull, ['region'])) {
            $locationValues['parent'] = LocationUtils::getCountryNameByCountryMetaObject($shipmentAddressFull->{'country'});
        } else {
            $locationValues['parent'] = LocationUtils::getRegionNameByRegionMetaObject($shipmentAddressFull->{'region'});
            $locationValues['parent'] = LocationUtils::convertRegionNameToMs($locationValues['parent'], 'BX');
        }   

        if(Utils::property_exists($shipmentAddressFull, ['city'])) {
            $locationValues['city_chain'] = LocationUtils::convertCityNameToMs($shipmentAddressFull->{'city'});
        }

        $locationGetListParams = [
            'select' => [
                'CODE',
                'NAME_RU' => 'NAME.NAME'
            ]
        ];

        $lastFindedLoc = [];
        if(!empty($locationValues['parent'])) {
            
            $parentLocationName = \Bitrix\Sale\Location\Name\LocationTable::getList([
                'filter' => [
                    '=LANGUAGE_ID' => 'ru',
                    '=NAME' => $locationValues['parent']
                ],
                'select' => [
                    'LOCATION_ID'
                ],
                'cache' => [
                    'ttl' => 86400 * 365
                ]
            ])->fetch();
            
            if(!empty($parentLocationName['LOCATION_ID'])) {
                $lastFindedLoc = \Bitrix\Sale\Location\LocationTable::getList([
                    'filter' => [
                        '=ID' => $parentLocationName['LOCATION_ID']
                    ],
                    'select' => [
                        'LEFT_MARGIN', 'RIGHT_MARGIN', 'CODE'
                    ],
                    'cache' => [
                        'ttl' => 86400 * 365
                    ]
                ])->fetch();
            }
        }
        
        if(!empty($locationValues['city_chain']) && Utils::is_count($locationValues['city_chain'])) {

            $typeListDb = \Bitrix\Sale\Location\TypeTable::getList([
                'cache' => [
                    'ttl' => 86400 * 365
                ]
            ])->fetchAll();
            $typeList = [];
            foreach($typeListDb as $type) {
                $typeList[$type['CODE']] = $type['ID'];
            }

            $canSearch = false;
            
            if (!empty($locationValues['city_chain']['VILLAGE']) && isset($typeList['VILLAGE'])) {
                $filter = [
                    '%NAME_RU' => $locationValues['city_chain']['VILLAGE'],
                    '=TYPE_ID' => $typeList['VILLAGE'],
                    '=NAME.LANGUAGE_ID' => 'ru'
                ];
                $canSearch = true;
            } else {
                if (!empty($locationValues['city_chain']['CITY']) && isset($typeList['CITY'])) {
                    $filter = [
                        '%NAME_RU' => $locationValues['city_chain']['CITY'],
                        '=TYPE_ID' => $typeList['CITY'],
                        '=NAME.LANGUAGE_ID' => 'ru'
                    ];
                    $canSearch = true;
                }
            }

            if (!empty($lastFindedLoc)) {
                if (!empty($lastFindedLoc['LEFT_MARGIN'])) {
                    $filter['>LEFT_MARGIN'] = $lastFindedLoc['LEFT_MARGIN'];
                }
                if (!empty($lastFindedLoc['RIGHT_MARGIN'])) {
                    $filter['<RIGHT_MARGIN'] = $lastFindedLoc['RIGHT_MARGIN'];
                }
            }

            if($canSearch) {
                $tmpLastFinded = \Bitrix\Sale\Location\LocationTable::getList(
                    $locationGetListParams + ['filter' => $filter] + ['cache' => ['ttl' => 86400 * 365]]
                )->fetch();
                if(!empty($tmpLastFinded)) {
                    $lastFindedLoc = $tmpLastFinded;
                }
            }

            
        }

        return !empty($lastFindedLoc['CODE']) ? $lastFindedLoc['CODE'] : '';
    }

    public static function getRegionNameByRegionMetaObject(object $regionMeta = null): string
    {
        $regionName = '';
        if (Utils::property_exists($regionMeta, ['meta', 'href'])) {
            $regionId = array_pop(explode('/', $regionMeta->{'meta'}->{'href'}));
            $regionName = LangMsg::get("REG_ID_{$regionId}");
        }
        return !empty($regionName) ? $regionName : '';
    }

    public static function getCountryNameByCountryMetaObject(object $countryMeta = null): string
    {
        if (Utils::property_exists($countryMeta, ['meta', 'href'])) {
            $allCountries = ApiNew::get('/entity/country', [], 86400 * 365);
            if(Utils::is_success($allCountries) && Utils::array_exists($allCountries)) {
                foreach($allCountries->{'rows'} as $country) {
                    if($countryMeta->{'meta'}->{'href'} === $country->{'meta'}->{'href'}) {
                        return $country->{'name'};
                    }
                }
            }
        }
        return '';
    }

    public static function getLocationMetaDataFromMs($type = '', $value = '', $filterSearchField = 'name')
    {
        $result = '';
        $value = mb_strtolower($value);

        if (in_array($type, ['country', 'region']) && !empty($value)) {

            if($type === 'region') {
                $value = LocationUtils::convertRegionNameToMs($value, 'MS');
            }                

            $location = ApiNew::get('/entity/' . $type, ['filter' => $filterSearchField . '=' . $value], 864000);
            if (Utils::is_success($location) && Utils::array_exists($location)) {
                $result = (object)[
                    'meta' => $location->{'rows'}[intval(0)]->{'meta'}
                ];
            }
        }

        return $result;
    }

    public static function convertCityNameToMs(string $cityName = ''): array
    {
        $cityName = mb_strtolower($cityName);
        $cityNameParts = explode(',', $cityName); 
        $nameParts = [];
        foreach($cityNameParts as $partName) {
            $cityPartsDetail = explode(' ', $partName);
            foreach($cityPartsDetail as $key => $partDetail) {
                if(mb_strlen($partDetail) === intval(0)) {
                    continue;
                }
                if (trim($partDetail) === LangMsg::get('LOC_CITY_TYPE_CITY')) {
                    unset($cityPartsDetail[$key]);
                    $nameParts['CITY'] = trim(implode(' ', $cityPartsDetail));
                } else {
                    foreach ([
                        'SELO', 'HUTOR', 'STANICA', 'DEREVNYA', 'SS', 'PGT', 'POSELOK'
                    ] as $cityTypeId) {
                        if (trim($partDetail) === LangMsg::get('LOC_CITY_TYPE_' . $cityTypeId)) {
                            unset($cityPartsDetail[$key]);
                            $nameParts['VILLAGE'] = trim(implode(' ', $cityPartsDetail));
                            break;
                        }
                    }
                }                    
            }
        }
        return $nameParts;
    }

    public static function convertRegionNameToMs(string $regionName = '', string $convertVector = 'MS'): string
    {
        $regionName = mb_strtolower($regionName);
        $regionArrayName = explode(' ', $regionName);

        if ($convertVector === 'MS') {
            foreach ([
                'KRYM' => 'REPUBLIC',
                'SEVASTOPOL' => 'CITY',
                'MOSCOW' => 'CITY',
                'PITER' => 'CITY',
                'UGRA' => 'CUSTOM'
            ] as $uniqName => $locType) {
                if ($regionName === LangMsg::get('LOC_NAME_' . $uniqName . '_FULL')) {
                    if($locType === 'CUSTOM') {
                        return LangMsg::get('LOC_NAME_' . $uniqName . '_FULL_CUSTOM');
                    } else {
                        return trim(LangMsg::get('LOC_TYPE_' . $locType . '_SHORT')) . ' ' . trim(LangMsg::get('LOC_NAME_' . $uniqName . '_FULL'));
                    }                        
                }
            }
            foreach (['ALANIA', 'CHUVASHIA'] as $uniqName) {
                if ($regionName === LangMsg::get('LOC_NAME_' . $uniqName . '_FULL')) {
                    return LangMsg::get('LOC_NAME_' . $uniqName . '_FULL_CUSTOM');
                }
            }
            foreach (['AO', 'AOBL'] as $locType) {
                if (mb_strpos($regionName, LangMsg::get('LOC_TYPE_' . $locType . '_FULL')) !== false) {
                    return str_replace(LangMsg::get('LOC_TYPE_' . $locType . '_FULL'), LangMsg::get('LOC_TYPE_' . $locType . '_SHORT'), $regionName);
                }
            }
        }

        foreach($regionArrayName as $key => $partName) {
            if ($convertVector === 'MS') {
                foreach (['OBLAST', 'REPUBLIC'] as $locType) {
                    if ($partName === LangMsg::get('LOC_TYPE_' . $locType . '_FULL')) {
                        $regionArrayName[$key] = LangMsg::get('LOC_TYPE_' . $locType . '_SHORT');
                    }
                }
                foreach (['YAKYT'] as $uniqName) {
                    if ($partName === LangMsg::get('LOC_NAME_' . $uniqName . '_FULL')) {
                        $regionArrayName[$key] = LangMsg::get('LOC_NAME_' . $uniqName . '_SHORT');
                    }
                }
            }
            if ($convertVector === 'BX') {
                if ($partName === trim(LangMsg::get('LOC_TYPE_CITY_SHORT'))) {
                    unset($regionArrayName[$key]);
                }
                foreach(['OBLAST', 'REPUBLIC', 'AO', 'AOBL'] as $locType) {
                    if ($partName === LangMsg::get('LOC_TYPE_' . $locType . '_SHORT')) {
                        $regionArrayName[$key] = LangMsg::get('LOC_TYPE_' . $locType . '_FULL');
                    }
                }
                foreach(['SAHA', 'ALANIA', 'CHUVASHIA', 'UGRA', 'KRYM'] as $uniqName) {
                    if ($partName === LangMsg::get('LOC_NAME_' . $uniqName . '_SHORT')) {
                        return LangMsg::get('LOC_NAME_' . $uniqName . '_FULL');
                    }
                }
            }
        }

        return implode(' ', $regionArrayName);
    }
}