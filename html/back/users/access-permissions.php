<?php
$pageTitle = 'Permission - Apps | Vuexy - Bootstrap Admin Template';
$vendorScripts = [
    '/assets/vendor/libs/@form-validation/umd/bundle/popular.min.js',
    '/assets/vendor/libs/@form-validation/umd/plugin-bootstrap5/index.min.js',
    '/assets/vendor/libs/@form-validation/umd/plugin-auto-focus/index.min.js',
];
$additionalScripts = [
    '/assets/js/app-access-permission.js',
    '/assets/js/modal-add-permission.js',
    '/assets/js/modal-edit-permission.js',
];
ob_start();
?>
              <h4 class="mb-4">Permissions List</h4>

              <p class="mb-4">
                Each category (Basic, Professional, and Business) includes the four predefined roles shown below.
              </p>

              <!-- Permission Table -->
              <div class="card">
                <div class="card-datatable table-responsive">
                  <table class="datatables-permissions table border-top">
                    <thead>
                      <tr>
                        <th></th>
                        <th></th>
                        <th>Name</th>
                        <th>Assigned To</th>
                        <th>Created Date</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                  </table>
                </div>
              </div>
              <!--/ Permission Table -->

              <!-- Modal -->
              <!-- Add Permission Modal -->
              <div class="modal fade" id="addPermissionModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content p-3 p-md-5">
                    <button
                      type="button"
                      class="btn-close btn-pinned"
                      data-bs-dismiss="modal"
                      aria-label="Close"></button>
                    <div class="modal-body">
                      <div class="text-center mb-4">
                        <h3 class="mb-2">Add New Permission</h3>
                        <p class="text-muted">Permissions you may use and assign to your users.</p>
                      </div>
                      <form id="addPermissionForm" class="row" onsubmit="return false">
                        <div class="col-12 mb-3">
                          <label class="form-label" for="modalPermissionName">Permission Name</label>
                          <input
                            type="text"
                            id="modalPermissionName"
                            name="modalPermissionName"
                            class="form-control"
                            placeholder="Permission Name"
                            autofocus />
                        </div>
                        <div class="col-12 mb-2">
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="corePermission" />
                            <label class="form-check-label" for="corePermission"> Set as core permission </label>
                          </div>
                        </div>
                        <div class="col-12 text-center demo-vertical-spacing">
                          <button type="submit" class="btn btn-primary me-sm-3 me-1">Create Permission</button>
                          <button
                            type="reset"
                            class="btn btn-label-secondary"
                            data-bs-dismiss="modal"
                            aria-label="Close">
                            Discard
                          </button>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
              <!--/ Add Permission Modal -->

              <!-- Edit Permission Modal -->
              <div class="modal fade" id="editPermissionModal" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-dialog-centered">
                  <div class="modal-content p-3 p-md-5">
                    <button
                      type="button"
                      class="btn-close btn-pinned"
                      data-bs-dismiss="modal"
                      aria-label="Close"></button>
                    <div class="modal-body">
                      <div class="text-center mb-4">
                        <h3 class="mb-2">Edit Permission</h3>
                        <p class="text-muted">Edit permission as per your requirements.</p>
                      </div>
                      <div class="alert alert-warning" role="alert">
                        <h6 class="alert-heading mb-2">Warning</h6>
                        <p class="mb-0">
                          By editing the permission name, you might break the system permissions functionality. Please
                          ensure you're absolutely certain before proceeding.
                        </p>
                      </div>
                      <form id="editPermissionForm" class="row" onsubmit="return false">
                        <div class="col-sm-9">
                          <label class="form-label" for="editPermissionName">Permission Name</label>
                          <input
                            type="text"
                            id="editPermissionName"
                            name="editPermissionName"
                            class="form-control"
                            placeholder="Permission Name"
                            tabindex="-1" />
                        </div>
                        <div class="col-sm-3 mb-3">
                          <label class="form-label invisible d-none d-sm-inline-block">Button</label>
                          <button type="submit" class="btn btn-primary mt-1 mt-sm-0">Update</button>
                        </div>
                        <div class="col-12">
                          <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="editCorePermission" />
                            <label class="form-check-label" for="editCorePermission"> Set as core permission </label>
                          </div>
                        </div>
                      </form>
                    </div>
                  </div>
                </div>
              </div>
              <!--/ Edit Permission Modal -->

              <!-- /Modal -->
<?php
$content = ob_get_clean();
require __DIR__ . '/../layouts/admin.php';
