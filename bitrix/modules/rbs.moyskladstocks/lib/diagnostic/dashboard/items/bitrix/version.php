<?php
namespace Rbs\MoyskladStocks\Diagnostic\Dashboard\Items\Bitrix;

use Rbs\MoyskladStocks\Diagnostic\Dashboard\Core\BaseItem;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class Version extends BaseItem
{
    protected function setTitle(): void
    {
        $this->title = Loc::getMessage('VERSIJA_BITRIKSA');
    }

    protected function setDescription(): void
    {
        $this->description = Loc::getMessage('PROVERKA_VERSII_BITRIKSA_');
    } 

    protected function executeItemCheck(): void
    {
        $this->value = Loc::getMessage('NE_ZAPOLNENO');
        $this->status = self::STATUS_ERROR;
        $this->valueDescription = Loc::getMessage('OSHIBKA__VERSIJA_BITRIKS_NE_OPREDELENA');
        if (defined('SM_VERSION')) {
            $this->value = SM_VERSION;
            $this->status = self::STATUS_SUCCESS;
            $date = date('d.m.Y', strtotime(SM_VERSION_DATE));
            $this->valueDescription = Loc::getMessage('DATA_OBNOVLENIJA__') . $date;
        }
    }
} 