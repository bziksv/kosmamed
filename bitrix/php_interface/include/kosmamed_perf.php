<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
	die();
}

if (!function_exists('kmEnsureWebpSrc')) {
	/**
	 * Create/read cached .webp sibling next to jpg/png under document root.
	 */
	function kmEnsureWebpSrc(string $relativeSrc): ?string
	{
		if (!function_exists('imagewebp')) {
			return null;
		}

		$relativeSrc = (string)preg_replace('#\?.*$#', '', $relativeSrc);
		if ($relativeSrc === '' || $relativeSrc[0] !== '/') {
			return null;
		}

		$ext = strtolower(pathinfo($relativeSrc, PATHINFO_EXTENSION));
		if (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
			return null;
		}

		$docRoot = rtrim((string)$_SERVER['DOCUMENT_ROOT'], '/');
		$sourcePath = $docRoot . $relativeSrc;
		if (!is_file($sourcePath) || !is_readable($sourcePath)) {
			return null;
		}

		$webpRelative = (string)preg_replace('/\.(jpe?g|png)$/i', '.webp', $relativeSrc);
		$webpPath = $docRoot . $webpRelative;

		if (is_file($webpPath) && filemtime($webpPath) >= filemtime($sourcePath)) {
			return $webpRelative;
		}

		// Do not generate WebP during HTTP — blocks TTFB on catalog (many images per page).
		// Run: php tools/perf/webp-warmup.php --limit=1000 (on server, after deploy).
		return null;
	}
}

if (!function_exists('kmGenerateWebpSrc')) {
	/** CLI / warmup only — writes .webp next to jpg/png. */
	function kmGenerateWebpSrc(string $relativeSrc): ?string
	{
		if (!function_exists('imagewebp')) {
			return null;
		}

		$relativeSrc = (string)preg_replace('#\?.*$#', '', $relativeSrc);
		if ($relativeSrc === '' || $relativeSrc[0] !== '/') {
			return null;
		}

		$ext = strtolower(pathinfo($relativeSrc, PATHINFO_EXTENSION));
		if (!in_array($ext, ['jpg', 'jpeg', 'png'], true)) {
			return null;
		}

		$docRoot = rtrim((string)$_SERVER['DOCUMENT_ROOT'], '/');
		$sourcePath = $docRoot . $relativeSrc;
		if (!is_file($sourcePath) || !is_readable($sourcePath)) {
			return null;
		}

		$webpRelative = (string)preg_replace('/\.(jpe?g|png)$/i', '.webp', $relativeSrc);
		$webpPath = $docRoot . $webpRelative;

		if (is_file($webpPath) && filemtime($webpPath) >= filemtime($sourcePath)) {
			return $webpRelative;
		}

		$webpDir = dirname($webpPath);
		if (!is_dir($webpDir) && !@mkdir($webpDir, 0755, true) && !is_dir($webpDir)) {
			return null;
		}

		$image = null;
		if (in_array($ext, ['jpg', 'jpeg'], true)) {
			$image = @imagecreatefromjpeg($sourcePath);
		} elseif ($ext === 'png') {
			$image = @imagecreatefrompng($sourcePath);
			if ($image !== false) {
				imagepalettetotruecolor($image);
				imagealphablending($image, true);
				imagesavealpha($image, true);
			}
		}

		if ($image === false || $image === null) {
			return null;
		}

		$saved = imagewebp($image, $webpPath, 82);
		imagedestroy($image);

		if (!$saved) {
			@unlink($webpPath);
			return null;
		}

		@chmod($webpPath, 0644);

		return $webpRelative;
	}
}

if (!function_exists('kmAttachWebp')) {
	function kmAttachWebp(array $picture): array
	{
		$src = $picture['SRC'] ?? '';
		if ($src === '') {
			return $picture;
		}

		$webpSrc = kmEnsureWebpSrc($src);
		if ($webpSrc !== null) {
			$picture['SRC_WEBP'] = $webpSrc;
		}

		return $picture;
	}
}

if (!function_exists('kmBestImageSrc')) {
	/** Prefer cached .webp sibling for img/background URLs (no generation on HTTP). */
	function kmBestImageSrc(string $src): string
	{
		$src = (string)preg_replace('#\?.*$#', '', $src);
		if ($src === '') {
			return $src;
		}

		$webpSrc = kmEnsureWebpSrc($src);
		return $webpSrc ?? $src;
	}
}

if (!function_exists('kmPicturePreloadSrc')) {
	function kmPicturePreloadSrc(array $picture): string
	{
		if (!empty($picture['SRC_WEBP'])) {
			return (string)$picture['SRC_WEBP'];
		}

		return (string)($picture['SRC'] ?? '');
	}
}

