<?php
// app/Services/ScoringDesaService.php

namespace App\Services;

use App\Models\Desa;
use App\Models\Kecamatan;
use App\Models\Kegiatan;
use App\Models\Keterlambatan;
use App\Models\LaporanKegiatan;
use App\Models\ScoringDesa;
use App\Repositories\ScoringDesaRepository;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ScoringDesaService
{
    protected $scoringRepo;
    protected $keterlambatanService;

    public function __construct(ScoringDesaRepository $scoringRepo, KeterlambatanService $keterlambatanService)
    {
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

    /**
     * GET DATA SCORING DESA DENGAN RANKING BARU
     * 
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    public function getDesaData(array $filters = [])
    {
        // Cache data for performance
        $cacheKey = 'scoring_desa_v2_' . md5(json_encode($filters));

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($filters) {

            // 1. Get List of Desa (filtered)
            $desaQuery = Desa::with('kecamatan')->where('status', 'active');
            if (!empty($filters['kecamatan'])) {
                $desaQuery->where('kecamatan_id', $filters['kecamatan']);
            }
            if (!empty($filters['filter_desa'])) { // Handle potential filter naming mismatch in controller
                $desaQuery->where('id_desa', $filters['filter_desa']);
            }
            $desas = $desaQuery->get();

            // 2. Get List of Active Kegiatan (filtered by Year/Period)
            $kegiatanQuery = Kegiatan::where('status', 'active')
                ->whereHas('jenisKegiatan', function ($q) {
                    $q->where('status', 'active');
                })
                ->whereHas('tahunAnggaran', function ($q) {
                    $q->where('status', 'active');
                });

            if (!empty($filters['tahun'])) {
                $kegiatanQuery->whereHas('tahunAnggaran', function ($q) use ($filters) {
                    $q->where('id_tahun_anggaran', $filters['tahun']);
                });
            }
            if (!empty($filters['periode'])) {
                $kegiatanQuery->where('bulan', $filters['periode']);
            }
            $kegiatans = $kegiatanQuery->get();
            $totalKegiatanWajib = $kegiatans->count();

            // 3. Eager Load Laporans & Documents for ALL Desas to avoid N+1
            // We fetch all relevant reports for these desas and kegiatans
            $laporans = LaporanKegiatan::with([
                'jawaban_pertanyaan.dokumen.persyaratan'
            ])
                ->whereIn('desa_id', $desas->pluck('id_desa'))
                ->whereIn('kegiatan_id', $kegiatans->pluck('id_kegiatan'))
                ->whereIn('status', ['submitted', 'approved']) // Hanya yang sudah submit yang dinilai
                ->get()
                ->groupBy('desa_id');

            // 4. Calculate Scores per Desa
            $result = $desas->map(function ($desa) use ($kegiatans, $laporans, $totalKegiatanWajib) {
                $desaLaporans = $laporans->get($desa->id_desa, collect());

                $totalSkor = 0;
                $submitDates = [];
                $docWajibCount = 0;
                $docTambahanCount = 0;
                $kegiatanTerlapor = 0;

                foreach ($kegiatans as $kegiatan) {
                    // Find report for this kegiatan
                    $laporan = $desaLaporans->firstWhere('kegiatan_id', $kegiatan->id_kegiatan);

                    if ($laporan) {
                        $kegiatanTerlapor++;

                        // A. Calculate Timeliness Score
                        $submittedAt = Carbon::parse($laporan->tanggal_submit);
                        $deadline = LaporanKegiatan::calculateTanggalTarget($kegiatan, $kegiatan->tahunAnggaran->tahun ?? $laporan->tahun, $kegiatan->bulan);

                        $score = 0;
                        if ($deadline) {
                            $diffDays = 0;

                            if ($submittedAt->lte($deadline)) {
                                $score = 1;
                            } else {
                                // Telat
                                $diffDays = $submittedAt->diffInDays($deadline);

                                // User rule: dikurangi 0.1 setiap harinya sampai 0
                                $penalty = $diffDays * 0.1;
                                $score = max(0, 1 - $penalty);
                            }
                        } else {
                            $score = 1;
                        }
                        $totalSkor += $score;
                        $submitDates[] = $submittedAt->timestamp;

                        // B. Count Documents
                        foreach ($laporan->jawaban_pertanyaan as $jawaban) {
                            foreach ($jawaban->dokumen as $dokumen) {
                                if ($dokumen->status === 'approved' && $dokumen->is_current) {
                                    if ($dokumen->persyaratan->tipe === 'wajib') {
                                        $docWajibCount++;
                                    } else {
                                        $docTambahanCount++;
                                    }
                                }
                            }
                        }
                    }
                }

                return (object) [
                    'id_desa' => $desa->id_desa,
                    'nama_desa' => $desa->nama_desa,
                    'nama_kecamatan' => $desa->kecamatan->nama_kecamatan ?? '-',
                    'kecamatan_id' => $desa->kecamatan_id,
                    'total_skor' => $totalSkor,
                    // Untuk tie-breaker submit tercepat, ambil min timestamp. Jika tidak ada laporan, set max int.
                    'earliest_submit' => !empty($submitDates) ? min($submitDates) : PHP_INT_MAX,
                    'earliest_submit_formatted' => !empty($submitDates) ? Carbon::createFromTimestamp(min($submitDates))->format('d M Y H:i') : '-',
                    'jumlah_dokumen_wajib' => $docWajibCount,
                    'jumlah_dokumen_tambahan' => $docTambahanCount,
                    'kegiatan_terlapor' => $kegiatanTerlapor,
                    'kegiatan_belum_terlapor' => $totalKegiatanWajib - $kegiatanTerlapor,
                    'persentase_kegiatan' => $totalKegiatanWajib > 0 ? ($kegiatanTerlapor / $totalKegiatanWajib) * 100 : 0
                ];
            });

            // 5. Ranking Logic (Sort)
            $sorted = $result->sort(function ($a, $b) {
                // Criteria 1: Total Skor (Desc)
                if (abs($a->total_skor - $b->total_skor) > 0.001) {
                    return $b->total_skor <=> $a->total_skor;
                }

                // Criteria 2: Earliest Submit Date (Asc)
                if ($a->earliest_submit !== $b->earliest_submit) {
                    return $a->earliest_submit <=> $b->earliest_submit;
                }

                // Criteria 3: Doc Wajib (Desc)
                if ($a->jumlah_dokumen_wajib !== $b->jumlah_dokumen_wajib) {
                    return $b->jumlah_dokumen_wajib <=> $a->jumlah_dokumen_wajib;
                }

                // Criteria 4: Doc Tambahan (Desc)
                return $b->jumlah_dokumen_tambahan <=> $a->jumlah_dokumen_tambahan;
            });

            // 6. Assign Rank & Format
            $rank = 1;
            $formatted = $sorted->map(function ($item) use (&$rank) {
                // Add formatting properties expected by controller/view
                $item->peringkat = $rank++;
                $item->persentase_dokumen_formatted = '-'; // Legacy field, optional
                $item->persentase_kegiatan_formatted = number_format($item->persentase_kegiatan, 0) . '%';
                $item->total_skor_formatted = number_format($item->total_skor, 2); // Show decimals
                $item->peringkat_badge = $this->getRankBadge($item->peringkat);

                return $item;
            });

            return $formatted->values();
        });
    }

    /**
     * GET DATA SCORING KECAMATAN DENGAN RANKING BARU (AKUMULASI DESA)
     * 
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    public function getKecamatanData(array $filters = [])
    {
        $cacheKey = 'scoring_kecamatan_v2_' . md5(json_encode($filters));

        return Cache::remember($cacheKey, now()->addMinutes(5), function () use ($filters) {

            // 1. Get Desa Data (Reuse logic)
            $desaData = $this->getDesaData($filters);

            // 2. Group by Kecamatan and Aggregate
            $grouped = $desaData->groupBy('kecamatan_id');

            $kecamatans = Kecamatan::where('status', 'active')->get()->keyBy('id_kecamatan');

            $result = $grouped->map(function ($desas, $kecamatanId) use ($kecamatans) {
                $kecamatan = $kecamatans->get($kecamatanId);
                if (!$kecamatan)
                    return null;

                $jumlahDesa = $desas->count();
                $totalSkorAccumulated = $desas->sum('total_skor');
                $totalTerlapor = $desas->sum('kegiatan_terlapor');
                $totalBelumTerlapor = $desas->sum('kegiatan_belum_terlapor');

                return (object) [
                    'id_kecamatan' => $kecamatanId,
                    'nama_kecamatan' => $kecamatan->nama_kecamatan,
                    'jumlah_desa' => $jumlahDesa,
                    'total_skor' => $totalSkorAccumulated, // Akumulasi
                    'rata_rata_skor' => $jumlahDesa > 0 ? $totalSkorAccumulated / $jumlahDesa : 0,
                    'kegiatan_terlapor' => $totalTerlapor,
                    'kegiatan_belum_terlapor' => $totalBelumTerlapor,

                    // Legacy attributes to prevent errors
                    'persentase_dokumen_formatted' => '-',
                    'persentase_kegiatan_formatted' => '-',
                ];
            })->filter()->values();

            // 3. Sort by Total Skor (Accumulated)
            $sorted = $result->sort(function ($a, $b) {
                return $b->total_skor <=> $a->total_skor;
            });

            // 4. Assign Rank
            $rank = 1;
            return $sorted->map(function ($item) use (&$rank) {
                $item->peringkat = $rank++;
                $item->total_skor_formatted = number_format($item->total_skor, 2);
                $item->rata_kegiatan_formatted = number_format($item->rata_rata_skor, 2);
                $item->peringkat_badge = $this->getRankBadge($item->peringkat);
                return $item;
            })->values();
        });
    }

    /**
     * GET DETAIL DATA SCORING KECAMATAN (LIST DESA)
     *
     * @param int $kecamatanId
     * @param array $filters
     * @return object
     */
    public function getKecamatanDetailData(int $kecamatanId, array $filters = [])
    {
        // 1. Get Kecamatan Info
        $kecamatan = Kecamatan::findOrFail($kecamatanId);

        // 2. Get Desa Data for this Kecamatan (Reuse existing logic)
        // We force the filter for this kecamatan
        $filters['kecamatan'] = $kecamatanId;
        $desaData = $this->getDesaData($filters); // This returns sorted collection

        // 3. Calculate Summary
        $summary = [
            'total_skor' => $desaData->sum('total_skor'),
            'rata_rata_skor' => $desaData->count() > 0 ? $desaData->sum('total_skor') / $desaData->count() : 0,
            'total_desa' => $desaData->count(),
            'total_kegiatan_terlapor' => $desaData->sum('kegiatan_terlapor'),
            'total_kegiatan_belum' => $desaData->sum('kegiatan_belum_terlapor'),
        ];

        return (object) [
            'kecamatan' => $kecamatan,
            'desas' => $desaData,
            'summary' => $summary
        ];
    }

    /**
     * GET DETAIL DATA SCORING DESA (BREAKDOWN)
     *
     * @param int $desaId
     * @param array $filters
     * @return object
     */
    public function getDesaDetailData(int $desaId, array $filters = [])
    {
        // 1. Get Desa Info
        $desa = Desa::with('kecamatan')->findOrFail($desaId);

        // 2. Get List of Active Kegiatan 
        $kegiatanQuery = Kegiatan::where('status', 'active')
            ->whereHas('jenisKegiatan', function ($q) {
                $q->where('status', 'active');
            })
            ->whereHas('tahunAnggaran', function ($q) {
                $q->where('status', 'active');
            });

        if (!empty($filters['tahun'])) {
            $kegiatanQuery->whereHas('tahunAnggaran', function ($q) use ($filters) {
                $q->where('id_tahun_anggaran', $filters['tahun']);
            });
        }
        if (!empty($filters['periode'])) {
            $kegiatanQuery->where('bulan', $filters['periode']);
        }
        $kegiatans = $kegiatanQuery->get();

        // 3. Adjust dates for display if needed
        $kegiatans->transform(function ($item) {
            $item->nama_bulan = $item->nama_bulan; // Accessor should handle this
            return $item;
        });

        // 4. Get Laporans for this Desa
        $laporans = LaporanKegiatan::with([
            'jawaban_pertanyaan.dokumen.persyaratan'
        ])
            ->where('desa_id', $desaId)
            ->whereIn('kegiatan_id', $kegiatans->pluck('id_kegiatan'))
            ->get()
            ->keyBy('kegiatan_id');

        // 5. Build Detail Set
        $details = $kegiatans->map(function ($kegiatan) use ($laporans) {
            $laporan = $laporans->get($kegiatan->id_kegiatan);

            $detail = [
                'nama_kegiatan' => $kegiatan->nama_kegiatan,
                'status' => $laporan ? $laporan->status : 'belum_dilaporkan',
                'tanggal_target' => '-',
                'tanggal_submit' => '-',
                'late_days' => 0,
                'timeliness_score' => 0,
                'doc_wajib_approved' => 0,
                'doc_wajib_total' => 0, // This needs requirement count logic ideally, but simpler is counting types
                'doc_tambahan_approved' => 0
            ];

            // Calculate Target (Deadline)
            $deadline = LaporanKegiatan::calculateTanggalTarget($kegiatan, $kegiatan->tahunAnggaran->tahun ?? ($laporan->tahun ?? date('Y')), $kegiatan->bulan);
            $detail['tanggal_target'] = $deadline ? $deadline->format('d M Y') : '-';

            if ($laporan && in_array($laporan->status, ['submitted', 'approved'])) {
                $submittedAt = Carbon::parse($laporan->tanggal_submit);
                $detail['tanggal_submit'] = $submittedAt->format('d M Y H:i');

                // Timeliness Logic
                $score = 0;
                if ($deadline) {
                    if ($submittedAt->lte($deadline)) {
                        $score = 1;
                        $detail['late_days'] = 0;
                    } else {
                        $diffDays = $submittedAt->diffInDays($deadline);
                        $penalty = $diffDays * 0.1;
                        $score = max(0, 1 - $penalty);
                        $detail['late_days'] = $diffDays;
                    }
                } else {
                    $score = 1;
                }
                $detail['timeliness_score'] = $score;

                // Document Logic
                // We rely on approved documents for count
                // Ideally we should know how many mandatory Requirements exist for accurate "2/2" display
                // For now let's just count Approved Wajib vs Total Wajib referenced in answers? 
                // Creating a simplified count:
                foreach ($laporan->jawaban_pertanyaan as $jawaban) {
                    foreach ($jawaban->dokumen as $dokumen) {
                        if ($dokumen->status === 'approved' && $dokumen->is_current) {
                            if ($dokumen->persyaratan->tipe === 'wajib') {
                                $detail['doc_wajib_approved']++;
                            } else {
                                $detail['doc_tambahan_approved']++;
                            }
                        }
                    }
                }
            }

            return (object) $detail;
        });

        // Calculate Header Stats
        $totalSkor = $details->sum('timeliness_score');
        $terlapor = $details->filter(fn($d) => in_array($d->status, ['submitted', 'approved']))->count();


        return (object) [
            'desa' => $desa,
            'details' => $details,
            'summary' => [
                'total_skor' => $totalSkor,
                'total_kegiatan' => $kegiatans->count(),
                'terlapor' => $terlapor,
                'belum_terlapor' => $kegiatans->count() - $terlapor
            ]
        ];
    }

    /**
     * CLEAR CACHE UNTUK REFRESH DATA
     * 
     * @return void
     */
    public function clearCache()
    {
        Cache::flush();
    }

    private function getRankBadge($rank)
    {
        $badgeClass = match ($rank) {
            1 => 'badge-warning',
            2 => 'badge-secondary',
            3 => 'badge-dark',
            default => 'badge-light'
        };

        $color = match ($rank) {
            1 => '#FFD700',
            2 => '#C0C0C0',
            3 => '#CD7F32',
            default => '#f0f0f0'
        };
        $text = match ($rank) {
            1, 2, 3 => 'white',
            default => 'black'
        };

        if ($rank <= 3) {
            return '<span class="badge" style="background-color: ' . $color . '; color: ' . $text . '">' . $rank . '</span>';
        }
        return '<span class="badge badge-light">' . $rank . '</span>';
    }
}
