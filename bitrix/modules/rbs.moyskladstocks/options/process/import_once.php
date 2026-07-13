<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

if (empty($processEntity)) {
	throw new \Bitrix\Main\SystemException(GetMessage('IMPORT_ONCE_ERROR_ENTITY'));
}

if ($moduleAccessLevel !== "W") {
	throw new \Bitrix\Main\SystemException(GetMessage('ACCESS_WRITE_ERROR'));
}

use Rbs\MoyskladStocks\Internals\ProcessManager;
use Rbs\MoyskladStocks\AgentManager;

$process = new ProcessManager("import_once_{$processEntity}");

$isExtCodesProcess = $processEntity === 'update_ext_codes';
$isPriceProcess = mb_strpos($processEntity, '_price') !== false;
$isPfolderProcess = $processEntity === 'productfolder';
$isStocksProcess = $processEntity === 'stocks';
$isDiscountProcess = $processEntity === 'discount';
$isBundleStocksProcess = $processEntity === 'bundle_stocks';

switch ($process->getProcessState()) {
	case ProcessManager::PROCESS_START:

		$agentManager = new AgentManager($processEntity);
		$process->saveStateParam("limit", $agentManager->getLimit());

		$defaultLimit = 100;
		if($isStocksProcess || $isPriceProcess || $isExtCodesProcess) {
			$defaultLimit = 500;
		}
		if($isDiscountProcess) {
			$defaultLimit = 10;
		}
		if($isPfolderProcess) {
			$defaultLimit = 1000;
		}

		$agentManager->setTag('import_once');
		$agentManager->setFullOnce();
		$agentManager->setConfigValue('limit', $defaultLimit);
		$agentManager->refreshOffset();

		CAdminMessage::ShowMessage([
			'MESSAGE' => GetMessage("IMPORT_PROCCESS_PRELOAD"),
			'TYPE' => 'OK'
		]);
		
		$process->switchModuleOff();
		$process->setProcessWork();

		break;
	case ProcessManager::PROCESS_WORK:

		if($isExtCodesProcess) {
			$importResult = \Rbs\MoyskladStocks\Import\Type\Assortment::import_ext_codes();
		} else if($isPfolderProcess) {
			$importResult = \Rbs\MoyskladStocks\Import\Productfolder::import();
		} else if($isDiscountProcess) {
			$importResult = \Rbs\MoyskladStocks\Import\Discount::import();
		} else if($isBundleStocksProcess) {
			$importResult = \CRbsMoyskladStocks::import_bundle_stocks();
		} else if($isStocksProcess) {
			$importResult = \Rbs\MoyskladStocks\Import\Type\Stocks::import();
		} else if ($isPriceProcess) {
			$entity = explode('_', $processEntity)[0];
			$importResult = \Rbs\MoyskladStocks\Import\Type\Prices::import($entity);
		} else {
			$importResult = \CRbsMoyskladStocks::import_entity($processEntity);
		}

		$agentManager = $importResult->agentManager;
		$logger = $importResult->logger;

		CAdminMessage::ShowMessage(array(
			"MESSAGE" => GetMessage(
				"IMPORT_PROCCESS_COUNT",
				[
					'#IN_PROCCESS#' => $agentManager->getCurrentStep(),
					'#SIZE#' => $agentManager->getSize()
				]
			),
			"DETAILS" => "#PROGRESS_BAR#",
			"HTML" => true,
			"TYPE" => "PROGRESS",
			"PROGRESS_TOTAL" => $agentManager->getSize(),
			"PROGRESS_VALUE" => $agentManager->getCurrentStep(),
		));

		if ($logger->hasErrors()) {
			CAdminMessage::ShowMessage([
				'MESSAGE' => GetMessage("IMPORT_PROCCESS_HAS_ERRORS")
			]);
		}

		//last step
		if ($agentManager->getOffset() === 0) {
			$agentManager->setConfigValue('limit', $process->getStateParam("limit", '100'));
			$agentManager->unSetTag();
			$process->setProcessFinish();
		}

		$process->nextStep();

		break;
	case ProcessManager::PROCESS_FINISH:

		CAdminMessage::ShowMessage([
			'MESSAGE' => GetMessage("IMPORT_PROCCESS_DONE"),
			'TYPE' => 'OK'
		]);

		$process->switchModuleOn();
		$process->finishProcess();

		break;
	default:
		CAdminMessage::ShowMessage([
			'MESSAGE' => GetMessage("IMPORT_ONCE_ERROR_ENTITY")
		]);
}