if (!function_exists('kmPictureHtml')) {
	function kmPictureHtml(array $picture, array $attrs = []): string
	{
		$src = $picture['SRC'] ?? '';
		if ($src === '') {
			return '';
		}

		$class = (string)($attrs['class'] ?? '');
		$alt = htmlspecialcharsbx((string)($attrs['alt'] ?? ''), ENT_QUOTES);
		$title = htmlspecialcharsbx((string)($attrs['title'] ?? ''), ENT_QUOTES);
		$width = (int)($picture['WIDTH'] ?? 0);
		$height = (int)($picture['HEIGHT'] ?? 0);
		$extra = '';

		foreach (['loading', 'fetchpriority', 'decoding'] as $key) {
			if (!empty($attrs[$key])) {
				$extra .= ' ' . $key . '="' . htmlspecialcharsbx((string)$attrs[$key], ENT_QUOTES) . '"';
			}
		}

		$widthAttr = $width > 0 ? ' width="' . $width . '"' : '';
		$heightAttr = $height > 0 ? ' height="' . $height . '"' : '';
		$classAttr = $class !== '' ? ' class="' . htmlspecialcharsbx($class, ENT_QUOTES) . '"' : '';
		$titleAttr = $title !== '' ? ' title="' . $title . '"' : '';
		$webpSrc = $picture['SRC_WEBP'] ?? '';

		if ($webpSrc !== '') {
			return '<picture>'
				. '<source srcset="' . htmlspecialcharsbx($webpSrc, ENT_QUOTES) . '" type="image/webp">'
				. '<img' . $classAttr . ' src="' . htmlspecialcharsbx($src, ENT_QUOTES) . '"' . $widthAttr . $heightAttr
				. ' alt="' . $alt . '"' . $titleAttr . $extra . ' />'
				. '</picture>';
		}

		return '<img' . $classAttr . ' src="' . htmlspecialcharsbx($src, ENT_QUOTES) . '"' . $widthAttr . $heightAttr
			. ' alt="' . $alt . '"' . $titleAttr . $extra . ' />';
	}
}

if (!function_exists('kmResizePicture')) {
	/**
	 * Resize with fallback dimensions when PHP cannot read /upload/ locally
	 * or CFile::ResizeImageGet returns 0×0.
	 */
	function kmResizePicture($picture, int $width, int $height): ?array
	{
		if (is_numeric($picture)) {
			$picture = CFile::GetFileArray((int)$picture);
		}

		if (!is_array($picture) || empty($picture['SRC'])) {
			return null;
		}

		$picWidth = (int)($picture['WIDTH'] ?? 0);
		$picHeight = (int)($picture['HEIGHT'] ?? 0);

		if ($picWidth <= 0 || $picHeight <= 0 || $picWidth > $width || $picHeight > $height) {
			$resized = CFile::ResizeImageGet(
				$picture,
				['width' => $width, 'height' => $height],
				BX_RESIZE_IMAGE_PROPORTIONAL,
				true
			);
		} else {
			$resized = [
				'src' => $picture['SRC'],
				'width' => $picWidth,
				'height' => $picHeight,
			];
		}

		if (!is_array($resized) || empty($resized['src'])) {
			if (empty($picture['SRC'])) {
				return null;
			}

			$resized = [
				'src' => $picture['SRC'],
				'width' => 0,
				'height' => 0,
			];
		}

		if ((int)($resized['width'] ?? 0) <= 0 || (int)($resized['height'] ?? 0) <= 0) {
			$resized['width'] = $width;
			$resized['height'] = $height;
		}

		return kmAttachWebp([
			'SRC' => $resized['src'],
			'WIDTH' => $resized['width'],
			'HEIGHT' => $resized['height'],
		]);
	}
}

if (!function_exists('kmOptimizePicture')) {
	/** Catalog list/card preview — target 280×280 for PSI «properly size images». */
	function kmOptimizePicture($picture, int $width = 280, int $height = 280): array
	{
		$fallback = [
			'SRC' => SITE_TEMPLATE_PATH . '/images/no-photo.jpg',
			'WIDTH' => 150,
			'HEIGHT' => 150,
		];

		if (!is_array($picture) || empty($picture['ID'])) {
			if (is_array($picture) && !empty($picture['SRC'])) {
				$optimized = kmResizePicture($picture, $width, $height);
				return $optimized ?? $picture;
			}

			return $fallback;
		}

		$optimized = kmResizePicture($picture, $width, $height);
		return $optimized ?? $fallback;
	}
}

if (!function_exists('kmBasketPicture')) {
	function kmBasketPicture($fileId): ?array
	{
		$fileId = (int)$fileId;
		if ($fileId <= 0) {
			return null;
		}

		$picture = kmResizePicture($fileId, 65, 65);
		if ($picture === null) {
			return null;
		}

		return [
			'src' => $picture['SRC'],
			'width' => $picture['WIDTH'],
			'height' => $picture['HEIGHT'],
		];
	}
}

if (!function_exists('kmSetLcpPreload')) {
	/** Queue LCP image preload — injected into <head> via OnEndBufferContent. */
	function kmSetLcpPreload(string $src): void
	{
		$src = (string)preg_replace('#\?.*$#', '', $src);
		if ($src === '') {
			return;
		}
		$GLOBALS['kmLcpPreloadSrc'] = $src;
	}
}

if (!function_exists('kmInjectLcpPreload')) {
	function kmInjectLcpPreload(string &$content): void
	{
		$src = $GLOBALS['kmLcpPreloadSrc'] ?? '';
		if ($src === '' && !empty($GLOBALS['kmIsProduct'])
			&& preg_match('/<img\b[^>]*\bfetchpriority=["\']high["\'][^>]*\bsrc=["\']([^"\']+)["\']/i', $content, $m)) {
			$src = (string)$m[1];
		}
		if ($src === '' || stripos($content, 'rel="preload" as="image"') !== false) {
			return;
		}

		// KOSMAMED perf: the LCP <img> is wrapped in <picture><source webp>, so browsers fetch
		// the .webp, leaving a .jpg/.png preload unused. Preload the webp the page actually uses.
		$type = '';
		if (function_exists('kmEnsureWebpSrc') && preg_match('/\.(?:png|jpe?g)$/i', $src)) {
			$webp = kmEnsureWebpSrc($src);
			if ($webp !== null && $webp !== '') {
				$src = $webp;
				$type = ' type="image/webp"';
			}
		}

		$link = '<link rel="preload" as="image" href="' . htmlspecialcharsbx($src, ENT_QUOTES) . '"' . $type . ' fetchpriority="high">';
		if (preg_match('/<head\b[^>]*>/i', $content)) {
			$content = preg_replace('/<head\b[^>]*>/i', '$0' . $link, $content, 1);
		}
	}
}

