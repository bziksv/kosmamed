<?php
/**
 * Обработчик взаимодействия метода доставки.
 * Метод интеграции выполнен с учётом рекомендаций документации изложенных в документации
 * к кастомным методам доставки: https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=5329
 * @author Vadim Lazev
 */


namespace Sale\Handlers\Delivery;

use BiaTech\Base\Log\Logger;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventManager;
use Bitrix\Main\EventResult;
use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Order;
use Bitrix\Sale\Result;
use Bitrix\Sale\ResultError;
use Bitrix\Sale\ResultWarning;
use Bitrix\Sale\Shipment;
use CModule;
use DellinShipping\Entity\Order\Order as DellinOrder;
use DellinShipping\Entity\Config as DellinConfig;
use DellinShipping\Entity\Order\Person;
use DellinShipping\Entity\Order\Product;
use DellinShipping\Kernel;
use Exception;
use Sale\Handlers\Delivery\Additional\Location;
use Bitrix\Sale\Delivery\ExtraServices\Table;
use Bitrix\Main\Loader;
use Sale\Handlers\Delivery\Dellin\AjaxService;
use Sale\Handlers\Delivery\Spsr\Cache;
use Sale\Handlers\Delivery\DellinBlockAdmin;
use CPHPCache;
use CBitrix24;



Loader::registerAutoLoadClasses(

    'dellindev.shipment',

    array(
        'Sale\Handlers\Delivery\DellinTracking' =>
            'classes/general/tracking.php',
        'Sale\Handlers\Delivery\Dellin\AjaxService' =>
            'classes/general/ajaxservice.php',
        'Sale\Handlers\Delivery\DellinBlockAdmin' =>
            'classes/general/dellinblockadmin.php',
        'DellinShipping\Entity\Packages' =>
            'lib/src/DellinShipping/Entity/Packages.php',
        //'Sale\Handlers\Delivery\Spsr\Cache' => 'handlers/delivery/spsr/cache.php',
    )
);


// \Bitrix\Main\Loader::includeModule('dellindev.shipment');
CModule::IncludeModule("sale");

class DellinHandler extends \Bitrix\Sale\Delivery\Services\Base
{


    protected static $isCalculatePriceImmediately = true;
//    protected static $whetherAdminExtraServicesShow = true;


    protected $trackingClass = '\Sale\Handlers\Delivery\DellinTracking';



    /**
     * @return string
     */
    public function getCode()
    {
        return 'dellin';
    }

    public function __construct(array $initParams)
    {
        parent::__construct($initParams);

        global $APPLICATION;

        $order_id = $_REQUEST['ID'];

        if (strpos($APPLICATION->GetCurPage(),'/bitrix/admin/sale_order_view.php') !== false){

            $data = DellinBlockAdmin::getAllDataForJS($order_id);
            DellinBlockAdmin::includeJSinAdminPage($this->getId(), $data);

        }
    }

    public static function getClassTitle()
    {
        return \Bitrix\Main\Localization\Loc::getMessage("METHOD_NAME");
    }

    public static function getClassDescription()
    {
        return Loc::getMessage("DELLINDEV_DESCRIPTION_METHOD");
    }

    public function isCalculatePriceImmediately()
    {
        return self::$isCalculatePriceImmediately;
    }

    public static function whetherAdminExtraServicesShow()
    {
        return self::$whetherAdminExtraServicesShow;
    }


