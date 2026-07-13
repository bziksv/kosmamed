<?php
$MESS ['IPOLSDEK_transit_header']  = "Внимание!";
$MESS ['IPOLSDEK_transit_content'] = "Интеграция со СДЭК переходит на версию СДЭК API 2.0. Если функционал модуля перестал работать, необходимо на вкладке \"Настройки\" в блоке настроек \"Сервисные свойства\" (кликните на заголовок, чтобы содержимое блока отобразилось) убрать флаг \"Использовать старый API для запросов\" для переключения модуля на использование методов СДЭК API 2.0.";

$MESS ['IPOLSDEK_manyorders_header']  = "Оповещение об обновлении статусов";
$MESS ['IPOLSDEK_manyorders_content'] = "Из-за большого количества заказов обработка статусов может идти с большой задержкой.<br>На данный момент для обработки статусов всех заказов потребуется не менее {COUNT} запусков агента.<br>Рекомендуется увеличить значение опции \"Количество заказов, обрабатываемых за 1 запуск синхронизирующего статусы агента\", чтобы модуль обрабатывал больше заказов за запуск агента и уменьшить \"Интервал запуска агента (мин)\", чтобы агент запускался чаще (не рекомендуется, если выполнение агентов не переведено на cron - уточните у вашего программиста).";

//опции
	// авторизация
$MESS ['IPOLSDEK_OPT_logIml'] = "Логин для работы со СДЭК";
$MESS ['IPOLSDEK_OPT_pasIml'] = "Пароль для работы со СДЭК";
	// общие
$MESS ['IPOLSDEK_OPT_departure'] = "Город-отправитель <a href='#' class='PropHint' onclick='return IPOLSDEK_setups.popup(\"pop-departure\", this);'></a>";
$MESS ['IPOLSDEK_OPT_termInc'] = "Увеличить срок доставки на (дн.)";
$MESS['IPOLSDEK_OPT_showInOrders'] = "Отображать кнопку заявки в заказах";
$MESS['IPOLSDEK_OPT_shipments'] = "Работа с отгрузками";
$MESS['IPOLSDEK_OPT_addDeparture'] = "Дополнительные города-отправители <a href='#' class='PropHint' onclick='return IPOLSDEK_setups.popup(\"pop-addDeparture\", this);'></a>";
    // Печать
$MESS['IPOLSDEK_OPT_prntActOrdr'] = "Действие при печати актов";
$MESS['IPOLSDEK_OPT_numberOfPrints'] = "Число копий одной квитанции на листе";
$MESS['IPOLSDEK_OPT_numberOfStrihs'] = "Число копий штрихкодов на листе";
$MESS['IPOLSDEK_OPT_formatOfStrihs'] = "Формат печати штрихкодов";
	// Размеры по умолчанию
$MESS ['IPOLSDEK_OPT_lengthD'] = "Длина, мм";
$MESS ['IPOLSDEK_OPT_widthD'] = "Ширина, мм";
$MESS ['IPOLSDEK_OPT_heightD'] = "Высота, мм";
$MESS ['IPOLSDEK_OPT_weightD'] = "Вес, г";
$MESS ['IPOLSDEK_OPT_defMode'] = "Рассчитывать средние габариты для ";
	// Общие заявки
$MESS ['IPOLSDEK_OPT_deliveryAsPosition'] = "Указывать доставку отдельной товарной позицией";
$MESS ['IPOLSDEK_OPT_normalizePhone'] = "Адаптация номера телефона";
$MESS ['IPOLSDEK_OPT_addData'] = "Указывать дату доставки";
	// Свойства заявки
$MESS ['IPOLSDEK_OPT_location'] = "Город доставки";
$MESS ['IPOLSDEK_OPT_name'] = "Контактное лицо";
$MESS ['IPOLSDEK_OPT_fName'] = "Имя";
$MESS ['IPOLSDEK_OPT_sName'] = "Фамилия";
$MESS ['IPOLSDEK_OPT_mName'] = "Отчество";
$MESS ['IPOLSDEK_OPT_extendName'] = "Расширенное указание Контактного лица";
$MESS ['IPOLSDEK_LBL_turnOnExtendName'] = "Раздельные свойства";
$MESS ['IPOLSDEK_LBL_turnOffExtendName'] = "Одним свойством";
$MESS ['IPOLSDEK_OPT_email'] = "E-mail";
$MESS ['IPOLSDEK_OPT_phone'] = "Контактный телефон";
$MESS ['IPOLSDEK_OPT_address'] = "Адрес доставки";
$MESS ['IPOLSDEK_OPT_street'] = "Улица";
$MESS ['IPOLSDEK_OPT_house'] = "Дом";
$MESS ['IPOLSDEK_OPT_flat'] = "Квартира";

$MESS ['IPOLSDEK_OPT_comment'] = "Комментарий";
	$MESS ['IPOLSDEK_OPT_comment_N'] = "Не экспортировать";
	$MESS ['IPOLSDEK_OPT_comment_M'] = "Менеджера";
	$MESS ['IPOLSDEK_OPT_comment_B'] = "Покупателя";
$MESS ['IPOLSDEK_OPT_articul'] = "Артикул";
$MESS ['IPOLSDEK_OPT_feacn'] = "Код ТН ВЭД";
$MESS ['IPOLSDEK_OPT_getParentArticul'] = "Брать данные товара, если отсутствуют у торгового предложения";
$MESS ['IPOLSDEK_OPT_addMeasureName'] = "Подписывать размерность дробного количества товаров";
$MESS ['IPOLSDEK_OPT_noVats'] = "Минимизировать страховку товаров";
// НДС
$MESS ['IPOLSDEK_OPT_NDSUseCatalog'] = "Использовать данные из каталога";
$MESS ['IPOLSDEK_OPT_NDSDelivery'] = "Ставка НДС на доставку по умолчанию";
$MESS ['IPOLSDEK_OPT_NDSGoods']    = "Ставка НДС на товары по умолчанию";
	// Обратная связь
$MESS ['IPOLSDEK_OPT_setDeliveryId'] = "Выставлять принятым заказам идентификатор отправления";
$MESS ['IPOLSDEK_OPT_markPayed'] = "Отмечать доставленный заказ оплаченным";
$MESS ['IPOLSDEK_OPT_setTrackingOrderProp'] = "Свойство заказа для сохранения ссылки на отслеживание заказа";
$MESS ['IPOLSDEK_OPT_statusOK'] = "Статус заказа, для которого успешно отправлена заявка в ЛК СДЭК";
$MESS ['IPOLSDEK_OPT_statusSTORE'] = "Статус заказа, доставленного на склад";
$MESS ['IPOLSDEK_OPT_statusTRANZT'] = "Статус заказа, находящегося в пути";
$MESS ['IPOLSDEK_OPT_statusCORIER'] = "Статус заказа, переданного курьеру";
$MESS ['IPOLSDEK_OPT_statusPVZ'] = "Статус заказа, доставленного на пункт самовывоза";
$MESS ['IPOLSDEK_OPT_statusDELIVD'] = "Статус доставленного заказа";
$MESS ['IPOLSDEK_OPT_statusOTKAZ'] = "Статус заказа, от которого отказался клиент";
	// Настройки виджета
$MESS ['IPOLSDEK_OPT_buttonName'] = "Подпись ссылки выбора ПВЗ";
$MESS ['IPOLSDEK_OPT_buttonNamePST'] = "Подпись ссылки выбора постамата";
$MESS ['IPOLSDEK_OPT_ymapsAPIKey'] = "API-ключ Яндекс.Карт";
$MESS ['IPOLSDEK_OPT_noYmaps'] = "Не подключать скрипт АПИ Яндекс.Карт";
$MESS ['IPOLSDEK_OPT_vidjetSearch'] = "Включить поиск в виджете (требуется подключение Яндекс.Карт с API-ключом)";
$MESS ['IPOLSDEK_OPT_pvzID'] = "ID тега, куда привязывать ссылку \"Выбрать пункт самовывоза\"";
$MESS ['IPOLSDEK_OPT_pickupID'] = "ID тега, куда привязывать ссылку \"Выбрать постамат\"";
$MESS ['IPOLSDEK_OPT_pvzPicker'] = "Код свойства, куда будет сохранен выбранный пункт самовывоза";
$MESS ['IPOLSDEK_OPT_autoSelOne'] = "Автовыбор единственного ПВЗ при закрытии виджета";
$MESS ['IPOLSDEK_OPT_mindVWeight'] = "Учитывать объемный вес заказа при построении списка ПВЗ";
$MESS ['IPOLSDEK_OPT_widjetVersion'] = "Используемая версия виджета";
	$MESS ['IPOLSDEK_OPT_ipol.sdekPickup'] = "Старый виджет (ipol.sdekPickup)";
	$MESS ['IPOLSDEK_OPT_ipol.sdekWidjet'] = "Адаптивный виджет (ipol.sdekWidjet)";
	// Оформление заказа
$MESS['IPOLSDEK_OPT_noPVZnoOrder']  = "Не давать оформить заказ на самовывоз, если клиент не выбрал пункт выдачи заказа";
$MESS['IPOLSDEK_OPT_hideNal']       = "Не давать оформить заказ с наличной оплатой при невозможности оплаты наличными";
$MESS['IPOLSDEK_OPT_hideNOC']       = "Не давать оформить заказ с наложенным платежом в других странах";
$MESS['IPOLSDEK_OPT_cntExpress']    = "Расчет экспрессов при стоимости доставки больше (руб)";
$MESS['IPOLSDEK_OPT_mindEnsure']    = "Прибавлять к стоимости доставки величину страховки";
$MESS['IPOLSDEK_OPT_ensureProc']    = "Величина страховки от стоимости товаров (%)";
	// Настройки доставки
$MESS['IPOLSDEK_OPT_mindNDSEnsure'] = "Учитывать НДС страховки";
$MESS['IPOLSDEK_OPT_forceRoundDelivery'] = "Округлять стоимость доставки";

	// Платежные системы, при которых курьер не берет деньги с покупателя
$MESS['IPOLSDEK_OPT_paySystems'] = "Платежные системы, при которых курьер не берет деньги с покупателя";
	// Настройки тарифов и доп. услуг.
$MESS ['IPOLSDEK_OPT_addingService'] = "Отображение дополнительных услуг";
	// Склады
$MESS ['IPOLSDEK_OPT_warhouses'] = "Включить разбиение на города-отправители";
	// Автоотгрузки
$MESS ['IPOLSDEK_OPT_autoloads'] = "Включить автоотгрузки";
$MESS ['IPOLSDEK_OPT_autoloadsMode'] = "Отправлять заказ в СДЭК";
$MESS ['IPOLSDEK_OPT_autoloadsMode_O'] = "при создании заказа";
$MESS ['IPOLSDEK_OPT_autoloadsMode_S'] = "при переводе заказа в статус";
$MESS ['IPOLSDEK_OPT_autoloadsStatus'] = "Статус заказа для отправки в СДЭК";
	// Сервисные свойства
$MESS['IPOLSDEK_OPT_last'] = "Последняя заявка отправлена: ";
$MESS['IPOLSDEK_OPT_statCync'] = "Дата последней проверки статусов заказов: ";
$MESS['IPOLSDEK_OPT_useOldApi'] = "Использовать старый API для запросов";
$MESS['IPOLSDEK_OPT_dostTimeout'] = "Таймаут расчёта доставки, сек <a href='#' class='PropHint' onclick='return IPOLSDEK_setups.popup(\"pop-dostTimeout\", this);'></a>";
$MESS['IPOLSDEK_OPT_timeoutRollback'] = "Ожидание подъема сервера, мин <a href='#' class='PropHint' onclick='return IPOLSDEK_setups.popup(\"pop-timeoutRollback\", this);'></a>";
$MESS['IPOLSDEK_OPT_orderStatusesLimit'] = "Количество заказов, обрабатываемых за 1 запуск синхронизирующего статусы агента <a href='#' class='PropHint' onclick='return IPOLSDEK_setups.popup(\"pop-orderStatusesLimit\", this);'></a>";
$MESS['IPOLSDEK_OPT_orderStatusesUptime'] = "Число дней с момента последнего изменения заказа, в пределах которых запрашиваются статусы <a href='#' class='PropHint' onclick='return IPOLSDEK_setups.popup(\"pop-orderStatusesUptime\", this);'></a>";
$MESS['IPOLSDEK_OPT_orderStatusesAgentRollback'] = "Интервал запуска агента (мин) <a href='#' class='PropHint' onclick='return IPOLSDEK_setups.popup(\"pop-orderStatusesAgentRollback\", this);'></a>";
$MESS['IPOLSDEK_OPT_autoAddCities'] = "Автосопоставление городов <a href='#' class='PropHint' onclick='return IPOLSDEK_setups.popup(\"pop-autoAddCities\", this);'></a>";
$MESS['IPOLSDEK_OPT_noSertifCheckNative'] = "Отключить проверку сертификатов при синхронизации <a href='#' class='PropHint' onclick='return IPOLSDEK_setups.popup(\"pop-noSertifCheckNative\", this);'></a>";
	// Прочее
$MESS['IPOLSDEK_OPT_noteOrderDateCC'] = "Конвертировать валюту курсом на момент оформления заказа <a href='#' class='PropHint' onclick='return IPOLSDEK_setups.popup(\"pop-noteOrderDateCC\", this);'></a>";
$MESS['IPOLSDEK_OPT_debugMode'] = "Включить режим отладки <a href='#' class='PropHint' onclick='return IPOLSDEK_setups.popup(\"pop-debugMode\", this);'></a>";
	// Отладка
$MESS['IPOLSDEK_OPT_debug_widget'] = "Вывод расчетов виджета в консоль";
$MESS['IPOLSDEK_OPT_debug_startLogging'] = "Включить логирование в файл";
$MESS['IPOLSDEK_OPT_debug_fileMode'] = "Режим записи файла лога";
	$MESS['IPOLSDEK_OPT_debug_fileMode_w'] = "Перезапись";
	$MESS['IPOLSDEK_OPT_debug_fileMode_a'] = "Добавление";
$MESS['IPOLSDEK_OPT_debug_calculation'] = "Калькуляция СДЭКа";
$MESS['IPOLSDEK_OPT_debug_turnOffWidget'] = "Отключить калькуляцию виджета";
$MESS['IPOLSDEK_OPT_debug_compability'] = "Доступность доставки профиля (compability)";
$MESS['IPOLSDEK_OPT_debug_calculate']   = "Расчет профиля (calculate)";
$MESS['IPOLSDEK_OPT_debug_shipments']   = "Детализация расчета профиля";
$MESS['IPOLSDEK_OPT_debug_orderSend']   = "Отправка заказа";
$MESS['IPOLSDEK_OPT_debug_statusCheck'] = "Проверка статуса";


//подсказки
	// Настройки
		// Общие
$MESS ['IPOLSDEK_HELPER_departure'] = "Город-отправитель выставляется в <a href='/bitrix/admin/settings.php?lang=ru&mid=sale' target='_blank'>настройках Интернет-магазина</a> в закладке \"Адрес магазина\" -> Местоположение магазина.";
$MESS ['IPOLSDEK_HELPER_showInOrders'] = "Когда показывать на странице информации о заказе кнопку \"СДЭК доставка\": всегда, или же только если выбрана доставка службой СДЭК.";
$MESS ['IPOLSDEK_HELPER_shipments'] = "Работа с отгрузками имеет ряд особенностей. Убедитесь, что изучили соответствующий пункт в FAQ.";
$MESS ['IPOLSDEK_HELPER_addDeparture'] = "Используются для изменения города-отправителя в форме оформления заявки.";
        // Печать
$MESS ['IPOLSDEK_HELPER_prntActOrdr'] = "Определяет, что будет отправлено на печать при выборе заказов и применении пункта \"Печать СДЭК\": только акт приема-передачи, или акт и заказы.";
$MESS ['IPOLSDEK_HELPER_numberOfPrints'] = "Квитанции присылаются сервером СДЭК в формате pdf. Если сервер не сможет уместить указанное количество копий на одном листе А4 - они будут разбиты на несколько листов.";
$MESS ['IPOLSDEK_HELPER_numberOfStrihs'] = "Форма квитанции для заказа присылается сервером СДЭК в формате pdf. Если сервер не сможет уместить указанное количество копий на одном листе указанного формата - они будут разбиты на несколько листов.";
$MESS ['IPOLSDEK_HELPER_formatOfStrihs'] = "Штрихкоды для мест заказа присылаются сервером СДЭК в формате pdf. Если сервер не сможет уместить указанное количество копий на одном листе указанного формата - они будут разбиты на несколько листов.";
		// Общие заявки
$MESS ['IPOLSDEK_HELPER_deliveryAsPosition'] = "В обычном режиме доставка идет отдельным полем вне состава заказа. Если поднять флаг - доставка будет записываться товарной позицией, и в накладной будет фигурировать как часть заказа. Позиция устанавливается в последнее грузоместо.";	
$MESS ['IPOLSDEK_HELPER_normalizePhone'] = "Номер телефона будет приведен в форме оформления заявки к формату СДЭКа (без пробелов, скобок, тире, итп); \"8\" будет заменена на \"+7\" или код другой страны (в соответствии с городом доставки), проверерено наличие плюса в начале.";	
$MESS ['IPOLSDEK_HELPER_addData'] = "В договоре с ИМ определяется условие, кто именно, ИМ или СДЭК, запрашивает у получателя расписание для доставки отправления. В случае, если ИМ самостоятельно запрашивает расписание, дату можно указать в соответствующем поле.<br>Модуль и API СДЭКа не проверяют на корректность выбранную дату доставки (только сравнивает с рассчетным временем доставки, возвращенным калькуляцией) - проверка поля возлагается на менеджера.";	
		// НДС
$MESS ['IPOLSDEK_HELPER_NDSUseCatalog'] = "В товары будут подставлены значения НДС, указанные в торговом каталоге, если их ставка - 0.20, 0.18, 0.12, 0.10, 0.07, 0.05 или 0.00. Иные значения (в том числе и их отсутствие) будет заменено на НДС по умолчанию.";
		// Свойства заявки
$MESS ['IPOLSDEK_HELPER_address'] = "Если вы используете раздельное заполнение адреса (отдельно дом, улица и т.д.), оставьте данное поле пустым";
$MESS ['IPOLSDEK_HELPER_noVats']  = "Оценочная стоимость товаров будет выставлена в 1 рубль, что максимально минимизирует стоимость страховки. Для более детального управления страховкой воспользуйтесь событием onGoodsToRequest. Если требуется минимизировать страховку только конкретных заказов - воспользуйтесь аналогичной опцией в окне оформления заявки, эту же оставьте неотмеченной.";
$MESS ['IPOLSDEK_HELPER_feacn'] = "Код ТН ВЭД товара рекомендуется указывать при международных заказах: доставке в Беларусь, Казахстан. В противном случае некоторые заказы может оказаться невозможным отправить в СДЭК, если проверка на стороне сервера СДЭК по названиям товаров соотнесет какой-либо из товаров заказа с категорией ТН ВЭД, запрещенной к международной доставке.";
$MESS ['IPOLSDEK_HELPER_addMeasureName'] = "Товары с дробным количеством (например, весовые или на отрез) передаются в СДЭК с количеством 1, а сама размерность указывается в названии в скобках: товар (3.4 м). Чтобы отключить вывод размерности - уберите флаг.";
		// Обратная связь
$MESS ['IPOLSDEK_HELPER_statusSTORE']  = "Заказы, которые были доставлены на склад СДЭК для распределения, переводятся в этот статус.";
$MESS ['IPOLSDEK_HELPER_statusCORIER'] = "Заказы, выданные курьеру для доставки со склада города-получателя до двери получателя.";
$MESS ['IPOLSDEK_HELPER_statusTRANZT'] = "Заказы, находящиеся в транспортировке между складом города-отправителя и города-получателя или ожидающие выдачи курьеру.";
$MESS ['IPOLSDEK_HELPER_setTrackingOrderProp'] = "Если необходимо хранить ссылку на отслеживание заказа в свойстве - укажите код этого свойства в этом поле. Настроить отправку его клиенту можно по событию requestSended (FAQ -> Модификация результатов расчетов -> Прочие события) или же через связку Обратной связи и бесплатного модуля <a href=\"https://marketplace.1c-bitrix.ru/solutions/ipol.mailorder/\" target='_blank'>\"Параметры заказа в письме\"</a>.<br>Внимание! Если трек-код сменится в ЛК СДЭКа - модуль не сможет подтянуть изменения!";
		// Настройки виджета
$MESS ['IPOLSDEK_HELPER_pvzPicker'] = "В это свойство будет сохранен выбранный пункт самовывоза. Должно использоваться текстовое свойство, например, адрес. Коды свойств берутся из <a href='/bitrix/admin/sale_order_props.php' target='_blank'>настроек свойств заказа</a>. Для всех типов плательщиков должен быть задан одинаковый код.";
$MESS ['IPOLSDEK_HELPER_autoSelOne'] = "Если в городе единственный ПВЗ покупатели иногда закрывают виджет без выбора пункта. При отметке этого флажка виджет будет автоматически выбирать ПВЗ при закрытии, если ПВЗ - единственный в городе.<br><br>
Плюс:<br>
Не будет заказов с невыбранным ПВЗ, если он - единственный в городе.<br>
Минус:<br>
Если пользователь хочет выбрать другую доставку - ему придется заново перезабивать адрес.";
$MESS ['IPOLSDEK_HELPER_ymapsAPIKey'] = "Для работы скриптов Яндекс.Карт необходимо получить API-ключи для сервисов \"JavaScript API и HTTP Геокодер\" и \"API Геосаджеста\" в <a href='https://developer.tech.yandex.ru/' target='_blank'>Кабинете разработчика Яндекса</a>.<br><br><b>Укажите в данной опции оба ключа в таком виде: ключ-для-JavaScript&suggest_apikey=ключ-для-Геосаджеста</b><br><br>Учтите: в рамках страницы оформления заказа скрипты АПИ Яндекс.Карт могут подключаться разными модулями, а также шаблоном компонента оформления заказа sale.order.ajax. В этом случае указание АПИ-ключа в модуле может не помочь и придется настраивать подключения карт в других решениях.";
$MESS ['IPOLSDEK_HELPER_noYmaps'] = "Если на странице оформления заказа АПИ Яндекс.Карт подключается другим модулем, компонентом оформления заказа, сторонним скриптом или в консоли браузера (F12 -> Console) есть характерные ошибки об ymaps - попробуйте включить эту опцию.";
$MESS ['IPOLSDEK_HELPER_vidjetSearch'] = "Поиск позволяет искать по адресам, станциям метро и другим данным, доступным в Яндекс.Картах. С его помощью клиенту сайта будет удобнее найти ближайший к нему пункт самовывоза.<br /><br /><b>Внимание!</b> Для работы поиска требуется, чтобы Яндекс.Карты были подключены с API-ключами для сервисов \"JavaScript API и HTTP Геокодер\" и \"API Геосаджеста\", в противном случае запросы будут блокироваться на стороне сервера Яндекса и поисковая строка не будет работать.<br /><br />Будет ли подключено АПИ Яндекс.Карт модулем или другим скриптом, не имеет решающего значения, <b>основные требования: подключение должно быть одно в рамках всей страницы оформления заказа и должны быть указаны API-ключи.</b>";
		// Оформление заказа
$MESS ['IPOLSDEK_HELPER_noPVZnoOrder'] = "Если покупатель не выберет точку самовывоза при доставке на самовывоз - ему будет выведено оповещение о необходимости это сделать. Оповещение не возникнет, если некорректно указана настройка \"Код свойства, куда будет сохранен выбранный пункт самовывоза\" (свойство отсутствует или не показано покупателю).";
$MESS ['IPOLSDEK_HELPER_hideNal'] = "При расчете доставки в города с невозможностью оплаты наличными или при превышении стоимости заказа лимита для города, пользователь не сможет выбрать оплату наличкой при выборе СДЭКа или выбрать СДЭК при оплате наличными (в зависимости от режима работы компонента). Оплата наличными определяется настройкой \"Платежные системы, при которых курьер не берет деньги с покупателя\".";
$MESS ['IPOLSDEK_HELPER_hideNOC'] = "При расчете доставки в города Белоруссии и Казахстана, пользователь не сможет выбрать оплату наличкой при выборе СДЭКа или выбрать СДЭК при оплате наличными (в зависимости от режима работы компонента). Оплата наличными определяется настройкой \"Платежные системы, при которых курьер не берет деньги с покупателя\".<br>Флаг рекомендуется убирать только если данным странам задан отдельный личный кабинет, работающий с их валютами.";
$MESS ['IPOLSDEK_HELPER_cntExpress'] = "Если стоимость доставки получилась больше или равна указанной сумме - будет отдельно рассчитаны экспрессы, так как их стоимость может оказаться меньше.<br>Если указать 0 - проверка на экспрессы будет проводиться всегда, однако это повлечет более длительную работу страницы оформления заказа.";
		// Настройки доставки
$MESS ['IPOLSDEK_HELPER_mindEnsure'] = "Если флаг указан - к стоимости доставки будет добавлена величина страховки, вычисляемая из стоимости товаров в корзине. Учитывая, что СДЭК не позволяет получить величину страховки, она указывается в соответствующем поле. Учет страховки происходит до срабатывания события onCalculate.";
$MESS ['IPOLSDEK_HELPER_mindNDSEnsure'] = "Ставка НДС берется из настройки \"Ставка НДС на доставку по умолчанию\" группы \"Ставки НДС\".";
$MESS ['IPOLSDEK_HELPER_forceRoundDelivery'] = "Стоимость доставки округляется после всех расчетов модуля до срабатывания события onCalculate. Если флаг стоит, а стоимость доставки не округлилась - причина в обработчике события или Битриксе (наценки).";
		// Настройки тарифов и доп. услуг.
$MESS ['IPOLSDEK_HELPER_TARSHOW'] = "Если флаг снят - тариф не будет отображаться в форме оформления заявки и в таблице тарифов. Это НЕ отключит его для расчета при оформлении заказа.";
$MESS ['IPOLSDEK_HELPER_TURNOFF'] = "Тариф будет отключен для расчета на странице оформления заказа. При оформлении заявки его все равно можно рассчитать и выбрать.";
		// склады
$MESS ['IPOLSDEK_HELPER_warhouses'] = "Для работы разбиения должна быть задана функция, определяющее разбиение, подписанная на событие onBeforeShipment.";
		// Сервисные
$MESS ['IPOLSDEK_HELPER_dostTimeout'] = "Максимальное время расчета стоимости и сроков доставки на странице оформления заказа.";
$MESS ['IPOLSDEK_HELPER_timeoutRollback'] = "Если сервер СДЭКа не отвечает - запросы на расчет доставки не будут поступать в течении указанного времени. Смысл настройки в том, чтобы не задерживать загрузку страницы оформления заказа (и других мест, где подключены виджеты СДЭКа) при длительном отключении сервера.";
$MESS ['IPOLSDEK_HELPER_orderStatusesLimit'] = "Опция позволяет управлять количеством заказов, по которым модуль будет запрашивать статусы у сервера СДЭК за 1 запуск агента модуля, синхронизирующего статусы. <br><br>В зависимости от количества находящихся в работе заказов (все, что уже передано в СДЭК и не находится в конечных статусах Доставлено или Отказ), мощности хостинга и работы других скриптов (например, обмен с 1С или CRM), эту опцию можно подкорректировать как в сторону увеличения, так и в сторону уменьшения.";
$MESS ['IPOLSDEK_HELPER_orderStatusesUptime'] = "Если заказ был передан в СДЭК и впоследствии удален, либо изменен напрямую в ЛК СДЭК минуя модуль, по известному модулю идентификатору отправления получить статус СДЭК такого заказа становится невозможно. <br><br>Данная опция позволяет отсеивать подобные заказы: после того, как с момента последнего изменения в СДЭК такого заказа (когда он в последний раз присутствовал в выдаче статусов сервера СДЭК) до текущего дня становится больше указанного числа дней, модуль перестанет запрашивать статусы по нему.";
$MESS ['IPOLSDEK_HELPER_orderStatusesAgentRollback'] = "Интервал времени запуска проверки статусов заказов. Выполняется на агенте. Имеет смысл изменять, если статусы не успевают обновляться из-за большого количества заказов.";
$MESS ['IPOLSDEK_HELPER_autoAddCities'] = "Если город не был найден в процессе синхронизации - он будет искаться по мере необходимости при каждом обращении к нему (страница оформления заказа, настройки, форма оформления заказа) прямыми запросами в СДЭК. Это процедура более времязатратная, но увеличивает шанс нахождения города. Таким образом, список доступных городов будет постепенно увеличиваться по мере обращения к ним.";
$MESS ['IPOLSDEK_HELPER_noSertifCheckNative'] = "В очень редких случаях при синхронизации реестра городов модуль получает ошибку \"60 SSL certificate problem: certificate has expired\". Она возникает в случае, если на сайте используются устаревшие сертификаты. Данная опция позволит отключить проверку сертификатов при запросе к нашим (не СДЭК-овским!) серверам, чтобы не ждать обновления сертификатов на сайте.";
$MESS ['IPOLSDEK_HELPER_debugMode'] = "Режим отладки позволяет вывести логи запросов, а так же - отобразить имеющиеся подписки на события.";
		// Аккаунты
$MESS ['IPOLSDEK_HELPER_BadLink'] = "Каждому аккаунту должна быть задана своя валюта.";
	// Заявки
$MESS ['IPOLSDEK_HELPER_statuses'] = "
    NEW - заявка еще не отсылалась на сервер.<br>
	ERROR - заявка не принята из-за ошибок в ее полях. Необходимо исправить ошибки и отправить ее заново.<br>
    OK - заявка принята.<br>
    TRANZIT - заказ в пути.<br>
    STORE - заказ на складе СДЭК.<br>
    CORIER - заказ у курьера.<br>
    PVZ - заказ на пункте самовывоза.<br>
    OTKAZ - клиент отказался от заказа.<br>
    DELIVD - заказ доставлен.
";
	// Города
$MESS ['IPOLSDEK_HELPER_noteOrderDateCC'] = "При отправки заказа с валютой, отличающейся от базовой (рубля) будет произведена конвертация валют по курсу, установленному в модуле валют на текущий момент. Чтобы воспользоваться курсом, определенным в момент оформления заказа (чтобы затребовать к получению столько средств, сколько было отображено клиенту), отметьте этот флаг. Учтите, что курс определяется Битриксом - если определить его на момент создания заказа невозможно, будет выбран курс по умолчанию.";
	// Отладка
