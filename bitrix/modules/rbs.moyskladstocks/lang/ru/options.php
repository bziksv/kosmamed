<?

$MESS['MAIN_SETTINGS_DOC'] = '<a target="_blank", href="https://docs.despi.ru#LINK#">Описание раздела в документации</a>';

$MESS['MODULE_SELF_ERROR'] = 'Не установлен модуль';
$MESS['MODULE_IBLOCK_ERROR'] = 'Не установлен модуль информационные блоки';
$MESS['MODULE_CATALOG_ERROR'] = 'Не установлен модуль торгового каталога';
$MESS['MODULE_SALE_ERROR'] = 'Не установлен модуль интернет магазина';
$MESS['MODULE_HL_ERROR'] = 'Не установлен модуль HL-блоков';
$MESS['MODULE_CURRENCY_ERROR'] = 'Не установлен модуль валют';

$MESS['EMPTY_PROCESS'] = 'Не найден файл процесса';
$MESS['SAVE_PARAMS_FOR_NEXT_ACTIONS'] = 'Для продолжения сохарните настройки модуля';
$MESS['DEMO_EXPIRED'] = 'Демонстрационный период модуля истек, для дальнейшей работы необходимо купить лицензию на использование модуля.';

$MESS['DEMO_WORK'] = 'Демонстрационный режим.';

$MESS['MAIN_SETTINGS_MAIN'] = 'Настройки';
$MESS['MAIN_SETTINGS_MAIN_DESCR'] = 'Настройки модуля';

$MESS['MAIN_SETTINGS_HEAD'] = 'Авторизация <a target="_blank", href="https://docs.despi.ru#LINK#">[подробнее]</a>';
$MESS['MAIN_SETTINGS_LOGIN'] = 'Логин';
$MESS['MAIN_SETTINGS_PASS'] = 'Пароль';

$MESS['MAIN_SETTINGS_ENABLAED'] = 'Работа модуля <a target="_blank", href="https://docs.despi.ru#LINK#">[подробнее]</a>';
$MESS['MAIN_ENABLED'] = 'Включить модуль';

//HL TABLES
$MESS['MAIN_SETTINGS_HL_TABLES'] = 'Таблицы для кеширования <a target="_blank", href="https://docs.despi.ru#LINK#">[подробнее]</a>';

$MESS['HL_CACHE_TABLE_NOTE'] = 'Таблица кеша для <b>#TYPE#</b> создана: <a target="_blank" href="/bitrix/admin/highloadblock_rows_list.php?ENTITY_ID=#ID#&lang=ru">[#ID#] #NAME#</a>';
$MESS['HL_CACHE_TABLE_NOTE_LINK'] = 'Таблица кеша для <b>#TYPE#</b> создана: <a target="_blank" href="/bitrix/admin/highloadblock_rows_list.php?ENTITY_ID=#ID#&lang=ru">[#ID#] #NAME#</a> [<a href="#LINK#">заполнить таблицу</a>]';

$MESS['HL_CACHE_TABLE_CREATE_ERROR_NOTE'] = 'Таблица кеша для <b>#TYPE# не создана</b>.';

$MESS['HL_CACHE_TABLE_NAME_Stocks'] = 'остатков';
$MESS['HL_CACHE_TABLE_NAME_CurrentStocks'] = 'быстрых остатков';
$MESS['HL_CACHE_TABLE_NAME_ExtCodes'] = 'внешних кодов';
$MESS['HL_CACHE_TABLE_NAME_Bundles'] = 'комплектов';
$MESS['HL_CACHE_TABLE_NAME_PFolder'] = 'групп (разделов)';

//HL TABLES

$MESS['PRODUCT_IMPORT_XML_IDS'] = 'Импортировать внешние коды товаров';
$MESS['VARIANT_IMPORT_XML_IDS'] = 'Импортировать внешние коды модификаций';
$MESS['SERVICE_IMPORT_XML_IDS'] = 'Импортировать внешние коды услуг';
$MESS['BUNDLE_IMPORT_XML_IDS'] = 'Импортировать внешние коды комплектов';
$MESS['STOCKS_IMPORT_FIRST'] = 'Импортировать остатки товаров';
$MESS['BUNDLE_IMPORT'] = 'Импортировать состав комплектов';

$MESS['IMPORT_PROCCESS_COUNT'] = 'Обработано элементов: #IN_PROCCESS# из #SIZE#';
$MESS['IMPORT_PROCCESS_PRELOAD'] = 'Подготовка к импорту...';
$MESS['IMPORT_PROCCESS_HAS_ERRORS'] = 'В процессе импорта возникла ошибка, подробнее в логах модуля.';
$MESS['IMPORT_PROCCESS_DONE'] = 'Процесс импорта завершен';

$MESS['IMPORT_PROCCESS_bundle'] = "Обработано комплектов: #IN_PROCCESS# из #SIZE#";
$MESS['IMPORT_PROCCESS_bundle_DONE'] = "Обработка комплектов завершена";


$MESS['WEBHOOK'] = 'Веб-хуки';
$MESS['MAIN_SETTINGS_WEBHOOK'] = 'Настройка веб-хуков';
$MESS['MAIN_SETTINGS_WEBHOOK_URL'] = 'Настройка URL <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';
$MESS['MAIN_SETTINGS_WEBHOOK_OPTION_URL'] = 'Полный путь до веб-хука';
$MESS['MAIN_SETTINGS_WEBHOOK_OPTION_URL_SALT'] = 'Дополнительная проверка веб-хука';
$MESS['MAIN_SETTINGS_WEBHOOK_OPTION'] = 'Установить / обновить веб-хук?';
$MESS['MAIN_SETTINGS_WEBHOOK_OPTION_SETTED'] = 'Веб-хук <b>#ENTITY#</b> на событие <b>#EVENT#</b> установлен по адресу: #URL#';
$MESS['MAIN_SETTINGS_WEBHOOK_OPTION_NEW'] = 'Веб-хук <b>#ENTITY#</b> на событие <b>#EVENT#</b> не установлен';

$MESS['API_ERROR'] = "[Код: #ERROR_ID#] #TEXT#";
$MESS['API_OK'] = 'Установлено подключение к МойСклад';
$MESS['API_GLOBAL_ERROR'] = 'Непредвиденная ошибка при обращении к МойСклад';
$MESS['STORES_COUNT_ERROR'] = 'Не выбраны склады для синхронизации';

$MESS['NON_SYNC'] = 'Не синхронизировать'; 
$MESS['NON_USE'] = 'Не использовать'; 

$MESS['STORES_HEAD'] = 'Остатки';
$MESS['STORES_HEAD_ASSOC'] = 'Соответствие складов Битрикс <> МойСклад <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';

$MESS['STORES_NEED_CREATE_NOTE'] = 'Необходимо <a href="/bitrix/admin/cat_store_list.php?lang=ru" target="_blank">создать склады</a> в БУС для дальнейшей настройки импорта остатков.';

$MESS['CURRENT_STOCKS_HEAD'] = 'Быстрые остатки';
$MESS['CURRENT_STOCKS_NOTE'] = '<b>ВНИМАНИЕ!</b> Перед использованием быстрых остатков отключите весь обмен во вкладке "Остатки" включая агенты. Использовать обе вкладки для обмена остатками не рекомендуется.';
$MESS['CURRENT_STOCKS_PARAMS'] = 'Параметры импорта быстрых остатков <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';

$MESS['CURRENT_STOCKS_IMPORT_TYPE'] = 'Тип импорта остатков';
$MESS['STOCKS_ENTITY'] = 'Тип сущности для импорта остатков';

