@extends('administration.layouts.app')

@section('title', 'Dashboard Sistem Informasi Monitoring Desa | Inspektorat Kabupaten Probolinggo')
@section('meta-description',
    'Halaman dashboard Sistem Informasi Monitoring Desa Inspektorat Kabupaten Probolinggo.
    Menyajikan ringkasan data dan pemantauan kegiatan desa secara real-time untuk mendukung pengawasan dan evaluasi.')

@section('this-page-style')
    <link href="{{ asset('templates/administration/vendor/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('templates/administration/vendor/datatables/responsive/responsive.css') }}" rel="stylesheet" />
    <link rel="stylesheet" href="{{ asset('templates/assets/plugins/apex-chart/apex-chart.css') }}">
@endsection

@section('content')
    <!-- Content body start -->
    <div class="content-body default-height">
        <div class="container-fluid">
            <!-- Section Heading -->
            <div class="form-head mb-4 d-flex align-items-center gap-2">
                <small class="text-muted">Dashboard -</small>
                <h4 class="text-dark fw-semibold mb-0">Sistem Informasi Monitoring Desa</h4>
            </div>

            <!-- Filter Section -->
            <div class="row mb-1">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- Tahun Anggaran -->
                                <div class="col-md-4">
                                    <label class="form-label">Tahun Anggaran</label>
                                    <select class="selectpicker form-control wide form-select-md" id="filter-tahun"
                                        data-live-search="true" data-size="5" title="Pilih Tahun">
                                        <option value="">Loading...</option>
                                    </select>
                                </div>

                                <!-- Periode Bulan -->
                                <div class="col-md-4">
                                    <label class="form-label">Periode Bulan</label>
                                    <select class="selectpicker form-control wide form-select-md" id="filter-bulan"
                                        data-live-search="true" data-size="5" title="Pilih Bulan">
                                        <option value="">Semua Bulan</option>
                                        <option value="1">Januari</option>
                                        <option value="2">Februari</option>
                                        <option value="3">Maret</option>
                                        <option value="4">April</option>
                                        <option value="5">Mei</option>
                                        <option value="6">Juni</option>
                                        <option value="7">Juli</option>
                                        <option value="8">Agustus</option>
                                        <option value="9">September</option>
                                        <option value="10">Oktober</option>
                                        <option value="11">November</option>
                                        <option value="12">Desember</option>
                                    </select>
                                </div>

                                <!-- Jenis Kegiatan -->
                                <div class="col-md-4">
                                    <label class="form-label">Jenis Kegiatan</label>
                                    <select class="selectpicker form-control wide form-select-md" id="filter-jenis-kegiatan"
                                        data-live-search="true" data-size="5" title="Pilih Jenis Kegiatan">
                                        <option value="">Semua Jenis</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row g-2">
                <!-- Total Kegiatan -->
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <div class="media align-items-center bgl-success rounded p-2 h-100">
                        <span class="bg-white p-3 me-3 rounded d-flex justify-content-center align-items-center"
                            style="width: 50px; height: 50px">
                            <i class="fas fa-tasks" style="font-size: 20px; color: #198754;"></i>
                        </span>
                        <div class="media-body">
                            <h5 id="total_kegiatan" class="fs-16 text-black font-w600 mb-0">0</h5>
                            <span class="fs-14">Total Kegiatan</span>
                        </div>
                    </div>
                </div>

                <!-- Total Laporan -->
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <div class="media align-items-center bgl-info rounded p-2 h-100">
                        <span class="bg-white p-3 me-3 rounded d-flex justify-content-center align-items-center"
                            style="width:50px;height:50px">
                            <i class="fas fa-file-alt" style="font-size:20px;color:#0dcaf0;"></i>
                        </span>
                        <div class="media-body">
                            <h5 id="total_laporan" class="fs-16 text-black font-w600 mb-0">0</h5>
                            <span class="fs-14">Total Laporan</span>
                        </div>
                    </div>
                </div>

                <!-- Total Laporan Draft -->
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <div class="media align-items-center bgl-secondary rounded p-2 h-100">
                        <span class="bg-white p-3 me-3 rounded d-flex justify-content-center align-items-center"
                            style="width:50px;height:50px">
                            <i class="fas fa-pencil-alt" style="font-size:20px;color:#6c757d;"></i>
                        </span>
                        <div class="media-body">
                            <h5 id="total_laporan_draft" class="fs-16 text-black font-w600 mb-0">0</h5>
                            <span class="fs-14">Laporan Draft</span>
                        </div>
                    </div>
                </div>

                <!-- Laporan Disetujui -->
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <div class="media align-items-center bgl-success rounded p-2 h-100">
                        <span class="bg-white p-3 me-3 rounded d-flex justify-content-center align-items-center"
                            style="width:50px;height:50px">
                            <i class="fas fa-check-circle" style="font-size:20px;color:#198754;"></i>
                        </span>
                        <div class="media-body">
                            <h5 id="laporan_disetujui" class="fs-16 text-black font-w600 mb-0">0</h5>
                            <span class="fs-14">Disetujui</span>
                        </div>
                    </div>
                </div>

                <!-- Laporan Perlu Revisi -->
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <div class="media align-items-center bgl-warning rounded p-2 h-100">
                        <span class="bg-white p-3 me-3 rounded d-flex justify-content-center align-items-center"
                            style="width:50px;height:50px">
                            <i class="fas fa-edit" style="font-size:20px;color:#ffc107;"></i>
                        </span>
                        <div class="media-body">
                            <h5 id="laporan_revisi" class="fs-16 text-black font-w600 mb-0">0</h5>
                            <span class="fs-14">Perlu Revisi</span>
                        </div>
                    </div>
                </div>

                <!-- Laporan Menunggu Approval -->
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <div class="media align-items-center bgl-primary rounded p-2 h-100">
                        <span class="bg-white p-3 me-3 rounded d-flex justify-content-center align-items-center"
                            style="width:50px;height:50px">
                            <i class="fas fa-hourglass-half" style="font-size:20px;color:#0d6efd;"></i>
                        </span>
                        <div class="media-body">
                            <h5 id="laporan_pending" class="fs-16 text-black font-w600 mb-0">0</h5>
                            <span class="fs-14">Menunggu Approval</span>
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
    <script src="{{ asset('templates/assets/plugins/apex-chart/apex-chart.js') }}"></script>
@endsection
