<!-- Modal Create Jenis Dokumen Start-->
<div class="modal fade" id="modalCreateDokumen" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Tambah Jenis Dokumen</h5>
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modalErrorDokumen" class="alert alert-danger d-none" role="alert"></div>
                <form id="createFormDokumen" method="post" class="form-sm">
                    <div class="mb-3">
                        <label for="nama_dokumen" class="form-label">Nama Dokumen</label>
                        <input type="text" class="form-control form-control-sm" id="nama_dokumen" name="nama_dokumen"
                            placeholder="Contoh: Dokumen Evaluasi Tahunan" required>
                    </div>

                    <div class="mb-3">
                        <label for="dokumen_rujukan" class="form-label">Dokumen Rujukan</label>
                        <input type="text" class="form-control form-control-sm" id="dokumen_rujukan"
                            name="dokumen_rujukan" placeholder="Contoh: Permendikbud No. 23 Tahun 2020">
                    </div>

                    <div class="mb-3">
                        <label for="status_dokumen" class="form-label">Status</label>
                        <select id="status_dokumen" name="status_dokumen"
                            class="selectpicker form-control wide form-select-md" data-live-search="false" required
                            aria-label="Pilih Status">
                            <option value="active">Aktif</option>
                            <option value="inactive">Tidak aktif</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="submit" form="createFormDokumen" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Create Jenis Dokumen End-->
