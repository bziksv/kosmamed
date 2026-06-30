<?define("NEED_AUTH", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

$APPLICATION->SetTitle("Авторизация");?>

<div>Вы зарегистрированы и успешно авторизовались.<br /><a href="<?=SITE_DIR?>">Вернуться на главную страницу</a></div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>