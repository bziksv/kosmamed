<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/navigator.bundle.css',
	'js' => 'dist/navigator.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'main.core.events',
		'im.v2.const',
		'im.v2.lib.layout',
		'im.v2.lib.feature',
		'im.v2.component.list.container.collab',
	],
	'skip_core' => true,
];
