<?php
#################################################
#        Company developer: IPOL
#        Developers: Nikta Egorov
#        Site: http://www.ipolh.com
#        E-mail: om-sv2@mail.ru
#        Copyright (c) 2006-2021 IPOL
#################################################
?>
<?php
use Ipolh\SDEK\Bitrix\Tools as Tools;

IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/options.php");

$module_id = "ipol.sdek";
CModule::IncludeModule($module_id);
if(sdekdriver::$MODULE_ID !== $module_id)
	echo "ERROR IN MODULE ID";

CModule::IncludeModule('sale');
Tools::initJQuery();
$isLogged  = sdekdriver::isLogged();
$converted = sdekdriver::isConverted();
$migrated  = sdekdriver::isLocation20();
$isB24     = Tools::isB24();
$ctId      = sdekOption::getCityTypeId();

// Payers
$tmpValue=CSalePersonType::GetList(array('ACTIVE'=>'Y'));
$arPayers=array();
while($payer=$tmpValue->Fetch()){
	$arPayers[$payer['ID']]=array('NAME'=>$payer['NAME']." [".$payer['LID']."]");
		$arPayers[$payer['ID']]['sel']=true;
}
// Locations
$tmpValue = CSaleOrderProps::GetList(array(),array("IS_LOCATION"=>"Y"));
$locProps = array();
while($element=$tmpValue->Fetch())
	$locProps[$element['CODE']] = $element['NAME'];

// sender-cities
$tmpValue = sqlSdekCity::select();
$senderCitiesJS = '';
$senderCities = array();
while($element=$tmpValue->Fetch()){
	$senderCitiesJS .= "{label:'{$element['NAME']} ({$element['REGION']})',value:'{$element['SDEK_ID']}'},";
	$senderCities[$element['SDEK_ID']] = $element['NAME']." (".$element['REGION'].")";
}

$arAllOptions = \Ipolh\SDEK\option::toOptions();

//Restore defaults
if ($USER->IsAdmin() && $_SERVER["REQUEST_METHOD"]=="GET" && strlen($RestoreDefaults)>0 && check_bitrix_sessid())
    COption::RemoveOption($module_id);

