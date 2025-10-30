<!-- Modal Create Pertanyaan Start-->
<div class="modal fade" id="modalCreatePertanyaan" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Pertanyaan</h5>
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modalErrorPertanyaan" class="alert alert-danger d-none" role="alert"></div>
                <form id="createFormPertanyaan" method="post" class="form-sm">
                    <div class="mb-3">
                        <label for="kategori_id" class="form-label">Kategori Laporan</label>
                        <select id="kategori_id" name="kategori_id" class="selectpicker form-control wide"
                            data-live-search="true" required>
                            <option value="">-- Pilih Kategori --</option>
                            <!-- render kategori laporan -->
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="jenis_id" class="form-label">Jenis Laporan</label>
                        <select id="jenis_id" name="jenis_id" class="selectpicker form-control wide"
                            data-live-search="true" required>
                            <option value="">-- Pilih Jenis Laporan --</option>
                            <!-- render jenis laporan sesuai kategori -->
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="isi_pertanyaan" class="form-label">Pertanyaan</label>
                        <textarea class="form-control form-control-sm" id="isi_pertanyaan" name="isi_pertanyaan"
                            placeholder="Tuliskan pertanyaan..." rows="3" required></textarea>
                    </div>

                    <div class="mb-3">
                        <label for="status_pertanyaan" class="form-label">Status</label>
                        <select id="status_pertanyaan" name="status_pertanyaan"
                            class="selectpicker form-control wide form-select-md" data-live-search="false" required>
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak aktif</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="createFormPertanyaan" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Create Pertanyaan End-->
