<?


use Sale\Handlers\Delivery\Dellin\AjaxService;
use DellinShipping\Kernel;

/**
 * Bitrix Framework
 * @global CMain $APPLICATION
 */

define("NO_KEEP_STATISTIC", true);
define("NO_AGENT_STATISTIC", true);
define("NO_AGENT_CHECK", true);
define("NOT_CHECK_PERMISSIONS", true);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$result = array("ERROR" => "");

if (!\Bitrix\Main\Loader::includeModule('sale'))
    $result["ERROR"] = "Error! Can't include module \"Sale\"";

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if($result["ERROR"] == '' && $saleModulePermissions >= "W" && check_bitrix_sessid())
{
    $action = isset($_REQUEST['action']) ? trim($_REQUEST['action']): '';

    if (\Bitrix\Main\Loader::includeModule('dellindev.shipment')) {


        switch ($action) {
            case "create_order":
                $shipment_id = (isset($_REQUEST['shipment_id']) && !empty($_REQUEST['shipment_id']))?
                    $_REQUEST['shipment_id'] :
                    AjaxService::LoggerInfo($action, 'shipment_id is null or empty');
                $order_id = (isset($_REQUEST['order_id']) && !empty($_REQUEST['order_id']))?
                    $_REQUEST['order_id'] :
                    AjaxService::LoggerInfo($action, 'order_id is null or empty');
                $is_order = (isset($_REQUEST['is_order']) && !empty($_REQUEST['is_order']))?
                    $_REQUEST['is_order'] :
                    AjaxService::LoggerInfo($action, 'is_order is null or empty');
                $produce_date = (isset($_REQUEST['produce_date']) && !empty($_REQUEST['produce_date']))?
                    $_REQUEST['produce_date'] :
                    AjaxService::LoggerInfo($action, 'produce_date is null or empty');
                $price_changed = (isset($_REQUEST['price_changed']) && !empty($_REQUEST['price_changed']))?
                    $_REQUEST['price_changed'] : false;
                $result = (!empty($shipment_id) || !empty($order_id) || !empty($produce_date))?
                    AjaxService::createOrder($order_id, $shipment_id, $produce_date, $is_order, $price_changed):
                    ['ERROR' => 'order_id || shipment_id || produce_date is null or empty'];
                break;
            default:
                $result["ERROR"] = "Error! Wrong action!";
                break;
        }

    } else {
        $result["ERROR"] = "Error! Can't include module \"dellindev.shipping\"";
    }
}
else
{
    if($result["ERROR"] == '')
        $result["ERROR"] = "Error! Access denied";
}



if(is_string($result)){
    $result = [
            'ERROR' => $result,
            'status' => 'error',
            'message'=> $result,
            'typeErrors' => 'network',
            'errors' => [$result]
        ];
}

if(is_array($result) && $result['status'] == 'error'){
    $result['ERROR'] = $result['message'];
}

if(is_array($result) && $result['status'] == 'success'){
        $result["RESULT"] = "OK";
}

if(is_array($result) && $result['status'] == 'price_changed'){
        $result["RESULT"] = "OK";
}

if(is_array($result) && $result['status'] == 'price_update'){
        $result["RESULT"] = "OK";
}

if(mb_strtolower(SITE_CHARSET) != 'utf-8')
    $result = \Bitrix\Main\Text\Encoding::convertEncoding($result, SITE_CHARSET, 'utf-8');

header('Content-Type: application/json');
echo json_encode($result);
\CMain::FinalActions();
die;