<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

class Petugas extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'petugas';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_petugas';

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
        'user_id',
        'kecamatan_id',
        'desa_id',
        'nama_lengkap',
        'nip',
        'jabatan',
        'unit_kerja',
        'no_telp',
        'tanggal_awal',
        'tanggal_akhir',
        'alamat',
        'foto_petugas',
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
        'id_petugas' => 'integer',
        'user_id' => 'integer',
        'kecamatan_id' => 'integer',
        'desa_id' => 'integer',
        'nama_lengkap' => 'string',
        'nip' => 'string',
        'jabatan' => 'string',
        'unit_kerja' => 'string',
        'no_telp' => 'string',
        'tanggal_awal' => 'date',
        'tanggal_akhir' => 'date',
        'alamat' => 'string',
        'foto_petugas' => 'string',
        'status' => 'string',
        'created_by' => 'integer',
        'updated_by' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot method for the model to handle automatic user attribution.
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

        static::deleting(function ($petugas) {
            // 1. Obfuscate NIP untuk melepaskan pengunci Unique DB
            if ($petugas->nip) {
                $petugas->nip = $petugas->nip . '-del-' . time();
                $petugas->saveQuietly(); // Menyimpan tanpa memicu event 'updated'
            }

            // 2. Cascade Soft-Delete ke akun User terkait
            if ($petugas->user) {
                $petugas->user->delete();
            }
        });
    }

    /**
     * Get the user associated with the Petugas.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id', 'id_user');
    }

    /**
     * Get the kecamatan associated with the Petugas.
     */
    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id', 'id_kecamatan');
    }

    /**
     * Get the desa associated with the Petugas.
     */
    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class, 'desa_id', 'id_desa');
    }

    /**
     * Get all of the kegiatan for the Petugas.
     *
     * @return HasMany
     */
    // public function kegiatan(): HasMany
    // {
    //     return $this->hasMany(Kegiatan::class, 'petugas_id', 'id_petugas');
    // }

    /**
     * Get all of the laporan for the Petugas.
     *
     * @return HasMany
     */
    // public function laporan(): HasMany
    // {
    //     return $this->hasMany(Laporan::class, 'petugas_id', 'id_petugas');
    // }

    /**
     * Get all wilayah binaan for the Petugas.
     */
    public function wilayahBinaan(): HasMany
    {
        return $this->hasMany(PetugasWilayahBinaan::class, 'petugas_id', 'id_petugas');
    }

    /**
     * Get all kecamatan binaan (For Petugas Inspektorat).
     */
    public function kecamatanBinaan(): BelongsToMany
    {
        return $this->belongsToMany(Kecamatan::class, 'petugas_wilayah_binaan', 'petugas_id', 'kecamatan_id')
                    ->whereNotNull('petugas_wilayah_binaan.kecamatan_id');
    }

    /**
     * Get all desa binaan (For Petugas Kecamatan).
     */
    public function desaBinaan(): BelongsToMany
    {
        return $this->belongsToMany(Desa::class, 'petugas_wilayah_binaan', 'petugas_id', 'desa_id')
                    ->whereNotNull('petugas_wilayah_binaan.desa_id');
    }

    /**
     * Get the user who created this Petugas.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id_user')->withTrashed();
    }

    /**
     * Get the user who last updated this Petugas.
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id_user')->withTrashed();
    }

    /**
     * Retrieve filtered petugas data.
     *
     * @param  array<string, mixed>  $filters
     */
    public static function getFilters(array $filters = [], ?string $context = null): \Illuminate\Support\Collection
    {
        $user = Auth::user();
        $petugasLogin = $user->petugas;

        $query = self::query()
            ->select([
                'petugas.id_petugas',
                'petugas.user_id',
                'petugas.kecamatan_id',
                'petugas.desa_id',
                'petugas.nama_lengkap',
                'petugas.nip',
                'petugas.jabatan',
                'petugas.unit_kerja',
                'petugas.no_telp',
                'petugas.status',
                'k.nama_kecamatan',
                'd.nama_desa',
                'u.name as user_name',
                'u.email as user_email',
                'u.status as user_status',
            ])
            ->leftJoin('kecamatan as k', 'k.id_kecamatan', '=', 'petugas.kecamatan_id')
            ->leftJoin('desa as d', 'd.id_desa', '=', 'petugas.desa_id')
            ->leftJoin('users as u', 'u.id_user', '=', 'petugas.user_id')
            ->orderBy('petugas.nama_lengkap', 'ASC');

        /**
         * 🧭 Filter berdasarkan konteks menu
         */
        if ($context === 'inspektorat') {
            $query->whereNull('petugas.kecamatan_id')->whereNull('petugas.desa_id');
        } elseif ($context === 'kecamatan') {
            $query->whereNotNull('petugas.kecamatan_id')->whereNull('petugas.desa_id');
        } elseif ($context === 'desa') {
            $query->whereNotNull('petugas.kecamatan_id')->whereNotNull('petugas.desa_id');
        }

        /**
         * 🔐 Logika pembatasan berdasarkan user login
         */
        if ($petugasLogin) {
            // kalau user dari kecamatan → filter kecamatan
            if ($petugasLogin->kecamatan_id && !$petugasLogin->desa_id) {
                $query->where('petugas.kecamatan_id', $petugasLogin->kecamatan_id);
            }
            // kalau user dari desa → filter desa
            elseif ($petugasLogin->desa_id) {
                $query->where('petugas.desa_id', $petugasLogin->desa_id);
            }
            // kalau inspektorat → bebas lihat semua (karena sudah difilter context di atas)
            // user tanpa relasi petugas (developer) → bisa lihat semua
        }

        if ($user && $user->isDeveloper()) {
            $query->withTrashed();
        }

        /**
         * 🧭 Filter tambahan dari request
         */
        if (!empty($filters['filter_status'])) {
            $query->where('petugas.status', $filters['filter_status']);
        }

        if (!empty($filters['filter_kecamatan'])) {
            $query->where('petugas.kecamatan_id', $filters['filter_kecamatan']);
        }

        if (!empty($filters['filter_desa'])) {
            $query->where('petugas.desa_id', $filters['filter_desa']);
        }

        if (!empty($filters['filter_jabatan'])) {
            $query->where('petugas.jabatan', $filters['filter_jabatan']);
        }

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('petugas.nama_lengkap', 'like', "%{$search}%")
                    ->orWhere('petugas.nip', 'like', "%{$search}%")
                    ->orWhere('petugas.jabatan', 'like', "%{$search}%")
                    ->orWhere('petugas.unit_kerja', 'like', "%{$search}%")
                    ->orWhere('k.nama_kecamatan', 'like', "%{$search}%")
                    ->orWhere('d.nama_desa', 'like', "%{$search}%")
                    ->orWhere('u.name', 'like', "%{$search}%")
                    ->orWhere('u.email', 'like', "%{$search}%");
            });
        }

        return $query->get();
    }

    /**
     * Get unique jabatan list.
     */
    public static function getJabatanList(): array
    {
        return self::whereNotNull('jabatan')
            ->where('jabatan', '!=', '')
            ->distinct()
            ->pluck('jabatan')
            ->toArray();
    }

    /**
     * Scope a query to only include active petugas.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include petugas by kecamatan.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $kecamatanId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByKecamatan($query, $kecamatanId)
    {
        return $query->where('kecamatan_id', $kecamatanId);
    }

    /**
     * Scope a query to only include petugas by desa.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  int  $desaId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByDesa($query, $desaId)
    {
        return $query->where('desa_id', $desaId);
    }

    public static function getRelationship(int $id): ?self
    {
        $query = self::query()
            ->select([
                'petugas.id_petugas',
                'petugas.user_id',
                'petugas.kecamatan_id',
                'petugas.desa_id',
                'petugas.nama_lengkap',
                'petugas.nip',
                'petugas.jabatan',
                'petugas.unit_kerja',
                'petugas.alamat',
                'petugas.no_telp',
                'petugas.foto_petugas',
                'petugas.status',
                'petugas.tanggal_awal',
                'petugas.tanggal_akhir',
                'k.nama_kecamatan',
                'd.nama_desa',
                'u.username as username',
                'u.email as email',
                'u.status as user_status',
            ])
            ->leftJoin('kecamatan as k', 'k.id_kecamatan', '=', 'petugas.kecamatan_id')
            ->leftJoin('desa as d', 'd.id_desa', '=', 'petugas.desa_id')
            ->leftJoin('users as u', 'u.id_user', '=', 'petugas.user_id')
            ->where('id_petugas', $id)
            ->first();

        return $query;
    }
}
