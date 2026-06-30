import 'im.v2.css.classes';
import { Analytics } from 'im.v2.lib.analytics';
import { Feature, FeatureManager } from 'im.v2.lib.feature';
import { Logger } from 'im.v2.lib.logger';
import { type ImModelChat } from 'im.v2.model';
import { ChatService } from 'im.v2.provider.service.chat';
import type { JsonObject } from 'main.core';

import { CopilotWidgetLayout } from './copilot-widget/layout';
import { MartaWidgetChatContent } from './marta-widget/chat-content';

import './css/ai-assistant-chat-opener.css';

// @vue/component
export const AiAssistantWidgetChatOpener = {
	name: 'AiAssistantWidgetChatOpener',
	components: { CopilotWidgetLayout, MartaWidgetChatContent },
	props: {
		botDialogId: {
			type: String,
			required: true,
		},
	},

	data(): JsonObject
	{
		return {
			selectedDialogId: '',
		};
	},

	computed: {
		isBgptMode(): boolean
		{
			return FeatureManager.isFeatureAvailable(Feature.isBitrixGptV2Available);
		},

		dialog(): ImModelChat
		{
			return this.currentDialogId && this.$store.getters['chats/get'](this.currentDialogId, true);
		},
		chatId(): number
		{
			return this.dialog && this.dialog.chatId;
		},
		currentDialogId(): string
		{
			return this.isBgptMode ? this.selectedDialogId : this.botDialogId;
		},
	},

	created(): void
	{
		if (!this.isBgptMode)
		{
			void this.onChatOpen();
		}
	},

	methods: {
		async onChatOpen()
		{
			if (!this.dialog)
			{
				return;
			}

			if (this.dialog.inited)
			{
				Logger.warn(`AiAssistantChatOpener: chat ${this.chatId} is already loaded`);

				return;
			}

			await this.loadChat();
			Analytics.getInstance().aiAssistant.onOpenWidget(this.dialog);
			Analytics.getInstance().aiAssistant.onOpenChatAI(this.dialog, true);
		},

		onChangeDialogId(dialogId: string): void
		{
			this.selectedDialogId = dialogId;
			void this.onChatOpen();
		},
		async loadChat()
		{
			Logger.warn(`AiAssistantChatOpener: loading chat ${this.chatId}`);
			await this.getChatService().loadChatWithMessages(this.currentDialogId);
			Logger.warn(`AiAssistantChatOpener: chat ${this.chatId} is loaded`);
		},
		getChatService(): ChatService
		{
			if (!this.chatService)
			{
				this.chatSerivce = new ChatService();
			}

			return this.chatSerivce;
		},
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
	`,
};
