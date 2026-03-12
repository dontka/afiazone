<?php
$file = __DIR__ . '/../html/front/layouts/header.php';
$content = file_get_contents($file);

// 1. Fix tel: link — remove $appUrl prefix
$content = str_replace(
    'href="<?php echo $appUrl; ?>tel:',
    'href="tel:',
    $content
);

// 2. Replace href="$appUrl#" with just href="#"
$content = str_replace(
    'href="<?php echo $appUrl; ?>#"',
    'href="#"',
    $content
);

// 3. Replace href="$appUrl PAGENAME" with href="/PAGENAME" for nav links
$content = preg_replace(
    '/href="<\?php echo \$appUrl; \?>([a-z][a-z0-9\-]*)"/',
    'href="/$1"',
    $content
);

// 4. Fix bare relative links in submenus: href="about" → href="/about"
// But NOT href="#" or href="tel:" or already-absolute href="/..."
$content = preg_replace(
    '/href="([a-z][a-z0-9\-]+)"/',
    'href="/$1"',
    $content
);

file_put_contents($file, $content);

echo "Done. Checking counts...\n";
echo "Remaining \$appUrl in href: " . preg_match_all('/href="<\?php/', $content) . "\n";
echo "Remaining bare relative: " . preg_match_all('/href="[a-z][a-z0-9\-]+"/', $content) . "\n";
echo "tel: links: " . substr_count($content, 'href="tel:') . "\n";
echo "Anchor links: " . substr_count($content, 'href="#"') . "\n";
echo "Absolute nav links: " . preg_match_all('/href="\/[a-z]/', $content) . "\n";
echo "Asset src with \$appUrl: " . preg_match_all('/src="<\?php/', $content) . "\n";
