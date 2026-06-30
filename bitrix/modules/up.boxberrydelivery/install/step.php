<? use Bitrix\Main\Localization\Loc;

if(!check_bitrix_sessid()) return;?>


<?


echo CAdminMessage::ShowNote(Loc::getMessage("MOD_INST_OK"));


?>


<form action="<?echo $APPLICATION->GetCurPage()?>">


    <input type="hidden" name="lang" value="<?echo LANG?>">


    <input type="submit" name="" value="<?echo Loc::getMessage("MOD_BACK")?>">


<form>


