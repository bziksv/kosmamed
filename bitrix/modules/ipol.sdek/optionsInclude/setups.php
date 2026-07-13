<?php
// Paysystems
$PayDefault = \Ipolh\SDEK\option::get('paySystems');

$paySysS=CSalePaySystem::GetList(array(),array('ACTIVE'=>'Y'));
$paySysHtml='<select name="paySystems[]" multiple size="5">';
while($paySys=$paySysS->Fetch()){
	$paySysHtml.='<option value="'.$paySys['ID'].'" ';
	if(!is_array($PayDefault) && !$PayDefault) {
		$name = strtolower($paySys['NAME']);
		if( strpos($name, GetMessage('IPOLSDEK_cashe')) === false && 
			strpos($name, GetMessage('IPOLSDEK_cashe2')) === false && 
			strpos($name, GetMessage('IPOLSDEK_cashe3')) === false)
			$paySysHtml.='selected';
	}
	else {
		if(in_array($paySys['ID'],$PayDefault))
			$paySysHtml.='selected';
	}
	$paySysHtml.='>'.$paySys['NAME'].'</option>';
}
$paySysHtml.="</select>";
?>
<link href="/bitrix/js/<?=$module_id?>/jquery-ui.css?<?= time() ?>" type="text/css" rel="stylesheet" />
<link href="/bitrix/js/<?=$module_id?>/jquery-ui.structure.css?<?= time() ?>" type="text/css" rel="stylesheet" />
<script src='/bitrix/js/<?=$module_id?>/jquery-ui.js?<?= time() ?>' type='text/javascript'></script>
<style>
	.PropHint { 
		background: url("/bitrix/images/ipol.sdek/hint.gif") no-repeat transparent;
		display: inline-block;
		height: 12px;
		position: relative;
		width: 12px;
	}
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
		z-index: 50;
	}
	.b-popup .pop-text { 
		margin-bottom: 10px;
		color:#000;
	}
	.pop-text i {color:#AC12B1;}
	.b-popup .close { 
		background: url("/bitrix/images/ipol.sdek/popup_close.gif") no-repeat transparent;
		cursor: pointer;
		height: 10px;
		position: absolute;
		right: 4px;
		top: 4px;
		width: 10px;
	}
	.IPOLSDEK_clz{
		background:url(/bitrix/panel/main/images/bx-admin-sprite-small.png) 0px -2989px no-repeat; 
		width:18px; 
		height:18px;
		cursor: pointer;
		margin-left:100%;
	}
	.IPOLSDEK_clz:hover{
		background-position: -0px -3016px;
	}
	.errorText{
		color:red;
		font-size:11px;
	}
	.IPOLSDEK_errorInput{
		background-color: #ffb4b4 !important;
	}
	.subHeading td{
		padding: 8px 70px 10px !important;
		background-color: #EDF7F9;
		border-top: 11px solid #F5F9F9;
		border-bottom: 11px solid #F5F9F9;
		color: #4B6267;
		font-size: 14px;
		font-weight: bold;
		text-align: center !important;
		text-shadow: 0px 1px #FFF;
	}
	.IPOLSDEK_sepTable{
		width: 50%;
		float: left;
		text-align: center;
		font-weight: bold;
	}

	#IPOLSDEK_accountWnd #IPOLSDEK_addAcc{
		display:none;
	}
	#IPOLSDEK_accountWnd.IPOLSDEK_addAcc #IPOLSDEK_addAcc{
		display:table;
	}
	#IPOLSDEK_accountWnd #IPOLSDEK_newAcc{
		display:inline;
	}
	#IPOLSDEK_accountWnd.IPOLSDEK_addAcc #IPOLSDEK_newAcc{
		display:none;
	}
	#IPOLSDEK_accountTable{
		margin:auto;
	}
	#IPOLSDEK_accountTable td {
        padding: 0 5px 2px;
		text-align: center;
	}
    #IPOLSDEK_accountTable tr:last-child td {
        border-bottom: 1px dashed #BCC2C4;
    }
    #IPOLSDEK_accountTable th {
        font-weight: bold;
        padding: 10px;
        text-align: center;
        border-top: 1px dashed #BCC2C4;
        border-bottom: 1px dashed #BCC2C4;
    }
    #IPOLSDEK_accountTable a.ipol_header {
        font-size:       16px;
        cursor:          pointer;
        display:         block;
        margin-bottom:   10px;
        color:           #2E569C;
        text-decoration: underline;
    }
    #IPOLSDEK_accountTable div.ipol_inst {
        display:       none;
        margin-left:   10px;
        margin-bottom: 10px;
    }
    #IPOLSDEK_addAcc td {
        padding: 0 5px 2px;
    }
    .IPOLSDEK_tariffCaption {
        font-weight: bold;
        padding: 10px;
        text-align: center;
        border-bottom: 1px dashed #BCC2C4;
    }
    .IPOLSDEK_tariffHeader {
        font-weight: bold;
        padding: 10px 0;
        border-bottom: 1px dashed #BCC2C4;
    }
    .IPOLSDEK_tariffHeader.ACTIVE {
        background-color: #E2FCE2 !important;
    }
    .IPOLSDEK_tariffHeader.ARCHIVE {
        background-color: #FCFCBF !important;
    }
    .IPOLSDEK_tariffSubHeader {
        font-style: italic;
        padding: 5px 0;
        text-align: center;
        border-bottom: 1px dashed #BCC2C4;
    }
