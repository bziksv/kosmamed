<?

namespace Rbs\MoyskladStocks\Import\Type;

use Rbs\MoyskladStocks\AgentManager;
use Rbs\MoyskladStocks\HlCache\ExtCodes;
use Rbs\MoyskladStocks\HlCache\Stocks as StocksHl;
use Rbs\MoyskladStocks\HlCache\Bundles;
use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\Debug;
use Rbs\MoyskladStocks\LangMsg;
use Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\ApiNew;
use Rbs\MoyskladStocks\Debug\Counter;
use Rbs\MoyskladStocks\HlCache;
use Rbs\MoyskladStocks\Controller\IblockCache;
use Rbs\MoyskladStocks\Services\StocksUtils;
use Rbs\MoyskladStocks\Services\ConfigurationUtils;
use Rbs\MoyskladStocks\Internals\IblockElementUpdaterForEvents;
use Rbs\MoyskladStocks\Compitable\ImportStocksClass;
use Rbs\MoyskladStocks\Internals\ProductFinder\StocksProductFinder;
use Rbs\MoyskladStocks\Internals\ProductFinder\ProductIdentifier;

\Bitrix\Main\Loader::includeModule('catalog');
\Bitrix\Main\Loader::includeModule('iblock');

class Stocks
{

    public static function update($entity = '', $productHref = '', $xmlId = '')
    {
        $loger = new Debug\Loger();
        $href = explode('?', $productHref)[0];
        self::update_entity_stocks([$href], $entity, $loger);
    }

    public static function import()
    {
        $agentManager = new AgentManager('stocks');
        $agentManager->setOnlyFullUpdate();
        $logger = new Debug\Loger();

        try {

            $isSomeStockExchange = Config::checkFeature('productstocks') || Config::checkFeature('variantstocks') || Config::checkFeature('bundlestocks');

            if (!$isSomeStockExchange) {
                throw new \Bitrix\Main\SystemException(LangMsg::get('WARNING_EMPTY_ENTITY'));
            }
            
            $needExtCodes = ProductIdentifier::isExtCodesRequired();
            if (!HlCache\Stocks::isExsist() || ($needExtCodes && !HlCache\ExtCodes::isExsist())) {
                throw new \Bitrix\Main\SystemException(LangMsg::get('WARNING_EMPTY_HL_CACHE'));
            }

            if (!Utils::is_count(ConfigurationUtils::getStoreList())) {
                throw new \Bitrix\Main\SystemException(LangMsg::get('WARNING_EMPTY_STORES_FOR_IMPORT'));
            }

            if (StocksUtils::isStocksWithChild(StocksUtils::STOCKS_TYPE_DEFAULT)) {
                StocksUtils::updateParentStoresChildTree(StocksUtils::STOCKS_TYPE_DEFAULT);
            }

            $params = [
                'limit' => $agentManager->getLimit(500),
                'offset' => $agentManager->getOffset(),
                'filter' => Config::getFilterStocksString()
            ];

            $logger->addInfoMessage(LangMsg::buildAgentFilterMessage($params['filter']));

            ApiNew::refreshCountRequests();
            $msResult = ApiNew::get('/report/stock/bystore', $params);

            if (Utils::is_success($msResult)) {

                if (!empty($msResult->meta->size)) {
                    $agentManager->setSize($msResult->meta->size);
                }

                if(Utils::array_exists($msResult)) {
                    
                    $counterUpdateHlTable = HlCache\Stocks::update($msResult->rows);
                    
                    $logger->addMessage(LangMsg::get('COUNTER_INFO', $counterUpdateHlTable->getReport()),  Debug\Message::TYPE_INFO);
                    
                    if ($counterUpdateHlTable->hasErrors()) {
                        $logger->addErrorMessageArray($counterUpdateHlTable->getErrorMessageArray());
                    }

                    $entityItemHrefs = [
                        'product' => [],
                        'variant' => []
                    ];

                    foreach ($msResult->rows as $row) {
                        $href = explode('?', $row->meta->href)[0];
                        $entityItemHrefs[$row->meta->type][] = $href;
                    }

                    $changes = [];
                    foreach (['product', 'variant'] as $entity) {
                        if (Config::checkFeature($entity . 'stocks') && Utils::is_count($entityItemHrefs[$entity])) {
                            $changes[$entity] = self::update_entity_stocks($entityItemHrefs[$entity], $entity, $logger);
                        }
                    }

                    if(in_array(true, $changes, true)) {
                        IblockCache::resetState('stocks');
                    } else {
                        IblockCache::handleCacheClear($logger, 'stocks');
                    }
                    
                } else {
                    $logger->addMessage(LangMsg::get('WARNING_EMPTY_ROWS'), Debug\Message::TYPE_WARNING);
                }

                if (!empty($msResult->meta->nextHref)) {
                    $agentManager->setNextStepOffset();
                } else {
                    $agentManager->setFinalStepParams();
                }
                
            } else {

                if (Utils::has_errors($msResult)) {
                    $logger->addMessageArray($msResult->{'errors'}, Debug\Message::TYPE_ERROR);
                } else {
                    throw new \Bitrix\Main\SystemException(LangMsg::get('EXCEPTION_API_ERROR'));
                }
            }
            
        } catch (\Throwable $e) {
            $logger->addErrorMessage(Utils::build_exception_message($e));
        }

        $logger->addFinishMessage(LangMsg::buildAgentFinishMessage($logger->getLogTime()));
        $logger->exportLog(LangMsg::buildAgentHeadMessage($agentManager));

        return (object)[
            'logger' => $logger,
            'agentManager' => $agentManager
        ];
    }

