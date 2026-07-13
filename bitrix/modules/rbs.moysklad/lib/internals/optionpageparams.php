<?php
namespace Rbs\Moysklad\Internals;

use Rbs\Moysklad\Config;

class OptionPageParams
{
	public static function getActionStr(): string
	{
		global $APPLICATION;
		$mid = Config::getModuleId(true);
		$actionStr = $APPLICATION->GetCurPage() . "?mid={$mid}&lang=" . LANG;
		if (Config::getProfileId() > 0) {
			$actionStr .= "&profile_id=" . Config::getProfileId();
		}
		return $actionStr;
	}

	public static function getStaticHtmlNames(): array
	{
		return [
			'option_form_name' => 'main_options',
			'option_form_id' => 'rbs_moysklad_option_form',
			'option_auth_btn_name' => 'UpdateAuth',
			'option_save_btn_name' => 'Update',
			'ajax_namespace' => 'rbs:moysklad.api.ajax.',
		];
	}
}