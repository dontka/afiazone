<main class="auth-page">
    <section class="auth-card auth-card-wide">
        <a class="auth-brand" href="<?= e(url()) ?>"><span class="logo-box">A</span><strong>AfiaZone</strong></a>
        <div class="auth-heading">
            <span><?= $merchant ? 'Espace professionnel' : 'Rejoindre AfiaZone' ?></span>
            <h1><?= $merchant ? 'Devenez vendeur verifie' : 'Creer votre compte' ?></h1>
            <p><?= $merchant ? 'Presentez vos produits et servez les clients de votre quartier.' : 'Trouvez des produits de sante fiables aupres de vendeurs locaux.' ?></p>
        </div>

        <?php if (! empty($errors)): ?>
            <div class="auth-alert error"><?= e((string) ($errors[array_key_first($errors)][0] ?? 'Veuillez verifier les informations.')) ?></div>
        <?php endif; ?>

        <form class="auth-form" action="<?= e(url($merchant ? 'inscription/marchand' : 'inscription')) ?>" method="post">
            <?= csrf_field() ?>
            <?php if ($merchant): ?>
                <label for="business_name">Nom de l'entreprise</label>
                <input id="business_name" name="business_name" type="text" value="<?= e($old['business_name'] ?? '') ?>" required>
            <?php endif; ?>
            <label for="full_name">Nom complet</label>
            <input id="full_name" name="full_name" type="text" value="<?= e($old['full_name'] ?? '') ?>" autocomplete="name" required>
            <div class="auth-two-columns">
                <div><label for="email">Email</label><input id="email" name="email" type="email" value="<?= e($old['email'] ?? '') ?>" autocomplete="email" required></div>
                <div><label for="phone">Telephone</label><input id="phone" name="phone" type="tel" value="<?= e($old['phone'] ?? '') ?>" autocomplete="tel"></div>
            </div>
            <div class="auth-two-columns">
                <div><label for="password">Mot de passe</label><input id="password" name="password" type="password" autocomplete="new-password" minlength="8" required></div>
                <div><label for="password_confirmation">Confirmation</label><input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="8" required></div>
            </div>
            <button class="auth-submit" type="submit"><?= $merchant ? 'Creer mon espace vendeur' : 'Creer mon compte' ?></button>
        </form>

        <p class="auth-switch">Deja inscrit ? <a href="<?= e(url('connexion')) ?>">Se connecter</a></p>
        <?php if (! $merchant): ?><p class="auth-switch"><a href="<?= e(url('inscription/marchand')) ?>">Je suis un professionnel de sante</a></p><?php endif; ?>
    </section>
</main>