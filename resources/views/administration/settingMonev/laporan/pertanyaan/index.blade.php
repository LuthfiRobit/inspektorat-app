@extends('administration.settingMonev.laporan.app')

@push('styles')
    {{-- Style tambahan kalau ada --}}
@endpush

@section('tab-content')
    <!-- Section Header -->
    <div class="d-sm-flex d-block border-bottom pb-3 mb-3 flex-wrap align-items-center justify-content-between">
        <div class="mb-2 mb-sm-0">
            <h4 class="fs-20 text-black mb-1">List Pertanyaan</h4>
            <span class="fs-12 text-muted">Kelola pertanyaan yang digunakan dalam laporan monev.</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <div class="">
                <select id="filter_kategori" class="selectpicker" data-live-search="true" title="Kategori">
                    <option value="">Semua Kategori</option>
                    <!-- render kategori laporan -->
                </select>
            </div>
            <div class="">
                <select id="filter_jenis" class="selectpicker" data-live-search="true" title="Jenis">
                    <option value="">Semua Jenis</option>
                    <!-- render jenis laporan -->
                </select>
            </div>
            <div class="">
                <select id="filter_status" class="selectpicker" data-live-search="false" title="Status">
                    <option value="">Semua</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Tidak aktif</option>
                </select>
            </div>
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalCreatePertanyaan">
                <i class="las la-plus me-1"></i>Tambah
            </button>
        </div>
    </div>

    <!-- Toolbar Aksi -->
    <div class="row mb-3 gy-2">
        <div class="col-12 col-md d-flex flex-wrap gap-2">
            <button class="btn-update-status btn btn-sm btn-primary" data-status="active">
                <i class="las la-check-circle me-1"></i>Aktifkan
            </button>
            <button class="btn-update-status btn btn-sm btn-danger" data-status="inactive">
                <i class="las la-times-circle me-1"></i>Nonaktifkan
            </button>
        </div>
        <div class="col-12 col-md-auto d-flex flex-wrap gap-2 justify-content-md-end">
            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalImportPertanyaan">
                <i class="las la-file-excel me-1"></i>Import
            </button>
            <button class="btn btn-sm btn-outline-success">
                <i class="las la-file-excel me-1"></i>Export
            </button>
        </div>
    </div>

    <!-- Tabel -->
    <div class="table-responsive">
        <table id="table-pertanyaan" class="table table-sm align-middle table-striped gs-0 gy-2 nowrap" style="width:100%;">
            <thead>
                <tr class="text-center text-muted text-uppercase">
                    <th style="width: 5%;" class="align-middle">
                        <span class="d-inline-flex align-items-center gap-1">
                            <input type="checkbox" class="form-check-input m-0" id="selectAll" />
                            <i class="bi bi-info-circle-fill text-primary" data-bs-toggle="tooltip"
                                title="Pilih beberapa data untuk aksi massal."></i>
                        </span>
                    </th>
                    <th style="width: 10%;">Aksi</th>
                    <th style="width: 20%;" class="text-start">Kategori</th>
                    <th style="width: 20%;" class="text-start">Jenis Laporan</th>
                    <th style="width: 35%;" class="text-start">Pertanyaan</th>
                    <th style="width: 10%;" class="text-start">Status</th>
                </tr>
            </thead>
            <tbody>
                <!-- render data -->
            </tbody>
        </table>
    </div>

    <!-- Catatan -->
    <div class="alert alert-info mt-3">
        <strong>Catatan:</strong> Pertanyaan hanya bisa ditambahkan jika sudah memilih <em>kategori</em> dan <em>jenis
            laporan</em>.
    </div>

    <!-- Modal Create -->
    @include('administration.settingMonev.laporan.pertanyaan.components.create')
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#table-pertanyaan').DataTable({
                responsive: true
            });
        });
    </script>
@endpush