$MESS['STOCKS_SYNC_COUNT'] = 'Количество записей (остатков) участвующие в выборке из МойСклад:  <a class="js-api-ajax" href="javascript:void(0)" data-type-query="stocks_count" data-replace-class="api-count-result"><span class="api-count-result">[подсчитать]</span></a>';

$MESS['ENTITY_SYNC_COUNT'] = 'Количество элементов участвующие в выборке из МойСклад: <a class="js-api-ajax" href="javascript:void(0)" data-type-query="#entity#_count" data-replace-class="api-count-result-#entity#"><span class="api-count-result-#entity#">[подсчитать]</span></a>';

$MESS['STORES_STOCK_AGENT'] = 'Настройка агента остатков <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';
$MESS['STORES_CURR_STOCK_AGENT'] = 'Настройка агента быстрых остатков <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';
$MESS['STOCKS_AGENT'] = 'Установить агент';
$MESS['STORES_STOCK_LIMIT'] = 'Лимит выборки остатков за шаг';
$MESS['STORES_STOCK_TIME'] = 'Частота вызова агента (секунды)';

$MESS['STORES_CURR_STOCK_FULL_UPD_TIME'] = 'Интервал времени для полного обновления остатков';
$MESS['STORES_CURR_STOCK_AGENT_NOTE'] = 'В указанный интервал модуль будет выполнять полный обмен остатками из МойСклад. В любое другое время будут браться только измененные данные по остаткам. Также полный обмен проводится при каждом пересохранении настроек модуля.';

$MESS['STORES_STOCK_FILTER'] = 'Фильтр выборки остатков <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';
$MESS['STORES_STOCK_FILTER_ENABLE'] = 'Включить фильтр остатков';
$MESS['STORES_STOCK_FILTER_GROUP'] = 'Группа';
$MESS['STORES_STOCK_FILTER_FLAG'] = 'Свойство-флаг';
 
$MESS['STOCKS_TYPE'] = 'Тип импорта остатков на складах';
$MESS['STOCKS_TYPE_S'] = '[S] Остатки на складах';
$MESS['STOCKS_TYPE_A'] = '[A] Доступное количество';
$MESS['STOCKS_TYPE_T'] = '[T] Доступное количество на складе';

$MESS['STOCKS_QTY'] = 'Импорт поля "Доступное количество"';
$MESS['ALL_QTY'] = 'Сумма всех синхр. складов модуля';
$MESS['ALL_MS_QTY'] = 'Сумма всех доступных складов в МойСклад';
$MESS['ALL_BX_QTY'] = 'Сумма всех доступных складов в Битрикс';
$MESS['ALL_STOCKS_MS_QTY'] = '[Сумма остатков со всех складов МС]';

$MESS['CURR_STOCKS_QTY_LIMIT'] = 'Шаг импорта остатков';
$MESS['CURR_STOCKS_FULL_QTY_LIMIT'] = 'Шаг импорта полного обмена остатками';

$MESS['STOCKS_SYNC_PRODUCT'] = 'Товары синхронизируются по внешнему коду из МойСклад.';
$MESS['STOCKS_SYNC_BUNDLE'] = 'Комплекты синхронизируются по внешнему коду из МойСклад, при этом на стороне Битрикс будет искаться обычный товар, а не комплект. Если на стороне Битрикс заведены именно комплекты, то нужно включить просто синхронизацию товаров выше.';
$MESS['STOCKS_SYNC_TYPE'] = 'Тип синхронизации отвечает за то, будут ли выгружаться остатки (тип S) или доступное количество (остатки + в ожидании - резерв) (тип A) или доступное количество на складе (остатки - резерв).';

$MESS['PRICES_HEAD'] = 'Цены';
$MESS['PRICES_HEAD_ASSOC'] = 'Соответствие цен Битрикс <> МойСклад <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';

$MESS['PRODUCT_HEAD'] = 'Товары';
$MESS['STOCKS_SYNC'] = 'Включить импорт остатков';
$MESS['PRICES_SYNC'] = 'Включить импорт цен';
$MESS['VARIANT_HEAD'] = 'Модификации';
$MESS['BUNDLE_HEAD'] = 'Комплекты';


$MESS['PRODUCT_HEAD_STOCKS'] = 'Товары <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';
$MESS['VARIANT_HEAD_STOCKS'] = 'Модификации <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';
$MESS['BUNDLE_HEAD_STOCKS'] = 'Комплекты <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';
$MESS['BUNDLE_STOCKS_NOTE'] = '<b>ВНИМАНИЕ!</b> Импорт остатков для комплектов осуществляется при условии, что запущен обычный импорт остатками выше и выбран тип импорта комплектов "Товары" (настраивается во вкладке "Комплекты"). Если все условия соблюдены, то настройте импорт остатков ниже и включите агент.';

$MESS['SERVICE_HEAD'] = 'Услуги';


$MESS['AGENT_HEAD'] = 'Агенты';
$MESS['AGENT_HEAD_LIST'] = 'Список агентов модуля';
$MESS['AGENT_INTERVAL'] = 'Интервал срабатывания агентов (сек)';


$MESS['LOGGER_HEAD'] = 'Отладка';
$MESS['LOGGER_API_ENABLED'] = 'Выводить ошибки API при запросе к МойСклад';
$MESS['LOGGER_BX_ENABLED'] = 'Выводить ошибки при обработке событий на сайте';
$MESS['LOGGER_API_REQUESTS'] = 'Выводить все запросы API';
$MESS['LOGGER_NOTE'] = 'Логи выводятся в соответствующей вкладке настроек (лог отладки)';

$MESS['LOGGER_API_HEAD'] = 'Отладка обмена товаров по API'; 
$MESS['LOGGER_API_EXCHANGE'] = 'Логировать выгрузку товаров по API';
$MESS['LOGGER_API_EXCHANGE_NOTIFY'] = 'Выводить уведомление об ошибке в админке Битрикс';

$MESS['LOGGER_FILE'] = 'Файлы отладки';
$MESS['LOGGER_FILE_SIZE'] = 'Максимальный размер файла (кб)'; 
$MESS['LOGGER_FILE_SIZE_NOTE'] = 'При превышении лимита, будет создан новый файл. Старый файл будет сохранен в папке с логами (/bitrix/modules/rbs.moyskladstocks/logs/)'; 



$MESS['CACHE_HEAD'] = 'Кеш';
$MESS['CACHE_REFRESH_API'] = 'Сбросить кеш запросов API к МойСклад'; 
$MESS['CACHE_REFRESH_API_NOTE'] = 'Будет обновлен кеш запросов к МойСклад. Текущая метка кеша: #CACHE_API_ID#';


$MESS['LOG'] = 'Лог';

$MESS['IMPORT_HEAD'] = 'Импорт';
$MESS['IMPORT_HEAD_product'] = 'Товары';
$MESS['IMPORT_HEAD_variant'] = 'Модификации';
$MESS['IMPORT_HEAD_bundle'] = 'Комплекты';
$MESS['IMPORT_HEAD_service'] = 'Услуги';
$MESS['IMPORT_HEAD_productfolder'] = 'Группы';

$MESS['IMPORT_HEAD_PRICES_product'] = 'Товары <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';
$MESS['IMPORT_HEAD_PRICES_variant'] = 'Модификации <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';
$MESS['IMPORT_HEAD_PRICES_bundle'] = 'Комплекты <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';
$MESS['IMPORT_HEAD_PRICES_service'] = 'Услуги <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';

$MESS['IMPORT_NEW'] = 'Основные настройки <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';

$MESS['FILTER_SECTION'] = 'Фильтрация элементов <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';


