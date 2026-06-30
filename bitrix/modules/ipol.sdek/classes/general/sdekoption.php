<?php

use Ipolh\SDEK\PrintHandler;

IncludeModuleLangFile(__FILE__);

	class sdekOption extends sdekHelper{
		
		/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
													Авторизация
		== auth ==  == logoff ==  == authConsolidation ==  == callAccounts ==  == newAccount ==  == checkAuth ==  == optionDeleteAccount ==  == deleteAccount ==  == optionMakeAccDefault ==  == makeAccDefault ==
		()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/

		static function auth($params){
		    \Ipolh\SDEK\AuthHandler::auth($params);
		}

		static function logoff(){
		    \Ipolh\SDEK\AuthHandler::delogin();
		}

		static function callAccounts(){
		    \Ipolh\SDEK\AuthHandler::callAccounts();
		}

		static function newAccount($params){
			\Ipolh\SDEK\AuthHandler::newAccount($params);
		}

		static function checkAuth($account,$password){
            return \Ipolh\SDEK\AuthHandler::checkAuth($account,$password);
		}

		static function optionDeleteAccount($params){
			echo json_encode(self::zajsonit(self::deleteAccount($params['ID'])));
		}

		static function deleteAccount($id){
			return \Ipolh\SDEK\AuthHandler::deleteAccount($id);
		}

		static function makeAccDefault($id=false){
            return \Ipolh\SDEK\AuthHandler::makeAccDefault($id);
		}

        static function authConsolidation(){
            if(!\Ipolh\SDEK\option::get('logged'))
                return;
            sqlSdekLogs::Add(array('ACCOUNT'=>COption::GetOptionString(self::$MODULE_ID,'logSDEK',false),'SECURE'=>COption::GetOptionString(self::$MODULE_ID,'pasSDEK',false)));
            $id = sqlSdekLogs::Check(COption::GetOptionString(self::$MODULE_ID,'logSDEK',false));
            \Ipolh\SDEK\option::set('logged',$id);
        }

        static function optionMakeAccDefault($params){
            echo json_encode(self::zajsonit(self::makeAccDefault($params['ID'])));
        }


        /*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
                                                        отображение таблицы о заявках
            == tableHandler ==
        ()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


		static function tableHandler($params){
			cmodule::includeModule('sale');
			$arSelect[0]=($params['by'])?$params['by']:'ID';
			$arSelect[1]=($params['sort'])?$params['sort']:'DESC';

			$arNavStartParams['iNumPage']=($params['page'])?$params['page']:1;
			$arNavStartParams['nPageSize']=($params['pgCnt']!==false)?$params['pgCnt']:1;

			foreach($params as $code => $val)
				if(strpos($code,'F')===0)
					$arFilter[substr($code,1)]=$val;

			$requests   = self::select($arSelect,$arFilter,$arNavStartParams);
			$adServises = sdekdriver::getExtraOptions();
			$strHtml='';
			$tarifs = self::getExtraTarifs();
			$accounts   = sqlSdekLogs::getAccountsList(true);
			$accFullCnt = sqlSdekLogs::getAccountsList(false,true);
			$accountBase = self::getBasicAuth(true);

			$arRules = array(
                'noLabel' => array('packs', 'currency', 'isdek_token'),
				'header'  => array(
					'courierDate' => GetMessage('IPOLSDEK_STT_SENDER'),
					'street'      => GetMessage('IPOLSDEK_STT_ADDRESS'),
					'line'        => GetMessage('IPOLSDEK_STT_ADDRESS'),
					'PVZ'         => GetMessage('IPOLSDEK_STT_ADDRESS'),
					'packs'		  => GetMessage('IPOLSDEK_STT_PACKS'),
				),
			);

			$isConverted = self::isConverted();
			if($isConverted)
				\Bitrix\Main\Loader::includeModule('sale');
			while($request=$requests->Fetch()){
				$reqParams=unserialize($request['PARAMS'], ['allowed_classes' => false]);
				$paramsSrt='';
				foreach($reqParams as $parCode => $parVal){
					if(array_key_exists($parCode,$arRules['header']))
						$paramsSrt .= "<strong>".$arRules['header'][$parCode]."</strong><br>";
					if(!in_array($parCode,$arRules['noLabel']))
                        $paramsSrt .= GetMessage("IPOLSDEK_JS_SOD_$parCode") . ": ";

					switch($parCode){
                        case 'isdek_token':
                        case 'currency': break;
						case "AS"      : foreach($parVal as $code => $noThing)
											 if(array_key_exists($code,$adServises))
												 $paramsSrt.= $adServises[$code]['NAME']." (".$code."), ";
										 $paramsSrt = substr($paramsSrt,0,strlen($paramsSrt)-2)."<br>";
										 break;
						case "GABS"    : $paramsSrt.= $parVal['D_L']."x".$parVal['D_W']."x".$parVal['D_H']." ".GetMessage("IPOLSDEK_cm")." ".$parVal['W']." ".GetMessage('IPOLSDEK_kg')."<br>";break;
						case "service" : $paramsSrt.=$tarifs[$parVal]['NAME']."<br>"; break;
						case "packs"   : 
										$orderGoods = ($request['SOURCE'] == 1) ? sdekdriver::getGoodsArray(self::oIdByShipment($request['ORDER_ID']),$request['ORDER_ID']) : sdekdriver::getGoodsArray($request['ORDER_ID']);
										foreach($parVal as $place => $params){
											$paramsSrt.="<span style='font-style:italic'>".GetMessage('IPOLSDEK_JS_SOD_Pack')." ".$place."</span><br>";
											$paramsSrt.=GetMessage('IPOLSDEK_dims').": ".$params['gabs']." (".GetMessage('IPOLSDEK_cm').")<br>";
											$paramsSrt.=GetMessage('IPOLSDEK_weight').": ".$params['weight']." ".GetMessage('IPOLSDEK_kg')."<br>";
											$paramsSrt.=GetMessage('IPOLSDEK_goods').": ";
											foreach($params['goods'] as $gId => $cnt )
												if(array_key_exists($gId,$orderGoods))
													$paramsSrt.=$orderGoods[$gId]['NAME']." ({$orderGoods[$gId]['PRODUCT_ID']}): $cnt, ";
											$paramsSrt = substr($paramsSrt,0,strlen($paramsSrt)-2)."<br>";
										 };
										 break;
						case 'toPay'   :
						case 'deliveryP' : $paramsSrt .= sdekExport::formatCurrency(array('TO'=>$reqParams['currency'],'SUM'=>$parVal,'FORMAT'=>true))."<br>"; break;
						case 'NDSDelivery' :
						case 'NDSGoods'	   : $paramsSrt.= GetMessage('IPOLSDEK_NDS_'.$parVal)."<br>"; break;
						case 'departure':
						case 'location' : $city = sqlSdekCity::getBySId($parVal);
											$paramsSrt.= $city['NAME']." (".$city['REGION'].")<br>";
						break;
                        default        : $paramsSrt.=$parVal."<br>"; break;
					}
				}

				$message=unserialize($request['MESSAGE'], ['allowed_classes' => false]);
				if($message && count($message))
					$message=implode('<br>',$message);
				else
					$message='';

				$addClass='';
				if($request['STATUS']=='OK')
					$addClass='IPOLSDEK_TblStOk';
				if($request['STATUS']=='ERROR')
					$addClass='IPOLSDEK_TblStErr';
				if($request['STATUS']=='TRANZT')
					$addClass='IPOLSDEK_TblStTzt';
				if($request['STATUS']=='DELETE')
					$addClass='IPOLSDEK_TblStDel';
				if($request['STATUS']=='STORE')
					$addClass='IPOLSDEK_TblStStr';
				if($request['STATUS']=='CORIER')
					$addClass='IPOLSDEK_TblStCor';
				if($request['STATUS']=='PVZ')
					$addClass='IPOLSDEK_TblStPVZ';
				if($request['STATUS']=='OTKAZ')
					$addClass='IPOLSDEK_TblStOtk';
				if($request['STATUS']=='DELIVD')
					$addClass='IPOLSDEK_TblStDvd';

				if($isConverted){
					if($request['SOURCE'] == 1){
						$oId = self::oIdByShipment($request['ORDER_ID']);
						$arActions = array(
							'link'    => '/bitrix/admin/sale_order_shipment_edit.php?order_id='.$oId.'&shipment_id='.$request['ORDER_ID'].'&lang=ru',
							'delete'  => 'IPOLSDEK_setups.table.delReq('.$request['ORDER_ID'].',\\\'shipment\\\');',
							'print'   => 'IPOLSDEK_setups.table.printer.get('.$request['ORDER_ID'].',\\\'shipment\\\',\\\''.PrintHandler::PRINT_FORM_ORDER.'\\\')',
							'shtrih'  => 'IPOLSDEK_setups.table.printer.get('.$request['ORDER_ID'].',\\\'shipment\\\',\\\''.PrintHandler::PRINT_FORM_BARCODE.'\\\')',
							'destroy' => 'IPOLSDEK_setups.table.killReq('.$request['ORDER_ID'].',\\\'shipment\\\')',
						);
					}else
						$arActions = array(
							'link'    => '/bitrix/admin/sale_order_view.php?ID='.$request['ORDER_ID'].'&lang=ru',
							'delete'  => 'IPOLSDEK_setups.table.delReq('.$request['ORDER_ID'].',\\\'order\\\');',
							'print'   => 'IPOLSDEK_setups.table.printer.get('.$request['ORDER_ID'].',\\\'order\\\',\\\''.PrintHandler::PRINT_FORM_ORDER.'\\\')',
							'shtrih'  => 'IPOLSDEK_setups.table.printer.get('.$request['ORDER_ID'].',\\\'order\\\',\\\''.PrintHandler::PRINT_FORM_BARCODE.'\\\')',
							'destroy' => 'IPOLSDEK_setups.table.killReq('.$request['ORDER_ID'].',\\\'order\\\')',
						);
				}else
					$arActions = array(
						'link'    => 'sale_order_detail.php?ID='.$request['ORDER_ID'].'&lang=ru',
						'delete'  => 'IPOLSDEK_setups.table.delReq('.$request['ORDER_ID'].',\\\'order\\\');',
						'print'   => 'IPOLSDEK_setups.table.printer.get('.$request['ORDER_ID'].',\\\'order\\\',\\\''.PrintHandler::PRINT_FORM_ORDER.'\\\')',
						'shtrih'  => 'IPOLSDEK_setups.table.printer.get('.$request['ORDER_ID'].',\\\'order\\\',\\\''.PrintHandler::PRINT_FORM_BARCODE.'\\\')',
						'destroy' => 'IPOLSDEK_setups.table.killReq('.$request['ORDER_ID'].',\\\'order\\\')',
					);

				$contMenu='<td class="adm-list-table-cell adm-list-table-popup-block" onclick="BX.adminList.ShowMenu(this.firstChild,[{\'DEFAULT\':true,\'GLOBAL_ICON\':\'adm-menu-edit\',\'DEFAULT\':true,\'TEXT\':\''.GetMessage('IPOLSDEK_STT_TOORDR').'\',\'ONCLICK\':\'BX.adminPanel.Redirect([],\\\''.$arActions['link'].'\\\', event);\'}';
				if($request['STATUS']=='ERROR' || $request['STATUS']=='NEW' || $request['STATUS']=='DELETE')
					$contMenu.=',{\'GLOBAL_ICON\':\'adm-menu-delete\',\'TEXT\':\''.GetMessage('IPOLSDEK_JSC_SOD_DELETE').'\',\'ONCLICK\':\''.$arActions['delete'].'\'}';
				else
					$contMenu.=',{\'GLOBAL_ICON\':\'adm-menu-view\',\'TEXT\':\''.GetMessage('IPOLSDEK_JSC_SOD_FOLLOW').'\',\'ONCLICK\':\'IPOLSDEK_setups.table.follow('.$request['SDEK_ID'].');\'}';
				if(!in_array($request['STATUS'],array('NEW','ERROR','DELETE','DELIVD','OTKAZ')))
					$contMenu.=',{\'GLOBAL_ICON\':\'adm-menu-move\',\'TEXT\':\''.GetMessage('IPOLSDEK_JSC_SOD_CHECK').'\',\'ONCLICK\':\'IPOLSDEK_setups.table.checkState('.$request['SDEK_ID'].');\'}';
				if($request['STATUS']=='OK'){
					$contMenu.=',{\'TEXT\':\''.GetMessage('IPOLSDEK_JSC_SOD_PRNTSH').'\',\'ONCLICK\':\''.$arActions['print'].'\'}';
					$contMenu.=',{\'TEXT\':\''.GetMessage('IPOLSDEK_JSC_SOD_SHTRIH').'\',\'ONCLICK\':\''.$arActions['shtrih'].'\'}';
					$contMenu.=',{\'GLOBAL_ICON\':\'adm-menu-delete\',\'TEXT\':\''.GetMessage('IPOLSDEK_JSC_SOD_DESTROY').'\',\'ONCLICK\':\''.$arActions['destroy'].'\'}';
				}
				$contMenu.='])"><div class="adm-list-table-popup"></div></td>';
				$strHtml.='<tr class="adm-list-table-row '.$addClass.'">
								'.$contMenu.'
								<td class="adm-list-table-cell"><div>'.$request['ID'].'</div></td>
								<td class="adm-list-table-cell"><div>'.$request['MESS_ID'].'</div></td>
								<td class="adm-list-table-cell"><div><a href="'.$arActions['link'].'" target="_blank">'.$request['ORDER_ID'].'</a></div></td>
								<td class="adm-list-table-cell"><div>'.$request['STATUS'].'</div></td>
								<td class="adm-list-table-cell"><div>'.$request['SDEK_ID'].'</div></td>';
				if($isConverted)
					$strHtml.='<td class="adm-list-table-cell"><div>'.(($request['SOURCE'] == 1)?GetMessage('IPOLSDEK_STT_shipment'):GetMessage('IPOLSDEK_STT_order')).'</div></td>';
				$strHtml.='<td class="adm-list-table-cell"><div><a href="javascript:void(0)" onclick="IPOLSDEK_setups.table.shwPrms($(this).siblings(\'div\'))">'.GetMessage('IPOLSDEK_STT_SHOW').'</a><div style="height:0px; overflow:hidden">'.$paramsSrt.'</div></div></td>
								<td class="adm-list-table-cell"><div>'.$message.'</div></td>
								<td class="adm-list-table-cell"><div>'.date("d.m.y H:i",$request['UPTIME']).'</div></td>';
				if(count($accFullCnt)>1){
					$acc = ($request['ACCOUNT']) ? $request['ACCOUNT'] : $accountBase;
					$strHtml.='<td class="adm-list-table-cell IPOLSDEK_account" title="'.((array_key_exists($acc,$accounts)) ? (($accounts[$acc]['LABEL']) ? $accounts[$acc]['LABEL'] : $accounts[$acc]['ACCOUNT']) : GetMessage("IPOLSDEK_TABLE_ACCINACTIVE")).'">'.$acc.'</td>';
				}
				$strHtml.='</tr>';
			}

			echo json_encode(
				self::zajsonit(
					array(
						'ttl'  => $requests->NavRecordCount,
						'mP'   => $requests->NavPageCount,
						'pC'   => $requests->NavPageSize,
						'cP'   => $requests->NavPageNomer,
						'sA'   => $requests->NavShowAll,
						'html' => $strHtml
					)
				)
			);
		}


		/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
														Функции для печати
			== getOrderInvoice ==  == getOrderShtrih ==  == getOrderPrint ==  == killOldInvoices == == displayActPrint ==  == OnBeforePrologHandler ==
		()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/

        /**
         * @deprecated
         */
		static function getOrderInvoice($orders){
			if(!is_array($orders))
				$orders = array('order' => $orders);
			
			return self::getOrderPrint($orders,'invoice');
		}

        /**
         * @deprecated
         */
		static function getOrderShtrih($orders){
			if(!is_array($orders))
				$orders = array('order' => $orders);
			
			return self::getOrderPrint($orders,'shtrih');
		}

        /**
         * @deprecated
         */
		static function getOrderPrint($orders,$type='invoice'){ // получаем квитанцию от сдека
			self::killOldInvoices(); //удаляем старые квитанции
			if(!$orders){
				return array(
					'result' => 'error',
					'error'  => 'No order id'
				);
			}
	
			if(!in_array($type,array('shtrih','invoice'))){
				return array(
					'result' => 'error',
					'error'  => 'Unknown print format: '.$type
				);
			}

			$arAccPrints = array();
			$defAccount = self::getBasicAuth(true);
			$arMade = array();
			foreach($orders as $mode => $IDs){
				if(!is_array($IDs)){
					$orders[$mode] = array($IDs);
				} elseif(empty($IDs)){
                    continue;
                }
				$requests = sqlSdekOrders::select(array(),array("ORDER_ID"=>$IDs,"SOURCE"=>($mode == 'order')?0:1));
				while($request=$requests->Fetch()){
					if($request['SDEK_ID']){
						$accId = ($request['ACCOUNT']) ? $request['ACCOUNT'] : $defAccount;
						if(!array_key_exists($accId,$arAccPrints))
							$arAccPrints[$accId] = array('XML' => '', 'cnt' => 0);
						$arAccPrints[$accId]['XML'] .= '<Order DispatchNumber="'.$request['SDEK_ID'].'"/>';
						$arAccPrints[$accId]['cnt']++;
						$arMade[$mode][]=$request['ORDER_ID'];
					}
				}
			}
			if(!count($arMade)){
				return array(
					'result' => 'error',
					'error'  => 'No orders founded'
				);
			}
			
			$copies = ($type == 'shtrih') ? (int)\Ipolh\SDEK\option::get("numberOfStrihs") : (int)\Ipolh\SDEK\option::get("numberOfPrints");
			if(!$copies) $copies = 1;
			
			$format = ($type == 'shtrih') ? 'PrintFormat="'.\Ipolh\SDEK\option::get("formatOfStrihs").'"' : "";
			
			$method = ($type == 'shtrih') ? 'ordersPackagesPrint' : 'orders_print';
			
			$container = ($type == 'shtrih') ? 'OrdersPackagesPrint ' : 'OrdersPrint';

			$arReturn = array(
				'result' => '',
				'error'  => '',
				'files'  => array()
			);

			foreach($arAccPrints as $accId => $data) {
                $headers = self::getXMLHeaders($accId);
                $request = '<?xml version="1.0" encoding="UTF-8" ?>
	<' . $container . ' Date="' . $headers['date'] . '" Account="' . $headers['account'] . '" Secure="' . $headers['secure'] . '"  OrderCount="' . $data['cnt'] . '" CopyCount="' . $copies . '" ' . $format . '>' . $data['XML'] . "</" . $container . ">";

                $result = self::sendToSDEK($request, $method);

                if (strpos($result['result'], '<') === 0) {
                    $answer = simplexml_load_string($result['result']);
                    $errAnswer = '';
                    foreach ($answer->$container as $print)
                        $errAnswer .= $print['Msg'] . ". ";
                    foreach ($answer->Order as $print)
                        $errAnswer .= $print['Msg'] . ". ";

                    $arReturn['error'] .= $errAnswer;
                } elseif (strpos($result['result'], '{') === 0){
                    $answer = json_decode($result['result'],true);
                    $errAnswer = '';
                    if(!empty($answer) && array_key_exists('alerts',$answer) && is_array($answer['alerts'])){
                        foreach ($answer['alerts'] as $arError){
                            $errAnswer .= $arError['msg'].". ";
                        }
                    }

                    $arReturn['error'] .= $errAnswer;
                } else {
					if(!file_exists($_SERVER['DOCUMENT_ROOT']."/upload/".self::$MODULE_ID))
						mkdir($_SERVER['DOCUMENT_ROOT']."/upload/".self::$MODULE_ID);
                    $fileName = time()."_".$accId.'_'.md5($headers['account'].time()).md5($headers['secure'].time());
					file_put_contents($_SERVER['DOCUMENT_ROOT']."/upload/".self::$MODULE_ID."/".$fileName.".pdf",$result['result']);

					$arReturn['files'][] = $fileName.".pdf";
				}
			}

			if(count($arReturn['files'])){
				$arReturn['result'] = 'ok';

				$ordersNotFound = '';
				foreach($arMade as $mode => $ids){
					$diff = array_diff($orders[$mode],$ids);
					if(count($diff))
						$ordersNotFound .= implode(', ',$diff).", ";
				}

				if($ordersNotFound){
					if($arReturn['errors'])
						$arReturn['errors'] .= "; ";
					$arReturn['errors'] .= substr($arReturn['errors'],0,strlen($arReturn['errors'])-2);
				}

				if(!$arReturn['errors'])
					unset($arReturn['errors']);
			}else{
				$arReturn['result'] = 'error';
				unset($arReturn['files']);
			}
			return $arReturn;
		}

        /**
         * @deprecated
         */
		static function killOldInvoices(){ // удаляет старые файлы с инвойсами
			$dirPath = $_SERVER['DOCUMENT_ROOT']."/upload/".self::$MODULE_ID."/";
			if(file_exists($dirPath)){
				$dirContain = scandir($dirPath);
				foreach($dirContain as $contain){
					if(strpos($contain,'.pdf')!==false && (time() - (int)filemtime($dirPath.$contain)) > 600)
						unlink($dirPath.$contain);
				}
			}
		}

		public static function displayActPrint(&$list)
        {
            if ($GLOBALS['APPLICATION']->GetCurPage() == '/bitrix/admin/sale_order.php') {
                $list->arActions['ipolSDEK_printOrderActs'] = [
                    'action' => "IPOLSDEK_actions.printer.get('".PrintHandler::PRINT_FORM_ORDER."', ".CUtil::PhpToJSObject(\Ipolh\SDEK\option::get('prntActOrdr') == 'A').")",
                    'value'  => 'ipolSDEK_printOrderActs',
                    'name'   => GetMessage('IPOLSDEK_SIGN_PRNTSDEK')
                ];
                $list->arActions['ipolSDEK_printOrderShtrihs'] = [
                    'action' => "IPOLSDEK_actions.printer.get('".PrintHandler::PRINT_FORM_BARCODE."', false)",
                    'value'  => 'ipolSDEK_printOrderShtrihs',
                    'name'   => GetMessage('IPOLSDEK_SIGN_SHTRIHSDEK')
                ];
			}
		}

        /**
         * @deprecated
         */
		public static function OnBeforePrologHandler()
        {
        }

		static function formActArray(){
			if(!cmodule::includeModule('sale')) return;
			if(self::canShipment())
				$arIds = array('order'=>explode(":",$_REQUEST['orders']),'shipment'=>explode(":",$_REQUEST['shipments']));
			else
				$arIds = array('order'=>explode(":",$_REQUEST['ORDER_ID']));
			$arOrders = array();
			$ttlPay = 0;
			$dWeight = \Ipolh\SDEK\option::get('weightD');
			foreach($arIds as $mode => $arId)
				if(count($arId))
					foreach($arId as $id){
						$req=sqlSdekOrders::select(array(),array('ORDER_ID'=>$id,'SOURCE'=>($mode == 'shipment') ? 1 : 0))->Fetch();
						if(!$req)
							continue;
						$params = unserialize($req['PARAMS'], ['allowed_classes' => false]);
						$baze  = ($mode == 'shipment') ? self::getShipmentById($id) : CSaleOrder::GetById($id);
						$price = array_key_exists('toPay',$params) ? $params['toPay'] : ((float)($baze['PRICE'] - $baze['PRICE_DELIVERY']));
						$toPay = (array_key_exists('toPay',$params) && array_key_exists('deliveryP',$params)) ? ($params['toPay'] + $params['deliveryP']) : (($params['isBeznal']=='Y') ? 0 : (float)$baze['PRICE']);
						$arOrders[] = array(
							'ID'     => ($baze['ACCOUNT_NUMBER']) ? $baze['ACCOUNT_NUMBER'] : $id,
							'SDEKID' => $req['SDEK_ID'],
							'WEIGHT' => ($params['GABS']['W'])?$params['GABS']['W']:($dWeight)/1000,
							'PRICE'  => $price,
							'TOPAY'  => $toPay
						);
						$ttlPay+=$price;
					}
			return array('arOrders' => $arOrders, 'ttlPay' => $ttlPay);
		}


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
												Отображение опций
	== placeFAQ ==  == placeHint ==  == getSDEKCity ==  == printSender ==  == placeStatuses ==  == makeSelect ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


		static function placeFAQ($code){?>
				<a class="ipol_header" onclick="$(this).next().toggle(); return false;"><?=GetMessage('IPOLSDEK_FAQ_'.$code.'_TITLE')?></a>
				<div class="ipol_inst"><?=GetMessage('IPOLSDEK_FAQ_'.$code.'_DESCR')?></div>
		<?php }

		static function placeHint($code){?>
			<div id="pop-<?=$code?>" class="b-popup" style="display: none; ">
				<div class="pop-text"><?=GetMessage("IPOLSDEK_HELPER_".$code)?></div>
				<div class="close" onclick="$(this).closest('.b-popup').hide();"></div>
			</div>
		<?php }

		static function getSDEKCity($city){
			$cityId = self::getNormalCity($city);
			$SDEKcity = self::getSQLCityBI($cityId);
			return $SDEKcity;
		}

		static function printSender($city){
			$SDEKcity = self::getSDEKCity($city);
			if(!$SDEKcity)
				echo "<tr><td colspan='2'>".GetMessage('IPOLSDEK_LABEL_NOSDEKCITY')."</td><tr>";
			else{
			    \Ipolh\SDEK\option::set('departure',$SDEKcity['BITRIX_ID']);
				echo "<tr><td>".GetMessage('IPOLSDEK_OPT_departure')."</td><td>".($SDEKcity['NAME'])."</td><tr>";
			}
		}

		static function placeStatuses($option){
			if(self::canShipment()){
				$arStatuses = array();
				$arStShipment = array();
				foreach($option as $key => $val)
					if(strpos($val[0],'status') !== false){
						unset($option[$key]);
						$arStatuses[] = $val;
					}elseif(strpos($val[0],'stShipment') !== false){
						unset($option[$key]);
						$arStShipment[] = $val;
					}
				ShowParamsHTMLByArray($option);
			?><tr><td></td><td><div class='IPOLSDEK_sepTable'><?=GetMessage('IPOLSDEK_STT_order')?></div><div class='IPOLSDEK_sepTable'><?=GetMessage('IPOLSDEK_STT_shipment')?></div></td></tr><?php
			foreach($arStatuses as $key => $description){?>
				<tr>
					<td><?=$description[1]?></td>
					<td>
						<div class='IPOLSDEK_sepTable'>
							<?php self::makeSelect($description[0], $description[4], \Ipolh\SDEK\option::get($description[0])); ?>
						</div>
						<div class='IPOLSDEK_sepTable'>
                <?php
							$name = str_replace('status','stShipment',$description[0]);
							self::makeSelect($name,$arStShipment[$key][4],\Ipolh\SDEK\option::get($name));?>
						</div>
					</td>
				</tr>
			<?php }
			}else{
				foreach($option as $key => $descr)
					if(strpos($descr[0],'stShipment') === 0)
						unset($option[$key]);
				ShowParamsHTMLByArray($option);
			}
		}

		static function makeSelect($id,$vals,$def=false,$atrs=''){?>
			<select <?php if($id) { ?>name='<?= $id ?>' id='<?= $id ?>'<?php } ?> <?= $atrs ?>>
			<?php foreach($vals as $val => $sign) { ?>
				<option value='<?=$val?>' <?=($def == $val)?'selected':''?>><?=$sign?></option>
			<?php } ?>
			</select>
		<?php }

		static function getCountryHeaderCities($params = array('country' => 'rus')){
			$allCities = sqlSdekCity::getCitiesByCountry($params['country'],true);
			echo "<table>";
			if(!$allCities->nSelectedCount)
				echo "<tr><td>".GetMessage("IPOLSDEK_NO_CITIES_FOUND")."</td></tr>";
			else{
				$arErrCities = sdekHelper::getErrCities($params['country']);
				echo '<tr class="IPOLSDEK_city_header"><td class="IPOLSDEK_city_header" onclick="IPOLSDEK_setups.cities.callCities(\'success\')">'.GetMessage("IPOLSDEK_HDR_success").' ('.$allCities->nSelectedCount.')</td></tr><tr><td id="IPOLSDEK_city_success"></td></tr>';

				foreach(array('many','notFound') as $type)
					if(count($arErrCities[$type]) > 0)
						echo '<tr class="IPOLSDEK_city_header"><td class="IPOLSDEK_city_header" onclick="IPOLSDEK_setups.cities.callCities(\''.$type.'\')">'.GetMessage("IPOLSDEK_HDR_$type").' ('.count($arErrCities[$type]).')</td></tr><tr><td id="IPOLSDEK_city_'.$type.'"></td></tr>';
			}
			echo "</table>";
		}

		static function getCountryDetailCities($params){
			echo "%".$params['country']."%";
			switch($params['mode']){
				case  'success':
					echo '<table class="adm-list-table">
						<thead>
								<tr class="adm-list-table-header">
									<td class="adm-list-table-cell" style="width: 80px;">'.GetMessage("IPOLSDEK_HDR_BITRIXID").'</td>
									<td class="adm-list-table-cell" style="width: 80px;">'.GetMessage("IPOLSDEK_HDR_SDEKID").'</td>
									<td class="adm-list-table-cell">'.GetMessage("IPOLSDEK_HDR_REGION").'</td>
									<td class="adm-list-table-cell">'.GetMessage("IPOLSDEK_HDR_CITY").'</td>
								</tr>
						</thead>
						<tbody>';
					$allCities = sqlSdekCity::getCitiesByCountry($params['country']);
					while($element=$allCities->Fetch())
						echo '<tr class="adm-list-table-row">
								<td class="adm-list-table-cell">'.$element['BITRIX_ID'].'</td>
								<td class="adm-list-table-cell">'.$element['SDEK_ID'].'</td>
								<td class="adm-list-table-cell">'.$element['REGION'].'</td>
								<td class="adm-list-table-cell">'.$element['NAME'].'</td>
							</tr>';

					echo '</tbody></table>';
				break;
				case 'many':
					$multipleMatchedCities = sdekHelper::getMultipleMatchedCities($params['country']);
					if(count($multipleMatchedCities) > 0){
						echo '<table class="adm-list-table">
							<thead>
									<tr class="adm-list-table-header">
										<td class="adm-list-table-cell">'.GetMessage("IPOLSDEK_HDR_BITRIXNM").'</td>
										<td class="adm-list-table-cell">'.GetMessage("IPOLSDEK_HDR_SDEKNM").'</td>
										<td class="adm-list-table-cell">'.GetMessage("IPOLSDEK_HDR_VARIANTS").'</td>
									</tr>
							</thead>
							<tbody>';

						foreach($multipleMatchedCities as $bitrixId => $arCities){
							$bitrix = false;
							if(self::isLocation20()){
								$city   = sdekCityGetter::getCityChain($bitrixId);
								if($city)
									$bitrix = array('REGION_NAME' => $city['REGION'],'CITY_NAME' => $city['CITY']);
							} else {
								$bitrix = CSaleLocation::GetList(array(),array("ID"=>$bitrixId,"REGION_LID"=>LANGUAGE_ID,"CITY_LID"=>LANGUAGE_ID))->Fetch();
							}
							if(!$bitrix)
								$bitrix = CSaleLocation::GetList(array(),array("ID"=>$bitrixId))->Fetch();
							
							$location = $bitrix['REGION_NAME'].", ".$bitrix['CITY_NAME']." (".$bitrixId.")";

							echo '<tr class="adm-list-table-row"><td class="adm-list-table-cell">'.$location.'</td><td class="adm-list-table-cell">'.$arCities['takenLbl'].'</td><td class="adm-list-table-cell">';

							foreach($arCities['sdekCity'] as $sdekId => $descr)
								echo $descr['region'].", ".$descr['name']."<br>";

							echo '</td></tr>';
						}

						echo '</tbody></table>';
					}
				break;
				case 'notFound':
					$notFoundedCities = sdekHelper::getNotFoundedCities($params['country']);
					if(count($notFoundedCities) > 0){
						echo '<table class="adm-list-table">
								<thead>
										<tr class="adm-list-table-header">
											<td class="adm-list-table-cell">'.GetMessage("IPOLSDEK_HDR_SDEKID").'</td>
											<td class="adm-list-table-cell">'.GetMessage("IPOLSDEK_HDR_REGION").'</td>
											<td class="adm-list-table-cell">'.GetMessage("IPOLSDEK_HDR_CITY").'</td>
										</tr>
								</thead>
								<tbody>';

						foreach($notFoundedCities as $arCity)
							echo '<tr class="adm-list-table-row">
									<td class="adm-list-table-cell">'.$arCity['sdekId'].'</td>
									<td class="adm-list-table-cell">'.$arCity['region'].'</td>
									<td class="adm-list-table-cell">'.$arCity['name'].'</td>
								</tr>';

						echo '</tbody></table>';
					}
				break;
			}
		}

	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
												Функции для опций
	== killSchet ==  == killUpdt ==  == clearCache ==  == printOrderInvoice ==  == killReqOD ==  == delReqOD ==  == callOrderStates ==  == callUpdateList ==  == goSlaughterCities ==  == senders ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


		static function killSchet(){ // Сбрасываем счетчик заявок в опциях
			if(!self::isAdmin()) return false;
			\Ipolh\SDEK\option::set('schet',0);
			return true;
		}

		static function killUpdt($wat){ // Убираем информацию об обновлении
			if(unlink($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".self::$MODULE_ID."/hint.txt"))
				echo 'done';
			else
				echo 'fail';
		}

		static function clearCache($noFdb=false){//Очистка кэша
			$obCache = new CPHPCache();
			$obCache->CleanDir('/IPOLSDEK/');
			if(!$noFdb)
				echo "Y";
		}

        /**
         * @deprecated
         */
		static function printOrderInvoice($params)
        {
			if(!array_key_exists('mode',$params))
				$params['mode'] = 'order';
			$resPrint = self::getOrderInvoice(array($params['mode'] => $params['oId']));
			echo json_encode(self::zajsonit($resPrint));
		}

        /**
         * @deprecated
         */
		static function printOrderShtrih($params)
        {
			if(!array_key_exists('mode',$params))
				$params['mode'] = 'order';
			$resPrint = self::getOrderShtrih(array($params['mode'] => $params['oId']));
			echo json_encode(self::zajsonit($resPrint));
		}

		static function killReqOD($params,$mode=false){// удаление заявки из СДЕКа
			if(!self::isAdmin()) return false;
			$oid = (is_array($params)) ? $params['oid'] : $params;
			if(!$mode)
				$mode = (array_key_exists('mode',$params)) ? $params['mode'] : 'order';
			if(sdekdriver::deleteRequest($oid,$mode)){
				if($mode == 'order')
					self::killAutoReq($oid);
				echo "GD:".GetMessage("IPOLSDEK_DRQ_DELETED");
			}else
				echo self::getAnswer();
		}

		static function delReqOD($params,$mode=false){// удаление заявки из БД
			if(!self::isAdmin()) return false;
			$oid = (is_array($params)) ? $params['oid'] : $params;
			if(!$mode)
				$mode = (array_key_exists('mode',$params)) ? $params['mode'] : 'order';
			if(self::CheckRecord($oid,$mode)){
				sqlSdekOrders::Delete($oid,$mode);
				if($mode == 'order')
					self::killAutoReq($oid);
			}
			echo GetMessage("IPOLSDEK_DRQ_DELETED");
		}

        /**
         * Launching "check order statuses" from the options
         * @return void
         */
        public static function checkUpdateStates()
        {
            $ret = array('MESSAGE' => '','COUNT' => 0, 'ERR' => false);
            $numberOfOrders = \Ipolh\SDEK\StatusHandler::getNumberOfActiveOrders();
            if (!$numberOfOrders) {
                $ret['MESSAGE'] = GetMessage('IPOLSDEK_callOrderStates_noNeed');
            } else {
                $ret['MESSAGE'] = GetMessage('IPOLSDEK_callOrderStates_proceed');
                $ret['COUNT']   = ceil(\Ipolh\SDEK\StatusHandler::getNumberOfActiveOrders() / \Ipolh\SDEK\option::get('orderStatusesLimit'));
            }

            echo json_encode(self::zajsonit($ret));
        }

        /**
         * Calls order statuses from module options
         * @return void
         */
		public static function callOrderStates()
        {
            $result = \Ipolh\SDEK\StatusHandler::getOrderStates();
            $ret = array('MESSAGE' => date('d.m.Y H:i:s', \Ipolh\SDEK\option::get('statCync')), 'ERR' => self::getErrors());
            echo json_encode(self::zajsonit($ret));
		}

		static function callUpdateList($params){ // запрос на синхронизацию из опций
			$arReturn = false;
			if(array_key_exists('full',$params) && $params['full'] && !array_key_exists('listDone',$params))
				if(!self::updateList(true))
					$arReturn = array(
						'result' => 'error',
						'text'   => GetMessage('IPOLSDEK_UPDT_ERR'),
					);

			if(!$arReturn){
				$us=self::cityUpdater();
				$arReturn = array('result' => $us['result']);
				switch($us['result']){
					case 'error'   : $arReturn['text'] = GetMessage("IPOLSDEK_SYNCTY_ERR_HAPPENING")." ".$us['error']; break;
					case 'end'     : $arReturn['text'] = (array_key_exists('full',$params) && $params['full']) ? GetMessage('IPOLSDEK_UPDT_DONE').date("d.m.Y H:i:s",filemtime($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".self::$MODULE_ID."/list.json")) : GetMessage('IPOLSDEK_SYNCTY_LBL_SCOD'); break;
					case 'country' : $arReturn['text'] = GetMessage('IPOLSDEK_SYNCTY_CNTRCTDONE').GetMessage('IPOLSDEK_SYNCTY_'.$us['country']); break;
					default        : $arReturn['text'] = GetMessage('IPOLSDEK_SYNCTY_LBL_PROCESS')." ".$us['done']."/".$us['total']; break;
				}
			}

			echo json_encode(self::zajsonit($arReturn));
		}

		static function goSlaughterCities($params){ // переопределение городов
			if(!self::isAdmin()) return false;
			$result = self::slaughterCities();
			if($result == 'done'){
				$tmpExportFile = $_SERVER['DOCUMENT_ROOT']."/bitrix/js/".self::$MODULE_ID."/tmpExport.txt";
				if(file_exists($tmpExportFile)){
					unlink($tmpExportFile);
				}
				$us=self::cityUpdater();
				if($us['result']!='error')
					$arResult = array(
						'text' => ($us['result'] == 'done') ? GetMessage("IPOLSDEK_SYNCTY_LBL_SCOD") : '',
						'status' => $us['result']
					);
				else
					$arResult = array(
						'text' => GetMessage("IPOLSDEK_ERRLOG_ERRSUNCCITY")." ".$us['error'],
						'status' => 'error'
					);
			}else
				$arResult = array(
					'text'   => GetMessage("IPOLSDEK_DELCITYERROR")." ".$result,
					'status' => 'error'
				);

			if($params['mode'] == 'json')
				echo json_encode(self::zajsonit($arResult));
			else
				return $arResult;
		}

        /**
         * @deprecated
         */
		static function senders($params = false){
			if(!self::isAdmin('R')) return false;
			$path = $_SERVER['DOCUMENT_ROOT'].'/bitrix/tools/'.self::$MODULE_ID.'/senders.txt';
			if($params){
				$dir = $_SERVER['DOCUMENT_ROOT'].'/bitrix/tools/'.self::$MODULE_ID;
				if(!file_exists($dir))
					mkdir($dir);
				return file_put_contents($path,serialize($params));
			}
			elseif(file_exists($path))
				return unserialize(file_get_contents($path), ['allowed_classes' => false]);
			else
				return false;
		}

		static function getAccountSelect($params){
			$accounts = array(0=>GetMessage('IPOLSDEK_TC_DEFAULT')) + sqlSdekLogs::getAccountsList();
			$soloAccount = (count($accounts) <= 2);
			if($soloAccount)
				echo $params['country'].'<-%->'.GetMessage('IPOLSDEK_TC_DEFAULT');
			else{
				echo $params['country'].'<-%->';
				sdekOption::makeSelect('countries['.$params['country'].'][acc]',$accounts,$params['default']);
			}
		}

		static function ressurect(){
		    \Ipolh\SDEK\option::set('sdekDeadServer',false);
		}

	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
												Функции для агентов
	== agentUpdateList ==  == agentOrderStates ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


		static function agentUpdateList(){ // вызов обновления списка городов, самовывозов и услуг
			if(!self::updateList())
				self::errorLog(GetMessage('IPOLSDEK_UPDT_ERR'));
			self::cityUpdater();
			return 'sdekOption::agentUpdateList();';
		}

		public static function agentOrderStates()
        {
            $result = \Ipolh\SDEK\StatusHandler::getOrderStates();
			return 'sdekOption::agentOrderStates();';
		}


	/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
													Синхронизации
		== getOrderStates ==  == updateList == == cityUpdater == == requestCityFile ==  == slaughterCities ==  == getOrderState ==
	()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/

        /**
         * @deprecated
         * Requests and sets order states
         */
		public static function getOrderStates()
        {
			if (!cmodule::includemodule('sale')) {
                self::errorLog(GetMessage("IPOLSDEK_ERRLOG_NOSALEOOS"));
                return false;
            }

            if (sqlSdekOrders::getDataCount() > 0) {
                $orderStatusesLimit = (int)\Ipolh\SDEK\option::get('orderStatusesLimit');
                if ($orderStatusesLimit < 1)
                    $orderStatusesLimit = 100;

                $orderStatusesUptime = (int)\Ipolh\SDEK\option::get('orderStatusesUptime');
                if ($orderStatusesUptime < 1)
                    $orderStatusesUptime = 60;

                $ordersData = [];
                $dbOrders = sqlSdekOrders::select(
                    array('UPTIME', 'ASC'),
                    array('OK' => true, 'STATUS' => array('OK', 'DELETE', 'STORE', 'TRANZT', 'CORIER', 'PVZ'), '>UPTIME' => strtotime('-'.$orderStatusesUptime.' days')),
                    array('nPageSize' => $orderStatusesLimit, 'iNumPage' => 1)
                );
                while ($tmp = $dbOrders->Fetch()) {
                    if (!empty($tmp['ACCOUNT']) && !empty($tmp['SDEK_ID'])) {
                        $ordersData[$tmp['ACCOUNT']][] = '<Order DispatchNumber="'.$tmp['SDEK_ID'].'"/>';
                    }
                }
                unset($dbOrders);

                $accounts = sqlSdekLogs::getAccountsList();

                foreach ($accounts as $id => $acc) {
                    $ordersStr = '';
                    if (!empty($ordersData) && is_array($ordersData[$id])) {
                        foreach ($ordersData[$id] as $xmlString) {
                            $ordersStr .= $xmlString;
                        }
                    }

                    $headers = self::getXMLHeaders($id);

                    $XML = '<?xml version="1.0" encoding="UTF-8" ?>
						<StatusReport Date="'.$headers['date'].'" Account="'.$headers['account'].'" Secure="'.$headers['secure'].'">'.
                        $ordersStr.
                        '</StatusReport>
					';

                    if ($ordersStr) {
                        // At least one order founded
                        $result = self::sendToSDEK($XML, 'status_report_h');
                        \Ipolh\SDEK\Bitrix\Admin\Logger::statusCheck(array('Request' => $XML, 'Response' => $result['result']));

                        $arOrders = array();
                        if ($result['code'] != 200) {
                            self::errorLog(GetMessage("IPOLSDEK_GOS_UNBLSND") . GetMessage("IPOLSDEK_ERRORLOG_BADRESPOND") . $result['code']);
                        } else {
                            $xml = simplexml_load_string($result['result']);

                            foreach($xml->Order as $orderMess) {
                                if (!isset($orderMess['ErrorCode']) && empty((string)$orderMess['ErrorCode'])) // Skip orders with ERR_INVALID_DISPATCHNUMBER and other errors
                                {
                                    $arOrders[] = array(
                                        'DispatchNumber' => (string)$orderMess['DispatchNumber'],
                                        'State'			 => (int)$orderMess->Status['Code'],
                                        'Number'		 => (string)$orderMess['Number'],
                                        'Description'    => (string)$orderMess->Status['Description']
                                    );
                                }
                            }
                        }

                        if (count($arOrders))
                            self::setOrderStates($arOrders);
                    } else {
                        // No orders in corresponded statuses, nothing to do
                        \Ipolh\SDEK\Bitrix\Admin\Logger::statusCheck(array(
                            'Request' => $XML,
                            'Response' => 'Request not sended to server cause no orders in corresponded statuses (Zero orders in DB or all in final statuses)'
                        ));
                    }
                }
            }

			if (!self::$ERROR_REF) {
                \Ipolh\SDEK\option::set('statCync', time());
            }
		}

        /**
         * @deprecated
         */
		static function getOrderState($params){
			$arOrder = false;

			if(is_array($params)){
				if(array_key_exists('DispatchNumber',$params))
					$dNumber = $params['DispatchNumber'];
				else{
					$arOrder = sqlSdekOrders::select(array(),array('ID' => $params['ID']))->Fetch();
					$dNumber = $arOrder['DispatchNumber'];
				}
			}else
				$dNumber = $params;

			if(!cmodule::includemodule('sale')){self::errorLog(GetMessage("IPOLSDEK_ERRLOG_NOSALEOOS"));return false;}//без модуля sale делать нечего

			if(!$arOrder)
				$arOrder = sqlSdekOrders::select(array(),array('SDEK_ID' => $dNumber))->Fetch();

			$headers = self::getXMLHeaders(array('ID' => self::getOrderAcc($arOrder)));
			$return = false;

			$XML = '<?xml version="1.0" encoding="UTF-8" ?>
			<StatusReport Date="'.$headers['date'].'" Account="'.$headers['account'].'" Secure="'.$headers['secure'].'">
				<Order DispatchNumber="'.$dNumber.'"/>
			</StatusReport>
			';

			$result = self::sendToSDEK($XML,'status_report_h');
			\Ipolh\SDEK\Bitrix\Admin\Logger::statusCheck(array('Request' => $XML, 'Response'=> $result['result']));

			if($result['code'] != 200)
				self::errorLog(GetMessage("IPOLSDEK_GOS_UNBLSND").GetMessage("IPOLSDEK_ERRORLOG_BADRESPOND").$result['code']);
			else{
				$xml = simplexml_load_string($result['result']);
				$arOrder=array(array(
					'DispatchNumber' => (string)$xml->Order['DispatchNumber'],
					'State'			 => (int)$xml->Order->Status['Code'],
					'Number'		 => (string)$xml->Order['Number'],
					'Description'    => (string)$xml->Order->Status['Description']
				));
				self::setOrderStates($arOrder);
			}
		}

		public static function setOrderStates($arOrders){
			$arStateCorr = array(
				 1 => "OK",
				 2 => "DELETE",
				 3 => "STORE",
				 4 => "DELIVD",
				 5 => "OTKAZ",
				 6 => "TRANZT",
				 7 => "TRANZT",
				 8 => "TRANZT",
				 9 => "TRANZT",
				10 => "TRANZT",
				11 => "CORIER",
				12 => "PVZ",
				13 => "TRANZT",
				16 => "TRANZT",
				17 => "TRANZT",
				18 => "TRANZT",
				19 => "TRANZT",
				20 => "TRANZT",
				21 => "TRANZT",
				22 => "TRANZT",
                27 => "TRANZT",
                28 => "TRANZT",
                1000 => "TRANZT"
			);

			global $USER;
			if(!is_object($USER))
				$USER = new CUser();

			foreach($arOrders as $orderMess){
				if(array_key_exists($orderMess['State'],$arStateCorr)){// описан ли статус
					$curState = $arStateCorr[$orderMess['State']];
					$arOrder = sqlSdekOrders::select(array(),array('SDEK_ID' => $orderMess['DispatchNumber']))->Fetch();
					if(!$arOrder) // not from API
						continue;
					$mode = ($arOrder['SOURCE'] == 1 ) ? 'shipment' : 'order';
					if($curState == 'DELETE')
						sqlSdekOrders::Delete($arOrder['ORDER_ID'],$mode);
					if($arOrder['OK']){
						if(!sqlSdekOrders::updateStatus(array(
							"ORDER_ID" => $arOrder['ORDER_ID'],
							"STATUS"   => $curState,
							"SOURCE"   => $arOrder['SOURCE']
						)))
							self::errorLog(GetMessage('IPOLSDEK_GOS_HASERROR').GetMessage('IPOLSDEK_GOS_CANTUPDATEREQ').$arOrder['ORDER_ID'].". ".GetMessage('IPOLSDEK_GOS_STATUS').$curState.".");
						elseif($curState !== $arOrder['STATUS']){
							// update statuses in Bitrix only if got new status
							$newStat = \Ipolh\SDEK\option::get((($arOrder['SOURCE'] == 1)?"stShipment":"status").$curState);
							if($newStat && strlen($newStat) < 3){
								if($arOrder['SOURCE'] == 1){ // отправление
									$shipment = self::getShipmentById($arOrder['ORDER_ID']);
									if($shipment['STATUS_ID'] != $newStat)
										if(!self::setShipmentField($arOrder['ORDER_ID'],'STATUS_ID',$newStat))
											self::errorLog(GetMessage('IPOLSDEK_GOS_HASERROR').GetMessage('IPOLSDEK_GOS_CANTUPDATESHP').$arOrder['ORDER_ID'].". ".GetMessage('IPOLSDEK_GOS_STATUS').$curState.".");
								}else{ // заказ
									$order = CSaleOrder::GetByID($arOrder['ORDER_ID']);
									if($order['STATUS_ID'] != $newStat){
										if(!CSaleOrder::StatusOrder($arOrder['ORDER_ID'],$newStat))
											self::errorLog(GetMessage('IPOLSDEK_GOS_HASERROR').GetMessage('IPOLSDEK_GOS_CANTUPDATEORD').$arOrder['ORDER_ID'].". ".GetMessage('IPOLSDEK_GOS_STATUS').$curState.".");
									}
								}
							}

                            foreach(GetModuleEvents(self::$MODULE_ID, "onNewStatus", true) as $arEvent)
                                ExecuteModuleEventEx($arEvent,Array($arOrder['ORDER_ID'],$curState,$arOrder['SOURCE'],$arOrder['STATUS']));
							
							// оплаченность
							if(
								$orderMess['State'] == 4 && 
								\Ipolh\SDEK\option::get("markPayed") == 'Y' &&
								$arOrder['SOURCE'] != 1
							){
								$order = CSaleOrder::GetByID($arOrder['ORDER_ID']);
								if($order && $order['PAYED'] != 'Y'){
									if(self::isConverted()){
										$order = \Bitrix\Sale\Order::load($arOrder['ORDER_ID']); 
										if($order && is_object($order)){
											$paymentCollection = $order->getPaymentCollection(); 
											foreach($paymentCollection as $payment)
											if(!$payment->isPaid()){ 
												$payment->setPaid("Y"); 
												$order->save();   
											}
										} else {
											self::errorLog(GetMessage('IPOLSDEK_GOS_HASERROR').GetMessage('IPOLSDEK_GOS_CANTMARKPAYED').$arOrder['ORDER_ID'].". ");
										}
									}elseif(!CSaleOrder::PayOrder($arOrder['ORDER_ID'],"Y"))
										self::errorLog(GetMessage('IPOLSDEK_GOS_HASERROR').GetMessage('IPOLSDEK_GOS_CANTMARKPAYED').$arOrder['ORDER_ID'].". ");
								}
							}
						}
					}else // попытка оформить неподтвержденный заказ
						self::errorLog(GetMessage('IPOLSDEK_GOS_HASERROR').GetMessage('IPOLSDEK_GOS_BADREQTOUPDT'.$mode).$arOrder['ORDER_ID'].". ".GetMessage('IPOLSDEK_GOS_STATUS').$curState.".");
				}else
					self::errorLog(GetMessage("IPOLSDEK_GOS_HASERROR").GetMessage("IPOLSDEK_GOS_UNKNOWNSTAT").($orderMess['Number'])." : ".$orderMess['State']." (".$orderMess['Description']."). ".GetMessage("IPOLSDEK_GOS_NOTUPDATED"));
			}
		}

        /**
         * PVZ list update
         * @param bool $forced
         * @return mixed
         */
		static function updateList($forced = false) {
			self::ordersNum();
			$syncResult = \Ipolh\SDEK\PointsHandler::updatePoints(\Ipolh\SDEK\PointsHandler::REQUEST_TYPE_SDEK, $forced);

			if(!$syncResult['SUCCESS'] && \Ipolh\SDEK\option::get('logged')){
                $file=fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".self::$MODULE_ID."/hint.txt","a");
                fwrite($file,"<br><br><strong>".date('d.m.Y H:i:s')."</strong><br>".$syncResult['ERROR']);
                fclose($file);
            }

            return $syncResult['SUCCESS'];
		}

		static function cityUpdater($params=false){
			if(!$params)
				$params = array('timeout'=>false,'mode'=>false);
			cmodule::includeModule('sale');
			$countries = self::getCountryOptions();

			$exportClass = false;
			if(file_exists($_SERVER['DOCUMENT_ROOT']."/bitrix/js/".self::$MODULE_ID."/tmpExport.txt"))
				$exportClass = unserialize(file_get_contents($_SERVER['DOCUMENT_ROOT']."/bitrix/js/".self::$MODULE_ID."/tmpExport.txt"), ['allowed_classes' => ['cityExport']]);
			else{
				foreach($countries as $country => $val)
					if($val['act'] == 'Y'){
						$exportClass = new cityExport($country,$params['timeout']);
						break;
					}
			}
			if($exportClass){
				$exportClass->start();
				$result = $exportClass->result;
				if($result['result'] == 'error')
					self::errorLog(GetMessage("IPOLSDEK_ERRLOG_ERRSUNCCITY")." ".$exportClass->result['error']);
				elseif($result['result'] == 'end'){
					$result['country'] = $exportClass->countryMode;
					$nxtCntry = false; // f*ck internal pointer, this crap doesn't work
					foreach($countries as $country => $params)
						if($params['act'] == 'Y'){
							if($nxtCntry){
								$nxtCntry = $country;
								break;
							}
							if($country == $exportClass->countryMode)
								$nxtCntry = true;
						}

					if($nxtCntry && $nxtCntry !== true){
						$exportClass = new cityExport($nxtCntry,$params['timeout']);
						$exportClass->quickSave();
						$result['result']  = 'country';
					}
				}
			}else
				$result = array(
					'result' => 'error',
					'error'  => GetMessage("IPOLSDEK_ERRLOG_ERRNOCOUNTRIES")
				);

			if($params['mode'] == 'json')
				echo json_encode(self::zajsonit($result));
			else
				return $result;
		}


		static function slaughterCities(){
			if(!self::isAdmin()) return false;
			global $DB;
			if($DB->Query("SELECT 'x' FROM ipol_sdekcities", true)){
				$errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".self::$MODULE_ID."/install/db/mysql/unInstallCities.sql");
				if($errors !== false)
					return "error.".implode("", $errors);
				$errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".self::$MODULE_ID."/install/db/mysql/installCities.sql");
				if($errors !== false)
					return "error.".implode("", $errors);
				return 'done';
			}
		}

		static function getCountries($makeArrays=false){ // список стран для синхронизации
			$arCountries = array('rus'=>false,'blr'=>false,'kaz'=>false);
			if($makeArrays){
				foreach($arCountries as $country => $nothing)
					$arCountries[$country] = self::getCountryDescr($country);
			}else
				$arCountries = array_keys($arCountries);
			return $arCountries;
		}

		static function getCountryDescr($cntry=false){
			$descr = false;
			
			switch($cntry){
				case false :
				case 'rus' : $descr = array(
									'FILE'  => 'city.csv',
									'NAME'  => array('Russia','Russian Federation',GetMessage('IPOLSDEK_SYNCTY_rus'),GetMessage('IPOLSDEK_SYNCTY_rus2'),GetMessage('IPOLSDEK_SYNCTY_rus3')),
									'LABEL' => GetMessage('IPOLSDEK_SYNCTY_rus')
								);
							break;
				case 'kaz' : $descr = array(
									'FILE' => 'kaz_city.csv',
									'NAME' => array('Kazakhstan',GetMessage('IPOLSDEK_SYNCTY_kaz')),
									'LABEL' => GetMessage('IPOLSDEK_SYNCTY_kaz')
								);
							break;
				case 'blr' : $descr = array(
									'FILE' => 'bel_city.csv',
									'NAME' => array('Belarus','Belorussia',GetMessage('IPOLSDEK_SYNCTY_blr'),GetMessage('IPOLSDEK_SYNCTY_blr2')),
									'LABEL' => GetMessage('IPOLSDEK_SYNCTY_blr')
								);
							break;
			}
			return $descr;
		}

		static function requestCityFile($cntr=false){
			$cntrDescr = self::getCountryDescr($cntr);
			if(!$cntrDescr)
				return false;
			$request = self::nativeReq($cntrDescr['FILE']);
			if($request['code'] != '200'){
				self::errorLog(GetMessage('IPOLSDEK_FILEIPL_UNBLUPDT').$request['code']);
				return false;
			}
			file_put_contents($_SERVER['DOCUMENT_ROOT']."/bitrix/js/".self::$MODULE_ID."/".$cntrDescr['FILE'],$request['result']);
			return true;
		}

		protected static function ordersNum(){
			cmodule::includeModule('sale');
			// требование СДЭК по сбору статистики, сколько заявок сделано через модуль
			$lastId = \Ipolh\SDEK\option::get('lastSuncId');
			$arOrders = array();
			$bdReqs = sqlSdekOrders::select(array("ID","ASC"),array(">ID"=>$lastId,"OK"=>true));
			while($arReq=$bdReqs->Fetch()){
				$year  = date("Y",$arReq['UPTIME']);
				if(!array_key_exists($year,$arOrders))
					$arOrders[$year] = array();

				$month = date("m",$arReq['UPTIME']);
				if(array_key_exists($month,$arOrders[$year]))
					$arOrders[$year][$month]['vis'] += 1;
				else
					$arOrders[$year][$month]['vis'] = 1;
				$arOrders[$year][$month]['id'][] = $arReq['ORDER_ID'];
				if($lastId < $arReq['ID'])
					$lastId = $arReq['ID'];
			}

			foreach($arOrders as $year => $arYear)
				foreach($arYear as $month => $arMonth){
					$ttlPrice = 0;
					$orders = CSaleOrder::GetList(array(),array('ID'=>$arMonth['id']),false,false,array('ID','PRICE'));
					while($order=$orders->Fetch())
						$ttlPrice += $order['PRICE'];
					$arOrders[$year][$month]['prc'] = round($ttlPrice);
					unset($arOrders[$year][$month]['id']);
				}

			if(count($arOrders)){
				$auth = self::getBasicAuth();
				$request = self::nativeReq('sdekStat.php',array(
					'req' => json_encode(self::zajsonit(array(
						'reqs' => $arOrders,
						'acc'  => $auth['ACCOUNT'],
						'host' => $_SERVER['SERVER_NAME'],
						'cms'  => 'bitrix'
					)))
				));
				if(
					$request['code']=='200' &&
					strpos($request['result'],'good') !== false
				){
				    \Ipolh\SDEK\option::set('lastSuncId',$lastId);
                }
			}
		}

		public static function restorePVZ()
        {
            $result = \Ipolh\SDEK\PointsHandler::updatePoints(\Ipolh\SDEK\PointsHandler::REQUEST_TYPE_BACKUP, true);
            echo json_encode($result);
        }

		
		/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
														Импорт городов
			== setImport ==  == handleImport ==  == getCityTypeId ==
		()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


		static function setImport($mode = 'N'){
			if(is_array($mode))
				$mode = (array_key_exists('mode',$mode)) ? $mode['mode'] : 'N';
			\Ipolh\SDEK\option::set('importMode',$mode);
		}

		static function handleImport($params){
			if(!self::isAdmin()) return false;
			cmodule::includeModule('sale');
			$fname = ($params['fileName']) ? $params['fileName'] : 'tmpImport.txt';
			switch($params['mode']){
				case 'setSync': $sync = self::cityUpdater($_REQUEST['timeOut']);
					if($sync['result'] == 'pause' || $sync['result'] == 'country')
						 $arReturn = array(
							'text' => GetMessage('IPOLSDEK_IMPORT_PROCESS_SYNC').$sync['done'].GetMessage("IPOLSDEK_IMPORT_PROCESS_FROM").$sync['total'],
							'step' => 'contSync',
							'result' => $sync
						 );
					else
						$arReturn = array(
							'text' => GetMessage('IPOLSDEK_IMPORT_STATUS_SDONE')."<br><br>",
							'step' => 'startImport',
							'result' => $sync
						 );
				break;
				case 'setImport': 
					$importClass = new cityExport('rus',$params['timeOut'],$fname);
					$importClass->pauseImport();
					if($importClass->error)
						$arReturn = array(
							'text'   => GetMessage('IPOLSDEK_IMPORT_ERROR_lbl').$importClass->error,
							'step' 	 => false,
							'result' => 'error',
						);
					else
						$arReturn = array(
							'text'   => GetMessage('IPOLSDEK_IMPORT_STATUS_MDONE'),
							'step'   => 'init',
							'result' => $importClass->result,
						);
				break;
				case 'process' :
					if(!file_exists($_SERVER['DOCUMENT_ROOT']."/bitrix/js/".self::$MODULE_ID."/{$fname}"))
						$arReturn = array(
							'text'   => GetMessage('IPOLSDEK_IMPORT_ERROR_NOFILES'),
							'step' 	 => false,
							'result' => 'error',
						);
					else{
						$importClass = unserialize(file_get_contents($_SERVER['DOCUMENT_ROOT']."/bitrix/js/".self::$MODULE_ID."/{$fname}"), ['allowed_classes' => ['cityExport']]);
						$importClass->loadCities();
						$errors = ($importClass->error) ? GetMessage('IPOLSDEK_IMPORT_ERROR_WHILEIMPORT')."<div class='IPOLSDEK_import_errors'>".$importClass->error."</div>" : '';
						if($importClass->result['result'] == 'end'){
							$arReturn = array(
								'text'   => GetMessage('IPOLSDEK_IMPORT_STATUS_IDONE').$importClass->result['added'].".".$errors ,
								'step' 	 => 'endImport',
								'result' => $importClass->result
							);
							self::setImport('N');
						}else
							$arReturn = array(
								'text'   => "> ".GetMessage('IPOLSDEK_IMPORT_PROCESS_'.$importClass->result['mode'])." ".GetMessage('IPOLSDEK_IMPORT_PROCESS_WORKING').($importClass->result['done']).GetMessage('IPOLSDEK_IMPORT_PROCESS_FROM').$importClass->result['total']." ".$errors,
								'step' 	 => 'process',
								'result' => 'process',
							);
					}
				break;
			}
			if($params['noJson'])
				return $arReturn;
			else
				echo json_encode(sdekdriver::zajsonit($arReturn));
		}

		static function getCityTypeId(){
			if(!sdekdriver::isLocation20()) return;
			$tmp = \Bitrix\Sale\Location\TypeTable::getList(array('select'=>array('*'),'filter'=>array('CODE'=>'CITY')))->Fetch();
			return (is_array($tmp) && array_key_exists('ID',$tmp)) ? $tmp['ID'] : false;
		}

		
		/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
														Автовыгрузки
			== setAutoloads ==  == autoLoadsHandler ==
		()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/
		

		static function setAutoloads($mode = 'Y'){
			if(is_array($mode))
				$mode = (array_key_exists('mode',$mode)) ? $mode['mode'] : 'Y';
			\Ipolh\SDEK\option::set('autoloads',$mode);
		}

		static function autoLoadsHandler($params){
			sdekdriver::$MODULE_ID;
			cmodule::includeModule('sale');
			$arSelect[($params['by'])?$params['by']:'ORDER_ID']=($params['sort'])?$params['sort']:'DESC';

			$arNavStartParams['iNumPage']=($params['page'])?$params['page']:1;
			$arNavStartParams['nPageSize']=($params['pgCnt']!==false)?$params['pgCnt']:1;

			$arFilter = array('CODE'=>'IPOLSDEK_AUTOSEND');
			foreach($params as $code => $val)
				if(strpos($code,'F')===0 && $code != 'FSTATUS')
					$arFilter[substr($code,1)]=$val;
				elseif($code == 'FSTATUS' && $val == 'Y')
					$arFilter['!VALUE']=GetMessage('IPOLSDEK_AUTOLOAD_RESPOND_1');
			$requests = CSaleOrderPropsValue::GetList($arSelect,$arFilter,false,$arNavStartParams);
			$strHtml='';
			$action = (self::isConverted()) ? '/bitrix/admin/sale_order_view.php?ID=' : '/bitrix/admin/sale_order_detail.php?ID=';
			while($request=$requests->Fetch()){
				$addClass = ($request['VALUE'] == GetMessage('IPOLSDEK_AUTOLOAD_RESPOND_1')) ? 'IPOLSDEK_TblStOk' : 'IPOLSDEK_TblStErr';

				$SDEKState = '';
				if($addClass == 'IPOLSDEK_TblStOk'){
					$SDEKState = sqlSdekOrders::GetByOI($request['ORDER_ID']);
					if(!$SDEKState['OK'])
						$addClass = 'IPOLSDEK_TblStErr';
					$SDEKState = $SDEKState['STATUS'];
				}
	
				$strHtml.='<tr class="adm-list-table-row '.$addClass.'">
					<td class="adm-list-table-cell"><div><a href="'.$action.$request['ORDER_ID'].'" target="_blank">'.$request['ORDER_ID'].'</a></div></td>
					<td class="adm-list-table-cell"><div>'.$request['MESS_ID'].'</div></td>
					
					<td class="adm-list-table-cell"><div>'.$request['VALUE'].'</div></td>
					<td class="adm-list-table-cell"><div>'.$SDEKState.'</div></td>';
			}

			echo json_encode(
				self::zajsonit(
					array(
						'ttl' =>$requests->NavRecordCount,
						'mP'  =>$requests->NavPageCount,
						'pC'  =>$requests->NavPageSize,
						'cP'  =>$requests->NavPageNomer,
						'sA'  =>$requests->NavShowAll,
						'html'=>$strHtml
					)
				)
			);
		}

		static function killAutoReq($orderId=false){
			if(!$orderId || !self::isAdmin())
				return;
			cmodule::includeModule('sale');
			$val = CSaleOrderPropsValue::GetList(array(),array('CODE'=>'IPOLSDEK_AUTOSEND','ORDER_ID'=>$orderId))->Fetch();
			if($val)
				CSaleOrderPropsValue::Delete($val['ID']);
		}


		/*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
														Связки и общие
			== select ==  == CheckRecord ==  == nativeReq ==
		()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


		public static function select($arOrder=array("ID","DESC"),$arFilter=array(),$arNavStartParams=array()){ // выборка
			if(!self::isAdmin('R')) 
				return false; 
			return sqlSdekOrders::select($arOrder,$arFilter,$arNavStartParams);
		}
		public static function CheckRecord($orderId,$mode='order'){// проверка наличия заявки для заказа / отгрузки
			if(!self::isAdmin('R')) 
				return false;
			return sqlSdekOrders::CheckRecord($orderId,$mode);
		}

		public static function nativeReq($where,$what=false){
			if(!$where) return false;
			$ch = curl_init();
			curl_setopt($ch,CURLOPT_URL,'https://ipolh.com/webService/sdek/'.$where);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			if($what){
				curl_setopt($ch, CURLOPT_POST, TRUE);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $what);
			}

            if(\Ipolh\SDEK\option::get('noSertifCheckNative') === 'Y'){
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            }

			$result = curl_exec($ch);
			$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
			curl_close($ch);
			return array(
				'result' => $result,
				'code'   => $code
			);
		}

		// LEGACY
		static function updateCities($params=array()){
			$exportClass = false;
			cmodule::includeModule('sale');
			if(file_exists($_SERVER['DOCUMENT_ROOT']."/bitrix/js/".self::$MODULE_ID."/tmpExport.txt"))
				$exportClass = unserialize(file_get_contents($_SERVER['DOCUMENT_ROOT']."/bitrix/js/".self::$MODULE_ID."/tmpExport.txt"), ['allowed_classes' => ['cityExport']]);
			else
				$exportClass = new cityExport('rus',$params['timeout']);

			$exportClass->start();

			if($exportClass->result['result'] == 'error')
				self::errorLog(GetMessage("IPOLSDEK_ERRLOG_ERRSUNCCITY")." ".$exportClass->result['error']);

			if($params['mode'] == 'json')
				echo json_encode($exportClass->result);
			else
				return $exportClass->result;
		}

        static function getAccountsWithDisabledOldApi($forced = false)
        {
            $lastCheck = \Ipolh\SDEK\option::get('lastOldApiCheck');

            if ($lastCheck && time() - $lastCheck < 7200 && !$forced) {
                return json_decode(\Ipolh\SDEK\option::get('accountsWithDisabledOldApi'), true);
            }

            $accounts = sqlSdekLogs::getAccountsList();

            $founded = [];
            foreach ($accounts as $id => $account) {
                 $accountData = self::defineAuth($id);
                 $secure = $accountData['SECURE'];

                $result = \Ipolh\SDEK\Legacy\transitApplication::getInvoicesByDispatchNumbers($account, $secure, ['not_valid_dispatch_number']);
                if ($result['code'] !== 400) {
                    continue;
                }

                $result = simplexml_load_string($result['result']);
                if ((string)$result->Order['ErrorCode'] === 'ERR_AUTH') {
                    $founded[] = $accountData;
                }
            }

            \Ipolh\SDEK\option::set('accountsWithDisabledOldApi', json_encode($founded));
            \Ipolh\SDEK\option::set('lastOldApiCheck', time());

            return $founded;
        }
	}
?>