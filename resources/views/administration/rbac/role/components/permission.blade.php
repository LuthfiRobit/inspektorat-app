<!-- Modal Permission Start-->
<div class="modal modal-xl fade" id="modalPermission" tabindex="-1" role="dialog" aria-hidden="true"
    data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalPermissionLabel">Permission Management</h5>
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="permissionForm" method="post" class="form" data-id="">
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-light">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6 mb-2">
                                            <span class="fw-bold text-primary">Role Name</span><br>
                                            <span id="detail_p_role_name" class="text-dark"></span>
                                        </div>
                                        <div class="col-md-6 mb-2">
                                            <span class="fw-bold text-primary">Role Description</span><br>
                                            <span id="detail_p_role_description" class="text-dark"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="fs-5 fw-bold text-dark">Permissions</span>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" id="selectAllPermissions">
                                <label class="form-check-label fw-semibold" for="selectAllPermissions">
                                    Select All
                                </label>
                            </div>
                        </div>

                        <div id="permissions_list" class="accordion" style="max-height: 60vh; overflow-y: auto;">
                            <!-- Structured permission groups will be injected here -->
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="permissionForm" id="savePermissions" class="btn btn-primary">
                    <i class="fas fa-save me-2"></i>Save Permissions
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Permission end -->