</style>
<script>
	<?=sdekdriver::getModuleExt('mask_input')?>

	IPOLSDEK_setups.base = {
		ready: function(){
			$('[name="termInc"]').on('keyup',IPOLSDEK_setups.base.onTermChange);
			$('[name="mindEnsure"]').on('change',IPOLSDEK_setups.base.onEnsChange);
			$('[name="addData"]').on('change',IPOLSDEK_setups.base.onTurnOnData);
			IPOLSDEK_setups.base.depature.init();
			IPOLSDEK_setups.base.properties.init();
			IPOLSDEK_setups.base.onEnsChange();
		},

		senderCities: [<?=$senderCitiesJS?>],

		/* Header buttons */
		clearCache: function(){
			$('#IPOLSDEK_cacheKiller').attr('disabled','disabled');
			IPOLSDEK_setups.ajax({
				data: {isdek_action:'clearCache'},
				success: function(){
					alert("<?=GetMessage('IPOLSDEK_LBL_CACHEKILLED')?>");
					$('#IPOLSDEK_cacheKiller').removeAttr('disabled');
				}
			});
		},

		ressurect: function(){
			$('#IPOLSDEK_ressurect').attr('disabled','disabled');
			IPOLSDEK_setups.ajax({
				data: {isdek_action:'ressurect'},
				success: function(){
					$('#IPOLSDEK_ressurect').closest('tr').replaceWith(' ');
				}
			});
		},

		/* Accounts */
		accounts: {
			wnd: false,

			show: function(){
				if(!IPOLSDEK_setups.base.accounts.wnd){
					IPOLSDEK_setups.base.accounts.wnd = new BX.CDialog({
						title: "<?=GetMessage('IPOLSDEK_OTHR_accHeader')?>",
						content: '<div id="IPOLSDEK_accountWnd"><div style="text-align:center"><img style="border:none" src="/bitrix/images/ipol.sdek/bigAjax.gif"></div></div>',
						icon: 'head-block',
						resizable: true,
						draggable: true,
						height: '450',
						width: '800',
						buttons: []
					});
				}
				IPOLSDEK_setups.base.accounts.wnd.Show();
				IPOLSDEK_setups.base.accounts.markAjax();
				IPOLSDEK_setups.base.accounts.requestAcs();
			},

			markAjax: function(){
				$('#IPOLSDEK_accountWnd').removeClass('IPOLSDEK_addAcc');
				$('#IPOLSDEK_accountWnd').html('<div style="text-align:center"><img src="/bitrix/images/ipol.sdek/bigAjax.gif" style="border:none"></div>');
			},

			requestAcs: function(){
				IPOLSDEK_setups.ajax({
					data: {isdek_action:'callAccounts'},
					dataType: 'json',
					success: IPOLSDEK_setups.base.accounts.loadAcs
				});
			},

			loadAcs: function(data){
				var html = '<table id="IPOLSDEK_accountTable">';
				var cnt = 0;
                var auth;

                html += '<tr><td colspan="6">';
                html += '<a class="ipol_header" onclick="$(this).next().toggle(); return false;"><?=GetMessage("IPOLSDEK_FAQ_ACCOUNTS_TABLE_TITLE")?></a>';
                html += '<div class="ipol_inst"><?=GetMessage("IPOLSDEK_FAQ_ACCOUNTS_TABLE_DESCR")?></div>';
                html += '</td></tr>';

                html += "<tr><th>ID</th><th><?=GetMessage('IPOLSDEK_ACCOUNT_TABLE_KEY');?></th><th><?=GetMessage('IPOLSDEK_ACCOUNT_TABLE_LABEL');?></th><th><?=GetMessage('IPOLSDEK_ACCOUNT_TABLE_AUTH');?></th><th><?=GetMessage('IPOLSDEK_ACCOUNT_TABLE_DEFAULT');?></th><th></th></tr>";
                for (var i in data) {
					cnt++;
                    auth = (data[i].auth) ? "<?=GetMessage('IPOLSDEK_ACCOUNT_TABLE_AUTH_OK')?>" : "<?=GetMessage('IPOLSDEK_ACCOUNT_TABLE_AUTH_ERROR')?>";
					html += "<tr><td>"+i+"</td><td>"+data[i].account+"</td><td>"+data[i].label+"</td><td>"+auth+"</td><td>"+((data[i].default)?"<?=GetMessage('IPOLSDEK_OTHR_accDefault')?>":"<input type='button' value='<?=GetMessage('IPOLSDEK_OTHR_accMakeDefault')?>' onclick='IPOLSDEK_setups.base.accounts.makeDefault("+i+")'>")+"</td><td><input type='button' value='<?=GetMessage('IPOLSDEK_OTHR_accDelete')?>' onclick='"+((data[i].default)?"IPOLSDEK_setups.base.accounts.defaultDelete("+i+")":"IPOLSDEK_setups.base.accounts.delete("+i+")")+"'></td></tr>";
				}
				if (cnt == 1)
					html = html.replace('firstDelete','lastDelete');
				html += "</table><br>";

                html += "<table id='IPOLSDEK_addAcc'><tr><td><?=GetMessage('IPOLSDEK_ACCOUNT_TABLE_KEY');?></td><td><input type='text' size='35' id='IPOLSDEK_addAccAccount'></td></tr><tr><td><?=GetMessage('IPOLSDEK_ACCOUNT_TABLE_PASS');?></td><td><input type='text' size='35' id='IPOLSDEK_addAccPassword'></td></tr><tr><td><?=GetMessage('IPOLSDEK_OTHR_acComent')?></td><td><input type='text' size='15' id='IPOLSDEK_addAccComment'></td></tr><tr><td colspan='2'><input type='button' value='<?=GetMessage('IPOLSDEK_OTHR_accAdd')?>' onclick='IPOLSDEK_setups.base.accounts.add()'></td></tr></table><input id='IPOLSDEK_newAcc' type='button' value='<?=GetMessage('IPOLSDEK_OTHR_accNew')?>' onclick=\"IPOLSDEK_setups.base.accounts.new()\"/>";
				$('#IPOLSDEK_accountWnd').html(html);
				$('#IPOLSDEK_addAccComment').on('keyup',IPOLSDEK_setups.base.accounts.onPressComment);
			},

				/* Account adding */
			new: function(){
				$('#IPOLSDEK_accountWnd').addClass('IPOLSDEK_addAcc');
			},
			add: function(){
				var fail = false;
				var account  = $('#IPOLSDEK_addAccAccount').val();
				var password = $('#IPOLSDEK_addAccPassword').val();
				var comment  = $('#IPOLSDEK_addAccComment').val();
				if(account.length != 32){
					$('#IPOLSDEK_addAccAccount').addClass('IPOLSDEK_errorInput');
					fail=true;
				}else
					$('#IPOLSDEK_addAccAccount').removeClass('IPOLSDEK_errorInput');

				if(password.length != 32){
					$('#IPOLSDEK_addAccPassword').addClass('IPOLSDEK_errorInput');
					fail=true;
				}else
					$('#IPOLSDEK_addAccPassword').removeClass('IPOLSDEK_errorInput');

				if(comment.length > 15)
					comment = comment.substr(0,15);

				if(!fail){
					IPOLSDEK_setups.base.accounts.markAjax();
					IPOLSDEK_setups.ajax({
						data: {isdek_action:'newAccount',ACCOUNT:account,PASSWORD:password,LABEL:comment},
						dataType: 'json',
						success: IPOLSDEK_setups.base.accounts.onAnswerAccount
					});
				}
			},
			onAnswerAccount: function(data){
				if(typeof(data.text) !== 'undefined' && data.text)
					alert(data.text);
				if(data.result === 'collapse')
					IPOLSDEK_setups.reload();
				else
					IPOLSDEK_setups.base.accounts.requestAcs();
			},
				/* Account delete */
			defaultDelete: function(id){
				if(confirm("<?=GetMessage("IPOLSDEK_OTHR_accDefaultDelete")?>"))
					IPOLSDEK_setups.base.accounts.delete(id);
			},

			lastDelete: function(id){
				if(confirm("<?=GetMessage("IPOLSDEK_OTHR_accLastDelete")?>"))
					IPOLSDEK_setups.base.accounts.delete(id);
			},

			delete: function(id){
				IPOLSDEK_setups.base.accounts.markAjax();
				IPOLSDEK_setups.ajax({
					data: {isdek_action:'optionDeleteAccount',ID:id},
					dataType: 'json',
					success: IPOLSDEK_setups.base.accounts.onAnswerAccount
				});
			},
				/* Main account */
			makeDefault: function(id){
				if(confirm("<?=GetMessage("IPOLSDEK_OTHR_accDefaultDelete")?>")){
					IPOLSDEK_setups.base.accounts.markAjax();
					IPOLSDEK_setups.ajax({
						data: {isdek_action:'optionMakeAccDefault',ID:id},
						dataType: 'json',
						success: IPOLSDEK_setups.base.accounts.onAnswerAccount
					});
				}
			},

			onPressComment: function(comInput){
				if($(comInput.currentTarget).val().length > 15)
					$(comInput.currentTarget).val($(comInput.currentTarget).val().substr(0,15));
			}
		},

		/* Departure cities */
		depature:{
			add: function(){
				$('#IPOLSDEK_addDeparturePlace').append("<div><input type='text' class='IPOLSDEK_addDeparture rescent'><input type='hidden' name='addDeparture[]'></div>");
				IPOLSDEK_setups.base.depature.input($('.IPOLSDEK_addDeparture.rescent'));
				$('.IPOLSDEK_addDeparture.rescent').removeClass('rescent');
			},
			input: function(wat){
				wat.autocomplete({
				  source: IPOLSDEK_setups.base.senderCities,
				  select: IPOLSDEK_setups.base.depature.onSelect
				});
			},
			init: function(){
				$('.IPOLSDEK_addDeparture').each(function(){IPOLSDEK_setups.base.depature.input($(this));});
			},
			onSelect: function(ev,ui){
				window.setTimeout(function(){
					$(arguments[0]).val(arguments[1]);
				},100,ev.target,ui.item.label);
				$(ev.target).siblings("[type='hidden']").val(ui.item.value);
			},
			delete: function(wat){
				wat.parent().replaceWith('');
			}
		},

		/* Props view variants */
		properties: {
			getModeNF : function(){
				/* T - extended, F - usual */
				return ($('[name="extendName"]').val()==='Y');
			},
			checkNF : function(){
				if(IPOLSDEK_setups.base.properties.getModeNF()){
					$('[name="name"]').closest('tr').css('display','none');
					$('[name="fName"]').closest('tr').css('display','');
					$('[name="sName"]').closest('tr').css('display','');
					$('[name="mName"]').closest('tr').css('display','');
				} else {
					$('[name="name"]').closest('tr').css('display','');
					$('[name="fName"]').closest('tr').css('display','none');
					$('[name="sName"]').closest('tr').css('display','none');
					$('[name="mName"]').closest('tr').css('display','none');
				}
			},
			turnOnNF: function(){
				$('[name="extendName"]').val('Y');
				IPOLSDEK_setups.base.properties.checkNF();
			},
			turnOffNF: function(){
				$('[name="extendName"]').val('N');
				IPOLSDEK_setups.base.properties.checkNF();
			},
			init: function(){
				IPOLSDEK_setups.base.properties.checkNF();
			}
		},
		/* Service */
		serverShow: function(){
			$('.IPOLSDEK_service').each(function(){
				$(this).css('display','table-row');
			});
			$('[onclick^="IPOLSDEK_setups.base.serverShow("]').css('cursor','auto');
			$('[onclick^="IPOLSDEK_setups.base.serverShow("]').css('textDecoration','none');
		},

		counterReset: function(){
			if(confirm('<?=GetMessage('IPOLSDEK_OTHR_schet_ALERT')?>'))
				IPOLSDEK_setups.ajax({
					data: {isdek_action:'killSchet'},
					success: function(data){
						if(data=='1'){
							alert('<?=GetMessage("IPOLSDEK_OTHR_schet_DONE")?>');
							$("[onclick^='IPOLSDEK_setups.base.counterReset(']").parent().html('0');
						}else
							alert('<?=GetMessage("IPOLSDEK_OTHR_schet_NONE")?>'+data);
					}
				});
		},

		clrUpdt: function(){
			if(confirm('<?=GetMessage('IPOLSDEK_OPT_clrUpdt_ALERT')?>')){
				$('.IPOLSDEK_clz').css('display','none');
				IPOLSDEK_setups.ajax({
					data: {isdek_action:'killUpdt'},
					success: function(data){
						if(data==='done')
							$("#IPOLSDEK_updtPlc").replaceWith('');
						else{
							$('.IPOLSDEK_clz').css('display','');
							alert('<?=GetMessage("IPOLSDEK_OPT_clrUpdt_ERR")?>');
						}
					}
				});
			}
		},
		
		subSyncList: function(){
			$('#IPOLSDEK_subsyncer').attr('disabled','disabled');
			IPOLSDEK_setups.base.syncList();
		},

		syncList: function(params){
			var dataObj = {text:false,status:false};
			var reqObj  = {isdek_action: 'callUpdateList',full: true};
			if(typeof(params) === 'undefined'){
				IPOLSDEK_setups.cities.controlSunc();
				dataObj.text = '<?=GetMessage("IPOLSDEK_OTHR_lastModList_START")?>';
			}else{
				dataObj.text   = params.text;
				dataObj.status = params.result;
				if(params.result !== 'error')
					reqObj['listDone'] = true;
			}

			if(dataObj.text){
				if($('#IPOLSDEK_syncInfo').length === 0)
					$('#IPOLSDEK_sT_sunc').after('<br><span id="IPOLSDEK_syncInfo"></span>');
				$('#IPOLSDEK_syncInfo').html(dataObj.text);
				if(dataObj.status == 'error')
					$('#IPOLSDEK_syncInfo').css('color','red');
			}

			if(dataObj.status !== 'error' && dataObj.status !== 'end'){
				IPOLSDEK_setups.ajax({
					data: reqObj,
					dataType: 'json',
					success: IPOLSDEK_setups.base.syncList,
					error: function(a,b,c){alert("sync "+b+" "+c);}
				});
			}else
				if(dataObj.status === 'end'){
					alert(dataObj.text);
					IPOLSDEK_setups.cities.controlSunc(true);
					IPOLSDEK_setups.reload();
				} else{
					alert(dataObj.text);
				}
		},

        countSuncOrdrs    : 0,
        launchedSuncOrdrs : 0,
		syncOrdrs: function(){
			$('[onclick="IPOLSDEK_setups.base.syncOrdrs()"]').css('display','none');
            $('#IPOLSDEK_SOinfo').html('');
            IPOLSDEK_setups.base.countSuncOrdrs    = 0;
            IPOLSDEK_setups.base.launchedSuncOrdrs = 0;

			IPOLSDEK_setups.ajax({
				data: {'isdek_action':'checkUpdateStates'},
                dataType : 'json',
				success: function(data){
                    $('#IPOLSDEK_SOinfo').html(data.MESSAGE);
                    if(data.COUNT){
                        IPOLSDEK_setups.base.countSuncOrdrs = data.COUNT;
                        $('#IPOLSDEK_SOinfo').append('&nbsp;<span id="IPOLSDEK_SO_process">0/'+data.COUNT+'</span>');
                        IPOLSDEK_setups.base.launchStatusUpdate();
                    } else {
                        $('#IPOLSDEK_SO').css('display','');
                    }
				}
			});

		},
        launchStatusUpdate : function(){
            IPOLSDEK_setups.ajax({
                data: {'isdek_action':'callOrderStates'},
                dataType : 'json',
                success: function(data){
                    if(data.ERR){
                        $('#IPOLSDEK_SOinfo').html(data.ERR);
                    } else {
                        IPOLSDEK_setups.base.launchedSuncOrdrs++;

                        if(IPOLSDEK_setups.base.launchedSuncOrdrs >= IPOLSDEK_setups.base.countSuncOrdrs){
                            $('#IPOLSDEK_SOinfo').html('');
                            $('#IPOLSDEK_SOtime').html(data.MESSAGE);
                            $('#IPOLSDEK_SO').css('display','');

                            IPOLSDEK_setups.table.getTable();
                        } else {
                            $('#IPOLSDEK_SO_process').html(IPOLSDEK_setups.base.launchedSuncOrdrs+'/'+IPOLSDEK_setups.base.countSuncOrdrs);
                            IPOLSDEK_setups.base.launchStatusUpdate();
                        }
                    }
                }
            });
        },

		importCities: function(){
		    var migrated = <?=($migrated)?'true':'false'?>;
			$('#IPOLSDEK_IMPORTCITIES').attr('disabled','disabled');

		    if(!migrated || confirm('<?=GetMessage("IPOLSDEK_LBL_NONEEDIMPORT")?>')) {
                IPOLSDEK_setups.ajax({
                    data: {'isdek_action': 'setImport', 'mode': 'Y'},
                    success: IPOLSDEK_setups.reload
                });
            }
		},

		autoloads: function(){
			$('#IPOLSDEK_AUTOLOADS').attr('disabled','disabled');
			IPOLSDEK_setups.ajax({
				data: {'isdek_action':'setAutoloads'},
				success: IPOLSDEK_setups.reload
			});
		},

		logOff: function(){
			$("#IPOLSDEK_logoffer").attr('disabled','disabled');
			if(confirm('<?=GetMessage("IPOLSDEK_LBL_ISLOGOFF")?>'))
				IPOLSDEK_setups.ajax({
					data: {'isdek_action':'logoff'},
					success: IPOLSDEK_setups.reload
				});
			else
				$("#IPOLSDEK_logoffer").removeAttr('disabled');
		},

		/* Other */
		onTermChange: function(){
			var day = parseInt($('[name="termInc"]').val());
			if(isNaN(day))
				day = '';			
			$('[name="termInc"]').val(day);
		},

		onEnsChange: function(){
			if($('[name="mindEnsure"]').attr('checked')){
				$('[name="ensureProc"]').closest('tr').css('display','');
				$('[name="mindNDSEnsure"]').closest('tr').css('display','');
			}else{
				$('[name="ensureProc"]').closest('tr').css('display','none');
				$('[name="mindNDSEnsure"]').closest('tr').css('display','none');
			}
		},
		
		onTurnOnData: function(){
			if($('[name="addData"]').attr('checked')){
				alert('<?=GetMessage('IPOLSDEK_OTHR_addDataWarn')?>');
			}
		}
	};
