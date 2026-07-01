/**
 * КосмаМед — live-поиск (выпадашка на всю ширину, две колонки).
 * Ходит в /ajax/search_suggest.php, который ищет по подстроке и
 * корректирует опечатки. Заменяет штатный JCTitleSearch.
 */
(function () {
	"use strict";

	function ready(fn) {
		if (document.readyState !== "loading") fn();
		else document.addEventListener("DOMContentLoaded", fn);
	}

	function initInput(input) {
		if (!input || input.__ksBound) return;
		input.__ksBound = true;

		var form = input.closest("form");
		var container = input.closest("#altop_search") || form || input;
		// ширина выпадашки = ширина основной контентной области шапки
		var anchor = input.closest(".center") || container;

		var panel = document.createElement("div");
		panel.className = "ks-suggest";
		panel.style.display = "none";
		document.body.appendChild(panel);

		var timer = null, lastQ = "", controller = null;

		function position() {
			var aRect = anchor.getBoundingClientRect();
			var iRect = input.getBoundingClientRect();
			panel.style.position = "absolute";
			panel.style.left = (aRect.left + window.pageXOffset) + "px";
			panel.style.width = aRect.width + "px";
			panel.style.top = (iRect.bottom + window.pageYOffset + 6) + "px";
		}
		function show() { position(); panel.style.display = "block"; }
		function hide() { panel.style.display = "none"; }

		function fetchSuggest(q) {
			var opts = {};
			if (window.AbortController) {
				if (controller) controller.abort();
				controller = new AbortController();
				opts.signal = controller.signal;
			}
			fetch("/ajax/search_suggest.php?q=" + encodeURIComponent(q), opts)
				.then(function (r) { return r.text(); })
				.then(function (html) {
					if (!html || !html.trim()) { hide(); return; }
					panel.innerHTML = html;
					show();
				})
				.catch(function () { /* отменённый запрос — игнор */ });
		}

		function onInput() {
			var q = input.value.trim();
			if (q.length < 2) { lastQ = ""; hide(); return; }
			if (q === lastQ) return;
			lastQ = q;
			clearTimeout(timer);
			timer = setTimeout(function () { fetchSuggest(q); }, 250);
		}

		input.addEventListener("input", onInput);
		input.addEventListener("focus", function () {
			if (input.value.trim().length >= 2 && panel.innerHTML.trim()) show();
		});

		// клик по крестику очистки
		if (container) {
			container.addEventListener("click", function (e) {
				var t = e.target;
				if (t && (t.classList.contains("fa-times") || (t.parentNode && t.parentNode.classList && t.parentNode.classList.contains("fa-times")))) {
					input.value = "";
					lastQ = "";
					hide();
				}
			});
		}

		document.addEventListener("click", function (e) {
			if (e.target === input) return;
			if (!panel.contains(e.target) && !(form && form.contains(e.target))) hide();
		});

		panel.addEventListener("click", function (e) {
			var minus = e.target.closest(".ks-qty-minus");
			var plus = e.target.closest(".ks-qty-plus");
			if (minus || plus) {
				e.preventDefault();
				e.stopPropagation();
				var qForm = (minus || plus).closest("form");
				if (!qForm) return;
				var input = qForm.querySelector('input[name="quantity"]');
				if (!input) return;
				var step = parseFloat(qForm.getAttribute("data-step")) || 1;
				var max = parseFloat(qForm.getAttribute("data-max-qty")) || 9999;
				var val = parseFloat(String(input.value).replace(",", ".")) || step;
				if (minus) val = Math.max(step, val - step);
				if (plus) val = Math.min(max, val + step);
				input.value = (step % 1 === 0 && val % 1 === 0) ? String(Math.round(val)) : String(val);
				return;
			}

			var btn = e.target.closest(".ks-item__cart[name=add2basket]");
			if (!btn || btn.disabled) return;
			e.preventDefault();
			e.stopPropagation();
			var buyForm = btn.closest("form");
			if (!buyForm) return;
			var body = new FormData(buyForm);
			fetch(buyForm.getAttribute("action") || "/ajax/add2basket.php", {
				method: "POST",
				body: body,
				credentials: "same-origin"
			})
				.then(function () {
					return fetch("/ajax/basket_line.php", { credentials: "same-origin" });
				})
				.then(function (r) { return r.text(); })
				.then(function (html) {
					if (typeof window.refreshCartLine === "function") {
						window.refreshCartLine(html);
					}
					btn.disabled = true;
					btn.innerHTML = "<i class=\"fa fa-check\"></i><span>Добавлено</span>";
				})
				.catch(function () { /* ignore */ });
		});
		document.addEventListener("keydown", function (e) {
			if (e.key === "Escape" || e.keyCode === 27) hide();
		});
		window.addEventListener("resize", function () { if (panel.style.display === "block") position(); });
		window.addEventListener("scroll", function () { if (panel.style.display === "block") position(); }, true);
	}

	ready(function () {
		initInput(document.getElementById("title-search-input"));
	});

	window.KSLiveSearch = { init: initInput };
})();
