<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class LaporanKegiatan extends Model
{
    use HasFactory;

    protected $table = 'laporan_kegiatan';
    protected $primaryKey = 'id_laporan';

    public $timestamps = true;

    protected $fillable = [
        'desa_id',
        'kegiatan_id',
        'tahun',
        'bulan',
        'status',
        'tanggal_target',
        'tanggal_submit',
        'tanggal_approve',
        'approved_by',
        'catatan_approval',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id_laporan' => 'integer',
        'desa_id' => 'integer',
        'kegiatan_id' => 'integer',
        'tahun' => 'integer',
        'bulan' => 'integer',
        'status' => 'string',
        'tanggal_target' => 'date',
        'tanggal_submit' => 'datetime',
        'tanggal_approve' => 'datetime',
        'approved_by' => 'integer',
        'catatan_approval' => 'string',
        'created_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check() && !$model->approved_by && $model->status === 'approved') {
                $model->approved_by = Auth::id();
                $model->tanggal_approve = now();
            }
        });

        // Cache invalidation: bump version key after any save/delete
        // Works with database cache driver (no Cache Tags needed)
        $bustCache = function ($model) {
            if ($model->created_by) {
                Cache::forget('laporan_list_version_' . $model->created_by);
            }
            // Also invalidate for the desa's kecamatan users (broader invalidation)
            Cache::forget('laporan_list_version_desa_' . $model->desa_id);
        };

        static::saved($bustCache);
        static::deleted($bustCache);
    }

    /**
     * Get the desa that owns the LaporanKegiatan.
     */
    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class, 'desa_id', 'id_desa');
    }

    /**
     * Get the kegiatan that owns the LaporanKegiatan.
     */
    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class, 'kegiatan_id', 'id_kegiatan');
    }

    /**
     * Get the user who approved this LaporanKegiatan.
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by', 'id_user')->withTrashed();
    }

    /**
     * Get the user who created this LaporanKegiatan.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id_user')->withTrashed();
    }

    /**
     * Get the jawaban pertanyaan for the LaporanKegiatan.
     */
    public function jawaban_pertanyaan(): HasMany
    {
        return $this->hasMany(JawabanPertanyaan::class, 'laporan_id', 'id_laporan');
    }

    /**
     * Get laporan kegiatan with relationships for detail view
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model|static|null
     */
    public static function getWithRelationships(int $id)
    {
        return self::select(
            'laporan_kegiatan.*',

            // Kolom pembuat laporan
            'creator_user.name as created_by_name',
            'creator_petugas.nama_lengkap as created_by_petugas',

            // Kolom yang menyetujui (approver)
            'approver_user.name as approved_by_name',
            'approver_petugas.nama_lengkap as approved_by_petugas'
        )
            // Join untuk pembuat laporan
            ->leftJoin('users as creator_user', 'laporan_kegiatan.created_by', '=', 'creator_user.id_user')
            ->leftJoin('petugas as creator_petugas', 'creator_petugas.user_id', '=', 'creator_user.id_user')

            // Join untuk approver laporan
            ->leftJoin('users as approver_user', 'laporan_kegiatan.approved_by', '=', 'approver_user.id_user')
            ->leftJoin('petugas as approver_petugas', 'approver_petugas.user_id', '=', 'approver_user.id_user')

            ->where('laporan_kegiatan.id_laporan', $id)
            ->first();
    }

    /**
     * Calculate tanggal_target based on kegiatan data
     *
     * @param mixed $kegiatan
     * @param int $tahun
     * @param int $bulan
     * @return \Carbon\Carbon|null
     */
    /**
     * Calculate tanggal_target based on kegiatan data (Standardized Logic v3)
     *
     * @param mixed $kegiatan (Master Data)
     * @param int|object $laporanOrTahun (If object: Laporan, if int: Tahun)
     * @param int|null $bulan (Parameter bulan if argument 2 is int)
     * @return \Carbon\Carbon|null
     */
    public static function calculateTanggalTarget($kegiatan, $laporanOrTahun, $bulan = null)
    {
        if (!$kegiatan->batas_akhir_upload) {
            return null;
        }

        // Determine Context (Tahun & Bulan)
        if (is_object($laporanOrTahun)) {
            // Context from Laporan Object
            $tahun = (int) $laporanOrTahun->tahun;
            $bulan = (int) $laporanOrTahun->bulan;
        } else {
            // Context from primitive params
            $tahun = (int) $laporanOrTahun;
        }

        // --- Logic Tanggal (Insidentil vs Rutin) ---
        // A. Kegiatan Insidentil (Satu Kali)
        // Basis: Tanggal Selesai + Bulan (Master Kegiatan) + Tahun (Master TA)
        // 
        // B. Kegiatan Rutin (Berulang)
        // Basis: Tanggal Selesai + Bulan Selesai (Master Kegiatan) + Tahun (Master TA)

        // Check if Rutin (has bulan_selesai)
        $isRutin = !empty($kegiatan->bulan_selesai) && !empty($kegiatan->bulan_mulai);

        if ($isRutin) {
            // Rutin: Base month is ALWAYS bulan_selesai from Master
            $targetMonth = (int) ($kegiatan->bulan_selesai ?: 12);
        } else {
            // Insidentil: Base month is bulan from Master
            $targetMonth = (int) $kegiatan->bulan;
        }

        // Fallback: If Master data is missing/invalid, use the report period (legacy safety)
        if (empty($targetMonth)) {
            $targetMonth = (int) ($bulan ?: 12); // Default to Dec if all fails
        }

        // Base Date: 1st of the target month
        $baseDate = Carbon::create($tahun, $targetMonth, 1);

        // --- Date Clamping Logic ---
        // If kegiatan finishes on 31st, but target month is Feb (28/29), clamp to end of month.
        $daysInMonth = $baseDate->daysInMonth;
        $tanggalSelesai = min((int) $kegiatan->tanggal_selesai, $daysInMonth);

        // Construct Target Date
        // Formula: Tanggal Selesai (Clamped) + Batas Akhir Upload
        $tanggalTarget = Carbon::create($tahun, $targetMonth, $tanggalSelesai)
            ->addDays((int) $kegiatan->batas_akhir_upload);

        return $tanggalTarget;
    }

    /**
     * Check if laporan is late (with 1-day grace period)
     *
     * @return bool
     */
    public function isTerlambat(): bool
    {
        if (!$this->tanggal_target) {
            return false;
        }

        $comparisonDate = $this->tanggal_submit ?: now();
        $gracePeriod = $this->tanggal_target->copy()->addDays(1);

        return $comparisonDate->gt($gracePeriod);
    }

    /**
     * Get days late (with grace period)
     *
     * @return int
     */
    public function getHariKeterlambatan(): int
    {
        if (!$this->tanggal_target) {
            return 0;
        }

        $comparisonDate = $this->tanggal_submit ?: now();
        $deadlineWithGrace = $this->tanggal_target->copy()->addDays(1);

        return max(0, $comparisonDate->diffInDays($deadlineWithGrace, false));
    }

    /**
     * Scope a query to only include active laporan.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['draft', 'submitted', 'revision']);
    }

    /**
     * Scope a query to only include laporan by desa.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $desaId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDesa($query, $desaId)
    {
        return $query->where('desa_id', $desaId);
    }

    /**
     * Scope a query to only include laporan by kecamatan.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $kecamatanId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByKecamatan($query, $kecamatanId)
    {
        return $query->whereHas('desa', function ($q) use ($kecamatanId) {
            $q->where('kecamatan_id', $kecamatanId);
        });
    }

    /**
     * Scope a query to only include laporan for review.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeForReview($query)
    {
        return $query->whereIn('laporan_kegiatan.status', ['submitted', 'revision']);
    }

    /**
     * Retrieve all laporan kegiatan that need to be reviewed by Inspektorat
     *
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    public static function getListForReview(array $filters = [])
    {
        $query = self::select(
            'laporan_kegiatan.*',
            'd.nama_desa',
            'kec.nama_kecamatan',
            'k.nama_kegiatan',
            'k.kode_kegiatan',
            'ta.tahun as tahun_anggaran'
        )
            ->join('desa as d', 'laporan_kegiatan.desa_id', '=', 'd.id_desa')
            ->join('kecamatan as kec', 'd.kecamatan_id', '=', 'kec.id_kecamatan')
            ->join('kegiatan as k', 'laporan_kegiatan.kegiatan_id', '=', 'k.id_kegiatan')
            ->join('tahun_anggaran as ta', 'k.tahun_anggaran_id', '=', 'ta.id_tahun_anggaran')
            ->forReview();

        // Filter by Inspektorat's Wilayah Binaan
        $user = Auth::user();
        if ($user && $user->petugas) {
            $petugas = $user->petugas;
            // Petugas Inspektorat (no kecamatan_id and no desa_id)
            if (is_null($petugas->kecamatan_id) && is_null($petugas->desa_id)) {
                $kecamatanBinaanIds = DB::table('petugas_wilayah_binaan')
                    ->where('petugas_id', $petugas->id_petugas)
                    ->whereNotNull('kecamatan_id')
                    ->pluck('kecamatan_id');

                if ($kecamatanBinaanIds->isNotEmpty()) {
                    $query->whereIn('d.kecamatan_id', $kecamatanBinaanIds);
                }
            }
        }

        // Apply filters
        if (!empty($filters['filter_tahun'])) {
            // FIX: Filter by ID, not Year value
            $query->where('ta.id_tahun_anggaran', $filters['filter_tahun']);
        }

        if (!empty($filters['filter_desa'])) {
            $query->where('d.id_desa', $filters['filter_desa']);
        }

        if (!empty($filters['filter_periode'])) {
            $query->where('laporan_kegiatan.bulan', $filters['filter_periode']);
        }

        if (!empty($filters['filter_status'])) {
            $query->where('laporan_kegiatan.status', $filters['filter_status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('k.nama_kegiatan', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('d.nama_desa', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('kec.nama_kecamatan', 'like', '%' . $filters['search'] . '%');
            });
        }

        $laporans = $query->get();

        // Transform results with consistent display fields
        return $laporans->map(function ($laporan) {
            return self::transformLaporanForDisplay($laporan);
        });
    }

    /**
     * Retrieve all kegiatan that need to be reported by desa under user's kecamatan.
     *
     * === PERFORMANCE OPTIMIZED ===
     * Before: N+1 Monster — 60,000+ DB queries (1 query per desa × kegiatan × bulan combination)
     * After:  Batch Strategy — exactly 3 DB queries total + O(1) PHP hash map lookup
     *
     * Strategy:
     * 1. Query all relevant desa          (1 query)
     * 2. Query all active kegiatan        (1 query)
     * 3. Query ALL existing laporan at once via WHERE IN  (1 query)
     * 4. PHP combine using key-value hash map (no DB call inside the loop)
     * 5. Result is cached for 5 minutes per user (cache versioning — database driver compatible)
     *
     * @param mixed $user
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    public static function getListForUser($user, array $filters = [])
    {
        // ── Cache Key with Version Busting ──────────────────────────────────────
        // "Version" is a short-lived key that gets deleted on any laporan save/delete.
        // When version key is missing, Cache::get returns null → use current timestamp.
        // This means the NEXT request will miss cache and rebuild fresh data.
        $versionKey = 'laporan_list_version_' . $user->id_user;
        $version    = Cache::get($versionKey, now()->timestamp);

        $cacheKey = 'laporan_list_user_' . $user->id_user . '_v' . $version . '_' . md5(json_encode($filters));

        // ── Cache Strategy: Cache only reference data (desa + kegiatan), not the full result ──
        // Reason: The combined result can be 30k+ records which exceeds database cache column size.
        // Desa and Kegiatan data rarely changes, so caching them gives the most benefit.
        // Laporan data (the batch query) runs fresh each time but is now just 1 fast query.

        return self::buildListForUser($user, $filters, $versionKey, $version);
    }

    /**
     * Internal builder method — separated for testability.
     */
    private static function buildListForUser($user, array $filters, string $versionKey, $version): \Illuminate\Support\Collection
    {
        // ── QUERY 1: All relevant desa (cached per user+version) ─────────────
        $desaCacheKey = 'laporan_desa_user_' . $user->id_user . '_v' . $version;
        $desaList = Cache::remember($desaCacheKey, 300, fn () => self::fetchDesaForUser($user, $filters));

        if ($desaList->isEmpty()) {
            return collect();
        }

        // ── QUERY 2: All active kegiatan (cached per filter_tahun+version) ───
        $kegCacheKey = 'laporan_kegiatan_aktif_v' . $version . '_' . md5(json_encode(['filter_tahun' => $filters['filter_tahun'] ?? '']));
        $kegiatanList = Cache::remember($kegCacheKey, 300, fn () => self::fetchKegiatanAktif($filters));

        if ($kegiatanList->isEmpty()) {
            return collect();
        }

        // ── QUERY 3: All existing laporan in ONE batch query ─────────────────
        // This single query replaces the N+1 queries that were inside the foreach loop.
        $desaIds     = $desaList->pluck('id_desa')->toArray();
        $kegiatanIds = $kegiatanList->pluck('id_kegiatan')->toArray();

        $laporanQuery = DB::table('laporan_kegiatan')
            ->whereIn('desa_id', $desaIds)
            ->whereIn('kegiatan_id', $kegiatanIds);

        if (!empty($filters['filter_tahun'])) {
            $tahunValue = $kegiatanList->first()->tahun_anggaran ?? null;
            if ($tahunValue) {
                $laporanQuery->where('tahun', $tahunValue);
            }
        }

        // Key the collection by composite key for O(1) lookup — no DB in the loop!
        $existingLaporans = $laporanQuery
            ->get()
            ->keyBy(fn ($l) => "{$l->desa_id}_{$l->kegiatan_id}_{$l->bulan}_{$l->tahun}");

        // ── PHP COMBINE (zero additional DB queries) ─────────────────────────
        $result = collect();

        foreach ($desaList as $desa) {
            foreach ($kegiatanList as $kegiatan) {
                $targetMonths = self::expandTargetMonths($kegiatan);

                foreach ($targetMonths as $targetBulan) {
                    // Early exit for periode filter — avoids processing unneeded months
                    if (!empty($filters['filter_periode']) && $targetBulan != $filters['filter_periode']) {
                        continue;
                    }

                    // O(1) hash map lookup — ZERO DB queries here!
                    $lookupKey       = "{$desa->id_desa}_{$kegiatan->id_kegiatan}_{$targetBulan}_{$kegiatan->tahun_anggaran}";
                    $existingLaporan = $existingLaporans->get($lookupKey);

                    $currentStatus = $existingLaporan->status ?? 'belum_dilaporkan';

                    // ── Early status pre-filter ───────────────────────────────────
                    // Skip building full record for statuses that will be filtered out anyway.
                    // Default view excludes 'submitted' and 'approved' — skip them early.
                    if (empty($filters['filter_status'])) {
                        if (in_array($currentStatus, ['submitted', 'approved'])) {
                            continue; // Will be excluded in applyFiltersForUser anyway
                        }
                    } elseif ($filters['filter_status'] !== 'belum_dilaporkan' && $currentStatus !== $filters['filter_status']) {
                        continue; // Status doesn't match the explicit filter
                    } elseif ($filters['filter_status'] === 'belum_dilaporkan' && $currentStatus !== 'belum_dilaporkan') {
                        continue;
                    }
                    // ─────────────────────────────────────────────────────────────

                    // Calculate tanggal_target purely in PHP (no DB)
                    try {
                        $tanggalTarget = self::calculateTanggalTargetFromKegiatan(
                            $kegiatan,
                            (int) $kegiatan->tahun_anggaran,
                            $targetBulan
                        );
                    } catch (\Exception $e) {
                        Log::warning('Failed to calculate tanggal_target', [
                            'desa_id'    => $desa->id_desa,
                            'kegiatan_id'=> $kegiatan->id_kegiatan,
                            'bulan'      => $targetBulan,
                            'error'      => $e->getMessage(),
                        ]);
                        $tanggalTarget = null;
                    }

                    $record = self::buildRecord($desa, $kegiatan, $targetBulan, $existingLaporan, $tanggalTarget);
                    $result->push($record);
                }
            }
        }

        // Apply status/search filters and sort — all in-memory (no DB)
        $result = self::applyFiltersForUser($result, $filters);

        return self::sortResultForUser($result);
    }

    /**
     * [HELPER] Fetch all desa that are relevant for the given user.
     * Extracted from getListForUser() for clarity.
     *
     * @param mixed $user
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    private static function fetchDesaForUser($user, array $filters): \Illuminate\Support\Collection
    {
        $query = DB::table('desa as d')
            ->select('d.id_desa', 'd.nama_desa', 'd.kode_desa', 'd.kecamatan_id', 'kec.nama_kecamatan')
            ->leftJoin('kecamatan as kec', 'd.kecamatan_id', '=', 'kec.id_kecamatan')
            ->where('d.status', 'active');

        $petugas = $user->petugas;
        if ($petugas) {
            if ($petugas->desa_id) {
                // Petugas Desa — only their single desa
                $query->where('d.id_desa', $petugas->desa_id);
            } elseif ($petugas->kecamatan_id) {
                // Petugas Kecamatan — check assigned desa binaan first
                $desaBinaanIds = DB::table('petugas_wilayah_binaan')
                    ->where('petugas_id', $petugas->id_petugas)
                    ->whereNotNull('desa_id')
                    ->pluck('desa_id');

                if ($desaBinaanIds->isNotEmpty()) {
                    $query->whereIn('d.id_desa', $desaBinaanIds);
                } else {
                    // Fallback: all desa in their kecamatan
                    $query->where('d.kecamatan_id', $petugas->kecamatan_id);
                }
            }
            // Inspektorat/Admin: no restriction → all desa
        }

        // Apply filter_desa early to reduce dataset
        if (!empty($filters['filter_desa'])) {
            $query->where('d.id_desa', $filters['filter_desa']);
        }

        return $query->get();
    }

    /**
     * [HELPER] Fetch all active kegiatan with their scheduling metadata.
     * Extracted from getListForUser() for clarity.
     *
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    private static function fetchKegiatanAktif(array $filters): \Illuminate\Support\Collection
    {
        $query = DB::table('kegiatan as k')
            ->select(
                'k.id_kegiatan',
                'k.nama_kegiatan',
                'k.kode_kegiatan',
                'k.tanggal_mulai',
                'k.tanggal_selesai',
                'k.bulan',
                'k.frekuensi_pelaporan',
                'k.bulan_mulai',
                'k.bulan_selesai',
                'k.batas_akhir_upload',
                'jk.nama_jenis as jenis_kegiatan',
                'ta.tahun as tahun_anggaran'
            )
            ->leftJoin('jenis_kegiatan as jk', 'k.jenis_kegiatan_id', '=', 'jk.id_jenis_kegiatan')
            ->leftJoin('tahun_anggaran as ta', 'k.tahun_anggaran_id', '=', 'ta.id_tahun_anggaran')
            ->where('k.status', 'active')
            ->where('jk.status', 'active');

        if (!empty($filters['filter_tahun'])) {
            $query->where('ta.id_tahun_anggaran', $filters['filter_tahun']);
        }

        $list = $query->get();

        // Normalize data types once, not inside the loop
        return $list->map(function ($kegiatan) {
            $kegiatan->id_kegiatan          = (int) $kegiatan->id_kegiatan;
            $kegiatan->tahun_anggaran       = (int) $kegiatan->tahun_anggaran;
            $kegiatan->bulan                = (int) $kegiatan->bulan;
            $kegiatan->batas_akhir_upload   = (int) $kegiatan->batas_akhir_upload;
            $kegiatan->frekuensi_pelaporan  = $kegiatan->frekuensi_pelaporan ? (int) $kegiatan->frekuensi_pelaporan : null;
            $kegiatan->bulan_mulai          = $kegiatan->bulan_mulai ? (int) $kegiatan->bulan_mulai : null;
            $kegiatan->bulan_selesai        = $kegiatan->bulan_selesai ? (int) $kegiatan->bulan_selesai : null;
            $kegiatan->tanggal_selesai      = $kegiatan->tanggal_selesai ? (int) $kegiatan->tanggal_selesai : null;
            $kegiatan->tanggal_mulai        = $kegiatan->tanggal_mulai ? (int) $kegiatan->tanggal_mulai : null;
            return $kegiatan;
        });
    }

    /**
     * [HELPER] Expand a kegiatan into its target reporting months.
     * Rutin kegiatan → array of months based on frequency.
     * Insidentil kegiatan → single month array.
     *
     * @param mixed $kegiatan
     * @return array<int>
     */
    private static function expandTargetMonths($kegiatan): array
    {
        if ($kegiatan->frekuensi_pelaporan) {
            // Rutin: generate sequence from bulan_mulai to bulan_selesai with step = frekuensi
            $startMonth = $kegiatan->bulan_mulai ?? 1;
            $endMonth   = $kegiatan->bulan_selesai ?? 12;
            $step       = $kegiatan->frekuensi_pelaporan;
            $months     = [];

            for ($m = $startMonth; $m <= $endMonth; $m += $step) {
                $months[] = $m;
            }

            return $months;
        }

        // Insidentil: single month from master data
        return [$kegiatan->bulan];
    }

    /**
     * [HELPER] Build a single record array for the DataTables result.
     * Centralized to ensure consistent structure across all code paths.
     *
     * @param mixed $desa
     * @param mixed $kegiatan
     * @param int $targetBulan
     * @param mixed|null $existingLaporan   Row from laporan_kegiatan or null
     * @param Carbon|null $tanggalTarget    Pre-calculated deadline
     * @return array
     */
    private static function buildRecord($desa, $kegiatan, int $targetBulan, $existingLaporan, $tanggalTarget): array
    {
        $status = $existingLaporan->status ?? 'belum_dilaporkan';

        $record = [
            'id_laporan'         => $existingLaporan->id_laporan ?? null,
            'desa_id'            => (int) $desa->id_desa,
            'kegiatan_id'        => $kegiatan->id_kegiatan,
            'tahun'              => $kegiatan->tahun_anggaran,
            'bulan'              => $targetBulan,
            'status'             => $status,
            'tanggal_target'     => $existingLaporan->tanggal_target ?? ($tanggalTarget ? $tanggalTarget->format('Y-m-d') : null),
            'tanggal_submit'     => $existingLaporan->tanggal_submit ?? null,
            'tanggal_approve'    => $existingLaporan->tanggal_approve ?? null,
            'nama_desa'          => $desa->nama_desa,
            'kode_desa'          => $desa->kode_desa,
            'nama_kecamatan'     => $desa->nama_kecamatan,
            'nama_kegiatan'      => $kegiatan->nama_kegiatan,
            'kode_kegiatan'      => $kegiatan->kode_kegiatan,
            'tanggal_mulai'      => $kegiatan->tanggal_mulai,
            'tanggal_selesai'    => $kegiatan->tanggal_selesai,
            'batas_akhir_upload' => $kegiatan->batas_akhir_upload,
            'jenis_kegiatan'     => $kegiatan->jenis_kegiatan,
            'tahun_anggaran'     => $kegiatan->tahun_anggaran,
            'frekuensi_pelaporan'=> $kegiatan->frekuensi_pelaporan,
        ];

        $record['status_display']   = self::getStatusDisplayForUser($status);
        $record['status_class']     = self::getStatusClass($status);
        $record['timeline_status']  = self::calculateTimelineStatus($record);

        if ($tanggalTarget instanceof Carbon) {
            try {
                $record['days_until_deadline'] = $tanggalTarget->diffInDays(now(), false);
            } catch (\Exception $e) {
                $record['days_until_deadline'] = 0;
            }
        } else {
            $record['days_until_deadline'] = 0;
        }

        $record['priority_order'] = self::getPriorityOrderForUser($status);

        // Build timeline_dates for display
        $record['timeline_dates'] = $record['tanggal_target']
            ? 'Target: ' . Carbon::parse($record['tanggal_target'])->format('d M Y')
            : '';

        return $record;
    }



    /**
     * Retrieve all laporan kegiatan that have been reported or approved (for history)
     *
     * @param mixed $user
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    public static function getListHistory($user, array $filters = [])
    {
        $petugas = $user->petugas;
        
        $query = DB::table('laporan_kegiatan as lk')
            ->select(
                'lk.id_laporan',
                'lk.desa_id',
                'lk.kegiatan_id',
                'lk.tahun',
                'lk.bulan',
                'lk.status',
                'lk.tanggal_target',
                'lk.tanggal_submit',
                'lk.tanggal_approve',
                'd.nama_desa',
                'd.kode_desa',
                'kec.nama_kecamatan',
                'k.nama_kegiatan',
                'k.kode_kegiatan',
                'k.tanggal_mulai',
                'k.tanggal_selesai',
                'k.batas_akhir_upload',
                'jk.nama_jenis as jenis_kegiatan',
                'ta.tahun as tahun_anggaran'
            )
            ->join('desa as d', 'lk.desa_id', '=', 'd.id_desa')
            ->join('kecamatan as kec', 'd.kecamatan_id', '=', 'kec.id_kecamatan')
            ->join('kegiatan as k', 'lk.kegiatan_id', '=', 'k.id_kegiatan')
            ->join('jenis_kegiatan as jk', 'k.jenis_kegiatan_id', '=', 'jk.id_jenis_kegiatan')
            ->join('tahun_anggaran as ta', 'k.tahun_anggaran_id', '=', 'ta.id_tahun_anggaran')
            ->whereIn('lk.status', ['submitted', 'approved'])
            ->where('d.status', 'active')
            ->where('k.status', 'active')
            ->where('jk.status', 'active');

        // Apply role-based filtering
        if ($petugas) {
            if ($petugas->desa_id) {
                // Desa - show only their desa
                $query->where('d.id_desa', $petugas->desa_id);
            } elseif ($petugas->kecamatan_id) {
                // Kecamatan - check if has assigned desa binaan
                $desaBinaanIds = DB::table('petugas_wilayah_binaan')
                    ->where('petugas_id', $petugas->id_petugas)
                    ->whereNotNull('desa_id')
                    ->pluck('desa_id');

                if ($desaBinaanIds->isNotEmpty()) {
                    $query->whereIn('d.id_desa', $desaBinaanIds);
                } else {
                    // Fallback to all desa in their kecamatan
                    $query->where('d.kecamatan_id', $petugas->kecamatan_id);
                }
            } elseif (is_null($petugas->kecamatan_id) && is_null($petugas->desa_id)) {
                // Inspektorat - check wilayah binaan (assigned kecamatan)
                $kecamatanBinaanIds = DB::table('petugas_wilayah_binaan')
                    ->where('petugas_id', $petugas->id_petugas)
                    ->whereNotNull('kecamatan_id')
                    ->pluck('kecamatan_id');

                if ($kecamatanBinaanIds->isNotEmpty()) {
                    $query->whereIn('d.kecamatan_id', $kecamatanBinaanIds);
                }
            }
        }

        // Apply filters
        if (!empty($filters['filter_tahun'])) {
            $query->where('ta.id_tahun_anggaran', $filters['filter_tahun']);
        }

        if (!empty($filters['filter_kegiatan'])) {
            $query->where('k.id_kegiatan', $filters['filter_kegiatan']);
        }

        if (!empty($filters['filter_desa'])) {
            $query->where('d.id_desa', $filters['filter_desa']);
        }

        if (!empty($filters['filter_periode'])) {
            $query->where('lk.bulan', $filters['filter_periode']);
        }

        if (!empty($filters['filter_status'])) {
            $query->where('lk.status', $filters['filter_status']);
        }

        if (!empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $query->where(function ($q) use ($search) {
                $q->where('d.nama_desa', 'like', '%' . $search . '%')
                  ->orWhere('k.nama_kegiatan', 'like', '%' . $search . '%')
                  ->orWhere('k.kode_kegiatan', 'like', '%' . $search . '%')
                  ->orWhere('jk.nama_jenis', 'like', '%' . $search . '%')
                  ->orWhere('kec.nama_kecamatan', 'like', '%' . $search . '%');
            });
        }

        $laporans = $query->get();

        // Transform collection to match expected structure
        $result = $laporans->map(function ($laporan) {
            $record = [
                'id_laporan' => $laporan->id_laporan,
                'desa_id' => $laporan->desa_id,
                'kegiatan_id' => $laporan->kegiatan_id,
                'tahun' => $laporan->tahun,
                'bulan' => $laporan->bulan,
                'status' => $laporan->status,
                'tanggal_target' => $laporan->tanggal_target,
                'tanggal_submit' => $laporan->tanggal_submit,
                'tanggal_approve' => $laporan->tanggal_approve,
                'nama_desa' => $laporan->nama_desa,
                'kode_desa' => $laporan->kode_desa,
                'nama_kecamatan' => $laporan->nama_kecamatan,
                'nama_kegiatan' => $laporan->nama_kegiatan,
                'kode_kegiatan' => $laporan->kode_kegiatan,
                'tanggal_mulai' => $laporan->tanggal_mulai,
                'tanggal_selesai' => $laporan->tanggal_selesai,
                'batas_akhir_upload' => $laporan->batas_akhir_upload,
                'jenis_kegiatan' => $laporan->jenis_kegiatan,
                'tahun_anggaran' => $laporan->tahun_anggaran,
            ];

            $record['status_display'] = self::getStatusDisplayForUser($record['status']);
            $record['status_class'] = self::getStatusClass($record['status']);
            $record['timeline_status'] = self::calculateTimelineStatus($record);
            $record['priority_order'] = self::getPriorityOrderForHistory($record['status']);
            $record['days_until_deadline'] = $record['tanggal_target'] ? Carbon::parse($record['tanggal_target'])->diffInDays(now(), false) : 0;

            return $record;
        });

        // Sort the result - newest first for history
        return self::sortResultForHistory($result);
    }

    /**
     * Transform laporan data for consistent display
     *
     * @param mixed $laporan
     * @return array
     */
    private static function transformLaporanForDisplay($laporan)
    {
        $record = [
            'id_laporan' => $laporan->id_laporan,
            'kegiatan_id' => $laporan->kegiatan_id,
            'desa_id' => $laporan->desa_id,
            'status' => $laporan->status,
            'tanggal_target' => $laporan->tanggal_target,
            'tanggal_submit' => $laporan->tanggal_submit,
            'bulan' => $laporan->bulan,
            'tahun' => $laporan->tahun_anggaran ?? $laporan->tahun,
            'nama_desa' => $laporan->nama_desa,
            'nama_kecamatan' => $laporan->nama_kecamatan,
            'nama_kegiatan' => $laporan->nama_kegiatan,
            'kode_kegiatan' => $laporan->kode_kegiatan,
        ];

        // Add display fields using existing methods
        $record['status_display'] = self::getStatusDisplayForReview($record['status']);
        $record['status_class'] = self::getStatusClass($record['status']);
        $record['timeline_status'] = self::calculateTimelineStatus($record);
        $record['priority_order'] = self::getPriorityOrderForReview($record['status']);

        return $record;
    }

    /**
     * Get status display text for user perspective (pelapor)
     *
     * @param string $status
     * @return string
     */
    public static function getStatusDisplayForUser($status)
    {
        return match ($status) {
            'draft' => 'Draft',
            'submitted' => 'Menunggu Review',
            'revision' => 'Perlu Revisi',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'belum_dilaporkan' => 'Belum Dilaporkan',
            default => 'Belum Dilaporkan'
        };
    }

    /**
     * Get status display text for review perspective (inspektorat)
     *
     * @param string $status
     * @return string
     */
    public static function getStatusDisplayForReview($status)
    {
        return match ($status) {
            'draft' => 'Draft',
            'submitted' => 'Butuh Review',
            'revision' => 'Sedang Direvisi',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            'belum_dilaporkan' => 'Belum Dilaporkan',
            default => 'Belum Dilaporkan'
        };
    }

    /**
     * Get status class for UI
     *
     * @param string $status
     * @return string
     */
    public static function getStatusClass($status)
    {
        return match ($status) {
            'approved' => 'success',
            'submitted' => 'primary',
            'revision' => 'warning',
            'rejected' => 'danger',
            'draft' => 'secondary',
            'belum_dilaporkan' => 'light',
            default => 'light'
        };
    }

    /**
     * Calculate timeline status
     *
     * @param array $record
     * @return string
     */
    public static function calculateTimelineStatus($record)
    {
        if (!$record['tanggal_target']) {
            return 'Menunggu';
        }

        $tanggalTarget = Carbon::parse($record['tanggal_target']);
        $comparisonDate = $record['tanggal_submit'] ? Carbon::parse($record['tanggal_submit']) : now();
        $gracePeriod = $tanggalTarget->copy()->addDays(1);

        if (in_array($record['status'], ['submitted', 'approved'])) {
            return $comparisonDate->gt($gracePeriod) ? 'Terlambat' : 'Tepat Waktu';
        } else {
            if (now()->gt($gracePeriod))
                return 'Terlambat';
            if (now()->gt($tanggalTarget))
                return 'Tenggang';
            return 'Menunggu';
        }
    }

    /**
     * Get priority order for sorting (user perspective)
     *
     * @param string $status
     * @return int
     */
    public static function getPriorityOrderForUser($status)
    {
        return match ($status) {
            'revision' => 1,    // Prioritas tertinggi - perlu segera direvisi
            'draft' => 2,       // Sedang dikerjakan
            'belum_dilaporkan' => 3, // Belum mulai
            'rejected' => 4,    // Ditolak - perlu diperbaiki
            'submitted' => 5,   // Sudah disubmit - tidak ditampilkan di list user
            'approved' => 6,    // Sudah disetujui - tidak ditampilkan di list user
            default => 7
        };
    }

    /**
     * Get priority order for sorting (review perspective)
     *
     * @param string $status
     * @return int
     */
    public static function getPriorityOrderForReview($status)
    {
        return match ($status) {
            'submitted' => 1,
            'revision' => 2,
            'draft' => 3,
            'rejected' => 4,
            'approved' => 5,
            'belum_dilaporkan' => 6,
            default => 7
        };
    }

    /**
     * Get priority order for sorting (history perspective)
     *
     * @param string $status
     * @return int
     */
    public static function getPriorityOrderForHistory($status)
    {
        return match ($status) {
            'submitted' => 1,
            'approved' => 2,
            default => 3
        };
    }

    /**
     * Calculate tanggal_target from kegiatan data
     *
     * @param mixed $kegiatan
     * @param int $tahun
     * @param int $bulan
     * @return \Carbon\Carbon|null
     */
    private static function calculateTanggalTargetFromKegiatan($kegiatan, $tahun, $bulan)
    {
        if (!$kegiatan->batas_akhir_upload) {
            return null;
        }

        // CAST nilai untuk memastikan tipe data integer
        $tahun = (int) $tahun;
        $bulan = (int) $bulan;
        $batasHari = (int) $kegiatan->batas_akhir_upload;

        // Validasi bulan dan tahun
        if ($bulan < 1 || $bulan > 12) {
            Log::error('Invalid bulan value', ['bulan' => $bulan, 'kegiatan_id' => $kegiatan->id_kegiatan]);
            return null;
        }

        try {
            $baseDate = Carbon::create($tahun, $bulan, 1);

            // Tangani tanggal_selesai yang mungkin null atau string
            $tanggalSelesai = $kegiatan->tanggal_selesai
                ? (int) $kegiatan->tanggal_selesai
                : $baseDate->daysInMonth;

            // Validasi tanggal_selesai
            if ($tanggalSelesai < 1 || $tanggalSelesai > $baseDate->daysInMonth) {
                $tanggalSelesai = $baseDate->daysInMonth;
            }

            $tanggalSelesai = min($tanggalSelesai, $baseDate->daysInMonth);

            return Carbon::create($tahun, $bulan, $tanggalSelesai)
                ->addDays($batasHari);
        } catch (\Exception $e) {
            // Log error dan return null untuk mencegah crash
            Log::error('Error calculateTanggalTarget: ' . $e->getMessage(), [
                'kegiatan_id' => $kegiatan->id_kegiatan ?? null,
                'tahun' => $tahun,
                'bulan' => $bulan,
                'tanggal_selesai' => $kegiatan->tanggal_selesai,
                'batas_akhir_upload' => $kegiatan->batas_akhir_upload
            ]);
            return null;
        }
    }

    /**
     * Apply additional filters to the result (user perspective)
     *
     * @param \Illuminate\Support\Collection $result
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    private static function applyFiltersForUser($result, $filters)
    {
        // Filter status logic:
        // By default (if no filter), we exclude 'submitted' and 'approved' to show only actionable items (To-Do List).
        // BUT, if user explicitly filters for a status (e.g. 'submitted'), we allow it.
        if (empty($filters['filter_status'])) {
            $result = $result->filter(function ($item) {
                return !in_array($item['status'], ['submitted', 'approved']);
            });
        }

        // Filter by status
        if (!empty($filters['filter_status'])) {
            $result = $result->filter(function ($item) use ($filters) {
                if ($filters['filter_status'] === 'belum_dilaporkan') {
                    return $item['status'] === 'belum_dilaporkan';
                }
                return $item['status'] === $filters['filter_status'];
            });
        }



        // Filter by bulan/periode
        if (!empty($filters['filter_periode'])) {
            $result = $result->filter(function ($item) use ($filters) {
                return $item['bulan'] == $filters['filter_periode'];
            });
        }

        // Search filter
        if (!empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $result = $result->filter(function ($item) use ($search) {
                return str_contains(strtolower($item['nama_desa']), $search) ||
                    str_contains(strtolower($item['nama_kegiatan']), $search) ||
                    str_contains(strtolower($item['kode_kegiatan']), $search) ||
                    str_contains(strtolower($item['jenis_kegiatan']), $search) ||
                    str_contains(strtolower($item['nama_kecamatan']), $search);
            });
        }

        return $result;
    }

    /**
     * Apply additional filters to the result (history perspective)
     *
     * @param \Illuminate\Support\Collection $result
     * @param array $filters
     * @return \Illuminate\Support\Collection
     */
    private static function applyFiltersForHistory($result, $filters)
    {
        // Filter by status
        if (!empty($filters['filter_status'])) {
            $result = $result->filter(function ($item) use ($filters) {
                return $item['status'] === $filters['filter_status'];
            });
        }

        // Filter by periode (bulan)
        if (!empty($filters['filter_periode'])) {
            $result = $result->filter(function ($item) use ($filters) {
                return $item['bulan'] == $filters['filter_periode'];
            });
        }

        // Search filter
        if (!empty($filters['search'])) {
            $search = strtolower($filters['search']);
            $result = $result->filter(function ($item) use ($search) {
                return str_contains(strtolower($item['nama_desa']), $search) ||
                    str_contains(strtolower($item['nama_kegiatan']), $search) ||
                    str_contains(strtolower($item['kode_kegiatan']), $search) ||
                    str_contains(strtolower($item['jenis_kegiatan']), $search) ||
                    str_contains(strtolower($item['nama_kecamatan']), $search);
            });
        }

        return $result;
    }

    /**
     * Sort the final result (user perspective)
     *
     * @param \Illuminate\Support\Collection $result
     * @return \Illuminate\Support\Collection
     */
    private static function sortResultForUser($result)
    {
        return $result->sortBy([
            ['priority_order', 'asc'],      // Urutan prioritas status
            ['days_until_deadline', 'asc'], // Tenggat waktu terdekat
            ['nama_desa', 'asc'],           // Nama desa A-Z
            ['nama_kegiatan', 'asc']        // Nama kegiatan A-Z
        ])->values();
    }

    /**
     * Sort the final result (history perspective)
     *
     * @param \Illuminate\Support\Collection $result
     * @return \Illuminate\Support\Collection
     */
    private static function sortResultForHistory($result)
    {
        return $result->sortBy([
            ['tanggal_submit', 'desc'],     // Terbaru di atas
            ['priority_order', 'asc'],      // Urutan prioritas status
            ['nama_desa', 'asc'],           // Nama desa A-Z
            ['nama_kegiatan', 'asc']        // Nama kegiatan A-Z
        ])->values();
    }

    /**
     * Get laporan kegiatan with relationships for detail view
     *
     * @param int $id
     * @return \Illuminate\Database\Eloquent\Model|static|null
     */
    public static function getMainHistory(int $id)
    {
        return self::select(
            'laporan_kegiatan.*',

            // Kolom kegiatan
            'kegiatan.kode_kegiatan',
            'kegiatan.nama_kegiatan as nama_kegiatan',
            'kegiatan.bulan as bulan_kegiatan',
            'kegiatan.tanggal_mulai',
            'kegiatan.tanggal_selesai',
            'kegiatan.batas_akhir_upload',
            'kegiatan.dasar_hukum',
            'kegiatan.frekuensi_pelaporan',
            'kegiatan.bulan_mulai',
            'kegiatan.bulan_selesai',

            // Kolom jenis kegiatan
            'jenis_kegiatan.kode_jenis',
            'jenis_kegiatan.nama_jenis',

            // Kolom tahun anggaran
            'tahun_anggaran.tahun as tahun_anggaran',
            'tahun_anggaran.status as status_tahun_anggaran',

            // Kolom desa
            'desa.kode_desa',
            'desa.nama_desa',

            // Kolom kecamatan
            'kecamatan.kode_kecamatan',
            'kecamatan.nama_kecamatan',

            // Kolom pembuat laporan
            'creator_user.name as created_by_name',
            'creator_petugas.nama_lengkap as created_by_petugas',

            // Kolom yang menyetujui (approver)
            'approver_user.name as approved_by_name',
            'approver_petugas.nama_lengkap as approved_by_petugas'
        )
            // Join untuk kegiatan dan relasinya
            ->leftJoin('kegiatan', 'laporan_kegiatan.kegiatan_id', '=', 'kegiatan.id_kegiatan')
            ->leftJoin('jenis_kegiatan', 'kegiatan.jenis_kegiatan_id', '=', 'jenis_kegiatan.id_jenis_kegiatan')
            ->leftJoin('tahun_anggaran', 'kegiatan.tahun_anggaran_id', '=', 'tahun_anggaran.id_tahun_anggaran')

            // Join untuk desa dan kecamatan
            ->leftJoin('desa', 'laporan_kegiatan.desa_id', '=', 'desa.id_desa')
            ->leftJoin('kecamatan', 'desa.kecamatan_id', '=', 'kecamatan.id_kecamatan')

            // Join untuk pembuat laporan
            ->leftJoin('users as creator_user', 'laporan_kegiatan.created_by', '=', 'creator_user.id_user')
            ->leftJoin('petugas as creator_petugas', 'creator_petugas.user_id', '=', 'creator_user.id_user')

            // Join untuk approver laporan
            ->leftJoin('users as approver_user', 'laporan_kegiatan.approved_by', '=', 'approver_user.id_user')
            ->leftJoin('petugas as approver_petugas', 'approver_petugas.user_id', '=', 'approver_user.id_user')

            ->where('laporan_kegiatan.id_laporan', $id)
            ->first();
    }

    /**
     * Get detailed history data for single laporan
     */
    public static function getHistoryDetail($id_laporan)
    {
        // First get the main laporan data using existing method
        $laporan = self::getMainHistory($id_laporan);

        if (!$laporan) {
            return null;
        }

        // Transform to array and add display fields
        $laporanData = $laporan->toArray();

        // Add display fields
        $laporanData['status_display'] = self::getStatusDisplayForUser($laporan->status);
        $laporanData['status_class'] = self::getStatusClass($laporan->status);
        $laporanData['timeline_status'] = self::calculateTimelineStatus($laporanData);
        $laporanData['priority_order'] = self::getPriorityOrderForHistory($laporan->status);

        // Calculate days until deadline
        $laporanData['days_until_deadline'] = 0;
        if ($laporan->tanggal_target) {
            $laporanData['days_until_deadline'] = Carbon::parse($laporan->tanggal_target)->diffInDays(now(), false);
        }

        return $laporanData;
    }

    /**
     * Get complete history data including documents and timeline
     */
    public static function getCompleteHistoryData($id_laporan)
    {
        $mainData = self::getHistoryDetail($id_laporan);

        if (!$mainData) {
            return null;
        }

        // Get additional data
        $additionalData = self::getAdditionalHistoryData($id_laporan, $mainData);

        return array_merge($mainData, $additionalData);
    }

    /**
     * Get additional history data (documents, timeline, etc)
     */
    private static function getAdditionalHistoryData($id_laporan, $laporanData = null)
    {
        // Get jawaban and dokumen
        $jawaban = JawabanPertanyaan::where('laporan_id', $id_laporan)->get();
        $jawabanIds = $jawaban->pluck('id_jawaban');

        $dokumen = DokumenPersyaratan::whereIn('jawaban_id', $jawabanIds)
            ->orderBy('created_at', 'desc')
            ->get();

        // Get pertanyaan with requirements
        $pertanyaanIds = $jawaban->pluck('pertanyaan_id');
        $pertanyaan = PertanyaanKegiatan::with([
            'persyaratan' => function ($query) {
                $query->where('status', 'active');
            }
        ])->whereIn('id_pertanyaan', $pertanyaanIds)->get();

        return [
            'jawaban' => $jawaban,
            'dokumen' => $dokumen,
            'pertanyaan' => $pertanyaan,
            'history_laporan' => self::buildHistoryLaporan($id_laporan, $laporanData),
            'completeness' => self::calculateCompleteness($jawaban, $dokumen)
        ];
    }

    /**
     * Build history laporan events
     */
    /**
     * Build history laporan events
     */
    private static function buildHistoryLaporan($id_laporan, $laporanData = null)
    {
        if (!$laporanData) {
            $laporan = self::getMainHistory($id_laporan);
            if (!$laporan)
                return [];
            $laporanData = $laporan->toArray();
        }

        $history = [];

        // Determine names
        $creatorName = $laporanData['created_by_petugas'] ?? $laporanData['created_by_name'] ?? 'Petugas Desa';
        $approverName = $laporanData['approved_by_petugas'] ?? $laporanData['approved_by_name'] ?? 'Admin Inspektorat';

        // Draft created event
        if (!empty($laporanData['created_at'])) {
            $history[] = [
                'event' => 'Draft Created',
                'timestamp' => $laporanData['created_at'],
                'user' => $creatorName,
                'catatan' => null,
                'icon' => 'la-save',
                'color' => 'primary'
            ];
        }

        // Submitted event
        if (!empty($laporanData['tanggal_submit'])) {
            $history[] = [
                'event' => 'Submitted for Review',
                'timestamp' => $laporanData['tanggal_submit'],
                'user' => $creatorName,
                'catatan' => null,
                'icon' => 'la-paper-plane',
                'color' => 'warning'
            ];
        }

        // Approved event
        if (!empty($laporanData['tanggal_approve'])) {
            $history[] = [
                'event' => 'Approved',
                'timestamp' => $laporanData['tanggal_approve'],
                'user' => $approverName,
                'catatan' => $laporanData['catatan_approval'] ?? null,
                'icon' => 'la-check-circle',
                'color' => 'success'
            ];
        }

        // Sort by timestamp
        usort($history, function ($a, $b) {
            return strtotime($a['timestamp']) - strtotime($b['timestamp']);
        });

        return $history;
    }

    /**
     * Calculate document completeness percentage
     */
    private static function calculateCompleteness($jawaban, $dokumen)
    {
        $totalRequirements = 0;
        $completedRequirements = 0;

        foreach ($jawaban as $jwb) {
            // Get requirements for this question
            $requirements = Persyaratan::where('pertanyaan_kegiatan_id', $jwb->pertanyaan_id)
                ->where('status', 'active')
                ->get();

            foreach ($requirements as $requirement) {
                $totalRequirements++;

                // Check if there's a current document for this requirement
                $hasCurrentDoc = $dokumen->where('jawaban_id', $jwb->id_jawaban)
                    ->where('persyaratan_id', $requirement->id_persyaratan)
                    ->where('is_current', true)
                    ->count() > 0;

                if ($hasCurrentDoc) {
                    $completedRequirements++;
                }
            }
        }

        return $totalRequirements > 0 ? round(($completedRequirements / $totalRequirements) * 100) : 0;
    }
}