if (!function_exists('kmInjectWebpImages')) {
	/** Wrap <img> in <picture> when .webp exists; skip already-wrapped and tiny vendor logos. */
	function kmInjectWebpImages(string &$content): void
	{
		if (stripos($content, '<img') === false) {
			return;
		}

		// Collapse duplicate nested <picture> from template + buffer.
		$prev = '';
		while ($prev !== $content) {
			$prev = $content;
			$content = preg_replace(
				'/(<picture>\s*<source[^>]+>\s*)<picture>\s*<source[^>]+>\s*(<img\b[^>]+>)\s*<\/picture>\s*<\/picture>/i',
				'$1$2</picture>',
				$content
			);
		}

		$offset = 0;
		while (preg_match('/<img\b([^>]*\ssrc="(\/[^"?]+\.(?:png|jpe?g))"[^>]*)>/i', $content, $m, PREG_OFFSET_CAPTURE, $offset)) {
			$fullMatch = $m[0][0];
			$pos = (int)$m[0][1];
			$attrs = $m[1][0];
			$src = $m[2][0];
			$nextOffset = $pos + strlen($fullMatch);

			$before = substr($content, max(0, $pos - 300), min(300, $pos));
			if (preg_match('/<picture\b[^>]*>\s*(?:<source[^>]*>\s*)?$/i', $before)) {
				$offset = $nextOffset;
				continue;
			}

			if (preg_match('/\bclass="[^"]*\bno-webp\b/i', $attrs)) {
				$offset = $nextOffset;
				continue;
			}

			$webp = kmEnsureWebpSrc($src);
			if ($webp === null) {
				$offset = $nextOffset;
				continue;
			}

			// Vendor logos and other tiny thumbs: one request, src=.webp (no <picture>).
			if (preg_match('#/resize_cache/iblock/[^/]+/69_24_1/#', $src)
				|| preg_match('#/upload/resize_cache/[^/]+/\d+_\d+_1/#', $src)) {
				$replacement = str_replace(
					'src="' . $src . '"',
					'src="' . $webp . '"',
					$fullMatch
				);
			} else {
				$replacement = '<picture><source srcset="' . htmlspecialcharsbx($webp, ENT_QUOTES) . '" type="image/webp">'
					. $fullMatch . '</picture>';
			}

			$content = substr_replace($content, $replacement, $pos, strlen($fullMatch));
			$offset = $pos + strlen($replacement);
		}
	}
}

if (!function_exists('kmWebpOnAfterFileSave')) {
	/** Auto-generate .webp when Bitrix saves jpg/png to /upload. */
	function kmWebpOnAfterFileSave(array $arFile): void
	{
		$src = (string)($arFile['SRC'] ?? '');
		if ($src === '' || $src[0] !== '/') {
			return;
		}

		kmGenerateWebpSrc($src);
	}
}

if (!function_exists('kmInjectLazyImages')) {
	/** Below-the-fold images — skip logo (no-lazy / fetchpriority=high). */
	function kmInjectLazyImages(string &$content): void
	{
		if (stripos($content, '<img') === false) {
			return;
		}

		$content = preg_replace_callback(
			'/<img\b(?![^>]*\bloading\s*=)([^>]*?)>/i',
			static function (array $m): string {
				$attrs = $m[1];
				if (preg_match('/\bclass="[^"]*\bno-lazy\b/i', $attrs)) {
					return $m[0];
				}
				if (stripos($attrs, 'fetchpriority="high"') !== false) {
					return $m[0];
				}

				return '<img loading="lazy"' . $attrs . '>';
			},
			$content
		);
	}
}

if (!function_exists('kmInjectBackgroundWebp')) {
	/** Swap background-image png/jpg URLs to .webp when pre-generated on disk. */
	function kmInjectBackgroundWebp(string &$content): void
	{
		$content = preg_replace_callback(
			'/(background(?:-image)?\s*:\s*[^;]*url\s*\(\s*(["\']?)(\/[^"\')\s>]+\.(?:png|jpe?g))\1\s*\))/i',
			static function (array $m): string {
				$webp = kmEnsureWebpSrc($m[2]);
				if ($webp === null) {
					return $m[0];
				}

				return str_replace($m[2], $webp, $m[0]);
			},
			$content
		);
	}
}

if (!function_exists('kmIsMobileClient')) {
	function kmIsMobileClient(): bool
	{
		if (isset($GLOBALS['kmIsMobile'])) {
			return (bool)$GLOBALS['kmIsMobile'];
		}

		$ua = (string)($_SERVER['HTTP_USER_AGENT'] ?? '');
		$GLOBALS['kmIsMobile'] = (bool)preg_match(
			'/Mobile|Android|iPhone|iPod|Opera Mini|IEMobile|webOS|BlackBerry/i',
			$ua
		);

		return $GLOBALS['kmIsMobile'];
	}
}

