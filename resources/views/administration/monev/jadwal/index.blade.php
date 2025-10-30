@extends('administration.layouts.app')

@section('this-page-style')
    <link href="{{ asset('templates/administration/vendor/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('templates/administration/vendor/datatables/responsive/responsive.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <div class="content-body default-height">
        <div class="container-fluid">
            <!-- Section Heading -->
            <div class="form-head mb-4 d-flex align-items-center gap-2">
                <small class="text-muted">Monev -</small>
                <h4 class="text-dark fw-semibold mb-0">Jadwal Monev</h4>
            </div>

            <!-- Section contain -->
            <div class="row">
                <div class="card">
                    <div class="card-header d-sm-flex d-block border-0 pb-0 flex-wrap">
                        <div class="pr-3 me-auto mb-sm-0 mb-3">
                            <h4 class="fs-20 text-black mb-1">Daftar Jadwal Monev</h4>
                            <span class="fs-12 text-muted">Kelola jadwal monitoring dan evaluasi setiap kecamatan.</span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <div class="">
                                <select id="filter_kecamatan" class="selectpicker form-control wide form-select-md"
                                    data-live-search="false" aria-describedby="status-feedback" placeholder="Pilih kec."
                                    required>
                                    <option value="">Semua</option>
                                </select>
                            </div>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#modalCreate">
                                <i class="las la-plus me-1"></i>Tambah
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Tabel -->
                        <div class="table-responsive">
                            <table id="jadwalMonevTable" class="table table-sm align-middle table-striped gs-0 gy-2 nowrap"
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
                                        <th style="width: 10%;">Aksi</th>
                                        <th class="text-start">Tahun</th>
                                        <th class="text-start">Kecamatan</th>
                                        <th class="text-start">Bulan</th>
                                        <th class="text-start">Jenis Laporan</th>
                                        <th class="text-start">Tanggal Awal</th>
                                        <th class="text-start">Tanggal Akhir</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-800 fw-bolder fs-sm-8 fs-lg-6">
                                    <!-- Dynamic rows here -->
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info mt-3">
                            <strong>Catatan:</strong> Jadwal monev ditentukan berdasarkan kecamatan, jenis laporan, dan
                            periode waktu tertentu.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Create -->
    <div class="modal fade" id="modalCreate" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Tambah Jadwal Monev</h5>
                    <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="modalError" class="alert alert-danger d-none" role="alert"></div>
                    <form id="createForm" method="post" class="form-sm">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="tahun" class="form-label">Tahun</label>
                                <input type="number" class="form-control form-control-sm" id="tahun" name="tahun"
                                    required placeholder="Contoh: 2025">
                            </div>
                            <div class="col-md-6">
                                <label for="bulan" class="form-label">Bulan</label>
                                <select id="bulan" name="bulan" class="selectpicker form-control wide form-select-md"
                                    required>
                                    <option value="" disabled selected>Pilih Bulan</option>
                                    @foreach (['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'] as $i => $bulan)
                                        <option value="{{ $i + 1 }}">{{ $bulan }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="kecamatan" class="form-label">Kecamatan</label>
                            <select id="kecamatan" name="kecamatan" class="selectpicker form-control wide form-select-md"
                                data-live-search="true" required>
                                <option value="" disabled selected>Pilih Kecamatan</option>
                                {{-- @foreach ($listKecamatan as $kec) --}}
                                {{-- <option value="{{ $kec->id }}">{{ $kec->nama }}</option> --}}
                                {{-- @endforeach --}}
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="jenis_laporan" class="form-label">Jenis Laporan</label>
                            <select id="jenis_laporan" name="jenis_laporan"
                                class="selectpicker form-control wide form-select-md" required>
                                <option value="" disabled selected>Pilih Jenis</option>
                            </select>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="tanggal_awal" class="form-label">Tanggal Awal</label>
                                <input type="date" class="form-control form-control-sm" id="tanggal_awal"
                                    name="tanggal_awal" required>
                            </div>
                            <div class="col-md-6">
                                <label for="tanggal_akhir" class="form-label">Tanggal Akhir</label>
                                <input type="date" class="form-control form-control-sm" id="tanggal_akhir"
                                    name="tanggal_akhir" required>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" form="createForm" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('this-page-scripts')
    <script src="{{ asset('templates/assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('templates/administration/vendor/datatables/responsive/responsive.js') }}"></script>
    <script src="{{ asset('templates/assets/plugins/datatables/lodash.min.js') }}"></script>

    {{-- @include('administration.monitoring.jadwal-monev.scripts.datatable-init') --}}
    {{-- @include('administration.monitoring.jadwal-monev.scripts.modal-create-handler') --}}
@endsection
