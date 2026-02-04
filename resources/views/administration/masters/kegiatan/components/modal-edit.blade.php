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
                                <label class="form-label">Jenis Pelaporan</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input edit-type-toggle" type="radio"
                                            name="jenis_pelaporan" id="edit_jenisInsidentil" value="insidentil">
                                        <label class="form-check-label" for="edit_jenisInsidentil">Insidentil (Sekali)</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input edit-type-toggle" type="radio"
                                            name="jenis_pelaporan" id="edit_jenisRutin" value="rutin">
                                        <label class="form-check-label" for="edit_jenisRutin">Rutin (Berkala)</label>
                                    </div>
                                </div>
                            </div>

                            <!-- GROUP INSIDENTIL -->
                            <div id="edit_groupInsidentil">
                                <div class="mb-3">
                                    <label for="edit_bulan" class="form-label">Bulan Pelaksanaan</label>
                                    <select class="selectpicker form-control wide form-select-md" data-size="5"
                                        data-live-search="true" id="edit_bulan" name="bulan">
                                        <option value="">Pilih Bulan</option>
                                        @foreach($bulanList as $key => $val)
                                            <option value="{{ $key }}">{{ $val }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_tanggal_mulai_insidentil" class="form-label">Tanggal Mulai</label>
                                            <input type="number" class="form-control" id="edit_tanggal_mulai_insidentil"
                                                name="tanggal_mulai_insidentil" min="1" max="31" placeholder="Tgl Mulai" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="edit_tanggal_selesai_insidentil" class="form-label">Tanggal Selesai</label>
                                            <input type="number" class="form-control" id="edit_tanggal_selesai_insidentil"
                                                name="tanggal_selesai_insidentil" min="1" max="31" placeholder="Tgl Selesai" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- GROUP RUTIN -->
                            <div class="d-none" id="edit_groupRutin">
                                <div class="mb-3">
                                    <label for="edit_frekuensi_pelaporan" class="form-label">Frekuensi Pelaporan</label>
                                    <select class="selectpicker form-control wide form-select-md"
                                        id="edit_frekuensi_pelaporan" name="frekuensi_pelaporan">
                                        <option value="">Pilih Frekuensi</option>
                                        <option value="1">Setiap 1 Bulan (Bulanan)</option>
                                        <option value="2">Setiap 2 Bulan</option>
                                        <option value="3">Setiap 3 Bulan (Triwulan)</option>
                                        <option value="4">Setiap 4 Bulan (Caturwulan)</option>
                                        <option value="6">Setiap 6 Bulan (Semester)</option>
                                        <option value="12">Setiap 1 Tahun (Tahunan)</option>
                                    </select>
                                </div>

                                <!-- ROW 1: Bulan Mulai & Tanggal Mulai -->
                                <div class="row mb-3">
                                    <div class="col-md-7">
                                        <label for="edit_bulan_mulai" class="form-label small">Bulan Mulai</label>
                                        <select class="selectpicker form-control wide form-select-sm"
                                            id="edit_bulan_mulai" name="bulan_mulai" data-size="5">
                                            @foreach($bulanList as $key => $val)
                                                <option value="{{ $key }}">{{ $val }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label for="edit_tanggal_mulai_rutin" class="form-label small">Tanggal Mulai</label>
                                        <input type="number" class="form-control form-control-sm"
                                            id="edit_tanggal_mulai_rutin" name="tanggal_mulai_rutin" min="1" max="31"
                                            placeholder="Tgl" />
                                    </div>
                                </div>

                                <!-- ROW 2: Bulan Selesai & Tanggal Selesai -->
                                <div class="row mb-3">
                                    <div class="col-md-7">
                                        <label for="edit_bulan_selesai" class="form-label small">Bulan Selesai</label>
                                        <select class="selectpicker form-control wide form-select-sm"
                                            id="edit_bulan_selesai" name="bulan_selesai" data-size="5">
                                            @foreach($bulanList as $key => $val)
                                                <option value="{{ $key }}">{{ $val }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-5">
                                        <label for="edit_tanggal_selesai_rutin" class="form-label small">Tanggal Selesai</label>
                                        <input type="number" class="form-control form-control-sm"
                                            id="edit_tanggal_selesai_rutin" name="tanggal_selesai_rutin" min="1" max="31"
                                            placeholder="Tgl" />
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="edit_batas_akhir_upload" class="form-label">Batas Akhir Upload</label>
                                <input type="number" class="form-control" id="edit_batas_akhir_upload"
                                    name="batas_akhir_upload" min="1" max="31" placeholder="Masukkan batas akhir upload" />
                                <div class="form-text text-muted">Jumlah hari setelah tanggal selesai.</div>
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