<?php

namespace Rbs\Moysklad\Diagnostic\Dashboard\Items\Module;

use Rbs\Moysklad\Diagnostic\Dashboard\Core\BaseItem;
use Rbs\Moysklad\Config;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class GlobalEnable extends BaseItem
{
    protected function setTitle(): void
    {
        $this->title = Loc::getMessage('SOSTOJANIE_MODULJA');
    }

    protected function setDescription(): void
    {
        $this->description = Loc::getMessage('PROVERKA_VKLJUCHENIJA_MODULJA_');
    } 

    protected function executeItemCheck(): void
    {
        $this->value = Loc::getMessage('OTKLJUCHEN');
        $this->status = self::STATUS_WARNING;
        if(Config::checkFeature('global_enabled')) {
            $this->value = Loc::getMessage('VKLJUCHEN');
            $this->status = self::STATUS_SUCCESS;
        }
    }
}