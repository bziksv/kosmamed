<?
use \Rbs\MoyskladStocks\Config;
use \Rbs\MoyskladStocks\Webhook;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
 
$inputData = file_get_contents('php://input');
$inputData = json_decode($inputData);

global $isHookScript;
$isHookScript = true;

if(
    !empty($inputData->events[0]->meta->href) && 
    \Bitrix\Main\Loader::includeModule('rbs.moyskladstocks')
){
    if(!Config::checkSalt()) return;
    Webhook::processHook($inputData);
}