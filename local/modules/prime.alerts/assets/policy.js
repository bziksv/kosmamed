/**
 * prime.alerts — live notice under email fields (register / checkout / 1-click)
 * Config: window.PRIME_ALERTS = { providers, noticeSignup, noticeCheckout, policyRegister, policyOrder }
 */
(function () {
	var cfg = window.PRIME_ALERTS;
	if (!cfg || cfg.enabled === false) return;

	var providers = cfg.providers || [];

	function domainOf(email) {
		email = String(email || '').trim().toLowerCase();
		var at = email.lastIndexOf('@');
		return at > 0 ? email.slice(at + 1) : '';
	}

	function isAllowed(email) {
		email = String(email || '').trim().toLowerCase();
		if (!email) return true;
		var domain = domainOf(email);
		if (!domain) return true;
		if (/\.(ru|su)$/.test(domain)) return true;
		for (var i = 0; i < providers.length; i++) {
			var p = providers[i];
			if (domain === p || domain.slice(-(p.length + 1)) === '.' + p) return true;
		}
		return false;
	}

	function fieldWrap(inp) {
		return inp.closest(
			'.bx-soa-customer-field, .form-group, .soa-property-container, .field, .row, .form-input, tr, label'
		) || inp.parentNode;
	}

	function contextFor(inp) {
		if (!inp) return 'signup';
		var form = inp.form || inp.closest('form');
		var id = ((form && form.id) || '') + ' ' + ((form && form.name) || '');
		if (/ORDER|soa|bx-soa/i.test(id) || document.getElementById('bx-soa-order')) {
			if (inp.closest('#bx-soa-order, #bx-soa-properties, form[name="ORDER_FORM"]')) {
				return 'checkout';
			}
		}
		if (/boc|1cb|one.?click/i.test(id) || (form && /boc/i.test(form.id || ''))) {
			return 'checkout';
		}
		if (inp.name === 'EMAIL' && form && /boc|script\.php/i.test(form.action || '')) {
			return 'checkout';
		}
		return 'signup';
	}

	function noticeHtml(ctx) {
		return ctx === 'checkout' ? (cfg.noticeCheckout || '') : (cfg.noticeSignup || '');
	}

	function ensureBox(inp) {
		var wrap = fieldWrap(inp);
		if (!wrap || !wrap.parentNode) return null;
		var anchor = (wrap.classList && wrap.classList.contains('form-input')) ? (wrap.parentNode || wrap) : wrap;
		var next = anchor.nextElementSibling;
		if (next && next.classList && next.classList.contains('prime-alerts-live-notice')) {
			return next;
		}
		var box = document.createElement('div');
		box.className = 'prime-alerts-live-notice';
		box.setAttribute('aria-live', 'polite');
		box.style.display = 'none';
		if (anchor.nextSibling) {
			anchor.parentNode.insertBefore(box, anchor.nextSibling);
		} else {
			anchor.parentNode.appendChild(box);
		}
		return box;
	}

	function isEmailInput(inp) {
		if (!inp || inp.tagName !== 'INPUT') return false;
		var type = (inp.type || '').toLowerCase();
		var name = (inp.name || '').toLowerCase();
		var auto = (inp.getAttribute('autocomplete') || '').toLowerCase();
		var id = (inp.id || '').toLowerCase();
		if (type === 'email') return true;
		if (auto === 'email') return true;
		if (name === 'user_email' || name === 'email' || name.indexOf('email') >= 0) return true;
		if (id.indexOf('email') >= 0) return true;
		var wrap = fieldWrap(inp);
		var label = wrap ? wrap.textContent : '';
		if (/e-?mail/i.test(label) && type === 'text') return true;
		return false;
	}

	function policyEnabledFor(ctx) {
		if (ctx === 'checkout') return cfg.policyOrder !== false;
		return cfg.policyRegister !== false;
	}

	function refreshInput(inp) {
		if (!isEmailInput(inp)) return;
		var ctx = contextFor(inp);
		if (!policyEnabledFor(ctx)) return;
		var box = ensureBox(inp);
		if (!box) return;
		var email = String(inp.value || '').trim();
		var bad = email && !isAllowed(email);
		if (bad) {
			if (!box.getAttribute('data-filled')) {
				box.innerHTML = noticeHtml(ctx);
				box.setAttribute('data-filled', '1');
				box.setAttribute('data-ctx', ctx);
			} else if (box.getAttribute('data-ctx') !== ctx) {
				box.innerHTML = noticeHtml(ctx);
				box.setAttribute('data-ctx', ctx);
			}
			box.style.display = '';
		} else {
			box.style.display = 'none';
		}
	}

	function scan(root) {
		root = root || document;
		var nodes = root.querySelectorAll('input[type="email"], input[type="text"], input:not([type])');
		for (var i = 0; i < nodes.length; i++) {
			if (isEmailInput(nodes[i])) refreshInput(nodes[i]);
		}
	}

	function bind() {
		document.addEventListener('input', function (e) {
			if (e.target && e.target.tagName === 'INPUT') refreshInput(e.target);
		}, true);
		document.addEventListener('change', function (e) {
			if (e.target && e.target.tagName === 'INPUT') refreshInput(e.target);
		}, true);
		scan();
	}

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', bind);
	} else {
		bind();
	}

	if (window.BX && BX.addCustomEvent) {
		BX.addCustomEvent('onAjaxSuccess', function () { scan(); });
		BX.addCustomEvent('onFrameDataReceived', function () { scan(); });
	}
	setInterval(function () { scan(); }, 1000);
})();
