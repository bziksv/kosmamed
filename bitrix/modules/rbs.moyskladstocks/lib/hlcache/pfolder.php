<?
namespace Rbs\MoyskladStocks\HlCache;

use \Rbs\MoyskladStocks\Config;
use \Rbs\MoyskladStocks\ApiNew;
use \Rbs\MoyskladStocks\Utils;
use Rbs\MoyskladStocks\Debug\Counter;
use Rbs\MoyskladStocks\LangMsg;
use Rbs\MoyskladStocks\Internals\ProductFinder\ProductIdentifier;

class PFolder extends Base
{
    static $tableName = 'rbs_ms_pfolder';
    static $className = 'RbsMsPFolder';

    static $ufFields = [
        'href' => [
            'NAME' => 'UF_PFOLDER_HREF',
            'TYPE' => 'string'
         ],
        'externalCode' => [
            'NAME' => 'UF_PFOLDER_XML_ID',
            'TYPE' => 'string'
         ],
         'pathName' => [
            'NAME' => 'UF_PFOLDER_PATH',
            'TYPE' => 'string'
         ],
         'parentHref' => [
            'NAME' => 'UF_PFOLDER_PHREF',
            'TYPE' => 'string'
         ],
         'archived' => [
            'NAME' => 'UF_PFOLDER_ARCHIVED',
            'TYPE' => 'string'
         ],
         'name' => [
            'NAME' => 'UF_PFOLDER_NAME',
            'TYPE' => 'string'
         ],
         'description' => [
            'NAME' => 'UF_PFOLDER_DESCRIPTION',
            'TYPE' => 'string'
         ],
         'date' => [
            'NAME' => 'UF_DATE_UPDATE',
            'TYPE' => 'datetime'
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

    public static function update($itemRows = []): Counter
    {
        $counter = new Counter(LangMsg::get('COUNTER_HL_PFOLDER_UPDATE'));

        $entityClass = parent::getEntityDataClass(self::getTableName());

        if(Utils::is_count($itemRows) && $entityClass !== null){

            $counter->set_count(count($itemRows));

            $dateUpdate = Utils::get_date_string();

            $currentHrefList = [];
            foreach ($itemRows as $row) {
                if (!is_object($row) || empty(ProductIdentifier::getSectionIdentifierValue($row)) || empty($row->meta->href) || empty($row->name)) {
                    continue;
                }
                $row->meta->href = explode('?', $row->meta->href)[0];
                $currentHrefList[] = $row->meta->href;
            }

            $currentListResult = $entityClass::getList([
                'filter' => [
                    '=' . self::$ufFields['href']['NAME'] => $currentHrefList
                ]
            ])->fetchAll();
            unset($currentHrefList);

            $currentList = [];
            foreach($currentListResult as $currentItem) {
                $currentList[$currentItem[self::$ufFields['href']['NAME']]] = $currentItem;
            }
            unset($currentListResult);
            
            foreach($itemRows as $row){

                $sectionXmlId = ProductIdentifier::getSectionIdentifierValue($row);
                if(!is_object($row) || empty($sectionXmlId) || empty($row->meta->href) || empty($row->name)){
                    continue;
                }

                $row->meta->href = explode('?', $row->meta->href)[0];

                $current = isset($currentList[$row->meta->href]) ? $currentList[$row->meta->href] : null;

                $parentHref = property_exists($row, 'productFolder') ? $row->productFolder->meta->href : 'null';
                $description = property_exists($row, 'description') ? (!empty($row->description) ? $row->description : 'null') : 'null';
                $pathName = property_exists($row, 'pathName') ? (!empty($row->pathName) ? $row->pathName : 'null') : 'null';

                $updFields = [
                    self::$ufFields['href']['NAME'] => $row->meta->href,
                    self::$ufFields['externalCode']['NAME'] => $sectionXmlId,
                    self::$ufFields['pathName']['NAME'] => $pathName,
                    self::$ufFields['parentHref']['NAME'] => $parentHref,
                    self::$ufFields['archived']['NAME'] => (string)(int)$row->archived,
                    self::$ufFields['name']['NAME'] => $row->name,
                    self::$ufFields['description']['NAME'] => $description,
                    self::$ufFields['date']['NAME'] => $dateUpdate
                ];
               
                if(is_array($current) && isset($current['ID']) && (int)$current['ID'] > 0){
                    $result = $entityClass::update($current['ID'], $updFields);
                    if ($result->isSuccess()) {
                        $counter->update();
                    } else {
                        $counter->error(LangMsg::get('ERROR_HL_UPDATE_ITEM', ['#ID#' => $current['ID']]));
                    }             
                } else {
                    $result = $entityClass::add($updFields);
                    if ($result->isSuccess()) {
                        $counter->add();
                    } else {
                        $counter->error(LangMsg::get('ERROR_HL_ADD_ITEM', ['#ID#' => $row->meta->href]));
                    }
                }
                
            }

        }

        return $counter;
    }

    public static function getList(array $params = []): array
    {
        $entityClass = parent::getEntityDataClass(self::getTableName());
        if ($entityClass === null) return [];

        if(!isset($params['order'])) {
            $params['order'] = [
                self::$ufFields['date']['NAME'] => 'DESC'
            ];
        }

        $list = $entityClass::getList($params)->fetchAll();
        $result = [];
        foreach ($list as $item) {
            $result[$item[self::$ufFields['href']['NAME']]] = self::buildPfolderItemParams($item);
        }
        return $result;
    }

    public static function get($pFolderHref = ''): array
    {
        $result = self::getList([
            'filter' => [
                '=' . self::$ufFields['href']['NAME'] => $pFolderHref
            ]
        ]);
        return count($result) > 0 ? array_pop($result) : [];
    }    

    public static function getListFromRootGroup(string $rootGroupHref, bool $withRootGroup = false): array
    {
        $rootGroupItem = self::get($rootGroupHref);
        if(!empty($rootGroupItem['ID']) && !empty($rootGroupItem['NAME'])) {

            $resultFirstLayer = self::getList([
                'filter' => [
                    '=' . self::$ufFields['pathName']['NAME'] => $rootGroupItem['NAME']
                ]
            ]);

            $resultOtherLayer = self::getList([
                'filter' => [
                    self::$ufFields['pathName']['NAME'] => $rootGroupItem['NAME'] . '/%'
                ]
            ]);

            return $withRootGroup ?  [$rootGroupHref => $rootGroupItem] + $resultFirstLayer + $resultOtherLayer : $resultFirstLayer + $resultOtherLayer;
        }
        
        return [];
    }

        private static function buildPfolderItemParams(array $item = []): array
        {
            $archived = null;
            switch ($item[self::$ufFields['archived']['NAME']]) {
                case '1':
                    $archived = false;
                    break;
                case '0':
                    $archived = true;
                    break;
            }

            $active = 'Y';
            if ($item[self::$ufFields['archived']['NAME']] === '1') {
                $active = 'N';
            }

            return [
                'ID' => $item['ID'],
                'HREF' => $item[self::$ufFields['href']['NAME']],
                'XML_ID' => $item[self::$ufFields['externalCode']['NAME']],
                'PATH' => $item[self::$ufFields['pathName']['NAME']] !== 'null' ? $item[self::$ufFields['pathName']['NAME']] : '',
                'PARENT_HREF' => $item[self::$ufFields['parentHref']['NAME']] !== 'null' ? $item[self::$ufFields['parentHref']['NAME']] : '',
                'ARCHIVED' => $archived,
                'ACTIVE' => $active,
                'NAME' => $item[self::$ufFields['name']['NAME']],
                'DESCRIPTION' => $item[self::$ufFields['description']['NAME']] !== 'null' ? $item[self::$ufFields['description']['NAME']] : '',
                'DATE_UPDATE' => $item[self::$ufFields['date']['NAME']] instanceof \Bitrix\Main\Type\DateTime ? $item[self::$ufFields['date']['NAME']]->format('d.m.Y H:i:s') : ''
            ];
        }

    public static function deleteById($id = 0)
    {
        $entityClass = parent::getEntityDataClass(self::getTableName());
        if($entityClass === null) return false;
        return $entityClass::delete($id);
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

    public static function getArray($productHref = [])
    {
        $entityClass = parent::getEntityDataClass(self::getTableName());
        if($entityClass === null) return false;

        $current = $entityClass::getList([
            'filter' => [
                "=" . self::$ufFields['href']['NAME'] => $productHref
            ]
        ])->fetchAll();
        
        $result = [];
        foreach($current as $item){
            $result[$item[self::$ufFields['href']['NAME']]] = $item[self::$ufFields['externalCode']['NAME']];
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