</script>

<?php
foreach(array("departure","showInOrders","addDeparture","shipments","prntActOrdr","numberOfPrints","numberOfStrihs","formatOfStrihs","deliveryAsPosition","normalizePhone","addData","NDSUseCatalog","address",'feacn',"pvzPicker","ymapsAPIKey","vidjetSearch","noPVZnoOrder","hideNal","hideNOC","autoSelOne","noYmaps","cntExpress","mindEnsure","mindNDSEnsure","forceRoundDelivery","noVats","addMeasureName","statusSTORE","statusTRANZT","statusCORIER","setTrackingOrderProp","warhouses","dostTimeout","timeoutRollback","orderStatusesLimit","orderStatusesUptime","orderStatusesAgentRollback","TURNOFF","TARSHOW","autoAddCities","noSertifCheckNative","debugMode") as $code)
	sdekOption::placeHint($code);

$deadServerCheck = \Ipolh\SDEK\option::get('sdekDeadServer');
if($deadServerCheck && (time() - $deadServerCheck) < (\Ipolh\SDEK\option::get('timeoutRollback') * 60)){?>
	<tr><td colspan='2'>
		<div class="adm-info-message-wrap adm-info-message-red">
		  <div class="adm-info-message">
			<div class="adm-info-message-title"><?=GetMessage('IPOLSDEK_DEAD_SERVER_HEADER')?></div>
				<?=GetMessage('IPOLSDEK_DEAD_SERVER_TITLE')?>&nbsp;<?=date('H:i:s d.m.Y',$deadServerCheck)?>.
				<br>
				<br>
				<?php sdekOption::placeFAQ('DEAD_SERVER') ?>
				<br>
				<input type='button' id='IPOLSDEK_ressurect' onclick='IPOLSDEK_setups.base.ressurect()' value='<?=GetMessage("IPOLSDEK_DEAD_SERVER_BTN")?>'>
			<div class="adm-info-message-icon"></div>
		  </div>
		</div>
	</td></tr>
<?php } ?>

