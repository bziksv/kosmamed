<?php

/**
 * Слой Tools приводящий коллекции магазина битрикс к общему знаменателю к методам ядра SDK.
 * Согласно правилам первой итерации методы не документируются, т.к. требуется подтверждения гипотезы взаимодействия
 * реальных пользователей.
 * В дальнейшем при развитии проекта SDK, возможно приведения в комментариях конкретных кейсов использования.
 * @author: Vadim Lazev
 * @company: BIA-Tech
 * @year: 2021
 */




namespace Sale\Handlers\Delivery;

use Bitrix\Sale;
use Bitrix\Main\Loader;
use Bitrix\Main\Page\Asset;
use Bitrix\Sale\Order;
//use Bitrix\Sale\Shipment;
use Bitrix\Sale\ShipmentItemCollection;
use CJSCore;
use CSaleBasket;
use CSalePersonType;
use DellinShipping\Entity\Config;
use DellinShipping\Entity\Config as DellinConfig;
use DellinShipping\Entity\Order\Order as DellinOrder;
use DellinShipping\Entity\Order\Person;
use DellinShipping\Entity\Order\Product;
use DellinShipping\NetworkService;
use Bitrix\Main\Localization\Loc;

\Bitrix\Main\Loader::includeModule('sale');
\Bitrix\Main\Loader::includeModule('dellindev.shipment');

class DellinBlockAdmin
{


    public static function getBasketShipmentsByOrderId($orderId){

        $arShipment = [];

        if($orderId <= 0)
            return null;

        $registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

        $orderClass = $registry->getOrderClassName();

        $order = $orderClass::load($orderId);

        if(!$order)
            return null;


        if(!$props = $order->getPropertyCollection())
            return '';

        $sCollect = $order->getShipmentCollection();

        if(!$sCollect)
            return null;



        foreach($sCollect as $shipment){

           $shipmentId = $shipment->getId();

            if($shipment->isSystem())
            continue;



            if(!self::isDellinMethod($shipment->getField('DELIVERY_ID')))
                continue;
            $method_id = $shipment->getField('DELIVERY_ID');
            $trackingNumber = $shipment->getField('TRACKING_NUMBER');
            if(isset($trackingNumber)){
                $appkey = \Bitrix\Sale\Delivery\Services\Manager::getById($method_id)['CONFIG']['MAIN']['APIKEY'];
                $statusData = NetworkService::getShipmentStatus($appkey, $trackingNumber);
            }

            $arShipment[$shipmentId] = [
                'ID' => $shipmentId,
                'ACCOUNT_NUMBER' => $shipment->getField('ACCOUNT_NUMBER'),
                'ORDER_ID' => $shipment->getField('ORDER_ID'),
                'DELIVERY_ID' => $shipment->getField('DELIVERY_ID'),
                'TRACKING_NUMBER' => $trackingNumber,
                'DEFAULT_DATE' => self::getDefaultDate($method_id),
                'PRICE_DELIVERY' => (float)$shipment->getField('PRICE_DELIVERY'),
                'IS_TERMINAL_ARRIVAL' => self::isTerminalArrival($method_id),
                'IS_TERMINAL_DERIVAL' => self::isTeminalDerival($method_id),
                'ADDRESS_DERIVAL' => (self::isTeminalDerival($method_id))?
                                    self::getTerminalInfo(self::getTerminalDerival($method_id),
                                        $method_id)->fullAddress :
                                    self::getAddressDerival($method_id),
                'ADDRESS_ARIVAL' => (self::isTerminalArrival($method_id))?
                                    self::getTerminalInfo($props->getItemByOrderPropertyCode('TERMINAL_ID')->getValue(),
                                        $method_id)->fullAddress :
                                    self::getAddressArival($orderId),
                'STATUS_TRACKING' => isset($statusData->state_name)?$statusData->state_name : null,
                'DATE_ARRIVAL' => isset($statusData->order_dates->arrival_to_osp_receiver)?
                    $statusData->order_dates->arrival_to_osp_receiver : null,
                'BASKET' => []
            ];

            $shipmentItemCollection = $shipment->getShipmentItemCollection();

            foreach($shipmentItemCollection as $item){

                $basketItem = $item->getBasketItem();

                if(!$basketItem->canBuy() || $basketItem->isDelay())
                    continue;

                if($basketItem->isBundleChild())
                    continue;

                $dimensions = $basketItem->getField('DIMENSIONS');

                if(!is_array($dimensions) && $dimensions <> '')
                    $dimensions = unserialize($dimensions);

                if(!empty($dimensions['WIDTH']) && !empty($dimensions['HEIGHT']) && !empty($dimensions['LENGTH']))
                {
                    $width = floatval($dimensions['WIDTH']);
                    $height = floatval($dimensions['HEIGHT']);
                    $length = floatval($dimensions['LENGTH']);
                }

                $product = ['productId' => $basketItem->getProductId(),
                    'productName' => $basketItem->getField('NAME'),
                    'quantity' => $item->getQuantity(),
                    'unitWeight' => 'G',
                    'weight' => $basketItem->getWeight(),
                    'unitDemensions' => Loc::getMessage("DELLINDEV_SHIPMENT_MM"),
                    'lenght' => $length,
                    'width' => $width,
                    'height' => $height,
                    'price' => $basketItem->getPrice()
                ];

                $arShipment[$shipmentId]['BASKET'][$product['productId']] = $product;

            }

        }

        return $arShipment;

    }

