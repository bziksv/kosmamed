<?

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Internals\StatusLangTable;

$module_id = 'up.boxberrydelivery';
Loc::loadMessages(__FILE__);
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$module_id.'/include.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.$module_id.'/classes/general/CModuleOptions.php');

$arStatusId = array();
$arStatusName = array();
$arStatusIdChange = array('0' => '');
$arStatusNameChange = array('0' => Loc::getMessage('BB_NO_CHANGE'));

$arTabs = array(
    array(
        'DIV' => 'edit1',
        'TAB' => Loc::getMessage("edit1_tab"),
        'ICON' => '',
        'TITLE' => Loc::getMessage("edit1_title")
    ),
    array(
        'DIV' => 'edit20',
        'TAB' => Loc::getMessage("edit20_tab"),
        'ICON' => '',
        'TITLE' => Loc::getMessage("edit20_title")
    )
);

$arGroups = array(
    'OPTION_150' => array('TITLE' => Loc::getMessage("BB_OPTION_1_TITLE"), 	'TAB' => 0),
    'OPTION_200' => array('TITLE' => Loc::getMessage("BB_OPTION_2_TITLE"), 	'TAB' => 0),
    'OPTION_250' => array('TITLE' => Loc::getMessage("BB_OPTION_3_TITLE"),    'TAB' => 0),
    'OPTION_300' => array('TITLE' => Loc::getMessage("BB_OPTION_4_TITLE"),    'TAB' => 0),
    'OPTION_400' => array('TITLE' => Loc::getMessage("BB_OPTION_6_TITLE"),    'TAB' => 0),
    'OPTION_350' => array('TITLE' => Loc::getMessage("BB_OPTION_5_TITLE"), 	'TAB' => 1),
);

