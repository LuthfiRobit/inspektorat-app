<!-- Modal Create Start-->
<div class="modal fade" id="modalCreate" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat Desa Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div id="modalError" class="alert alert-danger d-none" role="alert"></div>
                <form id="createForm" method="post" class="form-sm">
                    <div class="mb-3">
                        <label for="kecamatan_id" class="form-label">Kecamatan</label>
                        <select class="selectpicker form-control wide form-select-md" data-size="5"
                            data-live-search="true" id="kecamatan_id" name="kecamatan_id" required
                            placeholder="Pilih kecamatan">
                            @foreach ($kecamatanList as $item)
                                <option value="{{ $item->id_kecamatan }}">{{ $item->nama_kecamatan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="kode_desa" class="form-label">Kode Desa</label>
                        <input type="text" class="form-control form-control-sm" id="kode_desa" name="kode_desa"
                            placeholder="Masukkan kode desa" maxlength="20" autocomplete="off" required />
                    </div>
                    <div class="mb-3">
                        <label for="nama_desa" class="form-label">Nama Desa</label>
                        <input type="text" class="form-control form-control-sm" id="nama_desa" name="nama_desa"
                            placeholder="Masukkan nama desa" maxlength="200" autocomplete="off" required />
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label><br />
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="statusActive"
                                value="active" checked>
                            <label class="form-check-label" for="statusActive">Aktif</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="statusInactive"
                                value="inactive">
                            <label class="form-check-label" for="statusInactive">Tidak Aktif</label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="submit" form="createForm" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Create End -->
