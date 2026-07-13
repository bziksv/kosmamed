<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();?>
<?

if($params["ERROR"]):?>
	<?foreach($params["ERROR"] as $error):?>
		<p><font class="errortext"><?=$error?></font></p>
	<?endforeach?>
<?else:?>
    <?= $params['qrB64Image'];?>
<?endif?>
