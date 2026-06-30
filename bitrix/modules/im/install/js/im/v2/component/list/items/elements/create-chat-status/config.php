<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/create-chat-status.bundle.css',
	'js' => 'dist/create-chat-status.bundle.js',
	'rel' => [
		'main.core',
		'main.core.events',
		'ui.icon-set.api.vue',
		'im.public',
		'im.v2.lib.create-chat',
		'im.v2.component.elements.avatar',
		'im.v2.const',
	],
	'skip_core' => false,
];