<?php

namespace Rbs\MoyskladStocks\Process;

use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\ApiNew;
use Rbs\MoyskladStocks\LangMsg;
use Rbs\MoyskladStocks\AgentManager;
use Rbs\MoyskladStocks\Process\Helper;

class ImportVariantByWebhook extends ImportEntity
{
	protected $parentProductId;
	protected $offset = 0;

	public function __construct(string $parentProductId, int $offset = 0)
	{
		$this->offset = $offset;
		$this->parentProductId = $parentProductId;
		parent::__construct('variant');
	}

	protected function getAgentManagerId(): string
	{
		//fake agent for work process
		return 'variant_agent';
	}

	protected function buildParams(): array
	{
		if (empty($this->parentProductId)) {
			throw new \Exception(LangMsg::get('THROW_PROCESS_VARIANT_AGENT_EMPTY_PARENT_PRODUCT'));
		}
		$params = [
			'limit' => 100,
			'offset' => $this->offset,
			'filter' => 'productid=' . $this->parentProductId,
			'expand' => Helper::buildExpandParams($this->entity)
		];
		return $params;
	}

	protected function fetchData()
	{
		$this->refreshApiOnce();
		return ApiNew::get("/entity/{$this->entity}", $this->params);
	}

	protected function prepareResponse($response) 
	{
		if (!empty($response->{'meta'}->{'nextHref'})) {
			$this->offset += 100;
		} else {
			$this->offset = 0;
		}
	}

	public function getOffset(): int
	{
		return (int)$this->offset;
	}

	protected function processRows(array $rows)
	{
		\CRbsMoyskladStocks::importItems($rows, $this->entity, $this->arrLog, true);
		
		if (Config::checkFeature('variantprices')) {
			$this->logger->addInfoMessage(LangMsg::get('WORK_WITH_PRICES_IMPORT_FOR_ASSORTMENT_IDS', [
				'#COUNT#' => count($rows)
			]));
			\Rbs\MoyskladStocks\Import\Type\Prices::update_entity_prices_from_rows($rows, 'variant', $this->logger);
		}

		$agentManagerCurrentStocks = new AgentManager('curr_stocks');
		if($agentManagerCurrentStocks->isEnabled() || $agentManagerCurrentStocks->isEnableAgentForCron()) {
			$this->logger->addInfoMessage(LangMsg::get('WORK_WITH_CURR_STOCK_IMPORT_FOR_ASSORTMENT_IDS', [
				'#COUNT#' => count($rows)
			]));
			\Rbs\MoyskladStocks\Import\Type\CurrentStocks::update_entity_stocks_from_rows($rows, 'variant', $this->logger);
		}
	}	
}