    public static function getAddressArival($order_id){

        $personInfo = self::getPersonInfo($order_id);

        return $personInfo['country'].', '.$personInfo['regionName'].', '.
            $personInfo['cityName'].', '.$personInfo['personAddress'];

    }

    public static function getAddressDerival($method_id)
    {

        return \Bitrix\Sale\Delivery\Services\Manager::getById($method_id)['CONFIG']['DERIVAL']['ADDRESS'];

    }

    public static function getTerminalDerival($method_id)
    {

        return \Bitrix\Sale\Delivery\Services\Manager::getById($method_id)['CONFIG']['DERIVAL']['TERMINAL_ID'];

    }

    public static function getTerminalInfo($terminal_id, $method_id){


        $configParams = \Bitrix\Sale\Delivery\Services\Manager::getById($method_id);
        $apikey = $configParams['CONFIG']['MAIN']['APIKEY'];

        $terminalInfo = NetworkService::GetTerminals($apikey, true, $terminal_id);

        return $terminalInfo['terminal'];

    }




    public static function isTerminalArrival($method_id){

        $configParams = \Bitrix\Sale\Delivery\Services\Manager::getById($method_id);

        return ($configParams['CONFIG']['ARRIVAL']['GOODSLOADING'] == '0');

    }

    public static function isTeminalDerival($method_id){

        $configParams = \Bitrix\Sale\Delivery\Services\Manager::getById($method_id);

        return ($configParams['CONFIG']['DERIVAL']['GOODSLOADING'] == '0');

    }

    private static function getDefaultDate($method_id){

        $configParams = \Bitrix\Sale\Delivery\Services\Manager::getById($method_id);
        $deliveryDelay = (int)$configParams['CONFIG']['CARGO']['DAYDELAY'];
        $produceDate = date("d.m.Y",
            mktime(0, 0, 0, date("m"),
                date("d") + $deliveryDelay, date("Y")));

        return $produceDate;
    }

   public static function isDellinMethod($method_id){


       return \Bitrix\Sale\Delivery\Services\Manager::getById($method_id)['CLASS_NAME'] ==
                                                                                '\Sale\Handlers\Delivery\DellinHandler';

   }
   
    public static function getShipmentById($orderId, $shipmentId){

        if($orderId <= 0)
            return null;

        $registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

        $orderClass = $registry->getOrderClassName();

        $order = $orderClass::load($orderId);

        if(!$order)
            return null;

        $sCollect = $order->getShipmentCollection();

        if(!$sCollect)
            return null;


        return $sCollect->getItemById($shipmentId);

    }

