<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

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

    protected $casts = [
        'tanggal_target' => 'date',
        'tanggal_submit' => 'datetime',
        'tanggal_approve' => 'datetime',
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
        return $this->belongsTo(User::class, 'approved_by', 'id_user');
    }

    /**
     * Get the user who created this LaporanKegiatan.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id_user');
    }

    /**
     * Get laporan kegiatan with relationships for detail view
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
     */
    public static function calculateTanggalTarget($kegiatan, $tahun, $bulan)
    {
        if (!$kegiatan->batas_akhir_upload) {
            return null;
        }

        $baseDate = Carbon::create($tahun, $bulan, 1);
        $tanggalSelesai = $kegiatan->tanggal_selesai ?: $baseDate->daysInMonth;
        $tanggalSelesai = min($tanggalSelesai, $baseDate->daysInMonth);

        $tanggalTarget = Carbon::create($tahun, $bulan, $tanggalSelesai)
            ->addDays($kegiatan->batas_akhir_upload);

        return $tanggalTarget;
    }

    /**
     * Check if laporan is late (with 10-day grace period)
     */
    public function isTerlambat(): bool
    {
        if (!$this->tanggal_target) {
            return false;
        }

        $comparisonDate = $this->tanggal_submit ?: now();
        $gracePeriod = $this->tanggal_target->copy()->addDays(10);

        return $comparisonDate->gt($gracePeriod);
    }

    /**
     * Get days late (with grace period)
     */
    public function getHariKeterlambatan(): int
    {
        if (!$this->tanggal_target) {
            return 0;
        }

        $comparisonDate = $this->tanggal_submit ?: now();
        $deadlineWithGrace = $this->tanggal_target->copy()->addDays(10);

        return max(0, $comparisonDate->diffInDays($deadlineWithGrace, false));
    }

    /**
     * Scope a query to only include active laporan.
     */
    public function scopeActive($query)
    {
        return $query->whereIn('status', ['draft', 'submitted', 'revision']);
    }

    /**
     * Scope a query to only include laporan by desa.
     */
    public function scopeByDesa($query, $desaId)
    {
        return $query->where('desa_id', $desaId);
    }

    /**
     * Scope a query to only include laporan by kecamatan.
     */
    public function scopeByKecamatan($query, $kecamatanId)
    {
        return $query->whereHas('desa', function ($q) use ($kecamatanId) {
            $q->where('kecamatan_id', $kecamatanId);
        });
    }

    /**
     * Scope a query to only include laporan for review.
     */
    public function scopeForReview($query)
    {
        return $query->whereIn('laporan_kegiatan.status', ['submitted', 'revision']);
    }

    /**
     * Retrieve all laporan kegiatan that need to be reviewed by Inspektorat
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

        // Apply filters
        if (!empty($filters['filter_tahun'])) {
            $query->where('ta.tahun', $filters['filter_tahun']);
        }

        if (!empty($filters['filter_desa'])) {
            $query->where('d.id_desa', $filters['filter_desa']);
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
     * Transform laporan data for consistent display
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
        $record['status_display'] = self::getStatusDisplay($record['status']);
        $record['status_class'] = self::getStatusClass($record['status']);
        $record['timeline_status'] = self::calculateTimelineStatus($record);
        $record['priority_order'] = self::getPriorityOrder($record['status']);

        return $record;
    }

    /**
     * Get status display text
     */
    public static function getStatusDisplay($status)
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
     */
    public static function calculateTimelineStatus($record)
    {
        if (!$record['tanggal_target']) {
            return 'Menunggu';
        }

        $tanggalTarget = Carbon::parse($record['tanggal_target']);
        $comparisonDate = $record['tanggal_submit'] ? Carbon::parse($record['tanggal_submit']) : now();
        $gracePeriod = $tanggalTarget->copy()->addDays(10);

        if (in_array($record['status'], ['submitted', 'approved'])) {
            return $comparisonDate->gt($gracePeriod) ? 'Terlambat' : 'Tepat Waktu';
        } else {
            if (now()->gt($gracePeriod)) return 'Terlambat';
            if (now()->gt($tanggalTarget)) return 'Tenggang';
            return 'Menunggu';
        }
    }

    /**
     * Get priority order for sorting
     */
    public static function getPriorityOrder($status)
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
     * Retrieve all kegiatan that need to be reported by desa under user's kecamatan
     */
    public static function getListForUser($user, array $filters = [])
    {
        // Step 1: Get all desa under user's jurisdiction
        $desaQuery = DB::table('desa as d')
            ->select('d.id_desa', 'd.nama_desa', 'd.kode_desa', 'd.kecamatan_id', 'kec.nama_kecamatan')
            ->leftJoin('kecamatan as kec', 'd.kecamatan_id', '=', 'kec.id_kecamatan')
            ->where('d.status', 'active');

        // Role-based filtering for desa
        $petugas = $user->petugas;
        if ($petugas) {
            if ($petugas->kecamatan_id) {
                // Kecamatan - show all desa in their kecamatan
                $desaQuery->where('d.kecamatan_id', $petugas->kecamatan_id);
            } elseif ($petugas->desa_id) {
                // Desa - show only their desa
                $desaQuery->where('d.id_desa', $petugas->desa_id);
            }
        }

        $desaList = $desaQuery->get();

        // Step 2: Get all active kegiatan
        $kegiatanQuery = DB::table('kegiatan as k')
            ->select(
                'k.id_kegiatan',
                'k.nama_kegiatan',
                'k.kode_kegiatan',
                'k.tanggal_mulai',
                'k.tanggal_selesai',
                'k.bulan',
                'k.batas_akhir_upload',
                'jk.nama_jenis as jenis_kegiatan',
                'ta.tahun as tahun_anggaran'
            )
            ->leftJoin('jenis_kegiatan as jk', 'k.jenis_kegiatan_id', '=', 'jk.id_jenis_kegiatan')
            ->leftJoin('tahun_anggaran as ta', 'k.tahun_anggaran_id', '=', 'ta.id_tahun_anggaran')
            ->where('k.status', 'active')
            ->where('jk.status', 'active');

        // Filter by tahun if provided
        if (!empty($filters['filter_tahun'])) {
            $kegiatanQuery->where('ta.tahun', $filters['filter_tahun']);
        }

        $kegiatanList = $kegiatanQuery->get();

        // Step 3: Create combination of all desa and kegiatan
        $result = collect();

        foreach ($desaList as $desa) {
            foreach ($kegiatanList as $kegiatan) {
                // Get existing laporan if any
                $existingLaporan = DB::table('laporan_kegiatan as lk')
                    ->where('lk.desa_id', $desa->id_desa)
                    ->where('lk.kegiatan_id', $kegiatan->id_kegiatan)
                    ->first();

                // Calculate tanggal_target
                $tanggalTarget = self::calculateTanggalTargetFromKegiatan($kegiatan, $kegiatan->tahun_anggaran, $kegiatan->bulan);

                // Prepare the record
                $record = [
                    'id_laporan' => $existingLaporan->id_laporan ?? null,
                    'desa_id' => $desa->id_desa,
                    'kegiatan_id' => $kegiatan->id_kegiatan,
                    'tahun' => $kegiatan->tahun_anggaran,
                    'bulan' => $kegiatan->bulan,
                    'status' => $existingLaporan->status ?? 'belum_dilaporkan',
                    'tanggal_target' => $existingLaporan->tanggal_target ?? $tanggalTarget,
                    'tanggal_submit' => $existingLaporan->tanggal_submit ?? null,
                    'tanggal_approve' => $existingLaporan->tanggal_approve ?? null,
                    'nama_desa' => $desa->nama_desa,
                    'kode_desa' => $desa->kode_desa,
                    'nama_kecamatan' => $desa->nama_kecamatan,
                    'nama_kegiatan' => $kegiatan->nama_kegiatan,
                    'kode_kegiatan' => $kegiatan->kode_kegiatan,
                    'tanggal_mulai' => $kegiatan->tanggal_mulai,
                    'tanggal_selesai' => $kegiatan->tanggal_selesai,
                    'batas_akhir_upload' => $kegiatan->batas_akhir_upload,
                    'jenis_kegiatan' => $kegiatan->jenis_kegiatan,
                    'tahun_anggaran' => $kegiatan->tahun_anggaran,
                ];

                // Add display fields
                $record['status_display'] = self::getStatusDisplay($record['status']);
                $record['status_class'] = self::getStatusClass($record['status']);
                $record['timeline_status'] = self::calculateTimelineStatus($record);
                $record['priority_order'] = self::getPriorityOrder($record['status']);
                $record['days_until_deadline'] = $tanggalTarget ? Carbon::parse($tanggalTarget)->diffInDays(now(), false) : 0;

                $result->push($record);
            }
        }

        // Apply additional filters
        $result = self::applyFilters($result, $filters);

        // Sort the result
        return self::sortResult($result);
    }

    /**
     * Calculate tanggal_target from kegiatan data
     */
    private static function calculateTanggalTargetFromKegiatan($kegiatan, $tahun, $bulan)
    {
        if (!$kegiatan->batas_akhir_upload) {
            return null;
        }

        $baseDate = Carbon::create($tahun, $bulan, 1);
        $tanggalSelesai = $kegiatan->tanggal_selesai ?: $baseDate->daysInMonth;
        $tanggalSelesai = min($tanggalSelesai, $baseDate->daysInMonth);

        return Carbon::create($tahun, $bulan, $tanggalSelesai)
            ->addDays($kegiatan->batas_akhir_upload);
    }

    /**
     * Apply additional filters to the result
     */
    private static function applyFilters($result, $filters)
    {
        // Filter by status
        if (!empty($filters['filter_status'])) {
            $result = $result->filter(function ($item) use ($filters) {
                if ($filters['filter_status'] === 'belum_dilaporkan') {
                    return $item['status'] === 'belum_dilaporkan';
                }
                return $item['status'] === $filters['filter_status'];
            });
        }

        // Filter by desa
        if (!empty($filters['filter_desa'])) {
            $result = $result->where('desa_id', $filters['filter_desa']);
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
     * Sort the final result
     */
    private static function sortResult($result)
    {
        return $result->sortBy([
            ['priority_order', 'asc'],
            ['days_until_deadline', 'asc'],
            ['nama_desa', 'asc'],
            ['nama_kegiatan', 'asc']
        ])->values();
    }
}
