<!doctype html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Administration AfiaZone') ?></title>
    <link rel="stylesheet" href="<?= e(asset('app.css')) ?>">
</head>
<body>
    <div class="site-shell">
        <?php require BASE_PATH . '/app/Modules/Shared/Views/partials/admin-header.php'; ?>

        <main class="main-content">
            <?= $content ?>
        </main>
    </div>
</body>
</html>