<?php
$pageTitle = 'Shepherd tour - Extended UI | Vuexy - Bootstrap Admin Template';
$vendorStyles = ['/assets/vendor/libs/shepherd/shepherd.css'];
$vendorScripts = ['/assets/vendor/libs/shepherd/shepherd.js'];
$additionalScripts = ['/assets/js/extended-ui-tour.js'];
ob_start();
?>
              <h4 class="py-3 mb-4"><span class="text-muted fw-light">Extended UI /</span> Shepherd tour</h4>

              <div class="row">
                <div class="col-12">
                  <div class="card tour-card">
                    <h5 class="card-header">Tour</h5>
                    <div class="card-body">
                      <button class="btn btn-primary" id="shepherd-example">Start tour</button>
                    </div>
                  </div>
                </div>
              </div>
<?php
$content = ob_get_clean();
require __DIR__ . '/../../layouts/admin.php';
