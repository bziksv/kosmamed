<?php
namespace Ipolh\SDEK\Legacy;


use Bitrix\Main\Type\DateTime;
use Ipolh\SDEK\abstractGeneral;
use Ipolh\SDEK\Api\Entity\Request\CalculateList;
use Ipolh\SDEK\Api\Entity\Response\OrderInfo;
use Ipolh\SDEK\Api\Entity\Response\OrderMake;
use Ipolh\SDEK\Api\Entity\Response\Part\OrderInfo\Entity;
use Ipolh\SDEK\Core\Delivery\Shipment;
use Ipolh\SDEK\Core\Entity\Collection;
use Ipolh\SDEK\Core\Order\Item;
use Ipolh\SDEK\Core\Order\Order;
use Exception;
use Ipolh\SDEK\Api\ApiLevelException;
use Ipolh\SDEK\Api\BadResponseException;
use Ipolh\SDEK\Api\Entity\Response\ErrorResponse;
use Ipolh\SDEK\Core\Delivery\Location;
use Ipolh\SDEK\PointsHandler;
use Ipolh\SDEK\SDEK\AppLevelException;
use Ipolh\SDEK\SDEK\Entity\CalculateListResult;
use Ipolh\SDEK\SDEK\Entity\OrderInfoResult;
use Ipolh\SDEK\SDEK\Entity\OrderMakeResult;
use Ipolh\SDEK\SDEK\ExceptionCollection;
use Ipolh\SDEK\SDEK\Entity\DeliveryPointsResult;
use Ipolh\SDEK\Bitrix\Tools;

class transitApplication
{
    protected $account;

    protected $password;

    /**
     * @var ExceptionCollection
     * empty if no errors occurred, error-info otherwise
     */
    protected $errorCollection;

    public function __construct($account,$password)
    {
        $this->account  = $account;
        $this->password = $password;

        $this->errorCollection = new ExceptionCollection();
    }

    public function getToken($blank){
        \CDeliverySDEK::$sdekCity   = 44;
        \CDeliverySDEK::$sdekSender = 44;
        \CDeliverySDEK::setOrder(array('GABS'=>array(
            "D_L" => 20,
            "D_W" => 30,
            "D_H" => 20,
            "W"   => min(\Ipolh\SDEK\option::get('weightD') / 1000,1)
        )));

        \CDeliverySDEK::setAuth($this->account,$this->password);
        $resAuth = \CDeliverySDEK::calculateDost('pickup');
        if(!$resAuth['success'])
            $resAuth = \CDeliverySDEK::calculateDost('courier');

        if(!array_key_exists('success',$resAuth) || !$resAuth['success']){
            throw new AppLevelException(implode(',',$resAuth));
        }

        return $resAuth;
    }

