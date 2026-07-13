<?php
namespace Rbs\MoyskladStocks;

use \Bitrix\Main\Localization\Loc;
use \Rbs\MoyskladStocks\AgentManager;
use \Rbs\MoyskladStocks\ApiNew;

Loc::loadMessages(__FILE__);
Loc::setCurrentLang('ru');

class LangMsg
{
	public static function get($messId = '', $params = [], $defaultMsg = '')
	{
		$locMsg = Loc::getMessage($messId, $params);
		if (mb_strlen($locMsg)) {
			return $locMsg;
		} else {
			return str_replace(array_keys($params), array_values($params), $defaultMsg);
		}
	}

	public static function buildAgentHeadMessage(AgentManager $agentManager, string $agentMessageDefault = 'AGENT_START')
	{
		if($agentManager->getSize() <= 0 && $agentMessageDefault === 'AGENT_START') {
			$agentMessageDefault .= '_EMPTY';
		}

		if(!empty($agentManager->getTag())) {
			$agentMessageDefault .= '_' . $agentManager->getTag();
		}
		
		return self::get($agentMessageDefault, [
			'#AGENT_NAME#' => $agentManager->getAgentLangName(),
			'#LIMIT#' => $agentManager->getLimit(),
			'#OFFSET#' => $agentManager->getCurrentStep(),
			'#SIZE#' => $agentManager->getSize(),
		]);
	}

	public static function buildAgentFilterMessage(string $filterStr = '')
	{
		return self::get('INFO_API_FILTER', [
			'#FILTER#' => $filterStr
		]);
	}

	public static function buildAgentFinishMessage($logTime = 0)
	{
		return self::get('AGENT_FINISH', [
			'#API_COUNT#' => ApiNew::getCountRequests(),
			'#AGENT_TIME#' => $logTime
		]);
	}
}
