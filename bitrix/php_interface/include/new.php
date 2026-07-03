<?    
CModule::IncludeModule("iblock");

function idToNew(){

	$idIBc = 24;
	$idIBs = 25;
	$days = 30;

	$arSelect = Array("ID", "NAME", "PROPERTY_day");
	$arFilter = Array("IBLOCK_ID"=>IntVal($idIBs), "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
	$res = CIBlockElement::GetList(Array("ID"=>"DESC"), $arFilter, false, Array("nPageSize"=>1), $arSelect);
	if($ob = $res->GetNextElement())
	{
		$arFields = $ob->GetFields();

		$days = $arFields['PROPERTY_DAY_VALUE'];
	}

	$arSelect = Array("ID", "NAME", "CREATED_DATE");
	$arFilter = Array("IBLOCK_ID"=>IntVal($idIBc), "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y",">=DATE_CREATE"=>array(ConvertTimeStamp(time()-86400 * $days, "FULL")));
	$res = CIBlockElement::GetList(Array("CREATED_DATE"=>"DESC"), $arFilter, false, Array("nPageSize"=>99999), $arSelect);
	while($ob = $res->GetNextElement())
	{
		$arFields = $ob->GetFields();

		$newItems[] = $arFields["ID"];
	}

	foreach($newItems as $item){
		CIBlockElement::SetPropertyValueCode($item, "NEWPRODUCT", 1986);
	}


	$arSelect = Array("ID", "NAME", "CREATED_DATE");
	$arFilter = Array("IBLOCK_ID"=>IntVal($idIBc), "!PROPERTY_NEWPRODUCT_VALUE"=>false, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y","<DATE_CREATE"=>array(ConvertTimeStamp(time()-86400 * $days, "FULL")));
	$res = CIBlockElement::GetList(Array("CREATED_DATE"=>"DESC"), $arFilter, false, Array("nPageSize"=>99999), $arSelect);
	while($ob = $res->GetNextElement())
	{
		$arFields = $ob->GetFields();

		$newDelItems[] = $arFields["ID"];
	}

	foreach($newDelItems as $item){
		CIBlockElement::SetPropertyValueCode($item, "NEWPRODUCT", false);
	}
	
    return "idToNew();";
}