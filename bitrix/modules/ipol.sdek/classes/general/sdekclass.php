<?php

IncludeModuleLangFile(__FILE__);

/*
	onGoodsToRequest - изменение товаров в заказе
	requestSended - отправка заявки
*/

class sdekdriver extends sdekHelper{

	protected static $autoload = false;
	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
		                            Формирование заявок на заказ
		== sendOrderRequest == -> == genOrderXML == ->  == getPacks == | == getGoods == -> getGoodsArray // == getMessId ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/

	static function getMessId(){
		$mesId=(int)\Ipolh\SDEK\option::get('schet');
		\Ipolh\SDEK\option::set('schet',++$mesId);
		return $mesId;
	}

	// сборка упаковки в заказ
	public static function getPacks($oId,$mode,$orderParams)
    {
        $arPacks = array();
        $minEnsure
                 = ((array_key_exists('minVats', $orderParams) && $orderParams['minVats'] == 'Y') || (\Ipolh\SDEK\option::get('noVats') == 'Y'));

        $arMarks = false;
        if (array_key_exists('marks', $orderParams)) {
            $arMarks = $orderParams['marks'];
        }

        if (array_key_exists('packs', $orderParams) && $orderParams['packs']) {
            foreach ($orderParams['packs'] as $id => $content) {
                $marks        = ($arMarks && array_key_exists($id, $arMarks)) ? $arMarks[$id] : false;
                $gabs         = explode(' x ', $content['gabs']);
                $arPacks[$id] = array(
                    'WEIGHT' => $content['weight'] * 1000,
                    'LENGTH' => $gabs[0],
                    'WIDTH' => $gabs[1],
                    'HEIGHT' => $gabs[2],
                    'GOODS' => self::getGoods($oId, $mode, $content['weight'], $content['goods'], $minEnsure, $marks)
                );
            }
        } else {
            $marks = ($arMarks && array_key_exists(1, $arMarks)) ? $arMarks[1] : false;
            $arPacks[1] = array(
                'WEIGHT' => $orderParams["GABS"]['W'] * 1000,
                'LENGTH' => $orderParams["GABS"]['D_L'],
                'WIDTH' => $orderParams["GABS"]['D_W'],
                'HEIGHT' => $orderParams["GABS"]['D_H'],
                'GOODS' => self::getGoods($oId, $mode, $orderParams["GABS"]['W'], false, $minEnsure,$marks)
            );
        }
		return $arPacks; // вес - граммы, стороны - см
	}

	public static function getGoodsArray($orderId,$shipmentID=false){
		if(!class_exists('CDeliverySDEK')) return array();
		$arGoods = CDeliverySDEK::getBasketGoods(array("ORDER_ID" => $orderId));
		$arGoods = CDeliverySDEK::handleBitrixComplects($arGoods);
		if($shipmentID && self::canShipment())
			$arGoods = CDeliverySDEK::filterShipmentGoods($shipmentID,$arGoods);
		$cntDims = CDeliverySDEK::getGoodsDimensions($arGoods);
		foreach($cntDims['goods'] as $key => $dimVals){
			$arGoods[$key]['WEIGHT']     = $dimVals['W']; 
			$arGoods[$key]['DIMENSIONS'] = array(
												'LENGTH' => $dimVals['D_L'],
												'WIDTH'  => $dimVals['D_W'],
												'HEIGHT' => $dimVals['D_H'],
											);
		}
		$hasIblock  = cmodule::includemodule('iblock');
		$optARticul = \Ipolh\SDEK\option::get('articul');
        $sites = [];
		foreach($arGoods as $key => $good){
            $sites[] = $good['LID'];
			$articul = false;
			if($optARticul && $hasIblock){
				$gd = CIBlockElement::GetList(array(),array('ID'=> $good['PRODUCT_ID'],'LID'=>$good['LID'],'IBLOCK_ID'=>CIBlockElement::GetIBlockByID($good['PRODUCT_ID'])),false,false,array('ID','PROPERTY_'.strtoupper ($optARticul)))->Fetch();
				if($gd && $gd["PROPERTY_".strtoupper ($optARticul)."_VALUE"])
					$articul = $gd["PROPERTY_".strtoupper ($optARticul)."_VALUE"];
				// parent articul
				if(!$articul && \Ipolh\SDEK\option::get('getParentArticul') == 'Y' && cmodule::includeModule('catalog')){
					$parent = CCatalogSku::GetProductInfo($good['PRODUCT_ID']);
					if($parent){
						$gd = CIBlockElement::GetList(array(),array('ID'=> $parent['ID'],'LID'=>$good['LID']),false,false,array('ID','PROPERTY_'.strtoupper ($optARticul)))->Fetch();
						if($gd && $gd["PROPERTY_".strtoupper ($optARticul)."_VALUE"])
							$articul = $gd["PROPERTY_".strtoupper ($optARticul)."_VALUE"];
					}
				}
			}
			$arGoods[$key]['ARTICUL'] = ($articul)?$articul:$good['PRODUCT_ID'];
			
			$hasCatalog = cmodule::includemodule('catalog');
			$arGoods[$key]['VAT'] = false;
			if($hasCatalog && \Ipolh\SDEK\option::get('NDSUseCatalog') == 'Y'){
				$arAllowedV = array('0.00', '0.05', '0.07', '0.10', '0.12', '0.18', '0.20');
                $good['VAT_RATE'] = number_format((float)$good['VAT_RATE'], 2, ".", "");
				$gd = CCatalogProduct::GetByID($good['PRODUCT_ID']);
				if($gd && $gd['VAT_ID'] && in_array((string)$good['VAT_RATE'],$arAllowedV))
					$arGoods[$key]['VAT'] = $good['VAT_RATE'];
			}
		}

        $modeAddUrls = 'IPOLSDEK_ADDGOODSURLS';
        if ((defined($modeAddUrls) && constant($modeAddUrls) === true) && !empty($sites)) {
            $servers = [];
            foreach ($sites as $val) {
                $dbSite = CSite::GetByID($val);
                $site = $dbSite->Fetch();

                if (is_array($site) && !empty($site['SERVER_NAME'])) {
                    $servers[$val] = ((CMain::IsHTTPS()) ? "https://" : "http://").$site['SERVER_NAME'];
                }
            }

            foreach ($arGoods as $key => $good) {
                $arGoods[$key]['FULL_DETAIL_PAGE_URL'] = (array_key_exists($good['LID'], $servers) && !empty($good['DETAIL_PAGE_URL'])) ? $servers[$good['LID']].$good['DETAIL_PAGE_URL'] : '';
            }
        }

        // TN VED code
        $feacn = \Ipolh\SDEK\option::get('feacn');

        if (!empty($arGoods)) {
            \Ipolh\SDEK\Bitrix\Handler\goodsPicker::addBasketGoodProperties($arGoods, [$feacn]);
        }

		\Ipolh\SDEK\Bitrix\Handler\goodsPicker::addGoodsQRs($arGoods,$orderId);

		return $arGoods;
	}