//Save options
if($REQUEST_METHOD=="POST" && strlen($Update.$Apply.$RestoreDefaults)>0 && check_bitrix_sessid()){
	if(strlen($RestoreDefaults)>0)
		COption::RemoveOption($module_id);
	else{
		// blockPVZ
		if($_REQUEST['noPVZnoOrder'] == 'Y' && \Ipolh\SDEK\option::get('noPVZnoOrder') == 'N'){
			if($converted){
				RegisterModuleDependences("sale", "OnSaleOrderBeforeSaved", $module_id, "Ipolh\\SDEK\\subscribeHandler", "noPVZNewTemplate");
				RegisterModuleDependences("sale", "OnSaleComponentOrderOneStepProcess", $module_id, "Ipolh\\SDEK\\subscribeHandler", "noPVZOldTemplate");
			}else
				RegisterModuleDependences("sale", "OnSaleComponentOrderOneStepProcess", $module_id, "Ipolh\\SDEK\\subscribeHandler", "noPVZOldTemplate");
		}elseif((!array_key_exists('noPVZnoOrder',$_REQUEST) || $_REQUEST['noPVZnoOrder'] == 'N') && \Ipolh\SDEK\option::get('noPVZnoOrder') == 'Y'){
			if($converted){
				UnRegisterModuleDependences("sale", "OnSaleOrderBeforeSaved", $module_id, "Ipolh\\SDEK\\subscribeHandler", "noPVZNewTemplate");
				UnRegisterModuleDependences("sale", "OnSaleComponentOrderOneStepProcess", $module_id, "Ipolh\\SDEK\\subscribeHandler", "noPVZOldTemplate");
			}else
				UnRegisterModuleDependences("sale", "OnSaleComponentOrderOneStepProcess", $module_id, "Ipolh\\SDEK\\subscribeHandler", "noPVZOldTemplate");
		}
		// logfile
		if((!array_key_exists('debugMode',$_REQUEST) || $_REQUEST['debugMode'] != 'Y') && \Ipolh\SDEK\option::get('debugMode') == 'Y'){
			\Ipolh\SDEK\Bitrix\Admin\Logger::killLog();
		}

		foreach($_REQUEST['addDeparture'] as $key => $place)
			if(!$place)
				unset($_REQUEST['addDeparture'][$key]);
		foreach(array('paySystems','addingService','tarifs','addDeparture') as $opt)
			$_REQUEST[$opt] = ($_REQUEST[$opt]) ? serialize($_REQUEST[$opt]) : 'a:0:{}';
		$_REQUEST['dostTimeout']  = (floatval($_REQUEST['dostTimeout']) > 0) ? $_REQUEST['dostTimeout']  : 6;
		$_REQUEST['cntExpress']   = (floatval($_REQUEST['cntExpress']) > 0) ? $_REQUEST['cntExpress']  : 0;
		$_REQUEST['ensureProc']   = floatval(str_replace(array(',',' '),array('.',''),$_REQUEST['ensureProc']));
		
		$_REQUEST['countries']    = json_encode(sdekOption::zajsonit($_REQUEST['countries']));
		if(
			$_REQUEST['countries'] !== \Ipolh\SDEK\option::get('countries',true) &&
			file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/js/'.$module_id.'/tmpExport.txt')
		){
			unlink($_SERVER['DOCUMENT_ROOT'].'/bitrix/js/'.$module_id.'/tmpExport.txt');
            \Ipolh\SDEK\PointsHandler::updatePoints(\Ipolh\SDEK\PointsHandler::REQUEST_TYPE_SDEK, true);
		}

		$arNumReq = array('numberOfPrints','termInc','lengthD','widthD','heightD','weightD');
		foreach($arNumReq as $key){
			$_REQUEST[$key] = intval($_REQUEST[$key]);
			if($_REQUEST[$key] <= 0 && $key!='termInc')
				unset($_REQUEST[$key]);
		}

        // minding agent of statuses update
        if(array_key_exists('orderStatusesAgentRollback',$_REQUEST) && $_REQUEST['orderStatusesAgentRollback'] !== \Ipolh\SDEK\option::get('orderStatusesAgentRollback')){
            \Ipolh\SDEK\AgentHandler::remakeStatusCheckAgent($_REQUEST['orderStatusesAgentRollback']);
        }

		foreach($arAllOptions as $aOptGroup){
			foreach($aOptGroup as $option){
				__AdmSettingsSaveOption($module_id, $option);
			}
		}
	}

	if($_REQUEST["back_url_settings"] <> "" && $_REQUEST["Apply"] == "")
		 echo '<script type="text/javascript">window.location="'.CUtil::addslashes($_REQUEST["back_url_settings"]).'";</script>';

	sdekOption::clearCache(true);
}

