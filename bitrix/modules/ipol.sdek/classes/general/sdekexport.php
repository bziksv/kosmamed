<?php
use Ipolh\SDEK\StoresTable;

IncludeModuleLangFile(__FILE__);

/*
	onParseAddress
	onFormation
*/

class sdekExport extends sdekHelper{
	static $workMode    = false;
	static $orderId     = false;
	static $shipmentID  = '';
	static $workType    = false; // Standard and B24 support

	static $orderDescr  = false;
	static $requestVals = false;
	static $isLoaded    = false; // ������������ � orderDetail
	static $isEditable  = false;

	static $locStreet   = false; // ����� ������� �� ��������������

	static $subRequests = false;

	static function getAllProfiles(){
		return array('pickup','courier','postamat'); // profiler
	}

	public static function loadExportWindow($workMode, $workType = 'standard')
	{
		global $APPLICATION;				
		$dir = $APPLICATION->GetCurDir();

		$b24path = \Ipolh\SDEK\Bitrix\Tools::getB24URLs();
		
		self::$workMode = $workMode;
		self::$workType = $workType;
		
		// B24 support
		if (self::$workType == 'standard')
		{			
			if (self::$workMode == 'order')
			{
				self::$orderId = $_REQUEST['ID'];
				$reqId = self::$orderId;
			} 
			else 
			{
				self::$orderId    = $_REQUEST['order_id'];
				self::$shipmentID = $_REQUEST['shipment_id'];
				$reqId = self::$shipmentID;
			}
		}
		elseif (self::$workType == 'b24')
		{
			if (self::$workMode == 'order')
			{				
				self::$orderId = array_shift(explode('/', ltrim($dir, $b24path['ORDER'])));	
				$reqId = self::$orderId;
			}
			else
			{
				self::$orderId    = $_REQUEST['order_id'];
				self::$shipmentID = array_shift(explode('/', ltrim($dir, $b24path['SHIPMENT'])));
				$reqId = self::$shipmentID;
			}			
		}
		else
		{
			// Unsupported type
			return false;			
		}

		if(!CSaleOrder::GetByID(self::$orderId)){
		    return false;
        }

        if(self::$orderId == 0 && self::$workType == 'b24'){
		    return false;
        }

		self::$orderDescr = self::getOrderDescr();

		if(
            \Ipolh\SDEK\option::get('showInOrders') == 'N' &&
			!self::$orderDescr['info']['DELIVERY_SDEK']
		)
			return false;

		self::$requestVals = sdekdriver::GetByOI($reqId,self::$workMode);

		if(self::noSendings())
			include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/js/".self::$MODULE_ID."/orderDetail.php");
		else
			self::showExisted();
	}

	//�������� ����� ������ �� ��� id
	static $optCity = false;
	static $arTmpArLocation=false;
	public static function getOrderCity($id){ // ������������ ������ � orderDetail.php
		if(!self::$optCity)
			self::$optCity = \Ipolh\SDEK\option::get('location');
		if(!is_array(self::$arTmpArLocation)) self::$arTmpArLocation=array();

		$oCity=CSaleOrderPropsValue::GetList(array(),array('ORDER_ID'=>$id,'CODE'=>self::$optCity))->Fetch();
		if($oCity['VALUE']){
			if(is_numeric($oCity['VALUE'])){
				if(in_array($oCity['VALUE'],self::$arTmpArLocation))
					$oCity=self::$arTmpArLocation[$oCity['VALUE']];
				else{
					$cityId = self::getNormalCity($oCity['VALUE']);
					$tmpCity=CSaleLocation::GetList(array(),array("ID"=>$cityId,"CITY_LID"=>'ru'))->Fetch();
					if(!$tmpCity)
						$tmpCity=CSaleLocation::GetByID($cityId);
					self::$arTmpArLocation[$oCity['VALUE']] = (array_key_exists('CITY_NAME_LANG', $tmpCity) && $tmpCity['CITY_NAME_LANG']) ? $tmpCity['CITY_NAME_LANG'] : $tmpCity['CITY_NAME'];
					$oCity=str_replace(GetMessage('IPOLSDEK_LANG_YO_S'),GetMessage('IPOLSDEK_LANG_YE_S'),self::$arTmpArLocation[$oCity['VALUE']]);
				}
			}
			else
				$oCity=$oCity['VALUE'];
		}
		else
			$oCity=false;

		return $oCity;
	}

	public static function checkCityLocation($cityId){
		if(self::isLocation20()){
			$streetType = \Bitrix\Sale\Location\TypeTable::getList(array('filter'=>array('=CODE'=>'STREET')))->Fetch();
			if(strlen($cityId) >= 10)
				$arFilter = array('=CODE' => $cityId,'NAME.LANGUAGE_ID' => LANGUAGE_ID);
			else
				$arFilter = array('=ID' => $cityId,'NAME.LANGUAGE_ID' => LANGUAGE_ID);
			$location = \Bitrix\Sale\Location\LocationTable::getList(array('select' => array('ID','TYPE_ID','LBL'=>'NAME.NAME'),'filter' => $arFilter))->Fetch();
			if($location['TYPE_ID'] == $streetType['ID'])
				return $location['LBL'];
		}
		return false;
	}

