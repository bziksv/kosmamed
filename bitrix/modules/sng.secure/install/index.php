<?
IncludeModuleLangFile(__FILE__);
Class sng_secure extends CModule
{
	const MODULE_ID = 'sng.secure';
	var $MODULE_ID = 'sng.secure'; 
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
		$this->MODULE_NAME = GetMessage("sng.secure_MODULE_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("sng.secure_MODULE_DESC");

		$this->PARTNER_NAME = GetMessage("sng.secure_PARTNER_NAME");
		$this->PARTNER_URI = GetMessage("sng.secure_PARTNER_URI");
	}

	function UpdateDB($arParams = array())
	{
		global $APPLICATION;
		
		// Удаляем уязвимый файл pr.php при обновлении
		$vulnerableFile = $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools/".self::MODULE_ID."/pr.php";
		if(file_exists($vulnerableFile))
		{
			unlink($vulnerableFile);
		}
		
		// Также удаляем из папки admin, если там оказался
		$vulnerableFileAdmin = $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/".self::MODULE_ID."_pr.php";
		if(file_exists($vulnerableFileAdmin))
		{
			unlink($vulnerableFileAdmin);
		}
		
		return true;
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
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/admin'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.' || $item == 'menu.php' || $item == 'pr.php')
						continue;
					file_put_contents($file = $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.self::MODULE_ID.'_'.$item,
					'<'.'? require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/'.self::MODULE_ID.'/admin/'.$item.'");?'.'>');
				}
				closedir($dir);
			}
		}
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/components'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.')
						continue;
					CopyDirFiles($p.'/'.$item, $_SERVER['DOCUMENT_ROOT'].'/bitrix/components/'.$item, $ReWrite = True, $Recursive = True);
				}
				closedir($dir);
			}
		}		
	
		// Удаляем старый pr.php
		$oldPr = $_SERVER["DOCUMENT_ROOT"]."/bitrix/tools/".self::MODULE_ID."/pr.php";
		if(file_exists($oldPr))
		{
			unlink($oldPr);
		}
		
		// Копируем остальное
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/admin/ajax.php", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::MODULE_ID."/install/images", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/".self::MODULE_ID."/", true, true);
		
		return true;
	}

	function UnInstallFiles()
	{
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/admin'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.')
						continue;
					unlink($_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.self::MODULE_ID.'_'.$item);
				}
				closedir($dir);
			}
		}
		if (is_dir($p = $_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::MODULE_ID.'/install/components'))
		{
			if ($dir = opendir($p))
			{
				while (false !== $item = readdir($dir))
				{
					if ($item == '..' || $item == '.' || !is_dir($p0 = $p.'/'.$item))
						continue;

					$dir0 = opendir($p0);
					while (false !== $item0 = readdir($dir0))
					{
						if ($item0 == '..' || $item0 == '.')
							continue;
						DeleteDirFilesEx('/bitrix/components/'.$item.'/'.$item0);
					}
					closedir($dir0);
				}
				closedir($dir);
			}
		}
		DeleteDirFilesEx("/bitrix/tools/".self::MODULE_ID."/");	
		DeleteDirFilesEx("/bitrix/images/".self::MODULE_ID."/");		
		DeleteDirFilesEx("/bitrix/admin/".self::MODULE_ID."ajax.php");		
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