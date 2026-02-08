@extends('administration.layouts.app')

@section('title', 'Petugas Desa | Sistem Pelaporan')
@section('meta-description', 'Halaman untuk mengelola data petugas Desa dalam sistem pelaporan.')

@section('this-page-style')
    <link href="{{ asset('templates/administration/vendor/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('templates/administration/vendor/datatables/responsive/responsive.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <div class="content-body default-height">
        <div class="container-fluid">
            <div class="form-head mb-4 d-flex align-items-center gap-2">
                <small class="text-muted">Master Data -</small>
                <h4 class="text-dark fw-semibold mb-0">Petugas Desa</h4>
            </div>
            <div class="row">
                <div class="card">
                    <div class="card-header d-sm-flex d-block border-0 pb-0 flex-wrap align-items-center">
                        <div class="me-auto mb-sm-0 mb-3">
                            <h4 class="fs-20 text-black mb-1">List Data Petugas</h4>
                            <span class="fs-12 text-muted">Kelola data petugas Desa dengan mudah.</span>
                        </div>

                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <div>
                                <select id="filter_status" class="selectpicker form-control wide form-select-md"
                                    data-live-search="false" aria-label="Filter Status">
                                    <option value="">Semua Status</option>
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Tidak Aktif</option>
                                </select>
                            </div>
                            <div>
                                <select id="filter_jabatan" class="selectpicker form-control wide form-select-md"
                                    data-live-search="false" aria-label="Filter Jabatan">
                                    <option value="">Semua Jabatan</option>
                                    @foreach ($jabatanDesa as $item)
                                        <option value="{{ $item->role_name }}">{{ $item->role_name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <a href="{{ route('administrator.master.petugas.desa.create') }}"
                                class="btn btn-sm btn-outline-primary" title="Tambah Petugas Baru">
                                <i class="las la-plus me-1"></i>Tambah
                            </a>
                        </div>
                    </div>

                    <!-- Card Body -->
                    <div class="card-body">

                        {{-- <div class="row mb-3 gy-2">
                            <div class="col-12 col-md d-flex flex-wrap gap-2">
                                <button class="btn-update-status btn btn-sm btn-primary" data-status="active">
                                    <i class="las la-check-circle me-1"></i>Aktifkan
                                </button>
                                <button class="btn-update-status btn btn-sm btn-danger" data-status="inactive">
                                    <i class="las la-times-circle me-1"></i>Nonaktifkan
                                </button>
                            </div>

                            <div class="col-12 col-md-auto d-flex flex-wrap gap-2 justify-content-md-end">
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                    data-bs-target="#modalImport">
                                    <i class="las la-file-excel me-1"></i>Import
                                </button>
                                <button class="btn btn-sm btn-outline-success">
                                    <i class="las la-file-excel me-1"></i>Export
                                </button>
                            </div>
                        </div> --}}
                        <div class="table-responsive">
                            <table id="example" class="table table-sm align-middle table-striped gs-0 gy-2 nowrap"
                                style="width:100%;">
                                <thead>
                                    <tr class="text-center text-muted text-uppercase">
                                        <th style="width: 5%;" class="align-middle">
                                            <span class="d-inline-flex align-items-center gap-1">
                                                <input type="checkbox" class="form-check-input m-0" id="selectAll" />
                                                <i class="bi bi-info-circle-fill text-primary" data-bs-toggle="tooltip"
                                                    title="Pilih beberapa data untuk melakukan aksi massal."></i>
                                            </span>
                                        </th>
                                        <th style="width: 10%;">Aksi</th>
                                        <th style="width: 15%;" class="text-start">NIP</th>
                                        <th style="width: 20%;" class="text-start">Nama Pegawai</th>
                                        <th style="width: 15%;" class="text-start">Telepon</th>
                                        <th style="width: 10%;" class="text-start">Jabatan</th>
                                        <th style="width: 15%;" class="text-start">Instansi</th>
                                        <th style="width: 10%;" class="text-start">Unit Kerja</th>
                                        <th style="width: 10%;" class="text-start">Akses Login</th>
                                        <th style="width: 5%;" class="text-start">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-800 fw-bolder fs-sm-8 fs-lg-6">
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="las la-folder-open fs-1 d-block mb-2"></i>
                                            Tidak ada petugas desa.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-primary mt-3">
                            <strong>Catatan:</strong> Data petugas digunakan untuk pengaturan hak akses (kepala &
                            petugas). Pastikan username dan password disimpan dengan aman.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('administration.masters.petugas.desa.components.detail')
@endsection

@section('this-page-scripts')
    <script src="{{ asset('templates/assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('templates/administration/vendor/datatables/responsive/responsive.js') }}"></script>

    @include('administration.masters.petugas.desa.scripts.datatable-init')
    @include('administration.masters.petugas.desa.scripts.action-handler')
@endsection