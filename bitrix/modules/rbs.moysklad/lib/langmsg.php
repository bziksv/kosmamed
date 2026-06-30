<?php
namespace Rbs\Moysklad;

use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);
Loc::setCurrentLang('ru');
class LangMsg
{
    public static function get($messId = '', $params = [], $defaultMsg = '')
    {
        $locMsg = Loc::getMessage($messId, $params);
        if (mb_strlen($locMsg)) {
            return $locMsg;
        } else {
            return str_replace(array_keys($params), array_values($params), $defaultMsg);
        }
    }
}
