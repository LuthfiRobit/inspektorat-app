<!-- Modal Create Desa Start-->
<div class="modal fade" id="modalCreateDesa" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Data Desa</h5>
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modalErrorDesa" class="alert alert-danger d-none" role="alert"></div>
                <form id="createFormDesa" method="post" class="form-sm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="kode_desa" class="form-label">Kode Desa</label>
                            <input type="text" class="form-control form-control-sm" id="kode_desa" name="kode_desa"
                                maxlength="10" placeholder="Contoh: 350101" required>
                        </div>
                        <div class="col-md-6">
                            <label for="nama_desa" class="form-label">Nama Desa</label>
                            <input type="text" class="form-control form-control-sm" id="nama_desa" name="nama_desa"
                                placeholder="Contoh: Sogaan" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="kecamatan_id" class="form-label">Kecamatan</label>
                        <select id="kecamatan_id" name="kecamatan_id"
                            class="selectpicker form-control wide form-select-md" data-live-search="true" required
                            aria-describedby="kecamatan-feedback" aria-label="Pilih Kecamatan">
                            <option value="" disabled selected>Pilih Kecamatan</option>

                        </select>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="status_desa" class="form-label">Status</label>
                            <select id="status_desa" name="status_desa"
                                class="selectpicker form-control wide form-select-md" data-live-search="false" required
                                aria-describedby="status-feedback" aria-label="Pilih Status">
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak aktif</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="kode_pos" class="form-label">Kode Pos</label>
                            <input type="text" class="form-control form-control-sm" id="kode_pos" name="kode_pos"
                                maxlength="6" placeholder="Contoh: 67292">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="createFormDesa" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Create Desa End -->
