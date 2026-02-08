@extends('administration.layouts.app')

@section('title', 'Edit Laporan Kegiatan | Sistem Pelaporan')
@section('meta-description', 'Halaman untuk mengedit laporan kegiatan')

@section('this-page-style')
    <style>
        /* Compact styles untuk edit form */
        .info-label {
            font-size: 0.7rem;
            color: #6c757d;
            margin-bottom: 0.15rem;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .info-value {
            font-weight: 600;
            color: #333;
            font-size: 0.85rem;
        }

        .question-section {
            border-left: 3px solid #28a745;
            padding-left: 12px;
            margin-bottom: 15px;
        }

        .requirement-item {
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 12px;
            background-color: #f8f9fa;
            margin-bottom: 12px;
        }

        .card-header-compact {
            padding: 0.75rem 1rem;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .card-body-compact {
            padding: 1rem;
        }

        .section-header {
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            font-weight: 700;
            color: #495057;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #e9ecef;
        }

        .progress {
            height: 6px;
        }

        /* Compact alert */
        .alert-compact {
            padding: 0.75rem 1rem;
            margin-bottom: 1rem;
        }

        /* File input group */
        .file-input-group {
            display: flex;
            gap: 0.5rem;
            align-items: center;
        }

        .file-input-group .form-control {
            flex: 1;
        }

        /* Badge sizing */
        .badge-sm {
            font-size: 0.7rem;
            padding: 0.25rem 0.5rem;
        }
    </style>
@endsection

@section('content')
    <div class="content-body default-height">
        <div class="container-fluid">
            <!-- Header Halaman -->
            <div class="form-head mb-3 d-flex align-items-center gap-2">
                <small class="text-muted">Pelaporan {{ $id_laporan }} -</small>
                <h4 class="text-dark fw-semibold mb-0">Edit Laporan Kegiatan</h4>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <!-- Card Header Compact -->
                        <div class="card-header-compact border-0">
                            <div class="d-flex align-items-center justify-content-between w-100 flex-wrap gap-2">
                                <!-- Judul -->
                                <div>
                                    <h5 class="mb-1 fw-bold" style="font-size: 1rem;">Form Edit Laporan Kegiatan</h5>
                                    <span class="text-muted small">Edit laporan kegiatan yang sudah ada</span>
                                </div>

                                <!-- Tombol Aksi -->
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="saveDraftBtn">
                                        <i class="las la-save me-1"></i>Simpan Draft
                                    </button>
                                    <button type="button" class="btn btn-success btn-sm" id="submitLaporanBtn">
                                        <i class="las la-paper-plane me-1"></i>Submit Laporan
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card-body-compact">
                            <!-- Informasi Laporan - Compact Version -->
                            <div class="border rounded p-3 mb-3 bg-light">
                                <div class="section-header d-flex align-items-center">
                                    <i class="las la-info-circle me-1 fs-6"></i> Informasi Laporan
                                </div>

                                <div class="row g-2">
                                    <div class="col-md-3 col-6">
                                        <div class="bg-white rounded p-2">
                                            <label class="info-label mb-0">Dibuat Pada</label>
                                            <div class="info-value small" id="info_created_at">-</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="bg-white rounded p-2">
                                            <label class="info-label mb-0">Dibuat Oleh</label>
                                            <div class="info-value small" id="info_created_by">-</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="bg-white rounded p-2">
                                            <label class="info-label mb-0">Disunting Pada</label>
                                            <div class="info-value small" id="info_updated_at">-</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="bg-white rounded p-2">
                                            <label class="info-label mb-0">Status Saat Ini</label>
                                            <div class="info-value small" id="info_current_status">-</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <div class="bg-white rounded p-2">
                                            <label class="info-label mb-0">Disetujui Oleh</label>
                                            <div class="info-value small" id="info_approved_by">-</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <div class="bg-white rounded p-2">
                                            <label class="info-label mb-0">Disetujui Pada</label>
                                            <div class="info-value small" id="info_approved_at">-</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-12">
                                        <div class="bg-white rounded p-2">
                                            <label class="info-label mb-0">Catatan Approval</label>
                                            <div class="fw-normal small text-muted" id="info_approval_note">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Progress Indicator - Compact -->
                            <div class="d-flex align-items-center mb-3 p-2 bg-light rounded">
                                <div class="flex-grow-1 me-3">
                                    <div class="progress">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 0%"
                                            id="formProgress"></div>
                                    </div>
                                </div>
                                <small class="text-muted" id="progressText" style="min-width: 120px; font-size: 0.75rem;">0%
                                    selesai</small>
                            </div>

                            <!-- Alert Section -->
                            <div id="formAlerts"></div>

                            <!-- Informasi Kegiatan - Compact Version -->
                            <div class="mb-2 text-muted small d-flex align-items-center">
                                <i class="las la-map-marker me-1"></i>
                                <span id="detail_desa" class="fw-semibold me-2">-</span> •
                                <span id="detail_kecamatan" class="ms-2">-</span>
                            </div>

                            <div class="border rounded mb-3">
                                <!-- Header Kegiatan -->
                                <div class="bg-white p-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="flex-grow-1">
                                            <div class="section-header mb-2">Informasi Kegiatan</div>
                                            <h5 class="mb-0 fw-bold text-dark" id="detail_nama_kegiatan">-</h5>
                                        </div>
                                        <span class="badge bg-primary px-2 py-1" id="detail_tahun"
                                            style="font-size: 0.8rem;">-</span>
                                    </div>

                                    <div class="row g-2">
                                        <div class="col-md-4 col-6">
                                            <div class="bg-light rounded p-2">
                                                <label class="info-label mb-0">Kode Kegiatan</label>
                                                <div class="info-value" id="detail_kode_kegiatan">-</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-6">
                                            <div class="bg-light rounded p-2">
                                                <label class="info-label mb-0">Jenis Kegiatan</label>
                                                <div class="info-value" id="detail_jenis_kegiatan">-</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4 col-12">
                                            <div class="bg-light rounded p-2">
                                                <label class="info-label mb-0">Periode</label>
                                                <div class="info-value" id="detail_bulan">-</div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="bg-light rounded p-2">
                                                <label class="info-label mb-0">Dasar Hukum</label>
                                                <div class="info-value" id="detail_dasar_hukum">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Timeline Pelaksanaan -->
                                <div class="bg-light p-3">
                                    <div class="section-header d-flex align-items-center">
                                        <i class="las la-clock me-1 fs-6"></i> Timeline Pelaksanaan
                                    </div>

                                    <div class="row g-2">
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center bg-white p-2 rounded">
                                                <div class="bg-success bg-opacity-10 rounded p-2 me-2"
                                                    style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="las la-play text-success" style="font-size: 1.1rem;"></i>
                                                </div>
                                                <div>
                                                    <label class="info-label mb-0">Mulai</label>
                                                    <div class="info-value small" id="detail_tanggal_mulai">-</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center bg-white p-2 rounded">
                                                <div class="bg-info bg-opacity-10 rounded p-2 me-2"
                                                    style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="las la-stop text-info" style="font-size: 1.1rem;"></i>
                                                </div>
                                                <div>
                                                    <label class="info-label mb-0">Selesai</label>
                                                    <div class="info-value small" id="detail_tanggal_selesai">-</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center bg-white p-2 rounded border-danger">
                                                <div class="bg-danger bg-opacity-10 rounded p-2 me-2"
                                                    style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="las la-exclamation-triangle text-danger"
                                                        style="font-size: 1.1rem;"></i>
                                                </div>
                                                <div>
                                                    <label class="info-label mb-0">Deadline</label>
                                                    <div class="info-value small text-danger" id="detail_batas_upload">-
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Laporan -->
                            <form id="laporanForm">
                                <input type="hidden" id="laporan_id" name="laporan_id" value="{{ $id_laporan ?? '' }}">
                                <input type="hidden" id="desa_id" name="desa_id">
                                <input type="hidden" id="kegiatan_id" name="kegiatan_id">
                                <input type="hidden" id="tahun" name="tahun">
                                <input type="hidden" id="bulan" name="bulan">

                                <!-- Daftar Pertanyaan dan Persyaratan -->
                                <div id="questionsContainer">
                                    <div class="text-center py-4">
                                        <div class="spinner-border spinner-border-sm text-primary mb-2" role="status"></div>
                                        <p class="text-muted small mb-0">Memuat pertanyaan dan persyaratan...</p>
                                    </div>
                                </div>

                                <!-- Status dan Catatan - Hidden -->
                                <div class="row mt-3 d-none">
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <label class="form-label small">Status Laporan:</label>
                                            <div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="status"
                                                        id="statusDraft" value="draft">
                                                    <label class="form-check-label small" for="statusDraft">
                                                        <i class="las la-save me-1"></i>Simpan sebagai Draft
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="status"
                                                        id="statusSubmit" value="submitted">
                                                    <label class="form-check-label small" for="statusSubmit">
                                                        <i class="las la-paper-plane me-1"></i>Submit Laporan
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <label for="catatan_laporan" class="form-label small">Catatan
                                                (Opsional):</label>
                                            <textarea class="form-control form-control-sm" id="catatan_laporan"
                                                name="catatan_laporan" rows="2"
                                                placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <!-- Action Buttons - Compact -->
                            <div class="d-flex justify-content-between mt-3 pt-3 border-top">
                                <a href="{{ route('administrator.monev.laporan.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="las la-arrow-left me-1"></i>Kembali
                                </a>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary btn-sm" id="saveDraftBottom">
                                        <i class="las la-save me-1"></i>Simpan Draft
                                    </button>
                                    <button type="button" class="btn btn-success btn-sm" id="submitLaporanBottom">
                                        <i class="las la-paper-plane me-1"></i>Submit Laporan
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('this-page-scripts')
    @include('administration.monev.buatLaporan.scripts.edit-handler')
@endsection