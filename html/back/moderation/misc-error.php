<?php
$pageTitle = 'Error - Pages | Vuexy - Bootstrap Admin Template';
$pageStyles = ['/assets/vendor/css/pages/page-misc.css'];
ob_start();
?>
<!-- Content -->

    <!-- Error -->
    <div class="container-xxl container-p-y">
      <div class="misc-wrapper">
        <h2 class="mb-1 mt-4">Page Not Found :(</h2>
        <p class="mb-4 mx-2">Oops! 😖 The requested URL was not found on this server.</p>
        <a href="index.html" class="btn btn-primary mb-4">Back to home</a>
        <div class="mt-4">
          <img
            src="/assets/img/illustrations/page-misc-error.png"
            alt="page-misc-error"
            width="225"
            class="img-fluid" />
        </div>
      </div>
    </div>
    <div class="container-fluid misc-bg-wrapper">
      <img
        src="/assets/img/illustrations/bg-shape-image-light.png"
        alt="page-misc-error"
        data-app-light-img="illustrations/bg-shape-image-light.png"
        data-app-dark-img="illustrations/bg-shape-image-dark.png" />
    </div>
    <!-- /Error -->

    <!-- / Content -->
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/auth.php';
