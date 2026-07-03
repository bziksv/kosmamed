<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>

<div class="delay_line <?if(isset($arResult["QUANTITY"]) && $arResult["QUANTITY"] > 0):?>mob_b<?endif;?>">
	<?$frame = $this->createFrame("delay")->begin();?>
		<a href="<?=$arParams["PATH_TO_DELAY"]?>" class="delay" title="<?=GetMessage("MY_DELAY")?>" rel="nofollow">
			<i class="fa fa-heart"></i>
			<span class="text"><?=GetMessage("MY_DELAY")?></span>
			<span class="qnt_cont<?=(IntVal($arResult['QUANTITY']) <= 0 ? ' qnt_cont--empty' : '')?>">
				<span class="qnt"><?=(int)$arResult["QUANTITY"]?></span>
			</span>
		</a>
	<?$frame->end();?>
</div>