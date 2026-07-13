<?
IncludeModuleLangFile(__FILE__);

if (class_exists('askaron_clientid'))
{
	return;
}

class       askaron_clientid extends CModule
{
	var $MODULE_ID = "askaron.clientid";
	public $MODULE_VERSION;
	public $MODULE_VERSION_DATE;
	public $MODULE_NAME;
	public $MODULE_DESCRIPTION;
	public $PARTNER_NAME;
	public $PARTNER_URI;
	public $MODULE_GROUP_RIGHTS = 'Y';
	// first modules '8.0.7', 2009-06-29
	// htmlspecialcharsbx was added in '11.5.9', 2012-09-13

	public $NEED_MAIN_VERSION = '14.0.0';
	public $NEED_MODULES = ["iblock"];

	public $MY_DIR = "bitrix";

	public function __construct()
	{
		$arModuleVersion = [];

		$path = str_replace('\\', '/', __FILE__);
		$dir = mb_substr($path, 0, mb_strlen($path) - mb_strlen('/index.php'));
		include($dir . '/version.php');
		Bitrix\Main\Loader::includeModule("sale");
		$check_last = "/local/modules/" . $this->MODULE_ID . "/install/index.php";
		$check_last_len = mb_strlen($check_last);

		if (mb_substr($path, -$check_last_len) == $check_last)
		{
			$this->MY_DIR = "local";
		}

		if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion))
		{
			$this->MODULE_VERSION = $arModuleVersion['VERSION'];
			$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		}

		// !Twice! Marketplace bug. 2013-03-13
		$this->PARTNER_NAME = "Askaron Systems";
		$this->PARTNER_NAME = GetMessage("ASKARON_CLIENTID_PARTNER_NAME");
		$this->PARTNER_URI = 'http://askaron.ru/';

		$this->MODULE_NAME = GetMessage('ASKARON_CLIENTID_MODULE_NAME');
		$this->MODULE_DESCRIPTION = GetMessage('ASKARON_CLIENTID_MODULE_DESCRIPTION');
	}

	public function DoInstall()
	{
		global $APPLICATION;
		global $DB;
		global $step;
		global $askaron_clientid_global_errors;

		$askaron_clientid_global_errors = [];

		if (is_array($this->NEED_MODULES) && !empty($this->NEED_MODULES))
		{
			foreach ($this->NEED_MODULES as $module)
				if (!IsModuleInstalled($module))
				{
					$askaron_clientid_global_errors[] = GetMessage('ASKARON_CLIENTID_NEED_MODULES', ['#MODULE#' => $module]);
				}
		}

		if (mb_strlen($this->NEED_MAIN_VERSION) > 0 && version_compare(SM_VERSION, $this->NEED_MAIN_VERSION) < 0)
		{
			$askaron_clientid_global_errors[] = GetMessage('ASKARON_CLIENTID_NEED_RIGHT_VER', ['#NEED#' => $this->NEED_MAIN_VERSION]);
		}

		if (mb_strtolower($DB->type) != 'mysql')
		{
			$askaron_clientid_global_errors[] = GetMessage('ASKARON_CLIENTID_ONLY_MYSQL_ERROR');
		}

		if (count($askaron_clientid_global_errors) == 0)
		{
			if ($this->InstallDB())
			{
				$this->InstallFiles();
				$dbPayerType = \CSalePersonType::GetList(Array("SORT" => "ASC"), Array());
				while ($payerType = $dbPayerType->Fetch())
				{
					self::createOrderPropsForPayerType($payerType['ID']);
				}
				$this->createUserProps();
				\CAgent::AddAgent("\\Askaron\\ClientId\\QueueManager::sendQueue();", $this->MODULE_ID, "N", 60 * 60, "", "Y");
				RegisterModule("askaron.clientid");

				$eventManager = \Bitrix\Main\EventManager::getInstance();
				$eventManager->registerEventHandler("sale", "OnSaleOrderBeforeSaved", $this->MODULE_ID, "\\Askaron\\ClientId\\Handler\\MyOrder", "OnSaleOrderBeforeSaved");
				$eventManager->registerEventHandler("main", "OnAfterUserLogin", $this->MODULE_ID, "\\Askaron\\ClientId\\Handler\\MyAuth", "OnAfterUserLoginHandler");
				$eventManager->registerEventHandler("main", "OnEndBufferContent", $this->MODULE_ID, "\\Askaron\\ClientId\\Handler\\MyBuffer", "OnEndBufferContentHandler");

			}
			else
			{
				$askaron_clientid_global_errors[] = GetMessage('ASKARON_CLIENTID_INSTALL_TABLE_ERROR');
			};
		}

		$APPLICATION->IncludeAdminFile(GetMessage("ASKARON_CLIENTID_INSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/" . $this->MY_DIR . "/modules/" . $this->MODULE_ID . "/install/step.php");
		return true;

	}

	public function DoUninstall()
	{
		global $APPLICATION, $step;
		$RIGHT = $APPLICATION->GetGroupRight($this->MODULE_ID);
		if ($RIGHT >= "W")
		{
			$step = IntVal($step);
			if ($step < 2)
			{
				$APPLICATION->IncludeAdminFile(GetMessage("ASKARON_CLIENTID_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/" . $this->MY_DIR . "/modules/" . $this->MODULE_ID . "/install/unstep1.php");
			}
			else if ($step == 2)
			{
				// Tables
				if ($_REQUEST["savedata"] != "Y")
				{
					$this->UnInstallDB();
					$this->deleteProperties();
					$this->deleteUserProps();
				}

				$this->UnInstallFiles();

				UnRegisterModule('askaron.clientid');
				$eventManager = \Bitrix\Main\EventManager::getInstance();
				$eventManager->UnRegisterEventHandler("sale", "OnSaleOrderBeforeSaved", $this->MODULE_ID, "\\Askaron\\ClientId\\Handler\\MyOrder", "OnSaleOrderBeforeSaved");
				$eventManager->UnRegisterEventHandler("main", "OnAfterUserLogin", $this->MODULE_ID, "\\Askaron\\ClientId\\Handler\\MyAuth", "OnAfterUserLoginHandler");
				$eventManager->UnRegisterEventHandler("main", "OnEndBufferContent", $this->MODULE_ID, "\\Askaron\\ClientId\\Handler\\MyBuffer", "OnEndBufferContentHandler");
				\CAgent::RemoveModuleAgents("$this->MODULE_ID");
				$APPLICATION->IncludeAdminFile(GetMessage("ASKARON_CLIENTID_UNINSTALL_TITLE"), $_SERVER["DOCUMENT_ROOT"] . "/" . $this->MY_DIR . "/modules/" . $this->MODULE_ID . "/install/unstep2.php");
				return true;
			}
		}
	}

	function InstallFiles($arParams = [])
	{
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/" . $this->MY_DIR . "/modules/" . $this->MODULE_ID . "/install/admin/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin/");
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/" . $this->MY_DIR . "/modules/" . $this->MODULE_ID . "/install/themes/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes/", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/" . $this->MY_DIR . "/modules/" . $this->MODULE_ID . "/install/components/askaron/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/components/askaron/", true, true);//component

		if (!file_exists($_SERVER["DOCUMENT_ROOT"] . '/upload/tmp/conversion_csv'))
		{
			mkdir($_SERVER["DOCUMENT_ROOT"] . '/upload/tmp/conversion_csv', 0755, true);
		}
		// Included code samples
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"] . "/" . $this->MY_DIR . "/modules/" . $this->MODULE_ID . "/lang/ru/install/samples/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/php_interface/include/askaron.include/samples/", true, true);
	}
	function rmrf($dir)
	{
		foreach (glob($dir) as $file) {
			if (is_dir($file)) {
				$this->rmrf("{$file}/*");
				rmdir($file);
			} else {
				unlink($file);
			}
		}
	}
	function UnInstallFiles($arParams = [])
	{
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"] . "/" . $this->MY_DIR . "/modules/" . $this->MODULE_ID . "/install/admin/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin");
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"] . "/" . $this->MY_DIR . "/modules/" . $this->MODULE_ID . "/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/themes/.default");//css
		DeleteDirFilesEx("/bitrix/themes/.default/icons/" . $this->MODULE_ID . "/");//icons

		DeleteDirFilesEx("/bitrix/components/askaron/askaron.clientid.check/");

		if (file_exists($_SERVER["DOCUMENT_ROOT"] . '/upload/tmp/conversion_csv'))
		{
			$this->rmrf($_SERVER["DOCUMENT_ROOT"] . '/upload/tmp/conversion_csv');
		}
		// Included code samples
		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"] . "/" . $this->MY_DIR . "/modules/" . $this->MODULE_ID . "/lang/ru/install/samples/", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/php_interface/include/askaron.include/samples");
	}

	function InstallDB()
	{
		$result = true;

		global $APPLICATION, $DB;

		if (!$DB->Query("SELECT 'x' FROM b_askaron_clientid_session", true))
		{
			$EMPTY = "Y";
		}
		else
		{
			$EMPTY = "N";
		}

		$errors = false;

		if ($EMPTY == "Y")
		{
			$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"] . "/" . $this->MY_DIR . "/modules/" . $this->MODULE_ID . "/install/db/" . mb_strtolower($DB->type) . "/install.sql");
		}

		if (!empty($errors))
		{
			$APPLICATION->ThrowException(implode("", $errors));
			$result = false;
		}

		return $result;
	}

	function UnInstallDB()
	{
		global $APPLICATION, $DB;

		$errors = $DB->RunSQLBatch($_SERVER["DOCUMENT_ROOT"] . "/" . $this->MY_DIR . "/modules/" . $this->MODULE_ID . "/install/db/" . mb_strtolower($DB->type) . "/uninstall.sql");

		if (!empty($errors))
		{
			$APPLICATION->ThrowException(implode("", $errors));
			return false;
		}

		return true;
	}

	function deleteProperties()
	{
		$dbRes = \CSaleOrderProps::GetList([], ["@CODE" => ['YM_CODE', 'GA_CODE', 'SHORT_CODE']], false, false, ['CODE', 'ID']);
		while ($propId=$dbRes->Fetch()['ID'])
		{
			if ((int)$propId > 0)
			{
				\CSaleOrderProps::Delete($propId);
			}
		}

	}

	public static function createOrderPropsForPayerType($payerType)
	{
		$ymPropId = 0;
		$gaPropIds = 0;
		$shortCodePropId = 0;
		$gaPropExist = false;
		$ymPropExist = false;
		$shortPropExist = false;
		$dbRes = \CSaleOrderProps::GetList([], ["@CODE" => ['YM_CODE', 'GA_CODE', 'SHORT_CODE'], 'PERSON_TYPE_ID' => $payerType], false, false, ['CODE', 'ID']);

		while ($prop = $dbRes->Fetch())
		{
			switch ($prop['CODE'])
			{
				case 'YM_CODE':
					$ymPropExist = true;
					$ymPropId = $prop['ID'];
					break;
				case 'GA_CODE':
					$gaPropExist = true;
					$gaPropId = $prop['ID'];
					break;
				case 'SHORT_CODE':
					$shortPropExist = true;
					$shortCodePropId = $prop['ID'];
					break;
			}
		}
		if ((int)$payerType > 0)
		{

			$arFields = [
				"PERSON_TYPE_ID" => $payerType,
				"NAME" => GetMessage("ASKARON_CLIENTID_SHORTCODE_PROP_NAME"),
				"CODE" => "SHORT_CODE",
				"UTIL" => 'Y',
				"TYPE" => "STRING",
				"PROPS_GROUP_ID" => 1,
				"REQUIED" => "N",
				"DEFAULT_VALUE" => "",
				"SORT" => 100,
				"USER_PROPS" => "N",
				"IS_LOCATION" => "N",
				"IS_LOCATION4TAX" => "N",
				"SIZE1" => 0,
				"SIZE2" => 0,
				"DESCRIPTION" => "",
				"IS_EMAIL" => "N",
				"IS_PROFILE_NAME" => "N",
				"IS_PAYER" => "N",
			];
			if (!$shortPropExist)
			{
				$ID = \CSaleOrderProps::Add($arFields);
				if ($ID)
				{
					$shortCodePropIds = $ID;
				}
			}
			$arFields['NAME'] = GetMessage("ASKARON_CLIENTID_YMCODE_PROP_NAME");
			$arFields['CODE'] = 'YM_CODE';
			if (!$ymPropExist)
			{
				$ID = \CSaleOrderProps::Add($arFields);
				if ($ID)
				{
					$ymPropIds = $ID;
				}
			}
			$arFields['NAME'] = GetMessage("ASKARON_CLIENTID_GACODE_PROP_NAME");
			$arFields['CODE'] = 'GA_CODE';
			if (!$gaPropExist)
			{
				$ID = \CSaleOrderProps::Add($arFields);
				if ($ID)
				{
					$gaPropIds = $ID;
				}
			}
		}
		return ['YM_PROPS' => $ymPropId, 'GA_PROPS' => $gaPropId, 'SC_PROPS' => $shortCodePropId];
	}


	function createUserProps()
	{
		$isYmPropExist = false;
		$isGaPropExist = false;
		$oUserTypeEntity = new CUserTypeEntity();
		$uProps = ['isYmPropExist'=>'UF_USER_ASKARON_CLIENT_CODE_YM', 'isGaPropExist'=>'UF_USER_ASKARON_CLIENT_CODE_GA'];
		foreach ($uProps as $existVar => $propName)
		{
			$rsData = $oUserTypeEntity::getList(
				[],
				[
					'FIELD_NAME' => $propName,
				]

			);
			if ($res = $rsData->Fetch())
			{
				$$existVar=true;
			}
		}
		$aUserFields = [
			'ENTITY_ID' => 'USER',
			'FIELD_NAME' => 'UF_USER_ASKARON_CLIENT_CODE_YM',
			'USER_TYPE_ID' => 'string',
			'XML_ID' => 'XML_UF_USER_ASKARON_CLIENT_CODE_YM',
			'SORT' => 500,
			'MULTIPLE' => 'Y',
			'MANDATORY' => 'N',
			'SHOW_FILTER' => 'S',
			'SHOW_IN_LIST' => 'Y',
			'EDIT_IN_LIST' => '',
			'IS_SEARCHABLE' => 'N',
			'SETTINGS' => [
				'DEFAULT_VALUE' => '',
				'SIZE' => '20',
				'ROWS' => '1',
				'MIN_LENGTH' => '0',
				'MAX_LENGTH' => '0',
				'REGEXP' => '',
			],
			'EDIT_FORM_LABEL' => [
				'ru' => GetMessage('ASKARON_CLIENTID_YMCODE_USERPROP_NAME'),
				'en' => 'Yandex ClientID codes',
			],
			'LIST_COLUMN_LABEL' => [
				'ru' => GetMessage('ASKARON_CLIENTID_YMCODE_USERPROP_NAME'),
				'en' => 'Yandex ClientID codes',
			],
			'LIST_FILTER_LABEL' => [
				'ru' => GetMessage('ASKARON_CLIENTID_YMCODE_USERPROP_NAME'),
				'en' => 'Yandex ClientID codes',
			],
			'ERROR_MESSAGE' => [
				'ru' => GetMessage('ASKARON_CLIENTID_YMCODE_EDIT_ERROR'),
				'en' => 'An error in completing the Yandex ClientID codes',
			],
		];
		if (!$isYmPropExist){
			$iUserFieldId = $oUserTypeEntity->Add($aUserFields);
		}

		$aUserFields['FIELD_NAME'] = 'UF_USER_ASKARON_CLIENT_CODE_GA';
		$aUserFields['XML_ID'] = 'XML_UF_USER_ASKARON_CLIENT_CODE_GA';
		$aUserFields['EDIT_FORM_LABEL'] = [
			'ru' => GetMessage('ASKARON_CLIENTID_GACODE_USERPROP_NAME'),
			'en' => 'Google Analytics ClientID codes',
		];
		$aUserFields['LIST_COLUMN_LABEL'] = [
			'ru' => GetMessage('ASKARON_CLIENTID_GACODE_USERPROP_NAME'),
			'en' => 'Google Analytics ClientID codes',
		];
		$aUserFields['LIST_FILTER_LABEL'] = [
			'ru' => GetMessage('ASKARON_CLIENTID_GACODE_USERPROP_NAME'),
			'en' => 'Google Analytics ClientID codes',
		];
		$aUserFields['ERROR_MESSAGE'] = [
			'ru' => GetMessage('ASKARON_CLIENTID_GACODE_EDIT_ERROR'),
			'en' => 'An error in completing the Google Analytics ClientID codes',
		];
		if (!$isGaPropExist)
		{
			$iUserFieldId = $oUserTypeEntity->Add($aUserFields);
		}
	}

	function deleteUserProps()
	{
		$uProps = ['UF_USER_ASKARON_CLIENT_CODE_YM', 'UF_USER_ASKARON_CLIENT_CODE_GA'];
		$oUserTypeEntity = new CUserTypeEntity();
		if (!empty($uProps))
		{
			foreach ($uProps as $propName)
			{
				$rsData = $oUserTypeEntity::getList(
					[],
					[
						'FIELD_NAME' => $propName,
					]
				);
				if ($res = $rsData->Fetch())
				{
					$oUserTypeEntity->Delete($res['ID']);
				}
			}
		}


	}
}

?>