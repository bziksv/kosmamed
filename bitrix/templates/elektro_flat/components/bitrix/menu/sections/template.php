<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
use Bitrix\Iblock\SectionTable;
use Bitrix\Iblock\ElementTable;
use Bitrix\Main\Loader;
use Bitrix\Main\SystemException;
 
Loader::includeModule('iblock');
$this->setFrameMode(true);

if(count($arResult) < 1)
	return;

global $arSetting;
global $APPLICATION;
    
$dir = $APPLICATION->GetCurDir();
$arPage = explode('/',$dir);

$jsHide = explode("\r\n", str_replace([' ', '.'], '', $arResult[0]['PROPERTIES']['UF_DELETE_INDEX']));

foreach($jsHide as $arJsHide){

	$valJHide = explode(";",$arJsHide);
		
	$JS_HIDE[] = $valJHide[0];
}





//s2

$arSelect = Array("ID");
$arFilter = Array("IBLOCK_ID"=>24, "CODE" => $arPage[2]);
$res = CIBlockElement::GetList(Array(), $arFilter, false, Array("nPageSize"=>1), $arSelect);
if($ob = $res->GetNextElement())
{
	$arFields = $ob->GetFields();
	$elementId = $arFields["ID"];
}
 
$opanP = false;
$opanALL = false;

$arResult["AR_SEC"] = [];

$element = ElementTable::getRow([
    'select' => [
        'IBLOCK_SECTION_ID',
    ],
    'filter' => [
        '=ID' => $elementId,
    ],
]);

if ($element !== null) {

    $parentSections = [];
 
    $parentSectionIterator = SectionTable::getList([
        'select' => [
            'SECTION_ID' => 'SECTION_SECTION.ID',
            'IBLOCK_SECTION_ID' => 'SECTION_SECTION.IBLOCK_SECTION_ID',
        ],
        'filter' => [
            '=ID' => $element['IBLOCK_SECTION_ID'],
        ],
        'runtime' => [
            'SECTION_SECTION' => [
                'data_type' => '\Bitrix\Iblock\SectionTable',
                'reference' => [
                    '=this.IBLOCK_ID' => 'ref.IBLOCK_ID',
                    '>=this.LEFT_MARGIN' => 'ref.LEFT_MARGIN',
                    '<=this.RIGHT_MARGIN' => 'ref.RIGHT_MARGIN',
                ],
                'join_type' => 'inner'
            ],
        ],
    ]);
	
    while ($parentSection = $parentSectionIterator->fetch()) {
        $arResult["AR_SEC"][] = $parentSection['SECTION_ID'];

    }
    foreach ($arResult["AR_SEC"] as $key => $valueS) {

		$dbRes = CIBlockSection::GetList(array(), ["IBLOCK_ID" => 24, "ID" => $valueS], false, array("ID", "UF_INDEX_MENU_P"));
		if ($arCurSection = $dbRes->Fetch()){
			if($arCurSection["UF_INDEX_MENU_P"] == 10){
				$opanP = true;
			}
			if($arCurSection["UF_INDEX_MENU_P"] == 11){
				$opanALL = true;
			}
			//echo '<pre>';print_r($arCurSection);echo '</pre>';
		}
    }

 	
   // echo '<pre>';print_r($arResult["AR_SEC"]);echo '</pre>';
 
}

//s2

?>


