<?php
namespace Ipolh\SDEK\Bitrix\Controller;

use \Ipolh\SDEK\Api\Entity\Response\Part\DeliveryPoints\DeliveryPoint;

use \Ipolh\SDEK\Legacy\transitApplication;
use \Ipolh\SDEK\SDEK\SdekApplication;
use \Ipolh\SDEK\Bitrix\Tools;

use \Bitrix\Main\Result;
use \Bitrix\Main\Error;

class pvzController extends abstractController
{
    /**
     * @var array
     */
    protected static $arList = false;

    protected static $cacheTime = 86400;

    /**
     * @var SdekApplication|transitApplication|null
     */
    protected $application = null;

    public function __construct($loadList = true)
    {
        parent::__construct();

        if ($loadList)
            $this->loadPVZ();

        return $this;
    }

    public function getList()
    {
        return self::$arList;
    }

    /**
     * Get info from file
     */
    protected function loadPVZ()
    {
        if (!self::$arList) {
            self::$arList = json_decode(file_get_contents(self::getFilePath()),true);

            foreach(GetModuleEvents(self::$MODULE_ID,"onPVZListReady",true) as $arEvent) {
                ExecuteModuleEventEx($arEvent, array(&self::$arList));

                $akPVZ = array_keys(self::$arList['PVZ']);
                $akPOSTAMAT = array_keys(self::$arList['POSTAMAT']);
                $found = array();
                foreach ($akPVZ as $name){
                    if(!is_numeric($name)){
                        foreach(self::$arList['CITY'] as $code => $cityName){
                            if($cityName == $name){
                                if(array_key_exists($code,self::$arList['PVZ'])){
                                    self::$arList['PVZ'][$code] = array_merge(self::$arList['PVZ'][$code],self::$arList['PVZ'][$name]);
                                    unset(self::$arList['PVZ'][$name]);
                                    $found[$name] = $code;
                                }
                            }
                        }
                    }
                }
                foreach ($akPOSTAMAT as $name => $pvz){
                    if(!is_numeric($name)){
                        $newCode = false;
                        if(array_key_exists($name,$found)){
                            $newCode = $found[$name];
                        } else {
                            foreach(self::$arList['CITY'] as $code => $cityName){
                                if($cityName == $name){
                                    if(array_key_exists($code,self::$arList['POSTAMAT'])){
                                        $newCode = $code;
                                    }
                                }
                            }
                        }
                        if($newCode){
                            self::$arList['POSTAMAT'][$newCode] = array_merge(self::$arList['POSTAMAT'][$newCode],self::$arList['POSTAMAT'][$name]);
                            unset(self::$arList['POSTAMAT'][$name]);
                            $found[$name] = $newCode;
                        }

                    }
                }
            }
        }
    }

    /**
     * @param $location - CoreLocation, if null - all cities
     * @return Result
     */
    public function getPoints($location = null)
    {
        $result = new Result();

        $answer = $this->application->deliveryPoints($location);
        if ($answer->isSuccess()) {
            $response = $answer->getResponse();
            if ($response->getPointList() && $response->getPointList()->getQuantity()) {
                // At least one point returns
                $result->setData([
                    'DELIVERY_POINTS' => $response->getPointList(),
                ]);
            } else {
                $result->addError(new Error('Zero delivery points returns.'));
            }
        } else {
            if ($this->application->getErrorCollection()) {
                $this->application->getErrorCollection()->reset();
                while ($error = $this->application->getErrorCollection()->getNext())
                {
                    $result->addError(new Error($error->getMessage()));
                }
            }
            else
                $result->addError(new Error('Error while requests delivery points from API, but no error messages get from application.'));
        }

        return $result;
    }