$MESS['IMPORT_FIELDS_TABLE'] = 'Импортируемые поля <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';
$MESS['IMPORT_FIELDS_TABLE_NOTE'] = 'В этом разделе вы можете выбрать какие конкретно поля нужно импортировать в товары. При этом вы также можете указать импортироваться поле будет для новых товаров или для текущих.';

$MESS['IMPORT_FIELDS'] = 'Что импортируем у новых элементов? <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';

$MESS['ENABLE_IMPORT'] = 'Включить импорт';
$MESS['IBLOCK_ID'] = 'Инфоблок';
$MESS['SECTION_ID'] = 'Раздел в Битрикс куда импортируем';
$MESS['ROOT_SECTION'] = 'Корневой раздел';
$MESS['GROUP_ID'] = 'Группа в МойСклад из которой импортируем';

$MESS['IMPORT_HEAD_PARAMS_IMPORT'] = '<h3>Параметры импорта МойСклад -> Битрикс</h3>';
$MESS['IMPORT_HEAD_PARAMS_IMPORT_NOTE'] = 'Параметры импорта влияют на то, как будут создаваться новые товары в Битрикс из товаров в МойСклад. Товар считается новым если его нет в Битрикс по внешнему коду в целом.'; 
$MESS['IMPORT_HEAD_PARAMS_STRUCTURE'] = '<b>Структура импорта</b>';
$MESS['IMPORT_PARAMS_SECTION_OFF'] = 'Не использовать структуру разделов из МойСклад при импорте';
$MESS['IMPORT_PARAMS_SECTION_KEEP'] = 'Не изменять разделы, если у товара выбрано более 1 раздела на сайте';
$MESS['IMPORT_PARAMS_SECTION_OFF_NOTE'] = 'При проставленной опции товары из МойСклад будут выгружаться в выбранный раздел инфоблока Битрикс, игнорируя структуру разделов МойСклад в которой они лежат.'; 

$MESS['IMPORT_HEAD_PARAMS_DEFS'] = '<b>Общие</b>';
$MESS['HOOK_PARAMS_CODE_UNIQ'] = 'Проверять символьный код на уникальность';
$MESS['HOOK_PARAMS_CODE_UNIQ_NOTE'] = 'При совпадении символьного кода будет создан постфикс в виде ID товара для сохранения уникальности кода.';
$MESS['HOOK_PARAMS_CODE_UNIQ_NOTE_SEC'] = 'При совпадении символьного кода будет создан постфикс в виде ID раздела для сохранения уникальности кода.';
$MESS['HOOK_PARAMS_UOM'] = 'Единица измерения';
$MESS['HOOK_PARAMS_UOM_NOTE'] = 'Единица измерения синхронизируется по полю "Код" в Битрикс и соответствует полю "Цифровой код" в МойСклад (при редактировании ед. измерения в МойСклад можно увидеть "Цифровой код").';

$MESS['IMPORT_HEAD_PARAMS_DESCR'] = '<b>Поля описаний</b>';
$MESS['IMPORT_HEAD_PARAMS_BASE'] = '<b>Основные поля</b>';
$MESS['IMPORT_PARAMS_DESCR'] = 'Импортировать описание в анонс';
$MESS['IMPORT_PARAMS_DESCR_FULL'] = 'Импортировать описание в детально';
$MESS['IMPORT_PARAMS_DESCR_FULL_SOURCE'] = 'Источник детального описания';
$MESS['IMPORT_PARAMS_DESCR_SOURCE'] = 'Источник описания анонса';
$MESS['DESCR_FIELD'] = 'Стандартное поле в МойСклад';
$MESS['DESCR_FIELD_PARENT'] = 'Стандартное поле в МойСклад (у товара)';

$MESS['IMPORT_HEAD_PARAMS_PIC'] = '<b>Поля картинок</b>';
$MESS['IMPORT_PARAMS_IMG_DEL'] = 'Удалять картинки при отсутствии';
$MESS['IMPORT_PARAMS_IMG'] = 'Импортировать картинку в анонс';
$MESS['IMPORT_PARAMS_IMG_FULL'] = 'Импортировать картинку в детально';
$MESS['IMPORT_PARAMS_IMG_PROP'] = 'Импортировать остальные картинки в свойство';
$MESS['IMPORT_PARAMS_IMG_MORE_ALL'] = 'Импортировать в свойство все картинки';

$MESS['IMPORT_HEAD_PARAMS_PRODUCT'] = '<b>Поля торгового каталога</b>';
$MESS['IMPORT_PARAMS_WEIGHT'] = 'Импортировать вес';
$MESS['IMPORT_PARAMS_WEIGHT_M'] = 'Коэффициент веса';
$MESS['IMPORT_PARAMS_SIZES_length'] = 'Импортировать длину';
$MESS['IMPORT_PARAMS_SIZES_height'] = 'Импортировать высоту'; 
$MESS['IMPORT_PARAMS_SIZES_width'] = 'Импортировать ширину';
$MESS['IMPORT_PARAMS_SIZES_V'] = 'Рассчитывать объем по формуле Д*В*Ш'; 
$MESS['IMPORT_PARAMS_SIZES__NOTE'] = 'Для выбора полей габаритов на стороне МойСклад необходимо создать соответствующее доп. поле. <br>Коэффициент веса - величина на которую умножается вес при импорте в Битрикс (по-умолчанию 1000).';

$MESS['IMPORT_HEAD_PARAMS_CONFIG'] = '<b>Настройки</b>';
$MESS['IMPORT_HEAD_PARAMS_AGENT'] = '<b>Агент</b>';

$MESS['IMPORT_PARAMS_ONCE'] = 'Импортировать все данные единожды при пустых значениях'; 

$MESS['IMPORT_HEAD_AGENT_WITHOUT_LINK'] = 'Агент';

$MESS['IMPORT_HEAD_AGENT'] = 'Агент <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';
$MESS['IMPORT_HEAD_AGENT_ON'] = '';
$MESS['IMPORT_HEAD_AGENT_OFF'] = '<span style="color:red;">Агент не установлен</span>';
$MESS['IMPORT_HEAD_AGENT_INFO'] = '<a href="javascript:void(0);" class="js-agent-toggle-info" style="color:green;">Агент установлен</a><br><div class="agent-info"><pre><a target="_blank" href="/bitrix/admin/agent_edit.php?ID=#ID#&lang=ru">#NAME#</a></pre>Активность: <b>#ACTIVE#</b><br>Последний запуск: <b>#LAST_EXEC#</b><br>Следующий запуск: <b>#NEXT_EXEC#</b></div>';
$MESS['IMPORT_ITEM_LIMIT'] = 'Лимит выборки товаров за шаг';
$MESS['IMPORT_SEC_LIMIT'] = 'Лимит выборки групп за шаг';

$MESS['IMPORT_HEAD_AGENT_INFO_FOR_CRON'] = '<span style="color:green;">Агент будет запущен с помощью модуля</span>';

$MESS['IMPORT_HEAD_HOOK_SECTION'] = 'Что обновляем у существующих разделов? (на веб-хуке) <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';
$MESS['IMPORT_UPDATE_SECTION'] = 'Что обновляем у существующих разделов? (на агенте) <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';

