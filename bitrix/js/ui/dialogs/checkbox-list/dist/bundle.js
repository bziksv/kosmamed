/* eslint-disable */
this.BX = this.BX || {};
(function (exports, checkboxList_css, main_core, main_core_events, main_popup, ui_designTokens, ui_vue3) {
	'use strict';

	const viewMode = {
		view: 'view',
		edit: 'edit'
	};
	const CheckboxListOption = {
		props: ['id', 'title', 'isChecked', 'isLocked', 'isEditable', 'context'],
		emits: ['onToggleOption'],
		data() {
			return {
				viewMode: viewMode.view,
				titleData: this.title,
				isCheckedValue: this.isChecked
			};
		},
		methods: {
			getId() {
				return this.id;
			},
			getValue() {
				return this.isCheckedValue;
			},
			setValue(value) {
				this.isCheckedValue = value;
			},
			getTitle() {
				var _this$$refs$title$inn, _this$$refs$title;
				return (_this$$refs$title$inn = (_this$$refs$title = this.$refs.title) === null || _this$$refs$title === void 0 ? void 0 : _this$$refs$title.innerText) !== null && _this$$refs$title$inn !== void 0 ? _this$$refs$title$inn : this.titleData;
			},
			setTitle(title) {
				this.titleData = title;
			},
			setStateFromProps() {
				let value = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
				this.viewMode = viewMode.view;
				this.titleData = this.title;
				this.isCheckedValue = value === null ? this.isChecked : value;
			},
			getOptionClassName(_ref) {
				let {
					isChecked,
					isLocked
				} = _ref;
				return ['ui-ctl', 'ui-ctl-checkbox', 'ui-checkbox-list__field-item_label', {
					'--checked': isChecked
				}, {
					'--disabled': isLocked
				}, {
					'--editable': !(this.isViewMode || isLocked)
				}];
			},
			getLabelClassName() {
				return ['ui-ctl-label-text', 'ui-checkbox-list__field-item_text', {
					'--editable': this.isEditMode && !this.isLocked
				}];
			},
			emitHandleCheckBox(event) {
				setTimeout(() => {
					const {
						id,
						title,
						isChecked,
						isLocked,
						isEditable,
						context
					} = this;
					main_core_events.EventEmitter.emit('ui:checkbox-list:check-option', {
						id,
						title,
						isChecked,
						isLocked,
						isEditable,
						context,
						viewMode: this.viewMode
					});
				});
			},
			handleCheckBox(event) {
				if (this.isLocked) {
					// eslint-disable-next-line no-param-reassign
					event.target.checked = !event.target.checked;
				} else {
					this.isCheckedValue = !this.isCheckedValue;
				}
				const {
					id,
					title,
					isLocked,
					isCheckedValue,
					isEditable,
					context
				} = this;
				this.$emit('onToggleOption', {
					id,
					title,
					isChecked: isCheckedValue,
					isLocked,
					isEditable,
					context,
					viewMode: this.viewMode
				});
			},
			onToggleViewMode() {
				this.viewMode = this.isEditMode ? viewMode.view : viewMode.edit;
				if (this.viewMode === viewMode.view) {
					return;
				}
				void this.$nextTick(() => this.setFocusOnTitle());
			},
			setFocusOnTitle() {
				this.$refs.title.focus();
				const range = document.createRange();
				const selection = window.getSelection();
				range.selectNodeContents(this.$refs.title);
				range.collapse(false);
				selection.removeAllRanges();
				selection.addRange(range);
			},
			onChangeTitle(_ref2) {
				let {
					target
				} = _ref2;
				this.titleData = target.innerText;
			}
		},
		computed: {
			isEditMode() {
				return this.viewMode === viewMode.edit;
			},
			isViewMode() {
				return this.viewMode === viewMode.view;
			},
			labelClassName() {
				return this.getLabelClassName();
			}
		},
		template: "\n\t\t<label\n\t\t\t:title=\"titleData\"\n\t\t\t:class=\"getOptionClassName({ isChecked: isCheckedValue, isLocked })\"\n\t\t\t@click=\"this.emitHandleCheckBox\"\n\t\t>\n\t\t\t<input\n\t\t\t\ttype=\"checkbox\"\n\t\t\t\tclass=\"ui-ctl-element ui-checkbox-list__field-item_input\"\n\t\t\t\t:checked=\"isCheckedValue\"\n\t\t\t\t@click=\"this.handleCheckBox\"\n\t\t\t>\n\t\t\t<div\n\t\t\t\t:class=\"labelClassName\"\n\t\t\t\t:contenteditable=\"(isViewMode || isLocked) ? 'false' : 'true'\"\n\t\t\t\t@keydown.enter.prevent\n\t\t\t\t@blur=\"onChangeTitle\"\n\t\t\t\tref=\"title\"\n\t\t\t>\n\t\t\t\t{{ titleData }}\n\t\t\t</div>\n\t\n\t\t\t<div v-if=\"isLocked\" class=\"ui-checkbox-list__field-item_locked\"></div>\n\t\t\t<div\n\t\t\t\tv-else-if=\"isEditable\"\n\t\t\t\tclass=\"ui-checkbox-list__field-item_edit\"\n\t\t\t\t@click.prevent=\"onToggleViewMode\"\n\t\t\t></div>\n\t\t</label>\n\t"
	};

	const CheckboxListCategory = {
		props: ['columnCount', 'category', 'options', 'context', 'isActiveSearch', 'isEditableOptionsTitle', 'onChange', 'setOptionRef'],
		emits: ['onToggleOption'],
		components: {
			CheckboxListOption
		},
		methods: {
			setRef(ref) {
				if (ref) {
					this.setOptionRef(ref.getId(), ref);
				}
			},
			onToggleOption(event) {
				this.$emit('onToggleOption', event);
			}
		},
		template: "\n\t\t<div\n\t\t\tv-if=\"options.length > 0 || !isActiveSearch\"\n\t\t\tclass=\"ui-checkbox-list__category\"\n\t\t>\n\t\t\t<div v-if=\"category\" class=\"ui-checkbox-list__categories-title\">\n\t\t\t\t{{ category.title }}\n\t\t\t</div>\n\t\t\t<div \n\t\t\t\tclass=\"ui-checkbox-list__options\"\n\t\t\t\t:style=\"{ 'column-count': columnCount }\"\n\t\t\t>\n\t\t\t\t<div\n\t\t\t\t\tv-for=\"option in options\"\n\t\t\t\t\t:key=\"option.id\"\n\t\t\t\t>\n\t\t\t\t\t<checkbox-list-option\n\t\t\t\t\t\t:context=\"context\"\n\t\t\t\t\t\t:id=\"option.id\"\n\t\t\t\t\t\t:title=\"option.title ?? option.id\"\n\t\t\t\t\t\t:isChecked=\"option.value\"\n\t\t\t\t\t\t:isLocked=\"option?.locked\"\n\t\t\t\t\t\t:isEditable=\"isEditableOptionsTitle\"\n\t\t\t\t\t\t:ref=\"setRef\"\n\t\t\t\t\t\t@onToggleOption=\"onToggleOption\"\n\t\t\t\t\t/>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	const CheckboxComponent = {
		props: ['id', 'title'],
		data() {
			return {
				dataTitle: this.title,
				dataId: this.id,
				checked: false
			};
		},
		methods: {
			handleClick(key) {
				this.checked = !this.checked;
				this.$emit('onToggled', this.checked);
			}
		},
		template: "\n\t\t<div class=\"ui-checkbox-list__footer-custom-element --checkbox\" @click=\"handleClick\">\n\t\t\t<input type=\"checkbox\" :name=\"dataId\" v-model=\"checked\">\n\t\t\t<label :for=\"dataId\">{{ dataTitle }}</label>\n\t\t</div>\n\t"
	};

	const TextToggleComponent = {
		props: ['id', 'title', 'dataItems'],
		data() {
			return {
				dataTitle: this.title,
				dataId: this.id,
				value: null
			};
		},
		methods: {
			handleClick(key) {
				let index = this.dataItems.findIndex(item => item.value === this.value);
				if (index >= this.dataItems.length - 1) {
					index = 0;
				} else {
					index++;
				}
				this.value = this.dataItems[index].value;
				this.$emit('onToggled', this.value);
			}
		},
		computed: {
			currentLabel() {
				var _this$dataItems$find;
				if (this.value === null && main_core.Type.isArrayFilled(this.dataItems)) {
					this.value = this.dataItems[0].value;
					return this.dataItems[0].label;
				}
				return (_this$dataItems$find = this.dataItems.find(item => item.value === this.value)) === null || _this$dataItems$find === void 0 ? void 0 : _this$dataItems$find.label;
			}
		},
		template: "\n\t\t<div class=\"ui-checkbox-list__footer-custom-element --texttoggle\" @click=\"handleClick\">\n\t\t\t<span class=\"ui-checkbox-list__texttoggle__title\">{{ dataTitle }}</span>\n\t\t\t<span class=\"ui-checkbox-list__texttoggle__value\">{{ currentLabel }}</span>\n\t\t\t<input type=\"hidden\" :name=\"dataId\" v-model=\"value\">\n\t\t</div>\n\t"
	};

	const CheckboxListSections = {
		props: ['sections'],
		methods: {
			handleClick(key) {
				this.$emit('sectionToggled', key);
			},
			getSectionsItemClassName(sectionValue) {
				return ['ui-checkbox-list__sections-item', {
					'--checked': sectionValue
				}];
			}
		},
		template: "\n\t\t<div class=\"ui-checkbox-list__sections\">\n\t\t\t<div \n\t\t\t\tv-for=\"section in sections\"\n\t\t\t\t:key=\"section.key\"\n\t\t\t\t:title=\"section.title\"\n\t\t\t\t:class=\"getSectionsItemClassName(section.value)\"\n\t\t\t\t@click=\"handleClick(section.key)\"\n\t\t\t>\n\t\t\t\t<div class=\"ui-checkbox-list__check-box\"></div>\n\t\t\t\t<div class=\"ui-checkbox-list__section_title\">{{ section.title }}</div>\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	const Content = {
		components: {
			CheckboxListSections,
			CheckboxListCategory,
			CheckboxComponent,
			TextToggleComponent
		},
		props: ['dialog', 'popup', 'columnCount', 'compactField', 'customFooterElements', 'lang', 'sections', 'categories', 'options', 'params', 'context'],
		data() {
			return {
				dataSections: this.sections,
				dataCategories: this.categories,
				dataCompactField: this.compactField,
				dataOptions: this.getPreparedDataOptions(),
				dataParams: this.getPreparedParams(),
				optionsRef: new Map(),
				search: '',
				longContent: false,
				scrollIsBottom: true,
				scrollIsTop: false
			};
		},
		methods: {
			getPreparedDataOptions() {
				return new Map(this.options.map(option => [option.id, option]));
			},
			getPreparedParams() {
				var _params$useSearch, _params$useSectioning, _params$closeAfterApp, _params$showBackToDef, _params$isEditableOpt, _params$destroyPopupA;
				const {
					params
				} = this;
				return {
					useSearch: Boolean((_params$useSearch = params.useSearch) !== null && _params$useSearch !== void 0 ? _params$useSearch : true),
					useSectioning: Boolean((_params$useSectioning = params.useSectioning) !== null && _params$useSectioning !== void 0 ? _params$useSectioning : true),
					closeAfterApply: Boolean((_params$closeAfterApp = params.closeAfterApply) !== null && _params$closeAfterApp !== void 0 ? _params$closeAfterApp : true),
					showBackToDefaultSettings: Boolean((_params$showBackToDef = params.showBackToDefaultSettings) !== null && _params$showBackToDef !== void 0 ? _params$showBackToDef : true),
					isEditableOptionsTitle: Boolean((_params$isEditableOpt = params.isEditableOptionsTitle) !== null && _params$isEditableOpt !== void 0 ? _params$isEditableOpt : false),
					destroyPopupAfterClose: Boolean((_params$destroyPopupA = params.destroyPopupAfterClose) !== null && _params$destroyPopupA !== void 0 ? _params$destroyPopupA : true)
				};
			},
			renderSwitcher() {
				if (this.dataCompactField) {
					new BX.UI.Switcher({
						node: this.$refs.switcher,
						checked: this.dataCompactField.value,
						size: 'small',
						handlers: {
							toggled: () => this.handleSwitcherToggled()
						}
					});
				}
			},
			handleSwitcherToggled() {
				if (this.dataCompactField) {
					this.dataCompactField.value = !this.dataCompactField.value;
				}
			},
			clearSearch() {
				this.search = '';
			},
			handleClearSearchButtonClick() {
				this.setFocusToSearchInput();
				this.clearSearch();
			},
			setFocusToSearchInput() {
				var _this$$refs;
				(_this$$refs = this.$refs) === null || _this$$refs === void 0 || (_this$$refs = _this$$refs.searchInput) === null || _this$$refs === void 0 || _this$$refs.focus();
			},
			handleSectionsToggled(key) {
				const section = this.dataSections.find(item => item.key === key);
				if (section) {
					section.value = !section.value;
				}
			},
			getOptionsByCategory() {
				let category = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : null;
				return this.getOptions().filter(item => item.categoryKey === category);
			},
			getOptions() {
				return this.optionsByTitle;
			},
			getCheckedOptionsId() {
				return this.getCheckedOptions().map(option => option.getId());
			},
			getCheckedOptions() {
				return this.getOptionRefs().filter(option => option.getValue());
			},
			checkLongContent() {
				if (this.$refs.container) {
					this.longContent = this.$refs.container.clientHeight < this.$refs.container.scrollHeight;
				} else {
					this.longContent = false;
				}
			},
			getBottomIndent() {
				const {
					scrollTop,
					clientHeight,
					scrollHeight
				} = this.$refs.container;
				this.scrollIsBottom = scrollTop + clientHeight < scrollHeight - 10;
			},
			getTopIndent() {
				this.scrollIsTop = this.$refs.container.scrollTop;
			},
			handleScroll() {
				this.getBottomIndent();
				this.getTopIndent();
			},
			handleSearchEscKeyUp() {
				this.$refs.container.focus();
				this.clearSearch();
			},
			defaultSettings() {
				const event = new main_core_events.BaseEvent({
					data: {
						switcher: this.dataCompactField,
						fields: this.getCheckedOptionsId()
					}
				});
				main_core_events.EventEmitter.emit(this.dialog, 'onDefault', event);
				if (event.isDefaultPrevented()) {
					return;
				}
				this.clearSearch();
				const {
					dataCompactField,
					sections,
					categories,
					$refs
				} = this;
				if (dataCompactField && dataCompactField.value !== dataCompactField.defaultValue) {
					$refs.switcher.click();
				}
				this.dataSections = sections;
				this.dataOptions = this.getPreparedDataOptions();
				this.dataCategories = categories;
				this.setDefaultValuesForOptions();
			},
			setDefaultValuesForOptions() {
				void this.$nextTick(() => {
					this.getOptionRefs().forEach(option => option.setValue(this.dataOptions.get(option.getId()).defaultValue));
				});
			},
			toggleOption(id) {
				const option = this.optionsRef.get(id);
				if (!option) {
					return;
				}
				option.setValue(!option.getValue());
			},
			onSelectAllClick() {
				if (this.isAllSelected) {
					this.deselectAll();
				} else {
					this.selectAll();
				}
			},
			select(id) {
				let value = arguments.length > 1 && arguments[1] !== undefined ? arguments[1] : true;
				const option = this.getOptionRefs().find(item => item.id === id);
				option === null || option === void 0 || option.setValue(value);
			},
			selectAll() {
				this.setValueForAllVisibleOptions(true);
			},
			deselectAll() {
				this.setValueForAllVisibleOptions(false);
			},
			setValueForAllVisibleOptions(value) {
				const visibleOptionIds = new Set(this.getOptions().map(option => option.id));
				this.getOptionRefs().forEach(option => {
					if (option.isLocked || !visibleOptionIds.has(option.getId())) {
						return;
					}
					this.dataOptions.get(option.getId()).value = value;
					option.setValue(value);
				});
			},
			getOptionRefs() {
				return [...this.optionsRef.values()];
			},
			cancel() {
				main_core_events.EventEmitter.emit(this.dialog, 'onCancel');
				this.restoreOptionValues();
				this.destroyOrClosePopup();
			},
			restoreOptionValues() {
				this.getOptionRefs().forEach(option => option.setStateFromProps());
			},
			apply() {
				if (this.isCheckedCheckboxes) {
					return;
				}
				const fields = this.getCheckedOptionsId();
				const eventParams = {
					switcher: this.dataCompactField,
					fields,
					data: {
						titles: this.getOptionTitles()
					}
				};
				main_core_events.EventEmitter.emit(this.dialog, 'onApply', eventParams);
				this.adjustOptions(fields);
				if (this.dataParams.closeAfterApply) {
					this.destroyOrClosePopup();
				}
			},
			getOptionTitles() {
				const titles = {};
				this.getOptionRefs().forEach(option => {
					titles[option.getId()] = option.getTitle();
				});
				return titles;
			},
			adjustOptions() {
				let checkedFieldIds = arguments.length > 0 && arguments[0] !== undefined ? arguments[0] : [];
				for (const option of this.optionsRef.values()) {
					const id = option.getId();
					const value = checkedFieldIds.includes(id);
					this.dataOptions.set(id, {
						...this.dataOptions.get(id),
						title: option.getTitle(),
						value
					});
					void this.$nextTick(() => option.setStateFromProps(value));
				}
			},
			destroyOrClosePopup() {
				if (this.dataParams.destroyPopupAfterClose) {
					this.destroyPopup();
				} else {
					this.closePopup();
				}
			},
			destroyPopup() {
				this.popup.destroy();
			},
			closePopup() {
				this.popup.close();
			},
			setOptionRef(id, ref) {
				this.optionsRef.set(id, ref);
			},
			isAllSectionsDisabled() {
				return main_core.Type.isArrayFilled(this.dataSections) && this.dataSections.every(section => section.value === false);
			},
			onToggleOption(event) {
				if (this.dataOptions.has(event.id)) {
					const option = this.dataOptions.get(event.id);
					option.value = event.isChecked;
					this.dataOptions.set(event.id, option);
				}
			}
		},
		watch: {
			search() {
				void this.$nextTick(() => this.checkLongContent());
			},
			categoryBySection() {
				void this.$nextTick(() => this.checkLongContent());
			}
		},
		computed: {
			visibleOptions() {
				const {
					dataSections,
					optionsByTitle,
					dataCategories
				} = this;
				if (!main_core.Type.isArrayFilled(dataSections)) {
					return optionsByTitle;
				}
				return optionsByTitle.filter(option => {
					const category = dataCategories.find(item => item.key === option.categoryKey);
					const section = dataSections.find(item => item.key === category.sectionKey);
					return section === null || section === void 0 ? void 0 : section.value;
				});
			},
			isEmptyContent() {
				return main_core.Type.isArrayFilled(this.visibleOptions);
			},
			// @temporary temp, waiting for a new ui for this case
			isNarrowWidth() {
				return window.innerWidth * 0.9 < 500;
			},
			isSearchDisabled() {
				if (main_core.Type.isArrayFilled(this.dataSections)) {
					return !this.dataSections.some(section => section.value);
				}
				return false;
			},
			isCheckedCheckboxes() {
				for (const option of this.optionsRef.values()) {
					if (option.getValue() === true && option.locked !== true) {
						return false;
					}
				}
				return true;
			},
			optionsByTitle() {
				const options = [...this.dataOptions.values()];
				const searchString = this.search.toLowerCase();
				return options.filter(item => {
					var _item$title;
					return ((_item$title = item.title) !== null && _item$title !== void 0 ? _item$title : item.id).toLowerCase().includes(searchString);
				});
			},
			categoryBySection() {
				if (!main_core.Type.isArrayFilled(this.dataSections)) {
					return this.dataCategories;
				}
				return this.dataCategories.filter(category => {
					const section = this.dataSections.find(item => category.sectionKey === item.key);
					return section === null || section === void 0 ? void 0 : section.value;
				});
			},
			wrapperClassName() {
				return ['ui-checkbox-list__wrapper', {
					'--long': this.longContent
				}, {
					'--bottom': this.scrollIsBottom
				}, {
					'--top': this.scrollIsTop
				}];
			},
			searchClassName() {
				return ['ui-checkbox-list__search', {
					'--disabled': this.isSearchDisabled
				}];
			},
			applyClassName() {
				return ['ui-btn ui-btn-primary', {
					'ui-btn-disabled': this.isCheckedCheckboxes
				}];
			},
			selectAllClassName() {
				return ['ui-checkbox-list__footer-link --select-all', {
					'--narrow': this.isNarrowWidth
				}];
			},
			switcherText() {
				return main_core.Type.isStringFilled(this.lang.switcher) ? this.lang.switcher : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_SWITCHER');
			},
			placeholderText() {
				return main_core.Type.isStringFilled(this.lang.placeholder) ? this.lang.placeholder : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_PLACEHOLDER');
			},
			defaultSettingsBtnText() {
				return main_core.Type.isStringFilled(this.lang.defaultBtn) ? this.lang.defaultBtn : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_MSGVER_1');
			},
			applyBtnText() {
				return main_core.Type.isStringFilled(this.lang.acceptBtn) ? this.lang.acceptBtn : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_ACCEPT_BUTTON');
			},
			cancelBtnText() {
				return main_core.Type.isStringFilled(this.lang.cancelBtn) ? this.lang.cancelBtn : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_CANCEL_BUTTON');
			},
			selectAllBtnText() {
				return main_core.Type.isStringFilled(this.lang.selectAllBtn) ? this.lang.selectAllBtn : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SELECT_ALL_MSGVER_1');
			},
			emptyStateTitleText() {
				if (this.isAllSectionsDisabled()) {
					return main_core.Type.isStringFilled(this.lang.allSectionsDisabledTitle) ? this.lang.allSectionsDisabledTitle : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_EMPTY_STATE_TITLE_MSGVER_1');
				}
				return main_core.Type.isStringFilled(this.lang.emptyStateTitle) ? this.lang.emptyStateTitle : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_EMPTY_STATE_TITLE_MSGVER_1');
			},
			emptyStateDescriptionText() {
				if (this.isAllSectionsDisabled()) {
					return '';
				}
				return main_core.Type.isStringFilled(this.lang.emptyStateDescription) ? this.lang.emptyStateDescription : main_core.Loc.getMessage('UI_CHECKBOX_LIST_DEFAULT_SETTINGS_EMPTY_STATE_DESCRIPTION_MSGVER_1');
			},
			isAllSelected() {
				const isAllSelected = this.getOptionRefs().filter(option => !option.isLocked).every(option => option.getValue() === true);
				const isSomeSelected = this.getOptionRefs().filter(option => !option.isLocked).some(option => option.getValue() === true && !option.isLocked);
				if (!isAllSelected && isSomeSelected && this.$refs.selectAllCheckbox) {
					this.$refs.selectAllCheckbox.indeterminate = true;
					return false;
				}
				if (this.$refs.selectAllCheckbox) {
					this.$refs.selectAllCheckbox.indeterminate = false;
				}
				return isAllSelected;
			}
		},
		mounted() {
			this.renderSwitcher();
			void this.$nextTick(() => {
				this.checkLongContent();
				this.setFocusToSearchInput();
			});
		},
		template: "\n\t\t<div class=\"ui-checkbox-list\">\n\t\t\t<div\n\t\t\t\tclass=\"ui-checkbox-list__header\"\n\t\t\t\tv-if=\"dataParams.useSearch || (dataSections && dataParams.useSectioning)\"\n\t\t\t>\n\t\t\t\t<div class=\"ui-checkbox-list__header_options\">\n\t\t\t\t\t<div\n\t\t\t\t\t\tv-if=\"dataCompactField\"\n\t\t\t\t\t\tclass=\"ui-checkbox-list__switcher\"\n\t\t\t\t\t>\n\t\t\t\t\t\t<div class=\"ui-checkbox-list__switcher-text\">\n\t\t\t\t\t\t\t{{ switcherText }}\n\t\t\t\t\t\t</div>\n\t\t\t\t\t\t<div class=\"switcher\" ref=\"switcher\"></div>\n\t\t\t\t\t</div>\n\t\t\t\t\t<div\n\t\t\t\t\t\tv-if=\"dataParams.useSearch\"\n\t\t\t\t\t\t:class=\"searchClassName\"\n\t\t\t\t\t>\n\t\t\t\t\t\t<div class=\"ui-checkbox-list__search-wrapper\">\n\t\t\t\t\t\t\t<div class=\"ui-ctl ui-ctl-textbox ui-ctl-after-icon ui-ctl-w100\">\n\t\t\t\t\t\t\t\t<input\n\t\t\t\t\t\t\t\t\t:placeholder=\"placeholderText\"\n\t\t\t\t\t\t\t\t\ttype=\"text\"\n\t\t\t\t\t\t\t\t\tclass=\"ui-ctl-element\"\n\t\t\t\t\t\t\t\t\tv-model=\"search\"\n\t\t\t\t\t\t\t\t\t@keyup.esc.stop=\"handleSearchEscKeyUp\"\n\t\t\t\t\t\t\t\t\tref=\"searchInput\"\n\t\t\t\t\t\t\t\t>\n\t\t\t\t\t\t\t\t<button\n\t\t\t\t\t\t\t\t\tv-if=\"search.length > 0\"\n\t\t\t\t\t\t\t\t\t@click=\"handleClearSearchButtonClick\"\n\t\t\t\t\t\t\t\t\tclass=\"ui-ctl-after ui-ctl-icon-clear ui-checkbox-list__search-clear\"\n\t\t\t\t\t\t\t\t></button>\n\t\t\t\t\t\t\t\t<div\n\t\t\t\t\t\t\t\t\tv-else\n\t\t\t\t\t\t\t\t\tclass=\"ui-ctl-after ui-ctl-icon-search\"\n\t\t\t\t\t\t\t\t></div>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<checkbox-list-sections\n\t\t\t\t\tv-if=\"dataSections && dataParams.useSectioning\"\n\t\t\t\t\t:sections=\"dataSections\"\n\t\t\t\t\t@sectionToggled=\"handleSectionsToggled\"\n\t\t\t\t/>\n\t\t\t</div>\n\n\t\t\t<div\n\t\t\t\tref=\"wrapper\"\n\t\t\t\t:class=\"wrapperClassName\"\n\t\t\t>\n\t\t\t\t<div\n\t\t\t\t\tref=\"container\"\n\t\t\t\t\tclass=\"ui-checkbox-list__container\"\n\t\t\t\t\t@scroll=\"handleScroll\"\n\t\t\t\t\ttabindex=\"0\"\n\t\t\t\t\tv-if=\"isEmptyContent\"\n\t\t\t\t>\n\t\t\t\t\t<checkbox-list-category\n\t\t\t\t\t\tv-if=\"dataParams.useSectioning\"\n\t\t\t\t\t\tv-for=\"category in categoryBySection\"\n\t\t\t\t\t\t:key=\"category.key\"\n\t\t\t\t\t\t:context=\"context\"\n\t\t\t\t\t\t:category=\"category\"\n\t\t\t\t\t\t:columnCount=\"columnCount\"\n\t\t\t\t\t\t:options=\"getOptionsByCategory(category.key)\"\n\t\t\t\t\t\t:isActiveSearch=\"search.length > 0\"\n\t\t\t\t\t\t:isEditableOptionsTitle=\"dataParams.isEditableOptionsTitle\"\n\t\t\t\t\t\t:setOptionRef=\"setOptionRef\"\n\t\t\t\t\t\t@onToggleOption=\"onToggleOption\"\n\t\t\t\t\t/>\n\t\n\t\t\t\t\t<checkbox-list-category\n\t\t\t\t\t\tv-else\n\t\t\t\t\t\t:context=\"context\"\n\t\t\t\t\t\t:columnCount=\"columnCount\"\n\t\t\t\t\t\t:options=\"getOptions()\"\n\t\t\t\t\t\t:isActiveSearch=\"search.length > 0\"\n\t\t\t\t\t\t:isEditableOptionsTitle=\"dataParams.isEditableOptionsTitle\"\n\t\t\t\t\t\t:setOptionRef=\"setOptionRef\"\n\t\t\t\t\t\t@onToggleOption=\"onToggleOption\"\n\t\t\t\t\t/>\n\t\t\t\t</div>\n\t\t\t\t<div\n\t\t\t\t\tv-else\n\t\t\t\t\tclass=\"ui-checkbox-list__empty\"\n\t\t\t\t>\n\t\t\t\t\t<img\n\t\t\t\t\t\tsrc=\"/bitrix/js/ui/dialogs/checkbox-list/images/ui-checkbox-list-empty.svg\"\n\t\t\t\t\t\t:alt=\"emptyStateTitleText\"\n\t\t\t\t\t>\n\t\t\t\t\t<div class=\"ui-checkbox-list__empty-title\">\n\t\t\t\t\t\t{{ emptyStateTitleText }}\n\t\t\t\t\t</div>\n\t\t\t\t\t<div class=\"ui-checkbox-list__empty-description\">\n\t\t\t\t\t\t{{ emptyStateDescriptionText }}\n\t\t\t\t\t</div>\n\t\n\t\t\t\t\t<div\n\t\t\t\t\t\tclass=\"ui-checkbox-list__options\"\n\t\t\t\t\t\t:style=\"{ 'column-count': columnCount, opacity: 0 }\"\n\t\t\t\t\t>\n\t\t\t\t\t\t<div>\n\t\t\t\t\t\t\t<label class=\"ui-ctl\"></label>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t</div>\n\n\t\t\t<div class=\"ui-checkbox-list__footer\">\n\t\t\t\t<div class=\"ui-checkbox-list__footer-block --left\">\n\t\t\t\t\t<div\n\t\t\t\t\t\t@click=\"onSelectAllClick()\"\n\t\t\t\t\t\t:class=\"selectAllClassName\"\n\t\t\t\t\t>\n\t\t\t\t\t\t<input \n\t\t\t\t\t\t\ttype=\"checkbox\" \n\t\t\t\t\t\t\tname=\"selectAllCheckbox\"\n\t\t\t\t\t\t\tref=\"selectAllCheckbox\"\n\t\t\t\t\t\t\tv-model=\"isAllSelected\"\n\t\t\t\t\t\t>\n\t\t\t\t\t\t<label\n\t\t\t\t\t\t\tv-if=\"!isNarrowWidth\"\n\t\t\t\t\t\t\tfor=\"selectAllCheckbox\"\n\t\t\t\t\t\t>\n\t\t\t\t\t\t\t{{ selectAllBtnText }}\n\t\t\t\t\t\t</label>\n\t\t\t\t\t</div>\n\t\n\t\t\t\t\t<div\n\t\t\t\t\t\tv-if=\"customFooterElements\"\n\t\t\t\t\t\tv-for=\"customElement in customFooterElements\"\n\t\t\t\t\t>\n\t\t\t\t\t\t<checkbox-component\n\t\t\t\t\t\t\tv-if=\"customElement.type === 'checkbox'\"\n\t\t\t\t\t\t\t:id=\"customElement.id\"\n\t\t\t\t\t\t\t:title=\"customElement.title\"\n\t\t\t\t\t\t\t@onToggled=\"customElement.onClick\"\n\t\t\t\t\t\t/>\n\t\t\t\t\t\t<text-toggle-component\n\t\t\t\t\t\t\tv-if=\"customElement.type === 'textToggle'\"\n\t\t\t\t\t\t\t:id=\"customElement.id\"\n\t\t\t\t\t\t\t:title=\"customElement.title\"\n\t\t\t\t\t\t\t:dataItems=\"customElement.dataItems\"\n\t\t\t\t\t\t\t@onToggled=\"customElement.onClick\"\n\t\t\t\t\t\t/>\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"ui-checkbox-list__footer-block --right\">\n\t\t\t\t\t<div\n\t\t\t\t\t\tv-if=\"dataParams.showBackToDefaultSettings\"\n\t\t\t\t\t\tclass=\"ui-checkbox-list__footer-link --default\"\n\t\t\t\t\t\t@click=\"defaultSettings()\"\n\t\t\t\t\t>\n\t\t\t\t\t\t{{ defaultSettingsBtnText }}\n\t\t\t\t\t</div>\n\t\t\t\t</div>\n\t\t\t\t<div class=\"ui-checkbox-list__footer-block --center\">\n\t\t\t\t\t<button\n\t\t\t\t\t\t@click=\"apply()\"\n\t\t\t\t\t\t:class=\"applyClassName\"\n\t\t\t\t\t>\n\t\t\t\t\t\t{{ applyBtnText }}\n\t\t\t\t\t</button>\n\t\t\t\t\t<button\n\t\t\t\t\t\t@click=\"cancel()\"\n\t\t\t\t\t\tclass=\"ui-btn ui-btn-link\"\n\t\t\t\t\t>\n\t\t\t\t\t\t{{ cancelBtnText }}\n\t\t\t\t\t</button>\n\t\t\t\t</div>\n\t\t\t</div>\n\t\t</div>\n\t"
	};

	function _classPrivateMethodInitSpec(e, a) { _checkPrivateRedeclaration(e, a), a.add(e); }
	function _checkPrivateRedeclaration(e, t) { if (t.has(e)) throw new TypeError("Cannot initialize the same private elements twice on an object"); }
	function _assertClassBrand(e, t, n) { if ("function" == typeof e ? e === t : e.has(t)) return arguments.length < 3 ? t : n; throw new TypeError("Private element is not present on this object"); }
	var _CheckboxList_brand = /*#__PURE__*/new WeakSet();
	class CheckboxList extends main_core_events.EventEmitter {
		constructor(options) {
			var _this$params$useSecti;
			super();
			_classPrivateMethodInitSpec(this, _CheckboxList_brand);
			babelHelpers.defineProperty(this, "layoutApp", null);
			babelHelpers.defineProperty(this, "layoutComponent", null);
			this.setEventNamespace('BX.UI.Dialogs.CheckboxList');
			this.subscribeFromOptions(options.events);
			this.context = main_core.Type.isPlainObject(options.context) ? options.context : null;
			this.compactField = main_core.Type.isPlainObject(options.compactField) ? options.compactField : null;
			this.sections = main_core.Type.isArray(options.sections) ? options.sections : null;
			this.lang = main_core.Type.isPlainObject(options.lang) ? options.lang : {};
			this.popup = null;
			this.columnCount = main_core.Type.isNumber(options.columnCount) ? options.columnCount : 4;
			this.popupOptions = main_core.Type.isPlainObject(options.popupOptions) ? options.popupOptions : {};
			this.params = main_core.Type.isPlainObject(options.params) ? options.params : {};
			const useSectioning = (_this$params$useSecti = this.params.useSectioning) !== null && _this$params$useSecti !== void 0 ? _this$params$useSecti : true;
			if (useSectioning && !main_core.Type.isArray(options.categories)) {
				throw new Error('CheckboxList: "categories" parameter is required.');
			}
			this.categories = options.categories;
			if (useSectioning && !main_core.Type.isArray(options.options)) {
				throw new Error('CheckboxList: "options" parameter is required.');
			}
			this.options = options.options;
			this.customFooterElements = main_core.Type.isArrayFilled(options.customFooterElements) ? options.customFooterElements : [];
			this.closeAfterApply = main_core.Type.isBoolean(options.closeAfterApply) ? options.closeAfterApply : true;
		}
		getPopup() {
			const container = main_core.Dom.create('div');
			main_core.Dom.addClass(container, 'ui-checkbox-list__app-container');
			if (!this.popup) {
				const {
					lang,
					layoutComponent,
					popupOptions
				} = this;
				const {
					innerWidth,
					innerHeight
				} = window;
				this.popup = new main_popup.Popup({
					className: 'ui-checkbox-list-popup',
					width: 997,
					maxWidth: Math.round(innerWidth * 0.9),
					overlay: true,
					autoHide: true,
					minHeight: 200,
					maxHeight: Math.round(innerHeight * 0.9),
					borderRadius: 20,
					contentPadding: 0,
					contentBackground: 'transparent',
					animation: 'fading-slide',
					titleBar: lang.title,
					content: container,
					closeIcon: true,
					closeByEsc: true,
					...popupOptions,
					events: {
						onPopupClose: () => layoutComponent === null || layoutComponent === void 0 ? void 0 : layoutComponent.restoreOptionValues()
					}
				});
				const {
					compactField,
					customFooterElements,
					sections,
					categories,
					options,
					popup,
					params,
					context
				} = this;
				this.layoutApp = ui_vue3.BitrixVue.createApp(Content, {
					compactField,
					customFooterElements,
					lang,
					sections,
					categories,
					options,
					popup,
					columnCount: _assertClassBrand(_CheckboxList_brand, this, _getColumnCount).call(this),
					params,
					context,
					dialog: this
				});

				// eslint-disable-next-line unicorn/consistent-destructuring
				this.layoutComponent = this.layoutApp.mount(container);
			}
			return this.popup;
		}
		show() {
			this.getPopup().show();
			_assertClassBrand(_CheckboxList_brand, this, _getLayoutComponent).call(this).setFocusToSearchInput();
		}
		hide() {
			var _this$layoutComponent;
			(_this$layoutComponent = this.layoutComponent) === null || _this$layoutComponent === void 0 || _this$layoutComponent.destroyOrClosePopup();
		}
		destroy() {
			if (!this.layoutApp) {
				return;
			}
			this.hide();
			this.layoutApp.unmount();
			this.layoutComponent = null;
			this.popup = null;
		}
		isShown() {
			return this.popup && this.popup.isShown();
		}
		getOptions() {
			return _assertClassBrand(_CheckboxList_brand, this, _getLayoutComponent).call(this).getOptions();
		}
		getSelectedOptions() {
			return _assertClassBrand(_CheckboxList_brand, this, _getLayoutComponent).call(this).getCheckedOptionsId();
		}
		handleSwitcherToggled(id) {
			return _assertClassBrand(_CheckboxList_brand, this, _getLayoutComponent).call(this).handleSwitcherToggled(id);
		}
		handleOptionToggled(id) {
			return _assertClassBrand(_CheckboxList_brand, this, _getLayoutComponent).call(this).toggleOption(id);
		}
		saveColumns(columnIds, callback) {
			if (!main_core.Type.isArrayFilled(columnIds)) {
				return;
			}
			columnIds.forEach(id => this.selectOption(id));
			this.apply();
		}
		selectOption(id, value) {
			// to maintain backward compatibility without creating dependencies on main within the ticket #187991
			// @todo remove later and set default value = true in the function signature
			if (value !== false) {
				// eslint-disable-next-line no-param-reassign
				value = true;
			}
			_assertClassBrand(_CheckboxList_brand, this, _getLayoutComponent).call(this).select(id, value);
		}
		apply() {
			_assertClassBrand(_CheckboxList_brand, this, _getLayoutComponent).call(this).apply();
		}
	}
	function _getColumnCount() {
		let {
			columnCount
		} = this;
		const {
			innerWidth
		} = window;
		if (innerWidth <= 480) {
			columnCount = 1;
		} else if (innerWidth <= 768 && columnCount > 2) {
			columnCount = 2;
		}
		return columnCount;
	}
	function _getLayoutComponent() {
		if (!this.layoutComponent) {
			void this.getPopup();
		}
		return this.layoutComponent;
	}

	exports.CheckboxList = CheckboxList;

})(this.BX.UI = this.BX.UI || {}, BX, BX, BX.Event, BX.Main, BX, BX.Vue3);
//# sourceMappingURL=bundle.js.map