    /**
     * @return Result
     */
    public function requestPoints()
    {
        $result = new Result();

        $pointsResult = $this->getPoints();
        if ($pointsResult->isSuccess())
        {
            $data = $pointsResult->getData();

            if ($data['DELIVERY_POINTS']->getQuantity())
            {
                $arList = array('PVZ' => array(), 'CITY' => array(), 'REGIONS' => array(), 'CITYFULL' => array(), 'COUNTRIES' => array());

                $allowedCountries = array();
                $optionCountries  = \sdekHelper::getCountryOptions();
                $arDict = array(
                    'rus' => 'RU',
                    'blr' => 'BY',
                    'kaz' => 'KZ'
                );

                foreach ($optionCountries as $countryCode => $setups) {
                    if (
                        array_key_exists($countryCode, $arDict) &&
                        array_key_exists('act', $setups) &&
                        $setups['act'] == 'Y'
                    )
                    {
                        $allowedCountries[] = $arDict[$countryCode];
                    }
                }

                $countryMap = array(
                    'RU' => GetMessage('IPOLSDEK_SYNCTY_rus'),
                    'BY' => GetMessage('IPOLSDEK_SYNCTY_blr'),
                    'KZ' => GetMessage('IPOLSDEK_SYNCTY_kaz')
                );

                $data['DELIVERY_POINTS']->reset();
                /** @var DeliveryPoint $point */
                while ($point = $data['DELIVERY_POINTS']->getNext())
                {
                    if (!$point->getIsHandout())
                        continue;

                    $pointLocation = $point->getLocation();
                    if (in_array($pointLocation->getCountryCode(), $allowedCountries)) {
                        $cityCode = $pointLocation->getCityCode();
                        $type = strtoupper($point->getType());
                        $city = trim($pointLocation->getCity());
                        $city = str_replace(Tools::getMessage('LANG_YO_S'), Tools::getMessage('LANG_YE_S'), $city);
                        if (strpos($city, '(') !== false)
                            $city = trim(substr($city, 0, strpos($city, '(')));
                        if (strpos($city, ',') !== false)
                            $city = trim(substr($city, 0, strpos($city, ',')));
                        $code = $point->getCode();

                        $phones = [];
                        $point->getPhones()->reset();
                        while ($phone = $point->getPhones()->getNext()) {
                            $phones[] = $phone->getNumber();
                        }
                        $phoneNumbers = implode(', ', $phones);

                        $arList[$type][$cityCode][$code] = array(
                            'Name'           => $point->getName(),
                            'WorkTime'       => $point->getWorkTime(),
                            'Address'        => $pointLocation->getAddress(),
                            'Phone'          => $phoneNumbers,
                            'Note'           => str_replace(array("\n", "\r"), '', nl2br($point->getNote())),
                            'cX'             => $pointLocation->getLongitude(),
                            'cY'             => $pointLocation->getLatitude(),
                            'Dressing'       => $point->getIsDressingRoom(),
                            'Cash'           => $point->isHaveCashless(),
                            'Station'        => $point->getNearestStation(),
                            'Site'           => $point->getSite(),
                            'Metro'          => $point->getNearestMetroStation(),
                            'payNal'         => $point->isAllowedCod(),
                            'AddressComment' => $point->getAddressComment()
                        );

                        if ($point->getWeightMax() || $point->getWeightMin()) {
                            $arList[$type][$cityCode][$code]['WeightLim'] = array(
                                'MIN' => $point->getWeightMin(),
                                'MAX' => $point->getWeightMax()
                            );
                        }

                        if ($dimensions = $point->getDimensions()) {
                            $dimensions->reset();
                            while ($dimension = $dimensions->getNext()) {
                                $arList[$type][$cityCode][$code]['Dimensions'] = array(
                                    'L' => $dimension->getDepth(),
                                    'W' => $dimension->getWidth(),
                                    'H' => $dimension->getHeight()
                                );
                            }
                        }

                        $arImgs = array();

                        if ($images = $point->getOfficeImageList()) {
                            $images->reset();
                            while ($image = $images->getNext()) {
                                if (strstr($_tmpUrl = $image->getUrl(), 'http') === false) {
                                    continue;
                                }
                                $arImgs[] = $image->getUrl();
                            }
                        }

                        if (count($arImgs = array_filter($arImgs)))
                            $arList[$type][$cityCode][$code]['Picture'] = $arImgs;

                        if (!array_key_exists($cityCode, $arList['CITY'])) {
                            $arList['CITY'][$cityCode] = $city;
                            $arList['CITYFULL'][$cityCode] = $countryMap[$pointLocation->getCountryCode()].' '.$pointLocation->getRegion().' '.$city;
                            $arList['REGIONS'][$cityCode] = implode(', ', array_filter(array($pointLocation->getRegion(), $countryMap[$pointLocation->getCountryCode()])));
                        }
                    }
                }

                foreach ($arList['PVZ'] as $cityId => $arPoints){
                    uasort($arList['PVZ'][$cityId],'Ipolh\SDEK\Bitrix\Controller\pvzController::sortPoints');
                }

//                krsort($arList['PVZ']);
                if (array_key_exists('POSTAMAT', $arList)) {
                    foreach ($arList['POSTAMAT'] as $cityId => $arPoints){
                        uasort($arList['POSTAMAT'][$cityId],'Ipolh\SDEK\Bitrix\Controller\pvzController::sortPoints');
                    }
//                    krsort($arList['POSTAMAT']);
                }

                if (empty($arList['PVZ'])) {
                    $result->addError(new Error(GetMessage('IPOLSDEK_SUNCPVZ_NODATA')));
                } else {
                    $result->setData(['POINTS' => $arList]);
                }
            }
            else
                $result->addError(new Error('No data while getting pickup points from API'));
        }
        else
            $result->addErrors($pointsResult->getErrors());

        return $result;
    }