$MESS['IMPORT_HEAD_HOOK'] = 'Что обновляем у существующих элементов? (на веб-хуке) <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';
$MESS['IMPORT_UPDATE'] = 'Что обновляем у существующих элементов? (на агенте) <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';
$MESS['IMPORT_HOOK'] = 'Импортировать при создании в МойСклад через веб-хук';
$MESS['HOOK_PARAMS_NAME'] = 'Название';
$MESS['HOOK_PARAMS_CODE_GEN'] = 'Генерировать символьный код';
$MESS['HOOK_PARAMS_CODE'] = 'Символьный код';
$MESS['HOOK_PARAMS_DESCR'] = 'Описание анонса';
$MESS['HOOK_PARAMS_DESCR_FULL'] = 'Детальное описание';
$MESS['HOOK_PARAMS_IMG'] = 'Картинка анонса';
$MESS['HOOK_PARAMS_IMG_FULL'] = 'Детальная картинка';
$MESS['HOOK_PARAMS_IMG_PROP'] = 'Остальные картинки';
$MESS['HOOK_PARAMS_SIZES'] = 'Габариты (вес, длина, ширина, высота)';
$MESS['HOOK_PARAMS_PROPS'] = 'Свойства';

$MESS['HOOK_PARAMS_EVENTS'] = '<b>Параметры событий веб-хука</b>';
$MESS['HOOK_PARAMS_CREATE_ALWAYS'] = 'Событие создания сущности на веб-хуке';
$MESS['HOOK_CREATE'] = '[CREATE] Событие CREATE';
$MESS['HOOK_ALL'] = '[ALL] Событие CREATE и UPDATE';
$MESS['HOOK_PARAMS_CREATE_NOTE'] = 'По умолчанию создание сущности происходит только при веб-хуке CREATE';

/* $MESS['EXPORT_HEAD_PARAMS'] = 'Параметры, которые перезаписываем на стороне МойСклад при сохранении сущности в Битрикс';
$MESS['EXPORT_PARAMS_SIZES'] = 'Габариты';
$MESS['EXPORT_PARAMS_VOLUME'] = 'Объем (рассчитывается по формуле Д*Ш*В)'; */

$MESS['IMPORT_PROPS'] = 'Соответствие свойств <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';
$MESS['IMPORT_PROPS_NOTE'] = 'В этом разделе настраивается соответствие свойств, чтобы свойства начали импортироваться в товары, нужно в разделе настроек ниже ("Импортируемые поля ") выбрать поле "свойства".'; 
$MESS['PROP_LIST'] = 'Выберите свойства инфоблока Битрикс для импорта';
$MESS['ITEM_CODE_FIELD'] = '[Поле МС] Код';
$MESS['ITEM_ARTICLE_FIELD'] = '[Поле МС] Артикул';
$MESS['ITEM_ARTICLE_PARENT_FIELD'] = '[Родительское поле МС] Артикул';
$MESS['ITEM_BARCODE_FIELD'] = '[Поле МС] Штрихкод (первое значение)';
$MESS['ITEM_FILE_FIELD'] = '[Поле МС] Файлы';
$MESS['ITEM_BARCODE_FIELD_EAN8'] = '[Поле МС] Штрихкод (EAN8)';
$MESS['ITEM_BARCODE_FIELD_EAN13'] = '[Поле МС] Штрихкод (EAN13)';
$MESS['ITEM_BARCODE_FIELD_CODE128'] = '[Поле МС] Штрихкод (CODE128)';
$MESS['ITEM_BARCODE_FIELD_GTIN'] = '[Поле МС] Штрихкод (GTIN)';
$MESS['ITEM_BARCODE_FIELD_UPC'] = '[Поле МС] Штрихкод (UPC)';
$MESS['ITEM_CTRY_FIELD'] = '[Поле МС] Страна';
$MESS['ITEM_SUPPLIER_FIELD'] = '[Поле МС] Поставщик';

$MESS['IMPORT_VARIANTS'] = 'Модификации (торговые предложения)';
$MESS['ENABLE_IMPORT_VARIANT'] = 'Импортировать модификации';
$MESS['IMPORT_FIELDS_VARIANTS'] = 'Соответствие характеристик модификаций <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';
$MESS['IS_IMPORT_FIELDS_VARIANTS'] = 'Применять соответствие свойств модификаций';
$MESS['IS_IMPORT_FIELDS_VARIANTS_NOTE'] = 'При проставленной опции соответствие свойств будет браться из настроек модуля, иначе свойства будут созданы автоматически.';
$MESS['IS_IMPORT_FIELDS_VARIANTS_VALS'] = 'Искать значения свойств по названию';
$MESS['IS_IMPORT_FIELDS_VARIANTS_VALS_NOTE'] = 'При проставленной опции соответствие значений свойств будет происходит по названию свойства, иначе значения будут находиться по внешнему коду.';

$MESS['USER_ID'] = 'Записывать изменения под пользователем (ID)';

$MESS['HOOK_PARAMS_ARCHIVED'] = 'По архивности';
$MESS['HOOK_PARAMS_ARCHIVED_PF'] = 'Активность (по архивности группы в МС)';
$MESS['IMPORT_PARAMS_SECTION_OUTER_PF'] = 'Активность (при выходе за пределы группы в МС)';
$MESS['HOOK_PARAMS_ACTIVE_BY_FILTER'] = 'По фильтру';
$MESS['IMPORT_PARAMS_SECTION_OUTER'] = 'По группе';
$MESS['HOOK_PARAMS_DELETED'] = 'Удаление (если удалено в МС)';
$MESS['IMPORT_PARAMS_MS_SECTION_ROOT'] = 'Группа в МойСклад является корневой';
$MESS['IMPORT_PARAMS_MS_SECTION_ROOT_NOTE'] = 'Если выбрана группа в МойСклад для импорта, то при проставленной опции, она не импортируется и будет корневой';

$MESS['HOOK_PARAMS_FACET'] = 'Обновлять фасетный индекс элементов';

$MESS['CURRENCY_SYNC'] = 'Соответствие валют Битрикс <> МойСклад <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';

$MESS['STORES_HEAD_TYPE'] = 'Тип обмена остатков';
$MESS['STOCKS_TYPE_API'] = 'Тип обмена';

$MESS['STORES_HEAD_TYPE_ALL'] = '[ALL] Все склады одновременно';
$MESS['STORES_HEAD_TYPE_ONESTOCK'] = '[ONESTOCK] По одному складу';

$MESS['STOCKS_TYPE_API_NOTE'] = 'Тип обмена ALL использует метод АПИ, в котором для каждого товара запрашиваются все склады сразу. Метод ONESTOCK использует запрос остатков по одному складу. <br> Если не работает первый метод обмена, то можно попробовать использовать второй, т.к. при больших количествах товаров АПИ может не отдать все склады сразу.';

$MESS['IMAGES_HEAD'] = 'Картинки';
$MESS['FILES_HEAD'] = 'Файлы';
$MESS['IMG_GEN_FNAME'] = 'Изменять имя файла';
$MESS['IMG_GEN_FNAME_NOTE'] = 'Опция позволяет выбрать как будет изменено имя файла при импорте в Битрикс.';
$MESS['MD5'] = 'Функция md5';
$MESS['TRANSLIT'] = 'Транслит';
$MESS['SOURCE'] = 'Не изменять';

$MESS['API_ATTEMPTS'] = 'Параметры попыток обращений к API';
$MESS['ATTEMPT_API_ERROR_COUNT'] = 'Количество попыток повторного запроса при ошибке';
$MESS['ATTEMPT_API_ERROR_DELAY_MS'] = 'Задержка в миллисекундах между попытками';
$MESS['ATTEMPT_API_ERROR_COUNT_NOTE'] = 'При возникновении любой ошибки API, модуль будет пытаться повторить запрос несколько раз с определенной задержкой. Настройки выше позволяют указать количество попыток и интервал между попытками. Разрешено не более 10 попыток и интервал не более 2000 миллисекунд (2 секунд).';

