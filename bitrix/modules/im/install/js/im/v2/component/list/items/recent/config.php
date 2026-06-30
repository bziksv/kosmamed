<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/recent-list.bundle.css',
	'js' => 'dist/recent-list.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.component.list.items.elements.create-chat-status',
		'im.v2.lib.create-chat',
		'im.v2.application.core',
		'call.component.active-call-list',
		'im.v2.component.elements.button',
		'im.v2.lib.feature',
		'im.v2.lib.invite',
		'main.core.events',
		'im.v2.component.list.items.base',
		'im.v2.component.list.items.elements.empty-state',
		'im.v2.const',
		'im.v2.lib.draft',
		'im.v2.lib.menu',
		'im.v2.provider.service.recent',
		'im.v2.lib.unread-mode',
	],
	'skip_core' => true,
];