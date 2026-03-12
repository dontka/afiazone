<?php
$pageTitle = 'Two Steps Verifications Basic - Pages | Vuexy - Bootstrap Admin Template';
$vendorScripts = [
    '/assets/vendor/libs/cleavejs/cleave.js',
    '/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js',
    '/assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js',
    '/assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js',
];
$additionalScripts = [
    '/assets/js/pages-auth.js',
    '/assets/js/pages-auth-two-steps.js',
];
ob_start();
?>
<!-- Content -->

    <div class="authentication-wrapper authentication-basic px-4">
      <div class="authentication-inner py-4">
        <!--  Two Steps Verification -->
        <div class="card">
          <div class="card-body">
            <!-- Logo -->
            <div class="app-brand justify-content-center mb-4 mt-2">
              <a href="index.html" class="app-brand-link gap-2">
                <span class="app-brand-logo demo">
                  <svg width="32" height="22" viewBox="0 0 32 22" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                      fill-rule="evenodd"
                      clip-rule="evenodd"
                      d="M0.00172773 0V6.85398C0.00172773 6.85398 -0.133178 9.01207 1.98092 10.8388L13.6912 21.9964L19.7809 21.9181L18.8042 9.88248L16.4951 7.17289L9.23799 0H0.00172773Z"
                      fill="#7367F0" />
                    <path
                      opacity="0.06"
                      fill-rule="evenodd"
                      clip-rule="evenodd"
                      d="M7.69824 16.4364L12.5199 3.23696L16.5541 7.25596L7.69824 16.4364Z"
                      fill="#161616" />
                    <path
                      opacity="0.06"
                      fill-rule="evenodd"
                      clip-rule="evenodd"
                      d="M8.07751 15.9175L13.9419 4.63989L16.5849 7.28475L8.07751 15.9175Z"
                      fill="#161616" />
                    <path
                      fill-rule="evenodd"
                      clip-rule="evenodd"
                      d="M7.77295 16.3566L23.6563 0H32V6.88383C32 6.88383 31.8262 9.17836 30.6591 10.4057L19.7824 22H13.6938L7.77295 16.3566Z"
                      fill="#7367F0" />
                  </svg>
                </span>
                <span class="app-brand-text demo text-body fw-bold ms-1">Vuexy</span>
              </a>
            </div>
            <!-- /Logo -->
            <h4 class="mb-1 pt-2">Two Step Verification 💬</h4>
            <p class="text-start mb-4">
              We sent a verification code to your mobile. Enter the code from the mobile in the field below.
              <span class="fw-medium d-block mt-2">******1234</span>
            </p>
            <p class="mb-0 fw-medium">Type your 6 digit security code</p>
            <form id="twoStepsForm" action="index.html" method="POST">
              <div class="mb-3">
                <div
                  class="auth-input-wrapper d-flex align-items-center justify-content-sm-between numeral-mask-wrapper">
                  <input
                    type="text"
                    class="form-control auth-input h-px-50 text-center numeral-mask text-center h-px-50 mx-1 my-2"
                    maxlength="1"
                    autofocus />
                  <input
                    type="text"
                    class="form-control auth-input h-px-50 text-center numeral-mask text-center h-px-50 mx-1 my-2"
                    maxlength="1" />
                  <input
                    type="text"
                    class="form-control auth-input h-px-50 text-center numeral-mask text-center h-px-50 mx-1 my-2"
                    maxlength="1" />
                  <input
                    type="text"
                    class="form-control auth-input h-px-50 text-center numeral-mask text-center h-px-50 mx-1 my-2"
                    maxlength="1" />
                  <input
                    type="text"
                    class="form-control auth-input h-px-50 text-center numeral-mask text-center h-px-50 mx-1 my-2"
                    maxlength="1" />
                  <input
                    type="text"
                    class="form-control auth-input h-px-50 text-center numeral-mask text-center h-px-50 mx-1 my-2"
                    maxlength="1" />
                </div>
                <!-- Create a hidden field which is combined by 3 fields above -->
                <input type="hidden" name="otp" />
              </div>
              <button class="btn btn-primary d-grid w-100 mb-3">Verify my account</button>
              <div class="text-center">
                Didn't get the code?
                <a href="javascript:void(0);"> Resend </a>
              </div>
            </form>
          </div>
        </div>
        <!-- / Two Steps Verification -->
      </div>
    </div>

    <!-- / Content -->
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/auth.php';
