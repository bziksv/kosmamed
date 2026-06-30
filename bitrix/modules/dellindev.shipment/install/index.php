<?php
IncludeModuleLangFile(__FILE__);

use Bitrix\Main\EventManager;
use Bitrix\Main\Localization\Loc;

class dellindev_shipment extends CModule
{
    var $MODULE_ID = "dellindev.shipment";
    var $MODULE_VERSION;
    var $MODULE_VERSION_DATE;
    var $MODULE_NAME;
    var $MODULE_DESCRIPTION;
    var $PARTNER_NAME;
    var $PARTNER_URI;
    var $MODULE_GROUP_RIGHTS = 'N';
    var $NEED_MAIN_VERSION = '';
    var $NEED_MODULES = ['sale'];

    public $arTimeValues;
    public $messages = [];


    function __construct()
    {
        
        $arModuleVersion = array();

        $path = str_replace("\\", "/", __FILE__);
        $path = substr($path, 0, strlen($path) - strlen("/index.php"));
        include($path . "/version.php");    

        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

        $this->MODULE_NAME = GetMessage("DELLIN_MODULE_NAME");
        $this->MODULE_DESCRIPTION = GetMessage("DELLIN_MODULE_DESCRIPTION");

        $this->PARTNER_NAME = GetMessage("DELLIN_PARTNER_NAME");
        $this->PARTNER_URI = "https://www.dellin.ru/";
    }






    function AddOrderProps()
    {
        $arrPropertiesAllPersonType = [
            ['NAME'=> GetMessage('DELLIN_DELIVERYTIME_START'), 'CODE' => 'DELLIN_DELIVERYTIME_START'],
            ['NAME'=> GetMessage('DELLIN_DELIVERYTIME_END'), 'CODE' => 'DELLIN_DELIVERYTIME_END'],
            ['NAME'=> GetMessage('TERMINAL_ID'), 'CODE' =>'TERMINAL_ID']
        ];

//        $arPropertiesJuridicalPersonType = [
//            ['NAME'=>GetMessage('JURIDICAL_OPF_COUNTRY'),'CODE'=>'JURIDICAL_OPF_COUNTRY'],
//            ['NAME'=>GetMessage('JURIDICAL_OPF'),'CODE'=>'JURIDICAL_OPF'],
//            ['NAME'=>GetMessage('JURIDICAL_ADDRESS'),'CODE'=>'JURIDICAL_ADDRESS']
//        ];

        if(CModule::IncludeModule('sale')){
            $resPersonTypes = CSalePersonType::GetList(["SORT" => "ASC"], []);
            //��������� ��� ����������
            $personTypes = [];
            //����������
            while ($personType = $resPersonTypes->Fetch()){
                $personTypes[] = [
                    'ID' => $personType['ID'],
                    'LID' => $personType['LID'],
                    'LIDS' => $personType['LIDS']
                ];
            }
            //������ �� ���������� �����.
            foreach ($personTypes as $typeForWork){
                $this->AddPersonOrderProps($arrPropertiesAllPersonType, $typeForWork['ID']);
            }

        }

    }



    function AddPersonOrderProps($arProps, $personType)
    {
        $this->generateTimeDeliveryValue();

        foreach ($arProps as $arProperty) {

            $arFields = [
                "PERSON_TYPE_ID" => $personType,
                "NAME" => $arProperty['NAME'],
                "TYPE" => (strpos($arProperty['CODE'], 'DELIVERYTIME') !== false) ? "SELECT" : "STRING",
                "REQUIRED" => "N",
                "DEFAULT_VALUE" => "",
                "SORT" => 100,
                "CODE" => $arProperty['CODE'],
                "USER_PROPS" => "N",
                "IS_LOCATION" => "N",
                "IS_LOCATION4TAX" => "N",
                "PROPS_GROUP_ID" => 1,
                "SIZE1" => 0,
                "SIZE2" => 0,
                "DESCRIPTION" => "",
                "IS_EMAIL" => "N",
                "IS_PROFILE_NAME" => "N",
                "IS_PAYER" => "N"
            ];

            //���� �������� � ������ �� ������.
            $db_props = CSaleOrderProps::GetList( ["SORT" => "ASC"],
                ['CODE' => $arFields['CODE'], "PERSON_TYPE_ID" => $personType], false, false, [] );

            $dataForCheckCreatedProps = $db_props->Fetch();

            if(!$dataForCheckCreatedProps){

                $ID = CSaleOrderProps::Add($arFields);
                if ($ID <= 0) {
                    array_merge($this->messages, [GetMessage('PROPERTY_CREATE_ERROR')]) ;
                } else {
                    array_merge($this->messages, [Loc::getMessage("ADD_PROPERTY") . $arProperty['NAME']]);
                }
            }

            if (strpos($arProperty['CODE'], 'DELIVERYTIME') !== false) {
                CSaleOrderPropsVariant::DeleteAll($ID);
                foreach ($this->arTimeValues as $index => $timeValue) {
                    $arFieldsV = array(
                        "ORDER_PROPS_ID" => $ID,
                        "VALUE" => $timeValue,
                        "NAME" => $timeValue,
                        "SORT" => $index,
                        "DESCRIPTION" => ""
                    );
                    if (!CSaleOrderPropsVariant::Add($arFieldsV))
                        array_merge($this->messages, [Loc::getMessage("ERROR_ADD_PROPS") . $arProperty['CODE']] ) ;
                }
            }
        }
    }

