<?
namespace Rbs\MoyskladStocks\Compitable\Bitrix;

\Bitrix\Main\Loader::includeModule('iblock');

class CIblockElement {

	public static function SetPropertyValues($ELEMENT_ID, $IBLOCK_ID, $PROPERTY_VALUES, $PROPERTY_CODE = false)
	{
		global $DB;
		global $BX_IBLOCK_PROP_CACHE;

		$ELEMENT_ID = (int)$ELEMENT_ID;
		$IBLOCK_ID = (int)$IBLOCK_ID;

		if (!is_array($PROPERTY_VALUES))
			$PROPERTY_VALUES = array($PROPERTY_VALUES);

		$uniq_flt = $IBLOCK_ID;
		$arFilter = array(
			"IBLOCK_ID" => $IBLOCK_ID,
			"CHECK_PERMISSIONS" => "N",
		);

		if ($PROPERTY_CODE === false) {
			$arFilter["ACTIVE"] = "Y";
			$uniq_flt .= "|ACTIVE:" . $arFilter["ACTIVE"];
		} elseif ((int)$PROPERTY_CODE > 0) {
			$arFilter["ID"] = (int)$PROPERTY_CODE;
			$uniq_flt .= "|ID:" . $arFilter["ID"];
		} else {
			$arFilter["CODE"] = $PROPERTY_CODE;
			$uniq_flt .= "|CODE:" . $arFilter["CODE"];
		}

		if (!isset($BX_IBLOCK_PROP_CACHE[$IBLOCK_ID]))
			$BX_IBLOCK_PROP_CACHE[$IBLOCK_ID] = array();

		if (!isset($BX_IBLOCK_PROP_CACHE[$IBLOCK_ID][$uniq_flt])) {
			$BX_IBLOCK_PROP_CACHE[$IBLOCK_ID][$uniq_flt] = array();

			$db_prop = \CIBlockProperty::GetList(array(), $arFilter);
			while ($prop = $db_prop->Fetch())
				$BX_IBLOCK_PROP_CACHE[$IBLOCK_ID][$uniq_flt][$prop["ID"]] = $prop;
			unset($prop);
			unset($db_prop);
		}

		$ar_prop = &$BX_IBLOCK_PROP_CACHE[$IBLOCK_ID][$uniq_flt];
		reset($ar_prop);

		//Read current property values from database
		$arDBProps = array();
		if (\CIBlock::GetArrayByID($IBLOCK_ID, "VERSION") == 2) {
			$rs = $DB->Query("
				select *
				from b_iblock_element_prop_m" . $IBLOCK_ID . "
				where IBLOCK_ELEMENT_ID = " . $ELEMENT_ID . "
				order by ID asc
			");
			while ($ar = $rs->Fetch()) {
				$property_id = $ar["IBLOCK_PROPERTY_ID"];
				if (!isset($arDBProps[$property_id]))
					$arDBProps[$property_id] = array();

				$arDBProps[$property_id][$ar["ID"]] = $ar;
			}
			unset($ar);
			unset($rs);

			$rs = $DB->Query("
				select *
				from b_iblock_element_prop_s" . $IBLOCK_ID . "
				where IBLOCK_ELEMENT_ID = " . $ELEMENT_ID . "
			");
			if ($ar = $rs->Fetch()) {
				foreach ($ar_prop as $property) {
					$property_id = $property["ID"];
					if (
						$property["MULTIPLE"] == "N"
						&& isset($ar["PROPERTY_" . $property_id])
						&& mb_strlen($ar["PROPERTY_" . $property_id])
					) {
						if (!isset($arDBProps[$property_id]))
							$arDBProps[$property_id] = array();

						$arDBProps[$property_id][$ELEMENT_ID . ":" . $property_id] = array(
							"ID" => $ELEMENT_ID . ":" . $property_id,
							"IBLOCK_PROPERTY_ID" => $property_id,
							"VALUE" => $ar["PROPERTY_" . $property_id],
							"DESCRIPTION" => $ar["DESCRIPTION_" . $property_id],
						);
					}
				}
				if (isset($property))
					unset($property);
			} else {
				$DB->Query("
					insert into b_iblock_element_prop_s" . $IBLOCK_ID . "
					(IBLOCK_ELEMENT_ID) values (" . $ELEMENT_ID . ")
				");
			}
			unset($ar);
			unset($rs);
		} else {
			$rs = $DB->Query("
				select *
				from b_iblock_element_property
				where IBLOCK_ELEMENT_ID = " . $ELEMENT_ID . "
				order by ID asc
			");
			while ($ar = $rs->Fetch()) {
				$property_id = $ar["IBLOCK_PROPERTY_ID"];
				if (!isset($arDBProps[$property_id]))
					$arDBProps[$property_id] = array();

				$arDBProps[$property_id][$ar["ID"]] = $ar;
			}
			unset($ar);
			unset($rs);
		}

		foreach (GetModuleEvents("iblock", "OnIBlockElementSetPropertyValues", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ELEMENT_ID, $IBLOCK_ID, $PROPERTY_VALUES, $PROPERTY_CODE, $ar_prop, $arDBProps));
		if (isset($arEvent))
			unset($arEvent);

		$arFilesToDelete = array();
		$arV2ClearCache = array();
		foreach ($ar_prop as $prop) {
			if ($PROPERTY_CODE) {
				$PROP = $PROPERTY_VALUES;
			} else {
				if ($prop["CODE"] <> '' && array_key_exists($prop["CODE"], $PROPERTY_VALUES))
					$PROP = $PROPERTY_VALUES[$prop["CODE"]];
				else
					$PROP = $PROPERTY_VALUES[$prop["ID"]];
			}

			if (
				!is_array($PROP)
				|| ($prop["PROPERTY_TYPE"] == "F"
					&& (array_key_exists("tmp_name", $PROP)
						|| array_key_exists("del", $PROP)
					)
				)
				|| (count($PROP) == 2
					&& array_key_exists("VALUE", $PROP)
					&& array_key_exists("DESCRIPTION", $PROP)
				)
			) {
				$PROP = array($PROP);
			}

			if ($prop["USER_TYPE"] != "") {
				$arUserType = \CIBlockProperty::GetUserType($prop["USER_TYPE"]);
				if (array_key_exists("ConvertToDB", $arUserType)) {
					foreach ($PROP as $key => $value) {
						if (
							!is_array($value)
							|| !array_key_exists("VALUE", $value)
						) {
							$value = array("VALUE" => $value);
						}
						$prop["ELEMENT_ID"] = $ELEMENT_ID;
						$PROP[$key] = call_user_func_array($arUserType["ConvertToDB"], array($prop, $value));
					}
				}
			}

			if ($prop["VERSION"] == 2) {
				if ($prop["MULTIPLE"] == "Y")
					$strTable = "b_iblock_element_prop_m" . $prop["IBLOCK_ID"];
				else
					$strTable = "b_iblock_element_prop_s" . $prop["IBLOCK_ID"];
			} else {
				$strTable = "b_iblock_element_property";
			}

			if ($prop["PROPERTY_TYPE"] == "F") {
				//We'll be adding values from the database into the head
				//for multiple values and into tje tail for single
				//these values were not passed into API call.
				if ($prop["MULTIPLE"] == "Y")
					$orderedPROP = array_reverse($PROP, true);
				else
					$orderedPROP = $PROP;

				if (isset($arDBProps[$prop["ID"]])) {
					//Go from high ID to low
					foreach (array_reverse($arDBProps[$prop["ID"]], true) as $res) {
						//Preserve description from database
						if ($res["DESCRIPTION"] <> '') {
							$description = $res["DESCRIPTION"];
						} else {
							$description = false;
						}

						if (!array_key_exists($res["ID"], $orderedPROP)) {
							$orderedPROP[$res["ID"]] = array(
								"VALUE" => $res["VALUE"],
								"DESCRIPTION" => $description,
							);
						} else {
							$val = $orderedPROP[$res["ID"]];

							if (
								is_array($val)
								&& !array_key_exists("tmp_name", $val)
								&& !array_key_exists("del", $val)
							) {
								$val = $val["VALUE"];
							}

							//Check if no new file and no delete command
							if (
								is_array($val) &&
								!mb_strlen($val["tmp_name"])
								&& !mb_strlen($val["del"])
							) //Overwrite with database value
							{
								//But save description from incoming value
								if (array_key_exists("description", $val))
									$description = trim($val["description"]);
								elseif (
									is_array($orderedPROP[$res["ID"]])
									&& array_key_exists("DESCRIPTION", $orderedPROP[$res["ID"]])
								)
									$description = trim($orderedPROP[$res["ID"]]["DESCRIPTION"]);

								$orderedPROP[$res["ID"]] = array(
									"VALUE" => $res["VALUE"],
									"DESCRIPTION" => $description,
								);
							}
						}
					}
				}

				//Restore original order
				if ($prop["MULTIPLE"] == "Y"){
					$orderedPROP = array_reverse($orderedPROP, true);
				}

				$preserveID = array();
				//Now delete from database all marked for deletion  records
				if (isset($arDBProps[$prop["ID"]])) {
					foreach ($arDBProps[$prop["ID"]] as $res) {
						$val = $orderedPROP[$res["ID"]] ?? null;
						if (
							is_array($val)
							&& !array_key_exists("tmp_name", $val)
							&& !array_key_exists("del", $val)
						) {
							$val = $val["VALUE"];
						}

						if (
							is_array($val)
							&& array_key_exists('del', $val)
							&& mb_strlen($val["del"])
						) {
							unset($orderedPROP[$res["ID"]]);
							$arFilesToDelete[$res["VALUE"]] = array(
								"FILE_ID" => $res["VALUE"],
								"ELEMENT_ID" => $ELEMENT_ID,
								"IBLOCK_ID" => $prop["IBLOCK_ID"],
							);
						} elseif (
							$prop["MULTIPLE"] != "Y"
							|| (is_array($val) && isset($val["tmp_name"]) && $val["tmp_name"] != ''
							)
						) {
							//Delete all stored in database for replacement.
							$arFilesToDelete[$res["VALUE"]] = array(
								"FILE_ID" => $res["VALUE"],
								"ELEMENT_ID" => $ELEMENT_ID,
								"IBLOCK_ID" => $prop["IBLOCK_ID"],
							);
						}

						if ($prop["VERSION"] == 2 && $prop["MULTIPLE"] == "N") {
							$DB->Query("
								UPDATE b_iblock_element_prop_s" . $prop["IBLOCK_ID"] . "
								SET PROPERTY_" . $prop["ID"] . " = null
								" . self::__GetDescriptionUpdateSql($prop["IBLOCK_ID"], $prop["ID"]) . "
								WHERE IBLOCK_ELEMENT_ID = " . $ELEMENT_ID . "
							");
						} else {
							$DB->Query("DELETE FROM " . $strTable . " WHERE ID = " . $res["ID"]);
							$preserveID[$res["ID"]] = $res["ID"];
						}

						if ($prop["VERSION"] == 2 && $prop["MULTIPLE"] == "Y") {
							$arV2ClearCache[$prop["ID"]] =
								"PROPERTY_" . $prop["ID"] . " = NULL"
								. self::__GetDescriptionUpdateSql($prop["IBLOCK_ID"], $prop["ID"]);
						}
					} //foreach($arDBProps[$prop["ID"]] as $res)
				}

				//Write new values into database in specified order
				foreach ($orderedPROP as $propertyValueId => $val) {
					if (
						is_array($val)
						&& !array_key_exists("tmp_name", $val)
					) {
						$val_desc = $val["DESCRIPTION"] ?? '';
						$val = $val["VALUE"];
					} else {
						$val_desc = false;
					}

					if (is_array($val)) {
						$val["MODULE_ID"] = "iblock";
						if ($val_desc !== false)
							$val["description"] = $val_desc;

						$val = \CFile::SaveFile($val, "iblock");
					} elseif (
						$val > 0
						&& $val_desc !== false
					) {
						\CFile::UpdateDesc($val, $val_desc);
					}

					if (intval($val) <= 0){
						continue;
					}

					if ($prop["VERSION"] == 2 && $prop["MULTIPLE"] == "N") {
						$DB->Query($s = "
							UPDATE b_iblock_element_prop_s" . $prop["IBLOCK_ID"] . "
							SET
								PROPERTY_" . $prop["ID"] . " = '" . $DB->ForSql($val) . "'
								" . self::__GetDescriptionUpdateSql($prop["IBLOCK_ID"], $prop["ID"], $val_desc) . "
							WHERE IBLOCK_ELEMENT_ID=" . $ELEMENT_ID . "
						");
					} elseif ($preserveID) {
						$DB->Query("
							INSERT INTO " . $strTable . "
							(ID, IBLOCK_ELEMENT_ID, IBLOCK_PROPERTY_ID, VALUE, VALUE_NUM" . ($val_desc !== false ? ", DESCRIPTION" : "") . ")
							SELECT
								" . array_shift($preserveID) . "
								," . $ELEMENT_ID . "
								,P.ID
								,'" . $DB->ForSql($val) . "'
								," . \CIBlock::roundDB($val) . "
								" . ($val_desc !== false ? ", '" . $DB->ForSQL($val_desc, 255) . "'" : "") . "
							FROM
								b_iblock_property P
							WHERE
								ID = " . intval($prop["ID"]) . "
						");
					} else {
						$DB->Query("
							INSERT INTO " . $strTable . "
							(IBLOCK_ELEMENT_ID, IBLOCK_PROPERTY_ID, VALUE, VALUE_NUM" . ($val_desc !== false ? ", DESCRIPTION" : "") . ")
							SELECT
								" . $ELEMENT_ID . "
								,P.ID
								,'" . $DB->ForSql($val) . "'
								," . \CIBlock::roundDB($val) . "
								" . ($val_desc !== false ? ", '" . $DB->ForSQL($val_desc, 255) . "'" : "") . "
							FROM
								b_iblock_property P
							WHERE
								ID = " . intval($prop["ID"]) . "
						");
					}

					if ($prop["VERSION"] == 2 && $prop["MULTIPLE"] == "Y") {
						$arV2ClearCache[$prop["ID"]] =
							"PROPERTY_" . $prop["ID"] . " = NULL"
							. self::__GetDescriptionUpdateSql($prop["IBLOCK_ID"], $prop["ID"]);
					}

					if ($prop["MULTIPLE"] != "Y")
						break;
				} //foreach($PROP as $value)
			}
		}

		if ($arV2ClearCache) {
			$DB->Query("
				UPDATE b_iblock_element_prop_s" . $IBLOCK_ID . "
				SET " . implode(",", $arV2ClearCache) . "
				WHERE IBLOCK_ELEMENT_ID = " . $ELEMENT_ID . "
			");
		}

		foreach ($arFilesToDelete as $deleteTask) {
			\CIBlockElement::DeleteFile(
				$deleteTask["FILE_ID"],
				false,
				"PROPERTY",
				$deleteTask["ELEMENT_ID"],
				$deleteTask["IBLOCK_ID"]
			);
		}

		/****************************** QUOTA ******************************/
		\CDiskQuota::recalculateDb();
		/****************************** QUOTA ******************************/

		foreach (GetModuleEvents("iblock", "OnAfterIBlockElementSetPropertyValues", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ELEMENT_ID, $IBLOCK_ID, $PROPERTY_VALUES, $PROPERTY_CODE));
	}

	protected static function __GetDescriptionUpdateSql($iblock_id, $property_id, $description = false)
	{
		global $DB;
		$tableFields = $DB->GetTableFields("b_iblock_element_prop_s" . $iblock_id);
		if (isset($tableFields["DESCRIPTION_" . $property_id])) {
			if ($description !== false)
				$sqlValue = "'" . $DB->ForSQL($description, 255) . "'";
			else
				$sqlValue = "null";
			return ", DESCRIPTION_" . $property_id . "=" . $sqlValue;
		} else {
			return "";
		}
	}

}