$MESS ['IPOLSDEK_HELPER_debug_widget'] = "Виджет будет выводить информацию о расчетах в консоль браузера (F12 -> Console).";
$MESS ['IPOLSDEK_HELPER_startLogging'] = "Логируемые события будут записываться в файл лога, просмотреть который можно по <a href='/bitrix/js/ipol.sdek/log.php' target='_blank'>ссылке</a>. После отключения отладки файл будет очищен.";
$MESS ['IPOLSDEK_HELPER_debug_fileMode'] = "При перезаписи файл лога будет перезаписываться при каждом хите. При проверке калькуляции с виджетом рекомендуется включать Добавление.";
$MESS ['IPOLSDEK_HELPER_debug_calculation'] = "Не забудьте, что расчеты виджета кэшируются на сутки. Не забывайте сбрасывать кэш или отключите его установкой константы IPOLSDEK_NOCACHE.";
$MESS ['IPOLSDEK_HELPER_debug_turnOffWidget'] = "Виджет запрашивает калькуляцию по AJAX - чтобы не занулить данные, полученные при Битриксовском расчете, нужно либо поднять этот флаг, либо поставить режим записи лога в Добавление.";
$MESS ['IPOLSDEK_HELPER_noagent'] = "Рекомендуется перелогиниться в модуле, чтобы агенты заново добавились.";
$MESS ['IPOLSDEK_HELPER_disabledagent'] = "Агент был отключен. Рекомендуется проставить ему активность на <a href='/bitrix/admin/agent_list.php' target='_blank'>странице агентов</a>.";
$MESS ['IPOLSDEK_HELPER_lateagent'] = "Агент не запускался более суток. Проблема может быть в ошибках (проверьте лог php-ошибок сервера) или в некорректных настройках крона. Рекомендуется перелогиниться в модуле. Если проблема остается - свяжитесь с техподдержкой модуля.";


//заголовки
	// Настройки
$MESS ['IPOLSDEK_HDR_common'] = "Общие";
$MESS ['IPOLSDEK_HDR_STORE'] = "Адрес отправления, Отправитель и Продавец";
$MESS ['IPOLSDEK_HDR_print'] = "Печать";
$MESS ['IPOLSDEK_HDR_dimensionsDef'] = "Размеры по умолчанию";
$MESS ['IPOLSDEK_HDR_NDS'] = "Ставки НДС";
$MESS ['IPOLSDEK_HDR_requestProps'] = "Свойства заявки";
	$MESS ['IPOLSDEK_HDR_orderProps'] = "Свойства заказа";
	$MESS ['IPOLSDEK_HDR_itemProps'] = "Свойства товара";
$MESS ['IPOLSDEK_HDR_status'] = "Обратная связь";
$MESS ['IPOLSDEK_HDR_vidjet'] = "Настройки виджета";
$MESS ['IPOLSDEK_HDR_basket'] = "Оформление заказа";
$MESS ['IPOLSDEK_HDR_delivery'] = "Настройки доставки";
$MESS ['IPOLSDEK_HDR_addingService'] = "Настройки тарифов и дополнительных услуг";
$MESS ['IPOLSDEK_HDR_warhouses'] = "Разбиение товаров на города-отправители";
$MESS ['IPOLSDEK_HDR_autoUploads'] = "Автоматическая выгрузка заявок";
$MESS ['IPOLSDEK_HDR_service'] = "Сервисные свойства";
$MESS ['IPOLSDEK_HDR_syncstatus'] = "Синхронизация статусов";
	// Автоотгрузки
$MESS ['IPOLSDEK_HDR_autoloadSetups'] = "Настройки автоотгрузок";
$MESS ['IPOLSDEK_HDR_autoloadTable']  = "Таблица автоотгрузок";
	// Отладка
$MESS ['IPOLSDEK_HDR_logging'] = "Настройки логирования";
$MESS ['IPOLSDEK_HDR_loggingEvents'] = "Логируемые события";
$MESS ['IPOLSDEK_HDR_events']  = "Подписанные события";
$MESS ['IPOLSDEK_HDR_defines']  = "Определенные константы";
$MESS ['IPOLSDEK_HDR_agents']  = "Агенты";
$MESS ['IPOLSDEK_HDR_pvzRestore'] = "Восстановление списка ПВЗ";

	// Города
$MESS ['IPOLSDEK_HDR_countries'] = "Обрабатываемые страны";
$MESS ['IPOLSDEK_HDR_suncs'] = "Ручная синхронизация";

//Подписи
$MESS ['IPOLSDEK_LABEL_moduleVersion'] = "Версия модуля: ";
$MESS ['IPOLSDEK_LABEL_checkVersion'] = "Проверить обновления";
$MESS ['IPOLSDEK_LABEL_noPr'] = "Свойство не задано";
$MESS ['IPOLSDEK_LABEL_Sign_noPr'] = "Свойство с этим кодом не задано у типов плательщиков:";
$MESS ['IPOLSDEK_LABEL_unAct'] = "Свойство не активно";
$MESS ['IPOLSDEK_LABEL_Sign_unAct'] = "Свойство с этим кодом не активно у типов плательщиков:";
$MESS ['IPOLSDEK_LABEL_shPr'] = "Укажите код свойства";
$MESS ['IPOLSDEK_LABEL_addDeparture'] = "Добавить город";

$MESS ['IPOLSDEK_LABEL_NOCITY'] = "<span style='color:red'>Внимание!</span> Не задан город-отправитель! Небходимо выбрать местоположение магазина в <a href='/bitrix/admin/settings.php?mid=sale' target='_blank'>настройках Интернет-магазина</a>. После выбора, перезагрузите эту страницу.";
$MESS ['IPOLSDEK_LABEL_NOSDEKCITY'] = "<span style='color:red'>Внимание!</span> Не удается определить id города-отправителя в системе СДЭК! Проверьте местоположение магазина в <a href='/bitrix/admin/settings.php?mid=sale' target='_blank'>настройках Интернет-магазина</a> или уточните, обслуживается ли ваш город компанией СДЭК.";
$MESS ['IPOLSDEK_LABEL_NOSDEKCITYSHORT'] = "Не удается определить id города-отправителя в системе СДЭК!";
$MESS ['IPOLSDEK_LABEL_forOrder'] = "заказа";
$MESS ['IPOLSDEK_LABEL_forGood'] = "1 товара";

$MESS ['IPOLSDEK_LABEL_noLoc'] = "Отсутствует свойство заказа типа \"Местоположение\"";

$MESS ['IPOLSDEK_LABEL_authHint'] = "Введите доступы, полученные от компании СДЭК для Интеграции.<br>Имейте в виду, что доступы к Интеграции отличаются от доступов к личному кабинету.";
	// события
$MESS ['IPOLSDEK_LABEL_onCompabilityBefore'] = "Доступность доставки (onCompabilityBefore)";
$MESS ['IPOLSDEK_LABEL_onCalculate'] = "Итоговый расчет (onCalculate)";
$MESS ['IPOLSDEK_LABEL_onTarifPriority'] = "Приоритеты тарифов (onTarifPriority)";
$MESS ['IPOLSDEK_LABEL_onBeforeDimensionsCount'] = "Габариты товаров (onBeforeDimensionsCount)";
$MESS ['IPOLSDEK_LABEL_onCalculatePriceDelivery'] = "Дополнительные услуги (onCalculatePriceDelivery)";
$MESS ['IPOLSDEK_LABEL_onBeforeShipment'] = "Разбиение заказа на разные отгрузки (onBeforeShipment)";
$MESS ['IPOLSDEK_LABEL_onTabsBuild'] = "Дополнительное окно настроек (onTabsBuild)";
$MESS ['IPOLSDEK_LABEL_onGoodsToRequest'] = "Товары в заявке (onGoodsToRequest)";
$MESS ['IPOLSDEK_LABEL_requestSended'] = "Отправка заявки (requestSended)";
$MESS ['IPOLSDEK_LABEL_onParseAddress'] = "Парсинг адреса (onParseAddress)";
$MESS ['IPOLSDEK_LABEL_onNewStatus'] = "Смена статуса (onNewStatus)";
$MESS ['IPOLSDEK_LABEL_onFormation'] = "Данные заявки при загрузке заказа (onFormation)";
    // константы
$MESS ['IPOLSDEK_LABEL_CACHE_TIME']    = "Изменено время кэширования запросов";
$MESS ['IPOLSDEK_LABEL_NOCACHE']       = "Отключение кэша";
$MESS ['IPOLSDEK_LABEL_DOWNCOMPLECTS'] = "Разбиение комплектов на отдельные позиции";
$MESS ['IPOLSDEK_LABEL_IGNORECANBUY']  = "Игнорирование флага CAN_BUY у товаров корзины заказа";
$MESS ['IPOLSDEK_LABEL_BASIC_URL']     = "Базовый адрес для запросов";
$MESS ['IPOLSDEK_LABEL_CALCULATE_URL'] = "Адрес калькулятора";
    $MESS ['IPOLSDEK_LABEL_constantOn']    = "Включено";
    $MESS ['IPOLSDEK_LABEL_constantOff']   = "Отключено";
	// Дебаг
$MESS ['IPOLSDEK_LABEL_haslog']  = "Имеются данные лога";
$MESS ['IPOLSDEK_LABEL_nolog']   = "Данные лога отсутствуют";
$MESS ['IPOLSDEK_LABEL_openLog'] = "Открыть лог-файл";

// Additional services
$MESS['IPOLSDEK_AS_TABLE_ACTIVE'] = "Дополнительные услуги";
$MESS['IPOLSDEK_AS_TABLE_NAME'] = "Название услуги";
$MESS['IPOLSDEK_AS_TABLE_SHOW'] = "Показывать в форме отправки";
$MESS['IPOLSDEK_AS_TABLE_DEF'] = "Выбрана по-умолчанию";

// Tariffs
$MESS['IPOLSDEK_TARIF_TABLE_NAME'] = "Название тарифа (код)";
$MESS['IPOLSDEK_TARIF_TABLE_SHOW'] = "Показывать в форме отправки <a href='#' class='PropHint' onclick='return IPOLSDEK_setups.popup(\"pop-TARSHOW\", this);'></a>";
$MESS['IPOLSDEK_TARIF_TABLE_TURNOFF'] = "Отключить для расчета <a href='#' class='PropHint' onclick='return IPOLSDEK_setups.popup(\"pop-TURNOFF\", this);'></a>";
$MESS['IPOLSDEK_TARIF_TABLE_HINT'] = "Описание";
$MESS['IPOLSDEK_TARIF_TABLE_ACTIVE'] = "Действующие тарифы";
$MESS['IPOLSDEK_TARIF_TABLE_ARCHIVE'] = "Архивные тарифы";
$MESS['IPOLSDEK_TARIF_TABLE_DOOR'] = "Для курьерской доставки (до двери)";
$MESS['IPOLSDEK_TARIF_TABLE_PVZ'] = "Для доставки до пункта самовывоза (до склада)";
$MESS['IPOLSDEK_TARIF_TABLE_PST'] = "Для доставки до постамата";

//таблица заявок

$MESS['IPOLSDEK_TABLE_ORDN'] = "ID заказа";
$MESS['IPOLSDEK_TABLE_PARAM'] = "Параметры";
$MESS['IPOLSDEK_TABLE_MESS'] = "Сообщение";
$MESS['IPOLSDEK_TABLE_COLS'] = "Записи";
$MESS['IPOLSDEK_TABLE_FRM'] = "из";
$MESS['IPOLSDEK_TABLE_SHTRC'] = "Штрихкод";
$MESS['IPOLSDEK_TABLE_UPTIME'] = "Дата изменения";
$MESS['IPOLSDEK_TABLE_SENDTYPE'] = "Тип";
$MESS['IPOLSDEK_TABLE_ACCOUNT'] = "Аккаунт";

//таблица автозагрузок
$MESS['IPOLSDEK_ALTABLE_STATUS'] = "Статус автовыгрузки";
$MESS['IPOLSDEK_ALTABLE_FAILS']  = "Только неудачные автовыгрузки";
$MESS['IPOLSDEK_ALTABLE_STATUSDEK']  = "Статус СДЭКа";
// таблица стран

$MESS['IPOLSDEK_TC_NAME'] = "Страна";
$MESS['IPOLSDEK_TC_WORKOUT'] = "Обрабатывать";
$MESS['IPOLSDEK_TC_ACCOUNT'] = "Подключенный аккаунт";
$MESS['IPOLSDEK_TC_ACCOUNT_HINT'] = "Обновить данные по аккаунтам";
$MESS['IPOLSDEK_TC_CURRENCY'] = "Валюта";
$MESS['IPOLSDEK_TC_DEFAULT'] = "по-умолчанию";

//табы

$MESS['IPOLSDEK_TAB_LIST'] = "Заявки";
$MESS['IPOLSDEK_TAB_TITLE_LIST'] = "Отслеживание состояний заявок на заказ.";
$MESS['IPOLSDEK_TAB_FAQ'] = "FAQ";
$MESS['IPOLSDEK_TAB_TITLE_FAQ'] = "Помощь в настройке и работе с модулем";
$MESS['IPOLSDEK_TAB_LOGIN'] = "Авторизация";
$MESS['IPOLSDEK_TAB_TITLE_LOGIN'] = "Для начала работы с модулем введите доступы к учетной записи";
$MESS['IPOLSDEK_TAB_CITIES'] = "Города";
$MESS['IPOLSDEK_TAB_CITIES_LOGIN'] = "Соответствие местоположений Битрикса с городами СДЭКа.";
$MESS['IPOLSDEK_TAB_TITLE_CITIES'] = "Настройки синхронизации и соответствие местоположений Битрикса с городами СДЭКа.";
$MESS['IPOLSDEK_TAB_IMPORT'] = "Импорт городов";
$MESS['IPOLSDEK_TAB_TITLE_IMPORT'] = "Импорт городов СДЭКа в Битрикс.";
$MESS['IPOLSDEK_TAB_AUTOLOADS'] = "Автоматизация";
$MESS['IPOLSDEK_TAB_TITLE_AUTOLOADS'] = "Управление автоотгрузками";
$MESS['IPOLSDEK_TAB_DEBUG'] = "Отладка";
$MESS['IPOLSDEK_TAB_TITLE_DEBUG'] = "Управление логированием и прочая отладочная информация";
$MESS['IPOLSDEK_TAB_RIGHTS'] = "Права";
$MESS['IPOLSDEK_TAB_TITLE_RIGHTS'] = "Управления правами доступа к модулю";

//авторизация

$MESS['IPOLSDEK_LBL_AUTHORIZE'] = "Авторизоваться";
$MESS['IPOLSDEK_ALRT_NOLOGIN'] = "Введите Account";
$MESS['IPOLSDEK_ALRT_NOPASS'] = "Введите Secure_password";

$MESS['IPOLSDEK_LBL_YLOGIN'] = "Ваш Account";
$MESS['IPOLSDEK_LBL_DOLOGOFF'] = "Разлогиниться";
$MESS['IPOLSDEK_LBL_ISLOGOFF'] = "Функционал модуля будет отключен: синхронизация, отсылание и проверка статусов заявок, службы доставки - все будет отключено. Продолжить?";
$MESS['IPOLSDEK_LBL_CLRCACHE'] = "&nbsp;Сбросить кэш&nbsp;";
$MESS['IPOLSDEK_LBL_ACCOUNTS'] = "&nbsp;Аккаунты&nbsp;";
$MESS['IPOLSDEK_LBL_CACHEKILLED'] = "Кэш модуля очищен.";
$MESS['IPOLSDEK_LBL_SURETOREWRITE'] = "Все города Битрикса будут заново синхронизированы со СДЭКом. После синхронизации не забудьте перепроверить 'Город-отправитель' и нажать 'Сохранить'. Продолжить?";
$MESS['IPOLSDEK_LBL_NONEEDIMPORT'] = "У вас установлены Местоположения 2.0 - в подавляющем большинстве случаев Импорт только навредит структуре местоположений. Лучше включить опцию Автосопоставления городов в сервисных свойствах - это покроет большую часть географии. Вы все равно хотите включить вкладку Импорта?";
$MESS['IPOLSDEK_IMPORT_LBL_HAS20'] = "На сайте установлены Местоположения 2.0. По большей части они покрывают всю географию СДЭКа без необходимости Импорта, при условии включения Автосопоставления городов в сервисных свойствах.<br>Импорт может привести к образованию дублей местоположений, так как все местоположения добавятся с типом \"Город\", а не \"Село\" или прочие.<br>Запускайте Импорт только если четко понимаете, что делаете.<br>Если Импорт произведен ошибочно - необходимо заново загрузить местоположения Битрикса из настроек магазина и запустить Переопределение городов в настройках модуля.";
$MESS['IPOLSDEK_LBL_ATTENTION'] = "<span class='IPOLSDEK_warning'>Внимание!</span>";

// Восстановление списка ПВЗ
$MESS['IPOLSDEK_LBL_RESTOREPVZ'] = "Вы собираетесь восстановить список ПВЗ из резервной копии. Продолжить?";
$MESS['IPOLSDEK_LBL_RESTOREPVZBTN'] = "Восстановить список ПВЗ";
$MESS['IPOLSDEK_LBL_RESTORED'] = "Список ПВЗ восстановлен из резервной копии";
$MESS['IPOLSDEK_LBL_UNRESTORED'] = "Во время синхронизации произошла ошибка: ";


//Автовыставление платежных систем, с которых курьер деньги не берет

$MESS ['IPOLSDEK_cashe'] = "наличны";
$MESS ['IPOLSDEK_cashe2'] = "наложный";
$MESS ['IPOLSDEK_cashe3'] = "при получении";


//прочее
	// Склады
$MESS['IPOLSDEK_OTHR_noWarhouses'] = "Разбиение товаров по городам-отправителям не задано.";
$MESS['IPOLSDEK_OTHR_hasWarhouses'] = "Обнаруженные функции-обработчики распределения товаров по городам-отправителям:";
	// Аккаунты
$MESS['IPOLSDEK_ACCOUNT_TABLE_KEY'] = "Аккаунт";
$MESS['IPOLSDEK_ACCOUNT_TABLE_PASS'] = "Пароль";
$MESS['IPOLSDEK_ACCOUNT_TABLE_LABEL'] = "Комментарий";
$MESS['IPOLSDEK_ACCOUNT_TABLE_AUTH'] = "Авторизация";
$MESS['IPOLSDEK_ACCOUNT_TABLE_DEFAULT'] = "Основной";

$MESS['IPOLSDEK_ACCOUNT_TABLE_AUTH_OK'] = "<b style='color:green'>Успешно</b>";
$MESS['IPOLSDEK_ACCOUNT_TABLE_AUTH_ERROR'] = "<b style='color:red'>Ошибка</b>";

$MESS['IPOLSDEK_OTHR_accHeader'] = "Управление аккаунтами";
$MESS['IPOLSDEK_OTHR_accDefault'] = "Основной";
$MESS['IPOLSDEK_OTHR_accDelete'] = "Удалить";
$MESS['IPOLSDEK_OTHR_accMakeDefault'] = "Сделать основным";
$MESS['IPOLSDEK_OTHR_accNew'] = "Новый аккаунт";
$MESS['IPOLSDEK_OTHR_accAdd'] = "Добавить";
$MESS['IPOLSDEK_OTHR_accDefaultDelete'] = "Вы собираетесь удалить основной аккаунт. Основным станет следующий по списку. Продолжить?";
$MESS['IPOLSDEK_OTHR_accLastDelete'] = "Вы собираетесь удалить единственный аккаунт. Это равносильно разлогиниванию в модуле. Продолжить?";
$MESS['IPOLSDEK_OTHR_accDoMakeDefault'] = "Установить новый основной аккаунт? Все запросы, выполняемые по умолчанию, будут вестись с него.";
$MESS['IPOLSDEK_OTHR_acComent'] = "Комментарий";
	// Сервисные
$MESS['IPOLSDEK_OTHR_schet'] = "Отправлено заявок:";
$MESS['IPOLSDEK_OTHR_schet_BUTTON'] = "Сбросить счетчик";
$MESS['IPOLSDEK_OTHR_schet_ALERT'] = "Счетчик используется для синхронизации заявок и ответов. Все равно сбросить?";
$MESS['IPOLSDEK_OTHR_schet_DONE'] = "Счетчик сброшен";
$MESS['IPOLSDEK_OTHR_schet_NONE'] = "Ошибка сброса счетчика ";

$MESS['IPOLSDEK_OTHR_lastModList'] = "Последняя синхронизация:";
$MESS['IPOLSDEK_OTHR_lastModList_BUTTON'] = "Синхронизировать";
$MESS['IPOLSDEK_OTHR_lastModList_START'] = "Запуск синхронизации справочников";
$MESS['IPOLSDEK_OTHR_lastModList_STARTCITY'] = "Запуск синхронизации городов";

$MESS['IPOLSDEK_OTHR_getOutLst_BUTTON'] = "Проверить сейчас";
$MESS['IPOLSDEK_OTHR_getOutLst_BUTTON_OT'] = 'Проверить статусы';
$MESS['IPOLSDEK_OTHR_rewriteCities_BUTTON'] = 'Переопределить города';
$MESS['IPOLSDEK_OTHR_suncCities_BUTTON'] = 'Синхронизировать города';
$MESS['IPOLSDEK_OTHR_importCities_BUTTON'] = 'Импорт городов СДЭК';

$MESS['IPOLSDEK_OTHR_NOTCOMMITED'] = 'не проводилась.';
	// Отзыв заявки
$MESS['IPOLSDEK_OTHR_killReq_BUTTON'] = "Отозвать заявку";
$MESS['IPOLSDEK_OTHR_killReq_TITLE'] = "Отзыв заявки";
$MESS['IPOLSDEK_OTHR_killReq_DESCR'] = "Функционал предназначен для отзыва заявок со статусом \'ERROR\' и сообщением \'Заказ с номером # уже загружен\'. Такая ситуация может возникнуть в случае, если необходимо отозвать заявку к заказу, который был подтвержден, но впоследствии по каким-то причинам отослан заново и получил вместо статуса OK статус ERROR.";
$MESS['IPOLSDEK_OTHR_killReq_LABEL'] = "ID заказа: ";
$MESS['IPOLSDEK_OTHR_killReq_TYPE'] = "Тип: ";
$MESS['IPOLSDEK_OTHR_killReq_HINT'] = "Имейте ввиду, что попытка отозвать заявку со статусами кроме ERROR и OK потерпит крах.";
	// Действие при печати
$MESS['IPOLSDEK_OTHR_ACTSONLY']  = "Только акты";
$MESS['IPOLSDEK_OTHR_ACTSORDRS'] = "Акты и заказы";
	// Когда показывать кнопку
$MESS['IPOLSDEK_OTHR_ALWAYS'] = "Всегда";
$MESS['IPOLSDEK_OTHR_DELIVERY'] = "Доставка СДЭК";
	// Очистка обновления
$MESS['IPOLSDEK_OPT_clrUpdt_ALERT'] = "Очистить информацию об обновлении? Она будет удалена для всех пользователей!";
$MESS['IPOLSDEK_OPT_clrUpdt_ERR'] = "Не получилось удалить информацию об обновлении.";
	// СДЭК умер
$MESS['IPOLSDEK_DEAD_SERVER_HEADER'] = "Сервер СДЭК недоступен";
$MESS['IPOLSDEK_DEAD_SERVER_TITLE']  = "Последняя проверка статуса:";
$MESS['IPOLSDEK_DEAD_SERVER_BTN']    = "Сбросить";
	// Ошибки
$MESS['IPOLSDEK_FNDD_ERR_HEADER'] = "В процессе работы возникали ошибки";
$MESS['IPOLSDEK_FNDD_ERR_TITLE'] = "за подробностями обратитесь к <a href='/bitrix/js/ipol.sdek/errorLog.txt' target='blank'>лог-файлу</a>.<br><small>Чтобы убрать это оповещение - очистите лог-файл.</small>";
$MESS['IPOLSDEK_NOLIST_ERR_HEADER'] = "Модуль не просинхронизирован";
$MESS['IPOLSDEK_NOLIST_ERR_TITLE'] = "Информация о пунктах самовывоза не обнаружена: доставка самовывозом недоступна для вывода. Синхронизируйте модуль.<br><input type='button' id='IPOLSDEK_subsyncer' value='Синхронизировать сейчас' onclick='IPOLSDEK_setups.base.subSyncList()'>";
// Недоступен API 1.5
$MESS['IPOLSDEK_DISABLED_API_1_5_HEADER'] = "Для одного или нескольких аккаунтов отключена поддержка API 1.5";
$MESS['IPOLSDEK_DISABLED_API_1_5_TITLE'] = "<p><b>Обратитесь в техническую поддержку СДЭК на почту <a href='mailto:integrator@cdek.ru'>integrator@cdek.ru</a></b>, обязательно укажите в обращении аккаунт интеграции и номер договора со СДЭК, к которому данный аккаунт относится. <b>Попросите, чтобы вам подключили возможность использования всех методов API СДЭК по протоколу 1.5.</b> </p>
<p>Поддержку API 1.5 необходимо включить для следующих аккаунтов:</p>";

// СДЭК отключен
$MESS['IPOLSDEK_NO_ADOST_HEADER'] = "Служба доставки СДЭК отключена";
$MESS['IPOLSDEK_NO_ADOST_TITLE'] = "Чтобы служба доставки отображалась на странице оформления заказа - <a href='/bitrix/admin/sale_delivery_handler_edit.php?SID=sdek' target='_blank' class='IPOLSDEK_notConverted'>поставьте ей активность</a><a href='/bitrix/admin/sale_delivery_service_list.php' target='_blank' class='IPOLSDEK_converted'>поставьте ей активность</a>.";
	// СДЭК удален
$MESS['IPOLSDEK_NO_DOST_HEADER'] = "Служба доставки СДЭК удалена";
$MESS['IPOLSDEK_NO_DOST_TITLE'] = "Служба доставки была удалена. Чтобы вернуть ее - переустановите модуль.";
	// СДЭК не создан
$MESS['IPOLSDEK_NOT_CRTD_HEADER'] = "Служба доставки СДЭК не найдена";
$MESS['IPOLSDEK_NOT_CRTD_TITLE'] = "Служба доставки не найдена. <a href='/bitrix/admin/sale_delivery_service_list.php' target='_blank'>Добавьте доставку</a>, используя обработчик СДЭК.";
	// Прочие предупреждения
$MESS['IPOLSDEK_NO_PROFILE_HEADER_pickup'] = "Отключены все тарифы самовывоза";
$MESS['IPOLSDEK_NO_PROFILE_HEADER_courier'] = "Отключены все курьерские тарифы";
$MESS['IPOLSDEK_NO_PROFILE_HEADER_postamat'] = "Отключены все тарифы постаматов";
$MESS['IPOLSDEK_NO_PROFILE_TITLE'] = "Профиль не будет отображаться на странице оформления заказа, пока не будет включен хотя бы один соответствующий тариф (опция \"Управление тарифами\").";
	// Подписи
$MESS['IPOLSDEK_OTHR_NO_REQ'] = "Заявки еще не создавались.";
$MESS['IPOLSDEK_OTHR_NO_REQ_FILTER'] = "По заданному фильтру заявок не обнаружено.";
$MESS ['IPOLSDEK_BGMSC'] = "МОСКВА";
$MESS ['IPOLSDEK_OTHR_TurnOffautoloads'] = "Выключить автоотгрузки";
$MESS ['IPOLSDEK_OTHR_addDataWarn'] = "Убедитесь, что вы ознакомились с особенностями указания даты доставки (в подсказке к опции)!";
	// Импорт
$MESS ['IPOLSDEK_IMPORT_LBL_BEWIZE'] = "Данный функционал представлен \"Как есть\"! Он выполняется ПО ЖЕЛАНИЮ. Это НЕ синхронизация (синхронизация запускается из вкладки \"Настройка\" -> Сервисные свойства).<br>Если вы не знаете, что делаете на этой вкладке (тем более - если не читаете FAQ по настройке) - лучшим решением будет нажать на кнопку \"Убрать окно импорта\".";
$MESS ['IPOLSDEK_IMPORT_LBL_TIMEOUT'] = "Таймаут";
$MESS ['IPOLSDEK_IMPORT_LBL_sec'] = "сек";
$MESS ['IPOLSDEK_IMPORT_LBL_START'] = "Начать импорт";
$MESS ['IPOLSDEK_IMPORT_LBL_KILL'] = "Убрать окно импорта";
$MESS ['IPOLSDEK_IMPORT_LBL_ANSWER'] = 'Ответ через';
$MESS ['IPOLSDEK_IMPORT_LBL_A'] = "Начать импорт";

$MESS ['IPOLSDEK_IMPORT_PROCESS_SCHECK'] = "Проверка синхронизации...";
$MESS ['IPOLSDEK_IMPORT_PROCESS_ONINIT_1'] = "Найдено местоположений";
$MESS ['IPOLSDEK_IMPORT_PROCESS_ONINIT_2'] = "Начало импорта. Не закрывайте страницу.";
$MESS ['IPOLSDEK_IMPORT_PROCESS_WORKINGOUT'] = "Обработка";
$MESS ['IPOLSDEK_IMPORT_PROCESS_IEND'] = "Процесс импорта завершен. Не забудьте переиндексировать местоположения.";
$MESS ['IPOLSDEK_IMPORT_PROCESS_ISTART'] = "Запрос файла местоположений...";
//FAQ
	// Недоступность сервера
$MESS ['IPOLSDEK_FAQ_DEAD_SERVER_TITLE'] = "- И что делать?";
$MESS ['IPOLSDEK_FAQ_DEAD_SERVER_DESCR'] = "<p>Ждать, пока техническая поддержка СДЭК восстановит работу сервера. К сожалению, с серверами СДЭКа иногда возникают технические проблемы, сервера \"падают\".<br>Писать в техподдержку модуля нерационально: у нас нет доступов к серверам СДЭК, мы никак не можем повлиять на эту ситуацию.<br>Кнопка \"Сброс\" предназначена для сброса времени ожидания подъема сервера. Его работоспособность не может восстановиться моментально - чтобы не задерживать оформление заказа, модуль ждет определенное время, указанное в настройке \"Ожидание подъема сервера\" в сервисных свойствах.<p>";
    // Accounts
$MESS['IPOLSDEK_FAQ_ACCOUNTS_TABLE_TITLE'] = "- Об аккаунтах";
$MESS['IPOLSDEK_FAQ_ACCOUNTS_TABLE_DESCR'] = "<p>Аккаунт это данные о вашем ключе интеграции со СДЭК, хранящиеся в модуле и используемые для авторизации на сервере СДЭК. Для каждого договора со СДЭК может быть только один ключ интеграции.</p><p><b>Посмотреть доступы к интеграции, а также обновить их можно <a href=\\\"https://lk.cdek.ru/integration\\\" target=\\\"_blank\\\">в ЛК СДЭК в разделе Интеграция</a>.</b></p><p>Ошибка авторизации означает, что с данным ключом интеграции и паролем не удалось получить токен авторизации от сервера СДЭК. Соответственно, расчет стоимости доставки, отправка заказов в СДЭК, получение файлов квитанций и т.д. при использовании этого аккаунта работать не будут. <b>Основная причина этой проблемы: в ЛК СДЭК изменились доступы к интеграции, а на сайте в модуле остались старые недействующие</b>.</p><p>Техническая поддержка модуля не может ответить к какому именно договору относится определенный ключ интеграции: этими сведениями располагает только сам СДЭК. Связаться с поддержкой СДЭК IT можно <a href=\\\"mailto:integrator@cdek.ru\\\" target=\\\"_blank\\\">по email</a> или <a href=\\\"https://t.me/cdek_it_bot\\\" target=\\\"_blank\\\">через Telegram</a>.</p>";
    // Store
$MESS['IPOLSDEK_FAQ_STORE'] = "Внимательно ознакомьтесь с FAQ, пункт \"Адрес отправления, Отправитель и Продавец\".<br><br>Данные добавляются и редактируются <a href='/bitrix/admin/ipol_sdek_stores.php?lang=ru' target='_blank'>на отдельной странице настроек</a>";
	// Печать заказов и актов
