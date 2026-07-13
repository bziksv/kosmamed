<?
namespace Rbs\MoyskladStocks\HlCache;

use Rbs\MoyskladStocks\Debug;
use Rbs\MoyskladStocks\Config;
use Rbs\MoyskladStocks\LangMsg;
use Rbs\MoyskladStocks\Utils;
use Bitrix\Main\Type\DateTime;
use Rbs\MoyskladStocks\Services\StocksUtils;

class CurrentStocks extends Base
{
    static $tableName = 'rbs_ms_curr_stocks';
    static $className = 'RbsMsCurrStocks';

    static $ufFields = [

        'assrotmentId' => [
            'NAME' => 'UF_ASSORTMENT_ID',
            'TYPE' => 'string'
        ],

        'storeId' => [
            'NAME' => 'UF_STORE_ID',
            'TYPE' => 'string',
            'MANDATORY' => 'N'
        ],

        'stock' => [
            'NAME' => 'UF_STOCK',
            'TYPE' => 'double',
            'MANDATORY' => 'N'
        ],
        'freeStock' => [
            'NAME' => 'UF_FREE_STOCK',
            'TYPE' => 'double',
            'MANDATORY' => 'N'
        ],
        'quantity' => [
            'NAME' => 'UF_QUANTITY',
            'TYPE' => 'double',
            'MANDATORY' => 'N'
        ],

        'date_update' => [
            'NAME' => 'UF_DATE_UPDATE',
            'TYPE' => 'datetime'
        ],

        'need_update' => [
            'NAME' => 'UF_NEED_UPDATE',
            'TYPE' => 'boolean',
            'MANDATORY' => 'N'
        ],

    ];

    public static function update($stockRows = [], $stockType = 'stock', $updateType = 'soft'): Debug\Counter
    {
        $counter = new Debug\Counter(LangMsg::get('COUNTER_HL_CURR_STOCKS_UPDATE'));

        $entityClass = parent::getEntityDataClass(self::getTableName());
        if($entityClass === null) {
            $counter->error(LangMsg::get('ERROR_HL_NULL'));
            return $counter;
        }

        if(!isset(self::$ufFields[$stockType]['NAME'])){
            $counter->error(LangMsg::get('WARNING_HL_CURR_STOCKS_TYPE',[
                '#TYPE#' => !empty($stockType) ? $stockType : 'empty'
            ]));
            return $counter;
        }

        if(!Utils::is_count($stockRows)) {
            $counter->error(LangMsg::get('WARNING_EMPTY_ROWS'));
            return $counter;
        }

        $counter->set_count(count($stockRows));

        $uniqStoreAssoc = StocksUtils::getStoreIdsForImport(StocksUtils::STOCKS_TYPE_CURRENT);
        $hasAllStocks = in_array('all_stocks', $uniqStoreAssoc);

        $dateUpdate = Utils::get_date_string();

        $formatStockRows = [];
        foreach ($stockRows as $row) {
            $stockCount = !empty($row->{$stockType}) ? (float)$row->{$stockType} : (float)0;
            if(empty($row->storeId)) {
                $row->storeId = 'empty';
            }
            if ($hasAllStocks || in_array($row->storeId, $uniqStoreAssoc)) {
                $formatStockRows[$row->assortmentId][$row->storeId] = $stockCount;
            }
        }
        
        if (Utils::is_count($formatStockRows)) {

            $currentStocks = self::getStocksByType(array_keys($formatStockRows), $stockType);

            foreach ($formatStockRows as $assortmentId => $storeParams) {
                foreach ($storeParams as $storeId => $stock) {

                    $current = isset($currentStocks[$assortmentId][$storeId]) ? $currentStocks[$assortmentId][$storeId] : [];
                    $current[$stockType] = isset($current[$stockType]) ? (float)$current[$stockType] : (float)0;

                    if (isset($current['ID']) && (int)$current['ID'] > 0) {

                        if (
                            ($updateType === 'soft' && $current[$stockType] !== $stock
                            ) || $updateType === 'hard'
                        ) {

                            $updatedField =  [
                                self::$ufFields[$stockType]['NAME'] => $stock,
                                self::$ufFields['date_update']['NAME'] => $dateUpdate,
                                self::$ufFields['need_update']['NAME'] => true
                            ];

                            $result = $entityClass::update($current['ID'], $updatedField);

                            if ($result->isSuccess()) {
                                $counter->update();
                            } else {
                                $counter->error(LangMsg::get('ERROR_HL_UPDATE_ITEM', ['#ID#' => $current['ID']]));
                            }

                        }

                    } else {

                        $result =  $entityClass::add([
                            self::$ufFields['assrotmentId']['NAME'] => $assortmentId,
                            self::$ufFields['storeId']['NAME'] => $storeId,
                            self::$ufFields[$stockType]['NAME'] => $stock,
                            self::$ufFields['date_update']['NAME'] => $dateUpdate,
                            self::$ufFields['need_update']['NAME'] => true
                        ]);

                        if ($result->isSuccess()) {
                            $counter->add();
                        } else {
                            $counter->error(LangMsg::get('ERROR_HL_ADD_ITEM', ['#ID#' => $assortmentId . '_' . $storeId]));
                        }

                    }
                }
            }
        }

        return $counter;
    }