$MESS['MAIN_SETTINGS_WEBHOOK_LIMITS'] = 'Ограничения веб-хуков';
$MESS['MAIN_SETTINGS_WEBHOOK_LIMIT_COUNT'] = 'Максимальное число обрабатываемых сущностей за один веб-хук';
$MESS['MAIN_SETTINGS_WEBHOOK_LIMIT_COUNT_INTERVAL'] = 'Интервал между обработкой каждой сущности (миллисекунды)';
$MESS['MAIN_SETTINGS_WEBHOOK_LIMIT_COUNT_NOTE'] = 'Максимальное число сущностей влияет на то, как модуль будет обрабатывать веб-хук при массовом редактировании товаров. К примеру, вы массово отредактировали 20 товаров, а в параметре указано "10", в этом случае модуль обработает первые 10 товаров в веб-хуке с указанным интервалом между каждой сущностью.';

$MESS['IMG_HARD_LOAD'] = 'Загружать картинки принудительно';
$MESS['FILE_HARD_LOAD'] = 'Загружать файлы принудительно';
$MESS['IMG_HARD_LOAD_NOTE'] = 'На данный момент картинки загружаются в папку /upload/msfiles_images/ и при повторной загрузке по имени файла пытается считать уже загруженный файл. При включенной опции картинки будут всегда загружаться напрямую из МойСклад.';

$MESS['CACHE_REFRESH_IMG'] = 'Удалить папку загруженных картинок';


$MESS['IMPORT_PARAMS_DESCR_TYPE_TEXT'] = 'Тип загрузки анонса';
$MESS['IMPORT_PARAMS_DESCR_FULL_TYPE_TEXT'] = 'Тип загрузки детального описания';
$MESS['DELETE_DESCR_IF_EMPTY_IN_MS'] = 'Удалять описание на сайте, если оно пустое в МС';

$MESS['DESCR_TEXT_TYPE_DEFAULT'] = 'По умолчанию';
$MESS['DESCR_TEXT_TYPE_HTML'] = 'HTML';
$MESS['DESCR_TEXT_TYPE_TEXT'] = 'Текст';

$MESS['IMPORT_ONCE'] = 'Разовый импорт <a href="https://docs.despi.ru/rbs-moyskladstocks/template-settings/import-once" target="_blank">[подробнее]</a>';
$MESS['IMPORT_ONCE_START'] = 'Импортировать сущность единожды';

$MESS['IMPORT_ONCE_STEP'] = 'Шаг импорта';

$MESS['HOOK_PARAMS_FOLDER'] = 'Раздел';

$MESS['HOOK_PARAMS_STRUCTURE'] = 'Структура дерева';

$MESS['CURRENCY_SYNC_ENABLE'] = 'Включить обмен валютами';


$MESS['IMPORT_SECTION_OUTER_NOTE'] = 'При выбранной опции сущность будет деактивирован, при выходе за пределы группы выбранной в настройке "Группа в МойСклад". При возврате в группу произойдет активация сущности.';

$MESS['IBLOCK_REQ'] = 'Учитывать инфоблок для импорта остатков и цен';
$MESS['IBLOCK_REQ_NOTE'] = 'Опция позволяет осуществлять поиск элемента исключительно в выбранном инфоблоке (необходимо если на сайте имеются дубли элементов). Распространяется на поиск элемента через веб-хук.';

$MESS['HOOK_PARAMS_CODE_IBLOCK'] = 'Брать параметры транслита символьного кода с инфоблока';
$MESS['HOOK_PARAMS_CODE_IBLOCK_NOTE_ELEM'] = 'Берутся параметры сим. кода элементов: максимальная длины, регистр, замена пробелов и замена остальных символов.';
$MESS['HOOK_PARAMS_CODE_IBLOCK_NOTE_SECT'] = 'Берутся параметры сим. кода разделов: максимальная длины, регистр, замена пробелов и замена остальных символов.<br>ВНИМАНИЕ! Параметры берутся из того инфоблока, в котором производится импорт разделов (актуально если данная опция используется из вкладки "Товары")';

$MESS['HOOK_PARAMS_SEOCACHE'] = 'Сбрасывать кеш SEO-полей инфоблока';

$MESS['HOOK_PARAMS_DATE'] = 'Импортировать только измененные элементы';

$MESS['HOOK_PARAMS_CODE_TRIM'] = 'Удалять пробелы в начале и конце символьного кода';

$MESS['UP_ONLY'] = 'Обрабатывать только существующие элементы';
$MESS['UP_ONLY_NOTE'] = 'При данной опции новые элементы сущности не будут создаваться при работе агента и веб-хука. Модуль будет работать в режиме изменения существующих элементов.';

$MESS['FILTER_PROP'] = 'Свойство-флаг в МС для фильтрации элементов';
$MESS['FILTER_PROP_VALUE'] = 'Значение свойства-флага для фильтрации';
$MESS['FILTER_PROP_Y'] = 'Выгружать когда флаг отмечен';
$MESS['FILTER_PROP_N'] = 'Выгружать когда флаг снят';

$MESS['PRICES_STOCK_LIMIT'] = 'Лимит выборки цен';

/* $MESS['HOOK_PARENT_WEIGHT'] = 'Импортировать вес из родителя';
$MESS['HOOK_PARENT_SIZES'] = 'Импортировать габариты из родителя';
$MESS['HOOK_PARENT_MEASURE'] = 'Импортировать ед. изм. из родителя'; */
$MESS['HOOK_PARENT_WEIGHT_UP'] = 'Вес из родителя';
$MESS['HOOK_PARENT_SIZES_UP'] = 'Габариты из родителя';
$MESS['HOOK_PARENT_MEASURE_UP'] = 'Ед. изм. из родителя';

$MESS['IMPORT_HEAD_PARAMS_VAT'] = '<b>Параметры НДС</b>';
$MESS['IMPORT_HEAD_PARAMS_PARENT_VAT'] = '<b>НДС из родительского товара</b>';
$MESS['HOOK_PARAMS_VAT'] = 'НДС';
$MESS['HOOK_PARAMS_VAT_INC'] = 'Цена включает НДС';

$MESS['STORE_PARENTS'] = 'Суммировать остатки и резервы у дочерних складов';
$MESS['STORE_PARENTS_NOTE'] = 'Если в МойСклад заведены склады в виде дерева, то при выборе родительского склада в обмене, его остатки будут складываться из дочерних.';

$MESS['BUNDLE_TYPE_IMP'] = 'Тип импорта комплектов';
$MESS['BUNDLE_TYPE_IMP_DEFAULT'] = 'Товары';
$MESS['BUNDLE_TYPE_IMP_BUNDLE'] = 'Комплекты';
$MESS['BUNDLE_TYPE_IMP_NOTE'] = 'При импорте комплектов МойСклад как товары, происходит имитация стандартного импорта МойСклад. При импорте как комплекты на стороне Битрикс будут создаваться комплекты и к ним привязываться товары, товары привязываются по внешнему коду, поэтому они должны находится в одном инфоблоке с комплектом. Состав комплекта берется из таблицы кеша.';

$MESS['IMPORT_HEAD_AGENT_BUNDLE_COMPONENT'] = 'Агент выгрузки состава и остатков комплектов <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';

$MESS['IMPORT_PARAMS_CLEAR_XMLID'] = 'Игнорировать решетку # при импорте и поиске модификаций';

$MESS['ALL_PARAMS'] = 'Общие параметры <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';

$MESS['MIN_PRICE'] = '[Служебное] Минимальная цена';
$MESS['BUY_PRICE'] = '[Служебное] Закупочная цена';

$MESS['HOOK_HEAD_ENTITY'] = '#ENTITY#';
$MESS['HOOK_HEAD_EVENT'] = 'Сущность: #ENTITY# / Событие: #EVENT# <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';


