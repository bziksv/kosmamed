<?php
namespace Ipolh\SDEK;

use Ipolh\SDEK\Bitrix\Controller\pvzController;

IncludeModuleLangFile(__FILE__);

class pvzWidjetHandler /*extends abstractGeneral*/
{

    protected static $MODULE_LBL = IPOLH_SDEK_LBL;
    protected static $MODULE_ID  = IPOLH_SDEK;

    protected static $selDeliv = '';

    protected static $savingInput = 'chosenPVZ';
    protected static $postField   = 'sdek';

    /**
     * Prepare data about delivery
     */
    public static function pickupLoader($arResult,$arUR)
    {
        if(!\CDeliverySDEK::isActive()) return;

        \CDeliverySDEK::$orderWeight = ($arResult['ORDER_WEIGHT']) ? $arResult['ORDER_WEIGHT'] : \CDeliverySDEK::$orderWeight;
        \CDeliverySDEK::$orderPrice  = ($arResult['ORDER_PRICE'])  ? $arResult['ORDER_PRICE']  : \CDeliverySDEK::$orderPrice;

        self::$selDeliv = $arUR['DELIVERY_ID'];
    }

    /**
     * Include PVZ widget component
     */
    public static function loadComponent($arParams = array())
    {
        if(!is_array($arParams))
            $arParams = array();

        if(
            \CDeliverySDEK::isActive()
            && (!array_key_exists('is_ajax_post',$_REQUEST) || $_REQUEST['is_ajax_post'] != 'Y')
            && (!array_key_exists('AJAX_CALL', $_REQUEST)   || $_REQUEST["AJAX_CALL"] != 'Y')
            && (!array_key_exists('ORDER_AJAX', $_REQUEST)  || !$_REQUEST["ORDER_AJAX"])
        )
        {
            if(option::get('noYmaps') == 'Y' || defined('BX_YMAP_SCRIPT_LOADED') || defined('IPOL_YMAPS_LOADED'))
                $arParams['NOMAPS'] = 'Y';
            elseif(!array_key_exists('NOMAPS',$arParams) || $arParams['NOMAPS'] != 'Y')
                define('IPOL_YMAPS_LOADED',true);
            if(option::get('vidjetSearch') === 'Y'){
                $arParams['SEARCH_ADDRESS'] = 'Y';
            }
            $componentName = option::get('widjetVersion');
            $GLOBALS['APPLICATION']->IncludeComponent('ipol:'.$componentName, "order", $arParams,false);
        }
    }

    public static function onBufferContent(&$content) {
        if(\CDeliverySDEK::$city && \CDeliverySDEK::isActive()){
            $arData = self::getCurrentOrderInfo();
            $noJson = self::no_json($content);
            if (((array_key_exists('is_ajax_post', $_REQUEST) && $_REQUEST['is_ajax_post'] == 'Y')
                    || (array_key_exists('AJAX_CALL', $_REQUEST) && $_REQUEST["AJAX_CALL"] == 'Y')
                    || array_key_exists('ORDER_AJAX', $_REQUEST)) && $noJson) {
                $content .= '<input type="hidden" id="sdek_city" name="sdek_city" value=\''.$arData['city'].'\' />'; // city
                $content .= '<input type="hidden" id="sdek_cityID" name="sdek_cityID" value=\''.$arData['cityID'].'\' />'; // city
                $content .= '<input type="hidden" id="sdek_sdekID" name="sdek_sdekID" value=\''.$arData['sdekID'].'\' />'; // city
                $content .= '<input type="hidden" id="sdek_dostav" name="sdek_dostav" value=\''.$arData['dostav'].'\' />'; // selected delivery variant
                $content .= '<input type="hidden" id="sdek_payer" name="sdek_payer" value=\''.$arData['payer'].'\' />'; // payer
                $content .= '<input type="hidden" id="sdek_paysystem" name="sdek_paysystem" value=\''.$arData['paysystem'].'\' />'; // payment system

                $content .= '<input type="hidden" id="'.self::getPostField().'" name="'.self::getPostField().'" value=\''.json_encode(\CDeliverySDEK::zajsonit($arData)).'\' />';//new widjet
            } elseif(((array_key_exists('soa-action', $_REQUEST) && $_REQUEST['soa-action'] == 'refreshOrderAjax')
                    || (array_key_exists('action', $_REQUEST) &&$_REQUEST['action'] == 'refreshOrderAjax')) && !$noJson) {
                $content = substr($content, 0, strlen($content) - 1) . ',"' . self::getPostField() . '":{"city":"' . \CDeliverySDEK::zajsonit($arData['city']) . '","cityId":"' . $arData['cityID'] . '","sdekId":"' . $arData['sdekID'] . '","dostav":"' . $arData['dostav'] . '","payer":"' . $arData['payer'] . '","paysystem":"' . $arData['paysystem'] . '"}}';
            }
        }
    }

    public static function onAjaxAnswer(&$result){
        if(
            \CDeliverySDEK::$city &&
            \CDeliverySDEK::isActive() &&
            (is_array($result['order']) && !array_key_exists('REDIRECT_URL',$result['order']))
        )
            $result['sdek'] = array(
                'city'   => \CDeliverySDEK::$city,
                'cityId' => \CDeliverySDEK::$cityId,
                'sdekId' => \CDeliverySDEK::$sdekCity,
                'dostav' => self::$selDeliv
            );
    }

    public static function getMapsScript(){
        $path = 'https://api-maps.yandex.ru/2.1/?lang=ru_RU';
        if($key = option::get('ymapsAPIKey')){
            $path .= '&apikey='.$key;
        }
        return $path;
    }

