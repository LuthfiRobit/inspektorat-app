<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TahunAnggaran extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'tahun_anggaran';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_tahun_anggaran';

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
        'tahun',
        'status',
        'keterangan',
        'created_by',
        'updated_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'id_tahun_anggaran' => 'integer',
        'tahun' => 'integer',
        'status' => 'string',
        'keterangan' => 'string',
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

        // Automatically deactivate other active years when activating a new one
        static::updating(function ($model) {
            if ($model->isDirty('status') && $model->status === 'active') {
                static::where('id_tahun_anggaran', '!=', $model->id_tahun_anggaran)
                    ->where('status', 'active')
                    ->update(['status' => 'inactive']);
            }
        });
    }

    /**
     * Get all of the kegiatan for the TahunAnggaran.
     *
     * @return HasMany
     */
    // public function kegiatan(): HasMany
    // {
    //     return $this->hasMany(Kegiatan::class, 'tahun_anggaran_id', 'id_tahun_anggaran');
    // }

    /**
     * Get the user who created this TahunAnggaran.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id_user');
    }

    /**
     * Get the user who last updated this TahunAnggaran.
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id_user');
    }

    /**
     * Retrieve filtered tahun anggaran data.
     *
     * @param array<string, mixed> $filters
     * @return Collection
     */
    public static function getFilters(array $filters = []): Collection
    {
        $query = DB::table('tahun_anggaran as ta')
            ->select(
                'ta.id_tahun_anggaran',
                'ta.tahun',
                'ta.status',
                'ta.keterangan',
                'ta.created_at',
                DB::raw('COUNT(DISTINCT jk.id_jenis_kegiatan) as jumlah_jenis_kegiatan'),
                DB::raw('COUNT(DISTINCT k.id_kegiatan) as jumlah_kegiatan')
            )
            ->leftJoin('jenis_kegiatan as jk', 'jk.tahun_anggaran_id', '=', 'ta.id_tahun_anggaran')
            ->leftJoin('kegiatan as k', 'k.tahun_anggaran_id', '=', 'ta.id_tahun_anggaran')
            ->groupBy('ta.id_tahun_anggaran', 'ta.tahun', 'ta.status', 'ta.keterangan', 'ta.created_at')
            ->orderBy('ta.tahun', 'DESC');

        if (!empty($filters['filter_status'])) {
            $query->where('ta.status', $filters['filter_status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('ta.tahun', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('ta.keterangan', 'like', '%' . $filters['search'] . '%');
            });
        }

        if (!empty($filters['tahun_from']) && !empty($filters['tahun_to'])) {
            $query->whereBetween('ta.tahun', [$filters['tahun_from'], $filters['tahun_to']]);
        }

        return $query->get();
    }

    /**
     * Get active tahun anggaran.
     *
     * @return TahunAnggaran|null
     */
    public static function getActive(): ?TahunAnggaran
    {
        return static::where('status', 'active')->first();
    }

    /**
     * Check if tahun is unique.
     *
     * @param int $tahun
     * @param int|null $excludeId
     * @return bool
     */
    public static function isTahunUnique(int $tahun, ?int $excludeId = null): bool
    {
        $query = static::where('tahun', $tahun);

        if ($excludeId) {
            $query->where('id_tahun_anggaran', '!=', $excludeId);
        }

        return !$query->exists();
    }

    /**
     * Get active tahun anggaran.
     *
     * @return Collection
     */
    public static function scopeActive(): Collection
    {
        return DB::table('tahun_anggaran')
            ->select('id_tahun_anggaran', 'tahun')
            ->where('status', 'active')
            ->orderBy('tahun', 'DESC')
            ->get();
    }
}
