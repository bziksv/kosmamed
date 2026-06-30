<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'js' => 'dist/ai-assistant-widget.bundle.js',
	'css' => 'dist/ai-assistant-widget.bundle.css',
	'rel' => [
		'main.polyfill.core',
		'im.v2.application.core',
		'im.v2.css.classes',
		'im.v2.lib.analytics',
		'im.v2.lib.feature',
		'im.v2.lib.logger',
		'im.v2.component.animation',
		'im.v2.provider.service.copilot',
		'im.v2.component.elements.avatar',
		'im.v2.component.elements.chat-title',
		'im.v2.provider.service.chat',
		'im.v2.component.list.items.copilot',
		'ui.icon-set.api.vue',
		'im.v2.component.elements.loader',
		'main.core.events',
		'im.v2.const',
		'im.v2.component.content.chat',
		'im.v2.lib.message-notifier',
	],
	'skip_core' => true,
];
