<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\Engine\Response\AjaxJson;
use Bitrix\Main\Loader;
use Bitrix\Main\Context;
use Bitrix\Main\Web\Json;

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');
Loader::includeModule('up.boxberrydelivery');

if (isset($_POST['select_pvz_id']) && !empty($_POST['select_pvz_id'])) {
	CBoxberry::updateOrder($_POST['select_pvz_id'], $_POST['order_id'], (isset($_POST['address']) ? $_POST['address'] : NULL));
}

if (isset($_POST['save_pvz_id']) && !empty($_POST['save_pvz_id'])) {
	session_start();

	CBoxberry::savePvz($_POST['save_pvz_id']);
	echo true;
}

if (isset($_POST['remove_pvz']) && !empty($_POST['remove_pvz'])) {
	CBoxberry::removePvz();
	echo true;
}

if (isset($_POST['check_pvz']) && !empty($_POST['check_pvz'])) {
	CBoxberry::checkPvz();
	echo true;
}

if (isset($_POST['disable_check_pvz']) && !empty($_POST['disable_check_pvz'])) {
	CBoxberry::disableCheckPvz();
	echo true;
}

if (isset($_POST['get_link']) && $profile = CDeliveryBoxberry::getDeliveryCode($_POST['get_link'])) {
	echo CDeliveryBoxberry::makeWidgetLink($profile);
}

if (check_bitrix_sessid()) {
	if (isset($_POST['bb_reception_point_search'])) {
		$result = ReceptionPointsTable::getReceptionPoints($_POST['bb_reception_point_search']);
		header('Content-Type: application/json');
		echo Json::encode([
			'status' => !$result->isSuccess() ? 'error' : 'success',
			'data' => !$result->isSuccess() ? null : $result->getData(),
			'errors' => !$result->isSuccess() ? $result->getErrorCollection() : []
		], JSON_UNESCAPED_UNICODE);
	}

	if (isset($_POST['bb_get_reception_code_by_name'])) {
		$result = ReceptionPointsTable::getPointCodeByName($_POST['bb_get_reception_code_by_name']);
		header('Content-Type: application/json');
		echo Json::encode([
			'status' => !$result ? 'error' : 'success',
			'data' => !$result ? null : ['code' => $result],
		], JSON_UNESCAPED_UNICODE);

	}

	if (!empty($_POST['bb_ya_delivery'])) {
		CBoxberry::initApi($_POST['bb_ya_delivery']);
		$widgetKey = CBoxberry::getKeyIntegration();
		$authorization = (CBoxberry::getResponseCode() === 200);
		$result = [
			'yaDelivery' => false,
			'authorization' => $authorization,
			'reset_reception_point' => false
		];

		if (isset($widgetKey['key'])) {
			CBoxberry::initSource();
			$yaDelivery = CBoxberry::isYaDelivery();
			$result['yaDelivery'] = $yaDelivery;

			if ($yaDelivery === true && (ReceptionPointsTable::getReceptionPointsSource() === CBoxberry::SOURCE_BOXBERRY)) {
				CBoxberry::resetModuleApiData($_POST['bb_ya_delivery']);
				$result['reset_reception_point'] = true;
			}

			if ($yaDelivery === false && (ReceptionPointsTable::getReceptionPointsSource() === CBoxberry::SOURCE_YANDEX)) {
				CBoxberry::resetModuleApiData($_POST['bb_ya_delivery']);
				$result['reset_reception_point'] = true;
			}

			if (ReceptionPointsTable::isEmpty()) {
				CBoxberryAgents::loadReceptionPoints($_POST['bb_ya_delivery']);
			}
		}

		header('Content-Type: application/json');
		echo Json::encode($result);

	}
}


?>
