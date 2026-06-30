<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/builder.bundle.css',
	'js' => 'dist/builder.bundle.js',
	'rel' => [
		'im.v2.component.message.base',
		'im.v2.lib.feature',
		'main.core',
		'ui.icon-set.api.vue',
		'im.v2.component.message.elements',
		'im.v2.lib.parser',
	],
	'skip_core' => false,
];
