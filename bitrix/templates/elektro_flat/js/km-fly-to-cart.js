/**
 * КосмаМед — анимация «товар летит в корзину» при добавлении.
 */
(function () {
	"use strict";

	var NO_PHOTO = "/bitrix/templates/elektro_flat/images/no-photo.svg";

	function prefersReducedMotion() {
		return window.matchMedia && window.matchMedia("(prefers-reduced-motion: reduce)").matches;
	}

	function getCartIcon() {
		return document.querySelector(".cart_line a.cart, .cart_line .cart");
	}

	function isVisible(el) {
		if (!el) return false;
		var cs = window.getComputedStyle(el);
		return cs.display !== "none" && cs.visibility !== "hidden" && el.offsetWidth > 0 && el.offsetHeight > 0;
	}

	function findProductImage(btn) {
		var root = btn.closest(
			".catalog-item-card, .catalog-item, .catalog-detail-element, .ks-item, .tvr_search, [data-entity=\"item\"]"
		);
		if (!root) return null;

		var selectors = [
			".slick-slide.slick-active img",
			".detail_picture:not(.hidden) img",
			".item-image img.item_img",
			".item-image img",
			".ks-item__img img",
			".item_img",
			"img[itemprop=\"image\"]"
		];

		var i, j, imgs, img;
		for (i = 0; i < selectors.length; i++) {
			imgs = root.querySelectorAll(selectors[i]);
			for (j = 0; j < imgs.length; j++) {
				img = imgs[j];
				if (img.src && isVisible(img)) return img;
			}
		}

		imgs = root.querySelectorAll("img");
		for (j = 0; j < imgs.length; j++) {
			img = imgs[j];
			if (img.src && isVisible(img) && img.closest(".item-image, .ks-item__img, .detail_picture, .detail_picture_pa")) {
				return img;
			}
		}

		return null;
	}

	function pulseCart(cart) {
		if (!cart) return;
		cart.classList.remove("km-cart-pulse");
		void cart.offsetWidth;
		cart.classList.add("km-cart-pulse");
		window.setTimeout(function () {
			cart.classList.remove("km-cart-pulse");
		}, 450);
	}

	function flyToCart(sourceEl, imageSrc) {
		var cart = getCartIcon();
		if (!cart) return;

		if (prefersReducedMotion()) {
			pulseCart(cart);
			return;
		}

		var startRect;
		var src = imageSrc || NO_PHOTO;

		if (sourceEl && sourceEl.tagName === "IMG") {
			startRect = sourceEl.getBoundingClientRect();
			src = sourceEl.currentSrc || sourceEl.src || src;
		} else if (sourceEl) {
			startRect = sourceEl.getBoundingClientRect();
		} else {
			return;
		}

		if (startRect.width < 1 || startRect.height < 1) return;

		var cartRect = cart.getBoundingClientRect();
		var fly = document.createElement("img");
		fly.className = "km-fly-to-cart";
		fly.alt = "";
		fly.src = src;

		var size = Math.round(Math.min(Math.max(startRect.width, 44), 72));
		var startX = startRect.left + startRect.width / 2 - size / 2;
		var startY = startRect.top + startRect.height / 2 - size / 2;
		var endX = cartRect.left + cartRect.width / 2 - size / 2;
		var endY = cartRect.top + cartRect.height / 2 - size / 2;
		var arcY = Math.min(startY, endY) - Math.min(120, window.innerHeight * 0.12);

		fly.style.width = size + "px";
		fly.style.height = size + "px";
		fly.style.left = startX + "px";
		fly.style.top = startY + "px";

		document.body.appendChild(fly);

		var dx = endX - startX;
		var dy = endY - startY;
		var midX = dx * 0.45;
		var midY = arcY - startY;

		var onDone = function () {
			if (fly.parentNode) fly.parentNode.removeChild(fly);
			pulseCart(cart);
		};

		if (fly.animate) {
			fly.animate([
				{ transform: "translate(0, 0) scale(1)", opacity: 1 },
				{ transform: "translate(" + midX + "px, " + midY + "px) scale(0.82)", opacity: 1, offset: 0.55 },
				{ transform: "translate(" + dx + "px, " + dy + "px) scale(0.12)", opacity: 0.15 }
			], {
				duration: 720,
				easing: "cubic-bezier(0.55, 0, 0.1, 1)",
				fill: "forwards"
			}).onfinish = onDone;
		} else {
			fly.style.transition = "all 0.7s cubic-bezier(0.55, 0, 0.1, 1)";
			window.requestAnimationFrame(function () {
				fly.style.transform = "translate(" + dx + "px, " + dy + "px) scale(0.12)";
				fly.style.opacity = "0.15";
			});
			window.setTimeout(onDone, 720);
		}
	}

	function fromButton(btn) {
		if (!btn || btn.disabled) return;
		if (btn.classList.contains("apuo")) return;

		var img = findProductImage(btn);
		if (img) {
			flyToCart(img);
		} else {
			flyToCart(btn, NO_PHOTO);
		}
	}

	document.addEventListener("click", function (e) {
		var btn = e.target.closest('button[name="add2basket"], .ks-item__cart[name="add2basket"]');
		if (!btn) return;
		fromButton(btn);
	}, true);

	window.kmFlyToCart = flyToCart;
	window.kmFlyToCartFromButton = fromButton;

	window.kmResolveBasketPopupPict = function (product, visual) {
		var pict = (product && product.pict) ? product.pict : {};
		var src = pict.SRC || "";
		var width = pict.WIDTH || 150;
		var height = pict.HEIGHT || 150;

		if (src && src.indexOf("undefined") === -1) {
			return {SRC: src, WIDTH: width, HEIGHT: height};
		}

		var btnId = visual && (visual.BTN_BUY_ID || visual.PROPS_BTN_ID);
		if (btnId && window.BX) {
			var btn = BX(btnId);
			if (btn) {
				var img = findProductImage(btn);
				if (img && img.src) {
					return {
						SRC: img.currentSrc || img.src,
						WIDTH: img.naturalWidth || img.width || 150,
						HEIGHT: img.naturalHeight || img.height || 150
					};
				}
			}
		}

		return {SRC: NO_PHOTO, WIDTH: 150, HEIGHT: 150};
	};
})();