	// ��������� ���������� � ������
	public static function getOrderDescr($oId=false,$mode=false){
		$arOrderDescr = array('info'=>array(),'properties'=>array());
		if(!$oId)
			$oId = self::$orderId;
		if(!$oId)
			return $arOrderDescr;
		if(!$mode)
			$mode = (self::$workMode) ? self::$workMode : 'order';

		if(self::isConverted()){
			// ���������� � ������
			$orderInfo = Bitrix\Sale\Order::load($oId);
			$arUChecks = array("COMMENTS","PAY_SYSTEM_ID","PAYED","PRICE","SUM_PAID","PRICE_DELIVERY","USER_DESCRIPTION","PERSON_TYPE_ID");
			if($mode == 'order'){
				$ds = $orderInfo->getDeliverySystemId();
				foreach($ds as $id){
					$arOrderDescr['info']['DELIVERY_SDEK'] = (bool)self::defineDelivery($id);
                    $arOrderDescr['info']['DELIVERY_ID'] = $id;
					if($arOrderDescr['info']['DELIVERY_SDEK']) {
					    break;
                    }
				}
				$arUChecks[]="ACCOUNT_NUMBER";
			}else{
				if(!self::$shipmentID)
					self::$shipmentID = intval($_REQUEST["shipment_id"]);
				$shipment = self::getShipmentById(self::$shipmentID);

				if($shipment){
					$arOrderDescr['info']['DELIVERY_SDEK']  = (bool)self::defineDelivery($shipment['DELIVERY_ID']);
                    $arOrderDescr['info']['DELIVERY_ID']    = $shipment['DELIVERY_ID'];
					$arOrderDescr['info']['ACCOUNT_NUMBER'] = $shipment['ACCOUNT_NUMBER'];
				}
			}

			foreach($arUChecks as $code)
				$arOrderDescr['info'][$code] = $orderInfo->getField($code);
			// ��������
			$arProps = $orderInfo->getPropertyCollection()->getArray();
			foreach($arProps['properties'] as $arProp){
				$val = array_pop($arProp['VALUE']);
				if($val)
					$arOrderDescr['properties'][$arProp['CODE']] = $val;
			}
		}else{
			// ���������� � ������
			$order = CSaleOrder::getById($oId);
			$arOrderDescr['info']['DELIVERY_SDEK'] = (strpos($order['DELIVERY_ID'],'sdek:') === 0);
            $arOrderDescr['info']['DELIVERY_ID']   = $order['DELIVERY_ID'];
			$arUChecks = array("COMMENTS","PAY_SYSTEM_ID","PAYED","ACCOUNT_NUMBER","PRICE","SUM_PAID","PRICE_DELIVERY","USER_DESCRIPTION","PERSON_TYPE_ID");
			foreach($arUChecks as $code)
				$arOrderDescr['info'][$code] = $order[$code];
			// ��������
			$orderProps=CSaleOrderPropsValue::GetOrderProps($oId);
			while($orderProp=$orderProps->Fetch())
				$arOrderDescr['properties'][$orderProp['CODE']] = $orderProp['VALUE'];
		}

		return $arOrderDescr;
	}

