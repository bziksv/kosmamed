<?php
namespace Rbs\MoyskladStocks\Diagnostic\Dashboard\Items\Module;

use Rbs\MoyskladStocks\Diagnostic\Dashboard\Core\BaseItem;
use Rbs\MoyskladStocks\Config;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
 
class CronAgents extends BaseItem
{
    protected function setTitle(): void
    {
        $this->title = Loc::getMessage('AGENTY_MODULJA_NA_CRON'); 
    }

    protected function setDescription(): void
    {
        $this->description = Loc::getMessage('INFORMATSIJA_OB_AGENTAH__KOTORYE_ZAPUSKAJUTSJA_SKRIPTOM_MODULJA_');
    }

    protected function executeItemCheck(): void
    {
        $this->value = Loc::getMessage('NET');
        $this->status = self::STATUS_WARNING;

        if(Config::isLastCronInitActually()) {
            $this->value = Loc::getMessage('USPESHNO');
            $this->status = self::STATUS_SUCCESS;
            $this->valueDescription = Loc::getMessage('POSLEDNIJ_ZAPUSK__') . date('d.m.Y H:i:s', Config::getLastCronInitDate());
        }
    }
} 