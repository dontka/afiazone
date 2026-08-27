<main class="account-page">
    <section class="account-card">
        <div class="account-topline">
            <a class="auth-brand" href="<?= e(url()) ?>"><span class="logo-box">A</span><strong>AfiaZone</strong></a>
            <form action="<?= e(url('deconnexion')) ?>" method="post">
                <?= csrf_field() ?>
                <button class="account-logout" type="submit">Se deconnecter</button>
            </form>
        </div>
        <span class="account-kicker"><?= $merchant ? 'Espace marchand' : 'Espace client' ?></span>
        <h1>Bonjour <?= e($user['full_name'] ?? 'utilisateur') ?></h1>
        <p class="account-intro"><?= $merchant ? 'Votre espace professionnel est pret. La gestion du catalogue et du stock sera disponible dans les prochains modules.' : 'Retrouvez ici votre profil, vos commandes et vos pharmacies favorites.' ?></p>
        <div class="account-grid">
            <article><strong><?= e($user['email'] ?? '-') ?></strong><span>Email du compte</span></article>
            <article><strong><?= e($user['phone'] ?? 'Non renseigne') ?></strong><span>Telephone</span></article>
            <article><strong>Actif</strong><span>Statut du compte</span></article>
        </div>
        <div class="account-actions">
            <a href="<?= e(url()) ?>">Retour a l’accueil</a>
            <a href="<?= e(url('catalogue')) ?>">Explorer le catalogue</a>
        </div>
    </section>
</main>