<main class="auth-page">
    <section class="auth-card">
        <a class="auth-brand" href="<?= e(url()) ?>"><span class="logo-box">A</span><strong>AfiaZone</strong></a>
        <div class="auth-heading"><span>Acces au compte</span><h1>Reinitialiser le mot de passe</h1><p>Indiquez votre email ou telephone pour recevoir les instructions.</p></div>
        <?php if ($message = \App\Core\Session::consumeFlash('auth.message')): ?><div class="auth-alert success"><?= e($message) ?></div><?php endif; ?>
        <?php if (! empty($errors)): ?><div class="auth-alert error"><?= e((string) ($errors[array_key_first($errors)][0] ?? 'Veuillez verifier les informations.')) ?></div><?php endif; ?>
        <form class="auth-form" action="<?= e(url('mot-de-passe-oublie')) ?>" method="post">
            <?= csrf_field() ?>
            <label for="identifier">Email ou telephone</label>
            <input id="identifier" name="identifier" type="text" autocomplete="username" required>
            <button class="auth-submit" type="submit">Envoyer les instructions</button>
        </form>
        <p class="auth-switch"><a href="<?= e(url('connexion')) ?>">Retour a la connexion</a></p>
    </section>
</main>