if($isLogged){
	$import    = (\Ipolh\SDEK\option::get('importMode') === 'Y'); // IMPORT
	$autoloads = (\Ipolh\SDEK\option::get('autoloads')  === 'Y'); // AUTO
	$debug     = (\Ipolh\SDEK\option::get('debugMode')  === 'Y'); // DEBUG

	$aTabs = array(
		array("DIV" => "edit1", "TAB" => GetMessage("IPOLSDEK_TAB_FAQ"), "TITLE" => GetMessage("IPOLSDEK_TAB_TITLE_FAQ")),
		array("DIV" => "edit2", "TAB" => GetMessage("MAIN_TAB_SET"), "TITLE" => GetMessage("MAIN_TAB_TITLE_SET")),
		array("DIV" => "edit3", "TAB" => GetMessage("IPOLSDEK_TAB_LIST"), "TITLE" => GetMessage("IPOLSDEK_TAB_TITLE_LIST")),
		array("DIV" => "edit4", "TAB" => GetMessage("IPOLSDEK_TAB_RIGHTS"), "TITLE" => GetMessage("IPOLSDEK_TAB_TITLE_RIGHTS")),
		array("DIV" => "edit5", "TAB" => GetMessage("IPOLSDEK_TAB_CITIES"), "TITLE" => GetMessage("IPOLSDEK_TAB_TITLE_CITIES")),
	);
	if($import){
		$aTabs[] = array("DIV" => "edit6", "TAB" => GetMessage("IPOLSDEK_TAB_IMPORT"), "TITLE" => GetMessage("IPOLSDEK_TAB_TITLE_IMPORT"));
	}
	if($autoloads){ // AUTO
		$aTabs[] = array("DIV" => "edit".(($import)?'7':'6'), "TAB" => GetMessage("IPOLSDEK_TAB_AUTOLOADS"), "TITLE" => GetMessage("IPOLSDEK_TAB_TITLE_AUTOLOADS"));
	}
	if($debug){
		$tab = intval($import) + intval ($autoloads);
		$aTabs[] = array("DIV" => "edit".(6 + $tab), "TAB" => GetMessage("IPOLSDEK_TAB_DEBUG"), "TITLE" => GetMessage("IPOLSDEK_TAB_TITLE_DEBUG"));
	}

    $arTabs = array();
	foreach(GetModuleEvents($module_id,"onTabsBuild",true) as $arEvent)
		ExecuteModuleEventEx($arEvent,Array(&$arTabs));
	$divId = count($aTabs);
	if(count($arTabs))
		foreach($arTabs as $tabName => $path)
			$aTabs[]=array("DIV" => "edit".(++$divId), "TAB" => $tabName, "TITLE" => $tabName);
}else
	$aTabs = array(array("DIV" => "edit1", "TAB" => GetMessage("IPOLSDEK_TAB_LOGIN"), "TITLE" => GetMessage("IPOLSDEK_TAB_TITLE_LOGIN")));

