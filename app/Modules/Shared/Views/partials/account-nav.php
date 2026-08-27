<nav class="account-nav" aria-label="Navigation du compte">
    <a href="<?= e(url('compte')) ?>">Mon compte</a>
    <a href="<?= e(url('catalogue')) ?>">Catalogue</a>
    <form action="<?= e(url('deconnexion')) ?>" method="post">
        <?= csrf_field() ?>
        <button type="submit">Se déconnecter</button>
    </form>
</nav>