if (!function_exists('kmInjectCriticalHomeCss')) {
	/** Reserve layout before deferred template_*_v1.css loads (CLS on desktop). */
	function kmInjectCriticalHomeCss(string &$content): void
	{
		if (empty($GLOBALS['kmIsHome'])) {
			return;
		}
		if (stripos($content, 'id="km-critical"') !== false) {
			return;
		}

		$critical = '<style id="km-critical">'
			. 'html,body,.body,.page-wrapper{width:100%;margin:0;padding:0}'
			. '.center{width:1234px;display:table;margin:0 auto}'
			. 'header{width:100%;min-height:107px;padding:10px 0}'
			. 'header .center{height:107px}'
			. '.header_1,.header_2,.header_3,.header_4{display:table-cell;vertical-align:middle}'
			. '.top-catalog{width:100%;height:40px;float:left;box-sizing:border-box}'
			. '.top_panel{width:100%;height:56px;display:none;margin:0;padding:0}'
			. '.content-wrapper{width:100%;padding:0 0 20px}'
			. '.content{width:1185px;float:left;margin:0 0 0 24px}'
			. '.left-column{width:203px;float:left;margin:0 24px 0 0}'
			. '.clr{clear:both}'
			. '.anythingContainer_DEFAULT{aspect-ratio:958/304}'
			. '.anythingContainer_16_9{aspect-ratio:958/538}'
			. '.anythingContainer_16_7{aspect-ratio:958/419}'
			. 'body.bg-fixed{background-attachment:scroll}'
			. '</style>';

		if (preg_match('/<head\b[^>]*>/i', $content)) {
			$content = preg_replace('/<head\b[^>]*>/i', '$0' . $critical, $content, 1);
		}
	}
}

if (!function_exists('kmResequenceCoreScripts')) {
	/** core_frame_cache requires BX.localStorage from core_ls — load order matters. */
	function kmResequenceCoreScripts(string &$content): void
	{
		if (!preg_match('#<script(\s[^>]*?\bsrc="([^"]*core_ls\.min\.js[^"]*)"[^>]*)>\s*</script>#i', $content, $lsMatch)) {
			return;
		}
		if (!preg_match('#<script(\s[^>]*?\bsrc="([^"]*core_frame_cache\.min\.js[^"]*)"[^>]*)>\s*</script>#i', $content, $fcMatch)) {
			return;
		}

		$lsTag = $lsMatch[0];
		$fcTag = $fcMatch[0];
		$lsPos = strpos($content, $lsTag);
		$fcPos = strpos($content, $fcTag);

		if ($lsPos === false || $fcPos === false || $lsPos < $fcPos) {
			return;
		}

		$content = str_replace($lsTag, '', $content);
		$content = str_replace($fcTag, $lsTag . $fcTag, $content);
	}
}

if (!function_exists('kmDeferHomeStylesheets')) {
	/** Homepage: defer non-critical CSS; keep Bitrix template bundle CSS blocking for CLS. */
	function kmDeferHomeStylesheets(string &$content): void
	{
		if (empty($GLOBALS['kmIsHome'])) {
			return;
		}

		$patterns = [
			'ui\\.font\\.opensans',
			'font-awesome',
			'custom-forms',
			'slider\\.css',
			'fancybox',
			'slick\\.css',
			'template_styles\\.css',
			'colors\\.css',
			'schemes/',
		];

		if (kmIsMobileClient()) {
			// mobile-only extras handled in kmDeferHomeScripts
		}

		$content = preg_replace_callback(
			'/<link(\s[^>]+)>/i',
			static function (array $m) use ($patterns): string {
				if (!preg_match('#\brel=["\']stylesheet["\']#i', $m[1])) {
					return $m[0];
				}
				if (stripos($m[1], 'onload=') !== false) {
					return $m[0];
				}
				if (!preg_match('#\bhref=["\']([^"\']+)["\']#i', $m[1], $hrefMatch)) {
					return $m[0];
				}

				foreach ($patterns as $pattern) {
					if (preg_match('#' . $pattern . '#i', $hrefMatch[1])) {
						$href = htmlspecialcharsbx($hrefMatch[1], ENT_QUOTES);

						return '<link rel="preload" as="style" href="' . $href . '" onload="this.onload=null;this.rel=\'stylesheet\'">'
							. '<noscript><link rel="stylesheet" href="' . $href . '"></noscript>';
					}
				}

				return $m[0];
			},
			$content
		);
	}
}

if (!function_exists('kmFixFontDisplay')) {
	function kmFixFontDisplay(string &$content): void
	{
		if (stripos($content, '@font-face') === false) {
			return;
		}

		$content = preg_replace_callback(
			'/@font-face\s*\{([^}]*)\}/i',
			static function (array $m): string {
				if (stripos($m[1], 'font-display') !== false) {
					return $m[0];
				}

				return '@font-face{' . rtrim($m[1], ';') . ';font-display:swap}';
			},
			$content
		);
	}
}

if (!function_exists('kmDeferPublicScripts')) {
	/** Apply defer attribute to matching external script tags. */
	function kmDeferPublicScripts(string &$content, array $deferNeedles, array $neverDeferNeedles): void
	{
		$content = preg_replace_callback(
			'/<script(\s[^>]*?\bsrc="([^"]+)"[^>]*)>\s*<\/script>/i',
			static function (array $m) use ($deferNeedles, $neverDeferNeedles): string {
				if (stripos($m[1], ' defer') !== false || stripos($m[1], ' async') !== false) {
					return $m[0];
				}
				foreach ($neverDeferNeedles as $needle) {
					if (stripos($m[2], $needle) !== false) {
						return $m[0];
					}
				}
				foreach ($deferNeedles as $needle) {
					$matched = (strpos($needle, '.+') !== false || strpos($needle, '\\') !== false)
						? preg_match('#' . $needle . '#i', $m[2])
						: stripos($m[2], $needle) !== false;
					if ($matched) {
						return '<script' . $m[1] . ' defer></script>';
					}
				}

				return $m[0];
			},
			$content
		);
	}
}

