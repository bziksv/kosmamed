<?
/**DEPRECATED */
namespace Rbs\Moysklad;

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Lang
{
    static function get($messId = '', $params = [])
    {
        if(!empty($params)){
            return Loc::getMessage($messId, $params) ?? $messId;
        }
        return Loc::getMessage($messId) ?? $messId;
    }

    static function getMessage($messageLoc = '', $arParams = [], $message = '')
    {
        $locMsg = Loc::getMessage($messageLoc, $arParams);
        if(strlen($locMsg)){
            return $locMsg;
        } else {
            return str_replace(array_keys($arParams), array_values($arParams), $message);
        }
    }
}