<!-- Modal Create Kecamatan -->
<div class="modal fade" id="modalCreateKecamatan" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kecamatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formCreateKecamatan">
                    <div class="mb-3">
                        <label for="nama_kecamatan" class="form-label">Nama Kecamatan</label>
                        <input type="text" class="form-control form-control-sm" id="nama_kecamatan"
                            name="nama_kecamatan" required>
                    </div>
                    <div class="mb-3">
                        <label for="kode_wilayah" class="form-label">Kode Wilayah</label>
                        <input type="text" class="form-control form-control-sm" id="kode_wilayah" name="kode_wilayah"
                            required>
                    </div>
                    <div class="mb-3">
                        <label for="status_kecamatan" class="form-label">Status</label>
                        <select id="status_kecamatan" name="status"
                            class="selectpicker form-control wide form-select-md">
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak aktif</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="submit" form="formCreateKecamatan" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>
