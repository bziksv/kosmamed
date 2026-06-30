<?
namespace Rbs\MoyskladStocks\HlCache;

use \Rbs\MoyskladStocks\Config;
use \Rbs\MoyskladStocks\ApiNew;
use \Rbs\MoyskladStocks\Utils;

class ExtCodes extends Base
{
    static $tableName = 'rbs_ms_xml_ids';
    static $className = 'RbsMsXmlIds';

    static $ufFields = [
        'href' => [
            'NAME' => 'UF_PRODUCT_HREF',
            'TYPE' => 'string'
        ],
        'externalCode' => [
            'NAME' => 'UF_PRODUCT_XML_ID',
            'TYPE' => 'string'
        ]
    ];

    public static function updateFromHref($href = '')
    {
        $item = ApiNew::get($href);
        if(Utils::is_success($item)){
            self::update([$item]);
            return self::get($href);
        }
        return false;
    }

    public static function update($itemRows = [])
    {
        $entityClass = parent::getEntityDataClass(self::getTableName());
        if($entityClass === null) return false;

        if(Utils::is_count($itemRows)){
            
            $hrefs = [];
            foreach($itemRows as $row){
                if(empty($row->externalCode)){
                    continue;
                }
                $hrefs[] = $row->meta->href;
            }
            
            $currentItems = [];
            if(count($hrefs) > 0){
                $existingItems = $entityClass::getList([
                    'filter' => [
                        "=" . self::$ufFields['href']['NAME'] => $hrefs
                    ]
                ])->fetchAll();
                
                if(Utils::is_count($existingItems)){
                    foreach($existingItems as $item){
                        $currentItems[$item[self::$ufFields['href']['NAME']]] = $item;
                    }
                }
            }

            foreach($itemRows as $row){
                if(empty($row->externalCode)){
                    continue;
                }
                
                $href = $row->meta->href;
                
                if(isset($currentItems[$href])){
                    $current = $currentItems[$href];
                    if($current[self::$ufFields['externalCode']['NAME']] !== $row->externalCode){
                        $entityClass::update($current['ID'], [
                            self::$ufFields['href']['NAME'] => $href,
                            self::$ufFields['externalCode']['NAME'] => $row->externalCode
                        ]);
                    }
                } else {
                    $entityClass::add([
                        self::$ufFields['href']['NAME'] => $href,
                        self::$ufFields['externalCode']['NAME'] => $row->externalCode
                    ]);
                }
            }
        }
    }

    public static function get($productHref = '')
    {
        $entityClass = parent::getEntityDataClass(self::getTableName());
        if($entityClass === null) return false;

        $current = $entityClass::getList([
            'filter' => [
                self::$ufFields['href']['NAME'] => $productHref
            ]
        ])->fetch();

        if(is_array($current) && isset($current['ID']) && (int)$current['ID'] > 0){
            return $current[self::$ufFields['externalCode']['NAME']];
        }

        return false;
    }

    public static function delete($productHref = '')
    {
        $entityClass = parent::getEntityDataClass(self::getTableName());
        if($entityClass === null) return false;

        $current = $entityClass::getList([
            'filter' => [
                self::$ufFields['href']['NAME'] => $productHref
            ]
        ])->fetch();

        if(is_array($current) && isset($current['ID']) && (int)$current['ID'] > 0){
            return $entityClass::delete($current['ID']);
        }

        return false;
    }

    public static function getArray($productHref = []): array
    {
        $entityClass = parent::getEntityDataClass(self::getTableName());
        if($entityClass === null) return [];

        $result = [];

        if(Utils::is_count($productHref)) {
            $current = $entityClass::getList([
                'filter' => [
                    "=" . self::$ufFields['href']['NAME'] => $productHref
                ]
            ])->fetchAll();

            if (Utils::is_count($current)) {
                foreach ($current as $item) {
                    $result[$item[self::$ufFields['href']['NAME']]] = $item[self::$ufFields['externalCode']['NAME']];
                }
            }
        }

        return $result;
    }

    public static function getExternalCodesForAssortmentIds($assortmentIds = []): array
    {
        $entityClass = parent::getEntityDataClass(self::getTableName());
        if ($entityClass === null) return [];

        $result = [];
        $unfinded = [];

        if (Utils::is_count($assortmentIds)) {

            $filterHrefs = [];
            foreach($assortmentIds as $id) {
                $filterHrefs[] = ApiNew::getApiEndPointUrl() . "/entity/%/{$id}";
                $unfinded[$id] = true;
            }

            $current = $entityClass::getList([
                'filter' => [
                    self::$ufFields['href']['NAME'] => $filterHrefs
                ],
                //todo: this is temporary solution, we need to rewrite the cache logic of external codes table.
                'cache' => [
                    'ttl' => 86400 * 7
                ]
            ])->fetchAll();

            if(Utils::is_count($current)){
                foreach ($current as $item) {
                    $hrefParts = explode("/", $item[self::$ufFields['href']['NAME']]);
                    $assortmentId = array_pop($hrefParts);

                    if(isset($unfinded[$assortmentId])){
                        unset($unfinded[$assortmentId]);
                    }

                    $entity = array_pop($hrefParts);
                    $result[$entity][$assortmentId] = $item[self::$ufFields['externalCode']['NAME']];
                }
            }

        }

        return [
            'product' => isset($result['product']) ? $result['product'] : [],
            'variant' => isset($result['variant']) ? $result['variant'] : [],
            'bundle' => isset($result['bundle']) ? $result['bundle'] : [],
            'service' => isset($result['service']) ? $result['service'] : [],
            'unfinded' => array_keys($unfinded)
        ];
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
        $entityId = parent::createTable([
            'NAME' => self::getClassName(),
            'TABLE_NAME' => self::getTableName()
        ], self::getUfFields());

        return $entityId;
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
    public static function getExternalCodesForAssortmentId($assortmentIds = [], $entity = ''): array
    {
        $result = self::getExternalCodesForAssortmentIds($assortmentIds);
        if(isset($result[$entity])){
            return $result[$entity];
        }
        return [];
    }
    
}