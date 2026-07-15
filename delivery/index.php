<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Доставка по всей России — КОСМАМЕД");
$APPLICATION->SetTitle("Доставка");
?>
<div class="km-delivery-page">

<div class="km-delivery-page__intro">
	<p>Мы доставляем медицинское оборудование и расходные материалы по всей России. Каждый заказ проходит через наш склад: от приёмки у производителя до передачи в транспортную компанию. Ниже — как устроен процесс отгрузки вашего заказа.</p>
</div>

<h2 class="km-delivery-page__section-title">Как мы готовим ваш заказ</h2>

<ol class="km-delivery-steps">
	<li class="km-delivery-step">
		<div class="km-delivery-step__head">
			<span class="km-delivery-step__num">1</span>
			<span class="km-delivery-step__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24"><path d="M20 8h-3V4H3c-1.1 0-2 .9-2 2v11h2c0 1.66 1.34 3 3 3s3-1.34 3-3h6c0 1.66 1.34 3 3 3s3-1.34 3-3h2v-5l-3-4zM6 18.5c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zm13.5-9l1.96 2.5H17V9.5h2.5zm-1.5 9c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5z"/></svg>
			</span>
		</div>
		<h3 class="km-delivery-step__title">Поставка от производителя</h3>
		<p class="km-delivery-step__text">Товар поступает к нам напрямую с завода или от официального дистрибьютора. Работаем только с проверенными каналами — на склад попадает оригинальная продукция с регистрационным удостоверением и полным комплектом документов.</p>
	</li>

	<li class="km-delivery-step">
		<div class="km-delivery-step__head">
			<span class="km-delivery-step__num">2</span>
			<span class="km-delivery-step__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4zm7 0h-1.05c1.45 1.19 2.05 2.53 2.05 3.64V20h4v-2c0-2.66-5.33-4-8-4z"/></svg>
			</span>
		</div>
		<h3 class="km-delivery-step__title">Бережная приёмка</h3>
		<p class="km-delivery-step__text">Сотрудники склада принимают каждую позицию вручную: сверяют наименование, количество и целостность упаковки. Любые расхождения сразу фиксируются в учётной системе — бракованный или повреждённый товар не попадает в продажу.</p>
	</li>

	<li class="km-delivery-step">
		<div class="km-delivery-step__head">
			<span class="km-delivery-step__num">3</span>
			<span class="km-delivery-step__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24"><path d="M4 20h16v-2H4v2zM20 7h-3V4c0-1.1-.9-2-2-2H9c-1.1 0-2 .9-2 2v3H4c-1.1 0-2 .9-2 2v9c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V9c0-1.1-.9-2-2-2zM9 4h6v3H9V4zm11 14H4v-7h16v7z"/></svg>
			</span>
		</div>
		<h3 class="km-delivery-step__title">Хранение на складе</h3>
		<p class="km-delivery-step__text">После приёмки товар размещают на стеллажах в сухом, отапливаемом помещении. Медицинское оборудование и расходные материалы хранятся в условиях, рекомендованных производителем, с соблюдением товарного соседства.</p>
	</li>

	<li class="km-delivery-step">
		<div class="km-delivery-step__head">
			<span class="km-delivery-step__num">4</span>
			<span class="km-delivery-step__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24"><path d="M19 8H5c-1.66 0-3 1.34-3 3v6h4v4h12v-4h4v-6c0-1.66-1.34-3-3-3zm-3 11H8v-5h8v5zm3-7c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-1-9H6v4h12V3z"/></svg>
			</span>
		</div>
		<h3 class="km-delivery-step__title">Сборочный лист</h3>
		<p class="km-delivery-step__text">Когда заказ поступает в работу, система автоматически формирует и печатает сборочный лист: перечень позиций, артикулы, количество и адреса ячеек хранения на складе. Это исключает ошибки при комплектации.</p>
	</li>

	<li class="km-delivery-step">
		<div class="km-delivery-step__head">
			<span class="km-delivery-step__num">5</span>
			<span class="km-delivery-step__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24"><path d="M17 10.5V7c0-.55-.45-1-1-1H4c-.55 0-1 .45-1 1v10c0 .55.45 1 1 1h12c.55 0 1-.45 1-1v-3.5l4 4v-11l-4 4z"/></svg>
			</span>
		</div>
		<h3 class="km-delivery-step__title">Сборка под камерой</h3>
		<p class="km-delivery-step__text">Комплектация заказа выполняется строго по сборочному листу и фиксируется видеокамерами на складе. Так мы контролируем точность и можем при необходимости восстановить детали процесса.</p>
	</li>

	<li class="km-delivery-step">
		<div class="km-delivery-step__head">
			<span class="km-delivery-step__num">6</span>
			<span class="km-delivery-step__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24"><path d="M12 1L3 5v6c0 5.55 3.84 10.74 9 12 5.16-1.26 9-6.45 9-12V5l-9-4zm-2 16l-4-4 1.41-1.41L10 14.17l6.59-6.59L18 9l-8 8z"/></svg>
			</span>
		</div>
		<h3 class="km-delivery-step__title">Проверка комплектации</h3>
		<p class="km-delivery-step__text">Перед упаковкой каждый заказ проходит финальный контроль: проверяют комплектность, серийные номера, целостность упаковки и внешний вид. Отправляется только то, что соответствует заказу и стандартам качества.</p>
	</li>

	<li class="km-delivery-step">
		<div class="km-delivery-step__head">
			<span class="km-delivery-step__num">7</span>
			<span class="km-delivery-step__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24"><path d="M20 6h-4V4c0-1.11-.89-2-2-2h-4c-1.11 0-2 .89-2 2v2H4c-1.11 0-1.99.89-1.99 2L2 19c0 1.11.89 2 2 2h16c1.11 0 2-.89 2-2V8c0-1.11-.89-2-2-2zm-6 0h-4V4h4v2z"/></svg>
			</span>
		</div>
		<h3 class="km-delivery-step__title">Упаковка и документы</h3>
		<p class="km-delivery-step__text">Заказ упаковывают в жёсткую коробку, при необходимости добавляют амортизирующий материал и маркировку «Хрупкое». Внутрь вкладывают сопроводительную документацию: накладную, УПД и гарантийные материалы.</p>
	</li>

	<li class="km-delivery-step">
		<div class="km-delivery-step__head">
			<span class="km-delivery-step__num">8</span>
			<span class="km-delivery-step__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24"><path d="M18.92 6.01C18.72 5.42 18.16 5 17.5 5h-11c-.66 0-1.21.42-1.42 1.01L3 12v8c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-1h12v1c0 .55.45 1 1 1h1c.55 0 1-.45 1-1v-8l-2.08-5.99zM6.5 16c-.83 0-1.5-.67-1.5-1.5S5.67 13 6.5 13s1.5.67 1.5 1.5S7.33 16 6.5 16zm11 0c-.83 0-1.5-.67-1.5-1.5s.67-1.5 1.5-1.5 1.5.67 1.5 1.5-.67 1.5-1.5 1.5zM5 11l1.5-4.5h11L19 11H5z"/></svg>
			</span>
		</div>
		<h3 class="km-delivery-step__title">Передача в транспортную компанию</h3>
		<p class="km-delivery-step__text">Логист передаёт упакованный заказ в терминал выбранной транспортной службы. Отгрузка выполняется в течение 1–3 рабочих дней после подтверждения и оплаты заказа.</p>
	</li>

	<li class="km-delivery-step">
		<div class="km-delivery-step__head">
			<span class="km-delivery-step__num">9</span>
			<span class="km-delivery-step__icon" aria-hidden="true">
				<svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
			</span>
		</div>
		<h3 class="km-delivery-step__title">Трек-номер на почту</h3>
		<p class="km-delivery-step__text">После передачи заказа в транспортную компанию мы отправляем вам письмо с трек-номером и ссылкой для отслеживания посылки. Дальнейшее информирование о статусе доставки — от службы перевозчика.</p>
	</li>
