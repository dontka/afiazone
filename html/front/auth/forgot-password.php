<?php
$pageTitle = 'Mot de passe oublié - AfiaZone';
$inlineScripts = <<<'INLINE_SCRIPTS'
<script>
document.getElementById('forgotPasswordForm').addEventListener('submit', function(e) {
    e.preventDefault();
    var errorDiv = document.getElementById('forgot-error');
    var successDiv = document.getElementById('forgot-success');
    errorDiv.style.display = 'none';
    successDiv.style.display = 'none';

    fetch('/auth/forgot-password', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
        body: JSON.stringify({
            email: document.getElementById('email').value
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(res) {
        if (res.success) {
            successDiv.textContent = res.data && res.data.message
                ? res.data.message
                : 'Si l\'email existe, un lien de réinitialisation a été envoyé.';
            successDiv.style.display = 'block';
        } else {
            errorDiv.textContent = res.message || 'Erreur lors de l\'envoi du lien de réinitialisation.';
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
                    <a class="current" href="/auth/forgot-password">Forgot Password</a>
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
                    <h3 class="title">Forgot Password? 🔒</h3>
                    <p>Enter your email and we'll send you instructions to reset your password.</p>

                    <div id="forgot-error" class="alert alert-danger" style="display:none;"></div>
                    <div id="forgot-success" class="alert alert-success" style="display:none;"></div>

                    <form id="forgotPasswordForm" class="registration-form">
                        <div class="input-wrapper">
                            <label for="email">Email*</label>
                            <input type="email" id="email" name="email" required>
                        </div>
                        <button type="submit" class="rts-btn btn-primary">Send Reset Link</button>
                        <div class="another-way-to-registration">
                            <p>Remember your password? <a href="/auth/login">Login</a></p>
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
