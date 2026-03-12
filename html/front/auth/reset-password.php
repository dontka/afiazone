<?php
$pageTitle = 'Réinitialiser le mot de passe - AfiaZone';
$inlineScripts = <<<'INLINE_SCRIPTS'
<script>
(function() {
    var params = new URLSearchParams(window.location.search);
    var token = params.get('token') || '';
    var tokenInput = document.getElementById('token');
    if (tokenInput) {
        tokenInput.value = token;
    }
})();

document.getElementById('resetPasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var errorDiv = document.getElementById('reset-error');
    var successDiv = document.getElementById('reset-success');
    errorDiv.style.display = 'none';
    successDiv.style.display = 'none';

    fetch('/auth/reset-password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({
            token: document.getElementById('token').value,
            password: document.getElementById('password').value
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            successDiv.textContent = 'Mot de passe réinitialisé. Redirection vers la connexion...';
            successDiv.style.display = 'block';
            setTimeout(function() { window.location.href = '/auth/login'; }, 1200);
        } else {
            errorDiv.textContent = res.message || 'Lien de réinitialisation invalide ou expiré.';
            errorDiv.style.display = 'block';
        }
    })
    .catch(function() {
        errorDiv.textContent = 'Erreur réseau. Veuillez réessayer.';
        errorDiv.style.display = 'block';
    });
});
</script>
INLINE_SCRIPTS;
ob_start();
?>
<div class="rts-navigation-area-breadcrumb bg_light-1">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="navigator-breadcrumb-wrapper">
                    <a href="/">Home</a>
                    <i class="fa-regular fa-chevron-right"></i>
                    <a class="current" href="/auth/reset-password">Reset Password</a>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="section-seperator bg_light-1">
    <div class="container">
        <hr class="section-seperator">
    </div>
</div>

<div class="rts-register-area rts-section-gap bg_light-1">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="registration-wrapper-1">
                    <div class="logo-area mb--0">
                        <img class="mb--10" src="/html/front/assets/images/logo/fav.png" alt="logo">
                    </div>
                    <h3 class="title">Reset Your Password</h3>

                    <div id="reset-error" class="alert alert-danger" style="display:none;"></div>
                    <div id="reset-success" class="alert alert-success" style="display:none;"></div>

                    <form id="resetPasswordForm" class="registration-form">
                        <input type="hidden" id="token" name="token" value="">
                        <div class="input-wrapper">
                            <label for="password">New Password*</label>
                            <input type="password" id="password" name="password" minlength="8" required>
                        </div>
                        <button type="submit" class="rts-btn btn-primary">Reset Password</button>
                        <div class="another-way-to-registration">
                            <p>Back to <a href="/auth/login">Login</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/frontend.php';
