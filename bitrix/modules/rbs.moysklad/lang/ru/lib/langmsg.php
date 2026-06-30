<?php
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

//log window
$MESS['LOG_UPD'] = 'Обновить';
$MESS['LOG_SEARCH'] = 'Поиск...';
$MESS['ONLY_ERRORS'] = 'Ошибки и предупреждения';
$MESS['LOG_SCROLL'] = 'Перейти в конец лога';
$MESS['LOG_CLEAR'] = 'Очистить папку логов';
$MESS['LOG_OPEN_DIR'] = 'Открыть папку логов';

//exceptionruler
$MESS['EXCEPTION_metadata_get'] = 'Ошибка считывания мета-информации для выгрузки';
$MESS['EXCEPTION_metadata_get_attrs'] = 'Ошибка считывания мета-информации доп. полей для выгрузки';
$MESS['EXCEPTION_metadata_get_attrs'] = 'Ошибка считывания мета-информации доп. полей для выгрузки';
$MESS['EXCEPTION_delivery_create'] = 'Ошибка создания услуги доставки';
$MESS['EXCEPTION_delivery_search'] = 'Ошибка поиска услуги доставки';
$MESS['EXCEPTION_order_num_search'] = 'Ошибка поиска дублей документа по названию';
$MESS['EXCEPTION_basket_item_search'] = 'Ошибка поиска товара в МС';
$MESS['EXCEPTION_basket_item_create'] = 'Ошибка создания товара в МС';

$MESS['EXCEPTION_counterparty_create'] = 'Ошибка создания контрагента в МС';
$MESS['EXCEPTION_counterparty_search'] = 'Ошибка поиска контрагента в МС';

//api.php
$MESS['API_ERROR'] = 'Ошибка API. [#CODE#] #MSG#. <a target="_blank" href="#LINK#">Подробнее.</a> (Запрос: #ENDPOINT#)';
$MESS['ERROR_API_GET_HREF'] = 'Ошибка запроса по API. Href: #HREF#';
$MESS['API_ERROR_NULL'] = 'Сервис "МойСклад" не доступен.';

//agent.php
$MESS['AGENT_RESET_TASKER_INDEX_START'] = 'Сброс индекса таблицы очереди вебхуков';
$MESS['AGENT_RESET_TASKER_INDEX_SUCCESS'] = 'Индекс сброшен';
$MESS['AGENT_RESET_TASKER_INDEX_ERROR'] = 'Ошибка сброса индекса: #ERROR#';
$MESS['AGENT_RESET_TASKER_INDEX_END'] = 'Сброс индекса таблицы очереди вебхуков завершен';

$MESS['LOG_AGENT_ORDER_START'] = 'Заказ: #ORDER_ID# (агент создания заказа в МС)';
$MESS['LOG_AGENT_ORDER_FIND'] = 'Заказ найден в МС, выгрузка отменена.';
$MESS['LOG_AGENT_ORDER_FIND_BX'] = 'Проверка существования заказа в БУС.';
$MESS['LOG_AGENT_ORDER_FIND_BX_FAIL'] = 'Заказ не найден в БУС.';
$MESS['LOG_AGENT_ORDER_BX_DELAY_FINISH'] = 'Прекращения работы агента выгрузки (закончилось время жизни агента).';
$MESS['LOG_AGENT_ORDER_RESTART'] = 'Повторный запуск агента на выгрузку.';
$MESS['LOG_AGENT_ORDER_IMPORT_START'] = 'Импорт заказа: #ORDER_HREF#';
$MESS['CREATED_IN_BX'] = 'Заказ создан в БУС: ID #ORDER_ID#';
$MESS['LOG_AGENT_FILTERED_BY_STATUS'] = 'Заказ не прошел фильтр по статусу.';

//agent_on_sale_order_entity_saved_delay.php
$MESS['AGENT_ON_SALE_ORDER_ENTITY_SAVED_DELAY_START'] = 'Заказ: #ORDER_ID# (агент обновления заказа)';
$MESS['AGENT_ON_SALE_ORDER_ENTITY_SAVED_DELAY_FILTERED_BY_STATUS'] = 'Заказ отфильтрован по статусу';
$MESS['AGENT_ON_SALE_ORDER_ENTITY_SAVED_DELAY_ORDER_LOADED'] = 'Заказ найден в МойСклад, выполняется обновление';
$MESS['AGENT_ON_SALE_ORDER_ENTITY_SAVED_DELAY_ORDER_UPDATED'] = 'Заказ успешно обновлен в МойСклад';
$MESS['AGENT_ON_SALE_ORDER_ENTITY_SAVED_DELAY_ORDER_NOT_FOUND_MS'] = 'Заказ не найден в МойСклад';
$MESS['AGENT_ON_SALE_ORDER_ENTITY_SAVED_DELAY_ORDER_FOUND_BX'] = 'Заказ найден в Битриксе';
$MESS['AGENT_ON_SALE_ORDER_ENTITY_SAVED_DELAY_ORDER_BX_DELAY_FINISH'] = 'Заказ в Битриксе устарел, агент завершен';
$MESS['AGENT_ON_SALE_ORDER_ENTITY_SAVED_DELAY_ORDER_NOT_FOUND_BX'] = 'Заказ не найден в Битриксе';
$MESS['AGENT_ON_SALE_ORDER_ENTITY_SAVED_DELAY_ORDER_RESTART'] = 'Перезапуск агента для заказа';