    public static function setFieldInShipment($orderID, $shipmentID, $fieldName, $fieldValue){

        if($orderID <= 0)
            return null;

        $registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

        $orderClass = $registry->getOrderClassName();

        $order = $orderClass::load($orderID);

//        $order = \Bitrix\Sale\Order::load($orderID);

        if(!$order)
            return null;

        $sCollect = $order->getShipmentCollection();

        if(!$sCollect)
            return null;


       $shipment = $sCollect->getItemById($shipmentID);

       $result = $shipment->setField($fieldName, $fieldValue);

       $order->save();

       return $result->isSuccess();
    }

    public static function getPaymentsData($order_id)
    {

      //TODO нам неизвестны кейсы использования платёжных систем, поэтому мы не будем работать с оплатой как с коллекцией.
      // Требуется глубокий разбор применения. Берём первый идентификатор оплаты. Если метод оплаты в заказе один,
      // то данные будут корректны, если методов оплаты более одного, требуется доработка с определением возможных кейсов
      // бизнес процессов.
      // Для нашего кейса определения наложного платежа при одном идентификаторе - поведение будет корректное.
      // Субъективно: вообще не вижу перспектив у коллекции при определении наложного платежа.



        if($order_id <= 0)
            return null;

        $registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

        $orderClass = $registry->getOrderClassName();

        $order = $orderClass::load($order_id);


        $collection = $order->getPaymentCollection();

        foreach ($collection as $payment)
        {
            return  $payment->getField('PAY_SYSTEM_ID');
        }

       throw new \Exception(Loc::getMessage("DELLINDEV_COLLECTION_IS_UNDEFINED"));
    }


    public static function isCashOnDelivery($payment_id){

        $method_info = \Bitrix\Sale\PaySystem\Manager::getById($payment_id);

        return ($method_info['ACTION_FILE'] == 'cashondelivery');

    }



    public static function buildOrderDellinByShipmentId($orderId, $shipmentId){

        $dellinOrder = new DellinOrder();

        $personInfo = self::getPersonInfo($orderId);

        $person = new Person($personInfo['personType'], $personInfo['personName'], $personInfo['personEmail'],
            $personInfo["personPhone"],  $personInfo['country'], $personInfo['regionName'],
            $personInfo['cityName'], $personInfo['personAddress'], $personInfo['zip'], $personInfo['terminal_id']);

        $dellinOrder->setPerson($person);


        $collection = self::getShipmentById($orderId, $shipmentId);

        $method_id = $collection->getField('DELIVERY_ID');
        $payment_id = self::getPaymentsData($orderId);
        $isCash = self::isCashOnDelivery($payment_id);
        $priceOrder = $collection->getOrder()->getPrice();
        $priceDelivery = $collection->getPrice();
        $deliveryName = $collection->getDeliveryName();
        $createOrder = $collection->getOrder()->getDateInsert()->toString();



        $personInfo = self::getPersonInfo($orderId);


       $dellinOrder->setOrderData( $orderId, $shipmentId, $method_id, $payment_id, $isCash, $priceOrder, $priceDelivery, $deliveryName,
                                   $createOrder, $person, $personInfo['deliverytimestart'], $personInfo['deliverytimeend'], false );


        foreach($collection->getShipmentItemCollection() as $item){

            $basketItem = $item->getBasketItem();           
            if(!$basketItem->canBuy() || $basketItem->isDelay())
                continue;

            if($basketItem->isBundleChild())
                continue;

            $dimensions = $basketItem->getField('DIMENSIONS');

            if(!is_array($dimensions) && $dimensions <> '')
                $dimensions = unserialize($dimensions);

            if(!empty($dimensions['WIDTH']) && !empty($dimensions['HEIGHT']) && !empty($dimensions['LENGTH']))
            {
                $width = floatval($dimensions['WIDTH']);
                $height = floatval($dimensions['HEIGHT']);
                $length = floatval($dimensions['LENGTH']);
            }

            $isVATincluded = ($basketItem->getField('VAT_INCLUDED') == 'Y');
            $VATRate = $basketItem->getField('VAT_RATE');

            $product = [
                        'productId' => $basketItem->getProductId(),
                        'productName' => $basketItem->getField('NAME'),
                        'quantity' => $item->getQuantity(),
                        'unitWeight' => 'G',
                        'weight' => $basketItem->getWeight(),
                        'unitDemensions' => Loc::getMessage("DELLINDEV_SHIPMENT_MM"),
                        'lenght' => $length,
                        'width' => $width,
                        'height' => $height,
                        'price' => $basketItem->getPrice(),
            ];

//            var_dump($product);
//            die();


            $productDellin = new Product($product['productId'], $product['productName'], $product['quantity'],
                                         $product['unitWeight'], $product['weight'], $product['unitDemensions'],
                                         $product['lenght'], $product['width'], $product['height'], $product['price'],
                                        $isVATincluded, $VATRate);


            $dellinOrder->addProduct($productDellin);

        }


        return $dellinOrder;

    }



