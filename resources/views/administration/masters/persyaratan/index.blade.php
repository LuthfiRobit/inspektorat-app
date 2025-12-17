@extends('administration.layouts.app')

@section('title', 'Kelola Jenis Kegiatan | Admin Panel')
@section('meta-description',
    'Halaman untuk mengelola data jenis kegiatan berdasarkan tahun anggaran. Termasuk tambah,
    edit, status, import, dan export.')

@section('this-page-style')
    <link href="{{ asset('templates/administration/vendor/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('templates/administration/vendor/datatables/responsive/responsive.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <div class="content-body default-height">
        <div class="container-fluid">
            <!-- Breadcrumb or Section Heading -->
            <div class="form-head mb-4 d-flex align-items-center gap-2">
                <small class="text-muted">Master Data -</small>
                <h4 class="text-dark fw-semibold mb-0">Persyaratan</h4>
            </div>

            <div class="row">
                <div class="card">
                    <!-- Header -->
                    <div class="card-header d-sm-flex d-block border-0 pb-0 flex-wrap">
                        <div class="pr-3 me-auto mb-sm-0 mb-3">
                            <h4 class="fs-20 text-black mb-1">List Dokumen Persyaratan </h4>
                            <span class="fs-12 text-muted">Kelola daftar dokumen persyaratan berdasarkan pertanyaan dan
                                status.</span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <!-- Tombol tambah -->
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#modalCreate" title="Tambah pertanyaan kegiatan baru">
                                <i class="las la-plus me-1"></i>Tambah
                            </button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="card-body">
                        <div class="row g-3 mb-4 align-items-end">

                            <!-- Filter Pertanyaan -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Pertanyaan</label>
                                <select id="filter_pertanyaan" class="selectpicker form-control wide form-select-md"
                                    data-live-search="true" data-size="5" title="Pilih Pertanyaan">
                                    <option value="">Semua Pertanyaan</option>
                                    @foreach ($pertanyaanList as $item)
                                        <option value="{{ $item->id_pertanyaan }}">{{ $item->pertanyaan }} | {{ $item->nama_kegiatan }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filter Tipe -->
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Tipe</label>
                                <select id="filter_tipe" class="selectpicker form-control wide form-select-md"
                                    data-live-search="false" title="Filter Tipe">
                                    <option value="">Semua</option>
                                    <option value="wajib">Wajib</option>
                                    <option value="tambahan">Tambahan</option>
                                </select>
                            </div>

                            <!-- Filter Status -->
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Status</label>
                                <select id="filter_status" class="selectpicker form-control wide form-select-md"
                                    data-live-search="false" title="Status" required>
                                    <option value="">Semua</option>
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Tidak aktif</option>
                                </select>
                            </div>

                            <!-- Reset Button -->
                            <div class="col-md-2 d-flex justify-content-end">
                                <button id="btnResetFilter" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-sync-alt me-1"></i> Reset
                                </button>
                            </div>

                        </div>

                        <!-- Tombol Aksi Massal -->
                        {{-- <div class="row mb-3 gy-2">
                            <div class="col-12 col-md d-flex flex-wrap gap-2">
                                <button class="btn-update-status btn btn-sm btn-primary" data-status="active"
                                    title="Aktifkan pertanyaan yang dipilih">
                                    <i class="las la-check-circle me-1"></i>Aktifkan
                                </button>
                                <button class="btn-update-status btn btn-sm btn-danger" data-status="inactive"
                                    title="Nonaktifkan pertanyaan yang dipilih">
                                    <i class="las la-times-circle me-1"></i>Nonaktifkan
                                </button>
                            </div>
                            <!-- Tombol Import/Export -->
                            <div class="col-12 col-md-auto d-flex flex-wrap gap-2 justify-content-md-end">
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                    data-bs-target="#modalImport" title="Import data dari file Excel">
                                    <i class="las la-file-excel me-1"></i>Import
                                </button>
                                <button class="btn btn-sm btn-outline-success" title="Export data ke file Excel">
                                    <i class="las la-file-excel me-1"></i>Export
                                </button>
                            </div>
                        </div> --}}

                        <!-- Tabel Data -->
                        <div class="table-responsive">
                            <table id="example" class="table table-sm align-middle table-striped gs-0 gy-2 nowrap"
                                style="width:100%;">
                                <thead>
                                    <tr class="text-center text-muted text-uppercase">
                                        <th style="width: 5%;" class="align-middle">
                                            <span class="d-inline-flex align-items-center gap-1">
                                                <input type="checkbox" class="form-check-input m-0" id="selectAll" />
                                                <i class="bi bi-info-circle-fill text-primary"
                                                    title="Pilih beberapa data untuk aksi massal."></i>
                                            </span>
                                        </th>
                                        <th style="width: 10%;">Aksi</th>
                                        <th style="width: 25%;" class="text-start">Informasi Pertanyaan</th>
                                        <!-- pertanyaan_info -->
                                        <th style="width: 25%;" class="text-start">Persyaratan</th>
                                        <!-- nama_persyaratan -->
                                        <th style="width: 10%;" class="text-center">Tipe</th> <!-- tipe -->
                                        <th style="width: 10%;" class="text-center">Urutan</th>
                                        <th style="width: 10%;" class="text-center">Status</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>

                        <!-- Catatan -->
                        <div class="alert alert-primary mt-3">
                            <strong>Catatan:</strong> Fitur ini digunakan untuk mengelola persyaratan yang berkaitan dengan
                            pertanyaan.
                            Anda dapat menambahkan, mengubah, mengurutkan, atau mengaktifkan/nonaktifkan persyaratan sesuai
                            kebutuhan.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Include modals --}}
    @include('administration.masters.persyaratan.components.modal-create')
    @include('administration.masters.persyaratan.components.modal-edit')
    @include('administration.masters.persyaratan.components.modal-detail')
@endsection

@section('this-page-scripts')
    <script src="{{ asset('templates/assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('templates/administration/vendor/datatables/responsive/responsive.js') }}"></script>
    <script src="{{ asset('templates/assets/plugins/datatables/lodash.min.js') }}"></script>

    @include('administration.masters.persyaratan.scripts.datatable-init')
    @include('administration.masters.persyaratan.scripts.modal-create-handler')
    @include('administration.masters.persyaratan.scripts.actions-handler')
    @include('administration.masters.persyaratan.scripts.modal-edit-handler')
    {{-- @include('administration.masters.persyaratan.scripts.status-update-handler') --}}

@endsection
