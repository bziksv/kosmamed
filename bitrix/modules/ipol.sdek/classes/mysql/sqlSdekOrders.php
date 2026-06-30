<?php
class sqlSdekOrders
{
	public function toLog($wat,$sign){sdekHelper::toLog($wat,$sign);}
	private static $tableName = "ipol_sdek";
	public static function Add($Data)
    {
        // = $Data = format:
		// PARAMS - ALL INFO
		// ORDER_ID - corresponding order
		// STATUS - response from iml
		// MESSAGE - info from server
		// OK - 0 / 1 - was confirmed
		// UPTIME - order add time
		
		global $DB;
        
		if(!$Data['STATUS'])
			$Data['STATUS']='NEW';
		if($Data['STATUS']=='NEW')
			$Data['MESSAGE']='';
		if(is_array($Data['PARAMS'])) {
			$Data['PARAMS'] = serialize($Data['PARAMS']);
		}
		
		$Data['UPTIME']= time();
			
		$rec = self::CheckRecord($Data['ORDER_ID'],$Data['SOURCE']);
		if($rec)
		{
			$err_mess = "";
			$strUpdate = $DB->PrepareUpdate(self::$tableName, $Data);
			$strSql = "UPDATE ".self::$tableName." SET ".$strUpdate." WHERE ID=".$rec['ID'];
			$DB->Query($strSql, false, $err_mess.__LINE__);
		}
		else
		{
			$arInsert = $DB->PrepareInsert(self::$tableName, $Data);
			$strSql =
				"INSERT INTO ".self::$tableName."(".$arInsert[0].") ".
				"VALUES(".$arInsert[1].")";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		return self::CheckRecord($Data['ORDER_ID'],$Data['SOURCE']); 
    }

    public static function select($arOrder = array("ID", "DESC"), $arFilter = array(), $arNavStartParams = array())
    {
        global $DB;

        if (!$arFilter) {
            $arFilter = array();
        }
        $possFields = self::getPossFilterFields();
        $_arFilter = $arFilter;
        $arFilter = array();
        foreach ($_arFilter as $key => $val) {
            $field = self::checkFilterField($key, $possFields);
            if ($field) {
                $skip = false;
                switch ($possFields[$field]) {
                    case 'int'    :
                        if (!is_array($val)) {
                            $val = (int)$val;
                        } else {
                            foreach ($val as $vKey => $vData) {
                                $val[$vKey] = (int)$vData;
                            }
                        }
                        break;
                    case 'string' :
                        if (!is_array($val)) {
                            $val = (string)$val;
                            if (strpos($val, '(') !== false) {
                                $skip = true;
                            }
                        } else {
                            foreach ($val as $vKey => $vData) {
                                $val[$vKey] = (string)$vData;
                                if (strpos($val[$vKey], '(') !== false) {
                                    unset($val[$vKey]);
                                }
                            }
                            if (!count($val)) {
                                $skip = true;
                            }
                        }
                        break;
                }
                if (!$skip) {
                    $arFilter[$key] = $val;
                }
            }
        }

        $strSql = '';

        $where = '';
        if (!empty($arFilter['>=UPTIME']) && strpos($arFilter['>=UPTIME'], ".") !== false)
            $arFilter['>=UPTIME'] = strtotime($arFilter['>=UPTIME']);
        if (!empty($arFilter['<=UPTIME']) && strpos($arFilter['<=UPTIME'], ".") !== false)
            $arFilter['<=UPTIME'] = strtotime($arFilter['<=UPTIME']);

        if (is_array($arFilter) && count($arFilter) > 0)
            foreach ($arFilter as $field => $value) {
                if ($field == 'SOURCE' && $value == 0)
                    $where .= ' and ' . $DB->ForSql(self::getSource('order'));
                else {
                    if (strpos($field, '!') !== false)
                        $where .= ' and ' . $DB->ForSql(substr($field, 1)) . ' != "' . $DB->ForSql($value) . '"';
                    elseif (strpos($field, '<=') !== false)
                        $where .= ' and ' . $DB->ForSql(substr($field, 2)) . ' <= "' . $DB->ForSql($value) . '"';
                    elseif (strpos($field, '>=') !== false)
                        $where .= ' and ' . $DB->ForSql(substr($field, 2)) . ' >= "' . $DB->ForSql($value) . '"';
                    elseif (strpos($field, '>') !== false)
                        $where .= ' and ' . $DB->ForSql(substr($field, 1)) . ' > "' . $DB->ForSql($value) . '"';
                    elseif (strpos($field, '<') !== false)
                        $where .= ' and ' . $DB->ForSql(substr($field, 1)) . ' < "' . $DB->ForSql($value) . '"';
                    else {
                        if (is_array($value)) {
                            $where .= ' and (';
                            foreach ($value as $val)
                                $where .= $DB->ForSql($field) . ' = "' . $DB->ForSql($val) . '" or ';
                            $where = substr($where, 0, strlen($where) - 4) . ")";
                        } else
                            $where .= ' and ' . $DB->ForSql($field) . ' = "' . $DB->ForSql($value) . '"';
                    }
                }
            }
        if ($where)
            $strSql .= "
			WHERE " . substr($where, 4);

        if (!empty($arOrder) && in_array($arOrder[0], array('ID', 'ORDER_ID', 'STATUS', 'UPTIME')) && ($arOrder[1] == 'ASC' || $arOrder[1] == 'DESC'))
            $strSql .= "
			ORDER BY " . $arOrder[0] . " " . $arOrder[1];

        $err_mess = "";
        $cnt = $DB->Query("SELECT COUNT(*) as C FROM " . self::$tableName . " " . $strSql, false, $err_mess . __LINE__)->Fetch();

        if (!array_key_exists('nPageSize', $arNavStartParams) && $arNavStartParams['nPageSize'] == 0)
            $arNavStartParams['nPageSize'] = $cnt['C'];

        $strSql = "SELECT * FROM " . self::$tableName . " " . $strSql;

        $res = new CDBResult();
        $res->NavQuery($strSql, $cnt['C'], $arNavStartParams);

        return $res;
    }
		
	public static function Delete($orderId,$mode='order'){
		global $DB;
		$orderId = $DB->ForSql($orderId);
		$strSql =
            "DELETE FROM ".self::$tableName." 
            WHERE ORDER_ID='".$orderId."' && ".self::getSource($mode);
		$DB->Query($strSql, true);
        
        return true; 
    }
	
	public static function GetByOI($orderId){
		global $DB;
		$orderId=$DB->ForSql($orderId);
		$strSql =
            "SELECT PARAMS, STATUS, SDEK_ID, MESSAGE, OK, MESS_ID, ORDER_ID, ACCOUNT, SDEK_UID ".
            "FROM ".self::$tableName." ".
			"WHERE ORDER_ID = '".$orderId."'  && ".self::getSource('order');
		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if($arr = $res->Fetch())
			return $arr;
		else return false;
	}

	public static function GetBySI($shipmentId){
		global $DB;
		$shipmentId=$DB->ForSql($shipmentId);
		$strSql =
            "SELECT PARAMS, STATUS, SDEK_ID, MESSAGE, OK, MESS_ID, ORDER_ID, ACCOUNT, SDEK_UID ".
            "FROM ".self::$tableName." ".
			"WHERE ORDER_ID = '".$shipmentId."'  && ".self::getSource('shipment');
		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if($arr = $res->Fetch())
			return $arr;
		else return false;
	}

    public static function GetByUId($uid){
        global $DB;
        $strSql =
            "SELECT PARAMS, STATUS, SDEK_ID, MESSAGE, OK, MESS_ID, ORDER_ID, ACCOUNT, SDEK_UID, SOURCE ".
            "FROM ".self::$tableName." ".
            "WHERE SDEK_UID = '".$uid."'";
        $res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
        if($arr = $res->Fetch())
            return $arr;
        else return false;
    }
	
	public static function CheckRecord($orderId,$mode=0){
		global $DB;

		$source = (is_numeric($mode)) ? "SOURCE = '".$mode."'" : self::getSource($mode);
		
		$orderId = $DB->ForSql($orderId);
        $strSql =
            "SELECT ID, STATUS ".
            "FROM ".self::$tableName." ".
			"WHERE ORDER_ID = '".$orderId."' && ".$source;
		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if($res && $arr = $res->Fetch())
			return $arr;
		return false;
	}

	public static function updateStatus($arParams){
		global $DB;
		foreach($arParams as $key => $val)
			$arParams[$key] = $DB->ForSql($val);

		$okStat='';
		if($arParams["STATUS"]=='OK')
			$okStat=" OK='1',";
		elseif($arParams["STATUS"]=='DELETE')
			$okStat=" OK='',";

		$setStr = "STATUS ='".$arParams["STATUS"]."', MESSAGE = '".$arParams["MESSAGE"]."',";
		if (isset($arParams["SDEK_ID"]) && $arParams["SDEK_ID"])
			$setStr.="SDEK_ID = '".$arParams["SDEK_ID"]."',";
        if (isset($arParams["SDEK_UID"]) && $arParams["SDEK_UID"])
            $setStr.="SDEK_UID = '".$arParams["SDEK_UID"]."',";
		if (isset($arParams["MESS_ID"]) && $arParams["MESS_ID"])
			$setStr.="MESS_ID = '".$arParams["MESS_ID"]."',";
		if (isset($arParams["ACCOUNT"]) && $arParams["ACCOUNT"])
			$setStr.="ACCOUNT = '".$arParams["ACCOUNT"]."',";

		$setStr.=$okStat." UPTIME= '". time() ."'";

		if(array_key_exists('SOURCE',$arParams) && $arParams['SOURCE'])
			$source = "SOURCE = '".$arParams['SOURCE']."'";
		elseif(array_key_exists('SOURCE',$arParams) && $arParams['SOURCE'] === '')
			$source = "SOURCE <=> NULL";
		elseif(array_key_exists('mode',$arParams))
			$source = self::getSource($arParams['mode']);
		else
			$source = "SOURCE = 0";

		$strSql =
            "UPDATE ".self::$tableName." 
			SET ".$setStr."
			WHERE ORDER_ID = '".$arParams["ORDER_ID"]."' && $source";

		if($DB->Query($strSql, true))
			return true;
		else 
			return false;
	}

    /**
     * Return number of rows with some data
     * @return int
     */
    public static function getDataCount()
    {
        global $DB;
        $count = 0;

        $dbResult = $DB->Query("SELECT COUNT(*) as COUNT FROM ".self::$tableName, true);
        if ($dbResult && ($tmp = $dbResult->fetch())) {
            $count = $tmp['COUNT'];
        }

        return $count;
    }

	private static function getSource($mode='order'){
		return ($mode == 'order' || $mode == '') ? '(SOURCE <=> NULL || SOURCE = 0)' : "SOURCE = '1'";
	}

	protected static function getPossFilterFields()
    {
        return array("ID" => 'int',"MESS_ID" => 'int',"PARAMS" => 'text',"ORDER_ID"=>'int',"SOURCE"=>'int',"SDEK_ID"=>'string',"STATUS"=>'string',"MESSAGE"=> 'text',"ACCOUNT"=>'int',"OK"=>'string',"UPTIME"=>'string','SDEK_UID'=>'string');
    }

    protected static function checkFilterField($field,$filterFields)
    {
        $arKeys = array_keys($filterFields);
        foreach($arKeys as $_field){
            if(strpos($field,$_field) !== false){
                return $_field;
            }
        }
        return false;
    }
}
?>