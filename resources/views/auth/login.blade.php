<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    <!-- Judul Halaman -->
    <title>
        SIDESA-SAE - Inspektorat Kabupaten Probolinggo
    </title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Meta begin -->
    <!-- Set Karakter -->
    <meta charset="utf-8" />
    <!-- Mode Rendering -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- Penulis Halaman -->
    <meta name="author" content="Inspektorat Kabupaten Probolinggo" />
    <!-- Pengindeksan Mesin Pencari -->
    <meta name="robots" content="index, follow" />

    <!-- Kata Kunci SEO -->
    <meta name="keywords"
        content="SIDESA-SAE, Inspektorat Kabupaten Probolinggo, Monev Kegiatan Desa, Monitoring Evaluasi, Pelaporan Desa, Sistem Monev, Digitalisasi Pemerintahan, Transparansi, Kraksaan, Jawa Timur, Aplikasi Web, Laporan Digital, Pelaporan Online" />

    <!-- Deskripsi Halaman -->
    <meta name="description"
        content="SIDESA-SAE adalah sistem monitoring dan evaluasi (monev) milik Inspektorat Kabupaten Probolinggo untuk mendukung pelaporan, pengawasan, dan evaluasi kegiatan desa secara digital dan efisien." />

    <!-- Metadata Open Graph -->
    <meta property="og:title" content="SIDESA-SAE - Inspektorat Kabupaten Probolinggo" />
    <meta property="og:description"
        content="SIDESA-SAE adalah sistem monev milik Inspektorat Kabupaten Probolinggo untuk mendukung pelaporan dan evaluasi kegiatan desa secara transparan dan efisien." />
    <meta property="og:image" content="{{ asset('templates/administration/images/Logo_Kabupaten_Probolinggo.svg') }}" />

    <!-- Twitter Card Metadata -->
    <meta name="twitter:title" content="SIDESA-SAE - Inspektorat Kabupaten Probolinggo" />
    <meta name="twitter:description"
        content="SIDESA-SAE adalah sistem monev milik Inspektorat Kabupaten Probolinggo untuk mendukung pelaporan dan evaluasi kegiatan desa secara transparan dan efisien." />
    <meta name="twitter:image"
        content="{{ asset('templates/administration/images/Logo_Kabupaten_Probolinggo.svg') }}" />
    <meta name="twitter:card" content="summary_large_image" />
    <!-- Meta end -->

    <!-- Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="16x16"
        href="{{ asset('templates/administration/images/Logo_Kabupaten_Probolinggo.svg') }}" />
    @yield('this-page-style') <!-- Menyertakan Style tambahan dari halaman -->

    <style>
        .drop-shadow {
            filter: drop-shadow(0px 10px 20px rgba(0, 0, 0, 0.1));
        }

        .cursor-pointer {
            cursor: pointer;
        }
    </style>

    <!-- Global style start -->
    <link href="{{ asset('templates/administration/vendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}"
        rel="stylesheet" />
    <link class="main-css" href="{{ asset('templates/administration/css/style.css') }}" rel="stylesheet">
    <!-- Global style end -->

</head>

