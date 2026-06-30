<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'main.core',
		'im.v2.lib.helpdesk',
		'socialnetwork.collab.access-rights',
		'ui.icon-set.api.vue',
		'im.v2.lib.promo',
		'im.v2.lib.notifier',
		'main.core.events',
		'im.v2.lib.create-chat',
		'im.v2.lib.permission',
		'im.v2.model',
		'im.v2.application.core',
		'im.v2.component.elements.avatar',
		'main.popup',
		'im.public',
		'im.v2.component.content.chat-forms.elements',
		'im.v2.const',
		'im.v2.lib.analytics',
		'im.v2.lib.confirm',
		'im.v2.provider.service.chat',
	],
	'skip_core' => false,
];
