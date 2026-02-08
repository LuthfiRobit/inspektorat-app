@extends('administration.layouts.app')

@section('title', 'Monitoring | Detail Riwayat Laporan Kegiatan')
@section('meta-description',
    'Halaman monitoring untuk melihat dan memantau detail riwayat laporan kegiatan desa di
    wilayah kecamatan.')

@section('this-page-style')
    <link href="{{ asset('templates/administration/vendor/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('templates/administration/vendor/datatables/responsive/responsive.css') }}" rel="stylesheet" />
    <style>
        /* Timeline Styles */
        .timeline {
            position: relative;
            padding-left: 25px;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 15px;
        }

        .timeline-marker {
            position: absolute;
            left: -25px;
            top: 3px;
            width: 10px;
            height: 10px;
            border-radius: 50%;
            border: 2px solid #fff;
            box-shadow: 0 0 0 2px currentColor;
        }

        .timeline-content {
            padding-bottom: 8px;
            border-left: 2px solid #e9ecef;
            padding-left: 15px;
        }

        .timeline-item:last-child .timeline-content {
            border-left: 2px solid transparent;
        }

        /* Question & Document Styles */
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

        .revision-history-container {
            max-height: 180px;
            overflow-y: auto;
            background: #f8f9fa;
            padding: 8px;
            border-radius: 4px;
            margin-top: 8px;
        }

        .revision-item {
            background: white;
            border-radius: 4px;
            padding: 8px;
            margin-bottom: 6px;
            border-left: 3px solid #ffc107;
            font-size: 0.85rem;
        }

        .revision-item:last-child {
            margin-bottom: 0;
        }

        /* Compact Info Labels */
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

        /* Progress Bar */
        .progress {
            height: 6px;
        }

        /* File Version Badge */
        .file-version {
            font-size: 0.7rem;
            color: #6c757d;
        }

        /* Compact Card Headers */
        .card-header-compact {
            padding: 0.75rem 1rem;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .card-header-compact h5 {
            margin: 0;
            font-size: 0.95rem;
            font-weight: 600;
            color: #333;
        }

        /* Compact Card Body */
        .card-body-compact {
            padding: 1rem;
        }

        /* Section Headers */
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
    </style>
@endsection

@section('content')
    <div class="content-body default-height">
        <div class="container-fluid">
            <!-- Header Halaman -->
            <div class="form-head mb-3 d-flex align-items-center gap-2">
                <small class="text-muted">Monitoring -</small>
                <h4 class="text-dark fw-semibold mb-0">Detail Riwayat Laporan Kegiatan</h4>
            </div>

            <!-- Row 1: Informasi Utama & History -->
            <div class="row mb-3">
                <!-- Informasi Kegiatan & Laporan -->
                <div class="col-lg-8 col-12 mb-3">
                    <div class="card h-100">
                        <div class="card-body-compact">
                            <!-- Header Lokasi -->
                            <div class="mb-3 text-muted small d-flex align-items-center">
                                <i class="las la-map-marker me-1"></i>
                                <span id="detail_desa" class="fw-semibold me-2 fs-6">-</span> • 
                                <span id="detail_kecamatan" class="ms-2 fs-6">-</span>
                            </div>

                            <!-- Informasi Kegiatan -->
                            <div class="border rounded mb-3">
                                <!-- Header Kegiatan -->
                                <div class="bg-white p-3 border-bottom">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div class="flex-grow-1">
                                            <div class="section-header mb-2">Informasi Kegiatan</div>
                                            <h5 class="mb-0 fw-bold text-dark" id="detail_nama_kegiatan">-</h5>
                                        </div>
                                        <span class="badge bg-primary px-2 py-1" id="detail_tahun" style="font-size: 0.8rem;">-</span>
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
                                                <div class="bg-success bg-opacity-10 rounded p-2 me-2" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
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
                                                <div class="bg-info bg-opacity-10 rounded p-2 me-2" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
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
                                                <div class="bg-danger bg-opacity-10 rounded p-2 me-2" style="width: 35px; height: 35px; display: flex; align-items: center; justify-content: center;">
                                                    <i class="las la-exclamation-triangle text-danger" style="font-size: 1.1rem;"></i>
                                                </div>
                                                <div>
                                                    <label class="info-label mb-0">Deadline</label>
                                                    <div class="info-value small text-danger" id="detail_batas_upload">-</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Informasi Laporan - Compact Version -->
                            <div class="border rounded p-3 bg-light">
                                <div class="section-header d-flex align-items-center">
                                    <i class="las la-clipboard-check me-1 fs-6"></i> Informasi Laporan
                                </div>

                                <div class="row g-2">
                                    <div class="col-md-6 col-6">
                                        <div class="bg-white rounded p-2">
                                            <label class="info-label mb-0">Status Laporan</label>
                                            <div class="info-value">
                                                <span class="badge bg-success" id="info_status_badge" style="font-size: 0.75rem;">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-6">
                                        <div class="bg-white rounded p-2">
                                            <label class="info-label mb-0">Timeline</label>
                                            <div class="info-value">
                                                <span class="badge bg-primary" id="info_timeline_status" style="font-size: 0.75rem;">-</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <div class="bg-white rounded p-2">
                                            <label class="info-label mb-0">Target</label>
                                            <div class="info-value small" id="info_tanggal_target">-</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-6">
                                        <div class="bg-white rounded p-2">
                                            <label class="info-label mb-0">Submit</label>
                                            <div class="info-value small" id="info_tanggal_submit">-</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4 col-12">
                                        <div class="bg-white rounded p-2">
                                            <label class="info-label mb-0">Approve</label>
                                            <div class="info-value small" id="info_tanggal_approve">-</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="bg-white rounded p-2">
                                            <label class="info-label mb-0">Disetujui Oleh</label>
                                            <div class="info-value small" id="info_approved_by">-</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 col-12">
                                        <div class="bg-white rounded p-2">
                                            <label class="info-label mb-0">Dibuat Oleh</label>
                                            <div class="info-value small" id="info_created_by">-</div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="bg-white rounded p-2">
                                            <label class="info-label mb-0">Catatan Approval</label>
                                            <div class="fw-normal small text-muted" id="info_catatan_approval">-</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- History Laporan -->
                <div class="col-lg-4 col-12 mb-3">
                    <div class="card h-100">
                        <div class="card-header-compact border-0">
                            <h5 class="d-flex align-items-center mb-0">
                                <i class="las la-history me-2"></i>History Laporan
                            </h5>
                        </div>
                        <div class="card-body-compact">
                            <div class="timeline" id="timelineContainer">
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                                    <span class="text-muted small">Memuat history...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 2: Informasi Berkas Laporan -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header-compact">
                            <h5 class="d-flex align-items-center mb-0">
                                <i class="las la-file-alt me-2"></i>Informasi Berkas Laporan
                            </h5>
                        </div>
                        <div class="card-body-compact">
                            <!-- Progress Completeness -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="info-label mb-0">Kelengkapan Dokumen:</div>
                                    <span class="badge bg-success" id="completeness_badge" style="font-size: 0.75rem;">0% Lengkap</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-success" id="completeness_progress" role="progressbar" style="width: 0%;"></div>
                                </div>
                            </div>

                            <!-- Daftar Pertanyaan dan Dokumen -->
                            <div id="questionsContainer">
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                                    <span class="text-muted small">Memuat data berkas...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 3: Tombol Aksi -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body-compact">
                            <div class="d-flex justify-content-between flex-wrap gap-2">
                                <a href="{{ route('administrator.monitoring.riwayat.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="las la-arrow-left me-1"></i>Kembali
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('this-page-scripts')
    <script src="{{ asset('templates/assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('templates/administration/vendor/datatables/responsive/responsive.js') }}"></script>
    <script src="{{ asset('templates/assets/plugins/datatables/lodash.min.js') }}"></script>
    @include('administration.monitoring.riwayat.scripts.detail-handler')
@endsection