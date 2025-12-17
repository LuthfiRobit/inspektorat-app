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
            <div class="form-head mb-4 d-flex align-items-center gap-2">
                <small class="text-muted">Pelaporan -</small>
                <h4 class="text-dark fw-semibold mb-0">Buat Laporan</h4>
            </div>

            <div class="row">
                <div class="card">
                    <div class="card-header d-sm-flex d-block border-0 pb-0 flex-wrap">
                        <div class="pr-3 me-auto mb-sm-0 mb-3">
                            <h4 class="fs-20 text-black mb-1">Daftar Kegiatan yang Perlu Dilaporkan</h4>
                            <span class="fs-12 text-muted">Kelola laporan kegiatan untuk desa-desa di wilayah kecamatan
                                Anda.</span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            {{-- <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#modalCreate" data-bs-toggle="tooltip" title="Buat laporan baru">
                                <i class="las la-plus me-1"></i>Buat Laporan
                            </button> --}}
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row g-3 mb-4 align-items-end">

                            <!-- Filter Tahun -->
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Tahun</label>
                                <select id="filter_tahun" class="selectpicker form-control wide form-select-md"
                                    data-live-search="true" title="Pilih Tahun" data-size="5">
                                    <option value="">Semua Tahun</option>
                                    @foreach ($tahunAnggaranList as $tahun)
                                        <option value="{{ $tahun->id_tahun_anggaran }}">
                                            {{ $tahun->tahun }} @if ($tahun->status === 'aktif')
                                                (Aktif)
                                            @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Filter Bulan -->
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Periode</label>
                                <select id="filter_periode" class="selectpicker form-control wide form-select-md"
                                    data-live-search="true" title="Pilih Bulan" data-size="5">
                                    <option value="">Semua Periode</option>
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

                            <!-- Filter Status -->
                            <div class="col-md-2">
                                <label class="form-label fw-semibold">Status</label>
                                <select id="filter_status" class="selectpicker form-control wide form-select-md"
                                    data-live-search="false" title="Filter Status">
                                    <option value="">Semua Status</option>
                                    <option value="belum_dilaporkan">Belum Dilaporkan</option>
                                    <option value="draft">Draft</option>
                                    <option value="submitted">Menunggu Review</option>
                                    <option value="revision">Perlu Revisi</option>
                                    <option value="approved">Disetujui</option>
                                    <option value="rejected">Ditolak</option>
                                </select>
                            </div>

                            <!-- Filter Desa -->
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Desa</label>
                                <select id="filter_desa" class="selectpicker form-control wide form-select-md"
                                    data-live-search="true" title="Filter Desa" data-size="5">
                                    <option value="">Semua Desa</option>
                                    @foreach ($desaList as $item)
                                        <option value="{{ $item->id_desa }}">
                                            {{ $item->nama_desa }} | {{ $item->nama_kecamatan }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Reset Button -->
                            <div class="col-md-2 d-flex justify-content-end">
                                <button id="btnResetFilter" type="button" class="btn btn-outline-secondary w-100">
                                    <i class="fas fa-sync-alt me-1"></i> Reset Filter
                                </button>
                            </div>

                        </div>
                        {{-- <div class="row mb-3 gy-2">
                            <div class="col-12 col-md d-flex flex-wrap gap-2">
                                <button class="btn-submit-batch btn btn-sm btn-success" data-bs-toggle="tooltip"
                                    title="Submit laporan yang dipilih">
                                    <i class="las la-paper-plane me-1"></i>Submit Terpilih
                                </button>
                                <button class="btn-draft-batch btn btn-sm btn-warning" data-bs-toggle="tooltip"
                                    title="Simpan sebagai draft laporan yang dipilih">
                                    <i class="las la-save me-1"></i>Draft Terpilih
                                </button>
                            </div>
                            <div class="col-12 col-md-auto d-flex flex-wrap gap-2 justify-content-md-end">
                                <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal"
                                    data-bs-target="#modalTemplate" data-bs-toggle="tooltip"
                                    title="Download template laporan">
                                    <i class="las la-file-download me-1"></i>Template
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
                                        {{-- <th style="width: 5%;" class="align-middle">
                                            <span class="d-inline-flex align-items-center gap-1">
                                                <input type="checkbox" class="form-check-input m-0" id="selectAll" />
                                                <i class="bi bi-info-circle-fill text-primary" data-bs-toggle="tooltip"
                                                    title="Pilih beberapa laporan untuk aksi massal."></i>
                                            </span>
                                        </th> --}}
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
                                            Tidak ada kegiatan yang perlu dilaporkan saat ini.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info mt-3">
                            <strong>Informasi:</strong>
                            <ul class="mb-0 mt-2">
                                <li>Pilih kegiatan yang perlu dilaporkan untuk desa-desa di wilayah kecamatan Anda.</li>
                                <li>Halaman ini hanya menampilkan laporan yang <strong>belum dilaporkan</strong> dan laporan
                                    yang <strong>perlu direvisi</strong>.</li>
                                <li>Jika ada laporan dengan status <strong>Menunggu Review</strong>, untuk melihat detail
                                    dan status review silakan kunjungi halaman
                                    <a href="{{ route('administrator.monitoring.riwayat.index') }}" class="text-primary font-weight-bold"
                                        title="Buka halaman monitoring laporan">Riwayat Laporan</a>.
                                </li>
                                <li>Status <span class="badge light badge-danger">Terlambat</span> menunjukkan batas
                                    waktu telah lewat.</li>
                                <li>Status <span class="badge light badge-warning">Tenggang</span> menunjukkan mendekati
                                    batas waktu.</li>
                            </ul>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Include modals --}}
    {{-- @include('administration.pelaporan.buatLaporan.components.modal-create') --}}
    {{-- @include('administration.pelaporan.buatLaporan.components.modal-edit') --}}
    @include('administration.monev.buatLaporan.components.modal-detail')
@endsection

@section('this-page-scripts')
    <script src="{{ asset('templates/assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('templates/administration/vendor/datatables/responsive/responsive.js') }}"></script>
    <script src="{{ asset('templates/assets/plugins/datatables/lodash.min.js') }}"></script>

    @include('administration.monev.buatLaporan.scripts.datatable-init')
    {{-- @include('administration.pelaporan.buatLaporan.scripts.modal-create-handler') --}}
    {{-- @include('administration.pelaporan.buatLaporan.scripts.modal-edit-handler') --}}
    @include('administration.monev.buatLaporan.scripts.actions-handler')
@endsection
