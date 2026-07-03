<?    
CModule::IncludeModule("sale");

function idToHit(){

	$dbRes = \Bitrix\Sale\Order::getList([
	    'select' => [
	        'ID'
	    ],
		'filter' => [
			"@STATUS_ID" => ["F", "P"]
		],
		'order' => ['ID' => 'DESC']
	]);
		 
	while ($order = $dbRes->fetch())
	{
	    $dbResItems = \Bitrix\Sale\Basket::getList([
	        'select' => [
	            'PRODUCT_ID'
	        ],
	        'filter' => [
	            '=ORDER_ID' => $order['ID'],
	        ],
	    ]);
	    while ($item = $dbResItems->fetch()) {
	        $orderedItems[$item['PRODUCT_ID']] = $item['PRODUCT_ID'];
	    }
	}

	foreach($orderedItems as $item){
		CIBlockElement::SetPropertyValueCode($item, "SALELEADER", 1987);
	}
	
    return "idToHit();";
}