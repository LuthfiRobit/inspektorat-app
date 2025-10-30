@extends('administration.layouts.app')

@section('title', 'Buat Laporan Kegiatan Baru | Sistem Pelaporan')
@section('meta-description', 'Halaman untuk membuat laporan kegiatan baru')

@section('this-page-style')
@endsection

@section('content')
    <div class="content-body default-height">
        <div class="container-fluid">
            <div class="form-head mb-4 d-flex align-items-center gap-2">
                <small class="text-muted">Pelaporan -</small>
                <h4 class="text-dark fw-semibold mb-0">Buat Laporan Kegiatan Baru</h4>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header border-0 pb-0">
                            <div class="d-flex align-items-center justify-content-between w-100 flex-wrap">
                                <!-- Judul -->
                                <div class="mb-2 mb-md-0">
                                    <h4 class="fs-20 text-black mb-1">Form Laporan Kegiatan Baru</h4>
                                    <span class="fs-12 text-muted">Isi form berikut untuk membuat laporan kegiatan
                                        baru</span>
                                </div>

                                <!-- Tombol Aksi -->
                                <div class="d-flex gap-2 ms-auto d-none">
                                    <button type="button" class="btn btn-outline-primary">
                                        <i class="las la-save me-1"></i> Simpan Draft
                                    </button>
                                    <button type="button" class="btn btn-success">
                                        <i class="las la-paper-plane me-1"></i> Submit Laporan
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">

                            <!-- Progress Indicator -->
                            <div class="d-flex align-items-center mb-4">
                                <div class="flex-grow-1">
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-success" role="progressbar" style="width: 0%"
                                            id="formProgress"></div>
                                    </div>
                                </div>
                                <small class="text-muted ms-3" id="progressText">0% selesai (file terupload)</small>
                            </div>

                            <!-- Alert Section -->
                            <div id="formAlerts"></div>

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

                            <!-- Form Laporan -->
                            <form id="laporanForm">
                                <input type="hidden" id="desa_id" name="desa_id">
                                <input type="hidden" id="kegiatan_id" name="kegiatan_id">
                                <input type="hidden" id="tahun" name="tahun">
                                <input type="hidden" id="bulan" name="bulan">

                                <!-- Daftar Pertanyaan dan Persyaratan -->
                                <div id="questionsContainer">
                                    <div class="text-center py-5">
                                        <div class="spinner-border text-primary mb-3" role="status"></div>
                                        <p class="text-muted">Memuat pertanyaan dan persyaratan...</p>
                                    </div>
                                </div>

                                <!-- Status dan Catatan -->
                                <div class="row mt-4 d-none">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Status Laporan:</label>
                                            <div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="status"
                                                        id="statusDraft" value="draft" checked>
                                                    <label class="form-check-label" for="statusDraft">
                                                        <i class="las la-save me-1"></i>Simpan sebagai Draft
                                                    </label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="status"
                                                        id="statusSubmit" value="submitted">
                                                    <label class="form-check-label" for="statusSubmit">
                                                        <i class="las la-paper-plane me-1"></i>Submit Laporan
                                                    </label>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6 d-none">
                                        <div class="mb-3">
                                            <label for="catatan_laporan" class="form-label">Catatan (Opsional):</label>
                                            <textarea class="form-control" id="catatan_laporan" name="catatan_laporan" rows="2"
                                                placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between mt-4 pt-3 border-top">
                                <a href="{{ route('administrator.monev.laporan.index') }}" class="btn btn-secondary">
                                    <i class="las la-arrow-left me-1"></i>Kembali ke Daftar
                                </a>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-primary" id="saveDraftBottom">
                                        <i class="las la-save me-1"></i>Simpan Draft
                                    </button>
                                    <button type="button" class="btn btn-success" id="submitLaporanBottom">
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
    @include('administration.monev.buatLaporan.scripts.create-handler-new')
@endsection