<ul class="left-menu">
	<?$previousLevel = 0;	
	foreach($arResult["NEW_SORT_ASC"] as $arItem) {
		if($previousLevel && $arItem["DEPTH_LEVEL"] < $previousLevel) {
			echo str_repeat("</div></li>", ($previousLevel - $arItem["DEPTH_LEVEL"]));
		}
		if($arItem["DEPTH_LEVEL"] == 1) {
			if($arItem["IS_PARENT"]) {?>
				<?
				$arCode = explode('/',$arItem["LINK"]);
				?>
				<li class="parent<?=($arItem['SELECTED'] ? ' selected' : '')?> <? if(in_array($arItem["ID"], $JS_HIDE)) echo 'show_parent_'.$arCode[2]; ?>">

					<<? if(in_array($arItem["ID"], $JS_HIDE)): ?>span data-replace-content="<?=$arItem['LINK']?>" data-hide-text1="<?=$arItem["TEXT"].($arSetting["CATALOG_LOCATION"]["VALUE"] == "LEFT" ? "<span class='arrow'></span>" : "")?>"<?else:?>a href="<?=$arItem['LINK']?>"<?endif;?>>
					<?if(!in_array($arItem["ID"], $JS_HIDE)):?> 
						<?=$arItem["TEXT"].($arSetting["CATALOG_LOCATION"]["VALUE"] == "LEFT" ? "<span class='arrow'></span>" : "")?>
					<?else:?>
						<?=($arSetting["CATALOG_LOCATION"]["VALUE"] == "LEFT" ? "<span class='arrow'></span>" : "")?>
					<?endif;?>
<span data-href-content="<?=$arItem['LINK']?>"></span>
</<? if(in_array($arItem["ID"], $JS_HIDE)): ?>span<?else:?>a<?endif;?>>
					<?=($arSetting["CATALOG_LOCATION"]["VALUE"] == "HEADER" ? "<span class='arrow'></span>" : "")?>
					<div class="catalog-section-childs">
			<?} else {?>
							<?
							$arCode = explode('/',$arItem["LINK"]);
							?>
				<li
					<?if($arItem["SELECTED"]){
						echo "class='selected ";
						if(in_array($arItem["ID"], $JS_HIDE)) echo 'show_parent_'.$arCode[2];
						echo "'";
					}else{
						if(in_array($arItem["ID"], $JS_HIDE)) echo 'class="show_parent_'.$arCode[2].'"';
					}?>
				>
							<?
							$arCode = explode('/',$arItem["LINK"]);
							?>
					<<? if(in_array($arItem["ID"], $JS_HIDE)): ?>span class="show_<?=$arCode[2]?>" data-replace-content="<?=$arItem['LINK']?>" <?else:?>a href="<?=$arItem['LINK']?>"<?endif;?>><? if(!in_array($arItem["ID"], $JS_HIDE)) echo $arItem["TEXT"];?>
<span data-href-content="<?=$arItem['LINK']?>" ></span>
</<? if(in_array($arItem["ID"], $JS_HIDE)): ?>span<?else:?>a<?endif;?>>
				</li>
			<?}
			if(in_array($arItem['ID'], $arResult['AR_SEC']) ){

				$dbRes = CIBlockSection::GetList(array(), ["IBLOCK_ID" => 24, "ID" => $arItem['ID']], false, array("ID", "UF_INDEX_MENU_P"));
				if ($arCurSection = $dbRes->Fetch()){
					if($arCurSection["UF_INDEX_MENU_P"] == 10){
						$opanP = true;
					}
				}

			}else{
				$opanP = false;
			}
		} elseif($arItem["DEPTH_LEVEL"] == 2) {


			// if(in_array($arItem['ID'], $arResult['AR_SEC']) ){

			// 	$dbRes = CIBlockSection::GetList(array(), ["IBLOCK_ID" => 24, "ID" => $arItem['ID']], false, array("ID", "UF_INDEX_MENU_P"));
			// 	if ($arCurSection = $dbRes->Fetch()){
			// 		if($arCurSection["UF_INDEX_MENU_P"] == 10){
			// 			$opanP = true;
			// 		}
			// 		if($arCurSection["UF_INDEX_MENU_P"] == 11){
			// 			$opanALL = true;
			// 		}
			// 	}
			// }

			?>
			<div class="catalog-section-child">
				<?if($arItem["PARAMS"]["URL"]=='product' && !$opanP && !$opanALL){
					echo '<!--noindex-->';
				}?>
				<?
				$arCode = explode('/',$arItem["LINK"]);
				?>
				<<? if(in_array($arItem["ID"], $JS_HIDE)): ?>span class="show_<?=$arCode[2]?>" data-replace-content="<?=$arItem['LINK']?>"<?else:?>a href="<?=$arItem['LINK']?>"<?endif;?> title="<?=$arItem['TEXT']?>">
					<span class="child">
						<span class="graph">
							<?if(!empty($arItem["PARAMS"]["ICON"])) {?>
								<i class="<?=$arItem['PARAMS']['ICON']?>" aria-hidden="true"></i>
							<?} elseif(is_array($arItem["PICTURE"])) {?>								
								<img src="<?=$arItem['PICTURE']['SRC']?>" width="<?=$arItem['PICTURE']['WIDTH']?>" height="<?=$arItem['PICTURE']['HEIGHT']?>" alt="<?=$arItem['TEXT']?>" title="<?=$arItem['TEXT']?>" />
							<?} else {?>
								<img src="<?=SITE_TEMPLATE_PATH?>/images/no-photo.svg" width="50" height="50" alt="<?=$arItem['TEXT']?>" title="<?=$arItem['TEXT']?>" />
							<?}?>
						</span>
						<span class="text-cont">
							<?
							$arCode = explode('/',$arItem["LINK"]);
							?>
							<?if($opanALL):?>
							<span class="text opanALL"><?echo $arItem["TEXT"]?></span>
							<?elseif($opanP):?>
							<span class="text opanP"><?echo $arItem["TEXT"]?></span>
							<?else:?>
							<span class="text  <? if(in_array($arItem["ID"], $JS_HIDE) || $arItem["PARAMS"]["URL"]=='product') echo 'show_'.$arCode[2]; ?> "  ><? if(!in_array($arItem["ID"], $JS_HIDE) && $arItem["PARAMS"]["URL"]!='product') echo $arItem["TEXT"]?></span>
							<?endif;?>
						</span>
					</span>
<span data-href-content="<?=$arItem['LINK']?>"></span>
				</<? if(in_array($arItem["ID"], $JS_HIDE)): ?>span<?else:?>a<?endif;?>>
				<?if($arItem["PARAMS"]["URL"]=='product' && !$opanP && !$opanALL){
					echo '<!--/noindex-->';
				}?>
			</div>
		<?} else {
			continue;
		}
		$previousLevel = $arItem["DEPTH_LEVEL"];		
	}
	if($previousLevel > 1) {
		echo str_repeat("</div></li>", ($previousLevel-1));
	}?>
