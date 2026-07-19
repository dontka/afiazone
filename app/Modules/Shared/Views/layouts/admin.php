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
        <div class="top-strip admin-strip">
            <span>Administration</span>
            <span>Socle MVC actif</span>
            <a href="<?= e(url()) ?>">Voir le site</a>
        </div>

        <header class="site-header admin-header">
            <div class="header-main">
                <a class="brand" href="<?= e(url('admin')) ?>">
                    <span class="brand-mark">A</span>
                    <span>
                        <strong>AfiaZone Admin</strong>
                        <small>Operations marketplace</small>
                    </span>
                </a>
                <nav class="site-nav compact-nav" aria-label="Navigation administration">
                    <a href="<?= e(url('admin')) ?>">Dashboard</a>
                    <a href="<?= e(url()) ?>">Site public</a>
                    <a href="<?= e(url('health-check')) ?>">Health</a>
                </nav>
            </div>
        </header>

        <main class="main-content">
            <?= $content ?>
        </main>
    </div>
</body>
</html>