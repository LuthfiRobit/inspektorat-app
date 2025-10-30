@extends('administration.layouts.app')

@section('title', 'Tambah Petugas Kecamatan | Sistem Pelaporan')
@section('meta-description', 'Form untuk menambah data petugas Kecamatan baru.')

@section('this-page-style')
@endsection

@section('content')
    <div class="content-body default-height">
        <div class="container-fluid">
            <div class="form-head mb-4 d-flex align-items-center gap-2">
                <small class="text-muted">Master Data -</small>
                <h4 class="text-dark fw-semibold mb-0">Petugas Kecamatan</h4>
            </div>
            <div class="row">
                <div class="card">
                    <div class="card-header d-sm-flex d-block border-0 pb-0 flex-wrap align-items-center">
                        <div class="pr-3 me-auto mb-sm-0 mb-3">
                            <h4 class="fs-20 text-black mb-1">Tambah Data Petugas</h4>
                            <span class="fs-12 text-muted">Lengkapi formulir berikut untuk menambah petugas baru.</span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <a href="{{ route('administrator.master.petugas.kecamatan.index') }}"
                                class="btn btn-outline-primary btn-sm btn-rounded light" title="Kembali">
                                <i class="las la-arrow-left scale5 me-1"></i> Kembali
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="alert alert-primary">
                            <strong>Catatan:</strong>
                            <ul class="mb-0">
                                <li>Isi semua kolom dengan benar dan sesuai ketentuan.</li>
                                <li>Pastikan <strong>NIP</strong> diisi dengan format yang benar.</li>
                                <li>NIP akan digunakan sebagai <strong>username dan password awal</strong> petugas.</li>
                            </ul>
                        </div>
                        <form id="createForm" method="POST" enctype="multipart/form-data" class="form-sm">
                            @csrf
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="nip" class="form-label">Nomor Induk Pegawai (NIP)</label>
                                    <input type="text" class="form-control form-control-sm" id="nip" name="nip"
                                        maxlength="20" placeholder="Contoh: 19651012 199203 2 005" required>
                                    <small class="text-muted fst-italic">
                                        *NIP akan digunakan sebagai <strong>username</strong> dan <strong>password
                                            awal</strong> petugas.
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control form-control-sm" id="nama_lengkap"
                                        name="nama_lengkap" placeholder="Contoh: Imron Rosyadi, SP.MM.CGCAE" required>
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-4">
                                    <label for="kecamatan_id" class="form-label">Kecamatan</label>
                                    <select id="kecamatan_id" name="kecamatan_id"
                                        class="selectpicker form-control wide form-select-md" data-live-search="false"
                                        aria-label="Pilih kecamatan" required>
                                        <option value="">-- Pilih Kecamatan --</option>
                                        @foreach ($kecamatanList as $item)
                                            <option value="{{ $item->id_kecamatan }}">{{ $item->nama_kecamatan }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="jabatan" class="form-label">Jabatan</label>
                                    <select id="jabatan" name="jabatan"
                                        class="selectpicker form-control wide form-select-md" data-live-search="false"
                                        aria-label="Pilih Jabatan" required>
                                        <option value="">-- Pilih Jabatan --</option>
                                        @foreach ($jabatanKecamatan as $item)
                                            <option value="{{ $item->role_name }}">{{ $item->role_name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label for="unit_kerja" class="form-label">Unit Kerja</label>
                                    <input type="text" class="form-control form-control-sm" id="unit_kerja"
                                        name="unit_kerja" placeholder="Contoh: Bagian Umum" required>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat Lengkap</label>
                                <textarea class="form-control form-control-sm" id="alamat" name="alamat" rows="2"
                                    placeholder="Masukkan alamat lengkap" required></textarea>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="no_telp" class="form-label">Nomor Telepon</label>
                                    <input type="text" class="form-control form-control-sm" id="no_telp" name="no_telp"
                                        placeholder="Contoh: 082334511111" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="email" class="form-label">Alamat Email</label>
                                    <input type="email" class="form-control form-control-sm" id="email" name="email"
                                        placeholder="Contoh: imron@kecamatan.local">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="tanggal_awal" class="form-label">Tanggal Awal Menjabat</label>
                                    <input type="date" class="form-control form-control-sm" id="tanggal_awal"
                                        name="tanggal_awal" required>
                                </div>
                                <div class="col-md-6">
                                    <label for="tanggal_akhir" class="form-label">Tanggal Akhir Menjabat</label>
                                    <input type="date" class="form-control form-control-sm" id="tanggal_akhir"
                                        name="tanggal_akhir">
                                </div>
                            </div>

                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <label for="status" class="form-label">Status</label>
                                    <select id="status" name="status"
                                        class="selectpicker form-control wide form-select-md" data-live-search="false"
                                        required aria-label="Pilih Status">
                                        <option value="active">Aktif</option>
                                        <option value="inactive">Tidak aktif</option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label for="foto_petugas" class="form-label">Foto Petugas</label>
                                    <input type="file" class="form-control form-control-sm" id="foto_petugas"
                                        name="foto_petugas" accept="image/*">
                                    <small class="text-muted">Format: JPG, PNG. Maksimal 2MB.</small>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <button type="button" class="btn btn-secondary">Batal</button>
                                <button type="submit" form="createForm" class="btn btn-primary">Simpan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('this-page-scripts')
    @include('administration.masters.petugas.kecamatan.scripts.create-handler')
@endsection
