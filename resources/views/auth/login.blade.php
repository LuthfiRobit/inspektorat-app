<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    <!-- Judul Halaman -->
    <title>
        SIDESA APK - Inspektorat Kabupaten Probolinggo
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
        content="SIDESA APK, Inspektorat Kabupaten Probolinggo, Monev Kegiatan Desa, Monitoring Evaluasi, Pelaporan Desa, Sistem Monev, Digitalisasi Pemerintahan, Transparansi, Kraksaan, Jawa Timur, Aplikasi Web, Laporan Digital, Pelaporan Online" />

    <!-- Deskripsi Halaman -->
    <meta name="description"
        content="SIDESA APK adalah sistem monitoring dan evaluasi (monev) milik Inspektorat Kabupaten Probolinggo untuk mendukung pelaporan, pengawasan, dan evaluasi kegiatan desa secara digital dan efisien." />

    <!-- Metadata Open Graph -->
    <meta property="og:title" content="SIDESA APK - Inspektorat Kabupaten Probolinggo" />
    <meta property="og:description" content="SIDESA APK adalah sistem monev milik Inspektorat Kabupaten Probolinggo untuk mendukung pelaporan dan evaluasi kegiatan desa secara transparan dan efisien." />
    <meta property="og:image" content="{{ asset('templates/administration/images/Logo_Kabupaten_Probolinggo.svg') }}" />

    <!-- Twitter Card Metadata -->
    <meta name="twitter:title" content="SIDESA APK - Inspektorat Kabupaten Probolinggo" />
    <meta name="twitter:description" content="SIDESA APK adalah sistem monev milik Inspektorat Kabupaten Probolinggo untuk mendukung pelaporan dan evaluasi kegiatan desa secara transparan dan efisien." />
    <meta name="twitter:image" content="{{ asset('templates/administration/images/Logo_Kabupaten_Probolinggo.svg') }}" />
    <meta name="twitter:card" content="summary_large_image" />
    <!-- Meta end -->

    <!-- Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="16x16"
        href="{{ asset('templates/administration/images/Logo_Kabupaten_Probolinggo.svg') }}" />
    @yield('this-page-style') <!-- Menyertakan Style
        tambahan dari halaman -->

    <!-- Global style start -->
    <link href="{{ asset('templates/administration/vendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}"
        rel="stylesheet" />
    <link class="main-css" href="{{ asset('templates/administration/css/style.css') }}" rel="stylesheet">
    <!-- Global style end -->

</head>

<body class="h-100">
    <!-- Background Image Container -->
    <div
        style="background-image: url({{ asset('templates/administration/images/student-bg.jpg') }}); background-repeat: no-repeat; background-size: cover; min-height: 100vh;">
        <div class="d-flex justify-content-center align-items-center px-3 px-sm-4"
            style="min-height: 100vh; backdrop-filter: brightness(0.9);">
            <div class="login-container p-4 p-md-5 rounded shadow-lg w-100"
                style="background-color: rgba(255, 255, 255, 0.92); max-width: 420px;">
                <div class="text-center mb-4">
                    <div class="d-inline-flex align-items-center mb-4">
                        <img src="{{ asset('templates/administration/images/Logo_Kabupaten_Probolinggo.svg') }}"
                            alt="Logo Kabupaten Probolinggo" width="60" class="me-3" />
                        <div class="text-start border-start border-2 ps-3" style="border-color: #eee !important;">
                            <h1 class="mb-0 fw-black" style="letter-spacing: 2px; line-height: 0.9; font-size: 32px;">
                                <span class="text-dark">SIDESA</span><span class="text-primary">APK</span>
                            </h1>
                            <span class="text-muted fw-bold" style="font-size: 11px; letter-spacing: 1.5px; text-transform: uppercase;">Inspektorat</span>
                        </div>
                    </div>
                    <h4 class="fw-bold">Selamat Datang Kembali</h4>
                    <p class="text-muted small">Silakan masuk untuk melanjutkan akses ke Sistem Monev Kegiatan Desa.</p>
                </div>

                <h6 class="text-center mb-3"><span class="border-bottom pb-1">Masuk ke SIDESA APK</span></h6>

                <!-- Throttle Timer -->
                <div class="alert alert-warning d-none" id="throttle-timer" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    Anda dapat mencoba lagi dalam <strong id="timer"></strong> detik.
                </div>

                <form id="loginForm" action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="login" class="form-label">Email atau Username</label>
                        <input type="text" class="form-control" id="login" name="login"
                            placeholder="Masukkan email atau username" required>
                    </div>
                    <div class="mb-3">
                        <label for="password" class="form-label">Kata Sandi</label>
                        <input type="password" class="form-control" id="password" name="password"
                            placeholder="Masukkan kata sandi" required>
                    </div>
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="remember" name="remember">
                        <label class="form-check-label" for="remember">Ingat saya</label>
                    </div>
                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary" id="submitBtn">Masuk</button>
                    </div>
                    <div class="text-center">
                        <a href="javascript:void(0);" class="text-decoration-none text-muted small" data-bs-toggle="modal"
                            data-bs-target="#forgotPasswordModal">Lupa kata sandi?</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Required vendors -->
    <script src="{{ asset('templates/administration/vendor/global/global.min.js') }}"></script>
    <script src="{{ asset('templates/administration/vendor/bootstrap-select/dist/js/bootstrap-select.min.js') }}"></script>
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
                        <p class="text-muted">Untuk mereset atau mendapatkan kembali akses akun Anda, silakan hubungi Administrator Inspektorat Kabupaten Probolinggo melalui kontak berikut:</p>
                    </div>

                    <div class="contact-info-list text-start mx-auto" style="max-width: 300px;">
                        <div class="d-flex align-items-center mb-3">
                            <div class="icon-box bg-light text-primary rounded-circle p-2 me-3">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Email</small>
                                <a href="mailto:inspektorat@probolinggokab.go.id" class="fw-bold text-dark text-decoration-none">inspektorat@probolinggokab.go.id</a>
                            </div>
                        </div>

                        <div class="d-flex align-items-center">
                            <div class="icon-box bg-light text-primary rounded-circle p-2 me-3">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Call Center</small>
                                <a href="tel:+62335844110" class="fw-bold text-dark text-decoration-none">+62335844110</a>
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
