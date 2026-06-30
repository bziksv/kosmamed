<?
$MESS['PAY_INFO'] = "К оплате: #SUM# \nОплачено: #PAID# \nОсталось оплатить: #NEED_PAID#";

//__construct
$MESS['ERROR_CANT_FIND_ORDER_MS'] = '[Ошибка] Заказ ID: #ORDER# не найден на стороне МойСклад';
$MESS['ERROR_API_GET'] = '[Ошибка] Не удалось сделать запрос к API (заказ ID: #ORDER#)';
$MESS['ERROR_ENTITY_LOAD'] = '[Ошибка] Не удалось загрузить сущность заказа ID: #ORDER#';
$MESS['ERROR_INPUT_DATA'] = '[Ошибка] Не верно введенные данные (#ORDER#)';

//createFromHref
$MESS['ERROR_HREF_EMPTY'] = '[Ошибка] Пустая ссылка на контрагента';
$MESS['ERROR_XML_ID_EMPTY'] = '[Ошибка] Пустое поле внешнего кода';
$MESS['ERROR_ENTITY_LOAD_ACCOUNT'] = '[Ошибка] Не удалось загрузить сущность заказа XML_ID: #USER#';
$MESS['ERROR_API_GET_HREF'] = '[Ошибка] Не удалось сделать запрос к API (запрос: #HREF#)';

//checkUpdateHook
$MESS['ERROR_LOAD_STATUS'] = '[Ошибка] Не найден статус в МойСклад. ID статуса: #STATUS#';
$MESS['ERROR_LOAD_STATUS_BX'] = '[Ошибка] Не найден статус на сайте. ID статуса: #STATUS#';

//checkPaymentById
$MESS['ERROR_FIND_PAYMENT'] = '[Ошибка] Не найдена платежная система №#PAYMENT#';
$MESS['ERROR_LOAD_PAYMENT_COLLECTION'] = '[Ошибка] Не удалось загрузить коллекцию оплат заказа №#ORDER#';
$MESS['ERROR_ENTITY_LOAD_PAYMENT'] = '[Ошибка] Не удалось загрузить сущность заказа. Оплата №#PAYMENT#';

//
$MESS['ERROR_PAYMENT_OPTION_SET'] = '[Ошибка] Не заданы настройки для платежной системы ID: #PAYMENT#';
$MESS['ERROR_API_GET_PAYMENT'] = '[Ошибка] Не удалось сделать запрос к API (ID платежной системы: #PAYMENT#)';
$MESS['ERROR_PAYMENT_CONFIG_SET'] = '[Ошибка] Не верные настройки для платежной системы ID: #PAYMENT#';
$MESS['ERROR_GET_PAYMENT'] = '[Ошибка] Не удалось обработать информацию с платежной системы ID: #PAYMENT#';