<main class="catalog-page">
    <div class="catalog-shell">
        <div class="catalog-heading">
            <div><span>AfiaZone catalogue</span><h1>Produits de sante fiables</h1><p>Explorez les produits publies et les informations essentielles avant votre achat.</p></div>
            <a class="catalog-cart" href="<?= e(url('panier')) ?>">Voir le panier</a>
        </div>
        <form class="catalog-filter" action="<?= e(url('catalogue')) ?>" method="get">
            <input type="search" name="q" value="<?= e($search) ?>" placeholder="Rechercher un produit...">
            <select name="category"><option value="">Toutes les categories</option><?php foreach ($categories as $category): ?><option value="<?= e($category['slug']) ?>" <?= $selectedCategory === $category['slug'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select>
            <button type="submit">Filtrer</button>
        </form>
        <div class="catalog-layout">
            <aside class="catalog-categories"><strong>Categories</strong><?php foreach ($categories as $category): ?><a class="<?= $selectedCategory === $category['slug'] ? 'active' : '' ?>" href="<?= e(url('categorie/' . $category['slug'])) ?>"><?= e($category['name']) ?></a><?php endforeach; ?></aside>
            <section><div class="catalog-result-head"><strong><?= count($products) ?> produit(s)</strong><span>Disponibles dans le catalogue AfiaZone</span></div><div class="catalog-product-grid"><?php foreach ($products as $product): ?><article class="catalog-product-card"><div class="catalog-product-visual"><span><?= e(substr($product['name'], 0, 1)) ?></span></div><?php if ((int) $product['requires_prescription'] === 1): ?><b class="prescription-badge">Ordonnance</b><?php endif; ?><h2><?= e($product['name']) ?></h2><p><?= e($product['short_description'] ?? 'Produit de sante selectionne') ?></p><small><?= e($product['category_name']) ?><?= $product['brand_name'] ? ' · ' . e($product['brand_name']) : '' ?></small><a class="catalog-product-link" href="<?= e(url('produit/' . $product['slug'])) ?>">Voir le produit</a></article><?php endforeach; ?></div><?php if ($products === []): ?><div class="catalog-empty">Aucun produit ne correspond a votre recherche.</div><?php endif; ?></section>
        </div>
    </div>
</main>