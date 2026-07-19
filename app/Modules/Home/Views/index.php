<section class="hero marketplace-hero">
    <div class="hero-copy">
        <span class="eyebrow">Marketplace intelligente de produits de sante</span>
        <h1>Localiser, commander et recevoir les produits de sante essentiels.</h1>
        <p>
            AfiaZone connecte patients, pharmacies et structures de soins pour rendre les medicaments,
            dispositifs medicaux et equipements disponibles plus faciles a trouver et plus surs a acheter.
        </p>

        <form class="hero-search" action="<?= e(url('recherche')) ?>" method="get">
            <input name="q" type="search" placeholder="Ex: paracetamol, test paludisme, tensiometre">
            <button type="submit">Trouver</button>
        </form>

        <div class="hero-tags" aria-label="Avantages AfiaZone">
            <?php foreach ($benefits as $benefit): ?>
                <span><?= e($benefit) ?></span>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="hero-card" aria-label="Commande pilote">
        <span class="deal-label">Commande pilote</span>
        <h2>Produit sous ordonnance</h2>
        <p>Le checkout bloque la vente jusqu'a validation humaine de l'ordonnance.</p>
        <div class="hero-progress">
            <span>Panier</span>
            <span>Ordonnance</span>
            <span>Retrait</span>
        </div>
    </div>
</section>

<section class="section-block">
    <div class="section-heading">
        <span>Categories populaires</span>
        <h2>Demarrer par le besoin du patient</h2>
        <a href="<?= e(url('catalogue')) ?>">Voir tout</a>
    </div>

    <div class="category-grid">
        <?php foreach ($categories as $category): ?>
            <a class="category-card <?= e($category['tone']) ?>" href="<?= e(url('catalogue')) ?>">
                <span class="category-icon"><?= e(substr($category['name'], 0, 1)) ?></span>
                <strong><?= e($category['name']) ?></strong>
                <small><?= e($category['count']) ?></small>
            </a>
        <?php endforeach; ?>
    </div>
</section>

<section class="section-block product-section">
    <div class="section-heading">
        <span>Disponibilite locale</span>
        <h2>Offres de demonstration</h2>
        <a href="<?= e(url('health-check')) ?>">Etat du systeme</a>
    </div>

    <div class="product-grid">
        <?php foreach ($deals as $deal): ?>
            <article class="product-card">
                <div class="product-image" aria-hidden="true">
                    <span><?= e(substr($deal['name'], 0, 1)) ?></span>
                </div>
                <span class="product-badge"><?= e($deal['badge']) ?></span>
                <h3><?= e($deal['name']) ?></h3>
                <p><?= e($deal['seller']) ?></p>
                <div class="product-footer">
                    <strong><?= e($deal['price']) ?></strong>
                    <a class="button compact" href="<?= e(url('panier')) ?>">Ajouter</a>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="service-strip" aria-label="Indicateurs MVP">
    <?php foreach ($metrics as $value => $label): ?>
        <div class="service-item">
            <strong><?= e($value) ?></strong>
            <span><?= e($label) ?></span>
        </div>
    <?php endforeach; ?>
</section>