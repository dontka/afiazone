<?php
/**
 * Convert all back-end pages to use the layout system.
 *
 * Page types:
 *   - admin: Pages with sidebar + navbar (layout-content-navbar) → admin.php layout
 *   - auth:  Pages with customizer-hide (no sidebar)             → auth.php layout
 *   - standalone: payment-print.php (skipped)
 *   - fragment:   Pages without DOCTYPE (wrapped with ob_start)
 *
 * Usage: php bin/convert-back-pages.php [--dry-run]
 */

$dryRun = in_array('--dry-run', $argv ?? []);
$baseDir = realpath(__DIR__ . '/../html/back');

$excludeDirs = ['layouts', 'assets'];

// Files to skip (standalone, no layout)
$skipFiles = [
    'payments/payment-print.php',
];

// ─── Known CSS in admin.php layout ────────────────────────
$adminLayoutCss = [
    '/assets/vendor/fonts/fontawesome.css',
    '/assets/vendor/fonts/tabler-icons.css',
    '/assets/vendor/fonts/flag-icons.css',
    '/assets/vendor/css/rtl/core.css',
    '/assets/vendor/css/rtl/theme-default.css',
    '/assets/css/demo.css',
    '/assets/vendor/libs/node-waves/node-waves.css',
    '/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css',
    '/assets/vendor/libs/typeahead-js/typeahead.css',
    '/assets/vendor/libs/@form-validation/umd/styles/index.min.css',
    '/assets/vendor/libs/apex-charts/apex-charts.css',
    '/assets/vendor/libs/swiper/swiper.css',
    '/assets/vendor/libs/datatables-bs5/datatables.bootstrap5.css',
    '/assets/vendor/libs/datatables-responsive-bs5/responsive.bootstrap5.css',
    '/assets/vendor/libs/datatables-buttons-bs5/buttons.bootstrap5.css',
    '/assets/vendor/libs/datatables-checkboxes-jquery/datatables.checkboxes.css',
];

// ─── Known JS in admin.php layout ─────────────────────────
$adminLayoutJs = [
    '/assets/vendor/js/helpers.js',
    '/assets/vendor/js/template-customizer.js',
    '/assets/js/config.js',
    '/assets/vendor/libs/jquery/jquery.js',
    '/assets/vendor/libs/popper/popper.js',
    '/assets/vendor/js/bootstrap.js',
    '/assets/vendor/libs/node-waves/node-waves.js',
    '/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js',
    '/assets/vendor/libs/hammer/hammer.js',
    '/assets/vendor/libs/i18n/i18n.js',
    '/assets/vendor/libs/typeahead-js/typeahead.js',
    '/assets/vendor/js/menu.js',
    '/assets/vendor/libs/apex-charts/apexcharts.js',
    '/assets/vendor/libs/datatables-bs5/datatables-bootstrap5.js',
    '/assets/js/main.js',
];

// ─── Known CSS in auth.php layout ─────────────────────────
$authLayoutCss = [
    '/assets/vendor/fonts/fontawesome.css',
    '/assets/vendor/fonts/tabler-icons.css',
    '/assets/vendor/fonts/flag-icons.css',
    '/assets/vendor/css/rtl/core.css',
    '/assets/vendor/css/rtl/theme-default.css',
    '/assets/css/demo.css',
    '/assets/vendor/libs/node-waves/node-waves.css',
    '/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.css',
    '/assets/vendor/libs/typeahead-js/typeahead.css',
    '/assets/vendor/libs/@form-validation/umd/styles/index.min.css',
    '/assets/vendor/css/pages/page-auth.css',
];

// ─── Known JS in auth.php layout ──────────────────────────
$authLayoutJs = [
    '/assets/vendor/js/helpers.js',
    '/assets/vendor/js/template-customizer.js',
    '/assets/js/config.js',
    '/assets/vendor/libs/jquery/jquery.js',
    '/assets/vendor/libs/popper/popper.js',
    '/assets/vendor/js/bootstrap.js',
    '/assets/vendor/libs/node-waves/node-waves.js',
    '/assets/vendor/libs/perfect-scrollbar/perfect-scrollbar.js',
    '/assets/vendor/libs/hammer/hammer.js',
    '/assets/vendor/libs/i18n/i18n.js',
    '/assets/vendor/libs/typeahead-js/typeahead.js',
    '/assets/vendor/js/menu.js',
    '/assets/vendor/libs/apex-charts/apexcharts.js',
    '/assets/js/main.js',
];

// ─── Collect PHP files ────────────────────────────────────
$files = [];
$it = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($baseDir));
foreach ($it as $f) {
    if ($f->isDir() || $f->getExtension() !== 'php') continue;
    $rel = str_replace([$baseDir . '\\', $baseDir . '/'], '', $f->getPathname());
    $rel = str_replace('\\', '/', $rel);
    $firstDir = explode('/', $rel)[0];
    if (in_array($firstDir, $excludeDirs)) continue;
    $files[$rel] = $f->getPathname();
}
ksort($files);

echo "Found " . count($files) . " PHP files to process.\n\n";

