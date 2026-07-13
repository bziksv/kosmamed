<?php

namespace Ipolh\SDEK\Bitrix\Adapter;


use Ipolh\SDEK\Bitrix\Entity\Options;
use Ipolh\SDEK\Core\Entity\Collection;
use Ipolh\SDEK\Core\Entity\Money;
use Ipolh\SDEK\Core\Order\Item;
use Ipolh\SDEK\Core\Order\ReceiverCollection;
use Ipolh\SDEK\Core\Order\Sender;

class Order
{
    protected $bitrixId;
    protected $orderNumber;
    /**
     * @var Options
     */
    protected $options;
    protected $baseOrder;
    /**
     * @var Receiver
     */
    protected $receiver;
    /**
     * @var AddressTo
     */
    protected $addressTo;
    /*
     * $
     * */
    protected $addressFrom;
    protected $payment;
    protected $goods;
    /**
     * @var orderItems
     */
    protected $items;

    protected $moduleLbl;

    public function __construct(Options $options)
    {
        $this->options     = $options;
        $this->baseOrder   = new \Ipolh\SDEK\Core\Order\Order();
        $this->receiver    = new Receiver($options);
        $this->buyer       = new Buyer($options);
        $this->addressFrom = new AddressFrom($options);
        $this->addressTo   = new AddressTo($options);
        $this->payment     = new Payment($options);
        $this->goods       = new OrderGoods($options);
        $this->items       = new OrderItems($options);

        $this->moduleLbl   = IPOLH_SDEK_LBL;

        return $this;
    }

    /**
     * @param $bitrixId
     * @return $this
     * Заполняет заказ данными по заказу в Битрисе и настройкам по умолчанию.
     */
    public function newOrder($bitrixId)
    {
        $this->compileOrder();

        $this->setDefaultFields();

        return $this;
    }

    /**
     * Привязывает базовые сущности заказа (адрес, отправитель, итп) к базовому заказу
     */
    protected function compileOrder()
    {
        $this->getBaseOrder()
            //->addReciever($this->getReceiver()->getCoreReceiver())
            ->setBuyers($this->getBuyer()->getBuyerCollection())
            ->setAddressTo($this->getAddressTo()->getCoreAddress())
            ->setAddressFrom($this->getAddressFrom()->getCoreAddress())
            ->setPayment($this->getPayment()->getCorePayment())
            ->setNumber($this->getOrderNumber())
            ->setGoods($this->getGoods()->getCoreGoods())
            ->setItems($this->getItems()->getCoreItems());
    }

    /**
     * Устанавливает поля по умолчанию для заказа с учетом настроек модуля и данных в самом заказе.
     * ! только для newOrder
     */
    protected function setDefaultFields()
    {
        $this->getBaseOrder()
        ;
    }

    /**
     * @return $this
     * Устанавливает поля из запроса (по сути - из формы отправления заявки)
     */
    public function requestOrder()
    {
        /*
        // Deal with cp1251
        if (Tools::isModuleAjaxRequest()) {
            $_REQUEST = Tools::encodeFromUTF8($_REQUEST);
        }

        $this->bitrixId    = $_REQUEST['orderId'];
        $this->orderNumber = $_REQUEST['number'];

        $request = self::fromRequest();

        $this->getBaseOrder()->setNumber($this->orderNumber);

        $this->setArrayFields($request['order']);

        //$this->getReceiver()->fromArray($request['receiver']);
        $this->getBuyer()->fromArray($request['buyer']);
        $this->getAddressTo()->fromArray($request['addressTo']);
        $this->getAddressFrom()->fromArray($request['addressFrom']);

        $this->getPayment()->fromArray($request['payment']);
        $this->getGoods()->fromArray($request['goods']);
        $this->getItems()->fromArray($request['items']);

        $arDateCreate  = \Ipol\Ozon\Bitrix\Handler\Order::getOrderDate($this->bitrixId,true);

        $this->getBaseOrder()->setField('createDate',$arDateCreate['timestamp']);

        $this->compileOrder();

        */
        return $this;
    }