	static function formation(){ // ������������ ������ ������� ��� ������
		$arFormation = [
            'storeId'          => null,

            'from_loc_street'  => '',
            'from_loc_house'   => '',
            'from_loc_flat'    => '',

            'sender_company'   => '',
            'sender_name'      => '',
            'sender_phone'     => '',
            'sender_phone_add' => '',

            'seller_name'      => '',
            'seller_phone'     => '',
            'seller_address'   => '',
        ];

		// �������� ��������
        $arDeliveryConfig = (self::$orderDescr['info']['DELIVERY_SDEK']) ? self::getDeliveryConfig(self::$orderDescr['info']['DELIVERY_ID']) : self::getDeliveryConfig();

		// ������
		$paySys = \Ipolh\SDEK\option::get('paySystems');
		if(
			in_array(self::$orderDescr['info']['PAY_SYSTEM_ID'],$paySys) ||
			(self::$workMode == 'order' && self::$orderDescr['info']['PAYED'] == 'Y')
		)
			$arFormation['isBeznal'] = 'Y';

		// �����
		if(self::$orderDescr['properties']['IPOLSDEK_CNTDTARIF'])
			$arFormation['service'] = self::$orderDescr['properties']['IPOLSDEK_CNTDTARIF'];

		// �����-�����������
        if($arDeliveryConfig){
            $arFormation['departure'] = CDeliverySDEK::getDeliverySender($arDeliveryConfig);
        } else {
            $sender = self::getSQLCityBI(\Ipolh\SDEK\option::get('departure'));
            if ($sender)
                $arFormation['departure'] = $sender['SDEK_ID'];
        }

        if (empty($arFormation['departure'])) {
            $arFormation['departure'] = null;
        }

        // From.Location address, Sender and Seller
        if ($arFormation['departure']) {
            $store = StoresTable::getList([
                'select' => ['*'],
                'filter' => ['=IS_ACTIVE' => 'Y', '=FROM_LOCATION_CODE' => (int)$arFormation['departure'], '=IS_DEFAULT_FOR_LOCATION' => 'Y'],
                'order'  => ['ID' => 'ASC'],
                'limit'  => 1,
            ])->fetch();

            if (!empty($store) && is_array($store)) {
                $arFormation['storeId'] = $store['ID'];

                if ($store['IS_ADDRESS_DATA_SENT'] === 'Y') {
                    $arFormation['from_loc_street'] = $store['FROM_LOCATION_STREET'];
                    $arFormation['from_loc_house']  = $store['FROM_LOCATION_HOUSE'];
                    $arFormation['from_loc_flat']   = $store['FROM_LOCATION_FLAT'];
                }

                if ($store['IS_SENDER_DATA_SENT'] === 'Y') {
                    $arFormation['sender_company']   = $store['SENDER_COMPANY'];
                    $arFormation['sender_name']      = $store['SENDER_NAME'];
                    $arFormation['sender_phone']     = $store['SENDER_PHONE_NUMBER'];
                    $arFormation['sender_phone_add'] = $store['SENDER_PHONE_ADDITIONAL'];
                }

                if ($store['IS_SELLER_DATA_SENT'] === 'Y') {
                    $arFormation['seller_name']    = $store['SELLER_NAME'];
                    $arFormation['seller_phone']   = $store['SELLER_PHONE'];
                    $arFormation['seller_address'] = $store['SELLER_ADDRESS'];
                }
            }
        }

		// ��������
		$arProps = array();
		if(IsModuleInstalled('ipol.kladr')){
			$propCode = \Ipolh\SDEK\option::get('address');
			if($propCode && self::$orderDescr['properties'][$propCode]){
				$containment = explode(",",self::$orderDescr['properties'][$propCode]);
				if(is_numeric($containment[0])) $start = 2;
				else $start = 1;		
				if($containment[$start]){ self::$orderDescr['properties']['address'] = ''; $arProps['street'] = trim($containment[$start]);}
				if($containment[($start+1)]){ $containment[($start+1)] = trim($containment[($start+1)]); $arProps['house'] = trim(substr($containment[($start+1)],strpos($containment[($start+1)]," ")));}
				if($containment[($start+2)]){ $containment[($start+2)] = trim($containment[($start+2)]); $arProps['flat']  = trim(substr($containment[($start+2)],strpos($containment[($start+2)]," ")));}
			}
		}

		foreach(array('location','name','email','phone','address','street','house','flat','fName','sName','mName') as $prop){
			if(!(array_key_exists($prop, $arProps) && $arProps[$prop]) || $prop === 'location'){
				$propCode = \Ipolh\SDEK\option::get($prop);
				if($prop!='location' && (!self::$locStreet || $prop != 'street'))
					$arProps[$prop] = (isset($propCode) && $propCode) && array_key_exists($propCode, self::$orderDescr['properties']) ? self::$orderDescr['properties'][$propCode] : false;
				elseif($prop == 'street')
					$arProps[$prop] = self::$locStreet;
				elseif($propCode){
					self::$locStreet = self::checkCityLocation(self::$orderDescr['properties'][$propCode]);
					self::$orderDescr['properties'][$propCode] = sdekHelper::getNormalCity(self::$orderDescr['properties'][$propCode]);
					$src = sdekExport::getCity(self::$orderDescr['properties'][$propCode]);
					$orignCityId = $src;
					if(!(array_key_exists($prop, $arProps) && $arProps[$prop])){
						$arProps[$prop]=sdekExport::getCity(self::$orderDescr['properties'][$propCode]);
						$cityName = sdekExport::getOrderCity(self::$orderId);
					}
				}
			}
		}

		foreach(array('location','email','phone') as $prop){
			$arFormation[$prop] = $arProps[$prop];
			unset($arProps[$prop]);
		}
		
		if(\Ipolh\SDEK\option::get('extendName') === 'N'){
			$arFormation['name'] = $arProps['name'];
		} else {
			$arFormation['name'] = $arProps['sName']." ".$arProps['fName']." ".$arProps['mName'];
		}
		unset($arProps['name']);
		unset($arProps['sName']);
		unset($arProps['fName']);
		unset($arProps['mName']);

		// kukan phone
		if(\Ipolh\SDEK\option::get('normalizePhone') == 'Y'){
			$arFormation['oldPhone'] = $arFormation['phone'];
			$arFormation['phone'] = preg_replace("/[^0-9:#]/","",$arFormation['phone']);
			if(strlen($arFormation['phone']) > 10){
				$arCity  = sqlSdekCity::getBySId($arFormation['location']);
				if($arCity){
					$country = ($arCity['COUNTRY']) ? $arCity['COUNTRY'] : 'rus';
					
					$switcher = false;
					switch($country){
						case 'rus' : $switcher='7'; break;
						case 'blr' : $switcher='7'; break;
						case 'kaz' : $switcher='375'; break;
					}

					if(
						$switcher &&
						strpos($arFormation['phone'], $switcher) !== 0
						&& strpos($arFormation['phone'], '8') === 0
					){
						$arFormation['phone'] = $switcher.substr($arFormation['phone'],1);
					}
					$arFormation['phone'] = '+'.$arFormation['phone'];
				}
			}
		}

		// �����������
		switch(\Ipolh\SDEK\option::get('comment')){
			case 'B' : if(self::$orderDescr['info']['USER_DESCRIPTION']) $arFormation['comment'] = self::$orderDescr['info']['USER_DESCRIPTION']; break;
			case 'M' : if(self::$orderDescr['info']['COMMENTS'])         $arFormation['comment'] = self::$orderDescr['info']['COMMENTS'];         break;
		}
		
		// � ������ �� ��������
		$arFormation['deliveryP'] = self::$orderDescr['info']['PRICE_DELIVERY'];

		foreach($arProps as $prop => $value)
			$arFormation[$prop] = $value;

		// PVZ
		$PVZprop = \Ipolh\SDEK\option::get('pvzPicker');
		$PVZprop = (array_key_exists($PVZprop,self::$orderDescr['properties'])) ? self::$orderDescr['properties'][$PVZprop] : false;
		$arFormation['PVZ'] = ($PVZprop && strpos($PVZprop,"#S")) ? substr($PVZprop,strpos($PVZprop,"#S")+2):false;

		// ��������
		if(self::$workMode == 'order')
			CDeliverySDEK::setOrderGoods(self::$orderId);
		else
			CDeliverySDEK::setShipmentGoods(self::$shipmentID);

		$left = CDeliverySDEK::$orderPrice - self::$orderDescr['info']['SUM_PAID'];
		$left = ($left < 0 || $left < 0.1) ? 0 : $left;
		$arFormation['toPay'] = (self::$workMode == 'order') ? $left : CDeliverySDEK::$orderPrice;

        // Used in OrderSender delivery calculation (extCountDeliv) as base for insurance
        $arFormation['estimatedCost'] = CDeliverySDEK::$orderPrice;

		$arFormation['GABS'] = array(
			"D_L" => CDeliverySDEK::$goods['D_L'],
			"D_W" => CDeliverySDEK::$goods['D_W'],
			"D_H" => CDeliverySDEK::$goods['D_H'],
			"W" => CDeliverySDEK::$goods['W']
		);
		
		// NDS
		$arFormation['NDSGoods']    = \Ipolh\SDEK\option::get('NDSGoods');
		$arFormation['NDSDelivery'] = \Ipolh\SDEK\option::get('NDSDelivery');
		
		// ���� ��������
		$arFormation['deliveryDate'] = false;

		foreach(GetModuleEvents(self::$MODULE_ID, "onFormation", true) as $arEvent)
			ExecuteModuleEventEx($arEvent,Array(&$arFormation,self::$orderId,self::$orderDescr));

		return $arFormation;
	}

