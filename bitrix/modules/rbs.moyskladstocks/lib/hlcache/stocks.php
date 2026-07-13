<?
namespace Rbs\MoyskladStocks\HlCache;

use \Rbs\MoyskladStocks\Config;
use \Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\Debug\Counter;
use Rbs\MoyskladStocks\LangMsg;
use Rbs\MoyskladStocks\Services\StocksUtils;
use Rbs\MoyskladStocks\Services\ConfigurationUtils;

class Stocks extends Base
{
    static $tableName = 'rbs_ms_stocks';
    static $className = 'RbsMsStocks';

    static $ufFields = [
        'href' => [
            'NAME' => 'UF_PRODUCT_HREF',
            'TYPE' => 'string'
        ],
        'stocks' => [
            'NAME' => 'UF_STOCKS',
            'TYPE' => 'string'
        ],
        'date' => [
            'NAME' => 'UF_DATE_UPDATE',
            'TYPE' => 'datetime'
        ]
    ];

    public static function update($stockRows = []): Counter
    {
        $counter = new Counter(LangMsg::get('COUNTER_HL_STOCKS_UPDATE'));

        $entityClass = parent::getEntityDataClass(self::getTableName());
        if($entityClass !== null) {

            $storeAssoc = ConfigurationUtils::getStoreList();
            $dateUpdate = Utils::get_date_string();
            $isParentStores = StocksUtils::isStocksWithChild(StocksUtils::STOCKS_TYPE_DEFAULT);
            $parentStores = $isParentStores ? StocksUtils::getParentStores(StocksUtils::STOCKS_TYPE_DEFAULT) : [];

            if (Utils::is_count($stockRows)) {

                $hrefs = [];
                foreach ($stockRows as $row) {
                    $hrefs[] = explode('?', $row->meta->href)[0];
                }

                $currentStocks = [];
                if (count($hrefs) > 0) {
                    $currentStocks = self::getFullArray($hrefs);
                }

                $counter->set_count(count($stockRows));

                foreach ($stockRows as $row) {

                    $arStocks = [
                        'all' => [
                            's' => 0,
                            'q' => 0,
                            't' => 0
                        ]
                    ];

                    if ($isParentStores) {

                        foreach ($row->stockByStore as $byStore) {
                            $storeId = array_pop(explode('/', $byStore->meta->href));
                            if (in_array($storeId, $storeAssoc)) {
                                $arStocks[$storeId] = [
                                    's' => $byStore->stock,
                                    'q' => $byStore->stock + $byStore->inTransit - $byStore->reserve,
                                    't' => $byStore->stock - $byStore->reserve
                                ];
                                if (isset($parentStores[$storeId])) {
                                    foreach ($row->stockByStore as $byStoreChild) {
                                        $storeIdChild = array_pop(explode('/', $byStoreChild->meta->href));
                                        if (in_array($storeIdChild, $parentStores[$storeId])) {
                                            $arStocks[$storeId]['s'] += $byStoreChild->stock;
                                            $arStocks[$storeId]['q'] += $byStoreChild->stock + $byStoreChild->inTransit - $byStoreChild->reserve;
                                            $arStocks[$storeId]['t'] += $byStoreChild->stock - $byStoreChild->reserve;
                                        }
                                    }
                                }
                            }
                            $arStocks['all']['s'] += $byStore->stock;
                            $arStocks['all']['q'] += $byStore->stock + $byStore->inTransit - $byStore->reserve;
                            $arStocks['all']['t'] += $byStore->stock - $byStore->reserve;
                        }

                    } else {

                        foreach ($row->stockByStore as $byStore) {
                            $storeId = array_pop(explode('/', $byStore->meta->href));
                            if (in_array($storeId, $storeAssoc)) {
                                $arStocks[$storeId] = [
                                    's' => $byStore->stock,
                                    'q' => $byStore->stock + $byStore->inTransit - $byStore->reserve,
                                    't' => $byStore->stock - $byStore->reserve
                                ];
                            }
                            $arStocks['all']['s'] += $byStore->stock;
                            $arStocks['all']['q'] += $byStore->stock + $byStore->inTransit - $byStore->reserve;
                            $arStocks['all']['t'] += $byStore->stock - $byStore->reserve;
                        }
                    }

                    $productHref = explode('?', $row->meta->href)[0];
                    $stocksSerialize = serialize($arStocks);

                    if (isset($currentStocks[$productHref])) {
                        $current = $currentStocks[$productHref];
                        if ($current[self::$ufFields['stocks']['NAME']] !== $stocksSerialize) {
                            $result = $entityClass::update($current['ID'], [
                                self::$ufFields['href']['NAME'] => $productHref,
                                self::$ufFields['stocks']['NAME'] => $stocksSerialize,
                                self::$ufFields['date']['NAME'] => $dateUpdate
                            ]);
                            if ($result->isSuccess()) {
                                $counter->update();
                            } else {
                                $counter->error(LangMsg::get('ERROR_HL_UPDATE_ITEM', ['#ID#' => $current['ID']]));
                            }
                        }
                    } else {
                        
                        $result = $entityClass::add([
                            self::$ufFields['href']['NAME'] => $productHref,
                            self::$ufFields['stocks']['NAME'] => $stocksSerialize,
                            self::$ufFields['date']['NAME'] => $dateUpdate
                        ]);

                        if($result->isSuccess()) {
                            $counter->add();
                        } else {
                            $counter->error(LangMsg::get('ERROR_HL_ADD_ITEM', ['#ID#' => $productHref]));
                        }
                    }
                }
            }
        } else {

            $counter->error(LangMsg::get('ERROR_HL_NULL'));

        }

        return $counter;
    }

