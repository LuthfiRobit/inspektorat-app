<!-- Modal Detail Start -->
<div class="modal fade" id="modalDetail" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">

            <div class="modal-header bg-light border-bottom">
                <div>
                    <h5 class="modal-title mb-0">Detail Laporan Kegiatan</h5>
                    <div class="text-muted small mt-1">
                        <i class="las la-map-marker"></i>
                        <span id="detail_desa">-</span> • <span id="detail_kecamatan">-</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>

            <div class="modal-body p-0">

                <!-- Loading -->
                <div id="modalLoading" class="text-center py-5 d-none">
                    <div class="spinner-border text-primary mb-2" role="status"></div>
                    <div class="fw-semibold text-muted">Memuat detail...</div>
                </div>

                <!-- Content -->
                <div id="modalContent">

                    <!-- Kegiatan Info -->
                    <div class="border-bottom bg-white p-3">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div class="flex-grow-1">
                                <label class="text-muted small mb-1 d-block"
                                    style="font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.5px;">Nama
                                    Kegiatan</label>
                                <h6 class="mb-0 fw-bold" id="detail_nama_kegiatan">-</h6>
                            </div>
                            <span class="badge bg-primary ms-3 px-3 py-2" id="detail_tahun"
                                style="font-size: 0.85rem;">-</span>
                        </div>

                        <div class="row g-2 mt-2">
                            <div class="col-md-4 col-6">
                                <div class="bg-light rounded p-2">
                                    <label class="text-muted mb-0" style="font-size: 0.7rem;">Kode Kegiatan</label>
                                    <div class="fw-semibold small" id="detail_kode_kegiatan">-</div>
                                </div>
                            </div>
                            <div class="col-md-4 col-6">
                                <div class="bg-light rounded p-2">
                                    <label class="text-muted mb-0" style="font-size: 0.7rem;">Jenis Kegiatan</label>
                                    <div class="fw-semibold small" id="detail_jenis_kegiatan">-</div>
                                </div>
                            </div>
                            <div class="col-md-4 col-12">
                                <div class="bg-light rounded p-2">
                                    <label class="text-muted mb-0" style="font-size: 0.7rem;">Periode</label>
                                    <div class="fw-semibold small" id="detail_bulan">-</div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-2">
                            <div class="bg-light rounded p-2">
                                <label class="text-muted mb-0" style="font-size: 0.7rem;">Dasar Hukum</label>
                                <div class="fw-semibold small" id="detail_dasar_hukum">-</div>
                            </div>
                        </div>
                    </div>

                    <!-- Timeline & Status Combined -->
                    <div class="row g-0">

                        <!-- Timeline Pelaksanaan -->
                        <div class="col-md-6 border-end">
                            <div class="p-3 bg-white border-bottom">
                                <h6 class="text-uppercase text-primary mb-2 d-flex align-items-center"
                                    style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                    <i class="las la-calendar-check me-1" style="font-size: 1.2rem;"></i>
                                    Timeline Pelaksanaan
                                </h6>

                                <div class="d-flex align-items-center mb-2 pb-2 border-bottom">
                                    <div class="bg-success bg-opacity-10 rounded p-2 me-2"
                                        style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                        <i class="las la-play text-success" style="font-size: 1.3rem;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="text-muted mb-0" style="font-size: 0.7rem;">Tanggal Mulai</label>
                                        <div class="fw-bold small" id="detail_tanggal_mulai">-</div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center mb-2 pb-2 border-bottom">
                                    <div class="bg-info bg-opacity-10 rounded p-2 me-2"
                                        style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                        <i class="las la-stop text-info" style="font-size: 1.3rem;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="text-muted mb-0" style="font-size: 0.7rem;">Tanggal
                                            Selesai</label>
                                        <div class="fw-bold small" id="detail_tanggal_selesai">-</div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center">
                                    <div class="bg-danger bg-opacity-10 rounded p-2 me-2"
                                        style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                        <i class="las la-exclamation-triangle text-danger"
                                            style="font-size: 1.3rem;"></i>
                                    </div>
                                    <div class="flex-grow-1">
                                        <label class="text-muted mb-0" style="font-size: 0.7rem;">Batas Upload</label>
                                        <div class="fw-bold small text-danger" id="detail_batas_upload">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Status Pelaporan -->
                        <div class="col-md-6">
                            <div class="p-3 bg-white border-bottom">
                                <h6 class="text-uppercase text-primary mb-2 d-flex align-items-center"
                                    style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                    <i class="las la-clipboard-check me-1" style="font-size: 1.2rem;"></i>
                                    Status Pelaporan
                                </h6>

                                <div class="mb-2 pb-2 border-bottom">
                                    <label class="text-muted mb-1" style="font-size: 0.7rem;">Status Laporan</label>
                                    <div id="detail_status">-</div>
                                </div>

                                <div class="mb-2 pb-2 border-bottom" id="timeline_status_container"
                                    style="display: none;">
                                    <label class="text-muted mb-1" style="font-size: 0.7rem;">Status Timeline</label>
                                    <div class="d-flex align-items-center">
                                        <span id="detail_timeline_status">-</span>
                                        <span class="ms-2 text-muted small" id="detail_timeline_days"
                                            style="font-size: 0.75rem;"></span>
                                    </div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-6">
                                        <div class="bg-light rounded p-2">
                                            <label class="text-muted mb-0" style="font-size: 0.65rem;">Target</label>
                                            <div class="fw-semibold" style="font-size: 0.8rem;"
                                                id="detail_tanggal_target">-</div>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="bg-light rounded p-2">
                                            <label class="text-muted mb-0" style="font-size: 0.65rem;">Submit</label>
                                            <div class="fw-semibold" style="font-size: 0.8rem;"
                                                id="detail_tanggal_submit">-</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="bg-light rounded p-2">
                                            <label class="text-muted mb-0" style="font-size: 0.65rem;">Approve</label>
                                            <div class="fw-semibold" style="font-size: 0.8rem;"
                                                id="detail_tanggal_approve">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Catatan Approval -->
                    <div class="p-3 bg-light">
                        <label class="text-muted mb-1 d-flex align-items-center" style="font-size: 0.75rem;">
                            <i class="las la-comment-dots me-1"></i>
                            Catatan Approval
                        </label>
                        <div class="bg-white border rounded p-2">
                            <small class="text-muted" id="detail_catatan_approval" style="font-size: 0.85rem;">-</small>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer border-top bg-light">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="las la-times me-1"></i> Tutup
                </button>
                <button class="btn btn-primary btn-laporkan" style="display:none">
                    <i class="las la-edit me-1"></i> Laporkan
                </button>
            </div>

        </div>
    </div>
</div>
<!-- Modal Detail End -->