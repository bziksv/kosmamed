#!/usr/bin/env php
<?php
/**
 * Генерация превью для подкатегорий каталога (IBLOCK 24) без PICTURE/UF_ICON.
 *
 * Использование:
 *   php tools/section-previews/generate.php scan
 *   php tools/section-previews/generate.php apply [--limit=N] [--allow-generated]
 *   php tools/section-previews/generate.php fix-generated
 *   php tools/section-previews/generate.php reapply-products
 *   php tools/section-previews/generate.php fix-inherited
 *   php tools/section-previews/generate.php rollback
 *
 * Manifest: tools/section-previews/manifest.json
 * Список:   tools/section-previews/sections.csv
 */

if (PHP_SAPI !== 'cli') {
    fwrite(STDERR, "CLI only\n");
    exit(1);
}

const IBLOCK_ID = 24;
const IMG_SIZE = 200;
const TOOL_DIR = __DIR__;
const MANIFEST_PATH = TOOL_DIR . '/manifest.json';
const CSV_PATH = TOOL_DIR . '/sections.csv';
const FILES_DIR = TOOL_DIR . '/files';

$mode = $argv[1] ?? 'help';
$limit = null;
$allowGenerated = false;
foreach ($argv as $arg) {
    if (preg_match('/^--limit=(\d+)$/', $arg, $m)) {
        $limit = (int)$m[1];
    }
    if ($arg === '--allow-generated') {
        $allowGenerated = true;
    }
}

$docRoot = realpath(__DIR__ . '/../..');
if (!$docRoot) {
    fwrite(STDERR, "DOCUMENT_ROOT not found\n");
    exit(1);
}

$_SERVER['DOCUMENT_ROOT'] = $docRoot;
$_SERVER['HTTP_HOST'] = 'kosmamed.ru';
$_SERVER['SERVER_NAME'] = 'kosmamed.ru';
$_SERVER['REQUEST_URI'] = '/';
$_SERVER['REMOTE_ADDR'] = '127.0.0.1';

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('BX_CRONTAB', true);

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

while (ob_get_level() > 0) {
    ob_end_clean();
}

if (!CModule::IncludeModule('iblock')) {
    fwrite(STDERR, "iblock module missing\n");
    exit(1);
}

if (!is_dir(FILES_DIR)) {
    mkdir(FILES_DIR, 0755, true);
}

switch ($mode) {
    case 'scan':
        cmdScan();
        break;
    case 'apply':
        cmdApply($limit, $allowGenerated);
        break;
    case 'fix-generated':
        cmdFixGenerated(['generated']);
        break;
    case 'reapply-products':
        cmdFixGenerated(['generated', 'cleared']);
        break;
    case 'fix-inherited':
        cmdFixInherited();
        break;
    case 'rollback':
        cmdRollback();
        break;
    default:
        echo "Commands: scan | apply [--limit=N] [--allow-generated] | fix-generated | reapply-products | fix-inherited | rollback\n";
        exit($mode === 'help' ? 0 : 1);
}

function cmdScan(): void
{
    $sections = loadTargetSections();
    $rows = [];
    foreach ($sections as $s) {
        $source = detectSource($s['ID']);
        $rows[] = [
            'section_id' => (int)$s['ID'],
            'name' => $s['NAME'],
            'code' => $s['CODE'],
            'depth_level' => (int)$s['DEPTH_LEVEL'],
            'parent_id' => (int)$s['IBLOCK_SECTION_ID'],
            'old_picture' => (int)$s['PICTURE'],
            'planned_source' => $source['type'],
            'planned_product_id' => $source['product_id'],
        ];
    }

    writeCsv($rows);
    echo 'Sections without preview: ' . count($rows) . PHP_EOL;
    echo 'CSV: ' . CSV_PATH . PHP_EOL;
}