<?php if (!\Ipolh\SDEK\abstractGeneral::isNewApp()): ?>
<?php $accountsWithDisabledApi = sdekOption::getAccountsWithDisabledOldApi(); ?>
<?php if (count($accountsWithDisabledApi)): ?>
    <tr>
        <td colspan='2'>
            <div class="adm-info-message-wrap adm-info-message-red">
                <div class="adm-info-message">
                    <div class="adm-info-message-title"><?= GetMessage('IPOLSDEK_DISABLED_API_1_5_HEADER') ?></div>
                    <?= GetMessage('IPOLSDEK_DISABLED_API_1_5_TITLE') ?>
                    <?php foreach ($accountsWithDisabledApi as $account): ?>
                    <div><?= $account['ACCOUNT'] . (strlen($account['LABEL']) ? ' (' . $account['LABEL'] . ')' : '') ?></div>
                    <?php endforeach; ?>
                    <div class="adm-info-message-icon"></div>
                </div>
            </div>
        </td>
    </tr>
<?php endif; ?>
<?php endif; ?>
<?php

if(!file_exists(\Ipolh\SDEK\Bitrix\Controller\pvzController::getFilePath())){
	Ipolh\SDEK\Bitrix\Tools::placeErrorLabel(GetMessage('IPOLSDEK_NOLIST_ERR_TITLE'),GetMessage('IPOLSDEK_NOLIST_ERR_HEADER'));
}

