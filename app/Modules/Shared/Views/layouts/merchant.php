<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Espace marchand | AfiaZone') ?></title>
    <link rel="stylesheet" href="<?= e(asset('app.css')) ?>">
</head>
<body>
    <div class="site-shell">
        <?php require BASE_PATH . '/app/Modules/Shared/Views/partials/public-header.php'; ?>
        <?php require BASE_PATH . '/app/Modules/Shared/Views/partials/account-nav.php'; ?>
        <main class="layout-content">
            <?= $content ?>
        </main>
        <?php require BASE_PATH . '/app/Modules/Shared/Views/partials/public-footer.php'; ?>
    </div>
</body>
</html>