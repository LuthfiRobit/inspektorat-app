<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class JenisKegiatan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'jenis_kegiatan';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_jenis_kegiatan';

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'tahun_anggaran_id',
        'kode_jenis',
        'nama_jenis',
        'keterangan',
        'status',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id_jenis_kegiatan' => 'integer',
        'tahun_anggaran_id' => 'integer',
        'kode_jenis' => 'string',
        'nama_jenis' => 'string',
        'keterangan' => 'string',
        'status' => 'string',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot method for the model to handle automatic user attribution.
     *
     * @return void
     */
    protected static function booted(): void
    {
        static::creating(function ($model) {
            if (Auth::check()) {
                $model->created_by = Auth::id();
                $model->updated_by = Auth::id();
            }
        });

        static::updating(function ($model) {
            if (Auth::check()) {
                $model->updated_by = Auth::id();
            }
        });
    }

    /**
     * Get the tahunAnggaran that owns the JenisKegiatan.
     *
     * @return BelongsTo
     */
    public function tahunAnggaran(): BelongsTo
    {
        return $this->belongsTo(TahunAnggaran::class, 'tahun_anggaran_id', 'id_tahun_anggaran');
    }

    /**
     * Get all of the kegiatan for the JenisKegiatan.
     *
     * @return HasMany
     */
    // public function kegiatan(): HasMany
    // {
    //     return $this->hasMany(Kegiatan::class, 'jenis_kegiatan_id', 'id_jenis_kegiatan');
    // }

    /**
     * Get the user who created this JenisKegiatan.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id_user');
    }

    /**
     * Get the user who last updated this JenisKegiatan.
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id_user');
    }

    /**
     * Retrieve filtered jenis kegiatan data.
     *
     * @param array<string, mixed> $filters
     * @return Collection
     */
    public static function getFilters(array $filters = []): Collection
    {
        $query = DB::table('jenis_kegiatan as jk')
            ->select(
                'jk.id_jenis_kegiatan',
                'jk.tahun_anggaran_id',
                'jk.kode_jenis',
                'jk.nama_jenis',
                'jk.keterangan',
                'jk.status',
                'ta.tahun',
                DB::raw('COUNT(k.id_kegiatan) as jumlah_kegiatan') // Hitung jumlah kegiatan terkait
            )
            ->leftJoin('tahun_anggaran as ta', 'jk.tahun_anggaran_id', '=', 'ta.id_tahun_anggaran')
            ->leftJoin('kegiatan as k', 'k.jenis_kegiatan_id', '=', 'jk.id_jenis_kegiatan') // Join ke tabel kegiatan
            ->groupBy(
                'jk.id_jenis_kegiatan',
                'jk.tahun_anggaran_id',
                'jk.kode_jenis',
                'jk.nama_jenis',
                'jk.keterangan',
                'jk.status',
                'ta.tahun'
            )
            ->orderBy('ta.tahun', 'DESC')
            ->orderBy('jk.created_at', 'DESC');

        // Filter status
        if (!empty($filters['filter_status'])) {
            $query->where('jk.status', $filters['filter_status']);
        }

        // Filter tahun anggaran
        if (!empty($filters['filter_tahun'])) {
            $query->where('jk.tahun_anggaran_id', $filters['filter_tahun']);
        }

        // Search
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('jk.kode_jenis', 'like', $search)
                    ->orWhere('jk.nama_jenis', 'like', $search)
                    ->orWhere('jk.keterangan', 'like', $search)
                    ->orWhere('ta.tahun', 'like', $search);
            });
        }

        return $query->get();
    }

    /**
     * Check if kode_jenis is unique for the given tahun_anggaran_id.
     *
     * @param string $kodeJenis
     * @param int $tahunAnggaranId
     * @param int|null $excludeId
     * @return bool
     */
    public static function isKodeUnique(string $kodeJenis, int $tahunAnggaranId, ?int $excludeId = null): bool
    {
        $query = static::where('kode_jenis', $kodeJenis)
            ->where('tahun_anggaran_id', $tahunAnggaranId);

        if ($excludeId) {
            $query->where('id_jenis_kegiatan', '!=', $excludeId);
        }

        return !$query->exists();
    }

    /**
     * Get jenis kegiatan by tahun anggaran.
     *
     * @param int $tahunAnggaranId
     * @return Collection
     */
    public static function getByTahunAnggaran(int $tahunAnggaranId): Collection
    {
        return static::where('tahun_anggaran_id', $tahunAnggaranId)
            ->where('status', 'active')
            ->orderBy('kode_jenis', 'ASC')
            ->get(['id_jenis_kegiatan', 'kode_jenis', 'nama_jenis']);
    }

    /**
     * Scope a query to only include active jenis kegiatan.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include jenis kegiatan by tahun anggaran.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $tahunAnggaranId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByTahunAnggaran($query, $tahunAnggaranId)
    {
        return $query->where('tahun_anggaran_id', $tahunAnggaranId);
    }

    public static function getRelationship(int $id): ?self
    {
        $query = self::select('id_jenis_kegiatan', 'tahun_anggaran_id', 'kode_jenis', 'nama_jenis', 'jenis_kegiatan.keterangan', 'tahun_anggaran.tahun', 'jenis_kegiatan.status')
            ->leftJoin('tahun_anggaran', 'jenis_kegiatan.tahun_anggaran_id', 'tahun_anggaran.id_tahun_anggaran')
            ->where('id_jenis_kegiatan', $id)
            ->first();

        return $query;
    }
}
