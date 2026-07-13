<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");
CModule::IncludeModule("ipol.sdek");

if (
    isset($_POST['isdek_token']) &&
    (
        // Da widget count delivery call
        (in_array($_POST['isdek_action'],array('countDelivery','getCityPvz','getDataViaPointId')) && sdekHelper::checkTokens(sdekHelper::getWidgetToken(), $_POST['isdek_token'])) ||
        // Other calls
        ($_POST['isdek_action'] != 'countDelivery' && sdekHelper::checkTokens(sdekHelper::getModuleToken(), $_POST['isdek_token']))
    )
)
{
    sdekHelper::getAjaxAction($_POST['isdek_action'], $_POST['action']);
}
else
{
    header($_SERVER['SERVER_PROTOCOL'] . ' 405 Method Not Allowed');
    die();
}