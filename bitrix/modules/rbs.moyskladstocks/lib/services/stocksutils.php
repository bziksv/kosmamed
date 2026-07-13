<?php

namespace Rbs\MoyskladStocks\Services;

use Rbs\MoyskladStocks\ApiNew;
use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\Services\ConfigurationUtils;

class StocksUtils
{
	public const STOCKS_TYPE_CURRENT = 'current';
	public const STOCKS_TYPE_DEFAULT = 'default';

	public static function getStocksTypes(): array
	{
		return [self::STOCKS_TYPE_CURRENT, self::STOCKS_TYPE_DEFAULT];
	}

	public static function getStoreIdsForImport($stocksType = self::STOCKS_TYPE_DEFAULT): array
	{
		$storeAssoc = ConfigurationUtils::getStoreList($stocksType);
		$uniqStoreAssoc = array_unique($storeAssoc);

		if (self::isStocksWithChild($stocksType)) {
			$parentStores = self::getParentStores($stocksType);
			if (count($parentStores) > 0) {
				$childStoreIds = [];
				foreach($parentStores as $storeId => $childStores) {
					$childStoreIds = array_merge($childStoreIds, $childStores);
				}
				$uniqStoreAssoc = array_unique(array_merge($uniqStoreAssoc, $childStoreIds));
			}
		}

		return $uniqStoreAssoc;
	}

	public static function getParentStores($stocksType = self::STOCKS_TYPE_DEFAULT): array
	{
		if (!self::isStocksWithChild($stocksType)) {
			return [];
		}

		$parentStores = Config::getParentStores($stocksType);

		if (empty($parentStores)) {
			try {
				$parentStores = self::getSourceStoresChildTree($stocksType);
				Config::setParentStores($parentStores, $stocksType);
			} catch (\Exception $e) {
				return [];
			}
		}

		return $parentStores;
	}

	public static function isStocksWithChild($stocksType = self::STOCKS_TYPE_DEFAULT): bool
	{
		$feature = $stocksType === self::STOCKS_TYPE_CURRENT ? 'curr_stocks_store_parents' : 'store_parents';
		return Config::checkFeature($feature);
	}

	public static function updateParentStoresChildTree($stocksType = self::STOCKS_TYPE_DEFAULT): void
	{
		$parentStores = self::getSourceStoresChildTree($stocksType);
		Config::setParentStores($parentStores, $stocksType);
	}

	private static function getSourceStoresChildTree($stocksType = self::STOCKS_TYPE_DEFAULT): array
	{
		$store = ApiNew::get('/entity/store', ['limit' => 1000]);
		
		if(!Utils::is_success($store)) {
			throw new \Exception('Error get source stores child tree');
		}
		if (!Utils::array_exists($store)) {
			throw new \Exception('Error get source stores child tree');
		}

		$configStocks = [];

		$storeAssoc = ConfigurationUtils::getStoreList($stocksType);
		$needStocks = [];
		foreach ($store->rows as $row) {
			if (in_array($row->id, $storeAssoc)) {
				$needStocks[$row->id] = $row;
			}
		}
		foreach ($store->rows as $row) {
			foreach ($needStocks as $rowNeedId => $rowNeed) {
				if (mb_strpos($row->pathName, $rowNeed->name) !== false) {
					$configStocks[$rowNeedId][] = $row->id;
				}
			}
		}

		return $configStocks;
	}
	
}