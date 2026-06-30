<?

namespace Rbs\MoyskladStocks\Import\Type;

use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\ApiNew;
use Rbs\MoyskladStocks\HlCache;
use Rbs\MoyskladStocks\Debug;
use Rbs\MoyskladStocks\LangMsg;
use Rbs\MoyskladStocks\AgentManager;
use Rbs\MoyskladStocks\Controller\CachedCurrStocks;
use Rbs\MoyskladStocks\Controller\IblockCache;
use Rbs\MoyskladStocks\Services\StocksUtils;
use Rbs\MoyskladStocks\Services\ConfigurationUtils;
use Rbs\MoyskladStocks\Internals\IblockElementUpdaterForEvents;
use Rbs\MoyskladStocks\Internals\ProductFinder\ProductIdentifier;
use Rbs\MoyskladStocks\Internals\ProductFinder\StocksProductFinder;

class CurrentStocks
{
	public static function import()
	{
		$agentManager = new AgentManager('curr_stocks');
		$agentManager->setOnlyUpdated();

		$maxDiffUpdateSeconds = (int)Config::getOption('curr_stocks_max_diff_seconds', 1439);
		if($maxDiffUpdateSeconds > 1439 || $maxDiffUpdateSeconds < 0) {
			$maxDiffUpdateSeconds = 1439;
		}

		$agentManager->setMaxDiffUpdateMinutes($maxDiffUpdateSeconds);
		$logger = new Debug\Loger();

		try {

			$paramsCurrentStocks = Config::getCurrentStocksParams();

			if (!Utils::is_count($paramsCurrentStocks['entity_type'])) {
				throw new \Bitrix\Main\SystemException(LangMsg::get('WARNING_EMPTY_ENTITY'));
			}

			$needExtCodes = ProductIdentifier::isExtCodesRequired();
			if (!HlCache\Stocks::isExsist() || ($needExtCodes && !HlCache\ExtCodes::isExsist())) {
				throw new \Bitrix\Main\SystemException(LangMsg::get('WARNING_EMPTY_HL_CACHE'));
			}

			if (!Utils::is_count(ConfigurationUtils::getStoreList('current'))) {
				throw new \Bitrix\Main\SystemException(LangMsg::get('WARNING_EMPTY_STORES_FOR_IMPORT'));
			}

			$params = [
				'include' => 'zeroLines',
				'stockType' => $paramsCurrentStocks['stock_type']
			];

			if(!$agentManager->isFullUpdate()) {
				$params['changedSince'] = $agentManager->getLastDateUpdate();
				unset($params['include']);
				$logger->addInfoMessage(LangMsg::get('INFO_CURR_STOCKS_SOFT_UPDATE', [
					'#UPDATE_PERIOD#' => $params['changedSince']
				]));
			} else {
				$logger->addInfoMessage(LangMsg::get('INFO_CURR_STOCKS_FULL_UPDATE'));
			}

			if(!empty($params['filter'])) {
				$logger->addInfoMessage(LangMsg::buildAgentFilterMessage($params['filter']));
			}

			ApiNew::refreshCountRequests();

			if (StocksUtils::isStocksWithChild(StocksUtils::STOCKS_TYPE_CURRENT)) {
				StocksUtils::updateParentStoresChildTree(StocksUtils::STOCKS_TYPE_CURRENT);
			}
			
			$cachedCurrStocks = new CachedCurrStocks($params, $agentManager->isFullUpdate());
			$msResult = $cachedCurrStocks->getNextChunk();

			if (!Utils::is_count($msResult)) {

				if (Utils::has_errors($msResult)) {
					$logger->addErrorMessageArray($msResult->{'errors'});
				} elseif (is_array($msResult)) {
					$message = $agentManager->isFullUpdate() ? 'WARNING_EMPTY_ROWS' : 'INFO_EMPTY_ROWS';
					$logMethod = $agentManager->isFullUpdate() ? 'addWarningMessage' : 'addInfoMessage';
					$logger->{$logMethod}(LangMsg::get($message));
				} else {
					throw new \Bitrix\Main\SystemException(LangMsg::get('EXCEPTION_API_ERROR'));
				}

			} else {

				$agentManager->setSize($cachedCurrStocks->getTotalItems());
				$updateType = $agentManager->isFullUpdate() ? 'hard' : 'soft';

				$counterHlCurrentStocks = HlCache\CurrentStocks::update($msResult, $paramsCurrentStocks['stock_type'], $updateType);
				$logger->addInfoMessage(LangMsg::get('COUNTER_INFO', $counterHlCurrentStocks->getReport()));

				if ($counterHlCurrentStocks->hasErrors()) {
					$logger->addErrorMessageArray($counterHlCurrentStocks->getErrorMessageArray());
				}

				if ($cachedCurrStocks->hasCachedData()) {
					$logger->addInfoMessage(LangMsg::get('INFO_NOT_LAST_CHUNK', [
						'#TOTAL_ITEMS#' => $cachedCurrStocks->getTotalItems(),
						'#CURRENT_STEP#' => $cachedCurrStocks->getCurrentStep(),
					]));
				}

				if ($cachedCurrStocks->isLastChunk()) {
					$agentManager->setFinalStepParams();
					$cachedCurrStocks->clearCache();
				}

				if ($cachedCurrStocks->isLastChunkOfFullUpdate()) {
					$fullUpdateStartTime = $cachedCurrStocks->getFullUpdateStartTime();
					if(!empty($fullUpdateStartTime)) {
						$date = \Bitrix\Main\Type\DateTime::createFromTimestamp($fullUpdateStartTime);
						$deletedCount = HlCache\CurrentStocks::deleteOutdatedStocks($fullUpdateStartTime);
						$logger->addInfoMessage(LangMsg::get('INFO_DELETE_OUTDATED_STOCKS', [
							'#DELETED_COUNT#' => $deletedCount,
							'#TIMESTAMP#' => $date->format('d.m.Y H:i:s')
						]));
					}					
				}

			}

			if (!$cachedCurrStocks->hasCachedData()) {
				self::update_all_stocks($logger);
			}

		} catch (\Throwable $e) {
			$logger->addErrorMessage(Utils::build_exception_message($e));
		}

		$logger->addFinishMessage(LangMsg::buildAgentFinishMessage($logger->getLogTime()));
		$logger->exportLog(LangMsg::buildAgentHeadMessage($agentManager, 'AGENT_START_ENTITY_SIMPLE'));
	}

