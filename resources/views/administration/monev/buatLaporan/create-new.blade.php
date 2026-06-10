@extends('administration.layouts.app')

@section('title', 'Buat Laporan Kegiatan Baru | Sistem Pelaporan')
@section('meta-description', 'Halaman untuk membuat laporan kegiatan baru')

@section('this-page-style')
    <link href="https://cdn.jsdelivr.net/npm/dflip/css/dflip.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/dflip/css/themify-icons.min.css" rel="stylesheet">
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

                            <!-- Informasi Kegiatan (New Professional Layout) -->
                            <div class="mb-4 text-muted small d-flex align-items-center">
                                <i class="las la-map-marker me-1"></i>
                                <span id="detail_desa" class="fw-semibold me-2 fs-4">-</span> • <span id="detail_kecamatan"
                                    class="ms-2 fs-6">-</span>
                            </div>

                            <div class="border rounded mb-4">
                                <!-- Header & Basic Info -->
                                <div class="bg-white p-4 border-bottom rounded-top">
                                    <div class="d-flex justify-content-between align-items-start mb-4">
                                        <div class="flex-grow-1">
                                            <h6 class="text-uppercase text-muted fw-bold mb-2"
                                                style="font-size: 0.75rem; letter-spacing: 0.5px;">Informasi Kegiatan</h6>
                                            <h4 class="mb-0 fw-bold text-dark" id="detail_nama_kegiatan">-</h4>
                                        </div>
                                        <span class="badge bg-primary px-3 py-2 fs-6" id="detail_tahun">-</span>
                                    </div>

                                    <div class="row g-4">
                                        <div class="col-md-4">
                                            <div class="bg-light rounded p-3 h-100">
                                                <label class="text-muted small mb-1 d-block">Kode Kegiatan</label>
                                                <div class="fw-semibold text-dark" id="detail_kode_kegiatan">-</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="bg-light rounded p-3 h-100">
                                                <label class="text-muted small mb-1 d-block">Jenis Kegiatan</label>
                                                <div class="fw-semibold text-dark" id="detail_jenis_kegiatan">-</div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="bg-light rounded p-3 h-100">
                                                <label class="text-muted small mb-1 d-block">Periode Pelaporan</label>
                                                <div class="fw-semibold text-dark" id="detail_bulan">-</div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="bg-light rounded p-3">
                                                <label class="text-muted small mb-1 d-block">Dasar Hukum</label>
                                                <div class="fw-semibold text-dark" id="detail_dasar_hukum">-</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Timeline Section -->
                                <div class="bg-light p-4 rounded-bottom">
                                    <h6 class="text-uppercase text-primary fw-bold mb-3 d-flex align-items-center"
                                        style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                        <i class="las la-clock me-2 fs-5"></i> Timeline Pelaksanaan
                                    </h6>

                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center bg-white p-3 rounded border">
                                                <div class="bg-success bg-opacity-10 rounded p-2 me-3">
                                                    <i class="las la-play text-success fs-4"></i>
                                                </div>
                                                <div>
                                                    <label class="text-muted small mb-0 d-block">Tanggal Mulai</label>
                                                    <div class="fw-bold text-dark" id="detail_tanggal_mulai">-</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="d-flex align-items-center bg-white p-3 rounded border">
                                                <div class="bg-info bg-opacity-10 rounded p-2 me-3">
                                                    <i class="las la-stop text-info fs-4"></i>
                                                </div>
                                                <div>
                                                    <label class="text-muted small mb-0 d-block">Tanggal Selesai</label>
                                                    <div class="fw-bold text-dark" id="detail_tanggal_selesai">-</div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-4">
                                            <div
                                                class="d-flex align-items-center bg-white p-3 rounded border border-danger">
                                                <div class="bg-danger bg-opacity-10 rounded p-2 me-3">
                                                    <i class="las la-exclamation-triangle text-danger fs-4"></i>
                                                </div>
                                                <div>
                                                    <label class="text-muted small mb-0 d-block">Batas Upload</label>
                                                    <div class="fw-bold text-danger" id="detail_batas_upload">-</div>
                                                </div>
                                            </div>
                                        </div>
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
                                            <textarea class="form-control" id="catatan_laporan" name="catatan_laporan"
                                                rows="2" placeholder="Tambahkan catatan jika diperlukan..."></textarea>
                                        </div>
                                    </div>
                                </div>
                            </form>

                            <!-- Action Buttons -->
                            <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                                <a href="{{ route('administrator.monev.laporan.index') }}" class="btn btn-secondary">
                                    <i class="las la-arrow-left me-1"></i>Kembali ke Daftar
                                </a>
                                @if (auth()->user()->hasPermissionTo('monev.laporan.create'))
                                <button type="button" class="btn btn-primary" id="saveDraftBottom">
                                    <i class="las la-save me-1"></i>Simpan Draft
                                </button>
                                <button type="button" class="btn btn-success" id="submitLaporanBottom">
                                    <i class="las la-paper-plane me-1"></i>Submit Laporan
                                </button>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('this-page-scripts')
    <script src="https://cdn.jsdelivr.net/npm/dflip/js/dflip.min.js"></script>
    @include('administration.monev.buatLaporan.scripts.create-handler-new')
@endsection