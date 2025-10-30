<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Keterlambatan extends Model
{
    use HasFactory;

    protected $table = 'keterlambatan';
    protected $primaryKey = 'id_keterlambatan';

    protected $fillable = [
        'laporan_id',
        'desa_id',
        'tanggal_target',
        'tanggal_upload',
        'hari_keterlambatan'
    ];

    protected $casts = [
        'tanggal_target' => 'date',
        'tanggal_upload' => 'date',
    ];

    // Relationships
    public function laporan(): BelongsTo
    {
        return $this->belongsTo(LaporanKegiatan::class, 'laporan_id', 'id_laporan');
    }

    public function desa(): BelongsTo
    {
        return $this->belongsTo(Desa::class, 'desa_id', 'id_desa');
    }

    // Scopes
    public function scopeByDesa($query, $desaId)
    {
        return $query->where('desa_id', $desaId);
    }

    public function scopeTerlambat($query)
    {
        return $query->where('hari_keterlambatan', '>', 0);
    }

    public function scopeTepatWaktu($query)
    {
        return $query->where('hari_keterlambatan', '<=', 0);
    }

    public function scopeByPeriode($query, $tahun, $bulan = null)
    {
        // Relasikan melalui laporan_kegiatan
        return $query->whereHas('laporan', function ($q) use ($tahun, $bulan) {
            $q->where('tahun', $tahun);
            if ($bulan) {
                $q->where('bulan', $bulan);
            }
        });
    }

    // Helper methods
    public function isTerlambat(): bool
    {
        return $this->hari_keterlambatan > 0;
    }

    public function getKeterlambatanText(): string
    {
        if ($this->hari_keterlambatan > 0) {
            return "Terlambat {$this->hari_keterlambatan} hari";
        } elseif ($this->hari_keterlambatan < 0) {
            return "Lebih cepat " . abs($this->hari_keterlambatan) . " hari";
        } else {
            return "Tepat waktu";
        }
    }
}
