<div class="admin-catalog-page">
    <a href="<?= e(url('admin/produits')) ?>">Retour aux produits</a>
    <header class="admin-page-heading"><span>Produit canonique</span><h1>Modifier <?= e($product['name']) ?></h1></header>
    <?php if ($message): ?><div class="catalog-admin-message"><?= e($message) ?></div><?php endif; ?>
    <?php if (! empty($errors)): ?><div class="catalog-admin-error"><?= e((string) ($errors[array_key_first($errors)][0] ?? 'Une erreur est survenue.')) ?></div><?php endif; ?>
    <form class="catalog-admin-form" action="<?= e(url('admin/produits/' . $product['id'] . '/modifier')) ?>" method="post">
        <?= csrf_field() ?><input type="hidden" name="_method" value="PATCH">
        <label for="product-name">Nom</label><input id="product-name" name="name" value="<?= e($product['name']) ?>" required>
        <label for="product-category">Categorie</label><select id="product-category" name="category_id" required><?php foreach ($categories as $category): ?><option value="<?= e($category['id']) ?>" <?= (int) $product['category_id'] === (int) $category['id'] ? 'selected' : '' ?>><?= e($category['name']) ?></option><?php endforeach; ?></select>
        <label for="product-brand">Marque</label><select id="product-brand" name="brand_id"><option value="">Sans marque</option><?php foreach ($brands as $brand): ?><option value="<?= e($brand['id']) ?>" <?= (int) $product['brand_id'] === (int) $brand['id'] ? 'selected' : '' ?>><?= e($brand['name']) ?></option><?php endforeach; ?></select>
        <label for="product-short-description">Description courte</label><input id="product-short-description" name="short_description" value="<?= e($product['short_description'] ?? '') ?>">
        <label for="product-description">Description</label><textarea id="product-description" name="description"><?= e($product['description'] ?? '') ?></textarea>
        <label for="product-ingredients">Principes actifs</label><select id="product-ingredients" name="ingredient_ids[]" multiple><?php foreach ($ingredients as $ingredient): ?><option value="<?= e($ingredient['id']) ?>" <?= in_array((int) $ingredient['id'], array_map('intval', array_column($product['ingredients'], 'id')), true) ? 'selected' : '' ?>><?= e($ingredient['name']) ?></option><?php endforeach; ?></select>
        <label>Variantes</label>
        <?php foreach (array_pad($product['variants'], 3, []) as $variantIndex => $variant): ?><div><input name="variants[<?= e($variantIndex) ?>][name]" value="<?= e($variant['name'] ?? '') ?>" placeholder="Nom de variante"><input name="variants[<?= e($variantIndex) ?>][sku]" value="<?= e($variant['sku'] ?? '') ?>" placeholder="SKU"></div><?php endforeach; ?>
        <label class="checkbox-label"><input type="checkbox" name="requires_prescription" value="1" <?= (int) $product['requires_prescription'] === 1 ? 'checked' : '' ?>> Necessite une ordonnance</label>
        <label for="product-status">Statut</label><select id="product-status" name="status"><option value="draft" <?= $product['status'] === 'draft' ? 'selected' : '' ?>>Brouillon</option><option value="pending_review" <?= $product['status'] === 'pending_review' ? 'selected' : '' ?>>En attente</option><option value="published" <?= $product['status'] === 'published' ? 'selected' : '' ?>>Publie</option><option value="archived" <?= $product['status'] === 'archived' ? 'selected' : '' ?>>Archive</option></select>
        <button type="submit">Enregistrer les modifications</button>
    </form>
    <section class="catalog-admin-grid">
        <form class="catalog-admin-form" action="<?= e(url('admin/produits/' . $product['id'] . '/images')) ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?><h2>Ajouter une image</h2><input type="file" name="image" accept="image/jpeg,image/png,image/webp" required><input name="alt_text" placeholder="Texte alternatif"><button type="submit">Televerser</button>
        </form>
        <form class="catalog-admin-form" action="<?= e(url('admin/produits/' . $product['id'] . '/documents')) ?>" method="post" enctype="multipart/form-data">
            <?= csrf_field() ?><h2>Ajouter un document</h2><input type="file" name="document" accept="application/pdf,image/jpeg,image/png" required><input name="document_type" value="fiche"><button type="submit">Televerser</button>
        </form>
    </section>
    <?php if (! empty($product['images'])): ?><section class="catalog-admin-list"><h2>Images</h2><?php foreach ($product['images'] as $image): ?><div><span><?= e($image['path']) ?></span><form method="post" action="<?= e(url('admin/images/' . $image['id'])) ?>"><?= csrf_field() ?><input type="hidden" name="_method" value="DELETE"><button type="submit">Supprimer</button></form></div><?php endforeach; ?></section><?php endif; ?>
    <?php if (! empty($product['documents'])): ?><section class="catalog-admin-list"><h2>Documents</h2><?php foreach ($product['documents'] as $document): ?><div><span><?= e($document['document_type']) ?> · <?= e($document['path']) ?></span><form method="post" action="<?= e(url('admin/documents/' . $document['id'])) ?>"><?= csrf_field() ?><input type="hidden" name="_method" value="DELETE"><button type="submit">Supprimer</button></form></div><?php endforeach; ?></section><?php endif; ?>
</div>
