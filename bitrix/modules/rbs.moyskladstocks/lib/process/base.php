<?php

namespace Rbs\MoyskladStocks\Process;

use Rbs\MoyskladStocks\Debug;
use Rbs\MoyskladStocks\AgentManager;
use Rbs\MoyskladStocks\LangMsg;
use Rbs\MoyskladStocks\Utils;

abstract class Base
{
	protected $logger;
	protected $agentManager;
	protected $params;
	protected static $minimalChunckSize = 100;

	public function __construct()
	{
		$this->logger = new Debug\Loger();
		$this->agentManager = new AgentManager($this->getAgentManagerId());
		$this->initializeAgentManager();
	}

	public function execute()
	{
		try {

			$this->validateProcess();
			$this->prepareParams();

			if ($this->isNeedChunkRequests()) {
				$this->processInChunks();
			} else {
				$response = $this->fetchData();
				if(!$this->hasResponseErrors($response)) {
					$this->processResponse($response);
				}				
			}

		} catch (\Throwable $e) {

			$this->logger->addErrorMessage(Utils::build_exception_message($e));

		}

		$this->finalizeProcess();
		return $this->getResult();
	}

	private function isNeedChunkRequests(): bool
	{
		return !empty($this->params['expand']) && (int)($this->params['limit']) > self::$minimalChunckSize;
	}

	protected function processInChunks()
	{
		$chunkSize = self::$minimalChunckSize;
		$maxSteps = ceil($this->params['limit'] / $chunkSize);

		$this->addDebugMsg("Calculated maxSteps: $maxSteps");

		if($maxSteps > 10) {
			throw new \Bitrix\Main\SystemException(LangMsg::get('EXCEPTION_CHUNK_REQUESTS_MAX_STEPS'));
		}

		$localStep = 1;
		$isLastChunk = false;
		do {

			$this->addDebugMsg("Starting chunk processing for step: $localStep");

			$this->params['limit'] = $chunkSize;
			$isLastChunk = $localStep == $maxSteps;

			$this->addDebugMsg("Fetching data with params: " . json_encode($this->params));

			$response = $this->fetchData();

			if ($this->hasResponseErrors($response)) {
				$this->addDebugMsg("Response has errors, breaking the loop.");
				break;
			}

			if (empty($response->{'meta'}->{'nextHref'})) {
				$this->addDebugMsg("No nextHref in response, setting isLastChunk to true.");
				$isLastChunk = true;
			}

			$this->addDebugMsg("Processing response for step: $localStep");
			$this->processResponse($response, $isLastChunk);
			
			$this->params['offset'] += $chunkSize;
			$localStep++;

		} while (!$isLastChunk);

		$offset = $this->agentManager->getOffset();
		$this->addDebugMsg("Offset after last chunk: $offset");
	}


	private function addDebugMsg(string $msg)
	{
		//if(Config::checkFeature('debug_new_process_import')) {
			//$this->logger->addInfoMessage($msg);
		//}
	}

	protected function prepareParams()
	{
		$this->params = $this->buildParams();

		if(!empty($this->params['filter'])) {
			$this->logger->addInfoMessage(LangMsg::buildAgentFilterMessage($this->params['filter']));
		}
		if (empty($this->params['limit']) || (int)$this->params['limit'] <= 0) {
			throw new \Bitrix\Main\SystemException(LangMsg::get('EXCEPTION_LIMIT_EMPTY'));
		}

		$this->addDebugMsg("Prepared params: " . json_encode($this->params));
	}

	protected function hasResponseErrors($response): bool
	{
		if (!Utils::is_success(($response))) {
			if (Utils::has_errors($response)) {
				$this->logger->addErrorMessageArray($response->{'errors'});
				return true;
			} else {
				throw new \Bitrix\Main\SystemException(LangMsg::get('EXCEPTION_API_ERROR'));
			}
		}
		return false;
	}

	protected function processResponse($response, bool $isLastChunk = true)
	{
		$this->prepareResponse($response);

		if (!empty($response->{'meta'}->{'size'})) {
			$this->agentManager->setSize($response->{'meta'}->{'size'});
		}

		if (Utils::array_exists($response)) {
			$this->processRows($response->{'rows'});
		} else {
			$this->handleEmptyRows();
		}

		if($isLastChunk) {

			if (!empty($response->{'meta'}->{'nextHref'})) {
				$this->agentManager->setNextStepOffset();
			} else {
				$this->agentManager->setFinalStepParams();
			}

			$this->processLastStepActions($response);

		}
	}

	protected function handleEmptyRows()
	{
		if ($this->agentManager->isFullUpdate()) {
			$this->logger->addWarningMessage(LangMsg::get('WARNING_EMPTY_ROWS'));
		} else {
			$this->logger->addInfoMessage(LangMsg::get('INFO_EMPTY_ROWS'));
		}
	}

	protected function finalizeProcess()
	{
		$this->logger->addFinishMessage(LangMsg::buildAgentFinishMessage($this->logger->getLogTime()));
		$this->logger->exportLog(LangMsg::buildAgentHeadMessage($this->agentManager));
	}

	public function getResult(): object
	{
		return (object)[
			'logger' => $this->logger,
			'agentManager' => $this->agentManager
		];
	}

	abstract protected function initializeAgentManager();
	abstract protected function validateProcess();
	abstract protected function getAgentManagerId(): string;
	abstract protected function buildParams(): array;
	abstract protected function fetchData();
	abstract protected function processRows(array $rows);
	abstract protected function prepareResponse($response);
	abstract protected function processLastStepActions($response);
}