<main class="auth-page">
    <section class="auth-card">
        <a class="auth-brand" href="<?= e(url()) ?>"><span class="logo-box">A</span><strong>AfiaZone</strong></a>
        <div class="auth-heading"><span>Securite du compte</span><h1>Choisir un nouveau mot de passe</h1><p>Utilisez au moins huit caracteres pour proteger votre compte.</p></div>
        <?php if (! empty($errors)): ?><div class="auth-alert error"><?= e((string) ($errors[array_key_first($errors)][0] ?? 'Le lien est invalide.')) ?></div><?php endif; ?>
        <form class="auth-form" action="<?= e(url('reset-password/' . $token)) ?>" method="post">
            <?= csrf_field() ?>
            <label for="password">Nouveau mot de passe</label>
            <input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required>
            <label for="password_confirmation">Confirmation</label>
            <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required>
            <button class="auth-submit" type="submit">Modifier le mot de passe</button>
        </form>
    </section>
</main>