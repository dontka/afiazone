<main class="auth-page">
    <section class="auth-card">
        <a class="auth-brand" href="<?= e(url()) ?>"><span class="logo-box">A</span><strong>AfiaZone</strong></a>
        <div class="auth-heading">
            <span>Derniere etape</span>
            <h1>Verifiez votre adresse email</h1>
            <p>Un lien de confirmation a ete prepare pour <?= e($email ?? 'votre adresse email') ?>. Votre compte sera active apres verification.</p>
        </div>
        <div class="auth-alert success">Consultez votre boite de reception et vos courriers indesirables.</div>
        <?php if ($message = \App\Core\Session::consumeFlash('auth.message')): ?><div class="auth-alert success"><?= e($message) ?></div><?php endif; ?>
        <form class="auth-form" action="<?= e(url('verification-email/resend')) ?>" method="post">
            <?= csrf_field() ?>
            <label for="email">Renvoyer vers cette adresse</label>
            <input id="email" name="email" type="email" value="<?= e($email ?? '') ?>" required>
            <button class="auth-submit" type="submit">Renvoyer le lien</button>
        </form>
        <p class="auth-switch"><a href="<?= e(url('connexion')) ?>">Retour a la connexion</a></p>
    </section>
</main>