$MESS['IPOLSDEK_FAQ_PRINT_TITLE'] = "- Печать заказов и актов";
$MESS['IPOLSDEK_FAQ_PRINT_DESCR'] = "<p>Настройки отвечают за встроенный функционал печати актов, а так же квитанций на заказ.</p>
<p>В настройках модуля необходимо выбрать действие при печати актов (группа \"Общие\"): будет печататься либо акт, либо акт и квитанции для крепления к товарам и передачи их курьеру СДЭК.</p>
<p>Печать актов и заказов производится следующим образом: на <a href='/bitrix/admin/sale_order.php' target='_blank'>странице заказов</a> выбираются заказы, которые будут включены в акт (заявки на заказы должны быть подтверждены!), в действиях (внизу) выбирается \"Печать СДЭК\" и нажимается кнопка \"Применить\". В открывшемся окне будет текст акта (и квитанции на печать, открывающиеся в новом окне), эту страницу можно отправлять на печать.</p>
<p>Файл, в котором задается текст акта располагается по адресу /bitrix/js/ipol.sdek/printActs.php. Для его использования нужно заполнить пустующие поля в соответствии с договором со СДЭК. Более подробная информация указана в самом файле.</p>";
$MESS['IPOLSDEK_FAQ_PRINTSHTR_TITLE'] = "- Печать штрихкодов";
$MESS['IPOLSDEK_FAQ_PRINTSHTR_DESCR'] = "<p>Печать штрихкодов: на <a href='/bitrix/admin/sale_order.php' target='_blank'>странице заказов</a> выбираются заказы, для которых будут напечатаны штрихкоды, в действиях (внизу) выбирается \"Штрихкоды СДЭК\" и нажимается кнопка \"Применить\". В открывшемся окне будет pdf со штрихкодами, эту страницу можно отправлять на печать. Так же печать можно запускать из формы оформления заявки кнопкой \"Штрихкод\".</p>";
	// Габариты
$MESS['IPOLSDEK_FAQ_dimensionsDef_TITLE'] = "- О габаритах";
$MESS['IPOLSDEK_FAQ_dimensionsDef_DESCR'] = "<p>Данная группа настроек предназначена для определения габаритов тех заказов, где присутствуют товары без заполненных размеров и/или веса. Здесь можно задать те значения, что будут браться по умолчанию. Можно так же настроить порядок применения этих габаритов: либо они будут применяться для всего заказа, либо - для каждого товара.</p>
<p>При возникновении ситуации смешанных заказов, когда в корзине присутствуют как товары без габаритов, так и с заданными параметрами, проверяется общий размер и вес тех товаров, у которых габариты заняты и берется то значение - рассчитанное или же заданное по умолчанию - которое больше.</p>
<p>Все габариты берутся из <strong>настроек торгового каталога</strong>. При необходимости задавать габариты через свойство инфоблока или иным способом, в обход каталога - обратитесь к FAQ, пункт Модификация результатов расчетов (для программистов) -> Изменение габаритов товаров.</p>";
	// Свойства заявки
		// Свойства заказа
$MESS['IPOLSDEK_FAQ_PROPS_TITLE'] = "- О свойствах заказа";
$MESS['IPOLSDEK_FAQ_PROPS_DESCR'] = "<p>Эта группа настроек отвечает за экспорт свойств заказа в форму оформления заявки через указание <a href='/bitrix/admin/sale_order_props.php' target='_blank'>кодов свойств заказа</a>. Если в магазине есть несколько типов плательщиков, в аналогичных свойствах нужно задать одинаковый символьный код (например, код FIO для Ф.И.О. Физического лица и Контактного лица Юридического лица). В случае если свойство не будет обнаружено у какого-то плательщика, после сохранения модуль выдаст сообщение напротив конкретного свойства.</p>
<p>Свойство <strong>\"Город доставки\"</strong> определяется автоматически. Для его определения необходимо, чтобы на сайте было задан один код свойства с типом \"Местоположение\". Без него достоверно определить город доставки практически невозможно, и поэтому без этого свойства <strong>модуль работать не будет</strong>.</p>
<p><strong>Улица, дом и квартира</strong> - если адрес доставки разделен, в эти поля надо ввести данные об улице, доме и квартире/офисе. Поле <strong>Адрес</strong> при этом обязательно оставьте пустым.</p>";
		// Свойства товара
$MESS['IPOLSDEK_FAQ_IPROPS_TITLE'] = "- О свойствах товаров";
$MESS['IPOLSDEK_FAQ_IPROPS_DESCR'] = "<p>Эта группа настроек отвечает за экспорт свойств товара в XML, отсылаемый в СДЭК. Чтобы свойства подгружались - необходимо в значения вбить коды свойств товара, в которых хранятся нужные значения. Если используются несколько инфоблоков - необходимо в каждом задать указанный код для соответствующих свойств.</p><p>Если используются торговые предложения, а необходимые данные указаны непосредственно в товарах - убедитесь, что стоит флаг \"Брать данные товара, если отсутствуют у торгового предложения\". Тогда, если указанные свойства не найдены в торговых предложениях - они будут искаться в товарах.</p>";
		// НДС
$MESS['IPOLSDEK_FAQ_NDS_TITLE'] = "- Об НДС";
$MESS['IPOLSDEK_FAQ_NDS_DESCR'] = "<p>Эта группа настроек влияет исключительно на форму оформления заявки. Ставки НДС на товары устанавливаются в Торговом каталоге, а на доставку - в <a href='/bitrix/admin/sale_delivery_service_list.php' target='_blank'>настройках соответствующей доставки</a>.</p>";
	// Обратная связь
$MESS['IPOLSDEK_FAQ_status_TITLE'] = "- О статусах";
$MESS['IPOLSDEK_FAQ_status_DESCR'] = "<p>Данная группа настроек нужна для того, чтобы оперативно отслеживать статусы заказов. Раз в 30 минут запрашивается информация по статусам отправленных заявок. При получении ответа заказы выставятся в указанные статусы если они приняты, или по каким-то причинам отклонены. Так же отслеживаются статусы доставки заказов. Рекомендуется создать два новых статуса заказа, чтобы удобнее было отслеживать по ним состояние заявок, а так же задать специальные правила в <a href='/bitrix/admin/type_admin.php' target='_blank'>Типах почтовых событий</a>, чтобы отсылать письма о смене статусов заказа только менеджерам магазина, а не покупателям.</p>
<p>Свойство <strong>Отмечать доставленный заказ оплаченным</strong> позволяет выставлять флаг оплаты тем заказам, <strong>которые были доставлены</strong>. Это актуально для оперативного учета накопительных скидок.</p>
<p class='IPOLSDEK_converted'><strong>Отгрузки</strong><br>Если заказ отправляется не через заказ, а через отгрузки - то смена статусов происходит только у соответствующих отгрузок, сами заказы не трогаются. Поэтому для лучшей автоматизации не отправляйте отгрузками заказ, состоящий из одной отгрузки.<br><small>В дальнейшем возможно добавление функционала выставления заказам статусов при достижении соответствующего отгрузками (если все отгрузки доставлены - заказ считается доставленным) - следите за обновлениями.</small></p>";
	// Настройки виджета
$MESS['IPOLSDEK_FAQ_vidjet_TITLE'] = "- Подробнее о виджете";
$MESS['IPOLSDEK_FAQ_vidjet_DESCR'] = "<p>Виджет выбора пункта самовывоза автоматически подключается на странице оформления заказа, никаких манипуляций со стороны это не требует. При этом на странице оформления заказа рядом со способом доставки \"Самовывоз\" СДЭКа должна появиться ссылка <strong>\"Выбрать пункт самовывоза\"</strong>. Если ссылка не появилась автоматически - значит, модуль не может найти место, куда ее можно вставить, то есть следствие использования нестандартного или модифицированного шаблона. Чтобы исправить эту проблему, необходимо в шаблоне оформления заказа вставить тег (span, div, или p) и присвоить ему уникальный id. После чего вбить этот id в поле <strong>ID тега, куда привязывать ссылку \"Выбрать пункт самовывоза\"</strong>.</p><p>Виджет сохраняет адрес ПВЗ и его уникальный ID в свойство, код которого должен быть указан в поле <strong>Код свойства, куда будет сохранен выбранный пункт самовывоза</strong> - тогда при оформлении заявки ПВЗ будет автоматически подгружен в форму.</p><p>Свойство \"Подпись ссылки выбора ПВЗ\" позволяет задать название для ссылки выбора ПВЗ, которое заменит стандартное \"Выбрать пункт самовывоза\".</p>";
	// Доставки
$MESS['IPOLSDEK_FAQ_DELIVERY'] = "Все настройки служб доставки, связанные с платежными системами, названиями, сортировками, итп располагаются в <a class='IPOLSDEK_notConverted' href='/bitrix/admin/sale_delivery_handler_edit.php?SID=sdek' target='_blank'>настройках автоматизированных служб доставки</a><a class='IPOLSDEK_converted' href='/bitrix/admin/sale_delivery_service_list.php' target='_blank'>настройках автоматизированных служб доставки</a> и заполняются средствами Битрикса.";
	// Платежные системы
$MESS['IPOLSDEK_FAQ_PAYSYS_TITLE'] = "- Пояснение к платежным системам";
$MESS['IPOLSDEK_FAQ_PAYSYS_DESCR'] = "<p>В настройке необходимо выбрать те платежные системы, выборе которых курьер не должен брать деньги с получателя. Она используется при открытии формы оформления заявки созданного заказа - автоматически ставится флаг \"Курьер не получает деньги за заказ\". Кроме того, эта настройка определяет работу свойства <strong>Не давать оформить заказ с наличной оплатой при невозможности оплаты наличными</strong>.</p>";

// Tariffs
$MESS['IPOLSDEK_FAQ_TARIFFS_TITLE'] = '- О тарифах';
$MESS['IPOLSDEK_FAQ_TARIFFS_DESCR'] = "Список доступных для расчета стоимости доставки тарифов СДЭК, работу с которыми поддерживает модуль. В блоке \"Действующие тарифы\" собраны актуальные на данный момент тарифы СДЭК. В блоке \"Архивные тарифы\" собраны тарифы, которые ранее использовались, но устарели и вместо них настоятельно рекомендуется использовать аналоги из действующих тарифов. Возможность расчета доставки архивными тарифами не гарантируется.<br><br>
<b style='color:red'>Уточнить, какие именно тарифы СДЭК лучше подходят для доставки заказов вашего магазина, вы можете у вашего персонального менеджера СДЭК.</b><br><br>
Разные тарифы могут иметь разные ограничения: по максимальному и минимальному весу, по направлению и т.д. Краткие описания тарифов можно посмотреть, кликая на значок \"?\" около их названий.<br><br>        
Колонка \"Показывать в форме отправки\" отвечает за доступность тарифа в форме отправки заявки в СДЭК. Если флаг снят, тариф не будет отображаться в селекторе тарифов формы и в ее таблице тарифов. <br><b>Это не отключит данный тариф для расчета доставки при оформлении заказа покупателем на публичной части сайта.</b><br><br>
Колонка \"Отключить для расчета\" отвечает за возможность использования тарифа при расчете доставки на странице оформления заказа для покупателя. Если флаг установлен, тариф использоваться не будет. <br><b>Это не скроет данный тариф в форме отправки заявки для менеджера магазина.</b><br><br>
<b>Примеры настройки:</b>
<ul>
<li>Если вы собираетесь работать со всеми тарифами СДЭК, отметьте все тарифы в колонке \"Показывать в форме отправки\", а в \"Отключить для расчета\" не отмечайте ничего.</li>
<li>Если вы будете работать лишь с некоторыми тарифами СДЭК, например, вам нужны тарифы 136 \"Посылка склад-склад\" и 137 \"Посылка склад-дверь\", то в \"Показывать в форме отправки\" отметьте только 136 и 137 тарифы, а в \"Отключить для расчета\" отметьте все кроме 136 и 137.</li>
</ul>
<b>Принцип выбора конкретного тарифа СДЭК при расчете доставки на странице оформления заказа описан в разделе документации \"Справочная информация\" - \"Особенности расчета стоимости доставки\". При выборе в форме отправки заявки в СДЭК тарифа, отличного от расчетного (которым была посчитана доставка при создании заказа покупателем), стоимость и срок доставки могут измениться.</b>";

// Additional services
$MESS['IPOLSDEK_FAQ_ADD_SERVICES_TITLE'] = '- О дополнительных услугах';
$MESS['IPOLSDEK_FAQ_ADD_SERVICES_DESCR'] = "Список дополнительных услуг, которые отображаются в форме отправки заявки в СДЭК. Краткие описания дополнительных услуг можно посмотреть, кликая на значок \"?\" около их названий. Подробную информацию о дополнительных услугах вы можете у вашего персонального менеджера СДЭК.<br><br>
Колонка \"Показывать в форме отправки\" отвечает за доступность дополнительной услуги в форме отправки заявки в СДЭК.<br><br>
Колонка \"Выбрана по-умолчанию\" отвечает за автоматический выбор данной дополнительной услуги в форме отправки заявки и при создании заявок через Автоматизацию.<br><br>
<b>Если дополнительная услуга не отмечена в \"Показывать в форме отправки\", но отмечена в \"Выбрана по-умолчанию\", она все равно будет добавлена, но не будет видна в форме отправки заявки.</b><br>
<b>Если дополнительной услуги нет в списке, значит ее добавление для создаваемой через API СДЭК заявки невозможно, либо запрещено.</b>";

	// Города-отправители
$MESS['IPOLSDEK_FAQ_WARHOUSES_TITLE'] = "- О разбиении";
$MESS['IPOLSDEK_FAQ_WARHOUSES_DESCR'] = "<p>Разбиение предназначено для корректировки стоимости и сроков доставки товаров, при условии, что они доставляются из разных городов-отправителей. Модифицируется только финальная стоимость доставки и ее сроки, никаких других манипуляций с заказом (в том числе - и автоматическое разбиение на Отгрузки) не производится.</p><p>Для работы функционала необходимо задать функцию-распределение. Подробнее: FAQ -> Разные города-отправители для одного заказа.</p>";	
	// Об автоотгрузках
$MESS['IPOLSDEK_FAQ_AUTOUPLOADS_TITLE'] = "- Про автоотгрузки";
$MESS['IPOLSDEK_FAQ_AUTOUPLOADS_DESCR'] = "<p>Автоотгрузки предназначены для автоматической выгрузки заявок на доставку в СДЭК, <strong>если заказы оформлены через службы доставки модуля</strong>. Это обеспечит автоматизацию работы модуля, однако рекомендуется отправлять заявки через соответствующую форму. Не рекомендуется включать функционал при отсутствии разбиения адреса на улицу/дом/квартиру, так как в таком случае покупатель может занести в графу адреса что угодно. Контроль за отправленными автоматом заявками можно вести во вкладке \"Автоматизация\", которая появится после включения фукнкционала. Перед включением убедитесь, что вы изучили соответствующий пункт на закладке FAQ</p><p>Автоотправка производится либо при создании заказа, либо при переводе заказа в определенный статус - в зависимости от того, что выбрано в соответствующей настройке.</p><p>Имейте в виду, что отправляться будут только те заказы, что были оформлены через компонент одношагового оформления заказа. Использование функционала не освобождает от контроля за заявками в графе \"Заявки\" или через личный кабинет. Пользователь сам несет ответственность за корректность выгруженных заявок. Техподдержка модуля не занимается вопросами, связанными с автоматизацией: если работа функционала неудовлетворительна - следует перейти на ручную отправку.</p>";
	// Отладка
$MESS['IPOLSDEK_FAQ_LOGGING_TITLE'] = "- О логировании";
$MESS['IPOLSDEK_FAQ_LOGGING_DESCR'] = "<p>Встроенный функционал отладки позволяет получать данные по запросам и расчетам, в том числе - XML запроса и ответа, требуемый СДЭКом при диагностики. Необходимо отметить нужные события для логирования, после чего - сынициировать их (запустить расчет или отправить заказ). Ссылка на файл лога всегда закреплена на данной вкладке, независимо от того, велось ли логирование, или нет. Учтите, что модуль кэширует расчет доставки, поэтому для корректности обработки необходимо сбрасывать кэш.</p>";
$MESS['IPOLSDEK_FAQ_EVENTS_TITLE'] = "- О событиях";
$MESS['IPOLSDEK_FAQ_EVENTS_DESCR'] = "<p>Модуль генерирует ряд событий, которые позволяют модифицировать данные расчета или его результат. Часть из них документированы (см. FAQ -> Модификация результатов расчетов), часть оставлены для внутреннего пользования. В таблицах ниже представлены события модуля, на которые есть подписки, и функции-обработчики (при наличии подписки).</p>";
$MESS['IPOLSDEK_FAQ_CONSTANTS_TITLE'] = "- О константах";
$MESS['IPOLSDEK_FAQ_CONSTANTS_DESCR'] = "<p>Модуль имеет ряд констант, которые могут управлять его поведением. Это сказывается на результатах расчетов и скорости работы. Часть констант не документируются из-за критичного их влияния на код модуля.</p>";
$MESS['IPOLSDEK_FAQ_AGENTS_TITLE'] = "- Об агентах";
$MESS['IPOLSDEK_FAQ_AGENTS_DESCR'] = "<p>Модуль создает на сайте несколько <a href='http://dev.1c-bitrix.ru/learning/course/?COURSE_ID=43&LESSON_ID=3436' target='_blank'>агентов</a>, которые вызываются с определенной периодичностью для поддержания работоспособности функционалов. В редких случаях агенты могут \"слететь\" из-за непредвиденных ошибок. Лучший способ их починить - перелогиниться в модуле.</p>";
$MESS['IPOLSDEK_AGENT_NO_AGENT'] = "<span class='errorText'>Агент не обнаружен <a href='#' class='PropHint' onclick='return IPOLSDEK_setups.popup(\"pop-noagent\", this);'></a>.</span>";
$MESS['IPOLSDEK_AGENT_DISABLED_AGENT'] = "<span class='errorText'>Агент неактивен.</span>";
$MESS['IPOLSDEK_AGENT_LATE_AGENT'] = "<span class='errorText'>Агент давно не запускался.</span>";
$MESS['IPOLSDEK_AGENT_OK_AGENT'] = "<span style='color:green'>Агент работает корректно</span>";
$MESS['IPOLSDEK_FAQ_PVZRESTORE_TITLE'] = "- О восстановлении ПВЗ";
$MESS['IPOLSDEK_FAQ_PVZRESTORE_DESCR'] = "<p>Если пропали ПВЗ конкретного города или они кажутся \"битыми\" - можно восстановить список ПВЗ из резервной копии, чтобы выбор ПВЗ продолжал функционировать, пока СДЭК чинит список ПВЗ на своей стороне. Резервные копии делаются каждую неделю, поэтому данные могут быть менее актуальными в случае открытия-закрытия ПВЗ вне периода синхронизации. Данный случай восстановления служит только для экстренного решения проблемы с самовывозом.<br>Учтите, что он не влияет на стандартную синхронизацию, проводимую модулем раз в сутки - поэтому есть вероятность, что данные снова затрутся после обычной синхронизации, тогда процедуру придется повторять вплоть до решения проблем на стороне СДЭКа.</p>";

	// FAQ авторизация
$MESS ['IPOLSDEK_FAQ_API_TITLE'] = "Получение доступов";
$MESS ['IPOLSDEK_FAQ_API_DESCR'] = "<p>Для авторизации необходимо использовать ключи интеграции, которые можно получить 
в <a href='https://lk.cdek.ru/integration' target='_blank'>личном кабинете СДЭК</a> в разделе Интеграция.</p>";

$MESS ['IPOLSDEK_FAQ_AGREEMENT_TITLE'] = "Заключение договора";
$MESS ['IPOLSDEK_FAQ_AGREEMENT_DESCR'] = "<p>Для авторизации в ЛК СДЭК и получения ключей интеграции необходимо 
заключить договор с курьерской службой СДЭК. Для заключения договора со СДЭК необходимо отправить заявку на сайте в 
разделе «Заключить договор». Заполнить <a href='https://cdek.ru/zapolnit-anketu' target='_blank'>специальную форму</a> 
и нажать кнопку Отправить. После получения подписанного договора вам на электронную почту поступит письмо с паролем для 
входа в <a href='https://lk.cdek.ru' target='_blank'>личный кабинет СДЭК</a>.</p>
<p>Для полноценной работы модуля необходимо заключать договор типа Интернет-магазин. Модуль предназначен для работы с 
данным типом договора. Тип договора Интернет-магазин позволяет работать с более выгодными тарифами типа Посылка.</p>";

$MESS ['IPOLSDEK_FAQ_AUTH_FAILED_TITLE'] = "Не удалось авторизоваться. Что делать?";
$MESS ['IPOLSDEK_FAQ_AUTH_FAILED_DESCR'] = "<ul>
	<li>Проверьте данные, полученные от СДЭКа и повторите попытку авторизации.</li>
	<li>С <strong>тестовыми доступами</strong> из документации модуль не работает. Отправленные заявки после теста можно удалить из модуля. Необходимость работы в тестовом контуре отсутствует и добавлена не будет.</li>
</ul>
<br>
Если проблему решить не получается - напишите в нашу техподдержку на <span style='color:#AC12B1'>support@ipolh.com</span> с указанием вашего номера договора со СДЭК.
</div>";

	//FAQ города
$MESS ['IPOLSDEK_FAQ_ADDCOUNTRY_TITLE'] = "- Порядок добавления страны";
$MESS ['IPOLSDEK_FAQ_ADDCOUNTRY_DESCR'] = "<ol><li>Отметить необходимую страну (страны) в списке.</li><li>Сохранить настройки.</li><li>Запустить синхронизацию.</li></ol><p>Если страна впоследствии будет отключена в этой настройке - доставка СДЭКа не будет выводиться в ее городах, игнорируя все прочие настройки.</p><p>Поля \"Подключенный аккаунт\" и \"Валюта\" заполняются в соответствии с выбранным режимом работы с соответствующими странами (раздел FAQ -> Доставка в Республику Беларусь и Казахстан).</p>";
$MESS ['IPOLSDEK_FAQ_CITYHINT_TITLE'] = "- Пояснение по таблицам";
$MESS ['IPOLSDEK_FAQ_CITYHINT_DESCR'] = "<p>Чтобы отсылать заявку в СДЭК, необходимо отослать id местоположения города-получателя в системе СДЭК. Чтобы выяснить этот id, модуль регулярно проводит синхронизацию местоположений, имеющихся на сайте с местоположениями СДЭКа. В процессе синхронизации могут произойти ситуации, когда \"город\" СДЭКа не найден среди местоположений Битрикса (ведь у СДЭКа их около десяти тысяч, в то время как стандартные местоположения Битрикса ограничиваются полуторами). Данные таблицы несут справочный характер для решения спорных ситуаций.</p><p>Наибольшую ценность представляет из себя группа \"Конфликтующие\" - это те местоположения Битрикса, которым соответствуют два или более местоположений СДЭКа. Например, в Ленинградской области есть два населенных пункта с названием \"Никольское\", в то время как в Битриксе есть только одно местоположение с таким названием. В таблице указано местоположение, для которого рассчитывается доставка при оформлении заказа. Перед созданием заявки необходимо уточнить у клиента, какой именно населенный пункт имелся в виду при оформлении заказа. Если подразумевался тот, что является претендентом - его нужно выбрать в выпадающем списке.</p><p>Расширить список местоположений в Битриксе можно несколькими способами: <ul><li>Загрузив расширенную версию местоположений, синхронизировав их с имеющимися в <a href='/bitrix/admin/sale_location_import.php' target='_blank'>настройке импорта местоположений</a></li><li>Включив \"Автосопоставление городов\" (Сервисные свойства), чтобы города добавлялись по мере расчета доставки в них</li><li>Воспользоваться импортом модуля, чтобы попытаться загрузить местоположения, определенные в СДЭКе. Перед использованием внимательно ознакомьтесь с соответствующим разделом FAQ и помните, что функционал работает в бета-режиме</li></p>";

	// FAQ импорт 
$MESS ['IPOLSDEK_FAQ_IMPORT_TITLE'] = "- Импорт городов";
$MESS ['IPOLSDEK_FAQ_IMPORT_DESCR'] = "<p>Функционал позволяет импортировать на сайт города из таблицы СДЭКа. Предоставляется \"как есть\". Рекомендуется сделать резервную копию сайта.<br>Процесс импорта весьма продолжительный (несколько часов, в зависимости от мощности сервера). Учтите, что сайт будет работать медленно в процессе импорта. Не закрывайте окно, пока импорт не будет завершен.<br>Подробнее - FAQ -> Дополнительные возможности -> Прочее -> Импорт городов СДЭК</p>";
	// FAQ: О модуле
$MESS ['IPOLSDEK_FAQ_HDR_SETUP'] = "О модуле";

$MESS ['IPOLSDEK_FAQ_WTF_TITLE'] = "- Для чего нужен модуль";
$MESS ['IPOLSDEK_FAQ_WTF_DESCR'] = "Модуль обеспечивает интеграцию Интернет-магазина со службой доставки <a href='http://www.edostavka.ru/' target='_blank'>СДЭК</a>. Обеспечивается отсылание заявок на доставку заказов, мониторинг статусов доставки заказов и выставление соответствующих им статусов в админке Битрикса. В модуле присутствует функционал печати актов и товарных накладных для заказов, возможна массовая печать заказов.<br>Вместе с модулем устанавливаются автоматизированные службы доставки СДЭК, позволяющие покупателям выбрать доставку курьером, или же выбрать удобный для них пункт самовывоза. Стоимость доставки вычисляется с помощью API СДЭКа с учетом габаритов заказа.";

$MESS ['IPOLSDEK_FAQ_HIW_TITLE'] = "- Как работает модуль";
$MESS ['IPOLSDEK_FAQ_HIW_DESCR'] = "Состав модуля:
<ul>
	<li>функционал автоматизированных служб доставки;</li>
	<li>функционал расчета габаритов заказа;</li>
	<li>функионал расчета стоимости доставки;</li>
	<li>функционал отображения информации о пунктах самовывоза;</li>
	<li>функционал оформления заявки на доставку;</li>
	<li>функционал оформления заявки на вызов курьера;</li>
	<li>функционал печати заказов и актов;</li>
	<li>функионал синхронизации местоположений сайта с базой городов СДЭКа;</li>
	<li>база данных с отосланными заявками;</li>
	<li>прочий функционал</li>
</ul>
<p><span class='IPOLSDEK_notConverted'>Модуль создает новую <a href='/bitrix/admin/sale_delivery_handler_edit.php?SID=sdek' target='_blank'>автоматизированную службу доставки</a> с кодом sdek.</span><span class='IPOLSDEK_converted'>Модуль устанавливает новую <a href='/bitrix/admin/sale_delivery_service_list.php' target='_blank'>автоматизированную службу доставки</a> с кодом sdek, которую можно добавить на сайт.</span> У службы есть два профиля: курьер (courier) и самовывоз (pickup). Они будут отображаться на странице оформления заказа, если в выбранный пользователем город доставки возможна доставка указанным тарифом. Кроме того если выполняются ограничения на заказ: вес не более 75 килограмм (для заказов более 30 килограмм будет применяться тариф для крупногабаритных грузов). Эти службы доставки заметно упрощают процесс заполнения заявки, а так же позволяют покупателю наглядно и удобно выбрать пункт самовывоза или почтомат. Возможность доставки по выбранному профилю, стоимость и сроки рассчитываются на стороне API СДЭКа.</p>
<p>Модуль использует встроенный функционал расчета габаритов заказа и API СДЭКа для вычисления стоимости доставки при оформлении заказа.</p>
<p>Модуль устанавливает компонент \"Пункты самовывоза СДЭК\", который отображает детальные сведения о пунктах, и может использоваться в качестве наглядной информации о доставке.</p>
<p>Заявка на доставку составляется для каждого заказа в отдельности, причем контроль за корректностью введенных данных возлагается на пользователя. При сохранении данные о заявке сохраняются в базу данных. При отсылке заявки модуль формирует XML-документ согласно <a href='http://www.edostavka.ru/clients/integrator.html' target='_blank'>документации СДЭКа</a> и отсылает его на сервер. Результат обработки заявки приходит сразу же, выдавая либо ошибку, либо информацию об успешном принятии заявки. Модуль создает на сайте <a href='http://dev.1c-bitrix.ru/learning/course/?COURSE_ID=43&LESSON_ID=3436' target='_blank'>агент</a>, который должен запускаться каждые 30 минут и запрашивать статусы отосланных заявок. Получив ответ, модуль анализирует его и обновляет статусы заявок в зависимости от результатов их обработки, а так же выставляет статусы соответствующим заказам, если включена опция в настройках модуля.</p>
<p>Отосланные заявки при необходимости можно отозвать и пересоздать заново.</p>
<p><span class='IPOLSDEK_warning'>Важно!</span> Данный модуль разработан компанией, пользующейся услугами СДЭКа, но не являющейся ее представителем, поэтому мы не можем ответить на вопросы касательно работы сервиса СДЭКа.</p>";

	// FAQ: Начало работы
$MESS ['IPOLSDEK_FAQ_HDR_ABOUT'] = "Начало работы";

$MESS ['IPOLSDEK_FAQ_TURNON_TITLE'] = "- Включение функционала";
$MESS ['IPOLSDEK_FAQ_TURNON_DESCR'] = "<p><span class='IPOLSDEK_warning'>Важно!</span> Первым делом необходимо запустить синхронизацию модуля, чтобы выгрузить города СДЭКа. Для этого нужно перейти в Настройки, кликнуть по заголовку \"Сервисные свойства\" и нажать кнопку \"Синхронизация\". После получения сообщения о завершении синхронизации, <strong>сохраните настройки модуля</strong>. Имейте в виду, что \"Синхронизация\" и \"Импорт городов\" - <strong>это два совершенно разных функционала!</strong> Теперь перейдите на страницу настроек и заполните все поля согласно нуждам сайта.</p>
<div class='IPOLSDEK_converted'>
	На данном сайте была проведена Конвертация Интернет магазина, поэтому перед настройкой модуля необходимо добавить автоматизированную службу доставки СДЭК. Это делается на странице \"<a href='/bitrix/admin/sale_delivery_service_list.php' target='_blank'>Список служб доставок</a>\". Для добавления необходимо:
	<ol>
		<li>Нажать \"Добавить\"->\"Автоматизированная служба доставки\"</li>
		<li>Заполнить вкладку \"Общие настройки\" согласно нуждам сайта</li>
		<li>Во вкладке \"Настройки обработчика\" необходимо выбрать Службу доставки СДЭК [sdek] и нажать \"Применить\"</li>
		<li>Убедиться, что в закладке \"Профили\" создались профили \"Доставка курьером\" и \"Самовывоз\".</li>
		<li>Поставить активность службе доставки (\"Общие настройки\") и сохранить изменения.</li>
	</ol>
	<img src=\"/bitrix/images/ipol.sdek/FAQ_6.png\"><br><br>
</div>
<p>Особое внимание следует уделить полю <strong>Город-отправитель</strong> - он должен определиться автоматически из <a href='/bitrix/admin/settings.php?lang=ru&mid=sale' target='_blank'>настроек Интернет-магазина</a>. Если он не определился - значит либо указанная настройка не задана (и ее надо указать), либо город не был найден в местоположениях СДЭКа. Если город не определился - проверьте <a href='/bitrix/admin/settings.php?lang=ru&mid=sale'>настройки модуля Интернет-магазина</a>, а так же - определился ли он в базе данных СДЭК (при успешной синхронизации надо зайти во вкладку \"Города\": он должен присутствовать в таблице \"Обработанные\"; если его там нет - попробуйте обновить местоположения магазина из базы данных Битрикса и снова запустить синхронизацию). Так же проблема может возникнуть, если на сайте установлено несколько копий местоположений. Для корректной работы модуля на сайте должна быть только одна копия. Если проведенные манипуляции не помогут - нужно обратиться в техподдержку модуля.<br>Поля \"Дополнительные города-отправители\" служат исключительно для того, чтобы при необходимости поменять город-отправитель в процессе отправления заявки. Они не заменят автоматически определенный город-отправитель.</p>
<p>Поле <strong>API-ключ Яндекс.карт</strong> необходимо заполнить для корректной работы виджета выбора ПВЗ - без указания ключа он не сможет корректно подключать скрипт карт. Ознакомьтесь с подсказкой.</p>
<p>Все группы настроек содержат развернутые пояснения рядом со своей группой или в справочных ссылках. После настройки нужно отправить тестовую заявку (а именно - попробовать заполнить заявку на странице редактирования любого заказа и получить информацию об успешном отправлении или возникших ошибок). В случае успешной отправки - проверить корректность данных в ЛК и удалить заявку <strong>через функционал модуля</strong>, чтобы он не пытался синхронизировать ее статус. Подробнее о заявках - в разделе \"Оформление и отправка заявки\".</p>
<p>Закладка \"Права\" определяет, какие группы пользователей, помимо админов, могут пользоваться функционалом модуля. При выставлении \"Просмотра всех данных\" (R) пользователи будут иметь доступ к функциям чтения информации о заявках (со стороны API модуля, но не к странице настроек модуля). При выставлении \"Записи\" (W) - могут полноценно использовать модуль для отправления заявок. Доступ к странице настроек модуля будет только у админов.</p>";

$MESS['IPOLSDEK_FAQ_DELSYS_TITLE'] = "- Настройка службы доставки";
$MESS['IPOLSDEK_FAQ_DELSYS_DESCR'] = "
<p class='IPOLSDEK_converted'>Первым делом следует убедиться, что ваш шаблон оформления заказа поддерживает совместимость с прошлыми версиями. Для этого нужно поставить флаг \"Режим совместимости для предыдущего шаблона\" в параметрах шаблона оформления заказа. Имейте в виду, что 100% работоспособность с кастомизированными компонентами и шаблонами не гарантируется.</p>
<p><strong>Управление службами доставки</strong></p>
Управление доставками находится на <a class='IPOLSDEK_notConverted' href='/bitrix/admin/sale_delivery_handler_edit.php?SID=sdek' target='_blank'>странице настроек автоматизированных служб доставки</a><a class='IPOLSDEK_converted' href='/bitrix/admin/sale_delivery_service_list.php' target='_blank'>странице настроек служб доставки</a>. Здесь можно настроить: <ul><li>Активность службы доставки и ее профилей</li><li>Название и описание службе доставки и ее профилям</li><li>Наценку на стоимость доставки</li><li>Привязку профилей к платежным системам</li><li>Ограничения по габаритам и стоимости заказа</li></ul>
<span class='IPOLSDEK_warning'>Внимание!</span> Не трогайте вкладку \"Параметры запроса\" без надобности - она предназначена только для магазинов, пользующихся разными аккаунтами или отправляющими заказы со складов в разных городах.<br><br>
Во вкладке \"Параметры запроса\" можно задавать разные параметры для нескольких копий доставки (актуально только для Конвертированного Интернет-маагазина). Эту вкладку имеет смысл посещать после того, как настроена и опробована работа модуля.<br>
<ul>
    <li>Аккаунт - подключает службу доставки к определенному активному аккаунту. Список ваших активных аккаунтов можно найти в настройках модуля по кнопке \"Аккаунты\". Эта опция поможет магазинам, пользующимся несколькими аккаунтами; в форме оформления заявки будет по умолчанию выбран тот аккаунт, который установлен для службы доставки.</li>
    <li>Город-отправитель - привязывает службу доставки к определенному городу-отправителю. Список дополнительных городов-отправителей задается в настройках модуля в опции \"Дополнительные города-отправители\".</li>
    <li>Страны - с какими странами работает данная служба доставки. Список подключенных стран можно найти в настройках модуля во вкладке \"Города\". ВАЖНО: если у вас задан определенный аккаунт для стран с разной валютой (Казахстан) - в доставке с фиксированным аккаунтом необходимо отключить эти страны, иначе возможен некорректный расчет.</li>
    <li>Пользовательский идентификатор - дополнительная метка, которую можно использовать в таких событиях модуля, как onCalculate и OnCompabilityBefore.</li>
</ul>
</p>
<p><strong>Общие сведения</strong></p>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Привязка способа оплаты к Службе Доставок.</a>
    <div class='ipol_inst'>
        Для того, чтобы привязать платежные системы к конкретным вариантам доставки используйте стандартный функционал Bitrix (доступен с 14-й версии) - в <a href='/bitrix/admin/sale_pay_system.php' target='_blank'>настройках платежных систем</a> откройте нужную плат.систему и во вкладке 'Службы доставки' выберите службы для которых будет доступна данная платежная система.
    </div>
</div>
<div class='ipol_subFaq IPOLSDEK_notConverted'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Не отображается срок доставки.</a>
    <div class='ipol_inst'>
        Если это происходит при выборе профиля доставки - это обычная ошибка шаблона оформления заказа.
    </div>
</div>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Учет веса заказа.</a>
    <div class='ipol_inst'>
       	Ограничения по весу заказа учитываются самим модулем при расчете служб доставки. Данные о весе товара берутся только из торгового каталога. Если модуль некорректно обрабатывает вес заказа - проверьте в первую очередь настройки торгового каталога в товаре.
    </div>
</div>

Подробности расчета стоимости доставки рассмотрены в разделе \"Особенности расчета стоимости доставки\".

<p><strong>Дополнительные возможности (для программистов)</strong></p>
<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Вывести дату доставки.</a>
		<div class='ipol_inst'>
            При необходимости вывести срок доставки (в формате 1-2 дня) - достаточно в нужном месте в шаблоне оформления закза вставить следующую конструкцию: 
            <div style='color:#AC12B1'><pre>		
&lt;?
    if(cmodule::includeModule('ipol.sdek'))
        echo CDeliverySDEK::\$date;
?&gt;
            </pre></div>
            Если же необходимо вывести дату доставки в формате день.месяц.год, воспользуйтесь конструкцией:
            <div style='color:#AC12B1'><pre>		
&lt;?
    if(cmodule::includeModule('ipol.sdek'))
        echo CDeliverySDEK::getDateDeliv(PARAM);
?&gt;
            </pre></div>
            Где PARAM - это:
            <ul>
            <li>0 - если нужно получить ближайшую дату доставки;</li>
            <li>1 - если нужно получить максимальную дату доставки</li>
            <li>пусто - если нужно получить дату в виде минимальная - максимальная</li>
            </ul>
		</div>
</div>

<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Иконки для службы доставки.</a>
    <div class='ipol_inst'>
        <div class='IPOLSDEK_notConverted'>
            Чтобы поставить службам доставки иконки, необходимо изменить шаблон оформления заказа, который (скорей всего) располагается по адресу:
            <ul>
                <li>Компонент \"Одношаговое оформление заказа\": <путь к шаблонам>/sale.order.ajax/<название используемого шаблона>/delivery.php.</li>
                <li>Компонент \"Процедура оформления заказа\": <путь к шаблонам>/sale.order.full/<название используемого шаблона>/step3.php</li>
            </ul>
            В файле необходимо найти место, где происходит сборка html доставок. Если шаблон не был сильно модифицирован, это место выглядит примерно так:<br>
            <pre><span style='color:#AC12B1'>foreach (\$arDelivery[\"PROFILES\"] as \$profile_id => \$arProfile)</span></pre>
            Логотип можно вставить следующим способом:
            <div style='color:#AC12B1'>
                <pre>
if(\$delivery_id=='sdek') <span style='color:#008000'>//применяем только к доставкам СДЭК</span>
    \$deliveryImgURL='/bitrix/images/ipol.sdek/'.\$profile_id.'.png'; <span style='color:#008000'>//путь к картинке</span>
                </pre>
            </div>
            Если у вас стандартный шаблон visual, то данное условие необходимо вставить после кода в котором задается стандартное deliveryImgURL:
            <div style='color:#AC12B1'>
                <pre>
    \$deliveryImgURL = \$arFileTmp[\"src\"];
else:
    \$deliveryImgURL = \$templateFolder.\"/images/logo-default-d.gif\";
endif;
                </pre>
            </div>
            На случай если шаблон сильно модифицирован, то адреса картинок для профилей следующие:<br>
            <ul>
                <li>Курьер - /bitrix/images/ipol.sdek/courier.png</li>
                <li>Самовывоз - /bitrix/images/ipol.sdek/pickup.png</li>
                <li>Почтоматы - /bitrix/images/ipol.sdek/inpost.png</li>
            </ul>
        </div>
        <div class='IPOLSDEK_converted'>
            Чтобы задать уникальные иконки службе доставки - необходимо перейти в <a href='/bitrix/admin/sale_delivery_service_list.php' target='_blank'>настройки службы доставки</a> и задать иконку либо службе доставки, либо каждому профилю в отдельности (в списке профилей щелкнуть на название профиля или на выпадающий список -> Редактировать).<br>
                Логотипы необходимо загрузить на свой компьютер, и вставить в поле \"Логотип\".<br>
                <table>
                    <tr><td>Общий</td><td>Курьер</td><td>Самовывоз</td><td>Постамат</td></tr>
                    <tr>
                        <td><img src='/bitrix/images/ipol.sdek/sdek.png'></td>
                        <td><img src='/bitrix/images/ipol.sdek/courier.png'></td>
                        <td><img src='/bitrix/images/ipol.sdek/pickup.png'></td>
                        <td><img src='/bitrix/images/ipol.sdek/postamat.png'></td>
                    </tr>
                </table>
                Не забудьте сохранить настройки.
        </div>
    </div>
</div>

<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Автоматическое открытие окна выбора пункта самовывоза при выборе самовывоза СДЭК.</a>
    <div class='ipol_inst'>
        Чтобы обрабатывать выбор пункта самовывоза без перезагрузки страницы используется функция <span style='color:#AC12B1'>IPOLSDEK_DeliveryChangeEvent</span>.<br>
        В шаблоне оформления заказа тегу, ответственному за выбор пункта самовывоза (в стандартном шаблоне их несколько, можно отследить по <span style='color:#AC12B1'>\"onclick='submitForm()'\"</span>) отредактировать атрибут <span style='color:#AC12B1'>onclick</span>, заменив его с <span style='color:#AC12B1'>\"submitForm()\"</span> на <span style='color:#AC12B1'>\"IPOLSDEK_pvz.selectPVZ('#ID#','#MODE#');\"</span>, где <ul><li>#ID# - id профиля доставки (<span class='IPOLSDEK_notConverted'>pickup</span><span class='IPOLSDEK_converted'>из настроек профиля службы доставки</span>)</li><li>#MODE# - режим работы (PVZ - для самовывоза)</li></ul>Далее создать javascript-функцию вида
        <div style='color:#AC12B1'><pre>
function IPOLSDEK_DeliveryChangeEvent(id) { <span style='color:#008000'>//название принципиально</span>
	$('#'+id).prop('checked', 'Y');
	submitForm();
}
        </pre></div>
    </div>
</div>
";

$MESS['IPOLSDEK_FAQ_STORES_TITLE'] = "- Адрес отправления, Отправитель и Продавец";
$MESS['IPOLSDEK_FAQ_STORES_DESCR'] = "<b>Профили с данными об Адресе отправления, Отправителе и Продавце добавляются <a href='/bitrix/admin/ipol_sdek_stores.php?lang=ru' target='_blank'>на отдельной странице настроек</a> (Магазин -> СДЭК -> Отправители и продавцы).</b><br><br>
<b>Если вы не будете создавать заявки на вызов курьера, а также по отправляемым в СДЭК заказам указывать свой Адрес отправления, данные Отправителя, Продавца (в этом случае СДЭК возьмет их из договора), профили можно не создавать и при необходимости заполнять данные вручную.</b><br><br>
Добавление, либо редактирование данных производится с помощью формы \"Отправитель и Продавец\". Можно создать много разных профилей, с разными настройками. Они могут использоваться как в заявках по заказам, так и в заявках на вызов курьера.<br><br>
<img src=\"/bitrix/images/ipol.sdek/FAQ_STORE_1.png\">
<br><br>
<b>Использовать по умолчанию для данного города-отправителя</b><br><br>
Данные этого профиля будут использоваться для формы отправки заявки \"СДЭК доставка\", автоматически заполняя соответствующие поля формы, для указанного ниже города-отправителя. Эта опция позволяет определить: какой именно профиль использовать, если для одного города-отправителя их создано несколько, например, у вас два склада в одном городе.<br><br>
<b>Адрес отправления</b><br><br>
Используется как при отправке заявок по заказам, так и в заявках на забор консолидированного груза курьером СДЭК. На этот адрес приедет курьер. Опция \"Передавать при отправке заявок\" определяет: нужно ли подставлять в форму отправки заявок улицу, дом и квартиру/офис из данного профиля или нет. Их можно не передавать, оставляя при отправке заявки эти поля не заполненными. Тогда данные по адресу отправления для забора курьером одиночных заказов (заказы с тарифами доставки от двери) СДЭК возьмет из договора.<br><br>
<b>Отправитель</b><br><br>
Используется в заявках на забор консолидированного груза курьером СДЭК и может использоваться при отправке заявок по заказам. Опция \"Передавать при отправке заявок\" определяет: нужно ли подставлять в форму отправки заявок название компании, ФИО контактного лица, номер телефона из данного профиля Отправителя или нет. Если их не передавать, оставляя при отправке заявки не заполненными, СДЭК использует данные договора.<br><br>
Если при создании заявки по заказу данные Отправителя были заполнены, они будут указаны в накладной в блоке Компания вместо данных из договора со СДЭК, а вы будете третьей стороной.<br><br>
<b>Продавец</b><br><br>
Используется при отправке заявок по заказам. Опция \"Передавать при отправке заявок\" определяет: нужно ли подставлять в форму отправки заявок название компании, номер телефона и адрес Продавца из данного профиля или нет. Если их не передавать, оставляя при отправке заявки не заполненными, СДЭК использует данные договора.<br><br> 
Если при создании заявки по заказу данные Продавца были заполнены, они будут указаны в накладной в блоке Продавец вместо данных из договора со СДЭК.<br><br>
<b>Информация для курьера</b><br><br>
Эти данные используются исключительно в заявках на вызов курьера, как для консолидированного забора грузов, так и для забора одиночных заказов, автоматически заполняя поля формы отправки заявки на вызов курьера при выборе данного профиля Отправителя.<br><br>
<b>Пояснения по настройке</b><br><br>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Один город-отправитель, один адрес отправления</a>
    <div class='ipol_inst'>
        Создайте один профиль Адреса отправления, Отправителя и Продавца, указав в его настройках ваш город-отправитель и данные адреса отправления. Не забудьте отметить флаг \"Использовать по умолчанию для данного города-отправителя\".  
    </div>
</div>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Один город-отправитель, несколько адресов отправления</a>
    <div class='ipol_inst'>
        Создайте столько профилей Адреса отправления, Отправителя и Продавца, сколько разных адресов отправления вам необходимо. Всем укажите один и тот же город-отправитель. Для профиля с наиболее часто используемым адресом установите флаг \"Использовать по умолчанию для данного города-отправителя\", у остальных профилей по этому городу-отправителю отключите флаг.  
    </div>
</div>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Несколько городов-отправителей, несколько адресов отправления</a>
    <div class='ipol_inst'>
        Настройки идентичны схеме \"Один город-отправитель, несколько адресов отправления\" за исключением того, что их нужно повторить для каждого из городов-отправителей: как для основного, так и для дополнительных.   
    </div>
</div>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Несколько аккаунтов СДЭК</a>
    <div class='ipol_inst'>
        Профиль связан не с аккаунтом СДЭК, а с городом-отправителем. В зависимости от конкретной ситуации можно как обойтись одним профилем (по всем договорам СДЭК и, соответственно, аккаунтам, вы вызываете курьера СДЭК на один и тот же адрес отправления), так и создать несколько, с разными данными.   
    </div>
</div>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Что будет, если для города-отправителя нет используемого по умолчанию профиля Адреса отправления, Отправителя и Продавца?</a>
    <div class='ipol_inst'>
        В форме отправки заявки в СДЭК соответствующие поля не будут заполнены автоматически. Их можно заполнить вручную, непосредственно перед отправкой заявки. Если не заполнять, СДЭК использует данные из договора.<br><br>
        Учтите: в АПИ СДЭК нет технической возможности запросить данные вашего договора. Т.е. увидеть их \"через модуль\" нельзя. Вы можете посмотреть их Профиле в Личном Кабинете СДЭК, либо уточнить у персонального менеджера СДЭК.     
    </div>    
</div>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Что будет, если изменить данные в профиле?</a>
    <div class='ipol_inst'>
        Для новых заявок по заказам и заявок на вызов курьера начнут использоваться новые данные профиля, сообразно его текущим настройкам. В ранее созданных заявках изменений не произойдет.  
    </div>    
</div>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Что будет, если деактивировать профиль?</a>
    <div class='ipol_inst'>
        Его данные не будут использоваться для заявок по заказам, даже если отмечен флаг \"Использовать по умолчанию для данного города-отправителя\", а также его нельзя будет выбрать при создании заявок на вызов курьера.
    </div>    
</div>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Что будет, если удалить профиль?</a>
    <div class='ipol_inst'>
        Его данные будут удалены и их станет невозможно использовать как в заявках по заказам, так и в заявках на вызов курьера.<br><br>
        <b>Это касается только новых заявок. По уже созданным заявкам никаких изменений не произойдет.</b>  
    </div>    
</div>";

$MESS['IPOLSDEK_FAQ_SEND_TITLE'] = "- Оформление и отправка заявки";
$MESS['IPOLSDEK_FAQ_SEND_DESCR'] = "<p>
	<strong>1. Заполнение полей</strong><br>
	<p>Заполнить данные для доставки можно на странице заказа (Магазин -> Заказы -> Нужный заказ). Нужное окно вызывается кнопкой \"СДЭК доставка\".<br>
	<span class='IPOLSDEK_converted'>Если вы используете функционал отгрузок - то отправлять заявку следует из окна отгрузки (если заказ разбит на несколько отгрузок, иначе это не имеет смысла).</span></p>
	<img src=\"/bitrix/images/ipol.sdek/FAQ_1.png\">
	<p class='IPOLSDEK_b24'><img src=\"/bitrix/images/ipol.sdek/FAQ_SEND_B24.png\"></p>
	<p>В открывшемся окне необходимо заполнить данные заявки. Модуль проверит заполненность необходимых полей. По умолчанию поля будут заполнены свойствами заказа согласно указаниям в настройках.</p>
	<img src=\"/bitrix/images/ipol.sdek/FAQ_2.png\">
	<p>Обратите внимание, что поле <strong>\"Тариф\"</strong> изначально содержит только два значения, соответствующие самовывозу и курьеру. В настройках модуля в разделе \"Настройки тарифов и доп. услуг.\" -> \"Управление тарифами\" можно настроить вывод любого тарифа СДЭКа, чтобы таким образом отправлять из административной части заказ любым тарифом. При смене тарифа покажется оповещение о стоимости и сроках доставки для выбранного тарифа.</p>
	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Подробнее о полях</a>
		<div class='ipol_inst'>
			<strong>Город доставки</strong><br>
			<p>В очень редких случаях при оформлении заявки необходимо выбрать город доставки. Случается это из-за того, что по местоположению Битрикса не получается точно определить город доставки (это происходит, когда в одном регионе есть города с одинаковыми названиями, например, Никольское в Ленинградской области).</p>
			<strong>Адрес доставки</strong><br>
			<p>В СДЭК необходимо передавать адрес доставки, разбитый на улицу, дом и квартиру. Если у вас на сайте адрес заполняется строкой в одном свойстве - он будет записан в поле \"улица\", разбить руками его придется менеджеру.</p>
			<p>Для удобства импортирования адреса заявки, с возможностью автоматически разбивать его на улицу, дом и квартиру, его следует записывать в формате КЛАДРа. В этом может помочь наш модуль <a href='http://marketplace.1c-bitrix.ru/solutions/ipol.kladr/' target='_blank'>\"Автозаполнение адреса доставки по КЛАДР\"</a>.</p>
			<strong>Пункт самовывоза</strong><br>
			<p>Выбор самовывоза доступен при выборе соответствующего тарифа. Для поиска самовывоза по его названию нажмите на иконку карандаша около поля. Учтите, что отправка заявки невозможна, пока поле поиска не закроется (крестик, появившийся на месте карандаша). Это сделано для дополнительной проверки корректности заполнения поля. Если поиску соответствует только один ПВЗ, он будет выбран автоматически при закрытии функционала.</p>
			<strong>Контактный телефон</strong><br>
			СДЭК требует строгое соблюдение определенного формата поля для указания телефона. Номер должен быть написан в международном формате код страны (для России это +7) и сам номер (10 и более цифр).<br>
			Номер не должен содержать никаких букв и символов, кроме специальных символов:
			<ul>
				<li>после кода страны должно быть не менее 10 символов</li>
				<li>разделителями между номерами служат символы \",\" (запятая), \";\" (точка с запятой), \"/\" (косая черта)</li>
				<li>признаком добавочного номера считаются символы \":\" (двоеточие), \"#\" (решетка)</li>
			</ul>
			<strong>Оплата заказа</strong><br>
			<p>При необходимости в окне заявки можно изменить стоимость заказа и доставки в соответствующих полях. Если введенная в поле сумма заказа меньше стоимости включенных в него товаров (например, если заказ был частично оплачен) - оставшаяся сумма будет разбита по имеющимся товарам. Поле стоимости доставки импортируется только из заказа, и автоматически не меняется (то есть, если вы сменили тариф - придется стоимость вписывать руками). Если заказ был оплачен полностью - отметьте флаг \"Курьер не получает деньги за заказ\".</p>
			<p>Если флаг <strong>\"Курьер не получает деньги за заказ\"</strong> недоступен и промаркирован - значит, в этом городе/стране недоступна оплата наличными (а выбрана именно такая платежная система), либо стоимость заказа превышает допустимый предел для города.</p>
			<strong>Габариты заказа</strong><br>
			<p>Габариты заказа можно изменить - для этого нужно щелкнуть на ссылку \"Детали заказа\" и в \"Рассчитанные габариты\" нажать \"Изменить\". Учтите, что стоимость (и, возможно, сроки) доставки при этом могут изменяться, о чем будет сообщено во всплывающем окне.</p>
			<strong>Дополнительные услуги</strong><br>
			<p>Список дополнительных услуг, доступных к оформлению, представлен в настройках модуля в графе \"Настройки тарифов и доп. услуг.\". Если услуга там отсутствует - значит, ее невозможно оформить через Интеграцию, даже если она присутствует в Личном кабинете - таковы правила СДЭК.</p>
		</div>
	</div>	
	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Таблица тарифов</a>
		<div class='ipol_inst'>
			<p>Кнопка <strong>\"Таблица тарифов\"</strong> позволяет отобразить таблицу со всеми доступными тарифами. Доступным считается тариф, для которого доступна доставка заказа, и который был выбраны в настройках как \"Отображаемый тариф\" (Настройки -> Настройки тарифов и доп. услуг). Тарифы рассчитываются поочередно.</p>
			<p>Изначально тариф рассчитывается через службу доставки - таким образом применяются наценки, установленные на службу доставки. Если расчет не совпадает с личным кабинетом - в графе со стоимостью будет стоять оповещающая иконка, которая при наведении мыши выдаст стоимость в личном кабинете. Если расчет недоступен (например, не создана/отключена служба доставки или соответствующий профиль) - будет выведена стоимость личного кабинета, отмеченная иконкой СДЭКа.</p>
			<p><img src='\bitrix\images\ipol.sdek\FAQ_11.png'></p>
		</div>
	</div>
	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Детали заказа</a>
		<div class='ipol_inst'>
			<p>Детали заказа отображаются по щелчку на соответствующем заголовке в форме оформления заявки.</p>
			<p><img src='\bitrix\images\ipol.sdek\FAQ_12.png'></p>
			<strong>Рассчитанные габариты заказа</strong><br>
			<p>Отображаются габариты, рассчитанные по товарам в заказе, с учетом габаритов, заданных в торговом каталоге и значений по-умолчанию, выставленных в модуле. Кнопка \"Изменить\" позволяет редактировать габариты.</p>
			<strong>Указанные габариты заказа</strong><br>
			<p>Если габариты были изменены - они отображаются в этом поле. Если габариты изменены через Места - будет выведено соответствующее оповещение.</p>
			<strong>Объемный вес</strong><br>
			<p>Вес, рассчитанный из габаритов заказа (рассчитанных или указанных). Информативное поле. Если значение больше реального веса - СДЭК примет в расчет его.</p>
			<strong>Стоимость доставки (в заказе)</strong><br>
			<p>Стоимость доставки, зафиксированная в заказе (дубль информации из самого заказа). Если заказ не изменялся, равна той, что видел покупатель.</p>
			<strong>Стоимость доставки (новая)</strong><br>
			<p>Стоимость доставки, рассчитанная по данным из заявки (может отличаться из-за смены тарифа или габаритов).</p>
			<strong>Аккаунт заказа</strong><br>
			<p>Если в модуле авторизовано несколько аккаунтов - отображается тот, с которого он будет отправлен.</p>
		</div>
	</div>
	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Страховка</a>
		<div class='ipol_inst'>
			<p>Если вы хотите отключить страхование - поставьте флаг \"Минимизировать страховку товаров\". Тогда все товары будут выгружены в ЛК с оценочной стоимостью в 1 единицу валюты личного кабинета (как правило, это рубль). Таким образом величина страховки будет минимизирована. Учтите, что это повлечет за собой снижение страховых выплат в случае утраты/порчи товара (если они предусмотрены).</p>
			<p>Если нужно отключить страхование только конкретных заказов - уберите флаг и поставьте его в окне оформления заявки (раздел \"Оплата\"). Учтите, что пока выставлен флаг в настройках модуля, опция в окне оформления заявки не появится за ненадобностью.</p>
		</div>
	</div>
	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Дробное количество товара</a>
		<div class='ipol_inst'>
			<p>В то время как Битрикс допускает указание дробного количества товара (0.1 м, 0.05 кг) - СДЭК требует указывать количество товаров в целочисленном виде. Поэтому если в корзине встречается дробное количество товара - модуль будет считать его в количестве 1, при этом соответственно модифицировав цену и вес. Для информации количество будет подписано в названии товара: Товар (0.12 м) - чтобы отключить подпись, уберите флаг \"Подписывать размерность дробного количества товаров\".</p>
		</div>
	</div>
	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Проверка корректности заполнения поля адреса</a>
		<div class='ipol_inst'>
			<p>При парсинге адреса доставки зачастую работает человеческий фактор, из-за чего покупатель в это поле может запихнуть все, что душе угодно, из-за чего автоматическое разбиение адреса не рационально. Самый верный способ - просто не дать покупателю возможность заполнить его некорректно. Для этого может помочь наш <a href=\"https://marketplace.1c-bitrix.ru/solutions/ipol.kladr/\" target=\"_blank\">модуль для работы с КЛАДРом</a>, который поможет заполнить адрес по универсальному формату, который воспринимается модулем СДЭК.</p>
			<p>Модуль заменяет поле ввода адреса при оформлении заказа на удобную форму с возможностью обращаться к базе адресов Российской Федерации («ФИАС»). Это гарантирует корректность ввода данных и то, что такой адрес в принципе существует - что уберет проблемы с доставкой заказа по адресу.</p>
			<p><img src=\"/bitrix/images/ipol.sdek/FAQ_kladr.gif\"></p>
		</div>
	</div>
</p>
<p>
	<strong>2. Отправка заявки</strong><br>
	Если заявка готова к отправке - нажмите клавишу \"Сохранить и отправить\". После оповещения, что заявка сохранена, можно закрыть окно. Если при отравке возникнут ошибки, их можно просмотреть в этом же окне после перезагрузки страницы.<br>
	Возможна отправка заявки, содержащей только один заказ (то есть только методом, описанным выше).<br>
	При использовании API 2.0 заказ сначала должен пройти валидацию. Иногда это происходит сразу, иногда - через какое-то время. Проверить, принялась ли заявка, можно либо через кнопку \"Проверить статус\", либо дождаться срабатывания агента, который пробежится по всем заказам, что были отправлены - но не получили трекинг.<br>
	<span class='IPOLSDEK_warning'>Вниматие!</span> Если необходимо удалить заявку на заказ, отосланный через модуль, делать это лучше модулем, а не в личном кабинете. Иначе информация о том, что заявка была отозвана, не будет модулем учтена.<br>
	<span class='IPOLSDEK_warning'>Вниматие!</span> Если в заявке находится несколько товаров с одним артикулом, но разными позициями, их артикулы будут преобразованы в <артикул>(2), <артикул>(3), и так далее. СДЭК не позволяет передавать в рамках одного места товары с одним артикулом. Если эти товары идут одной позицией - артикул изменяться не будет.
</p>";

$MESS['IPOLSDEK_FAQ_PELENG_TITLE'] = "- Отслеживание состояний (статусов)";
$MESS['IPOLSDEK_FAQ_PELENG_DESCR'] = "<p>
	<strong>1. Таблица заявок</strong><br>
	Таблица заявок находится на вкладке \"Заявки\". На этой странице можно ознакомиться с состояниями всех имеющихся заявок, с возможностью их фильтрации и сортировки.<br>
	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Подробнее.</a>
		<div class='ipol_inst'>
			<img src=\"/bitrix/images/ipol.sdek/FAQ_3.png\">
		</div>
	</div>
	С помощью опций можно изменить поля неотправленной заявки, а так же стереть информацию о ней.<br>
	Принятые заявки отсюда можно отозвать и удалить, распечатать к ней квитанцию, а так же - отследить с помощью функционала сайта СДЭК.<br>
	В случае принятия заявки все эти действия можно производить и из окна оформления заявки на странице заказа.<br>
	Кнопка \"Отозвать заявку\" служит для стирания информации о заявке любой ценой, независимо от того, загрузилась он или нет.<br>
	<span style='color:red'>Внимание!</span> Под каким аккаунтом (боевым или тестовым) заявка была создана - под таким она должна удаляться! Несоблюдение этого правила приведет к тому, что заявка не будет найдена!
</p>
<p>
	<strong>2. Обновление информации о заявке</strong><br>
	По умолчанию опрос статусов заказов происходит каждые 30 минут. Если статус изменился - он поменяется в таблице Заявок, а так же сменится статус заказа на выставленный в настройках модуля (или не сменится, если он не выставлялся). Интервал опросов статусов задается сервисной опцией \"Интервал запуска агента (мин)\".
</p>
<p>
	<strong>3. Статусы заказов</strong><br>
	Заявке может быть присвоен один из следующих статусов:
	<ul>
		<li>NEW - заявка еще не отсылалась на сервер.</li>
		<li>OK - заявка принята.</li>
		<li>ERROR - заявка не принята из-за ошибок в ее полях. Необходимо исправить ошибки (комментарии есть в таблице, а так же рядом с соответствующими полями в окне \"СДЭК доставка\") и отправить ее заново.</li>
		<li>STORE - заказ на складе СДЭК.</li>
		<li>TRANZT - заказ в пути на склад города-получателя.</li>
		<li>CORIER - заказ у курьера.</li>
		<li>PVZ - заказ на пункте самовывоза.</li>
		<li>OTKAZ - клиент отказался от заказа.</li>
		<li>DELIVD - заказ доставлен.</li>
	</ul>
</p>
<p>
	<strong>4. Печать квитанции</strong><br>
	Если заявка имеет статус OK - значит, СДЭК может прислать файл с квитанцией для распечатки, аналогичный получаемому в личном кабинете. Распечатать его можно либо в окне оформления заявки на странице заказа, либо в таблице заявок. Рекомендуется сохранять pdf-файл с квитанцией на компьютере, после чего - открывать его и распечатывать.
</p>
<p>
	<strong>5. Удаление заявки</strong><br>
	Информацию о заявке можно удалить через окно оформления, или же из таблицы заявок. Если заявка была отослана и подтверждена - она сначала будет удалена из ЛК СДЭКа.<br> Форсированное удаление можно провести с помощью кнопки \"Отозвать заявку\" ниже таблицы заявок. В этом случае информация будет удалена всеми доступными способами, не взирая на ошибки (учтите, что в таком случае она может остаться в ЛК). Для удаления нужно указать ID заказа или отгрузки (если заявка отправлена из отгрузки).<br>
	<span style='color:red'>Внимание!</span> Если в настройках модуля Интернет-магазин используется опция \"Шаблон генерации номера заказа\", то фактические номера заказов и их ID будут различаться. Для отзыва заявки следует указывать именно ID!
</p>";

$MESS['IPOLSDEK_FAQ_COURIERCALLS_TITLE'] = "- Заявки на вызов курьера";
$MESS['IPOLSDEK_FAQ_COURIERCALLS_DESCR'] = "<b>Заявки на вызов курьера добавляются <a href='/bitrix/admin/ipol_sdek_courier_calls.php?lang=ru' target='_blank'>на отдельной странице</a> (Магазин -> СДЭК -> Заявки на вызов курьера).</b><br><br>
<b>Функционал заявок на вызов курьера связан с данными об Адресе отправления, Отправителе и Продавце (внимательно ознакомьтесь с одноименным разделом FAQ). При регулярном использовании заявок на вызов курьера рекомендуется настроить необходимое количество профилей Адреса отправления, чтобы выбирать готового Отправителя при оформлении заявки, а не заполнять все поля вручную. </b><br><br>
Добавление, проверка статуса, стирание заявки на вызов курьера производится на странице \"Заявки на вызов курьера\". В табличном виде представлены все созданные на сайте заявки на вызов курьера. Над таблицей находятся кнопки вызова формы \"Заявка на вызов курьера\": кнопка \"Создать заявку на консолидированный забор\" открывает форму с пресетом для консолидации, кнопка \"Создать заявку для забора одного заказа\" открывает пресет для забора единичного заказа.<br><br>
<b>Забор одного заказа</b><br><br>
Используется для забора курьером одного конкретного заказа, номер заказа СДЭК (он же номер накладной, он же трекномер) которого указывается при создании курьерской заявки. На одну дату можно создать несколько заявок на забор единичного заказа. Использование тарифов от двери и создание по ним курьерских заявок рекомендуется в случае, если количество заказов небольшое. В противном случае лучше использовать более дешевые тарифы от склада и вызывать курьера для консолидированного забора заказов.<br><br>  
<b>Данные об Адресе отправления и Отправителе будут взяты СДЭК из данных указанного заказа, а если они не были указаны при создании заявки по заказу, то из данных договора.</b><br>
Заказ <b>должен быть оформлен с тарифом от двери</b>, в противном случае регистрация заявки на вызов курьера будет отклонена с ошибкой \"Заявка не может быть оформлена по данному тарифу\".<br><br>
<b>Забор консолидированного груза</b><br><br>
Используется для забора курьером нескольких заказов с тарифами от склада. Курьеру возможно передать и заказы с тарифами от двери, но это менее выгодно: при консолидации лучше работать только с тарифами от склада, либо использовать тарифы от двери и вызов курьера для забора единичных заказов.<br><br>
При создании заявки потребуется указать Адрес отправления, данные Отправителя. Они могут не совпадать с данными, указанными при создании заявок по заказам, однако <b>вызов курьера для консолидированного забора должен происходить на адрес в том же городе-отправителе, что и был указан в заявках по заказам</b>.<br><br>
<b>На один Адрес отправления и конкретную дату ожидания курьера можно создать только одну заявку консолидированного забора.</b><br><br>
<img src=\"/bitrix/images/ipol.sdek/FAQ_COURIERCALL_1.png\">
<br><br>
<b>Номер заявки и служебная информация</b><br><br>
Наличие номера заявки - показатель того, что она успешно принята сервером СДЭК. Служебная информация имеется у заявок, которые отправлялись на сервер СДЭК (не факт, что успешно: отправка могла окончиться ошибкой, но тем не менее) и предназначена для отладки.<br><br>
Возникшие при отправке заявки ошибки, если они произошли, будут показаны в верхей части формы отправки. В зависимости от характера ошибок, можно попробовать исправить данные в заявке и отправить ее повторно.<br><br>
<b>Забор курьером</b><br><br>
Необходимо выбрать тип заявки: забор одного заказа, либо забор консолидированного груза. Разница между ними описана выше. От типа заявки зависит набор полей формы заявки, которые потребуется заполнить.<br><br>
Затем нужно указать Аккаунт СДЭК, по которому создается заявка. Внимательно отнеситесь к этому, если используется сразу несколько аккаунтов СДЭК: попытка вызова курьера для забора заказа, созданного от другого аккаунта, будет отклонена с ошибкой.<br><br>
Селектор \"Сохраненный отправитель\" позволяет выбрать готовый профиль данных Адреса отправления, Отправителя и Продавца чтобы не заполнять поля формы вручную. Выбор возможен только среди активных профилей. <b>Смена профиля повлияет на большую часть идущих далее полей формы: они будут заменены данными выбранного профиля.</b> Поэтому, если вы хотите подредактировать какие-то данные, лучше вначале выбрать Сохраненного отправителя и затем изменить желаемое.<br><br>
Флаг \"Необходимость прозвона\" означает, что курьер должен позвонить заранее перед приездом. При необходимости можно оставить какие-либо комментарии курьеру, например, уточнения о схеме проезда, пропускном режиме и т.п.<br><br>  
<b>Дата и время ожидания</b><br><br>
Здесь указывается дата и желаемый временной интервал приезда курьера. Он должен быть не менее 3 часов. <b>Вызов курьера после 15:00 по времени отправителя может быть выполнен на следующий день.</b><br><br>
<b>Отправитель</b><br><br>
Только для заявок на консолидированный забор груза. Необходимо указать название компании, ФИО контактного лица, номер телефона.<br><br>
<b>Адрес отправления</b><br><br>
Только для заявок на консолидированный забор груза. Необходимо указать город и адрес, на который приедет курьер. <br><br>
<b>Упаковка с грузом</b><br><br>
Только для заявок на консолидированный забор груза. Необходимо указать наименование и примерные данные о суммарном весе и размере отправлений, которые должен забрать курьер. Исходя из этих данных СДЭК определяет: присылать ли пешего курьера или необходим автомобиль и т.п.<br><br>
<b>Проверка статуса заявки</b><br><br>
Для принудительной проверки статуса конкретной заявки в контекстном меню таблицы заявок выберите действие \"Проверить статус\". Также можно воспользоваться кнопкой \"Проверить статусы\" для обновления статусов имеющихся заявок, которые успешно отправлены в СДЭК и находятся не в конечных статусах.<br><br>  
<b>Пояснения по работе с заявками</b><br><br>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Где посмотреть информацию о заявках?</a>
    <div class='ipol_inst'>
        Информация о заявках, зарегистрированных через модуль, отображается в таблице на странице Магазин -> СДЭК -> Заявки на вызов курьера. Поля таблицы можно настроить под свои предпочтения, режим настройки включается иконкой шестеренки в левом верхнем углу таблицы.    
    </div>
</div>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; На какой адрес приедет курьер?</a>
    <div class='ipol_inst'>
        Курьер приедет на адрес, который был указан в заявке на консолидированный забор, либо, если это заявка на забор одного заказа, на адрес отправления, переданный при создании этого заказа. Если он не был заполнен, СДЭК использует данные договора.<br><br>
        Если есть какие-то сомнения или уточнения, рекомендуется незамедлительно связаться с менеджером СДЭК для согласования деталей вызова курьера.       
    </div>
</div>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Ко скольким приедет курьер, почему он не звонит?</a>
    <div class='ipol_inst'>
        <b>По любым вопросам, касающимся работы курьера, следует обращаться напрямую к менеджеру СДЭК по телефону.<br><br> Поддержка модуля ничем не может вам помочь: мы не имеем доступа к этой информации, работой курьеров заведует сам СДЭК.</b>      
    </div>
</div>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Где в Личном кабинете СДЭК информация о заявках на вызов курьера?</a>
    <div class='ipol_inst'>
        Насколько нам известно, на данный момент в ЛК СДЭК такого функционала нет, посмотреть эту информацию в ЛК не удастся. Если информации из модуля недостаточно, свяжитесь с менеджером СДЭК.       
    </div>
</div>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Необходимо внести изменения в заявку</a>
    <div class='ipol_inst'>
        Если заявка уже успешно отправлена и ей назначен номер, незамедлительно свяжитесь по телефону с менеджером СДЭК, сообщите номер заявки, согласуйте изменения. Если заявка еще не была успешно отправлена (допустим, вы некорректно указали данные и заявка находится в статусе ERROR), попробуйте изменить данные через форму и отправить ее повторно.        
    </div>
</div>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Необходимо отменить заявку</a>
    <div class='ipol_inst'>
        Если заявка уже успешно отправлена и ей назначен номер, незамедлительно свяжитесь по телефону с менеджером СДЭК, сообщите номер заявки, согласуйте отмену вызова курьера.         
    </div>
</div>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Что будет, если не создавать профилей Адреса отправления, Отправителя и Продавца?</a>
    <div class='ipol_inst'>
        При оформлении заявок на вызов курьера все данные формы заявки потребуется заполнять вручную. Настройка профилей Отправителя экономит время, если заявки на вызов курьера оформляются регулярно.          
    </div>
</div>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Что будет, если в заказе один адрес отправления, а заявку создать на другой?</a>
    <div class='ipol_inst'>
        Исходя из имеющейся у нас информации от СДЭК, в первую очередь важен город-отправитель, а не адрес внутри города. Вы должны вызывать курьера в том же городе-отправителе, что и были оформлены заказы, которые вы планируете передать курьеру. <br><br>
        Если при оформлении заказов, допустим, с тарифом от склада, был указан город-отправитель Москва, а консолидированный забор заказов вы вызвали в Солнечногорск Московской области, то курьер вправе отказаться от забора таких заказов, либо потребовать их переоформления с пересчетом стоимости доставки от Солнечногорска.<br><br>
        При возникновении подобной нестыковки следует незамедлительно связаться с менеджером СДЭК.                 
    </div>
</div>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Что будет, если стереть запись о заявке?</a>
    <div class='ipol_inst'>
        Эта возможность доступна лишь для заявок в определенном статусе: NEW, ERROR, REMOVED. Стирание подразумевает удаление информации о ставшей ненужной заявке из таблицы модуля. <br><br>
        <b>Это не синоним отмены заявки! Для отмены заявки на вызов курьера следует обратиться к менеджеру СДЭК.</b>
    </div>
</div>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; При отправке заявок возникают ошибки, не понятно что делать</a>
    <div class='ipol_inst'>
        Внимательно прочитайте текст ошибки. Возможно, вы некорректно заполнили какие-либо данные по заявке. Например, не в том формате указали телефон. Или указываете один аккаунт СДЭК, а номер заказа для единичного забора, отправленный с другого аккаунта. Или вообще несуществующий номер заказа СДЭК.<br><br>
        Попробуйте подождать некоторое время: иногда бывают технические проблемы на стороне сервера СДЭК, в этом случае АПИ может работать некорректно или быть временно отключено, тогда отправить заявку не удастся. Когда на стороне СДЭК проблему решат, отправка заработает \"сама собой\".<br><br>
        Если ничего не помогает и разобраться не удается, обратитесь к поддержке модуля Интеграции со СДЭК, указав ваш номер договора, доступы к сайту и описание проблемы.
    </div>
</div>
";

	// FAQ: Дополнительные возможности
$MESS['IPOLSDEK_FAQ_HDR_WORK'] = "Дополнительные возможности";

$MESS['IPOLSDEK_FAQ_PRINTFULL_TITLE'] = "- Печать квитанций и штрихкодов";
$MESS['IPOLSDEK_FAQ_PRINTFULL_DESCR'] = "<p>Обращаем ваше внимание, что квитанции и штрихкоды формируются на стороне СДЭКа, основываясь на данных, переданных в заказе. Модуль и его техподдержка никак не может повлиять на ее формат и расположение полей.</p>
<div class='ipol_subFaq'>
	<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Структура полей квитанции.</a>
	<div class='ipol_inst'>
		<strong>Отправитель</strong><br>
		<ul>
			<li>Компания - данные поля подгружаются из настроек личного кабинета СДЭК</li>
			<li>Продавец - данные, указанные в настройке \"Истинный продавец\"</li>
		</ul>
		<strong>Получатель</strong><br>
		Данные, переданные в соответствующих полях контактной информации о покупателе и адресе доставки / ПВЗ<br>
		<strong>Информация об отправлении</strong><br>
		Сведения, описывающие заказ и посылку (места, вес, а так же тариф доставки).<br>
		<strong>Перечень товаров</strong><br>
		<ul>
			<li>Наименование товара - название товара, которое находится в корзине заказа.</li>
			<li>Объявленная стоимость - стоимость товара, указанная в корзине заказа. По ней же СДЭК вычисляет страховку.</li>
			<li>К оплате за ед - стоимость единицы товара. Равняется объявленной стоимости, если заказ не был оплачен; 0 - если оплачен; в промежутке от объявленной стоимости до 0, если заказ оплачен частично (оставшаяся сумма распределяется по всем товарам).</li>
			<li>Стоимость доставки - записывается отдельным полем, вне таблицы товаров.</li>
		</ul>
		Имейте в виду, что все поля перечня товаров можно редактировать с помощью события onGoodsToRequest.
	</div>
</div>

<p>Печать квитанций и штрихкодов возможна только для тех заказов, что были успешно отправлены в СДЭК. Есть три способа печати: <ul><li>Через форму отправления заявки</li><li>Через таблицу заявок</li><li>Через массовую печать</li></ul></p>
<p><strong>Форма отправления заявки</strong><br>После отправления заявки в СДЭК и перезагрузки страницы, откройте форму отправления заявки снова. Чтобы напечатать квитанцию - нажмите на кнопку \"Печать квитанции\" - квитанция откроется в новом окне. Для штрихкода - нажмите на кнопку \"Штрихкод\".</p>
<p><strong>Таблица заявок</strong><br>Перейдите во вкладку \"Заявки\", найдите в таблице нужный заказ. Щелкните на \"сэндвич\" (иконка из трех линий) - в выпадающем списке выберите \"Печать квитанции\" или \"Штрихкод\". Документ откроется в новом окне.</p>
<p><strong>Массовая печать</strong><br>На странице заказов выберете флажками те, которые были успешно отправлены в СДЭК. В действиях выберите \"Печать СДЭК\" для квитанции или \"Штрихкод СДЭК\" - для штрихкода. Документы откроются в новом окне. Здесь же возможно распечатать акт по всем заказам. Его печать необязательна и оформляется по необходимости. Перед использованием акта необходимо его заполнить, он находится по адресу /bitrix/js/ipol.sdek/printActs.php, подробные инструкции даны в самом файле. Убедитесь, что кодировка сохранена. Более подробную информацию можно найти в разделе \"Печать\" настроек модуля.</p>
";

$MESS['IPOLSDEK_FAQ_ACCOUNTS_TITLE'] = "- Дополнительные аккаунты";
$MESS['IPOLSDEK_FAQ_ACCOUNTS_DESCR'] = "<p>На данный момент дополнительные аккаунты предназначены исключительно для работы с разными странами. Поддержка данной функции для обеспечения возможности одновременной работы с несколькими личными кабинетами планируется в необозримом будущем.</p><p>Чтобы открыть окно управления аккаунтами, в настройках модуля необходимо кликнуть по кнопке \"Аккаунты\".</p><p><img src='\bitrix\images\ipol.sdek\FAQ_7.png'></p><p>В данном окне можно ввести новые авторизационные данные и отключить неактуальные аккаунты. В первую очередь это необходимо, если у вас есть несколько личных кабинетов, для последующей привязки аккаунтов к различным странам.</p>";

$MESS['IPOLSDEK_FAQ_RBK_TITLE'] = "- Доставка в Республику Беларусь и Казахстан";
$MESS['IPOLSDEK_FAQ_RBK_DESCR'] = "<p>Перед подключением Белоруссии и Казахстана уточните у вашего менеджера, возможно ли с вашего аккаунта отправлять грузы в эти страны. Имейте в виду, что техподдержка модуля не отвечает за работу самого СДЭКа - вопросы насчет аккаунтов и личных кабинетов следует задавать менеджеру СДЭК.</p><p>Чтобы обеспечить расчет доставки и доступность городов в админке, необходимо зайти во вкладку \"Города\" и отметить те страны, с которыми будет проводиться Интеграция, руководствуясь указаниями в пункте \"Порядок добавления страны\". После синхронизации в городах отмеченных стран будет доступна доставка через СДЭК. Учтите, что в инфовиджете есть возможность отключения отображения городов определенных стран: ознакомьтесь с параметрами компонента.</p>
<div class='ipol_subFaq'>
	<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Работа с одним личным кабинетом.</a>
	<div class='ipol_inst'>
		<p>Данный способ работы с Казахстаном и Республикой Беларусь подразумевает наличие только одного личного кабинета, который привязан к российскому рублю. Это означает невозможность приема наличной оплаты курьером, поэтому все заказы должны быть предоплачены.</p>
		<p>В настройках городов для разных стран должен быть установлен один и тот же личный кабинет, который ведет расчет доставки в рублях. На вкладке \"Валюта\" всегда должна стоять валюта сайта по-умолчанию (сейчас считается, что это - всегда рубли), иначе возможна путаница с конвертацией валют.</p>
		<p><img src='\bitrix\images\ipol.sdek\FAQ_8.png'></p>
	</div>
</div>

<div class='ipol_subFaq'>
	<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Работа с несколькими личными кабинетами с разными валютами.</a>
	<div class='ipol_inst'>
		<p>Данный способ работы с Казахстаном и / или Республикой Беларусь подразумевает наличие нескольких личных кабинетов, предназначенных для работы с этими странами. Имейте в виду: кабинет, настроенный на работу с другой страной ведет расчеты в валюте этой страны: Казахстан - тенге, Республика Беларусь - белорусские рубли (на данный момент недоступны). Это означает возможным прием налички от покупателей, но открывает проблемы установки курсов валют. Не забудьте убрать флаг с опции \"Не давать оформить заказ с наложенным платежом в других странах\"</p>
		<p><span class='IPOLSDEK_warning'>Внимание!</span> Как таковых готовых решений для магазинов, работающих с разными валютами, нет. Поэтому модуль настроен по следующему принципу: считается, что цены товаров и стоимости доставок всегда считаются в рублях. На своей стороне администраторы магазинов выводят клиентам стоимости доставок и товаров, конвертируя их по курсу, установленному в модуле Валют. При выгрузке товаров в СДЭК РУБЛЕВЫЕ цены товаров и стоимость доставки конвертируются в валюту страны (указанную в соответствующей настройке страны во вкладке \"Города\"). Если порядок работы с разными странами на вашем сайте отличается от описанного - сообщите нам в техподдержку: если решение универсальное - возможно, его поддержка будет добавлена в модуль.</p>
		<p>В настройках городов для разных стран должен быть установлен тот личный кабинет, с которым эта страна должна работать. Валюта должна быть установлена та, что выводится для цен в данной стране - по курсу этой валюты будет произведен перерасчет стоимостей товаров и доставки. Нет необходимости устанавливать каждой стране отдельный личный кабинет, однако если ей установлен аккаунт, отличный от основного - ему необходимо задать валюту для расчетов. Например, если имеются два аккаунта (рублевый и тенге) - то для России и Белоруссии устанавливается рублевый, валюта по-умолчанию, в то время как для Казахстана - в тенге, а валюта выставляется та, что отвечает за курс тенге.</p>
		<p><img src='\bitrix\images\ipol.sdek\FAQ_9.png'></p>
		<p>Валюты устанавливаются в соответствующем <a href='/bitrix/admin/currencies.php' target='_blank'>стандартном модуле Битрикса</a>. По их курсу производится перерасчет стоимости заказа. Информацию по работе с валютами можно получить из <a href='http://dev.1c-bitrix.ru/learning/course/?COURSE_ID=42&LESSON_ID=3162' target='_blank'>документации Битрикс</a>.</p>
		<p>При отправке заявки на доставку в СДЭК в окне оформления заявки можно ознакомиться с ценами, которые будут отправлены в личный кабинет, а так же уточнить аккаунт, с которого будет произведена отправка.<br><img src='\bitrix\images\ipol.sdek\FAQ_10.png'></p>
		<p>Личный кабинет СДЭКа работает только с одним типом валют. Это создает определенные трудности для отправки данных в личный кабинет СДЭКа и выводе стоимостей доставки для клиента. Ввиду этого была использована следующая логика работы, что все цены используются в рублях и пересчитываются для разных стран через модуль валют. Данный сценарий работы тестовый, но опробован на нескольких сайтах, работающих через разные личные кабинеты. Мы ждем сообщений в техподдержку с предложениями иных подходов к работе с разными странами, но включены в модуль они будут только в случае их универсальности.</p>
		<p>Если вы пользуетесь курсами валют - обратите внимание на настройку \"Конвертировать валюту курсом на момент оформления заказа\".</p>
	</div>
</div>

";

$MESS['IPOLSDEK_FAQ_PC_TITLE'] = "- Управление местами";
$MESS['IPOLSDEK_FAQ_PC_DESCR'] = "<p>Модуль позволяет задавать количество мест в заказе по аналогии с Личным кабинетом СДЭКа. Главное отличие - это необходимость указывать, к каком месте будет приписан каждый товар.<p>
<p><span style='color:red'>Внимание!</span> При задании мест и изменении их габаритов стоимость доставки изменится.</p>
<p><img src='/bitrix/images/ipol.sdek/FAQ_packControl.png'></p>
<p><ol>
<li><strong>Габариты места</strong> по умолчанию всегда равны настройкам \"Размеры по умолчанию\", независимо от режима работы (товар / заказ). Чтобы изменить размеры вручную - нужно нажать на карандаш (Изменить), внести изменения и сохранить их. Чтобы рассчитать габариты средствами модуля (основываясь на габаритах товаров) - нажмите иконку \"Рассчитать размеры\".</li>
<li><strong>Вес места</strong> автоматически рассчитывается как сумма весов всех товаров (которые вычисляются модулем, основываясь на заданных весах товаров или настройках модуля). Карандаш (Изменить) задаст фиксированный вес, отключив автоматическое отслеживание. Включить его обратно можно через иконку \"Автоматически рассчитывать вес через товары\".</li>
<li><strong>Товары</strong> берутся из корзины покупателя. Их габариты выставляются либо из торгового каталога, либо из настроек (программных или модуля). Перемещать их между местами можно либо перетаскиванием мышью, либо отмечая нужные товары флажками.</li>
<li><strong>Разбиение товаров</strong> по местам возможно через щелчок по количеству товаров и выбору места, в которое нужно переместить указанное количество товара. Доступно, если задано больше одного места.</li>
<li><strong>Удаление места</strong> при необходимости заданное место можно удалить - тогда все товары из него попадут в первое место.</li>
<li><strong>Добавить место</strong> - добавляет новое место с габаритами по умолчанию.</li>
<li><strong>Рассчитать доставку</strong> - вычисляет стоимость доставки для указанных мест, с учетом тарифов и прочих пунктов, указанных в окне оформления доставки.</li>
<li><strong>Автораспределение</strong> - автоматически разобьет товары по указанным местам. <span style='color:red'>Внимание!</span> Лучше воздержаться от этой функции и задавать товары четко следуя их реальному распределению по местам. Распределение будет учитывать только количество товаров - не вес, не габариты. Модуль не включает в себя функционал интеллектуального распределения товаров - и никогда не будет.</li>
<li><strong>Скрыть товары</strong> - пригодится при большом количестве товаров, когда необходимо задать габариты местам. Чтобы обратно отобразить товары - выберите \"Показать товары\".</li>
<li><strong>Применить</strong> - сохранит изменения и включит режим распределения товаров по местам при оформлении заявки. <strong>Сброс</strong> - отменит изменения и выключит режим распределения товаров по местам.</li>
</ol></p>
<p>
	Учтите, что при загрузке заказа в ЛК СДЭКа идет проверка на соответствие весов места весам включенных товаров. Если будет изменен вес места - веса товаров будут автоматически подогнаны таким образом, чтобы эта проверка была пройдена. В случае возникновения ошибок с несоответствием весов мест и товаров необходимо заполнить данные в соответствии с реальными габаритами. (В окне \"Управление местами\" и Торговом каталоге).
</p>
<p>
	Если задано распределение по местам, но все товары находятся в одном месте - модуль поведет себя так, будто были заданы особые габариты для всего заказа, а не распределение по местам.
</p>
<p>
	Если некоторые товары отсутствуют - необходимо проверить их \"наличие\". Модуль запрашивает товары из корзины заказа - и подчиняется тем правилам, что диктует сам Битрикс. Сам факт исчезновения товара из корзины означает \"аварию\" на стороне бизнес-процесса или cms. Подробнее: Частые проблемы -> Проблемы в административной части -> Пропадают товары в распределении по грузоместам.
</p>
";

$MESS['IPOLSDEK_FAQ_SHIPMENTS_TITLE'] = "- Работа с отгрузками";
$MESS['IPOLSDEK_FAQ_SHIPMENTS_DESCR'] = "<p>В модуле имеется возможность отправлять в СДЭК не только заказы, но и <a href='/bitrix/admin/sale_order_shipment.php?lang=ru' target='_blank'>отгрузки</a>. Это пригодится в случае, если заказ необходимо только частично доставить с помощью СДЭКа, или же отправить части заказа из разных городов-отправителей. Имейте в виду, что включение работы с отгрузками не отключит работу по заказам: оба варианта отправки будут доступны.</p>
<p><strong>Разбивание заказа на отгрузки</strong><br>Изначально в заказе будет одна отгрузка, в которой будут находиться все товары. Если необходимо разбить корзину заказа на несколько отгрузок, необходимо:<ul><li>Перейти в отгрузку, нажав в заказе \"редактировать\" на поле с отгрузкой.</li><li>Удалить из состава отгрузки необходимые товары и нажать \"применить\", после чего - вернуться в заказ.</li><li>В списке отгрузок добавить отгрузку</li><li>Нажать \"Добавить товар\", чтобы внести в состав отгрузки оставшиеся нераспределенными товары.</li></ul>Имейте в виду, что для обучения работы с отгрузками есть документация и бесплатные курсы Битрикса - это не задача техподдержки модуля.</p>
<p><strong>Подготовка к работе с отгрузками</strong><br>Первым делом необходимо в настройках модуля поставить флаг \"Работа с отгрузками\", после чего функционал будет включен. Дальнейших манипуляций не требуется, если только вы не пользуетесь печатью актов.<br>Если вы пользуетесь функционалом печати актов (на странице заказов выделяете их флагами и нажимаете \"Печать СДЭК\") - вам необходимо внести правки в файл с актом. Он не обновится автоматически, так как в него с вашей стороны были внесены правки (номер договора, реквизиты, итп). Необходимо внести в файл (/bitrix/js/ipol.sdek/printActs.php) следующие правки. Участок с кодом:
<div style='color:#AC12B1'><pre>
\$arOrders = explode(\":\", \$ORDER_ID);
unset(\$ORDER_ID);
\$ttlPay = 0;
foreach(\$arOrders as \$key => \$id){
	\$req=sdekdriver::select(array(),array('ORDER_ID'=>\$id))->Fetch();
	if(!\$req){
		unset(\$arOrders[\$key]);
		continue;
	}
	\$params = unserialize(\$req['PARAMS'], ['allowed_classes' => false]);
	\$order  = CSaleOrder::GetById(\$id);
	\$arOrders[\$key] = array(
		'ID'     => \$id,
		'SDEKID' => \$req['SDEK_ID'],
		'WEIGHT' => (\$params['GABS']['W'])?\$params['GABS']['W']:(COption::GetOptionString(\$module_id,'weightD',1000))/1000,
		'PRICE'  => (float)(\$order['PRICE'] - \$order['PRICE_DELIVERY']),
		'TOPAY'  => (\$params['isBeznal']=='Y')?0:(float)\$order['PRICE']
	);
	\$ttlPay+=\$arOrders[\$key]['PRICE'];
	}
</pre></div>
заменить на лаконичное:
<div style='color:#AC12B1'><pre>extract(sdekOption::formActArray());</pre></div>
</p>
<p><strong>Особенности работы с отгрузками</strong><br>Работа с отгрузками в целом очень схожа на работу с заказами, за несколькими отличительными особенностями:<ul><li>Если заказ отправлен отгрузкой - его уже нельзя будет отправить в СДЭК (что логично, так как теперь его следует рассматривать как отгрузку).</li><li>Если заказ отправлен формой на странице заказа - отдельные его отгрузки выгрузить в СДЭК не получится (да и зачем, если весь заказ уже выгружен).</li><li>Чтобы выгрузить заявку через отгрузку - нужно находиться на странице этой отгрузки и нажать знакомую кнопку \"СДЭК доставка\".</li><li>Обратная связь будет обеспечиваться не через статусы заказа, а через статусы отгрузок (не забудьте заполнить соответствующую опцию, которая появится после включения отправления отгрузками).</li><li>Опция \"отмечать доставленный заказ оплаченным\" в случае Отгрузок работать не будет (ибо как?..).</li><li>Номером заказа, с которым отгрузка уйдет в СДЭК, будет считаться Битриксовский номер отгрузки (как правило, это \"номер заказа\"/\"номер отгрузки\")</li><li>Особенности печати актов, которые описаны выше.</li><li>Если необходимо убить зависшую заявку (кнопка \"Отозвать заявку\" в таблице заявок) - придется при вводе номера заказа вводить и тип заявки (заказ или отгрузки).</li></ul></p>";

$MESS['IPOLSDEK_FAQ_COMPONENT_TITLE'] = "- Компонент \"Пункты Самовывоза СДЭК\"";
$MESS['IPOLSDEK_FAQ_COMPONENT_DESCR'] = "Компонент используются в первую очередь на странице оформления заказа, так же его можно использовать на странице доставки, чтобы вывести информацию о самовывозах, стоимости и сроках доставки для всех профилей.<br><strong><span style='color:red'>Важно!</span> На странице оформления заказа компонент подключать не нужно!</strong> Он подключится автоматически.<br>Компонент предназначен для вывода карты с отображением на ней пунктов самовывоза и информации о них, а так же проведения различных манипуляций вроде выбора пункта для доставки. Функционал выбора пункта самовывоза на странице оформления заказа реализован с помощью этого компонента. Его так же можно использовать, чтобы отображать информацию о пунктах самовывоза в разделе \"Доставка\".<br>
Вставить компонент на страницу можно с помощью визуального редактора. Расположен он по пути \"Магазин\" -> \"Компоненты IPOL\". Если после установки модуля компонент в визуальном редакторе не появился - попробуйте <a href='/bitrix/admin/cache.php' target='_blank'>очистить файлы кэша</a> Битрикса.<br>
<img src=\"/bitrix/images/ipol.sdek/componentAdd.png\"><br>
Компонент так же можно вставить php-кодом:<br>
<div style='color:#AC12B1'>
&lt;?\$GLOBALS['APPLICATION']->IncludeComponent(\"ipol:ipol.sdekPickup\",\".default\",array(),false);?&gt;
</div>
Компонент имеет следующие настройки:<br>
<ul>
	<li>Подключенный профиль на карте - можно настроить виджет, чтобы подключались либо пункты самовывоза, либо почтоматы (уже неактуальны), либо все сразу.</li>
	<li>Не подключать Яндекс-карты - если на странице с компонентом код Яндекс-карт подключается где-либо еще (в особенности - если подключается версия не 2.1), нужно поднять этот флаг, чтобы скрипты не конфликтовали.</li>
	<li>Рассчитывать доставку при подключении - компонент сразу рассчитает доставку при подключении(по умолчанию это делается аякс-запросом для более быстрой загрузки страницы). Это позволит ставить сложные условия на странице доставок.</li>
	<li>Рассчитывать доставку для корзины - при расчете доставки будут использоваться не габариты по умолчанию, а товары, находящиеся в корзине пользователя (впрочем, если товаров нет - все равно будут использоваться умолчания).</li>
	<li>Отключить расчет для профилей - если необходимо отключить какой-либо профиль, его нужно отметить в этой настройке. Если не выбран \"Самовывоз\" - карта вообще не будет грузиться.</li>
	<li>Тип плательщика, от лица которого считать доставку - если на службу доставки наложено ограничение на тип плательщика - укажите его в настройках, чтобы не было проблем с расчетом.</li>
	<li>Тип платежной системы, с которой будет считатся доставка - если на службу доставки наложено ограничение на платежную систему - укажите ее в настройках, чтобы не было проблем с расчетом.</li>
	<li>Подключенные страны - если необходимо отключить страны в инфовиджете - выберете необходимые в этой настройке. Учтите, что в нее грузятся только те страны, что были отмечены в настройке \"Обрабатываемые страны\" модуля.</li>
	<li>Подключаемые города - при необходимости отключить какой-либо город, отмечаются те, что будут отображаться на карте. Если не выбрано ни одного города - будут отображаться все (иначе подключение компонента лишается какого-либо смысла).</li>
</ul>
Вместе с компонентом поставляются два шаблона:
<ul>
	<li>.default - шаблон, предназначенный для отображения информации о пунктах самовывоза.</li>
	<li>order - шаблон, используемый для выбора пункта самовывоза при оформлении заказа.</li>
</ul>
Крайне не рекомендуется модифицировать эти шаблоны, в особенности - их скрипт. При необходимости вынесете их в отдельное пространство имен, иначе корректная работа модуля (в особенности - при оформлении заказа) не гарантируется.
";

$MESS['IPOLSDEK_FAQ_AUTOMATIZATION_TITLE'] = "- Автоматизация";
$MESS['IPOLSDEK_FAQ_AUTOMATIZATION_DESCR'] = "<p>Для обеспечения автоматизации процесса выгрузки заявок в модуле присутствует функционал Автоотгрузок. Чтобы его включить, необходимо нажать на кнопку \"Включить автоотгрузки\". <strong>Функционал предоставляется \"Как есть\".</strong></p>
<p><span class='IPOLSDEK_warning'>Важно!</span> Проверьте настройку \"Платежные системы, при которых курьер не берет деньги с покупателя\": если покупатель выберет указанную в ней платежную систему - заказ будет выгружен как оплаченный!</p>
<p><span class='IPOLSDEK_warning'>Важно!</span> Одно из требований СДЭКа - указание улицы, дома и квартиры в отдельных полях. Поэтому если у вас адрес заполняется одной строкой - готовьтесь к тому, что при отправке заявок могут быть ошибки!</p>
<p>При включении функционала, заказы, которые оформляются через компонент \"Одношаговое оформление заказа\" или переведены в определенный статус будут автоматически отправлены в СДЭК. Тариф, габариты и прочие параметры будут переданы те, что были использованы при расчете доставки на странице оформления заказа. Успешность подготовки отправки данных учитывается в свойстве заказа \"Отправлен автоматизацией СДЭК\" (будет создано автоматически). Контроль за отправкой заявок проводится на странице настроек модуля, вкладка \"Автоматизация\". Флаг фильтра \"Только неудачные\" выведет те заявки, что вообще не были отправлены в СДЭК по причине отсутствия какого-либо из полей заявки. Те заявки, что были отклонены СДЭКом, можно увидеть на вкладке \"Заявки\" в штатном режиме.</p>
<p>Автоматизация учитывает город-отправитель и аккаунт, заданный в используемой службе доставки.</p>
<p>Учтите, что в автоматизации игнорируется такой функционал как: <ul><li>Работа с отгрузками</li><li>Выбор Отправителя</li></ul><p><span class='IPOLSDEK_warning'>Важно!</span> Автоматизация <strong>не создает</strong> заявок на вызов курьера.</p>
<div class='ipol_subFaq'>
	<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Подробнее об адресе</a>
	<div class='ipol_inst'>
		Если адрес доставки на сайте задается через одно поле \"Адрес\" - возможны ошибка отправки под названием \"Невозможно распарсить адрес доставки.\". Причина ее в том, что крайне проблематично определить, в каком именно формате клиент вобьет свой адрес - да это и не входит в заявленный функционал модуля. Если такая ошибка возникает часто - возможны следующие варианты решения проблемы:<ul><li>Перейти на раздельное указание адреса, создав три свойства (улица, дом, квартира).</li><li>Воспользоваться решением <a href='http://marketplace.1c-bitrix.ru/solutions/ipol.kladr/' target='_blank'>Автозаполнение доставки по КЛАДР</a>, которое записывает адрес в фиксированном формате.</li><li>Использовать сторонние сервисы валидации адреса, подписавшись на событие onParseAddress.</li></ul></p>
			<div style='color:#AC12B1'><pre>
<span style='color:#008000'>// пример использования события</span>
AddEventHandler('ipol.sdek', 'onParseAddress', 'onParseAddressSDEK'); <span style='color:#008000'>//подписываемся на событие</span>

function onParseAddressSDEK(&\$fields){ 
<span style='color:#008000'>//функция, где \$fields - поля, которые будут переданы в обработчик отправки заявки. Нас интересуют ключи address (адрес), street, house и flat</span>
	\$address = \$fields['address']; <span style='color:#008000'>//получаем адрес клиента</span>
	
<div style='color:#008000'>/*
	Разбиение адреса на улицу, дом, квартиру
*/</div>

	\$fields['street'] = <улица>;
	\$fields['house']  = <дом>;
	\$fields['flat']   = <квартира>;
}
			</pre></div>
	</div>
</div>
<div class='ipol_subFaq'>
	<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Подробнее о номере телефона</a>
	<div class='ipol_inst'>
		Номер телефона должен соответствовать определенному стандарту (подробнее - Оформление и отправка заявки -> Заполнение полей -> Подробнее о полях). В случае использования автоматизации необходимо поставить валидатор (с модулем не поставляется, в рамках бесплатной ТП не решается) на поле, отвечающее за номер телефона, чтобы пользователи вводили его в строго заданном формате - иначе отправка заявок будет невозможной из-за множества ошибок. Как вариант - можно использовать плагин jquery masked input, однако убедитесь, что он не будет переопределять библиотеку и не затрет расширение jscrollpane.
	</div>
</div>
<p>В связи с тем, что автоматизация сильно зависит от свойств, определяющих адрес доставки - не гарантируется адекватное заполнение этих полей. В случае обращения в техподдержку по функционалу Автоматизации, будет рекомендовано перейти на ручную отправку.</p>";

$MESS['IPOLSDEK_FAQ_MULTISITE_TITLE'] = "- Много сайтов на одном аккаунте СДЭК";
$MESS['IPOLSDEK_FAQ_MULTISITE_DESCR'] = "Для обеспечения работы нескольких админок от одного аккаунта СДЭК необходимо задать каждому сайту свой шаблон генерации номеров заказов, чтобы они не пересекались. Шаблоны задаются в <a href='/bitrix/admin/settings.php?mid=sale'>настройках Интернет-магазина</a> в опции \"Шаблон генерации номера заказа\". Самый простой вариант - задать уникальный префикс для каждого сайта.<br>Если на сайтах будет задан одинаковый шаблон - некоторые заказы не будут отсылаться в ЛК СДЭК, так как их номера будут совпадать с теми, что уже были выгружены.<br><br>";

$MESS['IPOLSDEK_FAQ_DELIVERYPRICE_TITLE'] = "- Модификация результатов расчетов (для программистов)";
$MESS['IPOLSDEK_FAQ_DELIVERYPRICE_DESCR'] = "
<p>
	Данные модификации следует проводить только усилиями опытного программиста. При его отсутствии их могут провести разработчики в рамках платной техподдержки.
</p>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Отключение доставок для определенных городов [onCompabilityBefore].</a>
		<div class='ipol_inst'>
		Чтобы отключить курьерскую доставку или самовывоз для определенных городов, можно подписаться на событие <span style='color:#AC12B1'>onCompabilityBefore</span> модуля, возникающее перед тем, как будут определены доступные профили доставки.<br>
		Для этого в /bitrix/php_interface/init.php нужно добавить следующий год:<br>
		<div style='color:#AC12B1'><pre>
AddEventHandler('ipol.sdek', 'onCompabilityBefore', 'onCompabilityBeforeSDEK'); //подписываемся на событие

function onCompabilityBeforeSDEK(\$order, \$conf, \$keys) { 
<span style='color:#008000'>//функция, где \$order - данные о заказе, \$conf - настройки доставки, \$keys - коды профилей доставки, которые будут выбраны</span>
	\$profile = 'pickup'; <span style='color:#008000'>//профиль, который оставим: pickup - самовывоз, courier - курьер</span>
	if(\$order['LOCATION_TO'] == &lt;id местоположение нужного города&gt){
		if(in_array(\$profile,\$keys)) <span style='color:#008000'>// есть ли вообще профиль для этого города?</span>
			return array(\$profile);  <span style='color:#008000'>//возвращаем только его</span>
		else
			return false; <span style='color:#008000'>//полностью исключаем город</span>
	}
	return true; <span style='color:#008000'>//оставляем без изменений</span>
}
		</pre></div>
	В рассмотренном примере для определенного города был оставлен только самовывоз.
	</div>
</div>

<div class='ipol_subFaq'>
	<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Стоимость и сроки доставки [onCalculate].</a>
	<div class='ipol_inst'>
		Если необходимо задать более сложные условия для стоимости доставки или изменить ее сроки, можно воспользоваться событием <span style='color:#AC12B1'>onCalculate</span>, которое возникает после расчета. Для этого нужно в файле /bitrix/php_interface/init.php (если этого файла нет - его надо создать) добавить следующий код:<br><br>
		<div style='color:#AC12B1'><pre>
AddEventHandler('ipol.sdek', 'onCalculate', 'changeSDEKTerms');

function changeSDEKTerms(&\$arResult, \$profile, \$arConfig, \$arOrder){
<div style='color:#008000'>
	/*
		здесь задаются условия в зависимости от значений параметров:
		\$profile - профиль
		\$arConfig - настройки СД
		\$arOrder - параметры заказа
			LOCATION_TO   - id местоположения доставки
			LOCATION_FROM - id местоположения отправления
			PRICE         - стоимость заказа
			WEIGHT        - вес заказа в граммах
		\$arResult - массив вида
			RESULT  - OK, если расчет верен, ERROR - если ошибка
			VALUE   - стоимость доставки в рублях
			TRANSIT - срок доставки в днях
			TARIF   - рассчитанный тариф, только для информации
		
		!Не забудьте, что \$arResult - указатель на массив
	*/
</div>
<div style='color:#008000'> // Поставим бесплатную стоимость доставки на ПВЗ при стоимости заказа от 3000 рублей:</div>

    if(
        \$arResult['RESULT'] === 'OK' <span style='color:#008000'>// если стоимость доставки успешно рассчиталась</span>
        &&
        \$profile === 'pickup' <span style='color:#008000'>// только самовывоз</span>
        &&
        \$arOrder['PRICE'] >= 3000 <span style='color:#008000'>// стоимость заказа больше/равна 3000</span>
    ){
        \$arResult['VALUE'] = 0;
    }
}
		</pre></div>
	</div>
</div>

<div class='ipol_subFaq'>
	<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Изменение габаритов товаров [onBeforeDimensionsCount].</a>
	<div class='ipol_inst'>
		Если габариты товаров берутся не из торгового каталога (или есть необходимость модифицировать расчет для определенных товаров) необходимо в /bitrix/php_interface/init.php нужно добавить следующий год:
		<div style='color:#AC12B1'><pre>
AddEventHandler('ipol.sdek', 'onBeforeDimensionsCount', 'handleGoods');

function handleGoods(&\$arOrderGoods){

	if(!cmodule::includeModule('iblock')) return;
	<div style='color:#008000'>
	/*
		условия задаются модификацией массива arOrderGods, который выглядит следующим образом:
		array(
			'ключ' => array(
				'DIMENSIONS' => array( // указываются в миллиметрах
					'WIDTH'  => <ширина>,
					'HEIGHT' => <высота>,
					'LENGTH' => <длина>,
				),
				'WEIGHT' => <вес> // указывается в граммах
			)
		)
		остальные поля модифицировать крайне не рекомендуется

		!Не забудьте, что \$arOrderGoods - указатель на массив
		
		Пример: зададим, чтобы габариты брались из свойства с кодом GABS, заданными в формате ДxШxВ:
	*/
	</div>
	foreach(\$arOrderGoods as \$key => \$arGood){
		\$elt = CIBlockElement::GetList(array(),array('ID'=>\$arGood['PRODUCT_ID']),false,false,array('ID','PROPERTY_GABS'))->Fetch();
		if(!\$elt['PROPERTY_GABS_VALUE']){ <span style='color:#008000'>// ищем в торговых предложениях</span>
			\$TP = CCatalogSku::GetProductInfo(\$arGood['PRODUCT_ID']);
			\$elt = CIBlockElement::GetList(array(),array('ID'=>\$TP['ID']),false,false,array('ID','PROPERTY_GABS'))->Fetch();
		}
		if(\$elt['PROPERTY_GABS_VALUE'] && preg_match('/(\d+)x(\d+)x(\d+)/',\$elt['PROPERTY_GABS_VALUE'],\$matches)){
			\$arOrderGoods[\$key]['DIMENSIONS'] = array(
				\"LENGTH\" => \$matches[1],
				\"WIDTH\"  => \$matches[2],
				\"HEIGHT\" => \$matches[3],	
			);
		}
	}
}
		</pre></div>
	</div>
</div>

<div class='ipol_subFaq'>
	<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Изменение списка ПВЗ (Добавление / удаление пунктов) [onPVZListReady].</a>
	<div class='ipol_inst'>
	Если необходимо модифицировать имеющийся список ПВЗ, в файл /bitrix/php_interface/init.php нужно добавить следующий год:
	<div style='color:#AC12B1'><pre>
AddEventHandler('ipol.sdek', 'onPVZListReady', 'handlePVZ');

function handlePVZ(&\$arPVZ){

	<div style='color:#008000'>
	/*
		добавление/удаление ПВЗ задаются модификацией массива arPVZ, который выглядит следующим образом:
		array(
			'<тип>' => array( // PVZ - массив с пунктами самовывоза
				'ID города СДЭК ' => array(
					'код ПВЗ' => array(
						['Name'] => Название ПВЗ,
						'WorkTime' => график работы,
						'Address' => Адрес,
						'Phone' => телефон,
						'Note' => комментарий,
						'cX' => координаты,
						'cY' => 
					)
				),
			)
		)

		!Не забудьте, что \$arOrderGoods - указатель на массив
		Важно! Следите за кодировками!
		Код города ID города СДЭК можно узнать в таблице городов (Настройки модуля -> Города)
		
		Пример: Удалим ПВЗ в Дзержинском:
	*/
	</div>
	if(array_key_exists('44',\$arPVZ['PVZ']) && array_key_exists('MSK22',\$arPVZ['PVZ']['44']))
		unset(\$arPVZ['PVZ']['44']['MSK22']);
}
		</pre></div>
	</div>
</div>


<div class='ipol_subFaq'>
	<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Изменение приоритета расчета тарифов [onTarifPriority].</a>
	<div class='ipol_inst'>
	При необходимости можно изменить порядок расчета тарифов СДЭК. Для этого необходимо внимательно ознакомиться с разделом FAQ \"Особенности расчета стоимости доставки\", а после этого в файл /bitrix/php_interface/init.php нужно добавить следующий год:
	<div style='color:#AC12B1'><pre>
AddEventHandler('ipol.sdek', 'onTarifPriority', 'changePriority');

function changePriority(&\$arTarifPriority,\$profile){

	<div style='color:#008000'>
	/*
		Массив \$arTarifPriority задает очередность расчета тарифов.
		Не добавляйте тарифы извне, это чревато бедой.
		
		\$profile - это рассчитываемый профиль (courier / pickup)
	*/
	</div>
}
		</pre></div>
		Возможность учитывать в функции используемую службу доставки и прочие параметры отсутствует ввиду сложности отслеживания.
	</div>
</div>

<div class='ipol_subFaq'>
	<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Прочие события модуля.</a>
	<div class='ipol_inst'>

	<strong>Список товаров в оформлении заявки - onGoodsToRequest</strong><br>
	Срабатывает при отправке заявки, перед формированием XML списка товаров. Можно использовать для корректировки названий, стоимостей или переформирования всего списка товаров.<br>
	<div style='color:#AC12B1'><pre>
AddEventHandler('ipol.sdek', 'onGoodsToRequest', 'reassembleGoods');
function reassembleGoods(&\$arTG,\$oId){
	<div style='color:#008000'>/* 
	\$arTG - массив с данными о товарах (указатель)
	\$oId - ID заказа
*/</div>
}
	</pre></div>

	<strong>Отправка заказа - requestSended</strong><br>
	Срабатывает после отправки заявки. Можно использовать для дополнительного отслеживания статусов.<br>
	<div style='color:#AC12B1'><pre>
AddEventHandler('ipol.sdek', 'requestSended', 'onSdekSend');
function onSdekSend(\$oId,\$status,\$sdekId){
	<div style='color:#008000'>/* 
	\$oId - ID заказа
	\$status - статус заявки в модуле (ERROR - ошибка, OK - отправлен)
	\$sdekId - идентификатор отправления в базе СДЭК
*/</div>
}
	</pre></div>

	<strong>Парсинг адреса - onParseAddress</strong><br>
	Событие срабатывает при автоматической отправке заявки. Служит для задания собственного обработчика разбиения адресов для корректной работы функционала.<br>
	<div style='color:#AC12B1'><pre>
AddEventHandler('ipol.sdek', 'onParseAddress', 'myAdressParser');
function myAdressParser(&\$fields){
	<div style='color:#008000'>/* 
	\$fields - Массив вида 
	array(
			'address' => '<нераспарсенный адрес>'
		);
	На выходе должен иметь следующий вид:
	array(
			'street' => '<улица>',
			'house'  => '<дом>',
			'flat'   => '<квартира>',
		);
*/</div>
}
	</pre></div>
	</div>
</div>
</p>
";

$MESS['IPOLSDEK_FAQ_DIFFERENTSENDERS_TITLE'] = "- Разные города-отправители для одного заказа";
$MESS['IPOLSDEK_FAQ_DIFFERENTSENDERS_DESCR'] = "<p>При работе с разными складами может возникнуть ситуация, когда товары могут базироваться в разных городах. Тогда стоимость и сроки доставки должны учитывать доставку товаров с разных направлений. В этом случае можно воспользоваться функционалом разбиения на города-отправители.</p>
<p>Если задано разбиение на города-отправители - товары в заказе будут разбиты по указанному правилу по нескольким городам-отправителям. Тогда стоимость доставки будет равна суммарной стоимости доставки распределенных по городам-отправителям товаров до города доставки. Срок же будет выведен максимальный.</p>
<p>Пример: в заказе два товара, один находится на складе в Москве, второй - в Санкт-Петербурге. Их нужно доставить в Екатеринбург. По умолчанию стоимость и срок доставки будет рассчитываться до Екатеринбурга от города-отправителя, указанного в настройках. Если задать же распределение, стоимость доставки будет равна (Доставка I товара из Москвы в Екатеринбург) + (Доставка II товара из Санкт-Петербурга в Екатеринбург); срок доставки: максимум(Доставка из Москвы в Екатеринбург, Доставка из Санкт-Петербурга в Екатеринбург).</p>
<p>Обратите внимание, что функционал манипулирует только со стоимостями и сроками доставки! Он НЕ разбивает заявки на несколько отправлений, а заказ - на отгрузки. Все это возлагается на менеджера.</p>
<p>Чтобы задать распределение необходимо во-первых поставить флаг \"Включить разбиение на города-отправители\", а во-вторых задать функцию-распределитель товаров, подписанную на событие onBeforeShipment. Функция принимает массив распределения (пустой по умолчанию) и массив с товарами, которые необходимо распределить. В теле функции в массив распределения необходимо задать распределение товаров. Те товары, которые не были распределены функцией, будут определены в отдельную отгрузку с базовым городом-отправителем!<br><span class='IPOLSDEK_warning'>Внимание!</span> Задание функции возлагается на пользователей модуля. Для этого необходимо вмешательство стороннего программиста. Разработчики модуля могут помочь с этим, но только в рамках платной техподдержки.</p>
<p>Если у вас используются кастомизированные шаблоны виджетов выбора ПВЗ или инфовиджета - их расчет может отличаться от оформления заказа при задании распределения, так как они считают доставку по старой схеме. Адаптируйте шаблоны согласно стандартным.</p>
<p>В заявке у заказов, подверженных разбиению, может казаться неадекватным свойство \"Рассчитанный тариф СДЭК\", так как там хранится информация по всем тарифам отгрузок. В самом окне можно вызвать справку, которая выдаст эти сведения в удобочитаемом формате (\"Задано разбиение\").</p>
<div class='ipol_subFaq'>
	<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Пример распределения.</a>
	<div class='ipol_inst' style='color:#AC12B1'>
		<pre>
AddEventHandler('ipol.sdek', 'onBeforeShipment', 'SDEK_shipment'); <span style='color:#008000'>// подписываемся на событие</span>

function SDEK_shipment(&\$order,\$items){ <span style='color:#008000'>// получаем массив с товарами</span>
	<div style='color:#008000'>
	/*
		Вид входящего массива:
		array(
			array(
				'ID' => ID записи в корзине покупателя
				'PRODUCT_ID' => ID товара
				'PRICE' => цена
				'QUANTITY' => количество
				'DIMENSIONS' => Array габариты в милиметрах
					(
						'WIDTH' => 0
						'HEIGHT' => 0
						'LENGTH' => 0
					)
				'WEIGHT' => вес в граммах
				'SET_PARENT_ID' => признак комплекта
				'LID' => принадлежность сайту
			)
		)
		Вид выходного массива
		array(
			array(
				'SENDER' => ID Города-отправителя ПО БАЗЕ СДЭКа,
				'ITEMS'  => array(
					'ID записи в корзине покупателя' => количество
				)
			)
		)

		!Не забудьте, что \$order - указатель на массив
		
		Пример: допустим, что у всех товаров есть некое свойство, где записан город-отправитель в текстовом виде, и это свойство будет иметь код SENDER
	*/
	</div>
	
	cmodule::includeModule('iblock');
	cmodule::includeModule('sale');
	\$arOrder = array();
	foreach(\$items as \$item){
		<span style='color:#008000'>// получаем значение свойства</span>
		\$propValue = CIBlockElement::GetList(
			array(),
			array(
				'ID'=>\$item['PRODUCT_ID'],
				'LID'=>\$item['LID']
			),
			false,
			false,
			array('ID','PROPERTY_SENDER')
		)->Fetch();
		if(\$propValue && \$propValue['PROPERTY_SENDER_VALUE']){
			<span style='color:#008000'>// получаем местоположение</span>
			\$city = CSaleLocation::getList(array(),array('CITY_NAME'=>\$propValue['PROPERTY_SENDER_VALUE']))->Fetch();
			if(\$city){
				<span style='color:#008000'>// ищем город в таблице СДЭКа и молимся, чтобы все прошло хорошо</span>
				\$SDEKcity = sqlSdekCity::getByBId(\$city['ID']);
				if(\$SDEKcity){
					if(!array_key_exists(\$SDEKcity['SDEK_ID'],\$arOrder))
						\$arOrder[\$SDEKcity['SDEK_ID']] = array();
					\$arOrder[\$SDEKcity['SDEK_ID']][\$item['ID']] = \$item['QUANTITY'];
				}
			}
		}
	}

	foreach(\$arOrder as \$city => \$items)
		\$order[]= array(
			'SENDER' => \$city,
			'ITEMS'  => \$items
		);
}
		</pre>
	</div>
</div>";

$MESS['IPOLSDEK_FAQ_SENDWATCHLINK_TITLE'] = "- Отправка трекинга клиенту";
$MESS['IPOLSDEK_FAQ_SENDWATCHLINK_DESCR'] = "<p>Если необходимо отправлять покупателю ссылку на отслеживание посылки - можно воспользоваться опцией \"Свойство заказа для сохранения ссылки на отслеживание заказа\". В ней задается код свойства, куда модуль сохранит ссылку на отслеживание заказа на сайте СДЭКа. При отправке заявки модуль сохранит ссылку в указанное свойство.</p><p>Чтобы непосредственно отправить ее клиенту вы можете воспользоваться либо кастомным функционалом, либо - воспользоваться нашим бесплатным модулем <a href='https://marketplace.1c-bitrix.ru/solutions/ipol.mailorder/' target='_blank'>Параметры заказа в письме</a>. Отправку ссылки можно настроить на один из смены статусов из группы настроек Обратной связи, скажем, при доставке груза на склад СДЭКа - то есть, когда он непосредственно передан в доставку. Учтите тот факт, что консультации непосредственно по отправки ссылки в письме не рассматриваются в рамках бесплатной техподдержки ввиду кастомизации для конкретного сайта.</p>";

	// FAQ: Справка
$MESS['IPOLSDEK_FAQ_HDR_HELP'] = "Справочная информация";

$MESS['IPOLSDEK_FAQ_CITYSUNC_TITLE'] = "- О синхронизации местоположений";
$MESS['IPOLSDEK_FAQ_CITYSUNC_DESCR'] = "<p>В Битриксе и СДЭКе используются разные таблицы местопложений, поэтому в модуле имеется функционал их сопоставления для корректного расчета доставки. На данный момент возможность вносить правки вручную (добавлять, изменять, удалять записи о синхронизированных городах) не предусмотрена. Функционал состоит из трех частей: базовой, автоматической и импорта.</p><p><b>Базовая синхронизация</b><br>Занимается сопоставлением городов, игнорируя деревни (села, поселки, пгт - и так далее) для ускорения процесса. Обычно занимает не более 10 минут при стандартном наборе местоположений Битрикса. Функционал запускается по агенту раз в сутки для поддержания актуальности справочников. Вручную запускается из Сервисных свойств или из вкладки \"Города\".</p><p><b>Автоматическая синхронизация</b><br>Обеспечивает сопоставление местоположений с типом VILLAGE и TOWN. Включается поднятием флага в опции \"Автосопоставление городов\" (Сервисные свойства). При обнаружении города, которого нет в таблице соответствий, происходит запрос к серверу СДЭКа с попыткой определить город в их таблице местоположений. Наличие местоположения-родителя с типом SUBREGION (район) позволяет точнее сделать соответствие. Из минусов - требуется от одного до четырех лишних запросов на сервер СДЭКа для определения местоположения, что может пагубно сказаться на работе страницы Оформления заказа. Ненайденный город кэшируется на сутки, чтобы не тормозить оформление заказа в дальнейшем.<br><span class='IPOLSDEK_warning'>Внимание!</span> На данный момент функционал не затрагивает Конфликтные города, а так же есть риск повисания страницы оформления заказа, если метод СДЭКа недоступен (сейчас нельзя проверить, отсутствуют ли города в выборке, или же сервер повис).</p><p><b>Импорт</b><br>Позволяет записать в местоположения Битрикса те записи из таблицы СДЭКа, которые не были просинхронизированы. Риск возникновения дублей. В Местоположениях 2.0 и включенный автосинхронизацией потерял всякую актуальность. Подробнее - \"Справочная информация\" -> \"Прочее\".</p>";

$MESS['IPOLSDEK_FAQ_CNTDOST_TITLE'] = "- Особенности расчета стоимости доставки";
$MESS['IPOLSDEK_FAQ_CNTDOST_DESCR'] = "Стоимость доставки рассчитывается с помощью калькулятора тарифов СДЭК, она же отображается покупателю при оформлении заказа.<br><br>
Стоимость доставки зависит от размеров заказа: его габаритов и веса. <strong>Если в заказе несколько товаров, модуль считает их единой коробкой (одним грузоместом) и выводит посчитанные для нее стоимость и срок доставки. Для расчета веса и габаритов заказа используются данные его товаров, взятые из штатных параметров Торгового каталога, блок \"Вес и размеры\".</strong> Вес считается путем суммирования веса товаров. Габариты считаются с помощью быстрого алгоритма оптимальной компоновки, с учетом габаритов конкретных товаров.<br><br>
Если в заказе присутствуют товары с не указанными размерами или весом, то расчет изначально производится без их учета. Для расчета стоимости доставки принимается максимальное значение из рассчитанных габаритов или веса и настроек модуля по умолчанию (блок настроек \"Размеры по умолчанию\"). Причина того, что заказ в модуле весит больше, чем на сайте, в том, что в составе этого заказа есть товар с неуказанными габаритами.<br><br>
<strong>Для расчета доставки используется наибольшее значение из физического веса и объемного веса, который считается по формуле (длина * ширина * высота в сантиметрах) / 5000.</strong> Например, коробка с заказом длиной 40, шириной 30 и высотой 20 см имеет объемный вес 40*30*20/5000 = 4.8 кг. Если ее физический вес менее 4.8 кг, стоимость доставки будет считаться от объемного веса. Более детально вопросы тарификации стоимости доставки может разъяснить ваш клиентский менеджер СДЭК.<br><br>
Габариты и объемный вес, для которых рассчитывается доставка, можно увидеть на странице заказа (в админке), нажав на кнопку \"СДЭК доставка\" и щелкнув в открывшемся окне по заголовку \"Детали заказа\".<br><br>
<strong style='color:red'>Логика выбора конкретного тарифа СДЭК</strong><br><br>
Все тарифы СДЭК разбиты на три группы: курьер, самовывоз, постамат. API СДЭК получает список тарифов в порядке уменьшения приоритета (посылка, тяжеловесные, экспрессы в порядке увеличения стоимости). На выходе будет тот тариф, которым возможна доставка в указанный пользователем регион, попавшийся в списке допустимых тарифов первым.<br><br>
<strong>В расчете доставки для публичной страницы оформления заказа участвуют только те тарифы, которые не отмечены по колонке \"Отключить для расчета\" блока настроек \"Настройки тарифов и доп. услуг\".</strong><br><br>
Полный список тарифов в порядке уменьшения приоритета:<br>
<ul>
	<li>Курьер: 233,231,137,139,482,480,122,121,125,124,11,1,718,805,716,3,708,796,706,61,698,787,696,60,688,778,686,59,678,676,58,57,83,16,18</li>
	<li>Самовывоз: 234,232,136,138,483,481,62,123,63,126,5,10,12,719,806,717,804,709,797,707,795,699,788,697,786,689,779,687,777,679,677,15,17</li>
	<li>Постамат: 378,376,368,366,363,361,486,485</li>
</ul>
Для упрощения восприятия:
<ul>
    <li>Тарифы сгруппированы по разновидностям услуги СДЭК: наиболее приоритетна \"Экономичная посылка\", затем следуют \"Посылка\", \"Экспресс\", \"Магистральный экспресс\", \"Магистральный супер-экспресс\", \"Экспресс лайт\", \"Супер-экспресс\" и \"Экспресс тяжеловесы\"</li>
    <li>В рамках одной разновидности услуги вначале идет тариф от склада, затем от двери</li>
</ul>
<strong>Номер тарифа СДЭК, которым была рассчитана доставка данного заказа, можно увидеть в свойстве заказа \"Рассчитанный тариф СДЭК\".</strong> При открытии формы заполнения заявки этот тариф будет выбран по-умолчанию.<br><br>
<span style='color:red'>Важно!</span> Стоимость и сроки доставки рассчитываются сервером API СДЭК и могут измениться при изменении габаритов или веса заказа.<br><br>
<strong>Комплекты</strong><br><br>
При расчете доставки, формировании XML и расчете мест комплекты считаются одним товаром - комплектом (с габаритами, прописанными для комплекта, а не входящих в его состав товаров). Чтобы рассчитывать комплект как входящие в него товары - необходимо определить константу IPOLSDEK_DOWNCOMPLECTS в true (<span style='color:#AC12B1'>define(\"IPOLSDEK_DOWNCOMPLECTS\",true);</span>) в init.php. Определение происходит кодом, а не через опцию, так как это кардинально влияет на цену (в том числе и скидки) товара, а так же на расчет доставки.";

$MESS['IPOLSDEK_FAQ_CALLCOURIER_TITLE'] = "- Особенности наложенного платежа";
$MESS['IPOLSDEK_FAQ_CALLCOURIER_DESCR'] = "<p>Каким образом оплачивается заказ, модуль определяет по настройке \"Платежные системы, при которых курьер не берет деньги с покупателя\", сопровождающейся дополнительной подсказкой.</p>
<p>Модуль не учитывает комиссию за наложенный платеж, так как это задача Платежной системы, а не Службы доставки. Если необходимо ее добавить, можно сделать следующие манипуляции:
<ul>
<li>Создать отдельный обработчик автоматизированной службы доставки, который будет отвечать именно за наложенный платеж.</li>
<li>В настройках обработчиков службы доставки СДЭК на вкладке «Ограничения» для каждого из обработчиков задать ограничения по платежным системам. Чтобы один был доступен только для платежных систем, подразумевающих наложенный платеж, а другой - наоборот, с оплатой без наложенного платежа.</li>
<li>Компонент оформления заказа рекомендуется перевести в режим \"Оплата\" -> \"Доставка\", чтобы не смущать покупателя \"простыней\" с службой доставки с оплатой по налу / безналу.</li>
<li>Далее - либо воспользоваться Правилами работы с Корзиной (Административная панель Битрикса -> Маркетинг -> Товарный маркетинг -> Правила работы с корзиной) и добавить условия увеличения стоимости доставки для тех, в которых предусмотрена оплата наложенным платежом, либо - воспользоваться встроенным в модуль функционалом модификаций (FAQ -> Модификация результатов расчетов (для программистов) -> Стоимость и сроки доставки [onCalculate]). Во втором случае потребуется помощь опытного программиста. Определить, какая именно доставка используется для Наложенного платежа модификация может с помощью поля \"Пользовательский идентификатор\" вкладки \"Параметры запроса\" настройщика службы доставки.</li>
</ul>
</p>
<p>Обращаем внимание, что настройка сайта и ввод модификаций не входит в рамки бесплатной техподдержки.</p>
";

$MESS['IPOLSDEK_FAQ_TESTACCOUNT_TITLE'] = "- Тестовый аккаунт";
/*$MESS['IPOLSDEK_FAQ_TESTACCOUNT_DESCR'] = "<p>При запросе данных для работы с API вам пришлют два доступа: боевой и тестовый. Тестовый доступ нужен только для проверки функциональности модуля (нет php-ошибок, ничего не падает, расчет ведется). Учтите, что тестовые доступы обладают рядом особенностей, из-за которых функционирование может быть некорректным:
<ul><li>Не доступны для использования тарифы услуги «Посылка», поэтому для тестов рекомендуем использовать тарифы и услуги «Экспресс». Посылки для тестовых учетных записей не будут обрабатываться и доставляться.</li>
<li>При тестировании заказы не отображаются в личном кабинете клиента.</li>
<li>На печатной форме будет красным цветом написано «Тестовый заказ».</li>
<li>Тестовая учетная запись не имеет привязки к договору, следовательно, для нее не работают скидки и наценки, установленные в договоре, а услуга страхования считается как для обычных доставок.</li>
<li>При работе тестового аккаунта изменения, переданные в тестовом режиме, будут аннулированы автоматически в 23.00 по NSK. Поэтому проверку тестовых данных необходимо проверять в те же сутки.</li></ul>
</p>";*/
$MESS['IPOLSDEK_FAQ_TESTACCOUNT_DESCR'] = "<p>Модуль не поддерживает тестовые аккаунты из документации. Тестовый контур предназначен для отладки разрабатываемого функционала - модуль же протестирован тысячами клиентов и не нуждается в отладке основного функционала. Все отправленные в СДЭК заявки можно удалить (через модуль), и они не помешают работе. Запрашивать доступы в API все равно придется, если, конечно, клиент настроен на работу со СДЭКом, а не поставил модуль по каким-то иным причинам. Наконец, в тестовом режиме рассчитанные тарифы могут сильно отличаться от тех, что клиент видит в ЛК - что создавало множество вопросов среди тех, кто не переключался на боевые доступы ввиду того, что в их бизнес-процессе заказы в ЛК отправлялись не через модуль.</p>";

$MESS['IPOLSDEK_FAQ_ERRORS_TITLE'] = "- Уведомления и обновления";
$MESS['IPOLSDEK_FAQ_ERRORS_DESCR'] = "<p>
	<strong>1. Ошибки</strong><br>
	В процессе работы не исключены возможности возникновения ошибок. Все они находятся в файле <a href='/bitrix/js/<?=$module_id?>/errorLog.txt' target='_blank'>логов</a>. В случае, если возникли ошибки, на странице Отладки будет выведено оповещение о них.<br>
	<img src=\"/bitrix/images/ipol.sdek/FAQ_4.png\"><br>
	Оповещение убирается путем очистки файла логов.
</p>
<p>
	<strong>2. Обновления справочников</strong><br>
	Раз в день модуль запрашивает сервер СДЭК на наличие обновлений в пунктах самовывоза, а так же запрашивает новый файл с городами.<br>
	Чтобы вручную запросить информацию о наличии обновлений, нажмите кнопку \"Синхронизировать\" во вкладке \"Настройки\" -> \"Сервисные свойства\". \"Дата последней синхронизации\" - дата, когда в последний раз запрашивалась информация. Информация продублирована во вкладке \"Города\"<br>
</p>
";

$MESS['IPOLSDEK_FAQ_PROBLEMS_TITLE'] = "- Частые проблемы";
$MESS['IPOLSDEK_FAQ_PROBLEMS_DESCR'] = "
<p><strong>Общее</strong></p>
    <div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Не грузятся ПВЗ конкретного города или они \"битые\".</a>
		<div class='ipol_inst'>
		<ul>
		
			<li>Проверьте, что во вкладке \"Настройка\" нет сообщения, что отсутствуют данные о ПВЗ</li>
			<li>Можно восстановить ПВЗ из резервной копии. Для этого необходимо включить режим отладки в сервисных свойствах модуля, после чего во вкладке \"Отладка\" запустить восстановление списка ПВЗ.</li>
		</ul></div>
	</div>
	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Возникает ошибка \"Ошибка получения информации о ПВЗ\".</a>
		<div class='ipol_inst'>
		    Рядом с ошибкой выводится дата ее возникновения. Если она произошла единожды и давно - нет причин для беспокойства: список ПВЗ синхронизируется раз в сутки, поэтому он наверняка уже восстановился. Главное - чтобы не возникала ошибка на странице настроек модуля об отсутствии данных о ПВЗ. Тогда необходимо просинхронизировать список ПВЗ вручную (соответствующая кнопка появится в оповещении).
		</div>
	</div>
	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Отсутствуют города.</a>
		<div class='ipol_inst'>
		    <p>
		        Запустите синхронизацию модуля в сервисных свойствах или на вкладке \"Города\" и дождитесь информации, что она проведена.
		    </p>
		    <p>
		        Если нужный город отсутствует в форме выбора местоположения в оформлении заказа - модуль тут не при чем: он работает с теми местоположениями, что есть на сайте. В таком случае можно импортировать местоположения стандартного Битриксовского функционала (НЕ импорт местоположений модуля!) в разделе «Магазин – Настройки – Местоположения – Импорт местоположений». Перед любыми манипуляциями с местоположениями рекомендуется произвести полное резервное копирование сайта. Импорт местоположений рекомендуется производить до уровня сел с расширенным набором местоположений так как большая часть местоположений отсутствует в стандартном наборе местоположений. После импорта необходимо сного просинхронизировать модуль.
            </p>
            <p>Информация о работе с местоположениями представлена в <a href=\"https://dev.1c-bitrix.ru/learning/course/?COURSE_ID=42&CHAPTER_ID=05148\" target=\"_blank\">курсе Битрикс «Администратор. Бизнес»</a>
		    </p>
		</div>
	</div>
	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Отсутствует доставка в постаматы.</a>
		<div class='ipol_inst'>
		    Для решения проблемы необходимо провести следующую проверку:
		    <ol>
		        <li>Запустить синхронизацию модуля в его настройках (Настройки -> Сервисные свойства -> Синхронизировать) для загрузки списка постаматов.</li>
		        <li>В службу доставки модуля добавить профиль Постамат (Магазин -> Настройки -> Службы доставки -> Доставка СДЭК модуля ipol.sdek -> Профили -> Добавить профиль). Это актуально для Битрикса версии 15 и выше.</li>
		        <li>Проверить, что в настройках модуля, раздел \"Настройки тарифов и доп. услуг.\" не отключились тарифы постаматов (361, 363, 366 и прочие необходимые вам), также их следует показывать в форме отправки заявки.</li>
		        <li>Нелишним будет сбросить кэш модуля и Битрикса.</li>
		        <li>Учтите, что каждый постамат имеет определенные ограничения по размеру ячейки. Некоторые точки могут не отображаться из-за того, что размер заказа слишком большой.</li>
		    </ol>
		</div>
	</div>

<p><strong>Проблемы со стоимостями доставки</strong></p>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Расчет не совпадает с личным кабинетом.</a>
		<div class='ipol_inst'>Внимательно ознакомьтесь с пунктом FAQ \"Особенности расчета стоимости доставки\": в нем детально расписано, как считается вес и габариты товара. Если при проверке в личном кабинете задаются те же габариты, что и в контрольном заказе - обратитесь в техподдержку (support@ipolh.com), указав следующие данные: <ul>
			<li>Номер договора со СДЭК</li>
			<li>Скриншот (снимок экрана) личного кабинета СДЭКа с расчетом (через Новый заказ, чтобы были видны габариты и рассчитанные тарифы)</li>
			<li>Ссылку на контрольный товар (для габаритов которого вы проверяете доставку в ЛК)</li>
			<li>Доступы к админке сайта</li>
		</ul></div>
	</div>
	
	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Некорректная стоимость для Белоруссии или Казахстана.</a>
		<div class='ipol_inst'>Проверьте корректность установки валюты для этой страны в разделе \"Города\". Обязательно ознакомьтесь перед этим с разделом документации \"Дополнительные возможности\" -> \"Доставка в Республику Беларусь и Казахстан\".</div>
	</div>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Неправильно считаются габариты товаров.</a>
		<div class='ipol_inst'>Внимательно ознакомьтесь с пунктом FAQ \"Особенности расчета стоимости доставки\": в нем детально расписано, как считается вес и габариты товара. Если при создании заказа в личном кабинете стоимости доставки отличаются от рассчитанными модулем - причина в несоблюдении рассчитанных габаритов проверяющим (или модификациях стоимости доставки).</div>
	</div>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Не учитывается страхование при расчете стоимости доставки.</a>
		<div class='ipol_inst'>Чтобы включить страхование товара, воспользуйтесь опцией \"Прибавлять к стоимости доставки величину страховки\". Учтите, что она будет добавлена к исходной стоимости доставки (полученной из личного кабинета), без разделения на стоимость доставки и стоимость страховки.<br>
		Так же имейте в виду, что модуль не поставляет особых платежных систем для работы со службами доставки СДЭКа.</div>
	</div>
	
	<div class='ipol_subFaq IPOLSDEK_converted'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Ставка НДС не совпадает с выставленной в настройках модуля.</a>
		<div class='ipol_inst'>Настройка \"Ставка НДС на доставку по умолчанию\" влияет только на форму оформления заявки. Для управления ставками НДС конкретной службы доставки перейдите в <a href='/bitrix/admin/sale_delivery_service_list.php' target='_blank'>настройки службы доставки</a>. НДС задается в блоке \"Общие настройки\".</div>
	</div>
	
	<div class='ipol_subFaq IPOLSDEK_converted'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Не учитывается комиссия.</a>
		<div class='ipol_inst'>Задача модуля - выводить ту стоимость доставки, которая выдается личным кабинетом СДЭКа для конкретного заказа. В случае любых ее модификаций (в том числе - и с добавлением комиссии) нужно пользоваться Правилами работы с корзиной или же Модификацией расчета. См. также: Справочная информация -> Особенности наложенного платежа.</div>
	</div>

<p><strong>Проблемы в оформлении заказа</strong></p>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Служба доставки не отображается.</a>
		<div class='ipol_inst'><ul>
			<li>Убедитесь, что в настройках модуля указан город-отправитель.</li>
			<li>Проверьте активность у <a href='/bitrix/admin/sale_delivery_handler_edit.php?SID=sdek' target='_blank' class='IPOLSDEK_notConverted'>службы доставки</a><a href='/bitrix/admin/sale_delivery_service_list.php' target='_blank' class='IPOLSDEK_converted'>службы доставки</a> и ее профилей.</li>
			<li>Проверьте выставленные ограничения у <a href='/bitrix/admin/sale_delivery_handler_edit.php?SID=sdek' target='_blank' class='IPOLSDEK_notConverted'>службы доставки</a><a href='/bitrix/admin/sale_delivery_service_list.php' target='_blank' class='IPOLSDEK_converted'>службы доставки</a> и ее профилей.</li>
			<li>Убедитесь, что вы настройках службы доставки не установлен неактивный аккаунт.</li>
			<li>Проверьте доступность службы доставка в настройках <a href='/bitrix/admin/sale_pay_system.php' target='_blank'>платежных систем</a><span class='IPOLSDEK_converted'> и их ограничения</span>.</li>
			<li>Проверьте настройку \"Не давать оформить заказ с наличной оплатой при невозможности оплаты наличными\" - в некоторых городах оплата наличными невозможна.</li>
			<li>Проверьте настройку \"Не давать оформить заказ с наложенным платежом в других странах\" - в городах стран, отличных от России оплата наличными на данный момент невозможна.</li>
			<li class='IPOLSDEK_converted'>Проверьте, что в настройках компонента оформления заказа стоит флаг \"Режим совместимости для предыдущего шаблона\" (для новых компонентов).</li>
		</ul></div>
	</div>
	
	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Показывается кнопка \"расcчитать стоимость\" или доставка не рассчитывается, пока не выбрана.</a>
		<div class='ipol_inst'><ul>
			<li>Новый компонент  : В параметрах компонента оформления заказа (sale.order.ajax) необходимо поставить опцию \"Когда рассчитывать доставки с внешними системами расчета\" в \"Рассчитывать сразу\".</li>
			<li>Старый компонент : В параметрах компонента оформления заказа (sale.order.ajax) необходимо поставить галочку \"Рассчитывать стоимость доставки сразу\".</li>
        </ul></div>
	</div>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Не показывается доставка самовывозом (курьер отображается).</a>
		<div class='ipol_inst'><ul>
		<li>Синхронизируйте справочники (Настройки -> Сервисные свойства -> Синхронизировать).</li>
		<li>Убедитесь, что профиль в <span class='IPOLSDEK_notConverted'><a href='/bitrix/admin/sale_delivery_handler_edit.php?SID=sdek' target='_blank'>службе доставки</a> активен</span><span class='IPOLSDEK_converted'><a href='/bitrix/admin/sale_delivery_service_list.php' target='_blank'>службе доставки</a> создан и активен</span>.</li>
		<li class='IPOLSDEK_converted'>Убедитесь, что вы не создали каких-либо ограничений на профиль.</li>
		<li class='IPOLSDEK_converted'>Если это небольшой город или село - проверьте, что включена опция \"Автосопоставление городов\" - такие города могут иметь проблемы с синхронизацией. Так же убедитесь, что вы не модифицировали шаблон виджета - в обновлении 3.11.7 он претерпел значительные изменения.</li>
		</ul></div>
	</div>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Не показывается доставка в конкретный город.</a>
		<div class='ipol_inst'><ul>
		<li>Убедитесь, что город найден в обработанных (вкладка \"Города\").</li>
		<li>Убедитесь, что страна города отмечена обрабатываемой (вкладка \"Города\").</li>
		<li>Проверьте подписки на событие onCompabilityBefore.</li>
		<li>Проверьте ограничения службы доставки и местоположений.</li>
		</ul></div>
	</div>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Отсутстует возможность выбрать город, куда СДЭК точно доставляет.</a>
		<div class='ipol_inst'>В стандартном оформлении заказа поле для выбора местоположения берет данные из списка местоположений в Битриксе (Магазин -> Настройки -> Местоположения). Модуль не добавляет местоположения Битрикса все местоположения СДЭКа по умолчанию - он работает с тем, что установлено на вашем сайте. Поэтому если населенного пункта нет в поле для ввода местоположения - его нет в базе данных Битрикса вашего сайта. Вам необходимо обновить базу данных местоположений Битрикса и перезапустить их индексацию - в крайнем случае, если пункт не появился, попробовать добавить его вручную. <span class='IPOLSDEK_warning'>Подробнее об описанных процедурах можно узнать в документации к Битриксу (не у техподдержки модуля - он не имеет отношения к стандартному функционалу Битрикса)</span>. После добавления местоположения нелишним будет запустить синхронизацию местоположений модуля в соответствующем разделе настроек.</div>
	</div>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Не показывается кнопка выбора ПВЗ на странице оформления заказа.</a>
		<div class='ipol_inst'><ul>
		<li>Если используется кастомный шаблон оформления заказа - задайте настройку \"ID элемента, в котором выводить информацию о ПВЗ в оформлении заказа\". Обязательно прочитайте подсказку-пояснение (Подробнее о виджете).</li>
		<li>Если вы задали настройку \"ID элемента, в котором выводить информацию о ПВЗ в оформлении заказа\" просто потому, что она есть, не разбираясь в тонкостях шаблонов - очистите поле и сохраните настройки модуля.</li>
		<li>Убедитесь, что задана настройка \"Код свойства, куда будет сохранен выбранный пункт самовывоза\" в Настройки -> Настройки виджета.</li>
		<li>Убедитесь, что в консоли (страница оформления заказа -> F12) нет ошибок в JavaScript.</li>
			<ul>
				<li>Если есть ошибка, связанная с Яндекс-картами (ymaps) - попробуйте поставить флаг \"Не подключать Яндекс-карты\" в настройках модуля (раздел \"Виджет\") и сбросьте кэш страницы оформления заказа.</li>
				<li>Если ошибка связана с ключем - проверьте, что указана опция \"API-ключ Яндекс.карт\". Если скрипты яндекс.карты подключаются не модулем - убедитесь, что ключ указан там (не входит в техподдержку модуля).</li>
			</ul>
		</ul></div>
	</div>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Кнопка выбора ПВЗ на странице оформления заказа грузится слишком долго (более 2х секунд).</a>
		<div class='ipol_inst'><ul>
		<li>Если стоит флаг \"Не подключать Яндекс-карты\" в настройках модуля (раздел \"Виджет\"), а на странице Яндекс-карты не грузятся никаким скриптом - попробуйте убрать флаг.</li>
		<li>Если вы используете старый или кастомный шаблон оформления заказа - убедитесь, что он бросает js-событие onAjaxSuccess, на которое виджет начинает обработку страницы (потребуется помощь программиста).</li>
		</ul></div>
	</div>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Виджет открывается без карты.</a>
		<div class='ipol_inst'><ul>
		<li>Если стоит флаг \"Не подключать Яндекс-карты\" в настройках модуля (раздел \"Виджет\"), а на странице Яндекс-карты не грузятся никаким скриптом - попробуйте убрать флаг.</li>
		<li>Если в консоли (F12 -> console) есть ошибка, связанная с ключем яндекс.карт - задайте его в настройках модуля в опции \"API-ключ Яндекс.карт\". Ознакомьтесь с подсказкой опции. Если скрипт яндекс.карт подключается не модулем - в другом месте так же нужно указать API-ключ (не задача модуля).</li>
		</ul></div>
	</div>
	
	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; В виджете показываются не все пункты выдачи заказов или они отсутствуют.</a>
		<div class='ipol_inst'><ul>
            <li>Если проблема свойственна для всех заказов - запустите Синхронизацию модуля в сервисных свойствах, чтобы загрузился список пунктов, сбросьте кэш модуля и Битрикса.</li>
            <li>Если проблема свойственна определенному составу заказов - пункты могут отсутствовать из-за превышения допустимых габаритов, так как виджет не выводит те пункты, куда заказ могут не принять из-за его размеров/веса. Габариты заказа можно проверить в окне оформления заявки.</li>
		</ul>
		</div>
	</div>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; MySQL-ошибка в оформлении заказа (присутствуют слова 'rus', 'bel', итп).</a>
		<div class='ipol_inst'>Перейдите на вкладку \"Города\", отметьте необходимые для работы страны, сохраните настройки.</div>
	</div>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Отображаются ПВЗ, куда нет доставки (Дзержинский).</a>
		<div class='ipol_inst'>От СДЭКа ожидается модификация, которая позволит фильтровать ПВЗ на возможность работы с Интернет-магазинами. Пока что можно воспользоваться модификацией, описанной в разделе \"Модификация результатов расчетов (для программистов)\", пункт \"Изменение списка ПВЗ\".</div>
	</div>

<p><strong>Проблемы в административной части</strong></p>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Город-отправитель не определяется</a>
		<div class='ipol_inst'><ul>
			<li>Убедитесь, что была запущена синхронизация (настройки -> Сервисные свойства -> Синхронизировать, получено сообщение об успехе).</li>
			<li>Убедитесь, что в таблице городов (вкладка города -> Обработанные) есть город, установленный в настройке Интернет-магазина.</li>
			<li>Убедитесь, что сама <a href='/bitrix/admin/settings.php?lang=ru&amp;mid=sale' target='_blank'>настройка</a> задана.</li>
			<li>Убедитесь, что на сайте нет двух копий местоположений. Если это так - удалите лишнюю копию и запустите синхронизацию вручную.</li>
		</ul></div>
	</div>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Не отображается кнопка \"СДЭК доставка\" для оформления заявки.</a>
		<div class='ipol_inst'><ul>
		<li>Убедитесь, что вы авторизованы в модуле.</li>
		<li>Убедитесь, что вы находитесь на странице детальной информации о заказа (sale_order_detail.php), а не его редактирования.</li>
		<li>Убедитесь, что в консоли (страница оформления заказа -> F12) нет ошибок в JavaScript.</li>
		<li>Если задана настройка \"Отображать кнопку заявки в заказах\" в \"Доставка СДЭК\" - что доставкой выбрана служба доставки СДЭК модуля.</li>
		<li>Проверьте, что для группы пользователей, от которых идет попытка оформить заявку, стоит разрешение во вкладке \"Права\".</li>
		</ul></div>
	</div>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Не выбирается указанный пользователем ПВЗ (выбран самый первый).</a>
		<div class='ipol_inst'>
			ПВЗ, выбранный пользователем, сохраняется в свойство заказа, указанное в опции \"Код свойства, куда будет сохранен выбранный пункт самовывоза\". Это сделано для того, чтобы менеджер сразу видел, куда именно заказана доставка без лишних манипуляций.
		<ul>
		<li>Убедитесь, что указанное свойство существует в <a href='/bitrix/admin/sale_order_props.php?lang=ru' target='_blank'>свойствах заказа</a> и оно отображается в оформлении заказа, не скрывается и не становится disabled до выбора ПВЗ - иначе обеспечьте его вывод.</li>
		<li>Убедитесь, что в заказе это свойство заполнено в формате <адрес ПВЗ> #S<код ПВЗ> - иначе проверьте наличие JS-ошибок на странице оформления заказа.</li>
		<li>Убедитесь, что на странице заказа (в админке) нет ошибок, связанных с объектом IPOLSDEK_oExport.</li>
		</ul></div>
	</div>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Не отсылается заявка.</a>
		<div class='ipol_inst'><ul>
		<li>Убедитесь, что исправлены все возможные ошибки в полях (неверный формат телефона, заполнены все необходимые поля, определен город-получатель).</li>
		<li>Удалите (замените) из полей символы кавычек, углобые скобки, итп.</li>
		<li>Убедитесь, что на странице оформления доставок после очистки кэша (в настройках модуля) продолжают отображаться доставки. Если нет - сервер СДЭК \"лежит\".</li>
		<li>Проверьте права на доступ к модулю у пользователя.</li>
		<li>Ошибка \"ERR_CASH_ON_DELIV_PAYREC_MISTAKE\": в городе отправления невозможен наличный платеж. Чтобы пользователь не мог оформить заказ в этот город с оплатой наличными, воспользуйтесь настройкой \"Не давать оформить заказ с наличной оплатой при невозможности оплаты наличными\".</li>
		<li>Ошибка \"Отсутствие обязательного атрибута: ADDRESS (ERR_NEED_ATTRIBUTE)\" (а сам адрес заполнен корректно): для вашего аккаунта на стороне API СДЭКа подключена обработка старого формата адреса. Необходимо обратиться в техподдержку СДЭКа (integrator@cdek.ru) с указанием вашего номера договора с просьбой переключить вас на новый формат заполнения адреса (метод new_orders). Разумеется, если работа со старым форматом не требуется в иных функционалах сайта или других интеграций со СДЭК.</li>
		<li>Ошибка \"Заказ \"интернет-магазин\" может быть только у клиента с договором интернет-магазина. (ERROR_VALIDATE_PAYER_CONTRACT_IS_NOT_IM)\": ваш аккаунт подключен к интеграции не как ИМ. Метод API для отправки заявки по заказу от ИМ вам недоступен, аккаунт работает только как стандартная доставка. Модуль же напротив работает исключительно по договорам ИМ. Необходимо обратиться в техподдержку СДЭКа, чтобы они подключили аккаунт как ИМ.</li>
		<li>Ошибка \"Номер отправления ИМ не уникальный\". Заказ с таким номером уже отправлялся в ЛК СДЭК либо вручную, либо - возникла ошибка на стороне сервера при отправке через модуль, и последний не получил ответа, что отправка корректная. Вариант решения: удалить заказ из ЛК (если он является ошибочным) и отправить его заново. Убедитесь, что не удалите в таком случае рабочий заказ.</li>
		<li>Ошибка \"Неизвестная внутренняя ошибка (ERROR_INTERNAL)\". Поступает со стороны сервера СДЭК, в случае если на нем есть какие-либо технические проблемы. Обычно техподдержка на стороне СДЭК устраняет данные проблемы на сервере в течение нескольких часов и после этого заказы должны начать отправляться.</li>
		</ul></div>
	</div>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Ошибка про невозможность обновления ПВЗ (или синхронизации статусов заказов) код ответа - 0.</a>
		<div class='ipol_inst'>Такое иногда случается, если запрос происходит в момент, когда сервер СДЭКа недоступен.<br>
		Если же ошибка возникает постоянно - проверьте настройки хостинга, антивирус и фаервол: он может блокировать запросы к серверу СДЭКа. В старых версиях модуля запросы к СДЭКу поступают на 11443-й порт. Если с настройками все в порядке - обратитесь в техподдержку СДЭКа (integrator@cdek.ru). Смоделировать проблему можно, запустив синхронизацию вручную (в сервисных свойствах настроек).</div>
	</div>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Возвращается ошибка при расчете доставки, если сумма доставки - 0.</a>
		<div class='ipol_inst'>Это ошибка Битрикса. При применении изменений все сохранится корректно.</div>
	</div>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Некорректный формат телефона.</a>
		<div class='ipol_inst'>
			<p>СДЭК требует указывать телефон как +<код страны><номер>. Для перевода номера в СДЭКовский формат поставьте флаг \"Адаптация номера телефона\" в группе настроек \"Свойства заявки\" - однако на всякий случай не забывайтесь сверяться с введенным покупателем.</p>
			<p>Если при оформлении заказа посетитель сайта задает телефон с ведущим '+',а после создания заказа '+' исчезает:<br>
			Это не связано с работой модуля. Код компонента Битрикса осуществляет \"нормализацию\" телефонного номера при одновременно включенных флагах:
			<ul>
				<li>\"Является телефоном\" в настройках свойства заказа, используемого как поле для указания телефонного номера;</li>
				<li>\"Дополнительные настройки\" -> \"Использовать нормализацию номера телефона\" в настройках компонента оформления заказа sale.order.ajax.</li>
			</ul>
			При нормализации вырезаются все символы кроме 0123456789 # * , ; <br>
			Если вы используете собственный валидатор телефонных номеров в шаблоне компонента, можно отключить флаг \"Использовать нормализацию номера телефона\", чтобы встроенная нормализация не мешала, вырезая из номера в том числе и требуемый СДЭК ведущий '+'.</p>
		</div>
	</div>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Возникает ошибка \"Клиент не активен ERR_CLIENT_IS_DELETED\".</a>
		<div class='ipol_inst'>
			<p>Нужно обращаться в <a href='https://jira.cdek.ru/servicedesk/customer/user/login?destination=portals' target='_blank'>техподдержку СДЭКа</a>, указав, что получаете эту ошибку при манипуляциях с API.</p>
		</div>
	</div>
	
	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Пропадают товары в распределении по грузоместам.</a>
		<div class='ipol_inst'>
			<p>
			    Подобная проблема может возникать, если у товаров корзины заказа Битрикса установлен флаг CAN_BUY в N, так как. модуль при выборке товаров из корзины заказа учитывает его. Определить проблемный заказ можно, если открыть окно \"Места\": будет видно, что части товаров / всех товаров \"не хватает\" в списке товаров грузоместа. </p>
			<p>
			    В этом случае программисту следует посмотреть таблицу b_sale_basket и определить, что с товарами. Как временное решение, можно вручную исправить CAN_BUY по проблемным товарам заказа. В идеале - разбираться, почему Битрикс отметил данные товары недоступными к заказу и как вообще покупатель смог создать заказ с товарами, которые приобрести в данный момент нельзя. 
			</p>
			<p>
			    Такие проблемы крайне редки и встречаются разве что при учете остатков товаров с помощью 1C.
			</p>
		</div>
	</div>
	
	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Клиент ввел некорректный адрес доставки.</a>
		<div class='ipol_inst'>
			<p>
			    Для предотвращения ввода некорректного адреса рекомендуем воспользоваться нашим модулем <a href=\"https://marketplace.1c-bitrix.ru/solutions/ipol.kladr/\" target=\"_blank\">\"Автозаполнение адреса доставки по КЛАДР\"</a>. Подробнее: Начало работы -> Оформление и отправка заявки -> Проверка корректности заполнения поля адреса
			</p>
		</div>
	</div>

<p><strong>Проблемы в личном кабинете / с отправленными заявками</strong></p>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; При отправке заявки выводится сообщение, что \"Заявка успешно отослана и ожидает подтверждения\".</a>
		<div class='ipol_inst'><ul>
		<li>В отличие от API 1.5, в API 2.0 заказ сначала проходит процедуру валидации. Она может произойти мгновенно - а иногда через какое-то время. Модуль сам запросит статус заказа по агенту раз в полчаса, либо вы можете сделать это кнопкой в окне отправки заявки.</li>
		</ul></div>
	</div>
	
	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Заявка отправилась, но не появилась в ЛК.</a>
		<div class='ipol_inst'><ul>
		<li>Убедитесь, что сервер СДЭКа доступен (нет оповещения об этом в настройках, после очистки кэша службы доставки продолжают отображаться), иначе - нужно ждать, пока сервер не \"поднимется\".</li>
		</ul></div>
	</div>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Не создалась заявка на вызов курьера.</a>
		<div class='ipol_inst'>Вызов курьера работает в тестовом режиме. Техподдержка модуля никак не может решить вопрос с заявкой. Попробуйте еще раз обратиться в Call-центр: заявки на курьера не всегда сразу отображаются в системе. Также не забудьте, что курьер на один адрес выезжает только раз в сутки.</div>
	</div>
	
	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Заявка не отправлялась, но появилась в ЛК.</a>
		<div class='ipol_inst'>
		Отключите Автоотгрузки на соответствующей вкладке модуля.
		</div>
	</div>

<p><strong>Прочие проблемы</strong></p>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Проблемы с правами.</a>
		<div class='ipol_inst'>Если модуль выдает ошибку, связанную с правами и доступом - проверьте, выставлены ли группе пользователей, к которой принадлежит пользователь, получающий ошибку, права на полный доступ к модулю (При полном доступе админка модуля ему все равно не будет показываться, если не прописаны права к папке и файлу с настройками).</div>
	</div>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Импорт городов не работает.</a>
		<div class='ipol_inst'><p>Функционал Импорта предоставляется \"как есть\". В стандартной сборке Битрикса он работает корректно, но не всегда адаптируется под различные реалии сайтов. Поддержка функционала в рамках бесплатной техподдержки не осуществляется.</p><p>Самая распространенная проблема - это \"бесконечный\" цикл импорта, что возникает при ошибке в его выполнении. Чаще всего против этого помогает следующая последовательность действий: <ol><li>Удалить файл /bitrix/js/ipol.sdek/tmpImport.txt</li><li>Проверить настройки городов и нажать кнопку \"сохранить\".</li><li>Сбросить кэш модуля. Он никоим образом не затрагивает импорт, но в любой непонятной ситуации надо сбрасывать кэш модуля.</li></ol></p></div>
	</div>

	<div class='ipol_subFaq'>
		<a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; СДЭК требует XML запроса.</a>
		<div class='ipol_inst'><p>Можно обратиться либо в нашу техподдержку (о чем ниже), либо собрать логи самостоятельно. Для этого необходимо включить логирование (Настройки модуля -> Сервисные свойства -> Включить режим отладки). Во вкладке \"Отладка\" нужно включить логирование и отметить необходимые события. Полная информация по логу и отслеживаемым событиям предоставлена в этой вкладке. Обратите внимание: лог-файл нельзя скачать по ссылке из соображений безопасности (а так же как фикс, что отправляющий лог понимает, какие именно данные нужно отправить техподдержке СДЭКа, а не просто пошлет все, что сможет найти, затруднив диагностику) - требуется скопировать нужный фрагмент на экране вручную.</p></div>
	</div>

<p><strong>Ничего не помогло.</strong><br>
По всем вопросам по настройке модуля можно обращаться на support@ipolh.com.
При обращении укажите, пожалуйста:
<ul><li>модуль, с которым возникла проблема</li><li>подробное описание проблемы</li><li>Номер договора со СДЭК (указывать обязательно)</li><li>FAQ решить ее не помог</li></ul>
Для диагностирования проблемы нам точно потребуются доступы к админке Битрикса и, скорей всего, к ftp или ssh (так как проводить диагностику через админку черевато падением сайта).
</p>
<p><strong>Бесплатная техподдержка.</strong><br>
Бесплатная поддержка включает в себя консультацию по проблемам, возникшим при установке модуля. Задача бесплатной техподдержки - убедиться, что проблема возникла из-за ошибок в модуле. Если это так - проблема будет устранена бесплатно. <strong>Если причина проблемы в сайте</strong> (нестандартная верстка, кастомизация ядра, сторонние решения или прочие модификации) - проблема <strong>не решается</strong> бесплатной техподдержкой. Это же относится к пунктам, освещенным в FAQ (иконки, настройки программистов, итп) - все это решается в рамках <strong>платной техподдержки</strong>.<br>Просьба отнестись с пониманием, что факт бесплатности модуля не означает бесплатную адаптацию его под нужды сайта.
</p>
";

$MESS['IPOLSDEK_FAQ_UPDATES_TITLE'] = "- Информация по обновлениям";
$MESS['IPOLSDEK_FAQ_UPDATES_DESCR'] = "Ниже приводится более расширенная информация по обновлениям модуля, начиная с версии 3.9.0.
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.9.0</a>
    <div class='ipol_inst'>
    <ul>
        <li>Добавлена возможность отправки заказов из Битрикс24 Интернет-магазин+CRM (коробочная версия) в бета-режиме.</li>
        <li>Добавлен поиск по ПВЗ в оформлении заявки (FAQ -> Оформление и отправка заявки -> подробнее о полях -> Пункт самовывоза).</li>
        <li>Добавлена возможность сохранять ссылку на отслеживание посылки в свойствах заказа для передачи клиенту.</li>
        <li>Исправлена ошибка авторизации при недоступности тарифов или крупном весе.</li>
        <li>Исправлена проблема неотображения списка городов в инфовиджете.</li>
        <li>Исправлена ошибка неотображения кнопки выбора ПВЗ на мобильных устройствах.</li>
        <li>Обновлено FAQ.</li>
    </ul>
    Основа обновления - подключение возможности отправки заказов через Битрикс24 указанной редакции, которая позволяет отправлять заявки из интерфейса управления заказами Битрикс24. По факту меняется место подключения формы, в остальном модуль работает в стандартном режиме. Для удобства пользования добавлен поиск по ПВЗ - через поле ввода адреса. Ссылка на отслеживание посылки добавится в свойство заказа после отправки, в итоге ее можно добавить в поле почтового уведомления, чтобы оно улетело клиенту. Также в обновлении начат переход к более корректному выводу и хранению опций.
    </div>
</div>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.9.1</a>
    <div class='ipol_inst'>
    <ul>
        <li>Исправлена проблема сохранения опции автоотгрузок.</li>
        <li>Мелкие правки в js опций.</li>
    </ul>
    </div>
</div>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.9.2</a>
    <div class='ipol_inst'>
    <ul>
        <li>Исправлена проблема с некорректным content-type при запросе</li>
        <li>Исправлена проблема с отправкой заказов</li>
    </ul>
    </div>
</div>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.9.3</a>
    <div class='ipol_inst'>
    <ul>
        <li>Добавлена возможность указания информации по отправителю</li>
        <li>Исправлена проблема с получением НДС товаров</li>
        <li>Создан блок информации об обновлениях</li>
        <li>Скорректирована информация о получении данных для авторизации</li>
        <li>Исправлены мелкие проблемы с Б24</li>
        <li>Исправлена проблема с выводом ПВЗ в некоторых городах</li>
    </ul>
    Перед использованием данных об отправителях крайне рекомендуется ознакомиться с блоком FAQ, посвященным им - там описываются некоторые тонкости работы с этими полями.
    </div>
</div>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.9.4</a>
    <div class='ipol_inst'>
    <ul>
        <li>В виджет добавлен поиск по адресу</li>
        <li>Исправлена проблема с ПВЗ</li>
        <li>Исправлена проблема с указанием страны для службы доставки</li>
    </ul>
    Поиск по адресу поможет клиентам искать ПВЗ рядом со своим местоположением, однако он требует подключенного геокодера. Поэтому если вдруг после включенной опции у вас возникнут проблемы с виджетом - проснулась беда с передачей ключа Яндекс.Карт при подключении их скрипта. В таком случае придется обращаться к нашей <a target='_blank' href='https://ipol.ru/development/oshibka-podklyucheniya-yandeks-kart/'>статье</a>.<br>На данный момент поиск добавлен только в шаблон для оформления заказа, потому что в инфовиджете возникает проблема с переключением городов.
    </div>
</div>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.10.0</a>
    <div class='ipol_inst'>
    <ul>
        <li>Добавлена доставка в постаматы</li>
        <li>Добавлена возможность указывать маркировку товаров (в тестовом режиме)</li>
        <li>Добавлен дополнительная услуга \"Упаковка 1\"</li>
        <li>Поле \"Квартира\" перестала быть обязательным</li>
        <li>Исправлена ошибка при переходе на несуществующий заказ неавторизованным пользователем</li>
        <li>Исправлена проблема с выбором конфликтующего города в форме отправки заявки</li>
        <li>Обновлено FAQ</li>
        <li>Исправлен ряд мелких недочетов</li>
    </ul>
    Для включения постаматов необходимо запустить синхронизацию модуля (чтобы загрузить информацию по постаматам), добавить соответствующий профиль в службу доставки модуля, а также - проверить тарифы постаматов в настройках модуля (раздел \"Настройки тарифов и доп. услуг.\"). В случае возникновения затруднений обратитесь к пункту FAQ \"Частые проблемы\" -> \"Общее\" -> \"Отсутствует доставка в постаматы\".<br>
    Маркировка товаров также добавлена в модуль, однако на данный момент она работает в тестовом режиме ввиду специфики заполнения данных.
    </div>
</div>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.10.1</a>
    <div class='ipol_inst'>
    <ul>
        <li>Исправлена проблема с сохранением маркировок</li>
        <li>Включено предупреждение для профиля \"Постаматы\" при включенной опции \"Не давать оформить заказ на самовывоз, если клиент не выбрал пункт выдачи заказа\"</li>
        <li>Исправлена проблема с отображениями некоторых городов в виджете</li>
        <li>Автоматизация подружилась с тарифами для постаматов</li>
        <li>Исправлена проблема с печатью штрихкодов</li>
        <li>Исправлена проблема с синхронизацией статусов</li>
        <li>Исправлена проблема с ссылкой на отслеживание заказа</li>
        <li>Исправлена проблема с учетом габаритов в виджете</li>
        <li>Исправлен ряд мелких ошибок</li>
    </ul>
    </div>
</div>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.10.2</a>
    <div class='ipol_inst'>
    <ul>
        <li>Исправлены проблемы с неучитыванием флагов из-за версии jquery</li>
        <li>Исправлена проблема с отображением некорректного постамата после отправки заказа</li>
        <li>Исправлена проблема с отображением оплат</li>
        <li>Повышена помехозащищенность модуля</li>
    </ul>
    </div>
</div>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.10.3</a>
    <div class='ipol_inst'>
    <ul>
        <li>Исправлена проблема с синхронизацией статусов заказов</li>        
        <li>Улучшения защиты информации в модуле</li>
    </ul>
    </div>
</div>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.11.0</a>
    <div class='ipol_inst'>
    Начат процесс перехода на новое API.
    </div>
</div>
<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.11.1</a>
    <div class='ipol_inst'>
    Исправление проблем с калькуляцией
    </div>
</div>

<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.11.2</a>
    <div class='ipol_inst'>
    <ul>
        <li>Исправлена ошибка с сохранением тарифа в свойстве заказа.</li>        
        <li>Исправлена ошибка при отправке заказа.</li>
        <li>Исправлена ошибка при отправке отгрузки.</li>
        <li>Оптимизирована калькуляция.</li>
    </ul>
    </div>
</div>

<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.11.3</a>
    <div class='ipol_inst'>
        <ul>
            <li>Исправление загрузки данных о пунктах ПВЗ для windows-1251</li>
            <li>Улучшен расчет стоимости доставки</li>
            <li>Исправление проблем с авторизацией</li>
            <li>Исправление проблемы с передачей стоимости доставки товарной позицией</li>
            <li>Исправлена проблема не-сохранения измененного аккаунт по заявке при отправке через отгрузки</li>
            <li>Исправлена проблема некорректного сохранения опции \"Платежные системы, при которых курьер не берет деньги с покупателя\"</li>
        </ul>
    </div>
</div>

<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.11.4</a>
    <div class='ipol_inst'>
        <ul>
            <li>Доработан функционал отправки заказов через API 2.0.</li>
            <li>Убрана необходимость указывать контактный телефон при отправке заказа через API 2.0.</li>
            <li>Добавлена отладка агентов модуля.</li>
            <li>Исправлен ряд мелких недочетов.</li>
        </ul>
    </div>
</div>

<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.11.5</a>
    <div class='ipol_inst'>
        <ul>
            <li>Указание e-mail получателя заказа в форме отправки заявки сделано необязательным</li>
            <li>Исправлена проблема со ставкой НДС при передаче стоимости доставки как товарной позиции</li>
            <li>Добавлено принудительное округление габаритов и веса грузомест до целых величин при отправке заказа через АПИ 2.0</li>
            <li>Прочие внутренние исправления и улучшения</li>
        </ul>
    </div>
</div>

<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.11.6</a>
    <div class='ipol_inst'>
        <ul>
            <li>Устранена ошибка при расчете доставки через АПИ 2.0, возникающая на странице оформления заказа при полной недоступности сервера СДЭК из-за DDOS атаки</li>
            <li>Небольшое улучшение обработки ошибок сервера СДЭК при отправке заявок через АПИ 1.5</li>
        </ul>
    </div>
</div>

<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.11.7</a>
    <div class='ipol_inst'>
        <ul>
            <li>Ускорена работа виджета на мобильных устройствах (и в браузерах тоже).</li>
            <li>Оптимизирована работа под php8.</li>
            <li>Улучшена совместимость с некоторыми сторонними решениями.</li>
            <li>Ускорена работа окна отправки заявки.</li>
            <li>Добавлена услуга \"Частичная доставка\".</li>
            <li>Изменено событие \"onPVZListReady\". На данный момент оно поддерживает добавление своих ПВЗ по старому формату (с задаванием города через имя - но вскоре это будет убрано для ускорения работы виджета).</li>
            <li>Опция  \"Автосопоставление городов\" теперь по-умолчанию включена.</li>
            <li>Исправлена проблема с отображением списка тарифов и расчета заказа в окне оформления заявки.</li>
        </ul>
    </div>
</div>

<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.11.8</a>
    <div class='ipol_inst'>
        <ul>
            <li>Улучшения работы виджета пунктов самовывоза, добавлен прелоадер, доработана верстка для мобильных устройств.</li>
            <li>ПВЗ, работающие только для фулфилмента СДЭК, убраны из вывода в виджете и форме отправки заявки.</li>
            <li>Исправлено отображение списка ПВЗ в некорректной кодировке в форме отправки заявки.</li>
            <li>Исправлена ошибка с получением маркировочных кодов в форме отправки заявки на старых версиях Битрикса.</li>
            <li>Прочие мелкие исправления и улучшения.</li>
        </ul>
    </div>
</div>

<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.12.0</a>
    <div class='ipol_inst'>
        <ul>
            <li>Оптимизирована работа со статусами для сайтов с большим количеством заказов</li>
            <li>Добавлен учет выбранных по умолчанию доп. услуг при отправке заявок через Автоматизацию</li>
            <li>Исправлена недоработка с не добавляющейся комиссией за страховку в таблице тарифов формы отправки заявки, если используется несколько однотипных профилей службы доставки</li>
            <li>Актуализирован адрес метода запроса ПВЗ СДЭК для АПИ 1.5</li>
            <li>Исправления предупреждений и мелких ошибок, возникающих на PHP 8+</li>
            <li>Мелкие исправления в виджете ПВЗ</li>
            <li>Прочие мелкие доработки и исправления</li>
        </ul>
    </div>
</div>

<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.12.1</a>
    <div class='ipol_inst'>
        <ul>
            <li>Актуализированы тарифы СДЭК, улучшены настройки тарифов, добавлена документация к ним</li>
            <li>Список ПВЗ в виджете точек самовывоза отсортирован по названиям точек</li>
            <li>Добавлено отображение наличия услуги \"Примерка\" на ПВЗ</li>
            <li>Добавлена поддержка ставки НДС 12%</li>
            <li>Добавлена проверка габаритов заказа для вывода профилей доставки самовывозом / до постамата: профили не будут показываться, если нет ни одной подходящей под габариты заказа точки выдачи</li>
            <li>Добавлена возможность передачи адреса доставки (улица, дом, квартира) одной строкой, если используется АПИ 2.0</li>
            <li>Исправлена недоработка, из-за которой могли не обновляться статусы некоторых заказов (заказы оставались в статусе WAIT)</li>
            <li>Исправлена ошибка со стоимостью заказа при одновременной выгрузке через Автоматизацию нескольких заказов</li>
            <li>Добавлена ротация лог-файлов модуля</li>
            <li>Исправлена проблема запроса информации по городам при недействительном сертификате на сайте</li>
            <li>Исправлена оценка равенства стоимости доставки в таблице тарифов</li>
            <li>Улучшения и дополнения в документации модуля</li>
            <li>Прочие мелкие доработки и исправления</li>
        </ul>
    </div>
</div>

<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.12.2</a>
    <div class='ipol_inst'>
        <ul>
            <li>Исправлена проверка габаритов заказа для вывода профилей доставки самовывозом</li>
        </ul>
    </div>
</div>

<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.12.3</a>
    <div class='ipol_inst'>
        <p>Вместо набора опций модуля \"Данные Отправителя\" реализована возможность создавать разные профили данных об Адресе отправления, Отправителе, Продавце, используемые как при отправке заявок по заказам, так и в заявках на вызов курьера. Для разных городов-отправителей можно задать разные данные.</p>
        <p>Если ранее вы передавали в СДЭК данные Отправителя и наименование Продавца, добавьте профиль с нужными данными, руководствуясь разделом документации модуля \"Адрес отправления, Отправитель и Продавец\".</p>
        <ul>
            <li>Добавлен отдельный раздел модуля в админке Битрикса: Магазин -> СДЭК</li>
            <li>Добавлен функционал для создания заявок на вызов курьера, как на консолидированный забор, так и на забор одного заказа, в разделе Магазин -> СДЭК -> Заявки на вызов курьера</li>
            <li>Добавлен функционал для добавления, редактирования, удаления профилей с данными об Адресе отправления, Отправителе и Продавце в разделе Магазин -> СДЭК -> Отправители и продавцы</li>
            <li>Убраны блоки настроек модуля \"Данные Отправителя\" и \"Отправители (для тарифов дверь-)\"</li>
            <li>Обновлен FAQ модуля: добавлены разделы документации \"Адрес отправления, Отправитель и Продавец\" и \"Заявки на вызов курьера\"</li>
            <li>Добавлена дополнительная услуга \"Товары 18+\"</li>
            <li>Исправлена работа опции \"Подписывать размерность дробного количества товаров\"</li>
            <li>Прочие мелкие доработки и исправления</li>
        </ul>
    </div>
</div>

<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.12.4</a>
    <div class='ipol_inst'>
        <ul>
            <li>Добавлены дополнительные услуги \"Уведомление о создании заказа в СДЭК\", \"Уведомление о приеме заказа на доставку\" и \"СМС-уведомление\"</li>
            <li>Добавлена возможность автоматической смены статуса для успешно отправленной заявки</li>
            <li>Исправлена недоработка, из-за которой была возможность авторизации в настройках модуля с помощью некорректных данных</li>
            <li>Добавлена возможность синхронизации местоположений с использованием модуля <a href=\"https://marketplace.1c-bitrix.ru/solutions/ipol.kladr\" target=\"_blank\">\"Автозаполнение адреса доставки по КЛАДР\"</a></li>
            <li>Мелкие доработки и исправления</li>
        </ul>
    </div>
</div>

<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.12.5</a>
    <div class='ipol_inst'>
        <ul>
            <li>Исправление проблемы с идентификаторами отправлений</li>
        </ul>
    </div>
</div>

<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.12.6</a>
    <div class='ipol_inst'>
        <ul>
            <li>Обновление статусов и удаление заявок переведено на АПИ 2.0</li>
            <li>Автосопоставление городов будет использовать улучшенный алгоритм синхронизации и АПИ 2.0, если выключена опция \"Использовать старый API для запросов\"</li>
            <li>Город-отправитель теперь всегда выводится в форме отправки заявки</li>
            <li>Исправлена ошибка с не сохраняющимся адресом вызова курьера для консолидированного забора</li>
            <li>Исправлена ошибка, когда профиль Постаматов мог предлагаться при отсутствии доступных точек выдачи</li>
            <li>Прочие мелкие доработки и исправления</li>
        </ul>
    </div>
</div>

<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.12.7</a>
    <div class='ipol_inst'>
        <ul>
            <li>Исправление некорректного определения города получения, возникающего на некоторых сайтах с последними версиями Битрикса</li>
            <li>Исправлена ошибка с выбором кода маркировки при нескольких штуках одного и того же товара</li>
        </ul>
    </div>
</div>

<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.12.8</a>
    <div class='ipol_inst'>
        <ul>
            <li>Получение файлов печатных форм квитанций и штрихкодов переведено на АПИ 2.0</li>
            <li>Изменена фильтрация ПВЗ, на которых невозможна выдача заказов конечным покупателям: теперь для определения таких точек выдачи вместо флага fulfillment будет использоваться is_handout</li>
            <li>Исправлена ошибка с выбором кода маркировки</li>
            <li>Прочие мелкие доработки и исправления</li>
        </ul>
    </div>
</div>

<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.12.9</a>
    <div class='ipol_inst'>
        <ul>
            <li>Добавлена поддержка ставок НДС 5% и 7%</li>
            <li>Добавлено предупреждение о невозможности оплаты наложенным платежом при получении в форме отправки заявки в СДЭК, если в городе получателя нет приема НПП</li>
            <li>Добавлены тарифы Сборный груз 748-751</li>
            <li>Убрано предупреждение о поддержке ключом интеграции АПИ 1.5, если модуль работает через СДЭК АПИ 2.0 (опция \"Использовать старый API для запросов\" отключена)</li>
            <li>Прочие мелкие доработки и исправления</li>
        </ul>
    </div>
</div>

<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.12.10</a>
    <div class='ipol_inst'>
        <ul>
            <li>Исправлена передача ставок НДС 5% и 7% при создании заказов через АПИ 1.5</li>            
        </ul>
    </div>
</div>

<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.12.11</a>
    <div class='ipol_inst'>
        <ul>
            <li>Улучшен функционал управления аккаунтами: добавлена проверка авторизации в АПИ СДЭК, справка по аккаунтам и получению доступов к интеграции</li>
            <li>Добавлено подключение модулем своей библиотеки jQuery в случае, если в ядре Битрикса отсутствует файл соответствующей версии</li>
            <li>Добавлена обработка статусов заказов до постаматов \"Заложен в постамат\", \"Изъят из постамата клиентом\"</li>
            <li>Автосопоставление городов переключено на АПИ 2.0</li>
            <li>Исправлено срабатывание события модуля onTarifPriority при работе через АПИ 2.0</li>
            <li>Изменены значения по умолчанию некоторых опций модуля для упрощения первоначальной настройки</li>
            <li>Прочие мелкие доработки и исправления</li>
        </ul>
    </div>
</div>

<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.12.12</a>
    <div class='ipol_inst'>
        <ul>
            <li>Улучшение безопасности модуля</li>
        </ul>
    </div>
</div>

<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.12.13</a>
    <div class='ipol_inst'>
        <ul>
            <li>Добавлена опция модуля \"Код ТН ВЭД\"</li>
        </ul>
    </div>
</div>

<div class='ipol_subFaq'>
    <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; 3.12.14</a>
    <div class='ipol_inst'>
        <ul>
            <li>Исправлена PHP ошибка при проверке статуса заказа, если он отклонен СДЭК, но ответ сервера не содержит информации об ошибках</li>
        </ul>
    </div>
</div>
";

$MESS['IPOLSDEK_FAQ_OTHER_TITLE'] = "- Прочее";
$MESS['IPOLSDEK_FAQ_OTHER_DESCR'] = "
    <div class='ipol_subFaq'>
        <a class='ipol_smallHeader' onclick='$(this).next().toggle(); return false;'>&gt; Импорт городов СДЭК.</a>
        <div class='ipol_inst'>
            <p class='IPOLSDEK_mp1'>На данном сайте используются Местоположения 1.0.<br>Никаких дополнительных проверок не требуются.</p>
            <p class='IPOLSDEK_mp2'>На данном сайте используются Местоположения 2.0.<br>
            Города будут создаваться с типом CITY (город). <span class='IPOLSDEK_importHasCity'>Данный тип местоположения присутствует на сайте.</span><span class='IPOLSDEK_importHasNotCity'><span style='color:red'>отсутствует</span> на сайте. Импорт невозможен, так как структура местоположений отлична от стандартных.</span><br>
            Города загрузятся дочерними к регионам.<br>
            <span class='IPOLSDEK_warning'>Учтите, что использовать Импорт с Местоположениями 2.0 НЕ РЕКОМЕНДУЕТСЯ!</span> Используйте Автосопоставление городов (включается в сервисных свойствах модуля).
            </p>
            <p>
                Во вкладке \"Города\" многие местоположения отмечены как \"Не найденные\". Это происходит потому, что база данных городов в СДЭКе намного больше, чем в стандартном Битриксе.<br>
                Решить проблему можно импортом Местоположений. В процессе импорта те местоположения, что не были найдены в Битриксе, будут добавлены на сайт.<br>
                Функционал предоставляется \"Как есть\", и возможные ошибки не рассматриваются, так как структура местоположений может быть различна, и гарантировать 100% результат невозможно. Однако функционал был протестирован на местоположениях, загруженных с сервера Битрикса, и результат признан удовлетворительным. Использовать функционал можно под вашу ответственность.</p>
            <p>Что необходимо для импорта:
            <ol><li>Структура местоположений как минимум должна включать в себя цепочку Страна -> регион / область -> город.</li>
            <li>Если региона, указанного в списке СДЭКа нет на сайте - местоположения этого региона загружены не будут.</li>
            <li>Необходимо сделать бэкап сайта на случай проблемной выгрузки.</li>
            <li>Импорт - долгий процесс. В зависимости от скорости работы сайта может занять несколько часов. Желательно не закрывать вкладку с импортом и обеспечить бесперебойный доступ к Интернету.</li>
            <li>Пока идет шаг импорта, сервер будет занят его обработкой. Рекомендуется включать импорт, когда сайт минимально нагружен (например, ночью).</li></ol>
            Для запуска импорта нужно зайти во вкладку Настройки, перейти в Сервисные свойства и нажать на кнопку \"Импорт городов СДЭК\". После перезагрузки откроется вкладка \"Импорт городов\", в которой нужно задать длительность шага в секундах (зависит от настроек php - когда скрипт вылетает по таймауту). При нажатии на кнопку \"Начать импорт\" будет запущен скрипт импорта, состоящий из запроса файла с городами, предварительной проверки синхронизации, импорта городов и завершающей проверки синхронизации. Если импорт будет прерван - его необходимо перезапустить: при предварительной проверке синхронизации будут переопределены города для загрузки, поэтому импорт начнется с последнего добавленного города. Однако прерывать импорт не рекомендуется.</p>
            <p>Импорт не разрешает проблему Конфликтующих городов. Чтобы их убрать возможно удалить имеющееся Местоположение Битрикса Конфликтующего города, чтобы все Претенденты были пересозданы в системе.<br>
            <span class='IPOLSDEK_warning'>Внимание!</span> После импорта крайне не рекомендуется переопределять города (сервисные свойства -> Переопределить города)! Чревато возникновением дублей и Конфликтующих городов!
            </p>
	    </div>
	</div>
";

	// LEGACY

$MESS['IPOLSDEK_FAQ_CONVERT_TITLE'] = "- Конвертация Интернет-магазина";
$MESS['IPOLSDEK_FAQ_CONVERT_DESCR'] = "Конвертация магазина в нечто новое на момент выпуска обновления 1.5.1 находилось в стадии тестирования, поэтому <strong>крайне не рекомендуется</strong> конвертировать рабочий магазин, так как корректная работа модуля не гарантируется. Алгоритм работы модуля Интернет-магазина меняется с каждым обновлением, поэтому порядок настройки служб доставки тоже может меняться.<br>Если же конвертация была произведена - необходимо вручную создать <a href='/bitrix/admin/sale_delivery_service_list.php' target='_blank'>службы доставки</a>.<br><br>
<strong>Процесс создания</strong> (может отличаться при разности версий Интернет-магазина):
<ol>
	<li>В списке служб доставки нажать кнопку \"Добавить\".</li>
	<li>Вкладка \"Общие настройки\": Название - \"Доставка СДЭК\" (или другое подходящее), Тип доставки - Автоматизированная, Активность - галочка проставлена.</li>
	<li>Вкладка \"Настройки обработчика\": Служба доставки - СДЭК [sdek].</li>
	<li>Нажимаем кнопку \"Применить\" (не сохранить: наша работа еще не закончена!).</li>
	<li>Вкладка \"Профили\": кнопка \"Добавить профиль\".</li>
	<li>Вкладка \"Общие настройки\": Название - \"Курьер\" (или другое подходящее), Активность - галочка проставлена, Логотип - об этом ниже.</li>
	<li>Вкладка \"Свойства обработчика\": Профиль - Доставка курьером.</li>
	<li>Жмем \"Применить\", повторяем процедуру для профиля Самовывоз.</li>
</ol>
Обратите внимание, что наценки и ограничения, задаваемые в настройках автоматизированной службы доставки <strong>обрабатываются на стороне Битрикса</strong>, а не модуля.<br><br>
Логотипы для профилей доставки (нужно сохранить к себе на компьютер и загрузить в настройки профиля):<br>
<table style='text-align:center'><tr><td>Курьер</td><td>Самовывоз</td></tr><tr><td><img src='/bitrix/images/ipol.sdek/courier.png'></td><td><img src='/bitrix/images/ipol.sdek/pickup.png'></td></tr></table>";
?>