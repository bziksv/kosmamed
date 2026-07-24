/**
 * КосмаМед — live-поиск (выпадашка на всю ширину, две колонки).
 * Фичи как на polimer: фильтр категорий, клавиатура, подсветка с бэка.
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
		var anchor = input.closest(".center") || container;

		var panel = document.createElement("div");
		panel.className = "ks-suggest";
		panel.style.display = "none";
		document.body.appendChild(panel);

		var timer = null, lastQ = "", controller = null;
		var activeSectionIds = [];
		var sectionMeta = {};
		var currentRow = -1;
		var cache = {};

		function position() {
			var aRect = anchor.getBoundingClientRect();
			var iRect = input.getBoundingClientRect();
			panel.style.position = "absolute";
			panel.style.left = (aRect.left + window.pageXOffset) + "px";
			panel.style.width = aRect.width + "px";
			panel.style.top = (iRect.bottom + window.pageYOffset + 6) + "px";
		}
		function show() { position(); panel.style.display = "block"; }
		function hide() {
			panel.style.display = "none";
			currentRow = -1;
			unselectAll();
		}

		function visibleItems() {
			return Array.prototype.slice.call(panel.querySelectorAll(".ks-item")).filter(function (el) {
				return el.style.display !== "none" && !el.hasAttribute("hidden");
			});
		}

		function unselectAll() {
			panel.querySelectorAll(".ks-item.is-active").forEach(function (el) {
				el.classList.remove("is-active");
			});
		}

		function selectRow(idx) {
			var items = visibleItems();
			unselectAll();
			if (!items.length) { currentRow = -1; return; }
			if (idx < 0) idx = items.length - 1;
			if (idx >= items.length) idx = 0;
			currentRow = idx;
			items[idx].classList.add("is-active");
			items[idx].scrollIntoView({ block: "nearest" });
		}

		function applySectionFilter() {
			var items = panel.querySelectorAll(".ks-item");
			var seps = panel.querySelectorAll(".ks-suggest__sep");
			var emptyEl = panel.querySelector(".ks-suggest__filter-empty");
			var countEl = panel.querySelector(".ks-suggest__count");
			var visible = 0;

			items.forEach(function (el) {
				var sid = parseInt(el.getAttribute("data-section-id") || "0", 10);
				var ok = !activeSectionIds.length || activeSectionIds.indexOf(sid) !== -1;
				el.style.display = ok ? "" : "none";
				if (ok) visible++;
			});

			// прячем разделители, если после фильтра нет товаров в группе
			seps.forEach(function (sep) {
				var next = sep.nextElementSibling;
				var has = false;
				while (next && !next.classList.contains("ks-suggest__sep") && !next.classList.contains("ks-suggest__filter-empty")) {
					if (next.classList.contains("ks-item") && next.style.display !== "none") {
						has = true;
						break;
					}
					next = next.nextElementSibling;
				}
				sep.style.display = has ? "" : "none";
			});

			if (emptyEl) emptyEl.hidden = visible > 0 || !activeSectionIds.length;
			if (countEl) countEl.textContent = String(visible);

			renderFilterChips();
			updateCatActive();
			currentRow = -1;
			unselectAll();
		}

		function renderFilterChips() {
			var bar = panel.querySelector(".ks-suggest__filter-bar");
			if (!bar) return;
			if (!activeSectionIds.length) {
				bar.hidden = true;
				bar.innerHTML = "";
				return;
			}
			bar.hidden = false;
			var html = activeSectionIds.map(function (id) {
				var meta = sectionMeta[id] || { name: "#" + id };
				return '<button type="button" class="ks-suggest__chip" data-section-id="' + id + '">' +
					escapeHtml(meta.name) + ' <span aria-hidden="true">×</span></button>';
			}).join("") +
				'<button type="button" class="ks-suggest__chip-reset">Сбросить все</button>';
			bar.innerHTML = html;
		}

		function updateCatActive() {
			panel.querySelectorAll(".ks-cat").forEach(function (row) {
				var sid = parseInt(row.getAttribute("data-section-id") || "0", 10);
				row.classList.toggle("is-active", activeSectionIds.indexOf(sid) !== -1);
			});
		}

		function escapeHtml(s) {
			return String(s).replace(/[&<>"']/g, function (c) {
				return ({ "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;" })[c];
			});
		}

		function bindPanelUi() {
			activeSectionIds = [];
			sectionMeta = {};
			currentRow = -1;

			panel.querySelectorAll(".ks-cat__filter").forEach(function (btn) {
				var sid = parseInt(btn.getAttribute("data-section-id") || "0", 10);
				sectionMeta[sid] = {
					name: btn.getAttribute("data-section-name") || ""
				};
			});

			applySectionFilter();
		}

		function fetchSuggest(q) {
			if (cache[q]) {
				panel.innerHTML = cache[q];
				bindPanelUi();
				show();
				return;
			}
			var opts = {};
			if (window.AbortController) {
				if (controller) controller.abort();
				controller = new AbortController();
				opts.signal = controller.signal;
			}
			panel.classList.add("is-loading");
			fetch("/ajax/search_suggest.php?q=" + encodeURIComponent(q), opts)
				.then(function (r) { return r.text(); })
				.then(function (html) {
					panel.classList.remove("is-loading");
					if (!html || !html.trim()) { hide(); return; }
					cache[q] = html;
					panel.innerHTML = html;
					bindPanelUi();
					show();
				})
				.catch(function () { panel.classList.remove("is-loading"); });
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
			var chipReset = e.target.closest(".ks-suggest__chip-reset");
			if (chipReset) {
				e.preventDefault();
				activeSectionIds = [];
				applySectionFilter();
				return;
			}
			var chip = e.target.closest(".ks-suggest__chip");
			if (chip) {
				e.preventDefault();
				var cid = parseInt(chip.getAttribute("data-section-id") || "0", 10);
				activeSectionIds = activeSectionIds.filter(function (id) { return id !== cid; });
				applySectionFilter();
				return;
			}

			var filterBtn = e.target.closest(".ks-cat__filter");
			if (filterBtn) {
				e.preventDefault();
				e.stopPropagation();
				var sid = parseInt(filterBtn.getAttribute("data-section-id") || "0", 10);
				var idx = activeSectionIds.indexOf(sid);
				if (idx === -1) activeSectionIds.push(sid);
				else activeSectionIds.splice(idx, 1);
				applySectionFilter();
				return;
			}

			var minus = e.target.closest(".ks-qty-minus");
			var plus = e.target.closest(".ks-qty-plus");
			if (minus || plus) {
				e.preventDefault();
				e.stopPropagation();
				var qForm = (minus || plus).closest("form");
				if (!qForm) return;
				var qtyInput = qForm.querySelector('input[name="quantity"]');
				if (!qtyInput) return;
				var step = parseFloat(qForm.getAttribute("data-step")) || 1;
				var max = parseFloat(qForm.getAttribute("data-max-qty")) || 9999;
				var val = parseFloat(String(qtyInput.value).replace(",", ".")) || step;
				if (minus) val = Math.max(step, val - step);
				if (plus) val = Math.min(max, val + step);
				qtyInput.value = (step % 1 === 0 && val % 1 === 0) ? String(Math.round(val)) : String(val);
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
			var open = panel.style.display === "block";
			if (e.key === "Escape" || e.keyCode === 27) {
				if (open) hide();
				return;
			}
			if (!open || document.activeElement !== input) return;

			if (e.key === "ArrowDown" || e.keyCode === 40) {
				e.preventDefault();
				selectRow(currentRow + 1);
			} else if (e.key === "ArrowUp" || e.keyCode === 38) {
				e.preventDefault();
				selectRow(currentRow - 1);
			} else if (e.key === "Enter" || e.keyCode === 13) {
				var items = visibleItems();
				if (currentRow >= 0 && items[currentRow]) {
					e.preventDefault();
					var link = items[currentRow].querySelector(".ks-item__link");
					if (link) window.location.href = link.href;
				}
			}
		});
		window.addEventListener("resize", function () { if (panel.style.display === "block") position(); });
		window.addEventListener("scroll", function () { if (panel.style.display === "block") position(); }, true);
	}

	ready(function () {
		initInput(document.getElementById("title-search-input"));
	});

	window.KSLiveSearch = { init: initInput };
})();
