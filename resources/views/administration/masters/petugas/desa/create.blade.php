@extends('administration.layouts.app')

@section('title', 'Tambah Petugas Desa | Sistem Pelaporan')
@section('meta-description', 'Form untuk menambah data petugas Desa baru.')

@section('this-page-style')
    <style>
        /* Compact Form Styles */
        .card-header-compact {
            padding: 0.75rem 1rem;
            background-color: #f8f9fa;
            border-bottom: 1px solid #dee2e6;
        }

        .card-body-compact {
            padding: 1rem;
        }

        .form-label-sm {
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: #495057;
        }

        .form-section {
            background: #f8f9fa;
            border-radius: 0.375rem;
            padding: 1rem;
            margin-bottom: 1rem;
        }

        .form-section-title {
            font-size: 0.85rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #495057;
            margin-bottom: 0.75rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #dee2e6;
        }

        .alert-compact {
            padding: 0.75rem 1rem;
            font-size: 0.85rem;
        }

        .alert-compact ul {
            margin-bottom: 0.5rem;
            padding-left: 1.25rem;
        }

        .alert-compact li {
            font-size: 0.8rem;
            margin-bottom: 0.25rem;
        }

        .form-text-helper {
            font-size: 0.7rem;
            color: #6c757d;
            font-style: italic;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .form-section {
                padding: 0.75rem;
            }

            .card-body-compact {
                padding: 0.75rem;
            }

            .form-head {
                margin-bottom: 0.75rem !important;
            }
        }

        /* Form control sizing */
        .form-control-sm {
            font-size: 0.85rem;
            padding: 0.375rem 0.75rem;
        }

        .form-select-sm {
            font-size: 0.85rem;
            padding: 0.375rem 2.25rem 0.375rem 0.75rem;
        }

        /* Button sizing */
        .btn-action {
            padding: 0.5rem 1.25rem;
            font-size: 0.875rem;
        }

        /* Radio inline compact */
        .form-check-inline {
            margin-right: 1rem;
        }

        .form-check-label {
            font-size: 0.85rem;
        }
    </style>
@endsection