    public static function getStocksByType($assortmentIds = [], $stockType = 'stock'): array
    {
        $result = [];

        $entityClass = parent::getEntityDataClass(self::getTableName());

        $uniqStoreAssoc = StocksUtils::getStoreIdsForImport(StocksUtils::STOCKS_TYPE_CURRENT);
        $hasAllStocks = in_array('all_stocks', $uniqStoreAssoc);

        if($entityClass && Utils::is_count($assortmentIds) && ($hasAllStocks || Utils::is_count($uniqStoreAssoc))) {

            $filter = ["=" . self::$ufFields['assrotmentId']['NAME'] => $assortmentIds];
            if(!$hasAllStocks) {
                $filter = [
                    "=" . self::$ufFields['assrotmentId']['NAME'] => $assortmentIds,
                    '=' . self::$ufFields['storeId']['NAME'] => $uniqStoreAssoc
                ];
            }

            $select = [
                'ID',
                self::$ufFields['assrotmentId']['NAME'],
                self::$ufFields['storeId']['NAME'],
                self::$ufFields[$stockType]['NAME']
            ];

            $allRows = $entityClass::getList([
                'filter' => $filter,
                'select' => $select
            ])->fetchAll();

            if (Utils::is_count($allRows)) {
                foreach ($allRows as $row) {
                    $result[$row[self::$ufFields['assrotmentId']['NAME']]][$row[self::$ufFields['storeId']['NAME']]] = [
                        'ID' => $row['ID'],
                        $stockType => $row[self::$ufFields[$stockType]['NAME']]
                    ];
                }
            }

        }

        return $result;
    }

    public static function getForUpdateStocksByAssortmentIds($stockType = 'stock', $limit = 1000, $assortmentIds = []): array
    {
        $filter = ['=' . self::$ufFields['assrotmentId']['NAME'] => $assortmentIds];
        return self::getForUpdateStocks($stockType, 1000, $filter);
    }

    public static function getForUpdateStocks($stockType = 'stock', $limit = 1000, $customFilter = []): array
    {
        $result = [];

        $entityClass = parent::getEntityDataClass(self::getTableName());

        if ($entityClass !== null) {

            $order = [self::$ufFields['assrotmentId']['NAME'] => 'ASC'];

            if(!empty($customFilter)) {
                $filter = $customFilter;
            } else {
                $filter = ['=' . self::$ufFields['need_update']['NAME'] => true];
            }
            
            
            $select = [
                'ID',
                self::$ufFields['assrotmentId']['NAME'],
                self::$ufFields['storeId']['NAME'],
                self::$ufFields[$stockType]['NAME']
            ];

            $firstQuery = $entityClass::getList([
                'order' => $order,
                'limit' => $limit,
                'filter' => $filter,
                'select' => $select
            ])->fetchAll();

            $uniqAssortmentIds = [];
            if (Utils::is_count($firstQuery)) {
                foreach ($firstQuery as $row) {
                    $uniqAssortmentIds[$row[self::$ufFields['assrotmentId']['NAME']]] = true;
                }
            }

            if(Utils::is_count($uniqAssortmentIds)) {

                $filter = [
                    '=' . self::$ufFields['assrotmentId']['NAME'] => array_keys($uniqAssortmentIds)
                ];
                $allRows = $entityClass::getList([
                    'order' => $order,
                    'filter' => $filter,
                    'select' => $select
                ])->fetchAll();

                if (Utils::is_count($allRows)) {
                    foreach ($allRows as $row) {
                        $result[$row[self::$ufFields['assrotmentId']['NAME']]][$row[self::$ufFields['storeId']['NAME']]] = [
                            'ID' => $row['ID'],
                            $stockType => !empty($row[self::$ufFields[$stockType]['NAME']]) ? (float)$row[self::$ufFields[$stockType]['NAME']] : (float)0
                        ];
                    }
                }
            }

        }

        return $result;
    }

    public static function setUpdatedStocksByAssortmentIds(array $assortmentIds = [])
    {
        $entityClass = parent::getEntityDataClass(self::getTableName());
        if ($entityClass && Utils::is_count($assortmentIds)) {
            $result = $entityClass::getList([
                'filter' => [
                    '=' . self::$ufFields['assrotmentId']['NAME'] => $assortmentIds
                ],
                'select' => [
                    'ID'
                ]
            ])->fetchAll();
            if(Utils::is_count($result)) {
                $currentIds = [];
                foreach($result as $item) {
                    $currentIds[] = $item['ID'];
                }
                if(Utils::is_count($currentIds)) {
                    self::setUpdatedStocks($currentIds);
                }
            }
        }
    }

    public static function setUpdatedStocks(array $ids = [])
    {
        $entityClass = parent::getEntityDataClass(self::getTableName());
        if ($entityClass && Utils::is_count($ids)) {
            foreach($ids as $id) {
                $entityClass::update($id, [self::$ufFields['need_update']['NAME'] => false]);
            }
        }
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

    public static function deleteOutdatedStocks($timestamp): int
    {
        $entityClass = parent::getEntityDataClass(self::getTableName());
        if ($entityClass === null) {
            return 0;
        }

        $date = DateTime::createFromTimestamp($timestamp);

        $items = $entityClass::getList([
            'filter' => [
                '<' . self::$ufFields['date_update']['NAME'] => $date
            ],
            'select' => ['ID']
        ])->fetchAll();

        $count = 0;
        if (Utils::is_count($items)) {
            foreach ($items as $item) {
                $result = $entityClass::delete($item['ID']);
                if($result->isSuccess()) {
                    $count++;
                }
            }
        }

        return $count;
    }

    /** @deprecated */
    public static function updateParentsStocks()
    {
        StocksUtils::updateParentStoresChildTree(StocksUtils::STOCKS_TYPE_CURRENT);
    }

    /** @deprecated */
    public static function getParentStores(): array
    {
        return StocksUtils::getParentStores(StocksUtils::STOCKS_TYPE_CURRENT);
    }


}