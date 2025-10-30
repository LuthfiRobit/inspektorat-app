@extends('administration.layouts.app')

@section('title', 'Kelola Tahun Anggaran | Admin Panel')
@section('meta-description',
    'Halaman untuk mengelola data tahun anggaran, termasuk tambah, edit, status, import dan
    export.')

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
                <h4 class="text-dark fw-semibold mb-0">Tahun Anggaran</h4>
            </div>

            <!-- Section contain -->
            <div class="row">
                <div class="card">
                    <div class="card-header d-sm-flex d-block border-0 pb-0 flex-wrap">
                        <div class="pr-3 me-auto mb-sm-0 mb-3">
                            <h4 class="fs-20 text-black mb-1">List Tahun Anggaran</h4>
                            <span class="fs-12 text-muted">Kelola data tahun anggaran secara terpusat dan efisien.</span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#modalCreate" data-bs-toggle="tooltip" title="Tambah Tahun Anggaran Baru">
                                <i class="las la-plus me-1"></i>Tambah
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        {{-- Filter Section --}}
                        <div class="row mb-3 gy-2 align-items-end">
                            <div class="col-md-3">
                                <label for="filter_status" class="form-label">Status</label>
                                <select id="filter_status" class="selectpicker form-control wide" title="Filter status">
                                    <option value="">Semua</option>
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Tidak Aktif</option>
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="filter_tahun_from" class="form-label">Dari Tahun</label>
                                <select id="filter_tahun_from" class="selectpicker form-control wide"
                                    data-live-search="true" data-size="5" title="Pilih tahun awal">
                                    <option value="">Semua</option>
                                    @foreach ($yearsRange as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label for="filter_tahun_to" class="form-label">Sampai Tahun</label>
                                <select id="filter_tahun_to" class="selectpicker form-control wide" data-live-search="true"
                                    data-size="5" title="Pilih tahun akhir">
                                    <option value="">Semua</option>
                                    @foreach ($yearsRange as $year)
                                        <option value="{{ $year }}">{{ $year }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-3">
                                <label class="form-label d-block">&nbsp;</label>
                                <button id="btnResetFilters" class="btn btn-outline-secondary btn-sm w-100">
                                    <i class="las la-sync"></i> Reset Filter
                                </button>
                            </div>
                        </div>

                        <!-- Aksi Tambahan -->
                        <div class="row mb-3 gy-2">
                            <div class="col-12 col-md d-flex flex-wrap gap-2">
                                <button class="btn-update-status btn btn-sm btn-primary" data-status="active"
                                    data-bs-toggle="tooltip" title="Aktifkan tahun anggaran yang dipilih">
                                    <i class="las la-check-circle me-1"></i>Aktifkan
                                </button>
                                <button class="btn-update-status btn btn-sm btn-danger" data-status="inactive"
                                    data-bs-toggle="tooltip" title="Nonaktifkan tahun anggaran yang dipilih">
                                    <i class="las la-times-circle me-1"></i>Nonaktifkan
                                </button>
                            </div>
                            <div class="col-12 col-md-auto d-flex flex-wrap gap-2 justify-content-md-end">
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                    data-bs-target="#modalImport" data-bs-toggle="tooltip"
                                    title="Import data dari file Excel">
                                    <i class="las la-file-excel me-1"></i>Import
                                </button>
                                <button class="btn btn-sm btn-outline-success" data-bs-toggle="tooltip"
                                    title="Export data ke file Excel">
                                    <i class="las la-file-excel me-1"></i>Export
                                </button>
                            </div>
                        </div>

                        <!-- Tabel -->
                        <div class="table-responsive">
                            <table id="example" class="table table-sm align-middle table-striped gs-0 gy-2 nowrap"
                                style="width:100%;">
                                <thead>
                                    <tr class="text-center text-muted text-uppercase">
                                        <th style="width: 5%;" class="align-middle">
                                            <span class="d-inline-flex align-items-center gap-1">
                                                <input type="checkbox" class="form-check-input m-0" id="selectAll" />
                                                <i class="bi bi-info-circle-fill text-primary" data-bs-toggle="tooltip"
                                                    title="Pilih beberapa data pada halaman ini untuk melakukan aksi massal."></i>
                                            </span>
                                        </th>
                                        <th style="width: 10%;" class="align-middle">Aksi</th>
                                        <th style="width: 15%;" class="text-start align-middle" scope="col">Tahun
                                            Anggaran
                                        </th>
                                        <th style="width: 15%;" class="text-start align-middle" scope="col">Jenis
                                            Kegiatan</th>
                                        <th style="width: 15%;" class="text-start align-middle" scope="col">Kegiatan
                                        </th>
                                        <th style="width: 10%;" class="text-start align-middle" scope="col">Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-800 fw-bolder fs-sm-8 fs-lg-6">
                                    {{-- Data akan dimuat via Ajax --}}
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            <i class="las la-folder-open fs-1 d-block mb-2"></i>
                                            Tidak ada data tahun anggaran yang tersedia.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-primary mt-3">
                            <strong>Catatan:</strong> Fitur ini digunakan untuk mengelola tahun anggaran. Anda dapat
                            menambahkan,
                            mengubah, menonaktifkan, atau mengaktifkan tahun anggaran sesuai kebutuhan sistem.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Include modals --}}
    @include('administration.masters.tahunAnggaran.components.modal-create')
    @include('administration.masters.tahunAnggaran.components.modal-edit')
    @include('administration.masters.tahunAnggaran.components.modal-detail')
@endsection

@section('this-page-scripts')
    <script src="{{ asset('templates/assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('templates/administration/vendor/datatables/responsive/responsive.js') }}"></script>
    <script src="{{ asset('templates/assets/plugins/datatables/lodash.min.js') }}"></script>

    @include('administration.masters.tahunAnggaran.scripts.datatable-init')
    @include('administration.masters.tahunAnggaran.scripts.actions-handler')
    @include('administration.masters.tahunAnggaran.scripts.modal-create-handler')
    @include('administration.masters.tahunAnggaran.scripts.modal-edit-handler')
    {{-- @include('administration.masters.tahunAnggaran.scripts.status-update-handler') --}}
@endsection
