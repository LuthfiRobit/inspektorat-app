<!-- Modal User Role Start -->
<div class="modal fade" id="modalUserRole" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Pengaturan Role User</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form id="userRoleForm" data-id="">
                    <div class="mb-3">
                        <label class="form-label">Nama User:</label>
                        <div id="user_name_display" class="form-control-plaintext"></div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Daftar Role:</label>
                        <div id="roles_list" class="row row-gap-1 column-gap-0">
                            <!-- Checkbox roles will be inserted here -->
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" form="userRoleForm" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal User Role End -->
