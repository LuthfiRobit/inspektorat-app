<!-- Modal Edit Start -->
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Data Desa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div id="modalError" class="alert alert-danger d-none" role="alert"></div>
                <form id="editForm" method="post" class="form-sm" data-id="">
                    <div class="mb-3">
                        <label for="edit_tahun_anggaran_id" class="form-label">Tahun Anggaran</label>
                        <select class="selectpicker form-control wide form-select-md" data-size="5"
                            data-live-search="true" id="edit_tahun_anggaran_id" name="tahun_anggaran_id" required
                            placeholder="Pilih tahun anggaran">
                            @foreach ($tahunAnggaranList as $item)
                                <option value="{{ $item->id_tahun_anggaran }}">{{ $item->tahun }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="edit_kode_jenis" class="form-label">Kode Jenis</label>
                        <input type="text" class="form-control form-control-sm" id="edit_kode_jenis"
                            name="kode_jenis" placeholder="Masukkan kode jenis" maxlength="10" autocomplete="off"
                            required />
                    </div>
                    <div class="mb-3">
                        <label for="edit_nama_jenis" class="form-label">Nama Jenis</label>
                        <input type="text" class="form-control form-control-sm" id="edit_nama_jenis"
                            name="nama_jenis" placeholder="Masukkan nama jenis" maxlength="200" autocomplete="off"
                            required />
                    </div>
                    <div class="mb-3">
                        <label for="edit_keterangan" class="form-label">Keterangan</label>
                        <textarea name="edit_keterangan" id="keterangan" class="form-control form-control-sm" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label><br />
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="edit_statusActive"
                                value="active">
                            <label class="form-check-label" for="edit_statusActive">Aktif</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="edit_statusInactive"
                                value="inactive">
                            <label class="form-check-label" for="edit_statusInactive">Tidak Aktif</label>
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
<!-- Modal Edit End -->
