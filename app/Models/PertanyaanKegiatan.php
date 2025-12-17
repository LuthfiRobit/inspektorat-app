<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class PertanyaanKegiatan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'pertanyaan_kegiatan';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_pertanyaan';

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
        'kegiatan_id',
        'urutan',
        'pertanyaan',
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
        'id_pertanyaan'  => 'integer',
        'kegiatan_id'    => 'integer',
        'urutan'         => 'integer',
        'pertanyaan'     => 'string',
        'status'         => 'string',
        'created_by'     => 'integer',
        'updated_by'     => 'integer',
        'created_at'     => 'datetime',
        'updated_at'     => 'datetime',
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

        // Auto-increment urutan for new records in the same kegiatan
        static::creating(function ($model) {
            if (empty($model->urutan)) {
                $maxUrutan = static::where('kegiatan_id', $model->kegiatan_id)
                    ->max('urutan');
                $model->urutan = $maxUrutan ? $maxUrutan + 1 : 1;
            }
        });
    }

    /**
     * Get the kegiatan that owns the PertanyaanKegiatan.
     *
     * @return BelongsTo
     */
    public function kegiatan(): BelongsTo
    {
        return $this->belongsTo(Kegiatan::class, 'kegiatan_id', 'id_kegiatan');
    }

    /**
     * Get all of the jawaban for the PertanyaanKegiatan.
     *
     * @return HasMany
     */
    // public function jawaban(): HasMany
    // {
    //     return $this->hasMany(JawabanKegiatan::class, 'pertanyaan_id', 'id_pertanyaan');
    // }

    /**
     * Get the user who created this PertanyaanKegiatan.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id_user');
    }

    /**
     * Get the user who last updated this PertanyaanKegiatan.
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id_user');
    }

    /**
     * Retrieve filtered pertanyaan kegiatan data.
     *
     * @param array<string, mixed> $filters
     * @return Collection
     */
    public static function getFilters(array $filters = []): Collection
    {
        $query = DB::table('pertanyaan_kegiatan as pk')
            ->select(
                'pk.id_pertanyaan',
                'pk.kegiatan_id',
                'pk.urutan',
                'pk.pertanyaan',
                'pk.status',
                'k.kode_kegiatan',
                'k.nama_kegiatan'
            )
            ->leftJoin('kegiatan as k', 'pk.kegiatan_id', '=', 'k.id_kegiatan')
            ->orderBy('k.kode_kegiatan', 'DESC')
            ->orderBy('pk.urutan', 'ASC')
            ->orderBy('k.created_at', 'DESC');

        // Filter by status
        if (!empty($filters['filter_status'])) {
            $query->where('pk.status', $filters['filter_status']);
        }

        // Filter by kegiatan_id
        if (!empty($filters['filter_kegiatan'])) {
            $query->where('pk.kegiatan_id', $filters['filter_kegiatan']);
        }

        // Search in pertanyaan, kode_kegiatan, or nama_kegiatan
        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('pk.pertanyaan', 'like', $search)
                    ->orWhere('k.nama_kegiatan', 'like', $search)
                    ->orWhere('k.kode_kegiatan', 'like', $search);
            });
        }

        return $query->get();
    }


    /**
     * Get pertanyaan by kegiatan ID.
     *
     * @param int $kegiatanId
     * @return Collection
     */
    public static function getByKegiatan(int $kegiatanId): Collection
    {
        return static::select('id_pertanyaan', 'kegiatan_id', 'pertanyaan')->where('kegiatan_id', $kegiatanId)
            ->where('status', 'active')
            ->orderBy('urutan', 'ASC')
            ->get();
    }

    /**
     * Scope a query to only include active pertanyaan.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include pertanyaan by kegiatan.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $kegiatanId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByKegiatan($query, $kegiatanId)
    {
        return $query->where('kegiatan_id', $kegiatanId);
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

    /**
     * Get mapping nama bulan berdasarkan angka.
     *
     * @return array<int, string>
     */
    protected static function getBulanMap(): array
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
        ];
    }

    public static function getRelationship(int $id): ?Collection
    {
        $data = DB::table('pertanyaan_kegiatan as pk')
            ->select(
                'pk.id_pertanyaan',
                'pk.kegiatan_id',
                'pk.urutan',
                'pk.pertanyaan',
                'pk.status',
                'pk.created_by',
                'k.kode_kegiatan',
                'k.nama_kegiatan',
                'k.bulan',
                'k.tanggal_mulai',
                'k.tanggal_selesai',
                'k.batas_akhir_upload',
                'k.dasar_hukum',
                'k.status as kegiatan_status'
            )
            ->leftJoin('kegiatan as k', 'pk.kegiatan_id', '=', 'k.id_kegiatan')
            ->where('pk.id_pertanyaan', $id)
            ->first();

        if (!$data) {
            return null;
        }

        $bulanMap = self::getBulanMap();

        return collect((array) $data)
            ->put('nama_bulan', $bulanMap[$data->bulan] ?? null);
    }

    /**
     * Get all of the persyaratan for the PertanyaanKegiatan
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function persyaratan(): HasMany
    {
        return $this->hasMany(Persyaratan::class, 'pertanyaan_kegiatan_id', 'id_pertanyaan');
    }
}