	//сборка товаров в заказ
	protected static function getGoods($oId,$mode,$givenWeight,$given=false,$minEnsure=false,$arMarks=false){
		$givenWeight *= 1000;
		if($mode == 'order' || !self::canShipment())
			$arGoods = self::getGoodsArray($oId);
		else{
			$orderId = self::oIdByShipment($oId);
			$arGoods = self::getGoodsArray($orderId,$oId);
		}
		$arTG = array();

		$ttlWeight = 0;

        // TN VED code
        $feacn = \Ipolh\SDEK\option::get('feacn');

		foreach($arGoods as $key => $good){
			$doPack = ($given && array_key_exists($key,$given));
			if(!$given || $doPack){
				$cnt = ($given) ? $given[$key] : $good['QUANTITY'];
				$weight = ($good['WEIGHT']) ? $good['WEIGHT'] * 1000 : 1000;
				$marks  = ($arMarks && array_key_exists($key,$arMarks) && $arMarks[$key]['id'] == $good['PRODUCT_ID']) ? $arMarks[$key]['marks'] : false;
				$arTG[$key] = array(
					'price'    => $good['PRICE'],
					'cstPrice' => ($minEnsure) ? 1 : $good['PRICE'],
					'weight'   => $weight,
					'quantity' => $cnt,
					'name'     => $good['NAME'],
					'articul'  => $good['ARTICUL'],
					'id'	   => $good['PRODUCT_ID'],
					'vat'	   => $good['VAT'],
                    'marks'    => $marks,
                    'url'      => (!empty($good['FULL_DETAIL_PAGE_URL'])) ? $good['FULL_DETAIL_PAGE_URL'] : '',
                    'feacn'    => (!empty($feacn) && !empty($good['PROPERTIES'][$feacn])) ? $good['PROPERTIES'][$feacn] : '',
				);
			}
		}

		foreach(GetModuleEvents(self::$MODULE_ID, "onGoodsToRequest", true) as $arEvent)
			ExecuteModuleEventEx($arEvent,Array(&$arTG,$oId));

		foreach($arTG as $key => $vals)
			$ttlWeight += $vals['quantity'] * $vals['weight'];

		if($ttlWeight > $givenWeight){
			$kukan = floor($givenWeight *1000 / $ttlWeight);
			$ttlWeight = 0;
			foreach($arTG as $key => $good){
				$nw = floor(($arTG[$key]['weight'] * $kukan ) / $good['quantity']) / 1000;
				$arTG[$key]['weight'] = $nw;
				$ttlWeight += $nw * $good['quantity'];
			}
		}

		if($ttlWeight < $givenWeight){
			$diff = $givenWeight - $ttlWeight;
			foreach($arTG as $key => $good){
				if($good['quantity'] == 1)
					$applicant = $diff;
				else // really stupid stuff, but who cares
					$applicant = floor($diff * 1000 / $good['quantity']) / 1000;
				if($applicant * $good['quantity'] == $diff){
					$arTG[$key]['weight'] += $applicant;
					$diff = 0;
					break;
				}
			}
			if($diff != 0) // if nothing helps
				foreach($arTG as $key => $good){
					$arTG[$key]['weight'] += round($diff * 1000 / $good['quantity']) / 1000;
					break;
				}
		}

		return $arTG;
	}

