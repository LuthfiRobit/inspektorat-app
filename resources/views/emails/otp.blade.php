<!DOCTYPE html>
<html>
<head>
    <title>Verifikasi Login OTP - SIDESA-SAE</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: #333; line-height: 1.6; max-width: 550px; margin: 0 auto; padding: 20px; background-color: #fdfdfd;">
    
    <!-- HEADER -->
    <div style="border-bottom: 2px solid #2980b9; padding-bottom: 20px; margin-bottom: 30px; text-align: center;">
        <div style="display: inline-flex; align-items: center; justify-content: center; width: 100%;">
            <img src="https://upload.wikimedia.org/wikipedia/commons/thumb/e/e6/Logo_Kabupaten_Probolinggo_-_Seal_of_Probolinggo_Regency.svg/1920px-Logo_Kabupaten_Probolinggo_-_Seal_of_Probolinggo_Regency.svg.png" alt="Logo Kabupaten Probolinggo" width="55" height="55" border="0" style="margin-right: 15px; display: block;">
            <div style="text-align: left; border-left: 2px solid #eee; padding-left: 15px; vertical-align: middle; display: inline-block;">
                <h1 style="margin: 0; font-size: 26px; font-weight: 900; letter-spacing: 1px;">
                    <span style="color: #2c3e50;">SIDESA-</span><span style="color: #3498db;">SAE</span>
                </h1>
                <div style="font-size: 10px; font-weight: bold; letter-spacing: 1px; color: #7f8c8d; margin-top: -3px;">
                    INSPEKTORAT KAB. PROBOLINGGO
                </div>
            </div>
        </div>
    </div>
    <!-- /HEADER -->
    
    <p style="font-size: 16px;">Halo, <strong>{{ $userName }}</strong></p>
    
    <p style="font-size: 15px; color: #555;">
        Anda menerima email ini karena kami mendeteksi upaya login ke akun Anda. Untuk melanjutkan dan memastikan keamanan akun, silakan gunakan kode rahasia <strong>OTP (One-Time Password)</strong> berikut:
    </p>
    
    <!-- OTP BLOCK -->
    <div style="background-color: #f4f6f7; border: 1px dashed #bdc3c7; border-radius: 8px; padding: 25px; text-align: center; margin: 25px 0;">
        <div style="font-size: 38px; font-weight: bold; letter-spacing: 12px; color: #e74c3c; margin-bottom: 15px; user-select: all; padding: 15px; background-color: #fff; border: 1px solid #e0e0e0; border-radius: 6px; display: inline-block;" title="Blok angka ini untuk menyalin">
            {{ $otpCode }}
        </div>
        <p style="font-size: 12px; color: #7f8c8d; margin-top: 5px;">*Ketuk dua kali (klik ganda) angka di atas, lalu tekan Salin *(Copy)*</p>
    </div>
    <!-- /OTP BLOCK -->
    
    <div style="background-color: #fff3cd; border-left: 4px solid #ffecb5; padding: 12px 15px; font-size: 14px; color: #856404; margin-bottom: 20px;">
        <strong>Perhatian:</strong> Kode ini hanya berlaku selama <strong>3 Menit</strong>. Jangan pernah membagikan kode ini kepada siapapun demi keamanan akun Anda.
    </div>
    
    <p style="font-size: 14px; color: #666;">
        Jika Anda merasa tidak melakukan login baru-baru ini, segera hubungi administrator sistem atau abaikan email ini.
    </p>
    
    <div style="margin-top: 40px; border-top: 1px solid #eaeaea; padding-top: 20px; font-size: 12px; color: #999; text-align: center;">
        &copy; {{ date('Y') }} Inspektorat Daerah Kabupaten Probolinggo. Seluruh hak cipta dilindungi.<br>
        Email ini dikirim secara otomatis oleh sistem, mohon untuk tidak membalas pesan ini.
    </div>
    
</body>
</html>