    /**
     * @return array
     * Связка данных из массива запроса с полями заказа
     */
    protected static function fromRequest()
    {
        return array(
            /*'receiver'    => array(
                'firstName' => $_REQUEST['buyerName'],
                'phone'     => $_REQUEST['buyerPhone'],
                'email'     => $_REQUEST['buyerEmail'],
                'PersonType' => $_REQUEST['buyerType'],
                'Company'    => $_REQUEST['buyerLegalName']
            ),*/
            'buyer'    => array(
                'firstName' => $_REQUEST['buyerName'],
                'phone'     => $_REQUEST['buyerPhone'],
                'email'     => $_REQUEST['buyerEmail'],
                'PersonType' => $_REQUEST['buyerType'],
                'Company'    => $_REQUEST['buyerLegalName']
            ),
            'addressTo'   => array(
                'line'    => $_REQUEST['address'],
            ),
            'addressFrom'   => array(
                'code'    => $_REQUEST['fromPlaceId'],
            ),

            'payment'     => array(
                'goods'      => $_REQUEST['payment_sum'],
                'estimated'  => $_REQUEST['price'],
                'isBeznal'   => $_REQUEST['payment_isBeznal'],
                'delivery'   => $_REQUEST['deliveryCost'],
                'payed'      => 0,//$_REQUEST['payment_prepayment'],
                'ndsDelivery'  => $_REQUEST['payment_ndsDeliveryRate'],
            ),

            'goods' => array(
                'length'    => $_REQUEST['length'],
                'width'     => $_REQUEST['width'],
                'height'    => $_REQUEST['height'],
                'weight'    => $_REQUEST['weight']
            ),

            'order' => array(
                'orderId'              => $_REQUEST['orderId'],
                'DeliveryVariantId'    => $_REQUEST['deliveryVariantId'],
                'allowUncovering'      => (array_key_exists('allowUncovering',$_REQUEST) && $_REQUEST['allowUncovering'] === 'Y'),
                'allowPartialDelivery' => (array_key_exists('allowPartialDelivery',$_REQUEST) && $_REQUEST['allowPartialDelivery'] === 'Y'),
            ),

            'items' => $_REQUEST['items']
        );
    }

    /**
     * @param $array
     * Устанавливаем поля из массива в базовый заказ
     */
    protected function setArrayFields($array)
    {
        foreach($array as $key => $val)
        {
            $this->getBaseOrder()->setField($key,(string)$val);
        }
    }

