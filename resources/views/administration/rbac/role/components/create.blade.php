<!-- Modal Create Start-->
<div class="modal fade" id="modalCreate" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCreateLabel">Buat Data Baru</h5>
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modalError" class="alert alert-danger d-none" role="alert"></div>
                <form id="createForm" method="post" class="form-sm">
                    <div class="mb-3">
                        <label for="role_name" class="form-label">Scope Role</label>
                        <select class="selectpicker form-control wide form-select-md" id="role_scope" name="role_scope"
                            data-live-search="false" title="Pilih Scope">
                            <option value="inspektorat">Inspektorat</option>
                            <option value="kecamatan">Kecamatan</option>
                            <option value="desa">Desa</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="role_name" class="form-label">Nama Role</label>
                        <input type="text" class="form-control form-control-sm" id="role_name" name="role_name"
                            placeholder="Masukkan nama role" aria-label="Nama Role" maxlength="100"
                            autocomplete="off" />
                    </div>
                    <div class="mb-3">
                        <label for="role_description" class="form-label">Deskripsi Role</label>
                        <textarea class="form-control form-control-sm" id="role_description" name="role_description" rows="3"
                            placeholder="Masukkan deskripsi role" aria-label="Deskripsi Role"></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="createForm" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Create end -->
