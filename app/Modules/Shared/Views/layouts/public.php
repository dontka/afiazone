<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'AfiaZone') ?></title>
    <link rel="stylesheet" href="<?= e(asset('app.css')) ?>">
</head>
<body>
    <div class="site-shell">
        <?php if (($layoutChrome ?? true) === true): ?>
            <?php require BASE_PATH . '/app/Modules/Shared/Views/partials/public-header.php'; ?>
        <?php endif; ?>
        <?= $content ?>
        <?php if (($layoutChrome ?? true) === true): ?>
            <?php require BASE_PATH . '/app/Modules/Shared/Views/partials/public-footer.php'; ?>
        <?php endif; ?>
    </div>
</body>
</html>