	//формирование xml заказа
	private static $accountId = false;
	protected static function genOrderXML($oId,$mesId=false,$mode=false){
		if(!self::isAdmin()){
			self::errorLog(GetMessage('IPOLSDEK_SEND_ERR_NOACCESS'));
			return false;
		}
		if(!cmodule::includeModule('sale')) return false;

		$orderParams = self::GetByOI($oId,$mode);
		$account     = $orderParams['ACCOUNT'];
		if(!$orderParams){
			self::errorLog(GetMessage('IPOLSDEK_SEND_ERR_NOPARAMS'));
			return false;
		}
		$orderParams = unserialize($orderParams['PARAMS'], ['allowed_classes' => false]);
		$baze = ($mode == 'shipment') ? self::getShipmentById($oId) : CSaleOrder::GetById($oId);

		$on = ($baze['ACCOUNT_NUMBER'])?$baze['ACCOUNT_NUMBER']:$oId;

		$bezNal = ($orderParams['isBeznal'] == 'Y')?true:false;
		
		$usualDelivery = (\Ipolh\SDEK\option::get('deliveryAsPosition') != 'Y');

		if($mesId === false)
			$mesId=self::getMessId();

		$sendCity = self::getHomeCity();
		if(array_key_exists('courierCity',$orderParams) && $orderParams['courierCity']) 
			$sendCity = $orderParams['courierCity'];
		elseif(array_key_exists('departure',$orderParams) && $orderParams['departure'])
			$sendCity = $orderParams['departure'];
		
		if($sendCity){
			$arSendCity = sqlSdekCity::getBySId($sendCity);
			$senderCountry = ($arSendCity['COUNTRY']) ? $arSendCity['COUNTRY'] : 'rus';
			$senderCountry = self::getCountryCode($senderCountry);
			$senderCountry = ($senderCountry) ? 'SendCountryCode="'.$senderCountry.'" ' : '';
		}

		$arCity  = sqlSdekCity::getBySId($orderParams['location']);
		$country = ($arCity['COUNTRY']) ? $arCity['COUNTRY'] : 'rus';
		$recCountryCode = self::getCountryCode($country);
		$recCountryCode = ($recCountryCode) ? 'RecCountryCode="'.$recCountryCode.'" ' : '';

		$authSelect = ($account) ? $account : array('COUNTRY'=>$country);
		$headers = self::getXMLHeaders($authSelect);
		self::$accountId = $headers['ID'];

		$strXML = "<DeliveryRequest Number=\"".$mesId."\" Date=\"".$headers['date']."\" Account=\"".$headers['account']."\" Secure=\"".$headers['secure']."\" OrderCount=\"1\" DeveloperKey=\"4b1d17d262bdf16e36b9070934c74d47\" ".$recCountryCode.$senderCountry.">
	<Order Number=\"".$on."\"
		SendCityCode=\"".$sendCity."\" 
		RecCityCode=\"".$orderParams["location"]."\" 
		RecipientName=\"".$orderParams["name"]."\" 
		";
		if($orderParams['email'])
			$strXML .= "RecipientEmail=\"".$orderParams['email']."\" ";
		$strXML .= "
		Phone=\"".$orderParams['phone']."\"";

		// стоимости доставки и упаковок
			if(!array_key_exists('toPay',$orderParams))
				$orderParams['toPay'] = 0;
			
			$priceDeliveryVAT = false;

			// валюта
			$cntrCurrency = false;
			if($country != 'rus'){
				$cntrCurrency = array();
				$svdCountries = self::getCountryOptions();
				if(array_key_exists($country,$svdCountries) && $svdCountries[$country]['cur'] && $svdCountries[$country]['cur'] != $defVal)
					$cntrCurrency['site'] = $svdCountries[$country]['cur'];
				switch($country){
					case 'blr': $cntrCurrency['sdek'] = 'BYR'; break;
					case 'kaz': $cntrCurrency['sdek'] = 'KZT'; break;
				}
				if($cntrCurrency['sdek'])
				$strXML .= "
		RecipientCurrency=\"".$cntrCurrency['sdek']."\"
		ItemsCurrency=\"".$cntrCurrency['sdek']."\" 
		";
				if(array_key_exists('deliveryP',$orderParams))
					$orderParams['deliveryP'] = floatval(sdekExport::formatCurrency(array('TO'=>$cntrCurrency['site'],'SUM'=>$orderParams['deliveryP'],'orderId'=>$oId)));
				$orderParams['toPay'] = floatval(sdekExport::formatCurrency(array('TO'=>$cntrCurrency['site'],'SUM'=>$orderParams['toPay'],'orderId'=>$oId)));

                if (!$bezNal)
                {
                    if(array_key_exists('deliveryP',$orderParams))
                        $orderParams['deliveryP'] = floatval(sdekExport::formatCurrency(array('TO'=>$cntrCurrency['site'],'SUM'=>$orderParams['deliveryP'],'orderId'=>$oId)));

                    $orderParams['toPay'] = floatval(sdekExport::formatCurrency(array('TO'=>$cntrCurrency['site'],'SUM'=>$orderParams['toPay'],'orderId'=>$oId)));
                }
            }elseif(
				$country == 'rus' && 
				$orderParams['NDSDelivery'] &&
				array_key_exists('deliveryP',$orderParams)
			){
				// NDS delivery
				if($country == 'rus' && $orderParams['NDSDelivery']){
					$priceDeliveryVAT = self::ndsVal($orderParams['deliveryP'],$orderParams['NDSDelivery']);
				}
			}

			// товары и упаковки
		$packs = self::getPacks($oId,$mode,$orderParams);

		// handling prices
		foreach($packs as $number => $packContent){
			foreach($packContent['GOODS'] as $index => $arGood){
				if($cntrCurrency){
					$packs[$number]['GOODS'][$index]["price"]    = (float)sdekExport::formatCurrency(array('TO' => $cntrCurrency['site'], 'SUM' => $arGood["price"], 'orderId' => $oId));
					$arGood["price"]                             = (float)sdekExport::formatCurrency(array('TO' => $cntrCurrency['site'], 'SUM' => $arGood["price"], 'orderId' => $oId));
					$packs[$number]['GOODS'][$index]["cstPrice"] = (float)sdekExport::formatCurrency(array('TO' => $cntrCurrency['site'], 'SUM' => $arGood["cstPrice"], 'orderId' => $oId));
				}
				$toPay = ($bezNal || $orderParams['toPay'] == 0) ? 0 : $arGood["price"];
				$cnt = (int) $arGood["quantity"];
				if($toPay){
					$all = $toPay * $cnt;
					if($all > $orderParams['toPay']){
						$toPay = $orderParams['toPay'] / $cnt;
						$orderParams['toPay'] = 0;
					} else
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
					$packs[$number]['GOODS'][$index]["VATSum"]  = self::ndsVal($toPay,$vatRate);
					$packs[$number]['GOODS'][$index]["VATS"]    = true;
				} else {
					$packs[$number]['GOODS'][$index]["VATS"]    = false;
				}
				
				$packs[$number]['GOODS'][$index]["price"]    = $toPay;
				$packs[$number]['GOODS'][$index]["quantity"] = $cnt;
			}
		}
		
		if(!$bezNal){
			$priceDelivery = array_key_exists('deliveryP',$orderParams) ? $orderParams['deliveryP'] : $baze["PRICE_DELIVERY"];
			if($priceDelivery){
				if($usualDelivery){
					$strXML .= "
		DeliveryRecipientCost=\"".number_format($priceDelivery,2,'.','')."\"";
					if($priceDeliveryVAT !== false){
						$strXML .= "
		DeliveryRecipientVATRate=\"".$orderParams['NDSDelivery']."\"
		DeliveryRecipientVATSum =\"".$priceDeliveryVAT."\"	
";
					}
				} else {
					$countPacks = count($packs);
					$counter    = 1;
					foreach($packs as $number => $packContent){
						if($counter++ >= $countPacks){
							$arDelivery = array(
								'articul'  => 'delivery',
								'id'       => 'delivery',
								'cstPrice' => 0,
								'price'    => $priceDelivery,
								'weight'   => 0,
								'quantity' => 1,
								'name'	   => GetMessage('IPOLSDEK_LBL_DELIVERY'),
								'VATS'	   => false
							);
							if($priceDeliveryVAT !== false){
								$arDelivery['VATS']    = true;
								$arDelivery['VATSum']  = $priceDeliveryVAT;
								$arDelivery['VATRate'] = $orderParams['NDSDelivery'];
							}
							$packs[$number]['GOODS'] []= $arDelivery;
						}
					}
				}
			}
		}

		if($orderParams['comment'])
			$strXML .= "
		Comment=\"".str_replace('"',"'",$orderParams['comment'])."\" ";
		$strXML .= "
		TariffTypeCode=\"".$orderParams['service']."\" ";
		if(array_key_exists('realSeller',$orderParams) && $orderParams['realSeller'])
			$strXML .= "
		SellerName=\"".$orderParams['realSeller']."\">
		";
		else
			$strXML .= ">
		";
		// отправитель
        if(array_key_exists('sender_phone',$orderParams) && $orderParams['sender_phone']){
            $strXML .= "<Sender";
            foreach(array('company','name') as $attrName){
                if(array_key_exists('sender_'.$attrName,$orderParams) && $orderParams['sender_'.$attrName]){
                    $strXML .= " ".ucfirst($attrName)."=\"".$orderParams['sender_'.$attrName]."\"";
                }
            }
            $strXML .= ">
            ";
                if(array_key_exists('sender_street',$orderParams) && $orderParams['sender_street']) {
                    $strXML .= "<Address";
                    foreach(array('street','house','flat') as $attrName){
                        if(array_key_exists('sender_'.$attrName,$orderParams) && $orderParams['sender_'.$attrName]){
                            $strXML .= " ".ucfirst($attrName)."=\"".$orderParams['sender_'.$attrName]."\"";
                        }
                    }
                    $strXML .= "/>
            ";
                }
            $strXML .= "<phone>".$orderParams['sender_phone']."</phone>
        ";
            $strXML .= "</Sender>
        ";
        }
		//адрес
		if($orderParams["PVZ"])
			$strXML .= "<Address PvzCode=\"".$orderParams["PVZ"]."\" />
		";
		elseif($orderParams["PST"])
			$strXML .= "<Address PvzCode=\"".$orderParams["PST"]."\" />
		";
		else
			$strXML .= "<Address Street=\"".str_replace('"',"'",$orderParams['street'])."\" House=\"".$orderParams['house']."\" Flat=\"".$orderParams['flat']."\" />
		";

		foreach($packs as $number => $packContent){ // см, г
			$arPackArticules = array();
			$strXML .= "<Package Number=\"{$number}\" BarCode=\"{$number}\" Weight=\"{$packContent['WEIGHT']}\" SizeA=\"{$packContent['LENGTH']}\" SizeB=\"{$packContent['WIDTH']}\" SizeC=\"{$packContent['HEIGHT']}\">";
			foreach($packContent['GOODS'] as $arGood){
				$strNDS = '';
				if($arGood["VATS"]){
					$strNDS   .= " PaymentVATRate=\"".$arGood["VATRate"]."\"";
					$strNDS   .= " PaymentVATSum=\"".$arGood["VATSum"]."\"";
				}
				
				$articul = ($arGood["articul"])?str_replace('"',"'",$arGood["articul"]):$arGood["id"];
				if(array_key_exists($articul,$arPackArticules))
					$articul.="(".(++$arPackArticules[$articul]).")";
				else
					$arPackArticules[$articul] = 1;

				$strGood = "WareKey=\"".$articul."\" Cost=\"".number_format($arGood["cstPrice"],2,'.','')."\" Payment=\"".number_format($arGood["price"],2,'.','')."\" Weight=\"".$arGood["weight"]."\"".$strNDS." Comment=\"".str_replace('"',"'",$arGood["name"])."\"";

				if(array_key_exists("marks",$arGood) && $arGood["marks"] && is_array($arGood["marks"])){
                    $_cnt = count($arGood["marks"]);
                    for($i = 0; $i < $arGood["quantity"]; $i++){
                        if($_cnt > $i) {
                            $strXML.= "
            <Item " . $strGood . " Amount=\"1\" Marking=\"" . $arGood["marks"][$i] . "\" />";
                        } else {
                            $strXML.= "
            <Item " . $strGood . " Amount=\"".($arGood["quantity"] - $i)."\" />";
                            break;
                        }
                    }
                } else {
                    $strXML .= "
            <Item ".$strGood." Amount=\"".$arGood["quantity"]."\"/>";
                }
			}
			
			$strXML .= "
		</Package>
		";
		}

		if($payed){
			self::errorLog(GetMessage('IPOLSDEK_SEND_ERR_CANTCALCPRICE'));
			return false;
		}

		//допуслуги
		if(array_key_exists('AS',$orderParams) && count($orderParams['AS']))
			foreach($orderParams['AS'] as $service => $nothing)
				$strXML .= "<AddService ServiceCode=\"".$service."\"></AddService>
		";

		// время доставки
		if(\Ipolh\SDEK\option::get('addData') == 'Y' && array_key_exists('deliveryDate',$orderParams) && $orderParams['deliveryDate'] && strpos($orderParams['deliveryDate'],'.') !== false){
			$deliveryDate = explode('.',$orderParams['deliveryDate']);
			$strXML .= "<Schedule>
			<Attempt ID=\"1\" Date=\"".$deliveryDate[2]."-".$deliveryDate[1]."-".$deliveryDate[0]."\"></Attempt>
		</Schedule>";
		}

	$strXML .= "
	</Order>";

		// Отправители
		if(in_array($orderParams['service'],self::getDoorTarifs()) && $orderParams['courierDate']){
			preg_match('/(\d\d).(\d\d).([\d]+)/',$orderParams['courierDate'],$matches);
			$orderParams['courierDate'] = $matches[3].'-'.$matches[2].'-'.$matches[1];
	$strXML .= "<CallCourier>
		<Call 
			Date=\"{$orderParams['courierDate']}\"
			TimeBeg=\"{$orderParams['courierTimeBeg']}\"
			TimeEnd=\"{$orderParams['courierTimeEnd']}\"
			SendCityCode=\"{$orderParams['courierCity']}\"
			Comment=\"{$orderParams['courierComment']}\"
			SendPhone=\"{$orderParams['courierPhone']}\"
			SenderName=\"{$orderParams['courierName']}\">
			<SendAddress 
				Street=\"{$orderParams['courierStreet']}\"
				House=\"{$orderParams['courierHouse']}\"
				Flat=\"{$orderParams['courierFlat']}\"
			/>
		</Call>
	</CallCourier>";
		}
	$strXML.="
</DeliveryRequest>";
		return $strXML;
	}
	
