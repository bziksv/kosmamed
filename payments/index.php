<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetPageProperty("title", "Оплата товаров в интернет магазине КОСМАМЕД");
$APPLICATION->SetTitle("Способы оплаты");?> <?$APPLICATION->IncludeComponent(
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


<p>
	После оплаты денежные средства поступают <strong>в течение 1 РАБОЧЕГО дня</strong>, если статус заказа не изменился на оплаченный, то просьба позвонить по телефону: <a href="tel:74991120845">+7 (499) 112-08-45</a> или написать на почту <a href="mailto:info@kosmamed.ru">info@kosmamed.ru</a>
</p>
<p>Отгрузка товара происходит в течении 1-3 рабочих дней, службой доставки выбранной при оформлении заказа.</p>
<p>Если возникнет необходимость менеджер дополнительно свяжется с Вами по указанному номеру или электронной почте. Если Вы допустили ошибку при оформлении заказа, например ошиблись в адресе доставки, то напишите на почту: <a href="mailto:info@kosmamed.ru">info@kosmamed.ru</a> обязательно указав Ваш номер заказа в теме письма.</p>
<p>Вместе с товаром направляется чек/товарная накладная/упд, по которому предоставляется гарантия. Информирование о статусе доставки осуществляется курьерской или транспортной компанией после передачи Вашего заказа к ним. Договор оферы:  <a href="https://kosmamed.ru/upload/oferta.pdf" target="_blank" >https://kosmamed.ru/upload/oferta.pdf</a>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>