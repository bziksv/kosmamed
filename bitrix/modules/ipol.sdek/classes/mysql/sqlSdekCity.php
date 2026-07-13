<?php
	class sqlSdekCity{
		public static $tableName = 'ipol_sdekcities';

        /**
         * Adds or updates a location to a table
         * @param array $city
         * @return bool
         */
        public static function Add($city)
        {
            $dbCity = self::getByBId($city['BITRIX_ID']) ?: null;

            if (is_null($dbCity)) {
                self::_add($city);
                return true;
            } elseif ($dbCity['IS_UPDATABLE'] === 'Y') {
                self::update($city);
                return false;
            }
            return false;
        }

        /**
         * Updates a location in a table
         * @param array $city
         * @return void
         */
        public static function update($city)
        {
            global $DB;
            $tableName = self::$tableName;

            $update = $DB->PrepareUpdate(self::$tableName, $city);
            $query = "UPDATE {$tableName} SET {$update} WHERE BITRIX_ID = {$city['BITRIX_ID']}";
            $DB->Query($query, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
        }

		public static function _add($DATA){
			global $DB;
			$arInsert = $DB->PrepareInsert(self::$tableName, $DATA);
			$strSql =
				"INSERT INTO ".self::$tableName."(".$arInsert[0].") ".
				"VALUES(".$arInsert[1].")";
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}
		
		public static function Delete($id){
			global $DB;
			$id = $DB->ForSql($id);
			$strSql =
				"DELETE FROM ".self::$tableName."
				WHERE ID='".$id."'";
			$DB->Query($strSql, true);
			return true; 
		}

        /**
         * Returns the location in the table with the specified Bitrix ID
         * @param $bitrixId
         * @return array|null
         */
        public static function getByBId($bitrixId)
        {
            global $DB;
            $query = "SELECT * FROM ".self::$tableName." WHERE BITRIX_ID = '".$DB->ForSql($bitrixId)."'";
            $result = $DB->Query($query, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
            return $result->Fetch();
        }
		
		public static function getBySId($sid){
			global $DB;
			$sid = $DB->ForSql($sid);
			$strSql =
				"SELECT * ".
				"FROM ".self::$tableName." ".
				"WHERE SDEK_ID = '".$sid."'";
			$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			return $res->Fetch();
		}
		
		public static function getCityPM($sid,$mode='SDEK_ID'){
			if($mode != 'SDEK_ID' && $mode != 'BITRIX_ID')
				return false;
			global $DB;
			$sid = $DB->ForSql($sid);
			$strSql =
				"SELECT PAYNAL ".
				"FROM ".self::$tableName." ".
				"WHERE $mode = '".$sid."'";
			$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__)->Fetch();
			if($res){
				if($res['PAYNAL'] == '')
					$res = true;
				else{
					switch($res['PAYNAL']){
						case 'no limit': $res = true; break;
						case '0.00': $res = false; break;
						default: $res = $res['PAYNAL']; break;
					}
				}
			}
			
			return $res;
		}
		
		public static function select($arOrder=array("ID","DESC"),$arFilter=array(),$arNavStartParams=array())
		{
			global $DB;

			$strSql='';

			$where='';
			if(array_key_exists('>=UPTIME', $arFilter) && strpos($arFilter['>=UPTIME'],".") !== false)
				$arFilter['>=UPTIME']=strtotime($arFilter['>=UPTIME']);
			if(array_key_exists('<=UPTIME', $arFilter) && strpos($arFilter['<=UPTIME'], ".") !== false)
				$arFilter['<=UPTIME']=strtotime($arFilter['<=UPTIME']);

			if(count($arFilter)>0)
				foreach($arFilter as $field => $value)
				{
					if(strpos($field,'!')!==false)
						$where.=' and '.substr($field,1).' != "'.$value.'"';
					elseif(strpos($field,'<=')!==false)
						$where.=' and '.substr($field,2).' <= "'.$value.'"';				
					elseif(strpos($field,'>=')!==false)
						$where.=' and '.substr($field,2).' >= "'.$value.'"';
					elseif(strpos($field,'>')!==false)
						$where.=' and '.substr($field,1).' > "'.$value.'"';				
					elseif(strpos($field,'<')!==false)
						$where.=' and '.substr($field,1).' < "'.$value.'"';
					else
					{
						if(is_array($value))
						{
							$where.=' and (';
							foreach($value as $val)
								$where.=$field.' = "'.$val.'" or ';
							$where=substr($where,0,strlen($where)-4).")";
						}
						else
							$where.=' and '.$field.' = "'.$value.'"';
					}
				}
			if($where) 
				$strSql.="
				WHERE ".substr($where,4);

			if(in_array($arOrder[0],array('ID','BITRIX_ID','SDEK_ID','NAME','REGION'))&&($arOrder[1]=='ASC'||$arOrder[1]=='DESC'))
				$strSql.="
				ORDER BY ".$arOrder[0]." ".$arOrder[1];

			$err_mess = "";
			$cnt=$DB->Query("SELECT COUNT(*) as C FROM ".self::$tableName." ".$strSql, false, $err_mess.__LINE__)->Fetch();

            if(!array_key_exists('nPageSize', $arNavStartParams) || $arNavStartParams['nPageSize'] === 0)
            {
                $arNavStartParams['nPageSize'] = $cnt['C'];
            }

			$strSql="SELECT * FROM ".self::$tableName." ".$strSql;

			$res = new CDBResult();
			$res->NavQuery($strSql,$cnt['C'],$arNavStartParams);

			return $res;
		}

		public static function getCitiesByCountry($country=false,$doNav=false){
			global $DB;
			if($country){
				if(is_array($country)){
					$where = 'WHERE ';
					foreach($country as $country)
						if($country == 'rus')
							$where .= 'COUNTRY = "rus" or COUNTRY <=> NULL or ';
						else
							$where .= 'COUNTRY = "'.$country.'" or ';
					$where = substr($where,0,strlen($where)-3);
				}else{
					if($country == 'rus')
						$where = 'WHERE COUNTRY = "rus" or COUNTRY <=> NULL';
					else
						$where = 'WHERE COUNTRY = "'.$country.'"';
				}
			}else
				$where = '';

			$err_mess = "";
			if($doNav){
				$cnt=$DB->Query("SELECT COUNT(*) as C FROM ".self::$tableName." ".$where, false, $err_mess.__LINE__)->Fetch();
				$req = new CDBResult();
				$req->NavQuery("SELECT * FROM ".self::$tableName." ".$where." ORDER BY REGION ASC",$cnt['C'],array('nPageSize'=>$cnt['C']));
			}else
				$req=$DB->Query("SELECT * FROM ".self::$tableName." ".$where." ORDER BY REGION ASC", false, $err_mess.__LINE__);

			return $req;
		}
	}
?>