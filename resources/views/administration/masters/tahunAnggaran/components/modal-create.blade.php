<!-- Modal Create Start-->
<div class="modal fade" id="modalCreate" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalCreateLabel">Buat Tahun Anggaran Baru</h5>
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modalError" class="alert alert-danger d-none" role="alert"></div>
                <form id="createForm" method="post" class="form-sm">
                    <div class="mb-3">
                        <label for="tahun" class="form-label">Tahun Anggaran</label>
                        <select name="tahun" id="tahun" class="selectpicker form-control wide form-select-md"
                            data-live-search="true" data-size="5" title="Pilih tahun anggaran" required>
                            @foreach ($yearsRange as $year)
                                <option value="{{ $year }}"
                                    {{ $year == date('Y') ? 'data-subtext=TAHUN_INI' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">
                            Pilih tahun anggaran yang akan digunakan. Tahun saat ini akan ditandai sebagai
                            <code>TAHUN_INI</code>.
                        </small>
                    </div>

                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control form-control-sm" rows="3"></textarea>
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
                <button type="submit" form="createForm" class="btn btn-primary">Simpan</button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Create end -->