//agent_on_sale_payment_entity_saved_delay.php
$MESS['AGENT_ON_SALE_PAYMENT_ENTITY_SAVED_DELAY_START'] = 'Заказ: #ORDER_ID#, оплата: #PAYMENT_ID# (агент обновления оплаты)';
$MESS['AGENT_ON_SALE_PAYMENT_ENTITY_SAVED_DELAY_FILTERED_BY_STATUS'] = 'Заказ отфильтрован по статусу';
$MESS['AGENT_ON_SALE_PAYMENT_ENTITY_SAVED_DELAY_ORDER_LOADED'] = 'Заказ найден в МойСклад, выполняется обновление платежа';
$MESS['AGENT_ON_SALE_PAYMENT_ENTITY_SAVED_DELAY_PAYMENT_UPDATED'] = 'Платеж успешно обновлен в МойСклад';
$MESS['AGENT_ON_SALE_PAYMENT_ENTITY_SAVED_DELAY_ORDER_NOT_FOUND_MS'] = 'Заказ не найден в МойСклад';
$MESS['AGENT_ON_SALE_PAYMENT_ENTITY_SAVED_DELAY_ORDER_FOUND_BX'] = 'Заказ найден в Битриксе';
$MESS['AGENT_ON_SALE_PAYMENT_ENTITY_SAVED_DELAY_ORDER_BX_DELAY_FINISH'] = 'Заказ в Битриксе устарел, агент завершен';
$MESS['AGENT_ON_SALE_PAYMENT_ENTITY_SAVED_DELAY_ORDER_NOT_FOUND_BX'] = 'Заказ не найден в Битриксе';
$MESS['AGENT_ON_SALE_PAYMENT_ENTITY_SAVED_DELAY_ORDER_RESTART'] = 'Перезапуск агента для платежа';

//inlcude.php
$MESS['SYNC_DISABLED'] = 'Не участвует в обмене';
$MESS['AGENT_SETTED'] = 'Агент установлен';
$MESS['CREATED_BX'] = 'Создан в БУС';
$MESS['UPDATED_BX'] = 'Изменен в БУС';
$MESS['DELIVERY_SERVICE_NAME'] = 'Доставка сайт';
$MESS['ERROR_DATE_INPUT'] = 'Ошибка установки даты';

$MESS['LOC_TYPE_OBLAST_FULL'] = 'область';
$MESS['LOC_TYPE_OBLAST_SHORT'] = 'обл';
$MESS['LOC_TYPE_REPUBLIC_FULL'] = 'республика';
$MESS['LOC_TYPE_REPUBLIC_SHORT'] = 'респ';
$MESS['LOC_TYPE_AO_FULL'] = 'автономный округ';
$MESS['LOC_TYPE_AO_SHORT'] = 'ао';
$MESS['LOC_TYPE_AOBL_FULL'] = 'автономная область';
$MESS['LOC_TYPE_AOBL_SHORT'] = 'аобл';
$MESS['LOC_TYPE_CITY_SHORT'] = 'г ';

$MESS['LOC_CITY_TYPE_CITY'] = 'г';
$MESS['LOC_CITY_TYPE_CITY_FULL'] = 'город';
$MESS['LOC_CITY_TYPE_SELO'] = 'село';
$MESS['LOC_CITY_TYPE_SELO_FULL'] = 'село';
$MESS['LOC_CITY_TYPE_HUTOR'] = 'хутор';
$MESS['LOC_CITY_TYPE_HUTOR_FULL'] = 'хутор';
$MESS['LOC_CITY_TYPE_STANICA'] = 'ст-ца';
$MESS['LOC_CITY_TYPE_STANICA_FULL'] = 'станица';
$MESS['LOC_CITY_TYPE_DEREVNYA'] = 'деревня';
$MESS['LOC_CITY_TYPE_DEREVNYA_FULL'] = 'деревня';
$MESS['LOC_CITY_TYPE_SS'] = 'с/c';
$MESS['LOC_CITY_TYPE_SS_FULL'] = 'сельсовет';
$MESS['LOC_CITY_TYPE_PGT'] = 'пгт';
$MESS['LOC_CITY_TYPE_PGT_FULL'] = 'поселок городского типа';
$MESS['LOC_CITY_TYPE_RN'] = 'р-н';
$MESS['LOC_CITY_TYPE_RN_FULL'] = 'район';
$MESS['LOC_CITY_TYPE_POSELOK'] = 'поселок';
$MESS['LOC_CITY_TYPE_POSELOK_FULL'] = 'поселок';

$MESS['LOC_NAME_YAKYT_SHORT'] = '/якутия/';
$MESS['LOC_NAME_YAKYT_FULL'] = '(якутия)';
$MESS['LOC_NAME_SAHA_SHORT'] = 'саха';
$MESS['LOC_NAME_SAHA_FULL'] = 'республика саха (якутия)';
$MESS['LOC_NAME_ALANIA_SHORT'] = 'алания';
$MESS['LOC_NAME_ALANIA_FULL'] = 'республика северная осетия-алания';
$MESS['LOC_NAME_ALANIA_FULL_CUSTOM'] = 'респ северная осетия - алания';
$MESS['LOC_NAME_CHUVASHIA_SHORT'] = 'чувашия';
$MESS['LOC_NAME_CHUVASHIA_FULL'] = 'чувашская республика';
$MESS['LOC_NAME_CHUVASHIA_FULL_CUSTOM'] = 'Чувашская республика - Чувашия';
$MESS['LOC_NAME_UGRA_SHORT'] = 'югра';
$MESS['LOC_NAME_UGRA_FULL'] = 'ханты-мансийский автономный округ';
$MESS['LOC_NAME_UGRA_FULL_CUSTOM'] = 'ханты-мансийский автономный округ - югра';
$MESS['LOC_NAME_KRYM_SHORT'] = 'крым';
$MESS['LOC_NAME_KRYM_FULL'] = 'крым';
$MESS['LOC_NAME_SEVASTOPOL_FULL'] = 'севастополь';
$MESS['LOC_NAME_MOSCOW_FULL'] = 'москва';
$MESS['LOC_NAME_PITER_FULL'] = 'санкт-петербург';

