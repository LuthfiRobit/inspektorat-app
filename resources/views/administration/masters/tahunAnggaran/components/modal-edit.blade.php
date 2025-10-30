<!-- Modal Edit Start -->
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalEditLabel">Buat Tahun Anggaran Baru</h5>
                <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="modalError" class="alert alert-danger d-none" role="alert"></div>
                <!-- Alert for general errors -->
                <form id="editForm" method="post" class="form-sm" data-id="">
                    <div class="mb-3">
                        <label for="tahun_edit" class="form-label">Tahun Anggaran</label>
                        <select name="tahun" id="tahun_edit" class="selectpicker form-control wide form-select-md"
                            data-live-search="true" data-size="5" title="Pilih tahun anggaran" required>
                            @foreach ($yearsRange as $year)
                                <option value="{{ $year }}"
                                    {{ $year == old('tahun', $data->tahun ?? '') ? 'selected' : '' }}
                                    {{ $year == date('Y') ? 'data-subtext=TAHUN_INI' : '' }}>
                                    {{ $year }}
                                </option>
                            @endforeach
                        </select>
                        <small class="form-text text-muted">
                            Ubah tahun anggaran jika diperlukan. Tahun saat ini ditandai sebagai <code>TAHUN_INI</code>.
                        </small>
                    </div>
                    <div class="mb-3">
                        <label for="keterangan" class="form-label">Keterangan</label>
                        <textarea name="keterangan" id="edit_keterangan" class="form-control form-control-sm" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status</label><br>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="edit_status_active"
                                value="active">
                            <label class="form-check-label" for="edit_status_active">Aktif</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="status" id="edit_status_inactive"
                                value="inactive">
                            <label class="form-check-label" for="edit_status_inactive">Tidak aktif</label>
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
