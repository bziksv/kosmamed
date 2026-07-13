<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
	die();
}

use \Rbs\MoyskladStocks\Config;
use \Rbs\MoyskladStocks\Internals\OptionUtils;
?>
<script>
	const despiAgentController = {

		params: {

			profile_id: '<?= \Rbs\MoyskladStocks\Config::getProfileId() ?>',
			start_node: '.despi-agent-start',
			finish_node: '.despi-agent-finish',
			data_agent_id: 'data-agent-id',

			is_cron_actually: '<?= Config::isLastCronInitActually() ? 'Y' : 'N' ?>' === 'Y',
			global_enabled: '<?= Config::checkFeature('modulesync') ? 'Y' : 'N' ?>' === 'Y',

			old_node_tag_class: 'despi-old-agent-tr',
			node_agent_class: 'despi-agent-tr',

			lang: <?= CUtil::PhpToJSObject(OptionUtils::buildAgentJsControllerLangMessages()) ?>,
			agent_list: <?= CUtil::PhpToJSObject(OptionUtils::buildAgentJsControllerAgentListWithParams()) ?>,
			agent_function_list: <?= CUtil::PhpToJSObject(OptionUtils::buildAgentJsControllerAgentFunctionStateList()) ?>,
			agent_import_function_state: <?= CUtil::PhpToJSObject(OptionUtils::buildAgentJsControllerImportFunctionState()) ?>,
			despiAjaxController: despiAjaxController

		},



		init: function() {
			this.tagAgentTrNodes();
			this.renderAllAgentBlockNode();
			this.initNodeEvents();
			this.initParamsBlockEvents();

			this.hideOldTrs();
		},

		tagAgentTrNodes: function() {
			$(this.params.start_node).each($.proxy(function(index, node) {
				let currentAgentNodeStart = $(node);
				let agentId = currentAgentNodeStart.attr(this.params.data_agent_id);

				let count = 0;
				let currentTrOfAgent = currentAgentNodeStart.closest('tr');
				currentTrOfAgent.hide();
				while (true) {

					currentTrOfAgent = currentTrOfAgent.next();
					currentTrOfAgent.addClass(this.params.old_node_tag_class);
					currentTrOfAgent.attr(this.params.data_agent_id, agentId);
					//currentTrOfAgent.hide();
					if (!!currentTrOfAgent.find(this.params.finish_node).length) {
						break;
					}

					count++; //safety
					if (count == 12) {
						break;
					}
				}

			}, this));
		},

		renderAllAgentBlockNode: function() {
			$(this.params.start_node).each($.proxy(function(index, node) {
				let currentAgentNodeStart = $(node);
				let agentId = currentAgentNodeStart.attr(this.params.data_agent_id);

				let currentTrOfAgent = currentAgentNodeStart.closest('tr');

				currentTrOfAgent.after($(`
                    <tr class="${this.params.node_agent_class}" ${this.params.data_agent_id}="${agentId}">
                        <td colspan="2" style="text-align:center" class="despi-init-b24-hints">
                            <div class="despi-agent-block-node">
                                <div class="despi-agent-block-head">
                                </div>                                
                                <div class="despi-agent-block-params">
                                </div>
                            </div>
                        </td>
                    </tr>
                `));

				this.renderOneAgentBlockNodeHead(agentId);
				this.renderOneAgentBlockNodeParams(agentId);

			}, this));
		},

		renderOneAgentBlockNodeHead: function(agentId) {

			let agentParams = this.params.agent_list[agentId] || {};

			let template = `
                <div class="despi-toggle-isolated">
                    <span>${this.params.lang.enabled}</span>
                    <label class="switch">
                        <input data-agent-param-name="enabled" ${this.params.data_agent_id}="${agentId}" type="checkbox">
                        <span class="slider round"></span>
                    </label>
                </div>
            `;

			let hasErrors = false;

			if (!this.params.global_enabled) {
				template += `
					<div class="ui-alert ui-alert-warning">
						<span class="ui-alert-message">${this.params.lang.alert_module_work}</span>
					</div> 
				`;
				hasErrors = true;
			}

			if (!this.params.agent_import_function_state[agentId]) {
				if (agentId === 'stocks' || agentId === 'curr_stocks') {
					template += `
						<div class="ui-alert ui-alert-warning">
							<span class="ui-alert-message">${this.params.lang.alert_stocks_work}</span>
						</div>
					`;
				} else {
					template += `
					<div class="ui-alert ui-alert-warning">
						<span class="ui-alert-message">${this.params.lang.alert_import_work}</span>
					</div>
				`;
				}

				hasErrors = true;
			}

			if (!hasErrors) {
				template += `
					<div class="ui-alert ui-alert-success">
						<span class="ui-alert-message">${this.params.lang.alert_success_work}</span>
					</div>
				`;
			}

			this.getAgentBlockNode(agentId).find('.despi-agent-block-head').empty().append($(template));

			$(`[data-agent-param-name="enabled"][${this.params.data_agent_id}="${agentId}"]`).prop('checked', agentParams.enabled === 'Y');
		},

		renderOneAgentBlockNodeParams: function(agentId) {

			let agentParams = this.params.agent_list[agentId] || {};
			let template = '';

			if (agentParams.enabled === 'N') {
				template = `
                    <div class="ui-alert despi-alert-info">
                        ${this.params.lang.note_empty_agent}
                    </div>
                `;
			} else {
				template = `
                    <div class="despi-agent-tab-block">
                        <span data-tab-id="params" class="adm-detail-tab despi-agent-tab-active">${this.params.lang.tab_params}</span>
                        <span data-tab-id="info" class="adm-detail-tab">${this.params.lang.tab_info}</span>
                    </div>
                    <div class="despi-agent-tab-body-block-wrapper">
                        <div data-tab-id="params" class="despi-agent-tab-body-block">
                        </div>
                        <div data-tab-id="info" class="despi-agent-tab-body-block">
                        </div>
                    </div>
                `;
			}

			this.getAgentBlockNode(agentId).find('.despi-agent-block-params').empty().append($(template));

			this.renderParamsTabBlock(agentId);
			this.renderInfoTabBlock(agentId);

			this.initAgentTabEvent(agentId);
		},

		renderParamsTabBlock: function(agentId) {
			let tabNode = this.getAgentBlockNode(agentId).find('.despi-agent-tab-body-block[data-tab-id="params"]');
			let agentParams = this.params.agent_list[agentId] || {};


			let paramsBlockHtml = `
				<div>
					<div class="despi-agent-function-param-list">
			`;

			this.getOldAgentBlockNodeTrs(agentId).each($.proxy(function(index, node) {

				let currentTr = $(node);
				let name = currentTr.find('td').first().text();
				let paramId = '';
				let inputName = '';
				let sourceName = '';
				let additionalData = '';
				let checkedFlag = '';

				let currentInputNode = currentTr.find('input');

				if (!!currentInputNode.length) {

					sourceName = currentInputNode.attr('name');
					paramId = sourceName.split('ag_' + agentId + '_').join('');

					let is_cron_agent = agentParams['is_cron'] === 'Y';

					switch (paramId) {
						case 'interval':
							if (!is_cron_agent) {
								inputName = `<input onkeypress="return false" min="60" max="86400" step="60" data-agent-block-input data-link-input="${sourceName}" type="number" value="${agentParams[paramId]}">`;
							}
							break;
						case 'limit':
							inputName = `<input onkeypress="return false" min="${agentParams['input_params'][paramId]['min']}" max="${agentParams['input_params'][paramId]['max']}" step="${agentParams['input_params'][paramId]['step']}" data-agent-block-input data-link-input="${sourceName}" type="number" value="${agentParams[paramId]}">`;
							break;
						case 'updated':
							additionalData = 'data-toggle-param-main';
							break;
						case 'full_once':
							additionalData = 'data-toggle-param-child';
							break;

					}

					switch (paramId) {
						case 'updated':
						case 'full_once':
						case 'is_cron':

							let disabledOption = paramId !== 'is_cron' || this.params.is_cron_actually ? '' : 'disabled';

							checkedFlag = agentParams[paramId] === 'Y' ? 'checked' : '';
							inputName = `<input data-agent-block-input data-link-input="${sourceName}" type="checkbox" ${checkedFlag} value="Y" ${disabledOption} ${additionalData}>`;
							break;
					}

				}

				if (!!currentTr.find('select').length) {
					sourceName = currentTr.find('select').attr('name');
					paramId = sourceName.split('ag_' + agentId + '_').join('');
					switch (paramId) {
						case 'full_time':
							additionalData = 'data-toggle-param-child';
							inputName = `<select ${additionalData} data-agent-block-input data-link-input="${sourceName}">` + this.getSelectOptionsHtml(currentTr.find('select')) + '</select>';
							break;
					}
				}

				let classOfParamNode = '';
				if (additionalData === 'data-toggle-param-child') {
					classOfParamNode = 'despi-agent-block-sub-option';
				}

				if (!!inputName.length) {
					let hintText = this.params.lang[paramId + '_hint'];
					let hint = !!hintText ? `<span data-hint="${hintText}"></span>` : '';
					paramsBlockHtml += `
						<div class="despi-agent-param-row ${classOfParamNode}">
							<div>${hint}${name}</div>
							<span>${inputName}</span>
						</div>
					`;
				}

			}, this));

			paramsBlockHtml += `
					</div>
					<div class="despi-agent-tab-btn-area">
						<div data-agent-block-params-save ${this.params.data_agent_id}="${agentId}" class="btn-option">${this.params.lang.btn_save_changes_agent}</div>
					</div>
				</div>
			`;

			tabNode.empty().append($(paramsBlockHtml));

		},

		initParamsBlockEvents: function(isRefreshEvents) {

			if (!!isRefreshEvents) {
				$("[data-agent-block-input]").off();
				$("[data-agent-block-params-save]").off();
				$("[data-agent-block-params-update]").off();
			}

			$("[data-agent-block-input]").on('change', $.proxy(function(e) {
				let input = $(e.target);
				let inputTarget = $(`[name="${input.data('link-input')}"]`);

				switch (inputTarget.attr('type')) {
					case 'checkbox':
						inputTarget.prop('checked', input.is(':checked'));
						break;
					default:
						inputTarget.val(input.val());
						break;
				}

			}, this));

			$("[data-toggle-param-main]").on('change', $.proxy(function(e) {
				let input = $(e.target);
				let child = input.closest('.despi-agent-function-param-list').find("[data-toggle-param-child]").closest('div');
				if (input.is(':checked')) {
					child.slideDown(100);
				} else {
					child.slideUp(100);
				}
			}, this));
			$("[data-toggle-param-main]").trigger('change');

			$("[data-agent-block-params-save]").on('click', $.proxy(function(e) {

				let button = $(e.target);
				let agentId = button.attr(this.params.data_agent_id);

				if (!agentId.length) {
					return;
				}

				let trNodes = this.getOldAgentBlockNodeTrs(agentId);
				let data = {};
				trNodes.each($.proxy(function(index, node) {
					let input = $(node).find('input');
					if (!input.length) {
						input = $(node).find('select');
					}
					if (!!input.length && !!input.attr('name').length) {
						let paramId = input.attr('name').split('ag_' + agentId + '_').join('');
						if (input.attr('type') === 'checkbox') {
							if (input.is(':checked')) {
								data[paramId] = input.val();
							} else {
								data[paramId] = "N";
							}
						} else {
							data[paramId] = input.val();
						}
					}
				}, this));

				this.saveAgentParams(agentId, data);

			}, this));

			$("[data-agent-block-params-update]").on('click', $.proxy(function(e) {

				let button = $(e.target);
				let agentId = button.attr(this.params.data_agent_id);

				if (!agentId.length) {
					return;
				}

				this.getAgentParams(agentId, $.proxy(function() {
					this.getAgentBlockNode(agentId).find('[data-tab-id="info"]').trigger('click');
				}, this));

			}, this));

			BX.UI.Hint.init(BX('.despi-init-b24-hints'));

		},

		getSelectOptionsHtml: function(selectNode) {
			let optionsHtml = '';
			selectNode.find('option').each(function(i, t) {
				let selected = $(t).attr('value') == selectNode.val() ? 'selected' : '';
				optionsHtml += `
					<option ${selected} value="${$(t).attr('value')}">
						${$(t).html()}
					</option>
				`
			});
			return optionsHtml;
		},

		renderInfoTabBlock: function(agentId) {
			let tabNode = this.getAgentBlockNode(agentId).find('.despi-agent-tab-body-block[data-tab-id="info"]');
			let agentParams = this.params.agent_list[agentId] || {};
			let agentFunctionParams = this.params.agent_function_list[agentId] || {
				ID: 0
			};

			let agentFunctionHtml = `<div>`;
			agentFunctionHtml += `<div class="despi-agent-function-param-head">${this.params.lang.agent_info}</div>`;
			if (agentParams.is_cron === 'N') {
				if (agentFunctionParams.ID <= 0) {
					agentFunctionHtml += `
                        <div class="ui-alert despi-alert-info">
                            ${this.params.lang.note_cant_find_agent}
                        </div>
                    `;
				} else {
					agentFunctionHtml += `
                        <div>
                            <div class="despi-agent-function-param-list">
                                <div>
                                    <span>${this.params.lang.agent_info_id}</span>
                                    </span><a href="/bitrix/admin/agent_edit.php?ID=${agentFunctionParams.ID}&lang=ru" target="_blank">${agentFunctionParams.ID}</a></span>
                                </div>
                                <div>
                                    <span>${this.params.lang.agent_info_active}</span>
                                    </span>${agentFunctionParams.ACTIVE}</span>
                                </div>
                                <div>
                                    <span>${this.params.lang.agent_info_last_exec}</span>
                                    </span>${agentFunctionParams.LAST_EXEC}</span>
                                </div>
                                <div>
                                    <span>${this.params.lang.agent_info_next_exec}</span>
                                    </span>${agentFunctionParams.NEXT_EXEC}</span>
                                </div>
                            </div>
                        </div>
                    `;
				}
			} else {
				agentFunctionHtml += `
                    <div class="ui-alert despi-alert-info">
                        ${this.params.lang.note_cron_agent}
                    </div>
                `;
			}


			agentFunctionHtml += `<div class="despi-agent-function-param-head">${this.params.lang.agent_manager_info}</div>`;

			agentFunctionHtml += `
                <div>
                    <div class="despi-agent-function-param-list">
                        <div>
                            <span>${this.params.lang.agent_manager_info_offset}</span>
                            </span>${agentParams.offset}</span>
                        </div>
            `;

			let fieldList = ['last_update', 'last_full_update'];
			for (let i in fieldList) {
				let fieldId = fieldList[i];
				if (!!agentParams[fieldId].length) {
					let langFieldId = 'agent_manager_info_' + fieldId;
					agentFunctionHtml += `
						<div>
                            <span>${this.params.lang[langFieldId]}</span>
                            </span>${agentParams[fieldId]}</span>
                        </div>
					`;
				}
			}

			agentFunctionHtml += `
                    </div>
					<div class="despi-agent-tab-btn-area">
						<div data-open-tab="${agentId}_info" data-agent-block-params-update ${this.params.data_agent_id}="${agentId}" class="btn-option">${this.params.lang.btn_update}</div>
					</div>
                </div>
			`;

			agentFunctionHtml += `</div>`;

			tabNode.empty().append($(agentFunctionHtml));
		},

		getAgentBlockNode: function(agentId) {
			return $(`.${this.params.node_agent_class}[${this.params.data_agent_id}="${agentId}"]`);
		},

		getOldAgentBlockNodeTrs: function(agentId) {
			if (!!agentId) {
				return $(`.${this.params.old_node_tag_class}[${this.params.data_agent_id}="${agentId}"]`);
			}
			return $(`.${this.params.old_node_tag_class}`);
		},

		startLoader: function(agentId) {
			this.getAgentBlockNode(agentId).css({
				'opacity': '0.4',
				'pointer-events': 'none'
			})
		},

		finishLoader: function(agentId) {
			setTimeout($.proxy(function() {
				this.getAgentBlockNode(agentId).css({
					'opacity': '1',
					'pointer-events': 'all'
				});
			}, this), 200);
		},

		initNodeEvents: function() {
			//enabled agent
			$('input[type="checkbox"][data-agent-param-name="enabled"]').off().on('change', $.proxy(function(e) {
				let flag = $(e.target);
				let agentId = flag.attr(this.params.data_agent_id);
				this.startLoader(agentId);
				$('input[type="checkbox"]#ag_' + agentId + '_' + 'enabled').prop('checked', flag.is(':checked'));
				this.saveAgentParams(agentId, {
					enabled: flag.is(':checked') ? 'Y' : 'N'
				});
			}, this));
		},

		getAgentParams: function(agentId, callback) {
			this.startLoader(agentId);
			this.params.despiAjaxController.get('getAgentParams', {
				agentId: agentId,
				profileId: this.params.profile_id
			}, $.proxy(function(response) {
				if (response.status === 'success') {
					let data = JSON.parse(response.data);
					this.params.agent_function_list[agentId] = data.agent_function_params;
					this.params.agent_list[agentId] = data.agent_params;
				}
				if (response.status === 'error') {
					this.params.despiAjaxController.showErrorAlert(response.errors);
				}
				this.renderOneAgentBlockNodeParams(agentId);
				this.initParamsBlockEvents(true);
				this.finishLoader(agentId);

				if (!!callback) {
					callback();
				}

			}, this), $.proxy(function(response) {
				this.params.despiAjaxController.showErrorAlert(response.errors);
				this.finishLoader(agentId);
			}, this));
		},

		saveAgentParams: function(agentId, params, callback) {
			this.startLoader(agentId);

			if ('limit' in params) {
				let limit = parseInt(params['limit']);
				if (limit <= 0) {
					limit = 100;
				}
				if (limit > 1000) {
					limit = 1000;
				}
				params['limit'] = limit;
				$(`input[name="ag_${agentId}_limit"]`).val(limit);
			}
			if ('interval' in params) {
				let interval = parseInt(params['interval']);
				if (interval < 60) {
					interval = 60;
				}
				if (interval > 86400) {
					interval = 86400;
				}
				params['interval'] = interval;
				$(`input[name="ag_${agentId}_interval"]`).val(interval);
			}

			this.params.despiAjaxController.get('setAgentParams', {
				agentId: agentId,
				params: params,
				profileId: this.params.profile_id
			}, $.proxy(function(response) {
				if (response.status === 'success') {
					let data = JSON.parse(response.data);
					this.params.agent_function_list[agentId] = data.agent_function_params;
					this.params.agent_list[agentId] = data.agent_params;
				}
				if (response.status === 'error') {
					this.params.despiAjaxController.showErrorAlert(response.errors);
				}
				this.renderOneAgentBlockNodeParams(agentId);
				this.initParamsBlockEvents(true);
				this.finishLoader(agentId);

				if (!!callback) {
					callback();
				}

			}, this), $.proxy(function(response) {
				this.params.despiAjaxController.showErrorAlert(response.errors);
				this.finishLoader(agentId);
			}, this));
		},

		initAgentTabEvent: function(agentId) {
			//tabs
			this.getAgentBlockNode(agentId).find('.despi-agent-tab-block .adm-detail-tab').off().on('click', function() {
				$(this).addClass('despi-agent-tab-active');
				$(this).siblings().removeClass('despi-agent-tab-active');
				$('.despi-agent-tab-body-block').hide();
				let tabId = $(this).data('tab-id');
				$('.despi-agent-tab-body-block[data-tab-id="' + tabId + '"]').show();
			});
			$('.despi-agent-tab-active').trigger('click');
		},

		hideOldTrs: function() {
			this.getOldAgentBlockNodeTrs().hide();
		},

		showOldTrs: function() {
			this.getOldAgentBlockNodeTrs().show();
		}

	};

	BX.ready(function() {
		despiAgentController.init();
	});
</script>