$MESS['UPDATE'] = 'Обновить';
$MESS['DELETE'] = 'Удалить';
$MESS['CREATE'] = 'Создать';
$MESS['DEACTIVATE'] = 'Деактивировать';

$MESS['MAIN_SETTINGS_TOKEN'] = 'Токен';
$MESS['MAIN_SETTINGS_AUTH_NOTE'] = 'Для авторизации можно использовать как токен, так и связку логин / пароль. Если вы меняете токен или пароль, то рекомендуется сделать бэкап настроек.<br><br><a class="btn-option" href="/bitrix/admin/settings.php?mid=rbs.moyskladstocks&lang=ru&backup=Y&backup_create=Y&profile_id='.(int)$GLOBALS['rbsMsStocksProfile'].'">Сделать бэкап настроек</a> <a class="btn-option" href="/bitrix/admin/settings.php?mid=rbs.moyskladstocks&lang=ru&backup=Y&backup_get=Y&profile_id='.(int)$GLOBALS['rbsMsStocksProfile'].'">Восстановить настройки</a><br><br><b>ВНИМАНИЕ! В бэкап не записываются данные авторизации МойСклад. После бэкапа рекомендуется пересохранить настройки модуля и проверить веб-хуки <a target="_blank", href="https://docs.despi.ru#LINK#">(подробнее)</a>.</b>';

$MESS['BACKUP_CREATED'] = 'Бэкап сохранен: #NAME#. Дождитесь перезагрузки страницы.';
$MESS['BACKUP_CREATED_FAIL'] = 'Ошибка создания бэкапа';
$MESS['BACKUP_EMPTY'] = 'Папка с бэкапами пуста';
$MESS['BACKUP_DATE'] = 'Выберите дату бэкапа';
$MESS['BACKUP_GET'] = 'Восстановить';
$MESS['BACKUP_OK'] = 'Бэкап успешно восстановлен. Дождитесь перезагрузки страницы.';

$MESS['ACCESS_WRITE_ERROR'] = 'У вас нет прав на сохранение настроек модуля.';

$MESS['DOCS'] = 'Документация';

$MESS['CACHE_TAG_STORES'] = 'Управляемый кеш инфоблоков';
$MESS['CACHE_TAG_STORES_ENABLE'] = 'Сбрасывать управляемый кеш инфоблоков после обмена остатками';
$MESS['CACHE_TAG_STORES_IBLOCKS'] = 'Инфоблоки';
$MESS['CACHE_TAG_STORES_NOTE'] = 'Опция позволяет сбрасывать управляемый кеш инфоблоков после полного обмена остатками.';

$MESS['DOWNLOADS_HEAD'] = 'Файлы и картинки';

$MESS['IMPORT_SYM_CODE'] = '<b>Символьный код</b>';
$MESS['GET_CODE_PF_PARAMS'] = 'Брать параметры сим. кода для разделов из вкладки "Группы"';
$MESS['GET_CODE_PF_PARAMS_NOTE'] = 'Параметры сим. кода будут браться из вкладки "Группы", при этом инфоблок учитывается из текущей вкладки. Если опция не отмечен, то по умолчанию параметры сим. кода разделов: транслит с проверкой на уникальность, замена пробелов и остальных символов: "_"';
$MESS['HOOK_PARAMS_CODE_UNIQ_PARENT'] = 'Проверять уникальность кода внутри секции родителя';
$MESS['HOOK_PARAMS_CODE_UNIQ_PARENT_NOTE_SEC'] = 'Опция работает в связке с проверкой кода на уникальность, при этом уникальность проверяется внутри секции раздела.<br>Внимание! Опция не работает, если проверка на уникальность задана в самом инфоблоке.';

$MESS['PRICES_CLEAR_ZERO'] = 'Удалять нулевые цены';

$MESS['PRICES_RANGE'] = 'Диапазоны цен';
$MESS['PRICES_RANGE_FIRST_USE'] = 'Импортировать цены в первый диапазон (от 1 шт.)';
$MESS['PRICES_RANGE_ALL_USE'] = 'Импортировать цены во все диапазоны (при наличии диапазонов цен)';

$MESS['SEARCH_PROP_TYPE'] = 'Тип поиска свойств типа элементы / справочник';
$MESS['S_XML_ID'] = 'По внешнему коду';
$MESS['S_NAME'] = 'По наименованию';

$MESS['PROP_EMPTY_IMPORT'] = 'Импортировать пустые свойства из МойСклад';

$MESS['HOOK_PARAMS_DELETE'] = 'Удалять элемент (по событию DELETE)';
$MESS['HOOK_PARAMS_DELETE_GROUP'] = 'Удалять группу (по событию DELETE)';
$MESS['UP_PARAMS_DELETE_GROUP'] = 'Действия с удаленными разделами';

$MESS['CACHE_WEBHOOK_TIME'] = 'Кеш веб-хука (секунды)';

$MESS['IMPORT_HEAD_AGENT_CACHE_GROUPS'] = 'Агент кеширования запросов разделов <a target="_blank", href="https://docs.despi.ru#LINK#">[подробнее]</a>';
$MESS['CACHE_EXP_TIME'] = 'Время жизни кеша запросов';

$MESS['CACHE_CLEAR'] = 'Очистить папку кеша модуля';
$MESS['CACHE_CLEAR_NOTE'] = 'Текущий размер папки кеша: #SIZE#';

$MESS['VARIANT_LOAD_AGENT'] = 'Обрабатывать веб-хук отложено на агентах';

$MESS['DISCOUNT_HEAD'] = 'Скидки';
$MESS['DISCOUNT_TYPE_HEAD'] = 'Настройки импорта скидок <a target="_blank", href="https://docs.despi.ru#LINK#">[подробнее]</a>';
$MESS['DISCOUNT_SYNC'] = 'Включить импорт скидок';
$MESS['DISCOUNT_MODULE'] = 'Модуль обработки скидок';
$MESS['DISCOUNT_SITE_ID'] = 'Сайт для скидок в Битрикс';
$MESS['DISCOUNT_CURRENCY'] = 'Валюта';
$MESS['DISCOUNT_USER_GROUPS'] = 'Группы пользователей';
$MESS['DISCOUNT_PRICE_TYPE'] = 'Тип цены';
$MESS['DISCOUNT_LAST_LEVEL'] = 'Прекратить применение скидок на текущем уровне приоритетов';
$MESS['DISCOUNT_LAST'] = 'Прекратить дальнейшее применение правил';
$MESS['DISCOUNT_SORT'] = 'Сортировка';
$MESS['DISCOUNT_PRIORITY'] = 'Приоритет';
$MESS['DISCOUNT_MODULE_CATALOG'] = '[catalog] Скидки на товар';
$MESS['DISCOUNT_MODULE_SALE'] = '[sale] Правила работы с корзиной';
$MESS['DISCOUNT_LIMIT'] = 'Лимит выборки скидок';
$MESS['DISCOUNT_ITEM_SEARCH_HEAD'] = '<b>Настройки поиска товаров для скидок</b>';

$MESS['IMPORT_FIELDS_PARAMS'] = 'Настройки логики импорта <a target="_blank", href="https://docs.despi.ru#LINK#">[подробнее]</a>';
$MESS['IMPORT_HEAD_PARAMS_OTHER'] = '<b>Прочие поля</b>';

$MESS['CIBLOCK_ADD_PARAMS'] = '<b>Параметры при создании товара</b>';
$MESS['CIBLOCK_ADD_WORKFLOW'] = 'Вставка в режиме документооборота';
$MESS['CIBLOCK_ADD_UPDSEARCH'] = 'Индексировать элемент для поиска';
$MESS['CIBLOCK_ADD_RESIZEPIC'] = 'Использовать настройки инфоблока для обработки изображений';