if (!function_exists('kmDeferHomeScripts')) {
	/** Homepage: defer non-critical JS (desktop + mobile TBT). */
	function kmDeferHomeScripts(string &$content): void
	{
		if (empty($GLOBALS['kmIsHome'])) {
			return;
		}

		$neverDeferNeedles = [
			'core_frame_cache',
			'core_ls.min.js',
			'pull.client',
			'pull/protobuf',
			'rest.client',
			'dexie.bitrix',
			'sale.basket.basket.line',
		];

		$deferNeedles = [
			'socialservices/ss.js',
			'TweenMax.min.js',
		];

		kmDeferPublicScripts($content, $deferNeedles, $neverDeferNeedles);
	}
}

if (!function_exists('kmDeferCatalogScripts')) {
	/**
	 * Catalog/product: defer only scripts without inline init on the same page.
	 * Do not use broad needles like /script.js — they match basket.line and catalog.element.
	 */
	function kmDeferCatalogScripts(string &$content): void
	{
		if (empty($GLOBALS['kmIsCatalogLike'])) {
			return;
		}

		$neverDeferNeedles = [
			'core_frame_cache',
			'core_ls.min.js',
			'sale.basket.basket.line',
			'search.title',
			'fancybox',
			'catalog.element',
			'geolocation',
		];

		$deferNeedles = [
			'socialservices/ss.js',
			'TweenMax.min.js',
		];
		if (stripos($content, 'time_buy_timer') === false) {
			$deferNeedles[] = 'countdown/jquery.plugin.js';
			$deferNeedles[] = 'countdown/jquery.countdown.js';
		}

		if (!empty($GLOBALS['kmIsProduct'])) {
			$deferNeedles = array_merge($deferNeedles, [
				'TweenMax.min.js',
				'countUp.min.js',
				'spectrum/spectrum.js',
				'custom-forms/jquery.custom-forms.js',
				'sale.prediction.product.detail',
				'catalog.section/filtered/script.js',
				'geolocation.delivery/delivery/script.js',
				'geolocation/city/script.js',
				'sale.location.selector.search/geolocation.city/script.js',
			]);
		}

		kmDeferPublicScripts($content, $deferNeedles, $neverDeferNeedles);
	}
}

if (!function_exists('kmIsStorefrontRequest')) {
	function kmIsStorefrontRequest(): bool
	{
		if (defined('ADMIN_SECTION') && ADMIN_SECTION) {
			return false;
		}
		if (($_SERVER['REQUEST_METHOD'] ?? '') === 'POST') {
			return false;
		}

		$uri = (string)($_SERVER['REQUEST_URI'] ?? '');
		if (strpos($uri, '/bitrix/admin') !== false || strpos($uri, '/bitrix/tools') !== false) {
			return false;
		}

		return true;
	}
}

if (!function_exists('kmIsCatalogLikeRequest')) {
	function kmIsCatalogLikeRequest(): bool
	{
		if (!empty($GLOBALS['kmIsCatalogLike'])) {
			return true;
		}
		if (defined('ADMIN_SECTION') && ADMIN_SECTION) {
			return false;
		}

		$uri = (string)($_SERVER['REQUEST_URI'] ?? '');
		$siteDir = defined('SITE_DIR') ? SITE_DIR : '/';

		return (strpos($uri, $siteDir . 'catalog/') !== false
			|| strpos($uri, $siteDir . 'product/') !== false);
	}
}

if (!function_exists('kmDisablePullOnStorefront')) {
	/** Stop Bitrix Pull on public pages (pull.client, solid.ws). */
	function kmDisablePullOnStorefront(): void
	{
		if (!kmIsStorefrontRequest()) {
			return;
		}
		if (!defined('BX_PULL_SKIP_INIT')) {
			define('BX_PULL_SKIP_INIT', true);
		}
	}
}

if (!function_exists('kmStripPullOnStorefront')) {
	/** Public pages: drop Bitrix Pull stack from HTML. */
	function kmStripPullOnStorefront(string &$content): void
	{
		if (!kmIsStorefrontRequest()) {
			return;
		}

		$stripNeedles = [
			'pull.client',
			'pull/protobuf',
			'rest.client',
			'dexie.bitrix',
			'ui/dexie',
		];

		$content = preg_replace_callback(
			'/<script(\s[^>]*?\bsrc="([^"]+)"[^>]*)>\s*<\/script>/i',
			static function (array $m) use ($stripNeedles): string {
				foreach ($stripNeedles as $needle) {
					if (stripos($m[2], $needle) !== false) {
						return '';
					}
				}

				return $m[0];
			},
			$content
		);

		$content = preg_replace(
			'/<script[^>]*>\s*BX\.Runtime\.registerExtension\(\{"name":"(?:pull\.client|ui\.dexie)"[^}]+\}\);\s*<\/script>/i',
			'',
			$content
		);

		$content = preg_replace(
			'/<script[^>]*>\s*BX\.bind\(window,\s*"load",\s*function\(\)\{BX\.PULL\.start\(\);\}\);\s*<\/script>/i',
			'',
			$content
		);

		$content = preg_replace(
			'#,\s*[\'"]/bitrix/js/pull/[^\'"]+[\'"]#i',
			'',
			$content
		);
		$content = preg_replace(
			'#,\s*[\'"]/bitrix/js/rest/[^\'"]+[\'"]#i',
			'',
			$content
		);
	}
}

