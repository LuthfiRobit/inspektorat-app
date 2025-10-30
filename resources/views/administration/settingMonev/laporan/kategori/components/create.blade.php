<!-- Modal Create Kategori Start-->
<div class="modal fade" id="modalCreate" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Kategori Laporan</h5>
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modalErrorKategori" class="alert alert-danger d-none" role="alert"></div>
                <form id="createFormKategori" method="post" class="form-sm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="kode_kategori" class="form-label">Kode Kategori</label>
                            <input type="text" class="form-control form-control-sm" id="kode_kategori"
                                name="kode_kategori" maxlength="10" placeholder="Contoh: KL001" required>
                        </div>
                        <div class="col-md-6">
                            <label for="nama_kategori" class="form-label">Nama Kategori</label>
                            <input type="text" class="form-control form-control-sm" id="nama_kategori"
                                name="nama_kategori" placeholder="Contoh: Laporan Keuangan" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control form-control-sm" id="deskripsi" name="deskripsi" rows="2"
                            placeholder="Tuliskan deskripsi kategori laporan (opsional)"></textarea>
                    </div>

                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="status_kategori" class="form-label">Status</label>
                            <select id="status_kategori" name="status_kategori"
                                class="selectpicker form-control wide form-select-md" data-live-search="false" required
                                aria-describedby="status-feedback" aria-label="Pilih Status">
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak aktif</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="createFormKategori" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Create Kategori End -->
