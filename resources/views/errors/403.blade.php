<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    <!-- Judul Halaman -->
    <title>
        Aplikasi Pembayaran Iuran Madrasah Ibtidaiyah Ihyauddiniyah Desa Kecik Besuk Probolinggo
    </title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Meta begin -->
    <!-- Set Karakter -->
    <meta charset="utf-8" />
    <!-- Mode Rendering -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <!-- Penulis Halaman -->
    <meta name="author" content="Madrasah Ibtidaiyah Ihyauddiniyah" />
    <!-- Pengindeksan Mesin Pencari -->
    <meta name="robots" content="index, follow" />

    <!-- Kata Kunci SEO -->
    <meta name="keywords"
        content="Pembayaran Iuran, Madrasah Ibtidaiyah Ihyauddiniyah, Desa Kecik Besuk, Probolinggo, Aplikasi Pembayaran, Sistem Pembayaran Iuran, Pendidikan, Madrasah Ibtidaiyah, Manajemen Keuangan, Desain Responsif, UI Modern, Aplikasi Web, Sistem Administrasi, Pembayaran Mudah, Pengelolaan Iuran, Formulir Pembayaran, Pembayaran Online, Solusi Keuangan Madrasah" />

    <!-- Deskripsi Halaman -->
    <meta name="description"
        content="Aplikasi Pembayaran Iuran untuk Madrasah Ibtidaiyah Ihyauddiniyah di Desa Kecik Besuk Probolinggo. Aplikasi ini memudahkan proses pembayaran iuran, melacak transaksi keuangan, dan mengelola administrasi dengan desain responsif dan fitur yang user-friendly." />

    <!-- Metadata Open Graph -->
    <meta property="og:title"
        content="Aplikasi Pembayaran Iuran Madrasah Ibtidaiyah Ihyauddiniyah Desa Kecik Besuk Probolinggo" />
    <meta property="og:description"
        content="Aplikasi Pembayaran Iuran untuk Madrasah Ibtidaiyah Ihyauddiniyah di Desa Kecik Besuk Probolinggo. Aplikasi ini memudahkan proses pembayaran iuran, melacak transaksi keuangan, dan mengelola administrasi dengan desain responsif dan fitur yang user-friendly." />
    <meta property="og:image" content="{{ asset('template/social-image.png') }}" />

    <!-- Twitter Card Metadata -->
    <meta name="twitter:title"
        content="Aplikasi Pembayaran Iuran Madrasah Ibtidaiyah Ihyauddiniyah Desa Kecik Besuk Probolinggo" />
    <meta name="twitter:description"
        content="Aplikasi Pembayaran Iuran untuk Madrasah Ibtidaiyah Ihyauddiniyah di Desa Kecik Besuk Probolinggo. Aplikasi ini memudahkan proses pembayaran iuran, melacak transaksi keuangan, dan mengelola administrasi dengan desain responsif dan fitur yang user-friendly." />
    <meta name="twitter:image" content="{{ asset('template/social-image.png') }}" />
    <meta name="twitter:card" content="summary_large_image" />
    <!-- Meta end -->

    <!-- Mobile Specific -->
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="16x16"
        href="{{ asset('templates/administration/images/logo_mi.png') }}" />
    @yield('this-page-style') <!-- Menyertakan Style
        tambahan dari halaman -->

    <!-- Global style start -->
    <link href="{{ asset('templates/administration/vendor/bootstrap-select/dist/css/bootstrap-select.min.css') }}"
        rel="stylesheet" />
    <!-- Global style end -->
    <link class="main-css" href="{{ asset('templates/administration/css/style.css') }}" rel="stylesheet">

</head>

<body class="h-100">
    <div class="authincation fix-wrapper"
        style="background-image: url({{ asset('templates/administration/images/student-bg.jpg') }}); background-repeat:no-repeat; background-size:cover;">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 col-sm-12">
                    <div class="form-input-content  error-page">
                        <h1 class="error-text text-primary">403</h1>
                        <h4> Forbidden Error!</h4>
                        <p>You do not have permission to view this resource.</p>
                        <a class="btn btn-primary" href="{{ route('administrator.dashboard.index') }}">Back to Home</a>
                    </div>
                </div>
                <div class="col-lg-6 col-sm-12">
                    <img class="w-100" src="{{ asset('templates/administration/images/under-m.png') }}" alt="">
                </div>
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
</body>

</html>
