@extends('administration.layouts.app')

@section('title', 'Dashboard Sistem Informasi Monitoring Desa | Inspektorat Kabupaten Probolinggo')
@section('meta-description',
    'Halaman dashboard Sistem Informasi Monitoring Desa Inspektorat Kabupaten Probolinggo.
    Menyajikan ringkasan data dan pemantauan kegiatan desa secara real-time untuk mendukung pengawasan dan evaluasi.')

@section('this-page-style')
    <link href="{{ asset('templates/administration/vendor/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('templates/administration/vendor/datatables/responsive/responsive.css') }}" rel="stylesheet" />
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
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row g-3">

                                <!-- Tahun Anggaran -->
                                <div class="col-md-2">
                                    <label class="form-label">Tahun Anggaran</label>
                                    <select class="selectpicker form-control wide form-select-md" id="filter_tahun"
                                        data-live-search="true" data-size="5" title="Pilih Tahun">

                                        <option value="">Semua Tahun</option>
                                        @foreach ($tahunAnggaranList as $tahun)
                                            <option value="{{ $tahun->id_tahun_anggaran }}"
                                                data-tahun="{{ $tahun->tahun }}">
                                                {{ $tahun->tahun }} @if ($tahun->status === 'aktif')
                                                    (Aktif)
                                                @endif
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Periode Bulan -->
                                <div class="col-md-2">
                                    <label class="form-label">Periode Bulan</label>
                                    <select class="selectpicker form-control wide form-select-md" id="filter_bulan"
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
                                    <select class="selectpicker form-control wide form-select-md" id="filter_jenis"
                                        data-live-search="true" data-size="5" title="Pilih Jenis Kegiatan">
                                        <option value="">Semua Jenis</option>
                                    </select>
                                </div>

                                <!-- Filter Kecamatan -->
                                <div class="col-md-3" id="filter_kecamatan-container">
                                    <label class="form-label">Kecamatan</label>
                                    <select class="selectpicker form-control wide form-select-md" id="filter_kecamatan"
                                        data-live-search="true" data-size="5" title="Pilih Kecamatan">
                                        <option value="">Semua Kecamatan</option>
                                        @foreach ($kecamatanList as $item)
                                            <option value="{{ $item->id_kecamatan }}">
                                                {{ $item->nama_kecamatan }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Tombol Reset (Icon + Tooltip) -->
                                <div class="col-md-1 d-flex align-items-end">
                                    <button id="reset_filter" class="btn btn-light btn-sm border" data-bs-toggle="tooltip"
                                        data-bs-placement="top" title="Reset Filter">
                                        <i class="bi bi-arrow-counterclockwise"></i>
                                    </button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row g-2">
                <!-- Total Kecamatan -->
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <div class="media align-items-center bgl-primary rounded p-2 h-100">
                        <span class="bg-white p-3 me-3 rounded d-flex justify-content-center align-items-center"
                            style="width: 50px; height: 50px">
                            <i class="fas fa-map-marked-alt" style="font-size: 20px; color: #0d6efd;"></i>
                        </span>
                        <div class="media-body">
                            <h5 id="total_kecamatan" class="fs-16 text-black font-w600 mb-0">0</h5>
                            <span class="fs-14">Total Kecamatan</span>
                        </div>
                    </div>
                </div>

                <!-- Total Desa -->
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <div class="media align-items-center bgl-info rounded p-2 h-100">
                        <span class="bg-white p-3 me-3 rounded d-flex justify-content-center align-items-center"
                            style="width: 50px; height: 50px">
                            <i class="fas fa-home" style="font-size: 20px; color: #0dcaf0;"></i>
                        </span>
                        <div class="media-body">
                            <h5 id="total_desa" class="fs-16 text-black font-w600 mb-0">0</h5>
                            <span class="fs-14">Total Desa</span>
                        </div>
                    </div>
                </div>

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

                <!-- Total Laporan Disetujui -->
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <div class="media align-items-center bgl-success rounded p-2 h-100">
                        <span class="bg-white p-3 me-3 rounded d-flex justify-content-center align-items-center"
                            style="width: 50px; height: 50px">
                            <i class="fas fa-check-circle" style="font-size: 20px; color: #28a745;"></i>
                        </span>
                        <div class="media-body">
                            <h5 id="total_laporan_approved" class="fs-16 text-black font-w600 mb-0">0</h5>
                            <span class="fs-14">Laporan Disetujui</span>
                        </div>
                    </div>
                </div>

                <!-- Total Laporan Direvisi -->
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <div class="media align-items-center bgl-warning rounded p-2 h-100">
                        <span class="bg-white p-3 me-3 rounded d-flex justify-content-center align-items-center"
                            style="width: 50px; height: 50px">
                            <i class="fas fa-edit" style="font-size: 20px; color: #ffc107;"></i>
                        </span>
                        <div class="media-body">
                            <h5 id="total_laporan_revisi" class="fs-16 text-black font-w600 mb-0">0</h5>
                            <span class="fs-14">Laporan Direvisi</span>
                        </div>
                    </div>
                </div>

                <!-- Total Laporan Perlu Approval -->
                <div class="col-sm-6 col-md-4 col-lg-2">
                    <div class="media align-items-center bgl-secondary rounded p-2 h-100">
                        <span class="bg-white p-3 me-3 rounded d-flex justify-content-center align-items-center"
                            style="width: 50px; height: 50px">
                            <i class="fas fa-hourglass-half" style="font-size: 20px; color: #6c757d;"></i>
                        </span>
                        <div class="media-body">
                            <h5 id="total_laporan_pending" class="fs-16 text-black font-w600 mb-0">0</h5>
                            <span class="fs-14">Perlu Approval</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="row mt-4">
                <!-- Laporan Butuh Approval -->
                <div class="col-sm-12 col-md-6 col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-semibold text-dark">Laporan Butuh Approval</h5>
                            <span class="badge bg-primary" id="count_butuh_approval">0</span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover align-middle mb-0"
                                    id="table-butuh-approval">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Desa / Kecamatan</th>
                                            <th scope="col">Kegiatan / Jenis</th>
                                            <th scope="col">Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Tidak ada laporan yang perlu
                                                approval</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Laporan Direvisi -->
                <div class="col-sm-12 col-md-6 col-lg-6">
                    <div class="card shadow-sm">
                        <div class="card-header py-3 d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-semibold text-dark">Laporan Direvisi</h5>
                            <span class="badge bg-warning text-dark" id="count_direvisi">0</span>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-hover align-middle mb-0"
                                    id="table-direvisi">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Desa / Kecamatan</th>
                                            <th scope="col">Kegiatan / Jenis</th>
                                            <th scope="col">Tanggal</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="4" class="text-center text-muted">Tidak ada laporan yang
                                                direvisi</td>
                                        </tr>
                                    </tbody>
                                </table>
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
    @include('administration.dashboard.scripts.index-handler')

@endsection
