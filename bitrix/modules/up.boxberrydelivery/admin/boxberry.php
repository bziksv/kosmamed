<?

use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Page\Asset;
use Bitrix\Sale\Order;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Internals\OrderPropsValueTable;

$moduleId = 'up.boxberrydelivery';

require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/'.$moduleId.'/include.php');

Asset::getInstance()->addJs('https://insales.boxberry.ru/registration/js/boxberry.ci.js');
CBoxberry::initApi();
CJSCore::Init(array('jquery'));
CBoxberry::addWidgetJs();

$getKey = CBoxberry::getKeyIntegration();
$widgetKey = isset($getKey['key']) ? $getKey['key'] : '';

Loc::loadMessages(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight($moduleId);
if ($POST_RIGHT <= "D")    
	$APPLICATION->AuthForm(Loc::getMessage("ACCESS_DENIED"));


$sTableID = "tbl_boxberry_export";
$oSort = new CAdminSorting($sTableID, "ID", "desc");
$lAdmin = new CAdminList($sTableID, $oSort);
$arOrderProps = array();


$strAdminMessage = '';
$bBreak = FALSE;
if(($arID = $lAdmin->GroupAction()) && $POST_RIGHT=="W"){
	$bbObject = new CBoxberryParsel;

    foreach($arID as $ID){
			if(strlen($ID)<=0) continue;
	       	$ID = IntVal($ID);
	        switch($_REQUEST['action'])
	        {
		        case "exportOrder":
		        {
	        		$result = $bbObject->parselCreate($ID);
	        		if($result["ERROR"])
	        		{
	               		$lAdmin->AddGroupError($ID." > ".strip_tags($result["ERROR"]), $ID);
					}
					else
					{
						$strAdminMessage .= $ID." > ". Loc::getMessage("BB_TRACK_CODE").": ".$result["track"]."<br />";
					}
		            break;
				}
	        }
	        
	       	if($bBreak) 
				break;
	    }

    if ($_REQUEST['action'] === 'createAct'){
        $psend = (new CBoxberryPsend())->parselSend($arID);
        if(!empty($psend['label'])) {
            $strAdminMessage .= Loc::getMessage("BB_ACT_CREATED_SUCCESSFULLY")."<br />";
        } else {
            $lAdmin->AddGroupError(strip_tags($psend['err']), $ID);
        }
    }
}

if(strlen($strAdminMessage) > 1) 
	CAdminMessage::ShowMessage(array("MESSAGE" => $strAdminMessage, "HTML"=>true, "TYPE" => "OK"));
	
$arHeaders = array(
    array(    
        "id"         =>"ID",
        "content"    =>"ID",
        "sort"       =>"id",
        "align"      =>"left",
        "default"    =>true,
    ),  
    array(    
        "id"         => "PVZ",
        "content"    => Loc::getMessage("BB_FIELD_PVZ"),
        "default"    => false,
    ),
	array(    
        "id"         => "CITY_DELIVERY",
        "content"    => Loc::getMessage("BB_CITY_DELIVERY"),
        "default"    => false,
    ), 
	array(    
        "id"         => "ADDRESS_DELIVERY",
        "content"    => Loc::getMessage("BB_ADDRESS_DELIVERY"),
        "default"    => false,
    ),      
    array(    
        "id"         => "DATE_UPDATE",
        "content"    => Loc::getMessage("BB_FIELD_DATE_UPDATE"),
        "sort"       => "date_update",
        "default"    => false,
    ),
    array(    
        "id"         => "PERSON_TYPE_ID",
        "content"    => Loc::getMessage("BB_FIELD_PERSON_TYPE_ID"),
        "sort"       => "person_type_id",
        "default"    => false,
    ),    
    array(    
        "id"         => "STATUS_ID",
        "content"    => Loc::getMessage("BB_FIELD_STATUS"),
        "sort"       => "status",
        "default"    => true,
    ),  
    array(    
        "id"         => "DELIVERY_ID",
        "content"    => Loc::getMessage("BB_FIELD_DELIVERY"),
        "sort"       => "delivery",
        "default"    => false,
    ),       
    array(    
        "id"         => "PAYED",
        "content"    => Loc::getMessage("BB_FIELD_PAYED"),
        "sort"       => "payed",
        "default"    => true,
    ),   
    array(    
        "id"         =>"CANCELED",
        "content"    => Loc::getMessage("BB_FIELD_CANCELED"),
        "sort"       =>"canceled",
        "default"    => true,
    ),
    array(    
        "id"         =>"COMMENTS",
        "content"    => Loc::getMessage("BB_FIELD_COMMENT"),
        "default"    => true,
    ),   
        
);

$lAdmin->AddHeaders($arHeaders);

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();
$bNeedProps = false;
foreach ($arVisibleColumns as $visibleColumn)
{
    if (!$bNeedProps && SubStr($visibleColumn, 0, StrLen("PROP_")) == "PROP_"){
        $bNeedProps = true;        
    }

    if(SubStr($visibleColumn, 0, StrLen("PROP_")) != "PROP_") {
        $arSelectFields[] = $visibleColumn;         
   	}
}

$arSelectFields[] = 'DATE_INSERT';
$arSelectFields[] = 'ID';
$arSelectFields[] = 'DELIVERY_ID';
$arSelectFields[] = 'USER_ID';
$arSelectFields[] = 'PERSON_TYPE_ID';

$allDeliverys = Manager::getActiveList();
	foreach ($allDeliverys as $profile){	
		if (strpos($profile['CODE'],'boxberry')!==false && strpos($profile['CODE'],'KD')!==false){		
			$boxberryProfiles[] = $profile['ID'];
			$boxberryProfilesKd[] = $profile['ID'];
			
		}elseif (strpos($profile['CODE'],'boxberry')!==false && strpos($profile['CODE'],'PVZ')!==false){		 
			$boxberryProfiles[] = $profile['ID'];
			$boxberryProfilesPvz[] = $profile['ID'];
			
		}
	}
	function CheckFilter()
	{
		global $FilterArr, $lAdmin;
		foreach ($FilterArr as $f) global $$f;
		return count($lAdmin->arFilterErrors) == 0; //   ,  false;
	}
	$FilterArr = Array(
		"find_id_from",
		"find_id_to",
		"find_tracking_code",
		"find_date_insert_from",
		"find_date_insert_to",
		"find_payed",
		"find_canceled"  
	);
	$lAdmin->InitFilter($FilterArr);

	if (CheckFilter())
	{
		$arFilter = Array(
			">=ID"				=> $find_id_from,
			"<=ID"				=> $find_id_to,
			">=DATE_INSERT" 	=> $find_date_insert_from,
			"<=DATE_INSERT" 	=> $find_date_insert_to,
			"STATUS_ID" 		=> $find_status,
			"PAYED"            	=> $find_payed,
			"CANCELED"         	=> $find_canceled, 
			"DELIVERY_ID"		=> $boxberryProfiles
		);
	}	
	
	
	
	foreach($arFilter as $key => &$value)
	{
		if(empty($value)) 
		unset($arFilter[$key]);
	}


	$obOrder = CSaleOrder::GetList(
	   array($by => $order), 
	   $arFilter,
	   false,
	   array("nPageSize" => CAdminResult::GetNavSize($sTableID)),
	   $arSelectFields
	);
	
$rsData = new CAdminResult($obOrder, $sTableID); 
$rsData->NavStart(); 
$lAdmin->NavText($rsData->GetNavPrint(Loc::getMessage("BB_PAGING_TITLE")));




while($arRes = $rsData->NavNext(true, "f_"))
{		
	

		$arBbOrder = CBoxberryOrder::GetByOrderId($f_ID);					
		$row =&$lAdmin->AddRow($f_ID, $arRes);
		

		$IdField = '<a style="width:150px;display:block;" href="sale_order_view.php?ID='.$arRes["ID"].'&lang='.LANG.'" target="_blank"><b>'.$arRes["ID"].'</b></a>'. Loc::getMessage("BB_FROM").' ' .$arRes["DATE_INSERT"];
		
		if(intval($arBbOrder["STATUS"]) > 0)
		{	
			$IdField .= '<br />'. Loc::getMessage("BB_STATUS");
			
			switch ($arBbOrder["STATUS"])
			{
				case "1": 
					$IdField .= ' <span style="color:green">'. Loc::getMessage("BB_SEND_TO_API"). '</span>';
					break;
				default:
					break;
			}
			
			$arStatusHistory = unserialize($arBbOrder["STATUS_HISTORY"]);
			if(count((array)$arStatusHistory["SHOP"]) > 0 || count((array)$arStatusHistory["BOXBERRY"]) > 0)
			{	
				global $DB;
				$IdField .= '<br />
							<a href="javascript:void(0);" onclick="openStatusBox(\'stat_history_'.$arBbOrder["ORDER_ID"].'\')">'
								. Loc::getMessage("BB_STATUS_HISTORY").
							'</a>
							<div style="display:none; margin: 2px 0px 0px 10px;padding: 3px;border: 1px solid;" id="stat_history_'.$arBbOrder["ORDER_ID"].'">';
				
				$IdField .= '</div><div style="clear:both;"></div>';
			}
		}

		if($arBbOrder["CHECK_PDF_LINK"])
		{	if(count((array)$arStatusHistory["SHOP"]) <= 0 && count((array)$arStatusHistory["BOXBERRY"]) <= 0) $IdField .= '<br>';
		    $sticker_link = explode(' ', $arBbOrder["CHECK_PDF_LINK"]);
		    if (!empty($sticker_link[1])) {
                $IdField .= '<a class="adm-btn" target="_blank" href="' . trim($sticker_link[0]) . '">' . Loc::getMessage("BB_CHECK_PDF_LINK") . '</a>';
                $IdField .= '<p></p><a class="adm-btn" target="_blank" href="' . trim($sticker_link[1]) . '">' . Loc::getMessage("BB_CHECK_PDF_LINK_ACT") . '</a>';
                $IdField .= '<p></p><a class="adm-btn" target="_blank" href="https://boxberry.ru/tracking?id=' . $arBbOrder['TRACKING_CODE'] . '">' . Loc::getMessage("BB_CHECK_PDF_LINK_SITE_TRACK") . '</a>';
            }else{
                $IdField .= '<a class="adm-btn" target="_blank" href="' . trim($arBbOrder["CHECK_PDF_LINK"]) . '">'. Loc::getMessage("BB_CHECK_PDF_LINK").'</a>';
            }
		}

		if($arBbOrder["TRACKING_CODE"] && $arBbOrder["STATUS_TEXT"] != "DELETED")
		{
			$IdField .= '<p></p> '. Loc::getMessage("BB_TRACK_CODE").': '.$arBbOrder["TRACKING_CODE"];
		}
		$row->AddViewField("ID", $IdField);

		$status_res = CSaleStatus::GetByID($arRes["STATUS_ID"]);
		
		$row->AddViewField("STATUS_ID", '['.$status_res["ID"].'] '.$status_res["NAME"]);
		$row->AddViewField('PAYED', 	($f_PAYED == "Y" 	? Loc::getMessage("BB_YES") : Loc::getMessage("BB_NO")));
		$row->AddViewField('CANCELED',  ($f_CANCELED == "Y" ? Loc::getMessage("BB_YES") : Loc::getMessage("BB_NO")));
		
		$obProps = OrderPropsValueTable::getList(array('filter' => array('ORDER_ID' => $f_ID)));
		
		$address_prop_bb = Option::get('up.boxberrydelivery', 'BB_ADDRESS');
		$location_prop_bb = Option::get('up.boxberrydelivery', 'BB_LOCATION');
		while($prop = $obProps->Fetch()){
		   if ($prop["CODE"] == $location_prop_bb){
				$arLocs = CSaleLocation::GetByID($prop["VALUE"], LANGUAGE_ID);
				$row->AddViewField("CITY_DELIVERY",$arLocs["CITY_NAME"]);				
		   }
		   if ($prop["CODE"] == $address_prop_bb){
			   $row->AddViewField("ADDRESS_DELIVERY",$prop["VALUE"]);				
		   }
		}

    $order = Order::load($arRes['ID']);
    $basket = CBoxberry::GetFullOrderData($arRes['ID']);
    $price = $order->getPrice() - $order->getDeliveryPrice();
    $profile = CDeliveryBoxberry::getDeliveryCode($arRes['DELIVERY_ID']);
    $package = CDeliveryBoxberry::getFullDimensions($basket['ITEMS']);
    $paysum = 0;
    $prepaid = 0;

    if ($profile === 'boxberry:PVZ') {
        $paysum = $price;
        $prepaid = 1;
    }

    if (in_array('PVZ', $arVisibleColumns, true)) {
        if (empty($arBbOrder["STATUS"]) && in_array($arRes["DELIVERY_ID"], (array)$boxberryProfilesPvz, true)) {
            if (empty($arBbOrder["PVZ_CODE"])) {
                $arBbOrder["PVZ_CODE"] = Loc::getMessage('BB_SELECT_PVZ_ON_WIDGET');
            }

            $row->AddViewField(
                'PVZ',
                '<a class="js-bxb-select-'.$arRes["ID"].'" href="javascript:void(0);" onclick="selected_bxb_id='.$arRes["ID"].';boxberry.sucrh(1);boxberry.open(delivery, \''.$widgetKey.'\',\''.$arLocs["CITY_NAME"].' '.$arLocs["REGION_NAME"].'\',\'\',\''.$price.'\',\''.$package['WEIGHT'].'\',\''.$paysum.'\',\''.$package['HEIGHT'].'\',\''.$package['WIDTH'].'\',\''.$package['LENGTH'].'\',\''.$prepaid.'\');return false;" >'.$arBbOrder["PVZ_CODE"].'</a>'
            );
        } else {
            $row->AddViewField("PVZ", $arBbOrder["PVZ_CODE"]);
        }
    }

		$checkForActOption = Option::get($moduleId, 'BB_PARSELSEND');
		$arActions = array(
			"exportOrder" => array(
					"ICON" 		=> "edit",
					"TEXT" 		=> ($checkForActOption == 'Y' ? Loc::getMessage("BB_ACTION_EXPORT_AND_ACT") : Loc::getMessage("BB_ACTION_EXPORT")),
					"ACTION" 	=> $lAdmin->ActionDoGroup($f_ID, "exportOrder"),
			),
            "createAct" => array(
                "ICON" 		=> "edit",
                "TEXT" 		=> Loc::getMessage("BB_ACTION_ACT_MANUALLY"),
                "ACTION" 	=> $lAdmin->ActionDoGroup($f_ID, "createAct"),
            ),

		);
		
		
		if($arBbOrder["STATUS_TEXT"] == "CREATED" || $arBbOrder["STATUS_TEXT"] == "SENT")
		{
			$arActions["exportOrder"]["DISABLED"] = true;
		}

		if(($arBbOrder["STATUS_TEXT"] !== "CREATED" && !$arBbOrder["TRACKING_CODE"]) || strpos($arBbOrder["CHECK_PDF_LINK"], 'act?upload_id')!==false)
		{
            $arActions["createAct"]["DISABLED"]	 = true;
        }

		 $row->AddActions($arActions); 

	
}

$lAdmin->AddFooter(
    array(
        array("title" => Loc::getMessage("MAIN_ADMIN_LIST_SELECTED"), "value" => $rsData->SelectedRowsCount()),
        array("counter" => true, "title" => Loc::getMessage("MAIN_ADMIN_LIST_CHECKED"), "value" => "0"),
    )
);


$arActionsTable = Array(
    "exportOrder" 	=> ($checkForActOption == 'Y' ? Loc::getMessage("BB_ACTION_EXPORT_AND_ACT") : Loc::getMessage("BB_ACTION_EXPORT")),
    "createAct" => Loc::getMessage("BB_ACTION_ACT_MANUALLY"),
);

$arActionsParams = array("select_onchange" =>
	"if(this[this.selectedIndex].value == 'sendOrder' && !confirm('". Loc::getMessage("BB_ACTION_DELETE_CONFIRM")."')){ 
		this.selectedIndex = 0;
	}");
$lAdmin->AddGroupActionTable($arActionsTable, $arActionsParams);
$lAdmin->AddAdminContextMenu(); 
$lAdmin->CheckListMode();
$APPLICATION->SetTitle(Loc::getMessage('BB_PAGE_TITLE'));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

?>

<form name="find_form" method="get" action="<?echo $APPLICATION->GetCurPage();?>">

<? $arFilterOpts =   array(
	"ID",
	Loc::getMessage("BB_FILTER_TRACKING_CODE"),
	Loc::getMessage("BB_FILTER_DATE_INSERT"),
	Loc::getMessage("BB_FIELD_STATUS"),
	Loc::getMessage("BB_FIELD_PAYED"),
	Loc::getMessage("BB_FIELD_CANCELED"),
);

$oFilter = new CAdminFilter(
	$sTableID."_filter",
	$arFilterOpts
);

$oFilter->Begin();
?>
<tr>
  <td nowrap><?="ID";?>:</td>
  <td nowrap>
    <?= Loc::getMessage("BB_FROM_ALT");?><input type="text" name="find_id_from" size="20" value="<?echo htmlspecialchars($find_id_from)?>">
    <?= Loc::getMessage("BB_TO_ALT");?><input type="text" name="find_id_to" size="20" value="<?echo htmlspecialchars($find_id_to)?>">
  </td>
</tr>
<tr>
    <td nowrap><?= Loc::getMessage("BB_FILTER_DATE_INSERT")?>:</td>
    <td nowrap><?echo CalendarPeriod("find_date_insert_from", $find_date_insert_from, "find_date_insert_to", $find_date_insert_to, "find_form", "Y")?></td>
</tr>
<tr>
    <td valign="top"><?echo Loc::getMessage("BB_FIELD_STATUS")?>:<br /><img src="/bitrix/images/sale/mouse.gif" width="44" height="21" border="0" alt=""></td>
    <td valign="top">
	    <select name="find_status[]" multiple size="4">
	    	<option <?if(!$find_status) echo "selected";?>>(<?=strtolower(Loc::getMessage("BB_NO"));?>)</option>
	    <?
	        $dbStatusListFillter = array("LID" => LANGUAGE_ID);
	        if($StatusExclude){
	            $dbStatusListFillter["!ID"] = $StatusExclude;            
	        }
	        $dbStatusList = CSaleStatus::GetList(
	            array("SORT" => "ASC"),
	            $dbStatusListFillter,
	            false,
	            false,
	            array("ID", "NAME", "SORT")
	        );
	        while ($arStatusList = $dbStatusList->Fetch())
	        {
	        ?><option value="<?= htmlspecialchars($arStatusList["ID"]) ?>"<?if (is_array($find_status) && in_array($arStatusList["ID"], $find_status)) echo " selected"?>>[<?= htmlspecialchars($arStatusList["ID"]) ?>] <?= htmlspecialcharsEx($arStatusList["NAME"]) ?></option><?
	        }
	    ?>
	    </select>
	</td>
</tr>

<tr>
    <td><?echo Loc::getMessage("BB_FIELD_PAYED");?>:</td>
    <td>
        <select name="find_payed">
            <option value=""><?echo Loc::getMessage("BB_ALL")?></option>
            <option value="Y"<?if ($filter_payed=="Y") echo " selected"?>><?echo Loc::getMessage("BB_YES")?></option>
            <option value="N"<?if ($filter_payed=="N") echo " selected"?>><?echo Loc::getMessage("BB_NO")?></option>
        </select>
    </td>
</tr>
<tr>
    <td><?echo Loc::getMessage("BB_FIELD_CANCELED")?>:</td>
    <td>
        <select name="find_canceled">
            <option value=""><?echo Loc::getMessage("BB_ALL")?></option>
            <option value="Y"<?if ($find_canceled=="Y") echo " selected"?>><?echo Loc::getMessage("BB_YES")?></option>
            <option value="N"<?if ($find_canceled=="N") echo " selected"?>><?echo Loc::getMessage("BB_NO")?></option>
        </select>
    </td>
</tr>
<?
$oFilter->Buttons(array("table_id"=>$sTableID, "url"=>$APPLICATION->GetCurPage(), "form"=>"find_form"));
$oFilter->End();
?>
</form>

<script type="text/javascript">
var selected_bxb_id = null;
openStatusBox = function (val){
	var block = document.getElementById(val);
	block.style['display'] = (block.style['display'] == 'none' ? 'block' : 'none');
}

function c_function(result) {
    console.log(result);
    return false;
}

function delivery(result) {
    if (typeof (selected_bxb_id) !== 'undefined') {
        if (result.period === '0' && result.price === '0') {
            alert('<?=Loc::getMessage(
                'BB_ADMIN_WIDGET_CALC_ERROR'
            )?>')
            return false;
        }

        if ($('.js-bxb-select-' + selected_bxb_id).length > 0) {
            $.ajax({
                url: '/bitrix/js/up.boxberrydelivery/ajax.php',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    select_pvz_id: result.id,
                    order_id: selected_bxb_id,
                    address: 'Boxberry: ' + result.address + " #" + result.id
                },
                error: function (data) {
                    console.log(data)
                    alert('<?=Loc::getMessage(
                        'BB_ADMIN_WIDGET_ORDER_ALERT_ERROR_1'
                    )?>' + selected_bxb_id + '<?=Loc::getMessage(
                        'BB_ADMIN_WIDGET_ORDER_ALERT_ERROR_2'
                    )?>');
                },
                success: function (data) {
                    $('.js-bxb-select-' + selected_bxb_id).html(result.id);
                    $('#tbl_boxberry_export').load(location.href + ' #tbl_boxberry_export');
                }
            });
        }
    }
}
</script>

<?$apiToken = Option::get($moduleId, 'API_TOKEN');?>
<? if (!empty($apiToken)) { ?>
<div class="adm-info-message-wrap" >
    <div class="adm-info-message">
        <? echo Loc::getMessage("BB_CREATE_INTAKE_INFO_MESSAGE") ?>&nbsp&nbsp
            <a class="adm-btn adm-btn-save" href="javascript:void(0);" onclick="boxberry_registration.open(c_function,{api_token:'<?echo $apiToken?>'}); return false;"><? echo Loc::getMessage("BB_CREATE_INTAKE")?></a>
    </div>
</div>
<?}?>

<?$lAdmin->DisplayList();?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>