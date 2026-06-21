<!DOCTYPE html>
<html>
<head>
    <title>Hasil Review Laporan: {{ $statusReview }} - SIDESA-SAE</title>
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
    
    <p style="font-size: 16px;">Halo, <strong>Tim Kecamatan / Desa</strong></p>
    
    <p style="font-size: 15px; color: #555;">
        Laporan kegiatan yang Anda kirimkan telah selesai <strong>direview</strong> oleh Inspektorat. Berikut adalah rincian laporan beserta status terbarunya:
    </p>
    
    <!-- DETAILS BLOCK -->
    <div style="background-color: #f4f6f7; border: 1px solid #bdc3c7; border-radius: 8px; padding: 20px; margin: 25px 0;">
        <table style="width: 100%; border-collapse: collapse; font-size: 14px;">
            <tr>
                <td style="padding: 5px 0; color: #7f8c8d; width: 30%;"><strong>Nama Kegiatan</strong></td>
                <td style="padding: 5px 0; color: #2c3e50;">: {{ $kegiatanName }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 0; color: #7f8c8d;"><strong>Asal Desa</strong></td>
                <td style="padding: 5px 0; color: #2c3e50;">: {{ $desaName }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 0; color: #7f8c8d;"><strong>Periode</strong></td>
                <td style="padding: 5px 0; color: #2c3e50;">: {{ $periode }}</td>
            </tr>
            <tr>
                <td style="padding: 5px 0; color: #7f8c8d;"><strong>Hasil Review</strong></td>
                <td style="padding: 5px 0;">: 
                    @if($statusReview == 'Disetujui')
                        <span style="background-color: #2ecc71; color: white; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">Disetujui</span>
                    @else
                        <span style="background-color: #e74c3c; color: white; padding: 3px 8px; border-radius: 4px; font-size: 12px; font-weight: bold;">Revisi</span>
                    @endif
                </td>
            </tr>
        </table>
    </div>
    <!-- /DETAILS BLOCK -->
    
    @if($statusReview == 'Revisi')
        <div style="background-color: #fff3cd; border-left: 4px solid #ffecb5; padding: 12px 15px; font-size: 14px; color: #856404; margin-bottom: 20px;">
            <strong>Perhatian:</strong> Laporan ini memerlukan <strong>revisi</strong>. Harap periksa catatan dari Inspektorat dan segera unggah dokumen perbaikannya.
        </div>
    @endif
    
    <div style="text-align: center; margin-top: 30px; margin-bottom: 30px;">
        <a href="{{ $url }}" style="background-color: #2980b9; color: #ffffff; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; font-size: 15px; display: inline-block;">Lihat Detail Laporan</a>
    </div>
    
    <div style="margin-top: 40px; border-top: 1px solid #eaeaea; padding-top: 20px; font-size: 12px; color: #999; text-align: center;">
        &copy; {{ date('Y') }} Inspektorat Daerah Kabupaten Probolinggo. Seluruh hak cipta dilindungi.<br>
        Email ini dikirim secara otomatis oleh sistem, mohon untuk tidak membalas pesan ini.
    </div>
    
</body>
</html>