if (!function_exists('kmDeferCatalogStylesheets')) {
	/**
	 * Catalog/product: defer non-blocking CSS.
	 *  - ui.font.opensans (web font)
	 *
	 * NB: the main compiled template_*_v1.css (data-template-style) must stay
	 * render-blocking — deferring it caused a ~1s flash of unstyled content (FOUC).
	 */
	function kmDeferCatalogStylesheets(string &$content): void
	{
		if (empty($GLOBALS['kmIsCatalogLike'])) {
			return;
		}

		$patterns = [
			'ui\\.font\\.opensans',
		];

		$content = preg_replace_callback(
			'/<link(\s[^>]+)>/i',
			static function (array $m) use ($patterns): string {
				if (!preg_match('#\brel=["\']stylesheet["\']#i', $m[1])) {
					return $m[0];
				}
				if (stripos($m[1], 'onload=') !== false) {
					return $m[0];
				}
				foreach ($patterns as $pattern) {
					if (preg_match('#' . $pattern . '#i', $m[1])) {
						return '<link' . $m[1] . ' media="print" onload="this.media=\'all\'"><noscript>' . $m[0] . '</noscript>';
					}
				}

				return $m[0];
			},
			$content
		);
	}
}

if (!function_exists('kmPlaceholderImg')) {
	function kmPlaceholderImg(): string
	{
		return 'data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7';
	}
}

if (!function_exists('kmDeferImgSrcInFragment')) {
	function kmDeferImgSrcInFragment(string $html): string
	{
		$ph = kmPlaceholderImg();

		return preg_replace(
			'/\ssrc="(\/[^"]+\.(?:webp|jpe?g|png))"/i',
			' src="' . $ph . '" data-km-src="$1" fetchpriority="low"',
			$html
		);
	}
}

if (!function_exists('kmDeferHomeOffscreenImages')) {
	/**
	 * Homepage first paint: skip network for hidden catalog tabs + sidebar vendor logos.
	 * Images load on tab click / IntersectionObserver (see main.js).
	 */
	function kmDeferHomeOffscreenImages(string &$content): void
	{
		if (empty($GLOBALS['kmIsHome'])) {
			return;
		}

		$hasRecommend = stripos($content, 'tabs__box recommend') !== false;
		$deferTabClasses = ['tabs__box hit', 'tabs__box discount'];
		if ($hasRecommend) {
			$deferTabClasses[] = 'tabs__box new';
		}

		foreach ($deferTabClasses as $boxClass) {
			$quoted = preg_quote($boxClass, '/');
			$content = preg_replace_callback(
				'/(<div class="' . $quoted . '"[^>]*>)(.*?)(<\/div>\s*(?=<div class="tabs__box|<div class="clr"))/is',
				static function (array $m): string {
					return $m[1] . kmDeferImgSrcInFragment($m[2]) . $m[3];
				},
				$content
			);
		}

		if (preg_match('/(<div class="left-column"[^>]*>)(.*?)(<\/div>\s*<main class="workarea)/is', $content, $leftMatch)) {
			$inner = $leftMatch[2];
			$vendorCount = 0;
			$inner = preg_replace_callback(
				'/\ssrc="(\/[^"]+\.(?:webp|jpe?g|png))"/i',
				static function (array $m) use (&$vendorCount): string {
					$vendorCount++;
					if ($vendorCount <= 2) {
						return ' src="' . $m[1] . '"';
					}

					return ' src="' . kmPlaceholderImg() . '" data-km-src="' . $m[1] . '" fetchpriority="low"';
				},
				$inner
			);
			$content = str_replace($leftMatch[0], $leftMatch[1] . $inner . $leftMatch[3], $content);
		}
	}
}

if (!function_exists('kmFindCatalogListingStart')) {
	function kmFindCatalogListingStart(string $content): int
	{
		foreach (['catalog-item-table-view', 'catalog-item-list-view', 'catalog-item-price-view'] as $class) {
			$pos = strpos($content, '<div class="' . $class . '"');
			if ($pos !== false) {
				return $pos;
			}
		}

		return -1;
	}
}

if (!function_exists('kmDeferImagesInHtmlFragment')) {
	function kmDeferImagesInHtmlFragment(string $html, string $placeholder): string
	{
		$html = preg_replace_callback(
			'#<picture>(.*?)</picture>#is',
			static function (array $m) use ($placeholder): string {
				$inner = $m[1];
				$inner = preg_replace(
					'/\ssrcset="([^"]+)"/i',
					' data-km-srcset="$1"',
					$inner
				);
				$inner = preg_replace(
					'/\ssrc="(\/[^"]+\.(?:webp|jpe?g|png))"/i',
					' src="' . $placeholder . '" data-km-src="$1" fetchpriority="low"',
					$inner
				);

				return '<picture>' . $inner . '</picture>';
			},
			$html
		);

		return preg_replace(
			'/\ssrc="(\/[^"]+\.(?:webp|jpe?g|png))"/i',
			' src="' . $placeholder . '" data-km-src="$1" fetchpriority="low"',
			$html
		);
	}
}

