<?php
/**
 * КосмаМед — live-поиск (быстрая выпадашка).
 * Подстрочный поиск по товарам и категориям инфоблока 24
 * с коррекцией опечаток (mb-Levenshtein по словарю названий).
 * Отдаёт готовый HTML: две колонки — категории слева, товары справа.
 */
define("STOP_STATISTICS", true);
define("NO_AGENT_CHECK", true);
define("DisableEventsCheck", true);
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_before.php");

header("Content-Type: text/html; charset=UTF-8");
header("X-Robots-Tag: noindex");

const KS_IBLOCK_ID      = 24;
const KS_PRICE_NAMES    = array("Цена - КосмаМед Сайт", "Цена - Медмаркет Сайт");
const KS_MAX_PRODUCTS   = 16;  // в выпадашке (для фильтра по категориям)
const KS_FETCH_PRODUCTS = 80;  // кандидаты до сортировки
const KS_MAX_CATEGORIES = 10;
const KS_MIN_LEN        = 2;
const KS_DICT_TTL       = 86400; // сутки

if (!CModule::IncludeModule("iblock")) { echo ""; die(); }
$hasCatalog = CModule::IncludeModule("catalog");

$q = isset($_REQUEST["q"]) ? trim((string)$_REQUEST["q"]) : "";
$q = mb_substr($q, 0, 60, "UTF-8");

if (mb_strlen($q, "UTF-8") < KS_MIN_LEN) { echo ""; die(); }

/* ---------- утилиты ---------- */

function ks_words($string) {
	$string = mb_strtolower($string, "UTF-8");
	$parts = preg_split('~[^\p{L}\p{Nd}]+~u', $string, -1, PREG_SPLIT_NO_EMPTY);
	return is_array($parts) ? $parts : array();
}

/** GetNext() уже html-escapes поля — декодируем перед повторным выводом */
function ks_plain($value) {
	return htmlspecialcharsback((string)$value);
}

/** Подсветка совпадений запроса в названии (как <b> у Bitrix/polimer) */
function ks_highlight_name($name, $query) {
	$name = (string)$name;
	$query = trim((string)$query);
	$safe = htmlspecialcharsbx($name);
	if ($query === "") {
		return $safe;
	}
	$tokens = preg_split('/[\s,]+/u', mb_strtolower($query, "UTF-8"), -1, PREG_SPLIT_NO_EMPTY);
	$tokens = array_values(array_unique(array_filter($tokens, static function ($t) {
		return mb_strlen($t, "UTF-8") >= 2;
	})));
	usort($tokens, static function ($a, $b) {
		return mb_strlen($b, "UTF-8") - mb_strlen($a, "UTF-8");
	});
	foreach ($tokens as $token) {
		$quoted = preg_quote($token, "/");
		$flex = '(?:ы|и|а|я|е|у|ов|ев|ей|ам|ями|ах|ом|ем)?';
		$safe = preg_replace("/(".$quoted.$flex.")/iu", "<b>$1</b>", $safe);
	}
	return $safe;
}

/**
 * Ослабленные наборы слов: убираем хвост/начало, потом одиночные длинные токены.
 * Как polimerBuildRelaxedSearchQueries.
 */
function ks_relaxed_word_sets(array $words) {
	$words = array_values(array_filter($words, static function ($w) {
		return mb_strlen((string)$w, "UTF-8") >= 2;
	}));
	if (count($words) < 2) {
		return array();
	}
	$sets = array();
	for ($len = count($words) - 1; $len >= 1; $len--) {
		$sets[] = array_slice($words, 0, $len);
	}
	for ($start = 1; $start < count($words); $start++) {
		$sets[] = array_slice($words, $start);
	}
	$byLen = $words;
	usort($byLen, static function ($a, $b) {
		return mb_strlen($b, "UTF-8") - mb_strlen($a, "UTF-8");
	});
	foreach ($byLen as $w) {
		$sets[] = array($w);
	}
	$out = array();
	$seen = array();
	$orig = implode(" ", $words);
	foreach ($sets as $set) {
		$key = implode(" ", $set);
		if ($key === "" || $key === $orig || isset($seen[$key])) {
			continue;
		}
		$seen[$key] = true;
		$out[] = $set;
	}
	return $out;
}

