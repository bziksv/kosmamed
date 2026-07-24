<?php
class CNigesCookiesAcceptPublic
{
	/** @var string */
	private static $bannerHtml = '';

	/**
	 * Render cookie notice and park HTML for injection before </body>.
	 * OnEpilog runs after template footer already closed the document, so a raw
	 * echo would land after </html> and stay hidden (DOMContentLoaded already fired).
	 */
	public static function OnEpilog()
	{
		global $APPLICATION;

		if (!CModule::IncludeModule(cookiesaccept_MODULE_ID)) {
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

		ob_start();
		$APPLICATION->IncludeComponent(
			'niges:cookiesaccept',
			'.default',
			array(),
			false,
			array('HIDE_ICONS' => 'Y')
		);
		self::$bannerHtml = (string)ob_get_clean();
		if (self::$bannerHtml === '') {
			return;
		}

		static $handlerAdded = false;
		if (!$handlerAdded) {
			$handlerAdded = true;
			AddEventHandler('main', 'OnEndBufferContent', array(__CLASS__, 'onEndBufferContent'));
		}
	}

	/**
	 * @param string $content
	 */
	public static function onEndBufferContent(&$content)
	{
		if (self::$bannerHtml === '' || !is_string($content) || $content === '') {
			return;
		}

		$html = self::$bannerHtml;
		self::$bannerHtml = '';

		$pos = strripos($content, '</body>');
		if ($pos === false) {
			$content .= $html;
			return;
		}

		$content = substr($content, 0, $pos) . $html . substr($content, $pos);
	}
}