<body class="h-100">
    <div class="container-fluid p-0 m-0 min-vh-100">
        <div class="row g-0 min-vh-100">
            <!-- Sisi Form Login (Atas di Mobile, Kanan di Desktop) -->
            <div
                class="col-lg-5 col-12 d-flex align-items-center justify-content-center bg-white p-3 p-sm-5 order-1 order-lg-2">
                <div class="login-container w-100" style="max-width: 420px;">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center mb-4">
                            <div class="d-flex align-items-center me-3">
                                <img src="{{ asset('templates/administration/images/Logo-UNUJA.webp') }}"
                                    alt="Logo UNUJA" width="50" class="me-2" />
                                <img src="{{ asset('templates/administration/images/Logo_Kabupaten_Probolinggo.svg') }}"
                                    alt="Logo Kabupaten Probolinggo" width="50" />
                            </div>
                            <div class="text-start border-start border-2 ps-3" style="border-color: #eee !important;">
                                <h1 class="mb-0 fw-black"
                                    style="letter-spacing: 2px; line-height: 0.9; font-size: 32px;">
                                    <span class="text-dark">SIDESA-</span><span class="text-primary">SAE</span>
                                </h1>
                                <span class="text-muted fw-bold"
                                    style="font-size: 11px; letter-spacing: 1.5px; text-transform: uppercase;">Inspektorat</span>
                            </div>
                        </div>
                        <h4 class="fw-bold">Selamat Datang Kembali</h4>
                        <p class="text-muted small">Silakan masuk untuk melanjutkan akses ke Sistem Monev Kegiatan Desa.
                        </p>
                    </div>

                    <h6 class="text-center mb-3"><span class="border-bottom pb-1">Masuk ke SIDESA-SAE</span></h6>

                    <!-- Throttle Timer -->
                    <div class="alert alert-warning d-none" id="throttle-timer" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i>
                        Anda dapat mencoba lagi dalam <strong id="timer"></strong> detik.
                    </div>

                    <form id="loginForm" action="{{ route('login') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="login" class="form-label fw-semibold">Email atau Username</label>
                            <input type="text" class="form-control form-control-lg bg-light border-0" id="login"
                                name="login" placeholder="Masukkan email atau username" required>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label fw-semibold">Kata Sandi</label>
                            <div class="input-group">
                                <input type="password" class="form-control form-control-lg bg-light border-0"
                                    id="password" name="password" placeholder="Masukkan kata sandi" required>
                                <span class="input-group-text bg-light border-0 cursor-pointer"
                                    onclick="togglePassword()">
                                    <i class="fa fa-eye-slash" id="togglePasswordIcon"></i>
                                </span>
                            </div>
                        </div>
                        <div class="mb-4">
                            <div class="d-flex justify-content-center">
                                <div class="g-recaptcha" data-sitekey="{{ config('services.recaptcha.site_key') }}"></div>
                            </div>
                            @error('g-recaptcha-response')
                                <div class="text-danger small text-center mt-2">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                        <div class="form-check mb-4 d-flex justify-content-between align-items-center">
                            <div>
                                <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                <label class="form-check-label text-muted" for="remember">Ingat saya</label>
                            </div>
                            <a href="javascript:void(0);" class="text-decoration-none text-primary small fw-semibold"
                                data-bs-toggle="modal" data-bs-target="#forgotPasswordModal">Lupa kata sandi?</a>
                        </div>
                        <div class="d-grid mb-3">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm"
                                id="submitBtn">Masuk Aplikasi</button>
                        </div>
                    </form>

                    <div class="text-center mt-5">
                        <p class="text-muted small mb-0">&copy; {{ date('Y') }} Inspektorat Kab. Probolinggo.</p>
                    </div>
                </div>
            </div>

            <!-- Sisi Gambar Siklus (Bawah di Mobile, Kiri di Desktop) -->
            <div
                class="col-lg-7 col-12 d-flex bg-light align-items-center justify-content-center p-4 p-lg-5 border-end order-2 order-lg-1">
                <div class="text-center w-100">
                    <img src="{{ asset('templates/administration/images/siklus_perencanaan_desa.webp') }}"
                        alt="Siklus Perencanaan Desa" class="img-fluid drop-shadow"
                        style="max-height: 80vh; object-fit: contain;">
                </div>
            </div>
        </div>
    </div>

    <!-- Required vendors -->
     <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <script src="{{ asset('templates/administration/vendor/global/global.min.js') }}"></script>
    <script
        src="{{ asset('templates/administration/vendor/bootstrap-select/dist/js/bootstrap-select.min.js') }}"></script>
    <script src="{{ asset('templates/administration/js/custom.min.js') }}"></script>
    @include('administration.layouts.deznav') <!-- Digunakan karna default js tidak bisa load -->
    @include('administration.layouts.script')
    @include('scripts.globalHandler')

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Script token start -->
    <script>
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const icon = document.getElementById('togglePasswordIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            } else {
                passwordInput.type = 'password';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            }
        }
    </script>
    <!-- Script token end -->

    @include('auth.scripts.login')

    <!-- Modal Lupa Kata Sandi -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold" id="forgotPasswordModalLabel">
                        <i class="fas fa-info-circle text-primary me-2"></i>Informasi Lupa Kata Sandi
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center py-4">
                    <div class="mb-4">
                        <i class="fas fa-user-shield text-primary mb-3" style="font-size: 3rem;"></i>
                        <p class="text-muted">Untuk mereset atau mendapatkan kembali akses akun Anda, silakan hubungi
                            Administrator Inspektorat Kabupaten Probolinggo melalui kontak berikut:</p>
                    </div>

                    <div class="contact-info-list text-start mx-auto" style="max-width: 300px;">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-box bg-light text-primary rounded-circle p-2 me-3">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Email</small>
                                <a href="mailto:inspektorat@probolinggokab.go.id"
                                    class="fw-bold text-dark text-decoration-none">inspektorat@probolinggokab.go.id</a>
                            </div>
                        </div>

                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-light text-primary rounded-circle p-2 me-3">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Call Center</small>
                                <a href="tel:+62335844110"
                                    class="fw-bold text-dark text-decoration-none">+62335844110</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-primary w-100 py-2" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
</body>

</html>