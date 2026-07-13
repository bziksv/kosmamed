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

\Bitrix\Main\UI\Extension::load("ui.forms");
\Bitrix\Main\UI\Extension::load("ui.buttons");

use \Rbs\Moysklad\Internals\ProcessManager;
use \Rbs\Moysklad\AgentManager;
use \Rbs\Moysklad\Internals\State;

$process = new ProcessManager("import_once_{$processEntity}", ProcessManager::PROCESS_BEFORE_START);

switch ($process->getProcessState()) {
	case ProcessManager::PROCESS_BEFORE_START:
		$process->setProcessState(ProcessManager::PROCESS_START);
?>
		<form action="" method="GET">
			<? foreach ($process->getBaseParams() as $paramId => $paramValue) : ?>
				<input type="hidden" name="<?= $paramId ?>" value="<?= $paramValue ?>">
			<? endforeach ?>
			<? foreach ($process->getProcessParams() as $paramId => $paramValue) : ?>
				<input type="hidden" name="<?= $paramId ?>" value="<?= $paramValue ?>">
			<? endforeach ?>
			<label for=""><b><?= GetMessage('LABEL_IMPORT_ONCE_DATE_ORDER_CREATED') ?></b></label><br><br>
			<div class="ui-ctl ui-ctl-textbox ui-ctl-inline">
				<input type="date" class="ui-ctl-element" name="date_created">
			</div>
			<br><br>
			<label for=""><b><?= GetMessage('LABEL_IMPORT_ONCE_LIMIT') ?></b></label><br><br>
			<div class="ui-ctl ui-ctl-textbox ui-ctl-inline">
				<input type="number" class="ui-ctl-element" name="limit_process" value="50" min="1" max="100">
			</div>
			<br><br>
			<button type="submit" class="ui-btn ui-btn-primary"><?= GetMessage('IMPORT_ONCE_BTN') ?></button>
		</form>
		<?


		break;
	case ProcessManager::PROCESS_START:

		$agentManager = new AgentManager('import_' . $processEntity);
		$process->saveStateParam("limit", $agentManager->getLimit());

		$defaultLimit = $request->get('limit_process') > 0 && $request->get('limit_process') <= 100 ? $request->get('limit_process') : 50;

		$agentManager->setTag('import_once');
		$agentManager->setFullOnce();
		$agentManager->setConfigValue('limit', $defaultLimit);
		$agentManager->refreshOffset();

		if (empty($request->get('date_created'))) {

			CAdminMessage::ShowMessage([
				'MESSAGE' => GetMessage('IMPORT_PROCCESS_PRELOAD_ERROR', [
					'#ERROR#' => GetMessage('EMPTY_DATE_CREATED')
				])
			]);

		?>
			<button type="button" class="ui-btn ui-btn-primary" onclick="window.history.back();"><?= GetMessage('BACK_BUTTON') ?></button>
		<?

		} else {


			$date = new \DateTime($request->get('date_created'));

			$agentManager->setConfigValue('last_update', $date->format('Y-m-d 00:00:01'));

			CAdminMessage::ShowMessage([
				'MESSAGE' => GetMessage('IMPORT_PROCCESS_PRELOAD'),
				'TYPE' => 'OK'
			]);
			$process->switchModuleOff();
			$process->setProcessWork();
		}

		break;
	case ProcessManager::PROCESS_WORK:

		State::getInstance()->setState('import_once_process', 'customerorder');

		$importResult = \Rbs\Moysklad\Controller\ImportController::import_customerorder();

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