	static function parseAddress(&$fields,$forse=false){
		$parsed = false;
		foreach(GetModuleEvents(self::$MODULE_ID, "onParseAddress", true) as $arEvent){
			ExecuteModuleEventEx($arEvent,Array(&$fields));
			$parsed = true;
		}
		if(!$parsed && $forse && !($fields['street'] && $fields['house'])){
			$arAdress=array();
			$adrStr=explode(',',$fields['address']);
			$arDictionary = array(
				'STREET'   => array('len' => 20,'clr' => false),
				'HOUSE'    => array('len' => 2, 'clr' => true),
				'ENTRANCE' => array('len' => 3, 'clr' => true),
				'KORP'     => array('len' => 3, 'clr' => true),
				'FLOOR'    => array('len' => 3, 'clr' => true),
				'FLAT'     => array('len' => 5, 'clr' => true),
				'CITY'	   => array('len' => 3, 'clr' => false)
			);
			foreach($adrStr as $key => $addr){
				$addr = trim($addr);
				if(!$addr) unset($adrStr[$key]);
				if($key == 0 && is_numeric($addr)) unset($adrStr[$key]); // ������

				foreach($arDictionary as $key => $descr)
					if(!$arAdress[$key])
						for($i=1;$i<$descr['len'];$i++)
							if(self::strps($addr,GetMessage('IPOLSDEK_ADRSUFFER_'.$key.$i))!==false){
								$arAdress[$key]=($descr['clr']) ? self::ctAdr($addr,strlen(GetMessage('IPOLSDEK_ADRSUFFER_'.$key.$i))) : $addr;
								unset($adrStr[$key]);
							}

				if(!$arAdress['HOUSE']){//���
					if(self::strps($addr,GetMessage('IPOLSDEK_ADRSUFFER_HOUSE2'))!==false&&self::strps($addr,GetMessage('IPOLSDEK_ADRSUFFER_HOUSE2'))<2)
						{$arAdress['HOUSE']=self::ctAdr($addr,2);unset($adrStr[$key]);}
					if(self::strps($addr,GetMessage('IPOLSDEK_ADRSUFFER_HOUSE3'))===0&&self::strps($addr,GetMessage('IPOLSDEK_ADRSUFFER_HOUSE3'))<2)
						{$arAdress['HOUSE']=self::ctAdr($addr,2);unset($adrStr[$key]);}
				}
			}

			if(count($adrStr)==1 && !$arAdress['STREET'])
				$arAdress['STREET']=trim(array_pop($adrStr));
			elseif(count($adrStr)==1&&!$arAdress['CITY']){
				$needle=array_pop($adrStr);
				if(!$arAdress['HOUSE']&&preg_match('/[\d]+/',$needle))
					$arAdress['HOUSE']=$needle;
				else
					$arAdress['CITY']=$needle;
			}

			if(count($adrStr)==2&&!$arAdress['STREET']&&!$arAdress['CITY']){
				$arAdress['STREET']=trim(array_pop($adrStr));
				if(!$arAdress['HOUSE'])
					$arAdress['HOUSE']=array_pop($adrStr);
				else
					$arAdress['CITY']=array_pop($adrStr);
			}

			if(count($adrStr)>3&&!$arAdress['STREET'])
				$arAdress['STREET']=implode(', ',$addr);

			if($arAdress['KORP'])
				$arAdress['HOUSE'] .= "/".$arAdress['KORP'];

			$fields['street'] = $arAdress['STREET'];
			$fields['house']  = $arAdress['HOUSE'];
			$fields['flat']   = $arAdress['FLAT'];			
		}
	}

	// ��� �������� ������
	static function ctAdr($wt,$n){return trim(substr(trim($wt),$n));}
	static function strps($wr,$wat){return strpos(strtolower($wr),strtolower($wat));}

	static function loadGoodsPack($packs){ // ����������� �������� �� �������
		CDeliverySDEK::$goods = array();
		foreach($packs as $pack){
			$arGabs = explode(' x ',$pack['gabs']);
			if(count($arGabs) != 3) continue;
			CDeliverySDEK::$goods[] = array(
				'D_W' => $arGabs[0],
				'D_L' => $arGabs[1],
				'D_H' => $arGabs[2],
				'W'   => $pack['weight']
			);
		}
	}