    public static function buildConfig($method_id){


        if(self::isDellinMethod($method_id)){


            $configParams = \Bitrix\Sale\Delivery\Services\Manager::getById($method_id);

//            echo '<pre>';
//            var_dump($configParams);
//            echo '</pre>';
//            die();

            if(!isset($configParams['CONFIG']['MAIN'])){
                throw new \Exception(Loc::getMessage("DELLINDEV_AUTH_IS_UNDEFINED"));
            }

            if(!isset($configParams['CONFIG']['CARGO'])){
                throw new \Exception(Loc::getMessage("DELLINDEV_CARGO_IS_UNDEFINED"));
            }


            if(!isset($configParams['CONFIG']['DERIVAL'])){
                throw new \Exception(Loc::getMessage("DELLINDEV_SENDER_IS_UNDEFINED"));
            }

            if(!isset($configParams['CONFIG']['YURI'])){
                throw new \Exception(Loc::getMessage("DELLINDEV_YURI_IS_UNDEFINED"));
            }

            if(!isset($configParams['CONFIG']['PERSON'])){
                throw new \Exception(Loc::getMessage("DELLINDEV_CONTACT_PERSON_IS_UNDEFINED"));
            }

            if(!isset($configParams['CONFIG']['ARRIVAL'])){
                throw new \Exception(Loc::getMessage("DELLINDEV_RECEIVER_IS_UNDEFINED"));
            }

//            if(isset($configParams['CONFIG']['PRODUCT'])){
//                throw new \Exception('����������� ��������� ��-��������� ��� �� �������������� �����.');
//            }

            //auth
            $appkey = $configParams['CONFIG']['MAIN']['APIKEY'];
            $login = $configParams['CONFIG']['MAIN']['LOGIN'];
            $password = $configParams['CONFIG']['MAIN']['PASSWORD'];
            $counterAgent = $configParams['CONFIG']['YURI']['COUNTERAGENT'];

            //sender data
            $daydelay = $configParams['CONFIG']['CARGO']['DAYDELAY'];
            $derivalKLADR = $configParams['CONFIG']['DERIVAL']['CITY_KLADR'];
            $opfCountry = $configParams['CONFIG']['YURI']['OPF_COUNTRY'];
            $opf = $configParams['CONFIG']['YURI']['OPF'];
            $inn = $configParams['CONFIG']['YURI']['INN'];
            $name = $configParams['CONFIG']['YURI']['NAME'];
            $yuriAddress = $configParams['CONFIG']['YURI']['ADDRESS'];
            $personName = $configParams['CONFIG']['PERSON']['NAME'];
            $personPhone = $configParams['CONFIG']['PERSON']['PHONE'];
            $personEmail = $configParams['CONFIG']['PERSON']['EMAIL'];

            //preset Settings for Request
            $isSmall = ($configParams['CONFIG']['ARRIVAL']['IS_SMALL_GOODS'] == '0');
            $isInsurance = ($configParams['CONFIG']['ARRIVAL']['IS_INSURANCE_GOODS_WITH_DECLARE_PRICE'] == '0');
            $workStart = $configParams['CONFIG']['DERIVAL']['WORKTIMESTART'];
            $breakStart = $configParams['CONFIG']['DERIVAL']['BREAKSTART'];
            $breakEnd =  $configParams['CONFIG']['DERIVAL']['BREAKEND'];
            $workEnd = $configParams['CONFIG']['DERIVAL']['WORKTIMEEND'];
            $loadingGroupingOfGoods = $configParams['CONFIG']['CARGO']['LOADING_GROUPING_OF_GOODS'];

            //loading data
            $isTerminalLoadingOrAddress = ($configParams['CONFIG']['DERIVAL']['GOODSLOADING'] == '1');
//            $loadingType = $configParams['CONFIG']['DERIVAL']['LOADING_TYPE'];
//            $loadingTranspostReq = $configParams['CONFIG']['DERIVAL']['LOADING_TRANSPORT_REQUIREMENTS'];
//            $loadingAdditional = $configParams['CONFIG']['DERIVAL']['LOADING_ADDITIONAL_EQUIPMENT'];
            $requirementsLoading = json_decode($configParams['CONFIG']['DERIVAL']['REQUIREMENTSTRANSPORT']);
            $terminal_id = $configParams['CONFIG']['DERIVAL']['TERMINAL_ID'];
            $loadingAddress = $configParams['CONFIG']['DERIVAL']['ADDRESS'];

            //unloadingData
//            $unloadingType = $configParams['CONFIG']['ARRIVAL']['UNLOADING_TYPE'];
//            $unloadingTransportReq = $configParams['CONFIG']['ARRIVAL']['UNLOADING_TRANSPORT_REQUIREMENTS'];
//            $unloadingAdditional = $configParams['CONFIG']['ARRIVAL']['UNLOADING_ADDITIONAL_EQUIPMENT'];
            $isTerminalUnloadingOrAddress = ($configParams['CONFIG']['ARRIVAL']['GOODSLOADING'] == '1');

            //������ ��� �������� �������.
            $isDefaultDemensions = ($configParams['CONFIG']['CARGO']['DEFAULT_PARAMS'] == '0');
            $defaultLength = (empty($configParams['CONFIG']['PRODUCT']['LENGTH']))?
                               0 : $configParams['CONFIG']['PRODUCT']['LENGTH'];
            $defaultWidth = (empty($configParams['CONFIG']['PRODUCT']['WIDTH']))?
                               0 : $configParams['CONFIG']['PRODUCT']['WIDTH'];
            $defaultHeight = (empty($configParams['CONFIG']['PRODUCT']['HEIGHT']))?
                               0 : $configParams['CONFIG']['PRODUCT']['HEIGHT'];
            $defaultWeight = (empty($configParams['CONFIG']['PRODUCT']['WEIGHT']))?
                               0 : $configParams['CONFIG']['PRODUCT']['WEIGHT'];

            //������ �������� ����� ��������� ������� � SDK

            $isDebug = ($configParams['CONFIG']['DEBUG']['DEBUG_MODE'] == 'Y');
            $isWarning = ($configParams['CONFIG']['DEBUG']['WARNING_MODE'] == 'Y');
        //    var_dump($configParams['CONFIG']);
        //    var_dump($configParams['CONFIG']['DEBUG']['WARNING_MODE']);
        //    die();

            $isCrate = ($configParams['CONFIG']['PACKAGES']['crate'] == 'Y');
            $isCratePlus = ($configParams['CONFIG']['PACKAGES']['cratePlus'] == 'Y');
            $isBox = ($configParams['CONFIG']['PACKAGES']['box'] == 'Y');
            $isType = ($configParams['CONFIG']['PACKAGES']['type'] == 'Y');
            $isCrateWithBubble = ($configParams['CONFIG']['PACKAGES']['crateWithBubble'] == 'Y');
            $isCarGlass = ($configParams['CONFIG']['PACKAGES']['carGlass'] == 'Y');
            $isCarParts = ($configParams['CONFIG']['PACKAGES']['carParts'] == 'Y');
            $isPalletWithBubble = ($configParams['CONFIG']['PACKAGES']['palletWithBubble'] == 'Y');
            $isBag = ($configParams['CONFIG']['PACKAGES']['bag'] == 'Y');
            $isBubble = ($configParams['CONFIG']['PACKAGES']['bubble'] == 'Y');
            $isPallet = ($configParams['CONFIG']['PACKAGES']['pallet'] == 'Y');
            $frieghtTypeUID = $configParams['CONFIG']['CARGO']['FREIGHT_TYPE_UID'];


//echo '<pre>';
//var_dump($defaultLength);
//var_dump($defaultWeight);
//var_dump($defaultHeight);
//var_dump($isDefaultDemensions);
//echo '</pre>';
//die();

            $config = new DellinConfig();
            $config->setFrieghtTypeUID($frieghtTypeUID);
            $config->setLoginData($appkey, $login, $password, $counterAgent);
            $config->setSenderData((int)$daydelay, $derivalKLADR, $opfCountry, $opf, $name, (int)$inn,
                $personName, $personPhone, $personEmail, $yuriAddress);
            $config->setCargoParams($isSmall, $isInsurance, $workStart,
                $breakStart, $breakEnd, $workEnd, $loadingGroupingOfGoods);
            $config->setLoadingData( (int)$terminal_id, $isTerminalLoadingOrAddress, $loadingAddress);
            $config->setUnloadingData($isTerminalUnloadingOrAddress);
            $config->setLWHAndWeight($isDefaultDemensions, $defaultLength, $defaultWidth, $defaultHeight, $defaultWeight);
            $config->setLoggerSettings($isDebug, $isWarning);
            $config->setAdditionalServicePacking($isCrate, $isCratePlus, $isBox, $isType, $isCrateWithBubble,
                                                 $isCarGlass, $isCarParts, $isPalletWithBubble, $isBag, $isBubble,$isPallet);
            $config->setRequirementsLoading($requirementsLoading);
            $config->setRequirementsUnloading(null);

            return $config;

        } else {

            throw new \Exception(Loc::getMessage("DELLINDEV_METHOD_IS_NOT_DELLIN"));

        }

    }

