<?php
/**
 * CLI WebP warmup via GD (kmGenerateWebpSrc). Usage:
 *   php tools/perf/webp-warmup.php /path/to/upload [--limit=5000] [--include-resize-cache]
 */
$_SERVER['DOCUMENT_ROOT'] = dirname(__DIR__, 2);
define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

$root = $argv[1] ?? '';
if ($root === '' || !is_dir($root)) {
	fwrite(STDERR, "Usage: php webp-warmup.php <upload-dir> [--limit=N] [--include-resize-cache]\n");
	exit(1);
}

$limit = 0;
$includeResize = false;
foreach (array_slice($argv, 2) as $arg) {
	if (preg_match('/^--limit=(\d+)$/', $arg, $m)) {
		$limit = (int)$m[1];
	} elseif ($arg === '--include-resize-cache') {
		$includeResize = true;
	}
}

$root = rtrim(realpath($root), '/');
$created = 0;
$skipped = 0;
$failed = 0;

$iterator = new RecursiveIteratorIterator(
	new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $file) {
	if (!$file->isFile()) {
		continue;
	}
	$path = $file->getPathname();
	if (!$includeResize && strpos($path, '/resize_cache/') !== false) {
		continue;
	}
	if (!preg_match('/\.(?:jpe?g|png)$/i', $path)) {
		continue;
	}

	$relative = substr($path, strlen($_SERVER['DOCUMENT_ROOT']));
	if ($limit > 0 && ($created + $skipped + $failed) >= $limit) {
		break;
	}

	$existing = $path . '.webp';
	if (is_file($existing) && filemtime($existing) >= filemtime($path)) {
		$skipped++;
		continue;
	}

	$result = kmGenerateWebpSrc($relative);
	if ($result !== null) {
		$created++;
	} else {
		$failed++;
	}

	if (($created + $skipped + $failed) % 500 === 0) {
		echo '... processed ' . ($created + $skipped + $failed) . " (created=$created skipped=$skipped failed=$failed)\n";
	}
}

echo "Done: created=$created skipped=$skipped failed=$failed\n";
