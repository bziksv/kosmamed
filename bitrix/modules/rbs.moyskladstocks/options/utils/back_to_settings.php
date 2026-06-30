<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
   die();
}

$getParams = [
   'mid=' . $mid_orig,
   'lang=' . $_REQUEST["lang"]
];

if(\Rbs\MoyskladStocks\Config::getProfileId() > 0){
   $getParams[] = 'profile_id=' . \Rbs\MoyskladStocks\Config::getProfileId();
}

$refreshUrl = '/bitrix/admin/settings.php?' . implode('&', $getParams);
header("Refresh:1; url=" . $refreshUrl);