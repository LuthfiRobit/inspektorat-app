@extends('administration.layouts.app')

@section('title', 'Review Laporan Kegiatan | Sistem Pelaporan')
@section('meta-description', 'Halaman untuk mereview laporan kegiatan')

@section('this-page-style')
    <style>
        .requirement-item {
            border-radius: 8px;
            border-left: 4px solid #0d6efd;
        }

        .requirement-item .form-control-sm {
            font-size: 0.875rem;
            min-height: 80px;
        }

        .revision-history-container {
            max-height: 200px;
            overflow-y: auto;
            background: #f8f9fa;
            padding: 10px;
            border-radius: 4px;
        }

        .revision-item {
            background: white;
            border-radius: 4px;
            padding: 8px;
            margin-bottom: 8px;
        }

        .revision-item:last-child {
            margin-bottom: 0;
        }

        /* Status indicator styles */
        .text-info {
            color: #0dcaf0 !important;
        }

        .text-success {
            color: #198754 !important;
        }

        .text-warning {
            color: #ffc107 !important;
        }

        /* Radio button styling */
        .form-check-input:checked {
            background-color: #198754;
            border-color: #198754;
        }

        .form-check-input[value="revision"]:checked {
            background-color: #ffc107;
            border-color: #ffc107;
        }

        /* Tambahan untuk status badge */
        .badge.bg-success {
            background-color: #198754 !important;
        }

        .badge.bg-warning {
            background-color: #ffc107 !important;
            color: #000 !important;
        }

        /* Highlight untuk dokumen yang perlu revisi */
        .requirement-item .bg-warning {
            font-weight: bold;
        }

        /* Status text colors */
        .text-success {
            color: #198754 !important;
        }

        .text-warning {
            color: #ffc107 !important;
        }

        .text-info {
            color: #0dcaf0 !important;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .requirement-item .row>div {
                margin-bottom: 15px;
            }

            .requirement-item hr {
                margin: 10px 0;
            }
        }
    </style>
@endsection

@section('content')
    <div class="content-body default-height">
        <div class="container-fluid">
            <div class="form-head mb-4 d-flex align-items-center gap-2">
                <small class="text-muted">Review Laporan -</small>
                <h4 class="text-dark fw-semibold mb-0">Review Laporan Kegiatan</h4>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header border-0 pb-0">
                            <div class="d-flex align-items-center justify-content-between w-100 flex-wrap">
                                <!-- Judul -->
                                <div class="mb-2 mb-md-0">
                                    <h4 class="fs-20 text-black mb-1">Form Review Laporan Kegiatan</h4>
                                    <span class="fs-12 text-muted">Review dan berikan persetujuan pada laporan
                                        kegiatan</span>
                                </div>

                                <!-- Tombol Aksi -->
                                <div class="d-flex gap-2 ms-auto">
                                    <button type="button" class="btn btn-warning" id="requestRevisionBtn">
                                        <i class="las la-redo-alt me-1"></i>Minta Revisi
                                    </button>
                                    <button type="button" class="btn btn-success" id="approveLaporanBtn">
                                        <i class="las la-check-circle me-1"></i>Setujui Laporan
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <!-- Informasi Laporan -->
                            <div class="alert alert-primary">
                                <div class="row">
                                    <div class="col-md-4">
                                        <small>Dibuat Pada Tgl.:</small>
                                        <div class="fw-bold fs-6" id="info_created_at">-</div>
                                    </div>
                                    <div class="col-md-4">
                                        <small>Disubmit Pada Tgl.:</small>
                                        <div class="fw-bold fs-6" id="info_submitted_at">-</div>
                                    </div>
                                    <div class="col-md-4">
                                        <small>Status Saat Ini:</small>
                                        <div class="fw-bold fs-6" id="info_current_status">-</div>
                                    </div>
                                </div>

                                <hr>

                                <div class="row">
                                    <div class="col-md-6">
                                        <small>Dibuat Oleh:</small>
                                        <div class="fw-bold fs-6" id="info_created_by">-</div>
                                    </div>
                                    <div class="col-md-6">
                                        <small>Catatan Pelapor:</small>
                                        <div class="fw-normal fs-6" id="info_catatan_laporan"
                                            style="white-space: pre-wrap;">-</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Informasi Kegiatan -->
                            <div class="kegiatan-info mb-3 alert alert-success" id="kegiatanInfo">
                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <small>Desa:</small>
                                        <div class="fw-bold fs-6" id="info_desa">-</div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <small>Kegiatan:</small>
                                        <div class="fw-bold fs-6" id="info_kegiatan">-</div>
                                    </div>
                                    <div class="col-md-4 mb-2">
                                        <small>Periode:</small>
                                        <div class="fw-bold fs-6" id="info_periode">-</div>
                                    </div>
                                </div>

                                <hr>

                                <div class="row">
                                    <div class="col-md-4 mb-2">
                                        <small>Batas Upload:</small>
                                        <div class="fw-bold fs-6" id="info_batas_upload">-</div>
                                    </div>
                                    <div class="col-md-8 mb-2">
                                        <small>Dasar Hukum:</small>
                                        <div class="fw-bold fs-6" id="info_dasar_hukum">-</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Review -->
                            <form id="reviewForm">
                                <input type="hidden" id="laporan_id" name="laporan_id" value="{{ $id_laporan ?? '' }}">
                                <input type="hidden" id="desa_id" name="desa_id">
                                <input type="hidden" id="kegiatan_id" name="kegiatan_id">

                                <!-- Daftar Pertanyaan dan Persyaratan -->
                                <div id="questionsContainer">
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-primary mb-3" role="status"></div>
                                        <p class="text-muted">Memuat data laporan...</p>
                                    </div>
                                </div>

                                <!-- Status dan Catatan Review -->
                                <div class="row mt-4">
                                    <div class="col-md-6 d-none">
                                        <div class="mb-3">
                                            <label class="form-label">Status Review:</label>
                                            <div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="status"
                                                        id="statusRevision" value="revision" checked>
                                                    <label class="form-check-label" for="statusRevision">
                                                        <i class="las la-redo-alt me-1"></i>Minta Revisi
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="status"
                                                        id="statusApproved" value="approved">
                                                    <label class="form-check-label" for="statusApproved">
                                                        <i class="las la-check-circle me-1"></i>Setujui Laporan
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-12">
                                        <div class="mb-3">
                                            <label for="catatan_approval" class="form-label">Catatan Review:</label>
                                            <textarea class="form-control" id="catatan_approval" name="catatan_approval" rows="3"
                                                placeholder="Berikan catatan review untuk pelapor..."></textarea>
                                            <small class="form-text text-muted">Catatan ini akan dilihat oleh
                                                pelapor</small>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                                <a href="" class="btn btn-secondary">
                                    <i class="las la-arrow-left me-1"></i>Kembali ke Daftar
                                </a>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-warning" id="requestRevisionBottom">
                                        <i class="las la-redo-alt me-1"></i>Minta Revisi
                                    </button>
                                    <button type="button" class="btn btn-success" id="approveLaporanBottom">
                                        <i class="las la-check-circle me-1"></i>Setujui Laporan
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
    @include('administration.monev.reviewLaporan.scripts.review-handler')
@endsection