if (!function_exists('kmDeferCatalogOffscreenImages')) {
	/**
	 * Catalog listing: load first N cards immediately, defer the rest
	 * (placeholder + data-km-src / data-km-srcset, loaded via IntersectionObserver).
	 */
	function kmDeferCatalogOffscreenImages(string &$content): void
	{
		if (empty($GLOBALS['kmIsCatalogLike'])) {
			return;
		}

		$start = kmFindCatalogListingStart($content);
		if ($start < 0) {
			return; // not a listing page (e.g. product detail)
		}

		$skip = 8; // cards above-the-fold kept eager
		$cardIndex = 0;
		$ph = kmPlaceholderImg();

		$head = substr($content, 0, $start);
		$tail = substr($content, $start);

		$parts = preg_split('#(<div class="catalog-item-card"[^>]*>)#', $tail, -1, PREG_SPLIT_DELIM_CAPTURE);
		if ($parts === false || count($parts) < 2) {
			return;
		}

		$result = array_shift($parts);
		while (!empty($parts)) {
			$openTag = array_shift($parts);
			$cardBody = array_shift($parts) ?? '';
			$cardIndex++;
			if ($cardIndex > $skip) {
				$cardBody = kmDeferImagesInHtmlFragment($cardBody, $ph);
			}
			$result .= $openTag . $cardBody;
		}

		$content = $head . $result;
	}
}

if (!function_exists('kmDeferProductOffscreenImages')) {
	/**
	 * Product detail: defer images in below-the-fold blocks (buy_more, kit, viewed).
	 */
	function kmDeferProductOffscreenImages(string &$content): void
	{
		if (empty($GLOBALS['kmIsProduct'])) {
			return;
		}

		$deferStart = -1;
		foreach (['class="buy_more"', 'class="kit-items"', 'id="already_seen"'] as $marker) {
			$pos = strpos($content, $marker);
			if ($pos !== false && ($deferStart < 0 || $pos < $deferStart)) {
				$deferStart = $pos;
			}
		}
		if ($deferStart < 0) {
			return;
		}

		$head = substr($content, 0, $deferStart);
		$tail = kmDeferImagesInHtmlFragment(substr($content, $deferStart), kmPlaceholderImg());
		$content = $head . $tail;
	}
}

if (!function_exists('kmFixCatalogSliderImages')) {
	/** Карточки каталога: убрать display:none и lazy с фото слайдера. */
	function kmFixCatalogSliderImages(string &$content): void
	{
		if (stripos($content, 'magic_slide item_img') === false) {
			return;
		}

		$content = preg_replace_callback(
			'/<img\b([^>]*\bclass="[^"]*\bmagic_slide\s+item_img\b[^"]*"[^>]*)>/i',
			static function (array $m): string {
				$attrs = preg_replace('/\sstyle="display:\s*none;"/i', '', $m[1]);
				$attrs = preg_replace('/\sloading="lazy"/i', '', $attrs);
				if (!preg_match('/\bloading\s*=/i', $attrs)) {
					$attrs .= ' loading="eager"';
				}

				return '<img' . $attrs . '>';
			},
			$content
		);
	}
}

if (!function_exists('kmFixYandexMetrikaInformer')) {
	/** Информер Метрики — без lazy-load и без подмены src модулем lazyimage. */
	function kmFixYandexMetrikaInformer(string &$content): void
	{
		if (stripos($content, 'ym-advanced-informer') === false) {
			return;
		}

		$content = preg_replace_callback(
			'/<img\b([^>]*\bclass=["\'][^"\']*\bym-advanced-informer\b[^"\']*["\'][^>]*)>/i',
			static function (array $m): string {
				$attrs = preg_replace('/\sloading=["\']lazy["\']/i', '', $m[1]);
				if (!preg_match('/\bclass=["\'][^"\']*\bno-lazy\b/i', $attrs)) {
					$attrs = preg_replace('/\sclass=["\']([^"\']*)["\']/i', ' class="$1 no-lazy"', $attrs, 1, $count);
					if ($count === 0) {
						$attrs .= ' class="no-lazy ym-advanced-informer"';
					}
				}
				if (!preg_match('/\bloading\s*=/i', $attrs)) {
					$attrs .= ' loading="eager"';
				}
				if (preg_match('/\ssrc=["\'][^"\']*\/bitrix\/images\/arturgolubev\.lazyimage\/pixel\.gif["\']/i', $attrs)
					&& preg_match('/\sdata-src=["\']([^"\']+)["\']/i', $attrs, $srcMatch)) {
					$attrs = preg_replace('/\ssrc=["\'][^"\']*["\']/i', ' src="' . $srcMatch[1] . '"', $attrs);
					$attrs = preg_replace('/\sdata-src=["\'][^"\']*["\']/i', '', $attrs);
				}

				return '<img' . $attrs . '>';
			},
			$content
		);
	}
}

if (!function_exists('kmFixLcpImages')) {
	/**
	 * arturgolubev.lazyimage (browser_ll_image) prepends loading="lazy" to every <img>,
	 * including fetchpriority="high" LCP candidates — undo that after lazyimage runs.
	 */
	function kmFixLcpImages(string &$content): void
	{
		if (stripos($content, 'fetchpriority="high"') === false) {
			return;
		}

		$content = preg_replace_callback(
			'/<img\b([^>]*\bfetchpriority=["\']high["\'][^>]*)>/i',
			static function (array $m): string {
				$attrs = preg_replace('/\sloading=["\']lazy["\']/i', '', $m[1]);
				if (!preg_match('/\bloading\s*=/i', $attrs)) {
					$attrs = ' loading="eager"' . $attrs;
				}

				return '<img' . $attrs . '>';
			},
			$content
		);
	}
}

