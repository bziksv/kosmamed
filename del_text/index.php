<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Удаление анонсовых и детальных описаний, а также картинок");
global $USER;
if ($USER->IsAdmin()):
?>
<br><br>
<a href="/del_text/prev.php">Удалить "Описание для анонса"</a>
<br><br>
<a href="/del_text/detail.php">Удалить "Детальное описание"</a>
<br><br>
<br><br>
<a href="/del_text/prev_pict.php">Удалить "Картинка для анонса"</a> - предварительно изменив id раздела тут: <a href="/bitrix/admin/fileman_file_edit.php?path=%2Fdel_text%2Fprev_pict.php&full_src=Y&site=s1&lang=ru&&filter=Y&set_filter=Y">Настройки категории для Картинка для анонса</a>
<br><br>
<a href="/del_text/detail_pict.php">Удалить "Детальная картинка"</a> - предварительно изменив id раздела тут: <a href="https://kosmamed.ru/bitrix/admin/fileman_file_edit.php?path=%2Fdel_text%2Fdetail_pict.php&full_src=Y&site=s1&lang=ru&&filter=Y&set_filter=Y">Настройки категории для Картинка детальная</a>
<?endif;?>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>