/** Категории из найденных товаров (как polimerBuildSearchSectionsFromProducts) */
function ks_sections_from_products(array $products) {
	$counts = array();
	foreach ($products as $p) {
		$sid = (int)($p["SECTION_ID"] ?? 0);
		if ($sid > 0) {
			$counts[$sid] = ($counts[$sid] ?? 0) + 1;
		}
	}
	if (empty($counts)) {
		return array();
	}
	$res = array();
	$rs = CIBlockSection::GetList(
		array("NAME" => "ASC"),
		array("IBLOCK_ID" => KS_IBLOCK_ID, "ID" => array_keys($counts), "GLOBAL_ACTIVE" => "Y"),
		false,
		array("ID", "NAME", "CODE", "PICTURE")
	);
	while ($s = $rs->GetNext()) {
		$id = (int)$s["ID"];
		$img = "";
		if ($s["PICTURE"] > 0) {
			$f = CFile::ResizeImageGet($s["PICTURE"], array("width" => 48, "height" => 48), BX_RESIZE_IMAGE_EXACT, true);
			$img = $f["src"];
		}
		$res[] = array(
			"ID"      => $id,
			"NAME"    => ks_plain($s["NAME"]),
			"URL"     => "/catalog/".$s["CODE"]."/",
			"CNT"     => (int)($counts[$id] ?? 0),
			"CNT_AVL" => (int)($counts[$id] ?? 0),
			"IMG"     => $img,
			"FROM_PRODUCTS" => true,
		);
	}
	usort($res, static function ($a, $b) {
		return ($b["CNT"] <=> $a["CNT"]) ?: strcmp($a["NAME"], $b["NAME"]);
	});
	if (count($res) > KS_MAX_CATEGORIES) {
		$res = array_slice($res, 0, KS_MAX_CATEGORIES);
	}
	return $res;
}

// Левенштейн с поддержкой UTF-8 (стандартный levenshtein() работает по байтам)
function ks_mb_levenshtein($a, $b) {
	$a = preg_split('//u', $a, -1, PREG_SPLIT_NO_EMPTY);
	$b = preg_split('//u', $b, -1, PREG_SPLIT_NO_EMPTY);
	$la = count($a); $lb = count($b);
	if ($la === 0) return $lb;
	if ($lb === 0) return $la;
	$prev = range(0, $lb);
	for ($i = 1; $i <= $la; $i++) {
		$cur = array($i);
		for ($j = 1; $j <= $lb; $j++) {
			$cost = ($a[$i-1] === $b[$j-1]) ? 0 : 1;
			$cur[$j] = min($prev[$j] + 1, $cur[$j-1] + 1, $prev[$j-1] + $cost);
		}
		$prev = $cur;
	}
	return $prev[$lb];
}

/** Русская раскладка ↔ латиница (English typed on RU keyboard and vice versa) */
function ks_layout_map_ru_to_en() {
	static $map = null;
	if ($map !== null) {
		return $map;
	}
	$pairs = array(
		"й"=>"q","ц"=>"w","у"=>"e","к"=>"r","е"=>"t","н"=>"y","г"=>"u","ш"=>"i","щ"=>"o","з"=>"p",
		"х"=>"[","ъ"=>"]","ф"=>"a","ы"=>"s","в"=>"d","а"=>"f","п"=>"g","р"=>"h","о"=>"j","л"=>"k",
		"д"=>"l","ж"=>";","э"=>"'","я"=>"z","ч"=>"x","с"=>"c","м"=>"v","и"=>"b","т"=>"n","ь"=>"m",
		"б"=>",","ю"=>".","ё"=>"`",
		"Й"=>"Q","Ц"=>"W","У"=>"E","К"=>"R","Е"=>"T","Н"=>"Y","Г"=>"U","Ш"=>"I","Щ"=>"O","З"=>"P",
		"Х"=>"[","Ъ"=>"]","Ф"=>"A","Ы"=>"S","В"=>"D","А"=>"F","П"=>"G","Р"=>"H","О"=>"J","Л"=>"K",
		"Д"=>"L","Ж"=>";","Э"=>"'","Я"=>"Z","Ч"=>"X","С"=>"C","М"=>"V","И"=>"B","Т"=>"N","Ь"=>"M",
		"Б"=>",","Ю"=>".","Ё"=>"`",
	);
	$map = $pairs;
	return $map;
}

function ks_layout_swap($text, $direction = "ru_to_en") {
	$ruToEn = ks_layout_map_ru_to_en();
	$enToRu = array_flip($ruToEn);
	$map = ($direction === "en_to_ru") ? $enToRu : $ruToEn;
	$chars = preg_split('//u', $text, -1, PREG_SPLIT_NO_EMPTY);
	$out = "";
	foreach ($chars as $ch) {
		$out .= isset($map[$ch]) ? $map[$ch] : $ch;
	}
	return $out;
}

/**
 * Слова, набранные латиницей в русской раскладке (лфц → kaw, KaWe).
 * Русские слова из словаря каталога не трогаем (отоск → не jnjcr).
 */
