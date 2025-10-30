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
                        <label for="permission_name" class="form-label">Nama Permission</label>
                        <input type="text" class="form-control form-control-sm" id="permission_name"
                            name="permission_name" placeholder="Masukkan nama permission" aria-label="Nama Permission"
                            maxlength="100" autocomplete="off" />
                    </div>
                    <div class="mb-3">
                        <label for="permission_description" class="form-label">Deskripsi Permission</label>
                        <textarea class="form-control form-control-sm" id="permission_description" name="permission_description" rows="3"
                            placeholder="Masukkan deskripsi permission" aria-label="Deskripsi Permission"></textarea>
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
