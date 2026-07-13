<?include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/urlrewrite.php');

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");

CHTTP::SetStatus("404 Not Found");
@define("ERROR_404", "Y");

$APPLICATION->SetTitle("Страница не найдена — 404");
$APPLICATION->SetPageProperty("title", "Страница не найдена — 404 | КосмаМед");
$APPLICATION->SetPageProperty("description", "Запрашиваемая страница не найдена. Перейдите в каталог медицинского оборудования или на главную страницу интернет-магазина КосмаМед.");
?>

<div class="km-error-404">
	<div class="km-error-404__card">
		<div class="km-error-404__visual" aria-hidden="true">
			<svg class="km-error-404__svg" viewBox="0 0 320 240" role="img" focusable="false">
				<defs>
					<linearGradient id="km404-bg" x1="0%" y1="0%" x2="100%" y2="100%">
						<stop offset="0%" stop-color="#ecfeff"/>
						<stop offset="100%" stop-color="#e0f2fe"/>
					</linearGradient>
				</defs>
				<rect x="8" y="8" width="304" height="224" rx="20" fill="url(#km404-bg)"/>
				<circle cx="160" cy="118" r="72" fill="#fff" stroke="#bae6fd" stroke-width="2"/>
				<path d="M96 118c0-35.3 28.7-64 64-64s64 28.7 64 64" fill="none" stroke="#0e7490" stroke-width="3" stroke-linecap="round"/>
				<path d="M118 118h-14l-8 18h44l-10-18h-12z" fill="none" stroke="#155e75" stroke-width="3" stroke-linejoin="round"/>
				<circle cx="104" cy="118" r="9" fill="#fff" stroke="#155e75" stroke-width="3"/>
				<path d="M202 118h14l8 18h-44l10-18h12z" fill="none" stroke="#155e75" stroke-width="3" stroke-linejoin="round"/>
				<circle cx="216" cy="118" r="9" fill="#fff" stroke="#155e75" stroke-width="3"/>
				<path d="M128 148c8 10 18 15 32 15s24-5 32-15" fill="none" stroke="#0891b2" stroke-width="3" stroke-linecap="round"/>
				<path d="M88 170h144" fill="none" stroke="#7dd3fc" stroke-width="2" stroke-linecap="round" stroke-dasharray="4 6"/>
				<path d="M96 170c8-12 18-18 32-18 10 0 18 4 24 10 6-6 14-10 24-10 14 0 24 6 32 18" fill="none" stroke="#0e7490" stroke-width="2.5" stroke-linecap="round"/>
				<text x="160" y="62" text-anchor="middle" font-size="28" font-weight="700" fill="#155e75" font-family="Arial, sans-serif">404</text>
			</svg>
		</div>

		<div class="km-error-404__body">
			<p class="km-error-404__label">Диагноз: страница не найдена</p>
			<h1 class="km-error-404__title">Такой страницы нет в нашем каталоге</h1>
			<p class="km-error-404__text">
				Возможно, ссылка устарела, адрес набран с ошибкой или товар снят с продажи.
				Проверьте URL или воспользуйтесь поиском и каталогом — мы поможем найти нужное медоборудование.
			</p>

			<div class="km-error-404__actions">
				<a class="btn_buy km-error-404__btn" href="<?=SITE_DIR?>">
					<i class="fa fa-home" aria-hidden="true"></i>
					<span>На главную</span>
				</a>
				<a class="km-error-404__btn km-error-404__btn--secondary" href="<?=SITE_DIR?>catalog/">
					<i class="fa fa-th-large" aria-hidden="true"></i>
					<span>Каталог товаров</span>
				</a>
			</div>

			<form class="km-error-404__search" action="<?=SITE_DIR?>catalog/" method="get" role="search">
				<label class="km-error-404__search-label" for="km404-search">Или найдите нужный товар</label>
				<div class="km-error-404__search-row">
					<input id="km404-search" class="km-error-404__search-input" type="search" name="q" placeholder="Поиск по товарам и категориям" autocomplete="off">
					<button class="km-error-404__search-btn" type="submit">Найти</button>
				</div>
			</form>

			<div class="km-error-404__links">
				<span class="km-error-404__links-title">Популярные направления</span>
				<ul class="km-error-404__links-list">
					<li><a href="<?=SITE_DIR?>catalog/laboratornoe_oborudovanie/">Лабораторное оборудование</a></li>
					<li><a href="<?=SITE_DIR?>catalog/oftalmologiya/">Офтальмология</a></li>
					<li><a href="<?=SITE_DIR?>catalog/lor_oborudovanie_instrumenty/">ЛОР оборудование</a></li>
					<li><a href="<?=SITE_DIR?>catalog/raskhodnye_materialy_2/">Расходные материалы</a></li>
					<li><a href="<?=SITE_DIR?>contacts/">Контакты</a></li>
				</ul>
			</div>
		</div>
	</div>
</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>
