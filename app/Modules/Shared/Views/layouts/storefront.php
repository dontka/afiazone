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
        <?= $content ?>
    </div>
</body>
</html>
