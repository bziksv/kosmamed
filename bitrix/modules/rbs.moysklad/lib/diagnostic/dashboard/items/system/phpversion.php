<?php
namespace Rbs\Moysklad\Diagnostic\Dashboard\Items\System;

use Rbs\Moysklad\Diagnostic\Dashboard\Core\BaseItem;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

class PhpVersion extends BaseItem
{
    protected function setTitle(): void
    {
        $this->title = Loc::getMessage('VERSIJA_PHP');
    }

    protected function setDescription(): void
    {
        $this->description = Loc::getMessage('PROVERKA_VERSII_PHP_');
    }

    protected function executeItemCheck(): void
    {
        $this->value = Loc::getMessage('NE_OPREDELENA');
        $this->status = self::STATUS_ERROR;
        if (defined('PHP_VERSION')) {
            $this->value = PHP_VERSION;
            $this->status = version_compare(PHP_VERSION, '7.4', '>=') ? self::STATUS_SUCCESS : self::STATUS_ERROR;
        } else {
            $this->valueDescription = Loc::getMessage('NE_OPREDELENA_VERSIJA_PHP');
        }
    }
} 