function ks_word_in_dictionary($word, array $dict) {
	foreach ($dict as $dw) {
		if (mb_strpos($dw, $word, 0, "UTF-8") !== false) {
			return true;
		}
	}
	return false;
}

function ks_fix_layout_words(array $queryWords, array $dict) {
	$fixed = array();
	$changed = false;
	foreach ($queryWords as $word) {
		if (!preg_match('~[\p{Cyrillic}]~u', $word)) {
			$fixed[] = $word;
			continue;
		}
		if (ks_word_in_dictionary($word, $dict)) {
			$fixed[] = $word;
			continue;
		}
		$en = ks_layout_swap($word, "ru_to_en");
		$enLower = mb_strtolower($en, "UTF-8");
		if ($en !== $word && preg_match('~^[a-z0-9][a-z0-9._\-]*$~', $enLower)) {
			$fixed[] = $enLower;
			$changed = true;
		} else {
			$fixed[] = $word;
		}
	}
	return array("words" => $fixed, "changed" => $changed);
}

/**
 * Словарь слов из названий разделов и товаров ИБ24 (с кэшем в файле).
 * Используется только для коррекции опечаток.
 */
function ks_dictionary() {
	$cacheFile = $_SERVER["DOCUMENT_ROOT"]."/bitrix/cache/ks_search_dict.json";
	if (is_file($cacheFile) && (time() - filemtime($cacheFile) < KS_DICT_TTL)) {
		$data = json_decode(file_get_contents($cacheFile), true);
		if (is_array($data) && !empty($data)) return $data;
	}

	$words = array();
	$add = function($name) use (&$words) {
		foreach (ks_words($name) as $w) {
			if (mb_strlen($w, "UTF-8") >= 4) {
				$words[$w] = isset($words[$w]) ? $words[$w] + 1 : 1;
			}
		}
	};

	$rsS = CIBlockSection::GetList(array(), array("IBLOCK_ID" => KS_IBLOCK_ID), false, array("NAME"));
	while ($s = $rsS->Fetch()) { $add($s["NAME"]); }

	$rsE = CIBlockElement::GetList(
		array(),
		array("IBLOCK_ID" => KS_IBLOCK_ID, "ACTIVE" => "Y"),
		false,
		array("nTopCount" => 6000),
		array("ID", "NAME")
	);
	while ($e = $rsE->Fetch()) { $add($e["NAME"]); }

	$dict = array_keys($words);
	@file_put_contents($cacheFile, json_encode($dict, JSON_UNESCAPED_UNICODE));
	return $dict;
}

/**
 * Коррекция опечаток: для каждого слова запроса подбираем ближайшее слово
 * словаря, если точного вхождения нет. Возвращает массив [исправленные слова].
 */
function ks_correct_words(array $queryWords, array $dict) {
	$corrected = array();
	$changed = false;
	foreach ($queryWords as $word) {
		$len = mb_strlen($word, "UTF-8");
		if ($len < 3) { $corrected[] = $word; continue; }

		// если слово уже встречается как подстрока в словаре — не трогаем
		$exists = false;
		foreach ($dict as $dw) {
			if (mb_strpos($dw, $word, 0, "UTF-8") !== false) { $exists = true; break; }
		}
		if ($exists) { $corrected[] = $word; continue; }

		$maxDist = $len <= 4 ? 1 : ($len <= 7 ? 2 : 3);
		$best = null; $bestDist = PHP_INT_MAX;
		foreach ($dict as $dw) {
			$dl = mb_strlen($dw, "UTF-8");
			if (abs($dl - $len) > $maxDist) continue;
			$d = ks_mb_levenshtein($word, $dw);
			if ($d < $bestDist) { $bestDist = $d; $best = $dw; if ($d === 0) break; }
		}
		if ($best !== null && $bestDist <= $maxDist) {
			$corrected[] = $best;
			$changed = true;
		} else {
			$corrected[] = $word;
		}
	}
	return array("words" => $corrected, "changed" => $changed);
}

/* ---------- поиск ---------- */

function ks_section_in_stock_count($sectionId) {
	if (!CModule::IncludeModule("catalog")) {
		return 0;
	}
	$filter = array(
		"IBLOCK_ID"           => KS_IBLOCK_ID,
		"CHECK_PERMISSIONS"   => "Y",
		"MIN_PERMISSION"      => "R",
		"SECTION_ID"          => (int)$sectionId,
		"INCLUDE_SUBSECTIONS" => "Y",
		"ACTIVE"              => "Y",
		"ACTIVE_DATE"         => "Y",
		"AVAILABLE"           => "Y",
	);
	return (int)CIBlockElement::GetList(array(), $filter, array());
}

