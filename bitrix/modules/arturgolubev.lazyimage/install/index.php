<?
IncludeModuleLangFile(__FILE__);
include_once $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/arturgolubev.lazyimage/lib/installation.php';

Class arturgolubev_lazyimage extends CModule
{
	const MODULE_ID = 'arturgolubev.lazyimage';
	var $MODULE_ID = 'arturgolubev.lazyimage'; 
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $strError = '';

	function __construct()
	{
		$arModuleVersion = array();
		include(dirname(__FILE__)."/version.php");
		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
		$this->MODULE_NAME = GetMessage("arturgolubev.lazyimage_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("arturgolubev.lazyimage_MODULE_DESC");

		$this->PARTNER_NAME = GetMessage("arturgolubev.lazyimage_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("arturgolubev.lazyimage_PARTNER_URI");
	}

	function InstallDB($arParams = array())
	{
		RegisterModuleDependences('main', 'OnEpilog', self::MODULE_ID, 'CArturgolubevLazyimage', 'onEpilog');
		RegisterModuleDependences('main', 'OnEndBufferContent', self::MODULE_ID, 'CArturgolubevLazyimage', 'onBufferContent');
		
		return true;
	}

	function UnInstallDB($arParams = array())
	{
		UnRegisterModuleDependences('main', 'OnEpilog', self::MODULE_ID, 'CArturgolubevLazyimage', 'onEpilog');
		UnRegisterModuleDependences('main', 'OnEndBufferContent', self::MODULE_ID, 'CArturgolubevLazyimage', 'onBufferContent');
		
		return true;
	}

	function InstallEvents()
	{
		return true;
	}

	function UnInstallEvents()
	{
		return true;
	}

	function InstallFiles($arParams = array())
	{
		$mPath = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID;
		
		CopyDirFiles($mPath."/install/js", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js",true,true);
		CopyDirFiles($mPath."/install/css", $_SERVER["DOCUMENT_ROOT"]."/bitrix/css",true,true);
		CopyDirFiles($mPath."/install/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images",true,true);
		CopyDirFiles($mPath."/install/tools", $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools",true,true);
		CopyDirFiles($mPath."/install/themes", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
		
		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFilesEx("/bitrix/js/".self::MODULE_ID);
		DeleteDirFilesEx("/bitrix/css/".self::MODULE_ID);
		DeleteDirFilesEx("/bitrix/images/".self::MODULE_ID);
		DeleteDirFilesEx("/bitrix/tools/".self::MODULE_ID);

		DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/themes/.default/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default");
		DeleteDirFilesEx("/bitrix/themes/.default/icons/".self::MODULE_ID."/");
		
		return true;
	}

	function DoInstall()
	{
		$this->InstallFiles();
		$this->InstallDB();
		RegisterModule(self::MODULE_ID);
		
		if (class_exists('agInstaHelperLazyimage'))
		{
			agInstaHelperLazyimage::IncludeAdminFile(GetMessage("MOD_INST_OK"), "/bitrix/modules/".self::MODULE_ID."/install/success_install.php");
		}
	}

	function DoUninstall()
	{
		UnRegisterModule(self::MODULE_ID);
		$this->UnInstallDB();
		$this->UnInstallFiles();
	}
}
?>
