<?global $arAskPriceFilter;?>
<?$APPLICATION->IncludeComponent("altop:forms", "",
	array(
		"IBLOCK_TYPE" => "forms",
		"IBLOCK_ID" => "3",
		"ELEMENT_ID" => $arAskPriceFilter["ELEMENT_ID"],
		"ELEMENT_AREA_ID" => $arAskPriceFilter["ELEMENT_AREA_ID"],
		"ELEMENT_NAME" => $arAskPriceFilter["ELEMENT_NAME"],
		"ELEMENT_PRICE" => "",		
		"CACHE_TYPE" => "N",
		"CACHE_TIME" => "0"
	),
	false
);?>