function ShowParamsHTMLByArray($arParams){
	global $module_id;
	global $senderCities;
	foreach($arParams as $Option){
		if($Option[3][0]!='selectbox'){
			switch($Option[0]){
				case 'departure':
					$cityDef = COption::GetOptionString('sale','location',false);
					if(!$cityDef){
						$arCites = array();
						$cites = CSite::GetList($by="sort",$order="desc");
						$similar = true;
						$oldOp = 'none';
						while($cite=$cites->Fetch()){
							$op = COption::GetOptionString('sale','location',false,$cite['LID']);
							if($op)
								$arCites[$cite['LID']] = $op;
							if($similar && $oldOp != 'none' && $oldOp != $op)
								$similar = false;
							$oldOp = $op;
						}
						if(!count($arCites))
							echo "<tr><td colspan='2'>".GetMessage('IPOLSDEK_LABEL_NOCITY')."</td><tr>";
						elseif($similar)
							sdekOption::printSender(array_pop($arCites));
						else{
							$strSel = "<select name='departure'>";
							$seltd = \Ipolh\SDEK\option::get('departure');
							foreach($arCites as $cite => $city){
								$SDEKcity = sdekOption::getSDEKCity($city);
								if(!$SDEKcity)
									$strSel .= "<option value='' disabled>".GetMessage('IPOLSDEK_LABEL_NOSDEKCITYSHORT')." [$cite]</option>";
								else
									$strSel .= "<option ".(($seltd == $SDEKcity['BITRIX_ID'])?'selected':'')." value='".$SDEKcity['BITRIX_ID']."'>".$SDEKcity['NAME']." [$cite]</option>";
							}
							echo "<tr><td>".GetMessage('IPOLSDEK_OPT_departure')."</td><td>".$strSel."</select></td><tr>";
						}
					}else
						sdekOption::printSender($cityDef);
				break;
				case 'addDeparture':
					echo "<td style='vertical-align:top;'>".GetMessage('IPOLSDEK_OPT_'.$Option[0])."</td><td><div id='IPOLSDEK_{$Option[0]}Place'>";
					$svd = \Ipolh\SDEK\option::get($Option[0]);
					if($svd && count($svd))
						foreach($svd as $index => $city)
							echo "<div><input type='text' value='{$senderCities[$city]}' class='IPOLSDEK_{$Option[0]}'><input type='hidden' name='{$Option[0]}[$index]' value='$city'>&nbsp;<a href='javascript:void(0)' style='color:red;' onclick='IPOLSDEK_setups.base.depature.delete($(this))'>X</a></div>";
					else
						echo "<div><input type='text' class='IPOLSDEK_{$Option[0]}'><input type='hidden' name='{$Option[0]}[$index]' name='{$Option[0]}[]'></div>";
					echo "</div><br><input type='button' onclick='IPOLSDEK_setups.base.depature.add()' value='".GetMessage("IPOLSDEK_LABEL_".$Option[0])."'></td>";
				break;
				default: __AdmSettingsDrawRow($module_id, $Option); break;
			}
		}
		elseif($Option[0] == 'widjetVersion'){
            echo "<td colspan='2'><input type='hidden' value='ipol.sdekPickup' id='widjetVersion' name='widjetVersion'></td>";
        } else{
			$optVal= \Ipolh\SDEK\option::get($Option['0']);
			$str='';

			foreach($Option[4] as $key => $val){
				$chkd='';
				if($optVal==$key)
					$chkd='selected';
				$str.='<option '.$chkd.' value="'.$key.'">'.$val.'</option>';
			}
			echo '<tr>
					<td width="50%" class="adm-detail-content-cell-l">'.$Option[1].'</td>  
					<td width="50%" class="adm-detail-content-cell-r"><select name="'.$Option['0'].'">'.$str.'</select></td>
				</tr>';
		}
	}
}
// must be called after collecting info about payers
function showOrderOptions(){
	global $module_id;
	global $arPayers;
	$arNomatterProps=array('street'=>true,'house'=>true,'flat'=>true);
	foreach($GLOBALS['arAllOptions']['orderProps'] as $orderProp){
		if($orderProp[0] == 'extendName'){
			continue;
		}
		$value= \Ipolh\SDEK\option::get($orderProp[0]);
		if(!trim($value)){
			$showErr=true;
			if($orderProp[0]=='address'&& \Ipolh\SDEK\option::get('street')){
				unset($arNomatterProps['street']);
				$showErr=false;
			}
		}
		else
			$showErr=false;

		$arError=array(
			'noPr'=>false,
			'unAct'=>false,
			'str'=>false,
		);

		if(!array_key_exists($orderProp[0],$arNomatterProps)&&$value){
			foreach($arPayers as $payId =>$payerInfo)
				if($payerInfo['sel']){
					if($curProp=CSaleOrderProps::GetList(array(),array('PERSON_TYPE_ID'=>$payId,'CODE'=>$value))->Fetch()){
						if($curProp['ACTIVE']!='Y')
							$arError['unAct'].="<br>".$payerInfo['NAME'];
					}
					else
						$arError['noPr'].="<br>".$payerInfo['NAME'];
				}
			if($arError['noPr']){
				$arError['str']=GetMessage('IPOLSDEK_LABEL_noPr')." <a href='#' class='PropHint' onclick='return IPOLSDEK_setups.popup(\"pop-noPr_".$orderProp[0]."\",$(this));'></a> ";?>
				<div id="pop-noPr_<?=$orderProp[0]?>" class="b-popup" style="display: none; ">
					<div class="pop-text"><?=GetMessage('IPOLSDEK_LABEL_Sign_noPr')?><br><br><?=substr($arError['noPr'],4)?></div>
					<div class="close" onclick="$(this).closest('.b-popup').hide();"></div>
				</div>
			<?php }
			if($arError['unAct']){
				$arError['str'].=GetMessage('IPOLSDEK_LABEL_unAct')." <a href='#' class='PropHint' onclick='return IPOLSDEK_setups.popup(\"pop-unAct_".$orderProp[0]."\",$(this));'></a>";?>
				<div id="pop-unAct_<?=$orderProp[0]?>" class="b-popup" style="display: none; ">
					<div class="pop-text"><?=GetMessage('IPOLSDEK_LABEL_Sign_unAct')?><br><br><?=substr($arError['unAct'],4)?></div>
					<div class="close" onclick="$(this).closest('.b-popup').hide();"></div>
				</div>
			<?php }
			
			if($arError['str'])
				$showErr=true;
		}
		elseif(array_key_exists($orderProp[0],$arNomatterProps))
			$showErr=false;
		
		$styleTdStr = ($orderProp[0] == 'street')?'style="border-top: 1px solid #BCC2C4;"':'';
		
	?>
		<tr>
			<td width="50%" <?=$styleTdStr?> class="adm-detail-content-cell-l"><?=$orderProp[1]?></td>
			<td width="50%" <?=$styleTdStr?> class="adm-detail-content-cell-r">
				<?php if($orderProp[0] != 'location'){ ?>
					<input type="text" size="" maxlength="255" value="<?=$value?>" name="<?=$orderProp[0]?>">
				<?php } else {
					global $locProps;
                    // dont show "choose option"
					if($showErr && !$arError['str'])
						$showErr = false;
					// location will be chosen automatically from location-props
					if(count($locProps)==0){
						$showErr = true;
						$arError['str'] = GetMessage('IPOLSDEK_LABEL_noLoc');
					}elseif(count($locProps)==1){
						$key = array_pop(array_keys($locProps));
					?>
						<input type='hidden' value="<?=$key?>" name="<?=$orderProp[0]?>">
						<?=array_pop($locProps)?> [<?=$key?>]
					<?php } else { ?>
						<select name="<?=$orderProp[0]?>">
							<?php foreach($locProps as $code => $name) { ?>
								<option value='<?=$code?>' <?=($value==$code)?"selected":""?>><?=$name." [".$code."]"?></option>
							<?php } ?>
						</select>
					<?php }
				} ?>
				&nbsp;&nbsp;<span class='errorText' <?php if(!$showErr) { ?>style='display:none'<?php } ?>><?=($arError['str'])?$arError['str']:GetMessage('IPOLSDEK_LABEL_shPr')?></span>
				<?php if($orderProp[0] == 'name') { ?>
					&nbsp;&nbsp;<a href='javascript:void(0)' onclick='IPOLSDEK_setups.base.properties.turnOnNF()'><?=GetMessage('IPOLSDEK_LBL_turnOnExtendName')?></a>
					<input type='hidden' value="<?=(\Ipolh\SDEK\option::get('extendName') == 'Y') ? 'Y' : 'N'?>" name="extendName">
				<?php } elseif($orderProp[0] == 'fName') { ?>&nbsp;&nbsp;<a href='javascript:void(0)' onclick='IPOLSDEK_setups.base.properties.turnOffNF()'><?=GetMessage('IPOLSDEK_LBL_turnOffExtendName')?></a><?php } ?>
			</td>
		</tr>
	<?php }
}

