<?php

$_SERVER["DOCUMENT_ROOT"] = realpath(dirname(__FILE__). "/../../../..");
$DOCUMENT_ROOT = $_SERVER["DOCUMENT_ROOT"];

define("NO_KEEP_STATISTIC", true);
define("NOT_CHECK_PERMISSIONS",true); 

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

@set_time_limit(0);
@ignore_user_abort(true);
ini_set('memory_limit', '512M');

function log_cron_exec($flag = '', $id = '')
{
	file_put_contents(__DIR__ . '/log.txt', date('H:i:s') . "[{$id}] {$flag} " . PHP_EOL, FILE_APPEND);
}

$cronId = uniqid();

if(\Bitrix\Main\Loader::includeModule('rbs.moyskladstocks')){

	if(!empty($argv[1])) {
		$profile_id = (int)$argv[1];
		if($profile_id > 0){
			\Rbs\MoyskladStocks\Config::setProfileId($profile_id);
		}
	}

	\Rbs\MoyskladStocks\Config::setLastCronInitDate();

	if(!\Rbs\MoyskladStocks\Config::isLockCron()){
		\Rbs\MoyskladStocks\Agent::run_all_agents();
		\Rbs\MoyskladStocks\Config::unLockCron();
	}

}

?>