    /**
     * @param $bitrixId - ID заказа в Битриксе
     * @param $mode - всегда 1 (2 - для отгрузок, не подключено)
     * @return $this
     * Заполняет заказ данными по сведениям, хранящимся в таблице модуля (то есть - по отосланному заказу)
     */
    public function uploadedOrder($bitrixId,$mode = false,$oldApp=false)
    {
        $this->bitrixId = $bitrixId;

        $orderParams = \sdekdriver::GetByOI($bitrixId,$mode);
        $account     = $orderParams['ACCOUNT'];

        if($orderParams){
            $orderParams = unserialize($orderParams['PARAMS'], ['allowed_classes' => false]);
            $baze   = ($mode == 'shipment') ? \sdekdriver::getShipmentById($bitrixId) : \CSaleOrder::GetById($bitrixId);
            $on     = ($baze['ACCOUNT_NUMBER'])?$baze['ACCOUNT_NUMBER']:$bitrixId;
            $bezNal = ($orderParams['isBeznal'] == 'Y')?true:false;
            $usualDelivery = (\Ipolh\SDEK\option::get('deliveryAsPosition') != 'Y');
            $mesId=\sdekdriver::getMessId();

            $sendCity = \sdekdriver::getHomeCity();
            if(array_key_exists('departure',$orderParams) && $orderParams['departure'])
                $sendCity = $orderParams['departure'];

            $senderCountry = false;
            if($sendCity){
                $arSendCity = \sqlSdekCity::getBySId($sendCity);
                $senderCountry = ($arSendCity['COUNTRY']) ? $arSendCity['COUNTRY'] : 'rus';
                $senderCountry = \sdekdriver::getCountryCode($senderCountry);
            }

            $arCity  = \sqlSdekCity::getBySId($orderParams['location']);
            $country = ($arCity['COUNTRY']) ? $arCity['COUNTRY'] : 'rus';
            $recCountryCode = \sdekdriver::getCountryCode($country);
            $authSelect = ($account) ? $account : array('COUNTRY'=>$country);
            $headers    = \sdekdriver::getXMLHeaders($authSelect);

            if(!array_key_exists('toPay',$orderParams)) {
                $orderParams['toPay'] = 0;
            }

            // валюта
            $cntrCurrency = false;
            if($country != 'rus'){
                $cntrCurrency = array();
                $svdCountries = \sdekdriver::getCountryOptions();
                if(array_key_exists($country,$svdCountries) && $svdCountries[$country]['cur'] && $svdCountries[$country]['cur'] != $defVal) // todo wtf is defval?..
                    $cntrCurrency['site'] = $svdCountries[$country]['cur'];

                switch($country){
                    case 'blr': $cntrCurrency['sdek'] = 'BYR'; break;
                    case 'kaz': $cntrCurrency['sdek'] = 'KZT'; break;
                }

                if(array_key_exists('deliveryP',$orderParams))
                    $orderParams['deliveryP'] = floatval(\sdekExport::formatCurrency(array('TO'=>$cntrCurrency['site'],'SUM'=>$orderParams['deliveryP'],'orderId'=>$bitrixId)));
                $orderParams['toPay'] = floatval(\sdekExport::formatCurrency(array('TO'=>$cntrCurrency['site'],'SUM'=>$orderParams['toPay'],'orderId'=>$bitrixId)));

                if (!$bezNal)
                {
                    if(array_key_exists('deliveryP',$orderParams))
                        $orderParams['deliveryP'] = floatval(\sdekExport::formatCurrency(array('TO'=>$cntrCurrency['site'],'SUM'=>$orderParams['deliveryP'],'orderId'=>$bitrixId)));

                    $orderParams['toPay'] = floatval(\sdekExport::formatCurrency(array('TO'=>$cntrCurrency['site'],'SUM'=>$orderParams['toPay'],'orderId'=>$bitrixId)));
                }
            }elseif(
                $country == 'rus' &&
                $orderParams['NDSDelivery'] &&
                array_key_exists('deliveryP',$orderParams)
            ){
                // NDS delivery
                if($country == 'rus' && $orderParams['NDSDelivery']){
                    $priceDeliveryVAT = \sdekdriver::ndsVal($orderParams['deliveryP'],$orderParams['NDSDelivery']);
                }
            }
            $priceDelivery = array_key_exists('deliveryP',$orderParams) ? $orderParams['deliveryP'] : $baze["PRICE_DELIVERY"];

            $packs = \sdekdriver::getPacks($bitrixId,(($mode === 'shipment') ? $mode : 'order'),$orderParams);

            // handling prices
            foreach($packs as $number => $packContent){
                foreach($packContent['GOODS'] as $index => $arGood){
                    if($cntrCurrency){
                        $packs[$number]['GOODS'][$index]["price"]    = floatval(\sdekExport::formatCurrency(array('TO'=>$cntrCurrency['site'],'SUM'=>$arGood["price"],'orderId'=>$bitrixId)));
                        $arGood["price"]                             = floatval(\sdekExport::formatCurrency(array('TO'=>$cntrCurrency['site'],'SUM'=>$arGood["price"],'orderId'=>$bitrixId)));
                        $packs[$number]['GOODS'][$index]["cstPrice"] = floatval(\sdekExport::formatCurrency(array('TO'=>$cntrCurrency['site'],'SUM'=>$arGood["cstPrice"],'orderId'=>$bitrixId)));
                    }
                    $toPay = ($bezNal || $orderParams['toPay'] == 0) ? 0 : $arGood["price"];
                    $cnt = (int) $arGood["quantity"];
                    if($toPay){
                        $all = $toPay * $cnt;
                        if($all > $orderParams['toPay']){
                            $toPay = $orderParams['toPay'] / $cnt;
                            $orderParams['toPay'] = 0;
                        }else
                            $orderParams['toPay'] -= $all;
                    }

                    if($country == 'rus' && $orderParams['NDSGoods']){
                        switch($arGood["vat"]){
                            case '0.20' : $vatRate = 'VAT20'; break;
                            case '0.18' : $vatRate = 'VAT18'; break;
                            case '0.12' : $vatRate = 'VAT12'; break;
                            case '0.10' : $vatRate = 'VAT10'; break;
                            case '0.07' : $vatRate = 'VAT07'; break;
                            case '0.05' : $vatRate = 'VAT05'; break;
                            case '0.00' : $vatRate = 'VAT0'; break;
                            default     : $vatRate = $orderParams['NDSGoods']; break;
                        }
                        $packs[$number]['GOODS'][$index]["VATRate"] = $vatRate;
                        $packs[$number]['GOODS'][$index]["VATSum"]  = \sdekdriver::ndsVal($toPay,$vatRate);
                        $packs[$number]['GOODS'][$index]["VATS"]    = true;
                    } else {
                        $packs[$number]['GOODS'][$index]["VATS"]    = false;
                    }

                    $packs[$number]['GOODS'][$index]["price"]    = $toPay;
                    $packs[$number]['GOODS'][$index]["quantity"] = $cnt;
                }
            }
            // Making Order
            $this->getBaseOrder()->setNumber($on)
                                 ->setField('tariffCode',$orderParams['service'])
                                 ->setField('comment',$orderParams['comment'])
                                 ->setField('isInternational',false) // todo:check
                                 ->setField('print','waybill')
                                 ->setField('developerKey','4b1d17d262bdf16e36b9070934c74d47')
                                 ->setField('account',$headers['account']);

            // transit App
            $this->getBaseOrder()->setField('messId',$mesId)
                                 ->setField('headers',$headers)
                                 ->setField('RecCountryCode',$recCountryCode)
                                 ->setField('SendCountryCode',$senderCountry);

            $addressFrom = new \Ipolh\SDEK\Core\Order\Address();
            $addressFrom->setCode($sendCity)
                ->setStreet(($orderParams['from_loc_street'] !== '') ? $orderParams['from_loc_street'] : null)
                ->setHouse(($orderParams['from_loc_house'] !== '') ? $orderParams['from_loc_house'] : null)
                ->setFlat(($orderParams['from_loc_flat'] !== '') ? $orderParams['from_loc_flat'] : null)
                //->setField('countryCode','RU') // todo:check
                ->setCity($arSendCity['NAME']);
            $this->getBaseOrder()->setAddressFrom($addressFrom);

            $addressTo   = new \Ipolh\SDEK\Core\Order\Address();
            switch (true) {
                case ($orderParams["PVZ"]):
                    $addressTo->setField('pointId', $orderParams["PVZ"]);
                    break;
                case ($orderParams["PST"]):
                    $addressTo->setField('pointId', $orderParams["PST"]);
                    break;
                case ($orderParams['address']):
                    $addressTo
                        ->setCode($orderParams["location"])
                        ->setLine($orderParams['address']);
                    break;
                default :
                    $addressTo
                        ->setCode($orderParams["location"])
                        ->setStreet(str_replace('"', "'", $orderParams['street']))
                        ->setHouse($orderParams['house'])
                        ->setFlat($orderParams['flat']);
                    break;
            }
            $this->getBaseOrder()->setAddressTo($addressTo);

            //Payment
            $payment = new \Ipolh\SDEK\Core\Order\Payment();
            $obDeliveryPay = false;
            if($bezNal){
                $payment->setIsBeznal(true);
            } else {
                $payment->setIsBeznal(false);
                if($priceDelivery){
                    $payment->setDelivery(new Money($priceDelivery));
                    if(!is_null($priceDeliveryVAT)){
                        $payment->setField('DeliveryRecipientVATRate',$orderParams['NDSDelivery'])
                                ->setField('DeliveryRecipientVATSum',$priceDeliveryVAT);
                        switch ($orderParams['NDSDelivery']){
                            case 'VAT05' : $priceDeliveryVAT =  5; break;
                            case 'VAT07' : $priceDeliveryVAT =  7; break;
                            case 'VAT10' : $priceDeliveryVAT = 10; break;
                            case 'VAT12' : $priceDeliveryVAT = 12; break;
                            case 'VAT18' : $priceDeliveryVAT = 18; break;
                            case 'VAT20' : $priceDeliveryVAT = 20; break;
                            case 'VATX'  : $priceDeliveryVAT = null; break;
                            case 'VAT0'  :
                            default      : $priceDeliveryVAT = 0; break;
                        }
                        $payment->setNdsDelivery($priceDeliveryVAT);
                    }
                    if($usualDelivery) {
                        $payment->setField('usualDelivery',true);
                    } else {
                        $payment->setField('usualDelivery',false);
                        $obDeliveryPay = array(
                            'articul'  => 'delivery',
                            'id'       => 'delivery',
                            'cstPrice' => 0,
                            'price'    => $priceDelivery,
                            'weight'   => 1,
                            'quantity' => 1,
                            'name'	   => GetMessage('IPOLSDEK_LBL_DELIVERY'),
                            'VATS'	   => false
                        );
                        if($priceDeliveryVAT !== false){
                            $obDeliveryPay['VATS']    = true;
                            $obDeliveryPay['VATSum']  = \sdekdriver::ndsVal($priceDelivery, $orderParams['NDSDelivery']);
                            $obDeliveryPay['VATRate'] = $orderParams['NDSDelivery'];
                        }

                        // Add delivery payment as 'item'
                        $countPacks = count($packs);
                        $counter    = 1;
                        foreach($packs as $number => $packContent) {
                            if ($counter++ >= $countPacks) {
                                $packs[$number]['GOODS'][] = $obDeliveryPay;
                            }
                        }
                    }
                }
                if($cntrCurrency){
                    $payment->setField('countryCurrency',$cntrCurrency);
                }
            }
            $this->getBaseOrder()->setPayment($payment);

            // Sender
            $sender = new Sender();
            if(array_key_exists('sender_phone',$orderParams) && $orderParams['sender_phone']){
                $sender->setPhone($orderParams['sender_phone']);
            }
            if(array_key_exists('sender_phone_add',$orderParams) && $orderParams['sender_phone_add']){
                $sender->setField('phoneAdditional', $orderParams['sender_phone_add']);
            }
            if(array_key_exists('sender_company',$orderParams) && $orderParams['sender_company']){
                $sender->setCompany($orderParams['sender_company']);
            }
            if(array_key_exists('sender_name',$orderParams) && $orderParams['sender_name']){
                $sender->setFullName($orderParams['sender_name']);
            }
            if (array_key_exists('seller_name',$orderParams) && $orderParams['seller_name']) {
                $sender->setField('sellerCompany', $orderParams['seller_name']);
            }
            if (array_key_exists('seller_phone',$orderParams) && $orderParams['seller_phone']) {
                $sender->setField('sellerPhone', $orderParams['seller_phone']);
            }
            if (array_key_exists('seller_address',$orderParams) && $orderParams['seller_address']) {
                $sender->setField('sellerAddress', $orderParams['seller_address']);
            }
            $this->getBaseOrder()->setSender($sender);

            // Recipient
            $receiver = new \Ipolh\SDEK\Core\Order\Receiver();
            $receiver
                ->setPhone($orderParams['phone'])
                ->setFullName($orderParams["name"])
                ->setEmail($orderParams['email'])
            ;
            $receiverCollection = new ReceiverCollection();
            $receiverCollection->add($receiver);
            $this->getBaseOrder()->setReceivers($receiverCollection);

            // additional services
            $arServices = array();
            if(array_key_exists('AS',$orderParams) && count($orderParams['AS'])){
                foreach($orderParams['AS'] as $service => $nothing){
                    if($oldApp){
                        $arServices []= $service;
                    } else {
                        switch ($service) {
                            case 3  : $service = 'DELIV_WEEKEND';             break;
                            case 7  : $service = 'DANGER_CARGO';              break;
                            case 16 : $service = 'TAKE_SENDER';               break;
                            case 17 : $service = 'DELIV_RECEIVER';            break;
                            case 27 : $service = 'SMS';                       break;
                            case 30 : $service = 'TRYING_ON';                 break;
                            case 36 : $service = 'PART_DELIV';                break;
                            case 48 : $service = 'REVERSE';                   break;
                            case 59 : $service = 'NOTIFY_ORDER_CREATED';      break;
                            case 60 : $service = 'NOTIFY_ORDER_DELIVERY';     break;
                            case 81 : $service = 'BAN_ATTACHMENT_INSPECTION'; break;
                            case 96 : $service = 'ADULT_GOODS';               break;
                        }
                        $arServices[$service] = 1;
                    }
                }
            }
            $this->getBaseOrder()->setField('services', $arServices);

            if(\Ipolh\SDEK\option::get('addData') == 'Y' && array_key_exists('deliveryDate',$orderParams) && $orderParams['deliveryDate'] && strpos($orderParams['deliveryDate'],'.') !== false){
                $this->getBaseOrder()->setField('deliveryDate', $orderParams['deliveryDate']);
            }

            // Items and packages
            $arPacks = array();
            foreach ($packs as $packNumber => $packInfo)
            {
                $arPacks[$packNumber] = $packInfo;

                $arPacks[$packNumber]['GOODS'] = new Collection('Item');

                foreach ($packInfo['GOODS'] as $arGood){

                    if(array_key_exists("marks",$arGood) && $arGood["marks"] && is_array($arGood["marks"])){
                        $_cnt = count($arGood["marks"]);
                        for($i = 0; $i < $arGood["quantity"]; $i++){
                            switch ($arGood["VATRate"]){
                                case 'VAT05' : $goodVR =  5; break;
                                case 'VAT07' : $goodVR =  7; break;
                                case 'VAT10' : $goodVR = 10; break;
                                case 'VAT12' : $goodVR = 12; break;
                                case 'VAT18' : $goodVR = 18; break;
                                case 'VAT20' : $goodVR = 20; break;
                                case 'VATX'  : $goodVR = null; break;
                                case 'VAT0'  :
                                default      : $goodVR = 0; break;
                            }

                            $item = new Item();
                            $item->setName(str_replace('"', "'", $arGood["name"]))
                                ->setArticul($arGood["articul"])
                                ->setId($arGood['id'])
                                ->setPrice(new Money(number_format($arGood["price"], 2, '.', '')))
                                ->setCost(new Money(number_format($arGood["cstPrice"], 2, '.', '')))
                                ->setWeight($arGood["weight"])
                                ->setVatRate($goodVR)
                                ->setVatSum(new Money($arGood["VATSum"]))
                                ->setField('oldVATRate',$arGood["VATRate"]);

                            if (!empty($arGood['url'])) {
                                $item->setField('url', $arGood['url']);
                            }

                            if (!empty($arGood['feacn'])) {
                                $item->setField('feacn', $arGood['feacn']);
                            }

                            if($_cnt > $i) {
                                $item->setQuantity(1)
                                     ->setField('marking',$arGood["marks"][$i]);
                                $arPacks[$packNumber]['GOODS']->add($item);
                            } else {
                                $item->setQuantity($arGood["quantity"] - $i);
                                $arPacks[$packNumber]['GOODS']->add($item);
                                break;
                            }
                        }
                    } else {
                        switch ($arGood["VATRate"]){
                            case 'VAT05' : $goodVR =  5; break;
                            case 'VAT07' : $goodVR =  7; break;
                            case 'VAT10' : $goodVR = 10; break;
                            case 'VAT12' : $goodVR = 12; break;
                            case 'VAT18' : $goodVR = 18; break;
                            case 'VAT20' : $goodVR = 20; break;
                            case 'VATX'  : $goodVR = null; break;
                            case 'VAT0'  :
                            default      : $goodVR = 0; break;
                        }

                        $item = new Item();
                        $item->setName(str_replace('"', "'", $arGood["name"]))
                            ->setArticul($arGood["articul"])
                            ->setId($arGood['id'])
                            ->setPrice(new Money(number_format($arGood["price"], 2, '.', '')))
                            ->setCost(new Money(number_format($arGood["cstPrice"], 2, '.', '')))
                            ->setWeight($arGood["weight"])
                            ->setQuantity($arGood['quantity'])
                            ->setVatRate($goodVR)
                            ->setVatSum(new Money($arGood["VATSum"]))
                            ->setField('oldVATRate',$arGood["VATRate"]);

                        if (!empty($arGood['url'])) {
                            $item->setField('url', $arGood['url']);
                        }

                        if (!empty($arGood['feacn'])) {
                            $item->setField('feacn', $arGood['feacn']);
                        }

                        $arPacks[$packNumber]['GOODS']->add($item);
                    }
                }
            }
            $this->getBaseOrder()->setField('packages',$arPacks);
        }

        return $this;
    }