$stats = ['ok' => 0, 'skip' => 0, 'warn' => 0, 'fragment' => 0];

foreach ($files as $rel => $absPath) {
    // Skip list
    if (in_array($rel, $skipFiles)) {
        echo "SKIP  (standalone) $rel\n";
        $stats['skip']++;
        continue;
    }

    $src = file_get_contents($absPath);
    $lines = explode("\n", $src);

    // Already converted?
    if (strpos($src, 'ob_start()') !== false) {
        echo "SKIP  (converted)  $rel\n";
        $stats['skip']++;
        continue;
    }

    // ─── Fragment (no DOCTYPE) ───
    if (stripos($src, '<!DOCTYPE') === false) {
        $layoutFile = (strpos($rel, 'auth/') === 0) ? 'auth.php' : 'admin.php';
        $layoutReq = layoutRequirePath($rel, $layoutFile);
        $title = titleFromFilename($rel);

        $converted  = "<?php\n";
        $converted .= "\$pageTitle = " . var_export($title, true) . ";\n";
        $converted .= "ob_start();\n";
        $converted .= "?>\n";
        $converted .= rtrim($src) . "\n";
        $converted .= "<?php\n";
        $converted .= "\$content = ob_get_clean();\n";
        $converted .= "require __DIR__ . '/{$layoutReq}';\n";

        if (!$dryRun) file_put_contents($absPath, $converted);
        echo "OK    (fragment → $layoutFile) $rel\n";
        $stats['fragment']++;
        continue;
    }

    // ─── Full standalone page ───
    $type = detectType($src);
    $layoutFile = ($type === 'auth') ? 'auth.php' : 'admin.php';
    $layoutReq = layoutRequirePath($rel, $layoutFile);
    $knownCss = ($type === 'admin') ? $adminLayoutCss : $authLayoutCss;
    $knownJs  = ($type === 'admin') ? $adminLayoutJs  : $authLayoutJs;

    // Extract components
    $title  = extractTitle($lines);
    $allCss = extractCssHrefs($lines);
    $allJs  = extractJsSrcs($lines);

    // Extra CSS/JS not in layout
    $extraCss = array_values(array_diff($allCss, $knownCss));
    $extraJs  = array_values(array_diff($allJs, $knownJs));

    // Separate vendor vs page-specific
    [$vendorStyles, $pageStyles]   = splitVendorPage($extraCss, 'css');
    [$vendorScripts, $pageScripts] = splitVendorPage($extraJs, 'js');

    // Extract content
    if ($type === 'admin') {
        $content = extractAdminContent($lines);
    } else {
        $content = extractAuthContent($lines);
    }

    if ($content === null) {
        echo "WARN  (no content) $rel\n";
        $stats['warn']++;
        continue;
    }

    // Build converted file
    $converted = buildConverted(
        $title, $vendorStyles, $pageStyles,
        $vendorScripts, $pageScripts,
        $content, $layoutReq
    );

    if (!$dryRun) file_put_contents($absPath, $converted);
    echo "OK    ($type) $rel\n";
    $stats['ok']++;
}

echo "\n──────────────────────────────────────\n";
echo "Done: OK={$stats['ok']}, Fragments={$stats['fragment']}, Skip={$stats['skip']}, Warn={$stats['warn']}\n";
if ($dryRun) echo "(DRY RUN — no files modified)\n";

// ─── Syntax check ──────────────────────────────────────────
if (!$dryRun) {
    echo "\nSyntax check...\n";
    $errors = 0;
    foreach ($files as $rel => $absPath) {
        if (in_array($rel, $skipFiles)) continue;
        exec("php -l " . escapeshellarg($absPath) . " 2>&1", $output, $rc);
        if ($rc !== 0) {
            echo "SYNTAX ERROR: $rel\n";
            echo implode("\n", $output) . "\n";
            $errors++;
        }
        $output = [];
    }
    echo $errors === 0 ? "All files pass syntax check.\n" : "$errors file(s) with errors.\n";
}

// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
// Helper Functions
// ━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

function detectType(string $src): string
{
    if (preg_match('/class="[^"]*customizer-hide/', $src)) return 'auth';
    if (strpos($src, 'layout-content-navbar') !== false) return 'admin';
    if (strpos($src, 'layout-wrapper') !== false) return 'admin';
    // Standalone pages without sidebar/navbar → auth layout
    return 'auth';
}

function layoutRequirePath(string $rel, string $layoutFile): string
{
    $depth = substr_count($rel, '/');
    return str_repeat('../', $depth) . 'layouts/' . $layoutFile;
}

function titleFromFilename(string $rel): string
{
    $name = pathinfo($rel, PATHINFO_FILENAME);
    $name = str_replace(['admin-', '-'], ['', ' '], $name);
    return ucwords(trim($name)) . ' - AfiaZone';
}

function extractTitle(array $lines): string
{
    foreach ($lines as $line) {
        if (preg_match('/<title>(.*?)<\/title>/i', $line, $m)) {
            return trim($m[1]);
        }
    }
    return 'AfiaZone Admin';
}