$dost = sdekdriver::getDelivery(true);
if($dost){
	if($dost['ACTIVE'] != 'Y'){
		Ipolh\SDEK\Bitrix\Tools::placeErrorLabel(GetMessage('IPOLSDEK_NO_ADOST_TITLE'),GetMessage('IPOLSDEK_NO_ADOST_HEADER'));
	}
}else{
	if($converted){
		Ipolh\SDEK\Bitrix\Tools::placeErrorLabel(GetMessage('IPOLSDEK_NOT_CRTD_TITLE'),GetMessage('IPOLSDEK_NOT_CRTD_HEADER'));
	} else {
		Ipolh\SDEK\Bitrix\Tools::placeErrorLabel(GetMessage('IPOLSDEK_NO_DOST_TITLE'),GetMessage('IPOLSDEK_NO_DOST_HEADER'));
	}
}

foreach(sdekExport::getAllProfiles() as $profile)
	if(!sdekHelper::checkTarifAvail($profile)){
		Ipolh\SDEK\Bitrix\Tools::placeErrorLabel(GetMessage('IPOLSDEK_NO_PROFILE_TITLE'),GetMessage("IPOLSDEK_NO_PROFILE_HEADER_$profile"));
	}
?>

<tr>
    <?php
		$basicAuth = sdekHelper::getBasicAuth();
		if(!$basicAuth)
			sdekOption::authConsolidation();
		$basicAuth = ($basicAuth) ? $basicAuth['ACCOUNT'] : GetMessage('IPOLSDEK_LBL_BADAUTH');
	?>
	<td align="center"><?=GetMessage("IPOLSDEK_LBL_YLOGIN")?>: <strong><?=$basicAuth?></strong></td>
	<td align="center"><input type='button' id='IPOLSDEK_logoffer' onclick='IPOLSDEK_setups.base.logOff()' value='<?=GetMessage('IPOLSDEK_LBL_DOLOGOFF')?>'>&nbsp;&nbsp;<input type='button' onclick='IPOLSDEK_setups.base.accounts.show()' value='<?=GetMessage('IPOLSDEK_LBL_ACCOUNTS')?>'></td>