    protected function fromDB($arDB)
    {
        /*
        return array(
            'receiver'    => array(
                'firstName'  => $arDB['RECIPIENT_NAME'],
                'phone'      => $arDB['RECIPIENT_PHONE'],
                'email'      => $arDB['RECIPIENT_EMAIL'],
                'PersonType' => $arDB['RECIPIENT_TYPE'],
                'Company'    => $arDB['RECIPIENT_LEGAL_NAME'],
            ),

            'buyer'    => array(
                'firstName'  => $arDB['BUYER_NAME'],
                'phone'      => $arDB['BUYER_PHONE'],
                'email'      => $arDB['BUYER_EMAIL'],
                'PersonType' => $arDB['BUYER_TYPE'],
                'Company'    => $arDB['BUYER_LEGAL_NAME'],
            ),

            'addressTo'   => array(
                'line'    => $arDB['DELIVERY_ADDRESS'],
            ),
            'addressFrom'   => array(
                'code'    => $arDB['FIRSTMILE_TRANSFER_FROM_PLACE_ID'],
            ),

            'payment'     => array(
                'isBeznal'    => ($arDB['PAYMENT_TYPE'] === 'FullPrepayment'),
                'payed'       => $arDB['PAYMENT_PREPAYMENT_AMOUNT'],
                'delivery'    => $arDB['PAYMENT_DELIVERY_PRICE'],

                'ndsDelivery' => $arDB['PAYMENT_DELIVERY_VAT_RATE'],

                'goods'       => $arDB['PAYMENT_RECIPIENT_PAYMENT_AMOUNT'],
                'estimated'   => $arDB['ESTIMATED'],
            ),

            'goods' => unserialize($arDB['PACKAGES'], ['allowed_classes' => false]),
            'items' => unserialize($arDB['ORDER_LINES'], ['allowed_classes' => false]),

            'order' => array(
                'orderId'     => $arDB['BITRIX_ID'],
                'ozonId'      => $arDB['OZON_ID'],
                'ozon_logisticOrderNumber' => $arDB['LOGISTIC_ORDER_NUMBER'],
                'ozon_postingId' => $arDB['POSTING_ID'],
                'ozon_status' => $arDB['OZON_STATUS'],

                'firstMileTransferType' => $arDB['FIRSTMILE_TRANSFER_TYPE'],
                'fromPlaceId'           => $arDB['FIRSTMILE_TRANSFER_FROM_PLACE_ID'],
                'DeliveryVariantId'     => $arDB['DELIVERY_VARIANT_ID'],

                'deliveryTimeFrom' => $arDB['DELIVERY_TIME_INTERVAL_FROM'],
                'deliveryTimeTo'   => $arDB['DELIVERY_TIME_INTERVAL_TO'],

                'allowUncovering'      => $arDB['ALLOW_UNCOVERING'],
                'allowPartialDelivery' => $arDB['ALLOW_PARTIAL_DELIVERY'],
            ),
        );
        */
    }

