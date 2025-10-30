<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class Kegiatan extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'kegiatan';

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = 'id_kegiatan';

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
        'jenis_kegiatan_id',
        'kode_kegiatan',
        'nama_kegiatan',
        'bulan',
        'tanggal_mulai',
        'tanggal_selesai',
        'batas_akhir_upload',
        'dasar_hukum',
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
        'bulan' => 'integer',
        'tanggal_mulai' => 'integer',
        'tanggal_selesai' => 'integer',
        'batas_akhir_upload' => 'integer',
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
     * Get the tahunAnggaran that owns the Kegiatan.
     *
     * @return BelongsTo
     */
    public function tahunAnggaran(): BelongsTo
    {
        return $this->belongsTo(TahunAnggaran::class, 'tahun_anggaran_id', 'id_tahun_anggaran');
    }

    /**
     * Get the jenisKegiatan that owns the Kegiatan.
     *
     * @return BelongsTo
     */
    public function jenisKegiatan(): BelongsTo
    {
        return $this->belongsTo(JenisKegiatan::class, 'jenis_kegiatan_id', 'id_jenis_kegiatan');
    }

    /**
     * Get all of the laporan for the Kegiatan.
     *
     * @return HasMany
     */
    // public function laporan(): HasMany
    // {
    //     return $this->hasMany(Laporan::class, 'kegiatan_id', 'id_kegiatan');
    // }

    /**
     * Get the user who created this Kegiatan.
     *
     * @return BelongsTo
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id_user');
    }

    /**
     * Get the user who last updated this Kegiatan.
     *
     * @return BelongsTo
     */
    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by', 'id_user');
    }

    /**
     * Retrieve filtered kegiatan data.
     *
     * @param array<string, mixed> $filters
     * @return Collection
     */
    public static function getFilters(array $filters = []): Collection
    {
        $query = DB::table('kegiatan as k')
            ->select(
                'k.id_kegiatan',
                'k.tahun_anggaran_id',
                'k.jenis_kegiatan_id',
                'k.kode_kegiatan',
                'k.nama_kegiatan',
                'k.bulan',
                'k.status',
                'ta.tahun',
                'jk.kode_jenis',
                'jk.nama_jenis',
                DB::raw('COUNT(pk.id_pertanyaan) as jumlah_pertanyaan')
            )
            ->leftJoin('tahun_anggaran as ta', 'k.tahun_anggaran_id', '=', 'ta.id_tahun_anggaran')
            ->leftJoin('jenis_kegiatan as jk', 'k.jenis_kegiatan_id', '=', 'jk.id_jenis_kegiatan')
            ->leftJoin('pertanyaan_kegiatan as pk', 'pk.kegiatan_id', '=', 'k.id_kegiatan')
            ->groupBy(
                'k.id_kegiatan',
                'k.tahun_anggaran_id',
                'k.jenis_kegiatan_id',
                'k.kode_kegiatan',
                'k.nama_kegiatan',
                'k.bulan',
                'k.status',
                'ta.tahun',
                'jk.kode_jenis',
                'jk.nama_jenis'
            )
            ->orderBy('ta.tahun', 'DESC')
            ->orderBy('k.bulan', 'ASC')
            ->orderBy('k.kode_kegiatan', 'ASC');

        // Filter conditions
        if (!empty($filters['filter_status'])) {
            $query->where('k.status', $filters['filter_status']);
        }

        if (!empty($filters['filter_tahun'])) {
            $query->where('k.tahun_anggaran_id', $filters['filter_tahun']);
        }

        if (!empty($filters['filter_jenis'])) {
            $query->where('k.jenis_kegiatan_id', $filters['filter_jenis']);
        }

        if (!empty($filters['filter_bulan'])) {
            $query->where('k.bulan', $filters['filter_bulan']);
        }

        if (!empty($filters['search'])) {
            $search = '%' . $filters['search'] . '%';
            $query->where(function ($q) use ($search) {
                $q->where('k.kode_kegiatan', 'like', $search)
                    ->orWhere('k.nama_kegiatan', 'like', $search)
                    ->orWhere('ta.tahun', 'like', $search)
                    ->orWhere('jk.nama_jenis', 'like', $search)
                    ->orWhere('jk.kode_jenis', 'like', $search);
            });
        }

        $results = $query->get();
        $bulanMap = self::getBulanMap();

        return $results->map(function ($item) use ($bulanMap) {
            $item->nama_bulan = $bulanMap[$item->bulan] ?? null;
            return $item;
        });
    }

    /**
     * Check if kode_kegiatan is unique for the given tahun_anggaran_id.
     *
     * @param string $kodeKegiatan
     * @param int $tahunAnggaranId
     * @param int|null $excludeId
     * @return bool
     */
    public static function isKodeUnique(string $kodeKegiatan, int $tahunAnggaranId, ?int $excludeId = null): bool
    {
        $query = static::where('kode_kegiatan', $kodeKegiatan)
            ->where('tahun_anggaran_id', $tahunAnggaranId);

        if ($excludeId) {
            $query->where('id_kegiatan', '!=', $excludeId);
        }

        return !$query->exists();
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

    /**
     * Get periode pelaksanaan.
     *
     * @return string|null
     */
    public function getPeriodePelaksanaanAttribute(): ?string
    {
        if (!$this->bulan) return null;

        $periode = $this->nama_bulan;

        if ($this->tanggal_mulai && $this->tanggal_selesai) {
            $periode .= ' (' . $this->tanggal_mulai . ' - ' . $this->tanggal_selesai . ')';
        }

        return $periode;
    }

    /**
     * Get batas akhir upload date.
     *
     * @return string|null
     */
    public function getBatasAkhirUploadDateAttribute(): ?string
    {
        if (!$this->bulan || !$this->tanggal_selesai || !$this->batas_akhir_upload) {
            return null;
        }

        $tahun = $this->tahunAnggaran->tahun;
        $batasDate = date('Y-m-d', strtotime("$tahun-{$this->bulan}-{$this->tanggal_selesai} +{$this->batas_akhir_upload} days"));

        return date('d F Y', strtotime($batasDate));
    }

    /**
     * Scope a query to only include active kegiatan.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope a query to only include kegiatan by tahun anggaran.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $tahunAnggaranId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByTahunAnggaran($query, $tahunAnggaranId)
    {
        return $query->where('tahun_anggaran_id', $tahunAnggaranId);
    }

    /**
     * Scope a query to only include kegiatan by jenis kegiatan.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $jenisKegiatanId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByJenisKegiatan($query, $jenisKegiatanId)
    {
        return $query->where('jenis_kegiatan_id', $jenisKegiatanId);
    }

    /**
     * Scope a query to only include kegiatan by bulan.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $bulan
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByBulan($query, $bulan)
    {
        return $query->where('bulan', $bulan);
    }

    public static function getRelationship(int $id): ?Collection
    {
        $data = DB::table('kegiatan as k')
            ->select(
                'k.id_kegiatan',
                'k.tahun_anggaran_id',
                'k.jenis_kegiatan_id',
                'k.kode_kegiatan',
                'k.nama_kegiatan',
                'k.bulan',
                'k.tanggal_mulai',
                'k.tanggal_selesai',
                'k.batas_akhir_upload',
                'k.dasar_hukum',
                'k.status',
                'ta.tahun',
                'jk.kode_jenis',
                'jk.nama_jenis'
            )
            ->leftJoin('tahun_anggaran as ta', 'k.tahun_anggaran_id', '=', 'ta.id_tahun_anggaran')
            ->leftJoin('jenis_kegiatan as jk', 'k.jenis_kegiatan_id', '=', 'jk.id_jenis_kegiatan')
            ->where('k.id_kegiatan', $id)
            ->first();

        if (!$data) return null;

        $bulanMap = self::getBulanMap();

        // Convert to Collection + tambahkan nama_bulan
        return collect((array) $data)->put('nama_bulan', $bulanMap[$data->bulan] ?? null);
    }
}
