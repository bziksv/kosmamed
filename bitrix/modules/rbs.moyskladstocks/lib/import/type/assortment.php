<?
namespace Rbs\MoyskladStocks\Import\Type;

use Rbs\MoyskladStocks\Debug\Loger;
use Rbs\MoyskladStocks\ApiNew;
use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\LangMsg;
use Rbs\MoyskladStocks\AgentManager;

class Assortment 
{
	public static function import_ext_codes()
	{
		$logger = new Loger();
		$agentManager = new AgentManager('update_ext_codes');
		$agentManager->setOnlyUpdated();
		
		if($agentManager->isFullUpdate()) {
			$agentManager->setConfigValue('limit', 1000);
		} else {
			$agentManager->setConfigValue('limit', 500);
		}
		
		try {

			if (!\Rbs\MoyskladStocks\HlCache\ExtCodes::isExsist()) {
				\Rbs\MoyskladStocks\HlCache\ExtCodes::createTable();
			}

			if(!\Rbs\MoyskladStocks\HlCache\ExtCodes::isExsist()) {
				throw new \Bitrix\Main\SystemException(LangMsg::get('THROW_HL_TABLE_DOES_NOT_EXSISTS'));
			}

			$params = [
				'limit' => $agentManager->getLimit(),
				'offset' => $agentManager->getOffset(),
				'filter' => 'archived=true;archived=false;'
			];

			if (!$agentManager->isFullUpdate()) {
				$params['filter'] = 'archived=true;archived=false;updated>=' . $agentManager->getLastDateUpdate();
			}

			if (!empty($params['filter'])) {
				$logger->addInfoMessage(LangMsg::buildAgentFilterMessage($params['filter']));
			}

			ApiNew::refreshCountRequests();
			$msResult = ApiNew::get('/entity/assortment', $params);
			if (Utils::is_success($msResult)) {

				if (!empty($msResult->meta->size)) {
					$agentManager->setSize($msResult->meta->size);
				}

				if (Utils::array_exists($msResult)) {
					\Rbs\MoyskladStocks\HlCache\ExtCodes::update($msResult->rows);
				} else {
					if ($agentManager->isFullUpdate()) {
						$logger->addWarningMessage(LangMsg::get('WARNING_EMPTY_ROWS'));
					} else {
						$logger->addInfoMessage(LangMsg::get('INFO_EMPTY_ROWS'));
					}
				}

				if (!empty($msResult->meta->nextHref)) {
					$agentManager->setNextStepOffset();
				} else {
					$agentManager->setFinalStepParams();
				}
				
			} else {
				if (Utils::has_errors($msResult)) {
					$logger->addErrorMessageArray($msResult->{'errors'});
				} else {
					throw new \Bitrix\Main\SystemException(LangMsg::get('EXCEPTION_API_ERROR'));
				}
			}

		} catch (\Throwable $e) {
			$logger->addErrorMessage(Utils::build_exception_message($e));
		}

		$logger->addFinishMessage(LangMsg::buildAgentFinishMessage($logger->getLogTime()));

		$logger->exportLog(LangMsg::buildAgentHeadMessage($agentManager));

		return (object)[
			'logger' => $logger,
			'agentManager' => $agentManager
		];
	}
}