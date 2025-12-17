<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class ScoringDesa extends Model
{
    use HasFactory;

    protected $table = 'scoring_desa';
    protected $primaryKey = 'id_scoring';

    protected $fillable = [
        'desa_id',
        'laporan_id',
        'kegiatan_id',
        'tahun',
        'bulan',
        'total_persyaratan_wajib',
        'persyaratan_terpenuhi',
        'persentase_kelengkapan',
        'skor_ketepatan_waktu',
        'total_skor',
        'peringkat'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id_scoring' => 'integer',
        'desa_id' => 'integer',
        'laporan_id' => 'integer',
        'kegiatan_id' => 'integer',
        'tahun' => 'integer',
        'bulan' => 'integer',
        'total_persyaratan_wajib' => 'integer',
        'persyaratan_terpenuhi' => 'integer',
        'persentase_kelengkapan' => 'decimal:2',
        'skor_ketepatan_waktu' => 'integer',
        'total_skor' => 'integer',
        'peringkat' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class, 'desa_id', 'id_desa');
    }

    public function laporan(): BelongsTo
    {
        return $this->belongsTo(LaporanKegiatan::class, 'laporan_id', 'id_laporan');
    }

    // Scopes
    public function scopeByDesa($query, $desaId)
    {
        return $query->where('desa_id', $desaId);
    }

    public function scopeByPeriode($query, $tahun, $bulan = null)
    {
        $query->where('tahun', $tahun);
        if ($bulan) {
            $query->where('bulan', $bulan);
        }
        return $query;
    }

    public function scopeRanked($query)
    {
        return $query->whereNotNull('peringkat')->orderBy('peringkat', 'ASC');
    }

    public function scopeTopRank($query, $limit = 10)
    {
        return $query->ranked()->limit($limit);
    }

    // Helper methods
    public function getPersentaseKelengkapanFormatted(): string
    {
        return number_format($this->persentase_kelengkapan, 2) . '%';
    }

    public function getGrade(): string
    {
        if ($this->total_skor >= 90)
            return 'A';
        if ($this->total_skor >= 80)
            return 'B';
        if ($this->total_skor >= 70)
            return 'C';
        if ($this->total_skor >= 60)
            return 'D';
        return 'E';
    }

    public function getGradeColor(): string
    {
        return [
            'A' => 'success',
            'B' => 'primary',
            'C' => 'warning',
            'D' => 'info',
            'E' => 'danger'
        ][$this->getGrade()] ?? 'secondary';
    }

    public function getNamaBulanAttribute(): string
    {
        return [
            1 => 'Januari',
            2 => 'Februari',
            3 => 'Maret',
            4 => 'April',
            5 => 'Mei',
            6 => 'Juni',
            7 => 'Juli',
            8 => 'Agustus',
            9 => 'September',
            10 => 'Oktober',
            11 => 'November',
            12 => 'Desember'
        ][$this->bulan] ?? 'Unknown';
    }

    // Tambahkan relationship
    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class, 'kegiatan_id', 'id_kegiatan');
    }

    // Scope untuk filter per kegiatan
    public function scopeByKegiatan($query, $kegiatanId)
    {
        return $query->where('kegiatan_id', $kegiatanId);
    }

    // Scope untuk ranking per kegiatan
    public function scopeRankedByKegiatan($query, $kegiatanId, $tahun = null, $bulan = null)
    {
        $query->where('kegiatan_id', $kegiatanId);

        if ($tahun) {
            $query->where('tahun', $tahun);
        }

        if ($bulan) {
            $query->where('bulan', $bulan);
        }

        return $query->whereNotNull('peringkat')->orderBy('peringkat', 'ASC');
    }

    /**
     * GET SCORING DESA - AGGREGASI TANPA DUPLIKASI
     * 
     * Menghitung scoring semua desa tanpa duplikasi dengan agregasi data dari semua periode
     * Data dikelompokkan per desa dengan perhitungan kumulatif
     * 
     * @param array $filters Filter kecamatan, tahun, periode (opsional)
     * @return \Illuminate\Support\Collection
     */
    public static function getScoringDesa(array $filters = [])
    {
        $query = DB::table('desa as d')
            ->select(
                // Data Identitas Desa
                'd.id_desa',
                'd.nama_desa',
                'kec.id_kecamatan',
                'kec.nama_kecamatan',

                // Metrik Kegiatan - Agregasi Semua Periode
                DB::raw('COUNT(DISTINCT k.id_kegiatan) as jumlah_kegiatan'),
                DB::raw('COUNT(DISTINCT lk.id_laporan) as jumlah_kegiatan_terlapor'),

                // Metrik Dokumen - Agregasi Semua Periode
                DB::raw('COUNT(DISTINCT ps.id_persyaratan) as jumlah_kewajiban_dokumen'),
                DB::raw('COUNT(DISTINCT CASE WHEN dp.status = "approved" THEN dp.id_dokumen END) as jumlah_dokumen_approve'),

                // Perhitungan Persentase
                DB::raw('ROUND(
                CASE 
                    WHEN COUNT(DISTINCT ps.id_persyaratan) > 0 
                    THEN (COUNT(DISTINCT CASE WHEN dp.status = "approved" THEN dp.id_dokumen END) * 100.0 / COUNT(DISTINCT ps.id_persyaratan))
                    ELSE 0 
                END, 2
            ) as persentase_dokumen'),

                DB::raw('ROUND(
                CASE 
                    WHEN COUNT(DISTINCT k.id_kegiatan) > 0 
                    THEN (COUNT(DISTINCT lk.id_laporan) * 100.0 / COUNT(DISTINCT k.id_kegiatan))
                    ELSE 0 
                END, 2
            ) as persentase_kegiatan'),

                // Total Skor (Rata-rata kedua persentase)
                DB::raw('ROUND(
                (
                    CASE 
                        WHEN COUNT(DISTINCT ps.id_persyaratan) > 0 
                        THEN (COUNT(DISTINCT CASE WHEN dp.status = "approved" THEN dp.id_dokumen END) * 100.0 / COUNT(DISTINCT ps.id_persyaratan))
                        ELSE 0 
                    END +
                    CASE 
                        WHEN COUNT(DISTINCT k.id_kegiatan) > 0 
                        THEN (COUNT(DISTINCT lk.id_laporan) * 100.0 / COUNT(DISTINCT k.id_kegiatan))
                        ELSE 0 
                    END
                ) / 2, 2
            ) as total_skor')
            )
            // JOIN Hierarki Wilayah
            ->join('kecamatan as kec', 'd.kecamatan_id', '=', 'kec.id_kecamatan')

            // JOIN Data Kegiatan Aktif (semua tahun & periode)
            ->join('kegiatan as k', 'k.status', '=', DB::raw("'active'"))
            ->join('tahun_anggaran as ta', 'k.tahun_anggaran_id', '=', 'ta.id_tahun_anggaran')
            ->join('jenis_kegiatan as jk', 'k.jenis_kegiatan_id', '=', 'jk.id_jenis_kegiatan')

            // LEFT JOIN Data Laporan (semua yang approved)
            ->leftJoin('laporan_kegiatan as lk', function ($join) {
                $join->on('lk.desa_id', '=', 'd.id_desa')
                    ->on('lk.kegiatan_id', '=', 'k.id_kegiatan')
                    ->where('lk.status', 'approved');
            })

            // LEFT JOIN Persyaratan Wajib
            ->leftJoin('pertanyaan_kegiatan as pk', 'pk.kegiatan_id', '=', 'k.id_kegiatan')
            ->leftJoin('persyaratan as ps', function ($join) {
                $join->on('ps.pertanyaan_kegiatan_id', '=', 'pk.id_pertanyaan')
                    ->where('ps.tipe', 'wajib')
                    ->where('ps.status', 'active');
            })

            // LEFT JOIN Dokumen yang Sudah Diapprove
            ->leftJoin('jawaban_pertanyaan as jp', function ($join) {
                $join->on('jp.laporan_id', '=', 'lk.id_laporan')
                    ->on('jp.pertanyaan_id', '=', 'pk.id_pertanyaan');
            })
            ->leftJoin('dokumen_persyaratan as dp', function ($join) {
                $join->on('dp.jawaban_id', '=', 'jp.id_jawaban')
                    ->on('dp.persyaratan_id', '=', 'ps.id_persyaratan')
                    ->where('dp.status', 'approved')
                    ->where('dp.is_current', 1);
            })

            // Kondisi Status Aktif
            ->where('d.status', 'active')
            ->where('kec.status', 'active')
            ->where('ta.status', 'active')
            ->where('jk.status', 'active')

            // GROUP BY per Desa (tanpa tahun & periode)
            ->groupBy(
                'd.id_desa',
                'd.nama_desa',
                'kec.id_kecamatan',
                'kec.nama_kecamatan'
            );

        // APPLY FILTERS DINAMIS - PERBAIKAN: gunakan parameter yang benar
        self::applyDesaFilters($query, $filters);

        return $query->get();
    }

    /**
     * GET SCORING KECAMATAN - AGGREGASI TANPA DUPLIKASI
     * 
     * Menghitung scoring semua kecamatan tanpa duplikasi dengan agregasi data dari semua periode
     * Perhitungan dikalikan dengan jumlah desa di setiap kecamatan
     * 
     * @param array $filters Filter tahun, periode (opsional)
     * @return \Illuminate\Support\Collection
     */
    public static function getScoringKecamatan(array $filters = [])
    {
        $query = DB::table('kecamatan as kec')
            ->select(
                // Data Identitas Kecamatan
                'kec.id_kecamatan',
                'kec.nama_kecamatan',

                // Metrik Desa
                DB::raw('COUNT(DISTINCT d.id_desa) as jumlah_desa'),

                // Metrik Kegiatan - Dikalikan jumlah desa
                DB::raw('COUNT(DISTINCT k.id_kegiatan) * COUNT(DISTINCT d.id_desa) as jumlah_kegiatan'),
                DB::raw('COUNT(DISTINCT lk.id_laporan) as jumlah_kegiatan_terlapor'),

                // Metrik Dokumen - Dikalikan jumlah desa
                DB::raw('COUNT(DISTINCT ps.id_persyaratan) * COUNT(DISTINCT d.id_desa) as jumlah_kewajiban_dokumen'),
                DB::raw('COUNT(DISTINCT CASE WHEN dp.status = "approved" THEN dp.id_dokumen END) as jumlah_dokumen_approve'),

                // Perhitungan Persentase
                DB::raw('ROUND(
                    CASE 
                        WHEN COUNT(DISTINCT ps.id_persyaratan) * COUNT(DISTINCT d.id_desa) > 0 
                        THEN (COUNT(DISTINCT CASE WHEN dp.status = "approved" THEN dp.id_dokumen END) * 100.0 / (COUNT(DISTINCT ps.id_persyaratan) * COUNT(DISTINCT d.id_desa)))
                        ELSE 0 
                    END, 2
                ) as persentase_dokumen'),

                DB::raw('ROUND(
                    CASE 
                        WHEN COUNT(DISTINCT k.id_kegiatan) * COUNT(DISTINCT d.id_desa) > 0 
                        THEN (COUNT(DISTINCT lk.id_laporan) * 100.0 / (COUNT(DISTINCT k.id_kegiatan) * COUNT(DISTINCT d.id_desa)))
                        ELSE 0 
                    END, 2
                ) as persentase_kegiatan'),

                // Rata-rata Kegiatan per Desa
                DB::raw('ROUND(
                    CASE 
                        WHEN COUNT(DISTINCT d.id_desa) > 0 
                        THEN (COUNT(DISTINCT lk.id_laporan) * 1.0 / COUNT(DISTINCT d.id_desa))
                        ELSE 0 
                    END, 2
                ) as rata_kegiatan_per_desa'),

                // Total Skor
                DB::raw('ROUND(
                    (
                        CASE 
                            WHEN COUNT(DISTINCT ps.id_persyaratan) * COUNT(DISTINCT d.id_desa) > 0 
                            THEN (COUNT(DISTINCT CASE WHEN dp.status = "approved" THEN dp.id_dokumen END) * 100.0 / (COUNT(DISTINCT ps.id_persyaratan) * COUNT(DISTINCT d.id_desa)))
                            ELSE 0 
                        END +
                        CASE 
                            WHEN COUNT(DISTINCT k.id_kegiatan) * COUNT(DISTINCT d.id_desa) > 0 
                            THEN (COUNT(DISTINCT lk.id_laporan) * 100.0 / (COUNT(DISTINCT k.id_kegiatan) * COUNT(DISTINCT d.id_desa)))
                            ELSE 0 
                        END
                    ) / 2, 2
                ) as total_skor')
            )
            // JOIN Hierarki
            ->join('desa as d', 'd.kecamatan_id', '=', 'kec.id_kecamatan')
            ->join('kegiatan as k', 'k.status', '=', DB::raw("'active'"))
            ->join('tahun_anggaran as ta', 'k.tahun_anggaran_id', '=', 'ta.id_tahun_anggaran')
            ->join('jenis_kegiatan as jk', 'k.jenis_kegiatan_id', '=', 'jk.id_jenis_kegiatan')

            // LEFT JOIN Laporan
            ->leftJoin('laporan_kegiatan as lk', function ($join) {
                $join->on('lk.desa_id', '=', 'd.id_desa')
                    ->on('lk.kegiatan_id', '=', 'k.id_kegiatan')
                    ->where('lk.status', 'approved');
            })

            // LEFT JOIN Persyaratan & Dokumen
            ->leftJoin('pertanyaan_kegiatan as pk', 'pk.kegiatan_id', '=', 'k.id_kegiatan')
            ->leftJoin('persyaratan as ps', function ($join) {
                $join->on('ps.pertanyaan_kegiatan_id', '=', 'pk.id_pertanyaan')
                    ->where('ps.tipe', 'wajib')
                    ->where('ps.status', 'active');
            })
            ->leftJoin('jawaban_pertanyaan as jp', function ($join) {
                $join->on('jp.laporan_id', '=', 'lk.id_laporan')
                    ->on('jp.pertanyaan_id', '=', 'pk.id_pertanyaan');
            })
            ->leftJoin('dokumen_persyaratan as dp', function ($join) {
                $join->on('dp.jawaban_id', '=', 'jp.id_jawaban')
                    ->on('dp.persyaratan_id', '=', 'ps.id_persyaratan')
                    ->where('dp.status', 'approved')
                    ->where('dp.is_current', 1);
            })

            // Kondisi Status Aktif
            ->where('kec.status', 'active')
            ->where('d.status', 'active')
            ->where('ta.status', 'active')
            ->where('jk.status', 'active')

            // GROUP BY per Kecamatan (tanpa tahun & periode)
            ->groupBy('kec.id_kecamatan', 'kec.nama_kecamatan');

        // APPLY FILTERS
        self::applyKecamatanFilters($query, $filters);

        return $query->get();
    }

    /**
     * APPLY FILTERS UNTUK SCORING DESA
     * 
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $filters
     * @return void
     */
    private static function applyDesaFilters($query, array $filters)
    {
        // Filter Kecamatan - PERBAIKAN: gunakan nama field yang benar
        if (!empty($filters['kecamatan'])) {
            $query->where('kec.id_kecamatan', $filters['kecamatan']);
        }

        // Filter Tahun - PERBAIKAN: cari di tahun_anggaran
        if (!empty($filters['tahun'])) {
            $query->where('ta.id_tahun_anggaran', $filters['tahun']);
        }

        // Filter Periode (Bulan) - PERBAIKAN: cari di laporan_kegiatan
        if (!empty($filters['periode'])) {
            $query->where('k.bulan', $filters['periode']);
        }
    }

    /**
     * APPLY FILTERS UNTUK SCORING KECAMATAN
     * 
     * @param \Illuminate\Database\Query\Builder $query
     * @param array $filters
     * @return void
     */
    private static function applyKecamatanFilters($query, array $filters)
    {
        // Filter Tahun - PASTIKAN menggunakan id_tahun_anggaran
        if (!empty($filters['tahun'])) {
            $query->where('ta.id_tahun_anggaran', $filters['tahun']);
        }

        // Filter Periode (Bulan) - PASTIKAN menggunakan laporan_kegiatan
        if (!empty($filters['periode'])) {
            $query->where('k.bulan', $filters['periode']);
        }
    }
}
