@extends('administration.layouts.app')

@section('title', 'Buat Laporan Kegiatan | Kecamatan Panel')
@section('meta-description', 'Halaman untuk membuat dan mengelola laporan kegiatan desa-desa di wilayah kecamatan.')

@section('this-page-style')
    <link href="{{ asset('templates/administration/vendor/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('templates/administration/vendor/datatables/responsive/responsive.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <div class="content-body default-height">
        <div class="container-fluid">
            <!-- Header Halaman -->
            <div class="form-head mb-4 d-flex align-items-center gap-2">
                <small class="text-muted">Monev -</small>
                <h4 class="text-dark fw-semibold mb-0">History Laporan</h4>
            </div>

            <!-- Deskripsi Halaman -->
            <div class="alert alert-info mb-4">
                <strong>Tentang Halaman Ini:</strong>
                <ul class="mb-0 mt-2">
                    <li>Halaman ini menampilkan <strong>riwayat laporan kegiatan</strong> dari seluruh desa di wilayah
                        kecamatan Anda.</li>
                    <li>Anda dapat menggunakan filter untuk menelusuri laporan berdasarkan tahun, periode, desa, atau
                        status.</li>
                    <li>Status <span class="badge badge-sm badge-danger">Terlambat</span> menunjukkan laporan melewati batas
                        waktu.</li>
                    <li>Status <span class="badge badge-sm badge-warning">Tenggang</span> menunjukkan laporan mendekati
                        batas waktu.</li>
                </ul>
            </div>

            <div class="row">
                <div class="card">
                    <div class="card-header d-sm-flex d-block border-0 pb-0 flex-wrap">
                        <div class="pr-3 me-auto mb-sm-0 mb-3">
                            <h4 class="fs-20 text-black mb-1">Daftar Riwayat Laporan Kegiatan</h4>
                            <span class="fs-12 text-muted">Kelola dan tinjau laporan kegiatan dari desa-desa di wilayah
                                Anda.</span>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Filter Section -->
                        <div class="row mb-4 gy-2">
                            <div class="col-4">
                                <select id="filter_tahun" class="selectpicker form-control wide form-select-md"
                                    data-live-search="true" required aria-label="Filter tahun anggaran" data-size="5">
                                    <option value="">Semua Tahun</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <select id="filter_periode" class="selectpicker form-control wide form-select-md"
                                    data-live-search="true" title="Filter Periode" data-size="5">
                                    <option value="">Semua Periode</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <select id="filter_desa" class="selectpicker form-control wide form-select-md"
                                    data-live-search="true" title="Filter Desa" data-size="5">
                                    <option value="">Semua Desa</option>
                                </select>
                            </div>
                            <div class="col-4">
                                <select id="filter_status" class="selectpicker form-control wide form-select-md"
                                    data-live-search="false" title="Filter Status" required>
                                    <option value="">Semua Status</option>
                                </select>
                            </div>
                        </div>

                        <!-- Table Section -->
                        <div class="table-responsive">
                            <table id="example" class="table table-sm align-middle table-striped gs-0 gy-2 nowrap"
                                style="width:100%;">
                                <thead>
                                    <tr class="text-center text-muted text-uppercase">
                                        <th style="width: 5%;" class="align-middle">
                                            <span class="d-inline-flex align-items-center gap-1">
                                                <input type="checkbox" class="form-check-input m-0" id="selectAll" />
                                                <i class="bi bi-info-circle-fill text-primary" data-bs-toggle="tooltip"
                                                    title="Pilih beberapa laporan untuk aksi massal."></i>
                                            </span>
                                        </th>
                                        <th style="width: 10%;" class="align-middle">Aksi</th>
                                        <th style="width: 8%;" class="text-start align-middle">Tahun</th>
                                        <th style="width: 10%;" class="text-center align-middle">Periode</th>
                                        <th style="width: 15%;" class="text-start align-middle">Desa</th>
                                        <th style="width: 25%;" class="text-start align-middle">Kegiatan</th>
                                        <th style="width: 17%;" class="text-center align-middle">Timeline</th>
                                        <th style="width: 10%;" class="text-center align-middle">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-800 fw-bolder fs-sm-8 fs-lg-6">
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            <i class="las la-folder-open fs-1 d-block mb-2"></i>
                                            Tidak ada riwayat laporan kegiatan yang ditemukan.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Catatan Tambahan -->
                        <div class="alert alert-light border mt-3">
                            <strong>Catatan:</strong> Gunakan kolom filter di atas untuk menampilkan laporan berdasarkan
                            periode atau status tertentu.
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

@endsection