//customerorder.php
$MESS['COMMENT_WITH_DOC_ID_TYPE_PAYMENT'] = 'оплаты';
$MESS['COMMENT_WITH_DOC_ID_TYPE_DEMAND'] = 'отгрузки';
$MESS['COMMENT_WITH_DOC_ID'] = 'Номер #TYPE# в БУС: #ID#';
$MESS['CONSTRUCT_ORDER_FIND_NOT_ONE'] = 'Найдено заказов больше одного. Количество найденных заказов в МС: #SIZE#.';
$MESS['CONSTRUCT_ORDER_FIND_ZERO'] = 'Не найден заказ на стороне МС.';
$MESS['ENTITY_LOAD_BX_ERROR'] = 'Ошибка при загрузке заказа. Входящий ID заказа: #ID#.';
$MESS['ENTITY_LOAD_BX_INPUT_DATA_WRONG'] = 'Не верный формат ID заказа для поиска. Входящий ID заказа: #ID#.';
$MESS['EMPTY_HREF'] = 'Пустая ссылка на заказ.';
$MESS['EMPTY_ACCOUNT_ID'] = 'Пустой номер заказа для поиска.';
$MESS['EMPTY_ORDER_BX'] = 'Не найден заказ в БУС.';
$MESS['EMPTY_ORDER_UID'] = 'Не найден пользователь.';
$MESS['EMPTY_ORDER_UID_ERROR'] = 'Не найден пользователь: #ERROR#';
$MESS['ERROR_WHILE_IMPORT_USER_SET_DEFAULT'] = 'Ошибка создания пользователя: #ERROR# Импортирован пользователь по умолчанию.';
$MESS['IMPORTED_ORDER'] = 'Заказ импортирован в БУС. ID: #ID#.';
$MESS['ERROR_LOAD_STATUS'] = 'Не удалось сопоставить статус из настроек модуля. ID: #ID#.';
$MESS['ERROR_LOAD_ITEM'] = 'Ошибка загрузки позиции заказа. Href: #ID#.';
$MESS['CANT_FIND_BASKET_ITEMS_MS'] = 'Не найден товар на стороне МС. Внешний код товара: #ID#.';
$MESS['ORDER_ALREADY_CREATED'] = 'Заказ уже создан в БУС | ID: #ID#';
$MESS['ADDED_PRODUCT_MS'] = 'Товар <a href="#HREF#">#NAME#</a> создан в МС. Внешний код товара: <b>#ID#</b>';
$MESS['ADDED_SERVICE_MS'] = 'Услуга <a href="#HREF#">#NAME#</a> создана в МС. Внешний код услуги: <b>#ID#</b>';
$MESS['EVENT_BEFORE_IMPORT_STOP'] = 'Импорт заказа остановлен пользовательским событием.';
$MESS['ORDER_COMMENT_TEMPLATE'] = 'Заказ создан на сайте. Его номер: #ORDER_ID#';
$MESS['SUCCESS_ORDER_SAVE_IN_BX'] = 'Заказ <b>#ORDER_ID#</b> успешно сохранен на сайте.';
$MESS['SUCCESS_ORDER_SAVE_IN_MS'] = 'Заказ <b>#ORDER_ID#</b> успешно сохранен в МС.';
//customerorderapi.php
$MESS['ORG_CANT_FIND'] = 'Не указана организация в параметрах модуля';
$MESS['COUNTER_SEARCH_START'] = 'Старт поиска контрагента в МС';
$MESS['COUNTER_SEARCH_FINISH'] = 'Найден/создан контрагент в МС (#NAME#)';
$MESS['CHANGED_BY_EVENT'] = 'Изменен в событии: #EVENT_NAME#';
$MESS['CREATED_IN_MS'] = 'Заказ создан в МС (ID: #ID#)';
$MESS['API_FAIL'] = 'Ошибка в отправке по API';
$MESS['COUNTER_FAIL'] = 'Ошибка создания / поиска контрагента';
$MESS['EMPTY_EMPLOYEE_LIST'] = 'Не найден сотрудник на стороне МС. Email: #EMAIL#';
$MESS['EMPTY_CURRENCY_LIST'] = 'Не найдена валюта на стороне МС. ISO код валюты: #ISO#';
$MESS['EMPTY_PAY_PROP_ID'] = 'Не указано доп. поле для выгрузки типа оплаты';
$MESS['EMPTY_DELIVERY_PROP_ID'] = 'Не указано доп. поле для выгрузки типа доставки';
$MESS['EMPTY_CUSTOMENTITY_LIST'] = 'Не найдены значения справочника в МС';
//counterparty.php
$MESS['COUNTER_SEARCH_CP_WITH_DESPI_MSUSERS'] = 'Поиск контрагента модулем despi.moyskladusers';
$MESS['COUNTER_DESPI_MSUSERS_OFF'] = 'Модуль выключен. Поиск продолжается стандартным способом.';
$MESS['COUNTER_DESPI_MSUSERS_CRAETE_OFF'] = 'Создание контрагента модулем despi.moyskladusers отключено.';
$MESS['COUNTER_DESPI_MSUSERS_CRAETE_FAIL'] = 'Ошибка создания контрагента модулем despi.moyskladusers';
$MESS['COUNTER_SEARCH_CP_WITH_DESPI_MSUSERS_OFF'] = 'Отключен поиск контрагента модулем';
$MESS['COUNTER_ERROR_DESPI_MSUSERS_SEARCH'] = 'Ошибка в модуле при поиске контрагента. Текст ошибки: #ERROR#'; 
$MESS['COUNTER_SEARCH_CP_WITH_DESPI_MSUSERS_SUCCESS'] = 'Контрагент найден модулем despi.moyskladusers';
$MESS['COUNTER_SEARCH_CP_WITH_DESPI_MSUSERS_CREATE_SUCCESS'] = 'Контрагент создан модулем.';
$MESS['SEARCH'] = 'Поиск';
$MESS['EMPTY_USER_ID'] = 'Пустое значение USER_ID';
$MESS['ERROR_FETCH_USER'] = 'Ошибка загрузки пользователя ID: #USER#';
$MESS['EMPTY_HREF_CP'] = 'Пустая ссылка на контрагента.';
$MESS['CANT_FIND_USERBX'] = 'Не найден пользователь на стороне сайта. Внешний код: #XML_ID#';
$MESS['ERROR_XML_ID_EMPTY'] = 'Пустой внешний код у пользователя МС. ID пользователя в МС: #XML_ID#';
//webhook.php
$MESS['WEBHOOK_START'] = 'Вебхук <b>#ACTION#</b> / #TYPE# (id: #ID#)';
$MESS['WEBHOOK_START_FROM_TASKER'] = 'Вебхук <b>#ACTION#</b> / #TYPE# (id: #ID#) (из очереди)';
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


