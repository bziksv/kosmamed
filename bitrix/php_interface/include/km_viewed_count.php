<?
/**
 * Bitrix по умолчанию хранит 10 просмотренных товаров (catalog.viewed_count).
 * «Вы смотрели» — 19 пунктов: 20-й вылезал за ширину контентной колонки.
 */
if (!class_exists('\Bitrix\Main\Config\Option')) {
	return;
}

$target = 19;
$viewedCount = (int)\Bitrix\Main\Config\Option::get('catalog', 'viewed_count', 10);
if ($viewedCount !== $target) {
	\Bitrix\Main\Config\Option::set('catalog', 'viewed_count', $target);
}