if (!function_exists('kmInjectHomeDeferredLoader')) {
	function kmInjectHomeDeferredLoader(string &$content): void
	{
		if (empty($GLOBALS['kmIsHome']) && empty($GLOBALS['kmIsCatalogLike'])) {
			return;
		}
		if (stripos($content, 'km-deferred-images') !== false) {
			return;
		}

		$script = '<script id="km-deferred-images">'
			. 'window.kmLoadDeferredImages=function(r){var s=r||document;'
			. 's.querySelectorAll("source[data-km-srcset]").forEach(function(el){var u=el.getAttribute("data-km-srcset");if(u){el.setAttribute("srcset",u);el.removeAttribute("data-km-srcset");}});'
			. 's.querySelectorAll("img[data-km-src]").forEach(function(i){'
			. 'var u=i.getAttribute("data-km-src");if(u&&(!i.src||i.src.indexOf("data:image/gif")!==-1)){i.src=u;i.removeAttribute("data-km-src");}});};'
			. 'document.addEventListener("DOMContentLoaded",function(){'
			. 'if("IntersectionObserver" in window){var io=new IntersectionObserver(function(es){es.forEach(function(e){if(e.isIntersecting){var sc=e.target.closest("picture")||e.target;kmLoadDeferredImages(sc);io.unobserve(e.target);}});},{rootMargin:"200px"});'
			. 'document.querySelectorAll("img[data-km-src]").forEach(function(i){io.observe(i);});}'
			. 'else{kmLoadDeferredImages(document);}'
			. 'var vb=document.querySelector(".tabs-main .tabs__box[style*=block]")||document.querySelector(".tabs-main .tabs__box");'
			. 'if(vb){kmLoadDeferredImages(vb);}'
			. '});</script>';

		if (stripos($content, '</body>') !== false) {
			$content = str_replace('</body>', $script . '</body>', $content);
		}
	}
}

if (!function_exists('kmOnEndBufferContent')) {
	function kmOnEndBufferContent(string &$content): void
	{
		kmInjectCriticalHomeCss($content);
		kmInjectLcpPreload($content);
		kmInjectLazyImages($content);
		kmInjectWebpImages($content);
		kmInjectBackgroundWebp($content);
		kmFixFontDisplay($content);
		kmDeferHomeStylesheets($content);
		kmDeferCatalogStylesheets($content);
		kmDeferHomeOffscreenImages($content);
		kmDeferCatalogOffscreenImages($content);
		kmDeferProductOffscreenImages($content);
		kmDeferHomeScripts($content);
		kmDeferCatalogScripts($content);
		kmStripPullOnStorefront($content);
		kmResequenceCoreScripts($content);
		kmInjectHomeDeferredLoader($content);
	}
}

if (!function_exists('kmOnEndBufferContentPost')) {
	/** Runs after arturgolubev.lazyimage (sort 200 > default 100). */
	function kmOnEndBufferContentPost(string &$content): void
	{
		kmFixCatalogSliderImages($content);
		kmFixLcpImages($content);
		kmFixYandexMetrikaInformer($content);
	}
}

if (function_exists('AddEventHandler')) {
	AddEventHandler('main', 'OnEndBufferContent', 'kmOnEndBufferContent');
	AddEventHandler('main', 'OnEndBufferContent', 'kmOnEndBufferContentPost', 200);
	AddEventHandler('main', 'OnPageStart', 'kmDisablePullOnStorefront');
	AddEventHandler('main', 'OnAfterFileSave', 'kmWebpOnAfterFileSave');
}

if (!function_exists('kmEnsureCssinliner')) {
	/** One-time prod fix: stop inlining 300+ KB CSS into HTML. */
	function kmEnsureCssinliner(): void
	{
		if (defined('ADMIN_SECTION') || $_SERVER['REQUEST_METHOD'] === 'POST') {
			return;
		}
		if (\COption::GetOptionString('main', 'km_cssinliner_v2', '') === 'Y') {
			return;
		}
		if (!\CModule::IncludeModule('arturgolubev.cssinliner')) {
			return;
		}

		$moduleId = 'arturgolubev.cssinliner';
		$exceptions = implode("\n", [
			'colors.css',
			'template_styles.css',
			'template_styles.catalog',
			'template_styles.personal',
			'template_styles.compare',
			'font-awesome',
			'slider.css',
			'fancybox',
			'slick.css',
			'custom-forms.css',
			'ui.font',
			'opensans',
		]);

		\COption::SetOptionString($moduleId, 'inline_max_weight', '48');
		\COption::SetOptionString($moduleId, 'exceptions', $exceptions);
		\COption::SetOptionString('main', 'km_cssinliner_v2', 'Y');
	}
}

if (function_exists('AddEventHandler')) {
	AddEventHandler('main', 'OnPageStart', 'kmEnsureCssinliner');
}

if (!function_exists('kmDeferStylesheet')) {
	/** Non-blocking CSS — for below-the-fold blocks (e.g. catalog cards on homepage). */
	function kmDeferStylesheet(string $href): void
	{
		$href = htmlspecialcharsbx($href, ENT_QUOTES);
		\Bitrix\Main\Page\Asset::getInstance()->addString(
			'<link rel="preload" as="style" href="' . $href . '" onload="this.onload=null;this.rel=\'stylesheet\'">'
			. '<noscript><link rel="stylesheet" href="' . $href . '"></noscript>',
			true
		);
	}
}
