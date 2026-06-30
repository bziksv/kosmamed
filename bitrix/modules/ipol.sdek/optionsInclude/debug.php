<?php
	Ipolh\SDEK\Bitrix\Tools::placeWarningLabel(
		'<a href="/bitrix/js/'.$module_id.'/log.php" target="_blank">'.GetMessage('IPOLSDEK_LABEL_openLog').'</a>',
		(Ipolh\SDEK\Bitrix\Admin\Logger::getLog()) ? GetMessage('IPOLSDEK_LABEL_haslog') : GetMessage('IPOLSDEK_LABEL_nolog')
	);
?>

<?php
if(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".$module_id."/errorLog.txt")){
	$errorStr=Ipolh\SDEK\Bitrix\Tools::getLogContentMindingSize($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".$module_id."/errorLog.txt");
	if(strlen($errorStr)>0){
		Ipolh\SDEK\Bitrix\Tools::placeErrorLabel(GetMessage('IPOLSDEK_FNDD_ERR_TITLE'),GetMessage('IPOLSDEK_FNDD_ERR_HEADER'));
	}
}

if(file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".$module_id."/hint.txt")){
	$updateStr=Ipolh\SDEK\Bitrix\Tools::getLogContentMindingSize($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".$module_id."/hint.txt");
	if(strlen($updateStr)>0){
		Ipolh\SDEK\Bitrix\Tools::placeWarningLabel($updateStr,"<div class='IPOLSDEK_clz' onclick='IPOLSDEK_setups.base.clrUpdt()'></div>",300);
	}
}
?>

<?php
foreach(array("debug_widget","startLogging","debug_fileMode","debug_calculation","debug_turnOffWidget","noagent","disabledagent","lateagent") as $code)
	sdekOption::placeHint($code);
?>

<script>
    IPOLSDEK_setups.debug = {
        restorePVZ : function () {
            if(confirm('<?=GetMessage('IPOLSDEK_LBL_RESTOREPVZ')?>')){
                $('#SDEK_pvzRestore').attr('disabled','disabled');
                IPOLSDEK_setups.ajax({
                    data : {
                        isdek_action : 'restorePVZ'
                    },
                    dataType : 'JSON',
                    success  :function (data) {
                        if(data.SUCCESS){
                            alert('<?=GetMessage('IPOLSDEK_LBL_RESTORED')?>');
                        } else {
                            alert('<?=GetMessage('IPOLSDEK_LBL_UNRESTORED')?> '+data.ERROR);
                        }
                    }
                });
            }
        }
    };
</script>


<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_HDR_logging")?></td></tr>
<tr><td style="color:#555;" colspan="2">
	<?php sdekOption::placeFAQ('LOGGING') ?>
</td></tr>
<?php ShowParamsHTMLByArray($arAllOptions["debug"]); ?>
<tr class="subHeading"><td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_HDR_loggingEvents")?></td></tr>
<?php ShowParamsHTMLByArray($arAllOptions["debug_events"]); ?>

<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_HDR_events")?></td></tr>
<tr><td style="color:#555;" colspan="2">
	<?php sdekOption::placeFAQ('EVENTS'); ?>
</td></tr>
<?php
$arEvents = array(
	'onCompabilityBefore' => getMessage('IPOLSDEK_LABEL_onCompabilityBefore'),
	'onCalculate' => getMessage('IPOLSDEK_LABEL_onCalculate'),
	'onTarifPriority' => getMessage('IPOLSDEK_LABEL_onTarifPriority'),
	'onBeforeDimensionsCount' => getMessage('IPOLSDEK_LABEL_onBeforeDimensionsCount'),
	'onCalculatePriceDelivery' => getMessage('IPOLSDEK_LABEL_onCalculatePriceDelivery'),
	'onBeforeShipment' => getMessage('IPOLSDEK_LABEL_onBeforeShipment'),
	'onGoodsToRequest' => getMessage('IPOLSDEK_LABEL_onGoodsToRequest'),
	'requestSended' => getMessage('IPOLSDEK_LABEL_requestSended'),
	'onParseAddress' => getMessage('IPOLSDEK_LABEL_onParseAddress'),
	'onNewStatus'    => getMessage('IPOLSDEK_LABEL_onNewStatus'),
	'onFormation' => getMessage('IPOLSDEK_LABEL_onFormation'),
	'onTabsBuild' => getMessage('IPOLSDEK_LABEL_onTabsBuild'),
	
);

foreach($arEvents as $code => $name){
	$arSubscribe = array();
	foreach(GetModuleEvents($module_id,$code,true) as $arEvent){
		$arSubscribe []= $arEvent['TO_NAME'];
	}
	if(!empty($arSubscribe)){
		?>
		<tr class="subHeading"><td colspan="2" valign="top" align="center"><?=$name?></td></tr>
      <?php
		foreach($arSubscribe as $path){?>
			<tr><td colspan='2'><?=$path?></td></tr>
		<?php }
	}
}
	
?>

<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_HDR_defines")?></td></tr>
<tr><td style="color:#555;" colspan="2">
    <?php sdekOption::placeFAQ('CONSTANTS'); ?>
</td></tr>

<?php
    $arConstants = array(
        'IPOLSDEK_CACHE_TIME'    => GetMessage('IPOLSDEK_LABEL_CACHE_TIME'),
        'IPOLSDEK_NOCACHE'       => GetMessage('IPOLSDEK_LABEL_NOCACHE'),
        'IPOLSDEK_DOWNCOMPLECTS' => GetMessage('IPOLSDEK_LABEL_DOWNCOMPLECTS'),
        'IPOLSDEK_IGNORECANBUY'  => GetMessage('IPOLSDEK_LABEL_IGNORECANBUY'),
        'IPOLSDEK_BASIC_URL'     => GetMessage('IPOLSDEK_LABEL_BASIC_URL'),
        'IPOLSDEK_CALCULATE_URL' => GetMessage('IPOLSDEK_LABEL_CALCULATE_URL'),
    );

    foreach($arConstants as $constant => $sign){
        if(defined($constant)){
            $constantSign = constant($constant);
            if(is_bool($constantSign)){
                $constantSign = ($constantSign) ? GetMessage('IPOLSDEK_LABEL_constantOn') : GetMessage('IPOLSDEK_LABEL_constantOff');
            }
            ?>
<tr>
    <td><?=$sign?></td><td><?=$constantSign?></td>
</tr>
            <?php
        }
    }
?>

<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_HDR_agents")?></td></tr>
<tr><td style="color:#555;" colspan="2">
        <?php sdekOption::placeFAQ('AGENTS') ?>
</td></tr>
<?php
    $agents = \Ipolh\SDEK\AgentHandler::getAgentList();
    foreach ($agents as $agentName => $agentDescr){
        $agent = CAgent::GetList(array(),array('NAME'=>\Ipolh\SDEK\AgentHandler::getAgentPath().'::'.$agentDescr[0].'();'))->Fetch();
    ?>
<tr>
    <td><?=$agentName?></td>
    <td>
        <?=$agentDescr[0]?>&nbsp;
        <?php
            switch(true){
                case (!is_array($agent)): echo \Ipolh\SDEK\Bitrix\Tools::getMessage('AGENT_NO_AGENT'); break;
                case ($agent['ACTIVE'] !== 'Y'): echo \Ipolh\SDEK\Bitrix\Tools::getMessage('AGENT_DISABLED_AGENT'); break;
                case (time() -strtotime($agent['LAST_EXEC']) > 90000): echo \Ipolh\SDEK\Bitrix\Tools::getMessage('AGENT_LATE_AGENT'); break;
                default: echo \Ipolh\SDEK\Bitrix\Tools::getMessage('AGENT_OK_AGENT'); break;
            }
        ?>
    </td>
</tr>
    <?php }
?>

<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_HDR_pvzRestore")?></td></tr>
<tr><td style="color:#555;" colspan="2">
    <?php sdekOption::placeFAQ('PVZRESTORE') ?>
</td></tr>
<tr><td style="color:#555;" colspan="2"><br></td></tr>
<tr><td colspan="2"><input type="button" id="SDEK_pvzRestore" onclick="IPOLSDEK_setups.debug.restorePVZ()" value="<?=GetMessage('IPOLSDEK_LBL_RESTOREPVZBTN')?>"/></td></tr>