</ol>

<h2 class="km-delivery-page__section-title">Транспортные компании</h2>

<div class="km-delivery-partners">
	<img width="180" height="58" alt="Деловые Линии" src="/upload/medialibrary/a60/vqxv5zz643qf70m382wzdokh14w5z0zc.jpg" loading="lazy">
	<img width="180" height="58" alt="СДЭК" src="/upload/medialibrary/e79/1zsl6k93guxc7g9r7622ltadmtxmg39l.jpg" loading="lazy">
</div>

<div class="km-delivery-note">
	Если нужна доставка транспортной компанией, которой нет в списке, — сообщите об этом менеджеру при оформлении заказа. Мы организуем отправку через вашего перевозчика.
</div>

<p>Стоимость доставки в ваш город рассчитывается автоматически — вы увидите её на странице оформления заказа. Доступен самовывоз со склада в Воронеже.</p>

<p>Мы заботимся о сохранности товара: используем жёсткую упаковку, при необходимости — дополнительную амортизацию и маркировку хрупких грузов. Отправления страхуются от повреждений при транспортировке.</p>

<p>Вопросы по доставке: <a href="tel:+74991120845">+7 (499) 112-08-45</a>, <a href="mailto:info@kosmamed.ru">info@kosmamed.ru</a>.</p>

</div>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
