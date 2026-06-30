/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
this.BX.Messenger.v2.Component = this.BX.Messenger.v2.Component || {};
(function (exports,im_v2_component_message_base,im_v2_lib_feature,main_core,ui_iconSet_api_vue,im_v2_component_message_elements,im_v2_lib_parser) {
	'use strict';

	// @vue/component
	const BaseBlock = {
	  name: 'BaseBlock',
	  props: {
	    message: {
	      type: Object,
	      required: true
	    },
	    block: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {},
	  methods: {},
	  template: `
		<slot></slot>
	`
	};

	// @vue/component
	const MapBlock = {
	  name: 'MapBlock',
	  components: {
	    BaseBlock,
	    BIcon: ui_iconSet_api_vue.BIcon
	  },
	  props: {
	    message: {
	      type: Object,
	      required: true
	    },
	    block: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    OutlineIcons: () => ui_iconSet_api_vue.Outline,
	    mapBlock() {
	      return this.block;
	    },
	    hasStatus() {
	      return main_core.Type.isStringFilled(this.mapBlock.status);
	    },
	    hasText() {
	      return main_core.Type.isStringFilled(this.mapBlock.text);
	    }
	  },
	  template: `
		<BaseBlock
			:message="message"
			:block="block"
			:dialogId="dialogId"
		>
			<div class="bx-im-message-block-map__container">
				<div class="bx-im-message-block-map__image-container">
					<img :src="mapBlock.imageUrl" :alt="mapBlock.text" class="bx-im-message-block-map__image">
					<div
						v-if="hasStatus"
						:title="mapBlock.status"
						class="bx-im-message-block-map__location-status --ellipsis"
					>
						{{ mapBlock.status }}
					</div>
				</div>
				<div v-if="hasText" class="bx-im-message-block-map__location">
					<BIcon :name="OutlineIcons.LOCATION" class="bx-im-message-block-map__location-icon" />
					<div
						:title="mapBlock.text" 
						class="bx-im-message-block-map__location-text --line-clamp-2"
					>
						{{ mapBlock.text }}
					</div>
				</div>
			</div>
		</BaseBlock>
	`
	};

	// @vue/component
	const TextBlock = {
	  name: 'TextBlock',
	  components: {
	    BaseBlock,
	    TextContent: im_v2_component_message_elements.TextContent
	  },
	  props: {
	    message: {
	      type: Object,
	      required: true
	    },
	    block: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    textBlock() {
	      return this.block;
	    },
	    formattedText() {
	      return im_v2_lib_parser.Parser.decodeText(this.textBlock.text);
	    }
	  },
	  template: `
		<BaseBlock
			:message="message"
			:block="textBlock"
			:dialogId="dialogId"
		>
			<TextContent :text="formattedText" />
		</BaseBlock>
	`
	};

	// @vue/component
	const UnorderedList = {
	  name: 'UnorderedList',
	  components: {
	    TextContent: im_v2_component_message_elements.TextContent
	  },
	  props: {
	    block: {
	      type: Object,
	      required: true
	    }
	  },
	  computed: {
	    listBlock() {
	      return this.block;
	    }
	  },
	  methods: {
	    getFormattedText(text) {
	      return im_v2_lib_parser.Parser.decodeText(text);
	    }
	  },
	  template: `
		<ul class="bx-im-message-block-unordered-list__container">
			<li
				v-for="(listElement, index) in listBlock.elements"
				:key="index"
			>
				<TextContent :text="getFormattedText(listElement.text)" />
			</li>
		</ul>
	`
	};

	// @vue/component
	const OrderedList = {
	  name: 'OrderedList',
	  components: {
	    TextContent: im_v2_component_message_elements.TextContent
	  },
	  props: {
	    block: {
	      type: Object,
	      required: true
	    }
	  },
	  computed: {
	    listBlock() {
	      return this.block;
	    }
	  },
	  methods: {
	    getFormattedText(text) {
	      return im_v2_lib_parser.Parser.decodeText(text);
	    }
	  },
	  template: `
		<ol class="bx-im-message-block-ordered-list__container">
			<li
				v-for="(listElement, index) in listBlock.elements"
				:key="index"
			>
				<TextContent :text="getFormattedText(listElement.text)" />
			</li>
		</ol>
	`
	};

	// @vue/component
	const IconList = {
	  name: 'IconList',
	  components: {
	    TextContent: im_v2_component_message_elements.TextContent,
	    BIcon: ui_iconSet_api_vue.BIcon
	  },
	  props: {
	    block: {
	      type: Object,
	      required: true
	    }
	  },
	  computed: {
	    OutlineIcons: () => ui_iconSet_api_vue.Outline,
	    listBlock() {
	      return this.block;
	    },
	    listItemIcon() {
	      // there is only one icon for now
	      return ui_iconSet_api_vue.Outline.ARROW_RIGHT_L;
	    }
	  },
	  methods: {
	    getFormattedText(text) {
	      return im_v2_lib_parser.Parser.decodeText(text);
	    }
	  },
	  template: `
		<ul class="bx-im-message-block-icon-list__container">
			<li
				v-for="(listElement, index) in listBlock.elements"
				:key="index"
				class="bx-im-message-block-icon-list__item"
			>
				<BIcon
					:name="listItemIcon"
					class="bx-im-message-block-icon-list__icon"
				/>
				<TextContent :text="getFormattedText(listElement.text)" />
			</li>
		</ul>
	`
	};

	const ListIconType = {
	  number: 'number',
	  bullet: 'bullet',
	  arrow: 'arrow'
	};

	// @vue/component
	const ListBlock = {
	  name: 'ListBlock',
	  components: {
	    BaseBlock
	  },
	  props: {
	    message: {
	      type: Object,
	      required: true
	    },
	    block: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    listBlock() {
	      return this.block;
	    },
	    listComponent() {
	      var _iconToTypeMap$this$l;
	      const iconToTypeMap = {
	        [ListIconType.number]: OrderedList,
	        [ListIconType.bullet]: UnorderedList,
	        [ListIconType.arrow]: IconList
	      };
	      return (_iconToTypeMap$this$l = iconToTypeMap[this.listBlock.icon]) != null ? _iconToTypeMap$this$l : UnorderedList;
	    }
	  },
	  template: `
		<BaseBlock
			:message="message"
			:block="listBlock"
			:dialogId="dialogId"
		>
			<component :is="listComponent" :block="listBlock" />
		</BaseBlock>
	`
	};

	// @vue/component
	const TitleBlock = {
	  name: 'TitleBlock',
	  components: {
	    BaseBlock
	  },
	  props: {
	    message: {
	      type: Object,
	      required: true
	    },
	    block: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    titleBlock() {
	      return this.block;
	    },
	    containerClasses() {
	      const classes = [];
	      if (this.titleBlock.color) {
	        classes.push(`--color-${this.titleBlock.color}`);
	      }

	      // eslint-disable-next-line unicorn/explicit-length-check
	      if (this.titleBlock.size) {
	        classes.push(`--size-${this.titleBlock.size}`);
	      }
	      return classes;
	    }
	  },
	  template: `
		<BaseBlock
			:message="message"
			:block="titleBlock"
			:dialogId="dialogId"
		>
			<div class="bx-im-message-block-header__container" :class="containerClasses">
				<span
					:title="titleBlock.text"
					class="bx-im-message-block-header__text --line-clamp-3"
				>
					{{ titleBlock.text }}
				</span>
			</div>
		</BaseBlock>
	`
	};

	// @vue/component
	const LineDivider = {
	  name: 'LineDivider',
	  components: {
	    BaseBlock
	  },
	  props: {
	    message: {
	      type: Object,
	      required: true
	    },
	    block: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    lineDividerBlock() {
	      return this.block;
	    }
	  },
	  template: `
		<BaseBlock
			:message="message"
			:block="lineDividerBlock"
			:dialogId="dialogId"
		>
			<div class="bx-im-message-block-line-divider__container">
				<div class="bx-im-message-block-line-divider__line"></div>
			</div>
		</BaseBlock>
	`
	};

	// @vue/component
	const SpaceDivider = {
	  name: 'SpaceDivider',
	  components: {
	    BaseBlock
	  },
	  props: {
	    message: {
	      type: Object,
	      required: true
	    },
	    block: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  computed: {
	    spaceDividerBlock() {
	      return this.block;
	    },
	    containerClasses() {
	      return [`--size-${this.spaceDividerBlock.size}`];
	    }
	  },
	  template: `
		<BaseBlock
			:message="message"
			:block="spaceDividerBlock"
			:dialogId="dialogId"
		>
			<div class="bx-im-message-block-space-divider__container" :class="containerClasses"></div>
		</BaseBlock>
	`
	};

	// @vue/component
	const TableBlock = {
	  name: 'TableBlock',
	  components: {
	    BaseBlock,
	    TextContent: im_v2_component_message_elements.TextContent
	  },
	  props: {
	    message: {
	      type: Object,
	      required: true
	    },
	    block: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      isNarrow: false,
	      naturalWidth: 0
	    };
	  },
	  computed: {
	    tableBlock() {
	      return this.block;
	    },
	    columnCount() {
	      var _this$tableBlock$rows;
	      return (_this$tableBlock$rows = this.tableBlock.rows[0].length) != null ? _this$tableBlock$rows : 1;
	    }
	  },
	  mounted() {
	    this.naturalWidth = this.measureNaturalWidth();
	    this.initResizeObserver();
	  },
	  beforeUnmount() {
	    this.resizeObserver.disconnect();
	  },
	  methods: {
	    initResizeObserver() {
	      this.resizeObserver = new ResizeObserver(([entry]) => {
	        this.isNarrow = entry.contentRect.width < this.naturalWidth;
	      });
	      this.resizeObserver.observe(this.$refs.container.closest('.bx-im-message-base__wrap'));
	    },
	    measureNaturalWidth() {
	      const COLUMN_GAP = 10;
	      const tbody = this.$refs.container.querySelector('tbody');
	      const gridTemplateColumns = getComputedStyle(tbody).gridTemplateColumns.split(' ');
	      const [firstColumn, secondColumn] = gridTemplateColumns.map(element => {
	        return parseFloat(element);
	      });
	      if (secondColumn) {
	        return firstColumn + secondColumn + COLUMN_GAP;
	      }
	      return firstColumn;
	    },
	    getFormattedText(text) {
	      return im_v2_lib_parser.Parser.decodeText(text);
	    }
	  },
	  template: `
		<BaseBlock
			:message="message"
			:block="tableBlock"
			:dialogId="dialogId"
		>
			<div
				:class="{ '--narrow': isNarrow }"
				:style="{ '--im-message-builder-table-cols': columnCount }"
				ref="container"
				class="bx-im-message-block-table__container"
			>
				<table class="bx-im-message-block-table__table">
					<tbody>
						<tr
							v-for="(row, rowIndex) in tableBlock.rows"
							:key="rowIndex"
						>
							<td
								v-for="(cell, cellIndex) in row"
								:key="cellIndex"
							>
								<TextContent
									:text="getFormattedText(cell.text)"
									class="--line-clamp-3"
								/>
							</td>
						</tr>
					</tbody>
				</table>
			</div>
		</BaseBlock>
	`
	};

	const UNKNOWN_BLOCK_TYPE = 'unknown';

	// @vue/component
	const BuilderMessage = {
	  name: 'BuilderMessage',
	  components: {
	    MessageHeader: im_v2_component_message_elements.MessageHeader,
	    MessageFooter: im_v2_component_message_elements.MessageFooter,
	    BaseMessage: im_v2_component_message_base.BaseMessage,
	    DefaultMessageContent: im_v2_component_message_elements.DefaultMessageContent,
	    ReactionSelector: im_v2_component_message_elements.ReactionSelector,
	    MessageKeyboard: im_v2_component_message_elements.MessageKeyboard
	  },
	  props: {
	    item: {
	      type: Object,
	      required: true
	    },
	    dialogId: {
	      type: String,
	      required: true
	    },
	    withTitle: {
	      type: Boolean,
	      default: true
	    }
	  },
	  computed: {
	    message() {
	      return this.item;
	    },
	    messageBlocks() {
	      return this.$store.getters['messages/builder/getBlocks'](this.message.id);
	    },
	    isAvailable() {
	      return im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.isMessageBuilderAvailable);
	    },
	    hasKeyboard() {
	      return this.message.keyboard.length > 0;
	    }
	  },
	  methods: {
	    getComponentNameByType(type) {
	      const componentMap = {
	        title: TitleBlock,
	        text: TextBlock,
	        list: ListBlock,
	        map: MapBlock,
	        table: TableBlock,
	        lineDivider: LineDivider,
	        spaceDivider: SpaceDivider
	      };
	      return componentMap[type] || UNKNOWN_BLOCK_TYPE;
	    }
	  },
	  template: `
		<BaseMessage :item="item" :dialogId="dialogId" :afterMessageWidthLimit="false">
			<template #before-message v-if="$slots['before-message']">
				<slot name="before-message"></slot>
			</template>
			<div class="bx-im-message-default__container">
				<MessageHeader :withTitle="withTitle" :item="item" />
				<DefaultMessageContent
					:item="item"
					:dialogId="dialogId"
					:withAttach="false"
					:withText="false"
				>
					<template v-if="isAvailable">
						<component
							v-for="(block, index) in messageBlocks"
							:is="getComponentNameByType(block.type)"
							:key="index"
							:message="message"
							:block="block"
							:dialogId="dialogId"
						/>
					</template>
				</DefaultMessageContent>
			</div>
			<MessageFooter :item="item" :dialogId="dialogId" />
			<template #after-message v-if="hasKeyboard">
				<MessageKeyboard :item="item" :dialogId="dialogId" />
			</template>
		</BaseMessage>
	`
	};

	exports.BuilderMessage = BuilderMessage;

}((this.BX.Messenger.v2.Component.Message = this.BX.Messenger.v2.Component.Message || {}),BX.Messenger.v2.Component.Message,BX.Messenger.v2.Lib,BX,BX.UI.IconSet,BX.Messenger.v2.Component.Message,BX.Messenger.v2.Lib));
//# sourceMappingURL=builder.bundle.js.map
