<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Kecamatan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kecamatan';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_kecamatan';

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
        'kode_kecamatan',
        'nama_kecamatan',
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
        'id_kecamatan' => 'integer',
        'kode_kecamatan' => 'string',
        'nama_kecamatan' => 'string',
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
     * Get all of the desa for the Kecamatan.
     *
     * @return HasMany
     */
    public function desa(): HasMany
    {
        return $this->hasMany(Desa::class, 'kecamatan_id', 'id_kecamatan');
    }

    /**
     * Get all of the kegiatan for the Kecamatan.
     *
     * @return HasMany
     */
    // public function kegiatan(): HasMany
    // {
    //     return $this->hasMany(Kegiatan::class, 'kecamatan_id', 'id_kecamatan');
    // }

    /**
     * Get the user who created this Kecamatan.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id_user');
    }

    /**
     * Get the user who last updated this Kecamatan.
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id_user');
    }

    /**
     * Retrieve filtered kecamatan data.
     *
     * @param array<string, string> $filters
     * @return Collection
     */
    public static function getFilters(array $filters = []): Collection
    {
        $query = DB::table('kecamatan')
            ->select('id_kecamatan', 'kode_kecamatan', 'nama_kecamatan', 'status')
            ->orderBy('created_at', 'DESC');

        if (!empty($filters['filter_status'])) {
            $query->where('status', $filters['filter_status']);
        }

        if (!empty($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('nama_kecamatan', 'like', '%' . $filters['search'] . '%')
                    ->orWhere('kode_kecamatan', 'like', '%' . $filters['search'] . '%');
            });
        }

        return $query->get();
    }

    /**
     * Get active kecamatan data.
     *
     * @return Collection
     */
    public static function getActive(): Collection
    {
        return DB::table('kecamatan')
            ->select('id_kecamatan', 'kode_kecamatan', 'nama_kecamatan')
            ->where('status', 'active')
            ->orderBy('nama_kecamatan')
            ->get();
    }
}
