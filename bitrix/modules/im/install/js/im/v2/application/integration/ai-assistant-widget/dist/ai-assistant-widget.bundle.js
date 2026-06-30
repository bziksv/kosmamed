/* eslint-disable */
this.BX = this.BX || {};
this.BX.Messenger = this.BX.Messenger || {};
this.BX.Messenger.v2 = this.BX.Messenger.v2 || {};
(function (exports,im_v2_application_core,im_v2_css_classes,im_v2_lib_analytics,im_v2_lib_feature,im_v2_lib_logger,im_v2_component_animation,im_v2_provider_service_copilot,im_v2_component_elements_avatar,im_v2_component_elements_chatTitle,im_v2_provider_service_chat,im_v2_component_list_items_copilot,ui_iconSet_api_vue,im_v2_component_elements_loader,main_core_events,im_v2_const,im_v2_component_content_chat,im_v2_lib_messageNotifier) {
	'use strict';

	const MINIMIZE_EVENT_NAME = 'IM.AiAssistantWidget:minimize';

	// @vue/component
	const AiAssistantWidgetChatHeader = {
	  name: 'AiAssistantWidgetChatHeader',
	  components: {
	    BIcon: ui_iconSet_api_vue.BIcon,
	    ChatAvatar: im_v2_component_elements_avatar.ChatAvatar,
	    EditableChatTitle: im_v2_component_elements_chatTitle.EditableChatTitle
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    },
	    withListToggle: {
	      type: Boolean,
	      default: false
	    }
	  },
	  emits: ['toggleList'],
	  computed: {
	    AvatarSize: () => im_v2_component_elements_avatar.AvatarSize,
	    OutlineIcons: () => ui_iconSet_api_vue.Outline,
	    dialog() {
	      return this.$store.getters['chats/get'](this.dialogId, true);
	    },
	    isInited() {
	      return this.dialog.inited;
	    },
	    subtitle() {
	      return this.loc('IM_CONTENT_AI_ASSISTANT_CHAT_HEADER_TITLE');
	    }
	  },
	  methods: {
	    onNewTitleSubmit(newTitle) {
	      if (!this.chatService) {
	        this.chatService = new im_v2_provider_service_chat.ChatService();
	      }
	      void this.chatService.renameChat(this.dialogId, newTitle);
	    },
	    onMinimize() {
	      main_core_events.EventEmitter.emit(MINIMIZE_EVENT_NAME);
	    },
	    loc(phrase) {
	      return this.$Bitrix.Loc.getMessage(phrase);
	    }
	  },
	  template: `
		<div class="bx-im-ai-assistant-chat-header__container">
			<BIcon
				v-if="withListToggle"
				:name="OutlineIcons.CLOCK_BACK"
				:hoverable="true"
				class="bx-im-ai-assistant-chat-header__back"
				@click="$emit('toggleList')"
			/>
			<div class="bx-im-ai-assistant-chat-header__avatar">
				<ChatAvatar
					:avatarDialogId="dialogId"
					:contextDialogId="dialogId"
					:size="AvatarSize.L"
				/>
			</div>
			<div class="bx-im-ai-assistant-chat-header__info">
				<EditableChatTitle :dialogId="dialogId" @newTitleSubmit="onNewTitleSubmit"/>
				<div class="bx-im-ai-assistant-chat-header__subtitle">
					{{ subtitle }}
				</div>
			</div>
			<BIcon
				v-if="isInited"
				:name="OutlineIcons.CROSS_L"
				:hoverable="true"
				:title="loc('IM_AI_ASSISTANT_WIDGET_MINIMIZE')"
				class="bx-im-ai-assistant-chat-header__icon"
				@click="onMinimize"
			/>
		</div>
	`
	};

	// @vue/component
	const CopilotWidgetChatContent = {
	  name: 'CopilotWidgetChatContent',
	  components: {
	    CopilotContent: im_v2_component_content_chat.CopilotContent,
	    AiAssistantWidgetChatHeader
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    },
	    withSidebar: {
	      type: Boolean,
	      default: true
	    }
	  },
	  emits: ['toggleList'],
	  template: `
		<CopilotContent
			:dialogId="dialogId"
			:withSidebar="withSidebar"
		>
			<template #header>
				<AiAssistantWidgetChatHeader
					:dialogId="dialogId"
					:withListToggle="true"
					@toggleList="$emit('toggleList')"
				/>
			</template>
		</CopilotContent>
	`
	};

	// @vue/component
	const CopilotWidgetListHeader = {
	  name: 'CopilotWidgetListHeader',
	  components: {
	    BIcon: ui_iconSet_api_vue.BIcon,
	    Spinner: im_v2_component_elements_loader.Spinner
	  },
	  props: {
	    isCreating: {
	      type: Boolean,
	      default: false
	    }
	  },
	  emits: ['newChat', 'close'],
	  computed: {
	    OutlineIcons: () => ui_iconSet_api_vue.Outline,
	    SpinnerColor: () => im_v2_component_elements_loader.SpinnerColor,
	    SpinnerSize: () => im_v2_component_elements_loader.SpinnerSize
	  },
	  methods: {
	    loc(phrase) {
	      return this.$Bitrix.Loc.getMessage(phrase);
	    }
	  },
	  template: `
		<div class="bx-im-copilot-widget-header__container">
			<div class="bx-im-copilot-widget-header__title">
				{{ loc('IM_AI_ASSISTANT_WIDGET_HEADER_TITLE') }}
			</div>
			<div class="bx-im-copilot-widget-header__actions">
				<div
					class="bx-im-copilot-widget-header__create-chat"
					:title="loc('IM_AI_ASSISTANT_WIDGET_HEADER_NEW_CHAT')"
				>
					<div class="bx-im-copilot-widget-header__create-chat">
						<Spinner 
							v-if="isCreating" 
							:size="SpinnerSize.XS"
							:color="SpinnerColor.copilot"
						/>
						<BIcon
							v-else
							class="bx-im-copilot-widget-header__create-chat_icon"
							:name="OutlineIcons.PLUS_L"
							:hoverable="true"
							@click="$emit('newChat')"
						/>
					</div>
				</div>
				<div
					class="bx-im-copilot-widget-header__close"
					:title="loc('IM_AI_ASSISTANT_WIDGET_HEADER_CLOSE')"
					@click="$emit('close')"
				>
					<BIcon
						class="bx-im-copilot-widget-header__close-icon"
						:name="OutlineIcons.CROSS_L"
						:hoverable="true"
					/>
				</div>
			</div>
		</div>
	`
	};

	// @vue/component
	const CopilotWidgetRecentList = {
	  name: 'CopilotWidgetRecentList',
	  components: {
	    CopilotList: im_v2_component_list_items_copilot.CopilotList,
	    CopilotWidgetListHeader
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    },
	    withSidebar: {
	      type: Boolean,
	      default: true
	    },
	    isCreating: {
	      type: Boolean,
	      default: false
	    }
	  },
	  emits: ['newChat', 'close', 'chatSelect'],
	  template: `
		<div class="bx-im-ai-assistant-chat-recent-list">
			<CopilotWidgetListHeader
				:isCreating="isCreating"
				@newChat="$emit('newChat')"
				@close="$emit('close')"
			/>
			<CopilotList @chatClick="$emit('chatSelect', $event)"/>
		</div>
	`
	};

	const MINIMIZE_EVENT_NAME$1 = 'IM.AiAssistantWidget:minimize';

	// @vue/component
	const CopilotWidgetLayout = {
	  name: 'CopilotWidgetLayout',
	  components: {
	    SlideAnimation: im_v2_component_animation.SlideAnimation,
	    CopilotWidgetRecentList,
	    CopilotWidgetChatContent
	  },
	  data() {
	    return {
	      isPanelOpen: true,
	      selectedDialogId: '',
	      isCreatingChat: false
	    };
	  },
	  methods: {
	    onTogglePanel() {
	      this.isPanelOpen = !this.isPanelOpen;
	    },
	    async onSelectDialog(dialogId) {
	      this.selectedDialogId = dialogId;
	      await this.$nextTick();
	      this.isPanelOpen = false;
	      this.$emit('select', this.selectedDialogId);
	    },
	    async onCreateChat() {
	      this.isCreatingChat = true;
	      const newDialogId = await this.getCopilotService().createDefaultChat().catch(() => {
	        this.isCreatingChat = false;
	      });
	      this.isCreatingChat = false;
	      if (newDialogId) {
	        this.onSelectDialog(newDialogId);
	      }
	    },
	    onHeaderClose() {
	      if (this.selectedDialogId) {
	        this.isPanelOpen = false;
	      } else {
	        main_core_events.EventEmitter.emit(MINIMIZE_EVENT_NAME$1);
	      }
	    },
	    getCopilotService() {
	      if (!this.copilotService) {
	        this.copilotService = new im_v2_provider_service_copilot.CopilotService();
	      }
	      return this.copilotService;
	    }
	  },
	  template: `
		<div class="bx-im-ai-assistant-widget-layout__container">
			<main class="bx-im-ai-assistant-widget-layout__content">
				<CopilotWidgetChatContent
					v-if="selectedDialogId"
					:dialogId="selectedDialogId"
					:withSidebar="false"
					@toggleList="onTogglePanel"
				/>
			</main>

			<SlideAnimation>
				<aside class="bx-im-ai-assistant-widget-layout__panel-container" v-if="isPanelOpen">
					<CopilotWidgetRecentList
						:isCreating="isCreatingChat"
						@chatSelect="onSelectDialog"
						@close="onHeaderClose"
						@newChat="onCreateChat"
					/>
				</aside>
			</SlideAnimation>
		</div>
	`
	};

	// @vue/component
	const MartaWidgetChatContent = {
	  name: 'MartaWidgetChatContent',
	  components: {
	    AiAssistantWidgetChatHeader,
	    AiAssistantBotContent: im_v2_component_content_chat.AiAssistantBotContent
	  },
	  props: {
	    dialogId: {
	      type: String,
	      default: ''
	    },
	    withSidebar: {
	      type: Boolean,
	      default: true
	    }
	  },
	  created() {
	    main_core_events.EventEmitter.subscribe(im_v2_const.EventType.notifier.onBeforeShowMessage, this.onBeforeNotificationShow);
	  },
	  beforeUnmount() {
	    main_core_events.EventEmitter.unsubscribe(im_v2_const.EventType.notifier.onBeforeShowMessage, this.onBeforeNotificationShow);
	  },
	  methods: {
	    onBeforeNotificationShow(event) {
	      const eventData = event.getData();
	      if (eventData.dialogId !== this.dialogId) {
	        return im_v2_lib_messageNotifier.NotifierShowMessageAction.show;
	      }
	      return im_v2_lib_messageNotifier.NotifierShowMessageAction.skip;
	    }
	  },
	  template: `
		<AiAssistantBotContent :dialogId="dialogId" :withSidebar="withSidebar">
			<template #header>
				<AiAssistantWidgetChatHeader :dialogId="dialogId" />
			</template>
		</AiAssistantBotContent>
	`
	};

	// @vue/component
	const AiAssistantWidgetChatOpener = {
	  name: 'AiAssistantWidgetChatOpener',
	  components: {
	    CopilotWidgetLayout,
	    MartaWidgetChatContent
	  },
	  props: {
	    botDialogId: {
	      type: String,
	      required: true
	    }
	  },
	  data() {
	    return {
	      selectedDialogId: ''
	    };
	  },
	  computed: {
	    isBgptMode() {
	      return im_v2_lib_feature.FeatureManager.isFeatureAvailable(im_v2_lib_feature.Feature.isBitrixGptV2Available);
	    },
	    dialog() {
	      return this.currentDialogId && this.$store.getters['chats/get'](this.currentDialogId, true);
	    },
	    chatId() {
	      return this.dialog && this.dialog.chatId;
	    },
	    currentDialogId() {
	      return this.isBgptMode ? this.selectedDialogId : this.botDialogId;
	    }
	  },
	  created() {
	    if (!this.isBgptMode) {
	      void this.onChatOpen();
	    }
	  },
	  methods: {
	    async onChatOpen() {
	      if (!this.dialog) {
	        return;
	      }
	      if (this.dialog.inited) {
	        im_v2_lib_logger.Logger.warn(`AiAssistantChatOpener: chat ${this.chatId} is already loaded`);
	        return;
	      }
	      await this.loadChat();
	      im_v2_lib_analytics.Analytics.getInstance().aiAssistant.onOpenWidget(this.dialog);
	      im_v2_lib_analytics.Analytics.getInstance().aiAssistant.onOpenChatAI(this.dialog, true);
	    },
	    onChangeDialogId(dialogId) {
	      this.selectedDialogId = dialogId;
	      void this.onChatOpen();
	    },
	    async loadChat() {
	      im_v2_lib_logger.Logger.warn(`AiAssistantChatOpener: loading chat ${this.chatId}`);
	      await this.getChatService().loadChatWithMessages(this.currentDialogId);
	      im_v2_lib_logger.Logger.warn(`AiAssistantChatOpener: chat ${this.chatId} is loaded`);
	    },
	    getChatService() {
	      if (!this.chatService) {
	        this.chatSerivce = new im_v2_provider_service_chat.ChatService();
	      }
	      return this.chatSerivce;
	    }
	  },
	  template: `
		<div class="bx-im-messenger__scope bx-im-ai-assistant-chat-opener__container --ui-context-content-light">
			<CopilotWidgetLayout
				v-if="isBgptMode"
				@select="onChangeDialogId"
			/>
			<MartaWidgetChatContent
				v-else
				:dialogId="botDialogId"
				:withSidebar="false"
			/>
		</div>
	`
	};

	const APP_NAME = 'AiAssistantWidgetApplication';
	var _initPromise = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("initPromise");
	var _init = /*#__PURE__*/babelHelpers.classPrivateFieldLooseKey("init");
	class AiAssistantWidgetApplication {
	  constructor() {
	    Object.defineProperty(this, _init, {
	      value: _init2
	    });
	    Object.defineProperty(this, _initPromise, {
	      writable: true,
	      value: void 0
	    });
	    babelHelpers.classPrivateFieldLooseBase(this, _initPromise)[_initPromise] = babelHelpers.classPrivateFieldLooseBase(this, _init)[_init]();
	  }
	  ready() {
	    return babelHelpers.classPrivateFieldLooseBase(this, _initPromise)[_initPromise];
	  }
	  async mount(payload) {
	    await this.ready();
	    const {
	      rootContainer,
	      aiAssistantBotId,
	      onError
	    } = payload;
	    if (!rootContainer) {
	      return Promise.reject(new Error('Provide node or selector for root container'));
	    }
	    const dialogId = aiAssistantBotId.toString();
	    return im_v2_application_core.Core.createVue(this, {
	      name: APP_NAME,
	      el: rootContainer,
	      onError,
	      components: {
	        AiAssistantWidgetChatOpener
	      },
	      template: `<AiAssistantWidgetChatOpener botDialogId="${dialogId}" />`
	    });
	  }
	}
	async function _init2() {
	  await im_v2_application_core.Core.ready();
	  return this;
	}

	exports.AiAssistantWidgetApplication = AiAssistantWidgetApplication;

}((this.BX.Messenger.v2.Application = this.BX.Messenger.v2.Application || {}),BX.Messenger.v2.Application,BX.Messenger.v2.Css,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Lib,BX.Messenger.v2.Component.Animation,BX.Messenger.v2.Service,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Component.Elements,BX.Messenger.v2.Service,BX.Messenger.v2.Component.List,BX.UI.IconSet,BX.Messenger.v2.Component.Elements,BX.Event,BX.Messenger.v2.Const,BX.Messenger.v2.Component.Content,BX.Messenger.v2.Lib));
//# sourceMappingURL=ai-assistant-widget.bundle.js.map
