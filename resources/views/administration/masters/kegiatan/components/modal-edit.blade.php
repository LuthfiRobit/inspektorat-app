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
                <form id="editForm" method="post" class="form-lg" data-id="">
                    <div class="row">
                        <!-- Kolom Kiri -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_tahun_anggaran_id" class="form-label">Tahun Anggaran</label>
                                <select class="selectpicker form-control wide form-select-md" data-size="5"
                                    data-live-search="true" id="edit_tahun_anggaran_id" name="tahun_anggaran_id"
                                    required>
                                    @foreach ($tahunAnggaranList as $item)
                                        <option value="{{ $item->id_tahun_anggaran }}">{{ $item->tahun }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="edit_jenis_kegiatan_id" class="form-label">Jenis Kegiatan</label>
                                <select class="selectpicker form-control wide form-select-md" data-size="5"
                                    data-live-search="true" id="edit_jenis_kegiatan_id" name="jenis_kegiatan_id"
                                    required>

                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="edit_kode_kegiatan" class="form-label">Kode Kegiatan</label>
                                <input type="text" class="form-control" id="edit_kode_kegiatan" name="kode_kegiatan"
                                    placeholder="Masukkan kode kegiatan" maxlength="20" autocomplete="off" required />
                            </div>

                            <div class="mb-3">
                                <label for="edit_nama_kegiatan" class="form-label">Nama Kegiatan</label>
                                <input type="text" class="form-control" id="edit_nama_kegiatan" name="nama_kegiatan"
                                    placeholder="Masukkan nama kegiatan" maxlength="500" autocomplete="off" required />
                            </div>

                            <div class="mb-3">
                                <label for="edit_dasar_hukum" class="form-label">Dasar Hukum</label>
                                <textarea name="dasar_hukum" id="edit_dasar_hukum" class="form-control" rows="3"
                                    placeholder="Masukkan dasar hukum kegiatan"></textarea>
                            </div>
                        </div>

                        <!-- Kolom Kanan -->
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_bulan" class="form-label">Bulan</label>
                                <select class="form-control" class="selectpicker form-control wide form-select-md"
                                    data-size="5" data-live-search="true" id="edit_bulan" name="bulan">
                                    <option value="">Pilih Bulan</option>
                                    <option value="1">Januari</option>
                                    <option value="2">Februari</option>
                                    <option value="3">Maret</option>
                                    <option value="4">April</option>
                                    <option value="5">Mei</option>
                                    <option value="6">Juni</option>
                                    <option value="7">Juli</option>
                                    <option value="8">Agustus</option>
                                    <option value="9">September</option>
                                    <option value="10">Oktober</option>
                                    <option value="11">November</option>
                                    <option value="12">Desember</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="edit_tanggal_mulai" class="form-label">Tanggal Mulai</label>
                                <input type="number" class="form-control" id="edit_tanggal_mulai" name="tanggal_mulai"
                                    min="1" max="31" placeholder="Masukkan tanggal mulai" />
                            </div>

                            <div class="mb-3">
                                <label for="edit_tanggal_selesai" class="form-label">Tanggal Selesai</label>
                                <input type="number" class="form-control" id="edit_tanggal_selesai"
                                    name="tanggal_selesai" min="1" max="31"
                                    placeholder="Masukkan tanggal selesai" />
                            </div>

                            <div class="mb-3">
                                <label for="edit_batas_akhir_upload" class="form-label">Batas Akhir Upload</label>
                                <input type="number" class="form-control" id="edit_batas_akhir_upload"
                                    name="batas_akhir_upload" min="1" max="31"
                                    placeholder="Masukkan batas akhir upload" />
                            </div>

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