function extractCssHrefs(array $lines): array
{
    $hrefs = [];
    foreach ($lines as $line) {
        // Match <link ... href="/..." ... rel="stylesheet"> (both attribute orders)
        if (preg_match('/<link[^>]+href="(\/[^"]+)"[^>]*rel="stylesheet"/i', $line, $m) ||
            preg_match('/<link[^>]+rel="stylesheet"[^>]*href="(\/[^"]+)"/i', $line, $m)) {
            $hrefs[] = $m[1];
        }
    }
    return $hrefs;
}

function extractJsSrcs(array $lines): array
{
    $srcs = [];
    foreach ($lines as $line) {
        if (preg_match('/<script[^>]+src="(\/[^"]+)"/i', $line, $m)) {
            $srcs[] = $m[1];
        }
    }
    return $srcs;
}

function splitVendorPage(array $extras, string $type): array
{
    $vendor = [];
    $page = [];
    foreach ($extras as $path) {
        if ($type === 'css') {
            // Vendor CSS in /assets/vendor/libs/ or /assets/vendor/css/ (but not /pages/)
            if (preg_match('#/assets/vendor/(libs|css)/#', $path) && strpos($path, '/pages/') === false) {
                $vendor[] = $path;
            } else {
                $page[] = $path;
            }
        } else {
            // Vendor JS: anything in /assets/vendor/
            if (strpos($path, '/assets/vendor/') !== false) {
                $vendor[] = $path;
            } else {
                $page[] = $path;
            }
        }
    }
    return [$vendor, $page];
}

function extractAdminContent(array $lines): ?string
{
    $start = null;
    $end = null;
    $n = count($lines);

    for ($i = 0; $i < $n; $i++) {
        if ($start === null && strpos($lines[$i], 'container-xxl flex-grow-1 container-p-y') !== false) {
            $start = $i + 1;
            continue;
        }
        if ($start !== null && strpos($lines[$i], '<!-- / Content -->') !== false) {
            $end = $i - 1;
            // Skip trailing blank lines
            while ($end >= $start && trim($lines[$end]) === '') $end--;
            // Skip the closing </div> of container-xxl
            if (trim($lines[$end]) === '</div>') $end--;
            break;
        }
    }

    if ($start === null || $end === null || $end < $start) return null;
    return implode("\n", array_slice($lines, $start, $end - $start + 1));
}

function extractAuthContent(array $lines): ?string
{
    $bodyIdx = null;
    $endIdx = null;
    $n = count($lines);

    for ($i = 0; $i < $n; $i++) {
        // Find <body> or <body ...>
        if ($bodyIdx === null && preg_match('/<body/', $lines[$i])) {
            $bodyIdx = $i + 1;
            continue;
        }
        // End at <!-- / Content -->
        if ($bodyIdx !== null && strpos($lines[$i], '<!-- / Content -->') !== false) {
            $endIdx = $i; // include this marker line
            break;
        }
    }

    // Fallback: end before Core JS
    if ($endIdx === null && $bodyIdx !== null) {
        for ($i = $bodyIdx; $i < $n; $i++) {
            if (strpos($lines[$i], '<!-- Core JS -->') !== false) {
                $endIdx = $i - 1;
                break;
            }
        }
    }

    // Fallback: end before </body>
    if ($endIdx === null && $bodyIdx !== null) {
        for ($i = $n - 1; $i >= $bodyIdx; $i--) {
            if (strpos($lines[$i], '</body>') !== false) {
                $endIdx = $i - 1;
                break;
            }
        }
    }

    if ($bodyIdx === null || $endIdx === null || $endIdx < $bodyIdx) return null;

    $content = implode("\n", array_slice($lines, $bodyIdx, $endIdx - $bodyIdx + 1));
    return trim($content);
}

function buildConverted(
    string $title,
    array $vendorStyles,
    array $pageStyles,
    array $vendorScripts,
    array $pageScripts,
    string $content,
    string $layoutReq
): string {
    $php = "<?php\n";
    $php .= "\$pageTitle = " . var_export($title, true) . ";\n";

    if ($vendorStyles) {
        $php .= "\$vendorStyles = " . formatArray($vendorStyles) . ";\n";
    }
    if ($pageStyles) {
        $php .= "\$pageStyles = " . formatArray($pageStyles) . ";\n";
    }
    if ($vendorScripts) {
        $php .= "\$vendorScripts = " . formatArray($vendorScripts) . ";\n";
    }
    if ($pageScripts) {
        $php .= "\$additionalScripts = " . formatArray($pageScripts) . ";\n";
    }

    $php .= "ob_start();\n";
    $php .= "?>\n";
    $php .= $content . "\n";
    $php .= "<?php\n";
    $php .= "\$content = ob_get_clean();\n";
    $php .= "require __DIR__ . '/{$layoutReq}';\n";

    return $php;
}

function formatArray(array $arr): string
{
    if (count($arr) === 1) {
        return "['" . addslashes($arr[0]) . "']";
    }
    $items = array_map(fn($v) => "    '" . addslashes($v) . "'", $arr);
    return "[\n" . implode(",\n", $items) . ",\n]";
}
