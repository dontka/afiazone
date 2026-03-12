<?php
$pageTitle = 'Settings Store Payments - eCommerce | Vuexy - Bootstrap Admin Template';
$additionalScripts = ['/assets/js/app-ecommerce-settings.js'];
ob_start();
?>
              <h4 class="py-3 mb-4"><span class="text-muted fw-light">eCommerce /</span> Settings</h4>

              <div class="row g-4">
                <!-- Navigation -->
                <div class="col-12 col-lg-4">
                  <div class="d-flex justify-content-between flex-column mb-3 mb-md-0">
                    <ul class="nav nav-align-left nav-pills flex-column">
                      <li class="nav-item mb-1">
                        <a class="nav-link py-2" href="app-ecommerce-settings-detail.html">
                          <i class="ti ti-building-store me-2"></i>
                          <span class="align-middle">Store details</span>
                        </a>
                      </li>
                      <li class="nav-item mb-1">
                        <a class="nav-link py-2 active" href="javascript:void(0);">
                          <i class="ti ti-credit-card me-2"></i>
                          <span class="align-middle">Payments</span>
                        </a>
                      </li>
                      <li class="nav-item mb-1">
                        <a class="nav-link py-2" href="app-ecommerce-settings-checkout.html">
                          <i class="ti ti-shopping-cart me-2"></i>
                          <span class="align-middle">Checkout</span>
                        </a>
                      </li>
                      <li class="nav-item mb-1">
                        <a class="nav-link py-2" href="app-ecommerce-settings-shipping.html">
                          <i class="ti ti-discount-2 me-2"></i>
                          <span class="align-middle">Shipping & delivery</span>
                        </a>
                      </li>
                      <li class="nav-item mb-1">
                        <a class="nav-link py-2" href="app-ecommerce-settings-locations.html">
                          <i class="ti ti-map-pin me-2"></i>
                          <span class="align-middle">Locations</span>
                        </a>
                      </li>
                      <li class="nav-item">
                        <a class="nav-link py-2" href="app-ecommerce-settings-notifications.html">
                          <i class="ti ti-bell-ringing me-2"></i>
                          <span class="align-middle">Notifications</span>
                        </a>
                      </li>
                    </ul>
                  </div>
                </div>
                <!-- /Navigation -->
                <!-- Options -->
                <div class="col-12 col-lg-8 pt-4 pt-lg-0">
                  <div class="tab-content p-0">
                    <!-- Payments Tab -->
                    <div class="tab-pane fade show active" id="payments" role="tabpanel">
                      <div class="card mb-4">
                        <div class="card-header">
                          <h5 class="card-title m-0">Payment providers</h5>
                        </div>

                        <div class="card-body">
                          <p>
                            Providers that enable you to accept payment methods at a rate set by the third-party.<br />
                            An additional fee will apply to new orders once you select a plan
                          </p>
                          <button
                            class="btn btn-label-primary"
                            data-bs-toggle="modal"
                            data-bs-target="#paymentProvider">
                            Choose a provider
                          </button>
                        </div>
                      </div>

                      <div class="card mb-4">
                        <div class="card-header">
                          <div class="card-title m-0">
                            <h5 class="m-0 mb-1">Supported payment methods</h5>
                            <p class="text-muted mb-0">
                              Payment methods that are available with one of Vuexy's approved payment providers.
                            </p>
                          </div>
                        </div>
                        <div class="card-body">
                          <h6 class="mb-3">Default</h6>
                          <div class="row mb-3 g-3 bg-label-secondary px-4 pt-2 rounded-2 mx-0 mt-0">
                            <div class="col-12 d-flex justify-content-between align-items-start border-bottom p-0 pb-2">
                              <img src="/assets/img/icons/payments/paypal.png" alt="Paypal" width="60" />

                              <span><a href="javascript:void(0);">Activate Paypal</a></span>
                            </div>
                            <div class="col-12 p-0">
                              <div class="row">
                                <div class="col-4">
                                  <p class="mb-1">Provider</p>
                                  <p class="text-body fw-medium">Paypal</p>
                                </div>
                                <div class="col-4">
                                  <p class="mb-1">Status</p>
                                  <p class="badge bg-label-warning fw-medium">Inactive</p>
                                </div>
                                <div class="col-4">
                                  <p class="mb-1">Transaction Fee</p>
                                  <p class="text-body fw-medium">2.99%</p>
                                </div>
                              </div>
                            </div>
                          </div>
                          <button class="btn btn-label-primary" data-bs-toggle="modal" data-bs-target="#paymentMethods">
                            Add payment methods
                          </button>
                        </div>
                      </div>

                      <div class="card mb-4">
                        <div class="card-header">
                          <h5 class="card-title m-0">Manual payment methods</h5>
                        </div>

                        <div class="card-body">
                          <p>
                            Payments that are made outside your online store. When a customer selects a manual payment
                            method such as cash on delivery. You'll need to approve their order before it can be
                            fulfilled.
                          </p>
                          <div class="btn-group">
                            <button
                              type="button"
                              class="btn btn-label-primary dropdown-toggle"
                              data-bs-toggle="dropdown"
                              aria-expanded="false">
                              Add manual payment method
                            </button>
                            <ul class="dropdown-menu">
                              <li>
                                <a class="dropdown-item" href="javascript:void(0);">Create custom payment method</a>
                              </li>
                              <li><a class="dropdown-item" href="javascript:void(0);">Bank deposit</a></li>
                              <li><a class="dropdown-item" href="javascript:void(0);">Money order</a></li>
                              <li><a class="dropdown-item" href="javascript:void(0);">Cash on delivery (COD)</a></li>
                            </ul>
                          </div>
                        </div>
                      </div>

                      <div class="d-flex justify-content-end gap-3">
                        <button type="reset" class="btn btn-label-secondary">Discard</button>
                        <a class="btn btn-primary" href="app-ecommerce-settings-checkout.html">Save</a>
                      </div>
                    </div>
                  </div>
                </div>
                <!-- /Options-->
              </div>

              <!-- Payment provider modal -->
              <div class="modal fade" id="paymentProvider" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-simple modal-enable-otp modal-dialog-centered">
                  <div class="modal-content p-3 p-md-5">
                    <div class="modal-body">
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      <div class="text-center mb-4">
                        <h3 class="mb-3">Select payment providers</h3>
                        <p class="text-muted">Third-party payment providers</p>
                      </div>

                      <div
                        class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between border-bottom pb-3 mb-3">
                        <h6 class="m-0 mb-2 mb-sm-0 me-5">Adyen</h6>
                        <div class="d-flex flex-wrap gap-2">
                          <img
                            src="/assets/img/icons/payments/visa-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="visa-card"
                            data-app-light-img="icons/payments/visa-light.png"
                            data-app-dark-img="icons/payments/visa-dark.png" />
                          <img
                            src="/assets/img/icons/payments/master-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="master-card"
                            data-app-light-img="icons/payments/master-light.png"
                            data-app-dark-img="icons/payments/master-dark.png" />
                          <img
                            src="/assets/img/icons/payments/ae-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="american-express-card"
                            data-app-light-img="icons/payments/ae-light.png"
                            data-app-dark-img="icons/payments/ae-dark.png" />
                          <img
                            src="/assets/img/icons/payments/jcb-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="jcb-card"
                            data-app-light-img="icons/payments/jcb-light.png"
                            data-app-dark-img="icons/payments/jcb-dark.png" />
                          <img
                            src="/assets/img/icons/payments/dc-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="diners-club-card"
                            data-app-light-img="icons/payments/dc-light.png"
                            data-app-dark-img="icons/payments/dc-dark.png" />
                        </div>
                      </div>
                      <div
                        class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between border-bottom pb-3 mb-3">
                        <h6 class="m-0 mb-2 mb-sm-0">2Checkout</h6>
                        <div class="d-flex flex-wrap gap-2">
                          <img
                            src="/assets/img/icons/payments/visa-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="visa-card"
                            data-app-light-img="icons/payments/visa-light.png"
                            data-app-dark-img="icons/payments/visa-dark.png" />
                          <img
                            src="/assets/img/icons/payments/ae-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="american-express-card"
                            data-app-light-img="icons/payments/ae-light.png"
                            data-app-dark-img="icons/payments/ae-dark.png" />
                          <img
                            src="/assets/img/icons/payments/jcb-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="jcb-card"
                            data-app-light-img="icons/payments/jcb-light.png"
                            data-app-dark-img="icons/payments/jcb-dark.png" />
                          <img
                            src="/assets/img/icons/payments/dc-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="diners-club-card"
                            data-app-light-img="icons/payments/dc-light.png"
                            data-app-dark-img="icons/payments/dc-dark.png" />
                        </div>
                      </div>
                      <div
                        class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between border-bottom pb-3 mb-3">
                        <h6 class="m-0 mb-2 mb-sm-0">Airpay</h6>
                        <div class="d-flex flex-wrap gap-2">
                          <img
                            src="/assets/img/icons/payments/visa-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="visa-card"
                            data-app-light-img="icons/payments/visa-light.png"
                            data-app-dark-img="icons/payments/visa-dark.png" />
                          <img
                            src="/assets/img/icons/payments/ae-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="american-express-card"
                            data-app-light-img="icons/payments/ae-light.png"
                            data-app-dark-img="icons/payments/ae-dark.png" />
                          <img
                            src="/assets/img/icons/payments/master-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="master-card"
                            data-app-light-img="icons/payments/master-light.png"
                            data-app-dark-img="icons/payments/master-dark.png" />
                          <img
                            src="/assets/img/icons/payments/jcb-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="jcb-card"
                            data-app-light-img="icons/payments/jcb-light.png"
                            data-app-dark-img="icons/payments/jcb-dark.png" />
                        </div>
                      </div>
                      <div
                        class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between border-bottom pb-3 mb-3">
                        <h6 class="m-0 mb-2 mb-sm-0">Authorize.net</h6>

                        <div class="d-flex flex-wrap gap-2">
                          <img
                            src="/assets/img/icons/payments/ae-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="american-express-card"
                            data-app-light-img="icons/payments/ae-light.png"
                            data-app-dark-img="icons/payments/ae-dark.png" />
                          <img
                            src="/assets/img/icons/payments/jcb-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="jcb-card"
                            data-app-light-img="icons/payments/jcb-light.png"
                            data-app-dark-img="icons/payments/jcb-dark.png" />
                          <img
                            src="/assets/img/icons/payments/dc-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="diners-club-card"
                            data-app-light-img="icons/payments/dc-light.png"
                            data-app-dark-img="icons/payments/dc-dark.png" />
                        </div>
                      </div>
                      <div
                        class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between border-bottom pb-3 mb-3">
                        <h6 class="m-0 mb-2 mb-sm-0">Bambora</h6>

                        <div class="d-flex flex-wrap gap-2">
                          <img
                            src="/assets/img/icons/payments/master-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="master-card"
                            data-app-light-img="icons/payments/master-light.png"
                            data-app-dark-img="icons/payments/master-dark.png" />
                          <img
                            src="/assets/img/icons/payments/ae-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="american-express-card"
                            data-app-light-img="icons/payments/ae-light.png"
                            data-app-dark-img="icons/payments/ae-dark.png" />
                          <img
                            src="/assets/img/icons/payments/jcb-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="jcb-card"
                            data-app-light-img="icons/payments/jcb-light.png"
                            data-app-dark-img="icons/payments/jcb-dark.png" />
                        </div>
                      </div>
                      <div
                        class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between border-bottom pb-3 mb-3">
                        <h6 class="m-0 mb-2 mb-sm-0">Cayan</h6>

                        <div class="d-flex flex-wrap gap-2">
                          <img
                            src="/assets/img/icons/payments/visa-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="visa-card"
                            data-app-light-img="icons/payments/visa-light.png"
                            data-app-dark-img="icons/payments/visa-dark.png" />
                          <img
                            src="/assets/img/icons/payments/master-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="master-card"
                            data-app-light-img="icons/payments/master-light.png"
                            data-app-dark-img="icons/payments/master-dark.png" />
                          <img
                            src="/assets/img/icons/payments/ae-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="american-express-card"
                            data-app-light-img="icons/payments/ae-light.png"
                            data-app-dark-img="icons/payments/ae-dark.png" />
                          <img
                            src="/assets/img/icons/payments/jcb-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="jcb-card"
                            data-app-light-img="icons/payments/jcb-light.png"
                            data-app-dark-img="icons/payments/jcb-dark.png" />
                          <img
                            src="/assets/img/icons/payments/dc-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="diners-club-card"
                            data-app-light-img="icons/payments/dc-light.png"
                            data-app-dark-img="icons/payments/dc-dark.png" />
                        </div>
                      </div>
                      <div
                        class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between border-bottom pb-3 mb-3">
                        <h6 class="m-0 mb-2 mb-sm-0">Chase Paymentech (Orbital)</h6>

                        <div class="d-flex flex-wrap gap-2">
                          <img
                            src="/assets/img/icons/payments/visa-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="visa-card"
                            data-app-light-img="icons/payments/visa-light.png"
                            data-app-dark-img="icons/payments/visa-dark.png" />
                          <img
                            src="/assets/img/icons/payments/ae-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="american-express-card"
                            data-app-light-img="icons/payments/ae-light.png"
                            data-app-dark-img="icons/payments/ae-dark.png" />
                          <img
                            src="/assets/img/icons/payments/jcb-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="jcb-card"
                            data-app-light-img="icons/payments/jcb-light.png"
                            data-app-dark-img="icons/payments/jcb-dark.png" />
                          <img
                            src="/assets/img/icons/payments/dc-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="diners-club-card"
                            data-app-light-img="icons/payments/dc-light.png"
                            data-app-dark-img="icons/payments/dc-dark.png" />
                        </div>
                      </div>
                      <div
                        class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between border-bottom pb-3">
                        <h6 class="m-0 mb-2 mb-sm-0">Checkout.com</h6>
                        <div class="d-flex flex-wrap gap-2">
                          <img
                            src="/assets/img/icons/payments/visa-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="visa-card"
                            data-app-light-img="icons/payments/visa-light.png"
                            data-app-dark-img="icons/payments/visa-dark.png" />
                          <img
                            src="/assets/img/icons/payments/master-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="master-card"
                            data-app-light-img="icons/payments/master-light.png"
                            data-app-dark-img="icons/payments/master-dark.png" />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- / Payment provider modal -->

              <!-- Payment Methods modal -->
              <div class="modal fade" id="paymentMethods" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-simple modal-enable-otp modal-dialog-centered">
                  <div class="modal-content p-3 p-md-5">
                    <div class="modal-body">
                      <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                      <div class="text-center mb-4">
                        <h3 class="mb-3">Select payment methods</h3>
                        <p class="text-muted">Supported payment methods</p>
                      </div>

                      <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                        <div class="d-flex gap-2 align-items-center">
                          <img
                            src="/assets/img/icons/payments/visa-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="visa-card"
                            data-app-light-img="icons/payments/visa-light.png"
                            data-app-dark-img="icons/payments/visa-dark.png" />

                          <h6 class="m-0">Visa</h6>
                        </div>
                        <h6 class="m-0 d-none d-sm-block">Credit Card</h6>
                      </div>
                      <div class="d-flex justify-content-sm-between align-items-center border-bottom pb-3 mb-3">
                        <div class="d-flex gap-2 align-items-center">
                          <img
                            src="/assets/img/icons/payments/ae-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="american-express-card"
                            data-app-light-img="icons/payments/ae-light.png"
                            data-app-dark-img="icons/payments/ae-dark.png" />

                          <h6 class="m-0">American Express</h6>
                        </div>
                        <h6 class="m-0 d-none d-sm-block">Credit Card</h6>
                      </div>
                      <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                        <div class="d-flex gap-2 align-items-center">
                          <img
                            src="/assets/img/icons/payments/master-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="master-card"
                            data-app-light-img="icons/payments/master-light.png"
                            data-app-dark-img="icons/payments/master-dark.png" />

                          <h6 class="m-0">Mastercard</h6>
                        </div>
                        <h6 class="m-0 d-none d-sm-block">Credit Card</h6>
                      </div>
                      <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                        <div class="d-flex gap-2 align-items-center">
                          <img
                            src="/assets/img/icons/payments/jcb-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="jcb-card"
                            data-app-light-img="icons/payments/jcb-light.png"
                            data-app-dark-img="icons/payments/jcb-dark.png" />
                          <h6 class="m-0">JCB</h6>
                        </div>
                        <h6 class="m-0 d-none d-sm-block">Credit Card</h6>
                      </div>
                      <div class="d-flex justify-content-between align-items-center border-bottom pb-3">
                        <div class="d-flex gap-2 align-items-center">
                          <img
                            src="/assets/img/icons/payments/dc-light.png"
                            class="img-fluid w-px-50 h-px-30"
                            alt="diners-club-card"
                            data-app-light-img="icons/payments/dc-light.png"
                            data-app-dark-img="icons/payments/dc-dark.png" />
                          <h6 class="m-0">Diners Club</h6>
                        </div>
                        <h6 class="m-0 d-none d-sm-block">Credit Card</h6>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <!-- / Payment Methods modal -->
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