$MESS['WEBHOOK_TASKER_ADD_TASKS'] = 'Задачи обработки вебхуков в очередь добавлены: #COUNT#';
$MESS['WEBHOOK_TASKER_ADD_TASKS_ERROR'] = 'Ошибка добавления задач обработки вебхуков в очередь: #ERROR#';
$MESS['WEBHOOK_TASKER_ADD_TASKS_LOG'] = 'Добавление задач обработки вебхуков в очередь';

$MESS['WEBHOOK_OVERFLOW_HOOKS'] = 'Переполнившие лимит вебхуки отправлены в очередь: #COUNT#';
$MESS['WEBHOOK_OVERFLOW_HOOKS_ERROR'] = 'Ошибка отправки переполнивших хуков в очередь: #ERROR#';
$MESS['WEBHOOK_OVERFLOW_HOOKS_LOG'] = 'Отправка переполнивших хуков в очередь';


$MESS['DELIVERY_PARENT_NAME'] = '#PARENT# (#CURRENT#)';

$MESS['PAY_INFO'] = "К оплате: #SUM# \nОплачено: #PAID# \nОсталось оплатить: #NEED_PAID#";

$MESS['ERROR_EXCHANGE'] = "[rbs.moysklad] #MSG#. Подробнее в <a target='_blank' href='/bitrix/admin/settings.php?lang=ru&mid=rbs.moysklad&tab=log_window'>логах модуля.</a>";

$MESS['ERROR_EXCHANGE_BY_PROFILE'] = "[rbs.moysklad | Профиль: #PROFILE_ID#] #MSG#. Подробнее в <a target='_blank' href='/bitrix/admin/settings.php?lang=ru&mid=rbs.moysklad&profile_id=#PROFILE_ID#&tab=log_window'>логах модуля.</a>";

$MESS['CANT_FIND_DEFAULT_USER'] = 'Не найден пользователь по умолчанию. Будет создан пользователь модулем.';
$MESS['CREATE_DEFAULT_CP_NAME'] = 'Покупатель из сайта';
$MESS['USE_DEFAULT_COUNTERPARTY'] = 'Ошибка при поиске \ создании контрагента, будет использован пользователь по умолчанию.';

//demand.php

$MESS['DEMAND_START_LOGGING'] = 'Выгрузка отгрузок из БУС в МС для заказа ID: #ORDER_ID#';
$MESS['DEMAND_SUCCESS_LOGGING'] = 'успешная выгрузка.';
$MESS['DEMAND_EXPORT_ERROR_ALL'] = 'непредвиденная ошибка при обновлении отгрузок';
$MESS['EXPORT_DEMAND_FOR_SHIPMENT_MESSAGE'] = 'Отгрузка <b>#SHIPMENT_ID#</b>: #MESSAGE#';

$MESS['SUCCESS_UPDATE_SHIPMENT'] = 'Заказ <b>#ORDER_ID#</b>: Отгрузка <b>#ID#</b> успешно обновлена.';
$MESS['SUCCESS_UPDATE_ALL_SHIPMENT'] = 'Заказ <b>#ORDER_ID#</b>: обновлены все отгрузки.';

$MESS['LOG_UPDATE_ORDER'] = 'Заказ <b>#ORDER_ID#</b>: обновление заказа.';
$MESS['LOG_UPDATE_ORDER_AGENT'] = 'Заказ <b>#ORDER_ID#</b>: обновление заказа на агенте.';
$MESS['LOG_UPDATE_ORDER_STATUS'] = 'Заказ <b>#ORDER_ID#</b>: обновление статуса.';
$MESS['LOG_UPDATE_ORDER_CANCEL'] = 'Заказ <b>#ORDER_ID#</b>: отмена заказа.';
$MESS['LOG_UPDATE_ORDER_CANCEL_CANCEL'] = 'Заказ <b>#ORDER_ID#</b>: убран флаг отмены заказа.';
$MESS['LOG_UPDATE_SHIPMENT'] = 'Заказ <b>#ORDER_ID#</b>: обновление отгрузки.';
$MESS['LOG_DELETE_SHIPMENT'] = 'Заказ <b>#ORDER_ID#</b>: удаление отгрузки.';
$MESS['LOG_DELETE_PAYMENT'] = 'Заказ <b>#ORDER_ID#</b>: удаление оплаты.';
$MESS['LOG_UPDATE_PAYMENT'] = 'Заказ <b>#ORDER_ID#</b>: обновление оплаты.';
$MESS['LOG_UPDATE_PAYMENT_AGENT'] = 'Заказ <b>#ORDER_ID#</b>: обновление оплаты на агенте.';

