<?
IncludeModuleLangFile(__FILE__);
Class rbs_moyskladstocks extends CModule
{
	const MODULE_ID = 'rbs.moyskladstocks';
	var $MODULE_ID = 'rbs.moyskladstocks'; 
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
		$this->MODULE_NAME = GetMessage("rbs.moyskladstocks_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("rbs.moyskladstocks_MODULE_DESC");

		$this->PARTNER_NAME = GetMessage("rbs.moyskladstocks_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("rbs.moyskladstocks_PARTNER_URI");
	}

	function InstallDB($arParams = array())
	{
		return true;
	}

	function UnInstallDB($arParams = array())
	{
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
		if(!is_dir($_SERVER["DOCUMENT_ROOT"]."/mshooks")){
			mkdir($_SERVER["DOCUMENT_ROOT"]."/mshooks");
		}

		CopyDirFiles(
			__DIR__ . "/public/mshooks",
            $_SERVER["DOCUMENT_ROOT"]."/mshooks",
            true,
            true
		);
		
		return true;
	}

	function UnInstallFiles()
	{
		DeleteDirFiles(
			__DIR__ . "/public/mshooks",
            $_SERVER["DOCUMENT_ROOT"]."/mshooks"
		);
		
		return true;
	}

	function DoInstall()
	{
		global $APPLICATION;
		$this->InstallFiles();
		$this->InstallDB();
		RegisterModule(self::MODULE_ID);
	}

	function DoUninstall()
	{
		global $APPLICATION;
		UnRegisterModule(self::MODULE_ID);
		$this->UnInstallDB();
		$this->UnInstallFiles();
	}
}
?>
