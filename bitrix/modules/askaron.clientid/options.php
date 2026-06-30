<?
###################################################
# askaron.clientid module
# Copyright (c) 2011-2022 Askaron Systems ltd.
# http://askaron.ru
# mailto:mail@askaron.ru
###################################################

use Bitrix\Main\Config\Option;


IncludeModuleLangFile(__FILE__);
IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"] . BX_ROOT . "/modules/main/options.php");
require_once("prolog.php");

$module_id = "askaron.clientid";
$install_status = CModule::IncludeModuleEx($module_id);

//$arValuesUserConsent = [
//	"" => GetMessage("ASKARON_CLIENTID_OPTION_NOT_SELECTED"),
//];
//
//if (version_compare(SM_VERSION, "14.0.0") >= 0)
//{
//	$list = \Bitrix\Main\UserConsent\Internals\AgreementTable::getList([
//		'select' => ['ID', 'NAME'],
//		'filter' => ['=ACTIVE' => 'Y'],
//		'order' => ['ID' => 'ASC'],
//	]);
//	foreach ($list as $item)
//	{
//		$arValuesUserConsent[$item['ID']] = "[" . $item["ID"] . "] " . $item['NAME'];
//	}
//}

$aTabs = [];
$rsSites = \CSite::GetList($by = "sort", $order = "desc", []);
while ($arSite = $rsSites->Fetch())
{
	array_push($aTabs, ["DIV" => "edit" . $arSite['LID'], "TAB" => GetMessage("MAIN_TAB_SET") . ' ' . $arSite['NAME'], "ICON" => "", "TITLE" => GetMessage("MAIN_TAB_TITLE_SET"), 'LID' => $arSite['LID']]);
}
array_push($aTabs, ["DIV" => "edit2", 'COMMON_TAB' => true, "TAB" => GetMessage("ASKARON_CLIENTID_COMMON_SETTINGS"), "ICON" => "", "TITLE" => GetMessage("ASKARON_CLIENTID_COMMON_SETTINGSà")]);
array_push($aTabs, ["DIV" => "edit3", "TAB" => GetMessage("MAIN_TAB_GID"), "ICON" => "", "TITLE" => GetMessage("MAIN_TAB_GID")]);
$arGroups = [
	"group1" => [
		"NAME" => GetMessage("ASKARON_CLIENTID_COMMON_SETTINGS"),
		'COMMON' => true,
	],
	"group2" => [
		"NAME" => GetMessage("ASKARON_CLIENTID_GOALS_SETTINGS"),
	],
];

$arOptions = [
	"consider_user_authorize" => [
		"CODE" => 'consider_user_authorize',
		"COMMON" => 'Y',
		"NAME" => GetMessage("ASKARON_CLIENTID_OPTION_CONSIDER_USER_AUTH"),
		"TYPE" => "CHECKBOX",
		"HELP" => GetMessage("ASKARON_CLIENTID_OPTION_CONSIDER_USER_AUTH_HELP"),
		"GROUP" => "group1",
	],
	"ym_token" => [
		"CODE" => 'ym_token',
		"COMMON" => 'N',
		"NAME" => GetMessage("ASKARON_CLIENTID_OPTION_YM_OAUTH_TOKEN"),
		"TYPE" => "TEXT",
		"HELP" => GetMessage("ASKARON_CLIENTID_OPTION_YM_OAUTH_TOKEN_HELP"),
		"GROUP" => "group2",
	],
	"ym_counter" => [
		"CODE" => 'ym_counter',
		"COMMON" => 'N',
		"NAME" => GetMessage("ASKARON_CLIENTID_OPTION_YM_COUNTER_ID"),
		"TYPE" => "TEXT",
		"HELP" => GetMessage("ASKARON_CLIENTID_OPTION_YM_COUNTER_ID_HELP"),
		"GROUP" => "group2",
	],
	"ym_new_order" => [
		"CODE" => 'ym_new_order',
		"COMMON" => 'N',
		"NAME" => GetMessage("ASKARON_CLIENTID_OPTION_YM_NEW_ORDER_GOAL"),
		"TYPE" => "TEXT",
		"HELP" => '',
		"GROUP" => "group2",
	],
];