	public static function ndsVal($val,$nds)
	{
		switch($nds){
            case 'VAT05' : $val = $val *  5 / 105; break;
            case 'VAT07' : $val = $val *  7 / 107; break;
            case 'VAT10' : $val = $val * 10 / 110; break;
            case 'VAT12' : $val = $val * 12 / 112; break;
			case 'VAT18' : $val = $val * 18 / 118; break;
			case 'VAT20' : $val = $val * 20 / 120; break;
			case 'VATX'  :
			case 'VAT0'  :
			default      : $val  = 0; break;
		}
		
		return self::round2($val);
	}

	static function sendOrderRequest($oId,$mode='order'){
		if(!self::isAdmin()){
			self::errorLog(GetMessage('IPOLSDEK_SEND_ERR_NOACCESS'));
			return false;
		}
		if(!$oId) return false;
		if(!cmodule::includemodule('sale')){self::errorLog(GetMessage("IPOLSDEK_ERRLOG_NOSALEOML"));return false;}//без модуля sale делать нечего

        $options = new \Ipolh\SDEK\Bitrix\Entity\Options();
		$adapter = new \Ipolh\SDEK\Bitrix\Adapter\Order($options);
		$adapter->uploadedOrder($oId,$mode,!\Ipolh\SDEK\abstractGeneral::isNewApp());
		$auth    = \sqlSdekLogs::getByAcc($adapter->getBaseOrder()->getField('account'));
        $application = \Ipolh\SDEK\abstractGeneral::makeApplication($auth['ACCOUNT'], $auth['SECURE']);
        $controller  = new \Ipolh\SDEK\Bitrix\Controller\Order($application,$adapter->getBaseOrder());
        $obReturn    = $controller->sendOrder();

        $arErrors = array();
        if($obReturn->getError()){
            $obReturn->getError()->reset();
            while($obErr = $obReturn->getError()->getNext()){
                $arErrors [] = $obErr;
            }
        }
        $return = false;
        if($obReturn->isSuccess()){
            $response = $obReturn->getResponse();
            $sended = false;
            $uid    = false;

            $isNewApp = \Ipolh\SDEK\abstractGeneral::isNewApp();
            if ($response->getField('uid') && $isNewApp) {
                $uid = $response->getField('uid');
            }
            if ($response->getField('cdekNumber')) {
                $sended = $response->getField('cdekNumber');
            }

            if ($sended) {
                self::setOrderTrackingNumber($oId,$mode,$sended);
            }

            $hasErrors = (!empty($arErrors));

            switch (true){
                case ((!$uid && $isNewApp) || (!$sended && !$isNewApp) || $hasErrors) : $status = 'ERROR'; break;
                case ($response->getField('state') !== 'SUCCESSFUL') : $status = 'WAIT'; break;
                default : $status = 'OK'; break;
            }
            sqlSdekOrders::updateStatus(array(
                "ORDER_ID" => $oId,
                "STATUS"   => $status,
                "SDEK_ID"  => $sended,
                "SDEK_UID" => ($uid) ? $uid : false,
                "MESSAGE"  => ($hasErrors) ? serialize(self::zaDEjsonit($arErrors)) : false,
                "MESS_ID"  => $adapter->getBaseOrder()->getField('messId'),
                "mode"     => $mode,
                "ACCOUNT"  => $auth['ID'],
                "OK"       => ($sended)
            ));
            if ($status == 'ERROR')
                self::toAnswer(GetMessage("IPOLSDEK_SEND_NOTDENDED"));
            elseif ($hasErrors)
                self::toAnswer(GetMessage("IPOLSDEK_SEND_BADSENDED"));
            elseif ($status == 'WAIT')
                self::toAnswer(GetMessage("IPOLSDEK_SEND_WAITSENDED"));
            else {
                self::toAnswer(GetMessage("IPOLSDEK_SEND_SENDED"));
                $return = true;
            }
            foreach (GetModuleEvents(self::$MODULE_ID, "requestSended", true) as $arEvent)
                ExecuteModuleEventEx($arEvent, Array(
                    $oId,
                    $status,
                    $sended
                ));

        } else {
            sqlSdekOrders::updateStatus(array(
                "ORDER_ID" => $oId,
                "MESSAGE"  => (!empty($arErrors)) ? serialize(self::zaDEjsonit($arErrors)) : '',
            ));
            foreach ($arErrors as $error){
                self::toAnswer($error);
            }
        }

		return $return;
	}

