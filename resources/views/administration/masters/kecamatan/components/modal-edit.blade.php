<!-- Modal Edit Start -->
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditLabel">Buat Kecamatan Baru</h5>
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modalError" class="alert alert-danger d-none" role="alert"></div>
                <!-- Alert for general errors -->
                <form id="editForm" method="post" class="form-sm" data-id="">
                    <div class="mb-3">
                        <label for="kode_kecamatan" class="form-label">Kode Kecamatan</label>
                        <input type="text" class="form-control form-control-sm" id="kode_kecamatan"
                            name="kode_kecamatan" placeholder="Masukkan kode kecamatan" aria-label="Kode Kecamatan"
                            maxlength="10" autocomplete="off" required />
                    </div>
                    <div class="mb-3">
                        <label for="nama_kecamatan" class="form-label">Nama Kecamatan</label>
                        <input type="text" class="form-control form-control-sm" id="nama_kecamatan"
                            name="nama_kecamatan" placeholder="Masukkan nama kecamatan" aria-label="Nama Kecamatan"
                            maxlength="100" autocomplete="off" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="statusActive"
                                value="active" checked>
                            <label class="form-check-label" for="statusActive">Aktif</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="statusInactive"
                                value="inactive">
                            <label class="form-check-label" for="statusInactive">Tidak aktif</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" form="editForm" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Edit end -->
