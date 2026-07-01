<?php
/**
 * Оборачивает телефон из HTML-блока контактов в ссылку tel:.
 */
function ksPhoneTelHtml($html)
{
	if (empty($html)) {
		return $html;
	}
	if (stripos($html, 'href="tel:') !== false || stripos($html, "href='tel:") !== false) {
		return $html;
	}

	$plain = trim(preg_replace('/\s+/u', ' ', strip_tags($html)));
	if ($plain === '') {
		return $html;
	}

	$digits = preg_replace('/\D+/', '', $plain);
	if (strlen($digits) === 11 && $digits[0] === '8') {
		$digits = '7' . substr($digits, 1);
	} elseif (strlen($digits) === 10) {
		$digits = '7' . $digits;
	}
	if (strlen($digits) < 10) {
		return $html;
	}

	$tel = '+' . $digits;
	$icon = (stripos($html, 'fa-phone') !== false) ? '<i class="fa fa-phone"></i>' : '';

	return '<p>' . $icon . '<a href="tel:' . htmlspecialcharsbx($tel) . '">'
		. htmlspecialcharsbx($plain) . '</a></p>';
}

/**
 * Основной телефон сайта (для иконки в мобильной панели).
 */
function ksMainPhoneTel()
{
	return '+74991120845';
}
