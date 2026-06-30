import './text-content.css';

// @vue/component
export const TextContent = {
	name: 'TextContent',
	props: {
		text: {
			type: String,
			required: true,
		},
	},
	template: `
		<div class="bx-im-message-text-content__container" v-html="text"></div>
	`,
};
