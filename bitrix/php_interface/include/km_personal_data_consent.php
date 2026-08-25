<?php
/**
 * Единый текст галочки согласия для всех форм сайта.
 * Ссылки: /upload/compliance.png и /upload/mm_politics.png
 */
if (!function_exists('kmPersonalDataConsentHtml')) {
	function kmPersonalDataConsentHtml()
	{
		return 'Я даю <a target="_blank" href="/upload/compliance.png">согласие на обработку персональных данных</a>'
			. ' в соответствии с'
			. ' <a target="_blank" href="/upload/mm_politics.png">политикой обработки персональных данных</a>';
	}
}