function ks_find_sections(array $words) {
	// CIBlockSection::GetList не поддерживает вложенные числовые подфильтры,
	// поэтому фильтруем по самому длинному слову через %NAME (подстрока),
	// а соответствие всем словам проверяем в PHP.
	usort($words, function($a, $b) {
		return mb_strlen($b, "UTF-8") - mb_strlen($a, "UTF-8");
	});
	$anchor = $words[0];

	$filter = array("IBLOCK_ID" => KS_IBLOCK_ID, "GLOBAL_ACTIVE" => "Y", "%NAME" => $anchor);
	$res = array();
	$rs = CIBlockSection::GetList(
		array("ELEMENT_CNT" => "DESC", "left_margin" => "ASC"),
		$filter, true,
		array("ID", "NAME", "CODE", "PICTURE", "ELEMENT_CNT")
	);
	while ($s = $rs->GetNext()) {
		if ($s["ELEMENT_CNT"] <= 0) continue;
		$nameLower = mb_strtolower($s["NAME"], "UTF-8");
		$allMatch = true;
		foreach ($words as $w) {
			if (mb_strpos($nameLower, $w, 0, "UTF-8") === false) { $allMatch = false; break; }
		}
		if (!$allMatch) continue;
		$img = "";
		if ($s["PICTURE"] > 0) {
			$f = CFile::ResizeImageGet($s["PICTURE"], array("width" => 60, "height" => 60), BX_RESIZE_IMAGE_PROPORTIONAL, true);
			$img = $f["src"];
		}
		$res[] = array(
			"ID"      => (int)$s["ID"],
			"NAME"    => ks_plain($s["NAME"]),
			"URL"     => "/catalog/".$s["CODE"]."/",
			"CNT"     => (int)$s["ELEMENT_CNT"],
			"CNT_AVL" => ks_section_in_stock_count((int)$s["ID"]),
			"IMG"     => $img,
		);
		if (count($res) >= KS_MAX_CATEGORIES) break;
	}
	return $res;
}

function ks_product_catalog_row($productId) {
	$row = array(
		"ID"             => (int)$productId,
		"CAN_BUY"        => false,
		"CHECK_QUANTITY" => false,
		"QUANTITY"       => 0.0,
		"MIN_QUANTITY"   => 1.0,
		"HAS_OFFERS"     => false,
	);
	if (!CModule::IncludeModule("catalog")) {
		return $row;
	}
	if (CCatalogSKU::IsExistOffers($productId)) {
		$row["HAS_OFFERS"] = true;
		return $row;
	}
	$cat = CCatalogProduct::GetByID($productId);
	if (!$cat) {
		return $row;
	}
	$row["CAN_BUY"] = ($cat["AVAILABLE"] === "Y");
	$row["CHECK_QUANTITY"] = ($cat["QUANTITY_TRACE"] === "Y");
	$row["QUANTITY"] = (float)$cat["QUANTITY"];
	$ratio = CCatalogMeasureRatio::getList(array(), array("PRODUCT_ID" => $productId))->Fetch();
	if ($ratio && (float)$ratio["RATIO"] > 0) {
		$row["MIN_QUANTITY"] = (float)$ratio["RATIO"];
	}
	return $row;
}

/**
 * Релевантность названия к запросу (меньше = лучше).
 * 0 — точное совпадение, 1 — целое слово, 2 — начало слова,
 * 3 — подстрока в имени, 4 — в имени нет (артикул / шум).
 */
function ks_score_name_relevance($name, $query) {
	$nameLower = mb_strtolower(trim((string)$name), "UTF-8");
	$queryLower = mb_strtolower(trim((string)$query), "UTF-8");
	if ($queryLower === "" || $nameLower === "") {
		return 4;
	}
	if ($nameLower === $queryLower) {
		return 0;
	}

	$tokens = preg_split('/[\s,]+/u', $queryLower, -1, PREG_SPLIT_NO_EMPTY);
	$tokens = array_values(array_filter($tokens, static function ($token) {
		return mb_strlen($token, "UTF-8") >= 2;
	}));
	if (empty($tokens)) {
		return 4;
	}

	$worst = 0;
	$anyInName = false;
	foreach ($tokens as $token) {
		$tokenScore = 4;
		if (mb_strpos($nameLower, $token) === false) {
			$worst = max($worst, $tokenScore);
			continue;
		}
		$anyInName = true;
		$quoted = preg_quote($token, "/");
		// целое слово или слово + короткая русская флексия (ланцет → ланцеты)
		$flex = '(?:ы|и|а|я|е|у|ов|ев|ей|ам|ями|ах|ом|ем)?';
		if (preg_match('/(?:^|[^\p{L}\p{N}])'.$quoted.$flex.'(?:[^\p{L}\p{N}]|$)/u', $nameLower)) {
			$tokenScore = 1;
		} elseif (preg_match('/(?:^|[^\p{L}\p{N}])'.$quoted.'/u', $nameLower)) {
			$tokenScore = 2;
		} else {
			$tokenScore = 3;
		}
		$worst = max($worst, $tokenScore);
	}

	return $anyInName ? $worst : 4;
}