//webhooktaskerworker.php
$MESS['WEBHOOK_TASKER_WORKER_INVALID_PROFILE_ID'] = 'Неверный ID профиля';
$MESS['WEBHOOK_TASKER_WORKER_LINE_ID_EMPTY'] = 'ID линии не может быть пустым';
$MESS['WEBHOOK_TASKER_WORKER_EMPTY_EVENT_HOOK'] = 'Пустой объект веб-хука';
$MESS['WEBHOOK_TASKER_WORKER_ENTITY_ALREADY_EXECUTED'] = 'Веб-хук уже был обработана: #HREF#';
$MESS['WEBHOOK_TASKER_WORKER_ENTITY_PROCESSED'] = 'Веб-хук обработан: #HREF#';
$MESS['WEBHOOK_TASKER_WORKER_ENTITY_PROCESSED_WITH_ERROR'] = 'Веб-хук обработан с ошибкой: #HREF# Ошибка: #ERROR#';
$MESS['WEBHOOK_TASKER_WORKER_QUEUE_ERRORS'] = 'Ошибки в очереди вебхуков';
$MESS['WEBHOOK_TASKER_WORKER_CRITICAL_ERROR'] = 'Критическая ошибка обработки вебхуков: #ERROR#';
$MESS['WEBHOOK_TASKER_WORKER_PROCESSING_TIME'] = 'Время обработки вебхуков из очереди: #TIME# секунд';
$MESS['WEBHOOK_TASKER_WORKER_PROCESSING_TITLE'] = 'Обработка вебхуков из очереди';

/**CONFIG */

$MESS['CONFIG_COUNTERPTY_DONT_SYNC'] = 'Не синхронизировать';
$MESS['CONFIG_COUNTERPTY_EXTERNAL_CODE'] = 'Внешний код';
$MESS['CONFIG_COUNTERPTY_NAME'] = 'Наименование';
$MESS['CONFIG_COUNTERPTY_CODE'] = 'Код';
$MESS['CONFIG_COUNTERPTY_LOGIN'] = 'Логин';
$MESS['CONFIG_COUNTERPTY_EMAIL'] = 'Email';
$MESS['CONFIG_COUNTERPTY_PHONE'] = 'Телефон';
$MESS['CONFIG_COUNTERPTY_FAX'] = 'Факс';
$MESS['CONFIG_COUNTERPTY_ORDER_PROP'] = 'Свойство заказа';

$MESS['CONFIG_COUNTERPTY_POSTALCODE'] = 'Индекс';
$MESS['CONFIG_COUNTERPTY_CITY'] = 'Город';
$MESS['CONFIG_COUNTERPTY_STREET'] = 'Улица';
$MESS['CONFIG_COUNTERPTY_HOUSE'] = 'Дом';
$MESS['CONFIG_COUNTERPTY_APART'] = 'Квартира';
$MESS['CONFIG_COUNTERPTY_ADDINFO'] = 'Доп. адрес';
$MESS['CONFIG_COUNTERPTY_ADDRCOMMENT'] = 'Комментарий к адресу';

$MESS['CONFIG_COUNTERPTY_LEGALTITLE'] = 'Полное наименование';
$MESS['CONFIG_COUNTERPTY_LEGALADDRESS'] = 'Адрес регистрации / Юр. адрес';
$MESS['CONFIG_COUNTERPTY_INN'] = 'ИНН';

$MESS['CONFIG_COUNTERPTY_OKPO'] = 'ОКПО (ЮЛ / ИП)';
$MESS['CONFIG_COUNTERPTY_OGRNIP'] = 'ОГРНИП (ИП)';
$MESS['CONFIG_COUNTERPTY_OGRN'] = 'ОГРН (ЮЛ)';
$MESS['CONFIG_COUNTERPTY_KPP'] = 'КПП (ЮЛ)';

$MESS['CONFIG_COUNTERPTY_TYPE_LEGAL'] = 'Юридическое лицо';
$MESS['CONFIG_COUNTERPTY_TYPE_ENTREPRENEUR'] = 'Индивидуальный предприниматель	';
$MESS['CONFIG_COUNTERPTY_TYPE_INDIVIDUAL'] = 'Физическое лицо';

$MESS['CONFIG_COUNTERPTY_FILTER_SEARCH'] = 'Точный поиск';
$MESS['CONFIG_COUNTERPTY_SEARCH_SEARCH'] = 'Контекстный поиск';

$MESS['CONFIG_PUBLICK_LINK'] = 'Публичная ссылка';
$MESS['CONFIG_COUPON_LIST'] = 'Купоны';
$MESS['CONFIG_ORDER_LID'] = 'ID Сайта';

$MESS['AGENT_CHECK_ORDERS_START'] = 'Агент проверки заказов из МС. Шаг: <b>#OFFSET#</b>';
$MESS['LOG_AGENT_ORDER_FILTER'] = 'Фильтр запроса: <b>#FILTER#</b>';
$MESS['AGENT_CHECK_ORDERS_SUCCESS'] = 'Обработано заказов: #COUNT#';
$MESS['API_ERROR_ALL'] = 'Непредвиденная ошибка запроса к МС.';
$MESS['EMPTY_ENTITY_LIST'] = 'Нет элементов для обработки.';

$MESS['AGENT_NON_EXEC'] = 'Не запускался';

