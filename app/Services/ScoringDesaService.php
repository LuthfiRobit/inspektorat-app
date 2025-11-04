<?php
// app/Services/ScoringDesaService.php

namespace App\Services;

use App\Models\LaporanKegiatan;
use App\Models\ScoringDesa;
use App\Models\Keterlambatan;
use App\Repositories\ScoringDesaRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScoringDesaService
{
    protected $scoringRepo;
    protected $keterlambatanService;

    public function __construct(
        ScoringDesaRepository $scoringRepo,
        KeterlambatanService $keterlambatanService
    ) {
        $this->scoringRepo = $scoringRepo;
        $this->keterlambatanService = $keterlambatanService;
    }

    /**
     * Hitung scoring untuk laporan
     */
    public function hitungScoring(LaporanKegiatan $laporan): ScoringDesa
    {
        try {
            // Hitung persyaratan wajib dan yang terpenuhi
            $totalWajib = $this->hitungTotalPersyaratanWajib($laporan);
            $terpenuhi = $this->hitungPersyaratanTerpenuhi($laporan);

            $persentaseKelengkapan = $totalWajib > 0
                ? ($terpenuhi / $totalWajib) * 100
                : 0;

            // Hitung skor ketepatan waktu
            $skorKetepatanWaktu = $this->hitungSkorKetepatanWaktu($laporan);

            // Total skor dengan bobot
            $totalSkor = $this->hitungTotalSkor($persentaseKelengkapan, $skorKetepatanWaktu);

            // Simpan scoring DENGAN KEGIATAN_ID
            $scoringData = [
                'desa_id' => $laporan->desa_id,
                'laporan_id' => $laporan->id_laporan,
                'kegiatan_id' => $laporan->kegiatan_id, // ← INI YANG BARU
                'tahun' => $laporan->tahun,
                'bulan' => $laporan->bulan,
                'total_persyaratan_wajib' => $totalWajib,
                'persyaratan_terpenuhi' => $terpenuhi,
                'persentase_kelengkapan' => round($persentaseKelengkapan, 2),
                'skor_ketepatan_waktu' => $skorKetepatanWaktu,
                'total_skor' => $totalSkor
            ];

            $scoring = $this->scoringRepo->updateOrCreate(
                ['laporan_id' => $laporan->id_laporan],
                $scoringData
            );

            Log::info('Scoring desa dihitung', [
                'laporan_id' => $laporan->id_laporan,
                'kegiatan_id' => $laporan->kegiatan_id,
                'total_skor' => $totalSkor
            ]);

            return $scoring;
        } catch (\Exception $e) {
            Log::error('Error menghitung scoring: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Hitung total persyaratan wajib untuk kegiatan
     */
    private function hitungTotalPersyaratanWajib(LaporanKegiatan $laporan): int
    {
        return DB::table('pertanyaan_kegiatan as pk')
            ->join('persyaratan as p', 'pk.id_pertanyaan', '=', 'p.pertanyaan_kegiatan_id')
            ->where('pk.kegiatan_id', $laporan->kegiatan_id)
            ->where('p.tipe', 'wajib')
            ->where('p.status', 'active')
            ->where('pk.status', 'active')
            ->count();
    }

    /**
     * Hitung persyaratan wajib yang sudah terpenuhi
     */
    private function hitungPersyaratanTerpenuhi(LaporanKegiatan $laporan): int
    {
        return DB::table('jawaban_pertanyaan as jp')
            ->join('dokumen_persyaratan as dp', 'jp.id_jawaban', '=', 'dp.jawaban_id')
            ->join('persyaratan as p', 'dp.persyaratan_id', '=', 'p.id_persyaratan')
            ->where('jp.laporan_id', $laporan->id_laporan)
            ->where('p.tipe', 'wajib')
            ->where('dp.status', 'approved') // Hanya dokumen yang sudah approved
            ->where('dp.is_current', true)
            ->count();
    }

    /**
     * Hitung skor ketepatan waktu (100 jika tepat waktu, 0 jika terlambat)
     */
    private function hitungSkorKetepatanWaktu(LaporanKegiatan $laporan): int
    {
        $keterlambatan = Keterlambatan::where('laporan_id', $laporan->id_laporan)->first();
        return ($keterlambatan && $keterlambatan->hari_keterlambatan > 0) ? 0 : 100;
    }

    /**
     * Hitung total skor dengan bobot
     */
    private function hitungTotalSkor(float $persentaseKelengkapan, int $skorKetepatanWaktu): int
    {
        // Bobot: 70% kelengkapan, 30% ketepatan waktu
        return (int) round(($persentaseKelengkapan * 0.7) + ($skorKetepatanWaktu * 0.3));
    }

    /**
     * Update peringkat UNTUK KEGIATAN TERTENTU
     */
    public function updatePeringkat($kegiatanId, $tahun, $bulan): void
    {
        try {
            $scorings = ScoringDesa::where('kegiatan_id', $kegiatanId)
                ->where('tahun', $tahun)
                ->where('bulan', $bulan)
                ->orderBy('total_skor', 'DESC')
                ->get();

            $peringkat = 1;
            foreach ($scorings as $scoring) {
                $scoring->update(['peringkat' => $peringkat++]);
            }

            Log::info('Peringkat updated per kegiatan', [
                'kegiatan_id' => $kegiatanId,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'total' => $scorings->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error update peringkat: ' . $e->getMessage());
        }
    }

    /**
     * Get ranking untuk kegiatan tertentu
     */
    public function getRankingByKegiatan($kegiatanId, $tahun = null, $bulan = null, $limit = null)
    {
        $query = ScoringDesa::with(['desa', 'kegiatan'])
            ->where('kegiatan_id', $kegiatanId)
            ->whereNotNull('peringkat')
            ->orderBy('peringkat', 'ASC');

        if ($tahun) {
            $query->where('tahun', $tahun);
        }

        if ($bulan) {
            $query->where('bulan', $bulan);
        }

        if ($limit) {
            $query->limit($limit);
        }

        return $query->get();
    }

    /**
     * Get peringkat 1 untuk kegiatan tertentu
     */
    public function getPeringkatPertama($kegiatanId, $tahun = null, $bulan = null): ?ScoringDesa
    {
        return $this->getRankingByKegiatan($kegiatanId, $tahun, $bulan, 1)->first();
    }

    /**
     * Get scoring statistics untuk kegiatan
     */
    public function getStatistikByKegiatan($kegiatanId, $tahun = null, $bulan = null): array
    {
        $query = ScoringDesa::where('kegiatan_id', $kegiatanId);

        if ($tahun) {
            $query->where('tahun', $tahun);
        }

        if ($bulan) {
            $query->where('bulan', $bulan);
        }

        return [
            'total_desa' => $query->count(),
            'rata_rata_skor' => round($query->avg('total_skor') ?? 0, 2),
            'skor_tertinggi' => $query->max('total_skor') ?? 0,
            'skor_terendah' => $query->min('total_skor') ?? 0,
            'total_tepat_waktu' => $query->where('skor_ketepatan_waktu', 100)->count(),
            'total_terlambat' => $query->where('skor_ketepatan_waktu', 0)->count(),
        ];
    }
}
