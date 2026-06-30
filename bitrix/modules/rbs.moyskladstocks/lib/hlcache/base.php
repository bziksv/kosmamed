<?
namespace Rbs\MoyskladStocks\HlCache;

\Bitrix\Main\Loader::includeModule('highloadblock');

use Bitrix\Highloadblock\HighloadBlockTable;
use Rbs\MoyskladStocks\Debug;
use Rbs\MoyskladStocks\LangMsg;
use Rbs\MoyskladStocks\Utils;

class Base
{
    public static function getTableInfo($tableName = '')
    {
        if(!empty($tableName)){
            $rsData = HighloadBlockTable::getList([
                'filter'=> [
                    '=TABLE_NAME'=> $tableName
                ]
            ]);
            if($obTable = $rsData->fetch()){
                return $obTable;
            }
        }

        return null;
    }

    public static function getEntityDataClass($tableName = '')
    {
        if($tableName !== null){
            $table = self::getTableInfo($tableName);
            if($table !== null){
                $entity = HighloadBlockTable::compileEntity($table);
                return $entity->getDataClass();
            }
        }
        return null;
    }

    public static function isExsist($tableName = ''): bool
    {
        $entityClass = self::getEntityDataClass($tableName);

        if($entityClass === null){
            return false;
        }

        return true;
    }

    public static function createTable($tableParams = [], $ufFields = []): int
    {
        $result = HighloadBlockTable::add($tableParams);
        if($result->isSuccess()){
            
            $eId = $result->getId();
            if(Utils::is_count($ufFields)){
                foreach($ufFields as $uf){
                    $uf['ENTITY_ID'] = 'HLBLOCK_' . $eId;
                    $obUserField  = new \CUserTypeEntity;
                    $obUserField->Add($uf);
                }
            }
            return $eId;

        } else {

            $writer = new Debug\Writer(LangMsg::get('ERROR_HL_CREATE_TABLE'));
            $loger = new Debug\Loger();
            $loger->addMessageArray($result->getErrorMessages(), Debug\Message::TYPE_ERROR);
            $writer->setLogerMessages($loger->getMessageArray());
            $writer->exportLog();

        }

        return 0;
    }

    public static function getUfFieldsArray($ufStructure = []): array
    {
        $arUfFields = [];
        foreach($ufStructure as $ufField){

            $langDescr = LangMsg::get($ufField['NAME']);
            $langArray = ['ru'=> $langDescr, 'en'=> $langDescr];

            $arUfFields[] = [
                'FIELD_NAME' => $ufField['NAME'],
                'USER_TYPE_ID' => $ufField['TYPE'],
                'MANDATORY' => isset($ufField['MANDATORY']) ? $ufField['MANDATORY'] : 'Y',
                'SHOW_FILTER' => 'E',
                "EDIT_FORM_LABEL" => $langArray, 
                "LIST_COLUMN_LABEL" => $langArray,
                "LIST_FILTER_LABEL" => $langArray, 
                "ERROR_MESSAGE" => ['ru'=>'', 'en'=>''], 
                "HELP_MESSAGE" => ['ru'=>'', 'en'=>'']
            ];
        }

        return $arUfFields;
    }
}