$MESS['BUNDLE_PARTS_IBLOCK_ID'] = 'Инфоблок состава комплектов';

$MESS['START_TYPING_SEARCH_PROP'] = 'Начните вводить название свойства...';
$MESS['SEARCH_PROP'] = 'Поиск свойства...';
$MESS['SAVE_AUTH'] = 'Авторизация';
$MESS['ERROR_AUTH_DATA'] = 'Введите данные авторизации!';
$MESS['ERROR_AUTH_DATA_AJAX'] = 'Ошибка запроса, перезагрузите страницу.';

$MESS['IMPORT_HEAD_PARAMS_SORT'] = '<b>Сортировка</b>';
$MESS['IMPORT_PARAMS_SORT'] = 'Сортировка';
$MESS['IMPORT_PARAMS_SORT_PROP'] = 'Свойство, откуда считываем сортировку';

$MESS['IMPORT_HEAD_PARAMS_RATIO'] = '<b>Коэфф. ед. измерения</b>';
$MESS['IMPORT_PARAMS_RATIO'] = 'Коэфф. ед. измерения';
$MESS['HOOK_PARENT_RATIO'] = 'Родительский коэфф. ед. измерения';
$MESS['IMPORT_PARAMS_RATIO_PROP'] = 'Свойство, откуда считываем коэфф. ед. измерения';

$MESS['IMPORT_PARAMS_INCLUDE_ARCHIVED_PFOLDERS'] = 'Импортировать архивные группы';
$MESS['IMPORT_PARAMS_INCLUDE_ARCHIVED'] = 'Импортировать архивные элементы';
$MESS['IMPORT_PARAMS_IGNORE_SECTION_ACTIVE'] = 'Игнорировать активность разделов';


$MESS['STORES_STOCK_AGENT_IMPORT_ONCE'] = 'Разовый импорт <a href="https://docs.despi.ru#LINK#" target="_blank">[подробнее]</a>';

$MESS['IMPORT_ONCE_ERROR_ENTITY'] = 'Не верные параметры разового импорта.';

$MESS['IMPORT_ONCE_stocks'] = 'остатков';
$MESS['IMPORT_ONCE_product'] = 'товаров';
$MESS['IMPORT_ONCE_variant'] = 'модификаций';
$MESS['IMPORT_ONCE_service'] = 'услуг';
$MESS['IMPORT_ONCE_bundle'] = 'комплектов';
$MESS['IMPORT_ONCE_productfolder'] = 'разделов';
$MESS['IMPORT_ONCE_discount'] = 'скидок';

$MESS['IMPORT_ONCE_STOCKS_START_BTN'] = '<div class="rbs-ms-stocks-info-message-left"><div class="btn-option" data-import-once="#TYPE#">Начать разовый импорт #ENTITY#</div></div>';
$MESS['IMPORT_ONCE_STOCKS_START_NOTE'] = '<div class="rbs-ms-stocks-info-message-right">При нажатии на кнопку, модуль начнет импорт #ENTITY# в соответствии с настройками этой вкладки. Результат выполнения импорта можно посмотреть в логах. На время разового импорта модуль отключит текущий импорт #ENTITY# на агентах.</div>';

$MESS['STOCKS_DOUBLE_TYPE'] = 'Тип обработки дублей';
	$MESS['STOCKS_DOUBLE_TYPE_ASC'] = '[ASC] Импортировать в первый по ID товар';
	$MESS['STOCKS_DOUBLE_TYPE_DESC'] = '[DESC] Импортировать в последний по ID товар';
	$MESS['STOCKS_DOUBLE_TYPE_ALL'] = '[ALL] Импортировать во все товары';
	$MESS['STOCKS_DOUBLE_TYPE_SKIP'] = '[SKIP] Пропускать дубли при импорте';

$MESS['OTHERS_HEAD'] = 'Прочее';

$MESS['IMAGE_WORK'] = 'Работа с картинками';
	$MESS['IMAGE_CLEAR_PREV'] = 'Удалять картинку анонса \ детально, если файла не существует';
	$MESS['IMAGE_CLEAR_PREV_NOTE'] = 'Если вы импортируете картинки модулем и замечаете, что некоторые картинки анонса \ детально не прогружены, то отметив эту опцию модуль будет принудительно удалять эти файлы и загружать заново.';
	$MESS['IMAGE_CLEAR_SIZE'] = 'Перезаливать картинку, если она не совпадает с загруженной по размеру';
	$MESS['IMAGE_CLEAR_SIZE_NOTE'] = 'В редких случаях бывает, что картинка не загружается полностью (обрезается), отметив эту опцию модуль будет сверять принудительно размеры картинок и перезагружать, если размеры не совпадают.';


$MESS['STOCK_BUNDLE_IMPORT_NOTE'] = 'Лимит выборки комплектов при импорте остатков определяется агентом <b>"Агент выгрузки состава и остатков комплектов"</b> во вкладке "Комплекты".';

$MESS['STOCKS_AGENT_IS_CRON'] = 'Запускать агент с помощью модуля';
$MESS['STOCKS_AGENT_IS_CRON_NOTE'] = 'Стандартно агенты выполняются с помощью функционала самого 1С-Битрикс (агенты). При отмеченной опции вам нужно самостоятельно настроить скрипт на запуск с помощью cron. Подробнее читайте в <a href="https://docs.despi.ru/rbs-moyskladstocks/devs/module-agent" target="_blank">документации</a>.';

$MESS['AGENT_ON_MODULE_HEAD'] = 'Вызов агентов с помощью модуля';
$MESS['AGENT_ON_MODULE_NOTE_DOC'] = '<b>ВНИМАНИЕ!</b> Ознакомьтесь с документации по активации этой функции: <a href="https://docs.despi.ru/rbs-moyskladstocks/devs/module-agent" target="_blank">ссылка на документацию</a>. <br>После верной настройки скрипта, галочки у агентов "Запускать агент с помощью модуля" разблокируются автоматически.';
$MESS['AGENT_ON_MODULE_NOTE'] = 'Для вызова агентов с помощью модуля, установите следующий скрипт на cron:<br><br><pre>' . str_replace('lang/ru', 'cron/run_all.php', __DIR__ . ' #PROFILE_ID#</pre>');

$MESS['IBLOCK_NAVIGATION'] = '<a href="/bitrix/admin/iblock_list_admin.php?IBLOCK_ID=#IBLOCK_ID#&type=#IBLOCK_TYPE#&lang=ru&find_section_section=0&SECTION_ID=0&apply_filter=Y" target="_blank">Перейти в инфоблок</a> | <a href="/bitrix/admin/iblock_edit.php?type=#IBLOCK_TYPE#&lang=ru&ID=#IBLOCK_ID#&admin=Y" target="_blank">Перейти в настройки инфоблока</a>';

$MESS['IS_IGNORE_PRODUCT_TYPE'] = 'Не изменять тип товара при импорте';

$MESS['SERVICE_AGENT_WORK_HEAD'] = 'Работа служебных агентов';

$MESS['SERVICE_AGENT_TITLE_update_ext_codes'] = 'Агент обновления внешних кодов';

$MESS['SERVICE_AGENT_NOTE_update_ext_codes'] = 'Агент обновляет внешние коды товаров из МС в таблицу кеша. Можно выбрать время полного обновления всех внешних кодов товаров.';

$MESS['SERVICE_AGENT_TITLE_check_module_agents'] = 'Агент проверки работы всех агентов модуля';
$MESS['SERVICE_AGENT_NOTE_check_module_agents'] = 'Если агенты модуля запущенны стандартным путем (через агенты битрикса), то этот агент будет проверять активность этих агентов.';

