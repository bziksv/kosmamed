<?php
namespace Rbs\Moysklad\Diagnostic\Dashboard\Items\Bitrix;

use Rbs\Moysklad\Diagnostic\Dashboard\Core\BaseItem;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class CronAgents extends BaseItem
{   
    protected function setTitle(): void
    {
        $this->title = Loc::getMessage('AGENTY_BITRIKSA_NA_CRON');
    }

    protected function setDescription(): void
    {
        $this->description = Loc::getMessage('PROVERKA_ZAPUSKA_AGENTOV_BITRIKSA_CHEREZ_CRON_');
    } 

    protected function executeItemCheck(): void
    {
        $this->value = Loc::getMessage('OSHIBKA');
        $this->status = self::STATUS_ERROR;

        if (defined('BX_CRONTAB')) {
            $this->value = Loc::getMessage('OSHIBKA');
            $this->status = self::STATUS_ERROR;
            $this->valueDescription = Loc::getMessage('KONSTANTA_BX_CRONTAB_OPREDELENA__ZAPUSK_AGENTOV_CHEREZ_CRON_NEVOZMOZHEN_');
            return;
        }

        $isCronMode = \COption::GetOptionString("main", "agents_use_crontab", "N") == "Y" || 
                      (defined('BX_CRONTAB_SUPPORT') && BX_CRONTAB_SUPPORT === true) || 
                      \COption::GetOptionString("main", "check_agents", "Y") != "Y";
        
        if (!$isCronMode) {
            $this->value = Loc::getMessage('PROVERKA_NA_HITAH');
            $this->status = self::STATUS_WARNING;
            $this->valueDescription = Loc::getMessage('AGENTY_ZAPUSKAJUTSJA_NA_HITAH__REKOMENDUETSJA_NASTROIT__VYPOLNENIE_AGENTOV_CHEREZ_CRON_');
            return;
        }
        
        $lastAgentExecTime = \COption::GetOptionString("main", "last_agents_check", 0);
        $currentTime = time();
        
        if ($currentTime - $lastAgentExecTime < 1800) {
            $this->value = Loc::getMessage('USPESHNO');
            $this->status = self::STATUS_SUCCESS;
            $this->valueDescription = Loc::getMessage('POSLEDNIJ_ZAPUSK__') . date('d.m.Y H:i:s', $lastAgentExecTime);
        } else {
            $connection = \Bitrix\Main\Application::getConnection();
            $helper = $connection->getSqlHelper();
            
            if ($result = $connection->query('SELECT LAST_EXEC FROM b_agent WHERE LAST_EXEC > ' . $helper->addDaysToDateTime(-1) . ' AND IS_PERIOD = \'N\' LIMIT 1')->fetch()) {
                $this->value = Loc::getMessage('USPESHNO');
                $this->status = self::STATUS_SUCCESS;
                $date = date('d.m.Y H:i:s'); 
                $this->valueDescription = Loc::getMessage('POSLEDNIJ_ZAPUSK__') . $date;
            } else {
                $this->value = Loc::getMessage('OSHIBKA');
                $this->status = self::STATUS_WARNING;
                $this->valueDescription = Loc::getMessage('AGENTY_NASTROENY_NA_VYPOLNENIE_CHEREZ_CRON__NO_ONI_NE_ZAPUSKAJUTSJA__PROVER_TE_NASTROJKI_CRON_');
            }
        }

    }
} 