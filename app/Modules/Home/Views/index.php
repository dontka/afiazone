<div class="marketplace-page">
    <div class="marketplace-container">
        <header class="top-toolbar">
            <div class="toolbar-left">
                <span>📍 Livraison locale</span>
                <span>🚚 Retrait en 24h</span>
            </div>
            <div class="toolbar-right">
                <span>Support</span>
                <span>Connexion</span>
            </div>
        </header>

        <section class="main-header">
            <div class="brand-lockup">
                <div class="logo-box">A</div>
                <div>
                    <strong>AfiaZone</strong>
                    <small>Hyperlocal & santé</small>
                </div>
            </div>

            <nav class="main-nav" aria-label="Navigation principale">
                <a href="<?= e(url()) ?>">Accueil</a>
                <a href="<?= e(url('catalogue')) ?>">Catalogue</a>
                <a href="<?= e(url('pharmacies')) ?>">Pharmacies</a>
                <a href="<?= e(url('ordonnances')) ?>">Ordonnances</a>
                <a href="<?= e(url('marchands')) ?>">Vendre</a>
            </nav>

            <div class="header-tools">
                <button class="icon-button" type="button">♡</button>
                <button class="icon-button" type="button">🛒</button>
                <a class="account-button" href="<?= e(url('connexion')) ?>">Compte</a>
            </div>
        </section>

        <section class="search-bar-wrap">
            <form class="search-form" action="<?= e(url('recherche')) ?>" method="get">
                <select name="category" aria-label="Catégorie">
                    <option value="">Catégorie</option>
                    <option value="medicaments">Médicaments</option>
                    <option value="diagnostic">Diagnostic</option>
                    <option value="soins">Soins</option>
                </select>
                <input type="search" name="q" placeholder="Cherchez un produit, un service ou une pharmacie...">
                <button type="submit">Rechercher</button>
            </form>
        </section>

        <section class="promo-grid">
            <article class="promo-card promo-card-green">
                <span class="promo-tag">AfiaZone deals</span>
                <h1>Vos essentiels santé, au meilleur prix.</h1>
                <p>Des produits fiables livrés rapidement près de chez vous.</p>
                <a href="<?= e(url('catalogue')) ?>" class="cta-primary">Acheter maintenant</a>
                <span class="promo-art">+</span>
            </article>
            <article class="promo-card promo-card-blue">
                <span class="promo-tag">Service local</span>
                <h2>Livraison rapide et sécurisée</h2>
                <p>Commandez auprès de vendeurs vérifiés dans votre quartier.</p>
                <a href="<?= e(url('pharmacies')) ?>" class="cta-secondary">Voir les magasins</a>
                <span class="delivery-art">24<span>h</span></span>
            </article>
        </section>

        <section class="category-section">
            <div class="section-title-row">
                <div>
                    <span>Nos catégories</span>
                    <h2>Parcourir par besoin</h2>
                </div>
                <a href="<?= e(url('catalogue')) ?>">Voir tout</a>
            </div>

            <div class="category-grid">
                <?php foreach ($categories as $category): ?>
                    <a class="category-item <?= e($category['tone']) ?>" href="<?= e(url('catalogue')) ?>">
                        <span class="category-badge"><?= e($category['icon']) ?></span>
                        <strong><?= e($category['name']) ?></strong>
                        <small><?= e($category['count']) ?></small>
                    </a>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="stores-section">
            <div class="section-title-row">
                <div>
                    <span>Près de vous</span>
                    <h2>Parcourir les magasins</h2>
                </div>
                <a href="<?= e(url('pharmacies')) ?>">Voir tout</a>
            </div>

            <div class="store-grid">
                <?php foreach ($stores as $store): ?>
                    <article class="store-card">
                        <div class="store-image <?= e($store['tone']) ?>"><span>▦</span></div>
                        <div class="store-card-body">
                            <div class="store-rating">★ 4.9 <small>Ouvert</small></div>
                            <h3><?= e($store['name']) ?></h3>
                            <p>⌖ <?= e($store['location']) ?></p>
                            <span class="store-distance"><?= e($store['distance']) ?></span>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="brands-section">
            <div class="section-title-row narrow">
                <div>
                    <span>Nos partenaires</span>
                    <h2>Marques de confiance</h2>
                </div>
            </div>

            <div class="brands-row">
                <?php foreach ($brands as $brand): ?>
                    <div class="brand-pill"><?= e($brand) ?></div>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="products-section">
            <div class="section-title-row">
                <div>
                    <span>Produits populaires</span>
                    <h2>Les plus recherchés</h2>
                </div>
                <a href="<?= e(url('catalogue')) ?>">Tout afficher</a>
            </div>

            <div class="product-grid">
                <?php foreach ($featuredProducts as $product): ?>
                    <article class="product-card">
                        <div class="product-thumb <?= e($product['tone']) ?>">
                            <span><?= e(substr($product['name'], 0, 1)) ?></span>
                        </div>
                        <span class="buyer-tag"><?= e($product['tag']) ?></span>
                        <h3><?= e($product['name']) ?></h3>
                        <p><?= e($product['meta']) ?></p>
                        <div class="card-footer">
                            <strong><?= e($product['price']) ?> <small><?= e($product['unit']) ?></small></strong>
                            <a href="<?= e(url('panier')) ?>">Ajouter</a>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        </section>

        <?php foreach ($collections as $collection): ?>
            <section class="collection-section">
                <div class="section-title-row">
                    <div>
                        <span><?= e($collection['subtitle']) ?></span>
                        <h2><?= e($collection['title']) ?></h2>
                    </div>
                    <a href="<?= e(url('catalogue')) ?>">Explorer</a>
                </div>

                <div class="mini-grid">
                    <?php foreach ($collection['items'] as $item): ?>
                        <article class="mini-card-item">
                            <div class="mini-thumb <?= e($item['tone']) ?>">
                                <span><?= e(substr($item['name'], 0, 1)) ?></span>
                            </div>
                            <span class="mini-tag"><?= e($item['tag']) ?></span>
                            <h3><?= e($item['name']) ?></h3>
                            <div class="mini-footer">
                                <strong><?= e($item['price']) ?> <small>CDF</small></strong>
                                <a href="<?= e(url('panier')) ?>">+</a>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>

        <section class="benefits-grid">
            <?php foreach ($benefits as $benefit): ?>
                <div class="benefit-item">
                    <span class="check">✓</span>
                    <div>
                        <h3><?= e($benefit['title']) ?></h3>
                        <p><?= e($benefit['text']) ?></p>
                    </div>
                </div>
            <?php endforeach; ?>
        </section>

        <section class="metrics-strip">
            <?php foreach ($stats as $stat): ?>
                <div class="metric-box">
                    <strong><?= e($stat['value']) ?></strong>
                    <span><?= e($stat['label']) ?></span>
                </div>
            <?php endforeach; ?>
        </section>
    </div>
</div>