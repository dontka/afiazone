<div class="admin-catalog-page">
    <header class="admin-page-heading">
        <span>Back-office</span>
        <h1>Catalogue medical</h1>
        <p>Gerez les donnees canoniques visibles dans le catalogue public.</p>
    </header>

    <?php if ($message): ?><div class="catalog-admin-message"><?= e($message) ?></div><?php endif; ?>
    <?php if (! empty($errors)): ?><div class="catalog-admin-error"><?= e((string) ($errors[array_key_first($errors)][0] ?? 'Une erreur est survenue.')) ?></div><?php endif; ?>

    <div class="catalog-admin-grid">
        <form class="catalog-admin-form" action="<?= e(url('admin/categories')) ?>" method="post">
            <?= csrf_field() ?>
            <h2>Nouvelle categorie</h2>
            <label for="category-name">Nom</label>
            <input id="category-name" name="name" required>
            <label for="category-parent">Categorie parente</label>
            <select id="category-parent" name="parent_id">
                <option value="">Categorie racine</option>
                <?php foreach ($categories as $category): ?>
                    <option value="<?= e($category['id']) ?>"><?= e($category['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <label for="category-description">Description</label>
            <textarea id="category-description" name="description"></textarea>
            <label for="category-status">Statut</label>
            <select id="category-status" name="status"><option value="draft">Brouillon</option><option value="published">Publiee</option><option value="archived">Archivee</option></select>
            <button type="submit">Creer la categorie</button>
        </form>

        <form class="catalog-admin-form" action="<?= e(url('admin/produits')) ?>" method="post">
            <?= csrf_field() ?>
            <h2>Nouveau produit</h2>
            <label for="product-name">Nom</label>
            <input id="product-name" name="name" required>
            <label for="product-category">Categorie</label>
            <select id="product-category" name="category_slug" required><?php foreach ($categories as $category): ?><option value="<?= e($category['slug']) ?>"><?= e($category['name']) ?></option><?php endforeach; ?></select>
            <label for="product-brand">Marque</label>
            <select id="product-brand" name="brand_id"><option value="">Sans marque</option><?php foreach ($brands as $brand): ?><option value="<?= e($brand['id']) ?>"><?= e($brand['name']) ?></option><?php endforeach; ?></select>
            <label for="product-short-description">Description courte</label>
            <input id="product-short-description" name="short_description">
            <label for="product-description">Description</label>
            <textarea id="product-description" name="description"></textarea>
            <label>Principes actifs</label>
            <select name="ingredient_ids[]" multiple><?php foreach ($ingredients as $ingredient): ?><option value="<?= e($ingredient['id']) ?>"><?= e($ingredient['name']) ?></option><?php endforeach; ?></select>
            <label class="checkbox-label"><input type="checkbox" name="requires_prescription" value="1"> Necessite une ordonnance</label>
            <label for="product-status">Statut initial</label>
            <select id="product-status" name="status"><option value="pending_review">En attente de moderation</option><option value="draft">Brouillon</option><option value="published">Publie</option></select>
            <button type="submit">Enregistrer le produit</button>
        </form>
    </div>

    <section class="catalog-admin-list">
        <h2>Categories</h2>
        <?php foreach ($categories as $category): ?>
            <form method="post" action="<?= e(url('admin/categories/' . $category['id'])) ?>">
                <?= csrf_field() ?><input type="hidden" name="_method" value="PATCH">
                <strong><?= e($category['name']) ?></strong>
                <span><?= e($category['status']) ?> · <?= e($category['product_count']) ?> produit(s)</span>
                <input type="hidden" name="name" value="<?= e($category['name']) ?>">
                <input type="hidden" name="description" value="<?= e($category['description'] ?? '') ?>">
                <input type="hidden" name="parent_id" value="<?= e($category['parent_id'] ?? '') ?>">
                <input type="hidden" name="status" value="<?= e($category['status']) ?>">
                <button type="submit">Enregistrer</button>
            </form>
        <?php endforeach; ?>
    </section>

    <section class="catalog-admin-list">
        <h2>Produits</h2>
        <?php foreach ($products as $product): ?>
            <div>
                <strong><?= e($product['name']) ?></strong>
                <span><?= e($product['category_name']) ?> · <?= e($product['status']) ?></span>
                <a href="<?= e(url('admin/produits/' . $product['id'] . '/modifier')) ?>">Modifier</a>
                <form method="post" action="<?= e(url('admin/produits/' . $product['id'] . '/statut')) ?>">
                    <?= csrf_field() ?><input type="hidden" name="_method" value="PATCH">
                    <select name="status"><option value="draft">Brouillon</option><option value="pending_review">En attente</option><option value="published">Publie</option><option value="archived">Archive</option></select>
                    <button type="submit">Changer le statut</button>
                </form>
                <form method="post" action="<?= e(url('admin/produits/' . $product['id'])) ?>">
                    <?= csrf_field() ?><input type="hidden" name="_method" value="DELETE"><button type="submit">Supprimer</button>
                </form>
            </div>
        <?php endforeach; ?>
    </section>
</div>
