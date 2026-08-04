<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

// Проверка CSRF и прав администратора
global $USER;
if (!check_bitrix_sessid() || !$USER->IsAdmin()) {
    http_response_code(403);
    die('Forbidden');
}

\Bitrix\Main\Loader::includeModule('sng.secure');

$secureExeptions = array();
if(is_array($_POST['secure_exeptions']))
{
	foreach($_POST['secure_exeptions'] as $key => $path)
	{
		$cleanPath = trim($path);
		if(strlen($cleanPath) > 0)
		{
			$secureExeptions[] = $cleanPath;
		}
	}
}

if(!empty($secureExeptions))
{	
	COption::SetOptionString("sng.secure", "exeptions", serialize($secureExeptions));
}
else
{
	COption::SetOptionString("sng.secure", "exeptions", '');
}

$rawEx = COption::GetOptionString("sng.secure", "exeptions", '');
$arEx = ($rawEx !== '') ? unserialize($rawEx, ['allowed_classes' => false]) : array();
if(!is_array($arEx))
{
	$arEx = array();
}

if(!empty($arEx))
{					
	foreach($arEx as $key => $path)
	{
		?>
		<input class="secure_exeptions_i" type="text" size="35" maxlength="255" value="<?=htmlspecialcharsbx($path);?>" name="secure_ex[]"><br>
		<?
	}
}
?>
<input class="secure_exeptions_i" type="text" size="35" maxlength="255" value="" name="secure_ex[]"><br>