<!-- Modal Edit Start -->
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditLabel">Edit Data</h5>
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modalError" class="alert alert-danger d-none" role="alert"></div>
                <!-- Alert for general errors -->
                <form id="editForm" method="post" class="form-sm" data-id="">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control form-control-sm" id="edit_name" name="name"
                            placeholder="Masukkan nama lengkap" aria-label="Nama Lengkap" maxlength="100"
                            autocomplete="off" />
                    </div>
                    <div class="mb-3">
                        <label for="edit_username" class="form-label">Username</label>
                        <input type="text" class="form-control form-control-sm" id="edit_username" name="username"
                            placeholder="Masukkan username" aria-label="Username" maxlength="50" autocomplete="off" />
                    </div>
                    <div class="mb-3">
                        <label for="edit_email" class="form-label">Email</label>
                        <input type="email" class="form-control form-control-sm" id="edit_email" name="email"
                            placeholder="Masukkan email" aria-label="Email" maxlength="100" autocomplete="off" />
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" class="form-control form-control-sm" id="password" name="password"
                            placeholder="Password (kosongkan jika tidak ingin mengubah)" />
                    </div>
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                        <input type="password" class="form-control form-control-sm" id="password_confirmation"
                            name="password_confirmation" placeholder="Konfirmasi Password" />
                        <small id="password-error" class="form-text text-danger" style="display: none;">Password dan
                            konfirmasi password tidak cocok.</small>
                    </div>
                    <div class="mb-3">
                        <label for="edit_status" class="form-label">Status</label>
                        <select id="edit_status" name="status" class="selectpicker form-control wide form-select-md"
                            data-live-search="false" required aria-describedby="status-feedback"
                            aria-label="Pilih Status">
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak aktif</option>
                        </select>
                    </div>
                </form>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="editForm" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Edit End -->
