@extends('administration.layouts.app')

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
                <h4 class="text-dark fw-semibold mb-0">Petugas</h4>
            </div>

            <!-- Section contain -->
            <div class="row">
                <div class="card">
                    <div class="card-header d-sm-flex d-block border-0 pb-0 flex-wrap">
                        <div class="pr-3 me-auto mb-sm-0 mb-3">
                            <h4 class="fs-20 text-black mb-1">List Data Petugas</h4>
                            <span class="fs-12 text-muted">Kelola data petugas aplikasi (kepala & petugas) beserta periode
                                tugasnya.</span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <div class="">
                                <select id="filter_status" class="selectpicker form-control wide form-select-md"
                                    data-live-search="false" required>
                                    <option value="">Semua</option>
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Tidak aktif</option>
                                </select>
                            </div>
                            <div class="">
                                <select id="filter_jabatan" class="selectpicker form-control wide form-select-md"
                                    data-live-search="false" required>
                                    <option value="">Semua</option>
                                    @foreach ($jabatanList as $item)
                                        <option value="{{ $item->role_name }}">
                                            {{ $item->role_name }}
                                    @endforeach
                                </select>
                            </div>
                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                                data-bs-target="#modalCreate">
                                <i class="las la-plus me-1"></i>Tambah
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Aksi Tambahan -->
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
                                <button class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal"
                                    data-bs-target="#modalImport">
                                    <i class="las la-file-excel me-1"></i>Import
                                </button>
                                <button class="btn btn-sm btn-outline-success">
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
                                        <th style="width: 15%;" class="text-start align-middle">NIP</th>
                                        <th style="width: 20%;" class="text-start align-middle">Nama Pegawai</th>
                                        <th style="width: 15%;" class="text-start align-middle">Telepon</th>
                                        <th style="width: 10%;" class="text-start align-middle">Jabatan</th>
                                        <th style="width: 15%;" class="text-start align-middle">Instansi</th>
                                        <th style="width: 10%;" class="text-start align-middle">Unit Kerja</th>
                                        <th style="width: 5%;" class="text-start align-middle">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="text-gray-800 fw-bolder fs-sm-8 fs-lg-6">

                                </tbody>
                            </table>
                        </div>


                        <div class="alert alert-primary mt-3">
                            <strong>Catatan:</strong> Data petugas digunakan untuk mengelola hak akses (kepala & petugas),
                            pastikan username dan password disimpan dengan aman.
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Create Start-->
    <div class="modal fade" id="modalCreate" tabindex="-1" role="dialog" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCreateLabel">Tambah Data Petugas</h5>
                    <button type="button" class="btn-close" aria-label="Close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div id="modalError" class="alert alert-danger d-none" role="alert"></div>
                    <form id="createForm" method="post" class="form-sm">

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="nip" class="form-label">Nomor Induk Pegawai (NIP)</label>
                                <input type="text" class="form-control form-control-sm" id="nip" name="nip"
                                    maxlength="20" placeholder="Contoh: 19651012 199203 2 005" required>
                            </div>
                            <div class="col-md-6">
                                <label for="nama_pegawai" class="form-label">Nama Pegawai</label>
                                <input type="text" class="form-control form-control-sm" id="nama_pegawai"
                                    name="nama_pegawai" placeholder="Contoh: Imron Rosyadi, SP.MM.CGCAE" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="alamat_lengkap" class="form-label">Alamat Lengkap</label>
                            <textarea class="form-control form-control-sm" id="alamat_lengkap" name="alamat_lengkap" rows="2"
                                placeholder="Masukkan alamat lengkap" required></textarea>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="telepon" class="form-label">Telepon</label>
                                <input type="text" class="form-control form-control-sm" id="telepon" name="telepon"
                                    placeholder="Contoh: 082334511111" required>
                            </div>
                            <div class="col-md-6">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control form-control-sm" id="username"
                                    name="username" placeholder="Contoh: imronr" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control form-control-sm" id="password"
                                    name="password" placeholder="Minimal 6 karakter" required>
                            </div>
                            <div class="col-md-6">
                                <label for="jabatan" class="form-label">Jabatan</label>
                                <select id="jabatan" name="jabatan"
                                    class="selectpicker form-control wide form-select-md" data-live-search="true" required
                                    aria-describedby="jabatan-feedback" aria-label="Pilih Jabatan">
                                    <option value="" disabled selected>Pilih Jabatan</option>
                                    <option value="Kepala Bagian">Kepala Bagian</option>
                                    <option value="Inspektur">Inspektur</option>
                                    <option value="Auditor">Auditor</option>
                                    <option value="Staf">Staf</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="tahun_awal" class="form-label">Tahun Awal Menjabat</label>
                                <input type="date" class="form-control form-control-sm" id="tahun_awal"
                                    name="tahun_awal" required>
                            </div>
                            <div class="col-md-6">
                                <label for="tahun_akhir" class="form-label">Tahun Akhir Menjabat</label>
                                <input type="date" class="form-control form-control-sm" id="tahun_akhir"
                                    name="tahun_akhir" required>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label for="status" class="form-label">Status</label>
                                <select id="status" name="status"
                                    class="selectpicker form-control wide form-select-md" data-live-search="false"
                                    required aria-describedby="status-feedback" aria-label="Pilih Status">
                                    <option value="active">Aktif</option>
                                    <option value="inactive">Tidak aktif</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="foto" class="form-label">Foto Pegawai</label>
                                <input type="file" class="form-control form-control-sm" id="foto" name="foto"
                                    accept="image/*">
                            </div>
                        </div>

                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" form="createForm" class="btn btn-primary">Simpan</button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal Create end -->


    <!-- Content body end -->
@endsection

@section('this-page-scripts')
    <script src="{{ asset('templates/assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('templates/administration/vendor/datatables/responsive/responsive.js') }}"></script>
    <script src="{{ asset('templates/assets/plugins/datatables/lodash.min.js') }}"></script>

    @include('administration.masters.petugas.scripts.datatable-init')
@endsection