    /**
     * @param Order $order
     * @param null $type
     * @param null $developerKey
     */
    public function orderMake($order, $type = null, $developerKey = null)
    {
        $headers  = $order->getField('headers');
        $receiver = $order->getReceivers()->getFirst();
        $strXML = "<DeliveryRequest Number=\"".$order->getField('messId')."\" Date=\"".$headers['date']."\" Account=\"".$headers['account']."\" Secure=\"".$headers['secure']."\" OrderCount=\"1\" DeveloperKey=\"".$developerKey."\" ".(($order->getField('RecCountryCode')) ? "RecCountryCode=\"".$order->getField('RecCountryCode')."\" " : '').(($order->getField('SendCountryCode')) ? "SendCountryCode=\"".$order->getField('SendCountryCode')."\"" : '').">
	<Order Number=\"".$order->getNumber()."\"
		SendCityCode=\"".$order->getAddressFrom()->getCode()."\" 
		RecCityCode=\"".$order->getAddressTo()->getCode()."\" 
		RecipientName=\"".$receiver->getFullName()."\" 
		";
        if($order->getReceivers()->getFirst()->getEmail()){
            $strXML .= "RecipientEmail=\"".$receiver->getEmail()."\" ";
        }
        $strXML .= "
		Phone=\"".$receiver->getPhone()."\"";

        $cntrCurrency = $order->getPayment()->getField('countryCurrency');
        if($cntrCurrency && $cntrCurrency['sdek']){
            $strXML .= "
		RecipientCurrency=\"".$cntrCurrency['sdek']."\"
		ItemsCurrency=\"".$cntrCurrency['sdek']."\" 
		";
        }

        if(!$order->getPayment()->getIsBeznal()){
            if($order->getPayment()->getField('usualDelivery')){
                $strXML .= "
		DeliveryRecipientCost=\"".number_format($order->getPayment()->getDelivery()->getAmount(),2,'.','')."\"";
                if($order->getPayment()->getNdsDelivery() !== false) {
                    $strXML .= "
		DeliveryRecipientVATRate=\"" . $order->getPayment()->getField('DeliveryRecipientVATRate') . "\"
		DeliveryRecipientVATSum =\"" . $order->getPayment()->getField('DeliveryRecipientVATSum') . "\"	
";
                }
            } else {

            }
        }

        if($order->getField('comment')){
            $strXML .= "
		Comment=\"".str_replace('"',"'",$order->getField('comment'))."\" ";
        }
        $strXML .= "
		TariffTypeCode=\"".$order->getField('tariffCode')."\" ";
        $strXML .= ">
		";

        $sender = $order->getSender();
        if ($sender->getField('sellerCompany')) {
            $strXML .= "<Seller";
            if ($sender->getField('sellerAddress')) {
                $strXML .= " Address=\"".$sender->getField('sellerAddress')."\"";
            }
            if ($sender->getField('sellerCompany')) {
                $strXML .= " Name=\"".$sender->getField('sellerCompany')."\"";
            }
            if ($sender->getField('sellerPhone')) {
                $strXML .= " Phone=\"".$sender->getField('sellerPhone')."\"";
            }
            $strXML .= "/>
            ";
        }
        if($sender->getPhone()){
            $strXML .= "<Sender";
            if($sender->getCompany()){
                $strXML .= " Company=\"".$sender->getCompany()."\"";
            }
            if($sender->getFullName()){
                $strXML .= " Name=\"".$sender->getFullName()."\"";
            }
            $strXML .= ">
            ";
            if ($order->getAddressFrom()->getStreet()) {
                $strXML .= "<Address Street=\"".$order->getAddressFrom()->getStreet()."\"";
                if ($order->getAddressFrom()->getHouse()) {
                    $strXML .= " House=\"".$order->getAddressFrom()->getHouse()."\"";
                }
                if ($order->getAddressFrom()->getFlat()) {
                    $strXML .= " Flat=\"".$order->getAddressFrom()->getFlat()."\"";
                }
                $strXML .= "/>
            ";
            }
            $strXML .= "<phone>".$sender->getPhone()."</phone>
        ";
            $strXML .= "</Sender>
        ";
        }

        //адрес
		if($order->getAddressTo()->getField('pointId'))
            $strXML .= "<Address PvzCode=\"".$order->getAddressTo()->getField('pointId')."\" />
		";
        else
            $strXML .= "<Address Street=\"".$order->getAddressTo()->getStreet()."\" House=\"".$order->getAddressTo()->getHouse()."\" Flat=\"".$order->getAddressTo()->getFlat()."\" />
		";

        $items = $order->getItems();
        $goods = $order->getGoods();
        $packs = $order->getField('packages');

        foreach($packs as $number => $packContent){ // см, г
            $arPackArticules = array();
            $strXML .= "<Package Number=\"{$number}\" BarCode=\"{$number}\" Weight=\"{$packContent['WEIGHT']}\" SizeA=\"{$packContent['LENGTH']}\" SizeB=\"{$packContent['WIDTH']}\" SizeC=\"{$packContent['HEIGHT']}\">";
            /** @var Collection $packItems */
            $packItems = $packContent['GOODS'];

            $packItems->reset();
            /** @var Item $coreItem */
            while($coreItem = $packItems->getNext()){

                $strNDS = '';
                if($coreItem->getVatRate()){
                    $strNDS   .= " PaymentVATRate=\"".$coreItem->getField('oldVATRate')."\"";
                    $strNDS   .= " PaymentVATSum=\"".$coreItem->getVatSum()->getAmount()."\"";
                }

                $articul = ($coreItem->getArticul())?str_replace('"',"'",$coreItem->getArticul()):$coreItem->getId();
                /*if(array_key_exists($articul,$arPackArticules))
                    $articul.="(".(++$arPackArticules[$articul]).")";
                else
                    $arPackArticules[$articul] = 1;
                */

                $strGood = "WareKey=\"".$articul."\" Cost=\"".$coreItem->getCost()->getAmount()."\" Payment=\"".$coreItem->getPrice()->getAmount()."\" Weight=\"".$coreItem->getWeight()."\"".$strNDS." Comment=\"".$coreItem->getName()."\"";
                if($coreItem->getField('marking')){
                    $strXML .= "
            <Item ".$strGood." Marking=\"".$coreItem->getField('marking')."\" Amount=\"".$coreItem->getQuantity()."\"/>";
                } else {
                    $strXML .= "
            <Item ".$strGood." Amount=\"".$coreItem->getQuantity()."\"/>";
                }
            }
            $strXML .= "
        </Package>
        ";
        }

        //допуслуги
		if($order->getField('services'))
            foreach($order->getField('services') as $service)
                $strXML .= "<AddService ServiceCode=\"".$service."\"></AddService>
		";

        // врем€ доставки
		if($order->getField('deliveryDate')){
            $deliveryDate = explode('.',$order->getField('deliveryDate'));
            $strXML .= "<Schedule>
			<Attempt ID=\"1\" Date=\"".$deliveryDate[2]."-".$deliveryDate[1]."-".$deliveryDate[0]."\"></Attempt>
		</Schedule>";
        }

        $strXML .= "
	</Order>";

		$strXML.="
</DeliveryRequest>";

		$result  = self::sendToSDEK($strXML,'new_orders');
		$obReturn = new OrderMakeResult();
		$obReturn->setSuccess(false);

        \Ipolh\SDEK\Bitrix\Admin\Logger::orderSend(array('Request' => $strXML, 'Response'=> $result['result']));

        if($result['code'] == 200){
            $xml=simplexml_load_string($result['result']);
            if($xml['ErrorCode']){
                $ex = new \Exception(GetMessage("IPOLSDEK_SEND_UNBLSND").GetMessage("IPOLSDEK_ERRORLOG_ERRORCODE")."\n".\sdekdriver::zaDEjsonit($xml['Msg']));
                $this->addError($ex);
            }elseif($xml->DeliveryRequest['ErrorCode']){
                $ex = new \Exception(GetMessage("IPOLSDEK_SEND_UNBLSND").GetMessage("IPOLSDEK_ERRORLOG_ERRORCODE")."\n".\sdekdriver::zaDEjsonit($xml->DeliveryRequest['Msg']));
                $this->addError($ex);
            }else{
                foreach($xml->Order as $orderMess){
                    if($orderMess['ErrorCode']) {
                        $ex = new \Exception((string)$orderMess['Msg']);
                        $this->addError($ex);
                        $arErrors[(string)$orderMess['ErrorCode']] = (string)$orderMess['Msg'];
                    }
                    elseif($orderMess['DispatchNumber']){
                        $response = new OrderMake('{"entity":{"uuid":"'.(string)$orderMess['DispatchNumber'].'"}}');
                        $response->setFields($response->getDecoded());
                        $obReturn->setSuccess(true)
                                 ->setResponse($response);
                    }
                }
            }
        }
        else{
            $ex = new \Exception(GetMessage("IPOLSDEK_SEND_UNBLSND").GetMessage("IPOLSDEK_ERRORLOG_BADRESPOND").$result['code']);
            $this->addError($ex);
        }

		return $obReturn;
    }

