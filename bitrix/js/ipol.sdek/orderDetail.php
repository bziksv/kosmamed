<?php
$SDEK_ID = false;
$message = array();

// getting form parametrs from BD
if(self::$requestVals){
    $ordrVals = self::$requestVals;
    $status=$ordrVals['STATUS'];

    if($ordrVals['MESSAGE'])
        $_message=unserialize($ordrVals['MESSAGE'], ['allowed_classes' => false]);

    if(isset($_message) && is_array($_message)){
        $message = array('troubles' => '');
        foreach($_message as $key => $sign){
            if(in_array($key,array('service','location','street','house','flat','PVZ','name','phone','email','comment','number'),true)){
                $message[$key]='<br><span style="color:#FF4040">'.$sign.'</span>';
            }else{
                $message['troubles'].='<span style="color:#FF4040">'.$sign.' ('.$key.')</span><br>';
            }
        }

    }
    $SDEK_ID = $ordrVals['SDEK_ID'];
    $MESS_ID = $ordrVals['MESS_ID'];
    // array of form data, of not given - formed from default parametrs from options and order
    $ordrVals=unserialize($ordrVals['PARAMS'], ['allowed_classes' => false]);
    self::$isLoaded = true;

    if(self::$workMode == 'order')
        CDeliverySDEK::setOrderGoods(self::$orderId);
    else
        CDeliverySDEK::setShipmentGoods(self::$shipmentID);

    $naturalGabs = array(
        "D_L" => CDeliverySDEK::$goods['D_L'],
        "D_W" => CDeliverySDEK::$goods['D_W'],
        "D_H" => CDeliverySDEK::$goods['D_H'],
        "W" => CDeliverySDEK::$goods['W']
    );

    if(!$ordrVals['toPay'])
        $ordrVals['toPay'] = 0;
    if(!$ordrVals['deliveryP'])
        $ordrVals['deliveryP'] = 0;

    // Better than nothing, until refactoring
    if (!isset($ordrVals['estimatedCost']))
        $ordrVals['estimatedCost'] = $ordrVals['toPay'];

    $cntrCurrency = array_key_exists('currency', $ordrVals) ? $ordrVals['currency'] : false;

    // Partial compatibility with Store data from old orders
    if (!isset($ordrVals['from_loc_street']) && isset($ordrVals['sender_street']))
        $ordrVals['from_loc_street'] = $ordrVals['sender_street'];
    if (!isset($ordrVals['from_loc_house']) && isset($ordrVals['sender_house']))
        $ordrVals['from_loc_house'] = $ordrVals['sender_house'];
    if (!isset($ordrVals['from_loc_flat']) && isset($ordrVals['sender_flat']))
        $ordrVals['from_loc_flat'] = $ordrVals['sender_flat'];

    if (!isset($ordrVals['seller_name']) && isset($ordrVals['realSeller'])) {
        $ordrVals['seller_name'] = $ordrVals['sender_company'];
        $ordrVals['seller_phone'] = $ordrVals['sender_phone'];
        $ordrVals['seller_address'] = implode(', ', [$ordrVals['sender_street'], $ordrVals['sender_house'], $ordrVals['sender_flat']]);
    }
}else{
    $ordrVals = self::formation();
    $naturalGabs = $ordrVals['GABS'];
}

$autosendStatus = !empty(self::$orderDescr['properties']['IPOLSDEK_AUTOSEND']) ? self::$orderDescr['properties']['IPOLSDEK_AUTOSEND'] : '';

$orderCity = sqlSdekCity::getBySId($ordrVals['location']);

if($orderCity) {
    $cityName = $orderCity['NAME'];
}
else {
    $cityName = "ERROR";
}
if(!(isset($status) && $status))
    $status = 'NEW';

if(self::$isLoaded)
    self::$isEditable = (!self::$requestVals['OK']);
else
    self::$isEditable = true;

// Checking city, if sended in error one
$multipleMatchedCities = sdekHelper::getMultipleMatchedCities();
$multiCity = false;
$multiCityS = false;
$bitrixCityId = self::$orderDescr['properties'][\Ipolh\SDEK\option::get('location')];
if(is_array($multipleMatchedCities) && array_key_exists($bitrixCityId, $multipleMatchedCities)) {
    $multiCity = '&nbsp;&nbsp;<a href="#" class="PropWarning" onclick="return IPOLSDEK_oExport.popup(\'pop-multiCity\',this);"></a>	
	<div id="pop-multiCity" class="b-popup" style="display: none; ">
	<div class="pop-text">'.GetMessage("IPOLSDEK_SOD_MANYCITY").'<div class="close" onclick="$(this).closest(\'.b-popup\').hide();"></div>
</div>';

    $multiCityS = "<select id='IPOLSDEK_ms' onchange='IPOLSDEK_oExport.onMSChange(\$(this))'>
	<option value='".$orderCity['SDEK_ID']."' ".(($ordrVals['location'] == $orderCity['SDEK_ID'])?"selected":"").">" . $multipleMatchedCities[$bitrixCityId]['takenLbl'] . "</option>";
    foreach($multipleMatchedCities[$bitrixCityId]['sdekCity'] as $sdekId => $arAnalog)
        $multiCityS .= "<option value='".$sdekId."' ".(($ordrVals['location'] == $sdekId)?"selected":"").">".$arAnalog['region'].", ".$arAnalog['name']."</option>";
    $multiCityS .= "</select>";
}

$payment = sqlSdekCity::getCityPM($ordrVals['location']); // платежная система
$isCODavailable = $payment === true || floatval($payment) >= floatval(self::$orderDescr['info']['PRICE']);

//ТАРИФЫ
$controllerPVZ = new \Ipolh\SDEK\Bitrix\Controller\pvzController(true);
$arList =  $controllerPVZ->getList();//CDeliverySDEK::getListFile();
$arModdedList = CDeliverySDEK::weightPVZ($ordrVals['GABS']["W"] * 1000,$arList['PVZ']);
$arModdedPST  = CDeliverySDEK::weightPST($ordrVals['GABS'],$arList['POSTAMAT']);
$strOfCodes='';

$arTarif = sdekdriver::getExtraTarifs();
$arTarifMode = \Ipolh\SDEK\option::get("tarifs");
$hasSelected = false;

foreach($arTarif as $code => $arSign){//тариф
    if($arSign['SHOW'] == 'Y' || $code == $ordrVals['service']){
        $selected = '';
        if(!$hasSelected && $code == $ordrVals['service'])
            $selected='selected';
        elseif(!$hasSelected && !$ordrVals['service']){ //пытаемся угадать тариф
            if(strpos($ordrVals['address'],"#S")){
                if(array_key_exists($orderCity['SDEK_ID'],$arList['PVZ']) && $code == 136)
                    $selected = 'selected';
            }
            elseif($code == 137)
                $selected = 'selected';
        }

        if($selected)
            $hasSelected = true;

        $highLight = '';
        /*
        if($code == 138 || $code == 139)
            $highLight = "style='background-color:#F08192'";
        */

        $strOfCodes.="<option $highLight value='$code' $selected>".$arSign['NAME']."</option>";
    }
}

// Sender cities
$senderCities = [];

$mainDeparture = sqlSdekCity::getByBId(\Ipolh\SDEK\option::get('departure'));
if (!empty($mainDeparture)) {
    $senderCities[$mainDeparture['SDEK_ID']] = $mainDeparture['NAME'].' ('.GetMessage('IPOLSDEK_LBL_BASIC').')';
}
$addDepartures = \Ipolh\SDEK\option::get('addDeparture');
if (!empty($addDepartures) && count($addDepartures)) {
    foreach ($addDepartures as $val) {
        $addDeparture = sqlSdekCity::getBySId($val);
        if (!empty($addDeparture)) {
            $senderCities[$addDeparture['SDEK_ID']] = $addDeparture['NAME'].' ('.$addDeparture['REGION'].')';
        }
    }
}

// безнал
$badPay = (self::$orderDescr['info']['PAYED'] != 'Y');

// ПВЗ
$strOfPSV='';
$arBPVZ = "{";
if(is_array($arList['PVZ']) && array_key_exists($orderCity['SDEK_ID'],$arList['PVZ'])) {
    uasort($arList['PVZ'][$orderCity['SDEK_ID']],'sdekExport::sortPVZ');
    foreach($arList['PVZ'][$orderCity['SDEK_ID']] as $code => $punkts){
        if(!array_key_exists($code,$arModdedList[$orderCity['SDEK_ID']]))
            $arBPVZ .= $code.":true,";
        $selected = ($ordrVals['PVZ'] == $code) ? "selected" : "";
        $strOfPSV.="<option $selected value='".$code."'>".\Ipolh\SDEK\Bitrix\Tools::encodeFromUTF8($punkts['Name']." (".$punkts['Address'].") [".$code."]")."</option>";
    }
}
$arBPVZ .= "}";
// Постаматы
$strOfPST='';
$arBPST = "{";
if(is_array($arList['POSTAMAT']) && array_key_exists($orderCity['SDEK_ID'],$arList['POSTAMAT'])) {
    uasort($arList['POSTAMAT'][$orderCity['SDEK_ID']],'sdekExport::sortPVZ');
    foreach($arList['POSTAMAT'][$orderCity['SDEK_ID']] as $code => $punkts){
        if(!array_key_exists($code,$arModdedPST[$orderCity['SDEK_ID']]))
            $arBPST .= $code.":true,";
        $selected = ((array_key_exists('PVZ', $ordrVals) && $ordrVals['PVZ'] == $code)
            || (array_key_exists('PST', $ordrVals) &&  $ordrVals['PST'] == $code)) ? "selected" : "";
        $strOfPST.="<option $selected value='".$code."'>".\Ipolh\SDEK\Bitrix\Tools::encodeFromUTF8($punkts['Name']." (".$punkts['Address'].") [".$code."]")."</option>";
    }
}
$arBPST .= "}";

//Доп. опции
$exOpts = sdekdriver::getExtraOptions();
foreach($exOpts as $code => $vals)
    if(array_key_exists('AS', $ordrVals) && $ordrVals['AS'][$code] == 'Y')
        $exOpts[$code]['DEF'] = 'Y';
    elseif(self::$isLoaded)
        $exOpts[$code]['DEF'] = 'N';

// Splitting for cender-cities
$senderWH = 0;
if(\Ipolh\SDEK\option::get('warhouses')==='Y'){
    if(self::isConverted() && $workMode == 'order'){
        CDeliverySDEK::countDelivery(array(
            'GOODS'      => CDeliverySDEK::setOrderGoods(self::$orderId),
            'CITY_TO_ID' => $ordrVals['location']
        ));
    }
    $senderWH = is_array(sdekShipmentCollection::$shipments) ? count(sdekShipmentCollection::$shipments) - 1 : -1;
    if($senderWH)
        if(strpos($ordrVals['service'],'[')!==0){
            $senderWH = array();
            foreach(sdekShipmentCollection::$shipments as $shipment)
                $senderWH[]= array($shipment->sender,$ordrVals['service']);
        }else
            $senderWH = json_decode($ordrVals['service'],true);
}
// countries and currencies

$arCity  = sqlSdekCity::getBySId($ordrVals['location']);
$country = ($arCity['COUNTRY']) ? $arCity['COUNTRY'] : 'rus';

$acc = false;
if(self::$isLoaded)
    $acc = self::defineAuth(array('ID'=>self::$requestVals['ACCOUNT']));
else {
    if(self::$orderDescr['info']['DELIVERY_SDEK'] && self::$orderDescr['info']['DELIVERY_ID']){
        $config = self::getDeliveryConfig(self::$orderDescr['info']['DELIVERY_ID']);
        if(array_key_exists('ACCOUNT', $config) && $config['ACCOUNT'] && array_key_exists('VALUE',$config['ACCOUNT']) && $config['ACCOUNT']['VALUE']){
            $acc = self::defineAuth($config['ACCOUNT']['VALUE']);
        }
    }
    if(!$acc)
        $acc = self::defineAuth(array('COUNTRY' => $country));
}

