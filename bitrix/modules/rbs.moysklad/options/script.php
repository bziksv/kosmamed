<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

use Bitrix\Main\Localization\Loc;

$activeModuleTab = $isSaveHit ? $request->get('tabControl_active_tab') : $request->get("tab");
if (empty($activeModuleTab)) {
	$activeModuleTab = 'main';
}

$moduleParams = [
	'profileId' => \Rbs\Moysklad\Config::getProfileId(),
	'formNodeSelector' => '#' . \Rbs\Moysklad\Internals\OptionPageParams::getStaticHtmlNames()['option_form_id'],
	'moduleId' => \Rbs\Moysklad\Config::getModuleId(true),
	'ajaxNamespace' => \Rbs\Moysklad\Internals\OptionPageParams::getStaticHtmlNames()['ajax_namespace'],
	'showAlertForSaveCheckbox' => $showAlertForSaveCheckbox,
	'showAlertForSaveSelect' => $showAlertForSaveSelect,
	'actionStr' => \Rbs\Moysklad\Internals\OptionPageParams::getActionStr(),
];
?>
<script>
	const despiModuleController = {

		params: <?= CUtil::PhpToJSObject($moduleParams) ?>,

		init: function() {
			this.initAgentInfo();
			this.initAuthUpdate();
			this.initAlerts();
			this.initTabs();
		},

		initAgentInfo: function() {
			$('.agent-info').hide();
			$('.js-agent-toggle-info').on('click', function() {
				$(this).siblings('.agent-info').toggle();
			});
		},

		initAuthUpdate: function() {

			$('input[name="pass"]').prop('type', 'password');

			let actionStr = this.params.actionStr;
			$('[name="UpdateAuth"]').on('click', function(e) {
				e.preventDefault();

				let authData = '';
				let authType = 'empty';
				if ($('[name="token"]').val().length > 0) {
					authType = 'token';
					authData = $('[name="token"]').val();
				} else if ($('[name="login"]').val().length > 0 && $('[name="pass"]').val().length > 0) {
					authType = 'login';
					authData = $('[name="login"]').val() + '|' + $('[name="pass"]').val();
				}

				if (authType === 'empty') {
					alert("<?= Loc::getMessage("ERROR_AUTH_DATA") ?>");
				} else {
					BX.ajax.runAction(despiModuleController.params.ajaxNamespace + 'saveAuth', {
						data: {
							authType: authType,
							authData: authData,
							profile_id: despiModuleController.params.profileId
						}
					}).then(function(response) {
						window.location.href = actionStr;
					}, function(response) {
						alert("<?= Loc::getMessage("ERROR_AUTH_DATA_AJAX") ?>");
					});
				}
			});
		},

		initAlerts: function() {

			$(this.params.formNodeSelector + ' .adm-info-message').removeClass('adm-info-message').addClass('ui-alert despi-alert-info');

			const insertAlertForOption = function(node, message, isShow) {
				if (!node.closest('tr').next().hasClass('custom-alert') && isShow) {
					node.closest('tr').after($('<tr class="custom-alert"><td colspan="2" align="center"><div class="adm-info-message-wrap" align="center"><div class="adm-info-message">' + message + '</div></div></td></tr>'));
					$('.save-alert-around-save-btn').removeClass('alert-hide');
				}
				if (node.closest('tr').next().hasClass('custom-alert') && !isShow) {
					node.closest('tr').next().remove();
					if ($('.custom-alert').length <= 0) {
						$('.save-alert-around-save-btn').addClass('alert-hide');
					}
				}
			};

			if (Array.isArray(this.params.showAlertForSaveCheckbox) && this.params.showAlertForSaveCheckbox.length) {
				this.params.showAlertForSaveCheckbox.forEach((optionName) => {
					const oldOptionVal = $('#' + optionName).is(':checked');
					$('#' + optionName).on('change', function(e) {
						insertAlertForOption($(this), '<?= Loc::getMessage("SAVE_PARAMS_FOR_NEXT_ACTIONS") ?>', oldOptionVal != $(this).is(':checked'));
					});
				});
			}

			if (Array.isArray(this.params.showAlertForSaveSelect) && this.params.showAlertForSaveSelect.length) {
				this.params.showAlertForSaveSelect.forEach((optionName) => {
					$('select[name^="' + optionName + '"').on('change', function(e) {
						insertAlertForOption($(this), '<?= Loc::getMessage("SAVE_PARAMS_FOR_NEXT_ACTIONS") ?>', true);
					});
				});
			}
		},

		initTabs: function() {
			let activeModuleTab = '<?= $activeModuleTab ?>';
			let actionStr = this.params.actionStr;
			tabControl.SelectTab('<?= htmlspecialchars($activeModuleTab) ?>');
			$(this.params.formNodeSelector + ' .adm-detail-tab').on('click', function() {
				activeModuleTab = $(this).attr('id').split('tab_cont_').pop();
				history.pushState(null, null, actionStr + '&tab=' + activeModuleTab);
			});
			if (!!activeModuleTab) {
				setTimeout(function() {
					$('#tab_cont_' + activeModuleTab).click();
				}, 100);
			}
		},
	};

	BX.ready(function() {
		despiModuleController.init();
	});

	<? if ($moduleParams['profileId'] === 0) : ?>
		(function() {

			let moduleId = '<?= \Rbs\Moysklad\Config::getModuleId(true) ?>';
			let moduleModificator = moduleId.split('.').join('_');

			let baseMenuNode = $('#global_submenu_global_menu_despi_moysklad');
			let moduleMenuNode = baseMenuNode.find('span.adm-submenu-item-link-icon.' + moduleModificator + '_icon').closest('.adm-sub-submenu-block');

			let docLinkNode = baseMenuNode.find('a.adm-submenu-item-name-link[href*="docs.despi.ru"]').prop('target', '_blank');

			let tabNodes = moduleMenuNode.find('.adm-sub-submenu-block.adm-submenu-level-2:first-child .adm-sub-submenu-block-children>.adm-sub-submenu-block');

			let activeItemClass = 'adm-submenu-item-active';
			if (tabNodes.length > 0) {
				tabNodes.each(function(index, item) {
					$(item).addClass('despi-js-custom-item-event');
					let currentLink = $(item).find('a.adm-submenu-item-name-link');
					let currentTabName = currentLink.attr('href').split('tab=').pop();
					if (!!currentTabName) {
						$(item).attr('data-tab-name', currentTabName);
						$(item).attr('data-module-id', moduleId);
					}
				});
				tabNodes.find('a.adm-submenu-item-name-link').off().on('click', function(e) {
					let currItem = $(this).closest('.despi-js-custom-item-event');
					let tabName = currItem.data('tab-name');
					e.preventDefault();
					if (!!tabName) {
						tabControl.SelectTab(tabName);
						history.pushState(null, null, '<?= $actionStr ?>&tab=' + tabName);
					}
					tabNodes.removeClass(activeItemClass);
					currItem.addClass(activeItemClass);

				});
				$('#' + moduleModificator + '_option_form .adm-detail-tab').on('click', function() {
					let currentTabName = $(this).attr('id').split('tab_cont_').pop();
					let currItem = $('.despi-js-custom-item-event[data-tab-name="' + currentTabName + '"][data-module-id="' + moduleId + '"]');
					tabNodes.removeClass(activeItemClass);
					if (currItem.length) {
						currItem.addClass(activeItemClass);
					}
				});
			}

			setTimeout(function() {
				$('#global_menu_global_menu_despi_moysklad').click();
			}, 100);

		})();
	<? endif ?>
</script>