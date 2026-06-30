<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$server = \Bitrix\Main\Application::getInstance()->getContext()->getServer();

$description = array(
	'MAIN' => Loc::getMessage('R52_QRCODE_DESCRIPTION_PAYSYSTEM'),
);

$data = array(
	'NAME' => Loc::getMessage('R52_QRCODE_NAME'),
	'SORT' => 500,
	'CODES' => array(
		// Параметры QR-code
		'LEVEL' => array(
			'NAME' => Loc::getMessage('R52_QRCODE_LEVEL_NAME'),
			'DESCRIPTION' => Loc::getMessage('R52_QRCODE_LEVEL_DESCRIPTION'),
			'SORT' => 10,
			"INPUT" => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					'L' => 'L',
					'M' => 'M',
					'Q' => 'Q',
					'H' => 'H'
				),
				'VALUE' => 'M'
			),
			'GROUP' => Loc::getMessage('R52_QRCODE_MAIN_SETTINGS_GROUP'),
		),
		'SIZE' => array(
			'NAME' => Loc::getMessage('R52_QRCODE_SIZE_NAME'),
			'DESCRIPTION' => Loc::getMessage('R52_QRCODE_SIZE_DESCRIPTION'),
			'SORT' => 20,
			'GROUP' => Loc::getMessage('R52_QRCODE_MAIN_SETTINGS_GROUP'),
			"INPUT" => array(
				'TYPE' => 'STRING',
				'VALUE' => '3'
			)
		),
		'MARGIN' => array(
			'NAME' => Loc::getMessage('R52_QRCODE_MARGIN_NAME'),
			'DESCRIPTION' => Loc::getMessage('R52_QRCODE_MARGIN_DESCRIPTION'),
			'SORT' => 30,
			'GROUP' => Loc::getMessage('R52_QRCODE_MAIN_SETTINGS_GROUP'),
			"INPUT" => array(
				'TYPE' => 'STRING',
				'VALUE' => '3'
			)
		),
		// Настройки
		'ENCODING' => array(
			'NAME' => Loc::getMessage('R52_QRCODE_ENCODING_NAME'),
			'DESCRIPTION' => Loc::getMessage('R52_QRCODE_ENCODING_DESCRIPTION'),
			'SORT' => 100,
			'GROUP' => Loc::getMessage('R52_QRCODE_MAIN_SETTINGS_GROUP'),
			"INPUT" => array(
				'TYPE' => 'ENUM',
				'OPTIONS' => array(
					'1' => 'WIN1251',
					'2' => 'UTF8',
					'3' => 'KOI8-R'
				),
				'VALUE' => '2'
			)
		),
        'SAMPLE' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_SAMPLE_NAME'),
            'DESCRIPTION' => Loc::getMessage('R52_QRCODE_SAMPLE_DESCRIPTION'),
            'SORT' => 100,
            'GROUP' => Loc::getMessage('R52_QRCODE_MAIN_SETTINGS_GROUP'),
            "INPUT" => array(
                'TYPE' => 'STRING',
            ),
            'DEFAULT' => Loc::getMessage('R52_QRCODE_SAMPLE_DEFAULT'),
        ),
        'SHOW_TABLE' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_SHOW_TABLE_NAME'),
            'DESCRIPTION' => Loc::getMessage('R52_QRCODE_SHOW_TABLE_DESCRIPTION'),
            'SORT' => 100,
            'GROUP' => Loc::getMessage('R52_QRCODE_MAIN_SETTINGS_GROUP'),
            "INPUT" => array(
                'TYPE' => 'ENUM',
                'OPTIONS' => array(
                    '1' => Loc::getMessage('R52_QRCODE_SHOW_TABLE_DESCRIPTION_Y'),
                    '2' => Loc::getMessage('R52_QRCODE_SHOW_TABLE_DESCRIPTION_N'),
                ),
                'VALUE' => '2'
            )
        ),

        'SHOW_QR_EVENT' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_SHOW_QR_EVENT_NAME'),
            'DESCRIPTION' => Loc::getMessage('R52_QRCODE_SHOW_QR_EVENT_DESCRIPTION'),
            'SORT' => 100,
            'GROUP' => Loc::getMessage('R52_QRCODE_MAIN_SETTINGS_GROUP'),
            "INPUT" => array(
                'TYPE' => 'ENUM',
                'OPTIONS' => array(
                    '1' => Loc::getMessage('R52_QRCODE_SHOW_QR_EVENT_DESCRIPTION_Y'),
                    '2' => Loc::getMessage('R52_QRCODE_SHOW_QR_EVENT_DESCRIPTION_N'),
                ),
                'VALUE' => '2'
            )
        ),
        // Обязательные реквизиты
        'Name' => array(
			'NAME' => Loc::getMessage('R52_QRCODE_Name_NAME'),
			'DESCRIPTION' => Loc::getMessage('R52_QRCODE_Name_DESCRIPTION'),
			'SORT' => 100,
			'GROUP' => Loc::getMessage('R52_QRCODE_MAIN_REQUISITES_GROUP'),
		),
        'PersonalAcc' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_PersonalAcc_NAME'),
            'DESCRIPTION' => Loc::getMessage('R52_QRCODE_PersonalAcc_DESCRIPTION'),
            'SORT' => 110,
            'GROUP' => Loc::getMessage('R52_QRCODE_MAIN_REQUISITES_GROUP'),
        ),
        'BankName' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_BankName_NAME'),
            'DESCRIPTION' => Loc::getMessage('R52_QRCODE_BankName_DESCRIPTION'),
            'SORT' => 120,
            'GROUP' => Loc::getMessage('R52_QRCODE_MAIN_REQUISITES_GROUP'),
        ),
        'BIC' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_BIC_NAME'),
            'DESCRIPTION' => Loc::getMessage('R52_QRCODE_BIC_DESCRIPTION'),
            'SORT' => 130,
            'GROUP' => Loc::getMessage('R52_QRCODE_MAIN_REQUISITES_GROUP'),
        ),
        'CorrespAcc' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_CorrespAcc_NAME'),
            'DESCRIPTION' => Loc::getMessage('R52_QRCODE_CorrespAcc_DESCRIPTION'),
            'SORT' => 140,
            'GROUP' => Loc::getMessage('R52_QRCODE_MAIN_REQUISITES_GROUP'),
        ),
        //Информация по заказу
        'Sum' => array(
           	"NAME" => Loc::getMessage('R52_QRCODE_Sum_NAME'),
           	'SORT' => 200,
           	'GROUP' => Loc::getMessage('R52_QRCODE_SALE_INFO_GROUP'),
            'DESCRIPTION' => Loc::getMessage('R52_QRCODE_Sum_DESCRIPTION'),
           	'DEFAULT' => array(
           		'PROVIDER_KEY' => 'PAYMENT',
           		'PROVIDER_VALUE' => 'SUM'
           	)
        ),
        'OrderNum' => array(
            "NAME" => Loc::getMessage('R52_QRCODE_OrderNum_NAME'),
            'SORT' => 300,
            'GROUP' => Loc::getMessage('R52_QRCODE_SALE_INFO_GROUP'),
            'DEFAULT' => array(
                'PROVIDER_KEY' => 'ORDER',
                'PROVIDER_VALUE' => 'ACCOUNT_NUMBER'
            )
        ),
        //Дополнительные реквизиты
        'Purpose' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_Purpose_NAME'),
            'DESCRIPTION' => Loc::getMessage('R52_QRCODE_Purpose_DESCRIPTION'),
            'SORT' => 300,
            'GROUP' => Loc::getMessage('R52_QRCODE_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'PayeeINN' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_PayeeINN_NAME'),
            'DESCRIPTION' => Loc::getMessage('R52_QRCODE_PayeeINN_DESCRIPTION'),
            'SORT' => 300,
            'GROUP' => Loc::getMessage('R52_QRCODE_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'PayerINN' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_PayerINN_NAME'),
            'DESCRIPTION' => Loc::getMessage('R52_QRCODE_PayerINN_DESCRIPTION'),
            'SORT' => 310,
            'GROUP' => Loc::getMessage('R52_QRCODE_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'DrawerStatus' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_DrawerStatus_NAME'),
            'DESCRIPTION' => Loc::getMessage('R52_QRCODE_DrawerStatus_DESCRIPTION'),
            'SORT' => 320,
            'GROUP' => Loc::getMessage('R52_QRCODE_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'KPP' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_KPP_NAME'),
            'DESCRIPTION' => Loc::getMessage('R52_QRCODE_KPP_DESCRIPTION'),
            'SORT' => 330,
            'GROUP' => Loc::getMessage('R52_QRCODE_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'CBC' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_СВС_NAME'),
            'DESCRIPTION' => Loc::getMessage('R52_QRCODE_СВС_DESCRIPTION'),
            'SORT' => 340,
            'GROUP' => Loc::getMessage('R52_QRCODE_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'OKTMO' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_ОКТМО_NAME'),
            'DESCRIPTION' => Loc::getMessage('R52_QRCODE_ОКТМО_DESCRIPTION'),
            'SORT' => 350,
            'GROUP' => Loc::getMessage('R52_QRCODE_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'PaytReason' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_PaytReason_NAME'),
            'DESCRIPTION' => Loc::getMessage('R52_QRCODE_PaytReason_DESCRIPTION'),
            'SORT' => 350,
            'GROUP' => Loc::getMessage('R52_QRCODE_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'TaxPeriod' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_TaxPeriod_NAME'),
            'DESCRIPTION' => Loc::getMessage('R52_QRCODE_TaxPeriod_DESCRIPTION'),
            'SORT' => 350,
            'GROUP' => Loc::getMessage('R52_QRCODE_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'DocNo' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_DocNo_NAME'),
            'DESCRIPTION' => Loc::getMessage('R52_QRCODE_DocNo_DESCRIPTION'),
            'SORT' => 350,
            'GROUP' => Loc::getMessage('R52_QRCODE_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'DocDate' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_DocDate_NAME'),
            'DESCRIPTION' => Loc::getMessage('R52_QRCODE_DocDate_DESCRIPTION'),
            'SORT' => 350,
            'GROUP' => Loc::getMessage('R52_QRCODE_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'TaxPaytKind' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_TaxPaytKind_NAME'),
            'DESCRIPTION' => Loc::getMessage('R52_QRCODE_TaxPaytKind_DESCRIPTION'),
            'SORT' => 350,
            'GROUP' => Loc::getMessage('R52_QRCODE_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'SELLER_ADDRESS' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_SELLER_ADDRESS_NAME'),
            'DESCRIPTION' => Loc::getMessage('R52_QRCODE_SELLER_ADDRESS_DESCRIPTION'),
            'SORT' => 350,
            'GROUP' => Loc::getMessage('R52_QRCODE_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'SELLER_PHONE' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_SELLER_PHONE_NAME'),
            'DESCRIPTION' => Loc::getMessage('R52_QRCODE_SELLER_PHONE_DESCRIPTION'),
            'SORT' => 350,
            'GROUP' => Loc::getMessage('R52_QRCODE_ADDITIONAL_REQUISITES_GROUP'),
        ),
        // Прочие дополнительные реквизиты
        'LastName' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_LastName_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'FirstName' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_FirstName_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'MiddleName' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_MiddleName_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'PayerAddress' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_PayerAddress_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'PersonalAccount' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_PersonalAccount_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'Docldx' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_Docldx_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'PensAcc' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_PensAcc_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'PersAcc' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_PersAcc_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'Flat' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_Flat_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'Phone' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_Phone_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'PayerldType' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_PayerldType_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'PayerldNum' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_PayerldNum_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'ChildFio' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_ChildFio_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'BirthDate' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_BirthDate_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'PaymTerm' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_PaymTerm_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'PaymPeriod' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_PaymPeriod_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'Category' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_Category_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'ServiceName' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_ServiceName_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'Counterld' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_Counterld_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'CounterVal' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_CounterVal_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'Quittld' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_Quittld_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'QuittDate' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_QuittDate_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'InstNum' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_InstNum_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'ClassNum' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_ClassNum_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'SpecFio' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_SpecFio_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'AddAmount' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_AddAmount_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'Ruleld' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_Ruleld_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'Execld' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_Execld_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'RegType' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_RegType_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'UIN' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_UIN_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
        'TechCode' => array(
            'NAME' => Loc::getMessage('R52_QRCODE_TechCode_NAME'),
            'SORT' => 400,
            'GROUP' => Loc::getMessage('R52_QRCODE_OTHER_ADDITIONAL_REQUISITES_GROUP'),
        ),
	)
);
