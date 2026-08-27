<main class="auth-page">
    <section class="auth-card">
        <a class="auth-brand" href="<?= e(url()) ?>"><span class="logo-box">A</span><strong>AfiaZone</strong></a>
        <div class="auth-heading">
            <span>Votre espace santé</span>
            <h1>Bon retour parmi nous</h1>
            <p>Connectez-vous pour suivre vos commandes et retrouver vos magasins favoris.</p>
        </div>

        <?php if ($message = \App\Core\Session::consumeFlash('auth.message')): ?>
            <div class="auth-alert success"><?= e($message) ?></div>
        <?php endif; ?>
        <?php if (! empty($errors)): ?>
            <div class="auth-alert error"><?= e((string) ($errors[array_key_first($errors)][0] ?? 'Veuillez verifier les informations.')) ?></div>
        <?php endif; ?>

        <form class="auth-form" action="<?= e(url('connexion')) ?>" method="post">
            <?= csrf_field() ?>
            <?php if (! empty($returnTo)): ?><input type="hidden" name="return_to" value="<?= e($returnTo) ?>"><?php endif; ?>
            <label for="identifier">Email ou telephone</label>
            <input id="identifier" name="identifier" type="text" value="<?= e($old['identifier'] ?? '') ?>" autocomplete="username" required>
            <label for="password">Mot de passe</label>
            <input id="password" name="password" type="password" autocomplete="current-password" required>
            <button class="auth-submit" type="submit">Se connecter</button>
        </form>

        <div class="auth-links"><a href="<?= e(url('mot-de-passe-oublie')) ?>">Mot de passe oublie ?</a></div>
        <p class="auth-switch">Pas encore de compte ? <a href="<?= e(url('inscription')) ?>">Creer un compte</a></p>
    </section>
</main>