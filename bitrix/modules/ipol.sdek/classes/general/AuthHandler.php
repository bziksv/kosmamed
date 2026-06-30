<?php

namespace Ipolh\SDEK;

use Ipolh\SDEK\Bitrix\Entity\cache;
use Ipolh\SDEK\Bitrix\Entity\encoder;
use Ipolh\SDEK\SDEK\SdekApplication;

class AuthHandler extends AbstractGeneral
{
    public static function auth($params)
    {
        if(!$params['login'] || !$params['password'])
            die('No auth data');

        if(!class_exists('CDeliverySDEK'))
            die('No main class founded');

        if(!function_exists('curl_init'))
            die(GetMessage("IPOLSDEK_AUTH_NOCURL"));

        $login = trim($params['login']);
        $password = trim($params['password']);

        $resAuth = self::checkAuth($login, $password);
        if($resAuth['success']){
            \sqlSdekLogs::Add(array('ACCOUNT' => $login, 'SECURE' => $password));
            $lastCheck = \sqlSdekLogs::Check($login);
            \Ipolh\SDEK\option::set('logged',$lastCheck);
            if($lastCheck){
               self::login();
                echo "G".GetMessage('IPOLSDEK_AUTH_YES');
            }else
                echo GetMessage('IPOLSDEK_AUTH_NO')." ".GetMessage('IPOLSDEK_AUTH_NO_BD');
        }
        else{
            $retStr=GetMessage('IPOLSDEK_AUTH_NO');
            foreach($resAuth as $erCode => $erText)
                $retStr.=\sdekdriver::zaDEjsonit($erText." (".$erCode."). ");

            echo $retStr;
        }
    }

    /**
     * Loginning and making a lot of needfull stuff
     */
    public static function login()
    {
        $isPVZ = (\Ipolh\SDEK\option::get('noPVZnoOrder') == 'Y');
        \Ipolh\SDEK\subscribeHandler::register($isPVZ);

        \Ipolh\SDEK\AgentHandler::addModuleAgents();

        $path = \COption::GetOptionString('sale','delivery_handles_custom_path','/bitrix/php_interface/include/sale_delivery/');
        if(!file_exists($_SERVER["DOCUMENT_ROOT"].$path."delivery_sdek.php"))
            CopyDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/".self::$MODULE_ID."/install/delivery/", $_SERVER["DOCUMENT_ROOT"].$path, true, true);
    }

    /**
     * Deloginning - remove options, clear cache, kill subscribes, removing agents
     */
    public static function delogin()
    {
        \Ipolh\SDEK\option::set('logged',false);
        \sqlSdekLogs::clear();
        \CAgent::RemoveModuleAgents(self::$MODULE_ID);
        \Ipolh\SDEK\subscribeHandler::unRegister();

        if(false/*Tools::isModuleAjaxRequest()*/)
            echo 'Y';
    }

    public static function callAccounts()
    {
        $accounts = \sqlSdekLogs::getAccountsList(true, false, true);
        $default = \Ipolh\SDEK\option::get('logged');

        foreach ($accounts as $id => $account) {
            $authCheck = false;
            $application = self::makeApplication($account['ACCOUNT'], $account['SECURE']);
            try {
                if ($application->getToken(true)) {
                    $authCheck = true;
                }
            } catch (\Exception $e) {
            }

            $accounts[$id] = array(
                'account' => $account['ACCOUNT'],
                'label'   => $account['LABEL'],
                'default' => ($id == $default),
                'auth'    => $authCheck
            );
        }
        echo json_encode(\sdekdriver::zajsonit($accounts));
    }

    public static function newAccount($params)
    {
        $resAuth = self::checkAuth($params['ACCOUNT'],$params['PASSWORD']);
        if($resAuth['success']){
            $arRequest = array('ACCOUNT' => $params['ACCOUNT'],'SECURE' => $params['PASSWORD'],'ACTIVE'=>'Y','LABEL'=>\sdekdriver::zaDEjsonit($params['LABEL']));
            $arReturn = array('result' => 'ok');
            $id = \sqlSdekLogs::Check($params['ACCOUNT']);
            if ($id) {
                \sqlSdekLogs::Update($id, $arRequest);
                $arReturn['text'] = GetMessage('IPOLSDEK_AUTH_UPDATE');
            } else {
                \sqlSdekLogs::Add($arRequest);
            }
            \Ipolh\SDEK\option::set('lastOldApiCheck', null);
        } else {
            $retStr = GetMessage('IPOLSDEK_AUTH_NO');
            foreach($resAuth as $erCode => $erText)
                $retStr.=\sdekdriver::zaDEjsonit($erText." (".$erCode."). ");
            $arReturn = array(
                'result' => 'error',
                'text'	 => $retStr
            );
        }

        echo json_encode(\sdekdriver::zajsonit($arReturn));
    }

    /**
     * @param $account
     * @param $password
     * @return array{0?: string, success?: true, error?: string}
     * @throws \Exception
     */
    static function checkAuth($account, $password)
    {
        $app = self::makeApplication($account, $password);

        try {
            $result = $app->getToken(true);
            if ($result) {
                $resAuth = array('success' => true);
            } else {
                $resAuth = array('error' => 'Failed to get token');
            }
        } catch (\Exception $e) {
            $resAuth = array($e->getMessage());
        }

        return $resAuth;
    }

    static function deleteAccount($id){
        $arReturn = array('result' => 'error', 'text' => '');
        if(!\sqlSdekLogs::getById($id))
            $arReturn['text'] = GetMessage('IPOLSDEK_AUTH_NO_EXIST');
        else{
            \sqlSdekLogs::setActive($id,'N');
            $curAccs = \sqlSdekLogs::getAccountsList();
            if(count($curAccs)){
                if($id == \Ipolh\SDEK\option::get('logged')){
                    reset($curAccs);
                    \Ipolh\SDEK\option::set('logged',key($curAccs));
                    $arReturn['result'] = 'collapse';
                }else
                    $arReturn['result'] = 'ok';
            }else{
                \Ipolh\SDEK\option::set('logged',false);
                $arReturn['result'] = 'collapse';
            }
            \Ipolh\SDEK\option::set('lastOldApiCheck', null);
        }
        return $arReturn;
    }

    public static function makeAccDefault($id=false){
        $arReturn = array('result' => 'error', 'text' => '');
        if(!\sqlSdekLogs::getById($id))
            $arReturn['text'] = GetMessage('IPOLSDEK_AUTH_NO_EXIST');
        else{
            $arReturn['result'] = 'collapse';
            \Ipolh\SDEK\option::set('logged',$id);
        }
        return $arReturn;
    }

    public static function isAuthorized()
    {
        /*$options = new Options();

        return (bool) $options->fetchClientId();*/
    }

    /**
     * Da 2.0 only
     * @param string $account
     * @param string $secure
     * @return SdekApplication
     */
    public static function makeApplication($account, $secure)
    {
        return new SdekApplication(
            $account,
            $secure,
            false,
            10,
            new encoder(),
            new cache()
        //, new \Ipolh\SDEK\Admin\IvanInlineLoggerController()
        );
    }
}