    public function orderInfoByUuid($uid){
        $answer = new OrderInfoResult();
        $obResponse = new OrderInfo('{"entity":{"cdek_number":"'.(string)$uid.'"},"requests":[{"state":"SUCCESSFUL","TYPE":"CREATE"}]}');
        $obResponse->setFields($obResponse->getDecoded());
        $answer->setSuccess(true)
               ->setResponse($obResponse);
        return $answer;
    }

    /**
     * @param Location|null $coreLocation // Some location-based filters
     * @param string|null $pointType // 'PVZ' | 'POSTAMAT' | 'ALL'
     * @param bool|null $haveCashless
     * @param bool|null $haveCash
     * @param bool|null $allowedCod
     * @param bool|null $isDressingRoom
     * @param int|null $weightMax
     * @param int|null $weightMin
     * @param string|null $lang
     * @param bool|null $takeOnly
     * @param bool|null $isHandout
     * @param bool|null $isReception
     * @return DeliveryPointsResult
     */
    public function deliveryPoints(
        $coreLocation = null,
        $pointType = 'ALL',
        $haveCashless = null,
        $haveCash = null,
        $allowedCod = null,
        $isDressingRoom = null,
        $weightMax = null,
        $weightMin = null,
        $lang = null,
        $takeOnly = null,
        $isHandout = null,
        $isReception = null
    )
    {
        $result = new DeliveryPointsResult();
        $data = [];

        $get = 'type='.$pointType;
        if (is_object($coreLocation) && $coreLocation->getId())
            $get .= '&cityid='.$coreLocation->getId();

        if (isset($this->requestType) && $this->requestType == PointsHandler::REQUEST_TYPE_BACKUP) {
            // Da backup PVZ list call
            $request = \sdekOption::nativeReq('pvzSunc/ajax.php', array('account' => $this->account,'secure' => $this->password));
            if ($request['code'] == 200) {
                $decodedRequest = json_decode($request['result']);
                if ($decodedRequest->success) {
                    $request['result'] = $decodedRequest->data;
                } else {
                    $request['error'] = $decodedRequest->error;
                }
            }
        } else {
            $request = \sdekHelper::sendToSDEK(false, 'pvzlist/v1/xml', $get);
        }

        if ($request['code'] == 200) {
            if ($request['result'] && (!array_key_exists('error', $request) || !$request['error'])) {
                $xml = simplexml_load_string($request['result']);
                foreach ($xml as $key => $val) {
                    $tmp = [
                        'code' => (string)$val['Code'],
                        'name' => (string)$val['Name'],
                        'location' => [
                            'country_code' => (string)$val['countryCodeIso'],
                            'region_code'  => (string)$val['RegionCode'],
                            'region'       => (string)$val['RegionName'],
                            'city_code'    => (string)$val['CityCode'],
                            'city'         => (string)$val['City'],
                            'fias_guid'    => (string)$val['FiasGuid'],
                            'postal_code'  => (string)$val['PostalCode'],
                            'longitude'    => (float)$val['coordX'],
                            'latitude'     => (float)$val['coordY'],
                            'address'      => (string)$val['Address'],
                            'address_full' => (string)$val['FullAddress'],
                            ],
                        'address_comment'       => (string)$val['AddressComment'],
                        'nearest_station'       => (string)$val['NearestStation'],
                        'nearest_metro_station' => (string)$val['MetroStation'],
                        'work_time'             => (string)$val['WorkTime'],
                        'email'                 => (string)$val['Email'],
                        'note'                  => (string)$val['Note'],
                        'type'                  => (string)$val['Type'],
                        'owner_code'            => (string)$val['ownerCode'],
                        'take_only'             => ((string)$val['TakeOnly'] === 'true'),
                        'is_handout'            => ((string)$val['IsHandout'] === 'true'),
                        'is_reception'          => ((string)$val['IsReception'] === 'true'),
                        'is_dressing_room'      => ((string)$val['IsDressingRoom'] === 'true'),
                        'have_cashless'         => ((string)$val['HaveCashless'] === 'true'),
                        'have_cash'             => ((string)$val['HaveCash'] === 'true'),
                        'allowed_cod'           => ((string)$val['AllowedCod'] === 'true'),
                        'site'                  => (string)$val['Site'],
                        'fulfillment'           => ((string)$val['Fulfillment'] === 'true'),
                    ];

                    // phones
                    $phones = explode(',', (string)$val['Phone']);
                    $phones = array_map('trim', $phones);
                    foreach ($phones as $phone) {
                        $tmp['phones'][] = ['number' => $phone];
                    }

                    // office_image_list
                    if ($images = $val->OfficeImage) {
                        foreach ($images as $image) {
                            $tmp['office_image_list'][] = ['url' => (string)$image['url']];
                        }
                    }

                    // work_time_list
                    if ($workTimeY = $val->WorkTimeY) {
                        foreach ($workTimeY as $workTime) {
                            $tmp['work_time_list'][] = ['day' => (string)$workTime['day'], 'time' => (string)$workTime['periods']];
                        }
                    }

                    // work_time_exceptions
                    // TODO: add this

                    // weight limits
                    if ($weightLimit = $val->WeightLimit) {
                        $tmp['weight_min'] = (float)$weightLimit['WeightMin'];
                        $tmp['weight_max'] = (float)$weightLimit['WeightMax'];
                    }

                    // dimensions
                    if ($dimensions = $val->Dimensions) {
                        $tmp['dimensions'][] = [
                            'width'  => (float)$dimensions['width'],
                            'height' => (float)$dimensions['height'],
                            'depth'  => (float)$dimensions['depth'],
                        ];
                    }

                    $data[] = $tmp;
                }
            } else if ($request['error']) {
                $this->addError(new AppLevelException($request['error']));
            }
        } else {
            $this->addError(new AppLevelException(GetMessage('IPOLSDEK_FILE_UNBLUPDT').$request['code']."."));
        }

        try {
            $response = new \Ipolh\SDEK\Api\Entity\Response\DeliveryPoints(json_encode($data));
            $response->setSuccess(true);
            $response->setDecoded(Tools::encodeFromUTF8($response->getDecoded()));
        } catch (ApiLevelException $e) {
            $response = new ErrorResponse($e->getAnswer());
            $response->setSuccess(false);
        }
        $response->setFields($response->getDecoded());

        try {
            $result
                ->setSuccess($response->getSuccess())
                ->setResponse($response);

            if ($result->isSuccess()) {
                $result->parseFields();
            } elseif (is_a($response, ErrorResponse::class)) {
                $result->setError(new Exception('Execute fail'));
            }
        } catch (BadResponseException $e) {
            $result->setSuccess(false)->setError($e);
        } catch (AppLevelException $e) {
            $result->setSuccess(false)->setError($e);
        } catch (Exception $e) {
            $result->setSuccess(false)->setError($e);
            //echo '<pre>'.print_r($e, true).'</pre><hr>';
        } finally {
            return $result;
        }
    }

