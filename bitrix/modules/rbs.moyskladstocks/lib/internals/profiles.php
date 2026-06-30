<?php
namespace Rbs\MoyskladStocks\Internals;

use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\LangMsg;

class Profiles
{
	public static function getProfiles(): array
	{
		$profiles = [
			0 => self::getProfileDisplayName(0, LangMsg::get('PROFILE_MAIN_NAME')),
		];

		if(Config::isProfilesOn()) {
			foreach (range(1, 100) as $i) {
				$isEnableProfile = \Bitrix\Main\Config\Option::get(Config::getModuleId(true) . '_' . $i, 'global_enabled', null, '');
				if (!is_null($isEnableProfile)) {
					$defaultName = LangMsg::get('PROFILE_NUM_NAME', ['#NUM#' => $i]);
					$profiles[$i] = self::getProfileDisplayName($i, $defaultName);
				} else {
					break;
				}
			}
		}

		return $profiles;
	}

	public static function setProfileName(int $profileId, string $name): void
	{
		Config::setOption('profile_name_' . $profileId, $name, '', true);
	}

	public static function getProfileName(int $profileId): string
	{
		return (string)Config::getOption('profile_name_' . $profileId, '', '', true);
	}

	private static function getProfileDisplayName(int $profileId, string $defaultName): string
	{
		$customName = self::getProfileName($profileId);
		if ($customName !== '') {
			return '[' . $profileId . '] ' . $customName;
		}
		return '[' . $profileId . '] ' . $defaultName;
	}
}