    public static function changeFormatPhone($phone){

        $phone = preg_replace('~\\D+~u', '', $phone);

        if(substr( $phone, 0, 1 ) === "8"){

            $phone = str_split($phone, 1);
            $phone[0] = '7';
            $phone = implode($phone);

            return $phone;
        }

        return $phone;
    }

//    public static function changePriceShipping($shipment_id){
//
//
//
//    }

    public static function getPersonInfo($orderId){


        if($orderId <= 0)
            return null;

        $registry = Sale\Registry::getInstance(Sale\Registry::REGISTRY_TYPE_ORDER);

        $orderClass = $registry->getOrderClassName();

        $order = $orderClass::load($orderId);


        if(!$order)
            return null;

        if(!$props = $order->getPropertyCollection())
            return '';

        if(!$locationProp = $props->getDeliveryLocation())
            return '';

        if(!$locationCode = $locationProp->getValue())
            return '';

        if(!$locationData = \CSaleLocation::GetByID($locationCode))
            return '';

        $personType = $order->getPersonTypeId();
        $country = $locationData['COUNTRY_NAME_ORIG'];
        $regionName = $locationData['REGION_NAME_ORIG'];
        $cityName = $locationData['CITY_NAME_ORIG'];
        $price = $order->getPrice();
        $shipmentCost = $order->getDeliveryPrice();
        $personName = $props->getPayerName()->getValue();
//        $personPhone = $props->getItemByOrderPropertyCode('PHONE')->getValue();
//        $personAddress = $props->getItemByOrderPropertyCode('ADDRESS')->getValue();
//        $terminal_id = $props->getItemByOrderPropertyCode('TERMINAL_ID')->getValue();
//        $zip = $props->getItemByOrderPropertyCode('ZIP')->getValue();

//        var_dump($props->getItemByOrderPropertyCode('COMPANY')->getValue());
//        die();

//        $email = $props->getItemByOrderPropertyCode('EMAIL')->getValue();

        $mapProps = [
            'personPhone'  => 'PHONE',
            'zip' => 'ZIP',
            'personAddress' => 'ADDRESS',
            'email' => 'EMAIL',
            'terminal_id' => 'TERMINAL_ID',
            'deliveryTimeStart' => 'DELLIN_DELIVERYTIME_START',
            'deliveryTimeEnd' => 'DELLIN_DELIVERYTIME_END'
        ];

        foreach( $mapProps as $varName => $propName){
            $obj = $props->getItemByOrderPropertyCode($propName);
            if(is_null($obj)){
                ${$varName} = '';
            } else if(method_exists($obj, 'getValue')){
                ${$varName} = $obj->getValue();
            } else {
                ${$varName} = '';
            }

        }
        
        // $deliveryTimeStart = $props->getItemByOrderPropertyCode('DELLIN_DELIVERYTIME_START')->getValue();
        // $deliveryTimeEnd = $props->getItemByOrderPropertyCode('DELLIN_DELIVERYTIME_END')->getValue();


        $result = [
            'personType' => $personType,
            'personName' => $personName,
            'personPhone' => $personPhone,
            'personEmail' => $email,
            'personAddress' => $personAddress,
            'price' => $price,
            'shipmentCost' => $shipmentCost,
            'terminal_id' => $terminal_id,
            'deliverytimestart' => $deliveryTimeStart,
            'deliverytimeend' => $deliveryTimeEnd,
            'zip' => $zip, // TODO это поле должно быть обязательно используется при одноимённых населённых пунктах
            'country' => $country,
            'cityName' => $cityName,
            'regionName' => $regionName
        ];

        if (self::isYuriPerson($personType))
        {

            $result['companyInfo'] = [
                'nameCompany' => $props->getItemByOrderPropertyCode('COMPANY')->getValue(),
                'adrCompany' => $props->getItemByOrderPropertyCode('COMPANY_ADR')->getValue(),
                'INN' => $props->getItemByOrderPropertyCode('INN')->getValue(),
                'KPP' => $props->getItemByOrderPropertyCode('KPP')->getValue(),
                'contactPerson' => $props->getItemByOrderPropertyCode('CONTACT_PERSON')->getValue()
            ];

        }

        return $result;

    }

