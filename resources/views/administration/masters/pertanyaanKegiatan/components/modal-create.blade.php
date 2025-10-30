<!-- Modal Create Start-->
<div class="modal fade" id="modalCreate" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat Peranyaan Kegiatan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div id="modalError" class="alert alert-danger d-none" role="alert"></div>
                <form id="createForm" method="post" class="form-lg">
                    <div class="row">
                        <div class="col-md-12">
                            <!-- Kegiatan -->
                            <div class="mb-3">
                                <label for="kegiatan_id" class="form-label">Pilih Kegiatan</label>
                                <select class="selectpicker form-control wide form-select-md" data-size="5"
                                    data-live-search="true" id="kegiatan_id" name="kegiatan_id"
                                    placeholder="Pilih kegiatan" required>
                                    @foreach ($kegiatanList as $item)
                                        <option value="{{ $item->id_kegiatan }}">
                                            {{ $item->tahun }} | {{ $item->kode_kegiatan }} -
                                            {{ $item->nama_kegiatan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Pertanyaan -->
                            <div class="mb-3">
                                <label for="pertanyaan" class="form-label">Pertanyaan</label>
                                <textarea name="pertanyaan" id="pertanyaan" class="form-control" rows="3" placeholder="Masukkan pertanyaan"
                                    required></textarea>
                            </div>

                            <!-- Urutan -->
                            <div class="mb-3">
                                <label for="urutan" class="form-label">Urutan</label>
                                <input type="number" class="form-control" id="urutan" name="urutan"
                                    placeholder="Masukkan urutan pertanyaan" min="1" required />
                            </div>

                            <!-- Status -->
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
