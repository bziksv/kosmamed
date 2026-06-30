<?
use \Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
$status_list = array();
$dbStatuses = CSaleStatus::GetList();
while ($status_item = $dbStatuses->Fetch()) {
    $status_list[$status_item["ID"]] = $status_item["NAME"];
}
$data = array(
	'NAME' => 'Tochka',
	'SORT' => 100,
	'CODES' => array(
		"TOCHKA_FORM_URL" => array(
			"NAME" => Loc::getMessage("SALE_HPS_TOCHKA_FORM_URL"),
            "DESCRIPTION" => Loc::getMessage("SALE_HPS_TOCHKA_FORM_URL_DESCR"),
			'GROUP' => 'TOCHKA_SETTINGS',
			'SORT' => 100,
		),
		"TOCHKA_LOGIN" => array(
			"NAME" => Loc::getMessage("SALE_HPS_TOCHKA_LOGIN"),
            "DESCRIPTION" => Loc::getMessage("SALE_HPS_TOCHKA_LOGIN_DESCR"),
			'GROUP' => 'TOCHKA_SETTINGS',
			'SORT' => 200,
		),
//		"TOCHKA_LK_LOGIN" => array(
//			"NAME" => Loc::getMessage("SALE_HPS_TOCHKA_LK_LOGIN"),
//            "DESCRIPTION" => Loc::getMessage("SALE_HPS_TOCHKA_LK_LOGIN_DESCR"),
//			'GROUP' => 'TOCHKA_SETTINGS',
//			'SORT' => 200,
//		),
//		"TOCHKA_LK_PASSWORD" => array(
//			"NAME" => Loc::getMessage("SALE_HPS_TOCHKA_LK_PASSWORD"),
//            "DESCRIPTION" => Loc::getMessage("SALE_HPS_TOCHKA_LK_PASSWORD_DESCR"),
//			'GROUP' => 'TOCHKA_SETTINGS',
//			'SORT' => 300,
//		),
		"TOCHKA_SECRET" => array(
			"NAME" => Loc::getMessage("SALE_HPS_TOCHKA_SECRET"),
			'GROUP' => 'TOCHKA_SETTINGS',
			'SORT' => 400,
		),
		"TOCHKA_PSTYPE" => array(
			"NAME" => Loc::getMessage("SALE_HPS_TOCHKA_PSTYPE"),
			'GROUP' => 'TOCHKA_SETTINGS',
			'SORT' => 500,
		),
        'TOCHKA_STATUS_AFTER_PAYMENT' => array(
            'NAME' => Loc::getMessage('SALE_HPS_TOCHKA_STATUS_AFTER_PAYMENT'),
            'SORT' => 600,
            'GROUP' => 'TOCHKA_SETTINGS',
            'INPUT' => array(
                'TYPE' => 'ENUM',
                'OPTIONS' => $status_list
            )
        )
	)
);