</tr>
<tr><td></td><td align="center"><input type='button' id='IPOLSDEK_cacheKiller' onclick='IPOLSDEK_setups.base.clearCache()' value='<?=GetMessage('IPOLSDEK_LBL_CLRCACHE')?>'></td></tr>

<?php // Common ?>
<?php \Ipolh\SDEK\Bitrix\Tools::placeOptionBlock('common'); ?>

<?php // Store ?>
<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage('IPOLSDEK_HDR_STORE')?></td></tr>
<tr><td colspan="2" align="center"><?=GetMessage('IPOLSDEK_FAQ_STORE')?></td></tr>

<?php // Print ?>
<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_HDR_print")?></td></tr>
<tr><td style="color:#555;" colspan="2">
	<?php sdekOption::placeFAQ('PRINT') ?>
</td></tr>
<?php ShowParamsHTMLByArray($arAllOptions["print"]); ?>
<tr><td style="color:#555;" colspan="2">
	<?php sdekOption::placeFAQ('PRINTSHTR') ?>
</td></tr>
<?php ShowParamsHTMLByArray($arAllOptions["printShtr"]); ?>

<?php // Dimensions ?>
<?php \Ipolh\SDEK\Bitrix\Tools::placeOptionBlock('dimensionsDef'); ?>

<?php // Props ?>
<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage('IPOLSDEK_HDR_requestProps')?></td></tr>
<?php ShowParamsHTMLByArray($arAllOptions["commonRequest"]); ?>
    <?php // Order props ?>
<tr class="subHeading"><td colspan="2" valign="top" align="center"><?=GetMessage('IPOLSDEK_HDR_orderProps')?></td></tr>
<tr><td style="color:#555;" colspan="2">
	<?php sdekOption::placeFAQ('PROPS') ?>
</td></tr>
<?php showOrderOptions(); ?>
<?php ShowParamsHTMLByArray($arAllOptions["usualOrderProps"]); ?>
    <?php // Items props ?>
<tr class="subHeading"><td colspan="2" valign="top" align="center"><?=GetMessage('IPOLSDEK_HDR_itemProps')?></td></tr>
<tr><td style="color:#555;" colspan="2">
	<?php sdekOption::placeFAQ('IPROPS') ?>
</td></tr>
<?php ShowParamsHTMLByArray($arAllOptions["itemProps"]); ?>
    <?php // VAT ?>
<tr class="subHeading"><td colspan="2" valign="top" align="center"><?=GetMessage('IPOLSDEK_HDR_NDS')?></td></tr>
<tr class="IPOLSDEK_converted"><td style="color:#555;" colspan="2">
        <?php sdekOption::placeFAQ('NDS') ?>
</td></tr>
<?php ShowParamsHTMLByArray($arAllOptions["NDS"]); ?>

<?php // Statuses ?>
<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_HDR_status")?></td></tr>
<tr><td style="color:#555;" colspan="2">
	<?php sdekOption::placeFAQ('status') ?>
</td></tr>
<?php
	sdekOption::placeStatuses($arAllOptions["status"]);
?>

<?php // Widget ?>
<?php \Ipolh\SDEK\Bitrix\Tools::placeOptionBlock('vidjet'); ?>

<?php // Basket ?>
<?php \Ipolh\SDEK\Bitrix\Tools::placeOptionBlock('basket'); ?>

<?php // Delivery ?>
<?php \Ipolh\SDEK\Bitrix\Tools::placeOptionBlock('delivery'); ?>
<tr><td colspan="2"><?=GetMessage("IPOLSDEK_FAQ_DELIVERY")?></td></tr>

<?php // Payment systems ?>
<tr class="heading"><td colspan="2" valign="top" align="center"><?= GetMessage("IPOLSDEK_OPT_paySystems") ?></td></tr>
<tr><td style="color:#555;" colspan="2">
	<?php sdekOption::placeFAQ('PAYSYS') ?>
</td></tr>
<tr><td colspan="2" style='text-align:center'><?= $paySysHtml ?></td></tr>