    protected function getConfigStructure()
    {
        $result = array(
            'MAIN' => array(
                'TITLE' => Loc::getMessage("DELLINDEV_AUTH_SETTINGS"),
                'DESCRIPTION' => Loc::getMessage("DELLINDEV_AUTH_SETTINGS"),
                'ITEMS' => array(
                    'APIKEY' => array(
                        'TYPE' => 'STRING',
                        'NAME' => Loc::getMessage("DELLINDEV_APIKEY")
                    ),
                    'LOGIN' => array(
                        'TYPE' => 'STRING',
                        'NAME' => Loc::getMessage("DELLINDEV_LOGIN")
                    ),
                    'PASSWORD' => array(
                        'TYPE' => 'STRING',
                        'NAME' => Loc::getMessage("DELLINDEV_PASSWORD")
                    ),
                )
            ),
            'DEBUG' => array(
                'TITLE' => Loc::getMessage("DELLINDEV_SETTINGS_LOGGER"),
                    'DESCRIPTION' => Loc::getMessage("DELLINDEV_SETTINGS_LOGGER_ONLY_EXPERT"),
                    'ITEMS' => array(
                        "DEBUG_MODE" => array(
                            'TYPE' => 'Y/N',
                            "NAME" => Loc::getMessage("DELLINDEV_DEBUG_MODE_ON"),
                            "DEFAULT" => "N"
                        ),
                    )
                ),
            'PACKAGES' => array(
                'TITLE' => Loc::getMessage("DELLINDEV_ADDIDITIONAL_SERVICE"),
                'DESCRIPTION' => Loc::getMessage("DELLINDEV_ADDIDITIONAL_SERVICE_PACKING"),
                'ITEMS' => array(
                    "crate" => array(
                        'TYPE' => 'Y/N',
                        "NAME" => Loc::getMessage("DELLINDEV_CRATE"),
                        "DEFAULT" => "N"
                    ),
                    "cratePlus" => array(
                        'TYPE' => 'Y/N',
                        "NAME" => Loc::getMessage("DELLINDEV_CRATE_PLUS"),
                        "DEFAULT" => "N"
                    ),
                    "box" => array(
                        'TYPE' => 'Y/N',
                        "NAME" => Loc::getMessage("DELLINDEV_BOX"),
                        "DEFAULT" => "N"
                    ),
                    "type" => array(
                        'TYPE' => 'Y/N',
                        "NAME" => Loc::getMessage("DELLINDEV_TYPE"),
                        "DEFAULT" => "N"
                    ),
                    "crateWithBubble" => array(
                        'TYPE' => 'Y/N',
                        "NAME" => Loc::getMessage("DELLINDEV_CRATE_WITH_BUBBLE"),
                        "DEFAULT" => "N"
                    ),
                    "carGlass" => array(
                        'TYPE' => 'Y/N',
                        "NAME" => Loc::getMessage("DELLINDEV_CAR_GLASS"),
                        "DEFAULT" => "N"
                    ),
                    "carParts" => array(
                        'TYPE' => 'Y/N',
                        "NAME" => Loc::getMessage("DELLINDEV_CAR_PARTS"),
                        "DEFAULT" => "N"
                    ),
                    "palletWithBubble" => array(
                        'TYPE' => 'Y/N',
                        "NAME" => Loc::getMessage("DELLINDEV_PALLET_WITH_BUBBLE"),
                        "DEFAULT" => "N"
                    ),
                    "bag" => array(
                        'TYPE' => 'Y/N',
                        "NAME" => Loc::getMessage("DELLINDEV_BAG"),
                        "DEFAULT" => "N"
                    ),
                    "bubble" => array(
                        'TYPE' => 'Y/N',
                        "NAME" => Loc::getMessage("DELLINDEV_BUBBLE"),
                        "DEFAULT" => "N"
                    ),
                    "pallet" => array(
                        'TYPE' => 'Y/N',
                        "NAME" => Loc::getMessage("DELLINDEV_PALLET"),
                        "DEFAULT" => "N"
                    )
                )
            ),

        );
        return $result;
    }

    protected function builderConfigForLib($shipment){

        if(CModule::IncludeModule('dellindev.shipment')){

            $deliveryID = $shipment->getDeliveryId();

            return DellinBlockAdmin::buildConfig($deliveryID);

        } else {

            throw new Exception(Loc::getMessage("DELLINDEV_EXCEPTION_MODULE_NOT_INSTALL"));

        }
    }



