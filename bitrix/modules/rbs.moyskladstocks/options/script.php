<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

use \Rbs\MoyskladStocks\LangMsg;
use \Rbs\MoyskladStocks\Internals\OptionUtils;

$arTabList = [];
foreach($aTabs as $tabInfo) {
    if(!empty($tabInfo['DIV'])) {
        $arTabList[] = $tabInfo['DIV'];
    }    
}
$requestActiveModuleTab = $isSaveHit ? $request->get('tabControl_active_tab') : $request->get("tab");
$activeModuleTab = 'main';
if(in_array($requestActiveModuleTab, $arTabList)) {
    $activeModuleTab = $arTabList[array_search($requestActiveModuleTab, $arTabList)];
}
?>
<script>
    let rbsMsStocksProfile = '<?= \Rbs\MoyskladStocks\Config::getProfileId() ?>';
    let activeModuleTab = '<?= $activeModuleTab ?>';
    let rbsMsStocksFormNodeSelector = '#rbs_moyskladstocks_option_form';

    const despiAjaxController = {

        params: {
            ajaxNameSpace: 'rbs:moyskladstocks.api.ajax.',
            lang: {
                errorDefaultAjax: '<?= LangMsg::get('UNIVERSAL_LANG_AJAX_ERROR'); ?>'
            }
        },

        get: function(action, data, success_callback, err_callback) {
            let callbackAction = success_callback ?? this.defaultCallback;
            let errCallbackAction = err_callback ?? this.showErrorAlert;
            BX.ajax.runAction(this.getAjaxMethodName(action), {
                data: data
            }).then(BX.delegate(callbackAction, this), BX.delegate(errCallbackAction, this));
        },

        getAjaxMethodName(action) {
            return this.params.ajaxNameSpace + action;
        },

        defaultCallback: function(response) {

        },

        showErrorAlert: function(errors) {
            let errorTxt = this.params.lang.errorDefaultAjax;
            if (!!errors && !!errors.length) {
                for (let i in errors) {
                    if (message in errors[i]) {
                        errorTxt += "\n" + errors[i].message;
                    }
                }
            }
            alert(errorTxt);
        },

    };

    const despiJsConfig = {
        logAreaNode: '#loger',
        logBtnNode: '#loger-buttons',
        logBtnClass: '.js-log-btn',
        logToggleBlockNode: '.log-toggle-block',
        logToggleParentNode: '.log-block-messages',
        logToggleTriggerNode: '.log-block-messages>.log-message',
        logFilesNode: '#loger-files',
        logFilesInnerNode: '#loger-files-inner',
        logFilesHintNode: '#loger-files-hint',

        messages: {
            'log_btn_toggle_down': '<?= GetMessageJS("JS_CONFIG_MESSAGE_LOG_BTN_TOGGLE_OPEN") ?>',
            'log_btn_toggle_up': '<?= GetMessageJS("JS_CONFIG_MESSAGE_LOG_BTN_TOGGLE_CLOSE") ?>',
            'log_btn_update': '<?= GetMessageJS("JS_CONFIG_MESSAGE_LOG_BTN_UPDATE") ?>',
            'log_btn_clear': '<?= GetMessageJS("JS_CONFIG_MESSAGE_LOG_BTN_CLEAR") ?>',
            'log_btn_delete_all': '<?= GetMessageJS("JS_CONFIG_MESSAGE_LOG_BTN_DELETE_ALL") ?>',
            'log_btn_get_dir': '<?= GetMessageJS("JS_CONFIG_MESSAGE_LOG_BTN_GET_DIR") ?>',
        },

        getMessage: function(message_id) {
            return message_id in this.messages ? this.messages[message_id] : '';
        }
    }

    const despiAlertOnSaveController = {

        params: {
            targets: {
                'save_btn_alert_area': 'despi-save-alert-around-save-btn',
                'custom_alert_class': 'despi-custom-alert',
                'hide_alert_class': 'despi-alert-hide'
            },
            'alertOptionBlock': <?= CUtil::PhpToJSObject($showAlertForSaveOption); ?>,
            'lang': {
                'save_params_for_next_action': '<?= GetMessage("SAVE_PARAMS_FOR_NEXT_ACTIONS") ?>'
            }
        },

        init: function() {

            if (!!this.params.alertOptionBlock) {
                if (Array.isArray(this.params.alertOptionBlock['checkbox']) && !!this.params.alertOptionBlock['checkbox'].length) {
                    this.params.alertOptionBlock['checkbox'].forEach($.proxy(function(optionName) {
                        const oldOptionVal = $('#' + optionName).is(':checked');
                        $('#' + optionName).on('change', $.proxy(function(e) {
                            this.insertAlertForOption(
                                $(e.target),
                                this.params.lang.save_params_for_next_action,
                                oldOptionVal != $(this).is(':checked')
                            );
                        }, this));
                    }, this));
                }

                if (
                    Array.isArray(this.params.alertOptionBlock['select']) &&
                    !!this.params.alertOptionBlock['select'].length
                ) {
                    this.params.alertOptionBlock['select'].forEach($.proxy(function(optionName) {
                        $(`select[name^="${optionName}"]`).on('change', $.proxy(function(e) {
                            this.insertAlertForOption(
                                $(e.target),
                                this.params.lang.save_params_for_next_action,
                                true
                            );
                        }, this));
                    }, this));
                }
            }

        },

        insertAlertForOption: function(node, message, isShow) {
            if (!node.closest('tr').next().hasClass(this.params.targets.custom_alert_class) && isShow) {
                node.closest('tr').after($(this.buildAlertMessageHtml(message)));
                $(`.${this.params.targets.save_btn_alert_area}`).removeClass(this.params.targets.hide_alert_class);
            }
            if (node.closest('tr').next().hasClass(this.params.targets.custom_alert_class) && !isShow) {
                node.closest('tr').next().remove();
                if ($(`.${this.params.targets.custom_alert_class}`).length <= 0) {
                    $(`.${this.params.targets.save_btn_alert_area}`).addClass(targets.hide_alert_class);
                }
            }
        },

        buildAlertMessageHtml: function(message) {
            return `
                <tr class="${this.params.targets.custom_alert_class}">
                    <td colspan="2" align="center">
                        <div class="adm-info-message-wrap" align="center">
                            <div class="ui-alert ui-alert-warning">
                                ${message}
                            </div>
                        </div>
                    </td>
                </tr>
            `;
        }
    }

    const despiLocalModuleController = {
        init: function() {

            $('input[name="pass"]').prop('type', 'password');

            $(rbsMsStocksFormNodeSelector + ' .adm-detail-tab').on('click', function() {
                activeModuleTab = $(this).attr('id').split('tab_cont_').pop();
                history.pushState(null, null, '<?= $actionStr ?>&tab=' + activeModuleTab);
            });
            if (!!activeModuleTab) {
                setTimeout(function() {
                    $('#tab_cont_' + activeModuleTab).click();
                }, 100);
            }

            $(rbsMsStocksFormNodeSelector + ' .adm-info-message').removeClass('adm-info-message').addClass('ui-alert despi-alert-info');

            $('[data-silent-head]').closest('td').css({
                background: '#f6f9f9'
            });

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
                    alert("<?= GetMessage("ERROR_AUTH_DATA") ?>");
                } else {
                    BX.ajax.runAction('rbs:moyskladstocks.api.ajax.saveAuth', {
                        data: {
                            authType: authType,
                            authData: authData,
                            profileId: rbsMsStocksProfile
                        }
                    }).then(function(response) {
                        window.location.href = '<?= $actionStr ?>';
                    }, function(response) {
                        alert("<?= GetMessage("ERROR_AUTH_DATA_AJAX") ?>");
                    });
                }

            });

            $('.js-api-ajax').on('click', function(e) {
                e.preventDefault();

                let replaceClass = $(this).data('replace-class');
                let typeReqeust = $(this).data('type-query');

                BX.ajax.runAction('rbs:moyskladstocks.api.ajax.getOptionApiRequest', {
                    data: {
                        type: typeReqeust,
                        profileId: rbsMsStocksProfile
                    }
                }).then(function(response) {
                    if (response.status === 'success') {
                        let data = JSON.parse(response.data);
                        if (data.status !== 'empty' && data.status !== 'error') {
                            $('.' + replaceClass).html(data.response);
                        } else {
                            alert('ajax error, reload page');
                        }
                    } else {
                        alert('ajax error, reload page');
                    }
                }, function(response) {
                    alert('ajax error, reload page');
                });
            });

            $('[name="main_options"] select').css({
                'max-width': '300px'
            });

            let entityList = ['product', 'variant', 'bundle', 'service'];
            entityList.forEach(function(entity) {

                let selectSelect = 'select[name="im_' + entity + '_p_proplist[]"]';
                $(selectSelect).select2({
                    closeOnSelect: false,
                    placeholder: '<?= GetMessage('START_TYPING_SEARCH_PROP') ?>',
                    width: '300px'
                });
                $(`select[name^="im_${entity}_p_prop_"]`).select2({
                    width: '300px'
                });
                $(selectSelect).on('select2:select', function(e) {

                    let data = e.params.data;

                    let iblockId = $(this).closest('table').find('select[name="im_' + entity + '_iblock"]').val();

                    despiAjaxController.get('getPropOption', {
                        entity: entity,
                        propId: data.id,
                        iblockId: iblockId,
                        profileId: rbsMsStocksProfile
                    }, function(response) {

                        if (response.status === 'success') {
                            let data = JSON.parse(response.data);
                            if (data.status === 'success') {
                                if (data.option.variants.length > 0) {

                                    let trOption = $('<tr>');
                                    let tdOptionLeft = $('<td>').addClass('adm-detail-content-cell-l').css({
                                        width: '50%'
                                    }).append(data.option.name);
                                    let tdOptionRight = $('<td>').addClass('adm-detail-content-cell-r').css({
                                        width: '50%'
                                    });
                                    let selectOption = $('<select>').attr('name', data.option.id).css({
                                        maxWidth: '300px'
                                    });

                                    $(data.option.variants).each(function() {
                                        selectOption.append($("<option>").attr('value', this.val).text(this.text));
                                    });

                                    tdOptionRight.append(selectOption);
                                    tdOptionRight.find('select').select2({
                                        width: '300px'
                                    });
                                    trOption.append(tdOptionLeft).append(tdOptionRight);
                                    $(selectSelect).closest('tr').after(trOption);

                                } else {
                                    alert('empty variants for prop');
                                }
                            } else {
                                alert('error ajax action');
                            }
                        } else {
                            alert('error ajax action');
                        }

                    });

                });

                $(selectSelect).on('select2:unselect', function(e) {
                    let data = e.params.data;
                    $('select[name="im_' + entity + '_p_prop_' + data.id + '"]').closest('tr').remove();
                });

            });

        }
    }

    const despiImportFieldSettingsTable = {
        params: {
            entityList: ['product', 'variant', 'bundle', 'service'],
            fieldSearchLabels: ['new_items', 'update_items', 'webhook_items'],
            lang_messages: {
                import_fields: '<?= GetMessage('IMPORT_SETTINGS_TABLE_FIELDS') ?>',
                new_items: '<?= GetMessage('IMPORT_SETTINGS_TABLE_NEW_ITEMS') ?>',
                current_items_agent: '<?= GetMessage('IMPORT_SETTINGS_TABLE_CURENT_ITEMS') ?>',
                current_items_web_hook: '<?= GetMessage('IMPORT_SETTINGS_TABLE_CURRENT_ITEMS_HOOK') ?>'
            },
            hint_messages: <?= CUtil::PhpToJSObject(OptionUtils::getHintsForDespiImportFieldSettingsTable()); ?>
        },

        init: function() {
            this.processTables();
            this.initEvents();
            this.initHints();
        },

        initEvents: function() {
            $(window).resize(this.debounce(this.recalcStickyTable.bind(this), 200))
                .scroll(this.debounce(this.recalcStickyTable.bind(this), 200));
            setTimeout(this.recalcStickyTable.bind(this), 200);
        },

        initHints: function() {
            let hintMsgList = this.params.hint_messages;
            $('table.despi-internal-table-params td[data-field-id]').each(function() {
                let id = $(this).data('field-id');
                if (id in hintMsgList) {
                    $(this).prepend($(`<span data-hint="${hintMsgList[id]}">`));
                }
            });
        },

        processTables: function() {
            this.params.entityList.forEach((entity) => {
                const newTable = this.createTable(entity);
                this.params.fieldSearchLabels.forEach((label) => {
                    this.parseSettingsFromOriginalTable(label, entity).forEach((value) => {
                        this.processRow(newTable, label, value);
                    });
                });
                const newTableRow = $('<tr>').append($('<td colspan="2">').append(newTable));
                $(`.heading:contains("start_new_items_${entity}")`).closest('tr').before(newTableRow);
                this.modifyCheckboxes(entity);
                this.setFieldNameAttrs(newTable);
            });
        },

        createTable: function(entity) {
            const newTable = $('<table style="margin: 1em auto !important;">').addClass('internal despi-internal-table-params').prop({
                id: `table-params-for-entity-${entity}`
            });
            const header = this.getSectionHeaderTr([
                this.params.lang_messages.import_fields,
                this.params.lang_messages.new_items,
                this.params.lang_messages.current_items_agent,
                this.params.lang_messages.current_items_web_hook
            ]);
            newTable.append(header);
            return newTable;
        },

        processRow: function(table, label, value) {
            if (value.type === 'header') return;

            let row = table.find('tr').filter((_, row) => $(row).find('td:first-child').text().trim() === value.text).first();

            if (!row.length) {
                row = $('<tr>');
                if (value.type !== 'section') {
                    row.append($('<td>').text(value.text));
                    this.params.fieldSearchLabels.forEach((subLabel) => {
                        row.append($(`<td class="${subLabel}" style="text-align:center;">`));
                    });
                } else {
                    row = this.getSectionHeaderTr([value.text, '', '', '']);
                }
            }

            if (value.type !== 'section') {
                const col = row.find('.' + label);
                if (col.length) {
                    col.append(value.input);
                }
            }
            table.append(row);
        },

        setFieldNameAttrs: function(newTable) {

            newTable.find('tr:not(.heading)').each(function() {
                let names = [];
                $(this).find('td').each(function() {
                    if (!!$(this).find('input').length) {
                        names.push($(this).find('input').attr('name'));
                    }
                })
                if (names.length > 0) {
                    const pattern = /_(?:p|up|wh)_(.*)/;
                    const baseNames = names.map(name => {
                        const match = name.match(pattern);
                        return match ? match[1] : null;
                    });
                    if (!!baseNames[0]) {
                        $(this).find('td').first().attr('data-field-id', baseNames[0]);
                    }
                }
            });

        },

        parseSettingsFromOriginalTable: function(label, entity) {
            const rows = $(`table#import_${entity}_edit_table tr`);
            const startLabel = `start_${label}_${entity}`;
            const endLabel = `end_${label}_${entity}`;
            let capture = false;
            const result = [];

            rows.each((_, row) => {
                const text = $(row).text().trim();

                if (text.includes(startLabel)) {
                    $(row).hide();
                    capture = true;
                    return;
                }

                if (text.includes(endLabel)) {
                    $(row).hide();
                    capture = false;
                    return;
                }

                if (capture) {
                    $(row).hide();
                    const item = this.parseRow(row, text);
                    if (item) result.push(item);
                }
            });

            return result;
        },

        parseRow: function(row, text) {
            if ($(row).hasClass('heading')) {
                return {
                    type: 'header',
                    text: text
                };
            }

            const input = $(row).find('input');
            if (!input.length) {
                return {
                    type: 'section',
                    text: text
                };
            }

            const labelName = $(row).find('td').first().find('label');
            return {
                type: 'input',
                text: labelName.text(),
                input: input.removeClass('adm-designed-checkbox')
            };
        },

        getSectionHeaderTr: function(tdList) {
            const tr = $('<tr>').addClass('heading');
            tdList.forEach((value) => {
                tr.append($('<td>').text(value));
            });
            return tr;
        },

        modifyCheckboxes: function(entity) {
            const checkboxList = BX.findChildren(BX(`table-params-for-entity-${entity}`), {
                tagName: 'INPUT',
                property: {
                    type: 'checkbox'
                }
            }, true);
            if (checkboxList) {
                checkboxList.forEach(checkbox => BX.adminFormTools.modifyCheckbox(checkbox));
            }
        },

        recalcStickyTable: function() {
            const topOffset = this.calculateTopOffset();
            this.params.entityList.forEach((entity) => {
                $(`#table-params-for-entity-${entity} tr:first-child`).css('top', topOffset - 5);
            });
        },

        calculateTopOffset: function() {
            let isFixedAdmPanel = !($('.adm-header-wrap').prop('style').height === 'auto' || $('.adm-header-wrap').prop('style').height == '');
            let isFixedParamPanel = !$('.bx-fixed-top').hasClass('adm-detail-tabs-block-pin');

            let topOffset = 0;

            if (isFixedAdmPanel) {
                topOffset += $('.adm-header-wrap').outerHeight();
            }

            if (isFixedParamPanel) {
                topOffset += $('.bx-fixed-top').outerHeight();
            }

            return topOffset;
        },

        debounce: function(func, wait) {
            let timeout;
            return function(...args) {
                clearTimeout(timeout);
                timeout = setTimeout(() => func.apply(this, args), wait);
            };
        }
    };

    BX.ready(function() {
        despiLocalModuleController.init();
        despiAlertOnSaveController.init();
        tabControl.SelectTab(activeModuleTab);
        despiImportFieldSettingsTable.init();
    });

    (function() {

        let moduleId = '<?= \Rbs\MoyskladStocks\Config::getModuleId(true) ?>';
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
</script>