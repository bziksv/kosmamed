<?
//LOG EXCHANGE
$MESS['LOG_ORDER_START'] = 'Заказ: #ORDER_ID# | Начало выгрузки заказа.';
$MESS['LOG_ORDER_CANT_FIND_BX'] = 'Заказ: #ORDER_ID# | Не найден заказ на стороне БУС.';
$MESS['LOG_ORDER_CANT_FIND_COUNTER'] = 'Заказ: #ORDER_ID# | Не создан контрагент.';
$MESS['LOG_ORDER_CANT_FIND_ORG_CONFIG'] = 'Заказ: #ORDER_ID# | Не задана организация в настройках модуля.';
$MESS['LOG_ORDER_CANT_FIND_ORG'] = 'Заказ: #ORDER_ID# | Не найдена организация на стороне МС (#ORG_ID#).';
$MESS['LOG_ORDER_COUNTER_FIND'] = 'Заказ: #ORDER_ID# | Найден контрагент.';
$MESS['LOG_ORDER_API_SEND'] = 'Заказ: #ORDER_ID# | Заказ выгружен в МС.';
$MESS['LOG_ORDER_API_FAIL'] = 'Заказ: #ORDER_ID# | Ошибка создания заказа на стороне МС.';

$MESS['LOG_SET_BASKET_START'] = 'Заказ: #ORDER_ID# | Начало выгрузки корзины в МС';
$MESS['LOG_SET_BASKET_START_ITEM'] = 'Заказ: #ORDER_ID# | Позиция: #ID# | Поиск товара (внешний код: #XML_ID#)';
$MESS['LOG_SET_BASKET_START_ITEM_BUNDLE'] = 'Заказ: #ORDER_ID# | Позиция: #ID# | Начало обработки комплекта (внешний код: #XML_ID#)';
$MESS['LOG_SET_BASKET_ITEM_FIND'] = 'Заказ: #ORDER_ID# | Позиция: #ID# | Товар найден на стороне МС (Название: #NAME_MS#; Тип: #TYPE_MS#; ID: #ID_MS#;)';
$MESS['LOG_SET_BASKET_ITEM_NOT_FIND_ZERO'] = 'Заказ: #ORDER_ID# | Позиция: #ID# | Товар не найден на стороне МС';
$MESS['LOG_SET_BASKET_ITEM_NOT_FIND'] = 'Заказ: #ORDER_ID# | Позиция: #ID# | Найдено более 1 товара на стороне МС (поменяйте внешний код у дублирующего товара с внешним кодом: #XML_ID#)';
$MESS['LOG_SET_BASKET_ITEM_FINISH'] = 'Заказ: #ORDER_ID# | В корзину МС добавлено #COUNT# товаров (включая доставку, если она выгружается)';

$MESS['LOG_SET_BASKET_ITEM_CREATE'] = 'Заказ: #ORDER_ID# | Позиция: #ID# | Товар не удалось создать на стороне МС';
$MESS['LOG_SET_BASKET_ITEM_CANT_CREATE'] = 'Заказ: #ORDER_ID# | Позиция: #ID# | Товар создан стороне МС';
//ERRORS
$MESS['PAY_INFO'] = "К оплате: #SUM# \nОплачено: #PAID# \nОсталось оплатить: #NEED_PAID#";

//__construct
$MESS['ERROR_CANT_FIND_ORDER_MS'] = '[Ошибка] Заказ ID: #ORDER# не найден на стороне МойСклад';
$MESS['ERROR_API_GET'] = '[Ошибка] Не удалось сделать запрос к API (заказ ID: #ORDER#)';
$MESS['ERROR_ENTITY_LOAD'] = '[Ошибка] Не удалось загрузить сущность заказа ID: #ORDER#';
$MESS['ERROR_INPUT_DATA'] = '[Ошибка] Не верно введенные данные (#ORDER#)';

$MESS['DELIVERY_PARENT_NAME'] = '#PARENT# (#CURRENT#)';

//createFromHref
$MESS['ERROR_HREF_EMPTY'] = '[Ошибка] Пустая ссылка на заказ';
$MESS['ERROR_ENTITY_LOAD_ACCOUNT'] = '[Ошибка] Не удалось загрузить сущность заказа №#ORDER#';
$MESS['ERROR_API_GET_HREF'] = '[Ошибка] Не удалось сделать запрос к API (запрос: #HREF#)';

//checkUpdateHook
$MESS['ERROR_LOAD_STATUS'] = '[Ошибка] Не найден статус в МойСклад. ID статуса: #STATUS#';
$MESS['ERROR_LOAD_STATUS_BX'] = '[Ошибка] Не найден статус на сайте. ID статуса: #STATUS#';

$MESS['ERROR_LOAD_ITEM'] = '[Ошибка] Не найден товар на стороне МойСклад. Ссылка на товар: #POSITION_HREF#';
$MESS['ERROR_LOAD_POSITIONS'] = '[Ошибка] Не найден товар на стороне МойСклад. Ссылка на товар: #POSITIONS_HREF#';

//checkPaymentById
$MESS['ERROR_FIND_PAYMENT'] = '[Ошибка] Не найдена платежная система №#PAYMENT#';
$MESS['ERROR_LOAD_PAYMENT_COLLECTION'] = '[Ошибка] Не удалось загрузить коллекцию оплат заказа №#ORDER#';
$MESS['ERROR_ENTITY_LOAD_PAYMENT'] = '[Ошибка] Не удалось загрузить сущность заказа. Оплата №#PAYMENT#';

//
$MESS['ERROR_PAYMENT_OPTION_SET'] = '[Ошибка] Не заданы настройки для платежной системы ID: #PAYMENT#';
$MESS['ERROR_API_GET_PAYMENT'] = '[Ошибка] Не удалось сделать запрос к API (ID платежной системы: #PAYMENT#)';
$MESS['ERROR_PAYMENT_CONFIG_SET'] = '[Ошибка] Не верные настройки для платежной системы ID: #PAYMENT#';
$MESS['ERROR_GET_PAYMENT'] = '[Ошибка] Не удалось обработать информацию с платежной системы ID: #PAYMENT#';