if(!isset($cntrCurrency) || !$cntrCurrency){
    $svdCountries = self::zaDEjsonit(\Ipolh\SDEK\option::get('countries'));
    $defVal = CCurrency::GetBaseCurrency(); // сейчас считается, что всегда рубли
    $cntrCurrency = false;
    if(array_key_exists($country,$svdCountries) && $svdCountries[$country]['cur'] && $svdCountries[$country]['cur'] != $defVal)
        $cntrCurrency = $svdCountries[$country]['cur'];
}
\Ipolh\SDEK\Bitrix\Tools::initJQuery();
?>
<?=sdekdriver::getModuleExt('packController')?>
<?=sdekdriver::getModuleExt('markingController')?>
    <link href="/bitrix/js/<?=self::$MODULE_ID?>/jquery-ui.css?<?= time() ?>" type="text/css" rel="stylesheet" />
    <link href="/bitrix/js/<?=self::$MODULE_ID?>/jquery-ui.structure.css?<?= time() ?>" type="text/css" rel="stylesheet" />

    <script src='/bitrix/js/<?=self::$MODULE_ID?>/jquery-ui.js?<?= time() ?>' type='text/javascript'></script>
    <style type='text/css'>
        .PropWarning{
            background: url('/bitrix/images/<?=self::$MODULE_ID?>/trouble.png') no-repeat transparent;
            background-size: contain;
            display: inline-block;
            height: 12px;
            position: relative;
            width: 12px;
        }
        .PropWarning:hover{
            background: url('/bitrix/images/<?=self::$MODULE_ID?>/trouble.png') no-repeat transparent !important;
            background-size: contain !important;
        }
        .WarningLK{
            background: url('/bitrix/images/<?=self::$MODULE_ID?>/lkcount.png') no-repeat transparent;
            background-size: contain;
            display: inline-block;
            height: 12px;
            position: relative;
            width: 12px;
        }
        .WarningLK:hover{
            background: url('/bitrix/images/<?=self::$MODULE_ID?>/lkcount.png') no-repeat transparent !important;
            background-size: contain !important;
        }
        .PropHint {
            background: url('/bitrix/images/<?=self::$MODULE_ID?>/hint.gif') no-repeat transparent;
            display: inline-block;
            height: 12px;
            position: relative;
            width: 12px;
        }
        .PropHint:hover{background: url('/bitrix/images/<?=self::$MODULE_ID?>/hint.gif') no-repeat transparent !important;}
        .b-popup {
            background-color: #FEFEFE;
            border: 1px solid #9A9B9B;
            box-shadow: 0px 0px 10px #B9B9B9;
            display: none;
            font-size: 12px;
            padding: 19px 13px 15px;
            position: absolute;
            top: 38px;
            width: 300px;
            z-index: 12;
        }
        .b-popup .pop-text {
            margin-bottom: 10px;
            color:#000;
        }
        .pop-text i {color:#AC12B1;}
        .b-popup .close {
            background: url('/bitrix/images/<?=self::$MODULE_ID?>/popup_close.gif') no-repeat transparent;
            cursor: pointer;
            height: 10px;
            position: absolute;
            right: 4px;
            top: 4px;
            width: 10px;
        }
        #IPOLSDEK_wndOrder{
            width: 100%;
        }
        #IPOLSDEK_badDeliveryTerm{
            display:none;
        }
        #IPOLSDEK_killDeliveryTerm{
            width: 15px;
            height: 15px;
            display: none;
            background: url("/bitrix/images/<?=self::$MODULE_ID?>/delPack.png") !important;
            right: -24px;
            position: relative;
            top: 4px;
            cursor:pointer;
        }
        #IPOLSDEK_allTarifs{
            border-collapse: collapse;
            width: 100%;
        }
        #IPOLSDEK_allTarifs td{
            padding: 3px;
        }
        #IPOLSDEK_tarifWarning{
            display:none;
        }
        #IPOLSDEK_searchPVZ,#IPOLSDEK_noSearchPVZ,#IPOLSDEK_searchPST,#IPOLSDEK_noSearchPST{
            cursor: pointer;
            width: 15px;
            height: 15px;
        }
        #IPOLSDEK_searchPVZ,#IPOLSDEK_searchPST{
            background: url("/bitrix/images/<?=self::$MODULE_ID?>/edit.png") !important;
            display: inline-block;
        }
        #IPOLSDEK_noSearchPVZ,#IPOLSDEK_noSearchPST{
            display:none;
            background: url("/bitrix/images/<?=self::$MODULE_ID?>/delPack.png") !important;
        }
        #IPOLSDEK_searchPVZPlace,#IPOLSDEK_searchPSTPlace{
            display: none;
        }
        #IPOLSDEK_tarifWarning span{
            font-size: 10px;
        }
        #IPOLSDEK_service, #IPOLSDEK_PVZ,#IPOLSDEK_PST{
            max-width: 315px;
        }
        .IPOLSDEK_gabInput{
            width: 28px;
        }
        #IPOLSDEK_gabsPlace{
            min-height: 27px;
        }
        .IPOLSDEK_badInput{
            background-color: #FFBEBE !important;
        }
        #IPOLSDEK_account_table table{
            border-collapse: collapse;
            margin-bottom: 10px;
        }
        #IPOLSDEK_account_table table td{
            border: 1px solid #dce7ed;
            padding: 5px;
        }
        .errorText{
            color:red;
            font-size:11px;
        }
        [class^=IPOLSDEK_block_]{
            display:none;
        }
    </style>
    <script>
        <?=sdekdriver::getModuleExt('mask_input')?>
        var IPOLSDEK_oExport = {
            orderId  : "<?=self::$orderId?>",
            shipment : "<?=self::$shipmentID?>",
            mode     : "<?=self::$workMode?>",
            status   : "<?=$status?>",
            badPVZ   : <?=$arBPVZ?>,
            badPST   : <?=$arBPST?>,
            goodsPrice   : <?=$ordrVals['toPay']?>,
            estimatedCost : <?=$ordrVals['estimatedCost']?>,
            delivPrice   : <?=$ordrVals['deliveryP']?>,
            curDelivery  : <?=(self::$orderDescr['info']['DELIVERY_SDEK']) ? '"'.self::$orderDescr['info']['DELIVERY_ID'].'"' : "false"?>,
            country      : "<?=$country?>",
            person	     : '<?=(self::$orderDescr['info']['PERSON_TYPE_ID']) ? self::$orderDescr['info']['PERSON_TYPE_ID'] : '1'?>',
            paysystem    : <?=(self::$orderDescr['info']['PAY_SYSTEM_ID']) ? "'".self::$orderDescr['info']['PAY_SYSTEM_ID']."'" : 'false'?>,
            deliveryDate : false,
            goods : <?=CUtil::PhpToJSObject(sdekdriver::getGoodsArray(sdekExport::$orderId,sdekExport::$shipmentID))?>,
            cdekApi: '<?= \Ipolh\SDEK\abstractGeneral::isNewApp() ? \Ipolh\SDEK\abstractGeneral::API_2_0 : \Ipolh\SDEK\abstractGeneral::API_1_5 ?>',
            storeWarning: '<?=(!self::$isLoaded && empty($ordrVals['storeId']))?>',

            ajax: function(params){
                var ajaxParams = {
                    type  : 'POST',
                    url   : "/bitrix/js/<?=self::$MODULE_ID?>/ajax.php",
                    error : function(a,b,c){console.log('export '+b,c);}
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

            load: function(){
                if($('#IPOLSDEK_btn').length) return;

                /* B24 support */
                if ($('#IPOLSDEK_btn_container').length)
                {
                    $('#IPOLSDEK_btn_container').prepend("<a href='javascript:void(0)' onclick='IPOLSDEK_oExport.showWindow()' class='ui-btn ui-btn-light-border ui-btn-icon-edit' style='margin-left:12px;' id='IPOLSDEK_btn'><?=GetMessage('IPOLSDEK_JSC_SOD_BTNAME')?></a>");
                }

                /* Standard */
                if ($('.adm-detail-toolbar').find('.adm-detail-toolbar-right').length)
                {
                    $('.adm-detail-toolbar').find('.adm-detail-toolbar-right').prepend("<a href='javascript:void(0)' onclick='IPOLSDEK_oExport.showWindow()' class='adm-btn' id='IPOLSDEK_btn'><?=GetMessage('IPOLSDEK_JSC_SOD_BTNAME')?></a>");
                }

                var btn = $('#IPOLSDEK_btn');
                switch(IPOLSDEK_oExport.status){
                    case 'NEW'    : break;
                    case 'ERROR'  : btn.css('color','#F13939'); break;
                    default       : btn.css('color','#3A9640'); break;
                }

                IPOLSDEK_packs.init();
                IPOLSDEK_marks.init(<?=(array_key_exists('marks',$ordrVals) && $ordrVals['marks']) ? CUtil::PhpToJSObject($ordrVals['marks']) : 'false'?>);
            },

            /* window */
            wnd: false,
            showWindow: function(){
                var savButStat='';
                if(IPOLSDEK_oExport.status!=='ERROR' && IPOLSDEK_oExport.status!=='NEW')
                    savButStat='style="display:none"';
                var delButStat='';
                if(
                    IPOLSDEK_oExport.status !=='OK' &&
                    IPOLSDEK_oExport.status !=='ERROR'&&
                    IPOLSDEK_oExport.status !=='DELETD'
                )
                    delButStat='style="display:none"';
                var prntButStat='style="display:none"';
                if(IPOLSDEK_oExport.status ==='OK')
                    prntButStat='';
                var checkButStat = 'style="display:none"';
                if(IPOLSDEK_oExport.status === 'WAIT'){
                    checkButStat='';
                }

                var courierBtnStat = 'style="display:none"';
                if (IPOLSDEK_oExport.status === 'OK') {
                    courierBtnStat = '';
                }

                if (IPOLSDEK_oExport.storeWarning) {
                    IPOLSDEK_oExport.ui.toggleBlock('store_warning');
                }

                if(!IPOLSDEK_oExport.wnd){
                    var html=$('#IPOLSDEK_wndOrder').parent().html();
                    $('#IPOLSDEK_wndOrder').parent().html('');
                    IPOLSDEK_oExport.wnd = new BX.CDialog({
                        title: "<?=GetMessage('IPOLSDEK_JSC_SOD_WNDTITLE')?>",
                        content: html,
                        icon: 'head-block',
                        resizable: true,
                        draggable: true,
                        height: '500',
                        width: '505',
                        buttons: [
                            '<input type=\"button\" id=\"IPOLSDEK_sendBtn\" value=\"<?=GetMessage('IPOLSDEK_JSC_SOD_SAVESEND')?>\"  '+savButStat+'onclick=\"IPOLSDEK_oExport.send(\'saveAndSend\')\"/>',
                            '<input type=\"button\" id=\"IPOLSDEK_checkBtn\" value=\"<?=GetMessage('IPOLSDEK_JSC_SOD_CHECK')?>\"  '+checkButStat+'onclick=\"IPOLSDEK_oExport.checkCdekNumber()\"/>',
                            '<input id=\"IPOLSDEK_allTarifsBtn\" type=\"button\" value=\"<?=GetMessage('IPOLSDEK_JSC_SOD_ALLTARIFS')?>\"  '+savButStat+'onclick=\"IPOLSDEK_oExport.allTarifs.show()\"/>', /* all tarifs */
                            '<input type=\"button\" value=\"<?=GetMessage('IPOLSDEK_JSC_SOD_DELETE')?>\" '+delButStat+' onclick=\"IPOLSDEK_oExport.delete()\"/>', /* удалить */
                            '<input type=\"button\" id=\"IPOLSDEK_PRINT\" value=\"<?=GetMessage('IPOLSDEK_JSC_SOD_PRNTSH')?>\" '+prntButStat+' onclick="IPOLSDEK_oExport.printer.get(\'<?=\Ipolh\SDEK\PrintHandler::PRINT_FORM_ORDER?>\'); return false;"/>',
                            '<input type=\"button\" id=\"IPOLSDEK_SHTRIH\" value=\"<?=GetMessage('IPOLSDEK_JSC_SOD_SHTRIH')?>\" '+prntButStat+' onclick="IPOLSDEK_oExport.printer.get(\'<?=\Ipolh\SDEK\PrintHandler::PRINT_FORM_BARCODE?>\'); return false;"/>',
                            '<input type=\"button\" value=\"<?=GetMessage('IPOLSDEK_JS_SOD_PACKS')?>\"  onclick="IPOLSDEK_packs.wnd.open(); return false;"/>', /* места */
                            '<input type=\"button\" value=\"<?=GetMessage('IPOLSDEK_JS_SOD_MARKING')?>\"  onclick="IPOLSDEK_marks.wnd.open(); return false;"/>', /* места */
                            <?php if($SDEK_ID) { ?>
                            '<br><br>',
                            '<input type=\"button\" value=\"<?=GetMessage('IPOLSDEK_JSC_SOD_FOLLOW')?>\" onclick="IPOLSDEK_oExport.follow(\'<?=\Ipolh\SDEK\SDEK\Tools::getTrackLink($SDEK_ID)?>\')"/>',
                            '<input type=\"button\" id=\"IPOLSDEK_courierBtn\" value=\"<?=GetMessage('IPOLSDEK_JSC_SOD_COURIER')?>\" '+courierBtnStat+' onclick="IPOLSDEK_oExport.newCourierCall()"/>',
                            <?php } ?>
                        ]
                    });
                }
                IPOLSDEK_oExport.onCodeChange($('#IPOLSDEK_service'),true);
                IPOLSDEK_oExport.checkPay();
                IPOLSDEK_oExport.onRecheck(true);
                IPOLSDEK_oExport.wnd.Show();
                <?php if($cntrCurrency) { ?>
                IPOLSDEK_oExport.currency.init();
                <?php } ?>
            },

            /* events */
            /* changing tarif: check weither pvz or couries, shows corresponding form fields */
            onCodeChange: function(wat,ifDef){
                if(wat.val() == 138 || wat.val() == 139) $('#IPOLSDEK_tarifWarning').css('display','table-row');
                else $('#IPOLSDEK_tarifWarning').css('display','');
                $('#IPOLSDEK_wndOrder').find('.IPOLSDEK_notSV').css('display','none');
                $('#IPOLSDEK_wndOrder').find('.IPOLSDEK_PST').css('display','none');
                $('#IPOLSDEK_timeFrom').closest('tr').css('display','none');
                $('#IPOLSDEK_wndOrder').find('.IPOLSDEK_SV').css('display','none');
                switch(IPOLSDEK_oExport.defineTarifs(wat.val())){
                    case 'courier':
                        $('#IPOLSDEK_wndOrder').find('.IPOLSDEK_notSV').css('display','');
                        $('#IPOLSDEK_timeFrom').closest('tr').css('display','none');
                        break;
                    case 'pickup' :   $('#IPOLSDEK_wndOrder').find('.IPOLSDEK_SV').css('display',''); break;
                    case 'postamat' : $('#IPOLSDEK_wndOrder').find('.IPOLSDEK_PST').css('display',''); break;
                }

                if(typeof(ifDef) === 'undefined')
                    IPOLSDEK_oExport.onRecheck();
                else
                    IPOLSDEK_oExport.onRecheck(true);
                IPOLSDEK_oExport.onPVZChange();
            },
            /* changing destination city (if in error list) */
            onMSChange: function(wat){
                $('#IPOLSDEK_location').val(wat.val());
                IPOLSDEK_oExport.onRecheck();
            },
            /* changing PVZ - checking available */
            onPVZChange: function(wat){
                if(typeof(wat) === 'undefined')
                    wat = $('#IPOLSDEK_PVZ');
                if(typeof(IPOLSDEK_oExport.badPVZ[wat.val()]) !== 'undefined')
                    $('#IPOLSDEK_oExport.badPVZ').css('display','inline');
                else
                    $('#IPOLSDEK_oExport.badPVZ').css('display','none');
            },
            onPSTChange: function(wat){
                if(typeof(wat) === 'undefined')
                    wat = $('#IPOLSDEK_PST');
                if(typeof(IPOLSDEK_oExport.badPST[wat.val()]) !== 'undefined')
                    $('#IPOLSDEK_oExport.badPST').css('display','inline');
                else
                    $('#IPOLSDEK_oExport.badPST').css('display','none');
            },
            /* changing data delivery */
            onDeliveryDateChange: function(){
                $('#IPOLSDEK_badDeliveryTerm').css('display','');
                var deliveryDate    = $('#IPOLSDEK_deliveryDate').val();
                var deliveryDateR   = IPOLSDEK_oExport.deliveryDate.toString();;
                if(deliveryDate){
                    $('#IPOLSDEK_killDeliveryTerm').css('display','inline-block');
                }else{
                    $('#IPOLSDEK_killDeliveryTerm').css('display','none');
                }
                if(deliveryDate && deliveryDateR){
                    $('#IPOLSDEK_deliveryTerm').html(deliveryDateR);
                    var deliveryDateROb = new Date();
                    deliveryDate = deliveryDate.split('.');
                    var deliveryDateOb  = new Date(deliveryDate[2],deliveryDate[1]-1,deliveryDate[0]);

                    if(deliveryDateR.indexOf('-') !== -1){
                        deliveryDateR   = deliveryDateR.substr(0,deliveryDateR.indexOf('-'));
                    }
                    deliveryDateROb.setHours(0);
                    deliveryDateROb.setDate(deliveryDateROb.getDate()+Number(deliveryDateR));

                    if(Number(deliveryDateOb - deliveryDateROb) < -43200000){
                        $('#IPOLSDEK_badDeliveryTerm').css('display','table-row');
                    }
                }
            },
            /* data */
            resetDate: function(){
                $("#IPOLSDEK_deliveryDate").val("");
                IPOLSDEK_oExport.onDeliveryDateChange();
            },
            /* Changing cender-ciry: recheck all */
            onDepartureChange: function(){
                IPOLSDEK_oExport.ajax({
                    data: {
                        isdek_action:     'loadStoreRequest',
                        fromLocationCode: $('#IPOLSDEK_departure').val(),
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data.success) {
                            let store = data.data;

                            if (isNaN(parseInt(store.storeId, 10))) {
                                // This means: no default active Store for this city, take new one
                                $('.IPOLSDEK_block_store_warning').show();

                                $('#IPOLSDEK_from_loc_street').val('');
                                $('#IPOLSDEK_from_loc_house').val('');
                                $('#IPOLSDEK_from_loc_flat').val('');

                                $('#IPOLSDEK_sender_company').val('');
                                $('#IPOLSDEK_sender_name').val('');
                                $('#IPOLSDEK_sender_phone').val('');
                                $('#IPOLSDEK_sender_phone_add').val('');

                                $('#IPOLSDEK_seller_name').val('');
                                $('#IPOLSDEK_seller_phone').val('');
                                $('#IPOLSDEK_seller_address').val('');
                            } else {
                                $('.IPOLSDEK_block_store_warning').hide();

                                $('#IPOLSDEK_from_loc_street').val((store.isAddressDataSent) ? store.street : '');
                                $('#IPOLSDEK_from_loc_house').val((store.isAddressDataSent) ? store.house : '');
                                $('#IPOLSDEK_from_loc_flat').val((store.isAddressDataSent) ? store.flat : '');

                                $('#IPOLSDEK_sender_company').val((store.isSenderDataSent) ? store.company : '');
                                $('#IPOLSDEK_sender_name').val((store.isSenderDataSent) ? store.fullName : '');
                                $('#IPOLSDEK_sender_phone').val((store.isSenderDataSent) ? store.phone : '');
                                $('#IPOLSDEK_sender_phone_add').val((store.isSenderDataSent) ? store.phoneAdditional : '');

                                $('#IPOLSDEK_seller_name').val((store.isSellerDataSent) ? store.sellerCompany : '');
                                $('#IPOLSDEK_seller_phone').val((store.isSellerDataSent) ? store.sellerPhone : '');
                                $('#IPOLSDEK_seller_address').val((store.isSellerDataSent) ? store.sellerAddress : '');
                            }

                            IPOLSDEK_oExport.onRecheck();
                        } else {
                            var str = '<?=GetMessage('IPOLSDEK_JSC_SOD_STORE_NOT_LOADED')?>';
                            if (data.errors.length) {
                                str += "\n" + data.errors;
                            }
                            alert(str);
                        }
                    }
                });
            },
            /* changing terms: recalculate */
            onRecheck: function(isNoAlert){
                var reqParams = IPOLSDEK_oExport.getInputsRecheck();

                if(typeof(reqParams) !== 'object' || typeof(reqParams.cityTo) === 'undefined'){
                    alert(reqParams);
                    return false;
                }

                IPOLSDEK_oExport.ajax({
                    data     : reqParams,
                    dataType : 'json',
                    success  : function(data){
                        if(typeof data.success !== 'undefined'){
                            var text = '';
                            if(data.success){
                                var dayLbl = data.termMin + "-" + data.termMax;
                                if(data.termMin == data.termMax) dayLbl = data.termMax;
                                IPOLSDEK_oExport.deliveryDate = dayLbl;
                                text = "<?=GetMessage("IPOLSDEK_JSC_SOD_NEWCONDITIONS_1")?>"  + dayLbl + " <?=GetMessage("IPOLSDEK_JS_SOD_HD_DAY")?>";
                                if(typeof(data.price) !== 'undefined')
                                    text+="<?=GetMessage("IPOLSDEK_JSC_SOD_NEWCONDITIONS_2")?>" + data.price;
                                if(typeof(data.sourcePrice) !== 'undefined')
                                    text+="\n\n<?=GetMessage("IPOLSDEK_JSC_SOD_PriceInLK")?>"+data.sourcePrice;
                                $('#IPOLSDEK_newPrDel').html(data.price);
                            } else {
                                text = data.error;
                                $('#IPOLSDEK_newPrDel').html('<?=GetMessage("IPOLSDEK_JS_SOD_noDost")?>');
                            }
                        }else{
                            for(var i in data)
                                text += data[i]+" ("+i+") \n";
                            $('#IPOLSDEK_newPrDel').html('<?=GetMessage("IPOLSDEK_JS_SOD_noDost")?>');
                        }
                        if(typeof(isNoAlert) === 'undefined')
                            alert(text);
                        IPOLSDEK_oExport.onDeliveryDateChange();
                    }
                });
            },

            /* Data for sending */
            getInputsRecheck: function(params){
                /* var city = $('#IPOLSDEK_location').val(); */
                var city = $('#IPOLSDEK_cityTo').val();
                if(!city)
                    return '<?=GetMessage("IPOLSDEK_JSC_SOD_NOCITY")?>';

                var tarif = $('#IPOLSDEK_service').val();
                if(!tarif)
                    return '<?=GetMessage("IPOLSDEK_JSC_SOD_NOTARIF")?>';

                var account = $('#IPOLSDEK_account').val();
                if(!account)
                    return '<?=GetMessage("IPOLSDEK_JSC_SOD_NOACCOUNT")?>';

                var GABS = {
                    'D_L' : $('#IPOLSDEK_GABS_D_L').val(),
                    'D_W' : $('#IPOLSDEK_GABS_D_W').val(),
                    'D_H' : $('#IPOLSDEK_GABS_D_H').val(),
                    'W'   : $('#IPOLSDEK_GABS_W').val(),
                };
                var packs = $('#IPOLSDEK_PLACES').val();
                if(packs)
                    packs = JSON.parse(packs);

                if(typeof(params) === 'undefined')
                    params = {};

                var cityFrom = (params.cityFrom) ? params.cityFrom : $('#IPOLSDEK_departure').val();

                return {
                    isdek_action : 'extCountDeliv',
                    orderId   : (params.orderId) ? params.orderId : IPOLSDEK_oExport.orderId,
                    mode      : (params.mode) ? params.mode : IPOLSDEK_oExport.mode,
                    shipment  : (params.shipment) ? params.shipment : IPOLSDEK_oExport.shipment,
                    cityTo    : city,
                    cityFrom  : cityFrom,
                    tarif     : (params.tarif) ? params.tarif : tarif,
                    GABS      : (params.GABS) ? params.GABS : GABS,
                    packs     : (params.packs) ? params.packs : packs,
                    account   : account,
                    delivery  : IPOLSDEK_oExport.curDelivery,
                    price	  : IPOLSDEK_oExport.estimatedCost,
                    person    : IPOLSDEK_oExport.person,
                    paysystem : IPOLSDEK_oExport.paysystem
                };
            },

            getInputs: function(){
                var dO={};

                var profile = IPOLSDEK_oExport.defineTarifs($('#IPOLSDEK_service').val());

                if($('#IPOLSDEK_isBeznal').prop('checked'))
                    dO['isBeznal']='Y';
                if($('#IPOLSDEK_minVats').prop('checked'))
                    dO['minVats']='Y';

                dO['estimatedCost'] = IPOLSDEK_oExport.estimatedCost;

                var reqFields = {
                    'service'   	 : {need: true},
                    'departure'		 : {need: true,check: ($('#IPOLSDEK_departure').length && true)},
                    'location'  	 : {need: true},
                    'deliveryDate'   : {need: false},
                    'name'     		 : {need: true},
                    'email'     	 : {need: false},
                    'phone'     	 : {need: true,format: IPOLSDEK_oExport.checkPhone,failFormat: "<?=GetMessage('IPOLSDEK_JSC_SOD_badPhone')?>"},
                    'comment'    	 : {need: false},
                    'reccompany'     : {need: false},
                    'NDSGoods'    	 : {need: false},
                    'NDSDelivery'    : {need: false},
                    'toPay'			 : {need: true, check: (typeof(dO['isBeznal']) === 'undefined' || dO['isBeznal'] != 'Y')},
                    'deliveryP'		 : {need: true, check: (typeof(dO['isBeznal']) === 'undefined' && dO['isBeznal'] != 'Y')},
                    'address': {need: true, check: (IPOLSDEK_oExport.cdekApi === '<?= \Ipolh\SDEK\abstractGeneral::API_2_0 ?>')},
                    'street': {need: true, check: (profile === 'courier' && IPOLSDEK_oExport.cdekApi === '<?= \Ipolh\SDEK\abstractGeneral::API_1_5 ?>')},
                    'house': {need: true, check: (profile === 'courier' && IPOLSDEK_oExport.cdekApi === '<?= \Ipolh\SDEK\abstractGeneral::API_1_5 ?>')},
                    'flat': {need: false},
                    /* 'flat'       	 : {need: true,check: (profile == 'courier')}, */
                    'PVZ'            : {need: true,check: (profile == 'pickup')},
                    'PST'            : {need: true,check: (profile == 'postamat')},
                    'from_loc_street':  {need: false},
                    'from_loc_house':   {need: false},
                    'from_loc_flat':    {need: false},
                    'sender_company':   {need: false},
                    'sender_name':      {need: false},
                    'sender_phone':     {need: false},
                    'sender_phone_add': {need: false},
                    'seller_name':      {need: false},
                    'seller_phone':     {need: false},
                    'seller_address':   {need: false},
                    'account':          {need: false}
                };

                for(var i in reqFields){
                    if(typeof(reqFields[i].need) === 'undefined') continue;
                    if(typeof(reqFields[i].check) !== 'undefined' && !reqFields[i].check) continue;
                    dO[i]=$('#IPOLSDEK_'+i).val();
                    if(!dO[i] && reqFields[i].need){
                        return $('#IPOLSDEK_'+i).closest('tr').children('td').html();
                    }
                    if(typeof(reqFields[i].format) !== 'undefined'){
                        if(!reqFields[i].format(dO[i])){
                            return (typeof(reqFields[i].failFormat)!== 'undefined') ? reqFields[i].failFormat : $('#IPOLSDEK_'+i).closest('tr').children('td').html();
                        }
                    }
                }

                dO['AS'] = {};
                $('[id^="IPOLSDEK_AS_"]').each(function(){
                    if($(this).prop('checked'))
                        dO['AS'][$(this).val()]='Y';
                });

                var packs = $('#IPOLSDEK_PLACES').val();
                if(packs){
                    packs = JSON.parse(packs);
                    dO['packs'] = packs;
                }

                var marks = IPOLSDEK_marks.save;
                if(marks){
                    dO.marks = marks;
                }

                $('[id^="IPOLSDEK_GABS_"]').each(function(){
                    if(typeof dO['GABS'] == 'undefined') dO['GABS'] = {};
                    dO['GABS'][$(this).attr('id').substr(14)]=$(this).val();
                });

                if($("#IPOLSDEK_currency").val())
                    dO['currency'] = $('#IPOLSDEK_currency').val();

                return dO;
            },

            /* buttons */
            /* save and send */
            send: function(){
                if ($('#IPOLSDEK_isCODavailable').val() == 'N' && !$('#IPOLSDEK_isBeznal').prop('checked')) {
                    alert('<?=GetMessage('IPOLSDEK_JSC_SOD_NOCODAVAILABLE')?>');
                    return;
                }
                var dataObject=IPOLSDEK_oExport.getInputs();
                if(typeof dataObject !== 'object'){if(dataObject)alert('<?=GetMessage('IPOLSDEK_JSC_SOD_ZAPOLNI')?> "'+dataObject+'"');return;}
                dataObject['isdek_action'] = 'saveAndSend';
                dataObject['orderId']  = IPOLSDEK_oExport.orderId;
                dataObject['mode']     = IPOLSDEK_oExport.mode;
                dataObject['shipment'] = IPOLSDEK_oExport.shipment;
                $('[onclick^="IPOLSDEK_oExport.send("]').each(function(){$(this).css('display','none')});
                IPOLSDEK_oExport.ajax({
                    data    : dataObject,
                    success : function(data){
                        alert(data);
                        IPOLSDEK_oExport.wnd.Close();
                    }
                });
            },
            /* delete */
            delete: function(){
                var oId = (IPOLSDEK_oExport.mode == 'shipment') ? IPOLSDEK_oExport.shipment : IPOLSDEK_oExport.orderId;
                if(IPOLSDEK_oExport.status == 'NEW' || IPOLSDEK_oExport.status == 'ERROR' || IPOLSDEK_oExport.status == 'DELETE'){
                    if(confirm("<?=GetMessage('IPOLSDEK_JSC_SOD_IFDELETE')?>"))
                        IPOLSDEK_oExport.ajax({
                            data    : {isdek_action:'delReqOD',oid:oId,mode:IPOLSDEK_oExport.mode},
                            success : function(data){
                                alert(data);
                                document.location.reload();
                            }
                        });
                }else{
                    if(IPOLSDEK_oExport.status == 'OK'){
                        if(confirm("<?=GetMessage('IPOLSDEK_JSC_SOD_IFKILL')?>"))
                            IPOLSDEK_oExport.ajax({
                                data    : {isdek_action:'killReqOD',oid:oId,mode:IPOLSDEK_oExport.mode},
                                success : function(data){
                                    if(data.indexOf('GD:')===0){
                                        alert(data.substr(3));
                                        document.location.reload();
                                    }
                                    else
                                        alert(data);
                                }
                            });
                    }
                }
            },
            /* print invoice and barcode files */
            printer: {
                done: false,
                tryCount: 0,
                tryMax: 10,
                formType: '<?=\Ipolh\SDEK\PrintHandler::PRINT_FORM_ORDER?>',
                messages: '',

                get: function(formType){
                    IPOLSDEK_oExport.printer.done = false;
                    IPOLSDEK_oExport.printer.tryCount = 0;
                    IPOLSDEK_oExport.printer.formType = formType;
                    IPOLSDEK_oExport.printer.messages = '';

                    BX.showWait();
                    $('#IPOLSDEK_PRINT').attr('disabled', 'true').val('<?=GetMessage('IPOLSDEK_JSC_SOD_LOADING')?>');
                    $('#IPOLSDEK_SHTRIH').attr('disabled', 'true').val('<?=GetMessage('IPOLSDEK_JSC_SOD_LOADING')?>');

                    IPOLSDEK_oExport.printer.makeFile();
                },

                makeFile: function(){
                    let action;
                    switch(IPOLSDEK_oExport.printer.formType) {
                        case '<?=\Ipolh\SDEK\PrintHandler::PRINT_FORM_ORDER?>': action = 'getInvoiceRequest'; break;
                        case '<?=\Ipolh\SDEK\PrintHandler::PRINT_FORM_BARCODE?>': action = 'getBarcodeRequest'; break;
                    }

                    IPOLSDEK_oExport.ajax({
                        data: {
                            isdek_action: action,
                            data: {[IPOLSDEK_oExport.mode]: [(IPOLSDEK_oExport.mode === 'shipment') ? IPOLSDEK_oExport.shipment : IPOLSDEK_oExport.orderId]}
                        },
                        dataType: 'json',
                        success: IPOLSDEK_oExport.printer.onRequestFile
                    });
                },

                getFile: function(data){
                    let action;
                    switch(IPOLSDEK_oExport.printer.formType) {
                        case '<?=\Ipolh\SDEK\PrintHandler::PRINT_FORM_ORDER?>': action = 'getInvoiceByUuidRequest'; break;
                        case '<?=\Ipolh\SDEK\PrintHandler::PRINT_FORM_BARCODE?>': action = 'getBarcodeByUuidRequest'; break;
                    }

                    IPOLSDEK_oExport.ajax({
                        data: {
                            isdek_action: action,
                            data: data
                        },
                        dataType: 'json',
                        success: IPOLSDEK_oExport.printer.onRequestFile
                    });
                },

                onRequestFile: function(data){
                    if (data.success === true) {
                        if (typeof(data.files) !== 'undefined') {
                            for (let i in data.files) {
                                window.open(data.files[i]);
                            }
                        }

                        if (typeof(data.notReady) !== 'undefined') {
                            if (++IPOLSDEK_oExport.printer.tryCount > IPOLSDEK_oExport.printer.tryMax) {
                                IPOLSDEK_oExport.printer.done = true;
                                IPOLSDEK_oExport.printer.messages += "<br><br>" + '<?=GetMessage('IPOLSDEK_JS_PR_MESS_MAXTRY')?>';
                            } else {
                                // Wait a bit and try again
                                setTimeout(IPOLSDEK_oExport.printer.getFile, 300, data.notReady);
                            }
                        } else {
                            IPOLSDEK_oExport.printer.done = true;
                        }
                    } else {
                        IPOLSDEK_oExport.printer.done = true;

                        if (data.errors.length) {
                            IPOLSDEK_oExport.printer.messages += "<br><br>" + data.errors;
                        }
                    }

                    if (data.warnings.length) {
                        IPOLSDEK_oExport.printer.messages += "<br><br>" + data.warnings;
                    }

                    if (IPOLSDEK_oExport.printer.done) {
                        IPOLSDEK_oExport.printer.finish();
                    }
                },

                finish: function(){
                    BX.closeWait();
                    $('#IPOLSDEK_PRINT').removeAttr('disabled').val('<?=GetMessage('IPOLSDEK_JSC_SOD_PRNTSH')?>');
                    $('#IPOLSDEK_SHTRIH').removeAttr('disabled').val('<?=GetMessage('IPOLSDEK_JSC_SOD_SHTRIH')?>');

                    if (IPOLSDEK_oExport.printer.messages.length) {
                        let dialog = new BX.CDialog({
                            title: '<?=GetMessage('IPOLSDEK_JS_PR_WND_TITLE')?>',
                            content: '<?=GetMessage('IPOLSDEK_JS_PR_MESS_ERRORS')?>' + IPOLSDEK_oExport.printer.messages,
                            icon: 'head-block',
                            resizable: true,
                            draggable: true,
                            height: '250',
                            width: '300',
                            buttons: [BX.CDialog.prototype.btnClose]
                        });
                        dialog.Show();
                    }
                },
            },
            /* check */
            checkCdekNumber : function () {
                $('#IPOLSDEK_checkBtn').css('display','none');

                IPOLSDEK_oExport.ajax({
                    data    : {
                        'isdek_action' : 'checkCdekNumber',
                        'orderId'  : IPOLSDEK_oExport.orderId,
                        'mode'     : IPOLSDEK_oExport.mode,
                        'shipment' : IPOLSDEK_oExport.shipment
                    },
                    dataType:'JSON',
                    success : function(data){
                        if(data.cdek_number){
                            alert('<?=\Ipolh\SDEK\Bitrix\Tools::getMessage('JS_SOD_check_success')?>'+data.cdek_number+'.');
                        } else {
                            alert('<?=\Ipolh\SDEK\Bitrix\Tools::getMessage('JS_SOD_check_fail')?> '+data.error.join(', '));
                        }
                        document.location.reload();
                    }
                });
            },
            follow: function(url){
                window.open(url, '_blank');
            },
            newCourierCall: function(){
                window.open('/bitrix/admin/ipol_sdek_courier_calls.php?lang=ru', '_blank');
            },

            /* service */
            /* tarif: pvz, courier or inpost (old) */
            defineTarifs: function(val){
                val = parseInt(val);

                var arPVZ      = [<?=sdekHelper::getTarifList(array('type'=>'pickup','answer'=>'string','fSkipCheckBlocks'=>true))?>];
                var arPOSTAMAT = [<?=sdekHelper::getTarifList(array('type'=>'postamat','answer'=>'string','fSkipCheckBlocks'=>true))?>];

                if(arPVZ.indexOf(val) !== -1)
                    return 'pickup';
                if(arPOSTAMAT.indexOf(val) !== -1){
                    return 'postamat';
                }
                return 'courier';
            },
            /* tarif: todoor or to sklad */
            isToDoor: function(val){
                var dT = [<?=sdekHelper::getDoorTarifs(true)?>];
                for(var i = 0; i < dT.length; i++)
                    if(dT[i] == val) return true;
                return false;
            },
            /* checking payed / beznal */
            checkPay: function(){
                if($('#IPOLSDEK_isBeznal').prop('checked')){
                    <?php if($badPay) { ?>$('#IPOLSDEK_notPayed').css('display','inline');<?php } ?>
                    $('#IPOLSDEK_toPay').attr('disabled','disabled');
                    $('#IPOLSDEK_deliveryP').attr('disabled','disabled');
                    $('#IPOLSDEK_NDSGoods').attr('disabled','disabled');
                    $('#IPOLSDEK_NDSDelivery').attr('disabled','disabled');
                    $('#IPOLSDEK_toPay').val('0');
                    $('#IPOLSDEK_deliveryP').val('0');
                }else{
                    <?php if($badPay) { ?>$('#IPOLSDEK_notPayed').css('display','none');<?php } ?>
                    $('#IPOLSDEK_toPay').removeAttr('disabled');
                    $('#IPOLSDEK_deliveryP').removeAttr('disabled');
                    $('#IPOLSDEK_NDSGoods').removeAttr('disabled');
                    $('#IPOLSDEK_NDSDelivery').removeAttr('disabled');
                    $('#IPOLSDEK_toPay').val(IPOLSDEK_oExport.goodsPrice);
                    $('#IPOLSDEK_deliveryP').val(IPOLSDEK_oExport.delivPrice);
                    if ($('#IPOLSDEK_isCODavailable').val() == 'N') {
                        alert('<?=GetMessage('IPOLSDEK_JSC_SOD_NOCODAVAILABLE')?>');
                    }
                }
            },
            /* service props */
            serverShow: function(){
                $(".IPOLSDEK_detOrder").css("display","");
                IPOLSDEK_oExport.gabs.label();
            },
            /* popup hints */
            popup: function (code, info){
                var offset = $(info).position().top;
                var obj;
                if(code == 'next') 	obj = $(info).next();
                else  				obj = $('#'+code);

                var LEFT = (parseInt($('#IPOLSDEK_wndOrder').width())-parseInt(obj.width()))/2;
                obj.css({
                    top: (offset+15)+'px',
                    left: LEFT,
                    display: 'block'
                });
                return false;
            },

            checkFloat: function(wat){
                var val = parseFloat(wat.val().replace(',','.'));
                wat.val((isNaN(val)) ? 0 : val);
            },

            checkPhone: function(val){
                /* var check = /^(\+7(\d{10}))/; */
                var check = /^(\+(\d{11}))/;
                return check.test(val);
            },

            isEmpty: function(obj){
                if(typeof(obj) === 'object')
                    for(var i in obj)
                        return false;
                return true;
            },

            /* additional windows and functional */
            /* searchPVZ */
            searchPVZ: {
                on: function(label){
                    if(typeof(label) === 'undefined') {label = 'PVZ';}
                    $('#IPOLSDEK_search'+label).css('display','none');
                    $('#IPOLSDEK_noSearch'+label).css('display','inline-block');
                    $('#IPOLSDEK_'+label).attr('size',5);
                    $('#IPOLSDEK_search'+label+'Place').css('display','block');
                    $('#IPOLSDEK_sendBtn').attr('disabled','disabled');
                },
                off : function(label){
                    if(typeof(label) === 'undefined') {label = 'PVZ';}
                    $('#IPOLSDEK_search'+label).css('display','inline-block');
                    $('#IPOLSDEK_noSearch'+label).css('display','none');
                    $('#IPOLSDEK_'+label).removeAttr('size');
                    $('#IPOLSDEK_search'+label+'Place').css('display','none');
                    $('#IPOLSDEK_search'+label+'Input').val('');
                    if($('#IPOLSDEK_'+label+' option:visible').length === 1){
                        $('#IPOLSDEK_'+label).val($('#IPOLSDEK_'+label+' option:visible').attr('value'));
                    }
                    $('#IPOLSDEK_'+label+' option').each(function(){$(this).css('display','');});
                    $('#IPOLSDEK_sendBtn').removeAttr('disabled');
                },
                search : function(label){
                    if(typeof(label) === 'undefined') {label = 'PVZ';}
                    var text = $('#IPOLSDEK_search'+label+'Input').val().toLowerCase();
                    $('#IPOLSDEK_'+label+' option').each(function(){
                        if($(this).html().toLowerCase().indexOf(text) === -1){
                            $(this).css('display','none');
                        } else {
                            $(this).css('display','');
                        }
                    });
                }
            },
            /* all tarifs */
            allTarifs: {
                wnd: false,

                countData: false,

                availTarifs : false,
                tarifDescr  : false,

                curMode : false,
                stopF   : false,

                show: function(){
                    IPOLSDEK_oExport.allTarifs.stopF = false;
                    var wndContent = '<table id=\'IPOLSDEK_allTarifs\'></table><div id=\'IPOLSDEK_allTarAjax\' style=\'text-align:center;border:none;padding-top: 10px;\'><img src=\'/bitrix/images/<?=self::$MODULE_ID?>/ajax.gif\'></div><input type=\'button\' id=\'IPOLSDEK_tarifStopper\' value=\'<?=GetMessage('IPOLSDEK_LBL_STOP')?>\' onclick=\'IPOLSDEK_oExport.allTarifs.stop()\'>';

                    $('#IPOLSDEK_allTarifsBtn').attr('disabled','disabled');

                    if(!IPOLSDEK_oExport.allTarifs.wnd){
                        IPOLSDEK_oExport.allTarifs.wnd = new BX.CDialog({
                            title: "<?=GetMessage('IPOLSDEK_JSC_SOD_ALLTARIFS')?>",
                            content: wndContent,
                            icon: 'head-block',
                            resizable: true,
                            draggable: true,
                            height: '500',
                            width: '700',
                            buttons: []
                        });
                    }else
                        $('#IPOLSDEK_allTarifs').parent().html(wndContent);

                    var packs = $('#IPOLSDEK_PLACES').val();
                    if(packs)
                        packs = JSON.parse(packs);

                    IPOLSDEK_oExport.allTarifs.countData = {
                        isdek_action : 'htmlTaritfList',
                        orderId  : IPOLSDEK_oExport.orderId,
                        mode     : IPOLSDEK_oExport.mode,
                        shipment : IPOLSDEK_oExport.shipment,
                        cityTo   : $('#IPOLSDEK_cityTo').val(),
                        cityFrom : 0,
                        GABS	 : {
                            'D_L' : $('#IPOLSDEK_GABS_D_L').val(),
                            'D_W' : $('#IPOLSDEK_GABS_D_W').val(),
                            'D_H' : $('#IPOLSDEK_GABS_D_H').val(),
                            'W'   : $('#IPOLSDEK_GABS_W').val(),
                        },
                        packs    : packs,
                        account  : $('#IPOLSDEK_account').val()
                    };

                    IPOLSDEK_oExport.ajax({
                        data     : {'isdek_action':'getAllTarifsToCount'},
                        dataType : 'json',
                        success  :function(data){
                            $('#IPOLSDEK_allTarifsBtn').removeAttr('disabled');
                            if(IPOLSDEK_oExport.isEmpty(data))
                                alert('<?=GetMessage('IPOLSDEK_JSC_SOD_noTarifs')?>');
                            else{
                                IPOLSDEK_oExport.allTarifs.availTarifs = data;
                                IPOLSDEK_oExport.allTarifs.tarifDescr  = {};
                                for(var i in IPOLSDEK_oExport.allTarifs.availTarifs)
                                    for(var j in IPOLSDEK_oExport.allTarifs.availTarifs[i])
                                        IPOLSDEK_oExport.allTarifs.tarifDescr[j] = IPOLSDEK_oExport.allTarifs.availTarifs[i][j];
                                IPOLSDEK_oExport.allTarifs.wnd.Show();
                                IPOLSDEK_oExport.allTarifs.closer();
                                IPOLSDEK_oExport.allTarifs.carnage(true);
                            }
                        }
                    });
                },

                carnage: function(isStart){
                    if(
                        (
                            typeof(isStart) === 'undefined' &&
                            !IPOLSDEK_oExport.allTarifs.curMode
                        ) || IPOLSDEK_oExport.allTarifs.stopF
                    ){
                        IPOLSDEK_oExport.allTarifs.curMode = false;
                        $('#IPOLSDEK_allTarAjax').css('display','none');
                        IPOLSDEK_oExport.allTarifs.closer(true);
                        return;
                    }

                    if(!IPOLSDEK_oExport.allTarifs.curMode){
                        IPOLSDEK_oExport.allTarifs.curMode = IPOLSDEK_oExport.allTarifs.getFirstTafirType();
                        $('#IPOLSDEK_allTarifs').append("<tr class='adm-list-table-header'><td colspan='4' class='adm-list-table-cell'>"+IPOLSDEK_oExport.allTarifs.lang[IPOLSDEK_oExport.allTarifs.curMode]+"</td></tr>");
                    }

                    if(IPOLSDEK_oExport.isEmpty(IPOLSDEK_oExport.allTarifs.availTarifs[IPOLSDEK_oExport.allTarifs.curMode])){
                        delete(IPOLSDEK_oExport.allTarifs.availTarifs[IPOLSDEK_oExport.allTarifs.curMode]);
                        IPOLSDEK_oExport.allTarifs.curMode = IPOLSDEK_oExport.allTarifs.getFirstTafirType();
                        if(!IPOLSDEK_oExport.allTarifs.curMode){
                            $('#IPOLSDEK_allTarAjax').css('display','none');
                            IPOLSDEK_oExport.allTarifs.closer(true);
                        }else
                            $('#IPOLSDEK_allTarifs').append("<tr class='adm-list-table-header'><td colspan='4' class='adm-list-table-cell'>"+IPOLSDEK_oExport.allTarifs.lang[IPOLSDEK_oExport.allTarifs.curMode]+"</td></tr>");
                    }

                    if(IPOLSDEK_oExport.allTarifs.curMode){
                        var curTarif = false;
                        for(var i in IPOLSDEK_oExport.allTarifs.availTarifs[IPOLSDEK_oExport.allTarifs.curMode]){
                            curTarif = i;
                            delete(IPOLSDEK_oExport.allTarifs.availTarifs[IPOLSDEK_oExport.allTarifs.curMode][i]);
                            var reqParams = IPOLSDEK_oExport.getInputsRecheck();
                            reqParams.tarif = curTarif;
                            IPOLSDEK_oExport.ajax({
                                data: reqParams,
                                dataType: 'json',
                                success: function(data){
                                    var arBlocks = {ready: false,price:'',term:'',choosable:''};
                                    if(data.tarif){
                                        arBlocks.ready = true;
                                        arBlocks.name  = IPOLSDEK_oExport.allTarifs.tarifDescr[data.tarif];
                                        if(data.success){
                                            arBlocks.price = '';
                                            if(typeof(data.price) !== 'undefined'){
                                                arBlocks.price = data.price;
                                                if(typeof(data.sourcePrice) !== 'undefined')
                                                    arBlocks.price += '<a href="#" class="PropWarning" onclick="return false;" title="<?=GetMessage('IPOLSDEK_JSC_SOD_PriceInLK')?> '+data.sourcePrice+'">';
                                            }else{
                                                if(typeof(data.sourcePrice) !== 'undefined'){
                                                    arBlocks.price = data.sourcePrice+' <a href="#" class="WarningLK" onclick="return false;" title="<?=GetMessage('IPOLSDEK_JSC_SOD_PriceONLYInLK')?>">';
                                                }
                                            }
                                            arBlocks.term = ((data['termMin'] == data['termMax'])?data['termMin']:data['termMin']+" - "+data['termMax'])+" <?=GetMessage('IPOLSDEK_JS_SOD_HD_DAY')?>";
                                            arBlocks.choosable = "<input type='button' value='<?=GetMessage('IPOLSDEK_FRNT_CHOOSE')?>' onclick='IPOLSDEK_oExport.allTarifs.select(\""+data.tarif+"\");'>";
                                        } else{
                                            if(data.error){
                                                arBlocks.price = "<span class='errorText'>"+data.error+"</span>";
                                            }
                                        }
                                    }

                                    if(arBlocks.ready){
                                        $('#IPOLSDEK_allTarifs').append("<tr id='IPOLSDEK_tarifsTable_"+data.tarif+"' class='adm-list-table-row'><td class='adm-list-table-cell'>"+arBlocks.name+"</td><td class='adm-list-table-cell' style='text-align:center;'>"+arBlocks.price+"</td><td class='adm-list-table-cell' style='text-align:center;'>"+arBlocks.term+"</td><td class='adm-list-table-cell'>"+arBlocks.choosable+"</td></tr>");
                                    }
                                    IPOLSDEK_oExport.allTarifs.carnage();
                                }
                            });
                            break;
                        }
                    }

                },

                stop: function(){
                    IPOLSDEK_oExport.allTarifs.stopF = true;
                    $('#IPOLSDEK_tarifStopper').css('display','none');
                },

                getFirstTafirType: function(){
                    for(var i in IPOLSDEK_oExport.allTarifs.availTarifs)
                        return i;
                    return false;
                },

                select: function(wat){
                    if(!$('#IPOLSDEK_service option[value="'+wat+'"]').length)
                        $('#IPOLSDEK_service').append('<option value="'+wat+'">'+$('#IPOLSDEK_tarifsTable_'+wat).children(':first').html()+'</option>');
                    $('#IPOLSDEK_service').val(wat);
                    IPOLSDEK_oExport.onCodeChange($('#IPOLSDEK_service'),true);
                    IPOLSDEK_oExport.allTarifs.wnd.Close();
                },

                closer: function(doShow){
                    var handler = $('#IPOLSDEK_allTarifs').closest('.bx-core-adm-dialog').find('.bx-core-adm-icon-close');
                    if(typeof(doShow) === 'undefined')
                        handler.css('visibility','hidden');
                    else
                        handler.css('visibility','visible');
                },

                lang: {
                    <?php foreach(sdekExport::getAllProfiles() as $profile) { ?>
                    '<?=$profile?>' : '<?=GetMessage("IPOLSDEK_DELIV_".strtoupper($profile)."_TITLE")?>',
                    <?php } ?>
                }
            },
            /* Handling packs params */
            gabs:{
                /* button "change" */
                change: function(){
                    /* in sm - CDEK params */
                    var GABS = {
                        D_L: $('#IPOLSDEK_GABS_D_L').val() * 10,
                        D_W: $('#IPOLSDEK_GABS_D_W').val() * 10,
                        D_H: $('#IPOLSDEK_GABS_D_H').val() * 10
                    };
                    var htmlCG  = "<input type='text' class='IPOLSDEK_gabInput' id='IPOLSDEK_GABS_D_L_new' value='"+GABS.D_L+"'> <?=GetMessage("IPOLSDEK_mm")?>&nbsp;x&nbsp;";
                    htmlCG += "<input type='text' class='IPOLSDEK_gabInput' id='IPOLSDEK_GABS_D_W_new' value='"+GABS.D_W+"'> <?=GetMessage("IPOLSDEK_mm")?>&nbsp;x&nbsp;";
                    htmlCG += "<input type='text' class='IPOLSDEK_gabInput' id='IPOLSDEK_GABS_D_H_new' value='"+GABS.D_H+"'> <?=GetMessage("IPOLSDEK_mm")?>,";
                    htmlCG += "<input type='text' style='width:20px' id='IPOLSDEK_GABS_W_new' value='"+$('#IPOLSDEK_GABS_W').val()+"'> <?=GetMessage("IPOLSDEK_kg")?>";
                    htmlCG += " <a href='javascript:void(0)' onclick='IPOLSDEK_oExport.gabs.accept()'>OK</a>";
                    $('#IPOLSDEK_natGabs').css('display','none');
                    $('#IPOLSDEK_gabsPlace').parents('tr').css('display','table-row');
                    $('#IPOLSDEK_gabsPlace').html(htmlCG);
                },
                /* assepting changes via button "change" */
                accept: function(){
                    var ar = ['D_L','D_W','D_H','W'];
                    var GABS = {'mode':'mm'};
                    for(var i in ar){
                        IPOLSDEK_oExport.checkFloat($('#IPOLSDEK_GABS_'+ar[i]+'_new'));
                        GABS[ar[i]] = $('#IPOLSDEK_GABS_'+ar[i]+'_new').val();
                    }

                    IPOLSDEK_oExport.gabs.write(GABS);

                    IPOLSDEK_oExport.onRecheck();
                },
                /* setting changes according to gabs */
                write: function(GABS){
                    if(GABS.mode == 'mm'){
                        var GABSmm = GABS;
                        var GABScm = {
                            'D_L'  : GABS.D_L / 10,
                            'D_W'  : GABS.D_W / 10,
                            'D_H'  : GABS.D_H / 10
                        }
                    }else{
                        var GABSmm =  {
                            'D_L'  : GABS.D_L * 10,
                            'D_W'  : GABS.D_W * 10,
                            'D_H'  : GABS.D_H * 10
                        };
                        var GABScm = GABS;
                    }

                    var htmlCG  = GABSmm.D_L + " <?=GetMessage("IPOLSDEK_mm")?> x " + GABSmm.D_W + " <?=GetMessage("IPOLSDEK_mm")?> x " + GABSmm.D_H + " <?=GetMessage("IPOLSDEK_mm")?>, " + GABS.W + " <?=GetMessage("IPOLSDEK_kg")?> <a href='javascript:void(0)' onclick='IPOLSDEK_oExport.gabs.change()'> <?=GetMessage('IPOLSDEK_STT_CHNG')?></a>";
                    $('#IPOLSDEK_gabsPlace').html(htmlCG);
                    $('#IPOLSDEK_GABS_D_L').val(GABScm.D_L);
                    $('#IPOLSDEK_GABS_D_W').val(GABScm.D_W);
                    $('#IPOLSDEK_GABS_D_H').val(GABScm.D_H);
                    $('#IPOLSDEK_GABS_W').val(GABS.W);
                    $('#IPOLSDEK_gabsPlace').parents('tr').css('display','table-row');
                    $('#IPOLSDEK_VWeightPlace').html((GABScm.D_L*GABScm.D_W*GABScm.D_H) / 5000);
                    IPOLSDEK_oExport.gabs.changeStat = true;
                    IPOLSDEK_oExport.serverShow();
                },
                /* finishing work with gabs */
                onPackHandlerEnd: function(){
                    $('#IPOLSDEK_PLACES').val('');
                    if(IPOLSDEK_packs.saveObj.cnt == 1){
                        var gabs = [1,1,1,1];
                        for(var i in IPOLSDEK_packs.saveObj)
                            if(!isNaN(parseInt(i))){
                                gabs = IPOLSDEK_packs.saveObj[i].gabs.split(' x ');
                                gabs.push(IPOLSDEK_packs.saveObj[i].weight);
                                continue;
                            }

                        IPOLSDEK_oExport.gabs.write({
                            'D_L'  : gabs[0],
                            'D_W'  : gabs[1],
                            'D_H'  : gabs[2],
                            'W'    : gabs[3],
                            'mode' : 'cm'
                        });
                    }else{
                        if(IPOLSDEK_packs.saveObj){
                            delete IPOLSDEK_packs.saveObj.cnt;
                            $('#IPOLSDEK_PLACES').val(JSON.stringify(IPOLSDEK_packs.saveObj));
                        }
                        IPOLSDEK_oExport.serverShow();
                        IPOLSDEK_oExport.onRecheck();
                    }
                },
                /* cheching, what to show when opening and editting */
                changeStat: <?=(sdekHelper::isEqualArrs($naturalGabs,$ordrVals['GABS']) ? "false" : "true")?>,
                label: function(){
                    /* if given labels */
                    if($('#IPOLSDEK_PLACES').val()){
                        $('#IPOLSDEK_gabsPlace').closest('tr').css('display','none');
                        $('#IPOLSDEK_natGabs').css('display','none');
                        $('#IPOLSDEK_PLACES').closest('tr').css('display','');
                    }else{
                        if(IPOLSDEK_oExport.gabs.changeStat){
                            $('#IPOLSDEK_gabsPlace').closest('tr').css('display','table-row');
                            $('#IPOLSDEK_natGabs').css('display','none');
                            $('#IPOLSDEK_PLACES').closest('tr').css('display','none');
                        }else{
                            $('#IPOLSDEK_gabsPlace').closest('tr').css('display','none');
                            $('#IPOLSDEK_natGabs').css('display','inline');
                            $('#IPOLSDEK_PLACES').closest('tr').css('display','none');
                        }
                    }
                }
            },
            <?php if($senderWH) { ?>
            /* cender cities */
            senderWH: {
                wnd: false,
                show: function(){
                    if(!IPOLSDEK_oExport.senderWH.wnd){
                        IPOLSDEK_oExport.senderWH.wnd = new BX.CDialog({
                            title: "<?=GetMessage('IPOLSDEK_JS_SOD_senderWH_HEADER')?>",
                            content: "<div id='IPOLSDEK_senderWH_table'></div>",
                            icon: 'head-block',
                            resizable: true,
                            draggable: true,
                            height: '300',
                            width: '450',
                            buttons: []
                        });
                        $('#IPOLSDEK_senderWH_table').html($('#IPOLSDEK_senderWHcontent').html());
                        $('#IPOLSDEK_senderWHcontent').html('');
                    }
                    IPOLSDEK_oExport.senderWH.wnd.Show();
                },
            },
            <?php } ?>
            /* accounts */
            account : {
                wnd: false,

                change : function(){
                    if(!IPOLSDEK_oExport.account.wnd){
                        IPOLSDEK_oExport.account.wnd = new BX.CDialog({
                            title: "<?=GetMessage('IPOLSDEK_JS_SOD_account_HEADER')?>",
                            content: "<div id='IPOLSDEK_account_table'></div>",
                            icon: 'head-block',
                            resizable: true,
                            draggable: true,
                            height: '300',
                            width: '450',
                            buttons: []
                        });
                    }
                    $('#IPOLSDEK_account_table').html("<img src='/bitrix/images/<?=self::$MODULE_ID?>/ajax.gif'>");
                    IPOLSDEK_oExport.account.wnd.Show();
                    IPOLSDEK_oExport.ajax({
                        data     : {
                            isdek_action : 'getActiveAccounts',
                            COUNTRY      : IPOLSDEK_oExport.country,
                            DELIVERY     : IPOLSDEK_oExport.curDelivery
                        },
                        dataType : 'json',
                        success  :function(data){
                            if(IPOLSDEK_oExport.isEmpty(data))
                                alert('<?=GetMessage('IPOLSDEK_JSC_SOD_noAccounts')?>');
                            else{
                                if(data.success !== 'Y'){
                                    alert(data.error);
                                }else{
                                    var html =  "<table>";
                                    for(var i in data.accounts){
                                        html += "<tr>";
                                        html += "<td><input type='radio' name='IPOLSDEK_changeAcc' value='"+i+"'></td>";
                                        html += "<td class='IPOLSDEK_accName'>"+data.accounts[i].ACCOUNT+((data.accounts[i].LABEL) ? " ("+data.accounts[i].LABEL+")" : "")+"</td>";
                                        html += "<td>";
                                        html += (data.accounts[i].BASIC)    ? "<?=GetMessage('IPOLSDEK_LBL_BASICACCOUNT')?> "    : "";
                                        html += (data.accounts[i].COUNTRY)  ? "<?=GetMessage('IPOLSDEK_LBL_COUNTRYACCOUNT')?> "  : "";
                                        html += (data.accounts[i].DELIVERY) ? "<?=GetMessage('IPOLSDEK_LBL_DELIVERYACCOUNT')?> " : "";
                                        html += "</td>";
                                        html += "</tr>";
                                    }
                                    html += "</table>";
                                    html += "<input type='button' value='OK' onclick='IPOLSDEK_oExport.account.submit()'>";
                                    $('#IPOLSDEK_account_table').html(html);
                                }
                            }
                        },
                    });
                },

                submit : function(){
                    var value = $('[name="IPOLSDEK_changeAcc"]:checked').val();
                    if(typeof(value) !== 'undefined' && value){
                        $('#IPOLSDEK_account').val(value);
                        $('#IPOLSDEK_accountLbl').html($('[name="IPOLSDEK_changeAcc"]:checked').parent().parent().children('.IPOLSDEK_accName').html());
                        IPOLSDEK_oExport.onRecheck();
                    }
                    IPOLSDEK_oExport.account.wnd.Close();
                }
            },
            /* currencies */
            currency:{
                goal: '<?=$cntrCurrency?>',

                getFormat: function(sum,from,to,where){
                    IPOLSDEK_oExport.ajax({
                        data    : {isdek_action:'formatCurrency',SUM:sum,FROM:from,TO:to,WHERE:where,FORMAT:'Y',orderId:IPOLSDEK_oExport.orderId},
                        dataType: 'JSON',
                        success : function(data){
                            $('#'+data.WHERE).html(data.VALUE);
                        }
                    });
                },

                init: function(){
                    $('#IPOLSDEK_toPay').on('change',IPOLSDEK_oExport.currency.onChange);
                    $('#IPOLSDEK_deliveryP').on('change',IPOLSDEK_oExport.currency.onChange);
                },

                onChange: function(e){
                    var val = $(e.currentTarget).val();
                    var id  = $(e.currentTarget).attr('id') + 'Format';
                    $('#'+id).html('');
                    IPOLSDEK_oExport.currency.getFormat(val,0,IPOLSDEK_oExport.currency.goal,id);
                }
            },
            ui: {
                toggleBlock: function (code) {
                    $('.<?=self::$MODULE_LBL?>block_' + code).toggle();
                },
            }
        };

        $(document).ready(IPOLSDEK_oExport.load);
    </script>
    <div style='display:none'>
        <table id='IPOLSDEK_wndOrder'>
            <tr><td><?=GetMessage('IPOLSDEK_JS_SOD_STATUS')?></td><td><?=$status?></td></tr>
            <tr><td colspan='2'><small><?=GetMessage('IPOLSDEK_JS_SOD_STAT_'.$status)?></small><?=$message['number']?></td></tr>
            <?php if($SDEK_ID) { ?><tr><td><?= GetMessage('IPOLSDEK_JS_SOD_SDEK_ID') ?></td><td><?= $SDEK_ID ?></td></tr><?php } ?>
            <?php if($MESS_ID) { ?><tr><td><?= GetMessage('IPOLSDEK_JS_SOD_MESS_ID') ?></td><td><?= $MESS_ID ?></td></tr><?php } ?>
            <?php if($senderWH) { ?><tr><td colspan='2'><a href='javascript:void(0)' onclick='IPOLSDEK_oExport.senderWH.show()'><?= GetMessage('IPOLSDEK_JS_SOD_senderWH_TITLE') ?></a></td></tr>
            <?php } ?>
            <?php // Form ?>
            <tr class='heading'><td colspan='2'><?=GetMessage('IPOLSDEK_JS_SOD_HD_PARAMS')?></td></tr>
            <tr><td><?=GetMessage('IPOLSDEK_JS_SOD_number')?></td><td><?=(self::$orderDescr['info']['ACCOUNT_NUMBER'])?self::$orderDescr['info']['ACCOUNT_NUMBER']:self::$orderId?></td></tr>
            <tr><td><?=GetMessage('IPOLSDEK_JS_SOD_service')?></td><td>
                    <select id='IPOLSDEK_service' onchange='IPOLSDEK_oExport.onCodeChange($(this))'><?=$strOfCodes?></select>
                    <?=$message['service']?>
                </td></tr>
            <tr id='IPOLSDEK_tarifWarning'><td colspan='2'><span><?=GetMessage('IPOLSDEK_JS_SOD_WRONGTARIF')?></span></td></tr>
            <?php // Sender cities ?>
            <tr>
                <td><?=GetMessage('IPOLSDEK_JS_SOD_departure')?></td>
                <td>
                    <?php if (array_key_exists('departure', $ordrVals) && !array_key_exists($ordrVals['departure'], $senderCities)) {
                        $orderDeparture = sqlSdekCity::getBySId($ordrVals['departure']);?>
                        <span style='color:red'><?=$orderDeparture['NAME']?> <?=GetMessage('IPOLSDEK_ERR_SENDERCITYNOTFOUND');?></span><br><br>
                        <?php
                    } ?>
                    <select id='IPOLSDEK_departure' onchange='IPOLSDEK_oExport.onDepartureChange($(this))'>
                    <?php foreach($senderCities as $key => $val) { ?>
                        <option value="<?=$key?>" <?=(array_key_exists('departure', $ordrVals) && $ordrVals['departure'] == $key) ? 'selected' : ''?>><?=$val?></option>
                    <?php } ?>
                    </select>
                </td>
            </tr>
            <?php //Errors ?>
            <?php if($message['troubles']) { ?>
                <tr class='heading'><td colspan='2'><?=GetMessage('IPOLSDEK_JS_SOD_HD_ERRORS')?></td></tr>
                <tr><td colspan='2'><?=$message['troubles']?></td></tr>
            <?php } ?>
            <?php // Autosend messages
            if ($autosendStatus) { ?>
                <tr class='heading'><td colspan='2'><?=GetMessage('IPOLSDEK_JS_SOD_HD_AUTOSEND')?></td></tr>
                <tr><td colspan='2'><?=$autosendStatus?></td></tr>
            <?php } ?>
            <?php
            // Store (From.Location address, Sender and Seller)
            ?>
            <tr class='heading'><td colspan='2'><a href="javascript:void(0)" onclick="IPOLSDEK_oExport.ui.toggleBlock('store')"><?=GetMessage('IPOLSDEK_JS_SOD_HD_STORE')?></a>&nbsp;<a class='PropHint' onclick="return IPOLSDEK_oExport.popup('pop-STORE',this);" href='javascript:void(0)'></a></td></tr>
            <tr class='IPOLSDEK_block_store_warning'><td colspan="2"><?=GetMessage('IPOLSDEK_JS_SOD_MESS_NO_DEFAULT_STORE')?></td></tr>
            <tr class='IPOLSDEK_block_store'><td colspan="2"><?=GetMessage('IPOLSDEK_JS_SOD_MESS_ABOUT_FROM_ADDRESS')?></td></tr>
            <tr class='IPOLSDEK_block_store'><td colspan="2"><hr></td></tr>
            <tr class='IPOLSDEK_block_store'><td><?=GetMessage('IPOLSDEK_JS_SOD_from_loc_street')?></td><td><input id='IPOLSDEK_from_loc_street' type='text' value='<?=$ordrVals['from_loc_street']?>'></td></tr>
            <tr class='IPOLSDEK_block_store'><td><?=GetMessage('IPOLSDEK_JS_SOD_from_loc_house')?></td><td><input id='IPOLSDEK_from_loc_house' type='text' value='<?=$ordrVals['from_loc_house']?>'></td></tr>
            <tr class='IPOLSDEK_block_store'><td><?=GetMessage('IPOLSDEK_JS_SOD_from_loc_flat')?></td><td><input id='IPOLSDEK_from_loc_flat' type='text' value='<?=$ordrVals['from_loc_flat']?>'></td></tr>
            <tr class='IPOLSDEK_block_store'><td colspan="2"><hr></td></tr>
            <tr class='IPOLSDEK_block_store'><td colspan="2"><?=GetMessage('IPOLSDEK_JS_SOD_MESS_ABOUT_SENDER')?></td></tr>
            <tr class='IPOLSDEK_block_store'><td colspan="2"><hr></td></tr>
            <tr class='IPOLSDEK_block_store'><td><?=GetMessage('IPOLSDEK_JS_SOD_sender_company')?></td><td><input id='IPOLSDEK_sender_company' type='text' value='<?=$ordrVals['sender_company']?>'></td></tr>
            <tr class='IPOLSDEK_block_store'><td><?=GetMessage('IPOLSDEK_JS_SOD_sender_name')?></td><td><input id='IPOLSDEK_sender_name' type='text' value='<?=$ordrVals['sender_name']?>'></td></tr>
            <tr class='IPOLSDEK_block_store'><td><?=GetMessage('IPOLSDEK_JS_SOD_sender_phone')?> <a href='#' class='PropHint' onclick='return IPOLSDEK_oExport.popup("pop-storePhone",this);'></a></td><td><input id='IPOLSDEK_sender_phone' type='text' value='<?=$ordrVals['sender_phone']?>'></td></tr>
            <tr class='IPOLSDEK_block_store'><td><?=GetMessage('IPOLSDEK_JS_SOD_sender_phone_add')?></td><td><input id='IPOLSDEK_sender_phone_add' type='text' value='<?=$ordrVals['sender_phone_add']?>'></td></tr>
            <tr class='IPOLSDEK_block_store'><td colspan="2"><hr></td></tr>
            <tr class='IPOLSDEK_block_store'><td colspan="2"><?=GetMessage('IPOLSDEK_JS_SOD_MESS_ABOUT_SELLER')?></td></tr>
            <tr class='IPOLSDEK_block_store'><td colspan="2"><hr></td></tr>
            <tr class='IPOLSDEK_block_store'><td><?=GetMessage('IPOLSDEK_JS_SOD_seller_name')?></td><td><input id='IPOLSDEK_seller_name' type='text' value='<?=$ordrVals['seller_name']?>'></td></tr>
            <tr class='IPOLSDEK_block_store'><td><?=GetMessage('IPOLSDEK_JS_SOD_seller_phone')?> <a href='#' class='PropHint' onclick='return IPOLSDEK_oExport.popup("pop-storePhone",this);'></td><td><input id='IPOLSDEK_seller_phone' type='text' value='<?=$ordrVals['seller_phone']?>'></td></tr>
            <tr class='IPOLSDEK_block_store'><td><?=GetMessage('IPOLSDEK_JS_SOD_seller_address')?></td><td><input id='IPOLSDEK_seller_address' type='text' value='<?=$ordrVals['seller_address']?>'></td></tr>
            <?php //Address ?>
            <tr class='heading'><td colspan='2'><?=GetMessage('IPOLSDEK_JS_SOD_HD_ADDRESS')?></td></tr>
            <tr>
                <td>
                    <?=GetMessage('IPOLSDEK_JS_SOD_location')?>
                    <?=$multiCity?>
                </td>
                <td>
                    <?=($multiCityS)?$multiCityS:$cityName?>
                    <input id='IPOLSDEK_location' type='hidden' value="<?=$ordrVals['location']?>"><?=$message['location']?>
                    <input id='IPOLSDEK_cityTo' type='hidden' value="<?=$orderCity['BITRIX_ID']?>">
                </td>
            </tr>

            <?php if (\Ipolh\SDEK\abstractGeneral::isNewApp()): ?>
                <tr class='IPOLSDEK_notSV'>
                    <td><?= GetMessage('IPOLSDEK_JS_SOD_line') ?></td>
                    <td>
                        <textarea cols="30" rows="3" id="IPOLSDEK_address"><?= $ordrVals['address'] ?: $ordrVals['street'] . ', ' . $ordrVals['house'] . ', ' . $ordrVals['flat'] ?></textarea>
                    </td>
                </tr>
            <?php else: ?>
                <tr class='IPOLSDEK_notSV'>
                    <td><?= GetMessage('IPOLSDEK_JS_SOD_street') ?></td>
                    <td>
                        <?php if ($ordrVals['street']): ?>
                            <input id="IPOLSDEK_street" type="text" value="<?= $ordrVals['street'] ?>">
                        <?php else: ?>
                            <textarea id="IPOLSDEK_street"><?= $ordrVals['address'] ?></textarea>
                        <?php endif; ?>
                        <?= $message['street'] ?>
                    </td>
                </tr>
                <tr class='IPOLSDEK_notSV'>
                    <td><?= GetMessage('IPOLSDEK_JS_SOD_house') ?></td>
                    <td>
                        <input id="IPOLSDEK_house" type="text" value="<?= (self::$locStreet && $ordrVals['address'] && !$ordrVals['house']) ? $ordrVals['address'] : $ordrVals['house'] ?>">
                        <?= $message['house'] ?>
                    </td>
                </tr>
                <tr class='IPOLSDEK_notSV'>
                    <td><?= GetMessage('IPOLSDEK_JS_SOD_flat') ?></td>
                    <td>
                        <input id='IPOLSDEK_flat' type='text' value="<?= $ordrVals['flat'] ?>">
                        <?= $message['flat'] ?>
                    </td>
                </tr>
            <?php endif; ?>
            <?php if(!IsModuleInstalled('ipol.kladr')){?>
                <tr class='IPOLSDEK_notSV'><td colspan="2" style="font-size: smaller;"><?=GetMessage('IPOLSDEK_JS_SOD_KLADR')?></td></tr>
            <?php }?>

            <tr class='IPOLSDEK_SV'><td><?=GetMessage('IPOLSDEK_JS_SOD_PVZ')?>
                    <?php if($strOfPSV) { ?>&nbsp;<span id="IPOLSDEK_searchPVZ" onclick="IPOLSDEK_oExport.searchPVZ.on('PVZ');"></span><span id="IPOLSDEK_noSearchPVZ" onclick="IPOLSDEK_oExport.searchPVZ.off('PVZ');"></span><?php } ?></td>
                <td>
                    <?php if($strOfPSV) { ?>
                        <span id="IPOLSDEK_searchPVZPlace"><?=GetMessage('IPOLSDEK_JS_SOD_SEARCHPVZ')?>:&nbsp;<input type="text" id="IPOLSDEK_searchPVZInput" onkeyup="IPOLSDEK_oExport.searchPVZ.search('PVZ')"></span>
                        <select id='IPOLSDEK_PVZ' onchange='IPOLSDEK_oExport.onPVZChange($(this))'><?=$strOfPSV?></select>
                    <?php }
                    else { ?><span id='IPOLSDEK_deliveryPoint_noSV'><?=GetMessage('IPOLSDEK_JS_SOD_NOSVREG')?></span><?php } ?>
                    <?= $message['deliveryPoint'] ?>
                </td>
            </tr>
            <tr class='IPOLSDEK_SV'><td colspan='2'><span id='IPOLSDEK_badPVZ' style='display:none'><?=GetMessage('IPOLSDEK_JS_SOD_BADPVZ')?></span></td></tr>
            <tr class='IPOLSDEK_PST'><td><?= GetMessage('IPOLSDEK_JS_SOD_POSTAMAT') ?>
                    <?php if($strOfPST) { ?>&nbsp;<span id="IPOLSDEK_searchPST" onclick="IPOLSDEK_oExport.searchPVZ.on('PST');"></span><span id="IPOLSDEK_noSearchPST" onclick="IPOLSDEK_oExport.searchPVZ.off('PST');"></span><?php } ?></td>
                <td>
                    <?php if($strOfPST) { ?>
                        <span id="IPOLSDEK_searchPSTPlace"><?=GetMessage('IPOLSDEK_JS_SOD_SEARCHPST')?>:&nbsp;<input type="text" id="IPOLSDEK_searchPSTInput" onkeyup="IPOLSDEK_oExport.searchPVZ.search('PST')"></span>
                        <select id='IPOLSDEK_PST' onchange='IPOLSDEK_oExport.onPSTChange($(this))'><?=$strOfPST?></select>
                    <?php }
                    else { ?><span id='IPOLSDEK_deliveryPoint_noPST'><?=GetMessage('IPOLSDEK_JS_SOD_NOPSTMTREG')?></span><?php } ?>
                    <?= $message['deliveryPoint'] ?>
                </td>
            </tr>
            <tr class='IPOLSDEK_PST'><td colspan='2'><span id='IPOLSDEK_badPST' style='display:none'><?=GetMessage('IPOLSDEK_JS_SOD_BADPST')?></span></td></tr>
            <?php // Reciver ?>
            <tr class='heading'><td colspan='2'><?=GetMessage('IPOLSDEK_JS_SOD_HD_RESIEVER')?></td></tr>
            <?php if(\Ipolh\SDEK\option::get('addData') == 'Y') { ?>
                <tr>
                    <td><?=GetMessage('IPOLSDEK_JS_SOD_deliveryDate')?></td>
                    <td>
                        <div class="adm-input-wrap adm-input-wrap-calendar">
                            <input class="adm-input adm-input-calendar" disabled id='IPOLSDEK_deliveryDate' disabled type="text" name="IPOLSDEK_deliveryDate" style='width:148px;' value="<?=$ordrVals['deliveryDate']?>">
                            <span class="adm-calendar-icon" style='right:0px'onclick="BX.calendar({node:this, field:'IPOLSDEK_deliveryDate', form: '', bTime: false, bHideTime: true,callback_after: IPOLSDEK_oExport.onDeliveryDateChange});"></span>
                            &nbsp;&nbsp;<span id="IPOLSDEK_killDeliveryTerm" onclick='IPOLSDEK_oExport.resetDate();'></span>
                        </div>

                        <?=$message['Schedule']?></td>
                </tr>
                <tr id='IPOLSDEK_badDeliveryTerm'><td colspan='2'><small><?=GetMessage('IPOLSDEK_JS_SOD_badDeliveryDate')?><span id='IPOLSDEK_deliveryTerm'></span>&nbsp;<?=GetMessage('IPOLSDEK_JS_SOD_HD_DAY')?></small></td></tr>
            <?php } ?>
            <tr><td><?=GetMessage('IPOLSDEK_JS_SOD_name')?></td><td><input id='IPOLSDEK_name' type='text' value="<?=str_replace(array('"','<','>','\''), ' ', $ordrVals['name'])?>"><?=$message['name']?></td></tr>
            <tr><td valign="top"><?=GetMessage('IPOLSDEK_JS_SOD_phone')?></td><td><input id='IPOLSDEK_phone' type='text' value="<?=$ordrVals['phone']?>"></td></tr>
            <?php if(array_key_exists('oldPhone', $ordrVals) && str_replace(' ', '', $ordrVals['oldPhone']) != $ordrVals['phone']) { ?>
                <tr><td valign="top"><?=GetMessage('IPOLSDEK_JS_SOD_oldPhone')?></td><td><?=$ordrVals['oldPhone']?></td></tr>
            <?php } ?>
            <tr><td valign="top"><?=GetMessage('IPOLSDEK_JS_SOD_email')?></td><td><input id='IPOLSDEK_email' type='text' value="<?=$ordrVals['email']?>"></td></tr>
            <tr><td><?=GetMessage('IPOLSDEK_JS_SOD_comment')?></td><td><textarea id='IPOLSDEK_comment'><?=$ordrVals['comment']?></textarea><?=$message['comment']?></td></tr>
            <?php /*<tr><td><?=GetMessage('IPOLSDEK_JS_SOD_reccompany')?></td><td><input id='IPOLSDEK_reccompany'><?=$ordrVals['reccompany']?></input><?=$message['reccompany']?></td></tr>*/?>
            <tr><td colspan='2'>
                    <?php foreach(array('STORE', 'storePhone', 'GABARITES', 'minVats') as $hintCode) { ?>
                        <div id="pop-<?=$hintCode?>" class="b-popup" >
                            <div class="pop-text"><?=GetMessage("IPOLSDEK_JSC_SOD_HELPER_$hintCode")?></div>
                            <div class="close" onclick="$(this).closest('.b-popup').hide();"></div>
                        </div>
                    <?php } ?>
                </td></tr>
            <?php // Payment ?>
            <tr class='heading'><td colspan='2'><?= GetMessage('IPOLSDEK_JS_SOD_HD_PAYMENT') ?></td></tr>
            <tr><td><?= GetMessage('IPOLSDEK_JS_SOD_isBeznal') ?></td><td>
                    <input type='checkbox' id='IPOLSDEK_isBeznal' value='Y' <?=($ordrVals['isBeznal']=='Y')?'checked':''?> onchange='IPOLSDEK_oExport.checkPay()'>
                    &nbsp;&nbsp;<span id='IPOLSDEK_notPayed' style='color:red;display:none'><?=GetMessage('IPOLSDEK_JS_SOD_NOTPAYED')?></span>
            </td></tr>
            <tr><td></td><td>
                <input type='hidden' id='IPOLSDEK_isCODavailable' value='<?=($isCODavailable ? 'Y' : 'N')?>'>
                <?php if (!$isCODavailable) {
                    echo (!$payment ? GetMessage('IPOLSDEK_JS_SOD_NONALPAY') : str_replace("#VALUE#", $payment, GetMessage('IPOLSDEK_JS_SOD_TOOMANY')));
                }?>
            </td></tr>
            <?php if(self::$orderDescr['info']['SUM_PAID'] > 0) { ?>
                <tr><td><?=GetMessage('IPOLSDEK_JS_SOD_paid')?></td><td><?=self::$orderDescr['info']['SUM_PAID']?> <?=GetMessage('IPOLSDEK_JSC_SOD_RUB')?></td></tr>
            <?php } ?>
            <tr><td><?=GetMessage('IPOLSDEK_JS_SOD_toPay')?></td><td>
                    <input type='text' id='IPOLSDEK_toPay' value="<?=$ordrVals['toPay']?>" size='10' style='text-align: right' onchange='IPOLSDEK_oExport.checkFloat($(this))'>&nbsp;<?=GetMessage('IPOLSDEK_JSC_SOD_RUB')?>
                    <?php if($cntrCurrency) { ?>
                        &nbsp;&nbsp;&nbsp;<span id='IPOLSDEK_toPayFormat'><?=self::formatCurrency(array('SUM'=>$ordrVals['toPay'],'TO'=>$cntrCurrency,'FORMAT'=>'Y','orderId'=>$orderId))?></span>
                    <?php } ?>
                </td></tr>
            <tr>
                <td><?=GetMessage('IPOLSDEK_JS_SOD_NDSGoods')?></td>
                <td>
                    <select id='IPOLSDEK_NDSGoods'>
                        <?php foreach(array('VATX', 'VAT0', 'VAT05', 'VAT07', 'VAT10', 'VAT12', 'VAT18', 'VAT20') as $ndsVats) { ?>
                            <option value='<?=$ndsVats?>' <?=($ordrVals['NDSGoods'] == $ndsVats) ? 'selected' : ''?>><?=GetMessage('IPOLSDEK_NDS_'.$ndsVats)?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <tr><td><?=GetMessage('IPOLSDEK_JS_SOD_deliveryP')?></td><td>
                    <input type='text' id='IPOLSDEK_deliveryP' value="<?=$ordrVals['deliveryP']?>" size='10' style='text-align: right' onchange='IPOLSDEK_oExport.checkFloat($(this))'>&nbsp;<?=GetMessage('IPOLSDEK_JSC_SOD_RUB')?>
                    <?php if($cntrCurrency) { ?>
                        &nbsp;&nbsp;&nbsp;<span id='IPOLSDEK_deliveryPFormat'><?=self::formatCurrency(array('SUM'=>$ordrVals['deliveryP'],'TO'=>$cntrCurrency,'FORMAT'=>'Y','orderId'=>$orderId))?></span>
                    <?php } ?>
                </td></tr>
            <tr>
                <td><?= GetMessage('IPOLSDEK_JS_SOD_NDSDelivery') ?></td>
                <td>
                    <select id='IPOLSDEK_NDSDelivery'>
                        <?php foreach(array('VATX', 'VAT0', 'VAT05', 'VAT07', 'VAT10', 'VAT12', 'VAT18', 'VAT20') as $ndsVats) { ?>
                            <option value='<?= $ndsVats ?>' <?= ($ordrVals['NDSDelivery'] == $ndsVats) ? 'selected' : '' ?>><?= GetMessage('IPOLSDEK_NDS_' . $ndsVats) ?></option>
                        <?php } ?>
                    </select>
                </td>
            </tr>
            <?php if(\Ipolh\SDEK\option::get('noVats') === 'N') { ?>
                <tr>
                    <td><?=GetMessage('IPOLSDEK_JS_SOD_minVats')?>  <a href='#' class='PropHint' onclick='return IPOLSDEK_oExport.popup("pop-minVats",this);'></a></td>
                    <td><input type='checkbox' id='IPOLSDEK_minVats' value="Y"></td>
                </tr>
            <?php } ?>
            <?php // additional servises ?>
            <tr class='heading'><td colspan='2'><?=GetMessage('IPOLSDEK_AS')?></td></tr>
            <?php foreach($exOpts as $id => $option)
                if($option['SHOW']=="Y" || $option['DEF']=="Y") {
                    ?>
                    <tr><td><?=GetMessage("IPOLSDEK_AS_".$id."_NAME")?></td><td><input id='IPOLSDEK_AS_<?=$id?>' <?=($option['DEF']=="Y")?"checked":""?> type='checkbox' value='<?=$id?>'></td></tr>
                <?php } ?>

            <?php // about the order?>
            <tr class='heading'><td colspan='2'><a onclick='IPOLSDEK_oExport.serverShow()' href='javascript:void(0)'><?=GetMessage('IPOLSDEK_JS_SOD_ABOUT')?></td></tr>
            <?php // Gabarites defauls?>
            <tr class='IPOLSDEK_detOrder' style='display:none'>
                <td><?=GetMessage('IPOLSDEK_JS_SOD_GABARITES')?> <a href='#' class='PropHint' onclick='return IPOLSDEK_oExport.popup("pop-GABARITES",this);'></a></td>
                <td>
                    <?=($naturalGabs['D_L'])*10?><?=GetMessage("IPOLSDEK_mm")?> x <?=($naturalGabs['D_W'])*10?><?=GetMessage("IPOLSDEK_mm")?> x <?=($naturalGabs['D_H'])*10?><?=GetMessage("IPOLSDEK_mm")?>, <?=$naturalGabs['W']?><?=GetMessage("IPOLSDEK_kg")?>
                    <?php if(!self::$isLoaded || $status == 'NEW' || $status == 'ERROR') { ?>
                        <a <?=(sdekHelper::isEqualArrs($naturalGabs,$ordrVals['GABS'])?"":"style='display:none'")?> href='javascript:void(0)' id='IPOLSDEK_natGabs' onclick='IPOLSDEK_oExport.gabs.change()'><?=GetMessage('IPOLSDEK_STT_CHNG')?></a>
                    <?php } ?>
                    <input id='IPOLSDEK_GABS_D_L' type='hidden' value="<?=$ordrVals['GABS']['D_L']?>">
                    <input id='IPOLSDEK_GABS_D_W' type='hidden' value="<?=$ordrVals['GABS']['D_W']?>">
                    <input id='IPOLSDEK_GABS_D_H' type='hidden' value="<?=$ordrVals['GABS']['D_H']?>">
                    <input id='IPOLSDEK_GABS_W'   type='hidden' value="<?=$ordrVals['GABS']['W']?>">
                </td>
            </tr>
            <?php // Gabarites given?>
            <tr class='IPOLSDEK_detOrder' style='display:none'>
                <td><?=GetMessage('IPOLSDEK_JS_SOD_CGABARITES')?></td>
                <td>
                    <div id='IPOLSDEK_gabsPlace'>
                        <?=($ordrVals['GABS']['D_L'])*10?><?=GetMessage("IPOLSDEK_mm")?> x <?=($ordrVals['GABS']['D_W'])*10?><?=GetMessage("IPOLSDEK_mm")?> x <?=($ordrVals['GABS']['D_H'])*10?><?=GetMessage("IPOLSDEK_mm")?>, <?=$ordrVals['GABS']['W']?><?=GetMessage("IPOLSDEK_kg")?>
                        <?php if(!self::$isLoaded || $status == 'NEW' || $status == 'ERROR') { ?>
                            <a href='javascript:void(0)' onclick='IPOLSDEK_oExport.gabs.change()'><?=GetMessage('IPOLSDEK_STT_CHNG')?></a>
                        <?php } ?>
                    </div>
                </td>
            </tr>
            <?php // Gabarites result ?>
            <tr class='IPOLSDEK_detOrder' style='display:none'>
                <td colspan="2" style='text-align:center'><?=GetMessage('IPOLSDEK_JS_SOD_PACKS_GIVEN')?><input type='hidden' id='IPOLSDEK_PLACES' value='<?=(array_key_exists('packs',$ordrVals) && is_array($ordrVals['packs'])) ? json_encode($ordrVals['packs']) : false?>'></td>
            </tr>
            <tr class='IPOLSDEK_detOrder' style='display:none'>
                <td><?=GetMessage('IPOLSDEK_JS_SOD_VWEIGHT')?></td>
                <td>
                    <span id='IPOLSDEK_VWeightPlace'><?=self::getVolumeWeight($ordrVals['GABS']['D_L']*10,$ordrVals['GABS']['D_W']*10,$ordrVals['GABS']['D_H']*10)?></span><?=GetMessage("IPOLSDEK_kg")?>
                </td>
            </tr>
            <tr class='IPOLSDEK_detOrder' style='display:none'>
                <td><?=GetMessage('IPOLSDEK_JS_SOD_SDELPRICE')?></td>
                <td><?=self::$orderDescr['info']['PRICE_DELIVERY']?></td>
            </tr>
            <tr class='IPOLSDEK_detOrder' style='display:none'>
                <td><?=GetMessage('IPOLSDEK_JS_SOD_NDELPRICE')?></td>
                <td id='IPOLSDEK_newPrDel'></td>
            </tr>
            <?php // Account ?>
            <tr class='IPOLSDEK_detOrder' style='display:none'>
                <td><?=GetMessage('IPOLSDEK_JSC_SOD_ACCOUNT')?></td>
                <td>
                    <span id="IPOLSDEK_accountLbl"><?=($acc['LABEL'])?$acc['LABEL']:$acc['ACCOUNT']?></span>
                    <input type='hidden' id='IPOLSDEK_currency' value='<?=$cntrCurrency?>'>
                    <input type='hidden' id='IPOLSDEK_account' value='<?=$acc['ID']?>'>
                    &nbsp;&nbsp;
                    <a href="javascript:void(0)" onclick="IPOLSDEK_oExport.account.change()"><?=GetMessage("IPOLSDEK_STT_CHNG")?></a>
                </td>
            </tr>
        </table>
    </div>
<?php if($senderWH) { ?>
    <div id='IPOLSDEK_senderWHcontent' style='display:none'>
        <table id='IPOLSDEK_senderWH'>
            <tr><td colspan='3'><small><?=GetMessage('IPOLSDEK_JS_SOD_senderWH_HINT')?></small></td></tr>
            <?php
            foreach($senderWH as $ind => $descr){
                $sender = sqlSdekCity::getBySId($descr[0]);
                ?>
                <tr><th><?=$sender['NAME']?></th><th><?=$sender['REGION']?></th><th><?=$descr[1]?></th></tr>
                <?php
                if(array_key_exists($ind,sdekShipmentCollection::$shipments))
                    foreach(sdekShipmentCollection::$shipments[$ind]->goods as $goodCol){?>
                        <tr><td colspan='2'><?=$goodCol['NAME']?> (ID:<?=$goodCol['PRODUCT_ID']?>)</td><td><?=$goodCol['QUANTITY']?></td></tr>
                    <?php }
            }
            ?>
        </table>
    </div>
<?php } ?>