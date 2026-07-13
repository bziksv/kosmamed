<?php
namespace Rbs\MoySklad\Internals;

use \Rbs\Moysklad\Config;

class ModifyUser
{
	public static function getUserId(): int
	{
		global $USER;
		if (is_object($USER) && $USER->IsAuthorized()) {
			return (int)$USER->GetID();
		}
		return (int)Config::getUserId();
	}

	public static function authorize()
	{
		global $USER;
		if (is_object($USER) && !$USER->IsAuthorized() && (int)Config::getUserId() > 0) {
			$USER->Authorize((int)Config::getUserId(), false, false);
		}
	}

	public static function logout()
	{
		global $USER;
		if (is_object($USER) && $USER->IsAuthorized()) {
			$USER->Logout();
		}
	}
}
