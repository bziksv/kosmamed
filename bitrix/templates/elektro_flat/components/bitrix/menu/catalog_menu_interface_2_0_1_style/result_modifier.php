<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if(count($arResult) < 1)
	return;

global $APPLICATION;
    
$dir = $APPLICATION->GetCurDir();
$arPage = explode('/',$dir);


$uf_iblock_id = 24;
$uf_name = Array("UF_NAME_MENU");

//$uf_section_id = $matches[1];

if(CModule::IncludeModule("iblock")): 
    $uf_arresult = CIBlockSection::GetList(Array("SORT"=>"­­ASC"), Array("IBLOCK_ID" => $uf_iblock_id), false, $uf_name);
    while($uf_value = $uf_arresult->GetNext()):
	$idMenu[$uf_value["CODE"]] = $uf_value["ID"];
        if($uf_value["UF_NAME_MENU"]): 
            $nameMenu[$uf_value["CODE"]] = $uf_value["UF_NAME_MENU"];
		
        endif;
    endwhile;



	$dbRes = CIBlockSection::GetList(array(), ["IBLOCK_ID" => $uf_iblock_id, "CODE" => $arPage[2]], false, array("ID", "UF_DELETE_INDEX"));
	if ($arCurSection = $dbRes->Fetch()){
	    $arResult[0]['PROPERTIES'] = $arCurSection;
	}

endif;


foreach($arResult as $key => $arItem) {	


    $arCode = explode('/',$arItem['LINK']);
    $thisCode = $arCode[count($arCode)-2];

    if($nameMenu[$thisCode]){
        $arResult[$key]["TEXT"] = $nameMenu[$thisCode];
	
    }
	$arResult[$key]["ID"] = $idMenu[$thisCode];

	$arResult[$key]["PARAMS"]["URL"] = $arPage[1];
	if($arItem["DEPTH_LEVEL"] == 2) {
		$pictureId = (int)($arItem["PARAMS"]["PICTURE"] ?? 0);
		if ($pictureId <= 0 && empty($arItem["PARAMS"]["ICON"]) && !empty($arResult[$key]["ID"]) && function_exists('kmSectionPreviewFileId')) {
			$pictureId = kmSectionPreviewFileId((int)$arResult[$key]["ID"]);
		}
		if ($pictureId > 0) {
			$preview = function_exists('kmSectionPreviewResize')
				? kmSectionPreviewResize($pictureId)
				: null;
			if ($preview === null) {
				$arFileTmp = CFile::ResizeImageGet(
					$pictureId,
					array("width" => 50, "height" => 50),
					BX_RESIZE_IMAGE_PROPORTIONAL,
					true
				);
				$preview = array(
					"SRC" => $arFileTmp["src"],
					"WIDTH" => $arFileTmp["width"],
					"HEIGHT" => $arFileTmp["height"],
				);
			}
			if (!empty($preview["SRC"])) {
				if (function_exists('kmAttachWebp')) {
					$preview = kmAttachWebp($preview);
				}
				$arResult[$key]["PICTURE"] = $preview;
			}
		}
	}
}

//SELECTED_ITEM//
if($arParams["CACHE_SELECTED_ITEMS"] != "Y") {
	$items = array();
	$selectedItem = false;
	foreach($arResult as $arItem) {
		$items[] = $arItem;		
		if($arItem["SELECTED"]) {
			$selectedItem = true;
			break;
		}
	}
	unset($arItem);
	
	if($selectedItem) {
		krsort($items);
		
		foreach($items as $arItem) {
			if($arItem["DEPTH_LEVEL"] == 1) {
				$arResult[$arItem["ITEM_INDEX"]]["SELECTED"] = true;
				break;
			}
		}
		unset($arItem, $items);
	}
	unset($selectedItem);
}?>