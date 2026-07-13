<?php
define('IPOLH_SDEK', 'ipol.sdek');
define('IPOLH_SDEK_LBL', 'IPOLSDEK_');

IncludeModuleLangFile(__FILE__);
// IPOLSDEK_LOG - ����� ����

CModule::IncludeModule("sale");

/*
 * IPOLSDEK_BASIC_URL - API для базового запроса
	onPVZListReady
*/

class sdekHelper{
    static $MODULE_ID    = "ipol.sdek";
    static $MODULE_LBL   = "IPOLSDEK_";
    static $MODULE_TOKEN = 'IPOLSDEK_MODULE_TOKEN';
    static $WIDGET_TOKEN = 'IPOLSDEK_WIDGET_TOKEN';

    public static function getAjaxAction($action,$subaction){
		Ipolh\SDEK\subscribeHandler::getAjaxAction($action,$subaction);
    }

    /**
     * Get module security token
     * @return mixed
     */
    public static function getModuleToken()
    {
        return $_SESSION[self::$MODULE_TOKEN];
    }

    /**
     * Create module security token and set in session
     */
    public static function createModuleToken()
    {
        if (empty($_SESSION[self::$MODULE_TOKEN])) {
            $_SESSION[self::$MODULE_TOKEN] = self::makeSecurityToken();
        }
    }

    /**
     * Get widget security token
     * @return mixed
     */
    public static function getWidgetToken()
    {
        return $_SESSION[self::$WIDGET_TOKEN];
    }

    /**
     * Create widget security token and set in session
     */
    public static function createWidgetToken()
    {
        if (empty($_SESSION[self::$WIDGET_TOKEN])) {
            $_SESSION[self::$WIDGET_TOKEN] = self::makeSecurityToken();
        }
    }

    /**
     * Make security token used in ajax calls
     *
     * @return mixed|string
     */
    public static function makeSecurityToken()
    {
        if (version_compare(PHP_VERSION, '7.0.0') >= 0) {
            $rand = random_bytes(32);
        } else if (function_exists('mcrypt_create_iv')) {
            $rand = mcrypt_create_iv(32, MCRYPT_DEV_URANDOM);
        } else {
            $rand = openssl_random_pseudo_bytes(32);
        }

        return bin2hex($rand);
    }

    /**
     * Checks if given tokens equal
     *
     * @param $tokenA
     * @param $tokenB
     * @return bool
     */
    public static function checkTokens($tokenA, $tokenB)
    {
        return hash_equals($tokenA, $tokenB);
    }

