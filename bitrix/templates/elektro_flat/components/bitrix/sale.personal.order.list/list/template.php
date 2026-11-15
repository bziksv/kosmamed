<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

global $USER;
if(!$USER->IsAuthorized())
	return;

global $arSetting;

if($_REQUEST["show_canceled"] == "Y")
	$page = "cancel";
elseif($_REQUEST["filter_history"] == "Y")
	$page = "all";
else
	$page = "active";?>

<div class="order-list">
	<div class="sort tabfilter order">
		<div class="sorttext"><?=GetMessage("STPOL_F_NAME")?></div>
		<a class="sortbutton active<?=($page == 'active' ? ' current' : '');?>" href="<?=($page != 'active' ? $arResult['CURRENT_PAGE'].'?filter_history=N' : 'javascript:void(0)');?>"><?=GetMessage("STPOL_CUR_ORDERS")?></a>
		<a class="sortbutton all<?=($page == 'all' ? ' current' : '');?>" href="<?=($page != 'all' ? $arResult['CURRENT_PAGE'].'?filter_history=Y' : 'javascript:void(0)');?>"><?=GetMessage("STPOL_ORDERS_HISTORY")?></a>
		<a class="sortbutton cancel<?=($page == 'cancel' ? ' current' : '');?>" href="<?=($page != 'cancel' ? $arResult['CURRENT_PAGE'].'?filter_history=Y&show_canceled=Y' : 'javascript:void(0)');?>"><?=GetMessage("STPOL_ORDERS_CANCELED")?></a>
	</div>

	<?if(!empty($arResult["ORDERS"])) {?>
		<div class="km-orders">
			<?foreach($arResult["ORDERS"] as $val) {
				$accountHashNumber = md5($val["ORDER"]["ACCOUNT_NUMBER"]);
				$canPay = ($val["ORDER"]["CANCELED"] != "Y" && $page != "cancel" && $val["ORDER"]["PAYED"] != "Y" && $val["ORDER"]["IS_ALLOW_PAY"] != "N");
				?>
				<article class="km-order" id="order-<?=$accountHashNumber?>">
					<div class="km-order__main">
						<button type="button" class="km-order__toggle" data-order-toggle aria-expanded="false" aria-controls="order-more-<?=$accountHashNumber?>" title="<?=GetMessage("STPOL_ORDER_NAME")?>">
							<i class="fa fa-chevron-down" aria-hidden="true"></i>
						</button>
						<div class="km-order__id">
							<a class="km-order__num" href="<?=htmlspecialcharsbx($val["ORDER"]["URL_TO_DETAIL"])?>"><?=htmlspecialcharsbx($val["ORDER"]["ACCOUNT_NUMBER"])?></a>
							<span class="km-order__date"><?=$val["ORDER"]["DATE_INSERT_FORMATED"];?></span>
						</div>
						<div class="km-order__status">
							<?if($val["ORDER"]["CANCELED"] == "Y") {?>
								<span class="item-status-d"><?=GetMessage("STPOL_ORDER_DELETE");?></span>
							<?} else {?>
								<span class="item-status-<?=toLower($val["ORDER"]["STATUS_ID"])?>"><?=htmlspecialcharsbx($arResult["INFO"]["STATUS"][$val["ORDER"]["STATUS_ID"]]["NAME"]);?></span>
							<?}?>
						</div>
						<div class="km-order__sum">
							<span class="km-order__sum-value"><?=$val["ORDER"]["FORMATED_PRICE"];?></span>
							<?if($arSetting["REFERENCE_PRICE"]["VALUE"] == "Y" && !empty($arSetting["REFERENCE_PRICE_COEF"]["VALUE"])) {?>
								<span class="km-order__sum-ref"><?=CCurrencyLang::CurrencyFormat($val["ORDER"]["PRICE"] * $arSetting["REFERENCE_PRICE_COEF"]["VALUE"], $val["ORDER"]["CURRENCY"], true);?></span>
							<?}?>
						</div>
					</div>

					<div class="km-order__pay">
						<?foreach ($val["PAYMENT"] as $payment) {
							$payHref = "";
							$payBlank = false;
							if ($canPay && $payment["PAID"] === "N" && $payment["IS_CASH"] !== "Y") {
								if ($payment["NEW_WINDOW"] === "Y" && $payment["PSA_ACTION_FILE"]) {
									$payHref = $payment["PSA_ACTION_FILE"];
									$payBlank = true;
								} else {
									$payHref = $val["ORDER"]["URL_TO_DETAIL"]."#pay";
								}
							}
							?>
							<?
							$payName = (string)$payment["PAY_SYSTEM_NAME"];
							$payNameLower = mb_strtolower($payName);
							if (mb_strpos($payNameLower, "qr") !== false) {
								$payName = "QR-код в приложении банка";
							} elseif (mb_strpos($payNameLower, "безнал") !== false) {
								$payName = "Безналичный расчёт";
							} elseif (mb_strpos($payNameLower, "налич") !== false) {
								$payName = "Наличные";
							} else {
								$payName = trim(preg_split('/[,(]/u', $payName, 2)[0]);
							}
							?>
							<div class="km-order__pay-line">
								<span class="km-order__pay-name"><?=htmlspecialcharsbx($payName);?></span>
								<?if($payment["PAID"] === "Y" || $val["ORDER"]["PAYED"] == "Y") {?>
									<span class="km-order__badge km-order__badge--yes">Оплачен</span>
								<?} else {?>
									<span class="km-order__badge km-order__badge--no">Не оплачен</span>
								<?}?>
								<?if($payHref) {?>
									<a class="km-order__pay-btn" href="<?=htmlspecialcharsbx($payHref)?>"<?=$payBlank ? ' target="_blank" rel="noopener"' : ''?>><?=GetMessage("STPOL_REPEAT_PAY")?></a>
								<?}?>
							</div>
						<?}?>
					</div>

					<div class="km-order__more" id="order-more-<?=$accountHashNumber?>" hidden>
						<ul class="km-order__items">
							<?foreach($val["BASKET_ITEMS"] as $arBasketItems) {?>
								<li class="km-order__item">
									<span class="km-order__thumb">
										<?if(is_array($arBasketItems["DETAIL_PICTURE"])) {?>
											<img src="<?=$arBasketItems["DETAIL_PICTURE"]["src"]?>" width="<?=$arBasketItems["DETAIL_PICTURE"]["width"]?>" height="<?=$arBasketItems["DETAIL_PICTURE"]["height"]?>" alt="" />
										<?} else {?>
											<img src="<?=SITE_TEMPLATE_PATH?>/images/no-photo.svg" width="48" height="48" alt="" />
										<?}?>
									</span>
									<span class="km-order__item-name">
										<?if(strlen($arBasketItems["DETAIL_PAGE_URL"]) > 0) {?>
											<a href="<?=$arBasketItems["DETAIL_PAGE_URL"]?>"><?=htmlspecialcharsbx($arBasketItems["NAME"])?></a>
										<?} else {
											echo htmlspecialcharsbx($arBasketItems["NAME"]);
										}
										if(!empty($arBasketItems["PROPS"])) {?>
											<span class="km-order__item-props">
												<?foreach($arBasketItems["PROPS"] as $props) {
													echo htmlspecialcharsbx($props["NAME"]).": ".htmlspecialcharsbx($props["VALUE"])." ";
												}?>
											</span>
										<?}?>
									</span>
									<span class="km-order__item-qty">
										<?=$arBasketItems["QUANTITY"];
										if(!empty($arBasketItems["MEASURE_TEXT"])) {
											echo " ".$arBasketItems["MEASURE_TEXT"];
										}?>
									</span>
									<span class="km-order__item-price"><?=CCurrencyLang::CurrencyFormat($arBasketItems["PRICE"] * $arBasketItems["QUANTITY"], $arBasketItems["CURRENCY"], true);?></span>
								</li>
							<?}
							if(IntVal($val["ORDER"]["DELIVERY_ID"]) > 0 && $val["ORDER"]["PRICE_DELIVERY"] > 0) {?>
								<li class="km-order__item km-order__item--delivery">
									<span class="km-order__item-name">
										Доставка<?if($arResult["INFO"]["DELIVERY"][$val["ORDER"]["DELIVERY_ID"]]["NAME"]):?>
											— <?=htmlspecialcharsbx($arResult["INFO"]["DELIVERY"][$val["ORDER"]["DELIVERY_ID"]]["NAME"])?>
										<?endif;?>
									</span>
									<span class="km-order__item-price"><?=CCurrencyLang::CurrencyFormat($val["ORDER"]["PRICE_DELIVERY"], $val["ORDER"]["CURRENCY"], true);?></span>
								</li>
							<?}?>
						</ul>

						<?if(!empty($val["ORDER"]["ORDER_PROPS"]) || strlen($val["ORDER"]["USER_DESCRIPTION"]) > 0) {?>
							<dl class="km-order__props">
								<?foreach($val["ORDER"]["ORDER_PROPS"] as $orderProps) {?>
									<dt><?=htmlspecialcharsbx($orderProps["NAME"])?></dt>
									<dd>
										<?if($orderProps["TYPE"] == "CHECKBOX") {
											echo ($orderProps["VALUE"] == "Y") ? GetMessage("STPOL_YES") : GetMessage("STPOL_NO");
										} else {
											echo htmlspecialcharsbx($orderProps["VALUE"]);
										}?>
									</dd>
								<?}
								if(strlen($val["ORDER"]["USER_DESCRIPTION"]) > 0) {?>
									<dt><?=GetMessage("STPOL_ORDER_USER_COMMENT")?></dt>
									<dd><?=htmlspecialcharsbx($val["ORDER"]["USER_DESCRIPTION"])?></dd>
								<?}?>
							</dl>
						<?}?>

						<div class="km-order__actions">
							<a class="km-order__action" href="<?=htmlspecialcharsbx($val["ORDER"]["URL_TO_COPY"])?>"><?=GetMessage("STPOL_REPEAT_ORDER")?></a>
							<?if($page != "all" && $page != "cancel"):?>
								<a class="km-order__action km-order__action--danger" href="<?=htmlspecialcharsbx($val["ORDER"]["URL_TO_CANCEL"])?>"><?=GetMessage("STPOL_CANCEL_ORDER")?></a>
							<?endif;?>
							<a class="km-order__action" href="<?=htmlspecialcharsbx($val["ORDER"]["URL_TO_DETAIL"])?>"><?=GetMessage("STPOL_DETAIL_ORDER")?></a>
						</div>
					</div>
				</article>
			<?}?>
		</div>
	<?} else {
		echo ShowNote(GetMessage("STPOL_NO_ORDERS_NEW"), "infotext");
	}?>
</div>

<?if(strlen($arResult["NAV_STRING"]) > 0) {?>
	<div class="navigation"><?=$arResult["NAV_STRING"]?></div>
<?}?>

<script type="text/javascript">
(function () {
	if (window.__kmOrderListToggleBound) {
		return;
	}
	window.__kmOrderListToggleBound = true;
	document.addEventListener("click", function (e) {
		var btn = e.target.closest && e.target.closest(".km-orders [data-order-toggle]");
		if (!btn) {
			return;
		}
		var card = btn.closest(".km-order");
		var more = card && card.querySelector(".km-order__more");
		if (!more) {
			return;
		}
		var open = more.hasAttribute("hidden");
		if (open) {
			more.removeAttribute("hidden");
		} else {
			more.setAttribute("hidden", "");
		}
		btn.setAttribute("aria-expanded", open ? "true" : "false");
		card.classList.toggle("is-open", open);
	});
})();
</script>
