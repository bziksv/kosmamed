<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

$this->setFrameMode(true);

global $arSettings;

$isSiteClosed = false;
if(COption::GetOptionString("main", "site_stopped") == "Y" && !$USER->CanDoOperation("edit_other_settings"))
	$isSiteClosed = true;

$obName = 'ob'.preg_replace('/[^a-zA-Z0-9_]/', 'x', $this->GetEditAreaId($this->randString()));
$containerName = 'catalog-menu-'.$obName;

if(!$isSiteClosed && !empty($arResult)) {?>
	<?/*<script type="text/javascript">
		BX.message({
			MAIN_MENU: '<?=GetMessageJS("BM_MAIN_MENU")?>',
			CATALOG_FULL: '<?=GetMessageJS("BM_CATALOG_FULL")?>'
		});
		var <?=$obName?> = new JCCatalogMenu({
			container: '<?=$containerName?>'
		});
	</script>*/?>	
	<style>
		<?
		foreach($arResult as $arItem):
			if(!$arItem["SHOW"] || ($arItem["DEPTH_LEVEL"]>1 && $arItem["PARAMS"]["HIDE_MENU_INDEX"] && $arItem["PARAMS"]["URL"]=='product') ): 
				$arCode = explode('/',$arItem["LINK"]);
				?>
				.show_parent_<?=$arCode[2]?> div a:before{
					content:'';
				}
				.show_parent_<?=$arCode[2]?> a:before{
					content:'<?=$arItem["TEXT"]?>';
				}
				.show_<?=$arCode[2]?>:before{
					content:'<?=$arItem["TEXT"]?>';
				}
			<? endif;
		endforeach; ?>
	</style>

<?}?>