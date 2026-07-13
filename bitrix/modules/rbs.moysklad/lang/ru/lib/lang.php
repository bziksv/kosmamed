<?
//enum list values for HL Log
$MESS['UF_ENTITY_ORDER_ENUM'] = 'Заказ';
$MESS['UF_ENTITY_DELIVERY_ENUM'] = 'Отгрузка';
$MESS['UF_ENTITY_PAYMENTS_ENUM'] = 'Оплата';
$MESS['UF_ENTITY_USER_ENUM'] = 'Пользователь';

$MESS['UF_TYPE_DEFAULT_ENUM'] = 'Сообщение';
$MESS['UF_TYPE_ERROR_ENUM'] = 'Ошибка';
$MESS['UF_TYPE_SUCCESS_ENUM'] = 'Успешная операция';
$MESS['UF_TYPE_WARNING_ENUM'] = 'Предупреждение';

$MESS['ADMIN_NOTIFY'] = "МойСклад: Ошибка обмена заказов. Подробнее в <a target='_blank' href='/bitrix/admin/highloadblock_rows_list.php?ENTITY_ID=#HL_BLOCK_ID#&lang=ru'>логах модуля.</a>";

$MESS['EMAIL_SUBJECT'] = "МойСклад: Ошибка обмена заказов.";
$MESS['EMAIL_FOOTER'] = "\n\nПодробнее в <a target='_blank' href='#SITE_ADDRESS#/bitrix/admin/highloadblock_rows_list.php?ENTITY_ID=#HL_BLOCK_ID#&lang=ru'>логах модуля.</a>";

//api.php
$MESS['API_ERROR'] = 'Ошибка API. [#CODE#] #MSG#. <a target="_blank" href="#LINK#">Подробнее.</a> (Запрос: #ENDPOINT#)';
$MESS['ERROR_API_GET_HREF'] = 'Ошибка запроса по API. Href: #HREF#';
$MESS['API_ERROR_NULL'] = 'Сервис "МойСклад" не доступен.';

//agent.php
$MESS['LOG_AGENT_ORDER_START'] = 'Заказ: #ORDER_ID# | Выгрузка заказа в МС.';
$MESS['LOG_AGENT_ORDER_FIND'] = 'Заказ найден в МС, выгрузка отменена.';
$MESS['LOG_AGENT_ORDER_FIND_BX'] = 'Проверка существования заказа в БУС.';
$MESS['LOG_AGENT_ORDER_FIND_BX_FAIL'] = 'Заказ не найден в БУС.';
$MESS['LOG_AGENT_ORDER_BX_DELAY_FINISH'] = 'Прекращения работы агента выгрузки (закончилось время жизни агента).';
$MESS['LOG_AGENT_ORDER_RESTART'] = 'Повторный запуск агента на выгрузку.';
$MESS['LOG_AGENT_ORDER_IMPORT_START'] = 'Импорт заказа: #ORDER_HREF#';
$MESS['CREATED_IN_BX'] = 'Заказ создан в БУС: ID #ORDER_ID#';
//inlcude.php
$MESS['SYNC_DISABLED'] = 'Не участвует в обмене';
$MESS['AGENT_SETTED'] = 'Агент установлен';
$MESS['CREATED_BX'] = 'Создан в БУС';
$MESS['UPDATED_BX'] = 'Изменен в БУС';
$MESS['DELIVERY_SERVICE_NAME'] = 'Доставка сайт';
$MESS['ERROR_DATE_INPUT'] = 'Ошибка установки даты';
//customerorder.php
$MESS['CONSTRUCT_ORDER_FIND_NOT_ONE'] = 'Найдено заказов больше одного. Количество найденых заказов в МС: #SIZE#.';
$MESS['CONSTRUCT_ORDER_FIND_ZERO'] = 'Не найден заказ на стороне МС.';
$MESS['ENTITY_LOAD_BX_ERROR'] = 'Ошибка при загрузке заказа. Входящий ID заказа: #ID#.';
$MESS['ENTITY_LOAD_BX_INPUT_DATA_WRONG'] = 'Не верный формат ID заказа для поиска. Входящий ID заказа: #ID#.';
$MESS['EMPTY_HREF'] = 'Пустая ссылка на заказ.';
$MESS['EMPTY_ACCOUNT_ID'] = 'Пустой номер заказа для поиска.';
$MESS['EMPTY_ORDER_BX'] = 'Не найден заказ в БУС.';
$MESS['EMPTY_ORDER_UID'] = 'Не найден пользователь.';
$MESS['IMPORTED_ORDER'] = 'Заказ импортирован в БУС. ID: #ID#.';
$MESS['ERROR_LOAD_STATUS'] = 'Не удалось сопоставить статус из настроек модуля. ID: #ID#.';
$MESS['ERROR_LOAD_ITEM'] = 'Ошибка загрузки позиции заказа. Href: #ID#.';
$MESS['CANT_FIND_BASKET_ITEMS_MS'] = 'Не найден товар на стороне МС. Внешний код товара: #ID#.';
$MESS['ORDER_ALREADY_CREATED'] = 'Заказ уже создан в БУС | ID: #ID#';
$MESS['ADDED_PRODUCT_MS'] = 'Товар <a href="#HREF#">#NAME#</a> создан в МС. Внешний код товара: <b>#ID#</b>';
$MESS['ADDED_SERVICE_MS'] = 'Услуга <a href="#HREF#">#NAME#</a> создана в МС. Внешний код услуги: <b>#ID#</b>';
$MESS['EVENT_BEFORE_IMPORT_STOP'] = 'Импорт заказа остановлен пользовательским событием.';
//customerorderapi.php
$MESS['ORG_CANT_FIND'] = 'Не указана организация в параметрах модуля';
$MESS['COUNTER_SEARCH_START'] = 'Старт поиска контрагента в МС';
$MESS['COUNTER_SEARCH_FINISH'] = 'Найден/создан контрагент в МС (#NAME#)';
$MESS['CHANGED_BY_EVENT'] = 'Изменен в событии: #EVENT_NAME#';
$MESS['CREATED_IN_MS'] = 'Заказ создан в МС (ID: #ID#)';
$MESS['API_FAIL'] = 'Ошибка в отправке по API';
$MESS['COUNTER_FAIL'] = 'Ошибка создания / поиска контрагента';

