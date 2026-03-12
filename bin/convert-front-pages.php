<?php
/**
 * Convert front-end pages to use the shared layout system (frontend.php).
 *
 * Category C (general/*): Content-only fragments → wrap with layout include
 * Category B (other dirs): Full HTML pages → extract unique content, wrap with layout
 * order-invoice.php: Standalone → just fix asset paths
 *
 * Run: php bin/convert-front-pages.php
 */

$basePath = dirname(__DIR__);
$frontPath = $basePath . '/html/front';
$results = ['ok' => 0, 'skip' => 0, 'warn' => 0];

// ── Page titles ──────────────────────────────────────────────
$pageTitles = [
    'general/index.php'                        => 'AfiaZone - Accueil',
    'general/about.php'                        => 'À propos - AfiaZone',
    'general/404.php'                          => 'Page non trouvée - AfiaZone',
    'general/blog.php'                         => 'Blog - AfiaZone',
    'general/blog-article.php'                 => 'Article - AfiaZone',
    'general/blog-list-left-sidebar.php'       => 'Blog - AfiaZone',
    'general/blog-list-right-sidebar.php'      => 'Blog - AfiaZone',
    'general/contact.php'                      => 'Contact - AfiaZone',
    'general/cookies-policy.php'               => 'Politique de cookies - AfiaZone',
    'general/faq.php'                          => 'FAQ - AfiaZone',
    'general/privacy-policy.php'               => 'Politique de confidentialité - AfiaZone',
    'general/terms-condition.php'              => 'Conditions générales - AfiaZone',
    'auth/login.php'                           => 'Connexion - AfiaZone',
    'auth/register.php'                        => 'Inscription - AfiaZone',
    'catalog/compare-products.php'             => 'Comparer les produits - AfiaZone',
    'catalog/product-details.php'              => 'Détails du produit - AfiaZone',
    'catalog/product-details-affiliate.php'    => 'Détails du produit - AfiaZone',
    'catalog/product-details-group.php'        => 'Détails du produit - AfiaZone',
    'catalog/product-details-right-sidebar.php'=> 'Détails du produit - AfiaZone',
    'catalog/product-details-variable.php'     => 'Détails du produit - AfiaZone',
    'catalog/product-details-variant-1.php'    => 'Détails du produit - AfiaZone',
    'catalog/product-details-variant-2.php'    => 'Détails du produit - AfiaZone',
    'catalog/products-list-grid.php'           => 'Produits - AfiaZone',
    'catalog/products-list-sidebar.php'        => 'Produits - AfiaZone',
    'catalog/products-list-top-filter.php'     => 'Produits - AfiaZone',
    'delivery/tracking.php'                    => 'Suivi de livraison - AfiaZone',
    'shopping/cart.php'                        => 'Panier - AfiaZone',
    'shopping/checkout.php'                    => 'Paiement - AfiaZone',
    'shopping/wishlist.php'                    => 'Liste de souhaits - AfiaZone',
    'user/profile-edit.php'                    => 'Mon profil - AfiaZone',
];

// ── Helpers ──────────────────────────────────────────────────

/**
 * Fix /assets/ → /html/front/assets/ avoiding double-replacement.
 * Also handles relative url(assets/...) CSS patterns.
 */
function fixAssetPaths(string $content): string
{
    // /assets/ not preceded by /html/front → /html/front/assets/
    $content = preg_replace('#(?<!/html/front)(/assets/)#', '/html/front/assets/', $content);
    // url(assets/…) without leading /
    $content = preg_replace('#url\(assets/#', 'url(/html/front/assets/', $content);
    return $content;
}

/**
 * Wrap page content with the layout include boilerplate.
 */
function wrapWithLayout(string $content, string $title, string $inlineScripts = ''): string
{
    $titleExport = var_export($title, true);

    $php  = "<?php\n";
    $php .= "\$pageTitle = {$titleExport};\n";
    if ($inlineScripts !== '') {
        // Use heredoc so inline JS with single quotes / dollar signs is safe
        $php .= "\$inlineScripts = <<<'INLINE_SCRIPTS'\n{$inlineScripts}\nINLINE_SCRIPTS;\n";
    }
    $php .= "ob_start();\n";
    $php .= "?>\n";
    $php .= $content;
    $php .= "\n<?php\n\$content = ob_get_clean();\n";
    $php .= "require __DIR__ . '/../layouts/frontend.php';\n";

    return $php;
}

/**
 * Extract inline <script>…</script> blocks (not external src= includes)
 * from HTML content. Removes them from $content (by reference) and returns
 * the concatenated script blocks.
 */
function extractInlineScripts(string &$content): string
{
    $scripts = '';
    // Match <script> tags WITHOUT a src attribute (inline code blocks)
    if (preg_match_all('#<script(?![^>]*\bsrc\b)[^>]*>.*?</script>#si', $content, $matches)) {
        foreach ($matches[0] as $block) {
            $scripts .= $block . "\n";
            $content = str_replace($block, '', $content);
        }
    }
    return trim($scripts);
}

