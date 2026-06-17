@extends('administration.layouts.app')

@section('title', 'Pemetaan Wilayah Binaan | Admin Panel')
@section('meta-description', 'Halaman untuk mengelola data pemetaan wilayah binaan petugas kecamatan dan petugas inspektorat.')

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
                <small class="text-muted">Master Data -</small>
                <h4 class="text-dark fw-semibold mb-0">Wilayah Binaan</h4>
            </div>

            <!-- Section contain -->
            <div class="row">
                <div class="card">
                    <div class="card-header d-sm-flex d-block border-0 pb-0 flex-wrap">
                        <div class="pr-3 me-auto mb-sm-0 mb-3">
                            <h4 class="fs-20 text-black mb-1">Pemetaan Wilayah Binaan</h4>
                            <span class="fs-12 text-muted">Kelola data pemetaan wilayah binaan petugas kecamatan dan petugas inspektorat secara terpusat.</span>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Navigation Tabs (style-1) -->
                        <ul class="nav nav-tabs style-1 mb-4" id="myTab" role="tablist">
                            @if($scope === 'inspektorat')
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="inspektorat-tab" data-bs-toggle="tab" data-bs-target="#inspektorat" type="button" role="tab" aria-controls="inspektorat" aria-selected="true">Binaan Inspektorat (Kecamatan)</button>
                            </li>
                            @endif
                            <li class="nav-item" role="presentation">
                                <button class="nav-link {{ $scope === 'kecamatan' ? 'active' : '' }}" id="kecamatan-tab" data-bs-toggle="tab" data-bs-target="#kecamatan" type="button" role="tab" aria-controls="kecamatan" aria-selected="{{ $scope === 'kecamatan' ? 'true' : 'false' }}">Binaan Kecamatan (Desa)</button>
                            </li>
                        </ul>

                        <div class="tab-content" id="myTabContent">
                            @if($scope === 'inspektorat')
                            <!-- Tab 1: Inspektorat -->
                            <div class="tab-pane fade show active" id="inspektorat" role="tabpanel" aria-labelledby="inspektorat-tab">
                                <div class="table-responsive">
                                    <table id="table-inspektorat" class="table table-sm align-middle table-striped gs-0 gy-2 nowrap" style="width:100%;">
                                        <thead>
                                            <tr class="text-center text-muted text-uppercase">
                                                <th style="width: 5%;">No</th>
                                                <th style="width: 35%;" class="text-start">Petugas</th>
                                                <th style="width: 20%;" class="text-start">Jabatan</th>
                                                <th style="width: 30%;">Jumlah Kecamatan Binaan</th>
                                                <th style="width: 10%;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-800 fw-bolder fs-sm-8 fs-lg-6">
                                            {{-- Data dimuat via Ajax --}}
                                            <tr>
                                                <td colspan="5" class="text-center text-muted py-4">
                                                    <i class="las la-folder-open fs-1 d-block mb-2"></i>
                                                    Tidak ada data wilayah binaan inspektorat yang tersedia.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            @endif

                            <!-- Tab 2: Kecamatan -->
                            <div class="tab-pane fade {{ $scope === 'kecamatan' ? 'show active' : '' }}" id="kecamatan" role="tabpanel" aria-labelledby="kecamatan-tab">
                                <!-- Filter Kecamatan -->
                                @if($scope === 'inspektorat')
                                <div class="row mb-4 gy-2 align-items-end">
                                    <div class="col-md-4">
                                        <label for="filter_kecamatan" class="form-label">Filter Kecamatan</label>
                                        <select id="filter_kecamatan" class="selectpicker form-control wide" data-live-search="true" title="Filter Kecamatan">
                                            <option value="">Semua Kecamatan</option>
                                            @foreach($kecamatanList as $kec)
                                                <option value="{{ $kec->id_kecamatan }}">{{ $kec->nama_kecamatan }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-2">
                                        <button id="btnResetFilters" class="btn btn-outline-secondary btn-sm w-100">
                                            <i class="las la-sync"></i> Reset Filter
                                        </button>
                                    </div>
                                </div>
                                @endif

                                <div class="table-responsive">
                                    <table id="table-kecamatan" class="table table-sm align-middle table-striped gs-0 gy-2 nowrap" style="width:100%;">
                                        <thead>
                                            <tr class="text-center text-muted text-uppercase">
                                                <th style="width: 5%;">No</th>
                                                <th style="width: 30%;" class="text-start">Petugas</th>
                                                <th style="width: 15%;" class="text-start">Jabatan</th>
                                                <th style="width: 20%;" class="text-start">Kecamatan Asal</th>
                                                <th style="width: 20%;">Jumlah Desa Binaan</th>
                                                <th style="width: 10%;">Aksi</th>
                                            </tr>
                                        </thead>
                                        <tbody class="text-gray-800 fw-bolder fs-sm-8 fs-lg-6">
                                            {{-- Data dimuat via Ajax --}}
                                            <tr>
                                                <td colspan="6" class="text-center text-muted py-4">
                                                    <i class="las la-folder-open fs-1 d-block mb-2"></i>
                                                    Tidak ada data wilayah binaan kecamatan yang tersedia.
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-primary mt-4">
                            <strong>Catatan:</strong> Menu Pemetaan Wilayah Binaan digunakan untuk membatasi wilayah kerja masing-masing petugas kecamatan (desa binaan) dan petugas inspektorat (kecamatan binaan) agar sesuai dengan wilayah penugasannya.
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

    @include('administration.masters.wilayahBinaan.scripts.datatable-init')
@endsection