//counterparty.php
$MESS['SEARCH'] = 'Поиск';
$MESS['EMPTY_USER_ID'] = 'Пустое значение USER_ID';
$MESS['ERROR_FETCH_USER'] = 'Ошибка загрузки пользователя ID: #USER#';
$MESS['EMPTY_HREF_CP'] = 'Пустая ссылка на контрагента.';
$MESS['CANT_FIND_USERBX'] = 'Не найден пользователь на стороне сайта. Внешний код: #XML_ID#';
$MESS['ERROR_XML_ID_EMPTY'] = 'Пустой внешний код у пользователя МС. ID пользователя в МС: #XML_ID#';
//webhook.php
$MESS['WEBHOOK_START'] = 'Вебхук <b>#TYPE#</b> / #ACTION#. Метассылка: #HREF#';
$MESS['WEBHOOK_CACHED'] = 'Веб-хук закеширован. Обработка веб-хука приостановлена.';

$MESS['WEBHOOK_EVENT_BEFORE_ERROR_RESULT'] = 'Пользовательское событие OnBeforeWebhookProcess вернуло ошибку. Обработка веб-хука приостановлена.';
$MESS['WEBHOOK_EVENT_AFTER_ERROR_RESULT'] = 'Пользовательское событие OnAfterWebhookProcess вернуло ошибку. Обработка веб-хука приостановлена.';

$MESS['WEBHOOK_CO_IMPORT_START'] = 'Начало импорта заказа в БУС.';
$MESS['WEBHOOK_CO_IMPORT_SUCCESS'] = 'Заказ успешно импортирован в БУС | ID: #ID#';
$MESS['WEBHOOK_CO_IMPORT_ERROR'] = 'Ошибка импорта заказа в БУС.';

$MESS['WEBHOOK_CO_SEARCH_START'] = 'Начало поиска заказа в БУС.';
$MESS['WEBHOOK_CO_SEARCH_SUCCESS'] = 'Заказ найден в БУС ID: #ID#.';
$MESS['WEBHOOK_CO_SEARCH_ERROR'] = 'Ошибка при поиске заказа в БУС (см. лог отладки).';

$MESS['WEBHOOK_CO_DISABLED'] = 'Заказ не обрабатывается со стороны МС по свойству или фильтру (см. настройки модуля).';
$MESS['WEBHOOK_IMPORT_TYPE_UPDATE_FLAG_DISABLED'] = 'Заказ не может быть импортирован т.к. не стоит флаг в заказе МС.';

$MESS['WEBHOOK_CO_CHECK_UPDATE_HOOK'] = 'Заказ проверен по веб-хуку.';