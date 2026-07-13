<?php
namespace Rbs\Moysklad\Controller;
 
use \Rbs\Moysklad\AgentManager;
use \Rbs\Moysklad\Debug\Loger;
use \Rbs\Moysklad\LangMsg;
use \Rbs\Moysklad\Utils;
use \Rbs\Moysklad\ApiNew;
use \Rbs\Moysklad\Customerorder;
use \Rbs\Moysklad\Internals\TimezoneState;

class ImportController
{
	public static function import_customerorder()
	{
		TimezoneState::setTimeZone();

		$logger = new Loger();

		$agentManager = new AgentManager('import_customerorder');
		$agentManager->setOnlyUpdated();

		try {

			$params = [
				'limit' => $agentManager->getLimit(),
				'offset' => $agentManager->getOffset(),
				'expand' => 'positions.assortment,agent,state,files,store,salesChannel,demands.positions,rate.currency',
				'filter' => 'created>=' . $agentManager->getLastDateUpdate()
			];

			if (!empty($params['filter'])) {
				$logger->addInfoMessage(
					LangMsg::get('CONTROLLER_IMPORTCONTROLLER_IMPORT_CUSTOMERORDER_FILTER_MSG', [
						'#FILTER#' => $params['filter']
					])
				);
			}

			$msResult = ApiNew::get('/entity/customerorder', $params);
			if (Utils::is_success($msResult)) {

				if (!empty($msResult->meta->size)) {
					$agentManager->setSize($msResult->meta->size);
				}

				if (Utils::array_exists($msResult)) {

					foreach($msResult->rows as $row) {
						$logger->addInfoMessage(LangMsg::get(
							'CONTROLLER_IMPORTCONTROLLER_IMPORT_CUSTOMERORDER_START_IMPORT',
							[
								'#ID#' => $row->name
							]
						));
						$customerorder = Customerorder::createBxOrder($row->meta->href, 'CREATE', $row);
						$logger->addMessageArray($customerorder->getLogList());
					}
					
				} else {
					$logger->addInfoMessage(
						LangMsg::get('CONTROLLER_IMPORTCONTROLLER_IMPORT_CUSTOMERORDER_EMPTY_ROWS')
					);
				}

				if (!empty($msResult->meta->nextHref)) {
					$agentManager->setNextStepOffset();
				} else {
					$agentManager->setFinalStepParams();
				}
				
			} else {
				if (Utils::has_errors($msResult)) {
					foreach($msResult->{'errors'} as $error) {
						$logger->addErrorMessage($error);
					}
				} else {
					throw new \Bitrix\Main\SystemException(LangMsg::get('EXCEPTION_API_ERROR'));
				}
			}
		} catch (\Exception $e) {
			$logger->addErrorMessage($e->getMessage());
		} catch (\Bitrix\Main\SystemException $e) {
			$logger->addErrorMessage($e->getMessage());
		} finally {
			$logger->exportLog(LangMsg::get('CONTROLLER_IMPORTCONTROLLER_IMPORT_CUSTOMERORDER_MAIN_MSG', [
				'#OFFSET#' => $agentManager->getCurrentStep(),
				'#SIZE#' => $agentManager->getSize(),
				'#LIMIT#' => $agentManager->getLimit(),
			]));
		}

		return (object)[
			'logger' => $logger,
			'agentManager' => $agentManager
		];
	}
}