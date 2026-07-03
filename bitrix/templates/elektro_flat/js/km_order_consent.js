(function() {
	'use strict';

	function getConsentBlock() {
		return document.getElementById('hint_agreement');
	}

	function getConsentInput() {
		return document.getElementById('PERSONAL_DATA_order');
	}

	function getConsentCheckbox() {
		return document.getElementById('input-checkbox_order');
	}

	function getConsentError() {
		return document.getElementById('km_consent_error');
	}

	function isConsentChecked() {
		var input = getConsentInput();
		return !!(input && input.value === 'Y');
	}

	function setCheckboxVisual(checked) {
		var cb = getConsentCheckbox();
		var input = getConsentInput();
		if (!cb || !input)
			return;

		if (checked) {
			if (!BX.hasClass(cb, 'cheked')) {
				BX.addClass(cb, 'cheked');
				BX.adjust(cb, {
					children: [
						BX.create('i', {props: {className: 'fa fa-check'}})
					]
				});
			}
			input.value = 'Y';
			cb.setAttribute('aria-checked', 'true');
			clearConsentError();
		} else {
			BX.removeClass(cb, 'cheked');
			BX.remove(BX.findChild(cb, {className: 'fa fa-check'}));
			input.value = 'N';
			cb.setAttribute('aria-checked', 'false');
		}
	}

	function toggleCheckbox() {
		setCheckboxVisual(!BX.hasClass(getConsentCheckbox(), 'cheked'));
	}

	function clearConsentError() {
		var block = getConsentBlock();
		var err = getConsentError();
		if (block)
			block.classList.remove('km-consent-error');
		if (err)
			err.hidden = true;

		var legacyBad = document.getElementById('PERSONAL_DATA_BAD');
		if (legacyBad)
			legacyBad.parentNode.removeChild(legacyBad);

		var dynMsg = document.querySelector('.km-consent-error-msg');
		if (dynMsg)
			dynMsg.parentNode.removeChild(dynMsg);
	}

	function showConsentError(comp) {
		var block = getConsentBlock();
		var err = getConsentError();
		if (!block)
			return;

		block.classList.add('km-consent-error');
		if (err)
			err.hidden = false;

		if (comp && typeof comp.animateScrollTo === 'function')
			comp.animateScrollTo(block, 600, 80);
	}

	function validateConsent(comp, showUi) {
		if (isConsentChecked()) {
			clearConsentError();
			return true;
		}

		if (showUi)
			showConsentError(comp);

		return false;
	}

	function bindCheckbox() {
		var cb = getConsentCheckbox();
		var block = getConsentBlock();
		if (!cb || !block || cb._kmConsentBound)
			return;

		cb._kmConsentBound = true;

		BX.bind(cb, 'click', toggleCheckbox);
		BX.bind(cb, 'keydown', function(event) {
			if (event.keyCode === 13 || event.keyCode === 32) {
				toggleCheckbox();
				return BX.PreventDefault(event);
			}
		});

		var label = block.querySelector('.km-order-consent__text');
		if (label) {
			BX.bind(label, 'click', function(event) {
				if (event.target && event.target.tagName === 'A')
					return;
				toggleCheckbox();
			});
		}
	}

	function patchComponent(comp) {
		if (comp._kmConsentPatched)
			return;
		comp._kmConsentPatched = true;

		var origSave = comp.clickOrderSaveAction;
		comp.clickOrderSaveAction = function(event) {
			if (!validateConsent(comp, true))
				return BX.PreventDefault(event);

			return origSave.apply(this, arguments);
		};

		var origValidForm = comp.isValidForm;
		comp.isValidForm = function() {
			var ok = origValidForm.apply(this, arguments);
			if (!ok)
				return false;

			return validateConsent(this, true);
		};
	}

	function tryInit() {
		bindCheckbox();

		if (!BX.Sale || !BX.Sale.OrderAjaxComponent)
			return setTimeout(tryInit, 50);

		patchComponent(BX.Sale.OrderAjaxComponent);
	}

	BX.ready(tryInit);
})();
