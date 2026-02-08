<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Desa extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'desa';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_desa';

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
        'kecamatan_id',
        'kode_desa',
        'nama_desa',
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
        'id_desa' => 'integer',
        'kecamatan_id' => 'integer',
        'kode_desa' => 'string',
        'nama_desa' => 'string',
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
     * Get the kecamatan that owns the Desa.
     *
     * @return BelongsTo
     */
    public function kecamatan(): BelongsTo
    {
        return $this->belongsTo(Kecamatan::class, 'kecamatan_id', 'id_kecamatan');
    }

    /**
     * Get all of the kegiatan for the Desa.
     *
     * @return HasMany
     */
    // public function kegiatan(): HasMany
    // {
    //     return $this->hasMany(Kegiatan::class, 'desa_id', 'id_desa');
    // }

    /**
     * Get the user who created this Desa.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id_user');
    }

    /**
     * Get the user who last updated this Desa.
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id_user');
    }

    /**
     * Retrieve filtered desa data.
     *
     * @param array<string, mixed> $filters
     * @return Collection
     */
    public static function getFilters(array $filters = []): Collection
    {
        $query = DB::table('desa')
            ->select(
                'desa.id_desa',
                'desa.kecamatan_id',
                'desa.kode_desa',
                'desa.nama_desa',
                'desa.status',
                'kecamatan.nama_kecamatan'
            )
            ->leftJoin('kecamatan', 'desa.kecamatan_id', '=', 'kecamatan.id_kecamatan')
            ->orderBy('desa.created_at', 'DESC');

        // Role-based filtering
        $user = Auth::user();
        if ($user && $user->petugas) {
            $petugas = $user->petugas;
            if ($petugas->desa_id) {
                // Petugas Desa: Show only their desa
                $query->where('desa.id_desa', $petugas->desa_id);
            } elseif ($petugas->kecamatan_id) {
                // Petugas Kecamatan: Show all desa in their kecamatan
                $query->where('desa.kecamatan_id', $petugas->kecamatan_id);
            }
        }

        if (!empty($filters['filter_status'])) {
            $query->where('desa.status', $filters['filter_status']);
        }

        if (!empty($filters['filter_kecamatan'])) {
            $query->where('desa.kecamatan_id', $filters['filter_kecamatan']);
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';

            $query->where(function ($q) use ($search) {
                $q->where('desa.nama_desa', 'like', $search)
                    ->orWhere('desa.kode_desa', 'like', $search)
                    ->orWhere('kecamatan.nama_kecamatan', 'like', $search);
            });
        }

        return $query->get();
    }

    /**
     * Retrieve a single desa record with its related kecamatan name.
     *
     * This static method fetches a specific desa by its ID, including the
     * associated kecamatan name through a left join.
     *
     * @param int $id The ID of the desa to retrieve.
     * @return \App\Models\Desa|null The Desa model instance if found, otherwise null.
     */
    public static function getRelationship(int $id): ?self
    {
        return self::select(
            'desa.id_desa',
            'desa.kecamatan_id',
            'desa.kode_desa',
            'desa.nama_desa',
            'desa.status',
            'desa.created_at',
            'desa.updated_at',
            'kecamatan.nama_kecamatan'
        )
            ->leftJoin('kecamatan', 'kecamatan.id_kecamatan', '=', 'desa.kecamatan_id')
            ->where('desa.id_desa', $id)
            ->first();
    }
}
