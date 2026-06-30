<?php
namespace Ipolh\SDEK;

use Bitrix\Main\Entity\ExpressionField;

trait TableHelpers
{
    /**
     * Returns object data by primary ID.
     *
     * @param  int $id primary index
     * @param  array $select
     * @return array
     */
    public static function getByPrimaryId($id, $select = array())
    {
        return self::getList(array_filter(['select' => $select ?: null, 'filter' => ['=ID' => $id]]))->fetch();
    }

    /**
     * Return number of rows with some data
     *
     * @param  bool $onlyActive
     * @return int
     */
    public static function getDataCount($onlyActive = true)
    {
        $params = ['select' => ['CNT'], 'runtime' => [new ExpressionField('CNT', 'COUNT(*)')]];

        if ($onlyActive) {
            if (isset(self::$isActiveFieldName))
                $params['filter'] = [self::$isActiveFieldName => 'Y'];
            else
                $params['filter'] = ['SYNC_IS_ACTIVE' => 'Y'];
        }

        $result = self::getList($params)->fetch();
        return $result['CNT'];
    }
}