function cmdApply(?int $limit, bool $allowGenerated = false): void
{
    if (is_file(MANIFEST_PATH)) {
        fwrite(STDERR, "Manifest already exists. Run rollback first or remove it.\n");
        exit(1);
    }

    $sections = loadTargetSections();
    if ($limit !== null) {
        $sections = array_slice($sections, 0, $limit);
    }

    $manifest = [
        'created_at' => date('c'),
        'iblock_id' => IBLOCK_ID,
        'items' => [],
    ];

    $ok = 0;
    $fail = 0;
    $total = count($sections);

    foreach ($sections as $i => $s) {
        $sectionId = (int)$s['ID'];
        $num = $i + 1;

        try {
            $item = applyOne($s, $allowGenerated);
            $manifest['items'][] = $item;
            $ok++;
            echo sprintf("[%d/%d] OK #%d %s (%s)\n", $num, $total, $sectionId, $s['NAME'], $item['source']);
        } catch (Throwable $e) {
            $fail++;
            echo sprintf("[%d/%d] FAIL #%d %s: %s\n", $num, $total, $sectionId, $s['NAME'], $e->getMessage());
        }

        if ($num % 100 === 0) {
            file_put_contents(MANIFEST_PATH, json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }
    }

    file_put_contents(MANIFEST_PATH, json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
    writeCsv(array_map(static function ($item) {
        return [
            'section_id' => $item['section_id'],
            'name' => $item['name'],
            'code' => $item['code'],
            'depth_level' => $item['depth_level'],
            'parent_id' => $item['parent_id'],
            'old_picture' => $item['old_picture'],
            'new_picture' => $item['new_picture'],
            'source' => $item['source'],
            'source_product_id' => $item['source_product_id'],
            'local_file' => $item['local_file'],
        ];
    }, $manifest['items']));

    if (isset($GLOBALS['CACHE_MANAGER']) && is_object($GLOBALS['CACHE_MANAGER'])) {
        $GLOBALS['CACHE_MANAGER']->ClearByTag('iblock_id_' . IBLOCK_ID);
    }

    echo PHP_EOL . "Done: ok={$ok}, fail={$fail}" . PHP_EOL;
    echo 'Manifest: ' . MANIFEST_PATH . PHP_EOL;
}

function cmdFixGenerated(array $sources = ['generated']): void
{
    if (!is_file(MANIFEST_PATH)) {
        fwrite(STDERR, "Manifest not found: " . MANIFEST_PATH . PHP_EOL);
        exit(1);
    }

    $manifest = json_decode(file_get_contents(MANIFEST_PATH), true);
    if (!is_array($manifest) || empty($manifest['items'])) {
        fwrite(STDERR, "Invalid manifest\n");
        exit(1);
    }

    $sources = array_fill_keys($sources, true);
    $toFix = [];
    foreach ($manifest['items'] as $idx => $item) {
        if (isset($sources[$item['source'] ?? ''])) {
            $toFix[] = $idx;
        }
    }

    $total = count($toFix);
    if ($total === 0) {
        echo "No section previews to fix for sources: " . implode(', ', array_keys($sources)) . PHP_EOL;
        return;
    }

    echo "Fixing {$total} section previews...\n";

    $productOk = 0;
    $cleared = 0;
    $fail = 0;

    foreach ($toFix as $n => $idx) {
        $item = &$manifest['items'][$idx];
        $sectionId = (int)$item['section_id'];
        $num = $n + 1;

        try {
            $section = CIBlockSection::GetList(
                [],
                ['ID' => $sectionId, 'IBLOCK_ID' => IBLOCK_ID],
                false,
                ['ID', 'NAME', 'CODE', 'DEPTH_LEVEL', 'PICTURE', 'IBLOCK_SECTION_ID']
            )->Fetch();

            if (!$section) {
                throw new RuntimeException('section not found');
            }

            $oldPicture = (int)($item['new_picture'] ?? 0);
            $product = findProductImage($sectionId);

            if ($product) {
                $updated = replaceSectionPicture($section, $product, $oldPicture);
                $item['source'] = 'product';
                $item['source_product_id'] = (int)$product['ID'];
                $item['new_picture'] = $updated['new_picture'];
                $item['local_file'] = $updated['local_file'];
                $productOk++;
                echo sprintf("[%d/%d] PRODUCT #%d %s\n", $num, $total, $sectionId, $section['NAME']);
            } else {
                clearSectionPicture($sectionId, $oldPicture);
                if (!empty($item['local_file']) && is_file(TOOL_DIR . '/' . $item['local_file'])) {
                    @unlink(TOOL_DIR . '/' . $item['local_file']);
                }
                $item['source'] = 'cleared';
                $item['source_product_id'] = 0;
                $item['new_picture'] = 0;
                $item['local_file'] = '';
                $cleared++;
                echo sprintf("[%d/%d] CLEARED #%d %s\n", $num, $total, $sectionId, $section['NAME']);
            }
        } catch (Throwable $e) {
            $fail++;
            echo sprintf("[%d/%d] FAIL #%d: %s\n", $num, $total, $sectionId, $e->getMessage());
        }
        unset($item);

        if ($num % 100 === 0) {
            $manifest['fixed_at'] = date('c');
            file_put_contents(MANIFEST_PATH, json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }
    }

    $manifest['fixed_at'] = date('c');
    file_put_contents(MANIFEST_PATH, json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    writeCsv(array_map(static function ($item) {
        return [
            'section_id' => $item['section_id'],
            'name' => $item['name'],
            'code' => $item['code'],
            'depth_level' => $item['depth_level'],
            'parent_id' => $item['parent_id'],
            'old_picture' => $item['old_picture'],
            'new_picture' => $item['new_picture'],
            'source' => $item['source'],
            'source_product_id' => $item['source_product_id'],
            'local_file' => $item['local_file'],
        ];
    }, $manifest['items']));

    if (isset($GLOBALS['CACHE_MANAGER']) && is_object($GLOBALS['CACHE_MANAGER'])) {
        $GLOBALS['CACHE_MANAGER']->ClearByTag('iblock_id_' . IBLOCK_ID);
    }

    echo PHP_EOL . "Done: product={$productOk}, cleared={$cleared}, fail={$fail}" . PHP_EOL;
}

function cmdFixInherited(): void
{
    if (!is_file(MANIFEST_PATH)) {
        fwrite(STDERR, "Manifest not found: " . MANIFEST_PATH . PHP_EOL);
        exit(1);
    }

    $manifest = json_decode(file_get_contents(MANIFEST_PATH), true);
    if (!is_array($manifest) || empty($manifest['items'])) {
        fwrite(STDERR, "Invalid manifest\n");
        exit(1);
    }

    $total = count($manifest['items']);
    echo "Re-evaluating {$total} section previews (fix inherited/wrong subtree images)...\n";

    $kept = 0;
    $replaced = 0;
    $cleared = 0;
    $fail = 0;

    foreach ($manifest['items'] as $idx => &$item) {
        $sectionId = (int)$item['section_id'];
        $num = $idx + 1;
        $oldNewPicture = (int)($item['new_picture'] ?? 0);
        $oldProductId = (int)($item['source_product_id'] ?? 0);

        try {
            $section = CIBlockSection::GetList(
                [],
                ['ID' => $sectionId, 'IBLOCK_ID' => IBLOCK_ID],
                false,
                ['ID', 'NAME', 'CODE', 'DEPTH_LEVEL', 'PICTURE', 'IBLOCK_SECTION_ID']
            )->Fetch();

            if (!$section) {
                throw new RuntimeException('section not found');
            }

            $product = findProductImage($sectionId);
            $newProductId = $product ? (int)$product['ID'] : 0;

            if ($newProductId === $oldProductId && $oldNewPicture > 0) {
                $kept++;
                if ($num % 500 === 0) {
                    echo "[{$num}/{$total}] kept (unchanged)\n";
                }
                continue;
            }

            if ($product) {
                $updated = replaceSectionPicture($section, $product, $oldNewPicture);
                $item['source'] = 'product';
                $item['source_product_id'] = (int)$product['ID'];
                $item['source_scope'] = $product['scope'] ?? '';
                $item['new_picture'] = $updated['new_picture'];
                $item['local_file'] = $updated['local_file'];
                $replaced++;
                echo sprintf("[%d/%d] REPLACE #%d %s -> product #%d (%s)\n", $num, $total, $sectionId, $section['NAME'], $product['ID'], $product['scope'] ?? '');
            } else {
                clearSectionPicture($sectionId, $oldNewPicture);
                if (!empty($item['local_file']) && is_file(TOOL_DIR . '/' . $item['local_file'])) {
                    @unlink(TOOL_DIR . '/' . $item['local_file']);
                }
                $item['source'] = 'cleared';
                $item['source_product_id'] = 0;
                $item['source_scope'] = '';
                $item['new_picture'] = 0;
                $item['local_file'] = '';
                $cleared++;
                echo sprintf("[%d/%d] CLEARED #%d %s (was product #%d)\n", $num, $total, $sectionId, $section['NAME'], $oldProductId);
            }
        } catch (Throwable $e) {
            $fail++;
            echo sprintf("[%d/%d] FAIL #%d: %s\n", $num, $total, $sectionId, $e->getMessage());
        }

        if ($num % 200 === 0) {
            $manifest['fixed_inherited_at'] = date('c');
            file_put_contents(MANIFEST_PATH, json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        }
    }
    unset($item);

    $manifest['fixed_inherited_at'] = date('c');
    file_put_contents(MANIFEST_PATH, json_encode($manifest, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));

    writeCsv(array_map(static function ($item) {
        return [
            'section_id' => $item['section_id'],
            'name' => $item['name'],
            'code' => $item['code'],
            'depth_level' => $item['depth_level'],
            'parent_id' => $item['parent_id'],
            'old_picture' => $item['old_picture'],
            'new_picture' => $item['new_picture'],
            'source' => $item['source'],
            'source_product_id' => $item['source_product_id'],
            'local_file' => $item['local_file'],
        ];
    }, $manifest['items']));

    if (isset($GLOBALS['CACHE_MANAGER']) && is_object($GLOBALS['CACHE_MANAGER'])) {
        $GLOBALS['CACHE_MANAGER']->ClearByTag('iblock_id_' . IBLOCK_ID);
    }

    echo PHP_EOL . "Done: kept={$kept}, replaced={$replaced}, cleared={$cleared}, fail={$fail}" . PHP_EOL;
}

function cmdRollback(): void
{
    if (!is_file(MANIFEST_PATH)) {
        fwrite(STDERR, "Manifest not found: " . MANIFEST_PATH . PHP_EOL);
        exit(1);
    }

    $manifest = json_decode(file_get_contents(MANIFEST_PATH), true);
    if (!is_array($manifest) || empty($manifest['items'])) {
        fwrite(STDERR, "Invalid manifest\n");
        exit(1);
    }

    $bs = new CIBlockSection();
    $restored = 0;

    foreach ($manifest['items'] as $item) {
        $sectionId = (int)$item['section_id'];
        $oldPicture = (int)$item['old_picture'];
        $newPicture = (int)$item['new_picture'];

        if ($oldPicture > 0) {
            $fields = ['PICTURE' => $oldPicture];
        } else {
            $fields = ['PICTURE' => ['del' => 'Y']];
        }

        if (!$bs->Update($sectionId, $fields)) {
            echo "WARN: section #{$sectionId} rollback update failed\n";
            continue;
        }

        if ($newPicture > 0 && $oldPicture !== $newPicture) {
            CFile::Delete($newPicture);
        }

        $local = $item['local_file'] ?? '';
        if ($local && is_file(TOOL_DIR . '/' . $local)) {
            @unlink(TOOL_DIR . '/' . $local);
        }

        $restored++;
    }

    if (isset($GLOBALS['CACHE_MANAGER']) && is_object($GLOBALS['CACHE_MANAGER'])) {
        $GLOBALS['CACHE_MANAGER']->ClearByTag('iblock_id_' . (int)($manifest['iblock_id'] ?? IBLOCK_ID));
    }

    $backup = MANIFEST_PATH . '.rolled-back-' . date('Ymd-His') . '.json';
    rename(MANIFEST_PATH, $backup);

    echo "Rollback complete: {$restored} sections restored\n";
    echo "Manifest archived: {$backup}\n";
}

function loadTargetSections(): array
{
    $out = [];
    $rs = CIBlockSection::GetList(
        ['LEFT_MARGIN' => 'ASC'],
        [
            'IBLOCK_ID' => IBLOCK_ID,
            'ACTIVE' => 'Y',
            'GLOBAL_ACTIVE' => 'Y',
            '>DEPTH_LEVEL' => 1,
        ],
        false,
        ['ID', 'NAME', 'CODE', 'DEPTH_LEVEL', 'PICTURE', 'UF_ICON', 'IBLOCK_SECTION_ID']
    );

    while ($s = $rs->Fetch()) {
        if (!empty($s['UF_ICON']) || (int)$s['PICTURE'] > 0) {
            continue;
        }
        $out[] = $s;
    }

    return $out;
}

function detectSource(int $sectionId): array
{
    $product = findProductImage($sectionId);
    if ($product) {
        return [
            'type' => 'product',
            'product_id' => (int)$product['ID'],
            'file_id' => (int)$product['FILE_ID'],
            'scope' => $product['scope'] ?? '',
        ];
    }

    return ['type' => 'generated', 'product_id' => 0, 'file_id' => 0, 'scope' => ''];
}

function findProductImage(int $sectionId): ?array
{
    $result = findProductImageInSection($sectionId, true);
    if ($result) {
        $result['scope'] = 'subtree';
    }

    return $result ?: null;
}

function findProductImageInSection(int $sectionId, bool $includeSubsections): ?array
{
    $rs = CIBlockElement::GetList(
        ['SORT' => 'ASC', 'ID' => 'ASC'],
        [
            'IBLOCK_ID' => IBLOCK_ID,
            'SECTION_ID' => $sectionId,
            'INCLUDE_SUBSECTIONS' => $includeSubsections ? 'Y' : 'N',
            'ACTIVE' => 'Y',
        ],
        false,
        ['nTopCount' => 500],
        ['ID', 'NAME', 'PREVIEW_PICTURE', 'DETAIL_PICTURE']
    );

    while ($el = $rs->Fetch()) {
        $fileId = resolveElementImageFileId($el);
        if ($fileId > 0) {
            return ['ID' => (int)$el['ID'], 'FILE_ID' => $fileId];
        }
    }

    return null;
}

function resolveElementImageFileId(array $element): int
{
    $fileId = (int)$element['PREVIEW_PICTURE'] ?: (int)$element['DETAIL_PICTURE'];
    if (isFileAvailable($fileId)) {
        return $fileId;
    }

    $rs = CIBlockElement::GetProperty(IBLOCK_ID, (int)$element['ID'], ['sort' => 'asc'], ['CODE' => 'MORE_PHOTO']);
    while ($prop = $rs->Fetch()) {
        if (!empty($prop['VALUE']) && isFileAvailable((int)$prop['VALUE'])) {
            return (int)$prop['VALUE'];
        }
    }

    $rs = CIBlockElement::GetProperty(IBLOCK_ID, (int)$element['ID'], ['sort' => 'asc'], ['CODE' => 'INFOGRAPHICS']);
    while ($prop = $rs->Fetch()) {
        if (!empty($prop['VALUE']) && isFileAvailable((int)$prop['VALUE'])) {
            return (int)$prop['VALUE'];
        }
    }

    return 0;
}

function isFileAvailable(int $fileId): bool
{
    if ($fileId <= 0) {
        return false;
    }

    $file = CFile::GetFileArray($fileId);
    if (empty($file['SRC'])) {
        return false;
    }

    return is_file($_SERVER['DOCUMENT_ROOT'] . $file['SRC']);
}

function replaceSectionPicture(array $section, array $product, int $deletePictureId = 0): array
{
    $sectionId = (int)$section['ID'];
    $localRel = 'files/' . $sectionId . '.png';
    $localAbs = TOOL_DIR . '/' . $localRel;

    $resized = CFile::ResizeImageGet(
        $product['FILE_ID'],
        ['width' => IMG_SIZE, 'height' => IMG_SIZE],
        BX_RESIZE_IMAGE_PROPORTIONAL,
        true,
        []
    );
    if (empty($resized['src'])) {
        throw new RuntimeException('product resize failed');
    }
    $srcPath = $_SERVER['DOCUMENT_ROOT'] . $resized['src'];
    if (!is_file($srcPath)) {
        throw new RuntimeException('resized file missing');
    }
    copy($srcPath, $localAbs);

    $fileArray = CFile::MakeFileArray($localAbs);
    $fileArray['MODULE_ID'] = 'iblock';
    $fileArray['description'] = 'section preview #' . $sectionId;

    $bs = new CIBlockSection();
    if (!$bs->Update($sectionId, ['PICTURE' => $fileArray])) {
        throw new RuntimeException($bs->LAST_ERROR ?: 'section update failed');
    }

    $updated = CIBlockSection::GetList([], ['ID' => $sectionId], false, ['ID', 'PICTURE'])->Fetch();
    $newPicture = (int)($updated['PICTURE'] ?? 0);
    if ($newPicture <= 0) {
        throw new RuntimeException('picture not saved');
    }

    if ($deletePictureId > 0 && $deletePictureId !== $newPicture) {
        CFile::Delete($deletePictureId);
    }

    return [
        'new_picture' => $newPicture,
        'local_file' => $localRel,
    ];
}

function clearSectionPicture(int $sectionId, int $deletePictureId = 0): void
{
    $bs = new CIBlockSection();
    if (!$bs->Update($sectionId, ['PICTURE' => ['del' => 'Y']])) {
        throw new RuntimeException($bs->LAST_ERROR ?: 'section clear failed');
    }

    if ($deletePictureId > 0) {
        CFile::Delete($deletePictureId);
    }
}

function applyOne(array $section, bool $allowGenerated = false): array
{
    $sectionId = (int)$section['ID'];
    $source = detectSource($sectionId);
    $localRel = 'files/' . $sectionId . '.png';
    $localAbs = TOOL_DIR . '/' . $localRel;

    if ($source['type'] === 'product') {
        $updated = replaceSectionPicture($section, [
            'ID' => (int)$source['product_id'],
            'FILE_ID' => (int)$source['file_id'],
        ]);
        $newPicture = $updated['new_picture'];
    } elseif ($allowGenerated) {
        generatePlaceholder($section['NAME'], $localAbs);
        $fileArray = CFile::MakeFileArray($localAbs);
        $fileArray['MODULE_ID'] = 'iblock';
        $fileArray['description'] = 'section preview #' . $sectionId;

        $bs = new CIBlockSection();
        $oldPicture = (int)$section['PICTURE'];
        if (!$bs->Update($sectionId, ['PICTURE' => $fileArray])) {
            throw new RuntimeException($bs->LAST_ERROR ?: 'section update failed');
        }

        $updatedRow = CIBlockSection::GetList([], ['ID' => $sectionId], false, ['ID', 'PICTURE'])->Fetch();
        $newPicture = (int)($updatedRow['PICTURE'] ?? 0);
        if ($newPicture <= 0) {
            throw new RuntimeException('picture not saved');
        }
    } else {
        throw new RuntimeException('no product image found (use --allow-generated to force placeholder)');
    }

    $oldPicture = (int)$section['PICTURE'];

    return [
        'section_id' => $sectionId,
        'name' => $section['NAME'],
        'code' => $section['CODE'],
        'depth_level' => (int)$section['DEPTH_LEVEL'],
        'parent_id' => (int)$section['IBLOCK_SECTION_ID'],
        'old_picture' => $oldPicture,
        'new_picture' => $newPicture,
        'source' => $source['type'] === 'product' ? 'product' : 'generated',
        'source_product_id' => (int)$source['product_id'],
        'source_scope' => $source['scope'] ?? '',
        'local_file' => $localRel,
    ];
}

function generatePlaceholder(string $name, string $path): void
{
    $im = imagecreatetruecolor(IMG_SIZE, IMG_SIZE);
    imagesavealpha($im, true);

    $bg = imagecolorallocate($im, 248, 250, 252);
    $border = imagecolorallocate($im, 226, 232, 240);
    $primary = imagecolorallocate($im, 14, 116, 144);
    $primaryDark = imagecolorallocate($im, 21, 94, 117);
    $textColor = imagecolorallocate($im, 30, 41, 59);

    imagefilledrectangle($im, 0, 0, IMG_SIZE, IMG_SIZE, $bg);
    imagerectangle($im, 0, 0, IMG_SIZE - 1, IMG_SIZE - 1, $border);

    $cx = (int)(IMG_SIZE / 2);
    $cy = 72;
    imagefilledellipse($im, $cx, $cy, 86, 86, imagecolorallocate($im, 236, 254, 255));
    imageellipse($im, $cx, $cy, 86, 86, $primary);
    imagefilledrectangle($im, $cx - 22, $cy - 5, $cx + 22, $cy + 5, $primary);
    imagefilledrectangle($im, $cx - 5, $cy - 22, $cx + 5, $cy + 22, $primary);

    $font = findFont();
    $lines = wrapText($name, $font, 11, IMG_SIZE - 24, 3);
    $lineHeight = 15;
    $startY = 130 + (int)((3 - count($lines)) * $lineHeight / 2);

    foreach ($lines as $idx => $line) {
        $box = imagettfbbox(11, 0, $font, $line);
        $textW = abs($box[2] - $box[0]);
        $x = (int)((IMG_SIZE - $textW) / 2);
        $y = $startY + ($idx * $lineHeight);
        imagettftext($im, 11, 0, $x, $y, $textColor, $font, $line);
    }

    imagepng($im, $path);
    imagedestroy($im);
}

function wrapText(string $text, string $font, int $size, int $maxWidth, int $maxLines): array
{
    $words = preg_split('/\s+/u', trim($text)) ?: [];
    $lines = [];
    $current = '';

    foreach ($words as $word) {
        $candidate = $current === '' ? $word : $current . ' ' . $word;
        $box = imagettfbbox($size, 0, $font, $candidate);
        $w = abs($box[2] - $box[0]);
        if ($w <= $maxWidth) {
            $current = $candidate;
            continue;
        }
        if ($current !== '') {
            $lines[] = $current;
            $current = $word;
        } else {
            $lines[] = mb_substr($word, 0, 18, 'UTF-8');
            $current = '';
        }
        if (count($lines) >= $maxLines) {
            break;
        }
    }

    if ($current !== '' && count($lines) < $maxLines) {
        $lines[] = $current;
    }

    if (count($lines) > $maxLines) {
        $lines = array_slice($lines, 0, $maxLines);
    }

    if (count($lines) === $maxLines) {
        $last = $lines[$maxLines - 1];
        if (mb_strlen($last, 'UTF-8') > 20) {
            $lines[$maxLines - 1] = mb_substr($last, 0, 17, 'UTF-8') . '…';
        }
    }

    return $lines ?: [mb_substr($text, 0, 20, 'UTF-8')];
}

function findFont(): string
{
    static $font = null;
    if ($font !== null) {
        return $font;
    }

    $candidates = [
        '/System/Library/Fonts/Supplemental/Arial.ttf',
        '/Library/Fonts/Arial.ttf',
        '/usr/share/fonts/truetype/dejavu/DejaVuSans.ttf',
        '/usr/share/fonts/dejavu/DejaVuSans.ttf',
    ];

    foreach ($candidates as $path) {
        if (is_file($path)) {
            $font = $path;
            return $font;
        }
    }

    throw new RuntimeException('TTF font not found for Cyrillic text');
}

function writeCsv(array $rows): void
{
    $fp = fopen(CSV_PATH, 'w');
    if (!$fp) {
        throw new RuntimeException('cannot write CSV');
    }

    if ($rows) {
        fputcsv($fp, array_keys($rows[0]));
        foreach ($rows as $row) {
            fputcsv($fp, $row);
        }
    }
    fclose($fp);
}
