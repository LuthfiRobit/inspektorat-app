@extends('administration.layouts.app')

@section('title', 'Edit Petugas Inspektorat | Sistem Pelaporan')
@section('meta-description', 'Halaman untuk mengubah data petugas Inspektorat.')

@section('this-page-style')
@endsection

@section('content')
    <div class="content-body default-height">
        <div class="container-fluid">

            <div class="form-head mb-4 d-flex align-items-center gap-2">
                <small class="text-muted">Master Data -</small>
                <h4 class="text-dark fw-semibold mb-0">Edit Petugas Inspektorat</h4>
            </div>

            <div class="row">
                <div class="card">
                    <div class="card-header d-sm-flex d-block border-0 pb-0 flex-wrap align-items-center">
                        <div class="me-auto mb-sm-0 mb-3">
                            <h4 class="fs-20 text-black mb-1">Ubah Data Petugas</h4>
                            <span class="fs-12 text-muted">Perbarui informasi petugas sesuai data terbaru.</span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <a href="{{ route('administrator.master.petugas.inspektorat.index') }}"
                                class="btn btn-outline-primary btn-sm btn-rounded light" title="Kembali">
                                <i class="las la-arrow-left scale5 me-1"></i> Kembali
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="alert alert-primary">
                            <strong>Catatan:</strong>
                            <ul class="mb-0">
                                <li>Pastikan data yang diubah sudah benar sebelum disimpan.</li>
                                <li>NIP digunakan sebagai <strong>username</strong> petugas dan <strong>tidak dapat
                                        diubah</strong>.</li>
                            </ul>
                        </div>

                        <form id="editForm" method="post" class="form-sm" data-id="">

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="edit_nip" class="form-label">Nomor Induk Pegawai (NIP)</label>
                                    <input type="text" class="form-control form-control-sm" id="edit_nip" name="nip"
                                        placeholder="Masukkan NIP" aria-label="Nomor Induk Pegawai" maxlength="20"
                                        autocomplete="off" readonly />
                                    <small class="text-muted fst-italic">*NIP digunakan sebagai username petugas dan tidak
                                        dapat diubah.</small>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="edit_nama_lengkap" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control form-control-sm" id="edit_nama_lengkap"
                                        name="nama_lengkap" placeholder="Masukkan nama lengkap" aria-label="Nama Lengkap"
                                        maxlength="100" autocomplete="off" required />
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="edit_jabatan" class="form-label">Jabatan</label>
                                    <select id="edit_jabatan" name="jabatan"
                                        class="selectpicker form-control wide form-select-md" data-live-search="true"
                                        required aria-label="Pilih Jabatan" data-size="5" placeholder="Pilih Jabatan">
                                        <option value="">-- Pilih Jabatan --</option>
                                        @foreach ($jabatanInspektorat as $item)
                                            <option value="{{ $item->role_name }}">{{ $item->role_name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="edit_unit_kerja" class="form-label">Unit Kerja</label>
                                    <input type="text" class="form-control form-control-sm" id="edit_unit_kerja"
                                        name="unit_kerja" placeholder="Masukkan unit kerja" aria-label="Unit Kerja"
                                        maxlength="100" autocomplete="off" required />
                                </div>

                                <div class="col-md-12 mb-3">
                                    <label for="edit_alamat" class="form-label">Alamat Lengkap</label>
                                    <textarea class="form-control form-control-sm" id="edit_alamat" name="alamat" rows="2"
                                        placeholder="Masukkan alamat lengkap" aria-label="Alamat" required></textarea>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="edit_no_telp" class="form-label">Nomor Telepon</label>
                                    <input type="text" class="form-control form-control-sm" id="edit_no_telp"
                                        name="no_telp" placeholder="Masukkan nomor telepon" aria-label="Nomor Telepon"
                                        maxlength="20" autocomplete="off" required />
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="edit_email" class="form-label">Alamat Email</label>
                                    <input type="email" class="form-control form-control-sm" id="edit_email"
                                        name="email" placeholder="Masukkan email" aria-label="Email" maxlength="100"
                                        autocomplete="off" />
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="edit_tanggal_awal" class="form-label">Tanggal Awal Menjabat</label>
                                    <input type="date" class="form-control form-control-sm" id="edit_tanggal_awal"
                                        name="tanggal_awal" aria-label="Tanggal Awal Menjabat" required />
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="edit_tanggal_akhir" class="form-label">Tanggal Akhir Menjabat</label>
                                    <input type="date" class="form-control form-control-sm" id="edit_tanggal_akhir"
                                        name="tanggal_akhir" aria-label="Tanggal Akhir Menjabat" />
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label for="edit_status" class="form-label">Status</label>
                                    <select id="edit_status" name="status"
                                        class="selectpicker form-control wide form-select-md" data-live-search="false"
                                        required aria-label="Pilih Status">
                                        <option value="active">Aktif</option>
                                        <option value="inactive">Tidak aktif</option>
                                    </select>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <div class="form-label d-flex justify-content-between align-items-center">
                                        <label for="edit_foto_petugas">Foto Petugas</label>
                                        <!-- Tempat link Lihat Foto -->
                                        <div id="link-container"></div>
                                    </div>
                                    <input type="file" class="form-control form-control-sm" id="edit_foto_petugas"
                                        name="foto_petugas" accept="image/*" aria-label="Upload Foto Petugas" />
                                    <small class="text-muted">Kosongkan jika tidak ingin mengubah foto.</small>
                                </div>
                            </div>
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('administrator.master.petugas.inspektorat.index') }}"
                                    class="btn btn-secondary">Batal</a>
                                <button type="submit" form="editForm" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('this-page-scripts')
    @include('administration.masters.petugas.inspektorat.scripts.edit-handler')
@endsection
