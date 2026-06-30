<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$iblockId = array();
$sectionId = array();
$sectionItem = array();

foreach($arResult["SECTIONS"] as $key => $arSection) {	
	$iblockId[] = $arSection["IBLOCK_ID"];
	$sectionId[] = $arSection["ID"];
}

$iblockId = array_unique($iblockId);

/***SECTIONS_ITEMS***/
$rsElements = CIBlockElement::GetList(array("SORT" => "ASC", "NAME" => "ASC"), array("IBLOCK_ID" => $iblockId, "SECTION_ID" => $sectionId, "INCLUDE_SUBSECTIONS" => "N", "ACTIVE" => "Y"), false, false, array("ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "NAME", "PREVIEW_TEXT", "PROPERTY_LOGO_1", "PROPERTY_LOGO_2", "PROPERTY_LOGO_3", "PROPERTY_URL"));
while($obElement = $rsElements->GetNextElement()) {
	$arItem = $obElement->GetFields();	

	// echo"<pre>"; print_r($arItem); echo"</pre>";
	if(isset($arItem["PROPERTY_LOGO_1_VALUE"]) && $arItem["PROPERTY_LOGO_1_VALUE"] > 0) {
		$arFileTmp = CFile::ResizeImageGet(
			$arItem["PROPERTY_LOGO_1_VALUE"],
			array("width" => 66, "height" => 30),
			BX_RESIZE_IMAGE_PROPORTIONAL,
			true
		);

		$arItem["LOGO_1"] = array(
			"SRC" => $arFileTmp["src"],
			"WIDTH" => $arFileTmp["width"],
			"HEIGHT" => $arFileTmp["height"],
		);				
	}

	if(isset($arItem["PROPERTY_LOGO_2_VALUE"]) && $arItem["PROPERTY_LOGO_2_VALUE"] > 0) {
		$arFileTmp = CFile::ResizeImageGet(
			$arItem["PROPERTY_LOGO_2_VALUE"],
			array("width" => 66, "height" => 30),
			BX_RESIZE_IMAGE_PROPORTIONAL,
			true
		);

		$arItem["LOGO_2"] = array(
			"SRC" => $arFileTmp["src"],
			"WIDTH" => $arFileTmp["width"],
			"HEIGHT" => $arFileTmp["height"],
		);
	}

    if(isset($arItem["PROPERTY_LOGO_3_VALUE"]) && $arItem["PROPERTY_LOGO_3_VALUE"] > 0) {
        $arFileTmp = CFile::ResizeImageGet(
            $arItem["PROPERTY_LOGO_3_VALUE"],
            array("width" => 66, "height" => 30),
            BX_RESIZE_IMAGE_PROPORTIONAL,
            true
        );

        $arItem["LOGO_3"] = array(
            "SRC" => $arFileTmp["src"],
            "WIDTH" => $arFileTmp["width"],
            "HEIGHT" => $arFileTmp["height"],
        );
    }
	$sectionItem[$arItem["IBLOCK_SECTION_ID"]][] = $arItem;
}



$arrCheckId=[];
function checkIdElement($arr,$elemetn_id ){
    foreach ($arr as $elem){
        if($elemetn_id==$elem){
            return true;
        }
    }
    return false;
}

$sectionItemSort=[];
foreach ($sectionItem as $key => $arElements){
    if(!empty($arElements)){
        $count=1;
        foreach ($arElements as $element){
            if(checkIdElement($arrCheckId, $element["ID"])){
                $sectionItemSort[$key][0]["LOGO_3"][$count]=$element["LOGO_3"];
                $count++;
            }else{
                $arrCheckId[]=$element["ID"];
                $sectionItemSort[$key][]=$element;
                if(!empty($element["LOGO_3"])){
                    unset($sectionItemSort[$key][0]["LOGO_3"]);
                    $sectionItemSort[$key][0]["LOGO_3"][0]=$element["LOGO_3"];
                }
            }
        }
    }
}

foreach($arResult["SECTIONS"] as $key => $arSection) {
	if(!empty($sectionItemSort[$arSection["ID"]])) {
		$arResult["SECTIONS"][$key]["ITEMS"] = $sectionItemSort[$arSection["ID"]];
	}
}?>