/** available | order | unavailable — как на polimer / кнопке «Под заказ» в каталоге */
function ks_stock_status(array $p) {
	$price = (float)($p["PRICE"] ?? 0);
	$canBuy = !empty($p["CAN_BUY"]);
	if ($canBuy && $price > 0) {
		return "available";
	}
	if ($price > 0) {
		return "order";
	}
	return "unavailable";
}

function ks_stock_label(array $p) {
	$status = $p["STOCK_STATUS"] ?? ks_stock_status($p);
	if ($status === "order") {
		return array("class" => "ks-item__stock--order", "text" => "Под заказ");
	}
	if ($status === "unavailable" || empty($p["CAN_BUY"])) {
		return array("class" => "ks-item__stock--no", "text" => "Нет в наличии");
	}
	if (!empty($p["CHECK_QUANTITY"])) {
		$qty = (int)$p["QUANTITY"];
		if ($qty <= 0) {
			return array("class" => "ks-item__stock--order", "text" => "Под заказ");
		}
		return array("class" => "ks-item__stock--yes", "text" => "В наличии: ".$qty." шт.");
	}
	return array("class" => "ks-item__stock--yes", "text" => "В наличии");
}

/**
 * Сначала в наличии (релевантность → цена ↑), затем под заказ, затем без цены.
 */
function ks_sort_products_by_availability_and_price(array $products, $query) {
	$available = array();
	$order = array();
	$unavailable = array();

	foreach ($products as $p) {
		$status = $p["STOCK_STATUS"] ?? ks_stock_status($p);
		if ($status === "available") {
			$available[] = $p;
		} elseif ($status === "order") {
			$order[] = $p;
		} else {
			$unavailable[] = $p;
		}
	}

	$query = trim((string)$query);
	$cmp = static function (array $a, array $b) use ($query) {
		if ($query !== "") {
			$rel = ks_score_name_relevance($a["NAME"] ?? "", $query)
				<=> ks_score_name_relevance($b["NAME"] ?? "", $query);
			if ($rel !== 0) {
				return $rel;
			}
		}
		$priceA = isset($a["PRICE"]) && (float)$a["PRICE"] > 0 ? (float)$a["PRICE"] : PHP_FLOAT_MAX;
		$priceB = isset($b["PRICE"]) && (float)$b["PRICE"] > 0 ? (float)$b["PRICE"] : PHP_FLOAT_MAX;
		$priceCmp = $priceA <=> $priceB;
		if ($priceCmp !== 0) {
			return $priceCmp;
		}
		return strcmp((string)($a["NAME"] ?? ""), (string)($b["NAME"] ?? ""));
	};

	usort($available, $cmp);
	usort($order, $cmp);
	usort($unavailable, $cmp);

	return array_merge($available, $order, $unavailable);
}

