<?    
CModule::IncludeModule("iblock");

use Bitrix\Main;
Main\EventManager::getInstance()->addEventHandler(
    'sale',
    'OnSaleOrderSaved',
    'OnSaleOrderSavedFunction'
);

function OnSaleOrderSavedFunction(Main\Event $event)
{
    require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/csv_data.php");

    /** @var Order $order */
    $order = $event->getParameter("ENTITY");
	
	if($order->isPaid() != "PAID")
		return;
	
    $orderId = $order->getId();

    $propertyCollection = $order->getPropertyCollection();
    $properties = $propertyCollection->getArray();

	$ym_uid = "";
    $email = "";
    $phone = "";

    foreach($properties["properties"] as $prop)
    {
		if($prop["CODE"] == "YM_CODE")
            $ym_uid = current($prop["VALUE"]);
			
        if($prop["CODE"] == "EMAIL")
            $email = implode(", ", $prop["VALUE"]);

        if($prop["CODE"] == "PHONE")
            $phone = str_replace([" ", "-", "+", "(", ")"], "", implode(", ", $prop["VALUE"]));
    }

    $filePath = $_SERVER['DOCUMENT_ROOT'] . '/' . 'yandex_orders_conv.csv';
    $csv = new CCSVData();

    $csv->LoadFile($filePath);
    if(!$csv->Fetch()){
        $csv->SaveFile($filePath, ['create_date_time', 'client_ids', 'emails', 'phones']);
    }
	
	$date = $order->getDateInsert();

    $create_date_time = $date->format("d.m.Y H:i:s");
    $order_id = $orderId;
    $client_ids = $ym_uid ?: "";
    		
	$csv->SaveFile($filePath, [$create_date_time, $client_ids, $email, $phone]);
}