    protected function builderOrderForLib(\Bitrix\Sale\Shipment $shipment){



        if (CModule::IncludeModule("dellindev.shipment"))
        {
            //�������������� ������ ������
            $dellinOrder = new DellinOrder();


            /** @var Order $order */
            $order = $shipment->getCollection()->getOrder();

            if(!$props = $order->getPropertyCollection())
                return '';

            if(!$locationProp = $props->getDeliveryLocation())
                return '';

            if(!$locationCode = $locationProp->getValue())
                return '';

            if(!$locationData = \CSaleLocation::GetByID($locationCode))
                return '';

            $mapProps = [
                'personPhone'  => 'PHONE',
                'zip' => 'ZIP',
                'personAddress' => 'ADDRESS',
                'email' => 'EMAIL',
                'terminal_id' => 'TERMINAL_ID'
            ];

            foreach( $mapProps as $varName => $propName){
                $obj = $props->getItemByOrderPropertyCode($propName);
                // Fixed to php8.1
                if(is_null($obj)){
                   ${$varName} = '';
                } elseif (method_exists($obj, 'getValue')){
                   ${$varName} = $obj->getValue();
                } else {
                   ${$varName} = '';
                }
            }
            
            $personType = $order->getPersonTypeId();
            $country = $locationData['COUNTRY_NAME_ORIG'];
            $regionName = $locationData['REGION_NAME_ORIG'];
            $cityName = $locationData['CITY_NAME_ORIG'];

            $price = $order->getPrice();

            $shipmentCost = $order->getDeliveryPrice();
            $shipmentName = $shipment->getDeliveryName();
            $personName = $props->getPayerName()->getValue();
//            $personPhone = $props->getItemByOrderPropertyCode('PHONE')->getValue();
//            $zip = $props->getItemByOrderPropertyCode('ZIP')->getValue();
//            $personAddress = $props->getItemByOrderPropertyCode('ADDRESS')->getValue();
//            $email = $props->getItemByOrderPropertyCode('EMAIL')->getValue();
//            //�� ����� ������������ ������� ����� ������ ���������� ������, ������� ��������� ���������.
////            $deliveryTimeStart = $props->getItemByOrderPropertyCode('DELLIN_DELIVERYTIME_START')->getValue();
////            $deliveryTimeEnd = $props->getItemByOrderPropertyCode('DELLIN_DELIVERYTIME_END')->getValue();
//
            $deliveryTimeStart = '09:00';
            $deliveryTimeEnd = '18:00';
//            $terminal_id = $props->getItemByOrderPropertyCode('TERMINAL_ID')->getValue();


            $person = new Person($personType, $personName, $email, $personPhone,
                                $country, $regionName, $cityName, $personAddress, $zip, $terminal_id);


            $dellinOrder->setOrderData($order->getId(), null,
                                       $shipment->getDeliveryId(),
                                       null, false, $price, $shipmentCost,
                                       $shipmentName, date("Y-m-d"), $person, $deliveryTimeStart, $deliveryTimeEnd, false);



            /** @var \Bitrix\Sale\ShipmentItem $item */
            foreach($shipment->getShipmentItemCollection() as $item)
            {
                $basketItem = $item->getBasketItem();


                if(!$basketItem)
                    continue;

                if($basketItem->isBundleChild())
                    continue;


                $productId = floatval($basketItem->getProductId());
                $productName = $basketItem->getField('NAME');

                $itemWeight = floatval($basketItem->getWeight());
                $quantityItem = floatval($basketItem->getField('QUANTITY'));




                $dimensions = $basketItem->getField('DIMENSIONS');

                if(!is_array($dimensions) && $dimensions <> '')
                    $dimensions = unserialize($dimensions);

                if(!empty($dimensions['WIDTH']) && !empty($dimensions['HEIGHT']) && !empty($dimensions['LENGTH']))
                {
                    $width = floatval($dimensions['WIDTH']);
                    $height = floatval($dimensions['HEIGHT']);
                    $length = floatval($dimensions['LENGTH']);
                }



                $price = $basketItem->getPrice();

                $isVATincluded = ($basketItem->getField('VAT_INCLUDED') == 'Y');
                $VATRate = $basketItem->getField('VAT_RATE');


                $product = new Product($productId, $productName, $quantityItem, 'G', $itemWeight,
                         'MM', $length, $width, $height, $price, $isVATincluded, $VATRate);
                $dellinOrder->addProduct($product);

            }

            return $dellinOrder;

        } else {

            throw new Exception(Loc::getMessage("DELLINDEV_EXCEPTION_MODULE_NOT_INSTALL"));
        }
    }

    /**
     * @return string
     */
    public function getLogotipPath()
    {
        $logo = $this->getLogotip();

        if(empty($logo)){
            return '/bitrix/images/dellindev.shipment/dl-logo.png';
        }

        return intval($logo) > 0 ? \CFile::GetPath($logo) : "";
    }