    private static function update_entity_stocks($sourceItemList = [], $entity = 'product', Debug\Loger &$loger)
    {

        $loger->addMessage(LangMsg::get('WORK_WITH_STOCK_IMPORT', [
            '#ENTITY#' => $entity,
            '#SOURCE_COUNT#' => count($sourceItemList),
        ]), Debug\Message::TYPE_INFO);

        $entityXmlIds = ProductIdentifier::resolveXmlIdsFromHrefs($sourceItemList);
        if(!Utils::is_count($entityXmlIds)) {
            $loger->addMessage(LangMsg::get('WARNING_HL_EMPTY_EXT_CODES'), Debug\Message::TYPE_WARNING);
            return;
        }

        $hrefs = array_keys($entityXmlIds);

        $stocks = StocksHl::getArray($hrefs);
        if (!Utils::is_count($stocks)) {
            $loger->addMessage(LangMsg::get('WARNING_HL_EMPTY_STOCKS'), Debug\Message::TYPE_WARNING);
            return;
        }

        $storeList = ConfigurationUtils::getStoreList();
        if (!Utils::is_count($storeList)) {
            $loger->addMessage(LangMsg::get('WARNING_EMPTY_STORES_FOR_IMPORT'), Debug\Message::TYPE_WARNING);
            return;
        }

        $catalogIblockId = "-1";
        if (Config::isIblockRequired($entity)) {
            $catalogIblockId = Config::getIblockId($entity);
            if ((int)$catalogIblockId <= 0) {
                $loger->addMessage(LangMsg::get('WARNING_EMPTY_CATALOG_IBLOCK'), Debug\Message::TYPE_WARNING);
                return;
            }
        }

        $msStocksInfoByXmlId = [];
        foreach ($stocks as $stockInfo) {
            if (Utils::property_exists($stockInfo, ['href']) && !empty($stockInfo->href)) {
                if (isset($entityXmlIds[$stockInfo->href]) && !empty($entityXmlIds[$stockInfo->href])) {
                    $msStocksInfoByXmlId[$entityXmlIds[$stockInfo->href]] = $stockInfo->stocks;
                }
            }
        }

        if (!Utils::is_count($msStocksInfoByXmlId)) {
            $loger->addMessage(LangMsg::get('WARNING_EMPTY_STOCKS_XML_IDS_IMPORT'), Debug\Message::TYPE_WARNING);
            return;
        }

        $xmlIds = array_keys($msStocksInfoByXmlId);

        $doubleStockType = Config::getDoubleStockType($entity);

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

                switch($doubleStockType){
                    case 'ASC':
                    case 'DESC':
                        $productInfoArray[array_key_first($elements)] = [
                            'MS_STOCKS' => $msStocksInfoByXmlId[$xmlId],
                            'BX_STOCKS' => []
                        ];
                        break;
                    case 'ALL':
                        foreach($elements as $elId => $element) {
                            $productInfoArray[$elId] = [
                                'MS_STOCKS' => $msStocksInfoByXmlId[$xmlId],
                                'BX_STOCKS' => []
                            ];
                        }
                        break;
                    case 'SKIP':
                        if(!$isDouble){
                            $productInfoArray[array_key_first($elements)] = [
                                'MS_STOCKS' => $msStocksInfoByXmlId[$xmlId],
                                'BX_STOCKS' => []
                            ];
                        }
                        break;
                }
                
                if($isDouble) {
                    $doubleElementCount++;
                }

            }
        }
        unset($doublesCheckList);
        unset($msStocksInfoByXmlId);

        if($doubleElementCount > 0) {
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

        if(!Utils::is_count($productQuantityArray)) {
            $loger->addMessage(LangMsg::get('WARNING_EMPTY_STOCKS_PRODUCTS_IMPORT'), Debug\Message::TYPE_WARNING);
            return;
        }

        if(count($productQuantityArray) !== count($productInfoArray)) {
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

        $typeStocks = Config::getTypeStockSyncFormated($entity);
        $qtyStocks = Config::getQtyStockSync($entity);

        $filterStocks = ['PRODUCT_ID' => $currentIds, 'STORE_ID' => array_keys($storeList)];
        if($qtyStocks === 'ALL_BX'){
            unset($filterStocks['STORE_ID']);
        }
        $currentStocks = \Bitrix\Catalog\StoreProductTable::getList(['filter' => $filterStocks])->fetchAll();

        if (Utils::is_count($currentStocks)) {
            foreach ($currentStocks as $stockBxInfo) {
                $productInfoArray[$stockBxInfo['PRODUCT_ID']]['BX_STOCKS'][$stockBxInfo['STORE_ID']] = $stockBxInfo;
            }
        }
        unset($currentStocks);

        $counterProductChange = new Counter(LangMsg::get('COUNTER_PRODUCT_UPDATE'));
        $counterStocksChange = new Counter(LangMsg::get('COUNTER_STOCKS_UPDATE'));
        
        foreach ($productInfoArray as $productId => $info) {

            $msStocks = $info['MS_STOCKS'];
            $bxQuantity = $info['BX_QUANTITY'];
            $bxStocks = $info['BX_STOCKS'];

            $qtyByStocks = ['ALL' => 0, 'ALL_MS' => isset($msStocks['all'][$typeStocks]) ? (float)$msStocks['all'][$typeStocks] : 0];

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

                if (isset($bxStocks[$bxStoreId])) {
                    if ($currentMsStock !== (float)$bxStocks[$bxStoreId]['AMOUNT']) {
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
                $qtyByStocks['ALL'] += (float)$currentMsStock;
            }
            
            foreach($updateStockInfoArray as $typeAction => $actionParams) {
                foreach($actionParams as $id => $params){
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
            
            if($qtyStocks === 'ALL_BX') {
                $qtyByStocks['ALL_BX'] = 0;
                foreach($bxStocks as $bxStockInfo) {
                    $qtyByStocks['ALL_BX'] += (float)$bxStockInfo['AMOUNT'];
                }
            }

            $arUpdateProductFields = [];
            if (isset($qtyByStocks[$qtyStocks]) && $bxQuantity !== $qtyByStocks[$qtyStocks]) {
                $arUpdateProductFields['QUANTITY'] = (float)$qtyByStocks[$qtyStocks];
            }

            $event = new \Bitrix\Main\Event(\Rbs\MoyskladStocks\Config::getModuleId(true), "OnBeforeUpdateProductInStockRow", array(
                'arUpdateProductFields' => $arUpdateProductFields,
                'productBx' => $productId,
                'currentStocks' => $bxStocks,
                'productInfoStocks' => $info,
                'qtyByStocks' => $qtyByStocks
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
                IblockElementUpdaterForEvents::updateFromStocks($entity, $productId);
            }
        }

        $productReport = $counterProductChange->getReport();
        $stocksReport = $counterStocksChange->getReport();

        $loger->addInfoMessage(LangMsg::get('COUNTER_INFO_UPDATE', $productReport));
        $loger->addInfoMessage(LangMsg::get('COUNTER_INFO_ADD_UPDATE', $stocksReport));

        return $productReport['update'] > 0 || $stocksReport['add'] > 0 || $stocksReport['update'] > 0;
    }

    /** @deprecated */
    public static function update_bundle_stocks($arBundles = [], Debug\Loger &$loger)
    {
        BundleStocks::update($arBundles, $loger);
    }

    /** @deprecated */
    public static function updateAllBundle()
    {
        ImportStocksClass::updateAllBundle();
    }

    /** @deprecated */
    public static function updateAll($xmlIds = [], $entity = '')
    {
        ImportStocksClass::updateAll($xmlIds, $entity);
    }

    /** @deprecated */
    public static function updateRow($xmlId = '', $catalogIblockId = '', $entity = '', $typeStocks = 'A', $qtyStocks = 'ALL', $storeList = [], $stocks = [], &$arrLog = [])
    {
        ImportStocksClass::updateRow($xmlId, $catalogIblockId, $entity, $typeStocks, $qtyStocks, $storeList, $stocks, $arrLog);
    }
}