	public static function update_entity_stocks_from_rows(array $rows = [], string $entity = 'product', Debug\Loger &$loger) 
	{
		$paramsCurrentStocks = Config::getCurrentStocksParams();

		$entityXmlIds = [];
		foreach($rows as $row) {
			$entityXmlIds[$row->id] = ProductIdentifier::getIdentifierValue($row);
		}

		$stocks = HlCache\CurrentStocks::getForUpdateStocksByAssortmentIds($paramsCurrentStocks['stock_type'], $paramsCurrentStocks['limit'], array_keys($entityXmlIds));
		
		if (!Utils::is_count($stocks)) {
			$loger->addInfoMessage(LangMsg::get('WARNING_HL_EMPTY_CURR_STOCKS_FOR_UPD'));
			return;
		}

		self::update_entity_stocks($entity, $loger, $stocks, $entityXmlIds);
	}

	private static function update_all_stocks(Debug\Loger &$loger)
	{
		$paramsCurrentStocks = Config::getCurrentStocksParams();

		$stocks = HlCache\CurrentStocks::getForUpdateStocks($paramsCurrentStocks['stock_type'], $paramsCurrentStocks['limit']);		
		
		if (!Utils::is_count($stocks)) {
			IblockCache::handleCacheClear($loger, 'current_stocks');
			$loger->addInfoMessage(LangMsg::get('WARNING_HL_EMPTY_CURR_STOCKS_FOR_UPD'));
			return;
		}

		$assortmentIds = array_keys($stocks);
		
		if (ProductIdentifier::isExtCodesRequired()) {
			$xmlIdsResult = HlCache\ExtCodes::getExternalCodesForAssortmentIds($assortmentIds);
		} else {
			$xmlIdsResult = ProductIdentifier::groupAssortmentIdsByEntityType($assortmentIds);
		}

		if(Utils::is_count($xmlIdsResult['unfinded'])){
			$loger->addWarningMessage(LangMsg::get('INFO_UNFINDED_ASSORTMENT_IDS', [
				'#UNFINDED_COUNT#' => count($xmlIdsResult['unfinded']),
				'#FIRST_TEN_ITEMS#' => implode(', ', array_slice($xmlIdsResult['unfinded'], 0, 10))
			]));
			HlCache\CurrentStocks::setUpdatedStocksByAssortmentIds($xmlIdsResult['unfinded']);
		}
		
		foreach($paramsCurrentStocks['entity_type'] as $entity) {
			
			if($entity === 'bundle'){
				continue;
			}

			if(!Utils::is_count($xmlIdsResult[$entity])){
				continue;
			}

			$loger->addInfoMessage(LangMsg::get('WORK_WITH_CURR_STOCK_IMPORT', [
				'#ENTITY#' => $entity,
			]));

			self::update_entity_stocks($entity, $loger, $stocks, $xmlIdsResult[$entity]);
			
		}
	}