$tabControl = new CAdminTabControl("tabControl", $aTabs);
?>

<script>
	var IPOLSDEK_setups = {
		ajax: function(params){
			var ajaxParams = {
				type : 'POST',
				url  : "/bitrix/js/<?=$module_id?>/ajax.php",
			};
			if(typeof(params.data) !== 'undefined')
            {
                params.data['isdek_token'] = '<?=sdekHelper::getModuleToken()?>';
                ajaxParams.data = params.data;
            }
			if(typeof(params.dataType) !== 'undefined')
				ajaxParams.dataType = params.dataType;
			if(typeof(params.success) !== 'undefined')
				ajaxParams.success = params.success;
			$.ajax(ajaxParams);
		},

		copyObj: function(obj){
			if(obj == null || typeof(obj) !== 'object')
				return obj;
			if(obj.constructor == Array)
				return [].concat(obj);
			var temp = {};
			for(var key in obj)
				temp[key] = IPOLSDEK_setups.copyObj(obj[key]);
			return temp;
		},

		inArray: function(wat,arr){
			return arr.filter(function(item){return item == wat}).length;
		},

		isEmpty: function(obj){
			if(typeof(obj) === 'object')
				for(var i in obj)
					return false;
			return true;
		},

		popup: function(code, info){
			$('.b-popup').hide();

			var LEFT = $(info).offset().left;		
			var obj = $('#'+code);

			LEFT -= parseInt(parseInt(obj.css('width'))/2);

			obj.css({
				top: ($(info).position().top+15)+'px',
				left: LEFT,
				display: 'block'
			});

			return false;
		},

		reload: function(){
			window.location.reload();
		},

		page: function(wat){
			return (typeof(IPOLSDEK_setups[wat]) !== 'undefined');
		}
	};
	$(document).ready(function(){
		for(var i in IPOLSDEK_setups)
			if(typeof(IPOLSDEK_setups[i]) === 'object' && typeof(IPOLSDEK_setups[i].ready) === 'function')
				IPOLSDEK_setups[i].ready();
	});