    protected function calculateConcrete(\Bitrix\Sale\Shipment $shipment = null)
    {
        
        
        if(CModule::IncludeModule("dellindev.shipment")) { 

            try {

                $order =  $this->builderOrderForLib($shipment);
                $config = $this->builderConfigForLib($shipment);



                $logger = new Logger($config);

                if(!is_object($order)){
                    throw new Exception(Loc::getMessage("DELLINDEV_ORDER_IS_NULL"));
                }


                $cache = new CPHPCache();
                $life_time = 10*60;

                $cacheID = json_encode($order->person, JSON_UNESCAPED_UNICODE).'&
                '.json_encode($order->products, JSON_UNESCAPED_UNICODE).'&'.json_encode($config, JSON_UNESCAPED_UNICODE);

                $cacheDir = 'dellin_calcs';


                if($cache->InitCache($life_time, $cacheID, $cacheDir) && isset($cache->GetVars()['VALUE'])){

                    $cache_data = $cache->GetVars();
                    $data = $cache_data['VALUE'];
                    $_SESSION['current_terminals'] = $data['terminals'];

                } else {


                    $kernel = new Kernel($config, $order, $logger, false);
                    $data = $kernel->CalculateShippingCostAndPeriod();
                    $_SESSION['current_terminals'] = $data['terminals'];
                    $cache->StartDataCache($life_time, $cacheID, $cacheDir);
                    $cache->EndDataCache(array('VALUE' => $data));

                }



                $result = new \Bitrix\Sale\Delivery\CalculationResult();

                if($data['status'] == 'error'){
                    $error = new Error($data['message']);
                    $result->addError($error);

                }

                $result->setAsNextStep();

                $result->setDeliveryPrice(
                    roundEx(
                        (int)$data['price'],
                        SALE_VALUE_PRECISION
                    )
                );
                
                

                $result->setPeriodDescription($data['days'].' '. self::plural( (int)$data['days'],
                                                                                Loc::getMessage("DELLINDEV_1_DAY"),
                                                                                Loc::getMessage("DELLINDEV_3_DAYS"),
                                                                                Loc::getMessage("DELLINDEV_DAYS")));
//                ob_get_contents();
//                ob_end_clean();

                return $result;

            } catch ( Exception $exception){
                
                $this->clearTerminals($shipment);
                $result = new \Bitrix\Sale\Delivery\CalculationResult();
                $error = new Error($exception->getMessage(), false, '0001');
                $result->addError($error);

                return $result;

            }

        } else {
            throw new Exception(Loc::getMessage("DELLINDEV_EXCEPTION_MODULE_NOT_INSTALL"));
        }

    }

    private function clearTerminals($shipment)
    {


        if(CModule::IncludeModule('dellindev.shipment')){
            $config = $this->builderConfigForLib($shipment);
            
            if(is_null($config)){
                return false;
            }

            if($config->isGoodsUnloading())
            {
                unset($_SESSION['current_terminals']);
            }

        }
    }

    public function calculate(\Bitrix\Sale\Shipment $shipment = null, $extraServices = array())
    {
        return $this->calculateConcrete($shipment);
    }


    public function getAdminAdditionalTabs()
    {
        global $APPLICATION;

        ob_start();

        $APPLICATION->IncludeComponent(
            "dellindev:dellindev.shipment.derivalsettings",
            "",
            array(
                "AJAX_SERVICE_CLASS" => '\Sale\Handlers\Delivery\Dellin\AjaxService'
            ),
            false
        );
        $tabderival = ob_get_contents();

        ob_end_clean();

        ob_start();
        $APPLICATION->IncludeComponent(
            "dellindev:dellindev.shipment.cargosettings",
            "",
            array(
                "AJAX_SERVICE_CLASS" => '\Sale\Handlers\Delivery\Dellin\AjaxService'
            ),
            false
        );
        $tabcargo = ob_get_contents();

        ob_end_clean();

        ob_start();
        $APPLICATION->IncludeComponent(
            "dellindev:dellindev.shipment.yurisettings",
            "",
            array(
                "AJAX_SERVICE_CLASS" => '\Sale\Handlers\Delivery\Dellin\AjaxService'
            ),
            false
        );

        $tabyurisettings = ob_get_contents();

        ob_end_clean();

        ob_start();
        $APPLICATION->IncludeComponent(
            "dellindev:dellindev.shipment.widgetsettings",
            "",
            [],
            false
        );

        $tabwidgetsettings = ob_get_contents();

        ob_end_clean();


        return array(
            array(
                "TAB" => Loc::getMessage("DELLINDEV_SETTINGS_DERIVAL"),
                "TITLE" => '',
                "CONTENT" => $tabderival
            ),
            array(
                'TAB' => Loc::getMessage("DELLINDEV_SENDER_INFO"),
                'TITLE' => '',
                'CONTENT' => $tabyurisettings
            ),
            array(
                'TAB' => Loc::getMessage("DELLINDEV_CARGO_INFO"),
                'TITLE' => '',
                'CONTENT'=> $tabcargo
            ),
            array(
                'TAB' => GetMessage("DELLINDEV_SHIPMENT_WIDGET_SETTINGS"),
                'TITLE' => '',
                'CONTENT'=> $tabwidgetsettings
            )
        );
    }