	public static function setOrderTrackingNumber($oId,$mode,$tracking)
    {
        if (\Ipolh\SDEK\option::get('setDeliveryId') == 'Y' && CModule::IncludeModule('sale')) {
            if ($mode == 'order') {
                CSaleOrder::Update($oId, array('TRACKING_NUMBER' => $tracking));
            } elseif (self::isConverted()) {// <3 D7
                self::setShipmentField($oId, 'TRACKING_NUMBER', $tracking);
            }

            $saveProp = \Ipolh\SDEK\option::get('setTrackingOrderProp');
            if ($saveProp) {
                $arOrder = CSaleOrder::GetByID($oId);
                $op      = CSaleOrderProps::GetList(array(), array(
                    "PERSON_TYPE_ID" => $arOrder['PERSON_TYPE_ID'],
                    "CODE" => $saveProp
                ))->Fetch();
                if ($op) {
                    self::saveProp(array(
                        "ORDER_ID" => $oId,
                        "ORDER_PROPS_ID" => $op['ID'],
                        "NAME" => $op['NAME'],
                        "CODE" => $saveProp,
                        "VALUE" => \Ipolh\SDEK\SDEK\Tools::getTrackLink($tracking)
                    ));
                }
            }
        }
    }


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
												Манипуляции с информацией о заявках [БД + удаление]
	== updtOrder == == saveAndSend ==  == deleteRequest ==  
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/

	//База данных
	public static function GetByOI($ID,$mode=false){  // выбрать заявку по id заказа / отправления
		if(!self::isAdmin('R')) return false;
		return ($mode == 'shipment') ? sqlSdekOrders::GetBySI($ID) : sqlSdekOrders::GetByOI($ID);
	}

	static function updtOrder($params){ // сохраняем информацию о заявке в БД, возвращаем ее ID
		if(!self::isAdmin()){
			self::errorLog(GetMessage('IPOLSDEK_SEND_ERR_NOACCESS'));
			return false;
		}

		$params=self::zaDEjsonit($params);

		if($params['account']){
		    $arAccount = sqlSdekLogs::getById($params['account']);
		    if(!$arAccount){
                self::errorLog(GetMessage('IPOLSDEK_SEND_ERR_NOACCOUNT').$params['account']);
                return false;
            }elseif($arAccount['ACTIVE'] !== 'Y'){
                self::errorLog(GetMessage('IPOLSDEK_SEND_ERR_ACCOUNTINACTIVE')."({$arAccount['ACCOUNT']})");
                return false;
            }

            $account = $params['account'];
            unset($params['account']);
        } else {
            $account = false;
        }

		$arNeedFields = array('service','location','name','phone');
		if(in_array("PVZ",$params))
			$arNeedFields[]="PVZ";
		else
			array_merge($arNeedFields,array('street','house','flat'));

		foreach($params as $prop => $val){
			if(in_array($prop,$arNeedFields) && !$val){
                self::errorLog(GetMessage('IPOLSDEK_JS_SOD_' . $prop) . " " . GetMessage('IPOLSDEK_SOD_NOTGET'));
                return false;
			}
			if (!is_array($val)) {
				$params[$prop] = str_replace(array('"','<','>'),"'",$val);
			}
		}

        $storeKeys = [
            'from_loc_street',
            'from_loc_house',
            'from_loc_flat',
            'sender_company',
            'sender_name',
            'sender_phone',
            'sender_phone_add',
            'seller_name',
            'seller_phone',
            'seller_address',
        ];
        foreach ($storeKeys as $key) {
            if (!array_key_exists($key, $params)) {
                $params[$key] = null;
            }
        }

		if(
			(!$params['orderId'] && $params['mode'] == 'order') ||
			(!$params['shipment'] && $params['mode'] == 'shipment')
		){
            self::errorLog(GetMessage('IPOLSDEK_SOD_ORDERID') . " " . GetMessage('IPOLSDEK_SOD_NOTGET'));
            return false;
		}
		if(!$params['status'])
			$status = 'NEW';
        else
            $status = $params['status'];

		$orderId=($params['mode'] == 'order') ? $params['orderId'] : $params['shipment'];
		$source = ($params['mode'] == 'order') ? 0 : 1;
		unset($params['orderId']);
		unset($params['shipment']);
		unset($params['mode']);
		unset($params['isdek_action']);
		if($params['auto']){
			$autoLoad = true;
			unset($params['auto']);
		}else
			$autoLoad = false;

		if($newId=sqlSdekOrders::Add(array('ORDER_ID'=>$orderId,'PARAMS'=>serialize($params),'STATUS'=>$status, 'SOURCE' => $source, 'ACCOUNT' => $account))){
			if(!$autoLoad)
				echo GetMessage('IPOLSDEK_SOD_UPDATED')."\n";
			return $newId;
		}
		else{
			self::errorLog(GetMessage('IPOLSDEK_SEND_ERR_NOSAVED'));
			return false;
		}
	}

