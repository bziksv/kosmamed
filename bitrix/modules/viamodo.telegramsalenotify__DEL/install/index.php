<?php

use Bitrix\Main\EventManager;
use Bitrix\Main\Config\Option;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class viamodo_telegramsalenotify extends CModule
{

    public $MODULE_ID = 'viamodo.telegramsalenotify';
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $MODULE_CSS;
    public $MODULE_GROUP_RIGHTS = 'Y';

    public function __construct()
    {
        $arModuleVersion = array();
        include(dirname(__FILE__) . "/version.php");
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = GetMessage('VIAMODO_TSN_INSTALL_NAME');
        $this->MODULE_DESCRIPTION = GetMessage('VIAMODO_TSN_INSTALL_DESCRIPTION');
        $this->PARTNER_NAME = GetMessage('VIAMODO_TSN_INSTALL_COMPANY_NAME');
        $this->PARTNER_URI = 'https://agrebnev.ru/';
    }

    public function InstallDB($install_wizard = true)
    {
        ModuleManager::registerModule($this->MODULE_ID);

        return true;
    }

    public function UnInstallDB($arParams = array())
    {
        Option::delete($this->MODULE_ID);

        ModuleManager::unRegisterModule($this->MODULE_ID);

        return true;
    }

    public function InstallEvents()
    {
        $eventManager = EventManager::getInstance();

        $eventManager->registerEventHandler(
            'sale',
            'OnSaleOrderSaved',
            $this->MODULE_ID,
            '\Viamodo\Telegramsalenotify\Handler',
            'OnSaleOrderSaved'
        );

        $eventManager->registerEventHandler(
            'sale',
            'OnSaleOrderPaid',
            $this->MODULE_ID,
            '\Viamodo\Telegramsalenotify\Handler',
            'OnSaleOrderPaid'
        );

        return true;
    }

    public function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();

        $eventManager->unRegisterEventHandler(
            'sale',
            'OnSaleOrderSaved',
            $this->MODULE_ID,
            '\Viamodo\Telegramsalenotify\Handler',
            'OnSaleOrderSaved'
        );

        $eventManager->unRegisterEventHandler(
            'sale',
            'OnSaleOrderPaid',
            $this->MODULE_ID,
            '\Viamodo\Telegramsalenotify\Handler',
            'OnSaleOrderPaid'
        );

        return true;
    }

    public function InstallFiles()
    {
        return true;
    }

    public function InstallPublic()
    {
        return true;
    }

    public function InstallOptions()
    {
        return true;
    }

    public function UnInstallFiles()
    {
        return true;
    }

    public function DoInstall()
    {
        global $APPLICATION, $step;

        $this->InstallFiles();
        $this->InstallDB(false);
        $this->InstallEvents();
        $this->InstallPublic();

        return true;
    }

    public function DoUninstall()
    {
        global $APPLICATION, $step;

        $this->UnInstallDB();
        $this->UnInstallFiles();
        $this->UnInstallEvents();

        return true;
    }

}
