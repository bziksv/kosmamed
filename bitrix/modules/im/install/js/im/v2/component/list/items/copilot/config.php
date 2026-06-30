<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/copilot-list.bundle.css',
	'js' => 'dist/copilot-list.bundle.js',
	'rel' => [
		'im.v2.component.list.items.base',
		'im.v2.lib.draft',
		'main.core',
		'im.v2.lib.menu',
		'im.v2.lib.analytics',
		'im.v2.application.core',
		'im.v2.const',
		'im.v2.provider.service.recent',
	],
	'skip_core' => false,
];