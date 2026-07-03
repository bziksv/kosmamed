<?
CModule::IncludeModule("iblock");
CModule::IncludeModule("catalog");

function upQuantitySection($iNumPage=1){

  $arSection = [];
  $arFilter = Array('IBLOCK_ID'=>24, 'GLOBAL_ACTIVE'=>'Y');
  $db_list = CIBlockSection::GetList(Array($by=>$order), $arFilter, true, array("UF_QUANTITY"),Array("nPageSize"=>500, "iNumPage"=>$iNumPage));

  while($ar_result = $db_list->GetNext())
  {

      $arSection[] = $ar_result['ID'];
      
      $bs = new CIBlockSection;

      $arFields = Array(
         "UF_QUANTITY" => false
      );

      $arSelectE = Array("ID", "NAME", "CATALOG_QUANTITY");
      $arFilterE = Array("IBLOCK_ID"=>24, "SECTION_ID"=>$ar_result["ID"], "INCLUDE_SUBSECTIONS" => "Y", ">CATALOG_QUANTITY"=>0, "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
      $resE = CIBlockElement::GetList(Array(), $arFilterE, false, Array("nPageSize"=>1), $arSelectE);
      if($obE = $resE->GetNextElement())
      {
       $arFieldsE = $obE->GetFields();
        $arFields = Array(
           "UF_QUANTITY" => true
        );
      }

      $bs->Update($ar_result['ID'], $arFields);

  }



    if(count($arSection)==500){
        $iNumPage++;
    }else{
        $iNumPage = 1;
    }
    return "upQuantitySection(".$iNumPage.");";
}