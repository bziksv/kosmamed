<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

global $APPLICATION;

if(empty($arResult))
	return "";

$page = $APPLICATION->GetCurPage();

$arPage = explode('/',$page);

$strReturn = "";

$dTitle = [];
$dLevel = [];

$itemSize = count($arResult);
for($index = 0; $index < $itemSize; $index++) {
	$title = htmlspecialcharsex($arResult[$index]["TITLE"]);

	$nextRef = ($index < $itemSize-2 && $arResult[$index+1]["LINK"] <> "" ? " itemref='breadcrumb_".($index + 1)."'" : "");
	$child = ($index > 0 ? " itemprop='child'" : "");
	$arrow = ($index > 0 ? "<span class='breadcrumb__arrow'></span>" : "");

	if($arResult[$index]["LINK"] <> "" && $index != $itemSize-1) {


        if($arPage[1]=='product'){

        	if($index>2 && $arResult[$index]["LINK"] == '/product/') continue;

			if($index==2){
				$arSelect = Array("ID", "NAME");
				$arFilter = Array("IBLOCK_ID"=>24, "CODE"=>$arPage[2]);
				$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>1), $arSelect);
				while($ob = $res->GetNextElement()){ 
					$arFields = $ob->GetFields();  

					$db_old_groups = CIBlockElement::GetElementGroups($arFields["ID"], true);
					//$ar_new_groups = Array($NEW_GROUP_ID);
					while($ar_group = $db_old_groups->Fetch()){
						$ar_new_groups[] = $ar_group["ID"];
						//echo $ar_group["ID"].'<br>__888';

						$list = CIBlockSection::GetNavChain(false,$ar_group['ID'], array(), true);
						foreach ($list as $arSectionPath){
				            $arResult[$index]["LINK"] = '/catalog/'.$arSectionPath['CODE'].'/';
				            $title = $arSectionPath['NAME'];
				            if( !in_array($title, $dTitle) && !in_array($arSectionPath['DEPTH_LEVEL'], $dLevel)){
					  			$strReturn .= "<div class='breadcrumb__item' id='breadcrumb_".$index."' itemscope='' itemtype='".(CMain::IsHTTPS()? 'https' : 'http')."://data-vocabulary.org/Breadcrumb'".$child.$nextRef.">".$arrow."<a class='breadcrumb__link' href='".$arResult[$index]["LINK"]."' title='".$title."' itemprop='url'>".($index == 0 ? "<i class='fa fa-home breadcrumb__icon_main'></i>" : "")."<span class='".($index == 0 ? "breadcrumb__title_main" : "breadcrumb__title")."' itemprop='title'>".$title."</span></a></div>";   

					  			$dTitle[] = $title; 
					  			$dLevel[] = $arSectionPath['DEPTH_LEVEL'];  
					  		}

						//echo '<pre>';print_r($arSectionPath);echo '</pre>';
						}
					}
				}

				// foreach ($ar_new_groups as $key => $val_new_groups) {
				// 	if($val_new_groups){
				//         $arFilter = Array('IBLOCK_ID'=>24, 'GLOBAL_ACTIVE'=>'Y', 'ID'=>$val_new_groups);
				//         $db_list = CIBlockSection::GetList(Array($by=>$order), $arFilter, true);
				//         if($ar_result = $db_list->GetNext())
				//         {
				//         	//echo '<pre>';print_r($ar_result);echo '</pre>';
				//             $arResult[$index]["LINK"] = $ar_result['SECTION_PAGE_URL'];
				//             $title = $ar_result['NAME'];
				//             if( !in_array($title, $dTitle)){
				// 	  			$strReturn .= "<div class='breadcrumb__item' id='breadcrumb_".$index."' itemscope='' itemtype='".(CMain::IsHTTPS()? 'https' : 'http')."://data-vocabulary.org/Breadcrumb'".$child.$nextRef.">".$arrow."<a class='breadcrumb__link' href='".$arResult[$index]["LINK"]."' title='".$title."' itemprop='url'>".($index == 0 ? "<i class='fa fa-home breadcrumb__icon_main'></i>" : "")."<span class='".($index == 0 ? "breadcrumb__title_main" : "breadcrumb__title")."' itemprop='title'>".$title."</span></a></div>";   

				// 	  			$dTitle[] = $title;   
				// 	  		}
				//         }
				//     }
				// }
			}
			if($arResult[$index]["LINK"] == '/product/') $arResult[$index]["LINK"] = '/catalog/';
			if( $index==0){
				$strReturn .= "<div class='breadcrumb__item' id='breadcrumb_".$index."' itemscope='' itemtype='".(CMain::IsHTTPS()? 'https' : 'http')."://data-vocabulary.org/Breadcrumb'".$child.$nextRef.">".$arrow."<a class='breadcrumb__link' href='".$arResult[$index]["LINK"]."' title='".$title."' itemprop='url'>".($index == 0 ? "<i class='fa fa-home breadcrumb__icon_main'></i>" : "")."<span class='".($index == 0 ? "breadcrumb__title_main" : "breadcrumb__title")."' itemprop='title'>".$title."</span></a></div>";

				$dTitle[] = $title; 
			}
        }else{
			$strReturn .= "<div class='breadcrumb__item' id='breadcrumb_".$index."' itemscope='' itemtype='".(CMain::IsHTTPS()? 'https' : 'http')."://data-vocabulary.org/Breadcrumb'".$child.$nextRef.">".$arrow."<a class='breadcrumb__link' href='".$arResult[$index]["LINK"]."' title='".$title."' itemprop='url'>".($index == 0 ? "<i class='fa fa-home breadcrumb__icon_main'></i>" : "")."<span class='".($index == 0 ? "breadcrumb__title_main" : "breadcrumb__title")."' itemprop='title'>".$title."</span></a></div>";
		}

	} else {
		$strReturn .= "<div class='breadcrumb__item'>".$arrow.($index == 0 ? "<i class='fa fa-home breadcrumb__icon_main'></i>" : "")."<span class='".($index == 0 ? "breadcrumb__title_main" : "breadcrumb__title")."'>".$title."</span></div>";
	}
}

return $strReturn;