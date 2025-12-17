<!-- Modal Detail Start -->
<div class="modal fade" id="modalDetail" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Detail Laporan Kegiatan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body">

                <!-- Loading -->
                <div id="modalLoading" class="text-center py-5 d-none">
                    <div class="spinner-border text-primary mb-3" role="status"></div>
                    <div class="fw-semibold text-muted">Memuat detail...</div>
                </div>

                <!-- Content -->
                <div id="modalContent">

                    <!-- SECTION: DESA -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom fw-bold fs-6 py-3">
                            <i class="las la-home me-1"></i> Informasi Desa
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <small class="text-muted d-block">Nama Desa</small>
                                    <div class="fw-semibold" id="detail_desa">-</div>
                                </div>
                                <div class="col-sm-6">
                                    <small class="text-muted d-block">Kecamatan</small>
                                    <div class="fw-semibold" id="detail_kecamatan">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- SECTION: KEGIATAN -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom fw-bold fs-6 py-3">
                            <i class="las la-tasks me-1"></i> Informasi Kegiatan
                        </div>

                        <div class="card-body">
                            <div class="row g-3">

                                <div class="col-sm-6">
                                    <small class="text-muted d-block">Tahun Anggaran</small>
                                    <div class="fw-semibold" id="detail_tahun">-</div>
                                </div>

                                <div class="col-sm-6">
                                    <small class="text-muted d-block">Periode</small>
                                    <div class="fw-semibold" id="detail_bulan">-</div>
                                </div>

                                <div class="col-sm-6">
                                    <small class="text-muted d-block">Jenis Kegiatan</small>
                                    <div class="fw-semibold" id="detail_jenis_kegiatan">-</div>
                                </div>

                                <div class="col-sm-6">
                                    <small class="text-muted d-block">Kode Kegiatan</small>
                                    <div class="fw-semibold" id="detail_kode_kegiatan">-</div>
                                </div>

                                <div class="col-12">
                                    <small class="text-muted d-block">Nama Kegiatan</small>
                                    <div class="fw-semibold" id="detail_nama_kegiatan">-</div>
                                </div>

                                <div class="col-12">
                                    <small class="text-muted d-block">Dasar Hukum</small>
                                    <div class="fw-semibold" id="detail_dasar_hukum">-</div>
                                </div>
                            </div>

                            <!-- Divider -->
                            <hr class="my-4">

                            <!-- TIMELINE -->
                            <h6 class="fw-bold mb-3">
                                <i class="las la-clock me-1"></i> Timeline
                            </h6>

                            <div class="row g-3 text-center">
                                <div class="col-sm-4">
                                    <div class="p-3 border rounded bg-light">
                                        <small class="text-muted d-block">Tanggal Mulai</small>
                                        <div class="fw-bold" id="detail_tanggal_mulai">-</div>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="p-3 border rounded bg-light">
                                        <small class="text-muted d-block">Tanggal Selesai</small>
                                        <div class="fw-bold" id="detail_tanggal_selesai">-</div>
                                    </div>
                                </div>

                                <div class="col-sm-4">
                                    <div class="p-3 border rounded bg-light">
                                        <small class="text-muted d-block">Batas Upload</small>
                                        <div class="fw-bold text-danger" id="detail_batas_upload">-</div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- SECTION: LAPORAN -->
                    <div class="card mb-4 border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom fw-bold fs-6 py-3">
                            <i class="las la-file-alt me-1"></i> Informasi Laporan
                        </div>

                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <small class="text-muted d-block">Status Laporan</small>
                                    <div class="fw-semibold" id="detail_status">-</div>
                                </div>

                                <div class="col-sm-6">
                                    <small class="text-muted d-block">Tanggal Target</small>
                                    <div class="fw-semibold" id="detail_tanggal_target">-</div>
                                </div>

                                <div class="col-sm-6">
                                    <small class="text-muted d-block">Tanggal Submit</small>
                                    <div class="fw-semibold" id="detail_tanggal_submit">-</div>
                                </div>

                                <div class="col-sm-6">
                                    <small class="text-muted d-block">Tanggal Approve</small>
                                    <div class="fw-semibold" id="detail_tanggal_approve">-</div>
                                </div>

                                <div class="col-12">
                                    <small class="text-muted d-block">Catatan Approval</small>
                                    <div class="fw-semibold" id="detail_catatan_approval">-</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>

                <button class="btn btn-primary btn-laporkan" style="display:none">
                    <i class="las la-edit me-1"></i> Laporkan
                </button>
            </div>

        </div>
    </div>
</div>
<!-- Modal Detail End -->