    public static function isHandlerCompatible()
    {
        if(!parent::isHandlerCompatible())
        {
            return false;
        }

        $lang = '';

        if(Loader::includeModule('bitrix24'))
        {
            $lang = \CBitrix24::getLicensePrefix();
        }
        elseif (Loader::includeModule('intranet'))
        {
            $lang = \CIntranetUtils::getPortalZone();
        }

        return $lang !== 'ua';
    }



    // public static function install()
    // {
    //     $eventManager = EventManager::getInstance();
    //     $eventManager->registerEventHandler(
    //         'sale',
    //         'OnSaleComponentOrderResultPrepared' ,
    //         'sale', '\Sale\Handlers\Delivery\DellinHandler',
    //         'OnSaleComponentOrderResultPrepared');
//        $eventManager->registerEventHandler(
//            'sale',
//            'onSaleDeliveryTrackingClassNamesBuildList',
//            'sale',
//            '\Sale\Handlers\Delivery\SpsrHandler',
//            'onSaleDeliveryTrackingClassNamesBuildList'
//        );

 //       Location::install();
    // }

    // public static function unInstall()
    // {
    //     $eventManager = EventManager::getInstance();
    //     $eventManager->unRegisterEventHandler(
    //         'sale',
    //         'OnSaleComponentOrderResultPrepared' ,
    //         'sale', '\Sale\Handlers\Delivery\DellinHandler',
    //         'OnSaleComponentOrderResultPrepared');

//        $eventManager->unRegisterEventHandler(
//            'sale',
//            'onSaleDeliveryTrackingClassNamesBuildList',
//            'sale',
//            '\Sale\Handlers\Delivery\SpsrHandler',
//            'onSaleDeliveryTrackingClassNamesBuildList'
//        );
//
    // }

//    public static function onSaleDeliveryTrackingClassNamesBuildList()
//    {
//        if(CModule::IncludeModule('dellindev.shipment')) {
//            return new \Bitrix\Main\EventResult(
//                \Bitrix\Main\EventResult::SUCCESS,
//                array(
//                    '\Sale\Handlers\Delivery\DellinTracking' => '/bitrix/modules/dellindev.shipment/classes/general/tracking.php'
//                ),
//                'sale'
//            );
//        } else {
//
//        }
//    }

    protected static function getTerminalList($session_storage)
    {
        // if (!\Bitrix\Main\Loader::includeModule('dellindev.shipment'))
        //     return array();
        $result = [];
        if(isset($session_storage) && !empty($session_storage)){
            foreach ($session_storage as $terminal){
                $result[$terminal->id] =  $terminal->address;
                //  $result = array_push($result, $arr) ;
            }
        }
        //var_dump($result);
        return $result;
    }

    public static function OnSaleComponentOrderShowAjaxAnswer(&$result){


        $requestToMethod = AjaxService::getTerminalsForAjaxOfSession();
        $result['dellin'] = $requestToMethod;


    }

    public static function OnSaleComponentOrderResultPrepared($order, &$arUserResult, $request, &$arParams, &$arResult )
    {

       $activeMethods = \Bitrix\Sale\Delivery\Services\Manager::getActive();


         if(CModule::IncludeModule('dellindev.shipment')) {
            $stateRender = false;

            foreach ($activeMethods as $idMethod => $value){

                if($stateRender){
                    break;
                }

                if(DellinBlockAdmin::isDellinMethod($idMethod)){
                    $stateRender = true;
                }
            }

            if($stateRender){

                global $APPLICATION;

                $APPLICATION->IncludeComponent(
                    "dellindev:dellindev.shipment.choose",
                    ".default",
                    Array(
                        "USER_RESULT" => $arUserResult,
                        "TERMINAL_LIST" => self::getTerminalList($_SESSION['current_terminals'])
                    ));

            }

         }
    }

    public static function onSaleDeliveryTrackingClassNamesBuildList()
    {
        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            array(
                '\Sale\Handlers\Delivery\DellinTracking' => '/bitrix/modules/dellindev.shipment/classes/general/tracking.php'
            ),
            'sale'
        );
    }

    static function plural($n, $form1, $form2, $form3) {
        return in_array($n % 10, array(2,3,4)) && !in_array($n % 100, array(11,12,13,14)) ? $form2 : ($n % 10 == 1 ? $form1 : $form3);
    }


}