    public function generateTimeDeliveryValue(){


        for($n = 0;$n<=24;$n++) {
            if ($n == 24) {
                $this->arTimeValues[] = "23:59";
            } else {
                $this->arTimeValues[] = (($n < 10) ? "0" : "") . $n . ":00";
            }
        }

    }


    function InstallFiles()
    {
        $pathModule = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/';

        CopyDirFiles($pathModule.'install/php_interface/',
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/', true, true);
        CopyDirFiles($pathModule. '/install/components/',
            $_SERVER["DOCUMENT_ROOT"].'/local/components/', true, true);
        CopyDirFiles($pathModule. '/install/include/tools/',
            $_SERVER["DOCUMENT_ROOT"].'/bitrix/tools/', true, true);
        CopyDirFiles($pathModule. '/install/include/js/',
            $_SERVER["DOCUMENT_ROOT"].'/bitrix/js/', true, true);
       CopyDirFiles($pathModule. '/install/images/',
           $_SERVER["DOCUMENT_ROOT"].'/bitrix/images/', true, true);
           
        return true;
    }

    function UnInstallFiles()
    {
        DeleteDirFilesEx("/bitrix/php_interface/include/sale_delivery/dellin");
        DeleteDirFilesEx('/bitrix/js/dellindev.shipment');
        DeleteDirFilesEx('/bitrix/images/dellindev');
        DeleteDirFilesEx('/bitrix/tools/dellindev.shipment');
        DeleteDirFilesEx('/local/components/dellindev');
        return true;
    }

    function InstallEvents()
    {
        $eventManager = EventManager::getInstance();

        $eventManager->registerEventHandler(
            'sale',
            'OnSaleComponentOrderResultPrepared' ,
            'sale', 'Sale\Handlers\Delivery\DellinHandler',
            'OnSaleComponentOrderResultPrepared');

        $eventManager->registerEventHandler(
            'sale',
            'OnSaleComponentOrderShowAjaxAnswer' ,
            'sale', 'Sale\Handlers\Delivery\DellinHandler',
            'OnSaleComponentOrderShowAjaxAnswer');

        $eventManager->registerEventHandler(
            'sale',
            'onSaleDeliveryTrackingClassNamesBuildList',
            'sale',
            'Sale\Handlers\Delivery\DellinHandler',
            'onSaleDeliveryTrackingClassNamesBuildList'
        );

        $eventManager->registerEventHandler(
            'sale',
            'OnAdminSaleOrderViewDraggable',
            'sale', 'Sale\Handlers\Delivery\DellinBlockAdmin',
            'OnAdminSaleOrderViewDraggable'
        );

    }



    function UnInstallEvents()
    {
        $eventManager = EventManager::getInstance();
        $eventManager->unRegisterEventHandler(
            'sale',
            'OnSaleComponentOrderResultPrepared' ,
            'sale', 'Sale\Handlers\Delivery\DellinHandler',
            'OnSaleComponentOrderResultPrepared');
        $eventManager->unRegisterEventHandler(
            'sale',
            'OnSaleComponentOrderShowAjaxAnswer' ,
            'sale', 'Sale\Handlers\Delivery\DellinHandler',
            'OnSaleComponentOrderShowAjaxAnswer');
        $eventManager->unRegisterEventHandler(
            'sale',
            'onSaleDeliveryTrackingClassNamesBuildList',
            'sale',
            'Sale\Handlers\Delivery\DellinHandler',
            'onSaleDeliveryTrackingClassNamesBuildList'
        );
        $eventManager->unRegisterEventHandler(
            'sale',
            'OnAdminSaleOrderViewDraggable',
            'sale', 'Sale\Handlers\Delivery\DellinBlockAdmin',
            'OnAdminSaleOrderViewDraggable'
        );


    }

    function DoInstall()
    {
        global $APPLICATION, $step;
        // $this->InstallEvents();

        $this->InstallFiles();

        $this->AddOrderProps();
        $this->InstallEvents();
        $step['messages'] = $this->messages;
        RegisterModule($this->MODULE_ID);
        $APPLICATION->IncludeAdminFile(Loc::getMessage("INSTALL_MODULE_DELLIN"),
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/step_1.php');

    }

    function DoUninstall()
    {
        global $APPLICATION, $step;
        $this->UnInstallFiles();
        $this->UnInstallEvents();
        //  $this->AddOrderProps();
        UnRegisterModule($this->MODULE_ID);
        $APPLICATION->IncludeAdminFile(Loc::getMessage("UNINSTALL_MODULE_DELLIN"),
            $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$this->MODULE_ID.'/install/unstep_1.php');

    }
}