<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

/**
 * Экран оплаты по банковскому QR (не СБП).
 * @var array $params
 */

$errors = [];
if (!empty($params["ERROR"]) && is_array($params["ERROR"])) {
	$errors = $params["ERROR"];
}

$recipient = trim((string)($params["Name"] ?? ""));
$recipientShow = $recipient;
if ($recipientShow !== "" && (mb_stripos($recipientShow, "АЛЬМАМЕД") !== false || mb_stripos($recipientShow, "АЛЬМАМЕД") !== false)) {
	$recipientShow = "ООО «АЛЬМАМЕД»";
}
if ($recipientShow === "") {
	$recipientShow = "ООО «АЛЬМАМЕД»";
}
$sumRaw = $params["Summ"] ?? $params["PAYMENT_SHOULD_PAY"] ?? $params["Sum"] ?? "";
$sumFormatted = "";
if ($sumRaw !== "" && $sumRaw !== null) {
	if (is_numeric($sumRaw)) {
		$sumFormatted = number_format((float)$sumRaw, 2, ",", " ") . " ₽";
	} else {
		$sumFormatted = htmlspecialcharsbx((string)$sumRaw);
		if (mb_stripos($sumFormatted, "руб") === false && mb_stripos($sumFormatted, "₽") === false) {
			$sumFormatted .= " ₽";
		}
	}
}

$orderNum = trim((string)($params["OrderNum"] ?? $params["ACCOUNT_NUMBER"] ?? ""));
$purpose = trim((string)($params["Purpose"] ?? ""));
$phone = "+7 (499) 112-08-45";
$phoneHref = "+74991120845";
$qrHtml = (string)($params["qrB64Image"] ?? "");
?>

<div class="km-qr-pay">
	<?php if ($errors): ?>
		<div class="km-qr-pay__errors">
			<?php foreach ($errors as $error): ?>
				<p><?= htmlspecialcharsbx($error) ?></p>
			<?php endforeach; ?>
		</div>
	<?php else: ?>
		<div class="km-qr-pay__head">
			<div class="km-qr-pay__head-text">
				<h3 class="km-qr-pay__title">Оплата по QR-коду</h3>
				<p class="km-qr-pay__lead">Откройте приложение банка и наведите камеру на код — реквизиты подставятся сами.</p>
			</div>
			<?php if ($sumFormatted !== ""): ?>
				<div class="km-qr-pay__amount">
					<span class="km-qr-pay__amount-label">К оплате</span>
					<span class="km-qr-pay__amount-value"><?= $sumFormatted ?></span>
					<?php if ($orderNum !== ""): ?>
						<span class="km-qr-pay__amount-order">Счёт <?= htmlspecialcharsbx($orderNum) ?></span>
					<?php endif; ?>
				</div>
			<?php endif; ?>
		</div>

		<div class="km-qr-pay__layout">
			<div class="km-qr-pay__qr-col">
				<div class="km-qr-pay__qr-frame">
					<?php if ($qrHtml !== ""): ?>
						<div class="km-qr-pay__qr"><?= $qrHtml ?></div>
					<?php else: ?>
						<p class="km-qr-pay__error">Не удалось сформировать QR-код. Позвоните нам: <a href="tel:<?= $phoneHref ?>"><?= htmlspecialcharsbx($phone) ?></a></p>
					<?php endif; ?>
				</div>
				<p class="km-qr-pay__qr-caption">Покажите код камере в приложении банка</p>
			</div>

			<div class="km-qr-pay__info-col">
				<div class="km-qr-pay__check">
					<div class="km-qr-pay__check-title">В момент оплаты проверьте</div>
					<ul class="km-qr-pay__check-list">
						<li>
							<span class="km-qr-pay__check-key">Получатель</span>
							<span class="km-qr-pay__check-val"><?= htmlspecialcharsbx($recipientShow) ?></span>
						</li>
						<?php if ($orderNum !== ""): ?>
							<li>
								<span class="km-qr-pay__check-key">Назначение / счёт</span>
								<span class="km-qr-pay__check-val"><?= htmlspecialcharsbx($orderNum) ?></span>
							</li>
						<?php endif; ?>
						<?php if ($sumFormatted !== ""): ?>
							<li>
								<span class="km-qr-pay__check-key">Сумма</span>
								<span class="km-qr-pay__check-val"><?= $sumFormatted ?></span>
							</li>
						<?php endif; ?>
					</ul>
					<?php if ($purpose !== "" && $purpose !== $orderNum): ?>
						<p class="km-qr-pay__purpose"><?= htmlspecialcharsbx($purpose) ?></p>
					<?php endif; ?>
				</div>

				<ol class="km-qr-pay__steps">
					<li>
						<span class="km-qr-pay__step-num">1</span>
						<span class="km-qr-pay__step-text">Откройте приложение вашего банка</span>
					</li>
					<li>
						<span class="km-qr-pay__step-num">2</span>
						<span class="km-qr-pay__step-text">Выберите оплату по QR-коду</span>
					</li>
					<li>
						<span class="km-qr-pay__step-num">3</span>
						<span class="km-qr-pay__step-text">Наведите камеру на QR-код</span>
					</li>
					<li>
						<span class="km-qr-pay__step-num">4</span>
						<span class="km-qr-pay__step-text">Сверьте получателя и сумму — оплатите</span>
					</li>
				</ol>

				<div class="km-qr-pay__help">
					Если реквизиты не совпадают — не платите, позвоните:
					<a href="tel:<?= $phoneHref ?>"><?= htmlspecialcharsbx($phone) ?></a>
				</div>
			</div>
		</div>
	<?php endif; ?>
</div>
