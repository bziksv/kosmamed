<?php
use \Rbs\Moysklad\Config;
use \Rbs\Moysklad\Customerorder;
use \Rbs\Moysklad\Webhook;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
 
$inputData = file_get_contents('php://input');
$inputData = json_decode($inputData);

if(
    !empty($inputData->events[0]->meta->href) && 
    \Bitrix\Main\Loader::includeModule('rbs.moysklad')
){
    Config::setIgnorePushToMs(true);
    if(!Config::checkSalt()) return;
    Webhook::processHook($inputData);
}