$MESS['EXCEPTION_API_ERROR'] = 'Непредвиденная ошибка API';
$MESS['CONTROLLER_IMPORTCONTROLLER_IMPORT_CUSTOMERORDER_MAIN_MSG'] = 'Разовый импорт "<b>Заказы покупателя</b>": Текущий шаг: <b>#OFFSET#</b> из <b>#SIZE#</b>; Шаг импорта: <b>#LIMIT#</b>;';
$MESS['CONTROLLER_IMPORTCONTROLLER_IMPORT_CUSTOMERORDER_FILTER_MSG'] = 'Фильтр запроса: #FILTER#';
$MESS['CONTROLLER_IMPORTCONTROLLER_IMPORT_CUSTOMERORDER_EMPTY_ROWS'] = 'Нет заказов для обработки';
$MESS['CONTROLLER_IMPORTCONTROLLER_IMPORT_CUSTOMERORDER_START_IMPORT'] = 'Начало импорта заказа из МС. Номер заказа: #ID#';

$MESS['OPTION_UTILS_IMPORT_ONCE_HEAD'] = '<div data-silent-head>Разовый импорт</div>';
$MESS['OPTION_UTILS_IMPORT_ONCE_BTN'] = '<div class="rbs-ms-stocks-
	info-message-left"><a href="/bitrix/admin/settings.php?lang=ru&mid=rbs.moysklad&process=Y&process_name=#PROCESS#&profile_id=#PROFILE_ID#" class="btn-option">Начать разовый импорт</a></div>';
$MESS['OPTION_UTILS_IMPORT_ONCE_NOTE'] = '<div class="ui-alert"><span class="ui-alert-message">- Разовый импорт это процесс пошагового запуска импорта заказов.<br>- Вы будете видеть прогресс импорта и далее сможете посмотреть результат импорта в логах.<br>- Перед импортом вам будет предложено указать дату создания заказа, с которой будет начат импорт заказов.</span></div>';


//timezones
$MESS['TIMEZONES_KALININGRAD_DESC'] = 'KALT - Калининградское время UTC+2 (МСК-1)';
$MESS['TIMEZONES_MOSCOW_DESC'] = 'MSK - Московское время UTC+3 (МСК+0)';
$MESS['TIMEZONES_SAMARA_DESC'] = 'SAMT - Самарское время UTC+4 (МСК+1)';
$MESS['TIMEZONES_YEKATERINBURG_DESC'] = 'YEKT - Екатеринбургское время UTC+5 (МСК+2)';
$MESS['TIMEZONES_OMSK_DESC'] = 'OMST - Омское время UTC+6 (МСК+3)';
$MESS['TIMEZONES_KRASNOYARSK_DESC'] = 'KRAT - Красноярское время UTC+7 (МСК+4)';
$MESS['TIMEZONES_IRKUTSK_DESC'] = 'IRKT - Иркутское время UTC+8 (МСК+5)';
$MESS['TIMEZONES_YAKUTSK_DESC'] = 'YAKT - Якутское время UTC+9 (МСК+6)';
$MESS['TIMEZONES_VLADIVOSTOK_DESC'] = 'VLAT - Владивостокское время UTC+10 (МСК+7)';
$MESS['TIMEZONES_MAGADAN_DESC'] = 'MAGT - Магаданское время UTC+11 (МСК+8)';
$MESS['TIMEZONES_KAMCHATKA_DESC'] = 'PETT - Камчатское время UTC+12 (МСК+9)';

//assets loader
$MESS['DIAGNOSTIC_ASSETS_NOT_FOUND'] = 'Ошибка: не найдены файлы стилей и скриптов';
$MESS['DIAGNOSTIC_CSS_NOT_FOUND'] = 'Ошибка: не найдены файлы стилей';
$MESS['DIAGNOSTIC_JS_NOT_FOUND'] = 'Ошибка: не найдены файлы скриптов';

//license
$MESS['LICENSE_DATA_MESSAGE_HASH_KEY'] = 'Хеш ключ лицензии: <b>#HASH#</b>';
$MESS['LICENSE_DATA_ERROR_GET_HASH'] = 'Ошибка получения хеш-ключа лицензии: <b>#ERROR#</b>';
$MESS['LICENSE_DATA_ERROR_EMPTY_HASH'] = 'Хеш ключ лицензии не получен';
$MESS['LICENSE_DATA_SUCCESS_LICENSE'] = 'Данные лицензии получены успешно';
$MESS['LICENSE_DATA_ERROR_REQUEST'] = 'Запрос на проверку лицензии: <b>#REQUEST#</b>';
$MESS['LICENSE_DATA_ERROR_RESPONSE'] = 'Некорректный ответ от сервиса проверки лицензии: <b>#RESPONSE#</b>';
$MESS['LICENSE_DATA_ERROR_GET_DATA'] = 'Ошибка получения данных лицензии: <b>#ERROR#</b>';
$MESS['LICENSE_DATA_LOG_MODULE_CHECK'] = 'Проверка лицензии модуля <b>#MODULE#</b>';