<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_HDR_addingService");?></td></tr>
<?php // Tariffs ?>
<tr><td style="color:#555;" colspan="2"><?php sdekOption::placeFAQ('TARIFFS');?></td></tr>
<?php $tariffList = sdekdriver::getStructuredTariffList();?>
<tr>
    <td colspan="2" valign="top" align="center">
        <table>
        <tr>
            <td colspan='2' class="IPOLSDEK_tariffCaption"><?=GetMessage('IPOLSDEK_TARIF_TABLE_NAME');?></td>
            <td class="IPOLSDEK_tariffCaption"><?=GetMessage('IPOLSDEK_TARIF_TABLE_SHOW');?></td>
            <td class="IPOLSDEK_tariffCaption"><?=GetMessage('IPOLSDEK_TARIF_TABLE_TURNOFF');?></td>
            <td class="IPOLSDEK_tariffCaption"></td>
        </tr>
        <?php foreach ($tariffList as $tariffState => $tariffProfiles) {?>
            <?php if ($tariffState !== 'ACTIVE') {?>
                <tr><td colspan="5">&nbsp;</td></tr>
            <?php } ?>
            <tr><td colspan="5" valign="top" align="center" class="IPOLSDEK_tariffHeader <?=$tariffState;?>"><?=GetMessage('IPOLSDEK_TARIF_TABLE_'.$tariffState);?></td></tr>
            <?php foreach ($tariffProfiles as $tariffProfile => $tariffs) {?>
                <tr><td colspan="5" class="IPOLSDEK_tariffSubHeader"><?=GetMessage('IPOLSDEK_TARIF_TABLE_'.$tariffProfile);?></td></tr>
                <?php foreach ($tariffs as $tarifId => $tarifOption) {?>
                    <tr>
                        <td style='text-align:center'><?php if ($tarifOption['DESC']) {?><a href='#' class='PropHint' onclick='return IPOLSDEK_setups.popup("pop-AS<?=$tarifId;?>",this);'></a><?php }?></td>
                        <td><?=$tarifOption['NAME'];?></td>
                        <td align='center'><input type='checkbox' name='tarifs[<?=$tarifId?>][SHOW]' value='Y' <?=($tarifOption['SHOW']=='Y')?"checked":""?> /></td>
                        <td align='center'><input type='checkbox' name='tarifs[<?=$tarifId?>][BLOCK]' value='Y' <?=($tarifOption['BLOCK']=='Y')?"checked":""?> /></td>
                        <td>
                            <?php if ($tarifOption['DESC']) {?>
                                <div id="pop-AS<?=$tarifId;?>" class="b-popup" style="display: none; ">
                                    <div class="pop-text"><?=$tarifOption['DESC'];?></div>
                                    <div class="close" onclick="$(this).closest('.b-popup').hide();"></div>
                                </div>
                            <?php }?>
                        </td>
                    </tr>
                <?php } ?>
            <?php }?>
        <?php }?>
        </table>
    </td>
</tr>
<tr><td colspan='2' style="border-bottom: 1px dashed #BCC2C4;"><br></td></tr>
<tr><td colspan='2'><br></td></tr>
<?php // Additional services ?>
<tr><td style="color:#555;" colspan="2"><?php sdekOption::placeFAQ('ADD_SERVICES');?></td></tr>
<?php $arAddService = sdekdriver::getExtraOptions() ?>
<tr>
    <td colspan="2" valign="top" align="center">
        <table>
	    <tr><td colspan="5" valign="top" align="center" class="IPOLSDEK_tariffHeader ACTIVE"><?=GetMessage("IPOLSDEK_AS_TABLE_ACTIVE");?></td></tr>
	    <tr>
            <td colspan="2" class="IPOLSDEK_tariffCaption"><?=GetMessage("IPOLSDEK_AS_TABLE_NAME");?></td>
            <td class="IPOLSDEK_tariffCaption"><?=GetMessage("IPOLSDEK_AS_TABLE_SHOW");?></td>
            <td class="IPOLSDEK_tariffCaption"><?=GetMessage("IPOLSDEK_AS_TABLE_DEF");?></td>
            <td class="IPOLSDEK_tariffCaption"></td>
        </tr>
	    <?php foreach ($arAddService as $asId => $adOption) {?>
		    <tr>
                <td><a href='#' class='PropHint' onclick='return IPOLSDEK_setups.popup("pop-AS-<?=$asId;?>",this);'></a></td>
                <td><?=$adOption['NAME'];?></td>
                <td align='center'><input type='checkbox' name='addingService[<?=$asId?>][SHOW]' value='Y' <?=($adOption['SHOW']=='Y')?"checked":""?> /></td>
                <td align='center'><input type='checkbox' name='addingService[<?=$asId?>][DEF]' value='Y' <?=($adOption['DEF']=='Y')?"checked":""?> /></td>
                <td>
                    <div id="pop-AS-<?=$asId;?>" class="b-popup" style="display: none; ">
                        <div class="pop-text"><?=$adOption['DESC'];?></div>
                        <div class="close" onclick="$(this).closest('.b-popup').hide();"></div>
                    </div>
                </td>
		    </tr>
	    <?php } ?>
        </table>
    </td>
</tr>
<?php // Splitting for warehouses ?>
<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_HDR_warhouses")?></td>
</tr>
<tr><td style="color:#555;" colspan="2">
	<?php sdekOption::placeFAQ('WARHOUSES') ?>
</td></tr>
<?php ShowParamsHTMLByArray($arAllOptions["warhouses"]); ?>
<tr><td colspan='2'>
        <?php
	$arFounded = array();
	foreach(GetModuleEvents($module_id, "onBeforeShipment", true) as $arEvent)
		$arFounded[]=$arEvent;
	if(!count($arFounded))
		echo GetMessage('IPOLSDEK_OTHR_noWarhouses');
	else{
		echo GetMessage('IPOLSDEK_OTHR_hasWarhouses')."<br><ul>";
		foreach($arFounded as $arEvent)
			echo '<li>'.$arEvent['CALLBACK'].'</li>';
		echo '</ul>';
	}
?>
</td></tr>

<?php // Autoloads ?>
<?php if(!$autoloads) { ?>
<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_HDR_autoUploads")?></td>
</tr>
<tr><td style="color:#555;" colspan="2">
	<?php sdekOption::placeFAQ('AUTOUPLOADS') ?>
</td></tr>
<tr><td colspan='2' style='text-align:center'>
	<br><input type='button' value='<?=GetMessage('IPOLSDEK_OPT_autoloads')?>' id='IPOLSDEK_AUTOLOADS' onclick='IPOLSDEK_setups.base.autoloads()'/>
</td></tr>
<?php } ?>

<?php // Service ?>
<tr class="heading" onclick='IPOLSDEK_setups.base.serverShow()' style='cursor:pointer;text-decoration:underline'>
	<td colspan="2" valign="top" align="center"><?=GetMessage("IPOLSDEK_HDR_service")?></td>