function ks_find_products(array $words, $priceTypeId, $queryForRank = "") {
	$filter = array(
		"IBLOCK_ID"            => KS_IBLOCK_ID,
		"ACTIVE"               => "Y",
		"ACTIVE_DATE"          => "Y",
		"SECTION_GLOBAL_ACTIVE"=> "Y",
	);
	foreach ($words as $w) {
		$filter[] = array("LOGIC" => "OR", "%NAME" => $w, "%PROPERTY_CML2_ARTICLE" => $w);
	}

	$raw = array();
	$ids = array();
	$rs = CIBlockElement::GetList(
		array("SORT" => "ASC", "SHOW_COUNTER" => "DESC"),
		$filter, false,
		array("nTopCount" => KS_FETCH_PRODUCTS),
		array("ID", "NAME", "DETAIL_PAGE_URL", "PREVIEW_PICTURE", "DETAIL_PICTURE", "IBLOCK_SECTION_ID", "PROPERTY_CML2_ARTICLE")
	);
	while ($e = $rs->GetNext()) {
		$id = (int)$e["ID"];
		$pictId = $e["PREVIEW_PICTURE"] ?: $e["DETAIL_PICTURE"];
		$img = "";
		if ($pictId > 0) {
			$f = CFile::ResizeImageGet($pictId, array("width" => 70, "height" => 70), BX_RESIZE_IMAGE_PROPORTIONAL, true);
			$img = $f["src"];
		}
		$raw[$id] = array(
			"ID"         => $id,
			"NAME"       => ks_plain($e["NAME"]),
			"URL"        => $e["DETAIL_PAGE_URL"],
			"IMG"        => $img,
			"ARTICLE"    => ks_plain($e["PROPERTY_CML2_ARTICLE_VALUE"]),
			"SECTION_ID" => (int)$e["IBLOCK_SECTION_ID"],
		);
		$ids[] = $id;
	}

	if (empty($ids)) {
		return array();
	}

	$prices = array();
	if ($priceTypeId > 0 && CModule::IncludeModule("catalog")) {
		$rp = CPrice::GetList(
			array(),
			array("PRODUCT_ID" => $ids, "CATALOG_GROUP_ID" => $priceTypeId)
		);
		while ($p = $rp->Fetch()) {
			$pid = (int)$p["PRODUCT_ID"];
			$price = (float)$p["PRICE"];
			// 888888888 — служебная заглушка «цены нет» (как в шаблонах каталога)
			if ($price >= 888888888) {
				$price = 0;
			}
			if (!isset($prices[$pid]) || ($price > 0 && ($prices[$pid] <= 0 || $price < $prices[$pid]))) {
				$prices[$pid] = $price;
			}
		}
	}

	$res = array();
	foreach ($ids as $id) {
		$row = $raw[$id];
		$price = isset($prices[$id]) ? (float)$prices[$id] : 0;
		$cat = ks_product_catalog_row($id);
		$item = array(
			"ID"             => $id,
			"NAME"           => $row["NAME"],
			"URL"            => $row["URL"],
			"IMG"            => $row["IMG"],
			"ARTICLE"        => $row["ARTICLE"],
			"SECTION_ID"     => (int)$row["SECTION_ID"],
			"PRICE"          => $price,
			"CAN_BUY"        => $cat["CAN_BUY"] && $price > 0,
			"CHECK_QUANTITY" => $cat["CHECK_QUANTITY"],
			"QUANTITY"       => $cat["QUANTITY"],
			"MIN_QUANTITY"   => $cat["MIN_QUANTITY"],
			"HAS_OFFERS"     => $cat["HAS_OFFERS"],
		);
		$item["STOCK_STATUS"] = ks_stock_status($item);
		$res[] = $item;
	}

	$rankQuery = $queryForRank !== "" ? $queryForRank : implode(" ", $words);
	$res = ks_sort_products_by_availability_and_price($res, $rankQuery);

	// В выпадашке — только с ценой: сначала наличие, потом под заказ (без заглушек)
	$withPrice = array();
	foreach ($res as $item) {
		if (($item["STOCK_STATUS"] ?? "") === "unavailable") {
			continue;
		}
		$withPrice[] = $item;
		if (count($withPrice) >= KS_MAX_PRODUCTS) {
			break;
		}
	}
	return $withPrice;
}

function ks_price_type_id() {
	static $id = null;
	if ($id !== null) {
		return $id;
	}
	$id = 0;
	if (!CModule::IncludeModule("catalog")) {
		return $id;
	}
	foreach (KS_PRICE_NAMES as $name) {
		$rsG = CCatalogGroup::GetListEx(array(), array("NAME" => $name), false, false, array("ID"));
		if ($g = $rsG->Fetch()) {
			$id = (int)$g["ID"];
			return $id;
		}
	}
	return $id;
}

/* ---------- сбор данных ---------- */

$priceTypeId = $hasCatalog ? ks_price_type_id() : 0;

$queryWords = ks_words($q);
if (empty($queryWords)) { echo ""; die(); }

$rankQuery = implode(" ", $queryWords);
$effectiveQ = $q;
$suggestNote = "";
$suggestRelaxed = false;

$sections = ks_find_sections($queryWords);
$products = ks_find_products($queryWords, $priceTypeId, $rankQuery);