    public static function get($productHref = '')
    {
        $entityClass = parent::getEntityDataClass(self::getTableName());
        if($entityClass === null) return false;

        if(!empty($productHref)) {
            $current = $entityClass::getList([
                'order' => [
                    'ID' => 'DESC'
                ],
                'filter' => [
                    self::$ufFields['href']['NAME'] => $productHref
                ]
            ])->fetch();

            if (is_array($current) && isset($current['ID']) && (int)$current['ID'] > 0) {
                $stockObj = unserialize($current[self::$ufFields['stocks']['NAME']]);
                return $stockObj;
            }
        }

        return false;
    }

    public static function getAll(): array
    {
        $entityClass = parent::getEntityDataClass(self::getTableName());
        if($entityClass === null) return [];

        $result = [];

        $allRows = $entityClass::getList()->fetchAll();
        if(Utils::is_count($allRows)){
            foreach($allRows as $row){
                $result[] = (object)[
                    'href' => $row[self::$ufFields['href']['NAME']],
                    'stocks' => unserialize($row[self::$ufFields['stocks']['NAME']])
                ];
            }
        }

        return $result;
    }

    public static function getFullArray($hrefArray = []): array
    {
        $entityClass = parent::getEntityDataClass(self::getTableName());
        if($entityClass === null) return [];

        $result = [];

        if(Utils::is_count($hrefArray)) {
            $allRows = $entityClass::getList([
                'filter' => [
                    "=" . self::$ufFields['href']['NAME'] => $hrefArray
                ]
            ])->fetchAll();

            if (count($allRows) > 0) {
                foreach ($allRows as $row) {
                    $result[$row[self::$ufFields['href']['NAME']]] = $row;
                }
            }
        }
        
        return $result;
    }

    public static function getArray($hrefArray = []): array
    {
        $entityClass = parent::getEntityDataClass(self::getTableName());
        if($entityClass === null) return [];

        $result = [];

        if(Utils::is_count($hrefArray)) {

            $allRows = $entityClass::getList([
                'filter' => [
                    "=" . self::$ufFields['href']['NAME'] => $hrefArray
                ]
            ])->fetchAll();

            if (count($allRows) > 0) {
                foreach ($allRows as $row) {
                    $result[] = (object)[
                        'href' => $row[self::$ufFields['href']['NAME']],
                        'stocks' => unserialize($row[self::$ufFields['stocks']['NAME']])
                    ];
                }
            }

        }

        return $result;
    }

    public static function getTableInfo($tableName = '')
    {
        $tableName = !empty($tableName) ? : self::getTableName();
        return parent::getTableInfo($tableName);
    }

    public static function isExsist($tableName = ''): bool
    {
        $tableName = !empty($tableName) ? : self::getTableName();
        return parent::isExsist($tableName);
    }

    public static function createTable($arParams = [], $ufFields = []): int
    {
        return parent::createTable([
            'NAME' => self::getClassName(),
            'TABLE_NAME' => self::getTableName()
        ], self::getUfFields());
    }

    public static function getUfFields()
    {
        return parent::getUfFieldsArray(self::$ufFields);
    }

    public static function getTableName()
    {
        if((int)Config::getProfileId() > 0){
            return self::$tableName . (int)Config::getProfileId();
        }
        return self::$tableName;
    }

    public static function getClassName()
    {
        if((int)Config::getProfileId() > 0){
            return self::$className . (int)Config::getProfileId();
        }
        return self::$className;
    }

    /** @deprecated */
    public static function updateByOneStock($storeId = 0, $stockRows = [])
    {
    }

    /** @deprecated */
    public static function updateParentsStocks()
    {
        StocksUtils::updateParentStoresChildTree(StocksUtils::STOCKS_TYPE_DEFAULT);
    }

    /** @deprecated */
    public static function getParentStores(): array
    {
        return StocksUtils::getParentStores(StocksUtils::STOCKS_TYPE_DEFAULT);
    }

}