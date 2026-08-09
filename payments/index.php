<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Оплата товаров в интернет-магазине КОСМАМЕД");
$APPLICATION->SetTitle("Способы оплаты");
?>
<div class="km-payments-page">

<div class="km-payments-page__intro">
	<p>Оплата заказов в КОСМАМЕД — для физических и юридических лиц, а также ИП. Товар отгружается после 100% оплаты. Ниже — доступные способы расчёта и важные сроки.</p>
</div>

<div class="km-payments-notice">
	<strong>Важно:</strong> работаем с <strong>физическими</strong> и <strong>юридическими лицами</strong>, а также ИП. Отгрузка — при полной оплате заказа.
</div>

<h2 class="km-payments-page__section-title">Доступные способы</h2>

<?$APPLICATION->IncludeComponent(
	"bitrix:catalog.section.list",
	"payments",
	Array(
		"ADD_SECTIONS_CHAIN" => "N",
		"CACHE_GROUPS" => "Y",
		"CACHE_TIME" => "36000000",
		"CACHE_TYPE" => "A",
		"COUNT_ELEMENTS" => "N",
		"IBLOCK_ID" => "10",
		"IBLOCK_TYPE" => "content",
		"SECTION_CODE" => "",
		"SECTION_FIELDS" => array(),
		"SECTION_ID" => "",
		"SECTION_URL" => "",
		"SECTION_USER_FIELDS" => array(),
		"SHOW_PARENT_NAME" => "",
		"TOP_DEPTH" => "2",
		"VIEW_MODE" => ""
	)
);?>

<h2 class="km-payments-page__section-title">Что важно знать</h2>

<ul class="km-payments-facts">
	<li class="km-payments-fact">
		<span class="km-payments-fact__icon" aria-hidden="true">
			<svg viewBox="0 0 24 24"><path d="M11.99 2C6.47 2 2 6.48 2 12s4.47 10 9.99 10C17.52 22 22 17.52 22 12S17.52 2 11.99 2zM12 20c-4.42 0-8-3.58-8-8s3.58-8 8-8 8 3.58 8 8-3.58 8-8 8zm.5-13H11v6l5.25 3.15.75-1.23-4.5-2.67z"/></svg>
		</span>
		<span class="km-payments-fact__title">Зачисление оплаты</span>
		<span class="km-payments-fact__text">Деньги поступают <strong>в течение 1 рабочего дня</strong>. Если статус заказа не изменился — позвоните <a href="tel:+74991120845">+7 (499) 112-08-45</a> или напишите на <a href="mailto:info@kosmamed.ru">info@kosmamed.ru</a>.</span>
	</li>
	<li class="km-payments-fact">
		<span class="km-payments-fact__icon" aria-hidden="true">
			<svg viewBox="0 0 24 24"><path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>
		</span>
		<span class="km-payments-fact__title">Отгрузка</span>
		<span class="km-payments-fact__text">Товар отправляем в течение <strong>1–3 рабочих дней</strong> выбранной при оформлении службой доставки. Подробнее — на странице <a href="/delivery/">«Доставка»</a>.</span>
	</li>
	<li class="km-payments-fact">
		<span class="km-payments-fact__icon" aria-hidden="true">
			<svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
		</span>
		<span class="km-payments-fact__title">Ошибка в заказе</span>
		<span class="km-payments-fact__text">Если ошиблись в адресе или данных — напишите на <a href="mailto:info@kosmamed.ru">info@kosmamed.ru</a> и укажите <strong>номер заказа</strong> в теме письма. При необходимости менеджер свяжется сам.</span>
	</li>
	<li class="km-payments-fact">
		<span class="km-payments-fact__icon" aria-hidden="true">
			<svg viewBox="0 0 24 24"><path d="M14 2H6c-1.1 0-1.99.9-1.99 2L4 20c0 1.1.89 2 1.99 2H18c1.1 0 2-.9 2-2V8l-6-6zm2 16H8v-2h8v2zm0-4H8v-2h8v2zm-3-5V3.5L18.5 9H13z"/></svg>
		</span>
		<span class="km-payments-fact__title">Документы и гарантия</span>
		<span class="km-payments-fact__text">С товаром идут чек / накладная / УПД — по ним действует <a href="/warranty/">гарантия</a>. Статус доставки сообщает транспортная компания после передачи заказа.</span>
	</li>
</ul>

<div class="km-payments-note">
	Договор оферты: <a href="https://kosmamed.ru/upload/oferta.pdf" target="_blank" rel="noopener">скачать PDF</a>.
</div>

<div class="km-payments-links">
	<a class="km-payments-link" href="/howtobuy/">
		<span class="km-payments-link__title">Как купить</span>
		<span class="km-payments-link__text">Пошаговое оформление заказа</span>
	</a>
	<a class="km-payments-link" href="/delivery/">
		<span class="km-payments-link__title">Доставка</span>
		<span class="km-payments-link__text">СДЭК, Деловые Линии, самовывоз</span>
	</a>
	<a class="km-payments-link" href="/warranty/">
		<span class="km-payments-link__title">Гарантия</span>
		<span class="km-payments-link__text">Сроки и условия производителя</span>
	</a>
</div>

<div class="km-payments-cta">
	<div class="km-payments-cta__title">Нужна помощь с оплатой?</div>
	<p class="km-payments-cta__text"><a href="tel:+74991120845">+7 (499) 112-08-45</a> · <a href="mailto:info@kosmamed.ru">info@kosmamed.ru</a></p>
	<div class="km-payments-cta__actions">
		<a class="km-payments-cta__btn" href="/catalog/">Перейти в каталог</a>
		<a class="km-payments-cta__btn km-payments-cta__btn--outline" href="/personal/cart/">Открыть корзину</a>
	</div>
</div>

</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