//module switcher
$MESS['MODULE_SWITCHER_REASON'] = 'Причина: <b>#REASON#</b>. Пользователь ID: <b>#USER_ID#</b>';
$MESS['MODULE_SWITCHER_REASON_IMPORT_ONCE'] = 'Разовый импорт';
$MESS['MODULE_SWITCHER_REASON_OPTION_CHANGE'] = 'Изменение настроек';
$MESS['MODULE_SWITCHER_REASON_MONITORING_CHANGE'] = 'Изменение мониторинга';
$MESS['MODULE_SWITCHER_REASON_AGENT_AUTO_ON'] = 'Агент автоматического включения модуля';
$MESS['MODULE_SWITCHER_ACTION'] = 'Модуль <b>#STATE#</b> глобально';
$MESS['MODULE_SWITCHER_ACTION_STATE_Y'] = 'включен';
$MESS['MODULE_SWITCHER_ACTION_STATE_N'] = 'выключен';
$MESS['MODULE_SWITCHER_CHECK_MODULE_SWITCH_ON'] = 'Проверка автоматического включения модуля';
$MESS['MODULE_SWITCHER_AUTO_ENABLE'] = 'Модуль автоматически включен, так как с момента разового импорта прошло более #TIMEOUT# секунд';
$MESS['MODULE_SWITCHER_AUTO_ENABLE_NOT_ENOUGH_TIME'] = 'Модуль не включен, так как с момента последнего шага разового импорта прошло менее #TIMEOUT# секунд';
$MESS['MODULE_SWITCHER_DELAY_SET_MODULE_WORK'] = 'Установлен агент для автоматического включения модуля: ID <b>#AGENT_ID#</b>';
$MESS['MODULE_SWITCHER_DISABLE_DELAY_SET_MODULE_WORK'] = 'Агент автоматического включения модуля удален';
$MESS['MODULE_SWITCHER_AGENT_NOT_FOUND'] = 'Агент автоматического включения модуля не найден';
$MESS['MODULE_SWITCHER_CHECK_ACTION'] = 'Проверка автовключения модуля';
$MESS['MODULE_SWITCHER_DELAY_ACTION'] = 'Установка агента автовключения модуля';
$MESS['MODULE_SWITCHER_DISABLE_DELAY_ACTION'] = 'Удаление агента автовключения модуля';
$MESS['MODULE_SWITCHER_AGENT_NOT_DELETED'] = 'Агент автоматического включения модуля не удален';
$MESS['MODULE_SWITCHER_IMPORT_ONCE_DISABLED_TIME_NOT_FOUND'] = 'Время последнего шага разового импорта не найдено';

//clear logs
$MESS['DEBUG_FILE_CONTROLLER_CLEAR_FILE_PROCESS_clearDirByTime'] = 'Удаление логов по сроку (#VALUE# дней). Удалено #DELETE_COUNT# файлов (#DELETE_SIZE# mb).';
$MESS['DEBUG_FILE_CONTROLLER_CLEAR_FILE_PROCESS_clearDirByCount'] = 'Удаление логов по количеству (#VALUE# файлов). Удалено #DELETE_COUNT# файлов (#DELETE_SIZE# mb).';
$MESS['DEBUG_FILE_CONTROLLER_CLEAR_FILE_INFO'] = 'Файл: NAME от DATE (SIZE mb)';


//diag app
//main msg
$MESS['DIAG_APP_TAB_logs'] = 'Логи';
$MESS['DIAG_APP_TAB_monitoring'] = 'Мониторинг';
$MESS['DIAG_APP_TAB_checklist'] = 'Чек-лист';
$MESS['DIAG_APP_SUB_TAB_module'] = 'Модуль';
$MESS['DIAG_APP_SUB_TAB_bitrix'] = 'Битрикс';	
$MESS['DIAG_APP_DOCLINK_TEXT'] = 'Документация';
$MESS['DIAG_APP_DOCLINK_VALUE'] = 'https://docs.despi.ru/rbs-moyskladstocks/diag';
//logs
$MESS['DIAG_APP_MESSAGE_currentLog'] = 'Текущий лог';	
$MESS['DIAG_APP_MESSAGE_collapseAll'] = 'Свернуть все логи';
$MESS['DIAG_APP_MESSAGE_expandAll'] = 'Раскрыть все логи';
$MESS['DIAG_APP_MESSAGE_update'] = 'Обновить';
$MESS['DIAG_APP_MESSAGE_clear'] = 'Очистить';
$MESS['DIAG_APP_MESSAGE_search'] = 'Поиск';
$MESS['DIAG_APP_MESSAGE_error'] = 'Ошибки';
$MESS['DIAG_APP_MESSAGE_warning'] = 'Предупреждения';
$MESS['DIAG_APP_MESSAGE_success'] = 'Успешные';
$MESS['DIAG_APP_MESSAGE_noLogs'] = 'Нет логов в файле';
$MESS['DIAG_APP_MESSAGE_noFilteredLogs'] = 'Нет логов по выбранным фильтрам';

$MESS['DIAG_APP_MESSAGE_module_on'] = 'Модуль включен';
$MESS['DIAG_APP_MESSAGE_module_off'] = 'Модуль выключен';
$MESS['DIAG_APP_MESSAGE_module_on_note'] = 'Для мониторинга процессов необходимо включить модуль';

$MESS['DIAG_APP_MESSAGE_logs_description'] = 'Логи 1С-Битрикс не относятся напрямую к модулю. Они указывают на ошибки всего сайта. На этой странице модуль автоматически проверяет свои ошибки в файле.';
$MESS['DIAG_APP_MESSAGE_no_module_errors'] = 'В файле не найдены ошибок модуля.';
$MESS['DIAG_APP_MESSAGE_has_module_errors'] = 'В файле найдены ошибки модуля.';

//logs
$MESS['DIAG_APP_DOCUMENTATION_TEXT'] = 'Документация по включению логов в Битрикс';
$MESS['DIAG_APP_MODE_DISABLED'] = 'Отладка выключена в настройках Битрикса';
$MESS['DIAG_APP_LOG_NOT_CONFIGURED'] = 'Логирование не настроено в настройках Битрикса';
$MESS['DIAG_APP_LOG_FILE_NOT_EXISTS'] = 'Файл логов не найден';
$MESS['DIAG_APP_LOG_FILE_EMPTY'] = 'Файл логов пуст';

//profiles
$MESS['PROFILE_NUM_NAME'] = 'Профиль #NUM#';
$MESS['PROFILE_MAIN_NAME'] = 'Главный профиль';
$MESS['ADD_PROFILE'] = 'Добавить профиль';

//ui
$MESS['DIAG_APP_MESSAGE_close'] = 'Закрыть';	
$MESS['DIAG_APP_MESSAGE_more'] = 'Подробнее';

