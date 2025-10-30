<!-- Modal Create Jenis Laporan Start-->
<div class="modal fade" id="modalCreate" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Jenis Laporan</h5>
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modalErrorJenis" class="alert alert-danger d-none" role="alert"></div>
                <form id="createFormJenis" method="post" class="form-sm">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="kategori_id" class="form-label">Kategori Laporan</label>
                            <select id="kategori_id" name="kategori_id"
                                class="selectpicker form-control wide form-select-md" data-live-search="true" required
                                aria-label="Pilih Kategori Laporan">
                                <option value="" disabled selected>Pilih Kategori</option>
                                {{-- opsi kategori dari backend --}}
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="kode_jenis" class="form-label">Kode Jenis</label>
                            <input type="text" class="form-control form-control-sm" id="kode_jenis" name="kode_jenis"
                                maxlength="10" placeholder="Contoh: JL001" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="nama_jenis" class="form-label">Nama Jenis Laporan</label>
                        <input type="text" class="form-control form-control-sm" id="nama_jenis" name="nama_jenis"
                            placeholder="Contoh: Laporan Tahunan" required>
                    </div>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="status_jenis" class="form-label">Status</label>
                            <select id="status_jenis" name="status_jenis"
                                class="selectpicker form-control wide form-select-md" data-live-search="false" required
                                aria-label="Pilih Status">
                                <option value="active">Aktif</option>
                                <option value="inactive">Tidak aktif</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label for="keterangan" class="form-label">Keterangan</label>
                            <input type="text" class="form-control form-control-sm" id="keterangan" name="keterangan"
                                placeholder="Opsional">
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="createFormJenis" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Create Jenis Laporan End-->
