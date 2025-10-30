@extends('administration.layouts.app')

@section('title', 'Kelola Desa | Admin Panel')
@section('meta-description',
    'Halaman untuk mengelola data desa berdasarkan kecamatan. Termasuk tambah, edit, status,
    import, dan export.')

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
                <h4 class="text-dark fw-semibold mb-0">Desa</h4>
            </div>

            <!-- Section contain -->
            <div class="row">
                <div class="card">
                    <div class="card-header d-sm-flex d-block border-0 pb-0 flex-wrap">
                        <div class="pr-3 me-auto mb-sm-0 mb-3">
                            <h4 class="fs-20 text-black mb-1">List Desa</h4>
                            <span class="fs-12 text-muted">Kelola data desa berdasarkan kecamatan.</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            {{-- Filter Status --}}
                            <div>
                                <select id="filter_status" class="selectpicker form-control wide form-select-md"
                                    data-live-search="false" title="Filter status" required>
                                    <option value="">Semua Status</option>
                                    <option value="draft">Draft</option>
                                    <option value="submitted">Terkirim</option>
                                    <option value="revision">Revisi</option>
                                    <option value="approved">Disetujui</option>
                                    <option value="rejected">Ditolak</option>
                                </select>
                            </div>

                            {{-- Filter Tahun --}}
                            <div>
                                <select id="filter_tahun" class="selectpicker form-control wide form-select-md"
                                    data-live-search="false" title="Filter tahun" required>
                                    <option value="">Semua Tahun</option>
                                    {{-- @foreach ($tahunList as $tahun)
                                        <option value="{{ $tahun }}">{{ $tahun }}</option>
                                    @endforeach --}}
                                </select>
                            </div>

                            {{-- Filter Bulan --}}
                            <div>
                                <select id="filter_bulan" class="selectpicker form-control wide form-select-md"
                                    data-live-search="false" title="Filter bulan" required>
                                    <option value="">Semua Bulan</option>
                                    @foreach ([1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr', 5 => 'Mei', 6 => 'Jun', 7 => 'Jul', 8 => 'Agu', 9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Des'] as $num => $nama)
                                        <option value="{{ $num }}">{{ $nama }}</option>
                                    @endforeach
                                </select>
                            </div>

                            {{-- Filter Desa --}}
                            <div>
                                <select id="filter_desa" class="selectpicker form-control wide form-select-md"
                                    data-live-search="true" title="Filter desa" required>
                                    <option value="">Semua Desa</option>
                                    {{-- @foreach ($desaList as $desa)
                                        <option value="{{ $desa->id_desa }}">{{ $desa->nama_desa }}</option>
                                    @endforeach --}}
                                </select>
                            </div>
                        </div>

                    </div>

                    <div class="card-body">

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
                                                    title="Pilih beberapa data desa untuk aksi massal."></i>
                                            </span>
                                        </th>
                                        <th style="width: 10%;" class="align-middle">Aksi</th>
                                        <th class="text-start align-middle" scope="col">Nama Desa</th>
                                        <th class="text-start align-middle" scope="col">Nama Kegiatan</th>
                                        <th class="text-start align-middle" scope="col">Kode Kegiatan</th>
                                        <th class="text-start align-middle" scope="col">Periode</th>
                                        <th class="text-start align-middle" scope="col">Status</th>
                                        <th class="text-start align-middle" scope="col">Keterlambatan</th>
                                        <th class="text-start align-middle" scope="col">Dibuat Oleh</th>
                                        <th class="text-start align-middle" scope="col">Tanggal Dibuat</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-800 fw-bolder fs-sm-8 fs-lg-6">
                                    {{-- Data akan dimuat via AJAX datatable --}}
                                    <tr>
                                        <td colspan="10" class="text-center text-muted py-4">
                                            <i class="las la-folder-open fs-1 d-block mb-2"></i>
                                            Tidak ada data laporan yang tersedia.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-primary mt-3">
                            <strong>Catatan:</strong> Fitur ini digunakan untuk mengelola desa berdasarkan kecamatan. Anda
                            dapat
                            menambahkan, mengubah, atau mengaktifkan/desa sesuai kebutuhan.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Include modals --}}
    {{-- @include('administration.masters.desa.components.modal-create') --}}
    {{-- @include('administration.masters.desa.components.modal-edit') --}}
    {{-- @include('administration.masters.desa.components.modal-detail') --}}
@endsection

@section('this-page-scripts')
    <script src="{{ asset('templates/assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('templates/administration/vendor/datatables/responsive/responsive.js') }}"></script>
    <script src="{{ asset('templates/assets/plugins/datatables/lodash.min.js') }}"></script>

    @include('administration.monev.laporan.scripts.datatable-init')
    {{-- @include('administration.masters.desa.scripts.modal-create-handler') --}}
    {{-- @include('administration.masters.desa.scripts.modal-edit-handler') --}}
    {{-- @include('administration.masters.desa.scripts.actions-handler') --}}
    {{-- @include('administration.masters.desa.scripts.status-update-handler') --}}
@endsection