    public $goods;
    public $tarif;
    public $calcMode;
    /**
     * @param Shipment $shipment
     * @param DateTime|null $date - planned time of  departure
     * @param string|null $lang - lang for delivery info in response rus|eng|zho
     * @param int|null $currency
     * @param int|null $deliveryType - 1 = E-Shop 2 = regular shipping
     * @return CalculateListResult
     */
    public function calculateList(
        $shipment,
        $date = null,
        $lang = null,
        $currency = null,
        $deliveryType = null
    )
    {
        try {
            $obResponse = new \Ipolh\SDEK\Api\Entity\Response\CalculateList('{}');
            $obReturn   = new CalculateListResult();

            $calc = new \CalculatePriceDeliverySdek();
            $timeOut = \Ipolh\SDEK\option::get('dostTimeout');
            if(floatval($timeOut) <= 0) $timeOut = 6;
            $calc->setTimeout($timeOut);
            $calc->setAuth($this->account,$this->password);
            $calc->setSenderCityId($shipment->getFrom()->getId());
            $calc->setReceiverCityId($shipment->getTo()->getId());
            // кастомный урл калькул€ции
            if(defined('IPOLSDEK_CALCULATE_URL')){
                $calc->setCustomUrl(constant('IPOLSDEK_CALCULATE_URL'));
            }
            // $calc->setDateExecute(date()); 2012-08-20 //устанавливаем дату планируемой отправки
            //устанавливаем тариф по-умолчанию
            if(is_numeric($this->tarif))
                $calc->setTariffId($this->tarif);
            //задаЄм список тарифов с приоритетами
            else{
                $arPriority = \CDeliverySDEK::getListOfTarifs($this->tarif,$this->calcMode);

                foreach(GetModuleEvents(abstractGeneral::getMODULEID(),"onTarifPriority",true) as $arEvent)
                    ExecuteModuleEventEx($arEvent,Array(&$arPriority,$this->tarif));

                if(!count($arPriority)){
                    $err = new \stdClass();
                    $err->code = 'error';
                    $err->message = 'no_tarifs';
                    $obResponse->setErrors(array($err));
                    $obReturn->setSuccess(false)->setResponse($obResponse);
                }
                else
                    foreach($arPriority as $tarId)
                        $calc->addTariffPriority($tarId);
            }
            // $calc->setModeDeliveryId(3); //устанавливаем режим доставки
            //добавл€ем места в отправление
            // кг, см
            $shipment->getCargoes()->reset();
            while ($obCargo = $shipment->getCargoes()->getNext()){
                $dimensions = $obCargo->getDimensions();
                $calc->addGoodsItemBySize($obCargo->getWeight()/1000,$dimensions['L']/10,$dimensions['W']/10,$dimensions['H']/10);
            }

            /*
            if(array_key_exists('W',$this->goods)){
                $calc->addGoodsItemBySize($this->goods['W'],$this->goods['D_W'],$this->goods['D_H'],$this->goods['D_L']);
            }else
                foreach($this->goods as $arGood)
                    $calc->addGoodsItemBySize($arGood['W'],$arGood['D_W'],$arGood['D_H'],$arGood['D_L']);
*/
            $arServices = false;
            $arGoods = false;
            foreach(GetModuleEvents(abstractGeneral::getMODULEID(), "onCalculatePriceDelivery", true) as $arEvent){
                if(!$arGoods){
                    while ($obGood = $shipment->getCargoes()->getNext()){
                        $dims = $obGood->getDimensions();
                        $arGoods[]=array(
                            'W'   => $obGood->getWeight()/1000,
                            'D_L' => $dims['L']/10,
                            'D_W' => $dims['W']/10,
                            'D_H' => $dims['H']/10
                        );
                    }
                    if(count($arGoods) === 1){
                        $arGoods = $arGoods[0];
                    }
                }

                $arServices = ExecuteModuleEventEx($arEvent,Array($this->tarif,$this->calcMode,array(
                    'CITY_FROM' => $shipment->getFrom(),
                    'CITY_TO'   => $shipment->getTo(),
                    'GOODS'     => $arGoods
                )));
            }
            if($arServices && is_array($arServices)){
                $calc->setServices($arServices);
            }

            $arReturn = array();
            if($calc->calculate()===true){
                \Ipolh\SDEK\option::set('sdekDeadServer',false);
                $res = $calc->getResult();
                if(!is_array($res))
                    $arReturn['error'] = GetMessage('IPOLSDEK_DELIV_SDEKISDEAD');
                else{
                    $arReturn = array(
                        'success'  		  => true,
                        'price'    		  => $res['result']['price'],
                        'termMin'  		  => $res['result']['deliveryPeriodMin'],
                        'termMax'  		  => $res['result']['deliveryPeriodMax'],
                        'dateMin'  		  => $res['result']['deliveryDateMin'],
                        'dateMax'  		  => $res['result']['deliveryDateMax'],
                        'tarif'    		  => $res['result']['tariffId'],
                        'currency' 		  => $res['result']['currency'],
                        'priceByCurrency' => $res['result']['priceByCurrency']
                    );
                    if(array_key_exists('cashOnDelivery',$res['result']))
                        $arReturn['priceLimit'] = $res['result']['cashOnDelivery'];
                }
            }elseif($calc->getResult() == 'noanswer'){
                \Ipolh\SDEK\option::set('sdekDeadServer',time());
                $arReturn['error'] = GetMessage('IPOLSDEK_DEAD_SERVER');
            }elseif($calc->getResult() == 'badanswer'){
                $arReturn['error'] = GetMessage('IPOLSDEK_BAD_SERVER');
            }else{
                $err = $calc->getError();
                if(isset($err['error'])&&!empty($err))
                    foreach($err['error'] as $e)
                        $arReturn[$e['code']] = $e['text'];
            }
        } catch (Exception $e){
            $arReturn['error'] = $e->getMessage();
        }

        if(array_key_exists('success',$arReturn) && $arReturn['success']){
            $obTarifs = new \stdClass();
            $obTarifs->tariff_code = $arReturn['tarif'];
            $obTarifs->tariff_name = '';
            $obTarifs->tariff_description = '';
            $obTarifs->delivery_mode = '';
            $obTarifs->delivery_sum = $arReturn['price'];
            $obTarifs->period_min = $arReturn['termMin'];
            $obTarifs->period_max = $arReturn['termMax'];

            $obResponse->setTariffCodes(array($obTarifs));
            $obReturn->setSuccess(true)->setResponse($obResponse);
        } else {
            $_arReturn = array();
            foreach ($arReturn as $errCode => $errMess){
                $err = new \stdClass();
                $err->code = $errCode;
                $err->message = $errMess;
                $_arReturn[]=$err;
            }
            $obResponse->setErrors($_arReturn);
            $obReturn->setSuccess(false)->setResponse($obResponse);
        }

        return $obReturn;
    }