    /**
     * @param $ozonId
     * @return order
     * @throws \Exception
     * Заполняет заказ данными по таблице модуля (отправленный), выборка - по ID озона.
     */
    public function uploadedOrderByOzonId($ozonId)
    {
        /*
        $obOrder = OrdersTable::getByOzonId($ozonId);
        if($obOrder)
        {
            return $this->uploadedOrder($obOrder['BITRIX_ID']);
        }else
        {
            throw new \Exception('No order with ozon_Id '.$ozonId);
        }
        */
    }
    /**
     * @param $logisticOrderNumber
     * @return order
     * @throws \Exception
     * Заполняет заказ данными по таблице модуля (отправленный), выборка - по LON озона.
     */
    public function uploadedOrderByLogisticOrderNumber($logisticOrderNumber)
    {
        /*
        $obOrder = OrdersTable::getByLogisticOrderNumber($logisticOrderNumber);
        if($obOrder)
        {
            return $this->uploadedOrder($obOrder['BITRIX_ID']);
        }else
        {
            throw new \Exception('No order with LON '.$logisticOrderNumber);
        }
        */
    }


    /**
     * @param $code
     * @return bool
     * Конгениальная проверка чекбоксов
     */
    protected function checkBoolOption($code)
    {
        $method = 'get'.ucfirst($code);
        return ($this->options->$method() === 'Y');
    }

