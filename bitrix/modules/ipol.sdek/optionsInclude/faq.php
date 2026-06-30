<style>
	.ipol_header {
		font-size: 16px;
		cursor: pointer;
		display:block;
		color:#2E569C;
	}

	.ipol_inst {
		display:none; 
		margin-left:10px;
		margin-top:10px;
		margin-bottom: 10px;
	}

	.ipol_smallHeader{
		cursor: pointer;
		display:block;
		color:#2E569C;
	}

	.ipol_subFaq{
		margin-bottom:10px;
	}

	img{border: 1px dotted black;}
	.IPOLSDEK_optName{
		font-weight: bold;
	}
	.IPOLSDEK_warning{
		color:red;
	}
	.IPOLSDEK_converted{
		<?=($converted)?'':'display:none !important;'?>
	}
	.IPOLSDEK_notConverted{
		<?=($converted)?'display:none !important;':''?>
	}
	.IPOLSDEK_mp1{
		<?=($migrated)?'display:none !important;':''?>
	}
	.IPOLSDEK_mp2{
		<?=($migrated)?'':'display:none !important;'?>
	}
	.IPOLSDEK_importHasCity{
		<?=($ctId)?'':'display:none !important;'?>
	}
	.IPOLSDEK_importHasNotCity{
		<?=($ctId)?'display:none !important;':''?>
	}
    .IPOLSDEK_b24{
        <?=($isB24)? '' : 'display:none !important;'?>
    }
</style>

<?php
if(sdekHelper::getModuleVersion()){
	Ipolh\SDEK\Bitrix\Tools::placeWarningLabel('<a href="/bitrix/admin/partner_modules.php?lang=ru">'.GetMessage('IPOLSDEK_LABEL_checkVersion').'</a>',GetMessage('IPOLSDEK_LABEL_moduleVersion').sdekHelper::getModuleVersion());
}
?>

<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage('IPOLSDEK_FAQ_HDR_SETUP')?></td></tr>
<tr><td style="color:#555;" colspan="2">
	<?php sdekOption::placeFAQ('WTF') ?>
	<?php sdekOption::placeFAQ('HIW') ?>
</td></tr>

<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage('IPOLSDEK_FAQ_HDR_ABOUT')?></td></tr> 
<tr><td style="color:#555;" colspan="2">
	<?php sdekOption::placeFAQ('TURNON') ?>
	<?php sdekOption::placeFAQ('DELSYS') ?>
	<?php sdekOption::placeFAQ('STORES') ?>
	<?php sdekOption::placeFAQ('SEND') ?>
	<?php sdekOption::placeFAQ('PELENG') ?>
	<?php sdekOption::placeFAQ('COURIERCALLS') ?>
</td></tr>

<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage('IPOLSDEK_FAQ_HDR_WORK')?></td></tr>
<tr><td style="color:#555; " colspan="2">
	<?php sdekOption::placeFAQ('PRINTFULL') ?>
	<?php sdekOption::placeFAQ('ACCOUNTS') ?>
	<?php sdekOption::placeFAQ('RBK') ?>
	<?php sdekOption::placeFAQ('PC') ?>
	<?php if(sdekOption::isConverted())
		sdekOption::placeFAQ('SHIPMENTS') ?>
	<?php sdekOption::placeFAQ('COMPONENT') ?>
	<?php sdekOption::placeFAQ('AUTOMATIZATION') ?>
	<?php sdekOption::placeFAQ('MULTISITE') ?>
	<?php sdekOption::placeFAQ('DELIVERYPRICE') ?>
	<?php sdekOption::placeFAQ('DIFFERENTSENDERS') ?>
	<?php sdekOption::placeFAQ('SENDWATCHLINK') ?>
</td></tr>

<tr class="heading"><td colspan="2" valign="top" align="center"><?=GetMessage('IPOLSDEK_FAQ_HDR_HELP')?></td></tr>
<tr><td style="color:#555; " colspan="2">
	<?php sdekOption::placeFAQ('CITYSUNC') ?>
	<?php sdekOption::placeFAQ('CNTDOST') ?>
	<?php sdekOption::placeFAQ('CALLCOURIER') ?>
	<?php sdekOption::placeFAQ('TESTACCOUNT') ?>
	<?php sdekOption::placeFAQ('ERRORS') ?>
	<?php sdekOption::placeFAQ('PROBLEMS') ?>
	<?php sdekOption::placeFAQ('UPDATES') ?>
	<?php sdekOption::placeFAQ('OTHER') ?>
</td></tr>