<?php

/************************ tab ***************************/
$MESS['VIAMODO_TSN_TAB_NAME'] = 'Настройки';
$MESS['VIAMODO_TSN_TAB_TITLE'] = 'Общие настройки';

$MESS['VIAMODO_TSN_OPTIONS_TOKEN'] = 'Токен бота Telegram';
$MESS['VIAMODO_TSN_OPTIONS_CHAT_IDS'] = 'ID чатов Telegram (через запятую)';

/************************ tab ***************************/
$MESS['VIAMODO_TSN_TAB_SITES_NAME'] = 'Сайты';
$MESS['VIAMODO_TSN_TAB_SITES_TITLE'] = 'Настройки по сайтам';

$MESS['VIAMODO_TSN_TAB_SITES_NOTE'] = 'Ниже выберите, о каких событиях необходимо отправлять уведомления';
$MESS['VIAMODO_TSN_TAB_SITES_BIND_NEW_ORDER'] = 'Новый заказ';
$MESS['VIAMODO_TSN_TAB_SITES_BIND_UPDATE_ORDER'] = 'Изменения в заказе';
$MESS['VIAMODO_TSN_TAB_SITES_BIND_ORDER_PAID'] = 'Оплата заказа';

/************************ tab ***************************/
$MESS['VIAMODO_TSN_TAB_SUBSCRIBERS_NAME'] = 'Подписчики';
$MESS['VIAMODO_TSN_TAB_SUBSCRIBERS_TITLE'] = 'Подписчики бота';

$MESS['VIAMODO_TSN_TAB_SUBSCRIBERS_TITLE_NEW_SUBSCRIBERS'] = 'Новые подписчики';
$MESS['VIAMODO_TSN_TAB_SUBSCRIBERS_BTN_UPDATE'] = 'Получить новых подписчиков';
$MESS['VIAMODO_TSN_TAB_SUBSCRIBERS_ERROR_NO_NEW_MESSAGES'] = 'Напишите любое сообщение своему боту, чтобы получить id новых сообщений';

$MESS['VIAMODO_TSN_TAB_SUBSCRIBERS_TABLE_THEAD_CHAT_ID'] = 'ID чата';
$MESS['VIAMODO_TSN_TAB_SUBSCRIBERS_TABLE_THEAD_NAME'] = 'Имя в Telegram';
$MESS['VIAMODO_TSN_TAB_SUBSCRIBERS_TABLE_THEAD_ADD'] = '';
$MESS['VIAMODO_TSN_TAB_SUBSCRIBERS_TABLE_THEAD_REMOVE'] = '';

$MESS['VIAMODO_TSN_TAB_SUBSCRIBERS_TABLE_TBODY_ADD'] = 'Добавить';
$MESS['VIAMODO_TSN_TAB_SUBSCRIBERS_TABLE_TBODY_REMOVE'] = 'Удалить';

$MESS['VIAMODO_TSN_TAB_SUBSCRIBERS_TITLE_CURRENT_SUBSCRIBERS'] = 'Текущие подписчики';
$MESS['VIAMODO_TSN_TAB_SUBSCRIBERS_ERROR_NO_CURRENT_SUBSCRIBERS'] = 'Пока подписчиков нет';

/************************ tab ***************************/
$MESS['VIAMODO_TSN_TAB_HELP_NAME'] = 'Инструкция';
$MESS['VIAMODO_TSN_TAB_HELP_TITLE'] = 'Инструкция по настройки модуля';

$MESS['VIAMODO_TSN_TAB_HELP_GET_TOKEN_TITLE'] = 'Получение token для Telegram бота';
$MESS['VIAMODO_TSN_TAB_HELP_GET_TOKEN_BODY'] = '
Для начала вам нужно открыть чат с ботом <a href="https://tele.gs/botfather" target="_blank">@BotFather</a><br>
Теперь пишем ему команду на создание нового бота "/newbot"<br>
@BotFather предлагает задать наименование нашего бота, например, "Viamodo Bot"<br>
Теперь бот просит задать ссылку на нашего бота, например, "viamodo_bot"<br>
В следующем сообщении @BotFather предоставит вам ваш секретный ключ.<br>
Этот ключ мы и записываем в настройки модуля.
';

$MESS['VIAMODO_TSN_TAB_HELP_GET_CHAT_IDS_TITLE'] = 'Добавление подписчиков';
$MESS['VIAMODO_TSN_TAB_HELP_GET_CHAT_IDS_BODY'] = '
Для добавления подписчиков переходим на вкладку "'.$MESS['VIAMODO_TSN_TAB_SUBSCRIBERS_NAME'].'" и нажимаем ссылку "<u style="white-space: nowrap;">'.$MESS['VIAMODO_TSN_TAB_SUBSCRIBERS_BTN_UPDATE'].'</u>"<br>
В таблице вы увидите пользователей, которые недавно писали вашему боту.<br>
Если список пуст, то вам нужно написать боту любое сообщение, например "привет".<br>
Для добавления нового подписчика просто нажмите кнопку "'.$MESS['VIAMODO_TSN_TAB_SUBSCRIBERS_TABLE_TBODY_ADD'].'" напротив нужного пользователя.
';
