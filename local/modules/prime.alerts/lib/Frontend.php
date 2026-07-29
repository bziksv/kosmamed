<?php

namespace Prime\Alerts;

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Web\Json;

class Frontend
{
	public static function onEpilog(): void
	{
		if (defined('ADMIN_SECTION') && ADMIN_SECTION === true) {
			return;
		}

		if (!Config::isEnabled() || !Config::isYes('policy_enabled', 'Y')) {
			return;
		}

		$policyRegister = Config::isYes('policy_register', 'Y');
		$policyOrder = Config::isYes('policy_order', 'Y');
		if (!$policyRegister && !$policyOrder) {
			return;
		}

		$providers = array_values(array_unique(array_merge(
			EmailPolicy::getRuProviders(),
			EmailPolicy::getExtraDomains()
		)));

		$config = [
			'enabled' => true,
			'providers' => $providers,
			'policyRegister' => $policyRegister,
			'policyOrder' => $policyOrder,
			'noticeSignup' => EmailPolicy::getNoticeHtml('signup'),
			'noticeCheckout' => EmailPolicy::getNoticeHtml('checkout'),
		];

		$asset = Asset::getInstance();
		$asset->addCss('/local/modules/prime.alerts/assets/style.css');
		$asset->addString(
			'<script>window.PRIME_ALERTS=' . Json::encode($config) . ';</script>',
			true
		);
		$asset->addJs('/local/modules/prime.alerts/assets/policy.js');
	}
}
