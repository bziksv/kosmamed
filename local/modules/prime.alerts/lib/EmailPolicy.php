<?php

namespace Prime\Alerts;

class EmailPolicy
{
	/** @var string[] */
	protected static $ruProviders = [
		'mail.ru',
		'inbox.ru',
		'list.ru',
		'bk.ru',
		'internet.ru',
		'yandex.ru',
		'ya.ru',
		'yandex.com',
		'yandex.by',
		'yandex.kz',
		'yandex.ua',
		'rambler.ru',
		'lenta.ru',
		'autorambler.ru',
		'ro.ru',
		'pochta.ru',
		'e-mail.ru',
		'qip.ru',
		'live.ru',
	];

	public static function getDomain(string $email): string
	{
		$email = strtolower(trim($email));
		if ($email === '' || strpos($email, '@') === false) {
			return '';
		}

		return substr(strrchr($email, '@'), 1) ?: '';
	}

	public static function isAllowed(string $email): bool
	{
		$domain = self::getDomain($email);
		if ($domain === '') {
			return false;
		}

		if (preg_match('/\.(ru|su)$/u', $domain)) {
			return true;
		}

		foreach (self::$ruProviders as $provider) {
			if ($domain === $provider || substr($domain, -strlen('.' . $provider)) === '.' . $provider) {
				return true;
			}
		}

		$extra = Config::get('extra_domains', '');
		foreach (preg_split('/[\s,;]+/', $extra) ?: [] as $allowed) {
			$allowed = strtolower(trim($allowed));
			if ($allowed === '') {
				continue;
			}
			if ($domain === $allowed || substr($domain, -strlen('.' . $allowed)) === '.' . $allowed) {
				return true;
			}
		}

		return false;
	}

	/** @return string[] */
	public static function getRuProviders(): array
	{
		return self::$ruProviders;
	}

	/** @return string[] */
	public static function getExtraDomains(): array
	{
		$extra = Config::get('extra_domains', '');
		$out = [];
		foreach (preg_split('/[\s,;]+/', $extra) ?: [] as $allowed) {
			$allowed = strtolower(trim($allowed));
			if ($allowed !== '') {
				$out[] = $allowed;
			}
		}

		return $out;
	}

	public static function getErrorText(string $context = 'signup'): string
	{
		if ($context === 'checkout') {
			return 'Оформление заказа доступно только с e-mail в зонах .ru / .su или на российском почтовом сервисе (Яндекс, Mail.ru и т.п.). Зарубежные адреса (gmail.com и др.) не принимаются.';
		}

		return 'Регистрация доступна только с e-mail в зонах .ru / .su или на российском почтовом сервисе (Яндекс, Mail.ru и т.п.). Зарубежные адреса (gmail.com и др.) не принимаются.';
	}

	public static function getNoticeHtml(string $context = 'signup'): string
	{
		$email = htmlspecialcharsbx(Config::get('support_email', 'info@kosmamed.ru'));
		$phone = htmlspecialcharsbx(Config::get('support_phone', '8-800-100-37-97'));
		$tel = preg_replace('/\D+/', '', Config::get('support_phone', '88001003797')) ?: '88001003797';

		if ($context === 'checkout') {
			$title = 'Оформление заказа: требования к e-mail';
			$lead = 'Оформление заказа на сайте доступно только с адресом электронной почты в доменных зонах <strong>.ru</strong> или <strong>.su</strong>, '
				. 'либо на российском почтовом сервисе (например, '
				. '<a href="https://360.yandex.ru/mail/" target="_blank" rel="noopener">Яндекс</a> или '
				. '<a href="https://mail.ru/" target="_blank" rel="noopener">Mail.ru</a>). '
				. 'Адреса зарубежных почтовых сервисов и доменов других зон не принимаются.';
		} else {
			$title = 'Регистрация: требования к e-mail';
			$lead = 'Регистрация на сайте доступна только с адресом электронной почты в доменных зонах <strong>.ru</strong> или <strong>.su</strong>, '
				. 'либо на российском почтовом сервисе (например, '
				. '<a href="https://360.yandex.ru/mail/" target="_blank" rel="noopener">Яндекс</a> или '
				. '<a href="https://mail.ru/" target="_blank" rel="noopener">Mail.ru</a>). '
				. 'Адреса зарубежных почтовых сервисов и доменов других зон не принимаются.';
		}

		return '<div class="prime-alerts-notice signup-email-policy-notice">'
			. '<div class="prime-alerts-notice__inner">'
			. '<div class="prime-alerts-notice__icon" aria-hidden="true">!</div>'
			. '<div class="prime-alerts-notice__content">'
			. '<div class="prime-alerts-notice__title">' . $title . '</div>'
			. '<div class="prime-alerts-notice__text">'
			. '<p>' . $lead . '</p>'
			. '<p>Вы можете отправить нам заявку с любого почтового ящика на '
			. '<a href="mailto:' . $email . '">' . $email . '</a>.</p>'
			. '<p class="prime-alerts-notice__legal">Данная мера применяется в соответствии с Федеральным законом от 31.07.2023 № 406-ФЗ, '
			. 'а также в связи с требованиями Федерального закона от 27.07.2006 № 152-ФЗ «О персональных данных» '
			. '(в том числе в части локализации баз данных на территории Российской Федерации).</p>'
			. '<p>Если вы считаете, что это ошибка, позвоните нам: '
			. '<a href="tel:' . htmlspecialcharsbx($tel) . '">' . $phone . '</a>.</p>'
			. '</div></div></div></div>';
	}
}
