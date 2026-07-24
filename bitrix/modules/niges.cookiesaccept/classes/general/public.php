<?php
class CNigesCookiesAcceptPublic
{
	/**
	 * Inject cookie banner before </body> during OnEndBufferContent.
	 * Must run as a registered buffer handler (not from OnEpilog): otherwise
	 * Composite saves HTML without the banner, and warm homepage/catalog miss it.
	 *
	 * @param string $content
	 */
	public static function onEndBufferContent(&$content)
	{
		if (!is_string($content) || $content === '') {
			return;
		}

		if (!CModule::IncludeModule(cookiesaccept_MODULE_ID)) {
			return;
		}

		if (defined('ADMIN_SECTION') && ADMIN_SECTION === true) {
			return;
		}

		if (COption::GetOptionString(cookiesaccept_MODULE_ID, 'ACTIVE', 'N', SITE_ID) !== 'Y') {
			return;
		}

		if (defined('PUBLIC_AJAX_MODE') && PUBLIC_AJAX_MODE === true) {
			return;
		}
		if (isset($_REQUEST['ajax']) && (string)$_REQUEST['ajax'] !== '') {
			return;
		}
		if (isset($_REQUEST['bxajaxid']) && (string)$_REQUEST['bxajaxid'] !== '') {
			return;
		}

		// already injected
		if (strpos($content, 'id="nca-cookiesaccept-line"') !== false) {
			return;
		}

		// not an HTML page
		if (stripos($content, '</body>') === false) {
			return;
		}

		global $APPLICATION;
		if (!is_object($APPLICATION)) {
			return;
		}

		ob_start();
		$APPLICATION->IncludeComponent(
			'niges:cookiesaccept',
			'.default',
			array(),
			false,
			array('HIDE_ICONS' => 'Y')
		);
		$html = (string)ob_get_clean();
		if ($html === '') {
			return;
		}

		$pos = strripos($content, '</body>');
		if ($pos === false) {
			$content .= $html;
			return;
		}

		$content = substr($content, 0, $pos) . $html . substr($content, $pos);
	}

	/**
	 * @deprecated kept for old event registrations; forwards to buffer handler is N/A
	 */
	public static function OnEpilog()
	{
		// Intentionally empty: banner must be injected in OnEndBufferContent
		// so Bitrix Composite stores it in html_pages.
	}
}