	static function saveAndSend($params){ // кнопка "Сохранить и отправить" в редакторе заказа
		if(!self::isAdmin('R')) return false;
		if(self::updtOrder($params))
			self::sendOrderRequest((($params['mode'] == 'order') ? $params['orderId'] : $params['shipment']),$params['mode']);
		$err = self::getErrors();
		if(!$params['auto'])
			echo ($err)?$err:self::getAnswer();
	}

	static function deleteRequest($oId, $mode = 'order')
    {
        $return = false;

        if (!self::isAdmin() || !cmodule::includemodule('sale'))
            return $return;

		$request = self::GetByOI($oId, $mode);
		if ($request) {
			if ($request['STATUS'] === 'OK') {
                $account = \sqlSdekLogs::getById($request['ACCOUNT']);
                if (!empty($account)) {
                    // 2.0 only
                    $application = new \Ipolh\SDEK\SDEK\SdekApplication(
                        $account['ACCOUNT'], $account['SECURE'],
                        false,
                        10,
                        new \Ipolh\SDEK\Bitrix\Entity\encoder(),
                        new \Ipolh\SDEK\Bitrix\Entity\cache()
                        //, new \Ipolh\SDEK\Admin\IvanInlineLoggerController()
                    );
                    $controller = new \Ipolh\SDEK\Bitrix\Controller\Order($application);

                    $uuid = (!empty($request['SDEK_UID'])) ? $request['SDEK_UID'] : null;
                    if (empty($uuid)) {
                        // Case: order was sent via API 1.5, so CDEK uuid is unknown
                        if (!empty($request['SDEK_ID'])) {
                            $getInfoResult = $controller->getOrderInfoByNumber($request['SDEK_ID']);
                            if ($getInfoResult->isSuccess()) {
                                $uuid = $getInfoResult->getData()['UUID'];
                            } else {
                                self::toAnswer(GetMessage('IPOLSDEK_DRQ_GOTERRORS').$getInfoResult->getErrorsString());
                                return $return;
                            }
                        } else {
                             self::toAnswer(GetMessage('IPOLSDEK_DRQ_UNBLDLT').GetMessage('IPOLSDEK_DRQ_SDEK_ID_EMPTY'));
                             return $return;
                        }
                    }

                    $deleteResult = $controller->deleteOrder($uuid);
                    if ($deleteResult->isSuccess()) {
                        // Unmake from DB
                        if (sqlSdekOrders::Delete($oId, $mode)) {
                            $return = true;
                        } else {
                            self::toAnswer(GetMessage('IPOLSDEK_DRQ_CNTDELREQ'));
                        }
                    } else {
                        self::toAnswer(GetMessage('IPOLSDEK_DRQ_GOTERRORS').$deleteResult->getErrorsString());
                    }
                } else {
                    self::toAnswer(GetMessage('IPOLSDEK_DRQ_UNBLDLT').GetMessage('IPOLSDEK_DRQ_UNKNOWN_ACCOUNT'));
                }
			} else {
                self::toAnswer(GetMessage('IPOLSDEK_DRQ_UNBLDLT').GetMessage('IPOLSDEK_DRQ_BADSTATUS'));
            }
		} else {
            self::toAnswer(GetMessage('IPOLSDEK_DRQ_UNBLDLT').GetMessage('IPOLSDEK_ERRLOG_NOREQ').$oId);
        }

		return $return;
	}

	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
													Визуализация (форма оформления заявки)
		== onEpilog ==  == getExtraOptions ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/

	public static function onEpilog()
    {
        global $USER;
        if (!(isset($USER) && $USER instanceof \CUser && $USER->IsAuthorized() && (!defined('BX_AUTH_FORM') || !BX_AUTH_FORM))) {
            return false;
        }

        $check = ($_SERVER['PHP_SELF']) ? $_SERVER['PHP_SELF'] : $_SERVER['REQUEST_URI'];

        // Group actions in Bitrix orders list: print invoices or barcodes
        if (strpos($check, '/bitrix/admin/sale_order.php') !== false) {
            \Ipolh\SDEK\PrintHandler::loadActionJS();
        }

        // Order sender form
        $workMode = false;
		$workType = false;
		global $APPLICATION;				
		$dir = $APPLICATION->GetCurDir();
				
		$b24path = \Ipolh\SDEK\Bitrix\Tools::getB24URLs();
		
		// Standard BX support
		if(
			strpos($check, "/bitrix/admin/sale_order_detail.php") !== false || 
			strpos($check, "/bitrix/admin/sale_order_view.php")   !== false
		)
		{
			$workMode = 'order';
			$workType = 'standard';			
		}
		elseif(strpos($_SERVER['PHP_SELF'], "/bitrix/admin/sale_order_shipment_edit.php") !== false && self::canShipment())
		{
			$workMode = 'shipment';
			$workType = 'standard';
		}
		// B24 support
		elseif (strpos($dir, $b24path['ORDER']) !== false)
		{
			$workMode = 'order';
			$workType = 'b24';	
		}			
		elseif (strpos($dir, $b24path['SHIPMENT']) !== false && self::canShipment())
		{
			$workMode = 'shipment';
			$workType = 'b24';	
		}	
			
		if(!$workMode || !$workType || !cmodule::includeModule('sale') || !self::isAdmin('R'))
			return false;	
		
		// B24 button container adding
		if ($workType == 'b24')
		{
			\Bitrix\Main\UI\Extension::load('ui.buttons');
			\Bitrix\Main\UI\Extension::load('ui.buttons.icons');
		
			$containerHTML = '<div class="pagetitle-container" id="IPOLSDEK_btn_container"></div>';
			$APPLICATION->AddViewContent('inside_pagetitle', $containerHTML, 20000);

            CJSCore::Init(array("window"));
            $APPLICATION->SetAdditionalCSS("/bitrix/themes/.default/pubstyles.min.css");
            $APPLICATION->SetAdditionalCSS("/bitrix/panel/main/admin-public.min.css");
		}
		
		sdekExport::loadExportWindow($workMode, $workType);
	}

