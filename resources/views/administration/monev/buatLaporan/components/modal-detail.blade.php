<!-- Modal Detail Start -->
<div class="modal fade" id="modalDetail" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detail Laporan Kegiatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <!-- Informasi Utama -->
                    <div class="col-12 mb-4">
                        <h6 class="border-bottom pb-2 mb-3 text-primary">Informasi Umum</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <div class="row">
                                    <div class="col-5 fw-bold">Tahun Anggaran</div>
                                    <div class="col-7">: <span id="detail_tahun_anggaran"></span></div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="row">
                                    <div class="col-5 fw-bold">Periode</div>
                                    <div class="col-7">: <span id="detail_periode"></span></div>
                                </div>
                            </div>
                            {{-- <div class="col-md-6 mb-2">
                                <div class="row">
                                    <div class="col-5 fw-bold">Kecamatan</div>
                                    <div class="col-7">: <span id="detail_kecamatan"></span></div>
                                </div>
                            </div> --}}
                            <div class="col-md-6 mb-2">
                                <div class="row">
                                    <div class="col-5 fw-bold">Desa</div>
                                    <div class="col-7">: <span id="detail_desa"></span></div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="row">
                                    <div class="col-5 fw-bold">Status Laporan</div>
                                    <div class="col-7">: <span id="detail_status"></span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Kegiatan -->
                    <div class="col-12 mb-4">
                        <h6 class="border-bottom pb-2 mb-3 text-primary">Informasi Kegiatan</h6>
                        <div class="row">
                            <div class="col-12 mb-2">
                                <div class="row">
                                    <div class="col-3 fw-bold">Jenis Kegiatan</div>
                                    <div class="col-9">: <span id="detail_jenis_kegiatan"></span></div>
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <div class="row">
                                    <div class="col-3 fw-bold">Kode Kegiatan</div>
                                    <div class="col-9">: <span id="detail_kode_kegiatan"></span></div>
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <div class="row">
                                    <div class="col-3 fw-bold">Nama Kegiatan</div>
                                    <div class="col-9">: <span id="detail_nama_kegiatan"></span></div>
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <div class="row">
                                    <div class="col-3 fw-bold">Dasar Hukum</div>
                                    <div class="col-9">: <span id="detail_dasar_hukum"></span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline -->
                    <div class="col-12 mb-4">
                        <h6 class="border-bottom pb-2 mb-3 text-primary">Timeline</h6>
                        <div class="row">
                            <div class="col-md-4 mb-2">
                                <div class="card bg-light border-0">
                                    <div class="card-body text-center p-2">
                                        <small class="text-muted d-block">Tanggal Mulai</small>
                                        <strong id="detail_tanggal_mulai" class="text-primary">-</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="card bg-light border-0">
                                    <div class="card-body text-center p-2">
                                        <small class="text-muted d-block">Tanggal Selesai</small>
                                        <strong id="detail_tanggal_selesai" class="text-primary">-</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="card bg-light border-0">
                                    <div class="card-body text-center p-2">
                                        <small class="text-muted d-block">Batas Akhir Upload</small>
                                        <strong id="detail_batas_upload" class="text-danger">-</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="card bg-light border-0">
                                    <div class="card-body text-center p-2">
                                        <small class="text-muted d-block">Tanggal Target</small>
                                        <strong id="detail_tanggal_target" class="text-warning">-</strong>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="card bg-light border-0">
                                    <div class="card-body text-center p-2">
                                        <small class="text-muted d-block">Status Timeline</small>
                                        <strong id="detail_status_timeline" class="text-info">-</strong>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Informasi Submit & Approval -->
                    <div class="col-12 mb-4">
                        <h6 class="border-bottom pb-2 mb-3 text-primary">Status Laporan</h6>
                        <div class="row">
                            <div class="col-md-6 mb-2">
                                <div class="row">
                                    <div class="col-5 fw-bold">Tanggal Submit</div>
                                    <div class="col-7">: <span id="detail_tanggal_submit"></span></div>
                                </div>
                            </div>
                            <div class="col-md-6 mb-2">
                                <div class="row">
                                    <div class="col-5 fw-bold">Tanggal Approve</div>
                                    <div class="col-7">: <span id="detail_tanggal_approve"></span></div>
                                </div>
                            </div>
                            <div class="col-12 mb-2">
                                <div class="row">
                                    <div class="col-5 fw-bold">Catatan Approval</div>
                                    <div class="col-7">: <span id="detail_catatan_approval"></span></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Daftar Pertanyaan -->
                    <div class="col-12">
                        <h6 class="border-bottom pb-2 mb-3 text-primary">Daftar Pertanyaan</h6>
                        <div id="detail_list_pertanyaan" class="bg-light p-3 rounded">
                            <div class="text-center text-muted">
                                <i class="las la-question-circle fs-2 d-block mb-2"></i>
                                Memuat daftar pertanyaan...
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                <button type="button" class="btn btn-primary btn-laporkan" style="display: none;">
                    <i class="las la-edit me-1"></i>Laporkan
                </button>
            </div>
        </div>
    </div>
</div>
<!-- Modal Detail End -->
