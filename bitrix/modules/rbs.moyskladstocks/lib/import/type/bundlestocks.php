<?php

namespace Rbs\MoyskladStocks\Import\Type;

use Rbs\MoyskladStocks\HlCache\Stocks as StocksHl;
use Rbs\MoyskladStocks\HlCache\CurrentStocks as CurrStocksHl;
use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Debug;
use Rbs\MoyskladStocks\LangMsg;
use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\Debug\Counter;
use Rbs\MoyskladStocks\Services\StocksUtils;
use Rbs\MoyskladStocks\Services\ConfigurationUtils;
use Rbs\MoyskladStocks\Internals\ProductFinder\ProductIdentifier;

\Bitrix\Main\Loader::includeModule('catalog');
\Bitrix\Main\Loader::includeModule('iblock');

class BundleStocks
{
	public static function update($arBundles = [], Debug\Loger &$loger, $typeOfStocks = 'default')
	{
		$entity = 'bundle';

		if (!Utils::is_count($arBundles)) {
			$loger->addMessage(LangMsg::get('WARNING_EMPTY_ENTITY'), Debug\Message::TYPE_WARNING);
			return;
		}

		$loger->addMessage(LangMsg::get('WORK_WITH_BUNDLE_STOCKS_IMPORT', [
			'#TYPE_OF_STOCKS#' => $typeOfStocks,
		]), Debug\Message::TYPE_INFO);

		$storeList = ConfigurationUtils::getStoreList($typeOfStocks);
		if (!Utils::is_count($storeList)) {
			$loger->addMessage(LangMsg::get('WARNING_EMPTY_STORES_FOR_IMPORT'), Debug\Message::TYPE_WARNING);
			return;
		}

		$catalogIblockId = 0;
		$fModifIblock = '>';
		if (Config::isIblockRequired($entity)) {
			$catalogIblockId = Config::getIblockId($entity);
			$fModifIblock = '=';
			if ((int)$catalogIblockId <= 0) {
				$loger->addMessage(LangMsg::get('WARNING_EMPTY_CATALOG_IBLOCK'), Debug\Message::TYPE_WARNING);
				return;
			}
		}

		if ($typeOfStocks === 'default') {
			$typeStocks = Config::getTypeStockSyncFormated($entity);
			$qtyStocks = Config::getQtyStockSync($entity);
			$doubleStockType = Config::getDoubleStockType($entity);
		} else {
			$paramsCurrentStocks = Config::getCurrentStocksParams();
			$typeStocks = $paramsCurrentStocks['stock_type'];
			$qtyStocks = $paramsCurrentStocks['qty_type'];
			$doubleStockType = $paramsCurrentStocks['double_type'];
		}

		$bundlesXmlIdsWithComponents = [];
		$bundleComponentsByHrefToIds = [];
		$bundleComponentsByIdsToHref = [];
		foreach ($arBundles as $bundle) {
			$bundleXmlId = ProductIdentifier::getIdentifierValue($bundle);
			if (
				!empty($bundle->meta->href) &&
				!empty($bundleXmlId) &&
				!empty($bundle->components) &&
				Utils::array_exists($bundle->components, 'rows')
			) {
				foreach ($bundle->components->rows as $componentItem) {

					$productHref = $componentItem->assortment->meta->href;
					$productId = array_pop(explode('/', $productHref));
					$quantity = $componentItem->quantity;

					$bundlesXmlIdsWithComponents[$bundleXmlId][$productHref] = $quantity;
					$bundleComponentsByHrefToIds[$productHref] = $productId;
					$bundleComponentsByIdsToHref[$productId] = $productHref;

				}
			}
		}

		if(count($arBundles) !== count($bundlesXmlIdsWithComponents)) {
			$loger->addMessage(LangMsg::get('WARNING_DIFF_STOCKS_BUNDLES_AND_APROVED_BUNDLES'), Debug\Message::TYPE_WARNING);
		}

		if (count($bundleComponentsByHrefToIds) !== count($bundleComponentsByIdsToHref)) {
			$loger->addMessage(LangMsg::get('WARNING_DIFF_STOCKS_BUNDLES_HREFS'), Debug\Message::TYPE_WARNING);
			return;
		}

		unset($arBundles);

		/**get hl components stocks */
		$msStocksList = [];
		if ($typeOfStocks == 'default') {
			$stocks = StocksHl::getArray(array_values($bundleComponentsByIdsToHref));
			foreach ($stocks as $stockItem) {
				foreach($stockItem->stocks as $storeId => $stock) {
					$msStocksList[$stockItem->href][$storeId] = $stock[$typeStocks];
				}				
			}
			unset($stocks);
		} else {

			$currStocks = CurrStocksHl::getStocksByType(array_values($bundleComponentsByHrefToIds), $typeStocks);
			
			$parentStores = StocksUtils::getParentStores(StocksUtils::STOCKS_TYPE_CURRENT);
			$needProcessParentStores = StocksUtils::isStocksWithChild(StocksUtils::STOCKS_TYPE_CURRENT) && count($parentStores) > 0;
			$uniqStoreAssoc = array_diff(StocksUtils::getStoreIdsForImport(StocksUtils::STOCKS_TYPE_CURRENT), ['all_stocks']);

			foreach ($currStocks as $productId => $stockInfo) {

				if (!isset($bundleComponentsByIdsToHref[$productId])) {
					continue;
				}

				foreach ($uniqStoreAssoc as $storeId) {
					if (!isset($stockInfo[$storeId])) {
						$stockInfo[$storeId] = [
							$typeStocks => 0,
							'ID' => null
						];
					}
				}

				$productHref = $bundleComponentsByIdsToHref[$productId];
				foreach ($stockInfo as $storeId => $stock) {

					if (!isset($msStocksList[$productHref]['all_stocks'])) {
						$msStocksList[$productHref]['all_stocks'] = 0;
					}
					$msStocksList[$productHref]['all_stocks'] += $stock[$typeStocks];

					$currentStock = $stock[$typeStocks];
					if ($needProcessParentStores && isset($parentStores[$storeId])) {
						$childStores = $parentStores[$storeId];
						foreach ($childStores as $childStoreId) {
							if (isset($stockInfo[$childStoreId])) {
								$currentStock += $stockInfo[$childStoreId][$typeStocks];
							}
						}
					}
					$msStocksList[$productHref][$storeId] = $currentStock;
					
				}

			}
			
			unset($currStocks);
		}
		unset($bundleComponentsByIdsToHref);
		unset($bundleComponentsByHrefToIds);

		if (count($msStocksList) <= 0) {
			$loger->addMessage(LangMsg::get('WARNING_EMPTY_STOCKS_BUNDLE_COMPONENTS'), Debug\Message::TYPE_WARNING);
			return;
		}
		/**get hl components stocks */

		$doubleElementCount = 0;
		$elementCount = 0;

		/**get bx items ids */
		$filter = ProductIdentifier::buildBatchFilter(array_keys($bundlesXmlIdsWithComponents), $entity);
		$filter[$fModifIblock . 'IBLOCK_ID'] = $catalogIblockId;
		$bitrixSourceElements = \Bitrix\Iblock\ElementTable::getList([
			'order' => ['ID' => $doubleStockType === 'DESC' ? 'DESC' : 'ASC'],
			'filter' => $filter,
			'select' => ['ID', 'XML_ID']
		])->fetchAll();

		if(count($bitrixSourceElements) <= 0) {
			$loger->addMessage(LangMsg::get('WARNING_EMPTY_STOCKS_ELEMENTS_IMPORT'), Debug\Message::TYPE_WARNING);
			return;
		}

		$bxElementsWithDoubles = [];
		foreach ($bitrixSourceElements as $item) {
			$bxElementsWithDoubles[$item['XML_ID']][$item['ID']] = true;
		}
		unset($bitrixSourceElements);
		$bxElementIds = [];
		$bxElements = [];
		foreach ($bxElementsWithDoubles as $xmlId => $elements) {
			if (count($elements) > 1) {
				$doubleElementCount++;
				if ($doubleStockType === 'SKIP') {
					continue;
				}
			}
			foreach ($elements as $elId => $flag) {
				$bxElementIds[] = (int)$elId;
				$bxElements[$xmlId][] = (int)$elId;
				$elementCount++;
			}
		}
		unset($bxElementsWithDoubles);

		if ($doubleElementCount > 0) {
			$loger->addMessage(LangMsg::get('WARNING_STOCKS_IMOPORT_DOUBLES', [
				'#ELEMENT_COUNT#' => $elementCount,
				'#DOUBLE_ELEMENT_COUNT#' => $doubleElementCount,
			]), Debug\Message::TYPE_WARNING);
		}
		/**get bx items ids */

		/**get bx product qty */
		$productBxDb = \Bitrix\Catalog\ProductTable::getList([
			'filter' => ['ID' => $bxElementIds],
			'select' => ['ID', 'QUANTITY'],
		])->fetchAll();

		if (count($productBxDb) <= 0) {
			$loger->addMessage(LangMsg::get('WARNING_EMPTY_STOCKS_PRODUCTS_IMPORT'), Debug\Message::TYPE_WARNING);
			return;
		}

		$productBxList = [];
		foreach ($productBxDb as $product) {
			$productBxList[$product['ID']] = $product;
		}
		unset($productBxDb);
		/**get bx product qty */

		if (count($productBxList) !== count($bxElementIds)) {
			$loger->addMessage(LangMsg::get('WARNING_DIFF_STOCKS_PRODUCTS_AND_ELEMENTS_IMPORT'), Debug\Message::TYPE_WARNING);
		}

		/**get bx stocks by stores */
		$currentStocksDb = \Bitrix\Catalog\StoreProductTable::getList([
			'filter' => [
				'PRODUCT_ID' => $bxElementIds,
				'STORE_ID' => array_keys($storeList)
			]
		])->fetchAll();
		$currentStocksList = [];
		foreach ($currentStocksDb as $currentStocks) {
			$currentStocksList[$currentStocks['PRODUCT_ID']][] = $currentStocks;
		}
		unset($currentStocksDb);
		/**get bx stocks by stores */

		unset($bxElementIds);

		$counterProductChange = new Counter(LangMsg::get('COUNTER_PRODUCT_UPDATE'));
		$counterStocksChange = new Counter(LangMsg::get('COUNTER_STOCKS_UPDATE'));

		$undfoundElements = [];
		foreach ($bundlesXmlIdsWithComponents as $bundleXmlId => $components) {

			if (!isset($bxElements[$bundleXmlId]) || !Utils::is_count($bxElements[$bundleXmlId])) {
				$undfoundElements[] = $bundleXmlId;
				continue;
			}

			foreach ($bxElements[$bundleXmlId] as $bxElementId) {

				if (!isset($productBxList[$bxElementId])) {
					$loger->addWarningMessage(LangMsg::get('WARNING_EMPTY_PRODUCT_ITEM_FOR_XML_ID', [
						'#XML_ID#' => $bundleXmlId,
						'#ID#' => $bxElementId,
					]));
					continue;
				}

				$productBx = $productBxList[$bxElementId];

				$componentsStores = [];
				foreach ($components as $componentHref => $componentQuantity) {

					if ((float)$componentQuantity <= 0) {
						$loger->addWarningMessage(LangMsg::get('WARNING_COMPONENT_QTY_LESS_THAN_ZERO', [
							'#XML_ID#' => $bundleXmlId,
							'#ID#' => $bxElementId,
							'#COMPONENT_HREF#' => $componentHref,
						]));
						continue;
					}

					foreach ($storeList as $storeId) {
						if (!isset($msStocksList[$componentHref][$storeId])) {
							$componentsStores[$storeId] = 0;
						}
					}

					if(isset($msStocksList[$componentHref]) && Utils::is_count($msStocksList[$componentHref])) {
						foreach ($msStocksList[$componentHref] as $storeId => $quantity) {
							$stock = floor((float)$quantity / (float)$componentQuantity);
							$stock = (float)$stock <= 0 ? 0 : (float)$stock;
							if (isset($componentsStores[$storeId])) {
								if ($stock < $componentsStores[$storeId]) {
									$componentsStores[$storeId] = $stock;
								}
							} else {
								$componentsStores[$storeId] = $stock;
							}
						}
					}
					
				}

				$currentFindStocks = [];
				$qtyByStocks = ['ALL' => 0];
				if(isset($currentStocksList[$bxElementId]) && Utils::is_count($currentStocksList[$bxElementId])) {
					foreach ($currentStocksList[$bxElementId] as $stockInfoBx) {

						$currentFindStocks[] = $stockInfoBx['STORE_ID'];

						$storeBxId = $stockInfoBx['STORE_ID'];

						if (!isset($storeList[$storeBxId])) {
							continue;
						}

						$storeMsId = $storeList[$storeBxId];

						if (!isset($componentsStores[$storeMsId])) {
							continue;
						}

						$counterStocksChange->count();

						$finalStocks = $componentsStores[$storeMsId];

						if ((float)$finalStocks !== (float)$stockInfoBx['AMOUNT']) {
							$counterStocksChange->update();
							\Bitrix\Catalog\StoreProductTable::update($stockInfoBx['ID'], ['AMOUNT' => $finalStocks]);
						}

						$qtyByStocks[$storeBxId] = $finalStocks;
						$qtyByStocks['ALL'] += $finalStocks;
					}
				}

				if (count($currentFindStocks) !== count($storeList)) {
					foreach ($storeList as $storeId => $storeMsId) {
						if (!in_array($storeId, $currentFindStocks)) {

							if (isset($componentsStores[$storeList[$storeId]])) {
								$finalStocks = $componentsStores[$storeList[$storeId]];

								$counterStocksChange->count();
								$counterStocksChange->add();

								\Bitrix\Catalog\StoreProductTable::add([
									'STORE_ID' => $storeId,
									'PRODUCT_ID' => $productBx['ID'],
									'AMOUNT' => $finalStocks
								]);

								$qtyByStocks[$storeId] = $finalStocks;
								$qtyByStocks['ALL'] += $finalStocks;
							}
						}
					}
				}

				$counterProductChange->count();
				if (isset($qtyByStocks[$qtyStocks]) && (float)$productBx['QUANTITY'] !== (float)$qtyByStocks[$qtyStocks]) {
					$counterProductChange->update();
					\Bitrix\Catalog\Model\Product::update($productBx['ID'], [
						'QUANTITY' => (float)$qtyByStocks[$qtyStocks]
					]);
				}
			}
		}

		if(count($undfoundElements) > 0){
			$firstTenUnfoundElements = array_slice($undfoundElements, 0, 10);
			$loger->addMessage(LangMsg::get('WARNING_EMPTY_IBLOCK_ITEM_FOR_XML_ID', [
				'#XML_IDS#' => implode(', ', $firstTenUnfoundElements),
			]));
		}

		$loger->addMessage(LangMsg::get('COUNTER_INFO_UPDATE', $counterProductChange->getReport()), Debug\Message::TYPE_SUCCESS);
		$loger->addMessage(LangMsg::get('COUNTER_INFO_ADD_UPDATE', $counterStocksChange->getReport()), Debug\Message::TYPE_SUCCESS);
	}
}