    /*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
                                                        LOGS & ERRORS
            == toLog ==  == errorLog ==  == getErrors ==  == toAnswer ==  == getAnswer ==
    ()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


    // ������� ���
    static function toLog($wat,$sign='',$noAction=false){
        if($noAction && ($_REQUEST['isdek_action']=='countDelivery' || $_REQUEST['action']=='countDelivery')) return;
        if($sign) $sign.=" ";
        if(!$GLOBALS['isdek_logfile']){
            $GLOBALS['isdek_logfile'] = fopen($_SERVER['DOCUMENT_ROOT'].'/SDEKLog.txt','w');
            fwrite($GLOBALS['isdek_logfile'],"\n\n".date('H:i:s d.m')."\n");
        }
        fwrite($GLOBALS['isdek_logfile'],$sign.print_r($wat,true)."\n");
    }
    // ��� ������
    static $ERROR_REF = '';
    static function errorLog($error){
        if(!\Ipolh\SDEK\option::get('logged'))
            return;
        self::$ERROR_REF .= $error."\n";
        $file=fopen($_SERVER["DOCUMENT_ROOT"]."/bitrix/js/".self::$MODULE_ID."/errorLog.txt","a");
        fwrite($file,"\n".date("d.m.Y H:i:s")." ".self::zaDEjsonit($error));
        fclose($file);
    }
    static function getErrors(){
        return self::$ERROR_REF;
    }
    // ��� �������
    static $ANSWER_REF;
    static function toAnswer($wat,$sign=''){
        if($sign) $sign.=" ";
        if(self::$ANSWER_REF) self::$ANSWER_REF.="\n";
        self::$ANSWER_REF.=$sign.print_r($wat,true);
    }
    static function getAnswer(){
        return self::$ANSWER_REF;
    }


    /*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
                                                        ENCODING
            == zajsonit ==  == zaDEjsonit ==  == toUpper ==
    ()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


    static function zajsonit($handle){
        return \Ipolh\SDEK\Bitrix\Tools::encodeToUTF8($handle);
    }
    static function zaDEjsonit($handle){
        return \Ipolh\SDEK\Bitrix\Tools::encodeFromUTF8($handle);
    }

    static function toUpper($str){
        $str = str_replace( //H8 ANSI
            array(
                GetMessage('IPOLSDEK_LANG_YO_S'),
                GetMessage('IPOLSDEK_LANG_CH_S'),
                GetMessage('IPOLSDEK_LANG_YA_S')
            ),
            array(
                GetMessage('IPOLSDEK_LANG_YO_B'),
                GetMessage('IPOLSDEK_LANG_CH_B'),
                GetMessage('IPOLSDEK_LANG_YA_B')
            ),
            $str
        );
        if(function_exists('mb_strtoupper'))
            return mb_strtoupper($str,LANG_CHARSET);
        else
            return strtoupper($str);
    }


    /*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
                                                        SENDING TO SDEK
            == sendToSDEK ==  == getXMLHeaders ==
    ()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


    public static function sendToSDEK($XML=false,$where=false,$get=false){
        return \Ipolh\SDEK\Legacy\transitApplication::sendToSDEK($XML,$where,$get);
    }

    static function getXMLHeaders($auth = false){
        $auth = self::defineAuth($auth);
        $date = date('Y-m-d');
        return array(
            'date'    => $date,
            'account' => $auth['ACCOUNT'],
            'secure'  => md5($date."&".$auth['SECURE']),
            'ID'	  => $auth['ID']
        );
    }


    /*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
                                                            AUTHORIZATION
            == defineAuth ==  == getBasicAuth ==  == getOrderAcc ==
    ()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


    static function defineAuth($params=false){
        if(!$params)
            $auth = self::getBasicAuth();
        else{
            if(is_array($params) && array_key_exists('ID',$params) && $params['ID'])
                $params = $params['ID'];
            if(is_numeric($params))
                $auth = sqlSdekLogs::getById($params);
            else{
                // ���������� �� ������
                $svd = self::getCountryOptions();
                if(array_key_exists($params['COUNTRY'], $svd) && array_key_exists('acc', $svd[$params['COUNTRY']]) && $svd[$params['COUNTRY']]['acc']) {
                    $auth = sqlSdekLogs::getById($svd[$params['COUNTRY']]['acc']);
                    if($auth['ACTIVE'] != 'Y') {
                        $auth = self::getBasicAuth();
                    }
                } else {
                    $auth = self::getBasicAuth();
                }
            }
        }

        return array('ACCOUNT' => $auth['ACCOUNT'],'SECURE' => $auth['SECURE'],'ID'=>$auth['ID'],'LABEL'=>$auth['LABEL']);
    }

    static function getBasicAuth($onlyId = false){
        $idBasic = \Ipolh\SDEK\option::get('logged');
        if($idBasic !== false && $auth = sqlSdekLogs::getById($idBasic))
            return ($onlyId) ? $idBasic : $auth;
        else
            return false;
    }

    static function getOrderAcc($src,$mode=false){
        if(!self::isAdmin('R')) return false;
        if(!is_array($src))
            $src = ($mode == 'shipment') ? sqlSdekOrders::GetBySI($src) : sqlSdekOrders::GetByOI($src);
        return ($src['ACCOUNT']) ? $src['ACCOUNT'] : false;
    }


    /*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
                                                        TARIFS
            == getTarifList ==  == checkTarifAvail ==  == getDoorTarifs ==  == getExtraTarifs ==
    ()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/

    /**
     * Returns map with supported CDEK tariffs
     * @return array
     */
    public static function getTariffMap()
    {
        $map = [
            /*
             * ID        - tariff CDEK id
             * FROM      - DOOR | PVZ
             * TO        - DOOR | PVZ | PST
             * STATE     - ACTIVE | ARCHIVE - is tariff valid or outdated
             * SORT_OPT  - sort index for module options page
             * SORT_LIST - sort index for lists like tariff selector in Order sender form
             * MODE      - usual | express | heavy - calculation mode
             * PRIORITY  - tariff priority between tariffs with same TO and MODE
             */

            // Courier -------------------------------------------------------------------------------------------------

            ['ID' => 137, 'FROM' => 'PVZ',  'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 1,  'SORT_LIST' => 3,   'MODE' => 'usual', 'PRIORITY' => 3],
            ['ID' => 139, 'FROM' => 'DOOR', 'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 2,  'SORT_LIST' => 4,   'MODE' => 'usual', 'PRIORITY' => 4],
            ['ID' => 233, 'FROM' => 'PVZ',  'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 3,  'SORT_LIST' => 9,   'MODE' => 'usual', 'PRIORITY' => 1],
            ['ID' => 231, 'FROM' => 'DOOR', 'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 4,  'SORT_LIST' => 10,  'MODE' => 'usual', 'PRIORITY' => 2],

            ['ID' => 482, 'FROM' => 'PVZ',  'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 5,  'SORT_LIST' => 15,  'MODE' => 'express', 'PRIORITY' => 1],
            ['ID' => 480, 'FROM' => 'DOOR', 'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 6,  'SORT_LIST' => 16,  'MODE' => 'express', 'PRIORITY' => 2],

            ['ID' => 122, 'FROM' => 'PVZ',  'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 7,  'SORT_LIST' => 21,  'MODE' => 'express', 'PRIORITY' => 3],
            ['ID' => 121, 'FROM' => 'DOOR', 'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 8,  'SORT_LIST' => 22,  'MODE' => 'express', 'PRIORITY' => 4],
            ['ID' => 125, 'FROM' => 'PVZ',  'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 9,  'SORT_LIST' => 25,  'MODE' => 'express', 'PRIORITY' => 5],
            ['ID' => 124, 'FROM' => 'DOOR', 'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 10, 'SORT_LIST' => 26,  'MODE' => 'express', 'PRIORITY' => 6],

            ['ID' => 750, 'FROM' => 'PVZ',  'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 11, 'SORT_LIST' => 52,  'MODE' => 'express', 'PRIORITY' => 7],
            ['ID' => 748, 'FROM' => 'DOOR', 'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 12, 'SORT_LIST' => 53,  'MODE' => 'express', 'PRIORITY' => 8],

            ['ID' => 57,  'FROM' => 'DOOR', 'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 13, 'SORT_LIST' => 101, 'MODE' => 'express', 'PRIORITY' => 30],
            ['ID' => 678, 'FROM' => 'PVZ',  'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 14, 'SORT_LIST' => 104, 'MODE' => 'express', 'PRIORITY' => 27],
            ['ID' => 58,  'FROM' => 'DOOR', 'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 15, 'SORT_LIST' => 105, 'MODE' => 'express', 'PRIORITY' => 29],
            ['ID' => 676, 'FROM' => 'DOOR', 'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 16, 'SORT_LIST' => 106, 'MODE' => 'express', 'PRIORITY' => 28],
            ['ID' => 778, 'FROM' => 'PVZ',  'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 17, 'SORT_LIST' => 111, 'MODE' => 'express', 'PRIORITY' => 24],
            ['ID' => 688, 'FROM' => 'PVZ',  'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 18, 'SORT_LIST' => 112, 'MODE' => 'express', 'PRIORITY' => 23],
            ['ID' => 59,  'FROM' => 'DOOR', 'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 19, 'SORT_LIST' => 113, 'MODE' => 'express', 'PRIORITY' => 26],
            ['ID' => 686, 'FROM' => 'DOOR', 'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 20, 'SORT_LIST' => 114, 'MODE' => 'express', 'PRIORITY' => 25],
            ['ID' => 787, 'FROM' => 'PVZ',  'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 21, 'SORT_LIST' => 119, 'MODE' => 'express', 'PRIORITY' => 20],
            ['ID' => 698, 'FROM' => 'PVZ',  'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 22, 'SORT_LIST' => 120, 'MODE' => 'express', 'PRIORITY' => 19],
            ['ID' => 60,  'FROM' => 'DOOR', 'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 23, 'SORT_LIST' => 121, 'MODE' => 'express', 'PRIORITY' => 22],
            ['ID' => 696, 'FROM' => 'DOOR', 'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 24, 'SORT_LIST' => 122, 'MODE' => 'express', 'PRIORITY' => 21],
            ['ID' => 796, 'FROM' => 'PVZ',  'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 25, 'SORT_LIST' => 127, 'MODE' => 'express', 'PRIORITY' => 16],
            ['ID' => 708, 'FROM' => 'PVZ',  'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 26, 'SORT_LIST' => 128, 'MODE' => 'express', 'PRIORITY' => 15],
            ['ID' => 61,  'FROM' => 'DOOR', 'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 27, 'SORT_LIST' => 129, 'MODE' => 'express', 'PRIORITY' => 18],
            ['ID' => 706, 'FROM' => 'DOOR', 'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 28, 'SORT_LIST' => 130, 'MODE' => 'express', 'PRIORITY' => 17],
            ['ID' => 805, 'FROM' => 'PVZ',  'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 29, 'SORT_LIST' => 135, 'MODE' => 'express', 'PRIORITY' => 12],
            ['ID' => 718, 'FROM' => 'PVZ',  'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 30, 'SORT_LIST' => 136, 'MODE' => 'express', 'PRIORITY' => 11],
            ['ID' => 3,   'FROM' => 'DOOR', 'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 31, 'SORT_LIST' => 137, 'MODE' => 'express', 'PRIORITY' => 14],
            ['ID' => 716, 'FROM' => 'DOOR', 'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 32, 'SORT_LIST' => 138, 'MODE' => 'express', 'PRIORITY' => 13],

            ['ID' => 83,  'FROM' => 'PVZ',  'TO' => 'DOOR', 'STATE' => 'ACTIVE',  'SORT_OPT' => 33, 'SORT_LIST' => 139, 'MODE' => 'express', 'PRIORITY' => 31],

            ['ID' => 11,  'FROM' => 'PVZ',  'TO' => 'DOOR', 'STATE' => 'ARCHIVE', 'SORT_OPT' => 1,  'SORT_LIST' => 504, 'MODE' => 'express', 'PRIORITY' => 9],
            ['ID' => 1,   'FROM' => 'DOOR', 'TO' => 'DOOR', 'STATE' => 'ARCHIVE', 'SORT_OPT' => 2,  'SORT_LIST' => 505, 'MODE' => 'express', 'PRIORITY' => 10],
            ['ID' => 16,  'FROM' => 'PVZ',  'TO' => 'DOOR', 'STATE' => 'ARCHIVE', 'SORT_OPT' => 3,  'SORT_LIST' => 510, 'MODE' => 'heavy', 'PRIORITY' => 1],
            ['ID' => 18,  'FROM' => 'DOOR', 'TO' => 'DOOR', 'STATE' => 'ARCHIVE', 'SORT_OPT' => 4,  'SORT_LIST' => 511, 'MODE' => 'heavy', 'PRIORITY' => 2],

            // Pickup --------------------------------------------------------------------------------------------------

            ['ID' => 136, 'FROM' => 'PVZ',  'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 1,  'SORT_LIST' => 1,   'MODE' => 'usual', 'PRIORITY' => 3],
            ['ID' => 138, 'FROM' => 'DOOR', 'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 2,  'SORT_LIST' => 2,   'MODE' => 'usual', 'PRIORITY' => 4],
            ['ID' => 234, 'FROM' => 'PVZ',  'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 3,  'SORT_LIST' => 7,   'MODE' => 'usual', 'PRIORITY' => 1],
            ['ID' => 232, 'FROM' => 'DOOR', 'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 4,  'SORT_LIST' => 8,   'MODE' => 'usual', 'PRIORITY' => 2],

            ['ID' => 483, 'FROM' => 'PVZ',  'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 5,  'SORT_LIST' => 13,  'MODE' => 'express', 'PRIORITY' => 1],
            ['ID' => 481, 'FROM' => 'DOOR', 'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 6,  'SORT_LIST' => 14,  'MODE' => 'express', 'PRIORITY' => 2],

            ['ID' => 62,  'FROM' => 'PVZ',  'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 7,  'SORT_LIST' => 19,  'MODE' => 'express', 'PRIORITY' => 3],
            ['ID' => 123, 'FROM' => 'DOOR', 'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 8,  'SORT_LIST' => 20,  'MODE' => 'express', 'PRIORITY' => 4],
            ['ID' => 63,  'FROM' => 'PVZ',  'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 9,  'SORT_LIST' => 23,  'MODE' => 'express', 'PRIORITY' => 5],
            ['ID' => 126, 'FROM' => 'DOOR', 'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 10, 'SORT_LIST' => 24,  'MODE' => 'express', 'PRIORITY' => 6],

            ['ID' => 751, 'FROM' => 'PVZ',  'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 11, 'SORT_LIST' => 50,  'MODE' => 'express', 'PRIORITY' => 7],
            ['ID' => 749, 'FROM' => 'DOOR', 'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 12, 'SORT_LIST' => 51,  'MODE' => 'express', 'PRIORITY' => 8],

            ['ID' => 679, 'FROM' => 'PVZ',  'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 13, 'SORT_LIST' => 102, 'MODE' => 'express', 'PRIORITY' => 28],
            ['ID' => 677, 'FROM' => 'DOOR', 'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 14, 'SORT_LIST' => 103, 'MODE' => 'express', 'PRIORITY' => 29],
            ['ID' => 779, 'FROM' => 'PVZ',  'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 15, 'SORT_LIST' => 107, 'MODE' => 'express', 'PRIORITY' => 25],
            ['ID' => 689, 'FROM' => 'PVZ',  'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 16, 'SORT_LIST' => 108, 'MODE' => 'express', 'PRIORITY' => 24],
            ['ID' => 777, 'FROM' => 'DOOR', 'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 17, 'SORT_LIST' => 109, 'MODE' => 'express', 'PRIORITY' => 27],
            ['ID' => 687, 'FROM' => 'DOOR', 'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 18, 'SORT_LIST' => 110, 'MODE' => 'express', 'PRIORITY' => 26],
            ['ID' => 788, 'FROM' => 'PVZ',  'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 19, 'SORT_LIST' => 115, 'MODE' => 'express', 'PRIORITY' => 21],
            ['ID' => 699, 'FROM' => 'PVZ',  'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 20, 'SORT_LIST' => 116, 'MODE' => 'express', 'PRIORITY' => 20],
            ['ID' => 786, 'FROM' => 'DOOR', 'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 21, 'SORT_LIST' => 117, 'MODE' => 'express', 'PRIORITY' => 23],
            ['ID' => 697, 'FROM' => 'DOOR', 'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 22, 'SORT_LIST' => 118, 'MODE' => 'express', 'PRIORITY' => 22],
            ['ID' => 797, 'FROM' => 'PVZ',  'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 23, 'SORT_LIST' => 123, 'MODE' => 'express', 'PRIORITY' => 17],
            ['ID' => 709, 'FROM' => 'PVZ',  'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 24, 'SORT_LIST' => 124, 'MODE' => 'express', 'PRIORITY' => 16],
            ['ID' => 795, 'FROM' => 'DOOR', 'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 25, 'SORT_LIST' => 125, 'MODE' => 'express', 'PRIORITY' => 19],
            ['ID' => 707, 'FROM' => 'DOOR', 'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 26, 'SORT_LIST' => 126, 'MODE' => 'express', 'PRIORITY' => 18],
            ['ID' => 806, 'FROM' => 'PVZ',  'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 27, 'SORT_LIST' => 131, 'MODE' => 'express', 'PRIORITY' => 13],
            ['ID' => 719, 'FROM' => 'PVZ',  'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 28, 'SORT_LIST' => 132, 'MODE' => 'express', 'PRIORITY' => 12],
            ['ID' => 804, 'FROM' => 'DOOR', 'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 29, 'SORT_LIST' => 133, 'MODE' => 'express', 'PRIORITY' => 15],
            ['ID' => 717, 'FROM' => 'DOOR', 'TO' => 'PVZ',  'STATE' => 'ACTIVE',  'SORT_OPT' => 30, 'SORT_LIST' => 134, 'MODE' => 'express', 'PRIORITY' => 14],

            ['ID' => 5,   'FROM' => 'PVZ',  'TO' => 'PVZ',  'STATE' => 'ARCHIVE', 'SORT_OPT' => 1,  'SORT_LIST' => 501, 'MODE' => 'express', 'PRIORITY' => 9],
            ['ID' => 10,  'FROM' => 'PVZ',  'TO' => 'PVZ',  'STATE' => 'ARCHIVE', 'SORT_OPT' => 2,  'SORT_LIST' => 502, 'MODE' => 'express', 'PRIORITY' => 10],
            ['ID' => 12,  'FROM' => 'DOOR', 'TO' => 'PVZ',  'STATE' => 'ARCHIVE', 'SORT_OPT' => 3,  'SORT_LIST' => 503, 'MODE' => 'express', 'PRIORITY' => 11],
            ['ID' => 15,  'FROM' => 'PVZ',  'TO' => 'PVZ',  'STATE' => 'ARCHIVE', 'SORT_OPT' => 4,  'SORT_LIST' => 508, 'MODE' => 'heavy', 'PRIORITY' => 1],
            ['ID' => 17,  'FROM' => 'DOOR', 'TO' => 'PVZ',  'STATE' => 'ARCHIVE', 'SORT_OPT' => 5,  'SORT_LIST' => 509, 'MODE' => 'heavy', 'PRIORITY' => 2],

            // Postamat ------------------------------------------------------------------------------------------------

            ['ID' => 368, 'FROM' => 'PVZ',  'TO' => 'PST',  'STATE' => 'ACTIVE',  'SORT_OPT' => 1,  'SORT_LIST' => 5,   'MODE' => 'usual', 'PRIORITY' => 3],
            ['ID' => 366, 'FROM' => 'DOOR', 'TO' => 'PST',  'STATE' => 'ACTIVE',  'SORT_OPT' => 2,  'SORT_LIST' => 6,   'MODE' => 'usual', 'PRIORITY' => 4],
            ['ID' => 378, 'FROM' => 'PVZ',  'TO' => 'PST',  'STATE' => 'ACTIVE',  'SORT_OPT' => 3,  'SORT_LIST' => 11,  'MODE' => 'usual', 'PRIORITY' => 1],
            ['ID' => 376, 'FROM' => 'DOOR', 'TO' => 'PST',  'STATE' => 'ACTIVE',  'SORT_OPT' => 4,  'SORT_LIST' => 12,  'MODE' => 'usual', 'PRIORITY' => 2],

            ['ID' => 486, 'FROM' => 'PVZ',  'TO' => 'PST',  'STATE' => 'ACTIVE',  'SORT_OPT' => 5,  'SORT_LIST' => 17,  'MODE' => 'express', 'PRIORITY' => 3],
            ['ID' => 485, 'FROM' => 'DOOR', 'TO' => 'PST',  'STATE' => 'ACTIVE',  'SORT_OPT' => 6,  'SORT_LIST' => 18,  'MODE' => 'express', 'PRIORITY' => 4],

            ['ID' => 363, 'FROM' => 'PVZ',  'TO' => 'PST',  'STATE' => 'ARCHIVE', 'SORT_OPT' => 1,  'SORT_LIST' => 506, 'MODE' => 'express', 'PRIORITY' => 1],
            ['ID' => 361, 'FROM' => 'DOOR', 'TO' => 'PST',  'STATE' => 'ARCHIVE', 'SORT_OPT' => 2,  'SORT_LIST' => 507, 'MODE' => 'express', 'PRIORITY' => 2],
        ];

        return $map;
    }

    /**
     * Returns tariff list
     * @param array $params - Possible params are
     * type   - courier | pickup | postamat - tariff type
     * mode   - usual | express | heavy - calculation mode
     * answer - string | array - representation of returned data
     * @return array|string
     */
    public static function getTarifList($params = array())
    {
        $tariffList = [];

        $tariffMap = self::getTariffMap();
        foreach ($tariffMap as $tariff) {
            // Compatibility reasons
            switch ($tariff['TO']) {
                case 'DOOR': $type = 'courier'; break;
                case 'PVZ':  $type = 'pickup'; break;
                case 'PST':  $type = 'postamat'; break;
            }

            $tariffList[$type][$tariff['MODE']][$tariff['ID']] = $tariff['PRIORITY'];
        }

        foreach ($tariffList as $tariffType => $tariffModes) {
            foreach ($tariffModes as $tariffMode => $tariffs) {
                uasort($tariffList[$tariffType][$tariffMode], function($a, $b) {
                    if ($a == $b) {
                        return 0;
                    }
                    return ($a < $b) ? -1 : 1;
                });

                $tariffList[$tariffType][$tariffMode] = array_keys($tariffList[$tariffType][$tariffMode]);
            }
        }

        $blocked = \Ipolh\SDEK\option::get('tarifs');
        if ($blocked && count($blocked) && (!array_key_exists('fSkipCheckBlocks', $params) || !$params['fSkipCheckBlocks'])) {
            foreach ($blocked as $key => $val) {
                if (!array_key_exists('BLOCK', $val))
                    unset($blocked[$key]);
            }

            if (count($blocked)) {
                foreach ($tariffList as $tariffType => $tariffModes) {
                    foreach ($tariffModes as $tariffMode => $tariffs) {
                        foreach ($tariffs as $key => $tariffId) {
                            if (array_key_exists($tariffId, $blocked))
                                unset($tariffList[$tariffType][$tariffMode][$key]);
                        }
                    }
                }
            }
        }

        $answer = $tariffList;
        if ($params['type']) {
            if (is_numeric($params['type'])) {
                $type = ($params['type'] == 136) ? $type = 'pickup' : $type = 'courier';
            } else {
                $type = $params['type'];
            }

            $answer = $answer[$type];

            if ((array_key_exists('mode', $params) && $params['mode']) && array_key_exists($params['mode'], $answer))
                $answer = $answer[$params['mode']];
        }

        if (array_key_exists('answer', $params)) {
            $answer = self::arrVals($answer);
            if ($params['answer'] == 'string') {
                $answer = implode(',', $answer);
                $answer = substr($answer, 0, strlen($answer));
            }
        }

        return $answer;
    }

    /**
     * Checks if some tariffs available for given profile
     * @param $profile
     * @return bool
     */
    public static function checkTarifAvail($profile = false)
    {
        $tarifs = self::getTarifList(array('type' => $profile, 'answer' => 'array'));
        return (count($tarifs)>0);
    }

    /**
     * Returns DOOR-* tariff list
     * @param bool $isStr
     * @return int[]|string
     */
    public static function getDoorTarifs($isStr = false)
    {
        $tariffList = [];

        $tariffMap = self::getTariffMap();
        foreach ($tariffMap as $tariff) {
            if ($tariff['FROM'] === 'DOOR') {
                $tariffList[] = $tariff['ID'];
            }
        }
        sort($tariffList);

        if ($isStr) {
            $tariffList = implode(',', $tariffList);
            $tariffList = substr($tariffList, 0, strlen($tariffList));
        }

        return $tariffList;
    }

    /**
     * Returns tariff list as flat array. Used in OrderSender tariff selector
     * @return array
     */
    public static function getExtraTarifs()
    {
        $result = [];

        $tariffMap = self::getTariffMap();
        $svdOpts    = \Ipolh\SDEK\option::get('tarifs');

        foreach ($tariffMap as $tariff) {
            $id = $tariff['ID'];
            $result[$id] = [
                'NAME'  => GetMessage('IPOLSDEK_tarif_'.$id.'_NAME').' ('.$id.')',
                'DESC'  => GetMessage('IPOLSDEK_tarif_'.$id.'_DESCR'),
                'SHOW'  => (array_key_exists($id, $svdOpts) && array_key_exists('SHOW', $svdOpts[$id]) && $svdOpts[$id]['SHOW']) ? $svdOpts[$id]['SHOW'] : 'N',
                'BLOCK' => (array_key_exists($id, $svdOpts) && array_key_exists('BLOCK', $svdOpts[$id]) && $svdOpts[$id]['BLOCK']) ? $svdOpts[$id]['BLOCK']: 'N',
                'SORT'  => $tariff['SORT_LIST'],
            ];
        }

        uasort($result, function($a, $b) {
            if ($a['SORT'] == $b['SORT']) {
                return 0;
            }
            return ($a['SORT'] < $b['SORT']) ? -1 : 1;
        });

        return $result;
    }

    /**
     * Returns tariff list as structured array. Used in module options.
     * @return array
     */
    public static function getStructuredTariffList()
    {
        $result = [];

        $tariffMap = self::getTariffMap();
        $svdOpts    = \Ipolh\SDEK\option::get('tarifs');

        foreach ($tariffMap as $tariff) {
            $id = $tariff['ID'];
            $result[$tariff['STATE']][$tariff['TO']][$id] = [
                'NAME'  => GetMessage('IPOLSDEK_tarif_'.$id.'_NAME').' ('.$id.')',
                'DESC'  => GetMessage('IPOLSDEK_tarif_'.$id.'_DESCR'),
                'SHOW'  => (array_key_exists($id, $svdOpts) && array_key_exists('SHOW', $svdOpts[$id]) && $svdOpts[$id]['SHOW']) ? $svdOpts[$id]['SHOW'] : 'N',
                'BLOCK' => (array_key_exists($id, $svdOpts) && array_key_exists('BLOCK', $svdOpts[$id]) && $svdOpts[$id]['BLOCK']) ? $svdOpts[$id]['BLOCK']: 'N',
                'SORT'  => $tariff['SORT_OPT'],
            ];
        }

        foreach ($result as $state => $profiles) {
            foreach ($profiles as $to => $tariffs) {
                uasort($result[$state][$to], function($a, $b) {
                    if ($a['SORT'] == $b['SORT']) {
                        return 0;
                    }
                    return ($a['SORT'] < $b['SORT']) ? -1 : 1;
                });
            }
        }

        return $result;
    }

    /**
     * Defines, what tarif is given
     * @param $tarif - id of tarif
     * @return false|string - either profile or nothing
     */
    public static function defineTarif($tarif)
    {
        $arTarifs = self::getTarifList();
        foreach ($arTarifs as $profile => $_arTarif) {
            foreach ($_arTarif as $_tarifs) {
                if (in_array($tarif, $_tarifs)) {
                    return $profile;
                }
            }
        }

        return false;
    }


    /*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
                                                        HANDLING DELIVERIES
            == getDeliveryId ==  == defineDelivery ==  == getDelivery ==  == isActive ==  == getDeliveryConfig ==
    ()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


    static function getDeliveryId($profile){ // ���������� id �������� �������
        $profiles = array();
        if(self::isConverted()){
            $dTS = Bitrix\Sale\Delivery\Services\Table::getList(array(
                'order'  => array('SORT' => 'ASC', 'NAME' => 'ASC'),
                'filter' => array('CODE' => 'sdek:'.$profile)
            ));
            while($dPS = $dTS->Fetch())
                $profiles[]=$dPS['ID'];
        }else
            $profiles = array('sdek_'.$profile);
        return $profiles;
    }

    static function defineDelivery($id){ // ���������� ������� ��������
        if(self::isConverted() && strpos($id,':') === false){
            $dTS = Bitrix\Sale\Delivery\Services\Table::getList(array(
                'order'  => array('SORT' => 'ASC', 'NAME' => 'ASC'),
                'filter' => array('ID' => $id)
            ))->Fetch();
            $delivery = $dTS['CODE'];
        }else
            $delivery = $id;
        $position = strpos($delivery,'sdek:');
        return ($position === 0) ? substr($delivery,5) : false;
    }

    static function getDelivery($skipSite = false,$curId = false){// �������� ���������� ��
        if(!cmodule::includeModule("sale")) return false;
        $cite = ($skipSite) ? false : SITE_ID;
        if(self::isConverted()){
            $arFilter = ($curId) ? array('ID' => $curId) : array('CODE' => 'sdek');
			$request = Bitrix\Sale\Delivery\Services\Table::getList(array(
                'order'  => array('SORT' => 'ASC', 'NAME' => 'ASC'),
                'filter' => $arFilter
            ));
			while($dS = $request->Fetch()){
				if($dS['ACTIVE'] == 'Y')
					break;
			}
        }else
            $dS = CSaleDeliveryHandler::GetBySID('sdek',$cite)->Fetch();
        return $dS;
    }

    static function isActive(){
        $dS = self::getDelivery();
        return ($dS && $dS['ACTIVE'] == 'Y');
    }

    static function checkProfileActive($profile,$skipSite = false){
        cmodule::includeModule('sale');
        $cite = ($skipSite) ? false : SITE_ID;
        if(self::isConverted()){
            $dTS = Bitrix\Sale\Delivery\Services\Table::getList(array(
                'order'  => array('SORT' => 'ASC', 'NAME' => 'ASC'),
                'filter' => array('CODE' => 'sdek:'.$profile)
            ));
            while($dPS = $dTS->Fetch())
                if($dPS['ACTIVE'] == 'Y')
                    return true;
        }else{
            $dS = CSaleDeliveryHandler::GetBySID('sdek',$cite)->Fetch();
            return (array_key_exists($profile,$dS['PROFILES']) && $dS['PROFILES'][$profile]['ACTIVE']=='Y');
        }
        return false;
    }

    static function getDeliveryConfig($deliveryId=false,$skipSite = false){
        cmodule::includeModule('sale');
        $cite = ($skipSite) ? false : SITE_ID;
        if(self::isConverted()) {
            $dTS = Bitrix\Sale\Delivery\Services\Table::getList(array(
                'order'  => array('SORT' => 'ASC', 'NAME' => 'ASC'),
                'filter' => array('ID'   => $deliveryId)
            ))->Fetch();
            if($dTS && array_key_exists('PARENT_ID',$dTS) && $dTS['PARENT_ID']){
                $dTS = Bitrix\Sale\Delivery\Services\Table::getList(array(
                    'order'  => array('SORT' => 'ASC', 'NAME' => 'ASC'),
                    'filter' => array('ID'   => $dTS['PARENT_ID'])
                ))->Fetch();
            }

			if($dTS){
				$oldSettings = unserialize(unserialize($dTS['CONFIG']['MAIN']['OLD_SETTINGS'], ['allowed_classes' => false]), ['allowed_classes' => false]);
				foreach($oldSettings as $name => $value){
					$oldSettings[$name] = array('VALUE' => $value);
				}
			} else {
				$oldSettings = array();
			}

            return $oldSettings;
        } else {
            $dS = CSaleDeliveryHandler::GetBySID('sdek',$cite)->Fetch();
            return $dS['CONFIG']['CONFIG'];
        }
    }

    /*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
                                                        ������ � �������� � ����������������
            == getErrCities ==  == getNormalCity ==  == isLocation20 ==  == isCityAvail ==
    ()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


    public static function getErrCities($link = 'rus') {
        return ['many' => self::getMultipleMatchedCities($link), 'notFound' => self::getNotFoundedCities($link)];
    }

    public static function getNotFoundedCities($link = 'rus') {
        $jsPath = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/js/' . self::$MODULE_ID . '/';
        $fileName = 'notFoundedCities' . (($link === 'rus') ? ''  : '_' . $link);
        if (!file_exists($jsPath . $fileName . '.json')) {
            return false;
        }
        return  self::zaDEjsonit(json_decode(file_get_contents($jsPath . $fileName . '.json'),true));
    }

    public static function getMultipleMatchedCities($link = 'rus') {
        $jsPath = $_SERVER['DOCUMENT_ROOT'] . '/bitrix/js/' . self::$MODULE_ID . '/';
        $fileName = 'multipleMatchedCities' . (($link === 'rus') ? ''  : '_' . $link);
        if (!file_exists($jsPath . $fileName . '.json')) {
            return false;
        }
        return  self::zaDEjsonit(json_decode(file_get_contents($jsPath . $fileName . '.json'),true));
    }

    static function getNormalCity($cityId,$onlyCity = false){// �������������� 2.0, �������� id �����a
        if(self::isLocation20() && $cityId){//getLocationIDbyCODE
            $cityType    = \Bitrix\Sale\Location\TypeTable::getList(array('filter'=>array('=CODE'=>'CITY')))->Fetch();
            $villageType = \Bitrix\Sale\Location\TypeTable::getList(array('filter'=>array('=CODE'=>'VILLAGE')))->Fetch();
            if(strlen($cityId) >= 10 || !is_numeric($cityId))
                $city = \Bitrix\Sale\Location\LocationTable::getList(array('filter' => array('=CODE' => $cityId)))->Fetch();
            else
                $city = \Bitrix\Sale\Location\LocationTable::getById($cityId)->Fetch();

            if(
				$city['TYPE_ID'] != $cityType['ID'] && 
				($onlyCity || !$villageType || $city['TYPE_ID'] != $villageType['ID'])
			){
                $newCityId = false;
                while(!$newCityId){
                    if(empty($city['PARENT_ID']))
                        break;
                    $city = \Bitrix\Sale\Location\LocationTable::getList(array('filter' => array('=ID' => $city['PARENT_ID'])))->Fetch();
                    if($city['TYPE_ID'] == $cityType['ID'])
                        $newCityId = $city['ID'];
                }
            }
            $cityId = $city['ID'];
        }
        return $cityId;
    }

    public static function isLocation20()
    {
        CModule::IncludeModule('sale');
        return (method_exists('CSaleLocation', 'isLocationProMigrated') && CSaleLocation::isLocationProMigrated());
    }

    /**
     * @param $city - bitrixId || cityName
     * @param $mode
     * @return false|string[]
     */
    static function isCityAvail($city, $mode=false){// �������� ����������� �������� � �����
        if(is_numeric($city)){
            $cityId = $city;
            $city = CSaleLocation::GetByID($cityId);
            $cityName = str_replace(GetMessage('IPOLSDEK_LANG_YO_S'),GetMessage('IPOLSDEK_LANG_YE_S'),$city['CITY_NAME']);
        } else {
            $cityName = str_replace(GetMessage('IPOLSDEK_LANG_YO_S'),GetMessage('IPOLSDEK_LANG_YE_S'),$city);
            $city = CSaleLocation::getList(array(),array('CITY_NAME'=>self::zaDEjsonit($city)))->Fetch();
            if($city)
                $cityId = $city['ID'];
        }

        $return = false;
        if($city){
            $arCity = self::getSQLCityBI($cityId);
            if($arCity['SDEK_ID']){
                $return = array('courier');
                if(CDeliverySDEK::checkPVZ($cityName))
                    $return[]='pickup';
            }
        }
        return $return;
    }

    public static function getCity($location,$ifFull = false){ // �������� ����� �� �� �� ��� ���� / id
        if(!$location)
            return false;
        $arCity = self::getSQLCityBI($location);
		if($arCity){
			if($ifFull)
				return $arCity;
			else
				return $arCity['SDEK_ID'];
		}
		return false;
    }

	public static function getSQLCityBI($bitrixID, $skipAPI = false)
	{
        $locationId = self::getNormalCity($bitrixID);
		if (!$bitrixID && !$locationId)
			return false;

		$arCity = sqlSdekCity::getByBId($locationId);

        if (!$arCity && !$skipAPI && is_numeric($locationId) &&
			\Ipolh\SDEK\option::get('autoAddCities') == 'Y') {
			$cityAdder = new sdekCityGetter($locationId, \Ipolh\SDEK\option::get('dostTimeout'));
			$cityAdder->search();
			if ($cityAdder->getSDEK()) {
				$arCity = $cityAdder->getSDEK();
			}
		}

		return $arCity;
	}

    public static function getHomeCity(){ // �������� �����
        return self::getCity(\Ipolh\SDEK\option::get('departure'));
    }

    public static function getCountryCities($countries=false){
        $cities = sqlSdekCity::getCitiesByCountry($countries);

        $arCities = array();
        while($city=$cities->Fetch()){
            if(!$city['COUNTRY'])
                $city['COUNTRY'] = 'rus';
            $city['COUNTRY_NAME'] = GetMessage('IPOLSDEK_SYNCTY_'.$city['COUNTRY']);
            $arCities[] = $city;
        }

        return $arCities;
    }
	
	public static function getCountryCode($country = 'rus'){
		$arCodes = array(
			'rus' => 643,
			'blr' => 112,
			'kaz' => 398
		);
		
		return (array_key_exists($country,$arCodes)) ? $arCodes[$country] : false;
	}

    /*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
                                                        ��������� � ��� ������
            == getListFile ==  == arrVals ==  == isEqualArrs ==  == isLogged ==  == isConverted ==  == isAdmin ==  == getSaleVersion ==  == oIdByShipment ==
    ()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/


    static function getVolumeWeight($length,$width,$height){
        return $length*$width*$height / 5000000;
    }

    static function getListFile($noEnc=false){// ������� ������ �� LIST - ����� � ��� �������, � ������� ���... ������... ����...
        $controller = new \Ipolh\SDEK\Bitrix\Controller\pvzController();
        $arList = $controller->getListFile();

        if(!$noEnc)
            $arList = self::zaDEjsonit($arList);

        return $arList;
    }

    static function isLogged(){
        return \Ipolh\SDEK\option::get('logged');
    }

    static function isConverted(){
        return (\COption::GetOptionString("main","~sale_converted_15",'N') == 'Y');
//        return \Ipolh\SDEK\Bitrix\Tools::isConverted();
    }

    static function isAdminSection()
    {
        $result = false;

        if (class_exists('\\Bitrix\\Main\\Request') && method_exists('\\Bitrix\\Main\\Request','isAdminSection'))
        {
            $request = \Bitrix\Main\Context::getCurrent()->getRequest();
            $result = $request->isAdminSection();
        }
        else
            $result = defined('ADMIN_SECTION') && ADMIN_SECTION === true;

        return ($result || self::isB24Section());
    }

    public static function isB24Section()
    {
        return (defined('SITE_TEMPLATE_ID') && SITE_TEMPLATE_ID === "bitrix24");
    }

    protected static $skipAdminCheck = false;
    static function isAdmin($min = 'W'){
        if(self::$skipAdminCheck) return true;
        $rights = CMain::GetUserRight(self::$MODULE_ID);
        $DEPTH = array('D'=>1,'R'=>2,'W'=>3);
        return($DEPTH[$min] <= $DEPTH[$rights]);
    }

    protected static function getSaleVersion(){
        include($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/install/version.php');
        return $arModuleVersion['VERSION'];
    }
	
	public static function getModuleVersion()
    {
		$moduleObject = CModule::CreateModuleObject(self::$MODULE_ID);

		if(is_object($moduleObject)){
			return $moduleObject->MODULE_VERSION;
		}

        return false; 
    }

    static function getCountryOptions(){
		$result = self::zaDEjsonit(\Ipolh\SDEK\option::get('countries'));
        return (is_array($result)) ? $result : array();
    }

    // �����������
    static function oIdByShipment($shipmentID){
        if(!self::isConverted())
            return false;
        \Bitrix\Main\Loader::includeModule('sale');
        $shipment = self::getShipmentById($shipmentID);
        return $shipment['ORDER_ID'];
    }

    protected static function setShipmentField($shipmentId,$field,$value){
        if(!$shipmentId || !self::isConverted())
            return false;
        $order = \Bitrix\Sale\Order::load(self::oIdByShipment($shipmentId));
        $shipmentCollection = $order->getShipmentCollection();
        $shipment = $shipmentCollection->getItemById($shipmentId);
        $shipment->setField($field,$value);
        $order->save();
        return true;
    }

    static function getShipmentById($shipmentId){
        if(!self::isConverted())
            return false;
        \Bitrix\Main\Loader::includeModule('sale');
        return Bitrix\Sale\Shipment::getList(array('filter'=>array('ID' => $shipmentId)))->Fetch();
    }

    static function canShipment(){
        return (self::isConverted() && \Ipolh\SDEK\option::get('shipments') == 'Y');
    }

	// getting links for editing order in standart & b24
	static function makePathForEditing ($workMode, $workType, $orderID, $shipmentID = false)
	{
		if ($workType == 'standard')
		{
			if ($workMode == 'order')
				return '/bitrix/admin/sale_order_detail.php?ID='.$orderID;
			elseif ($workMode == 'shipment')
				return '/bitrix/admin/sale_order_shipment_edit.php?order_id='.$orderID.'&shipment_id='.$shipmentID;
		}
		elseif ($workType == 'b24')
		{
			if ($workMode == 'order')
				return '/shop/orders/details/'.$orderID.'/';
			elseif ($workMode == 'shipment')
				return '/shop/orders/shipment/details/'.$shipmentID.'/?order_id='.$orderID;
		}
		
		// Unsupported type or mode
		return false;			
	}

    /*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
                                                        SERVICE
            == round2 ==  == arrVals ==  == isEqualArrs ==
    ()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/

    static function round2($wat){
        return floor($wat * 100) / 100;
    }

    static function arrVals($arr){ // ����� ���������
        $return = array();
        foreach($arr as $key => $val)
            if(is_array($val))
                $return = array_merge($return,self::arrVals($val));
            else
                $return []= $val;
        return $return;
    }

    static function isEqualArrs($arr1,$arr2){ // ��� ����� ���������
        foreach($arr1 as $key => $val)
            if(!array_key_exists($key,$arr2) || $arr1[$key] != $arr2[$key])
                return false;
            else
                unset($arr2[$key]);

        if(count($arr2))
            return false;

        return true;
    }


    /*()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()
                                                        LEGACY
            == cntDelivs ==  == defineProto ==
    ()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()()*/

    static function cntDelivs($arOrder){//������ ���� � ��������� �������� ��� �������
        return CDeliverySDEK::countDelivery($arOrder);
    }

    static function defineProto(){
        return (
            !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ||
            $_SERVER['SERVER_PORT'] == 443 ||
            isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] == 'https' ||
            isset($_SERVER['HTTP_X_HTTPS']) && $_SERVER['HTTP_X_HTTPS'] ||
            isset($_SERVER['SERVER_PROTOCOL']) && $_SERVER['SERVER_PROTOCOL'] == 'https'
        ) ? 'https' : 'http';
    }
}

spl_autoload_register(function($className){
	if (strpos($className, 'Ipolh\SDEK') === 0)
	{
		$classPath = implode(DIRECTORY_SEPARATOR, explode('\\', substr($className,11)));

		$filename = __DIR__ . DIRECTORY_SEPARATOR . "classes".DIRECTORY_SEPARATOR."lib" . DIRECTORY_SEPARATOR . $classPath . ".php";

		if (is_readable($filename) && file_exists($filename))
			require_once $filename;
	}
});

CModule::AddAutoloadClasses(
    sdekHelper::$MODULE_ID,
    array(
        'sdekdriver'				 => '/classes/general/sdekclass.php',
        'CDeliverySDEK'				 => '/classes/general/sdekdelivery.php',
        'sdekOption'				 => '/classes/general/sdekoption.php',
        'sdekExport'				 => '/classes/general/sdekexport.php',
        'sqlSdekOrders'				 => '/classes/mysql/sqlSdekOrders.php',
        'sqlSdekCity'				 => '/classes/mysql/sqlSdekCity.php',
        'sqlSdekLogs'				 => '/classes/mysql/sqlSdekLogs.php',
        'CalculatePriceDeliverySdek' => '/classes/sdekMercy/calculator.php',
        'cityExport'				 => '/classes/sdekMercy/syncCityClass.php',
        'sdekCityGetter'			 => '/classes/sdekMercy/getCityClass.php',
        'sdekShipment'				 => '/classes/lib/sdekShipment.php',
        'sdekShipmentCollection'	 => '/classes/lib/sdekShipmentCollection.php',
        '\\Ipolh\\SDEK\\abstractGeneral'      => '/classes/general/abstractGeneral.php',
        '\\Ipolh\\SDEK\\AgentHandler'         => '/classes/general/AgentHandler.php',
        '\\Ipolh\\SDEK\\AuthHandler'          => '/classes/general/AuthHandler.php',
        '\\Ipolh\\SDEK\\CourierCallHandler'   => '/classes/general/CourierCallHandler.php',
		'\\Ipolh\\SDEK\\subscribeHandler'     => '/classes/general/subscribeHandler.php',
        '\\Ipolh\\SDEK\\PointsHandler'        => '/classes/general/PointsHandler.php',
        '\\Ipolh\\SDEK\\PrintHandler'         => '/classes/general/PrintHandler.php',
		'\\Ipolh\\SDEK\\pvzWidjetHandler'     => '/classes/general/pvzWidjetHandler.php',
		'\\Ipolh\\SDEK\\option'               => '/classes/general/option.php',
		'\\Ipolh\\SDEK\\StatusHandler'        => '/classes/general/StatusHandler.php',
        '\\Ipolh\\SDEK\\StoreHandler'         => '/classes/general/StoreHandler.php',

        // DB ORM
        '\\Ipolh\\SDEK\\CourierCallsTable' => '/classes/db/CourierCallsTable.php',
        '\\Ipolh\\SDEK\\StoresTable'       => '/classes/db/StoresTable.php',
        '\\Ipolh\\SDEK\\TableHelpers'      => '/classes/db/TableHelpers.php',
    )
);

// Create security tokens used for AJAX calls
sdekHelper::createModuleToken();
sdekHelper::createWidgetToken();
?>