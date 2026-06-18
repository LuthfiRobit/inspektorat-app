<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    <!-- Judul Halaman -->
    <title>Verifikasi OTP - SIDESA-SAE</title>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- Meta begin -->
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="16x16"
        href="{{ asset('templates/administration/images/Logo_Kabupaten_Probolinggo.svg') }}" />

    <style>
        .drop-shadow {
            filter: drop-shadow(0px 10px 20px rgba(0, 0, 0, 0.1));
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
            <!-- Sisi Form OTP -->
            <div
                class="col-lg-5 col-12 d-flex align-items-center justify-content-center bg-white p-3 p-sm-5 order-1 order-lg-2">
                <div class="login-container w-100" style="max-width: 420px;">
                    <div class="text-center mb-4">
                        <div class="d-inline-flex align-items-center justify-content-center mb-4">
                            <div class="d-flex align-items-center me-3">
                                <img src="{{ asset('templates/administration/images/Logo_Kabupaten_Probolinggo.svg') }}"
                                    alt="Logo Kabupaten Probolinggo" width="50" />
                            </div>
                            <div class="text-start border-start border-2 ps-3" style="border-color: #eee !important;">
                                <h1 class="mb-0 fw-black"
                                    style="letter-spacing: 2px; line-height: 0.9; font-size: 32px;">
                                    <span class="text-dark">SIDESA-</span><span class="text-primary">SAE</span>
                                </h1>
                            </div>
                        </div>
                        <h4 class="fw-bold">Verifikasi 2 Langkah</h4>
                        <p class="text-muted small">Kami telah mengirimkan 6 digit kode OTP ke email Anda. Berlaku selama 3 menit. <br><span class="text-danger fw-bold">Catatan:</span> Jika email belum masuk, silakan periksa kotak <strong>Spam</strong> atau <strong>Junk</strong>.</p>
                    </div>

                    <form id="otpForm" action="{{ route('otp.verify.post') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="otp_code" class="form-label fw-semibold text-center w-100">Kode OTP</label>
                            <input type="text" class="form-control form-control-lg bg-light border-0 text-center fw-bold" id="otp_code"
                                name="otp_code" placeholder="0 0 0 0 0 0" maxlength="6" style="letter-spacing: 10px; font-size: 24px;" required autocomplete="off">
                        </div>

                        <div class="d-grid mb-3 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill shadow-sm"
                                id="submitBtn">Verifikasi & Masuk</button>
                        </div>
                    </form>

                    <div class="text-center mt-4">
                        <p class="text-muted small mb-2">Belum menerima email?</p>
                        <button type="button" id="resendOtpBtn" class="btn btn-outline-secondary btn-sm rounded-pill px-4">
                            Kirim Ulang Kode
                        </button>
                    </div>
                </div>
            </div>

            <!-- Sisi Gambar Siklus -->
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
    <script src="{{ asset('templates/administration/vendor/global/global.min.js') }}"></script>
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

    @include('auth.scripts.otp')

</body>

</html>