</script>


<?php
if(!function_exists('curl_init')){
    ?><table><?php
    Tools::placeErrorLabel(GetMessage('IPOLSDEK_NOCURL_LBL'), GetMessage('IPOLSDEK_NOCURL_HEADER'));
    ?></table><?php
}
?>

<?php if (!\Ipolh\SDEK\abstractGeneral::isNewApp()): ?>
    <table>
        <?php Tools::placeWarningLabel(GetMessage('IPOLSDEK_transit_content'), GetMessage('IPOLSDEK_transit_header')) ?>
    </table>
<?php endif; ?>

<?php
// Checking number of launching syncronisation - if it takes more than 5 hours - we have troubles
$oc = ceil(\Ipolh\SDEK\StatusHandler::getNumberOfActiveOrders() / \Ipolh\SDEK\option::get('orderStatusesLimit'));
if($oc * \Ipolh\SDEK\option::get('orderStatusesAgentRollback') > 300){?>
<table>
    <?php Tools::placeWarningLabel(str_replace('{COUNT}',$oc,GetMessage('IPOLSDEK_manyorders_content')),GetMessage('IPOLSDEK_manyorders_header')) ?>
</table>
<?php }?>

<?php if($isLogged) { ?>
<form method="post" action="<?php echo $APPLICATION->GetCurPage() ?>?mid=<?=htmlspecialchars($mid)?>&amp;lang=<?php echo LANG; ?>">
    <?php
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$module_id."/optionsInclude/faq.php");
	$tabControl->BeginNextTab();
	include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$module_id."/optionsInclude/setups.php");
	$tabControl->BeginNextTab();
	include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$module_id."/optionsInclude/table.php");
	$tabControl->BeginNextTab();
	require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/admin/group_rights.php");
	$tabControl->BeginNextTab();
	include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$module_id."/optionsInclude/errCities.php");
	if($import){
		$tabControl->BeginNextTab();
		include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$module_id."/optionsInclude/import.php");
	}
	if($autoloads){
		$tabControl->BeginNextTab();
		include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$module_id."/optionsInclude/autoloads.php");
	}
	if($debug){
		$tabControl->BeginNextTab();
		include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$module_id."/optionsInclude/debug.php");
	}
	if(is_array($arTabs) && count($arTabs))
		foreach($arTabs as $tabName => $path){
			$tabControl->BeginNextTab();
			include_once($_SERVER['DOCUMENT_ROOT'].$path);
		}
	$tabControl->Buttons();
	?>
	<div align="left">
		<input type="hidden" name="Update" value="Y">
		<input type="submit" <?php if(!$USER->IsAdmin()) echo " disabled "; ?> name="Update" value="<?php echo GetMessage("MAIN_SAVE"); ?>">
	</div>
	<?php $tabControl->End(); ?>
	<?= bitrix_sessid_post(); ?>
</form>
<?php }
else{
	$tabControl->Begin();
	$tabControl->BeginNextTab();
	include_once($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$module_id ."/optionsInclude/login.php");
	$tabControl->End();
}
?>