@section('content')
    <div class="content-body default-height">
        <div class="container-fluid">
            <!-- Header - Compact -->
            <div class="form-head mb-3 d-flex align-items-center gap-2">
                <small class="text-muted">Master Data -</small>
                <h4 class="text-dark fw-semibold mb-0">Petugas Desa</h4>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <!-- Card Header - Compact -->
                        <div class="card-header-compact border-0">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <h5 class="mb-1 fw-bold" style="font-size: 1rem;">Tambah Data Petugas</h5>
                                    <span class="text-muted small">Lengkapi formulir berikut untuk menambah petugas
                                        baru.</span>
                                </div>
                                <a href="{{ route('administrator.master.petugas.desa.index') }}"
                                    class="btn btn-outline-primary btn-sm" title="Kembali">
                                    <i class="las la-arrow-left me-1"></i> Kembali
                                </a>
                            </div>
                        </div>

                        <div class="card-body-compact">
                            <!-- Alert - Compact -->
                            <div class="alert alert-primary alert-compact mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="las la-info-circle me-2 mt-1" style="font-size: 1.1rem;"></i>
                                    <div class="flex-grow-1">
                                        <strong style="font-size: 0.85rem;">Catatan Penting:</strong>
                                        <ul class="mb-0 mt-1">
                                            <li>Isi semua kolom dengan benar dan sesuai ketentuan</li>
                                            <li>Pastikan <strong>NIP</strong> diisi dengan format yang benar</li>
                                            <li>NIP akan digunakan sebagai <strong>username dan password awal</strong>
                                                petugas</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <form id="createForm" method="POST" enctype="multipart/form-data">
                                @csrf

                                <!-- Section 1: Data Identitas -->
                                <div class="form-section">
                                    <div class="form-section-title">
                                        <i class="las la-user me-1"></i> Data Identitas
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-lg-6 col-md-6 col-12">
                                            <label for="nip" class="form-label-sm">
                                                Nomor Induk Pegawai (NIP) <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control form-control-sm" id="nip" name="nip"
                                                maxlength="20" placeholder="Contoh: 19651012 199203 2 005" required>
                                            <small class="form-text-helper">
                                                <i class="las la-key"></i> NIP akan digunakan sebagai
                                                <strong>username</strong> dan <strong>password awal</strong>
                                            </small>
                                        </div>

                                        <div class="col-lg-6 col-md-6 col-12">
                                            <label for="nama_lengkap" class="form-label-sm">
                                                Nama Lengkap <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control form-control-sm" id="nama_lengkap"
                                                name="nama_lengkap" placeholder="Contoh: Imron Rosyadi, SP.MM.CGCAE"
                                                required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 2: Penempatan -->
                                <div class="form-section">
                                    <div class="form-section-title">
                                        <i class="las la-map-marked-alt me-1"></i> Penempatan & Jabatan
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-lg-6 col-md-6 col-12">
                                            <label for="kecamatan_id" class="form-label-sm">
                                                Kecamatan <span class="text-danger">*</span>
                                            </label>
                                            <select id="kecamatan_id" name="kecamatan_id"
                                                class="selectpicker form-control wide form-select-sm"
                                                data-live-search="true" aria-label="Pilih kecamatan" data-size="5" required>
                                                <option value="">-- Pilih Kecamatan --</option>
                                                @foreach ($kecamatanList as $item)
                                                    <option value="{{ $item->id_kecamatan }}">{{ $item->nama_kecamatan }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-6 col-md-6 col-12">
                                            <label for="desa_id" class="form-label-sm">
                                                Desa <span class="text-danger">*</span>
                                            </label>
                                            <select id="desa_id" name="desa_id"
                                                class="selectpicker form-control wide form-select-sm"
                                                data-live-search="true" aria-label="Pilih desa" data-size="5" required>
                                                <option value="">-- Pilih Desa --</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-6 col-md-6 col-12">
                                            <label for="jabatan" class="form-label-sm">
                                                Jabatan <span class="text-danger">*</span>
                                            </label>
                                            <select id="jabatan" name="jabatan"
                                                class="selectpicker form-control wide form-select-sm"
                                                data-live-search="true" aria-label="Pilih Jabatan" data-size="5" required>
                                                <option value="">-- Pilih Jabatan --</option>
                                                @foreach ($jabatanDesa as $item)
                                                    <option value="{{ $item->role_name }}">{{ $item->role_name }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-lg-6 col-md-6 col-12">
                                            <label for="unit_kerja" class="form-label-sm">
                                                Unit Kerja <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" class="form-control form-control-sm" id="unit_kerja"
                                                name="unit_kerja" placeholder="Contoh: Bagian Umum" required>
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 3: Kontak & Alamat -->
                                <div class="form-section">
                                    <div class="form-section-title">
                                        <i class="las la-address-book me-1"></i> Kontak & Alamat
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-12">
                                            <label for="alamat" class="form-label-sm">
                                                Alamat Lengkap <span class="text-danger">*</span>
                                            </label>
                                            <textarea class="form-control form-control-sm" id="alamat" name="alamat"
                                                rows="2" placeholder="Masukkan alamat lengkap" required></textarea>
                                        </div>

                                        <div class="col-lg-6 col-md-6 col-12">
                                            <label for="no_telp" class="form-label-sm">
                                                Nomor Telepon <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text"><i class="las la-phone"></i></span>
                                                <input type="text" class="form-control form-control-sm" id="no_telp"
                                                    name="no_telp" placeholder="Contoh: 082334511111" required>
                                            </div>
                                        </div>

                                        <div class="col-lg-6 col-md-6 col-12">
                                            <label for="email" class="form-label-sm">
                                                Alamat Email <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text"><i class="las la-envelope"></i></span>
                                                <input type="email" class="form-control form-control-sm" id="email"
                                                    name="email" placeholder="Contoh: imron@desalocal" required>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Section 4: Periode Jabatan & Status -->
                                <div class="form-section">
                                    <div class="form-section-title">
                                        <i class="las la-calendar-alt me-1"></i> Periode Jabatan & Status
                                    </div>

                                    <div class="row g-3">
                                        <div class="col-lg-6 col-md-6 col-12">
                                            <label for="tanggal_awal" class="form-label-sm">
                                                Tanggal Awal Menjabat <span class="text-danger">*</span>
                                            </label>
                                            <input type="date" class="form-control form-control-sm" id="tanggal_awal"
                                                name="tanggal_awal" required>
                                        </div>

                                        <div class="col-lg-6 col-md-6 col-12">
                                            <label for="tanggal_akhir" class="form-label-sm">
                                                Tanggal Akhir Menjabat
                                            </label>
                                            <input type="date" class="form-control form-control-sm" id="tanggal_akhir"
                                                name="tanggal_akhir">
                                            <small class="form-text-helper">Kosongkan jika masih aktif</small>
                                        </div>

                                        <div class="col-lg-6 col-md-6 col-12">
                                            <label for="status" class="form-label-sm">
                                                Status Petugas <span class="text-danger">*</span>
                                            </label>
                                            <select id="status" name="status"
                                                class="selectpicker form-control wide form-select-sm"
                                                data-live-search="false" required aria-label="Pilih Status">
                                                <option value="active">Aktif</option>
                                                <option value="inactive">Tidak aktif</option>
                                            </select>
                                        </div>

                                        <div class="col-lg-6 col-md-6 col-12">
                                            <label for="foto_petugas" class="form-label-sm">
                                                Foto Petugas
                                            </label>
                                            <input type="file" class="form-control form-control-sm" id="foto_petugas"
                                                name="foto_petugas" accept="image/*">
                                            <small class="form-text-helper">
                                                <i class="las la-image"></i> Format: JPG, PNG. Maksimal 2MB
                                            </small>
                                        </div>

                                        <div class="col-12">
                                            <div class="border rounded p-3 bg-white">
                                                <label class="form-label-sm d-block mb-2">
                                                    Akses Login <span class="text-danger">*</span>
                                                </label>
                                                <div class="d-flex flex-wrap gap-3">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="akses_login"
                                                            id="akses_login_active" value="active" checked>
                                                        <label class="form-check-label" for="akses_login_active">
                                                            <i class="las la-check-circle text-success"></i> Aktif
                                                        </label>
                                                    </div>
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="radio" name="akses_login"
                                                            id="akses_login_inactive" value="inactive">
                                                        <label class="form-check-label" for="akses_login_inactive">
                                                            <i class="las la-times-circle text-danger"></i> Tidak Aktif
                                                        </label>
                                                    </div>
                                                </div>
                                                <small class="form-text-helper d-block mt-2">
                                                    <i class="las la-info-circle"></i> Jika tidak aktif, petugas tidak dapat
                                                    login ke sistem
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons - Sticky Bottom -->
                                <div class="d-flex justify-content-between gap-2 mt-3 pt-3 border-top">
                                    <a href="{{ route('administrator.master.petugas.desa.index') }}"
                                        class="btn btn-secondary btn-action">
                                        <i class="las la-times me-1"></i> Batal
                                    </a>
                                    <button type="submit" form="createForm" class="btn btn-primary btn-action">
                                        <i class="las la-save me-1"></i> Simpan Data
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('this-page-scripts')
    @include('administration.masters.petugas.desa.scripts.create-handler')
@endsection