$MESS['TIMEZONE_PARAMS'] = 'Параметры часового пояса';
$MESS['IS_EU_MSK_TIMEZONE'] = 'Применять принудительно часовой пояс Москвы при работе с датами';

$MESS['AGENT_HEAD_LINE'] = 'Настройка агента импорта <a href="https://docs.despi.ru/rbs-moyskladstocks/settings/#ENTITY#/agent" target="_blank">[подробнее]</a>';
$MESS['AGENT_HEAD_LINE_STATIC'] = 'Настройка агента импорта';
$MESS['AGENT_ENABLE'] = 'Включить агент';
$MESS['AGENT_FULL_ENABLE'] = 'Провести полный обмен';
$MESS['AGENT_FULL_TIME'] = 'Интервал, когда проводим полный обмен';
$MESS['AGENT_UPDATED_ENABLE'] = 'Обмен только измененными элементами';
$MESS['AGENT_TIME'] = 'Частота вызова агента (секунды)';
$MESS['AGENT_LIMIT'] = 'Шаг выборки элементов';
$MESS['AGENT_OFFSET'] = 'Текущий шаг импорта';
$MESS['AGENT_IS_CRON'] = 'Запускать агент с помощью модуля';
$MESS['AGENT_IS_CRON_NOTE'] = 'Стандартно агенты выполняются с помощью функционала самого 1С-Битрикс (агенты). При отмеченной опции вам нужно самостоятельно настроить скрипт на запуск с помощью cron. Подробнее читайте в <a href="https://docs.despi.ru/rbs-moyskladstocks/devs/module-agent" target="_blank">документации</a>.';

$MESS['NOTE_PRICES_VARIANT_TEXT'] = 'Ознакомьтесь с особенностями импорта цен для модификаций: <a href="https://docs.despi.ru/rbs-moyskladstocks/settings/prices/special-mod" target="_blank">[подробнее]</a>';

$MESS['JS_CONFIG_MESSAGE_LOG_BTN_UPDATE'] = 'Обновить';
$MESS['JS_CONFIG_MESSAGE_LOG_BTN_CLEAR'] = 'Очистить';
$MESS['JS_CONFIG_MESSAGE_LOG_BTN_TOGGLE_OPEN'] = 'Раскрыть';
$MESS['JS_CONFIG_MESSAGE_LOG_BTN_TOGGLE_CLOSE'] = 'Скрыть';
$MESS['JS_CONFIG_MESSAGE_LOG_BTN_DELETE_ALL'] = 'Удалить все логи';
$MESS['JS_CONFIG_MESSAGE_LOG_BTN_GET_DIR'] = 'Перейти в папку логов';
$MESS['JS_OPTION_CONTROLLER_ERROR_AJAX'] = 'Ошибка сохранения опций, перезагрузите страницу и попробуйте снова.';

$MESS['HINT_LOGER_FILES'] = 'Выберите файл логов';
$MESS['LOG_FILE_LABEL_TEMPLATE_DEFAULT'] = 'Текущий лог от #DATE#';
$MESS['LOG_FILE_LABEL_TEMPLATE_FROM'] = 'Лог от #DATE#';

$MESS['MAIN_PROFILE'] = 'Главный профиль';
$MESS['PROFILE_DEF_NAME'] = 'Профиль #NUM#';
$MESS['ADD_PROFILE'] = '[Добавить профиль]';
$MESS['PROFILE_NAME_SAVE_BTN'] = 'Сохранить';
$MESS['PROFILE_NAME_EDIT_BTN'] = 'Переименовать профиль';
$MESS['PROFILE_SETTINGS'] = 'Настройки профиля';

$MESS['IMPORT_SETTINGS_TABLE_FIELDS'] = 'Поле товара';
$MESS['IMPORT_SETTINGS_TABLE_NEW_ITEMS'] = 'Импортируем для новых товаров';
$MESS['IMPORT_SETTINGS_TABLE_CURENT_ITEMS'] = 'Обновляем у текущих товаров (на агенте)';
$MESS['IMPORT_SETTINGS_TABLE_CURRENT_ITEMS_HOOK'] = 'Обновляем у текущих товаров (на веб-хуке)';

$MESS['IMPORT_HEAD_PARAMS_ACTIVE'] = 'Активировать \ Деактивировать';

$MESS['BETA_FUNCTIONS'] = 'Управление бета функциями модуля';

$MESS['IDENTIFY_BY_ID'] = 'Идентифицировать товары по полю ID (вместо внешнего кода)';
$MESS['IDENTIFY_BY_ID_NOTE'] = 'При включенной опции товары будут идентифицироваться по полю ID из МойСклад вместо внешнего кода (externalCode). Таблица внешних кодов при этом не будет использоваться. <b>ВНИМАНИЕ!</b> Перед включением убедитесь, что каталог пуст или XML_ID элементов соответствует ID из МойСклад.';

$MESS['IDENTIFY_SECTIONS_BY_ID'] = 'Идентифицировать разделы (группы товаров) по полю ID (вместо внешнего кода)';
$MESS['IDENTIFY_SECTIONS_BY_ID_NOTE'] = 'При включенной опции разделы (productfolder) будут идентифицироваться по полю ID из МойСклад вместо внешнего кода (externalCode). <b>ВНИМАНИЕ!</b> Перед включением убедитесь, что XML_ID разделов каталога соответствует ID из МойСклад.';

$MESS['CHOOSE_IBLOCK_FOR_ASSOC_PROPS'] = 'Выберите инфоблок для сопоставления свойств.';

$MESS['LOGS_MOVE_TO_DIAG'] = 'Раздел перенесен в <a href="/bitrix/admin/rbs.moyskladstocks_diagnostic.php" target="_blank">диагностику</a>';

$MESS['CURR_STOCKS_MAX_DIFF_SECONDS'] = 'Максимальный интервал выборки измененных быстрых остатков (минуты, максимум 1439 мин.)';

$MESS['PROCESS_NOT_FOUND'] = 'Процесс не найден';

$MESS['IMPORT_HEAD_PARAMS_TRACKING_TYPE'] = 'Тип продукции (маркировка)';
$MESS['HOOK_PARENT_TRACKING_TYPE'] = 'Тип продукции (родительский)';

$MESS['GLOBAL_TIMEZONE'] = 'Принудительная установка часового пояса для всех процессов';
$MESS['GLOBAL_TIMEZONE_N'] = 'Не устанавливать';
$MESS['GLOBAL_TIMEZONE_NOTE'] = 'Настройка переопределяет часовой пояс для всех процессов. Применяется если время лога не соответствует времени сервера.';
$MESS['IS_EU_MSK_TIMEZONE_NOTE'] = 'Настройка влияет только на установку часового пояса при работе с датами в МойСклад.';

$MESS['HOOK_PARAMS_BARCODE'] = 'Штрихкоды';

$MESS['UPDATE_IBLOCK_ELEMENT_PARAMS'] = 'Обновлять элементы инфоблока';
	$MESS['UPDATE_IBLOCK_ELEMENT_PARAMS_NONE'] = 'Не обновлять';
	$MESS['UPDATE_IBLOCK_ELEMENT_PARAMS_AUTO'] = 'Определить автоматически';
	$MESS['UPDATE_IBLOCK_ELEMENT_PARAMS_UPDATE'] = 'Обновлять всегда';

$MESS['IMPORT_HEAD_PARAMS_ACTIVE_PROPERTY'] = '<b>Активность по доп. полю</b>';
$MESS['IMPORT_PARAMS_ACTIVE_PROPERTY_PROP'] = 'Свойство, откуда считываем активность';