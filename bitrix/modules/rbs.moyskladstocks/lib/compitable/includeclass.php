<?php

namespace Rbs\MoyskladStocks\Compitable;

use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\ApiNew;
use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\Debug;
use Rbs\MoyskladStocks\LangMsg;
use Rbs\MoyskladStocks\Process\Helper as ProcessHelper;

class IncludeClass
{
	public static function importByApiRequest($entity = 'product', $customFilter = '', $offset = 0, $isWebhookUpdate = false)
	{
		$result['offset'] = -1;
		$limit = 100;

		$writer = new Debug\Writer(LangMsg::get('AGENT_START_ENTITY', [
			'#AGENT_NAME#' => LangMsg::get('AGENT_IMPORT_API_REQUEST'),
			'#ENTITY#' => $entity,
			'#LIMIT#' => $limit,
			'#OFFSET#' => $offset,
		]));

		$loger = new Debug\Loger();

		$catalogIblockId = Config::getIblockId($entity);
		if ((int)$catalogIblockId <= 0) {

			$loger->addMessage(LangMsg::get('WARNING_EMPTY_CATALOG_IBLOCK'), Debug\Message::TYPE_WARNING);

		} else {

			try {

				$updateParamList = Config::getUpParams($entity);
				if ($isWebhookUpdate) {
					$updateParamList = Config::getWhParams($entity);
				}

				if (!empty($customFilter)) {
					if (mb_substr($customFilter, -1) !== ';') {
						$customFilter .= ';';
					}
				}

				if ($updateParamList['archived']) {
					$customFilter .= 'archived=true;archived=false';
				}

				$filterStr = \CRbsMoyskladStocks::getFilterString($entity, $customFilter, true);
				if (!empty($filterStr)) {
					$loger->addMessage(LangMsg::get('INFO_API_FILTER', [
						'#FILTER#' => $filterStr
					]), Debug\Message::TYPE_INFO);
				}

				$filter = [
					'limit' => (int)$limit,
					'offset' => (int)$offset,
					'filter' => $filterStr,
					'expand' => ProcessHelper::buildExpandParams($entity)
				];

				$entityItems = ApiNew::get('/entity/' . $entity, $filter);
				if (Utils::is_success($entityItems)) {

					if (Utils::array_exists($entityItems)) {

						$arrLog = \CRbsMoyskladStocks::getArrLog($entity, $limit, $offset);

						\CRbsMoyskladStocks::importItems($entityItems->{'rows'}, $entity, $arrLog, $isWebhookUpdate);
						if (Config::checkFeature($entity . 'prices')) {
							\Rbs\MoyskladStocks\Import\Type\Prices::import_from_ms_object($entityItems->{'rows'});
						}

						$loger->addMessage(LangMsg::get('FAUE_INFO', [
							'#FIND#' => (string)$arrLog['#FIND#'],
							'#ADD#' => (string)$arrLog['#ADD#'],
							'#UPDATE#' => (string)$arrLog['#UPDATE#'],
							'#ERROR#' => (string)$arrLog['#ERROR#'],
						]), Debug\Message::TYPE_INFO);

						if (Utils::is_count($arrLog['ERROR_LIST'])) {
							$loger->addMessageArray($arrLog['ERROR_LIST'], Debug\Message::TYPE_ERROR);
						}

						if (Utils::is_count($arrLog['INFO_LIST'])) {
							$loger->addMessageArray($arrLog['INFO_LIST'], Debug\Message::TYPE_INFO);
						}
					} else {

						$loger->addMessage(LangMsg::get('WARNING_EMPTY_ROWS'), Debug\Message::TYPE_WARNING);
					}

					if (!empty($entityItems->{'meta'}->{'nextHref'})) {
						$result['offset'] = $offset + $limit;
					} else {
						$result['offset'] = -1;
					}

					$loger->addMessage(
						LangMsg::get('AGENT_IMPORT_API_REQUEST_SUCCESS', [
							'#COUNT#' => (string)count($entityItems->{'rows'})
						]),
						Debug\Message::TYPE_SUCCESS
					);
				} else {

					if (Utils::has_errors($entityItems)) {
						$loger->addMessageArray($entityItems->{'errors'}, Debug\Message::TYPE_ERROR);
					} else {
						$loger->addMessage(LangMsg::get('EXCEPTION_API_ERROR'), Debug\Message::TYPE_ERROR);
					}
				}
			} catch (\Throwable $e) {
				$loger->addMessage(Utils::build_exception_message($e), Debug\Message::TYPE_ERROR);
			}
		}

		$loger->addMessage(LangMsg::get('AGENT_FINISH', [
			'#API_COUNT#' => ApiNew::getCountRequests(),
			'#AGENT_TIME#' => $loger->getLogTime()
		]), Debug\Message::TYPE_INFO);

		$writer->setLogerMessages($loger->getMessageArray());
		$writer->exportLog();

		return $result;
	}
}

