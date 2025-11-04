@extends('administration.layouts.app')

@section('title', 'Monitoring | Detail Riwayat Laporan Kegiatan')
@section('meta-description',
    'Halaman monitoring untuk melihat dan memantau detail riwayat laporan kegiatan desa di
    wilayah kecamatan.')

@section('this-page-style')
    <link href="{{ asset('templates/administration/vendor/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('templates/administration/vendor/datatables/responsive/responsive.css') }}" rel="stylesheet" />
    <style>
        .timeline {
            position: relative;
            padding-left: 30px;
        }

        .timeline-item {
            position: relative;
            margin-bottom: 20px;
        }

        .timeline-marker {
            position: absolute;
            left: -30px;
            top: 5px;
            width: 12px;
            height: 12px;
            border-radius: 50%;
            border: 3px solid #fff;
        }

        .timeline-content {
            padding-bottom: 10px;
            border-left: 2px solid #e9ecef;
            padding-left: 20px;
        }

        .timeline-item:last-child .timeline-content {
            border-left: 2px solid transparent;
        }

        .document-history .history-item {
            background-color: #f8f9fa;
            transition: background-color 0.2s;
        }

        .document-history .history-item:hover {
            background-color: #e9ecef;
        }

        .question-section {
            border-left: 4px solid #28a745;
            padding-left: 15px;
            margin-bottom: 20px;
        }

        .requirement-item {
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            background-color: #f8f9fa;
            margin-bottom: 15px;
        }

        .card-header h5 {
            color: #333;
            font-weight: 600;
        }

        .info-label {
            font-size: 0.875rem;
            color: #6c757d;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-weight: 600;
            color: #333;
        }

        .revision-history-container {
            max-height: 200px;
            overflow-y: auto;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
            margin-top: 10px;
        }

        .revision-item {
            background: white;
            border-radius: 4px;
            padding: 8px;
            margin-bottom: 8px;
            border-left: 3px solid #ffc107;
        }

        .revision-item:last-child {
            margin-bottom: 0;
        }

        .file-version {
            font-size: 0.75rem;
            color: #6c757d;
        }

        .progress {
            height: 8px;
        }
    </style>
@endsection

@section('content')
    <div class="content-body default-height">
        <div class="container-fluid">
            <!-- Header Halaman -->
            <div class="form-head mb-4 d-flex align-items-center gap-2">
                <small class="text-muted">Monitoring -</small>
                <h4 class="text-dark fw-semibold mb-0">Detail Riwayat Laporan Kegiatan</h4>
            </div>

            <!-- Row 1: Informasi Kegiatan & Laporan Gabungan & History Laporan -->
            <div class="row mb-4">
                <!-- Informasi Kegiatan & Laporan Gabungan -->
                <div class="col-lg-8 col-12 mb-4">
                    <div class="card h-100">
                        <div class="card-body">
                            <!-- Alert Informasi Kegiatan -->
                            <div class="alert alert-primary mb-4" role="alert">
                                <h5 class="alert-heading d-flex align-items-center">
                                    <i class="las la-tasks me-2"></i>Informasi Kegiatan
                                </h5>
                                <hr>
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <div class="info-label">Kecamatan:</div>
                                        <div class="info-value" id="info_kecamatan">-</div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="info-label">Desa:</div>
                                        <div class="info-value" id="info_desa">-</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <div class="info-label">Kegiatan:</div>
                                        <div class="info-value" id="info_kegiatan">-</div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="info-label">Periode:</div>
                                        <div class="info-value" id="info_periode">-</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <div class="info-label">Jenis Kegiatan:</div>
                                        <div class="info-value" id="info_jenis_kegiatan">-</div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="info-label">Batas Upload:</div>
                                        <div class="info-value" id="info_batas_upload">-</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <div class="info-label">Kode Kegiatan:</div>
                                        <div class="info-value" id="info_kode_kegiatan">-</div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="info-label">Tahun Anggaran:</div>
                                        <div class="info-value" id="info_tahun_anggaran">-</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <div class="info-label">Dasar Hukum:</div>
                                        <div class="info-value" id="info_dasar_hukum">-</div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="info-label">Rentang Waktu:</div>
                                        <div class="info-value" id="info_rentang_waktu">-</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Alert Informasi Laporan -->
                            <div class="alert alert-secondary" role="alert">
                                <h5 class="alert-heading d-flex align-items-center">
                                    <i class="las la-info-circle me-2"></i>Informasi Laporan
                                </h5>
                                <hr>
                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <div class="info-label">Status Laporan:</div>
                                        <div class="info-value">
                                            <span class="badge bg-success" id="info_status_badge">Disetujui</span>
                                        </div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="info-label">Timeline Status:</div>
                                        <div class="info-value">
                                            <span class="badge bg-primary" id="info_timeline_status">Tepat Waktu</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <div class="info-label">Tanggal Target:</div>
                                        <div class="info-value" id="info_tanggal_target">-</div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="info-label">Tanggal Submit:</div>
                                        <div class="info-value" id="info_tanggal_submit">-</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <div class="info-label">Tanggal Approve:</div>
                                        <div class="info-value" id="info_tanggal_approve">-</div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="info-label">Disetujui Oleh:</div>
                                        <div class="info-value" id="info_approved_by">-</div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <div class="info-label">Catatan Approval:</div>
                                        <div class="fw-normal fs-6 p-2 bg-light rounded" id="info_catatan_approval">
                                            -
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-6 mb-3">
                                        <div class="info-label">Dibuat Oleh:</div>
                                        <div class="info-value" id="info_created_by">-</div>
                                    </div>
                                    <div class="col-6 mb-3">
                                        <div class="info-label">Tanggal Dibuat:</div>
                                        <div class="info-value" id="info_created_at">-</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- History Laporan -->
                <div class="col-lg-4 col-12 mb-4">
                    <div class="card h-100">
                        <div class="card-header border-0 pb-0">
                            <h5 class="card-title mb-0">
                                <i class="las la-history me-2"></i>History Laporan
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline" id="timelineContainer">
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                                    <span class="text-muted">Memuat history...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Row 2: Informasi Berkas Laporan -->
            <div class="row mb-4">
                <!-- Informasi Berkas Laporan -->
                <div class="col-12 mb-4">
                    <div class="card h-100">
                        <div class="card-header border-0 pb-0">
                            <h5 class="card-title mb-0">
                                <i class="las la-file-alt me-2"></i>Informasi Berkas Laporan
                            </h5>
                        </div>
                        <div class="card-body">
                            <!-- Progress Completeness -->
                            <div class="mb-4">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="info-label">Kelengkapan Dokumen:</div>
                                    <span class="badge bg-success" id="completeness_badge">0% Lengkap</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar bg-success" id="completeness_progress" role="progressbar"
                                        style="width: 0%;"></div>
                                </div>
                            </div>

                            <!-- Daftar Pertanyaan dan Dokumen -->
                            <div id="questionsContainer">
                                <div class="text-center py-3">
                                    <div class="spinner-border spinner-border-sm text-primary me-2"></div>
                                    <span class="text-muted">Memuat data berkas...</span>
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
                        <div class="card-body">
                            <div class="d-flex justify-content-between flex-wrap gap-2">
                                <a href="{{ route('administrator.monitoring.riwayat.index') }}" class="btn btn-secondary">
                                    <i class="las la-arrow-left me-1"></i>Kembali ke Riwayat
                                </a>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-primary" id="downloadLaporanBtn">
                                        <i class="las la-download me-1"></i>Download Laporan
                                    </button>
                                    <button type="button" class="btn btn-outline-success" id="printLaporanBtn">
                                        <i class="las la-print me-1"></i>Cetak Laporan
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
    <script src="{{ asset('templates/assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('templates/administration/vendor/datatables/responsive/responsive.js') }}"></script>
    <script src="{{ asset('templates/assets/plugins/datatables/lodash.min.js') }}"></script>
    @include('administration.monitoring.riwayat.scripts.detail-handler')
@endsection
