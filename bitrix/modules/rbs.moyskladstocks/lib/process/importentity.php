<?php

namespace Rbs\MoyskladStocks\Process;

use Rbs\MoyskladStocks\Debug;
use Rbs\MoyskladStocks\ApiNew;
use Rbs\MoyskladStocks\LangMsg;
use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\Process\Helper;
use Rbs\MoyskladStocks\Services\ImportParamsConfig;

class ImportEntity extends Base
{
	protected $entity = 'product';
	protected $arrLog = [];

	public function __construct(string $entity)
	{
		$this->entity = $entity;
		parent::__construct();
		$this->arrLog = \CRbsMoyskladStocks::getArrLog($this->entity, $this->agentManager->getLimit(), $this->agentManager->getOffset());
	}

	protected function initializeAgentManager()
	{
		$localLimit = $this->agentManager->getLimit() < 100 ? $this->agentManager->getLimit() : 100;
		$maxSteps = ceil($this->agentManager->getLimit() / $localLimit);
		if ($this->agentManager->getLimit() > 100) {
			$this->agentManager->setConfigValue('limit', $maxSteps * 100);
		}
	}

	protected function getAgentManagerId(): string 
	{
		return $this->entity;
	}

	protected function validateProcess()
	{
		if (empty($this->entity)) {
			throw new \Bitrix\Main\SystemException(LangMsg::get('WARNING_EMPTY_ENTITY'));
		}

		if (Config::getIblockId($this->entity) <= intval(0)) {
			throw new \Bitrix\Main\SystemException(LangMsg::get('WARNING_EMPTY_CATALOG_IBLOCK'));
		}

	}

	private function getCustomFilter(): array
	{
		$customFilter = [];

		if($this->entity === 'variant') {
			$customFilter[] = 'type=' . $this->entity;
		}
	
		if (ImportParamsConfig::getImportFeature($this->entity, 'include_archived')) {
			$customFilter[] = 'archived=true';
			$customFilter[] = 'archived=false';
		}

		return $customFilter;
	}

	protected function buildParams(): array 
	{
		$filter = \CRbsMoyskladStocks::buildAgentFilterStringForEntity(
			$this->entity,
			$this->agentManager,
			$this->getCustomFilter()
		);

		$params = [
			'limit' => $this->agentManager->getLimit(),
			'offset' => $this->agentManager->getOffset(),
			'filter' => $filter,
			'expand' => Helper::buildExpandParams($this->entity)
		];

		return $params;
	}

	protected function fetchData() 
	{
		$this->refreshApiOnce();
		if ($this->entity === 'variant') {
			return ApiNew::get("/entity/assortment", $this->params);
		}
		return ApiNew::get("/entity/{$this->entity}", $this->params);
	}

	protected $isRefreshedApiRequests = false;
	protected function refreshApiOnce()
	{
		if(!$this->isRefreshedApiRequests) {
			ApiNew::refreshCountRequests();
			$this->isRefreshedApiRequests = true;
		}
	}

	protected function processRows(array $rows) 
	{
		\CRbsMoyskladStocks::importItems($rows, $this->entity, $this->arrLog);
	}

	protected function processLastStepActions($response) 
	{
		$arrLog = $this->arrLog;

		$this->logger->addMessage(LangMsg::get('FAUE_INFO', [
			'#FIND#' => (string)$arrLog['#FIND#'],
			'#ADD#' => (string)$arrLog['#ADD#'],
			'#UPDATE#' => (string)$arrLog['#UPDATE#'],
			'#ERROR#' => (string)$arrLog['#ERROR#'],
		]), Debug\Message::TYPE_INFO);

		if (Utils::is_count($arrLog['ERROR_LIST'])) {
			$this->logger->addMessageArray($arrLog['ERROR_LIST'], Debug\Message::TYPE_ERROR);
		}

		if (Utils::is_count($arrLog['INFO_LIST'])) {
			$this->logger->addMessageArray($arrLog['INFO_LIST'], Debug\Message::TYPE_INFO);
		}
	}

	protected function prepareResponse($response) {}
}