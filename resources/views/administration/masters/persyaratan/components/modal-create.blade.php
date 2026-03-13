<!-- Modal Create Start-->
<div class="modal fade" id="modalCreate" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buat Persyaratan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div id="modalError" class="alert alert-danger d-none" role="alert"></div>
                <form id="createForm" method="post" class="form-lg" enctype="multipart/form-data">
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

                            <!-- Pertanyaan berdasarkan kegiatan -->
                            <div class="mb-3">
                                <label for="pertanyaan_kegiatan_id" class="form-label">Pilih Pertanyaan</label>
                                <select class="selectpicker form-control wide form-select-md" data-size="5"
                                    data-live-search="true" id="pertanyaan_kegiatan_id" name="pertanyaan_kegiatan_id"
                                    placeholder="Pilih pertanyaan" required>
                                </select>
                            </div>
                        </div>

                        <!-- Grid 2 Kolom -->
                        <div class="col-md-6">
                            <!-- Nama Persyaratan -->
                            <div class="mb-3">
                                <label for="nama_persyaratan" class="form-label">Nama Persyaratan</label>
                                <input type="text" class="form-control" id="nama_persyaratan" name="nama_persyaratan"
                                    placeholder="Masukkan nama persyaratan" maxlength="200" required />
                            </div>

                            <!-- Template Persyaratan (file input) -->
                            <div class="mb-3">
                                <label for="template_persyaratan" class="form-label">Template Persyaratan
                                    (opsional)</label>
                                <input type="file" class="form-control" id="template_persyaratan"
                                    name="template_persyaratan" accept=".pdf,.doc,.docx" />
                                <small class="form-text text-muted">Maksimal 2MB. Format: PDF, DOC, DOCX</small>
                            </div>

                            <!-- Tipe Persyaratan -->
                            <div class="mb-3">
                                <label class="form-label">Tipe Persyaratan</label><br />
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tipe" id="tipeWajib"
                                        value="wajib" checked>
                                    <label class="form-check-label" for="tipeWajib">Wajib</label>
                                </div>
                                <div class="form-check form-check-inline">
                                    <input class="form-check-input" type="radio" name="tipe" id="tipeTambahan"
                                        value="tambahan">
                                    <label class="form-check-label" for="tipeTambahan">Tambahan</label>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <!-- Deskripsi -->
                            <div class="mb-3">
                                <label for="deskripsi" class="form-label">Deskripsi</label>
                                <textarea name="deskripsi" id="deskripsi" class="form-control" rows="5"
                                    placeholder="Tulis deskripsi tambahan (jika ada)"></textarea>
                            </div>

                            <!-- Urutan -->
                            <div class="mb-3">
                                <label for="urutan" class="form-label">Urutan</label>
                                <input type="number" class="form-control" id="urutan" name="urutan"
                                    placeholder="Masukkan urutan persyaratan" min="1" required />
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
                                    <input class="form-check-input" type="radio" name="status"
                                        id="statusInactive" value="inactive">
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