// ══════════════════════════════════════════════════════════════
//  Category C: Content-only pages in general/
// ══════════════════════════════════════════════════════════════
echo "=== Category C: Content-only pages (general/) ===\n";

$categoryC = [
    'general/index.php',
    'general/about.php',
    'general/404.php',
    'general/blog.php',
    'general/blog-article.php',
    'general/blog-list-left-sidebar.php',
    'general/blog-list-right-sidebar.php',
    'general/contact.php',
    'general/cookies-policy.php',
    'general/faq.php',
    'general/privacy-policy.php',
    'general/terms-condition.php',
];

foreach ($categoryC as $relFile) {
    $filePath = $frontPath . '/' . $relFile;
    if (!file_exists($filePath)) {
        echo "  SKIP: {$relFile} (not found)\n";
        $results['skip']++;
        continue;
    }

    $content = file_get_contents($filePath);
    $content = fixAssetPaths($content);
    $title   = $pageTitles[$relFile] ?? 'AfiaZone';

    file_put_contents($filePath, wrapWithLayout($content, $title));
    echo "  OK: {$relFile}\n";
    $results['ok']++;
}

// ══════════════════════════════════════════════════════════════
//  Category B: Full HTML pages → extract content, wrap
// ══════════════════════════════════════════════════════════════
echo "\n=== Category B: Full HTML pages ===\n";

$categoryB = [
    'auth/login.php',
    'auth/register.php',
    'catalog/compare-products.php',
    'catalog/product-details.php',
    'catalog/product-details-affiliate.php',
    'catalog/product-details-group.php',
    'catalog/product-details-right-sidebar.php',
    'catalog/product-details-variable.php',
    'catalog/product-details-variant-1.php',
    'catalog/product-details-variant-2.php',
    'catalog/products-list-grid.php',
    'catalog/products-list-sidebar.php',
    'catalog/products-list-top-filter.php',
    'delivery/tracking.php',
    'shopping/cart.php',
    'shopping/checkout.php',
    'shopping/wishlist.php',
    'user/profile-edit.php',
];

foreach ($categoryB as $relFile) {
    $filePath = $frontPath . '/' . $relFile;
    if (!file_exists($filePath)) {
        echo "  SKIP: {$relFile} (not found)\n";
        $results['skip']++;
        continue;
    }

    $raw       = file_get_contents($filePath);
    $lines     = explode("\n", $raw);
    $lineCount = count($lines);

    // ── Find header end (last "<!-- rts header area end -->") ──
    $headerEnd = -1;
    for ($i = 0; $i < $lineCount; $i++) {
        if (strpos($lines[$i], '<!-- rts header area end -->') !== false) {
            $headerEnd = $i;
        }
    }
    if ($headerEnd === -1) {
        echo "  WARN: {$relFile} — no header-end marker, skipping\n";
        $results['warn']++;
        continue;
    }

    // ── Find footer start ──
    $footerStart = $lineCount;
    for ($i = $headerEnd + 1; $i < $lineCount; $i++) {
        // Prefer the comment marker
        if (strpos($lines[$i], '<!-- rts footer one area start -->') !== false) {
            $footerStart = $i;
            break;
        }
        // Fallback: the footer div itself
        if (strpos($lines[$i], 'class="rts-footer-area') !== false
            && strpos($lines[$i], '<div') !== false) {
            $footerStart = $i;
            break;
        }
    }

    // ── Extract unique content between markers ──
    $contentLines = array_slice($lines, $headerEnd + 1, $footerStart - $headerEnd - 1);
    $pageContent  = trim(implode("\n", $contentLines));

    // ── Extract inline scripts (login/register AJAX etc.) ──
    $inlineScripts = extractInlineScripts($pageContent);

    // ── Fix asset paths ──
    $pageContent = fixAssetPaths($pageContent);

    $title = $pageTitles[$relFile] ?? 'AfiaZone';
    file_put_contents($filePath, wrapWithLayout($pageContent, $title, $inlineScripts));

    $cnt = count($contentLines);
    echo "  OK: {$relFile} (headerEnd:L{$headerEnd} footerStart:L{$footerStart} content:{$cnt}L";
    if ($inlineScripts !== '') {
        echo " +inlineScripts";
    }
    echo ")\n";
    $results['ok']++;
}

// ══════════════════════════════════════════════════════════════
//  order-invoice.php — standalone, just fix asset paths
// ══════════════════════════════════════════════════════════════
echo "\n=== Standalone: order-invoice.php (paths only) ===\n";
$invoicePath = $frontPath . '/shopping/order-invoice.php';
if (file_exists($invoicePath)) {
    $content = file_get_contents($invoicePath);
    $content = fixAssetPaths($content);
    file_put_contents($invoicePath, $content);
    echo "  OK: shopping/order-invoice.php\n";
    $results['ok']++;
}

// ══════════════════════════════════════════════════════════════
echo "\n=== Summary ===\n";
echo "OK: {$results['ok']} | Skip: {$results['skip']} | Warn: {$results['warn']}\n";
echo "Done!\n";