    /**
     * Returns info about supported Additional services
     * @return array
     */
	public static function getExtraOptions()
    {
		$arAddService = array(3,7,16,17,27,30,36,48,59,60,81,96);
		$src = \Ipolh\SDEK\option::get('addingService');

		$arReturn = array();
		foreach($arAddService as $asId)
			$arReturn[$asId] = array(
				'NAME' => GetMessage("IPOLSDEK_AS_".$asId."_NAME"),
				'DESC' => GetMessage("IPOLSDEK_AS_".$asId."_DESCR"),
				'SHOW' => (isset($src) && array_key_exists($asId, $src) && array_key_exists('SHOW', $src[$asId]) && $src[$asId]['SHOW']) ? $src[$asId]['SHOW'] : "N",
				'DEF'  => (isset($src) && array_key_exists($asId, $src) && array_key_exists('DEF', $src[$asId]) && $src[$asId]['DEF'])  ? $src[$asId]['DEF']  : "N",
			);
		return $arReturn;
	}

	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
													Создание заказа
		== orderCreate ==  == controlProps ==  == saveProp ==  == handleProp ==  == autoLoad ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


	static function orderCreate($oId,$arFields){
		if(!cmodule::includemodule('sale'))
			return;
		if(!self::controlProps())
			return;

		if(array_key_exists('IPOLSDEK_CHOSEN',$_SESSION)){
			$checkTarif = self::defineDelivery($arFields['DELIVERY_ID']);
			$tarif = ($checkTarif) ? $checkTarif : 'courier';
			$op = CSaleOrderProps::GetList(array(),array("PERSON_TYPE_ID" =>$arFields['PERSON_TYPE_ID'],"CODE"=>"IPOLSDEK_CNTDTARIF"))->Fetch();
			if($op)
				self::saveProp(array(
				   "ORDER_ID"       => $oId,
				   "ORDER_PROPS_ID" => $op['ID'],
				   "NAME"           => GetMessage('IPOLSDEK_prop_name'),
				   "CODE"           => "IPOLSDEK_CNTDTARIF",
				   "VALUE"          => $_SESSION['IPOLSDEK_CHOSEN'][$tarif]
				));
			unset($_SESSION['IPOLSDEK_CHOSEN']);
		}
		if(
            \Ipolh\SDEK\option::get('autoloads') == 'Y' &&
            \Ipolh\SDEK\option::get('autoloadsMode') === 'O'
        ){
			if($respond = self::autoLoad($oId,$arFields,'O')){
				$op = CSaleOrderProps::GetList(array(),array("PERSON_TYPE_ID" =>$arFields['PERSON_TYPE_ID'],"CODE"=>"IPOLSDEK_AUTOSEND"))->Fetch();
				if($op)
					self::saveProp(array(
						"ORDER_ID"       => $oId,
						"ORDER_PROPS_ID" => $op['ID'],
						"NAME"           => GetMessage('IPOLSDEK_propAuto_name'),
						"CODE"           => "IPOLSDEK_AUTOSEND",
						"VALUE"          => GetMessage('IPOLSDEK_AUTOLOAD_RESPOND_'.$respond)
					));
			}
		}
	}

	static function saveProp($arPropFields){
		if(!CSaleOrderPropsValue::Add($arPropFields)){
			$prop = CSaleOrderPropsValue::GetList(array(),array("ORDER_ID" => $arPropFields['ORDER_ID'],"ORDER_PROPS_ID" => $arPropFields['ORDER_PROPS_ID']))->Fetch();
			if($prop) {
                CSaleOrderPropsValue::Update($prop['ID'],$arPropFields);
            }
		}
	}

	static function controlProps($mode=1){//1-add/update, 2-delete
		if(!CModule::IncludeModule("sale"))
			return false;
		$arProps = array(
			array(// Свойство для сохранения тарифа
				'CODE'  => "IPOLSDEK_CNTDTARIF",
				'NAME'  => GetMessage('IPOLSDEK_prop_name'),
				'DESCR' => GetMessage('IPOLSDEK_prop_descr')
			)/*,
            array(
                'CODE'  => "IPOLSDEK_ACCOUNT",
                'NAME'  => GetMessage('IPOLSDEK_propAcc_name'),
                'DESCR' => GetMessage('IPOLSDEK_propAcc_descr')
            )*/
		);
		if(\Ipolh\SDEK\option::get('autoloads') == 'Y')
			$arProps[]=array(// Свойство для автоматизации
				'CODE'  => "IPOLSDEK_AUTOSEND",
				'NAME'  => GetMessage('IPOLSDEK_propAuto_name'),
				'DESCR' => GetMessage('IPOLSDEK_propAuto_descr')
			);
		$return = true;
		foreach($arProps as $prop){
			$subReturn = self::handleProp($prop,$mode);
			if(!$subReturn)
				$return = $subReturn;
		}
		return $return;
	}

	protected static function handleProp($arProp,$mode){
		$tmpGet=CSaleOrderProps::GetList(array("SORT" => "ASC"),array("CODE" => $arProp['CODE']));
		$existedProps=array();
		while($tmpElement=$tmpGet->Fetch())
			$existedProps[$tmpElement['PERSON_TYPE_ID']]=$tmpElement['ID'];
		if($mode=='1'){
			$return = true;
			
			$tmpGet = CSite::GetList($by="sort", $order="desc",array('ACTIVE' => 'Y'));
			$arLids = array();
			while($tmpElement=$tmpGet->Fetch()){
				$arLids[]=$tmpElement['LID'];
			}

            $tmpGet = CSalePersonType::GetList(Array("SORT" => "ASC"), Array());
            $allPayers=array();
            while($tmpElement=$tmpGet->Fetch()) {
                if (!$tmpElement['ACTIVE'] == 'Y')
                {
                    continue;
                }
                if (in_array($tmpElement['LID'], $arLids)) {
                    $allPayers[] = $tmpElement['ID'];
                    continue;
                }
                if (array_key_exists('LIDS', $tmpElement)) {
                    foreach ($tmpElement['LIDS'] as $lid) {
                        if (in_array($lid, $arLids)) {
                            $allPayers[] = $tmpElement['ID'];
                            break;
                        }
                    }
                }
            }

			foreach($allPayers as $payer){
				$tmpGet = CSaleOrderPropsGroup::GetList(array("SORT" => "ASC"),array("PERSON_TYPE_ID" => $payer),false,array('nTopCount' => '1'));
				$tmpVal=$tmpGet->Fetch();
				// Case: payer, but without props group and order props
                if (!is_array($tmpVal) || empty($tmpVal['ID']))
                    continue;
				$arFields = array(
				   "PERSON_TYPE_ID" => $payer,
				   "NAME" => $arProp['NAME'],
				   "TYPE" => "TEXT",
				   "REQUIED" => "N",
				   "DEFAULT_VALUE" => "",
				   "SORT" => 100,
				   "CODE" => $arProp['CODE'],
				   "USER_PROPS" => "N",
				   "IS_LOCATION" => "N",
				   "IS_LOCATION4TAX" => "N",
				   "PROPS_GROUP_ID" => $tmpVal['ID'],
				   "SIZE1" => 10,
				   "SIZE2" => 1,
				   "DESCRIPTION" => $arProp['DESCR'],
				   "IS_EMAIL" => "N",
				   "IS_PROFILE_NAME" => "N",
				   "IS_PAYER" => "N",
				   "IS_FILTERED" => "Y",
				   "IS_ZIP" => "N",
				   "UTIL" => "Y"
				);
				if(!array_key_exists($payer,$existedProps))
					if(!CSaleOrderProps::Add($arFields))
						$return = false;
			}
			return $return;
		}
		if($mode=='2'){
			foreach($existedProps as $existedPropId)
				if (!CSaleOrderProps::Delete($existedPropId))
					echo "Error delete CNTDTARIF-prop id".$existedPropId."<br>";
		}
	}

