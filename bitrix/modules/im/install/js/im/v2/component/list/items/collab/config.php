<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/registry.bundle.css',
	'js' => 'dist/registry.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'im.v2.lib.menu',
		'main.core.events',
		'im.v2.component.list.items.base',
		'im.v2.component.list.items.elements.create-chat-status',
		'im.v2.lib.draft',
		'im.v2.lib.notifier',
		'im.v2.provider.service.recent',
		'im.v2.const',
		'im.v2.lib.create-chat',
	],
	'skip_core' => true,
];