$MESS['REG_ID_00000000-0000-0000-0000-000000000001'] = 'Респ Адыгея';
$MESS['REG_ID_00000000-0000-0000-0000-000000000002'] = 'Респ Башкортостан';
$MESS['REG_ID_00000000-0000-0000-0000-000000000003'] = 'Респ Бурятия';
$MESS['REG_ID_00000000-0000-0000-0000-000000000004'] = 'Респ Алтай';
$MESS['REG_ID_00000000-0000-0000-0000-000000000005'] = 'Респ Дагестан';
$MESS['REG_ID_00000000-0000-0000-0000-000000000006'] = 'Респ Ингушетия';
$MESS['REG_ID_00000000-0000-0000-0000-000000000007'] = 'Кабардино-Балкарская Респ';
$MESS['REG_ID_00000000-0000-0000-0000-000000000008'] = 'Респ Калмыкия';
$MESS['REG_ID_00000000-0000-0000-0000-000000000009'] = 'Карачаево-Черкесская Респ';
$MESS['REG_ID_00000000-0000-0000-0000-000000000010'] = 'Респ Карелия';
$MESS['REG_ID_00000000-0000-0000-0000-000000000011'] = 'Респ Коми';
$MESS['REG_ID_00000000-0000-0000-0000-000000000012'] = 'Респ Марий Эл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000013'] = 'Респ Мордовия';
$MESS['REG_ID_00000000-0000-0000-0000-000000000014'] = 'Респ Саха /Якутия/';
$MESS['REG_ID_00000000-0000-0000-0000-000000000015'] = 'Респ Северная Осетия - Алания';
$MESS['REG_ID_00000000-0000-0000-0000-000000000016'] = 'Респ Татарстан';
$MESS['REG_ID_00000000-0000-0000-0000-000000000017'] = 'Респ Тыва';
$MESS['REG_ID_00000000-0000-0000-0000-000000000018'] = 'Удмуртская Респ';
$MESS['REG_ID_00000000-0000-0000-0000-000000000019'] = 'Респ Хакасия';
$MESS['REG_ID_00000000-0000-0000-0000-000000000020'] = 'Чеченская Респ';
$MESS['REG_ID_00000000-0000-0000-0000-000000000021'] = 'Чувашская республика - Чувашия';
$MESS['REG_ID_00000000-0000-0000-0000-000000000022'] = 'Алтайский край';
$MESS['REG_ID_00000000-0000-0000-0000-000000000023'] = 'Краснодарский край';
$MESS['REG_ID_00000000-0000-0000-0000-000000000024'] = 'Красноярский край';
$MESS['REG_ID_00000000-0000-0000-0000-000000000025'] = 'Приморский край';
$MESS['REG_ID_00000000-0000-0000-0000-000000000026'] = 'Ставропольский край';
$MESS['REG_ID_00000000-0000-0000-0000-000000000027'] = 'Хабаровский край';
$MESS['REG_ID_00000000-0000-0000-0000-000000000028'] = 'Амурская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000029'] = 'Архангельская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000030'] = 'Астраханская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000031'] = 'Белгородская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000032'] = 'Брянская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000033'] = 'Владимирская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000034'] = 'Волгоградская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000035'] = 'Вологодская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000036'] = 'Воронежская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000037'] = 'Ивановская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000038'] = 'Иркутская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000039'] = 'Калининградская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000040'] = 'Калужская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000041'] = 'Камчатский край';
$MESS['REG_ID_00000000-0000-0000-0000-000000000042'] = 'Кемеровская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000043'] = 'Кировская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000044'] = 'Костромская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000045'] = 'Курганская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000046'] = 'Курская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000047'] = 'Ленинградская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000048'] = 'Липецкая обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000049'] = 'Магаданская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000050'] = 'Московская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000051'] = 'Мурманская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000052'] = 'Нижегородская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000053'] = 'Новгородская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000054'] = 'Новосибирская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000055'] = 'Омская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000056'] = 'Оренбургская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000057'] = 'Орловская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000058'] = 'Пензенская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000059'] = 'Пермский край';
$MESS['REG_ID_00000000-0000-0000-0000-000000000060'] = 'Псковская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000061'] = 'Ростовская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000062'] = 'Рязанская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000063'] = 'Самарская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000064'] = 'Саратовская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000065'] = 'Сахалинская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000066'] = 'Свердловская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000067'] = 'Смоленская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000068'] = 'Тамбовская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000069'] = 'Тверская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000070'] = 'Томская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000071'] = 'Тульская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000072'] = 'Тюменская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000073'] = 'Ульяновская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000074'] = 'Челябинская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000075'] = 'Забайкальский край';
$MESS['REG_ID_00000000-0000-0000-0000-000000000076'] = 'Ярославская обл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000077'] = 'г Москва';
$MESS['REG_ID_00000000-0000-0000-0000-000000000078'] = 'г Санкт-Петербург';
$MESS['REG_ID_00000000-0000-0000-0000-000000000079'] = 'Еврейская Аобл';
$MESS['REG_ID_00000000-0000-0000-0000-000000000083'] = 'Ненецкий АО';
$MESS['REG_ID_00000000-0000-0000-0000-000000000086'] = 'Ханты-Мансийский Автономный округ - Югра';
$MESS['REG_ID_00000000-0000-0000-0000-000000000087'] = 'Чукотский АО';
$MESS['REG_ID_00000000-0000-0000-0000-000000000089'] = 'Ямало-Ненецкий АО';
$MESS['REG_ID_00000000-0000-0000-0000-000000000091'] = 'Респ Крым';
$MESS['REG_ID_00000000-0000-0000-0000-000000000092'] = 'г Севастополь';
$MESS['REG_ID_00000000-0000-0000-0000-000000000099'] = 'г Байконур';