$arOptions = array(
    'API_TOKEN' => array(
        'GROUP' => 'OPTION_150',
        'TITLE' => Loc::getMessage("BB_API_TOKEN"),
        'TYPE' => 'STRING',
        'DEFAULT' => '',
        'SIZE' => 20,
        'SORT' => '0',
        'REFRESH' => 'N',
    ),
    'API_URL' => array(
        'GROUP' => 'OPTION_150',
        'TITLE' => Loc::getMessage("BB_API_URL"),
        'TYPE' => 'STRING',
        'DEFAULT' => 'https://api.boxberry.ru/json.php',
        'SIZE' => 20,
        'SORT' => '10',
        'REFRESH' => 'N',

    ),
    'WIDGET_URL' => array(
        'GROUP' => 'OPTION_150',
        'TITLE' => Loc::getMessage("BB_WIDGET_URL"),
        'TYPE' => 'STRING',
        'DEFAULT' => '/bitrix/js/up.boxberrydelivery/boxberry.js',
        'SIZE' => 20,
        'SORT' => '15',
        'REFRESH' => 'N',

    ),
    'RECEPTION_POINT_NAME' => array(
        'GROUP' => 'OPTION_150',
        'TITLE' => Loc::getMessage('BB_RECEPTION_POINT_NAME'),
        'TYPE' => 'STRING',
        'DEFAULT' => '',
        'SIZE' => 50,
        'SORT' => '15',
        'REFRESH' => 'N',
    ),
    'RECEPTION_POINT_CODE' => array(
        'GROUP' => 'OPTION_150',
        'TITLE' => Loc::getMessage('BB_RECEPTION_POINT_CODE'),
        'TYPE' => 'STRING',
        'DEFAULT' => '',
        'SIZE' => 50,
        'SORT' => '16',
        'REFRESH' => 'N',
    ),
    'BB_CUSTOM_LINK' => array(
        'GROUP' => 'OPTION_250',
        'TITLE' => Loc::getMessage("BB_CUSTOM_LINK"),
        'TYPE' => 'STRING',
        'DEFAULT' => '',
        'SIZE' => 20,
        'SORT' => '25',
        'REFRESH' => 'N',

    ),
    'BB_ACCOUNT_NUMBER' => array(
        'GROUP' => 'OPTION_200',
        'TITLE' => Loc::getMessage("BB_ACCOUNT_NUMBER"),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => '',
        'SORT' => '70',
        'REFRESH' => 'N',
    ),
    'BB_PVZ_SURCH' => array(
        'GROUP' => 'OPTION_300',
        'TITLE' => Loc::getMessage('BB_PVZ_SURCH'),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => '',
        'SORT' => '100',
        'REFRESH' => 'N',
    ),
    'BB_KD_SURCH' => array(
        'GROUP' => 'OPTION_300',
        'TITLE' => Loc::getMessage("BB_KD_SURCH"),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => '',
        'SORT' => '100',
        'REFRESH' => 'N',
    ),
    'BB_DISABLE_CALC_CACHE' => array(
        'GROUP' => 'OPTION_300',
        'TITLE' => Loc::getMessage("BB_DISABLE_CALC_CACHE"),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => '',
        'SORT' => '110',
        'REFRESH' => 'N',
    ),
    'BB_LOG' => array(
        'GROUP' => 'OPTION_400',
        'TITLE' => Loc::getMessage("BB_LOG"),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => '',
        'SORT' => '1500',
        'REFRESH' => 'N',
    ),
    'BB_BUTTON' => array(
        'GROUP' => 'OPTION_250',
        'TITLE' => Loc::getMessage("BB_BUTTON"),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => '',
        'SORT' => '1700',
        'REFRESH' => 'N',
    ),
    'BB_LINK_IN_PERIOD' => array(
        'GROUP' => 'OPTION_250',
        'TITLE' => Loc::getMessage("BB_LINK_IN_PERIOD"),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => '',
        'SORT' => '1800',
        'REFRESH' => 'N',
    ),
    'BB_PARSELCREATE' => array(
        'GROUP' => 'OPTION_200',
        'TITLE' => Loc::getMessage("BB_PARSELCREATE"),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => '',
        'SORT' => '1900',
        'REFRESH' => 'N',
    ),
    'BB_PARSELSEND' => array(
        'GROUP' => 'OPTION_200',
        'TITLE' => Loc::getMessage("BB_PARSELSEND"),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => '',
        'SORT' => '2100',
        'REFRESH' => 'N',
    ),
    'BB_STORE_STICKERS_LOCALLY' => array(
        'GROUP' => 'OPTION_200',
        'TITLE' => Loc::getMessage("BB_STORE_STICKERS_LOCALLY"),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => '',
        'SORT' => '2300',
        'REFRESH' => 'N',
    ),
    'BB_ADD_TRACK_NUMBER_INTO_SHIPMENT' => array(
        'GROUP' => 'OPTION_200',
        'TITLE' => Loc::getMessage("BB_ADD_TRACK_NUMBER_INTO_SHIPMENT"),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => '',
        'SORT' => '2500',
        'REFRESH' => 'N',
    ),
    'BB_PAYMENT_AS_PROFILE_TYPE' => array(
        'GROUP' => 'OPTION_200',
        'TITLE' => Loc::getMessage('BB_PAYMENT_AS_PROFILE_TYPE'),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => '',
        'SORT' => '2500',
        'REFRESH' => 'N',
    ),
    'BB_WEIGHT' => array(
        'GROUP' => 'OPTION_300',
        'TITLE' => Loc::getMessage("BB_WEIGHT"),
        'TYPE' => 'NUMBER',
        'DEFAULT' => '1000',
        'SIZE' => 20,
        'SORT' => '25',
        'REFRESH' => 'N',

    ),
    'BB_HEIGHT' => array(
        'GROUP' => 'OPTION_300',
        'TITLE' => Loc::getMessage("BB_HEIGHT"),
        'TYPE' => 'NUMBER',
        'DEFAULT' => '',
        'SIZE' => 20,
        'SORT' => '35',
        'REFRESH' => 'N',

    ),
    'BB_WIDTH' => array(
        'GROUP' => 'OPTION_300',
        'TITLE' => Loc::getMessage("BB_WIDTH"),
        'TYPE' => 'NUMBER',
        'DEFAULT' => '',
        'SIZE' => 20,
        'SORT' => '45',
        'REFRESH' => 'N',

    ),
    'BB_DEPTH' => array(
        'GROUP' => 'OPTION_300',
        'TITLE' => Loc::getMessage("BB_DEPTH"),
        'TYPE' => 'NUMBER',
        'DEFAULT' => '',
        'SIZE' => 20,
        'SORT' => '45',
        'REFRESH' => 'N',

    ),
    'BB_APPLY_DEFAULT_DIMENSIONS_TO_ORDER' => array(
        'GROUP' => 'OPTION_300',
        'TITLE' => Loc::getMessage("BB_APPLY_DEFAULT_DIMENSIONS_TO_ORDER"),
        'TYPE' => 'CHECKBOX',
        'DEFAULT' => '',
        'SIZE' => 20,
        'SORT' => '55',
        'REFRESH' => 'N',

    ),
    'BB_FIO' => array(
        'GROUP' => 'OPTION_350',
        'TITLE' => Loc::getMessage("BB_FIO"),
        'TYPE' => 'STRING',
        'DEFAULT' => 'FIO',
        'SIZE' => 20,
        'SORT' => '20',
        'REFRESH' => 'N',
    ),
    'BB_PHONE' => array(
        'GROUP' => 'OPTION_350',
        'TITLE' => Loc::getMessage("BB_PHONE"),
        'TYPE' => 'STRING',
        'DEFAULT' => 'PHONE',
        'SIZE' => 20,
        'SORT' => '120',
        'REFRESH' => 'N',
    ),
    'BB_EMAIL' => array(
        'GROUP' => 'OPTION_350',
        'TITLE' => Loc::getMessage("BB_EMAIL"),
        'TYPE' => 'STRING',
        'DEFAULT' => 'EMAIL',
        'SIZE' => 20,
        'SORT' => '220',
        'REFRESH' => 'N',
    ),
    'BB_ZIP' => array(
        'GROUP' => 'OPTION_350',
        'TITLE' => Loc::getMessage("BB_ZIP"),
        'TYPE' => 'STRING',
        'DEFAULT' => 'ZIP',
        'SIZE' => 20,
        'SORT' => '320',
        'REFRESH' => 'N',
    ),
    'BB_LOCATION' => array(
        'GROUP' => 'OPTION_350',
        'TITLE' => Loc::getMessage("BB_LOCATION"),
        'TYPE' => 'STRING',
        'DEFAULT' => 'LOCATION',
        'SIZE' => 20,
        'SORT' => '420',
        'REFRESH' => 'N',
    ),
    'BB_ADDRESS' => array(
        'GROUP' => 'OPTION_350',
        'TITLE' => Loc::getMessage("BB_ADDRESS"),
        'TYPE' => 'STRING',
        'DEFAULT' => 'ADDRESS',
        'SIZE' => 20,
        'SORT' => '520',
        'REFRESH' => 'N',
    ),
    'BB_JUR_ADDRESS' => array(
        'GROUP' => 'OPTION_350',
        'TITLE' => Loc::getMessage("BB_JUR_ADDRESS"),
        'TYPE' => 'STRING',
        'DEFAULT' => 'JUR_ADDRESS',
        'SIZE' => 20,
        'SORT' => '620',
        'REFRESH' => 'N',
    ),
    'BB_INN' => array(
        'GROUP' => 'OPTION_350',
        'TITLE' => Loc::getMessage("BB_INN"),
        'TYPE' => 'STRING',
        'DEFAULT' => 'INN',
        'SIZE' => 20,
        'SORT' => '720',
        'REFRESH' => 'N',
    ),
    'BB_KPP' => array(
        'GROUP' => 'OPTION_350',
        'TITLE' => Loc::getMessage("BB_KPP"),
        'TYPE' => 'STRING',
        'DEFAULT' => 'KPP',
        'SIZE' => 20,
        'SORT' => '820',
        'REFRESH' => 'N',
    ),
    'BB_CONTACT_PERSON' => array(
        'GROUP' => 'OPTION_350',
        'TITLE' => Loc::getMessage("BB_CONTACT_PERSON"),
        'TYPE' => 'STRING',
        'DEFAULT' => 'CONTACT_PERSON',
        'SIZE' => 20,
        'SORT' => '920',
        'REFRESH' => 'N',
    ),
    'BB_COMPANY_NAME' => array(
        'GROUP' => 'OPTION_350',
        'TITLE' => Loc::getMessage("BB_COMPANY"),
        'TYPE' => 'STRING',
        'DEFAULT' => 'COMPANY_NAME',
        'SIZE' => 20,
        'SORT' => '940',
        'REFRESH' => 'N',
    ),
    'BB_PAID_PERSON_PH' => array(
        'GROUP' => 'OPTION_350',
        'TITLE' => Loc::getMessage("BB_PAID_PERSON_PH"),
        'TYPE' => 'STRING',
        'DEFAULT' => '1',
        'SIZE' => 20,
        'SORT' => '1000',
        'REFRESH' => 'N',
    ),
    'BB_PAID_PERSON_JUR' => array(
        'GROUP' => 'OPTION_350',
        'TITLE' => Loc::getMessage("BB_PAID_PERSON_JUR"),
        'TYPE' => 'STRING',
        'DEFAULT' => '2',
        'SIZE' => 20,
        'SORT' => '1020',
        'REFRESH' => 'N',
    ),
);

$bxOrderStatuses = StatusLangTable::getList(array(
    'order'  => array('STATUS.SORT'=>'ASC', 'STATUS_ID' => 'ASC'),
    'filter' => array('STATUS.TYPE'=>'O','LID'=>'RU'),
    'select' => array('STATUS_ID', 'NAME'),
))->fetchAll();

$arOptions['BB_STATUS_PARSELCREATE']= array(
    'GROUP'   => 'OPTION_200',
    'TITLE'   => Loc::getMessage("BB_STATUS_PARSELCREATE"),
    'TYPE'    => 'SELECT',
    'VALUES'  => [
        'REFERENCE'    => array_merge([''], array_column($bxOrderStatuses, 'NAME')),
        'REFERENCE_ID' => array_merge([''], array_column($bxOrderStatuses, 'STATUS_ID')),
    ],
    'SORT'    => 35,
    'REFRESH' => 'N',
);

$RIGHT = $APPLICATION->GetGroupRight($module_id);

if($RIGHT != "D"){
    $opt = new CModuleOptions($module_id, $arTabs, $arGroups, $arOptions, true);
    $opt->ShowHTML();
}
?>