	private static function update_entity_stocks(string $entity = 'product', Debug\Loger &$loger, array $stocks = [], array $entityXmlIds = [])
	{
		if(!Utils::is_count($entityXmlIds) || !Utils::is_count($stocks)){
			return;
		}

		$storeList = ConfigurationUtils::getStoreList('current');
		if (!Utils::is_count($storeList)) {
			throw new \Bitrix\Main\SystemException(LangMsg::get('WARNING_EMPTY_STORES_FOR_IMPORT'));
		}

		$uniqStoreAssoc = [];
		if (!empty($storeList)) {
			$uniqStoreAssoc = array_diff(StocksUtils::getStoreIdsForImport(StocksUtils::STOCKS_TYPE_CURRENT), ['all_stocks']);
		}    

		$catalogIblockId = "-1";
		if (Config::isIblockRequired($entity)) {
			$catalogIblockId = Config::getIblockId($entity);
			if ((int)$catalogIblockId <= 0) {
				throw new \Bitrix\Main\SystemException(LangMsg::get('WARNING_EMPTY_CATALOG_IBLOCK'));
			}
		}

		$paramsCurrentStocks = Config::getCurrentStocksParams();

		$msStocksInfoByXmlId = [];
		$updatedIds = [];

		$parentStores = StocksUtils::getParentStores(StocksUtils::STOCKS_TYPE_CURRENT);
		$needProcessParentStores = StocksUtils::isStocksWithChild(StocksUtils::STOCKS_TYPE_CURRENT) && count($parentStores) > 0;
		$stockType = $paramsCurrentStocks['stock_type'];

		foreach ($stocks as $assortmentId => $stocksInfo) {
			if (Utils::is_count($stocksInfo)) {
				
				foreach ($uniqStoreAssoc as $storeId) {
					if (!isset($stocksInfo[$storeId])) {
						$stocksInfo[$storeId] = [
							$stockType => 0,
							'ID' => null
						];
					}
				}
				
				foreach($stocksInfo as $storeId => $infoQty){
					if (isset($entityXmlIds[$assortmentId]) && !empty($entityXmlIds[$assortmentId])) {

						$assortmentXmlId = $entityXmlIds[$assortmentId];
						$currentStock = $infoQty[$stockType];

						//all_stocks
						if (!isset($msStocksInfoByXmlId[$assortmentXmlId]['all_stocks'])) {
							$msStocksInfoByXmlId[$assortmentXmlId]['all_stocks'][$stockType] = 0;
						}
						$msStocksInfoByXmlId[$assortmentXmlId]['all_stocks'][$stockType] += $currentStock;

						if($needProcessParentStores && isset($parentStores[$storeId])) {
							foreach ($stocksInfo as $childStoreId => $childInfoQty) {
								if(in_array($childStoreId, $parentStores[$storeId])) {
									$currentStock += $childInfoQty[$stockType];
								}
							}							
						}

						$msStocksInfoByXmlId[$assortmentXmlId][$storeId][$stockType] = $currentStock;
						
						if ($infoQty['ID'] !== null) {
							$updatedIds[] = $infoQty['ID'];
						}

					}				
				}
			}
		}
		unset($stockType);

		if(Utils::is_count($updatedIds)) {
			HlCache\CurrentStocks::setUpdatedStocks($updatedIds);
		}

		if (!Utils::is_count($msStocksInfoByXmlId)) {
			$loger->addMessage(LangMsg::get('WARNING_EMPTY_STOCKS_XML_IDS_IMPORT'), Debug\Message::TYPE_WARNING);
			return;
		}

		$xmlIds = array_keys($msStocksInfoByXmlId);

		$doubleStockType = $paramsCurrentStocks['double_type'];

		$additionalFilter = [];
		if ($catalogIblockId !== "-1") {
			$additionalFilter['IBLOCK_ID'] = $catalogIblockId;
		}
		$doublesCheckList = StocksProductFinder::findElementsWithDoubles(
			$xmlIds, $entity, $additionalFilter, $doubleStockType
		);
		if (!Utils::is_count($doublesCheckList)) {
			$loger->addMessage(LangMsg::get('WARNING_EMPTY_STOCKS_ELEMENTS_IMPORT'), Debug\Message::TYPE_WARNING);
			return;
		}

		$sourceElementCount = array_sum(array_map('count', $doublesCheckList));

		$productInfoArray = [];
		$doubleElementCount = 0;
		foreach ($doublesCheckList as $xmlId => $elements) {
			if (isset($msStocksInfoByXmlId[$xmlId])) {

				$isDouble = count($elements) > 1;

				switch ($doubleStockType) {
					case 'ASC':
					case 'DESC':
						$productInfoArray[array_key_first($elements)] = [
							'MS_STOCKS' => $msStocksInfoByXmlId[$xmlId],
							'BX_STOCKS' => []
						];
						break;
					case 'ALL':
						foreach ($elements as $elId => $element) {
							$productInfoArray[$elId] = [
								'MS_STOCKS' => $msStocksInfoByXmlId[$xmlId],
								'BX_STOCKS' => []
							];
						}
						break;
					case 'SKIP':
						if (!$isDouble) {
							$productInfoArray[array_key_first($elements)] = [
								'MS_STOCKS' => $msStocksInfoByXmlId[$xmlId],
								'BX_STOCKS' => []
							];
						}
						break;
				}

				if ($isDouble) {
					$doubleElementCount++;
				}
			}
		}
		unset($doublesCheckList);
		unset($msStocksInfoByXmlId);

		if ($doubleElementCount > 0) {
			$loger->addMessage(LangMsg::get('WARNING_STOCKS_IMOPORT_DOUBLES', [
				'#ELEMENT_COUNT#' => $sourceElementCount,
				'#DOUBLE_ELEMENT_COUNT#' => $doubleElementCount,
			]), Debug\Message::TYPE_WARNING);
		}

		$currentIds = array_keys($productInfoArray);

		$rsProducts = \Bitrix\Catalog\ProductTable::getList([
			'filter' => ['=ID' => $currentIds],
			'select' => ['ID', 'QUANTITY']
		])->fetchAll();

		$productQuantityArray = [];
		if (Utils::is_count($rsProducts)) {
			foreach ($rsProducts as $product) {
				$productQuantityArray[$product['ID']] = (float)$product['QUANTITY'];
			}
		}
		unset($rsProducts);

		if (!Utils::is_count($productQuantityArray)) {
			$loger->addMessage(LangMsg::get('WARNING_EMPTY_STOCKS_PRODUCTS_IMPORT'), Debug\Message::TYPE_WARNING);
			return;
		}

		if (count($productQuantityArray) !== count($productInfoArray)) {
			$loger->addMessage(LangMsg::get('WARNING_DIFF_STOCKS_PRODUCTS_AND_ELEMENTS_IMPORT'), Debug\Message::TYPE_WARNING);
		}

		foreach ($productInfoArray as $elId => $params) {
			if (!isset($productQuantityArray[$elId])) {
				unset($productInfoArray[$elId]);
				continue;
			}
			$productInfoArray[$elId]['BX_QUANTITY'] = (float)$productQuantityArray[$elId];
		}
		unset($productQuantityArray);

		$typeStocks = $paramsCurrentStocks['stock_type'];
		$qtyStocks = $paramsCurrentStocks['qty_type'];

		$filterStocks = ['PRODUCT_ID' => $currentIds, 'STORE_ID' => array_keys($storeList)];
		if ($qtyStocks === 'ALL_BX') {
			unset($filterStocks['STORE_ID']);
		}
		$currentStocks = \Bitrix\Catalog\StoreProductTable::getList(['filter' => $filterStocks])->fetchAll();

		if (Utils::is_count($currentStocks)) {
			foreach ($currentStocks as $stockBxInfo) {
				$productInfoArray[$stockBxInfo['PRODUCT_ID']]['BX_STOCKS'][$stockBxInfo['STORE_ID']] = $stockBxInfo;
			}
		}
		unset($currentStocks);

		$counterProductChange = new Debug\Counter(LangMsg::get('COUNTER_PRODUCT_UPDATE'));
		$counterStocksChange = new Debug\Counter(LangMsg::get('COUNTER_STOCKS_UPDATE'));
		
		foreach ($productInfoArray as $productId => $info) {

			$msStocks = $info['MS_STOCKS'];
			$bxQuantity = $info['BX_QUANTITY'];
			$bxStocks = $info['BX_STOCKS'];

			$qtyByStocks = ['ALL' => 0];

			$updateStockInfoArray = [
				'add' => [],
				'update' => []
			];

			foreach ($storeList as $bxStoreId => $msStoreId) {

				if (!isset($msStocks[$msStoreId][$typeStocks])) {
					continue;
				}

				$counterStocksChange->count();

				$currentMsStock = (float)$msStocks[$msStoreId][$typeStocks];
				$currentBxStocks = 0;

				if (isset($bxStocks[$bxStoreId])) {
					$currentBxStocks = (float)$bxStocks[$bxStoreId]['AMOUNT'];
					if ($currentMsStock !== $currentBxStocks) {
						$updateStockInfoArray['update'][$bxStocks[$bxStoreId]['ID']] = [
							'AMOUNT' => $currentMsStock
						];
						$bxStocks[$bxStoreId]['AMOUNT'] = $currentMsStock;
					}
				} else {
					$updateStockInfoArray['add'][] = [
						'STORE_ID' => $bxStoreId,
						'PRODUCT_ID' => $productId,
						'AMOUNT' => $currentMsStock
					];
					$bxStocks[$bxStoreId]['AMOUNT'] = $currentMsStock;
				}

				$qtyByStocks[$bxStoreId] = $currentMsStock;
			}

			foreach ($updateStockInfoArray as $typeAction => $actionParams) {
				foreach ($actionParams as $id => $params) {
					switch ($typeAction) {
						case 'add':
							$counterStocksChange->add();
							\Bitrix\Catalog\StoreProductTable::add($params);
							break;
						case 'update':
							$counterStocksChange->update();
							\Bitrix\Catalog\StoreProductTable::update($id, $params);
							break;
					}
				}
			}

			if ($qtyStocks === 'ALL') {
				foreach ($storeList as $bxStoreId => $msStoreId) {
					if(isset($bxStocks[$bxStoreId]['AMOUNT'])) {
						$qtyByStocks['ALL'] += (float)$bxStocks[$bxStoreId]['AMOUNT'];
					}
				}
			}

			if ($qtyStocks === 'ALL_BX') {
				$qtyByStocks['ALL_BX'] = 0;
				foreach ($bxStocks as $bxStockInfo) {
					$qtyByStocks['ALL_BX'] += (float)$bxStockInfo['AMOUNT'];
				}
			}

			$arUpdateProductFields = [];
			if (isset($qtyByStocks[$qtyStocks]) && $bxQuantity !== $qtyByStocks[$qtyStocks]) {
				$arUpdateProductFields['QUANTITY'] = (float)$qtyByStocks[$qtyStocks];
			}

			$event = new \Bitrix\Main\Event(\Rbs\MoyskladStocks\Config::getModuleId(true), "OnBeforeUpdateProductInCurrentStockRow", array(

				'arUpdateProductFields' => $arUpdateProductFields,
				'productBx' => $productId,
				'currentStocks' => $bxStocks,

				'productInfoStocks' => $info

			));

			$event->send();

			if ($event->getResults()) {
				foreach ($event->getResults() as $eventResult) {
					if ($eventResult->getType() == \Bitrix\Main\EventResult::SUCCESS) {
						$arUpdateProductFields = $eventResult->getParameters();
					}
				}
			}

			$counterProductChange->count();

			if (count($arUpdateProductFields) > 0) {
				$counterProductChange->update();
				\Bitrix\Catalog\Model\Product::update($productId, $arUpdateProductFields);
				IblockElementUpdaterForEvents::updateFromCurrentStocks($productId);
			}
		}

		$productReport = $counterProductChange->getReport();
		$stocksReport = $counterStocksChange->getReport();

		if($productReport['update'] > 0 || $stocksReport['add'] > 0 || $stocksReport['update'] > 0) {
			IblockCache::resetState('current_stocks');
		}

		$loger->addInfoMessage(LangMsg::get('COUNTER_INFO_UPDATE', $productReport));
		$loger->addInfoMessage(LangMsg::get('COUNTER_INFO_ADD_UPDATE', $stocksReport));

	}
}