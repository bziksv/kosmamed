<?    
CModule::IncludeModule("iblock");

function hide_item_sect(){

    $iblockId = 24;

    $arSecHide = [];
	$arFilter = Array('IBLOCK_ID'=>$iblockId, 'GLOBAL_ACTIVE'=>'Y', '!UF_HIDE_ITEM' => false);
	$db_list = CIBlockSection::GetList(Array($by=>$order), $arFilter, true);
	while($ar_result = $db_list->GetNext())
	{
		$arSecHide[$ar_result['ID']] = $ar_result['ID'];
		$arSelectE = Array("ID", "IBLOCK_SECTION_ID");
		$arFilterE = Array("IBLOCK_ID"=>IntVal($iblockId), "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y", "SECTION_ID" => $ar_result['ID'], "INCLUDE_SUBSECTIONS" => "Y",);
		$resE = CIBlockElement::GetList(Array(), $arFilterE, false, Array("nPageSize"=>50), $arSelectE);
		while($obE = $resE->GetNextElement())
		{
			$arFields = $obE->GetFields();

			$arSecHide[$ar_result['IBLOCK_SECTION_ID']] = $ar_result['IBLOCK_SECTION_ID'];

            $el = new CIBlockElement;
            $arFieldsUP = [ "ACTIVE" => "N" ];
            if (!$el->Update($arFields['ID'], $arFieldsUP)) {
                echo "Ошибка: " . $el->LAST_ERROR;
            }

		}

	}

	foreach ($arSecHide as $key => $secHide) {
        $bs = new CIBlockSection;
        $arSecUP = [ "ACTIVE" => "N" ];
        $resSS = $bs->Update($secHide, $arSecUP);
	}

    return "hide_item_sect();";
}