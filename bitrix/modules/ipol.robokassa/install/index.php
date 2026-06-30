<?php

    use Bitrix\Main\Localization\Loc;
    use Bitrix\Sale\PaySystem;
    use Ipol\Robokassa;

    global $MESS;
    IncludeModuleLangFile(__FILE__);

    if (class_exists("ipol_robokassa")) return;

    Class ipol_robokassa extends CModule
    {
        var $MODULE_ID = "ipol.robokassa";
        var $MODULE_VERSION;
        var $MODULE_VERSION_DATE;
        var $MODULE_NAME;
        var $MODULE_DESCRIPTION;
        var $MODULE_CSS;
        var $MODULE_GROUP_RIGHTS = "N";
        var $PARTNER_NAME;
        var $PARTNER_URI;

        /** @var Bitrix\Main\Entity\DataManager[] $tables */
        private static array $tables = [
            Robokassa\Internals\OrderTable::class,
            Robokassa\Internals\BasketItemTable::class,
            Robokassa\Internals\RefundOrderTable::class,
        ];

        function __construct()
        {
            $arModuleVersion = array();
            $path = str_replace("\\", "/", __FILE__);
            $path = substr($path, 0, strlen($path) - strlen("/index.php"));
            include($path."/version.php");
            if (is_array($arModuleVersion) && array_key_exists("VERSION", $arModuleVersion))
            {
                $this->MODULE_VERSION = $arModuleVersion["VERSION"];
                $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
            }
            $this->PARTNER_NAME = Loc::getMessage("ROBOKASSA.PARTNER_NAME");
            $this->PARTNER_URI = Loc::getMessage("ROBOKASSA.PARTNER_URI");
            $this->MODULE_NAME = Loc::getMessage("ROBOKASSA.MODULE_NAME");
            $this->MODULE_DESCRIPTION = Loc::getMessage("ROBOKASSA.MODULE_DESCRIPTION");
        }

        function InstallFiles($arParams = array())
        {

            CopyDirFiles(
                $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/ipol.robokassa/install/handler',
                $_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/sale_payment/',
                true,
                true
            );

            CopyDirFiles(
                $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/ipol.robokassa/install/admin',
                $_SERVER["DOCUMENT_ROOT"].'/bitrix/admin',
                true,
                true
            );

            CopyDirFiles(
                $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/ipol.robokassa/install/tools',
                $_SERVER["DOCUMENT_ROOT"].'/bitrix/tools',
                true,
                true
            );

            CopyDirFiles(
                $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/ipol.robokassa/install/themes',
                $_SERVER["DOCUMENT_ROOT"].'/bitrix/themes',
                true,
                true
            );

            CopyDirFiles(
                $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/ipol.robokassa/install/components',
                $_SERVER["DOCUMENT_ROOT"].'/bitrix/components',
                true,
                true
            );

            return true;
        }

        function UnInstallFiles()
        {

            DeleteDirFilesEx($_SERVER['DOCUMENT_ROOT'].'/bitrix/php_interface/include/sale_payment/robokassapayment');

            DeleteDirFiles(
                $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/ipol.robokassa/install/admin',
                $_SERVER["DOCUMENT_ROOT"].'/bitrix/admin'
            );

            DeleteDirFiles(
                $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/ipol.robokassa/install/tools',
                $_SERVER["DOCUMENT_ROOT"].'/bitrix/tools'
            );

            DeleteDirFiles(
                $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/ipol.robokassa/install/themes',
                $_SERVER["DOCUMENT_ROOT"].'/bitrix/themes'
            );

            DeleteDirFiles(
                $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/ipol.robokassa/install/components',
                $_SERVER["DOCUMENT_ROOT"].'/bitrix/components'
            );

            return true;
        }

        function DoInstall()
        {

            global $APPLICATION;

            RegisterModule("ipol.robokassa");

            \Bitrix\Main\Loader::includeModule('ipol.robokassa');
            \Bitrix\Main\Loader::includeModule('sale');

            $this->InstallDB();
            $this->InstallFiles();
            $this->InstallEventMessageType();

            $eventManager = \Bitrix\Main\EventManager::getInstance();

            $eventManager->registerEventHandler(
                'sale',
                'OnSaleStatusOrder',
                $this->MODULE_ID,
                '\Ipol\Robokassa\RobokassaPaymentService',
                'sendSecondCheck'
            );

            $eventManager->registerEventHandler(
                'sale',
                'OnSaleStatusOrder',
                $this->MODULE_ID,
                '\Ipol\Robokassa\RobokassaRefund',
                'onSaleBeforeStatusOrderChange'
            );

            $APPLICATION->IncludeAdminFile(
                Loc::getMessage("ROBOKASSA.MODULE_INSTALL_TITLE"),
                $_SERVER['DOCUMENT_ROOT']."/bitrix/modules/ipol.robokassa/install/step.php"
            );

            try
            {
                $fields = [
                    'NAME' => Loc::getMessage('ROBOKASSA_PAYMENT_CREATE_NAME'),
                    'PSA_NAME' => Loc::getMessage('ROBOKASSA_PAYMENT_CREATE_PAY_NAME'),
                    'DESCRIPTION' => Loc::getMessage('ROBOKASSA_PAYMENT_CREATE_DESCRIPTION'),
                    'ACTIVE' => 'N',
                    'CAN_PRINT_CHECK' => 'N',
                    'NEW_WINDOW' => 'N',
                    'ALLOW_EDIT_PAYMENT' => 'Y',
                    'IS_CASH' => 'N',
                    'ENTITY_REGISTRY_TYPE' => \Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER,
                    'SORT' => 500,
                    'ACTION_FILE' => 'robokassapayment',
                    'PS_MODE' => $psMode ?? '',
                    'XML_ID' => PaySystem\Manager::generateXmlId()
                ];

                $fields['LOGOTIP'] = CFile::MakeFileArray($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/ipol.robokassa/install/images/payment-logo.png");

                $fields['LOGOTIP']['MODULE_ID'] = "sale";

                CFile::SaveForDB($fields, 'LOGOTIP', 'sale/paysystem/logotip');

                PaySystem\Manager::add($fields);
            }
            catch (\Exception $e)
            {}
        }

        function DoUninstall()
        {

            global $APPLICATION;

            \Bitrix\Main\Loader::includeModule('ipol.robokassa');

            $this->postNotification();

            UnRegisterModule("ipol.robokassa");

            $this->UnInstallDB();
            $this->UnInstallFiles();
            $this->UnInstallEventMessageType();

            $eventManager = \Bitrix\Main\EventManager::getInstance();

            $eventManager->unRegisterEventHandler(
                'sale',
                'OnSaleStatusOrder',
                $this->MODULE_ID,
                '\Ipol\Robokassa\RobokassaPaymentService',
                'sendSecondCheck'
            );

            $eventManager->unRegisterEventHandler(
                'sale',
                'OnSaleStatusOrder',
                $this->MODULE_ID,
                '\Ipol\Robokassa\RobokassaRefund',
                'onSaleBeforeStatusOrderChange'
            );

            $APPLICATION->IncludeAdminFile(
                Loc::getMessage("ROBOKASSA.MODULE_UNINSTALL_TITLE"),
                $_SERVER['DOCUMENT_ROOT']."/bitrix/modules/ipol.robokassa/install/unstep.php"
            );
        }

        function postNotification()
        {

            $httpClient = new \Bitrix\Main\Web\HttpClient;

            $merchant = '';

            if(\Bitrix\Main\Loader::includeModule('sale'))
            {

                $payments = \Bitrix\Sale\PaySystem\Manager::getList(
                    array(
                        'filter' => [
                            'ACTION_FILE' => 'robokassapayment'
                        ],
                    )
                )->fetchAll();

                foreach($payments as $payment)
                {
                    $value = \Bitrix\Sale\Internals\BusinessValueTable::getList(
                        [
                            'filter' => [
                                'CODE_KEY' => 'SHOPLOGIN',
                                'CONSUMER_KEY' => 'PAYSYSTEM_' . $payment['ID'],
                            ]
                        ]
                    )->fetch();

                    if(!empty($value['PROVIDER_VALUE']))
                    {
                        $merchant = $value['PROVIDER_VALUE'];
                    }
                }

                if(empty($merchant))
                {

                    $value = \Bitrix\Sale\Internals\BusinessValueTable::getList(
                        [
                            'filter' => [
                                'CODE_KEY' => 'SHOPLOGIN',
                                '!PROVIDER_VALUE' => false
                            ]
                        ]
                    )->fetch();

                    if(!empty($value['PROVIDER_VALUE']))
                    {
                        $merchant = $value['PROVIDER_VALUE'];
                    }
                }
            }
            else
            {
                $merchant = \Bitrix\Main\Config\Option::get(
                    $this->MODULE_ID,
                    'START_FUNCTION_OPTIONS_SHOPLOGIN',
                    ''
                );
            }

            $site = \CSite::GetList(
                $by = "sort",
                $order = "asc",
                [
                    'DEF' => 'Y'
                ]
            )->GetNext(true, false);

            if(empty($site))
            {
                $site = \CSite::GetList(
                    $by = "sort",
                    $order = "asc",
                    [
                        'ACTIVE' => 'Y'
                    ]
                )->GetNext(true, false);
            }

            if(empty($site['SERVER_NAME']))
            {
                $site['SERVER_NAME'] = \Bitrix\Main\Config\Option::get('main', 'server_name', '');
            }

            $httpClient->setHeader('X-API-KEY','robokassa-plugin-stat-key-3953');
            $httpClient->setHeader('Content-Type', 'application/json');

            $response = $httpClient->query(
                $httpClient::HTTP_POST,
                'https://pulse.robokassa.com/api/module-status',
                \Bitrix\Main\Web\Json::encode(
                    [
                        "cms" => "bitrix",
                        "merchant_id" => $merchant,
                        "site_id" => $site['SERVER_NAME'] ?? '',
                        "status" => "disabled",
                        "reported_at" => (new \DateTime())->format(\DATE_ATOM)
                    ]
                )
            );
        }

        function InstallDB()
        {

            $connection = \Bitrix\Main\Application::getConnection();

            foreach (self::$tables as $table)
            {
                if(!$connection->isTableExists($table::getTableName()))
                {
                    $table::getEntity()->createDbTable();
                }
            }

            return true;
        }

        function UnInstallDB()
        {

            $connection = \Bitrix\Main\Application::getConnection();

            foreach (self::$tables as $table)
            {
                if($connection->isTableExists($table::getTableName()))
                {
                    $connection->dropTable($table::getTableName());
                }
            }

            return true;
        }

        public function UnInstallEventMessageType(): void
        {

            foreach(
                [
                    'ROBOKASSA_SIMPLE_ORDER_PAYED',
                    'ROBOKASSA_SIMPLE_ORDER_NEW_ORDER'
                ] as $eventType
            )
            {

                $eventMessages = \CEventMessage::GetList('id', 'desc', ['EVENT_NAME' => $eventType]);

                while ($eventMessage = $eventMessages->GetNext())
                {
                    \CEventMessage::Delete($eventMessage['id']);
                }

                \CEventType::Delete($eventType);
            }
        }

        public function InstallEventMessageType(): void
        {

            $languages = CLanguage::GetList();

            while ($language = $languages->Fetch())
            {
                \CEventType::Add(
                    [
                        "LID" => $language["LID"],
                        "EVENT_NAME" => "ROBOKASSA_SIMPLE_ORDER_NEW_ORDER",
                        "NAME" => Loc::getMessage("ROBOKASSA_SIMPLE_ORDER_NEW_ORDER_NAME"),
                        "DESCRIPTION" => Loc::getMessage("ROBOKASSA_SIMPLE_ORDER_NEW_ORDER_DESCRIPTION"),
                        "SORT" => 100,
                    ]
                );

                \CEventType::Add(
                    [
                        "LID" => $language["LID"],
                        "EVENT_NAME" => "ROBOKASSA_SIMPLE_ORDER_PAYED",
                        "NAME" => Loc::getMessage("ROBOKASSA_SIMPLE_ORDER_PAYED_NAME"),
                        "DESCRIPTION" => Loc::getMessage("ROBOKASSA_SIMPLE_ORDER_PAYED_DESCRIPTION"),
                        "SORT" => 100,
                    ]
                );

                $arSites = array();
                $sites = \CSite::GetList("", "", Array("LANGUAGE_ID"=>$language["LID"]));
                while ($site = $sites->Fetch())
                    $arSites[] = $site["LID"];

                (new \CEventMessage)->Add(
                    [
                        'ACTIVE' => 'Y',
                        'EVENT_NAME' => 'ROBOKASSA_SIMPLE_ORDER_NEW_ORDER',
                        'LID' => $arSites,
                        'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
                        'EMAIL_TO' => '#DEFAULT_EMAIL_FROM#',
                        'SUBJECT' => Loc::getMessage('ROBOKASSA_SIMPLE_ORDER_NEW_ORDER_ADMIN_MAIL_EVENT_SUBJECT'),
                        'MESSAGE' => Loc::getMessage('ROBOKASSA_SIMPLE_ORDER_NEW_ORDER_ADMIN_MAIL_EVENT_MESSAGE'),
                        'BODY_TYPE' => 'html',
                    ]
                );

                (new \CEventMessage)->Add(
                    [
                        'ACTIVE' => 'Y',
                        'EVENT_NAME' => 'ROBOKASSA_SIMPLE_ORDER_NEW_ORDER',
                        'LID' => $arSites,
                        'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
                        'EMAIL_TO' => '#CLIENT_EMAIL#',
                        'SUBJECT' => Loc::getMessage('ROBOKASSA_SIMPLE_ORDER_NEW_ORDER_CLIENT_MAIL_EVENT_SUBJECT'),
                        'MESSAGE' => Loc::getMessage('ROBOKASSA_SIMPLE_ORDER_NEW_ORDER_CLIENT_MAIL_EVENT_MESSAGE'),
                        'BODY_TYPE' => 'html',
                    ]
                );

                (new \CEventMessage)->Add(
                    [
                        'ACTIVE' => 'Y',
                        'EVENT_NAME' => 'ROBOKASSA_SIMPLE_ORDER_PAYED',
                        'LID' => $arSites,
                        'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
                        'EMAIL_TO' => '#DEFAULT_EMAIL_FROM#',
                        'SUBJECT' => Loc::getMessage('ROBOKASSA_SIMPLE_ORDER_PAYED_ADMIN_MAIL_EVENT_SUBJECT'),
                        'MESSAGE' => Loc::getMessage('ROBOKASSA_SIMPLE_ORDER_PAYED_ADMIN_MAIL_EVENT_MESSAGE'),
                        'BODY_TYPE' => 'html',
                    ]
                );

                (new \CEventMessage)->Add(
                    [
                        'ACTIVE' => 'Y',
                        'EVENT_NAME' => 'ROBOKASSA_SIMPLE_ORDER_PAYED',
                        'LID' => $arSites,
                        'EMAIL_FROM' => '#DEFAULT_EMAIL_FROM#',
                        'EMAIL_TO' => '#CLIENT_EMAIL#',
                        'SUBJECT' => Loc::getMessage('ROBOKASSA_SIMPLE_ORDER_PAYED_CLIENT_MAIL_EVENT_SUBJECT'),
                        'MESSAGE' => Loc::getMessage('ROBOKASSA_SIMPLE_ORDER_PAYED_CLIENT_MAIL_EVENT_MESSAGE'),
                        'BODY_TYPE' => 'html',
                    ]
                );
            }
        }
    }