import { EventEmitter } from 'main.core.events';
import { SlideAnimation } from 'im.v2.component.animation';
import { CopilotService } from 'im.v2.provider.service.copilot';

import { CopilotWidgetChatContent } from './components/chat-content';
import { CopilotWidgetRecentList } from './components/list';

import './css/chat-layout.css';

const MINIMIZE_EVENT_NAME = 'IM.AiAssistantWidget:minimize';

// @vue/component
export const CopilotWidgetLayout = {
	name: 'CopilotWidgetLayout',
	components: { SlideAnimation, CopilotWidgetRecentList, CopilotWidgetChatContent },
	data()
	{
		return {
			isPanelOpen: true,
			selectedDialogId: '',
			isCreatingChat: false,
		};
	},
	methods: {
		onTogglePanel()
		{
			this.isPanelOpen = !this.isPanelOpen;
		},
		async onSelectDialog(dialogId: string)
		{
			this.selectedDialogId = dialogId;
			await this.$nextTick();
			this.isPanelOpen = false;
			this.$emit('select', this.selectedDialogId);
		},
		async onCreateChat()
		{
			this.isCreatingChat = true;
			const newDialogId = await this.getCopilotService().createDefaultChat()
				.catch(() => {
					this.isCreatingChat = false;
				});
			this.isCreatingChat = false;

			if (newDialogId)
			{
				this.onSelectDialog(newDialogId);
			}
		},
		onHeaderClose()
		{
			if (this.selectedDialogId)
			{
				this.isPanelOpen = false;
			}
			else
			{
				EventEmitter.emit(MINIMIZE_EVENT_NAME);
			}
		},
		getCopilotService(): CopilotService
		{
			if (!this.copilotService)
			{
				this.copilotService = new CopilotService();
			}

			return this.copilotService;
		},
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
	`,
};
