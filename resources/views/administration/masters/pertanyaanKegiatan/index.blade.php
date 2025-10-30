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
                <h4 class="text-dark fw-semibold mb-0">Pertanyaan Kegiatan</h4>
            </div>

            <div class="row">
                <div class="card">
                    <!-- Header -->
                    <div class="card-header d-sm-flex d-block border-0 pb-0 flex-wrap">
                        <div class="pr-3 me-auto mb-sm-0 mb-3">
                            <h4 class="fs-20 text-black mb-1">List Pertanyaan Kegiatan</h4>
                            <span class="fs-12 text-muted">Kelola daftar pertanyaan berdasarkan kegiatan dan status.</span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <!-- Filter status -->
                            <div>
                                <select id="filter_status" class="selectpicker form-control wide form-select-md"
                                    data-live-search="false" title="Filter status" required>
                                    <option value="">Semua</option>
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Tidak aktif</option>
                                </select>
                            </div>
                            <!-- Filter kegiatan -->
                            <div>
                                <select id="filter_kegiatan" class="selectpicker form-control wide form-select-md"
                                    data-live-search="true" required aria-label="Filter kegiatan" data-size="5"
                                    placeholder="Pilih kegiatan">
                                    <option value="">Semua</option>
                                    @foreach ($kegiatanList as $item)
                                        <option value="{{ $item->id_kegiatan }}">
                                            {{ $item->tahun }} | {{ $item->kode_kegiatan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <!-- Tombol tambah -->
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#modalCreate" title="Tambah pertanyaan kegiatan baru">
                                <i class="las la-plus me-1"></i>Tambah
                            </button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="card-body">
                        <!-- Tombol Aksi Massal -->
                        <div class="row mb-3 gy-2">
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
                        </div>

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
                                        <th style="width: 10%;" class="align-middle">Aksi</th>
                                        <th style="width: 20%;" class="text-start align-middle">Kegiatan</th>
                                        <th style="width: 35%;" class="text-start align-middle">Pertanyaan</th>
                                        <th style="width: 10%;" class="text-center align-middle">Urutan</th>
                                        <th style="width: 10%;" class="text-center align-middle">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-800 fw-bolder fs-sm-8 fs-lg-6">
                                    <tr>
                                        <td colspan="6" class="text-center text-muted py-4">
                                            <i class="las la-folder-open fs-1 d-block mb-2"></i>
                                            Tidak ada data pertanyaan kegiatan yang tersedia.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Catatan -->
                        <div class="alert alert-primary mt-3">
                            <strong>Catatan:</strong> Fitur ini digunakan untuk mengelola pertanyaan yang berkaitan dengan
                            kegiatan.
                            Anda dapat menambahkan, mengubah, mengurutkan, atau mengaktifkan/nonaktifkan pertanyaan sesuai
                            kebutuhan.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    {{-- Include modals --}}
    @include('administration.masters.pertanyaanKegiatan.components.modal-create')
    @include('administration.masters.pertanyaanKegiatan.components.modal-edit')
    @include('administration.masters.pertanyaanKegiatan.components.modal-detail')
@endsection

@section('this-page-scripts')
    <script src="{{ asset('templates/assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('templates/administration/vendor/datatables/responsive/responsive.js') }}"></script>
    <script src="{{ asset('templates/assets/plugins/datatables/lodash.min.js') }}"></script>

    @include('administration.masters.pertanyaanKegiatan.scripts.datatable-init')
    @include('administration.masters.pertanyaanKegiatan.scripts.modal-create-handler')
    @include('administration.masters.pertanyaanKegiatan.scripts.modal-edit-handler')
    @include('administration.masters.pertanyaanKegiatan.scripts.actions-handler')
    {{-- @include('administration.masters.pertanyaanKegiatan.scripts.status-update-handler') --}}

@endsection