    protected static function makeFivepostTimeFromTimestamp($timeStamp){
        $strDateCreate = new \DateTime( 'now', new \DateTimeZone('UTC'));
        $strDateCreate->setTimestamp($timeStamp);
        return str_replace('+00:00', '.000Z', $strDateCreate->format('c'));
    }

    protected static function makeDBTime($timeStamp){
        $obDate = \Bitrix\Main\Type\DateTime::createFromTimestamp($timeStamp);
        return $obDate;
    }

    public function getOrderData()
    {
        return ($this->getBaseOrder()) ? $this->getBaseOrder()->getField('account') : null;
    }

    /**
     * @return mixed
     */
    public function getBitrixId()
    {
        return $this->bitrixId;
    }

    /**
     * @return orderItems
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param mixed $items
     * @return $this
     */
    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * @return \Ipolh\SDEK\Core\Order\Order
     */
    public function getBaseOrder()
    {
        return $this->baseOrder;
    }

    /**
     * @return Receiver
     */
    public function getReceiver()
    {
        return $this->receiver;
    }

    /**
     * @param Receiver $receiver
     * @return $this
     */
    public function setReceiver($receiver)
    {
        $this->receiver = $receiver;

        return $this;
    }

    /**
     * @return Buyer
     */
    public function getBuyer()
    {
        return $this->buyer;
    }

    /**
     * @param Buyer $buyer
     * @return $this
     */
    public function setBuyer($buyer)
    {
        $this->buyer = $buyer;

        return $this;
    }

    // Getters/setters
    /**
     * @return addressTo
     */
    public function getAddressTo()
    {
        return $this->addressTo;
    }

    /**
     * @return addressFrom
     */
    public function getAddressFrom()
    {
        return $this->addressFrom;
    }

    /**
     * @param mixed $addressTo
     * @return $this
     */
    public function setAddressTo($addressTo)
    {
        $this->addressTo = $addressTo;

        return $this;
    }

    /**
     * @return payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * @param mixed $payment
     * @return $this
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * @return OrderGoods
     */
    public function getGoods()
    {
        return $this->goods;
    }

    /**
     * @param mixed $obGoods
     * @return $this
     */
    public function setGoods($obGoods)
    {
        $this->goods = $obGoods;

        return $this;
    }


    /**
     * @return mixed
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }
}