</ul>

<script type="text/javascript">
	//<![CDATA[
	$(function() {
		<?if($arSetting["CATALOG_LOCATION"]["VALUE"] == "HEADER"):?>			
			$(".top-catalog ul.left-menu").moreMenu();
		<?endif;?>
		$("ul.left-menu").children(".parent").on({
			mouseenter: function() {
				<?if($arSetting["CATALOG_LOCATION"]["VALUE"] == "LEFT") {?>
					var pos = $(this).position(),
						dropdownMenu = $(this).children(".catalog-section-childs"),
						dropdownMenuLeft = pos.left + $(this).width() + 9 + "px",
						dropdownMenuTop = pos.top - 5 + "px";
					if(pos.top + dropdownMenu.outerHeight() > $(window).height() + $(window).scrollTop() - 46) {
						dropdownMenuTop = pos.top - dropdownMenu.outerHeight() + $(this).outerHeight() + 5;
						dropdownMenuTop = (dropdownMenuTop < 0 ? $(window).scrollTop() : dropdownMenuTop) + "px";
					}
					dropdownMenu.css({"left": dropdownMenuLeft, "top": dropdownMenuTop, "z-index" : "9999"});
					dropdownMenu.stop(true, true).delay(200).fadeIn(150);
				<?} elseif($arSetting["CATALOG_LOCATION"]["VALUE"] == "HEADER") {?>
					var pos = $(this).position(),
						menu = $(this).closest(".left-menu"),
						dropdownMenu = $(this).children(".catalog-section-childs"),
						dropdownMenuLeft = pos.left + "px",
						dropdownMenuTop = pos.top + $(this).height() + 13 + "px",
						arrow = $(this).children(".arrow"),
						arrowLeft = pos.left + ($(this).width() / 2) + "px",
						arrowTop = pos.top + $(this).height() + 3 + "px";
					if(menu.width() - pos.left < dropdownMenu.width()) {
						dropdownMenu.css({"left": "auto", "right": "10px", "top": dropdownMenuTop, "z-index" : "9999"});
						arrow.css({"left": arrowLeft, "top": arrowTop});
					} else {
						dropdownMenu.css({"left": dropdownMenuLeft, "right": "auto", "top": dropdownMenuTop, "z-index" : "9999"});
						arrow.css({"left": arrowLeft, "top": arrowTop });
					}
					dropdownMenu.stop(true, true).delay(200).fadeIn(150);
					arrow.stop(true, true).delay(200).fadeIn(150);
				<?}?>
			},
			mouseleave: function() {
				$(this).children(".catalog-section-childs").stop(true, true).delay(200).fadeOut(150);
				<?if($arSetting["CATALOG_LOCATION"]["VALUE"] == "HEADER") {?>
					$(this).children(".arrow").stop(true, true).delay(200).fadeOut(150);
				<?}?>
			}
		});
	});
	//]]>
</script>