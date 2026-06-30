<?php
#################################################
#        Company developer: IPOL
#        Developer: Nikta Egorov
#        Site: http://www.ipol.com
#        E-mail: om-sv2@mail.ru
#        Copyright (c) 2006-2012 IPOL
#################################################
?>
<?php
IncludeModuleLangFile(__FILE__); 

if(class_exists("ipol_sdek")) 
    return;
	
Class ipol_sdek extends CModule{
    var $MODULE_ID = "ipol.sdek";
    var $MODULE_NAME;
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = "N";
        var $errors;

	function __construct(){
		$arModuleVersion = array();

		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		$this->MODULE_VERSION = $arModuleVersion["VERSION"];
		$this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];

		$this->MODULE_NAME = GetMessage("IPOLSDEK_INSTALL_NAME");
		$this->MODULE_DESCRIPTION = GetMessage("IPOLSDEK_INSTALL_DESCRIPTION");
        
        $this->PARTNER_NAME = "Ipol";
        $this->PARTNER_URI = "http://www.ipolh.com";
	}

    /**
     * Returns mapping 'DB table name' <> 'file suffix'
     * @return string[]
     */
	protected function getDB()
    {
		return array(
            'ipol_sdek'               => 'Orders',
            'ipol_sdekcities'         => 'Cities',
            'ipol_sdeklogs'           => 'Auth',
            'ipol_sdek_courier_calls' => 'CourierCalls',
            'ipol_sdek_stores'        => 'Stores',
        );
	}
	
	function InstallDB(){
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		$arDB = $this->getDB();

		foreach($arDB as $name => $path)
			if(!$DB->Query("SELECT 'x' FROM ".$name, true)){
				$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$this->MODULE_ID."/install/db/mysql/install".$path.".sql");
				if($this->errors !== false){
					$APPLICATION->ThrowException(implode("", $this->errors));
					return false;
				}
			}

		return true;
	}

	function UnInstallDB($preserveOrders = false){
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;

		$arDB = $this->getDB();

		foreach($arDB as $name => $path){
			if($name != 'ipol_sdek' || !$preserveOrders){
				$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$this->MODULE_ID."/install/db/mysql/unInstall".$path.".sql");
				if(!empty($this->errors)){
					$APPLICATION->ThrowException(implode("", $this->errors));
					return false;
				}
			}
		}

		return true;
	}

	function InstallEvents(){
		//all events sets in /classes/general/sdekhelper.php ������� auth
		return true;
	}
	function UnInstallEvents() {
		UnRegisterModuleDependences("main", "OnEpilog", $this->MODULE_ID, "sdekdriver", "onEpilog");
		UnRegisterModuleDependences("main", "OnEndBufferContent", $this->MODULE_ID, "CDeliverySDEK", "onBufferContent");
		UnRegisterModuleDependences("sale", "OnSaleComponentOrderOneStepDelivery", $this->MODULE_ID, "CDeliverySDEK", "pickupLoader");
		UnRegisterModuleDependences("sale", "OnSaleComponentOrderOneStepProcess", $this->MODULE_ID, "CDeliverySDEK", "loadComponent");
		UnRegisterModuleDependences("main", "OnAdminListDisplay", $this->MODULE_ID, "sdekdriver", "displayActPrint"); // ������
		UnRegisterModuleDependences("main", "OnBeforeProlog", $this->MODULE_ID, "sdekdriver", "OnBeforePrologHandler");
		UnRegisterModuleDependences("sale", "OnSaleComponentOrderOneStepComplete", $this->MODULE_ID, "sdekdriver", "orderCreate"); // �������� ������
		UnRegisterModuleDependences("sale", "OnSaleComponentOrderOneStepPaySystem", $this->MODULE_ID, "CDeliverySDEK", "checkNalD2P");
		UnRegisterModuleDependences("sale", "OnSaleComponentOrderOneStepDelivery", $this->MODULE_ID, "CDeliverySDEK", "checkNalP2D");
		return true;
	}

	function InstallFiles(){
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/images/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/".$this->MODULE_ID, true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/js/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".$this->MODULE_ID, true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin", true, true);
        CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/themes/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes", true, true);
		CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/components/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/", true, true);
		return true;
	}

	function UnInstallFiles(){
		DeleteDirFilesEx("/bitrix/js/".$this->MODULE_ID);
		if(file_exists($_SERVER['DOCUMENT_ROOT'].'/bitrix/tools/'.$this->MODULE_ID))
			DeleteDirFilesEx("/bitrix/tools/".$this->MODULE_ID);
		DeleteDirFilesEx("/bitrix/images/".$this->MODULE_ID);

        if (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/admin/")) {
            $adminFiles = scandir($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/admin/");
            foreach ($adminFiles as $file) {
                if (strlen($file) > 2 && strpos($file, '.')) {
                    unlink($_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/".$file);
                }
            }
        }

        if (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/themes/")) {
            $adminFiles = scandir($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/themes/.default/");
            foreach ($adminFiles as $file) {
                if (strlen($file) > 2 && strpos($file, '.')) {
                    unlink($_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/.default/".$file);
                }
            }
        }

		DeleteDirFilesEx("/bitrix/php_interface/include/sale_delivery/delivery_sdek.php");
		DeleteDirFilesEx("/bitrix/components/ipol/ipol.sdekPickup");
		DeleteDirFilesEx("/upload/".$this->MODULE_ID);
		$arrayOfFiles=scandir($_SERVER['DOCUMENT_ROOT'].'/bitrix/components/ipol');
		$flagForDelete=true;
		foreach($arrayOfFiles as $element){
			if(strlen($element)>2)
				$flagForDelete=false;
		}
		if($flagForDelete)
			DeleteDirFilesEx("/bitrix/components/ipol");
		return true;
	}
	
    function DoInstall(){
        global $DB, $APPLICATION, $step;
		$this->errors = false;

        if(!function_exists('curl_init'))
        {
            $GLOBALS['IPOL_SDEK_LBL_INSTALL_ERROR'] = GetMessage('IPOLSDEK_NOCURL');
        }elseif(!cmodule::includeModule('sale')){
            $GLOBALS['IPOL_SDEK_LBL_INSTALL_ERROR'] = GetMessage('IPOLSDEK_NOSALE');
        }

        if($GLOBALS['IPOL_SDEK_LBL_INSTALL_ERROR'])
        {
            $GLOBALS['APPLICATION']->IncludeAdminFile(GetMessage('IPOL_SDEK_INSTALL_ERROR_TITLE'), __DIR__ .'/error.php');

            return;
        }
		
		$this->InstallDB();
		$this->InstallEvents();
		$this->InstallFiles();
		
		RegisterModule($this->MODULE_ID);
		
        $APPLICATION->IncludeAdminFile(GetMessage("IPOLSDEK_INSTALL"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/step1.php");
    }

    function DoUninstall(){
        global $DB, $APPLICATION, $step;
		$this->errors = false;
		
		if($_REQUEST['step'] < 2){
			$this->ShowDataSaveForm();
		}elseif($_REQUEST['step'] == 2){
			COption::SetOptionString($this->MODULE_ID,'logged',false);
			 
			$this->UnInstallDB($_REQUEST['savedata']);
			$this->UnInstallFiles();
			$this->UnInstallEvents();
			
			CAgent::RemoveModuleAgents('ipol.sdek');
			
			UnRegisterModule($this->MODULE_ID);
			$APPLICATION->IncludeAdminFile(GetMessage("IPOLSDEK_DEL"), $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/unstep1.php");
		}
    }
	
	private function ShowDataSaveForm() {
		$keys = array_keys($GLOBALS);
		for ($i = 0; $i < count($keys); $i++) {
			if ($keys[$i] != 'i' && $keys[$i] != 'GLOBALS' && $keys[$i] != 'strTitle' && $keys[$i] != 'filepath') {
				global ${$keys[$i]};
			}
		}

		$APPLICATION->SetTitle(GetMessage('IPOLSDEK_DEL'));
		include($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php');
		?>
		<form action="<?= $APPLICATION->GetCurPage() ?>" method="get">
			<?= bitrix_sessid_post();?>
			<input type="hidden" name="lang" value="<?= LANG ?>" />
			<input type="hidden" name="id" value="<?= $this->MODULE_ID ?>" />
			<input type="hidden" name="uninstall" value="Y" />
			<input type="hidden" name="step" value="2" />
        <?php CAdminMessage::ShowMessage(GetMessage('IPOLSDEK_PRESERVE_TABLES')) ?>
			<p><?php echo GetMessage('MOD_UNINST_SAVE')?></p>
			 <p><input type="checkbox" name="savedata" id="savedata" value="Y" checked="checked" /><label for="savedata"><?php echo GetMessage('MOD_UNINST_SAVE_TABLES')?></label><br /></p>
			<input type="submit" name="inst" value="<?php echo GetMessage('MOD_UNINST_DEL');?>" />
		</form>
      <?php
		include($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php');
		die();
	}
}
?>
