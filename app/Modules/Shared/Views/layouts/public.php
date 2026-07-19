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
        <div class="top-strip">
            <span>Marketplace sante pilote</span>
            <span>Livraison et retrait en pharmacie</span>
            <a href="<?= e(url('health-check')) ?>">Verifier le systeme</a>
        </div>

        <header class="site-header" aria-label="En-tete AfiaZone">
            <div class="header-main">
                <a class="brand" href="<?= e(url()) ?>" aria-label="AfiaZone accueil">
                    <span class="brand-mark">A</span>
                    <span>
                        <strong>AfiaZone</strong>
                        <small>Produits de sante</small>
                    </span>
                </a>

                <form class="search-box" action="<?= e(url('recherche')) ?>" method="get">
                    <label class="sr-only" for="site-search">Rechercher un produit</label>
                    <select name="category" aria-label="Categorie">
                        <option value="">Toutes categories</option>
                        <option value="medicaments">Medicaments</option>
                        <option value="diagnostic">Diagnostic</option>
                        <option value="protection">Protection</option>
                    </select>
                    <input id="site-search" name="q" type="search" placeholder="Rechercher medicament, test, dispositif...">
                    <button type="submit">Rechercher</button>
                </form>

                <div class="header-actions" aria-label="Actions rapides">
                    <a href="<?= e(url('panier')) ?>">Panier</a>
                    <a href="<?= e(url('connexion')) ?>">Compte</a>
                </div>
            </div>

            <nav class="site-nav" aria-label="Navigation principale">
                <a href="<?= e(url()) ?>">Accueil</a>
                <a href="<?= e(url('catalogue')) ?>">Catalogue</a>
                <a href="<?= e(url('pharmacies')) ?>">Pharmacies proches</a>
                <a href="<?= e(url('ordonnances')) ?>">Ordonnances</a>
                <a href="<?= e(url('marchands')) ?>">Vendre sur AfiaZone</a>
                <a href="<?= e(url('admin')) ?>">Admin</a>
            </nav>
        </header>

        <main class="main-content">
            <?= $content ?>
        </main>

        <footer class="site-footer">
            <strong>AfiaZone</strong>
            <span>Socle MVC actif. Les modules catalogue, auth et stock arrivent ensuite.</span>
        </footer>
    </div>
</body>
</html>