</tr> 
<tr style='display:none' class='IPOLSDEK_service'>
	<td><?=GetMessage('IPOLSDEK_OTHR_schet')?></td>
	<td>
      <?php
		$tmpVal=\Ipolh\SDEK\option::get('schet');
		echo $tmpVal;
		if($tmpVal>0){
	?> <input type='button' value='<?=GetMessage('IPOLSDEK_OTHR_schet_BUTTON')?>' onclick='IPOLSDEK_setups.base.counterReset()'/>
	<?php } ?>
	</td>
</tr>
<tr style='display:none' class='IPOLSDEK_service'>
	<td><?=GetMessage('IPOLSDEK_OTHR_lastModList')?></td>
	<td>
      <?php $ft = filemtime($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".$module_id."/list.json");?>
		<span id='IPOLSDEK_updtTime'><?=($ft)?date("d.m.Y H:i:s",$ft):GetMessage("IPOLSDEK_OTHR_NOTCOMMITED");?></span>
		<input id='IPOLSDEK_sT_sunc' type='button' value='<?=GetMessage('IPOLSDEK_OTHR_lastModList_BUTTON')?>' onclick='IPOLSDEK_setups.base.syncList()'/>
	</td>
</tr>		
<tr style='display:none' class='IPOLSDEK_service'>
	<td><?=GetMessage('IPOLSDEK_OPT_statCync')?></td>
	<td>
        <span id="IPOLSDEK_SOtime">
      <?php $optVal = \Ipolh\SDEK\option::get('statCync');
			if($optVal>0) echo date("d.m.Y H:i:s",$optVal);
			else echo GetMessage('IPOLSDEK_OTHR_NOTCOMMITED');
		?>
        </span>
		<input type='button' value='<?=GetMessage('IPOLSDEK_OTHR_getOutLst_BUTTON')?>' id='IPOLSDEK_SO' onclick='IPOLSDEK_setups.base.syncOrdrs()'/>
        <div id="IPOLSDEK_SOinfo"></div>
	</td>
</tr>
<tr style='display:none' class='IPOLSDEK_service'>
    <td><?=GetMessage('IPOLSDEK_OPT_useOldApi')?></td>
    <td>
        <input type='checkbox' value='Y' name='useOldApi' <?=(\Ipolh\SDEK\option::get('useOldApi') === 'Y') ? 'checked' : ''?>/>
    </td>
</tr>
<tr style='display:none' class='IPOLSDEK_service'>
	<td><?=GetMessage('IPOLSDEK_OPT_dostTimeout')?></td>
	<td>
      <?php
			$optVal = \Ipolh\SDEK\option::get('dostTimeout');
			if(floatval($optVal)<=0) $optVal=6;
		?>
		<input type='text' value='<?=$optVal?>' name='dostTimeout' size="1"/>
	</td>
</tr>
<tr style='display:none' class='IPOLSDEK_service'>
	<td><?=GetMessage('IPOLSDEK_OPT_timeoutRollback')?></td>
	<td>
      <?php
			$optVal = \Ipolh\SDEK\option::get('timeoutRollback');
			if(floatval($optVal)<=0) $optVal=15;
		?>
		<input type='text' value='<?=$optVal?>' name='timeoutRollback' size="1"/>
	</td>
</tr>
<tr style='display:none' class='IPOLSDEK_service'>
	<td><?=GetMessage('IPOLSDEK_OPT_debugMode')?></td>
	<td>
		<input type='checkbox' value='Y' name='debugMode' <?=(\Ipolh\SDEK\option::get('debugMode') === 'Y') ? 'checked' : ''?>/>
	</td>
</tr>
<tr style='display:none' class='IPOLSDEK_service'>
	<td><?=GetMessage('IPOLSDEK_OPT_autoAddCities')?></td>
	<td>
		<input type='checkbox' value='Y' name='autoAddCities' <?=(\Ipolh\SDEK\option::get('autoAddCities') === 'Y') ? 'checked' : ''?>/>
	</td>
</tr>
<?php if(!$import) { ?>
<tr style='display:none' class='IPOLSDEK_service'><td colspan='2' style='text-align:center'>
	<br><input type='button' value='<?=GetMessage('IPOLSDEK_OTHR_importCities_BUTTON')?>' id='IPOLSDEK_IMPORTCITIES' onclick='IPOLSDEK_setups.base.importCities()'/>
</td></tr>
<?php } ?>

<tr class="subHeading IPOLSDEK_service" style='display:none'><td colspan="2" valign="top" align="center"><?=GetMessage('IPOLSDEK_HDR_syncstatus')?></td></tr>
<tr style='display:none' class='IPOLSDEK_service'>
    <td><?=GetMessage('IPOLSDEK_OPT_orderStatusesLimit')?></td>
    <td>
        <?php
        $optVal = \Ipolh\SDEK\option::get('orderStatusesLimit');
        if((int)$optVal <= 0) $optVal = 100;
        ?>
        <input type='text' value='<?=$optVal?>' name='orderStatusesLimit' size="1"/>
    </td>
</tr>
<tr style='display:none' class='IPOLSDEK_service'>
    <td><?=GetMessage('IPOLSDEK_OPT_orderStatusesUptime')?></td>
    <td>
        <?php
        $optVal = \Ipolh\SDEK\option::get('orderStatusesUptime');
        if((int)$optVal <= 0) $optVal = 60;
        ?>
        <input type='text' value='<?=$optVal?>' name='orderStatusesUptime' size="1"/>
    </td>
</tr>
<tr style='display:none' class='IPOLSDEK_service'>
    <td><?=GetMessage('IPOLSDEK_OPT_orderStatusesAgentRollback')?></td>
    <td>
        <?php
        $optVal = \Ipolh\SDEK\option::get('orderStatusesAgentRollback');
        if((int)$optVal <= 0) $optVal = 30;
        ?>
        <input type='text' value='<?=$optVal?>' name='orderStatusesAgentRollback' size="1"/>
    </td>
</tr>