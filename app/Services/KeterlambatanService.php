<?php
// app/Services/KeterlambatanService.php

namespace App\Services;

use App\Models\LaporanKegiatan;
use App\Models\Kegiatan;
use App\Models\Keterlambatan;
use App\Repositories\KeterlambatanRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class KeterlambatanService
{
    protected $keterlambatanRepo;

    public function __construct(KeterlambatanRepository $keterlambatanRepo)
    {
        $this->keterlambatanRepo = $keterlambatanRepo;
    }

    /**
     * Update atau create record keterlambatan untuk laporan
     * Memastikan satu laporan hanya punya satu record keterlambatan
     */
    public function updateKeterlambatan(LaporanKegiatan $laporan): int
    {
        try {
            $kegiatan = $laporan->kegiatan;

            if (!$kegiatan || !$kegiatan->batas_akhir_upload) {
                Log::info('Kegiatan atau batas_akhir_upload tidak ditemukan', [
                    'laporan_id' => $laporan->id_laporan,
                    'kegiatan_id' => $laporan->kegiatan_id
                ]);
                return 0;
            }

            // Hitung tanggal target dengan Centralized Logic
            // Pass $laporan object untuk context Rutin/Insidentil yang benar
            $tanggalTarget = LaporanKegiatan::calculateTanggalTarget($kegiatan, $laporan);

            if (!$tanggalTarget) {
                return 0;
            }

            $tanggalUpload = $laporan->tanggal_submit ? Carbon::parse($laporan->tanggal_submit) : now();

            // Hitung hari keterlambatan
            $hariKeterlambatan = $this->hitungHariKeterlambatan($tanggalTarget, $tanggalUpload);

            // Gunakan updateOrCreate untuk memastikan satu record per laporan
            Keterlambatan::updateOrCreate(
                ['laporan_id' => $laporan->id_laporan], // Condition: cari berdasarkan laporan_id
                [ // Data yang akan di-update atau di-create
                    'desa_id' => $laporan->desa_id,
                    'tanggal_target' => $tanggalTarget,
                    'tanggal_upload' => $tanggalUpload,
                    'hari_keterlambatan' => $hariKeterlambatan
                ]
            );

            Log::info('Keterlambatan diupdate/dicreate (V3 Logic)', [
                'laporan_id' => $laporan->id_laporan,
                'hari_keterlambatan' => $hariKeterlambatan,
                'tanggal_target' => $tanggalTarget->format('Y-m-d'),
                'tanggal_upload' => $tanggalUpload->format('Y-m-d')
            ]);

            return $hariKeterlambatan;
        } catch (\Exception $e) {
            Log::error('Error update keterlambatan: ' . $e->getMessage(), [
                'laporan_id' => $laporan->id_laporan,
                'exception' => $e
            ]);
            return 0;
        }
    }

    /**
     * @deprecated Use LaporanKegiatan::calculateTanggalTarget
     */
    private function hitungTanggalTarget(Kegiatan $kegiatan, $tahun, $bulan): ?Carbon
    {
        return LaporanKegiatan::calculateTanggalTarget($kegiatan, $tahun, $bulan);
    }

    /**
     * Hitung hari keterlambatan
     * Keterlambatan = selisih hari antara upload dan target
     */
    private function hitungHariKeterlambatan(Carbon $tanggalTarget, Carbon $tanggalUpload): int
    {
        // Jika upload <= target, tepat waktu (0 hari keterlambatan)
        if ($tanggalUpload->lte($tanggalTarget)) {
            return 0;
        }

        // Jika upload > target, hitung selisih hari absolute
        return (int) $tanggalTarget->diffInDays($tanggalUpload);
    }

    /**
     * Get keterlambatan by laporan ID
     */
    public function getByLaporan($laporanId): ?Keterlambatan
    {
        return Keterlambatan::where('laporan_id', $laporanId)->first();
    }

    /**
     * Hapus keterlambatan (jika diperlukan untuk rollback)
     */
    public function deleteByLaporan($laporanId): bool
    {
        return Keterlambatan::where('laporan_id', $laporanId)->delete();
    }
}
