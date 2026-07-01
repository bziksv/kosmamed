<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

if (!empty($arResult['CONTACTS']) && function_exists('ksPhoneTelHtml')) {
	$arResult['CONTACTS'] = ksPhoneTelHtml($arResult['CONTACTS']);
}
