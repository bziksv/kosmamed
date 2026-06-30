<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
   die();
}

use \Rbs\Moysklad\Config;

$getParams = [
   'mid=' . $mid_orig,
   'lang=' . $_REQUEST["lang"]
];

if(Config::getProfileId() > 0){
   $getParams[] = 'profile_id=' . Config::getProfileId();
}

$refreshUrl = '/bitrix/admin/settings.php?' . implode('&', $getParams);
header("Refresh:1; url=" . $refreshUrl);