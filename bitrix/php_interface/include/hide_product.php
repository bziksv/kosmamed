<?    
CModule::IncludeModule("iblock");

function hideProduct($iNumPage=1){
	//$iNumPage=1;

	$iblockId = 24; 

	$dbItems = CIBlockElement::GetList(
	    [],
	    ["IBLOCK_ID" => $iblockId, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", "PROPERTY_hide_VALUE" => 'y'],
	    false,
	    ["nPageSize"=>500, "iNumPage"=>$iNumPage],
	    ["ID", "NAME"]
	);

	while ($item = $dbItems->Fetch()) {
	    $arEtamId[] = $item["ID"];
	}

	foreach ($arEtamId as $key => $value) {

	    $el = new CIBlockElement;
	    $arLoadProductArray = Array(
	      "ACTIVE"         => "N"
	    );
	    $res = $el->Update($value, $arLoadProductArray);
	    
	}
    return "hideProduct();";
}