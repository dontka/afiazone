<section class="panel">
    <h1>Page introuvable</h1>
    <p>La route demandee n'existe pas encore dans le socle AfiaZone.</p>
    <p><strong>Chemin :</strong> <?= e($path ?? '/') ?></p>
    <div class="actions">
        <a class="button" href="<?= e(url()) ?>">Retour a l'accueil</a>
    </div>
</section>