if ($install_status == 0)
{
	// module not found (0)
}
else if ($install_status == 3)
{
	//demo expired (3)
	CAdminMessage::ShowMessage(
		[
			"TYPE" => "ERROR",
			"MESSAGE" => GetMessage("askaron_clientid_prolog_status_demo_expired"),
			"DETAILS" => GetMessage("askaron_clientid_prolog_buy_html"),
			"HTML" => true,
		]
	);
}
else
{

	$RIGHT = $APPLICATION->GetGroupRight($module_id);
	$RIGHT_W = ($RIGHT >= "W");
	$RIGHT_R = ($RIGHT >= "R");
	if ($RIGHT_R)
	{
		$arErrors = [];
		$arSettings = [];

		if (
			$_SERVER['REQUEST_METHOD'] == "POST"
			&& mb_strlen($_REQUEST["Update"]) > 0
			&& $RIGHT_W
			&& check_bitrix_sessid()
		)
		{
			// Update all options
			foreach ($aTabs as $tabItem)
			{
				foreach ($arOptions as $key => $arOption)
				{
					$arOption['COMMON'] == 'Y' ? $optionSiteSuffix = '' : $optionSiteSuffix = $tabItem['LID'];

					if ($arOption["TYPE"] == "CHECKBOX")
					{
						if (isset($_REQUEST["arrOptions"][$key . $optionSiteSuffix]) && $_REQUEST["arrOptions"][$key . $optionSiteSuffix] == "Y")
						{
							$res = Option::set($module_id, $key . $optionSiteSuffix, "Y");
						}
						else
						{
							Option::set($module_id, $key . $optionSiteSuffix, "N");
						}
					}

					if ($arOption["TYPE"] == "TEXT")
					{
						if (isset($_REQUEST["arrOptions"][$key . $optionSiteSuffix]))
						{
							$res = Option::set($module_id, $key . $optionSiteSuffix, $_REQUEST["arrOptions"][$key . $optionSiteSuffix]);
						}
					}

					if ($arOption["TYPE"] == "INTEGER")
					{
						if (isset($_REQUEST["arrOptions"][$key . $optionSiteSuffix]))
						{
							if (mb_strlen($_REQUEST["arrOptions"][$key . $optionSiteSuffix]) > 0)
							{
								$val = intval($_REQUEST["arrOptions"][$key . $optionSiteSuffix]);
								$min = $arOption["MIN"];

								if (mb_strlen($min) > 0 && $val < $min)
								{
									$val = $min;
								}

								Option::set($module_id, $key . $optionSiteSuffix, $val);
							}
						}
					}

					if ($arOption["TYPE"] == "LIST")
					{
						if ($arOption["MULTIPLE"] == "N")
						{
//						if ( isset( $_REQUEST["arrOptions"][ $key ] ) )
//						{
//							$arOptions[$key][ "VALUE" ] = $_REQUEST["arrOptions"][ $key ];
//						}
//						else
//						{
//							$arOptions[$key][ "VALUE" ] = array();
//						}
						}
						else
						{
							if (isset($_REQUEST["arrOptions"][$key]))
							{
								Option::set($module_id, $key, $_REQUEST["arrOptions"][$key]);
							}
						}
					}
				}
			}
		}

		if (
			$_SERVER['REQUEST_METHOD'] == "POST"
			&& $RIGHT_W
			&& mb_strlen($_REQUEST["RestoreDefaults"]) > 0
			&& check_bitrix_sessid()
		)
		{
			COption::RemoveOption($module_id);
			$z = CGroup::GetList($v1 = "id", $v2 = "asc", ["ACTIVE" => "Y", "ADMIN" => "N"], $get_users_amount = "N");
			while ($zr = $z->Fetch())
			{
				$APPLICATION->DelGroupRight($module_id, [$zr["ID"]]);
			}
		}

		$commonOptions = [];
		// init all options:
		$arDisplayOptions = [];
		foreach ($aTabs as $tabItem)
		{
			foreach ($arOptions as $key => $arOption)
			{
				$arOption['COMMON'] == 'Y' ? $optionSiteSuffix = '' : $optionSiteSuffix = $tabItem['LID'];

				$arOptionAdd = $arOption;
				$arOption['COMMON'] == 'Y' ? $optionSiteSuffix = '' : $optionSiteSuffix = $tabItem['LID'];
				$arOptionAdd["INPUT_ID"] = "option_" . $key . $optionSiteSuffix;
				$arOptionAdd["INPUT_NAME"] = "arrOptions[" . $key . $optionSiteSuffix . "]";
				$arOptionAdd["~INPUT_VALUE"] = Option::get($module_id, $key . $optionSiteSuffix);
				$arOptionAdd["INPUT_VALUE"] = htmlspecialcharsbx(Option::get($module_id, $key . $optionSiteSuffix));

				$arDisplayOptions[$tabItem['LID']][$key . $optionSiteSuffix] = $arOptionAdd;
			}
		}

		foreach ($arGroups as $group_key => $arGroup)
		{
			$arGroups[$group_key]["~NAME"] = $arGroup["NAME"];
			$arGroups[$group_key]["NAME"] = htmlspecialcharsbx($arGroup["NAME"]);
		}


//		if ( count( $arErrors ) > 0 )
//		{
//			CAdminMessage::ShowMessage(
//				Array(
//					"TYPE"=>"ERROR",
//					"MESSAGE" => GetMessage("askaron_clientid_error_save_header"),
//					"DETAILS"=> implode( "<br />", $arErrors ),
//					"HTML"=>true
//				)
//			);
//		}

		//demo (2)
		if ($install_status == 2)
		{
			CAdminMessage::ShowMessage(
				[
					"TYPE" => "OK",
					"MESSAGE" => GetMessage("askaron_clientid_prolog_status_demo"),
					"DETAILS" => GetMessage("askaron_clientid_prolog_buy_html"),
					"HTML" => true,
				]
			);
		}

		if(function_exists('curl_init') === false)
		{
			CAdminMessage::ShowMessage(
				Array(
					"TYPE" => "ERROR",
					"MESSAGE" => GetMessage("ASKARON_CLIENTID_EXTENSION_NOT_INSTALLED"),
					"DETAILS" => GetMessage("ASKARON_CLIENTID_INSTALL_EXTENSION"),
					"HTML" => true
				)
			);
		}


		$tabControl = new CAdminTabControl("tabControl", $aTabs);
		$tabControl->Begin(); ?>


        <form method="post"
              action="<? echo $APPLICATION->GetCurPage() ?>?mid=<?= htmlspecialcharsbx($mid) ?>&lang=<?= LANGUAGE_ID ?>">

			<? foreach ($aTabs as $tabItem):
				if ($tabItem['TAB'] === GetMessage("MAIN_TAB_GID"))
				{
					break;
				}
				$tabControl->BeginNextTab(); ?>

				<?= bitrix_sessid_post() ?>

				<? foreach ($arGroups as $group_key => $arGroup): ?>
				<? if (($arGroup['COMMON'] && !$tabItem['COMMON_TAB'])||(!$arGroup['COMMON'] && $tabItem['COMMON_TAB']))
				{
					continue;
				} ?>
                <tr class="heading">
                    <td valign="top" colspan="2" align="center"><?= $arGroup["NAME"] ?></td>
                </tr>
				<? foreach ($arDisplayOptions[$tabItem['LID']] as $key => $arInput): ?>

					<? if ($group_key == $arInput["GROUP"]): ?>
                        <tr>
                            <td valign="top" width="50%" class="field-name"><label
                                        for="<?= $arInput["INPUT_ID"] ?>"><?= $arInput["NAME"] ?></label></td>
                            <td valign="top" width="50%">
								<? if ($arInput["TYPE"] == "CHECKBOX"): ?>
                                    <input
                                            type="checkbox"
                                            value="Y"
                                            id="<?= $arInput["INPUT_ID"] ?>"
										<? if ($arInput["INPUT_VALUE"] == "Y"): ?>
                                            checked="checked"
										<? endif ?>
                                            name="<?= $arInput["INPUT_NAME"] ?>"
                                    />
								<? endif ?>

								<? if ($arInput["TYPE"] == "TEXT" || $arInput["TYPE"] == "INTEGER"): ?>
                                    <input
                                            type="text"
                                            value="<?= $arInput["INPUT_VALUE"] ?>"
                                            id="<?= $arInput["INPUT_ID"] ?>"
                                            name="<?= $arInput["INPUT_NAME"] ?>"
                                            size="40"
                                    />
								<? endif ?>

								<? if ($arInput["TYPE"] == "LIST"): ?>
									<? if ($arInput["VALUES"]): ?>
										<? $index = 0; ?>
										<? foreach ($arInput["VALUES"] as $value => $item): ?>
                                            <input id="<?= $arInput["INPUT_ID"] ?>_<?= $index ?>"
                                                   name="<?= $arInput["INPUT_NAME"] ?>" type="radio"
                                                   value="<?= htmlspecialcharsbx($value) ?>"
												<? if ('' . $value == '' . $arInput["~INPUT_VALUE"]): ?>
                                                    checked
												<? endif ?>
                                            > <label
                                                    for="<?= $arInput["INPUT_ID"] ?>_<?= $index ?>"><?= htmlspecialcharsbx($item) ?></label>
                                            <div style="clear: both; height: 2px"></div>
											<? $index++; ?>
										<? endforeach ?>
									<? endif ?>
								<? endif ?>
								<? if (mb_strlen($arInput["HELP"]) > 0): ?>
									<?= BeginNote(); ?>
									<?= $arInput["HELP"]; ?>
									<?= EndNote(); ?>
								<? endif ?>
                            </td>
                        </tr>
					<? endif ?>
				<? endforeach /*******$arDisplayOptions*******/
				?>

			<? endforeach /*******$arGroups*******/
				?>

			<? endforeach /*******$aTabs*******/
			?>

			<? $tabControl->BeginNextTab(); ?>
			<? require_once(__DIR__ . '/instruction.php'); ?>
			<? $tabControl->Buttons(); ?>
            <input <? if (!$RIGHT_W) echo "disabled" ?> type="submit" name="Update"
                                                        value="<?= GetMessage("MAIN_SAVE") ?>"
                                                        title="<?= GetMessage("MAIN_OPT_SAVE_TITLE") ?>">
            <input <? if (!$RIGHT_W) echo "disabled" ?> type="submit" name="RestoreDefaults"
                                                        title="<? echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS") ?>"
                                                        OnClick="return confirm('<? echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING")) ?>')"
                                                        value="<? echo GetMessage("MAIN_RESTORE_DEFAULTS") ?>">
			<? $tabControl->End(); ?>
        </form>

		<?
	}
}
?>