if (empty($sections) && empty($products)) {
	$dict = ks_dictionary();
	$layout = ks_fix_layout_words($queryWords, $dict);
	if ($layout["changed"]) {
		$rankQuery = implode(" ", $layout["words"]);
		$effectiveQ = $rankQuery;
		$sections = ks_find_sections($layout["words"]);
		$products = ks_find_products($layout["words"], $priceTypeId, $rankQuery);
		if (!empty($sections) || !empty($products)) {
			$suggestNote = "Исправлено: показаны результаты по запросу «".htmlspecialcharsbx($effectiveQ)."»";
		}
	}
}
if (empty($sections) && empty($products)) {
	$corr = ks_correct_words($queryWords, $dict ?? ks_dictionary());
	if ($corr["changed"]) {
		$rankQuery = implode(" ", $corr["words"]);
		$effectiveQ = $rankQuery;
		$sections = ks_find_sections($corr["words"]);
		$products = ks_find_products($corr["words"], $priceTypeId, $rankQuery);
		if (!empty($sections) || !empty($products)) {
			$suggestNote = "Исправлено: показаны результаты по запросу «".htmlspecialcharsbx($effectiveQ)."»";
		}
	}
}
if (empty($sections) && empty($products)) {
	foreach (ks_relaxed_word_sets($queryWords) as $relaxedWords) {
		$rankQuery = implode(" ", $relaxedWords);
		$sections = ks_find_sections($relaxedWords);
		$products = ks_find_products($relaxedWords, $priceTypeId, $rankQuery);
		if (!empty($sections) || !empty($products)) {
			$effectiveQ = $rankQuery;
			$suggestRelaxed = true;
			$suggestNote = "Точных совпадений по «".htmlspecialcharsbx($q)."» нет. Показаны ближайшие результаты по «".htmlspecialcharsbx($effectiveQ)."»";
			break;
		}
	}
}

// Категории из найденных товаров (как на polimer) — приоритетнее «голых» совпадений по имени раздела
$sectionsFromProducts = ks_sections_from_products($products);
if (!empty($sectionsFromProducts)) {
	$sections = $sectionsFromProducts;
}

$total = count($sections) + count($products);
$qEsc = htmlspecialcharsbx($q);
$effectiveEsc = htmlspecialcharsbx($effectiveQ);
$allUrl = "/catalog/?q=".rawurlencode($effectiveQ);

