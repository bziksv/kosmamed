<?    
CModule::IncludeModule("iblock");

function idToId(){

	$idIB = 24;

	$arSelect = Array("ID", "NAME");
	$arFilter = Array("IBLOCK_ID"=>$idIB, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", "PROPERTY_ID" => false);

	$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>1500), $arSelect);
	while($ob = $res->GetNextElement())
	{
	   $arFields = $ob->GetFields();

	   $idItem = $arFields["ID"];

	   CIBlockElement::SetPropertyValuesEx($idItem, $idIB, array("ID" => $idItem));

	}

    return "idToId();";
}