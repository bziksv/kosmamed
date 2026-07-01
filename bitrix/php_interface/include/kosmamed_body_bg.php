<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	return;
}

AddEventHandler('main', 'OnEndBufferContent', static function (&$content) {
	if (!is_string($content) || stripos($content, '<body') === false) {
		return;
	}

	if (!preg_match('/<body\b([^>]*)>/i', $content, $bodyMatch)) {
		return;
	}

	$bodyAttrs = $bodyMatch[1];
	$bgUrl = '';

	if (preg_match('/\sdata-background-image="([^"]+)"/i', $bodyAttrs, $bgMatch)) {
		$bgUrl = $bgMatch[1];
	} elseif (preg_match('/background-image:\s*url\((["\']?)([^"\')]+)\1\)/i', $bodyAttrs, $bgMatch)) {
		$bgUrl = $bgMatch[2];
	}

	if ($bgUrl === '') {
		return;
	}

	$bgUrl = htmlspecialcharsbx($bgUrl);
	$cleanAttrs = preg_replace('/\sclass="[^"]*"/i', '', $bodyAttrs);
	$cleanAttrs = preg_replace('/\sdata-background-image="[^"]*"/i', '', $cleanAttrs);
	$cleanAttrs = preg_replace('/\sstyle="[^"]*"/i', '', $cleanAttrs);

	$newBodyTag = '<body' . $cleanAttrs . ' class="km-body-bg" style="background-image:url(\'' . $bgUrl . '\');background-repeat:repeat;background-size:640px auto;background-color:#e2e8f0;background-position:center top;">';
	$content = preg_replace('/<body\b[^>]*>/i', $newBodyTag, $content, 1);

	$inject = '<style id="km-body-bg-final">html{background-color:#e2e8f0!important;}html,body.km-body-bg{background-image:url(\'' . $bgUrl . '\')!important;background-repeat:repeat!important;background-size:640px auto!important;background-color:#e2e8f0!important;background-position:center top!important;}</style>';
	$content = preg_replace('/<\/body>/i', $inject . '</body>', $content, 1);
}, 200);

function kmFixLazyimageBackgrounds(&$content)
{
	if (!is_string($content) || stripos($content, 'arturgolubev.lazyimage/pixel.gif') === false) {
		return;
	}

	$content = preg_replace_callback(
		'/(<(?:a|span|div|li|section)[^>]*\sdata-background-image="([^"]+)"[^>]*>)/iu',
		static function (array $m): string {
			$tag = $m[0];
			if (stripos($tag, 'arturgolubev.lazyimage/pixel.gif') === false) {
				return $tag;
			}

			$url = $m[2];
			$tag = preg_replace(
				'/background\s*:\s*url\(\s*[\'"]?\/bitrix\/images\/arturgolubev\.lazyimage\/pixel\.gif[\'"]?\s*\)[^;"]*/iu',
				'background:url(' . $url . ') center center / cover no-repeat',
				$tag
			);

			if (preg_match('/\bclass="([^"]*)"/iu', $tag, $classMatch)) {
				$classes = preg_replace('/\bagll(_inited)?\b/u', '', $classMatch[1]);
				$classes = trim(preg_replace('/\s+/u', ' ', $classes . ' agll_loaded'));
				$tag = preg_replace('/\bclass="[^"]*"/iu', 'class="' . $classes . '"', $tag, 1);
			}

			return $tag;
		},
		$content
	);
}

AddEventHandler('main', 'OnEndBufferContent', 'kmFixLazyimageBackgrounds', false, 10);