	// ������ ��������� ������� �� ��������� ����������
	static function countGoods($params){
		$arGCatalog = array();
		if(!cmodule::includeModule('catalog')) return;
		if(!count($params['goods'])){
			echo "G{0,0,0,}G";
			return;
		}
		$gC = CCatalogProduct::GetList(array(),array('ID'=>array_keys($params['goods'])));
		while($element=$gC->Fetch())
			$arGCatalog[$element['ID']] = array(
				'WEIGHT' => $element['WEIGHT'],
				'LENGTH' => $element['LENGTH'],
				'WIDTH'  => $element['WIDTH'],
				'HEIGHT' => $element['HEIGHT']
			);

		$arGoods = array();
		foreach($params['goods'] as $goodId => $cnt)
			$arGoods[$goodId] = array(
				'ID'		    => $goodId,
				'PRODUCT_ID'    => $goodId,
				'QUANTITY'      => $cnt,
				'CAN_BUY'       => 'Y',
				'DELAY'         => 'N',
				'SET_PARENT_ID' => false,
				'WEIGHT'		=> $arGCatalog[$goodId]['WEIGHT'],
				'DIMENSIONS' 	=> array(
					'LENGTH' => $arGCatalog[$goodId]['LENGTH'],
					'WIDTH'  => $arGCatalog[$goodId]['WIDTH'],
					'HEIGHT' => $arGCatalog[$goodId]['HEIGHT']
				),
			);
		CDeliverySDEK::setGoods($arGoods);
		echo "G{".CDeliverySDEK::$goods['D_L'].",".CDeliverySDEK::$goods['D_W'].",".CDeliverySDEK::$goods['D_H'].",}G";
	}

	static function getAllTarifsToCount($arParams){
		$tarifs = self::getTarifList(array('fSkipCheckBlocks'=>true));
		$tarifDescr = self::getExtraTarifs();
		$rezTarifs = array();
		foreach($tarifs as $type => $arTarifs){
			$arTarifs = self::arrVals($arTarifs);
			foreach($arTarifs as $id){
				if($tarifDescr[$id]['SHOW'] == 'N') continue;
				$rezTarifs[$type][$id]=$tarifDescr[$id]['NAME'];
			}
		}

		if($arParams['isdek_action'])
			echo json_encode(self::zajsonit($rezTarifs));
		else
			return $rezTarifs;
	}

	// ���������� ��������
	public static function extCountDeliv($arParams){
		if(!$arParams['orderId'] || !$arParams['cityTo'] || !$arParams['tarif'])
			return false;
		if(!$arParams['delivery'])
            $arParams['delivery'] = false;

		self::setCalcData($arParams);
		$arBlockedTarifs = array();
		$curProfile = false;
		foreach(self::getAllProfiles() as $tarifName)
			if(!in_array($arParams['tarif'],CDeliverySDEK::getTarifList(array('type'=>$tarifName,'answer'=>'array','fSkipCheckBlocks'=>true))))
				$arBlockedTarifs[] = $tarifName;
			else
				$curProfile = $tarifName;
		$dost = sdekdriver::getDelivery(true,$arParams['delivery']);
		$arReturn = array('success' => false, 'tarif' => $arParams['tarif']);
		if($dost && $dost['ACTIVE'] && self::checkProfileActive($curProfile)){
			$arOrder = array(
				'CITY_TO_ID'     =>$arParams['cityTo'],
				'FORBIDDEN'      => $arBlockedTarifs,
				'SDEK_CITY_FROM' => $arParams['cityFrom'],
				'SDEK_ACCOUNT'   => $arParams['account'],
				'PERSON_TYPE_ID' => $arParams['person'],
				'PAY_SYSTEM_ID'  => $arParams['paysystem']
			);

            // Case: more than one profile of the same type, so we forward delivery ID to cntDelivsConverted
            if ($arParams['delivery'] && sdekHelper::defineDelivery($arParams['delivery']) === $curProfile) {
                $arOrder['DELIVERY'] = $arParams['delivery'];
            }
			
			if($arParams['GABS']['D_L'] && $arParams['GABS']['D_W'] && $arParams['GABS']['D_H']){
				$arOrder['DIMS'] = array(
					"WIDTH"  => $arParams["GABS"]["D_W"] * 10,
					"HEIGHT" => $arParams["GABS"]["D_H"] * 10,
					"LENGTH" => $arParams["GABS"]["D_L"] * 10,
				);
				$arOrder["WEIGHT"] = $arParams['GABS']["W"];
				$arOrder["PRICE"]  = $arParams['price'];
			}

			$arReturn = CDeliverySDEK::countDelivery($arOrder);

			if($arReturn['success']){
				$arReturn['price']       = strip_tags($arReturn['price']);
				if(self::diffPrice($arReturn['price']))
					$arReturn['sourcePrice'] = CDeliverySDEK::$lastCnt;
			} else { // bad count from existed delivery - direct calculation
				$_arReturn = self::directRequestDelivery($arParams);
				if($_arReturn['success']){
					$arReturn['success']     = true;
					$arReturn['termMin']     = $_arReturn['termMin'];
					$arReturn['termMax']     = $_arReturn['termMax'];
					$arReturn['termMax']     = $_arReturn['termMax'];
					$arReturn['sourcePrice'] = $_arReturn['price'];
				}
			}
			$arReturn['tarif'] = $arParams['tarif'];
		}else{ // no delivery - direct calculation
			$_arReturn = self::directRequestDelivery($arParams);
			if($_arReturn['success']){
				$arReturn['success']     = true;
				$arReturn['termMin']     = $_arReturn['termMin'];
				$arReturn['termMax']     = $_arReturn['termMax'];
				$arReturn['termMax']     = $_arReturn['termMax'];
				$arReturn['sourcePrice'] = $_arReturn['price'];
			}
		}

		if($arParams['isdek_action'])
			echo json_encode(self::zajsonit($arReturn));
		else
			return $arReturn;
	}
	