/* ---------- рендер ---------- */
ob_start();
if ($total === 0) { ?>
	<div class="ks-suggest__empty">По запросу «<?=$qEsc?>» ничего не найдено</div>
<?php } else { ?>
	<div class="ks-suggest__root" data-query="<?=$qEsc?>" data-query-effective="<?=$effectiveEsc?>">
	<?php if ($suggestNote !== ""): ?>
		<div class="ks-suggest__note<?=$suggestRelaxed ? " ks-suggest__note--relaxed" : ""?>"><?=$suggestNote?></div>
	<?php endif; ?>
	<div class="ks-suggest__cols<?=(empty($sections) || empty($products)) ? " ks-suggest__cols--single" : ""?>">
		<div class="ks-suggest__col ks-suggest__col--cats">
			<div class="ks-suggest__title">
				Категории
				<?php if (!empty($sectionsFromProducts)): ?>
					<span class="ks-suggest__hint">можно выбрать несколько</span>
				<?php endif; ?>
			</div>
			<?php if (empty($sections)): ?>
				<div class="ks-suggest__none">Нет подходящих категорий</div>
			<?php else: foreach ($sections as $s):
				$sid = (int)$s["ID"];
				$sName = htmlspecialcharsbx($s["NAME"]);
			?>
				<div class="ks-cat" data-section-id="<?=$sid?>">
					<button type="button" class="ks-cat__filter"
						data-section-id="<?=$sid?>"
						data-section-name="<?=$sName?>"
						title="Показать товары из «<?=$sName?>»">
						<span class="ks-cat__check" aria-hidden="true"><i class="fa fa-check"></i></span>
						<?php if (!empty($s["IMG"])): ?>
							<span class="ks-cat__img"><img src="<?=htmlspecialcharsbx($s["IMG"])?>" alt="" width="40" height="40" loading="lazy" /></span>
						<?php endif; ?>
						<span class="ks-cat__name"><?=$sName?></span>
						<span class="ks-cat__cnt" title="<?=(int)$s["CNT"]?> в выдаче">
							<?=(int)$s["CNT"]?> шт.
						</span>
					</button>
					<a class="ks-cat__go" href="<?=htmlspecialcharsbx($s["URL"])?>" title="Перейти в раздел">
						<i class="fa fa-external-link" aria-hidden="true"></i>
					</a>
				</div>
			<?php endforeach; endif; ?>
		</div>
		<div class="ks-suggest__col ks-suggest__col--items">
			<div class="ks-suggest__products-head">
				<div class="ks-suggest__title">
					Товары
					<span class="ks-suggest__count" data-total="<?=count($products)?>"><?=count($products)?></span>
				</div>
				<div class="ks-suggest__filter-bar" hidden></div>
			</div>
			<?php if (empty($products)): ?>
				<div class="ks-suggest__none">Нет подходящих товаров</div>
			<?php else:
				$prevStatus = "";
				foreach ($products as $p):
				$status = $p["STOCK_STATUS"] ?? ks_stock_status($p);
				if ($status === "order" && $prevStatus !== "order"): ?>
				<div class="ks-suggest__sep" data-stock-sep="order">Под заказ</div>
				<?php elseif ($status === "unavailable" && $prevStatus !== "unavailable"): ?>
				<div class="ks-suggest__sep" data-stock-sep="unavailable">Нет в наличии</div>
				<?php endif;
				$prevStatus = $status;
				$stock = ks_stock_label($p);
				$canAdd = $p["CAN_BUY"] && !$p["HAS_OFFERS"] && $p["PRICE"] > 0;
				$props = "";
				if (!empty($p["ARTICLE"])) {
					$propsArr = array(array(
						"NAME"  => "Артикул",
						"CODE"  => "CML2_ARTICLE",
						"VALUE" => $p["ARTICLE"],
					));
					$props = strtr(base64_encode(serialize($propsArr)), "+/=", "-_,");
				}
				$nameHtml = ks_highlight_name($p["NAME"], $effectiveQ);
			?>
				<div class="ks-item ks-item--<?=htmlspecialcharsbx($status)?>" data-section-id="<?=(int)$p["SECTION_ID"]?>" data-stock-status="<?=htmlspecialcharsbx($status)?>" tabindex="-1">
					<a class="ks-item__link" href="<?=htmlspecialcharsbx($p["URL"])?>">
						<span class="ks-item__img">
							<?php if ($p["IMG"]): ?>
								<img src="<?=htmlspecialcharsbx($p["IMG"])?>" alt="" loading="lazy" />
							<?php else: ?>
								<img src="<?=SITE_TEMPLATE_PATH?>/images/no-photo.svg" alt="" />
							<?php endif; ?>
						</span>
						<span class="ks-item__info">
							<span class="ks-item__name"><?=$nameHtml?></span>
							<?php if (!empty($p["ARTICLE"])): ?>
								<span class="ks-item__art">Артикул: <?=htmlspecialcharsbx($p["ARTICLE"])?></span>
							<?php endif; ?>
							<span class="ks-item__code">Код товара: <?=(int)$p["ID"]?></span>
							<span class="ks-item__stock <?=$stock["class"]?>"><?=htmlspecialcharsbx($stock["text"])?></span>
						</span>
					</a>
					<div class="ks-item__side">
						<?php if ($p["PRICE"] > 0): ?>
							<span class="ks-item__price"><?=number_format($p["PRICE"], 0, ".", " ")?> ₽</span>
						<?php endif; ?>
						<?php if ($canAdd):
							$qtyStep = $p["MIN_QUANTITY"] > 0 ? $p["MIN_QUANTITY"] : 1;
							$qtyMax = $p["CHECK_QUANTITY"] ? max($qtyStep, (int)$p["QUANTITY"]) : 9999;
						?>
							<form action="/ajax/add2basket.php" class="ks-item__buy add2basket_form" method="post"
								data-step="<?=htmlspecialcharsbx($qtyStep)?>"
								data-max-qty="<?=htmlspecialcharsbx($qtyMax)?>">
								<input type="hidden" name="ID" value="<?=$p["ID"]?>" />
								<?php if ($props !== ""): ?>
									<input type="hidden" name="PROPS" value="<?=htmlspecialcharsbx($props)?>" />
								<?php endif; ?>
								<div class="ks-item__qnt">
									<a href="javascript:void(0)" class="minus ks-qty-minus" title="Меньше"><span>-</span></a>
									<input type="text" name="quantity" class="quantity" value="<?=htmlspecialcharsbx($qtyStep)?>" />
									<a href="javascript:void(0)" class="plus ks-qty-plus" title="Больше"><span>+</span></a>
								</div>
								<button type="button" class="btn_buy ks-item__cart" name="add2basket" title="В корзину">
									<i class="fa fa-shopping-cart"></i><span>В корзину</span>
								</button>
							</form>
						<?php elseif ($p["HAS_OFFERS"]): ?>
							<a class="ks-item__choose" href="<?=htmlspecialcharsbx($p["URL"])?>">Выбрать</a>
						<?php elseif ($status === "order"): ?>
							<a class="ks-item__choose" href="<?=htmlspecialcharsbx($p["URL"])?>">Под заказ</a>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; endif; ?>
			<div class="ks-suggest__filter-empty" hidden>В этой категории нет товаров по текущему запросу</div>
		</div>
	</div>
	<a class="ks-suggest__all" href="<?=htmlspecialcharsbx($allUrl)?>" data-url-all="<?=htmlspecialcharsbx($allUrl)?>">
		Показать все результаты по запросу «<?=$effectiveEsc?>»
	</a>
	</div>
<?php }
echo ob_get_clean();
die();
