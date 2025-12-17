<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Persyaratan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'persyaratan';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_persyaratan';

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
        'pertanyaan_kegiatan_id',
        'urutan',
        'nama_persyaratan',
        'template_persyaratan',
        'deskripsi',
        'tipe',
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
        'id_persyaratan'           => 'integer',
        'pertanyaan_kegiatan_id'   => 'integer',
        'urutan'                   => 'integer',
        'nama_persyaratan'         => 'string',
        'template_persyaratan'     => 'string',
        'deskripsi'                => 'string',
        'tipe'                     => 'string',
        'status'                   => 'string',
        'created_by'               => 'integer',
        'updated_by'               => 'integer',
        'created_at'               => 'datetime',
        'updated_at'               => 'datetime',
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

        // Auto-increment urutan for new records in the same pertanyaan
        static::creating(function ($model) {
            if (empty($model->urutan)) {
                $maxUrutan = static::where('pertanyaan_kegiatan_id', $model->pertanyaan_kegiatan_id)
                    ->max('urutan');
                $model->urutan = $maxUrutan ? $maxUrutan + 1 : 1;
            }
        });
    }

    /**
     * Get the pertanyaanKegiatan that owns the Persyaratan.
     *
     * @return BelongsTo
     */
    public function pertanyaanKegiatan(): BelongsTo
    {
        return $this->belongsTo(PertanyaanKegiatan::class, 'pertanyaan_kegiatan_id', 'id_pertanyaan');
    }

    /**
     * Get all of the dokumen for the Persyaratan.
     *
     * @return HasMany
     */
    // public function dokumen(): HasMany
    // {
    //     return $this->hasMany(Dokumen::class, 'persyaratan_id', 'id_persyaratan');
    // }

    /**
     * Get the user who created this Persyaratan.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id_user');
    }

    /**
     * Get the user who last updated this Persyaratan.
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id_user');
    }

    /**
     * Retrieve filtered persyaratan data.
     *
     * @param array<string, mixed> $filters
     * @return Collection
     */
    public static function getFilters(array $filters = []): Collection
    {
        $query = DB::table('persyaratan as p')
            ->select(
                'p.id_persyaratan',
                'p.pertanyaan_kegiatan_id',
                'p.urutan',
                'p.nama_persyaratan',
                'p.template_persyaratan',
                'p.deskripsi',
                'p.tipe',
                'p.status',
                'pk.pertanyaan',
                'k.id_kegiatan',
                'k.kode_kegiatan',
                'k.nama_kegiatan'
            )
            ->leftJoin('pertanyaan_kegiatan as pk', 'p.pertanyaan_kegiatan_id', '=', 'pk.id_pertanyaan')
            ->leftJoin('kegiatan as k', 'pk.kegiatan_id', '=', 'k.id_kegiatan')
            ->orderBy('p.urutan', 'ASC');

        if (!empty($filters['filter_status'])) {
            $query->where('p.status', $filters['filter_status']);
        }

        if (!empty($filters['filter_tipe'])) {
            $query->where('p.tipe', $filters['filter_tipe']);
        }

        if (!empty($filters['filter_pertanyaan'])) {
            $query->where('p.pertanyaan_kegiatan_id', $filters['filter_pertanyaan']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];

            $query->where(function ($q) use ($search) {
                $q->where('p.nama_persyaratan', 'like', "%{$search}%")
                    ->orWhere('p.deskripsi', 'like', "%{$search}%")
                    ->orWhere('pk.pertanyaan', 'like', "%{$search}%")
                    ->orWhere('k.nama_kegiatan', 'like', "%{$search}%")
                    ->orWhere('k.kode_kegiatan', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }


    /**
     * Get persyaratan by pertanyaan kegiatan ID.
     *
     * @param int $pertanyaanKegiatanId
     * @return Collection
     */
    public static function getByPertanyaanKegiatan(int $pertanyaanKegiatanId): Collection
    {
        return static::where('pertanyaan_kegiatan_id', $pertanyaanKegiatanId)
            ->where('status', 'active')
            ->orderBy('urutan', 'ASC')
            ->get();
    }

    /**
     * Check if persyaratan is required (wajib).
     *
     * @return bool
     */
    public function isWajib(): bool
    {
        return $this->tipe === 'wajib';
    }

    /**
     * Check if persyaratan is optional (tambahan).
     *
     * @return bool
     */
    public function isTambahan(): bool
    {
        return $this->tipe === 'tambahan';
    }

    /**
     * Scope a query to only include active persyaratan.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include wajib persyaratan.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeWajib($query)
    {
        return $query->where('tipe', 'wajib');
    }

    /**
     * Scope a query to only include persyaratan by pertanyaan kegiatan.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $pertanyaanKegiatanId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByPertanyaanKegiatan($query, $pertanyaanKegiatanId)
    {
        return $query->where('pertanyaan_kegiatan_id', $pertanyaanKegiatanId);
    }

    /**
     * Scope a query ordered by urutan.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('urutan', 'ASC');
    }

    public static function getRelationship(int $id): ?self
    {
        return self::select(
            'persyaratan.id_persyaratan',
            'persyaratan.pertanyaan_kegiatan_id',
            'persyaratan.urutan',
            'persyaratan.nama_persyaratan',
            'persyaratan.template_persyaratan',
            'persyaratan.deskripsi',
            'persyaratan.tipe',
            'persyaratan.status',
            'pk.pertanyaan',
            'pk.kegiatan_id',
            'k.id_kegiatan',
            'k.kode_kegiatan',
            'k.nama_kegiatan'
        )
            ->leftJoin('pertanyaan_kegiatan as pk', 'persyaratan.pertanyaan_kegiatan_id', '=', 'pk.id_pertanyaan')
            ->leftJoin('kegiatan as k', 'pk.kegiatan_id', '=', 'k.id_kegiatan')
            ->where('persyaratan.id_persyaratan', $id)
            ->first();
    }
}
