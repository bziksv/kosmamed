import { type JsonObject } from 'main.core';
import { BIcon, Main as MainIcons } from 'ui.icon-set.api.vue';

import { BaseMenu } from 'im.v2.lib.menu';
import { RecentType } from 'im.v2.const';

import { RecentHeaderMenu } from './classes/recent-header-menu';
import { TaskHeaderMenu } from './classes/task-header-menu';
import { BaseRecentHeaderMenu } from './classes/base-header-menu';

import './css/header-menu.css';

const MenuClass = {
	[RecentType.taskComments]: TaskHeaderMenu,
	[RecentType.default]: RecentHeaderMenu,
};

// @vue/component
export const HeaderMenu = {
	name: 'HeaderMenu',
	components: { BIcon },
	props: {
		unreadMode: {
			type: Boolean,
			default: false,
		},
		recentSection: {
			type: String,
			required: true,
		},
	},
	emits: ['toggleUnreadMode'],
	data(): JsonObject
	{
		return {
			showMenu: false,
		};
	},
	computed: {
		MainIcons: () => MainIcons,
		isActive(): boolean
		{
			return this.showMenu || this.unreadMode;
		},
	},
	created()
	{
		this.contextMenuManager = new MenuClass[this.recentSection]();

		this.contextMenuManager.subscribe(BaseMenu.events.close, this.closeMenu);
		this.contextMenuManager.subscribe(BaseRecentHeaderMenu.events.onToggleUnreadMode, this.onToggleUnreadMode);
	},
	beforeUnmount()
	{
		this.contextMenuManager.destroy();
	},
	methods: {
		onToggleUnreadMode()
		{
			this.$emit('toggleUnreadMode');
		},
		openMenu(event: PointerEvent)
		{
			this.contextMenuManager.openMenu({ unreadMode: this.unreadMode }, event.currentTarget);

			this.showMenu = true;
		},
		closeMenu()
		{
			this.showMenu = false;
		},
		onClick(event: PointerEvent)
		{
			if (this.unreadMode)
			{
				this.onToggleUnreadMode();

				return;
			}

			if (this.showMenu)
			{
				this.closeMenu();

				return;
			}

			this.openMenu(event);
		},
	},
	template: `
		<div
			class="bx-im-list-container-header-menu__container"
			:class="{'--active': isActive }" 
			@click="onClick"
		>
			<BIcon
				class="bx-im-list-container-header-menu-icon"
				:name="MainIcons.FUNNEL"
			/>
		</div>
	`,
};
