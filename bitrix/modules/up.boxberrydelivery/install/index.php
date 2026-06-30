<?global $MESS;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\EventManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\SystemException;
use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Main\IO\Directory;
use Bitrix\Main\IO\File;

Loc::loadMessages(__FILE__);
Class up_boxberrydelivery extends CModule {

	var $MODULE_ID = "up.boxberrydelivery";
	var $MODULE_VERSION;
	var $MODULE_VERSION_DATE;
	var $MODULE_NAME;
	var $MODULE_DESCRIPTION;
	var $MODULE_CSS;
	var $MODULE_GROUP_RIGHTS = 'Y';
	var $NEED_MAIN_VERSION = '16.0.0';
	var $NEED_MODULES = array('main', 'sale');

	public function __construct() {
		$arModuleVersion = array();
		$path = str_replace("\\", "/", __FILE__);
		$path = substr($path, 0, strlen($path) - strlen("/index.php"));
		include($path."/version.php");

		if (is_array($arModuleVersion) && array_key_exists('VERSION', $arModuleVersion)) {
			$this->MODULE_VERSION = $arModuleVersion['VERSION'];
			$this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
		}

		$this->PARTNER_URI  = "https://boxberry.ru";
		$this->PARTNER_NAME = Loc::getMessage('BOXBERRY_DELIVERY_PARTNER_NAME');
		$this->MODULE_NAME = Loc::getMessage('BOXBERRY_DELIVERY_INSTALL_NAME');
		$this->MODULE_DESCRIPTION = Loc::getMessage('BOXBERRY_DELIVERY_INSTALL_DESCRIPTION');

	}

    function InstallDB()
    {
        global $DB, $DBType, $APPLICATION;
        $strSql = "ALTER TABLE `b_boxberry_order` 
     ADD `BB_WIDTH` VARCHAR(255) NULL, ADD `BB_HEIGHT` VARCHAR(255) NULL,  ADD `BB_DEPTH` VARCHAR(255) NULL;";

        $addTable = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/' . $this->MODULE_ID . '/install/db/install.sql');

        if (!$addTable) {
            $DB->Query($strSql, true);
        }

        $DB->Query('CREATE TABLE IF NOT EXISTS `b_boxberry_cities` (
  `ID` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
  `BB_CITY_CODE` varchar(255) NULL,
  `BB_COUNTRY_CODE` varchar(255) NULL,
  `BITRIX_CITY_CODE` varchar(255) NULL)');

        $DB->Query('CREATE TABLE IF NOT EXISTS `b_boxberry_reception_points` (
      `ID` int NOT NULL AUTO_INCREMENT PRIMARY KEY,
      `CODE` varchar(255) NULL,
      `NAME` varchar(255) NULL,
      `CITY` varchar(255) NULL)');

        if ($addTable !== false) {
            $APPLICATION->ThrowException(implode("", $addTable));
            return false;
        }

        return true;
    }

	function UnInstallDB(){
		global $DB, $DBType, $APPLICATION;
		$this->errors = false;
		$this->errors = $DB->RunSQLBatch($_SERVER['DOCUMENT_ROOT']."/bitrix/modules/".$this->MODULE_ID."/install/db/uninstall.sql");
		if(!empty($this->errors)){
			$APPLICATION->ThrowException(implode("", $this->errors));
			return false;
		}

		return true;
	}

    function InstallEvents()
    {
        EventManager::getInstance()->registerEventHandler(
            "sale",
            "OnSaleComponentOrderOneStepDelivery",
            $this->MODULE_ID,
            "CDeliveryBoxberry",
            "widgetInit"
        );
    }

    function UnInstallEvents()
    {
        EventManager::getInstance()->unRegisterEventHandler(
            "sale",
            "OnSaleComponentOrderOneStepDelivery",
            $this->MODULE_ID,
            "CDeliveryBoxberry",
            "widgetInit"
        );
    }

	function InstallFiles()	{
		$pdf_directory =  $_SERVER["DOCUMENT_ROOT"]."/bitrix/pdf/";
		if (!file_exists($pdf_directory)) {
			mkdir($pdf_directory, 0777, true);
		}

		$res = false;
		$res = CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/admin/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/", true, true);
		$res = CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/js/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/js/", true, true);
        $res = CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/themes/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/themes/", true, true);
        $res = CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/images/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/images/", true, true);
		$res = CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/components/", $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/", true, true);
		$res = CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".$this->MODULE_ID."/install/delivery_boxberry/delivery_boxberry.php", $_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/include/sale_delivery/delivery_boxberry.php", true, true);
		return $res;
	}

	function UnInstallFiles() {
        Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"]."/bitrix/pdf/");
        Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".$this->MODULE_ID);
        Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"]."/bitrix/components/bberry/boxberry.widget");
        Directory::deleteDirectory($_SERVER["DOCUMENT_ROOT"]."/bitrix/images/".$this->MODULE_ID);
        File::deleteFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/boxberry.php");
        File::deleteFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/php_interface/include/sale_delivery/delivery_boxberry.php");
		return true;
	}

    function InstallAgents()
    {
        $agentName = 'CBoxberryAgents::loadReceptionPoints();';
        $agentExists = CAgent::GetList(
            [],
            [
                'MODULE_ID' => $this->MODULE_ID,
                'NAME' => $agentName,
            ]
        )->Fetch();

        if (!$agentExists) {
            CAgent::AddAgent(
                $agentName,
                $this->MODULE_ID,
                'N',
                3600,
                '',
                'Y',
                ConvertTimeStamp(time() + 3600, 'FULL')
            );
        }

    }

    public function InstallDelivery()
    {
        Loader::includeModule('sale');

        $fields = [
            'CODE'        => 'boxberry',
            'PARENT_ID'   => 0,
            'SORT'        => 100,
            'NAME'        => Loc::getMessage('BOXBERRY_DELIVERY_SHIPPING_NAME'),
            'DESCRIPTION' => Loc::getMessage('BOXBERRY_DELIVERY_SHIPPING_DESCRIPTION'),
            'CURRENCY'    => CurrencyManager::getBaseCurrency(),
            'CONFIG'      => [
                'MAIN' => [
                    'SID'               => 'boxberry',
                    'DESCRIPTION_INNER' => Loc::getMessage('BOXBERRY_DELIVERY_SHIPPING_NAME'),
                    'MARGIN_VALUE'      => 0,
                    'MARGIN_TYPE'       => '%',
                    'CURRENCY'          => CurrencyManager::getBaseCurrency(),
                ]
            ],
            'CLASS_NAME'          => '\\Bitrix\\Sale\\Delivery\\Services\\Automatic',
            'TRACKING_PARAMS'     => [],
            // 'CHANGED_FIELDS'      => [],
            'ACTIVE'              => 'Y',
            'ALLOW_EDIT_SHIPMENT' => 'Y',
            'LOGOTIP'             => array_merge([
                'MODULE_ID' => $this->MODULE_ID
            ], CFile::MakeFileArray($_SERVER["DOCUMENT_ROOT"].'/bitrix/images/'.$this->MODULE_ID.'/ya_delivery_bb_logo.png')
            ),
        ];

        CFile::SaveForDB($fields, "LOGOTIP", "ya_delivery_bb_logo");

        try {
            $service = Manager::createObject($fields);

            if($service) {
                $fields = $service->prepareFieldsForSaving($fields);
            }

            $res = Manager::add($fields);
            if ($res->isSuccess()) {
                if(!$fields["CLASS_NAME"]::isInstalled()) {
                    $fields["CLASS_NAME"]::install();
                }
            }
        } catch(SystemException $e) {
            $srvStrError = $e->getMessage();
        }
    }

    public function UnInstallDelivery()
    {

    }

	function DoInstall() {

		global $DOCUMENT_ROOT, $APPLICATION;
		if (is_array($this->NEED_MODULES) && !empty($this->NEED_MODULES))
			foreach ($this->NEED_MODULES as $module)
				if (!IsModuleInstalled($module))
					$this->ShowForm('ERROR', Loc::getMessage('BOXBERRY_DELIVERY_NEED_MODULES', array('#MODULE#' => $module)));
		if (strlen($this->NEED_MAIN_VERSION) <= 0 || version_compare(SM_VERSION, $this->NEED_MAIN_VERSION) >= 0) {
			RegisterModule($this->MODULE_ID);
			$this->InstallDB();
			$this->InstallEvents();
            $this->InstallFiles();
            $this->InstallDelivery();
            $this->InstallAgents();
		}
		else
			$this->ShowForm('ERROR', Loc::getMessage('BOXBERRY_DELIVERY_NEED_RIGHT_VER', array('#NEED#' => $this->NEED_MAIN_VERSION)));

	}

	function DoUninstall() {
		global $DOCUMENT_ROOT, $APPLICATION;
		$this->UnInstallFiles();
		$this->UnInstallDB();
		$this->UnInstallEvents();
        CAgent::RemoveModuleAgents($this->MODULE_ID);
        $this->UnInstallDelivery();
		
		UnRegisterModule($this->MODULE_ID);
		$this->ShowForm('OK', Loc::getMessage('BOXBERRY_DELIVERY_INSTALL_DEL'));
	}

	private function ShowForm($type, $message, $buttonName = '')
	{
		$keys = array_keys($GLOBALS);

		for ($i = 0; $i < count($keys); $i++)
			if ($keys[$i] != 'i' && $keys[$i] != 'GLOBALS' && $keys[$i] != 'strTitle' && $keys[$i] != 'filepath')
				global ${$keys[$i]};
				$APPLICATION->SetTitle(Loc::getMessage('BOXBERRY_DELIVERY_INSTALL_NAME'));

		include($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
		echo CAdminMessage::ShowMessage(array('MESSAGE' => $message, 'TYPE' => $type));?>
		<form action="<?= $APPLICATION->GetCurPage()?>" method="get">
		<p>
			<input type="hidden" name="lang" value="<?= LANG?>" />
			<input type="submit" value="<?= strlen($buttonName) ? $buttonName : Loc::getMessage('MOD_BACK')?>" />
		</p>
		</form>
		<?include($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
		die();
	}
}


?>