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
            <div class="form-head mb-4 d-flex align-items-center gap-2">
                <small class="text-muted">Master Data -</small>
                <h4 class="text-dark fw-semibold mb-0">Kegiatan</h4>
            </div>

            <div class="row">
                <div class="card">
                    <div class="card-header d-sm-flex d-block border-0 pb-0 flex-wrap">
                        <div class="pr-3 me-auto mb-sm-0 mb-3">
                            <h4 class="fs-20 text-black mb-1">List Kegiatan</h4>
                            <span class="fs-12 text-muted">Kelola data kegiatan berdasarkan jenis kegiatan.</span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <div>
                                <select id="filter_status" class="selectpicker form-control wide form-select-md"
                                    data-live-search="false" title="Filter status" required>
                                    <option value="">Semua</option>
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Tidak aktif</option>
                                </select>
                            </div>
                            <div>
                                <select id="filter_tahun" class="selectpicker form-control wide form-select-md"
                                    data-live-search="true" required aria-label="Filter tahun anggaran" data-size="5"
                                    placeholder="Pilih tahun">
                                    <option value="">Semua</option>
                                    {{-- @foreach ($tahunAnggaranList as $item)
                                        <option value="{{ $item->id_tahun_anggaran }}">{{ $item->tahun }}</option>
                                    @endforeach --}}
                                </select>
                            </div>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#modalCreate" data-bs-toggle="tooltip" title="Tambah jenis kegiatan baru">
                                <i class="las la-plus me-1"></i>Tambah
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        {{-- <div class="row mb-3 gy-2">
                            <div class="col-12 col-md d-flex flex-wrap gap-2">
                                <button class="btn-update-status btn btn-sm btn-primary" data-status="active"
                                    data-bs-toggle="tooltip" title="Aktifkan data yang dipilih">
                                    <i class="las la-check-circle me-1"></i>Aktifkan
                                </button>
                                <button class="btn-update-status btn btn-sm btn-danger" data-status="inactive"
                                    data-bs-toggle="tooltip" title="Nonaktifkan data yang dipilih">
                                    <i class="las la-times-circle me-1"></i>Nonaktifkan
                                </button>
                            </div>
                            <div class="col-12 col-md-auto d-flex flex-wrap gap-2 justify-content-md-end">
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                    data-bs-target="#modalImport" data-bs-toggle="tooltip"
                                    title="Import data dari file Excel">
                                    <i class="las la-file-excel me-1"></i>Import
                                </button>
                                <button class="btn btn-sm btn-outline-success" title="Export data ke file Excel">
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
                                                    title="Pilih beberapa data pada halaman ini untuk melakukan aksi massal."></i>
                                            </span>
                                        </th>
                                        <th style="width: 10%;" class="align-middle">Aksi</th>
                                        <th style="width: 10%;" class="text-start align-middle">Tahun</th>
                                        <th style="width: 10%;" class="text-center align-middle">Periode</th>
                                        <th style="width: 10%;" class="text-start align-middle">Kode Kegiatan</th>
                                        <th style="width: 20%;" class="text-start align-middle">Nama Kegiatan</th>
                                        {{-- <th style="width: 20%;" class="text-start align-middle">Jenis Kegiatan</th> --}}
                                        <th style="width: 10%;" class="text-center align-middle">Pertanyaan</th>
                                        <th style="width: 10%;" class="text-center align-middle">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-800 fw-bolder fs-sm-8 fs-lg-6">
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="las la-folder-open fs-1 d-block mb-2"></i>
                                            Tidak ada data kegiatan yang tersedia.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>

                        <div class="alert alert-primary mt-3">
                            <strong>Catatan:</strong> Fitur ini digunakan untuk mengelola kegiatan berdasarkan jenis
                            kegiatan. Anda dapat menambahkan, mengubah, atau mengaktifkan/nonaktifkan data sesuai kebutuhan.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Include modals --}}
    @include('administration.masters.kegiatan.components.modal-create')
    @include('administration.masters.kegiatan.components.modal-edit')
    @include('administration.masters.kegiatan.components.modal-detail')
@endsection

@section('this-page-scripts')
    <script src="{{ asset('templates/assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('templates/administration/vendor/datatables/responsive/responsive.js') }}"></script>
    <script src="{{ asset('templates/assets/plugins/datatables/lodash.min.js') }}"></script>

    @include('administration.masters.kegiatan.scripts.datatable-init')
    @include('administration.masters.kegiatan.scripts.modal-create-handler')
    @include('administration.masters.kegiatan.scripts.modal-edit-handler')
    @include('administration.masters.kegiatan.scripts.actions-handler')
    {{-- @include('administration.masters.kegiatan.scripts.status-update-handler') --}}
    {{-- <script>
        $(document).ready(function() {
            $('#tahun_anggaran_id').on('change', function() {
                const tahunId = $(this).val();

                if (!tahunId) {
                    $('#jenis_kegiatan_id')
                        .html('<option value="">Pilih jenis kegiatan</option>')
                        .selectpicker('refresh');
                    return;
                }

                const url = '{{ route('administrator.master.jenis-kegiatan.list-by-tahun', ':id') }}'
                    .replace(
                        ':id', tahunId);

                AjaxHandler.sendGetRequest(url, function(response) {
                    if (response.status === 200 && Array.isArray(response.data)) {
                        let options = '<option value="">Pilih jenis kegiatan</option>';
                        response.data.forEach(item => {
                            options +=
                                `<option value="${item.id_jenis_kegiatan}">${item.kode_jenis} - ${item.nama_jenis}</option>`;
                        });
                        $('#jenis_kegiatan_id').html(options).selectpicker('refresh');
                    } else {
                        ResponseHandler.handleError("Gagal memuat data jenis kegiatan.");
                        $('#jenis_kegiatan_id').html(
                            '<option value="">Pilih jenis kegiatan</option>').selectpicker(
                            'refresh');
                    }
                });
            });
        });
    </script> --}}

@endsection
