<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("dev");

include($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/r52.qrcode/lib/CreateQrcode.php');

$params = [
	'ST00012',
	'OrderNum=1',
	'Sum=100',
	'PersonalAcc=30232810400000000003',
	'PayeeINN=7750005725',
	'BIC=044525444',
	'CorrespAcc=30103810945250000444',
	'BankName=Общество с ограниченной ответственностью небанковская кредитная организация «ЮМани»',
	'Name=ООО НКО «ЮМани»',
	'Purpose=Пополнение кошелька 4100118651483095, НДС не облагается.',
];

$qr = new R52\QrCode\CreateQrcode;
$res = $qr->create(implode('|', $params));
$QR_CODE = $qr->getQrLogo($res);

print $QR_CODE;
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>