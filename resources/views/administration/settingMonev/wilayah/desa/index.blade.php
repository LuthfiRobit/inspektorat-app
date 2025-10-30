@extends('administration.settingMonev.wilayah.app')

@push('styles')
    {{-- Tambahkan style khusus jika ada --}}
@endpush

@section('tab-content')
    <!-- Section Header -->
    <div class="d-sm-flex d-block border-bottom pb-3 mb-3 flex-wrap align-items-center justify-content-between">
        <div class="mb-2 mb-sm-0">
            <h4 class="fs-20 text-black mb-1">List Desa</h4>
            <span class="fs-12 text-muted">Kelola data desa sesuai kebutuhan monev.</span>
        </div>
        <div class="d-flex align-items-center gap-2">
            <!-- Filter Kecamatan -->
            <div>
                <select id="filter_kecamatan" class="selectpicker form-control wide form-select-md" data-live-search="true"
                    aria-describedby="kecamatan-feedback" placeholder="Pilih Kecamatan" required>
                    <option value="">Semua Kecamatan</option>
                    {{-- Option kecamatan bisa diisi dinamis di controller --}}
                </select>
            </div>
            <!-- Filter Status -->
            <div>
                <select id="filter_status" class="selectpicker form-control wide form-select-md" data-live-search="false"
                    aria-describedby="status-feedback" placeholder="Pilih status" required>
                    <option value="">Semua</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Tidak aktif</option>
                </select>
            </div>
            <!-- Tombol Tambah -->
            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalCreateDesa">
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
            <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#modalImportDesa">
                <i class="las la-file-excel me-1"></i>Import
            </button>
            <button class="btn btn-sm btn-outline-success">
                <i class="las la-file-excel me-1"></i>Export
            </button>
        </div>
    </div>

    <!-- Tabel Data Desa -->
    <div class="table-responsive">
        <table id="example" class="table table-sm align-middle table-striped gs-0 gy-2 nowrap" style="width:100%;">
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
                    <th style="width: 20%;" class="text-start align-middle">Kode Desa</th>
                    <th style="width: 25%;" class="text-start align-middle">Nama Desa</th>
                    <th style="width: 20%;" class="text-start align-middle">Kecamatan</th>
                    <th style="width: 10%;" class="text-start align-middle">Status</th>
                </tr>
            </thead>
            <tbody>
                <!-- data render -->
            </tbody>
        </table>
    </div>

    <!-- Catatan -->
    <div class="alert alert-primary mt-3">
        <strong>Catatan:</strong> Data desa dikelola berdasarkan kecamatan dan menjadi dasar dalam kegiatan monitoring dan
        evaluasi.
    </div>

    <!-- Modal Create Desa -->
    @include('administration.settingMonev.wilayah.desa.components.create')
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            $('#example').DataTable({
                responsive: true
            });
        });
    </script>
@endpush
