<?php
$pageTitle = 'Coming Soon - Pages | Vuexy - Bootstrap Admin Template';
$pageStyles = ['/assets/vendor/css/pages/page-misc.css'];
ob_start();
?>
<!-- Content -->

    <!-- Under Maintenance -->
    <div class="container-xxl container-p-y">
      <div class="misc-wrapper">
        <h2 class="mb-1 mx-2">We are launching soon</h2>
        <p class="mb-4 mx-2">We're creating something awesome. Please subscribe to get notified when it's ready!</p>
        <form onsubmit="return false" class="mb-4">
          <div class="mb-0">
            <div class="input-group">
              <input type="text" class="form-control" placeholder="email" autofocus />
              <button type="submit" class="btn btn-primary">Notify</button>
            </div>
          </div>
        </form>
        <div class="mt-4">
          <img
            src="/assets/img/illustrations/page-misc-launching-soon.png"
            alt="page-misc-launching-soon"
            width="263"
            class="img-fluid" />
        </div>
      </div>
    </div>
    <div class="container-fluid misc-bg-wrapper">
      <img
        src="/assets/img/illustrations/bg-shape-image-light.png"
        alt="page-misc-coming-soon"
        data-app-light-img="illustrations/bg-shape-image-light.png"
        data-app-dark-img="illustrations/bg-shape-image-dark.png" />
    </div>
    <!-- /Under Maintenance -->

    <!-- / Content -->
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/auth.php';