    public static function getCurrentOrderInfo(){
        // Case: some pathetic script calls delivery calculation in SOA after usual component calculation, with empty LOCATION_TO
        // As result $sdekCity dropped to false in \CDeliverySDEK::Compability() and empty sdekID sent to widget
        if (!empty(\CDeliverySDEK::$sdekCity)) {
            $sdekId = \CDeliverySDEK::$sdekCity;
        } else {
            $arCity = \sqlSdekCity::getByBId(\CDeliverySDEK::$cityId);
            $sdekId = $arCity['SDEK_ID'];
        }

        return array(
            'city'      => \CDeliverySDEK::$city,
            'cityID'    => \CDeliverySDEK::$cityId,
            'sdekID'    => $sdekId,
            'dostav'    => self::$selDeliv,
            'payer'     => \CDeliverySDEK::$payerType,
            'paysystem' => \CDeliverySDEK::$paysystem
        );
    }

//	 public static function getPVZ($arParams = array()){
    // $arList = CDeliverySDEK::getListFile();
//		 $weight = option::get('weightD');
    // $arList['PVZ'] = CDeliverySDEK::weightPVZ($weight,$arList['PVZ']);
//	 }

    /**
     * @param array $arData - array of type city - sdekCityId || cityName, mode - type of points (PVZ/PICKUP)
     * @return void
     */
    public static function getCityPvz($arData = false)
    {
        $weightCheck = \Ipolh\SDEK\option::get('weightD');
        $gabsCheck   = array();

        $pvzController = new pvzController(true);
        $arList = (is_numeric($arData['city'])) ? $pvzController->getList() : $pvzController->getListFile();
        $arResult = array('city' => $arData['city'], 'mode' => $arData['mode'], 'POINTS' => array());

        if ($arList[$arData['mode']] && $arList[$arData['mode']][$arData['city']]) {
            $arResult['POINTS'] = $arList[$arData['mode']][$arData['city']];

            if (!empty($arResult['POINTS']) && is_array($arResult['POINTS'])) {
                if (!empty($arData['weight'])) {
                    $weightCheck = $arData['weight'];
                }

                if (\Ipolh\SDEK\option::get('mindVWeight') === 'Y' && !empty($arData['goods']) && is_array($arData['goods'])) {
                    $goods = \Ipolh\SDEK\Bitrix\Tools::encodeFromUTF8($arData['goods']);
                    \CDeliverySDEK::setGoods($goods);

                    $weightCheck = max($weightCheck, \sdekHelper::getVolumeWeight(\CDeliverySDEK::$goods['D_L'] * 10,
                            \CDeliverySDEK::$goods['D_W'] * 10, \CDeliverySDEK::$goods['D_H'] * 10) * 1000);

                    $gabsCheck = array(\CDeliverySDEK::$goods['D_L'], \CDeliverySDEK::$goods['D_W'], \CDeliverySDEK::$goods['D_H']);
                    rsort($gabsCheck);
                }

                $weightCheck /= 1000; // To kg

                foreach ($arResult['POINTS'] as $code => $data) {
                    if (array_key_exists('WeightLim', $data) && ($data['WeightLim']['MIN'] > $weightCheck || $data['WeightLim']['MAX'] < $weightCheck)) {
                        unset($arResult['POINTS'][$code]);
                        continue;
                    }

                    if (!empty($gabsCheck) && array_key_exists('Dimensions', $data)) {
                        rsort($data['Dimensions']);
                        foreach ($gabsCheck as $key => $gab) {
                            if ($data['Dimensions'][$key] < $gab) {
                                unset($arResult['POINTS'][$code]);
                            }
                        }
                    }
                }
            }
        }

        echo json_encode($arResult);
    }

    /**
     * @param $arData - array of type 'city' -> cityName | sdekId, 'mode' - false/PVZ/POSTAMAT, point - pointId
     * @return void
     */
    public static function getDataViaPointId($arData)
    {
        $pvzController = new pvzController(true);
        $arList = (is_numeric($arData['city'])) ? $pvzController->getList() : $pvzController->getListFile();
        $arResult = array('city'=>false,'mode'=>false,'POINTS'=>array());
        $break = false;

        foreach ($arList as $mode => $arCities){
            if($arData['city'] && $arCities[$arData['city']]){
                foreach ($arCities[$arData['city']] as $pointId => $arPointData){
                    if($pointId === $arData['point']){
                        $arResult['city']   =$arData['city'];
                        $arResult['mode']   = $mode;
                        $arResult['POINTS'] = $arCities[$arData['city']];
                        $arResult['point']  = $arData['point'];
                        $break = true;
                        break;
                    }
                }
            } else {
                foreach ($arCities as $city => $arPoints){
                    foreach ($arPoints as $pointId => $arPointData){
                        if($pointId === $arData['point']){
                            $arResult['city']   = $city;
                            $arResult['mode']   = $mode;
                            $arResult['POINTS'] = $arPoints;
                            $arResult['point']  = $arData['point'];
                            $break = true;
                            break;
                        }
                    }
                    if($break) break;
                }
            }
            if($break) break;
        }

        echo json_encode($arResult);
    }

    // SERVICE

    public static function getSavingInput(){
        return self::$MODULE_ID.self::$savingInput;
    }

    public static function getPostField(){
        return self::$postField;
    }

    protected static function no_json($wat){
        return is_null(json_decode(\CDeliverySDEK::zajsonit($wat),true));
    }
}