<?
namespace Rbs\MoyskladStocks\HlCache;

use \Rbs\MoyskladStocks\Config;
use \Rbs\MoyskladStocks\Utils;

/** @deprecated The table is not created for new installations. Data is not read by main pipelines. */
class Bundles extends Base
{
    static $tableName = 'rbs_ms_bundles';
    static $className = 'RbsMsBundles';

    static $ufFields = [
        'href' => [
            'NAME' => 'UF_BUNDLE_HREF',
            'TYPE' => 'string'
        ],
        'externalCode' => [
            'NAME' => 'UF_BUNDLE_EXT',
            'TYPE' => 'string'
        ],
        'components' => [
            'NAME' => 'UF_COMPONENTS',
            'TYPE' => 'string'
        ]
    ];

    public static function update($bundleRows = [])
    {
        $entityClass = parent::getEntityDataClass(self::getTableName());
        if($entityClass === null) return false;

        if(Utils::is_count($bundleRows)){
            foreach($bundleRows as $row){

                $components = $row->components;
                if(!Utils::array_exists($components)) {
                    continue;
                }

                $arComponents = [];
                foreach($components->rows as $component){
                    $arComponents[$component->assortment->meta->href] = $component->quantity;
                }

                $productHref = explode('?', $row->meta->href)[0];

                $current = $entityClass::getList([
                    'filter' => [
                        self::$ufFields['href']['NAME'] => $productHref
                    ]
                ])->fetch();

                if(is_array($current) && isset($current['ID']) && (int)$current['ID'] > 0){
                    if(
                        $current[self::$ufFields['externalCode']['NAME']] !== $row->externalCode ||
                        md5($current[self::$ufFields['components']['NAME']]) !== md5(serialize($arComponents))
                    ){
                        $entityClass::update($current['ID'], [
                            self::$ufFields['href']['NAME'] => $productHref,
                            self::$ufFields['externalCode']['NAME'] => $row->externalCode,
                            self::$ufFields['components']['NAME'] => serialize($arComponents)
                        ]);
                    }
                } else {
                    $entityClass::add([
                        self::$ufFields['href']['NAME'] => $productHref,
                        self::$ufFields['externalCode']['NAME'] => $row->externalCode,
                        self::$ufFields['components']['NAME'] => serialize($arComponents)
                    ]);
                }
            }
        }
    }

    public static function updateComponents($bundleHref = '', $componentRows = [])
    {
        
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
            $stockObj = (object)[
                'href' => $current[self::$ufFields['href']['NAME']],
                'externalCode' => $current[self::$ufFields['externalCode']['NAME']],
                'components' => unserialize($current[self::$ufFields['components']['NAME']])
            ];;
            return $stockObj;
        }

        return false;
    }

    public static function getAll()
    {
        $entityClass = parent::getEntityDataClass(self::getTableName());
        if($entityClass === null) return [];

        $result = [];
        $allRows = $entityClass::getList()->fetchAll();
        if(count($allRows) > 0){
            foreach($allRows as $row){
                $result[] = (object)[
                    'href' => $row[self::$ufFields['href']['NAME']],
                    'externalCode' => $row[self::$ufFields['externalCode']['NAME']],
                    'components' => unserialize($row[self::$ufFields['components']['NAME']])
                ];
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
}