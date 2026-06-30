<?php
namespace Rbs\MoyskladStocks\Diagnostic\Dashboard\Items\Module;

use Rbs\MoyskladStocks\Diagnostic\Dashboard\Core\BaseItem;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class Version extends BaseItem
{
    protected function setTitle(): void
    {
        $this->title = Loc::getMessage('VERSIJA_MODULJA');
    }

    protected function setDescription(): void
    {
        $this->description = Loc::getMessage('PROVERKA_VERSII_MODULJA_');
    }

    protected function executeItemCheck(): void
    {
        $this->value = Loc::getMessage('NE_OPREDELENA');
        $this->status = self::STATUS_ERROR;
        $this->valueDescription = Loc::getMessage('OSHIBKA__VERSIJA_MODULJA_NE_OPREDELENA');
        
        $modulePath = dirname(__FILE__, 6) . '/install/version.php';

        if (file_exists($modulePath)) {
            include_once($modulePath);
            if (isset($arModuleVersion) && isset($arModuleVersion["VERSION"])) {
                $this->value = $arModuleVersion["VERSION"];
                $this->status = self::STATUS_SUCCESS;
                $date = date('d.m.Y', strtotime($arModuleVersion["VERSION_DATE"]));
                $this->valueDescription = Loc::getMessage('DATA_OBNOVLENIJA__') . $date;
            }
        }
    }
} 