    // Common methods --------------

    /**
     * @return ExceptionCollection
     */
    public function getErrorCollection()
    {
        return $this->errorCollection;
    }

    /**
     * @param Exception $error - throwable (Exceptions)
     * @return $this
     */
    protected function addError($error)
    {
        $this->errorCollection->add($error);
        return $this;
    }

    public static function sendToSDEK($XML=false,$where=false,$get=false)
    {
        if(!$where) return false;
        if ($where !== 'ordersPackagesPrint' && $where !== 'pvzlist/v1/xml') // Damn undocumented API changes...
            $where .= '.php';
        $where .= (($get) ? "?".$get : '');

        $ch = curl_init();
        $specialUrl = defined('IPOLSDEK_BASIC_URL') ? constant('IPOLSDEK_BASIC_URL') : false;
        if($specialUrl) {
            curl_setopt($ch, CURLOPT_URL, $specialUrl . $where);
        } else {
            curl_setopt($ch, CURLOPT_URL, 'https://integration.cdek.ru/' . $where);
        }

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        if($XML){
            curl_setopt($ch, CURLOPT_POST, TRUE);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query(\sdekdriver::zajsonit(array('xml_request' => $XML))));
        }
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER,array("Content-type: application/x-www-form-urlencoded"));

        $result = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return array(
            'code'   => $code,
            'result' => $result
        );
    }

    public static function getInvoicesByDispatchNumbers($account, $secure, array $dispatchNumbers, $copiesCount = null)
    {
        $method = 'orders_print';
        $date = date("Y-m-d");
        $orderCount = count($dispatchNumbers);
        $copyCount = $copiesCount ? "CopyCount=\"$copiesCount\"" : '';

        $xml = '<?xml version="1.0" encoding="UTF-8" ?>';
        $xml .= "<OrdersPrint Date=\"$date\" Account=\"$account\" Secure=\"$secure\" OrderCount=\"$orderCount\" $copyCount>";
        foreach ($dispatchNumbers as $dispatchNumber) {
            $xml .= "<Order DispatchNumber=\"$dispatchNumber\" />";
        }
        $xml .= '</OrdersPrint>';

        return self::sendToSDEK($xml, $method);
    }
}