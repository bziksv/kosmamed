<?    
CModule::IncludeModule("iblock");

function priceFalse(){

	$idIB = 24;

	$arSelect = Array("ID", "NAME", "CATALOG_PRICE_SCALE_3");
	$arFilter = Array("IBLOCK_ID"=>$idIB, "CATALOG_PRICE_SCALE_3" => 0);

	$ress = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>2000), $arSelect);
	while($ob = $ress->GetNextElement())
	{
		$arFieldss = $ob->GetFields();

		$PRODUCT_ID = $arFieldss["ID"];

		$PRICE_TYPE_ID = 3;
		$arFields = Array(
			"PRODUCT_ID" => $PRODUCT_ID,
			"CATALOG_GROUP_ID" => $PRICE_TYPE_ID,
			"PRICE" => 888888888,
			"CURRENCY" => "RUB"
		);
		$res = CPrice::GetList(
			array(),
			array(
				"PRODUCT_ID" => $PRODUCT_ID,
				"CATALOG_GROUP_ID" => $PRICE_TYPE_ID
			)
		);
		if ($arr = $res->Fetch())
		{
			CPrice::Update($arr["ID"], $arFields);
		}
		else
		{
			CPrice::Add($arFields);
		}

	}


	$arFilter = Array("IBLOCK_ID"=>$idIB, "CATALOG_PRICE_SCALE_3" => false);

	$ress = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>2000), $arSelect);
	while($ob = $ress->GetNextElement())
	{
		$arFieldss = $ob->GetFields();

		$PRODUCT_ID = $arFieldss["ID"];

		$PRICE_TYPE_ID = 3;
		$arFields = Array(
			"PRODUCT_ID" => $PRODUCT_ID,
			"CATALOG_GROUP_ID" => $PRICE_TYPE_ID,
			"PRICE" => 888888888,
			"CURRENCY" => "RUB"
		);
		$res = CPrice::GetList(
			array(),
			array(
				"PRODUCT_ID" => $PRODUCT_ID,
				"CATALOG_GROUP_ID" => $PRICE_TYPE_ID
			)
		);
		if ($arr = $res->Fetch())
		{
			CPrice::Update($arr["ID"], $arFields);
		}
		else
		{
			CPrice::Add($arFields);
		}

	}


    return "priceFalse();";
}