@extends('administration.layouts.app')

@section('title', 'Kelola Wilayah Binaan | Admin Panel')
@section('meta-description', 'Halaman untuk mengelola detail penugasan wilayah binaan petugas.')

@section('this-page-style')
    <link href="{{ asset('templates/administration/vendor/datatables/css/jquery.dataTables.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('templates/administration/vendor/datatables/responsive/responsive.css') }}" rel="stylesheet" />
    <style>
        .bg-primary-light {
            background-color: rgba(69, 101, 246, 0.1);
        }
        .bg-success-light {
            background-color: rgba(46, 202, 106, 0.1);
        }
        #table-assigned_wrapper .dataTables_filter {
            margin-bottom: 15px;
        }
        #table-assigned_wrapper .dataTables_filter input {
            border: 1px solid #e6e6e6;
            padding: 5px 10px;
            border-radius: 5px;
        }
    </style>
@endsection

@section('content')
    <!-- Content body start -->
    <div class="content-body default-height">
        <div class="container-fluid">
            <!-- Section Heading -->
            <div class="form-head mb-4 d-flex align-items-center gap-2">
                <small class="text-muted">Master Data -</small>
                <h4 class="text-dark fw-semibold mb-0">Kelola Wilayah Binaan</h4>
            </div>

            <!-- Card contain -->
            <div class="row">
                <div class="card">
                    <div class="card-header d-sm-flex d-block border-0 pb-0 flex-wrap">
                        <div class="pr-3 me-auto mb-sm-0 mb-3">
                            <h4 class="fs-20 text-black mb-1">Kelola Wilayah Binaan</h4>
                            <span class="fs-12 text-muted">Kelola area kecamatan/desa binaan untuk petugas bersangkutan.</span>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Info Petugas Panel -->
                        <div class="row mb-4">
                            <div class="col-md-12">
                                <div class="p-3 bg-light rounded d-flex flex-wrap gap-4 align-items-center">
                                    <div>
                                        <small class="text-muted d-block fs-11">NAMA LENGKAP</small>
                                        <span class="fw-bold text-dark fs-15">{{ $petugas->nama_lengkap }}</span>
                                    </div>
                                    <div class="border-end pe-4 d-none d-md-block" style="height: 30px;"></div>
                                    <div>
                                        <small class="text-muted d-block fs-11">NIP</small>
                                        <span class="fw-bold text-dark fs-15">{{ $petugas->nip ?: '-' }}</span>
                                    </div>
                                    <div class="border-end pe-4 d-none d-md-block" style="height: 30px;"></div>
                                    <div>
                                        <small class="text-muted d-block fs-11">JABATAN</small>
                                        <span class="fw-bold text-dark fs-15">{{ $petugas->jabatan ?: '-' }}</span>
                                    </div>
                                    <div class="border-end pe-4 d-none d-md-block" style="height: 30px;"></div>
                                    <div>
                                        <small class="text-muted d-block fs-11">TIPE PETUGAS / WILAYAH TUGAS</small>
                                        @if($type === 'inspektorat')
                                            <span class="badge badge-primary light fs-13">Petugas Inspektorat</span>
                                        @else
                                            <span class="badge badge-info light fs-13">Petugas Kecamatan</span>
                                            <small class="text-muted ms-1">(Kec. {{ $petugas->kecamatan->nama_kecamatan ?? '-' }})</small>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="row">
                            <!-- SISI KIRI: Form Assign Baru -->
                            <div class="col-lg-5 col-md-12 mb-4">
                                <div class="border rounded p-4 bg-white h-100 shadow-sm">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="bg-primary-light text-primary rounded p-2 d-inline-flex">
                                            <i class="las la-plus-circle fs-20"></i>
                                        </div>
                                        <h5 class="mb-0 fs-16 text-dark font-w600">Tambah Wilayah Binaan</h5>
                                    </div>
                                    <p class="text-muted fs-13 mb-4">Pilih satu atau beberapa wilayah kerja aktif untuk ditambahkan ke daftar binaan petugas ini.</p>
                                    
                                    <form id="form-assign">
                                        @csrf
                                        <input type="hidden" name="petugas_id" value="{{ $petugas->id_petugas }}">
                                        <input type="hidden" name="type" value="{{ $type }}">
                                        
                                        <div class="mb-4">
                                            <label class="form-label text-dark font-w500">Pilih {{ $type === 'inspektorat' ? 'Kecamatan' : 'Desa' }} Tersedia</label>
                                            <select class="selectpicker form-control wide form-select-md" name="wilayah_ids[]" id="wilayah_ids" multiple="multiple" data-actions-box="true" data-live-search="true" title="Pilih area binaan..." required>
                                                {{-- Opsi dimuat secara dinamis via JS --}}
                                            </select>
                                            <small class="text-muted mt-2 d-block">Hanya menampilkan wilayah aktif yang belum dibina oleh petugas lain.</small>
                                        </div>
                                        
                                        <button type="submit" class="btn btn-primary btn-sm w-100" id="btn-submit">
                                            <i class="las la-plus-circle me-1"></i>Tambahkan Area Binaan
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <!-- SISI KANAN: Tabel Wilayah Sudah Dibina (DataTable Client-side) -->
                            <div class="col-lg-7 col-md-12 mb-4">
                                <div class="border rounded p-4 bg-white h-100 shadow-sm">
                                    <div class="d-flex align-items-center gap-2 mb-3">
                                        <div class="bg-success-light text-success rounded p-2 d-inline-flex">
                                            <i class="las la-map-marked-alt fs-20"></i>
                                        </div>
                                        <h5 class="mb-0 fs-16 text-dark font-w600">Daftar {{ $type === 'inspektorat' ? 'Kecamatan' : 'Desa' }} Binaan</h5>
                                    </div>
                                    <p class="text-muted fs-13 mb-4">Berikut adalah daftar area yang saat ini menjadi tanggung jawab binaan petugas.</p>
                                    
                                    <div class="table-responsive">
                                        <table id="table-assigned" class="table table-sm align-middle table-striped gs-0 gy-2 nowrap" style="width:100%;">
                                            <thead>
                                                <tr class="text-center text-muted text-uppercase fs-11">
                                                    <th style="width: 10%;">No</th>
                                                    <th class="text-start">Nama {{ $type === 'inspektorat' ? 'Kecamatan' : 'Desa' }}</th>
                                                    <th style="width: 20%;">Aksi</th>
                                                </tr>
                                            </thead>
                                            <tbody class="text-gray-800 fw-bold">
                                                {{-- Baris dimuat secara dinamis via JS --}}
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tombol Kembali -->
                        <div class="mt-4 border-top pt-3 d-flex justify-content-between">
                            <a href="{{ route('administrator.master.wilayah-binaan.index') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="las la-arrow-left me-1"></i>Kembali Ke List
                            </a>
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

    @include('administration.masters.wilayahBinaan.scripts.show-handler')
@endsection
