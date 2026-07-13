<?php

use Bitrix\Main\Config\Option;

class CBoxberryAgents
{

    private static function checkAgent($agentName, $moduleId): void
    {
        $agentExists = CAgent::GetList(
            [],
            [
                'MODULE_ID' => $moduleId,
                'NAME' => $agentName,
            ]
        )->Fetch();

        if (!$agentExists) {
            CAgent::AddAgent(
                $agentName,
                $moduleId,
                'N',
                3600,
                '',
                'Y',
                ConvertTimeStamp(time() + 3600, 'FULL')
            );
        }

    }

    public static function loadReceptionPoints($apiToken = ''): string
    {
        $moduleId = GetModuleID(__FILE__);
        $agentName = 'CBoxberryAgents::loadReceptionPoints();';

        if (!IsModuleInstalled($moduleId)) {
            return '';
        }

        if (!CBoxberry::initApi($apiToken)) {
            return $agentName;
        }

        self::checkAgent($agentName, $moduleId);

        try {

            global $DB;
            if (!$DB->TableExists(ReceptionPointsTable::getTableName())) {
                $DB->Query(
                    'CREATE TABLE IF NOT EXISTS ' . $DB->ForSql(ReceptionPointsTable::getTableName()) . ' (
                        ID INT NOT NULL AUTO_INCREMENT,
                        CODE VARCHAR(255) NOT NULL,
                        NAME VARCHAR(255) NOT NULL,
                        CITY VARCHAR(255) NOT NULL,
                        PRIMARY KEY (ID)
                    )'
                );
            }

            $pointsForParcels = CBoxberry::pointsForParcels();
            if (!is_array($pointsForParcels)) {
                return $agentName;
            }
            ReceptionPointsTable::getEntity()->cleanCache();
            ReceptionPointsTable::addReceptionPoints($pointsForParcels);
        } catch (\Exception $e) {
        }

        return $agentName;

    }
}