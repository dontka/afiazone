<?php
/**
 * One-time script to fix front-end asset paths.
 * Replaces /assets/ → /html/front/assets/ in front-end template files.
 */

$files = [
    __DIR__ . '/../html/front/auth/login.php',
    __DIR__ . '/../html/front/auth/register.php',
];

foreach ($files as $file) {
    if (!is_file($file)) {
        echo "SKIP: {$file} not found\n";
        continue;
    }

    $content = file_get_contents($file);
    $original = $content;

    // Replace "/assets/ and '/assets/ (absolute paths in HTML attributes)
    $content = str_replace('"/assets/', '"/html/front/assets/', $content);
    $content = str_replace("'/assets/", "'/html/front/assets/", $content);

    // Fix any double-replacement: /html/front/html/front/assets/ → /html/front/assets/
    $content = str_replace('/html/front/html/front/assets/', '/html/front/assets/', $content);

    if ($content !== $original) {
        file_put_contents($file, $content);
        echo "UPDATED: {$file}\n";
    } else {
        echo "NO CHANGE: {$file}\n";
    }
}

echo "Done.\n";
