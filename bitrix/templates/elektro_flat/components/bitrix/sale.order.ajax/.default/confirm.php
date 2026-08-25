<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Localization\Loc;

/**
 * @var array $arParams
 * @var array $arResult
 * @var CMain $APPLICATION
 */

if ($arParams["SET_TITLE"] == "Y")
{
	$APPLICATION->SetTitle(Loc::getMessage("SOA_ORDER_COMPLETE"));
}
?>

<? if (!empty($arResult["ORDER"])): ?>
	<?
	$orderNumber = htmlspecialcharsbx($arResult["ORDER"]["ACCOUNT_NUMBER"]);
	$orderDate = $arResult["ORDER"]["DATE_INSERT"]->toUserTime()->format('d.m.Y H:i');
	$personalLink = htmlspecialcharsbx($arParams['PATH_TO_PERSONAL']);
	?>
	<div class="km-order-confirm">
		<div class="km-order-confirm__success">
			<div class="km-order-confirm__success-icon" aria-hidden="true">
				<svg width="28" height="28" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
					<path d="M9 12.5l2.2 2.2L16 10" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"/>
					<circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2"/>
				</svg>
			</div>
			<div class="km-order-confirm__success-body">
				<div class="km-order-confirm__eyebrow">Заказ оформлен</div>
				<h2 class="km-order-confirm__title">№<?=$orderNumber?></h2>
				<p class="km-order-confirm__meta">от <?=$orderDate?></p>
				<? if ($arParams['NO_PERSONAL'] !== 'Y'): ?>
					<p class="km-order-confirm__hint">
						Статус можно смотреть в
						<a href="<?=$personalLink?>">личном кабинете</a>
						— войдите с логином и паролем с сайта.
					</p>
				<? endif; ?>
				<div class="km-order-confirm__actions">
					<? if ($arParams['NO_PERSONAL'] !== 'Y'): ?>
						<a class="km-order-confirm__btn km-order-confirm__btn--primary" href="<?=$personalLink?>">Мои заказы</a>
					<? endif; ?>
					<a class="km-order-confirm__btn km-order-confirm__btn--ghost" href="/catalog/">В каталог</a>
				</div>
			</div>
		</div>

		<?
		if ($arResult["ORDER"]["IS_ALLOW_PAY"] === 'Y')
		{
			if (!empty($arResult["PAYMENT"]))
			{
				foreach ($arResult["PAYMENT"] as $payment)
				{
					if ($payment["PAID"] == 'Y')
					{
						continue;
					}

					if (
						empty($arResult['PAY_SYSTEM_LIST'])
						|| !array_key_exists($payment["PAY_SYSTEM_ID"], $arResult['PAY_SYSTEM_LIST'])
					)
					{
						?><p class="km-order-confirm__error"><?=Loc::getMessage("SOA_ORDER_PS_ERROR")?></p><?
						continue;
					}

					$arPaySystem = $arResult['PAY_SYSTEM_LIST_BY_PAYMENT_ID'][$payment["ID"]];
					if (!empty($arPaySystem["ERROR"]))
					{
						?><p class="km-order-confirm__error"><?=Loc::getMessage("SOA_ORDER_PS_ERROR")?></p><?
						continue;
					}

					$isQrPay = (
						stripos((string)$arPaySystem["ACTION_FILE"], 'qr') !== false
						|| stripos((string)$arPaySystem["NAME"], 'QR') !== false
						|| (is_string($arPaySystem["BUFFERED_OUTPUT"] ?? null) && strpos($arPaySystem["BUFFERED_OUTPUT"], 'km-qr-pay') !== false)
					);
					?>
					<section class="km-order-confirm__pay<?=$isQrPay ? ' km-order-confirm__pay--qr' : ''?>">
						<? if (!$isQrPay): ?>
							<div class="km-order-confirm__pay-head">
								<div class="km-order-confirm__pay-label"><?=Loc::getMessage("SOA_PAY")?></div>
								<? if (!empty($arPaySystem["LOGOTIP"])): ?>
									<div class="km-order-confirm__pay-logo">
										<?=CFile::ShowImage($arPaySystem["LOGOTIP"], 100, 100, "border=0", "", false)?>
									</div>
								<? endif; ?>
								<div class="km-order-confirm__pay-name"><?=htmlspecialcharsbx($arPaySystem["NAME"])?></div>
							</div>
						<? endif; ?>

						<div class="km-order-confirm__pay-body">
							<? if ($arPaySystem["ACTION_FILE"] <> '' && $arPaySystem["NEW_WINDOW"] == "Y" && $arPaySystem["IS_CASH"] != "Y"): ?>
								<?
								$orderAccountNumber = urlencode(urlencode($arResult["ORDER"]["ACCOUNT_NUMBER"]));
								$paymentAccountNumber = $payment["ACCOUNT_NUMBER"];
								?>
								<script>
									window.open('<?=$arParams["PATH_TO_PAYMENT"]?>?ORDER_ID=<?=$orderAccountNumber?>&PAYMENT_ID=<?=$paymentAccountNumber?>');
								</script>
								<p class="km-order-confirm__hint">
									<?=Loc::getMessage("SOA_PAY_LINK", array("#LINK#" => $arParams["PATH_TO_PAYMENT"]."?ORDER_ID=".$orderAccountNumber."&PAYMENT_ID=".$paymentAccountNumber))?>
								</p>
								<? if (CSalePdf::isPdfAvailable() && $arPaySystem['IS_AFFORD_PDF']): ?>
									<p class="km-order-confirm__hint">
										<?=Loc::getMessage("SOA_PAY_PDF", array("#LINK#" => $arParams["PATH_TO_PAYMENT"]."?ORDER_ID=".$orderAccountNumber."&pdf=1&DOWNLOAD=Y"))?>
									</p>
								<? endif; ?>
							<? else: ?>
								<?=$arPaySystem["BUFFERED_OUTPUT"]?>
							<? endif; ?>
						</div>
					</section>
					<?
				}
			}
		}
		else
		{
			?><p class="km-order-confirm__note"><strong><?=$arParams['MESS_PAY_SYSTEM_PAYABLE_ERROR']?></strong></p><?
		}
		?>
	</div>

<? else: ?>

	<div class="km-order-confirm km-order-confirm--error">
		<h2 class="km-order-confirm__title"><?=Loc::getMessage("SOA_ERROR_ORDER")?></h2>
		<p class="km-order-confirm__hint">
			<?=Loc::getMessage("SOA_ERROR_ORDER_LOST", ["#ORDER_ID#" => htmlspecialcharsbx($arResult["ACCOUNT_NUMBER"])])?>
			<?=Loc::getMessage("SOA_ERROR_ORDER_LOST1")?>
		</p>
	</div>

<? endif ?>
