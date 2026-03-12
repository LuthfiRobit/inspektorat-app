@extends('administration.layouts.app')

@section('title', 'Profil Pengguna | Sistem Pelaporan')
@section('meta-description', 'Halaman untuk mengubah data profil pengguna.')

@section('this-page-style')
    <style>
        /* Compact Form Styles from Petugas Edit */
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

        /* Custom overrides for profile */
        .profile-photo-preview {
            max-height: 120px;
            width: auto;
            border-radius: 0.375rem;
            border: 1px solid #dee2e6;
            margin-top: 0.5rem;
        }
    </style>
@endsection

@section('content')
    <div class="content-body default-height">
        <div class="container-fluid">
            <!-- Header - Compact -->
            <div class="form-head mb-3 d-flex align-items-center gap-2">
                <small class="text-muted">Manajemen Akun -</small>
                <h4 class="text-dark fw-semibold mb-0">Profil Pengguna</h4>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <!-- Card Header - Compact -->
                        <div class="card-header-compact border-0">
                            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                                <div>
                                    <h5 class="mb-1 fw-bold" style="font-size: 1rem;">Edit Profil</h5>
                                    <span class="text-muted small">Perbarui data diri dan keamanan akun Anda.</span>
                                </div>
                            </div>
                        </div>

                        <div class="card-body-compact">
                            <!-- Alert - Compact -->
                            <div class="alert alert-primary alert-compact mb-3">
                                <div class="d-flex align-items-start">
                                    <i class="las la-info-circle me-2 mt-1" style="font-size: 1.1rem;"></i>
                                    <div class="flex-grow-1">
                                        <strong style="font-size: 0.85rem;">Catatan:</strong>
                                        <ul class="mb-0 mt-1">
                                            <li>Kosongkan field "Password Baru" jika Anda tidak ingin mengubah password.</li>
                                            <li>Username Anda tidak dapat dirubah (menggunakan NIP).</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <form id="profile-form" action="javascript:void(0)" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="row">
                                    <div class="col-lg-7">
                                        <!-- Section 1: Data Identitas -->
                                        <div class="form-section h-100">
                                            <div class="form-section-title">
                                                <i class="las la-user me-1"></i> Data Identitas
                                            </div>

                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label for="nama_lengkap" class="form-label-sm">
                                                        Nama Lengkap <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" class="form-control form-control-sm" id="nama_lengkap" name="nama_lengkap"
                                                           value="{{ old('nama_lengkap', $petugas->nama_lengkap ?? $user->name) }}" placeholder="Masukkan nama lengkap" required />
                                                </div>

                                                <div class="col-md-6 col-12">
                                                    <label for="email" class="form-label-sm">
                                                        Alamat Email <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text"><i class="las la-envelope"></i></span>
                                                        <input type="email" class="form-control form-control-sm" id="email" name="email"
                                                               value="{{ old('email', $user->email) }}" placeholder="Masukkan email" required />
                                                    </div>
                                                </div>

                                                <div class="col-md-6 col-12">
                                                    <label for="no_telp" class="form-label-sm">
                                                        Nomor Telepon
                                                    </label>
                                                    <div class="input-group input-group-sm">
                                                        <span class="input-group-text"><i class="las la-phone"></i></span>
                                                        <input type="text" class="form-control form-control-sm" id="no_telp" name="no_telp"
                                                               value="{{ old('no_telp', $petugas->no_telp ?? '') }}" placeholder="Masukkan nomor telepon" />
                                                    </div>
                                                </div>

                                                <div class="col-12">
                                                    <label for="alamat" class="form-label-sm">
                                                        Alamat
                                                    </label>
                                                    <textarea class="form-control form-control-sm" id="alamat" name="alamat"
                                                              rows="3" placeholder="Masukkan alamat lengkap">{{ old('alamat', $petugas->alamat ?? '') }}</textarea>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-lg-5">
                                        <!-- Section 2: Keamanan Akun -->
                                        <div class="form-section mb-3">
                                            <div class="form-section-title">
                                                <i class="las la-lock me-1"></i> Keamanan Akun
                                            </div>

                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label class="form-label-sm">
                                                        Username Saat Ini
                                                    </label>
                                                    <input type="text" class="form-control form-control-sm bg-light" value="{{ $user->username }}" readonly disabled />
                                                </div>

                                                <div class="col-12">
                                                    <label for="password" class="form-label-sm">
                                                        Password Baru
                                                    </label>
                                                    <input type="password" class="form-control form-control-sm" id="password" name="password" placeholder="Minimal 8 karakter" minlength="8" />
                                                    <small class="form-text-helper">Kosongkan jika tidak ingin ganti password</small>
                                                </div>

                                                <div class="col-12">
                                                    <label for="password_confirmation" class="form-label-sm">
                                                        Konfirmasi Password Baru
                                                    </label>
                                                    <input type="password" class="form-control form-control-sm" id="password_confirmation" name="password_confirmation" placeholder="Ulangi password baru" minlength="8" />
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Section 3: Foto Profil -->
                                        <div class="form-section">
                                            <div class="form-section-title">
                                                <i class="las la-camera me-1"></i> Foto/Avatar Profile
                                            </div>

                                            <div class="row g-3">
                                                <div class="col-12">
                                                    <label for="foto_petugas" class="form-label-sm d-block text-start">
                                                        Upload Foto
                                                    </label>
                                                    <input type="file" class="form-control form-control-sm" id="foto_petugas" name="foto_petugas" accept="image/png, image/jpeg, image/jpg" />
                                                    <small class="form-text-helper d-block mt-1 mb-2">
                                                        <i class="las la-image"></i> Format: JPG, PNG. Maksimal 2MB.
                                                    </small>

                                                    <div class="mt-2 text-start" id="photo-preview-container" style="display: {{ isset($petugas) && $petugas->foto_petugas ? 'block' : 'none' }};">
                                                        <p class="mb-1 text-muted" style="font-size: 0.75rem;">Preview Foto:</p>
                                                        <img id="photo-preview-image" src="{{ isset($petugas) && $petugas->foto_petugas ? asset('uploads/' . $petugas->foto_petugas) : '#' }}" alt="Preview Foto" class="profile-photo-preview" />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Action Buttons - Sticky Bottom -->
                                <div class="d-flex justify-content-end gap-2 mt-3 pt-3 border-top">
                                    <button type="submit" class="btn btn-primary btn-action" id="btn-save">
                                        <i class="las la-save me-1"></i> Simpan Profil
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
<script>
    // Photo Preview Logic
    $('#foto_petugas').on('change', function() {
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function(e) {
                $('#photo-preview-image').attr('src', e.target.result);
                $('#photo-preview-container').show();
            }
            reader.readAsDataURL(this.files[0]);
        }
    });

    $('#btn-save').on('click', function (e) {
        e.preventDefault();
        
        // Basic HTML5 validation trigger
        if (!$('#profile-form')[0].checkValidity()) {
            $('#profile-form')[0].reportValidity();
            return;
        }

        // Custom Password Confirmation check
        var password = $('#password').val();
        var password_confirmation = $('#password_confirmation').val();

        if (password && password !== password_confirmation) {
            Swal.fire({
                icon: 'error',
                title: 'Validasi Gagal!',
                text: 'Konfirmasi password tidak cocok dengan password baru.',
            });
            return;
        }

        const formElement = document.getElementById('profile-form');
        const formData = new FormData(formElement);
        formData.append('_method', 'PUT');

        Swal.fire({
            title: 'Simpan Perubahan?',
            text: "Pastikan data profil sudah benar.",
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#1d8ae0',
            cancelButtonColor: '#f95f53',
            confirmButtonText: 'Ya, Simpan!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading state
                Swal.fire({
                    title: 'Menyimpan...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: '{{ route('administrator.profile.update') }}',
                    type: 'POST', // POST simulation for PUT
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function (response) {
                        if (response.status === 200) {
                            Swal.fire(
                                'Berhasil!',
                                'Profil berhasil diperbarui.',
                                'success'
                            ).then(() => {
                                window.location.reload();
                            });
                        } else {
                            Swal.fire(
                                'Error!',
                                response.message || "Gagal memperbarui profil.",
                                'error'
                            );
                        }
                    },
                    error: function (xhr) {
                        let errorMessage = 'Terjadi kesalahan sistem.';
                        if (xhr.status === 422) {
                            const errors = xhr.responseJSON.data || xhr.responseJSON.errors;
                            if (errors) {
                                errorMessage = Object.values(errors).map(e => e.join('<br>')).join('<br>');
                            }
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Validasi Gagal!',
                            html: errorMessage,
                        });
                    }
                });
            }
        });
    });
</script>
@endsection