	protected static function directRequestDelivery($arParams){
		$cityTo = self::getCity($arParams['cityTo'],true);
		CDeliverySDEK::$sdekCity = $cityTo['SDEK_ID'];
		if($arParams['account']){
			CDeliverySDEK::setAuth(self::defineAuth($arParams['account']));
		}else{
			CDeliverySDEK::setAuth(self::defineAuth(array('COUNTRY'=>($cityTo['COUNTRY']) ? $cityTo['COUNTRY'] : 'rus')));
		}
		if($arParams['cityFrom']){
			CDeliverySDEK::$sdekSender = $arParams['cityFrom'];
		}

		return CDeliverySDEK::calculateDost($arParams['tarif']);
	}

	private static function diffPrice($got){
		return (CDeliverySDEK::$lastCnt != floatval(str_replace(array(" ","&nbsp;"),"",$got)));
	}

	// ��������� ��������� ��� ������� ��������
	private static function setCalcData($arParams){
		if(!array_key_exists('packs',$arParams) || !$arParams['packs']){
			if(!array_key_exists('GABS',$arParams)){
				if($arParams['mode'] == 'order')
					CDeliverySDEK::setOrderGoods($arParams['orderId']);
				else
					CDeliverySDEK::setShipmentGoods($arParams['shipment'],$arParams['orderId']);
			}else
				CDeliverySDEK::$goods = $arParams['GABS'];
		}else
			self::loadGoodsPack($arParams['packs']);

		CDeliverySDEK::$sdekSender = ($arParams['cityFrom']) ? $arParams['cityFrom'] : self::getHomeCity();
		CDeliverySDEK::$preSet = $arParams['tarif'];
	}

	// ������ ������� / ��������
	public static function noSendings(){
		self::$subRequests = array();
		if(!self::isConverted() || self::$requestVals || !self::canShipment())
			return true;
		if(self::$workMode == 'shipment'){
			$req = sdekdriver::GetByOI(self::$orderId,'order');
			if($req)
				self::$subRequests = array($req);
		}else{
			$shipments = Bitrix\Sale\Shipment::getList(array('filter'=>array('ORDER_ID' => self::$orderId)));
			$unsended = array();
			while($element=$shipments->Fetch()){
				
				// Skip system shipment
				if ($element['SYSTEM'] == 'Y')
					continue;
				
				$req = sdekdriver::GetByOI($element['ID'],'shipment');
				if($req)
					self::$subRequests[]=$req;
				else
					$unsended[] = $element['ID'];
			}
			if(count(self::$subRequests))
				self::$subRequests['unsended'] = $unsended;
		}
		return !(bool)count(self::$subRequests);
	}

