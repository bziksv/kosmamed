(function() {
	'use strict';

	var MSG = 'Выберите пункт самовывоза СДЭК — нажмите «Выбрать пункт самовывоза» и укажите адрес на карте.';
	var PICKUP_IDS = (window.KM_SDEK_PICKUP && window.KM_SDEK_PICKUP.deliveryIds) || [135, 136];

	function pickupIds() {
		return PICKUP_IDS.map(function(id) { return parseInt(id, 10); });
	}

	function getSelectedDeliveryId(comp) {
		if (typeof comp.getSelectedDelivery === 'function') {
			var delivery = comp.getSelectedDelivery();
			if (delivery && delivery.ID)
				return parseInt(delivery.ID, 10);
		}

		var nodes = [
			comp.deliveryBlockNode,
			comp.deliveryHiddenBlockNode
		];
		var i, node, cb;

		for (i = 0; i < nodes.length; i++) {
			node = nodes[i];
			if (!node)
				continue;

			cb = node.querySelector('input[type=checkbox][name=DELIVERY_ID]:checked');
			if (cb)
				return parseInt(cb.value, 10);

			cb = node.querySelector('input[type=radio][name=DELIVERY_ID]:checked');
			if (cb)
				return parseInt(cb.value, 10);

			cb = node.querySelector('input[type=hidden][name=DELIVERY_ID]');
			if (cb && cb.value)
				return parseInt(cb.value, 10);
		}

		return 0;
	}

	function isSdekPickupDelivery(comp) {
		var id = getSelectedDeliveryId(comp);
		return id > 0 && pickupIds().indexOf(id) !== -1;
	}

	function isPvzSelected() {
		if (window.IPOLSDEK_pvz && IPOLSDEK_pvz.pvzId)
			return true;

		var inputs = document.querySelectorAll('[name^="ORDER_PROP_"]'), i, value, pvzId;
		for (i = 0; i < inputs.length; i++) {
			value = inputs[i].value || '';
			if (value.indexOf('#S') === -1)
				continue;
			pvzId = value.substr(value.indexOf('#S') + 2);
			if (pvzId && parseInt(pvzId, 10) > 0)
				return true;
		}

		return false;
	}

	function getActivePvzLair(comp) {
		var selected = comp.deliveryBlockNode && comp.deliveryBlockNode.querySelector('.bx-soa-pp-company.bx-selected');
		if (!selected && comp.deliveryHiddenBlockNode)
			selected = comp.deliveryHiddenBlockNode.querySelector('.bx-soa-pp-company.bx-selected');
		return selected ? selected.querySelector('.sdek_pvzLair') : null;
	}

	function clearPvzHighlight(comp) {
		var lairs = document.querySelectorAll('.sdek_pvzLair.km-sdek-pvz-error');
		var i;
		for (i = 0; i < lairs.length; i++) {
			lairs[i].classList.remove('km-sdek-pvz-error');
			var msg = lairs[i].querySelector('.km-sdek-pvz-error-msg');
			if (msg)
				msg.parentNode.removeChild(msg);
		}

		if (comp && comp.deliveryBlockNode) {
			BX.removeClass(comp.deliveryBlockNode, 'bx-step-error');
			var alert = comp.deliveryBlockNode.querySelector('.alert.alert-danger');
			if (alert && alert.getAttribute('data-km-sdek-pvz') === 'Y') {
				alert.style.display = 'none';
				BX.cleanNode(alert);
			}
		}
	}

	function showPvzError(comp) {
		var lair = getActivePvzLair(comp);

		clearPvzHighlight(comp);

		if (lair) {
			lair.classList.add('km-sdek-pvz-error');
			if (!lair.querySelector('.km-sdek-pvz-error-msg')) {
				lair.appendChild(BX.create('div', {
					props: {className: 'km-sdek-pvz-error-msg'},
					text: MSG
				}));
			}
		}

		comp.showError(comp.deliveryBlockNode, MSG, true);
		comp.animateScrollTo(comp.deliveryBlockNode, 600, 40);
	}

	function validateSdekPickup(comp, showUi) {
		if (!isSdekPickupDelivery(comp)) {
			clearPvzHighlight(comp);
			return true;
		}

		if (isPvzSelected()) {
			clearPvzHighlight(comp);
			return true;
		}

		if (showUi)
			showPvzError(comp);

		return false;
	}

	function wrapChoozePvz(comp) {
		if (!window.IPOLSDEK_pvz || IPOLSDEK_pvz._kmPvzWrapped || typeof IPOLSDEK_pvz.choozePVZ !== 'function')
			return;

		var original = IPOLSDEK_pvz.choozePVZ;
		IPOLSDEK_pvz.choozePVZ = function() {
			var result = original.apply(this, arguments);
			clearPvzHighlight(comp);
			return result;
		};
		IPOLSDEK_pvz._kmPvzWrapped = true;
	}

	function bindDeliveryChange(comp) {
		if (comp._kmSdekPvzBound)
			return;
		comp._kmSdekPvzBound = true;

		BX.bindDelegate(comp.orderBlockNode, 'change', {attr: {name: 'DELIVERY_ID'}}, function() {
			setTimeout(function() {
				if (!isSdekPickupDelivery(comp))
					clearPvzHighlight(comp);
			}, 100);
		});
	}

	function patchComponent(comp) {
		if (comp._kmSdekPvzPatched)
			return;
		comp._kmSdekPvzPatched = true;

		var origNext = comp.clickNextAction;
		comp.clickNextAction = function(event) {
			var target = event.target || event.srcElement,
				actionSection = BX.findParent(target, {className: 'bx-active'});

			if (actionSection && actionSection.id === comp.deliveryBlockNode.id) {
				if (!validateSdekPickup(comp, true))
					return BX.PreventDefault(event);
			}

			return origNext.apply(this, arguments);
		};

		var origSave = comp.clickOrderSaveAction;
		comp.clickOrderSaveAction = function(event) {
			if (!validateSdekPickup(comp, true))
				return BX.PreventDefault(event);

			return origSave.apply(this, arguments);
		};

		var origValidForm = comp.isValidForm;
		comp.isValidForm = function() {
			var ok = origValidForm.apply(this, arguments);
			if (!ok)
				return false;
			return validateSdekPickup(this, true);
		};

		bindDeliveryChange(comp);
		wrapChoozePvz(comp);

		var pvzTimer = setInterval(function() {
			wrapChoozePvz(comp);
			if (window.IPOLSDEK_pvz && IPOLSDEK_pvz._kmPvzWrapped)
				clearInterval(pvzTimer);
		}, 300);
	}

	function tryInit() {
		if (!BX.Sale || !BX.Sale.OrderAjaxComponent || !BX.Sale.OrderAjaxComponent.deliveryBlockNode)
			return setTimeout(tryInit, 50);

		patchComponent(BX.Sale.OrderAjaxComponent);
	}

	BX.ready(tryInit);
})();
