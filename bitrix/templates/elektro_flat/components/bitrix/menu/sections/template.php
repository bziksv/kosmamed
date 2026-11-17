<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
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





$kmMenu = function_exists('kmMenuIndexState')
	? kmMenuIndexState()
	: ['mode' => 'off', 'open' => []];
$kmMenuOnProduct = ($kmMenu['mode'] !== 'off');
$kmOpenIds = array_map('intval', $kmMenu['open']);
$kmBranchOpen = false;

?>


<ul class="left-menu">
	<?$previousLevel = 0;	
	foreach($arResult["NEW_SORT_ASC"] as $arItem) {
		if($previousLevel && $arItem["DEPTH_LEVEL"] < $previousLevel) {
			echo str_repeat("</div></li>", ($previousLevel - $arItem["DEPTH_LEVEL"]));
		}
		if($arItem["DEPTH_LEVEL"] == 1) {
			$kmBranchOpen = false;
			if($kmMenu['mode'] === 'all') {
				$kmBranchOpen = true;
			} elseif($kmMenu['mode'] === 'parent' && in_array((int)$arItem['ID'], $kmOpenIds, true)) {
				$kmBranchOpen = true;
			}
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
		} elseif($arItem["DEPTH_LEVEL"] == 2) {
			?>
			<div class="catalog-section-child">
				<?if($kmMenuOnProduct && !$kmBranchOpen){
					echo '<!--noindex-->';
				}?>
				<?
				$arCode = explode('/',$arItem["LINK"]);
				?>
				<<? if(in_array($arItem["ID"], $JS_HIDE)): ?>span class="show_<?=$arCode[2]?>" data-replace-content="<?=$arItem['LINK']?>"<?else:?>a href="<?=$arItem['LINK']?>"<?endif;?> title="<?=$arItem['TEXT']?>">
					<span class="child">
						<span class="graph"<?
							if(!empty($arItem["PARAMS"]["ICON"])) {
								echo ' data-km-menu-icon="'.htmlspecialcharsbx($arItem['PARAMS']['ICON']).'"';
							} elseif(is_array($arItem["PICTURE"]) && !empty($arItem["PICTURE"]["SRC"])) {
								echo ' data-km-menu-src="'.htmlspecialcharsbx($arItem["PICTURE"]["SRC"]).'"';
								echo ' data-km-menu-w="'.(int)($arItem["PICTURE"]["WIDTH"] ?: 50).'"';
								echo ' data-km-menu-h="'.(int)($arItem["PICTURE"]["HEIGHT"] ?: 50).'"';
								echo ' data-km-menu-alt="'.htmlspecialcharsbx($arItem["TEXT"]).'"';
								if(!empty($arItem["PICTURE"]["SRC_WEBP"])) {
									echo ' data-km-menu-webp="'.htmlspecialcharsbx($arItem["PICTURE"]["SRC_WEBP"]).'"';
								}
							} else {
								echo ' data-km-menu-src="'.htmlspecialcharsbx(SITE_TEMPLATE_PATH.'/images/no-photo.svg').'"';
								echo ' data-km-menu-w="50" data-km-menu-h="50"';
								echo ' data-km-menu-alt="'.htmlspecialcharsbx($arItem["TEXT"]).'"';
							}
						?>></span>
						<span class="text-cont">
							<?
							$arCode = explode('/',$arItem["LINK"]);
							?>
							<?if($kmMenu['mode'] === 'all'):?>
							<span class="text opanALL"><?=$arItem["TEXT"]?></span>
							<?elseif($kmMenu['mode'] === 'parent' && $kmBranchOpen):?>
							<span class="text opanP"><?=$arItem["TEXT"]?></span>
							<?else:?>
							<span class="text" title="<?=htmlspecialcharsbx($arItem['TEXT'])?>"><?=htmlspecialcharsbx($arItem['TEXT'])?></span>
							<?endif;?>
						</span>
					</span>
<span data-href-content="<?=$arItem['LINK']?>"></span>
				</<? if(in_array($arItem["ID"], $JS_HIDE)): ?>span<?else:?>a<?endif;?>>
				<?if($kmMenuOnProduct && !$kmBranchOpen){
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
		<?if($arSetting["CATALOG_LOCATION"]["VALUE"] == "HEADER") {?>
		$("ul.left-menu").children(".parent").on({
			mouseenter: function() {
				var pos = $(this).position(),
					menu = $(this).closest(".left-menu"),
					dropdownMenu = $(this).children(".catalog-section-childs"),
					dropdownMenuLeft = pos.left + "px",
					dropdownMenuTop = pos.top + $(this).height() + 13 + "px",
					arrow = $(this).children(".arrow"),
					arrowLeft = pos.left + ($(this).width() / 2) + "px",
					arrowTop = pos.top + $(this).height() + 3 + "px";
				if (typeof kmLoadMenuIcons === "function") {
					kmLoadMenuIcons(dropdownMenu);
				}
				if(menu.width() - pos.left < dropdownMenu.width()) {
					dropdownMenu.css({"left": "auto", "right": "10px", "top": dropdownMenuTop, "z-index" : "9999"});
					arrow.css({"left": arrowLeft, "top": arrowTop});
				} else {
					dropdownMenu.css({"left": dropdownMenuLeft, "right": "auto", "top": dropdownMenuTop, "z-index" : "9999"});
					arrow.css({"left": arrowLeft, "top": arrowTop });
				}
				dropdownMenu.stop(true, true).delay(200).fadeIn(150);
				arrow.stop(true, true).delay(200).fadeIn(150);
			},
			mouseleave: function() {
				$(this).children(".catalog-section-childs").stop(true, true).delay(200).fadeOut(150);
				$(this).children(".arrow").stop(true, true).delay(200).fadeOut(150);
			}
		});
		<?}?>
	});
	//]]>
</script>