	// ����������� ���� ������������ ������ ����� ������
	public static function showExisted()
    {
        \Ipolh\SDEK\Bitrix\Tools::initJQuery();

		$unsended = false;
		if(array_key_exists('unsended',self::$subRequests)){
			$unsended = self::$subRequests['unsended'];
			unset(self::$subRequests['unsended']);
		}
		?>
			<style>
				.IPOLSDEK_sendedTable{
					background-color: #FFFFFF;
					border: 1px solid #DCE7ED;
					width: 100%;
					margin: 5px 0px;
					padding: 5px;
				}
			</style>
			<script>
			var IPOLSDEK_existedInfo = {
				load: function(){
					if($('#IPOLSDEK_btn').length) return;
					
					/* B24 support */
					if ($('#IPOLSDEK_btn_container').length)
					{
						$('#IPOLSDEK_btn_container').prepend("<a href='javascript:void(0)' onclick='IPOLSDEK_existedInfo.showWindow()' class='ui-btn ui-btn-light-border ui-btn-icon-edit' style='margin-left:12px;' id='IPOLSDEK_btn'><?=GetMessage('IPOLSDEK_JSC_SOD_BTNAME')?></a>");		
					}
					
					/* Standard */
					if ($('.adm-detail-toolbar').find('.adm-detail-toolbar-right').length)
					{
						$('.adm-detail-toolbar').find('.adm-detail-toolbar-right').prepend("<a href='javascript:void(0)' onclick='IPOLSDEK_existedInfo.showWindow()' class='adm-btn' id='IPOLSDEK_btn'><?=GetMessage('IPOLSDEK_JSC_SOD_BTNAME')?></a>");
					}
				},
				/* ���� */
				wnd: false,
				showWindow: function(){
					if(!IPOLSDEK_existedInfo.wnd){
						var html=$('#IPOLSDEK_wndOrder').html();
						$('#IPOLSDEK_wndOrder').html('');
						IPOLSDEK_existedInfo.wnd = new BX.CDialog({
							title: "<?=GetMessage('IPOLSDEK_JSC_SOD_WNDTITLE')?>",
							content: html,
							icon: 'head-block',
							resizable: true,
							draggable: true,
							height: '350',
							width: '400',
							buttons: []
						});
					}
					IPOLSDEK_existedInfo.wnd.Show();
				},
				print: function(oId){
					$('#IPOLSDEK_print_'+oId).attr('disabled','true');
					$('#IPOLSDEK_print_'+oId).val('<?=GetMessage("IPOLSDEK_JSC_SOD_LOADING")?>');
					$.ajax({
						url  : "/bitrix/js/<?=self::$MODULE_ID?>/ajax.php",
						type : 'POST',
						data : {
							isdek_action : 'printOrderInvoice',
                            isdek_token  : '<?=sdekHelper::getModuleToken()?>',
							oId  : oId,
							mode : '<?=(self::$workMode == 'shipment') ? 'order' : 'shipment'?>'
						},
						dataType : 'json',
						success  : function(data){
							$('#IPOLSDEK_print_'+oId).removeAttr('disabled');
							$('#IPOLSDEK_print_'+oId).val('<?=GetMessage("IPOLSDEK_JSC_SOD_PRNTSH")?>');
							if(data.result == 'ok')
								for (var i = 0; i < data.files.length; i++) {
									window.open('/upload/<?=self::$MODULE_ID?>/'+data.files[i]);
								}
							else
								alert(data.error);
						}
					});
				},
				shtrih: function(oId){
					$('#IPOLSDEK_shtrih_'+oId).attr('disabled','true');
					$('#IPOLSDEK_shtrih_'+oId).val('<?=GetMessage("IPOLSDEK_JSC_SOD_LOADING")?>');
					$.ajax({
						url  : "/bitrix/js/<?=self::$MODULE_ID?>/ajax.php",
						type : 'POST',
						data : {
							isdek_action : 'printOrderShtrih',
                            isdek_token  : '<?=sdekHelper::getModuleToken()?>',
							oId  : oId,
							mode : '<?=(self::$workMode == 'shipment') ? 'order' : 'shipment'?>'
						},
						dataType : 'json',
						success  : function(data){
							$('#IPOLSDEK_shtrih_'+oId).removeAttr('disabled');
							$('#IPOLSDEK_shtrih_'+oId).val('<?=GetMessage("IPOLSDEK_JSC_SOD_SHTRIH")?>');
							if(data.result == 'ok')
								for (var i = 0; i < data.files.length; i++) {
									window.open('/upload/<?=self::$MODULE_ID?>/'+data.files[i]);
								}
							else
								alert(data.error);
						}
					});
				},

				curDelete: false,
				delete: function(oId,status){
					if(IPOLSDEK_existedInfo.curDelete != false)
						return;
					$('#IPOLSDEK_delete_'+oId).attr('disabled','true');
					IPOLSDEK_existedInfo.curDelete = oId;
					if(status == 'NEW' || status == 'ERROR' || status == 'DELETE'){
						if(confirm("<?=GetMessage('IPOLSDEK_JSC_SOD_IFDELETE')?>"))
							$.post(
								"/bitrix/js/<?=self::$MODULE_ID?>/ajax.php",
								{isdek_action:'delReqOD',isdek_token:'<?=sdekHelper::getModuleToken()?>',oid:oId,mode:'<?=(self::$workMode == 'order') ? 'shipment' : 'order'?>'},
								function(data){
									IPOLSDEK_existedInfo.onDelete(data);
								}
							);
					}else{
						if(status == 'OK'){
							if(confirm("<?=GetMessage('IPOLSDEK_JSC_SOD_IFKILL')?>"))
								$.post(
									"/bitrix/js/<?=self::$MODULE_ID?>/ajax.php",
									{isdek_action:'killReqOD',isdek_token:'<?=sdekHelper::getModuleToken()?>',oid:oId,mode:'<?=(self::$workMode == 'order') ? 'shipment' : 'order'?>'},
									function(data){
										if(data.indexOf('GD:')===0)
											IPOLSDEK_existedInfo.onDelete(data.substr(3));
										else{
											alert(data);
											$('#IPOLSDEK_print_'+IPOLSDEK_existedInfo.curDelete).removeAttr('disabled');
										}
									}
								);
							else {
								$('#IPOLSDEK_delete_'+oId).removeAttr('disabled');
							}
						}
					}
				},
				onDelete: function(data){
					alert(data);
					$('#IPOLSDEK_sT_'+IPOLSDEK_existedInfo.curDelete).replaceWith('');
					if($('.IPOLSDEK_sendedTable').length == 0)
						document.location.reload();
					IPOLSDEK_existedInfo.curDelete = false;
				}
			};
			$(document).ready(IPOLSDEK_existedInfo.load);
			</script>
			<div style='display:none' id='IPOLSDEK_wndOrder'>
				<div><?=GetMessage('IPOLSDEK_JSC_NOWND_'.self::$workMode)?></div>
				<?php foreach(self::$subRequests as $request) { ?>
					<table class='IPOLSDEK_sendedTable' id='IPOLSDEK_sT_<?=$request['ORDER_ID']?>'>
						<tr>
							<?php if(self::$workMode == 'shipment') { ?>
								<td><?=GetMessage("IPOLSDEK_JSC_SOD_order")?></td>
								<td>
									<a target='_blank' href='<?=self::makePathForEditing('order', self::$workType, 
									$request["ORDER_ID"]);?>'><?=$request['ORDER_ID']?></a>
							<?php } else { ?>
								<td><?=GetMessage("IPOLSDEK_JSC_SOD_shipment")?></td>
								<td>
									<a target='_blank' href='<?=self::makePathForEditing('shipment', self::$workType, 
									self::$orderId, $request["ORDER_ID"]);?>'><?=$request["ORDER_ID"]?></a>
							<?php } ?>
							</td>
						</tr>
						<tr><td><?=GetMessage('IPOLSDEK_JS_SOD_STATUS')?></td><td><?=$request['STATUS']?></td></tr>
						<tr><td colspan='2'><small><?=GetMessage('IPOLSDEK_JS_SOD_STAT_'.$request['STATUS'])?></small></td></tr>
						<?php if($request['SDEK_ID']) { ?><tr><td><?=GetMessage('IPOLSDEK_JS_SOD_SDEK_ID')?></td><td><?=$request['SDEK_ID']?></td></tr><?php } ?>
						<?php if($request['MESS_ID']) { ?><tr><td><?=GetMessage('IPOLSDEK_JS_SOD_MESS_ID')?></td><td><?=$request['MESS_ID']?></td></tr><?php } ?>
						<?php if($request['SDEK_ID']) { ?><tr><td colspan='2'><a href="<?=\Ipolh\SDEK\SDEK\Tools::getTrackLink($request['SDEK_ID'])?>" target="_blank"><?=GetMessage('IPOLSDEK_JSC_SOD_FOLLOW')?></a></td></tr><?php } ?>
						<tr><td colspan='2'><hr></td></tr>
						<tr><td colspan='2'>
							<?php if(in_array($request['STATUS'], array('OK', 'ERROR', 'NEW', 'DELETD'))) { ?>
							<input id='IPOLSDEK_delete_<?=$request['ORDER_ID']?>' value="<?=GetMessage('IPOLSDEK_JSC_SOD_DELETE')?>" onclick="IPOLSDEK_existedInfo.delete(<?=$request['ORDER_ID']?>,'<?=$request['STATUS']?>'); return false;" type="button">&nbsp;&nbsp;
							<?php } ?>
							<?php if($request['STATUS'] == 'OK') { ?>
							<input id='IPOLSDEK_print_<?=$request['ORDER_ID']?>' value="<?=GetMessage('IPOLSDEK_JSC_SOD_PRNTSH')?>" onclick="IPOLSDEK_existedInfo.print(<?=$request['ORDER_ID']?>); return false;" type="button">&nbsp;&nbsp;
							<input id='IPOLSDEK_shtrih_<?=$request['ORDER_ID']?>' value="<?=GetMessage('IPOLSDEK_JSC_SOD_SHTRIH')?>" onclick="IPOLSDEK_existedInfo.shtrih(<?=$request['ORDER_ID']?>); return false;" type="button">&nbsp;&nbsp;
							<?php } ?>
						</td></tr>
					</table>
				<?php } ?>
				<?php if($unsended) { ?>
				<div>
					<?=GetMessage('IPOLSDEK_JSC_NOWND_noSended')?>
					<?php foreach($unsended as $shipmintId) { ?><a target='_blank' href='<?=self::makePathForEditing('shipment', self::$workType,
									self::$orderId, $shipmintId);?>'><?=$shipmintId?></a>&nbsp;
					<?php } ?>
				</div>
				<?php } ?>
			</div>
		<?php
	}

	static function formatCurrency($params){
		if(cmodule::includeModule('currency')){
			$from = ($params['FROM']) ? $params['FROM'] : CCurrency::GetBaseCurrency();
			$into = ($params['TO'])   ? $params['TO']   : CCurrency::GetBaseCurrency();
			if(
                \Ipolh\SDEK\option::get('noteOrderDateCC') == 'Y' &&
				array_key_exists('orderId',$params) &&
				$params['orderId'] &&
				cmodule::includeModule('sale')
			){
				$orderSelf = CSaleOrder::GetById($params['orderId']);
				$date = $orderSelf['DATE_INSERT'];
			}else
				$date = false;
			$itog = CCurrencyRates::ConvertCurrency($params['SUM'],$from,$into,$date);
			if($params['FORMAT'])
				$itog = CCurrencyLang::CurrencyFormat($itog,$into,true);
			if(array_key_exists('isdek_action',$params) && $params['isdek_action'] == __function__)
				echo json_encode(self::zajsonit(array('VALUE' => $itog, 'WHERE' => $params['WHERE'])));
			else
				return $itog;
		} 
	}

	// ��������� ������ ���������
    public static function getActiveAccounts($params){
	    $arReturn = array('success' => 'Y');

	    if(self::isAdmin('R')){
	        $accounts = sqlSdekLogs::getAccountsList(true);
            $basic    = self::getBasicAuth(true);
            $country  = false;
            $delivery = false;
            if($params['COUNTRY']){
                $svd = self::getCountryOptions();
                if(array_key_exists($params['COUNTRY'],$svd)){
                    $country  = $svd[$params['COUNTRY']]['acc'];
                }
            }
            if(array_key_exists('DELIVERY',$params) && $params['DELIVERY']){
                $config = self::getDeliveryConfig($params['DELIVERY']);
                if(!empty($config) && array_key_exists('VALUE',$config['ACCOUNT']) && $config['ACCOUNT']['VALUE']){
                    $delivery = $config['ACCOUNT']['VALUE'];
                }
            }

            foreach ($accounts as $id => $vals){
                $accounts[$id]['COUNTRY']  = ($country == $id);
                $accounts[$id]['BASIC']    = ($basic == $id);
                $accounts[$id]['DELIVERY'] = ($delivery == $id);
            }

            if(empty($accounts)){
                $arReturn = array('success' => 'N', 'error' => GetMessage('IPOLSDEK_JSC_SOD_noAccounts'));
            }else{
                $arReturn = array('success' => 'Y', 'accounts' => $accounts);
            }
        }
        else
        {
            $arReturn = array('success' => 'N', 'error' => GetMessage('IPOLSDEK_ERR_NORIGHTS'));
        }

        if(array_key_exists('isdek_action',$_POST)){
	        echo json_encode(self::zajsonit($arReturn));
        } else {
            return $arReturn;
        }
    }
	
	// ���������� ��� �� ��������
	public static function sortPVZ($pvz1,$pvz2){
		$pvz1['Name'] = str_replace(array('"',"'",'�'),"",$pvz1['Name']);
		$pvz2['Name'] = str_replace(array('"',"'",'�'),"",$pvz2['Name']);
		return ($pvz1['Name'] < $pvz2['Name']) ? -1 : 1;
	}

	// LEGACY
	static function countAlltarifs($arParams){
		return array();
	}
	static function htmlTaritfList($params){
		echo "Update the module";
	}
}
?>