    public static function isYuriPerson($type_id){

       return CSalePersonType::GetByID($type_id)['NAME'] == Loc::getMessage("DELLINDEV_IS_YURI");

    }


    public static function getAllDataForJS($order_id){

        $data = [];

        $data['order_id'] = $order_id;
        $data['personInfo'] = self::getPersonInfo($order_id);
        $data['shipments'] = self::getBasketShipmentsByOrderId($order_id);

        return $data;
    }

    public static function includeJSinAdminPage($id, $request){

        $module_id = 'dellindev.shipment';

        CJSCore::RegisterExt(
            'BX.DellinAdmin',   
            array(
                'js'=> '/bitrix/js/'.$module_id.'/request.js',
                'css'=>array('/bitrix/css/'.$module_id.'/dellinDeliveryAdmin.css'),
                'lang' => '/bitrix/modules/dellindev.shipment/lang/'.LANGUAGE_ID.
                          '/install/include/js/dellindev.shipment/request.php',
                'rel'=>array('jquery')
            )
        );

        \Bitrix\Main\UI\Extension::load("ui.dialogs.messagebox");

        CJSCore::Init('BX.DellinAdmin');

        $asset = Asset::getInstance();
        $asset->addString('<script>BX.ready(function() {  
            BX.namespace("BX.DellinAdmin");
            BX.DellinAdmin.init();
            BX.DellinAdmin.orderId = '.$request["order_id"].';
            BX.DellinAdmin.methodId ="'.$id.'";
            BX.DellinAdmin.shipmentData = '.json_encode($request["shipments"]).';
            BX.DellinAdmin.personData = '.json_encode($request["personInfo"]).';
            
            }); </script>');
    }

   public static function normalizerPhones($phone)
   {
       $normalizePhone = preg_replace('/[^0-9]/', '', $phone);
       if(substr($normalizePhone, "0",1) == 8)
       {
            $normalizePhone = substr_replace($normalizePhone,'7',0,1);
       }
       return $normalizePhone;
   }


}