	static function autoLoad($orderId,$arFields=false,$mode='O'){
		if(!cmodule::includeModule('sale'))
			return false;

        CDeliverySDEK::$orderPrice = false;

		if(!$arFields)
			$arFields = CSaleOrder::GetById($orderId);

		if(!self::defineDelivery($arFields['DELIVERY_ID']))
			return false;
		if(self::GetByOI($orderId,'order'))
			return false;

		sdekExport::$orderId  = $orderId;
		sdekExport::$workMode = 'order';
		sdekExport::$orderDescr = sdekExport::getOrderDescr();
		$ordrVals = self::GetByOI(sdekExport::$orderId,sdekExport::$workMode);

		if($mode == 'O' || !$ordrVals){
			$fields = sdekExport::formation();

			$configs = self::getDeliveryConfig($arFields['DELIVERY_ID']);
			if(!empty($configs)){
				if(array_key_exists('VALUE',$configs['ACCOUNT']) && $configs['ACCOUNT']['VALUE']){
					$fields['account'] = $configs['ACCOUNT']['VALUE'];
				}
			}

            if(in_array($fields['service'], array_merge(self::getTarifList(array('type'=>'pickup','answer'=>'array')), sdekHelper::getTarifList(array('type'=>'postamat','answer'=>'array'))))){ // ПВЗ
			    if(!$fields['PVZ']) {
                    return 4; // нет данных о ПВЗ
                }
				unset($fields['street']);
				unset($fields['flat']);
				unset($fields['house']);
				unset($fields['address']);
			}else{
				unset($fields['PVZ']);

                if (\Ipolh\SDEK\abstractGeneral::isNewApp()) {
                    if (!$fields['address'] && !($fields['street'] && $fields['house'])) {
                        return 5;
                    }
                } else {
                    sdekExport::parseAddress($fields, true);
                    if ($fields['street'] && $fields['house']) {
                        unset($fields['address']);
                    } elseif (!$fields['address']) {
                        return 5;
                    } // нет данных об адресе
                    else {
                        return 6;
                    } // невозможно распарсить адрес
                }
			}

			if($fields['isBeznal'] == 'Y'){
				$fields['toPay']     = 0;
				$fields['deliveryP'] = 0;
			}

			$fields['orderId'] = $orderId;
			$fields['mode']    = 'order';
			$fields['auto']    = 'Y';

            // Set some default Additional services if exists
            $addServices = sdekdriver::getExtraOptions();
            if (!empty($addServices)) {
                foreach ($addServices as $code => $data) {
                    if ($data['DEF'] == 'Y') {
                        $fields['AS'][$code] = 'Y';
                    }
                }
            }
		} else {
			$fields = unserialize($ordrVals['PARAMS'], ['allowed_classes' => false]); // 4 future
		}

		if(!$fields['service'])
			return 2; // нет данных о тарифе

		if(!$fields['departure'])
			return 3; // нет данных об отправителе

		self::$skipAdminCheck = true;

		self::saveAndSend(self::zajsonit($fields));
        if (self::getErrors()) {
            return 'LOCAL_ERROR';
        }

        $order = self::GetByOI($orderId, sdekExport::$workMode);
        if ($order['STATUS'] === 'ERROR') {
            return 'CDEK_ERROR';
        }

		return 1;
	}

    static function statusAutoLoad($oId, $orderStatus)
    {
        if (
            \Ipolh\SDEK\option::get('autoloads') === 'Y' &&
            \Ipolh\SDEK\option::get('autoloadsMode') === 'S' &&
            \Ipolh\SDEK\option::get('autoloadsStatus') === $orderStatus
		){
			$checkSQL = self::GetByOI($oId,'order');
			if(!$checkSQL || !$checkSQL['OK']){
				$arFields = CSaleOrder::GetById($oId);
				if($respond = self::autoLoad($oId,$arFields,'S')){
                    $op = CSaleOrderProps::GetList(array(), array("PERSON_TYPE_ID" => $arFields['PERSON_TYPE_ID'], "CODE" => "IPOLSDEK_AUTOSEND"))->Fetch();
                    if ($op) {
                        $value = GetMessage('IPOLSDEK_AUTOLOAD_RESPOND_' . $respond);
                        if ($respond === 'LOCAL_ERROR') {
                            $value = GetMessage('IPOLSDEK_AUTOLOAD_RESPOND_LOCAL_ERROR', ['#ERROR#' => self::getErrors()]);
                        }
                        self::saveProp(array(
                            "ORDER_ID"       => $oId,
                            "ORDER_PROPS_ID" => $op['ID'],
                            "NAME"           => GetMessage('IPOLSDEK_propAuto_name'),
                            "CODE"           => "IPOLSDEK_AUTOSEND",
                            "VALUE"          => $value
                        ));
                    }
				}
			}
		}
	}

	// подключение js и аналогичных файлов
	static function getModuleExt($wat){
		$arDef = array(
			'packController' => ".php",
			'markingController' => ".php",
			'mask_input' => '.js'
		);

		if(!array_key_exists($wat,$arDef)) return;

		$fPath = $_SERVER['DOCUMENT_ROOT'].'/bitrix/js/'.self::$MODULE_ID."/$wat{$arDef[$wat]}";
		if(file_exists($fPath))
			include_once($fPath);
	}

	// связки
	static function senders($params = false){
		return sdekOption::senders($params);
	}

	// переходные функции
	static function displayActPrint(&$list){
		sdekOption::displayActPrint($list);
	}

    /**
     * @deprecated
     */
	static function OnBeforePrologHandler(){
	}
	static function agentUpdateList(){
		return sdekOption::agentUpdateList();
	}
	static function agentOrderStates(){
		return sdekOption::agentOrderStates();
	}
	static function select($arSelect,$arFilter=array()){
		if(array_key_exists('ORDER_ID',$arFilter))
			$arFilter['SOURCE'] = 0;
		return sqlSdekOrders::select($arSelect,$arFilter);
	}
}
?>