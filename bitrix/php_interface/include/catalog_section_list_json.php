<?
CModule::IncludeModule('iblock');

function catalog_section_list_json()
{ 
  $IBLOCK_ID = 24;

  $arFilter = Array('IBLOCK_ID'=>$IBLOCK_ID, 'GLOBAL_ACTIVE'=>'Y');
  $db_list = CIBlockSection::GetList(Array($by=>$order), $arFilter, true);
  while($ar_result = $db_list->GetNext())
  {
    $sectionArr[$ar_result['NAME']] = $ar_result['ID'];
  }


    $ch = curl_init();
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_URL, 'https://vilmed.ru/catalog/data.json');
    $result = curl_exec($ch);
    $resultVilmedSec = json_decode($result, true);


    foreach ($resultVilmedSec as $key => $sections) {

        $resultVilmedSecNew[$key] = $sections;

    }


    $arSelect = Array("ID", "NAME", "PROPERTY_CML2_ARTICLE");
    $arFilter = Array("IBLOCK_ID"=>IntVal($IBLOCK_ID), "ACTIVE_DATE"=>"Y", "ACTIVE"=>"Y");
    $res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>250000), $arSelect);
    while($ob = $res->GetNextElement())
    {
     $arFields = $ob->GetFields();

        $ELEMENT_ID = $arFields["ID"];

        $db_old_groups = CIBlockElement::GetElementGroups($ELEMENT_ID, true);

        while($ar_group = $db_old_groups->Fetch()){
            $resultVilmedSecNew[$arFields["PROPERTY_CML2_ARTICLE_VALUE"]][] = $ar_group["NAME"];
            //$resultVilmedSecNew[$arFields["PROPERTY_CML2_ARTICLE_VALUE"]][] = 132;
        }

        $arSects = [];

        foreach($resultVilmedSecNew[$arFields["PROPERTY_CML2_ARTICLE_VALUE"]] as $nameSec){
            if($sectionArr[$nameSec]){
                $arSects[] = $sectionArr[$nameSec];
            }
        }

        if($arSects){
            CIBlockElement::SetElementSection($ELEMENT_ID, $arSects);
        }



    }

    return('catalog_section_list_json();');
}
