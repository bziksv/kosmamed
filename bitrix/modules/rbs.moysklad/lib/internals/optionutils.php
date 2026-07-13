<?php
namespace Rbs\Moysklad\Internals;

use Rbs\Moysklad\Config;
use Rbs\Moysklad\LangMsg;

class OptionUtils
{

	public static function buildImportOnceButton(&$localTab = [], $process = '', $withHeader = true)
	{
		if($withHeader) {
			$localTab[] = LangMsg::get('OPTION_UTILS_IMPORT_ONCE_HEAD');
		} else {
			$localTab[] = [
				"import_once",
				'',
				'',
				['statichtml']
			];
		}
		
		$localTab[] = [
			"import_once", 
			GetMessage(
				'OPTION_UTILS_IMPORT_ONCE_BTN', 
				[
					'#PROCESS#' => $process,
					'#PROFILE_ID#' => Config::getProfileId()
				]
			),
			GetMessage('OPTION_UTILS_IMPORT_ONCE_NOTE'),
			['statichtml']
		];

	}

}