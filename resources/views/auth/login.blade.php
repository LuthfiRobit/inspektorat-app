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
                    <h3 class="fw-bold">Selamat Datang di SIDESA APK</h3>
                    <p class="text-muted small">Masukkan email/username dan kata sandi Anda untuk masuk ke SIDESA APK — Sistem Monev Kegiatan Desa.</p>
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
                        <a href="#" class="text-decoration-none text-muted small">Lupa kata sandi?</a>
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
</body>

</html>
