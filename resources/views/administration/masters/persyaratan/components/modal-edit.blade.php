<!-- Modal Edit Start -->
<div class="modal fade" id="modalEdit" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Data</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div id="modalError" class="alert alert-danger d-none" role="alert"></div>
                <form id="editForm" method="post" class="form-lg" enctype="multipart/form-data" data-id="">
                    <div class="row">
                        <div class="col-md-12">
                            <!-- Kegiatan -->
                            <div class="mb-3">
                                <label for="edit_kegiatan_id" class="form-label">Pilih Kegiatan</label>
                                <select class="selectpicker form-control wide form-select-md" data-size="5"
                                    data-live-search="true" id="edit_kegiatan_id" name="kegiatan_id" required>
                                    @foreach ($kegiatanList as $item)
                                        <option value="{{ $item->id_kegiatan }}">
                                            {{ $item->tahun }} | {{ $item->kode_kegiatan }} -
                                            {{ $item->nama_kegiatan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Pertanyaan berdasarkan kegiatan -->
                            <div class="mb-3">
                                <label for="edit_pertanyaan_kegiatan_id" class="form-label">Pilih Pertanyaan</label>
                                <select class="selectpicker form-control wide form-select-md" data-size="5"
                                    data-live-search="true" id="edit_pertanyaan_kegiatan_id"
                                    name="pertanyaan_kegiatan_id" required>
                                </select>
                            </div>
                        </div>

                        <!-- Grid 2 Kolom -->
                        <div class="col-md-6">
                            <!-- Nama Persyaratan -->
                            <div class="mb-3">
                                <label for="edit_nama_persyaratan" class="form-label">Nama Persyaratan</label>
                                <input type="text" class="form-control" id="edit_nama_persyaratan"
                                    name="nama_persyaratan" maxlength="200" placeholder="Masukkan nama persyaratan"
                                    required />
                            </div>

                            <!-- Template Persyaratan (file input) -->
                            <div class="mb-3">
                                <label for="edit_template_persyaratan" class="form-label">Template Persyaratan
                                    (opsional)</label>
                                <input type="file" class="form-control" id="edit_template_persyaratan"
                                    name="template_persyaratan" accept=".pdf,.doc,.docx,.xls,.xlsx,.jpg,.png" />
                                <small class="form-text text-muted d-flex align-items-center gap-2">
                                    File saat ini: <div id="link-container"></div>
                                </small>
                            </div>

                            <!-- Tipe Persyaratan -->
                            <div class="mb-3">
                                <label class="form-label">Tipe Persyaratan</label><br />
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tipe" id="edit_tipeWajib"
                                        value="wajib">
                                    <label class="form-check-label" for="edit_tipeWajib">Wajib</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tipe" id="edit_tipeTambahan"
                                        value="tambahan">
                                    <label class="form-check-label" for="edit_tipeTambahan">Tambahan</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Deskripsi -->
                            <div class="mb-3">
                                <label for="edit_deskripsi" class="form-label">Deskripsi</label>
                                <textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="5"
                                    placeholder="Tulis deskripsi tambahan (jika ada)"></textarea>
                            </div>

                            <!-- Urutan -->
                            <div class="mb-3">
                                <label for="edit_urutan" class="form-label">Urutan</label>
                                <input type="number" class="form-control" id="edit_urutan" name="urutan"
                                    min="1" placeholder="Masukkan urutan persyaratan" required />
                            </div>

                            <!-- Status -->
                            <div class="mb-3">
                                <label class="form-label">Status</label><br />
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status"
                                        id="edit_statusActive" value="active">
                                    <label class="form-check-label" for="edit_statusActive">Aktif</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="status"
                                        id="edit_statusInactive" value="inactive">
                                    <label class="form-check-label" for="edit_statusInactive">Tidak Aktif</label>
                                </div>
                            </div>
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
