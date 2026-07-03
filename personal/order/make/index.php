<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

$APPLICATION->SetTitle("Оформление заказа");


//$APPLICATION->SetTitle("СЧЕТ № ".$params['ACCOUNT_NUMBER']);



$flag = true;

use Bitrix\Sale,
    Bitrix\Sale\Basket;

Bitrix\Main\Loader::includeModule("sale");
Bitrix\Main\Loader::includeModule("catalog");

$price = \Bitrix\Sale\BasketComponentHelper::getFUserBasketPrice(Sale\Fuser::getId(),Bitrix\Main\Context::getCurrent()->getSite());

if(!empty($arSetting["ORDER_MIN_PRICE"]["VALUE"])){
    $pageUrl = $APPLICATION->GetCurPageParam();
    $query_str = parse_url($pageUrl, PHP_URL_QUERY);
    parse_str($query_str, $query_params);
    
    if ($price > $arSetting["ORDER_MIN_PRICE"]["VALUE"])
        $flag = true;
    else
        $flag = false;
    
    if (array_key_exists('ORDER_ID', $query_params))
        $flag = true;
    
    
}?>

<?if($flag){?>
    <? $APPLICATION->IncludeComponent("bitrix:sale.order.ajax", ".default", Array(
	"COMPONENT_TEMPLATE" => ".default",
		"PAY_FROM_ACCOUNT" => "N",	// Разрешить оплату с внутреннего счета
		"ONLY_FULL_PAY_FROM_ACCOUNT" => "N",	// Разрешить оплату с внутреннего счета только в полном объеме
		"ALLOW_AUTO_REGISTER" => "N",	// Оформлять заказ с автоматической регистрацией пользователя
		"SEND_NEW_USER_NOTIFY" => "Y",	// Отправлять пользователю письмо, что он зарегистрирован на сайте
		"DELIVERY_NO_AJAX" => "Y",	// Когда рассчитывать доставки с внешними системами расчета
		"SHOW_NOT_CALCULATED_DELIVERIES" => "N",	// Отображение доставок с ошибками расчета
		"DELIVERY_NO_SESSION" => "Y",	// Проверять сессию при оформлении заказа
		"TEMPLATE_LOCATION" => "popup",	// Визуальный вид контрола выбора местоположений
		"DELIVERY_TO_PAYSYSTEM" => "d2p",	// Последовательность оформления
		"USE_PREPAYMENT" => "N",	// Использовать предавторизацию для оформления заказа (PayPal Express Checkout)
		"COMPATIBLE_MODE" => "Y",	// Режим совместимости для предыдущего шаблона
		"USE_PRELOAD" => "Y",	// Автозаполнение оплаты и доставки по предыдущему заказу
		"ALLOW_USER_PROFILES" => "Y",	// Разрешить использование профилей покупателей
		"ALLOW_NEW_PROFILE" => "Y",	// Разрешить множество профилей покупателей
		"SHOW_ORDER_BUTTON" => "final_step",	// Отображать кнопку оформления заказа (для неавторизованных пользователей)
		"SHOW_TOTAL_ORDER_BUTTON" => "N",	// Отображать дополнительную кнопку оформления заказа
		"SHOW_PAY_SYSTEM_LIST_NAMES" => "Y",	// Отображать названия в списке платежных систем
		"SHOW_PAY_SYSTEM_INFO_NAME" => "Y",	// Отображать название в блоке информации по платежной системе
		"SHOW_DELIVERY_LIST_NAMES" => "Y",	// Отображать названия в списке доставок
		"SHOW_DELIVERY_INFO_NAME" => "Y",	// Отображать название в блоке информации по доставке
		"SHOW_DELIVERY_PARENT_NAMES" => "Y",	// Показывать название родительской доставки
		"SHOW_STORES_IMAGES" => "Y",	// Показывать изображения складов в окне выбора пункта выдачи
		"SKIP_USELESS_BLOCK" => "Y",	// Пропускать шаги, в которых один элемент для выбора
		"BASKET_POSITION" => "before",	// Расположение списка товаров
		"SHOW_BASKET_HEADERS" => "N",	// Показывать заголовки колонок списка товаров
		"DELIVERY_FADE_EXTRA_SERVICES" => "Y",	// Дополнительные услуги, которые будут показаны в пройденном (свернутом) блоке
		"SHOW_COUPONS_BASKET" => "N",	// Показывать поле ввода купонов в блоке списка товаров
		"SHOW_COUPONS_DELIVERY" => "N",	// Показывать поле ввода купонов в блоке доставки
		"SHOW_COUPONS_PAY_SYSTEM" => "N",	// Показывать поле ввода купонов в блоке оплаты
		"SHOW_NEAREST_PICKUP" => "Y",	// Показывать ближайшие пункты самовывоза
		"DELIVERIES_PER_PAGE" => "160",	// Количество доставок на странице
		"PAY_SYSTEMS_PER_PAGE" => "160",	// Количество платежных систем на странице
		"PICKUPS_PER_PAGE" => "160",	// Количество пунктов самовывоза на странице
		"SHOW_MAP_IN_PROPS" => "N",	// Показывать карту в блоке свойств заказа
		"PROPS_FADE_LIST_1" => array(	// Свойства заказа, которые будут показаны в пройденном (свернутом) блоке (Физическое лицо)[s1]
			0 => "1",
			1 => "2",
			2 => "3",
		),
		"PROPS_FADE_LIST_2" => array(	// Свойства заказа, которые будут показаны в пройденном (свернутом) блоке (Юридическое лицо)[s1]
			0 => "10",
			1 => "8",
			2 => "12",
			3 => "13",
			4 => "14",
		),
		"ACTION_VARIABLE" => "action",	// Название переменной, в которой передается действие
		"PATH_TO_BASKET" => "/personal/cart/",	// Путь к странице корзины
		"PATH_TO_PERSONAL" => "/personal/orders/",	// Путь к странице персонального раздела
		"PATH_TO_PAYMENT" => "/personal/order/payment/",	// Страница подключения платежной системы
		"PATH_TO_AUTH" => "/personal/private/",	// Путь к странице авторизации
		"SET_TITLE" => "Y",	// Устанавливать заголовок страницы
		"DISABLE_BASKET_REDIRECT" => "N",	// Оставаться на странице оформления заказа, если список товаров пуст
		"PRODUCT_COLUMNS_VISIBLE" => array(	// Выбранные колонки таблицы списка товаров
			0 => "PREVIEW_PICTURE",
			1 => "PROPS",
			2 => "DISCOUNT_PRICE_PERCENT_FORMATED",
			3 => "PRICE_FORMATED",
		),
		"ADDITIONAL_PICT_PROP_15" => "-",	// Дополнительная картинка [Продукция]
		"BASKET_IMAGES_SCALING" => "standard",	// Режим отображения изображений товаров
		"SERVICES_IMAGES_SCALING" => "standard",	// Режим отображения вспомагательных изображений
		"USE_YM_GOALS" => "N",	// Использовать цели счетчика Яндекс.Метрики
		"USE_ENHANCED_ECOMMERCE" => "N",	// Отправлять данные электронной торговли в Google и Яндекс
		"USE_CUSTOM_MAIN_MESSAGES" => "Y",	// Заменить стандартные фразы на свои
		"USE_CUSTOM_ADDITIONAL_MESSAGES" => "Y",	// Заменить стандартные фразы на свои
		"USE_CUSTOM_ERROR_MESSAGES" => "N",	// Заменить стандартные фразы на свои
		"TEMPLATE_THEME" => "blue",	// Цветовая тема
		"PRODUCT_COLUMNS_HIDDEN" => "",	// Свойства товаров отображаемые в свернутом виде в списке товаров
		"ALLOW_APPEND_ORDER" => "Y",	// Разрешить оформлять заказ на существующего пользователя
		"SPOT_LOCATION_BY_GEOIP" => "Y",	// Определять местоположение покупателя по IP-адресу
		"SHOW_VAT_PRICE" => "Y",	// Отображать значение НДС
		"USER_CONSENT" => "N",	// Запрашивать согласие
		"USER_CONSENT_ID" => "0",	// Соглашение
		"USER_CONSENT_IS_CHECKED" => "Y",	// Галка по умолчанию проставлена
		"USER_CONSENT_IS_LOADED" => "N",	// Загружать текст сразу
		"EMPTY_BASKET_HINT_PATH" => "/",	// Путь к странице для продолжения покупок
		"USE_PHONE_NORMALIZATION" => "N",	// Использовать нормализацию номера телефона
		"ADDITIONAL_PICT_PROP_16" => "-",	// Дополнительная картинка [Торговые предложения]
		"ADDITIONAL_PICT_PROP_24" => "-",	// Дополнительная картинка [Основной каталог товаров]
		"COMPOSITE_FRAME_MODE" => "N",	// Не кешировать: result_modifier и фото корзины должны собираться на каждый запрос
		"COMPOSITE_FRAME_TYPE" => "AUTO",	// Содержимое компонента
		"MESS_AUTH_BLOCK_NAME" => "Авторизация",	// Название блока авторизации
		"MESS_REG_BLOCK_NAME" => "Регистрация",	// Название блока регистрации
		"MESS_BASKET_BLOCK_NAME" => "Товары в заказе",	// Название блока списка товаров
		"MESS_REGION_BLOCK_NAME" => "Регион доставки",	// Название блока региона доставки
		"MESS_PAYMENT_BLOCK_NAME" => "Оплата",	// Название блока оплаты
		"MESS_DELIVERY_BLOCK_NAME" => "Доставка",	// Название блока доставки
		"MESS_BUYER_BLOCK_NAME" => "Покупатель",	// Название блока свойств заказа
		"MESS_BACK" => "Назад",	// Кнопка возврата к предыдущему блоку
		"MESS_FURTHER" => "Далее",	// Кнопка перехода к следующему блоку
		"MESS_EDIT" => "изменить",	// Кнопка редактирования блока
		"MESS_ORDER" => "Оформить заказ",	// Кнопка оформления заказа
		"MESS_PRICE" => "Стоимость",	// Заголовок для цены
		"MESS_PERIOD" => "Срок доставки",	// Заголовок для срока доставки
		"MESS_NAV_BACK" => "Назад",	// Кнопка перехода к предыдущей странице
		"MESS_NAV_FORWARD" => "Вперед",	// Кнопка перехода к следующей странице
		"MESS_REGISTRATION_REFERENCE" => "Если вы впервые на сайте, и хотите что бы мы вас помнили и все ваши заказы сохранялись, заполните регистрационную форму.",	// Текст для перехода к блоку регистрации
		"MESS_AUTH_REFERENCE_1" => "Символом 'звездочка' (*) отмечены обязательные для заполнения поля.",	// Справочная информация №1 блока "Авторизация"
		"MESS_AUTH_REFERENCE_2" => "После регистрации вы получите информационное письмо.",	// Справочная информация №2 блока "Авторизация"
		"MESS_AUTH_REFERENCE_3" => "Личные сведения, полученные в распоряжение интернет-магазина при регистрации или каким-либо иным образом, не будут без разрешения пользователей передаваться третьим организациям и лицам за исключением ситуаций, когда этого требует закон или судебное решение.",	// Справочная информация №3 блока "Авторизация"
		"MESS_ADDITIONAL_PROPS" => "Дополнительные свойства",	// Кнопка дополнительных свойств товара
		"MESS_USE_COUPON" => "Применить купон",	// Заголовок поля ввода купона
		"MESS_COUPON" => "Купон",	// Заголовок для примененных купонов
		"MESS_PERSON_TYPE" => "Тип плательщика",	// Заголовок выбора типа плательщика
		"MESS_SELECT_PROFILE" => "Выберите профиль",	// Заголовок выбора профиля
		//TODO "MESS_REGION_REFERENCE" => "Выберите свой город в списке. Если вы не нашли свой город, выберите \"другое местоположение\", а город впишите в поле \"Город\"",	// Справочная информация блока "Регион"
		"MESS_PICKUP_LIST" => "Пункты самовывоза:",	// Заголовок пунктов самовывоза
		"MESS_NEAREST_PICKUP_LIST" => "Ближайшие пункты:",	// Заголовок ближайших пунктов самовывоза
		"MESS_SELECT_PICKUP" => "Выбрать",	// Кнопка выбора пункта самовывоза
		"MESS_INNER_PS_BALANCE" => "На вашем пользовательском счете:",	// Информация о балансе внутреннего счета
		"MESS_ORDER_DESC" => "Комментарии к заказу:",	// Заголовок комментариев к заказу
		"SHOW_PICKUP_MAP" => "Y",	// Показывать карту для доставок с самовывозом
		"PICKUP_MAP_TYPE" => "yandex",	// Тип используемых карт
		"SHOW_COUPONS" => "Y",	// Отображать поля ввода купонов
		"HIDE_ORDER_DESCRIPTION" => "N",	// Скрыть поле комментариев к заказу
		"MESS_PRICE_FREE" => "бесплатно",	// Текст для "бесплатно"
		"MESS_ECONOMY" => "Экономия",	// Текст для "Экономия"
	),
	false
);


if($_REQUEST['ORDER_ID']){
$APPLICATION->SetTitle("СЧЕТ № ".$_REQUEST['ORDER_ID']);
}
}else{ ?>
    <div id="min_price_message" class="alertMsg info ">
        <i class="fa fa-info"></i>
        <span class="text"><?= GetMessage('ORDER_MIN_PRICE_VALUE') ?><?= CurrencyFormat($arSetting["ORDER_MIN_PRICE"]["VALUE"], Bitrix\Currency\CurrencyManager::getBaseCurrency()) ?></span>
    </div>
<?}?>

<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>