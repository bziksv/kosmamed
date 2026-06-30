<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

return [
	'css' => 'dist/list-slider.bundle.css',
	'js' => 'dist/list-slider.bundle.js',
	'rel' => [
		'main.polyfill.core',
		'main.core.events',
		'ui.icon-set.api.vue',
		'im.v2.const',
		'im.v2.component.animation',
		'im.v2.lib.esc-manager',
	],
	'skip_core' => true,
];