    /**
     * @param bool $forced
     * @return Result
     */
    public function refreshPoints($forced = false)
    {
        $result = new Result();

        if ($forced || !$this->isActual()) {
            $requestResult = $this->requestPoints();

            if ($requestResult->isSuccess()) {
                $data = $requestResult->getData();

                if (!file_put_contents(self::getFilePath(), json_encode(Tools::encodeToUTF8($data['POINTS'])))) {
                    $result->addError(new Error(GetMessage('IPOLSDEK_SUNCPVZ_NOWRITE')));
                } else {
                    $result->setData(['IS_ACTUAL' => true, 'IS_UPDATED' => true]);
                }
            } else {
                $result->addErrors($requestResult->getErrors());
            }
        } else {
            $result->setData(['IS_ACTUAL' => true, 'IS_UPDATED' => false]);
        }

        return $result;
    }

    protected function isActual()
    {
        return (
            file_exists(self::getFilePath()) &&
            time() - filemtime(self::getFilePath()) < self::$cacheTime
        );
    }

    /**
     * @param $point1
     * @param $point2
     * @return int
     * for usort: sorts point in alphabet order, minding "Na" und "("
     */
    public static function sortPoints($point1, $point2)
    {
        $a = array($point1['Name'],$point2['Name']);

        foreach ($a as $pos => $name){
            if(strpos($name,'(') === 0){
                $a[$pos] = substr($a[$pos],1);
            }
            if(strpos($name,Tools::encodeToUTF8(GetMessage('IPOLSDEK_LANG_NA'))) === 0){
                $a[$pos] = trim(substr($a[$pos],strlen(Tools::encodeToUTF8(GetMessage('IPOLSDEK_LANG_NA')))));
            }
        }

        return ($a[0] < $a[1]) ? -1 : 1;
    }


    /**
     * @return string
     * where file should be
     */
    public static function getFilePath()
    {
        return $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".self::$MODULE_ID."/list.json";
    }

    /**
     * old usage
     */
    public function getListFile()
    {
        $arList = array();

        foreach(array('PVZ','POSTAMAT') as $type) {
            if(is_array(self::$arList) && array_key_exists($type,self::$arList)) {
                foreach (self::$arList[$type] as $cityCode => $arCityPVZ) {
                    if (array_key_exists($cityCode, self::$arList['CITY'])) {
                        $cityName                 = self::$arList['CITY'][$cityCode];
                        $arList[$type][$cityName] = array();
                        foreach ($arCityPVZ as $pvzCode => $arPVZ) {
                            $arList[$type][$cityName][$pvzCode] = $arPVZ;
                        }
                    }
                }
            }
        }

        return $arList;
    }
}